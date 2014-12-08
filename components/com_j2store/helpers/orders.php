<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/



// no direct access
defined('_JEXEC') or die('Restricted access');

class J2StoreOrdersHelper {


	public static function sendUserEmail($user_id, $order_id, $payment_status, $order_status, $order_state_id)
	{
		$mainframe =JFactory::getApplication();
		$config = JFactory::getConfig();
		$j2params     = JComponentHelper::getParams('com_j2store');

		if(version_compare(JVERSION, '3.0', 'ge')) {
			$sitename = $config->get('sitename');
		} else {
			$sitename = $config->getValue('config.sitename');
		}

		//now get the order table's id based on order id
		$order = J2StoreOrdersHelper::_getOrderKey($order_id);

		//inventory
		//TODO::move this function to the plugin.
		require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/inventory.php');
		require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/version.php');
		J2StoreInventory::setInventory($order->id, $order_state_id);

		//now get the receipient
		$recipient = J2StoreOrdersHelper::_getRecipient($order->order_id);

		//check for email templates. If it is there, get the orderemail from there
		require_once (JPATH_SITE.'/components/com_j2store/helpers/email.php');
		$emailHelper = new J2StoreHelperEmail();

		$orderObj=self::getOrder($order->id);

		if(count($emailHelper->getEmailTemplates($orderObj)) && J2STORE_PRO == 1) {
			$mailer = $emailHelper->getEmail($orderObj);
		} else {

			if($user_id && empty($recipient->billing_first_name)) {
				$recipient->name = JFactory::getUser($user_id)->name;
			} else {
				$recipient->name = $recipient->billing_first_name.' '.$recipient->billing_last_name;
			}

			$body = J2StoreOrdersHelper::_getHtmlFormatedOrder($order->id, $user_id);
			$subject = JText::sprintf('J2STORE_ORDER_USER_EMAIL_SUB', $recipient->name, $sitename);
			$mailer =clone JFactory::getMailer();
			$mailer->IsHTML(true);
			$mailer->CharSet = 'UTF-8';
			$mailer->setSubject( $subject );
			$mailer->setBody($body);

		}

		$admin_emails = $j2params->get('admin_email') ;
		$admin_emails = explode(',',$admin_emails ) ;

		if(version_compare(JVERSION, '3.0', 'ge')) {
			$mailfrom = $j2params->get('emails_defaultemail', $config->get('mailfrom'));
			$fromname = $j2params->get('emails_defaultname', $config->get('fromname'));
		} else {
			$mailfrom = $j2params->get('emails_defaultemail', $config->getValue('config.mailfrom'));
			$fromname = $j2params->get('emails_defaultname', $config->getValue('config.fromname'));
		}

		//send email
		if ($recipient)
		{

			$mailer->addRecipient($recipient->user_email);
		//	$mailer->addBCC( $admin_emails );
			$mailer->setSender(array( $mailfrom, $fromname ));
			$mailer->send();
			$mailer = null;
		}

		if($admin_emails) {
			if(count($emailHelper->getEmailTemplates($orderObj)) && J2STORE_PRO == 1) {
				$mailer = $emailHelper->getEmail($orderObj);
			} else {
				$mailer =clone JFactory::getMailer();
				$mailer->IsHTML(true);
				$mailer->CharSet = 'UTF-8';
				$mailer->setSubject( $subject );
				$mailer->setBody($body);
			}
			$mailer->addRecipient($admin_emails);
			$mailer->setSender(array( $mailfrom, $fromname ));
			$mailer->send();
			$mailer = null;
		}

		return true;
	}


	function _getUser($uid)
	{

		$db =JFactory::getDBO();
		$q = "SELECT name, email FROM #__users "
		. "WHERE id = {$uid}"
		;

		$db->setQuery($q);
		$user_email = $db->loadObject();

		if ($error = $db->getErrorMsg()) {
			JError::raiseError(500, $error);
			return false;
		}

		return $user_email;
	}


	public static function _getRecipient($order_id) {


		$db =JFactory::getDBO();
		$q = "SELECT user_email,user_id,billing_first_name,billing_last_name FROM #__j2store_orderinfo"
		. " WHERE order_id = ".$db->q($order_id);
		;
		$db->setQuery($q);
		$user_email = $db->loadObject();

		if ($error = $db->getErrorMsg()) {
			JError::raiseError(500, $error);
			return false;
		}

		return $user_email;
	}


	public static function _getOrderKey($order_id) {

		$db = JFactory::getDBO();
		$query = 'SELECT * FROM #__j2store_orders WHERE order_id='.$db->Quote($order_id);
		$db->setQuery($query);
		return $db->loadObject();
	}


	public static function _getHtmlFormatedOrder($id, $user_id) {

		$app = JFactory::getApplication();
		$j2storeparams   = JComponentHelper::getParams('com_j2store');


		$sitename   = $j2storeparams->get( 'sitename', $app->getCfg('sitename') );
		$siteurl    = $j2storeparams->get( 'siteurl', JURI::root() );

		$html = ' ';

		JLoader::register( "J2StoreViewOrders", JPATH_SITE."/components/com_j2store/views/orders/view.html.php" );

		$config = array();
		$config['base_path'] = JPATH_SITE."/components/com_j2store";
			// finds the default Site template
			$db = JFactory::getDBO();
			$query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home=1";
			$db->setQuery( $query );
			$template = $db->loadResult();

			jimport('joomla.filesystem.file');
			if (JFile::exists(JPATH_SITE.'/templates/'.$template.'/html/com_j2store/orders/orderemail.php'))
			{
				// (have to do this because we load the same view from the admin-side Orders view, and conflicts arise)
				$config['template_path'] = JPATH_SITE.'/templates/'.$template.'/html/com_j2store/orders';
			}



		require_once(JPATH_SITE.'/components/com_j2store/models/orders.php');
		$model =  new J2StoreModelOrders();
		//lets set the id first
		$model->setId($id);

		$order = $model->getTable( 'orders' );
		$order->load( $model->getId() );
		$orderitems = $order->getItems();
		$row = $model->getItem();
		$shipping_info = $model->getShippingInfo($row);
		$ordertaxes = $order->getOrderTax();
		if(!$user_id) {
			$isGuest = true;
		}else{
			$isGuest=false;
		}
		if(!empty($order->customer_language)) {
			$lang = JFactory::getLanguage();
			$lang->load('com_j2store', JPATH_SITE, $order->customer_language);
		}
		$view = new J2StoreViewOrders( $config );
		$view->addTemplatePath(JPATH_SITE.'/templates/'.$template.'/html/com_j2store/orders');

		$view->set( '_controller', 'orders' );
		$view->set( '_view', 'orders' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', false);
		$view->setModel( $model, true );
		$view->assign( 'row', $row );
		$view->assign( 'shipping_info', $shipping_info);
		$show_tax = $j2storeparams->get('show_tax_total');
		$view->assign( 'show_tax', $show_tax );
		foreach ($orderitems as &$item)
		{
			$item->orderitem_price = $item->orderitem_price + floatval( $item->orderitem_attributes_price );
			$taxtotal = 0;
			if($show_tax)
			{
				$taxtotal = ($item->orderitem_tax / $item->orderitem_quantity);
			}
			$item->orderitem_price = $item->orderitem_price + $taxtotal;
			$item->orderitem_final_price = $item->orderitem_price * $item->orderitem_quantity;
			$order->order_subtotal += ($taxtotal * $item->orderitem_quantity);
		}
		$view->assign( 'ordertaxes', $ordertaxes );
		$view->assign( 'order', $order );
		$view->assign( 'isGuest', $isGuest);
		$view->assign( 'sitename', $sitename);
		$view->assign( 'siteurl', $siteurl);
		$view->assign( 'params', $j2storeparams);
		$view->setLayout( 'orderemail' );

		//$this->_setModelState();
		ob_start();
		$view->display();
		$html .= ob_get_contents();
		ob_end_clean();
		return $html;
	}


	public static function getAddress($user_id) {

		$db = JFactory::getDBO();
		$query = 'SELECT tbl.*,c.country_name,z.zone_name'
		.' FROM #__j2store_address AS tbl'
		.' LEFT JOIN #__j2store_countries AS c ON tbl.country_id=c.country_id'
		.' LEFT JOIN #__j2store_zones AS z ON tbl.zone_id=z.zone_id'
		.' WHERE tbl.user_id='.(int) $user_id;
		$db->setQuery($query);
		return $db->loadObject();
	}


	public static function isJson($string) {
		json_decode($string);
		if(function_exists('json_last_error')) {
			return (json_last_error() == JSON_ERROR_NONE);
		}
		return true;
	}

	public static function getOrder($order_id) {

		require_once(JPATH_SITE.'/components/com_j2store/models/orders.php');
		$model =  new J2StoreModelOrders();
		//lets set the id first
		$model->setId($order_id);

		$order = $model->getTable( 'orders' );
		$order->load( $model->getId() );
		$orderitems = $order->getItems();
		$row = $model->getItem();
		$shipping_info = $model->getShippingInfo($row);

		$object = new JObject();
		$object->order = $row;
		$object->orderitems = $orderitems;
		$object->shipping = $shipping_info;
		$object->ordertaxes = $order->getOrderTax();

		return $object;
	}
}
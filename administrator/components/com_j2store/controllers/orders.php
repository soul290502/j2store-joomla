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

jimport('joomla.application.component.controller');
class J2StoreControllerOrders extends J2StoreController {

	function display($cachable = false, $urlparams = array()) {
		JRequest::setVar('view', 'orders');
		parent::display();
	}


	function remove()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to delete' ) );
		}

		$model = $this->getModel('orders');
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_j2store&view=orders', 'Deleted item(s)' );
	}


	function view() {

		$app = JFactory::getApplication();
		if(!$app->isAdmin()) {
			echo JText::_('J2STORE_ERROR_NO_PERMISSION');
			$app->close();
			return;
		}

		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');

		require_once( JPATH_SITE.'/components/com_j2store/models/orders.php' );
		$orders_model = new J2StoreModelOrders;
		$id = $app->input->getInt('id');
		$orders_model  = $this->getModel('orders');
		$orders_model->setId($id);
		$order = $orders_model->getTable( 'orders' );
		$row = $order->load($orders_model->getId());
		$orderitems = $order->getItems();
		$row = $orders_model->getItem();
		$ordertaxes = $order->getOrderTax();
		$shipping_info = $orders_model->getShippingInfo($row);

		$view = $this->getView( 'orders', 'html' );
		$view->set( '_controller', 'orders' );
		$view->set( '_view', 'orders' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', false);
		//$view->setModel( $orders_model, true );
		$view->assign( 'row', $row );
		$view->assign( 'shipping_info', $shipping_info);
		$params = JComponentHelper::getParams('com_j2store');
		$show_tax = $params->get('show_tax_total');
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

		//get the select list;
		require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/models/orderstatuses.php');
		$os_model = new J2StoreModelOrderstatuses();
		$statuses = $os_model->getOrderStatuses();

		$options = array();
		$options[] = JHTML::_('select.option', '', JText::_('J2STORE_ORDER_SELECT_STATE'));

		foreach($statuses as $status) :
			$options[] = JHTML::_('select.option', $status->orderstatus_id, JText::_($status->orderstatus_name));
		endforeach;

		$order_state = JHTML::_('select.genericlist', $options, 'order_state_id', 'class="inputbox"', 'value', 'text', $order->order_state_id);

		$label_class = $os_model->getOrderStateByID($row->order_state_id)->orderstatus_cssclass;
		$view->assign( 'label_class', $label_class );

		//print_r($order);     exit;
		$view->assign( 'ordertaxes', $ordertaxes );
		$view->assign( 'order', $order );
		$view->assign( 'order_state', $order_state );
		$view->assign( 'params', $params );
		$view->setLayout( 'view' );
		$this->_setModelState();
		$view->display();
	}


	function printOrder() {

		$app = JFactory::getApplication();
		if(!$app->isAdmin()) {
			echo JText::_('J2STORE_ERROR_NO_PERMISSION');
			$app->close();
			return;
		}
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
		require_once( JPATH_SITE.'/components/com_j2store/models/orders.php' );
		$orders_model = new J2StoreModelOrders;
		$id = JRequest::getVar('id');
		$orders_model  = $this->getModel('orders');
		$orders_model->setId($id);
		$order = $orders_model->getTable( 'orders' );
		$row = $order->load($orders_model->getId());
		$orderitems = $order->getItems();
		$row = $orders_model->getItem();
		$shipping_info = $orders_model->getShippingInfo($row);
		$ordertaxes = $order->getOrderTax();
		$view = $this->getView( 'orders', 'html' );

		if(!empty($row->shipping_addr_id)){
			$ship_address = JTable::getInstance('Address','Table');
			$ship_address->load($row->shipping_addr_id);
			$view->assign( 'ship_address', $ship_address );
		}

		if(!empty($row->billing_addr_id)){
			$bill_address = JTable::getInstance('Address','Table');
			$bill_address->load($row->billing_addr_id);
			$view->assign( 'bill_address', $bill_address );
		}

		$view->set( '_controller', 'orders' );
		$view->set( '_view', 'orders' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', false);
		//$view->setModel( $orders_model, true );
		$view->assign( 'row', $row );
		$view->assign( 'shipping_info', $shipping_info);
		$params = JComponentHelper::getParams('com_j2store');
		$show_tax = $params->get('show_tax_total');
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
		//print_r($order);     exit;
		$view->assign( 'ordertaxes', $ordertaxes );
		$view->assign( 'order', $order );
		$view->assign( 'params', $params );
		$view->setLayout( 'print' );
		$this->_setModelState();
		$view->display();
	}


	function viewtxnlog() {

		$app = JFactory::getApplication();
		if(!$app->isAdmin()) {
			echo JText::_('J2STORE_ERROR_NO_PERMISSION');
			$app->close();
			return;
		}

		require_once( JPATH_SITE.DS.'components'.DS.'com_j2store'.DS.'models'.DS.'orders.php' );
		$orders_model = new J2StoreModelOrders;
		$id = JRequest::getVar('id');
		$orders_model->setId($id);
		$row = $orders_model->getItem();
		$view = $this->getView( 'orders', 'html' );
		$view->assign( 'row', $row );
		$view->setLayout( 'txnlog' );
		$view->display();
	}


	function _setModelState()
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_j2store');
		$model = $this->getModel('orders');
		$ns = 'com_j2store.orders';

		$state = array();
		$state['limit']  	= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$state['limitstart'] = $app->getUserStateFromRequest($ns.'limitstart', 'limitstart', 0, 'int');
		$state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.'.$model->getTable()->getKeyName(), 'cmd');
		$state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'ASC', 'word');
		$state['filter']    = $app->getUserStateFromRequest($ns.'.filter', 'filter', '', 'string');
		$state['filter_enabled'] 	= $app->getUserStateFromRequest($ns.'enabled', 'filter_enabled', '', '');
		$state['id']        = JRequest::getVar('id', JRequest::getVar('id', '', 'get', 'int'), 'post', 'int');


		// adjust offset for when filter has changed
		if (
				$app->getUserState( $ns.'orderstate' ) != $app->getUserStateFromRequest($ns.'orderstate', 'filter_orderstate', '', '')
		)
		{
			$state['limitstart'] = '0';
		}

		$state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.created_date', 'cmd');
		$state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'DESC', 'word');

		$state['filter_orderstate'] = $app->getUserStateFromRequest($ns.'orderstate', 'filter_orderstate', '', 'string');

		//  $state['filter_userid']     = JFactory::getUser()->id;
		//  $filter_userid = $app->getUserStateFromRequest($ns.'userid', 'filter_userid', JFactory::getUser()->id, 'int');

		$state['filter_total']      = $app->getUserStateFromRequest($ns.'total', 'filter_total', '', 'float');

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}
		return $state;

	}

	function orderstatesave() {

		$app = JFactory::getApplication();
		$id = $app->input->getInt('id', 0);
		$order_state_id = $app->input->getInt('order_state_id', 0);
		$notify_customer= $app->input->getInt('notify_customer', 0);

		if(isset($order_state_id) && $order_state_id > 0 ) {
			require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/models/orderstatuses.php');
			$os_model = new J2StoreModelOrderstatuses();
			$order_state = $os_model->getOrderStateByID($order_state_id)->orderstatus_name;

			JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/tables' );
			$order = JTable::getInstance('Orders', 'Table');
			$order->load($id);

			if($order->id == $id) {
				//lets change the status
				$order->order_state = $order_state;
				$order->order_state_id = $order_state_id;

				if ($order->store()) {
					$msg = JText::_('J2STORE_ORDER_STATUS_UPDATE_SUCCESSFUL');

					if(isset($notify_customer) && $notify_customer == 1) {
						require_once(JPATH_SITE.'/components/com_j2store/helpers/orders.php');
						J2StoreOrdersHelper::sendUserEmail($order->user_id, $order->order_id, $order->transaction_status, $order->order_state, $order->order_state_id);
					} else {
						require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/inventory.php');
						J2StoreInventory::setInventory($order->id, $order_state_id);
					}

				} else {
					$msg = JText::_('J2STORE_ORDER_STATUS_UPDATE_FAILED');
				}
			} else {
				$msg = JText::_('J2STORE_ORDER_STATUS_UPDATE_FAILED');
			}
		} else {
			$msg = JText::_('J2STORE_CHOOSE_AN_ORDER_STATUS');
		}

		$link = 'index.php?option=com_j2store&view=orders&task=view&id='.$order->id;

		$this->setRedirect($link, $msg);
	}

}

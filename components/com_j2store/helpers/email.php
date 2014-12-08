<?php
/*------------------------------------------------------------------------
# com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class J2StoreHelperEmail {

	protected function loadEmailTemplate($order) {

		// Initialise
		$templateText = '';
		$subject = '';
		$loadLanguage = null;
		$isHTML = false;

		// Look for desired languages
		$jLang = JFactory::getLanguage();

		if(JFactory::getUser($order->order->user_id)->id > 0) {
			$userLang = JFactory::getUser()->getParam('language','');
		} else {
			$userLang = $order->order->customer_language;
		}
		$languages = array(
				$userLang, $jLang->getTag(), $jLang->getDefault(), 'en-GB', '*'
		);

		//load all templates
		$allTemplates = $this->getEmailTemplates($order);

		if(count($allTemplates))
		{
			// Pass 1 - Give match scores to each template
			$preferredIndex = null;
			$preferredScore = 0;

			foreach($allTemplates as $idx => $template)
			{
				// Get the language and level of this template
				$myLang = $template->language;

				// Make sure the language matches one of our desired languages, otherwise skip it
				$langPos = array_search($myLang, $languages);
				if ($langPos === false)
				{
					continue;
				}
				$langScore = (5 - $langPos);


				// Calculate the score
				$score = $langScore;
				if ($score > $preferredScore)
				{
					$loadLanguage = $myLang;
					$subject = $template->subject;
					$templateText = $template->body;
					$preferredScore = $score;

					$isHTML = true;
				}
			}
		} else {
			$config = JFactory::getConfig();
			if(version_compare(JVERSION, '3.0', 'ge')) {
				$sitename = $config->get('sitename');
			} else {
				$sitename = $config->getValue('config.sitename');
			}
			$isHTML = true;
			$templateText = J2StoreOrdersHelper::_getHtmlFormatedOrder($order->order->id, $order->order->user_id);
			$subject = JText::sprintf('J2STORE_ORDER_USER_EMAIL_SUB', $order->order->billing_first_name . ' ' .$order->order->billing_last_name, $sitename);
		}
		return array($isHTML, $subject, $templateText, $loadLanguage);
	}

	/**
	 * Method to process tags
	 *
	 * @param string $text Text to process
	 * @param object $order TableOrder object
	 * @param array $extras an array containing extra tags to process
	 */

	protected function processTags($text, $order, $extras=array()) {

		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_j2store');
		require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/prices.php');

		// -- Get the site name
		$config = JFactory::getConfig();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$sitename = $config->get('sitename');
		} else {
			$sitename = $config->getValue('config.sitename');
		}

		//site url
		$baseURL = JURI::base();
		$subpathURL = JURI::base(true);
		//replace administrator string, if present
		$baseURL = str_replace('/administrator', '', $baseURL);
		$subpathURL = str_replace('/administrator', '', $subpathURL);

		//invoice url
		if($order->order->user_id > 0) {
			$url = str_replace('&amp;','&', JRoute::_('index.php?option=com_j2store&view=orders&task=view&id='.$order->order->id));
		} else {
			$url = str_replace('&amp;','&', JRoute::_('index.php?option=com_j2store&view=orders&task=view'));
		}

		$url = str_replace('/administrator', '', $url);
		$url = ltrim($url, '/');
		$subpathURL = ltrim($subpathURL, '/');
		if(substr($url,0,strlen($subpathURL)+1) == "$subpathURL/") $url = substr($url,strlen($subpathURL)+1);
		$invoiceURL = rtrim($baseURL,'/').'/'.ltrim($url,'/');

		//order date
		$order_date = JHTML::_('date', $order->order->created_date, $params->get('date_format', JText::_('DATE_FORMAT_LC1')));


		//items table
		$items = $this->loadItemsTemplate($order);

		if(isset($order->order->invoice_number) && $order->order->invoice_number > 0) {
			$invoice_number = $order->order->invoice_prefix.$order->order->invoice_number;
		}else {
			$invoice_number = $order->order->id;
		}
		//now process tags

		$tags = array(
				"\\n"					=> "\n",
				'[SITENAME]'			=> $sitename,
				'[SITEURL]'				=> $baseURL,
				'[INVOICE_URL]'				=> $invoiceURL,
				'[ORDERID]'				=> $order->order->order_id,

				'[INVOICENO]'			=> $invoice_number,
				'[ORDERDATE]'			=> $order_date,
				'[ORDERSTATUS]'			=> JText::_($order->order->order_state),
				'[ORDERAMOUNT]'			=> J2StorePrices::number( $order->order->order_total, $order->order->currency_code, $order->order->currency_value ),

				'[BILLING_FIRSTNAME]'	=> $order->order->billing_first_name,
				'[BILLING_LASTNAME]'	=> $order->order->billing_last_name,
				'[BILLING_EMAIL]'		=> $order->order->user_email,
				'[BILLING_ADDRESS_1]'	=> $order->order->billing_address_1,
				'[BILLING_ADDRESS_2]'	=> $order->order->billing_address_2,
				'[BILLING_CITY]'		=> $order->order->billing_city,
				'[BILLING_ZIP]'			=> $order->order->billing_zip,
				'[BILLING_COUNTRY]'		=> $order->order->billing_country_name,
				'[BILLING_STATE]'		=> $order->order->billing_zone_name,
				'[BILLING_COMPANY]'		=> $order->order->billing_company,
				'[BILLING_VATID]'		=> $order->order->billing_tax_number,
				'[BILLING_PHONE]'		=> $order->order->billing_phone_1,
				'[BILLING_MOBILE]'		=> $order->order->billing_phone_2,

				'[SHIPPING_FIRSTNAME]'	=> $order->order->shipping_first_name,
				'[SHIPPING_LASTNAME]'	=> $order->order->shipping_last_name,
				'[SHIPPING_ADDRESS_1]'	=> $order->order->shipping_address_1,
				'[SHIPPING_ADDRESS_2]'	=> $order->order->shipping_address_2,
				'[SHIPPING_CITY]'		=> $order->order->shipping_city,
				'[SHIPPING_ZIP]'		=> $order->order->shipping_zip,
				'[SHIPPING_COUNTRY]'	=> $order->order->shipping_country_name,
				'[SHIPPING_STATE]'		=> $order->order->shipping_zone_name,
				'[SHIPPING_COMPANY]'	=> $order->order->shipping_company,
				'[SHIPPING_VATID]'		=> $order->order->shipping_tax_number,
				'[SHIPPING_PHONE]'		=> $order->order->shipping_phone_1,
				'[SHIPPING_MOBILE]'		=> $order->order->shipping_phone_2,

				'[SHIPPING_METHOD]'		=> $order->shipping->ordershipping_name,

				'[CUSTOMER_NOTE]'		=> $order->order->customer_note,
				'[PAYMENT_TYPE]'		=> JText::_($order->order->orderpayment_type),
				'[ORDER_TOKEN]'			=> $order->order->token,


				'[ITEMS]'				=> $items

		);

		$tags = array_merge($tags, $extras);
		foreach ($tags as $key => $value)
		{
			$text = str_replace($key, $value, $text);
		}

		//process custom fields.

		//billing Format [CUSTOM_BILLING_FIELD:KEYNAME]
		$text = $this->processCustomFields($order->order, 'billing', $text);

		//shipping Format [CUSTOM_SHIPPING_FIELD:KEYNAME]
		$text = $this->processCustomFields($order->order, 'shipping', $text);

		//payment Format [CUSTOM_PAYMENT_FIELD:KEYNAME]
		$text = $this->processCustomFields($order->order, 'payment', $text);

		//now we have unprocessed fields. remove any other square brackets found.
		preg_match_all("^\[(.*?)\]^",$text,$removeFields, PREG_PATTERN_ORDER);
		if(count($removeFields[1])) {
			foreach($removeFields[1] as $fieldName) {
				$text= str_replace('['.$fieldName.']', '', $text);
			}
		}
		return $text;

	}

	private function getDecodedFields($json) {
		$result = array();
		if(!empty($json)) {
			$registry = new JRegistry();
			$registry->loadString(stripslashes($json), 'JSON');
			$result = $registry->toArray();
		}
		return $result;
	}

	private function processCustomFields($row, $type, $text) {
		if($type=='billing') {
			$field = 'all_billing';
		}elseif($type=='shipping') {
			$field = 'all_shipping';
		}elseif($type=='payment') {
			$field = 'all_payment';
		}

		$fields = array();
		if(!empty($row->$field) && JString::strlen($row->$field) > 0) {
			$custom_fields = $this->getDecodedFields($row->$field);
			if(isset($custom_fields) && count($custom_fields)) {
				foreach($custom_fields as $namekey=>$field) {
					if(!property_exists($row, $type.'_'.$namekey) && !property_exists($row, 'user_'.$namekey) && $namekey !='country_id' && $namekey != 'zone_id' && $namekey != 'option' && $namekey !='task' && $namekey != 'view' ) {
						$fields[$namekey] = $field;
					}
				}

			}
		}

		if(isset($fields) && count($fields)) {
			foreach($fields as $namekey=>$field) {

				//label
				/* $tag_label = '[CUSTOM_'.strtoupper($type).'_LABEL:'.strtoupper($namekey).']';
				$text = str_replace($tag_label, $field['label'], $text);

				//value
				$tag_value = '[CUSTOM_'.strtoupper($type).'_VALUE:'.strtoupper($namekey).']';

				if(is_array($field['value'])) {
					$v = '';
					foreach($field['value'] as $value) {
						$v .= '-'.JText::_($value).'\n';
					}
					$value = $v;
				} else {
					$value = JText::_($field['value']);
				}


				$text = str_replace($tag_value, $field['value'], $text);

 */
				$string = '';
				if(is_array($field['value'])) {
					foreach($field['value'] as $value) {
						$string .='-'.JText::_($value).'\n';
					}

				}elseif(J2StoreOrdersHelper::isJson(stripslashes($field['value']))) {
					$json_values = json_decode(stripslashes($field['value']));
					foreach($json_values as $value){
						$string .='-'.JText::_($value).'\n';
					}

				} else {
					$string = JText::_($field['value']);
				}

				$value = $field['label'].' : '.$string;

				$tag_value = '[CUSTOM_'.strtoupper($type).'_FIELD:'.strtoupper($namekey).']';

				$text = str_replace($tag_value, $value, $text);
			}
		}

		return $text;

	}

	public function getEmailTemplates($order) {

 		$db = JFactory::getDbo();

			$query = $db->getQuery(true)
			->select('*')
			->from('#__j2store_emailtemplates')
			->where($db->qn('state').'='.$db->q(1))
			->where(' CASE WHEN orderstatus_id = '.$order->order->order_state_id .' THEN orderstatus_id = '.$order->order->order_state_id .'
							ELSE orderstatus_id ="*" OR orderstatus_id =""
						END
					');
			if(isset($order->order->customer_group) && !empty($order->order->customer_group)) {
				$query->where(' CASE WHEN group_id = '.$order->order->customer_group.' THEN group_id IN('.$order->order->customer_group.')
									ELSE group_id ="*" OR group_id =""
								END
					');

			}
			$query->where(' CASE WHEN paymentmethod ='.$db->q($order->order->orderpayment_type).' THEN paymentmethod ='.$db->q($order->order->orderpayment_type).'
							ELSE paymentmethod="*" OR paymentmethod=""
						END
					');

			$db->setQuery($query);
			try {
				$allTemplates = $db->loadObjectList();
			} catch (Exception $e) {
				$allTemplates = array();
			}

		return $allTemplates;
	}

	/**
	 * Creates a PHPMailer instance
	 *
	 * @param   boolean  $isHTML
	 *
	 * @return  PHPMailer  A mailer instance
	 */
	private static function &getMailer($isHTML = true)
	{
		$mailer = clone JFactory::getMailer();

		$mailer->IsHTML($isHTML);
		// Required in order not to get broken characters
		$mailer->CharSet = 'UTF-8';

		return $mailer;
	}


	/**
	 * Method to get the pre-loaded mailer function
	 *
	 * @param object $order
	 * @return PHPMailer  A mailer instance
	 */

	public function getEmail($order) {

		list($isHTML, $subject, $templateText, $loadLanguage) = self::loadEmailTemplate($order);

		$extras= array();
		$templateText = self::processTags($templateText, $order, $extras);
		$subject = self::processTags($subject, $order, $extras);

		$baseURL = str_replace('/administrator', '', JURI::base());
		//replace administrator string, if present
		$baseURL = ltrim($baseURL, '/');

		// Get the mailer
		$mailer = self::getMailer($isHTML);
		$mailer->setSubject($subject);

		// Include inline images
		$pattern = '/(src)=\"([^"]*)\"/i';
		$number_of_matches = preg_match_all($pattern, $templateText, $matches, PREG_OFFSET_CAPTURE);
		if($number_of_matches > 0) {
			$substitutions = $matches[2];
			$last_position = 0;
			$temp = '';

			// Loop all URLs
			$imgidx = 0;
			$imageSubs = array();
			foreach($substitutions as &$entry)
			{
				// Copy unchanged part, if it exists
				if($entry[1] > 0)
					$temp .= substr($templateText, $last_position, $entry[1]-$last_position);
				// Examine the current URL
					 $url = $entry[0];
				if( (substr($url,0,7) == 'http://') || (substr($url,0,8) == 'https://') ) {
					// External link, skip
					$temp .= $url;
				} else {
					 $ext = strtolower(JFile::getExt($url));
					if(!JFile::exists($url)) {
						// Relative path, make absolute
						$url = $baseURL.ltrim($url,'/');
					}
					if( !JFile::exists($url) || !in_array($ext, array('jpg','png','gif')) ) {
						// Not an image or inexistent file
						$temp .= $url;
					} else {
						// Image found, substitute
						if(!array_key_exists($url, $imageSubs)) {
							// First time I see this image, add as embedded image and push to
							// $imageSubs array.
							$imgidx++;
							$mailer->AddEmbeddedImage($url, 'img'.$imgidx, basename($url));
							$imageSubs[$url] = $imgidx;
						}
						// Do the substitution of the image
						$temp .= 'cid:img'.$imageSubs[$url];
					}
				}

				// Calculate next starting offset
				$last_position = $entry[1] + strlen($entry[0]);
			}
			// Do we have any remaining part of the string we have to copy?
			if($last_position < strlen($templateText))
				$temp .= substr($templateText, $last_position);
			// Replace content with the processed one
			$templateText = $temp;
		}
		$mailer->setBody($templateText);
		return $mailer;
	}

	private function loadItemsTemplate($order) {

		$app = JFactory::getApplication();
		$j2storeparams   = JComponentHelper::getParams('com_j2store');

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
		if (JFile::exists(JPATH_SITE.'/templates/'.$template.'/html/com_j2store/orders/orderitems.php'))
		{
			// (have to do this because we load the same view from the admin-side Orders view, and conflicts arise)
			$config['template_path'] = JPATH_SITE.'/templates/'.$template.'/html/com_j2store/orders';
		}

		if(!$order->order->user_id) {
			$isGuest = true;
		}else{
			$isGuest=false;
		}

		if(!empty($order->order->customer_language)) {
			$lang = JFactory::getLanguage();
			$lang->load('com_j2store', JPATH_SITE, $order->order->customer_language);
		}

		$view = new J2StoreViewOrders( $config );
		$view->addTemplatePath(JPATH_SITE.'/templates/'.$template.'/html/com_j2store/orders');

		$view->set( '_controller', 'orders' );
		$view->set( '_view', 'orders' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', false);
		$show_tax = $j2storeparams->get('show_tax_total');
		$view->assign( 'show_tax', $show_tax );


		foreach ($order->orderitems as &$item)
		{
			$item->orderitem_price = $item->orderitem_price + floatval( $item->orderitem_attributes_price );
			$taxtotal = 0;
			if($show_tax)
			{
				$taxtotal = ($item->orderitem_tax / $item->orderitem_quantity);
			}
			$item->orderitem_price = $item->orderitem_price + $taxtotal;
			$item->orderitem_final_price = $item->orderitem_price * $item->orderitem_quantity;
			$order->order->order_subtotal += ($taxtotal * $item->orderitem_quantity);
		}
		$view->assign( 'ordertaxes', $order->ordertaxes );
		$view->assign( 'order', $order );
		$view->assign( 'orderitems', $order->orderitems );
		$view->assign( 'isGuest', $isGuest);
		$view->assign( 'params', $j2storeparams);
		$view->setLayout( 'orderitems' );

		//$this->_setModelState();
		ob_start();
		$view->display();
		$html .= ob_get_contents();
		ob_end_clean();
		return $html;

	}

}
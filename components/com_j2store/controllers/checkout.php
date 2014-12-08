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

jimport('joomla.application.component.controller');
JLoader::register( 'J2StoreHelperCart', JPATH_SITE.'/components/com_j2store/helpers/cart.php');
JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/tables' );
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/base.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/tax.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/inventory.php');
class J2StoreControllerCheckout extends J2StoreController
{

	var $_order        = null;
//	var $defaultShippingMethod = null; // set in constructor
	var $initial_order_state   = 4;
	var $_cartitems = null;
	var $tax = null;
	var $session = null;
	var $option = 'com_j2store';
	var $params = null;

	function __construct()
	{
		parent::__construct();
		JFactory::getDocument()->setCharset('utf-8');
		JResponse::setHeader('X-Cache-Control', 'False', true);
		$this->params = JComponentHelper::getParams($this->option);
	//	$this->defaultShippingMethod = J2StoreHelperCart::getStoreAddress()->config_shipping_default;
		// create the order object
		$this->_order = JTable::getInstance('Orders', 'Table');
		//initialise tax class
		$this->tax = new J2StoreTax();
		//initialise the session object
		$this->session = JFactory::getSession();
		//language
		$language = JFactory::getLanguage();
		/* Set the base directory for the language */
		$base_dir = JPATH_SITE;
		/* Load the language. IMPORTANT Becase we use ajax to load cart */
		$language->load('com_j2store', $base_dir, $language->getTag(), true);
	}

	function display($cachable = false, $urlparams = array()) {
		$app = JFactory::getApplication();

		$values =  $app->input->getArray($_POST);
		$view = $this->getView( 'checkout', 'html' );
		$task = JRequest::getVar('task');
		$model		= $this->getModel('checkout');
		$cart_helper = new J2StoreHelperCart();
		$cart_model = $this->getModel('mycart');
		$link = JRoute::_('index.php?option=com_j2store&view=mycart');
		if (!$cart_helper->hasProducts() && $task != 'confirmPayment' )
		{
			$msg = JText::_('J2STORE_NO_ITEMS_IN_CART');
			$app->redirect($link, $msg);
		}

		//minimum order value check
		//prepare order
		$order= $this->_order;
		$order = $this->populateOrder(false);
		if(!$this->checkMinimumOrderValue($order)) {
			$msg = JText::_('J2STORE_ERROR_MINIMUM_ORDER_VALUE').J2StorePrices::number($this->params->get('global_minordervalue'));
			$app->redirect($link, $msg);
		}

		// Validate minimum quantity requirments.
		$products = $cart_model->getDataNew();
		try {
			J2StoreInventory::validateQuantityRestrictions($products);
		} catch (Exception $e) {
			$app->redirect($link, $e->getMessage());
		}

		$user 		=	JFactory::getUser();

		$isLogged = 0;
		if($user->id) {
			$isLogged = 1;
		}
		$view->assign('logged',$isLogged);

		//prepare shipping
		// Checking whether shipping is required
		$showShipping = false;

		if($this->params->get('show_shipping_address', 0)) {
			$showShipping = true;
		}

		if ($isShippingEnabled = $cart_model->getShippingIsEnabled())
		{
			$showShipping = true;
		}

		//fire a plugin event
		JPluginHelper::importPlugin ('j2store');
		$app->triggerEvent('onJ2StoreBeforeCheckout', array($order));

		$view->assign( 'showShipping', $showShipping );
		$view->assign('params', $this->params);
		$view->setLayout( 'checkout');

		$view->display();
		return;
	}


	function login() {
		$app = JFactory::getApplication();

		$view = $this->getView( 'checkout', 'html' );
		$model		= $this->getModel('checkout');
		//check session
		$account = $this->session->get('account', 'register', 'j2store');
		if (isset($account)) {
			$view->assign('account', $account);
		} else {
			$view->assign('account', 'register');
		}

		$view->assign('params', $this->params);
		$view->setLayout( 'checkout_login');
		$html = '';
		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
		$app->close();
	}

	function login_validate() {

		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$this->session->set('uaccount', 'login', 'j2store');
		$model = $this->getModel('checkout');
		$cart_helper = new J2StoreHelperCart();
		$redirect_url = JRoute::_('index.php?option=com_j2store&view=checkout');



		$json = array();

		if ($user->id) {
			$json['redirect'] = $redirect_url;
		}

		if ((!$cart_helper->hasProducts())) {
			$json['redirect'] = $redirect_url;
		}

		if (!$json) {

			require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/user.php');
			$userHelper = new J2StoreHelperUser;
			//now login the user
			if ( !$userHelper->login(
					array('username' => $app->input->getString('email'), 'password' => $app->input->getString('password'))
			))
			{
				$json['error']['warning'] = JText::_('J2STORE_CHECKOUT_ERROR_LOGIN');
			}

		}

		if (!$json) {
			$this->session->clear('guest', 'j2store');

			// Default Addresses
			$address_info = $this->getModel('address')->getSingleAddressByUserID();

			if ($address_info) {
				if ($this->params->get('config_tax_default') == 'shipping') {
					$this->session->set('shipping_country_id', $address_info->country_id, 'j2store');
					$this->session->set('shipping_zone_id',$address_info->zone_id, 'j2store');
					$this->session->set('shipping_postcode',$address_info->zip, 'j2store');
				}

				if ($this->params->get('config_tax_default') == 'billing') {
					$this->session->set('billing_country_id', $address_info->country_id, 'j2store');
					$this->session->set('billing_zone_id',$address_info->zone_id, 'j2store');
				}
			} else {
				$this->session->clear('shipping_country_id', 'j2store');
				$this->session->clear('shipping_zone_id', 'j2store');
				$this->session->clear('shipping_postcode', 'j2store');
				$this->session->clear('billing_country_id', 'j2store');
				$this->session->clear('billing_zone_id', 'j2store');
			}

			$json['redirect'] = $redirect_url;
		}
		echo json_encode($json);
		$app->close();
	}

	function register() {
		$app = JFactory::getApplication();

		$view = $this->getView( 'checkout', 'html' );
		$model		= $this->getModel('checkout');
		$cart_model = $this->getModel('mycart');
		$link = JRoute::_('index.php?option=com_j2store&view=mycart');

		$this->session->set('uaccount', 'register', 'j2store');

		$products = $cart_model->getDataNew();
		try {
			J2StoreInventory::validateQuantityRestrictions($products);
		} catch (Exception $e) {
			$app->redirect($link, $e->getMessage());
		}

		$selectableBase = J2StoreFactory::getSelectableBase();
		$view->assign('fieldsClass', $selectableBase);
		$address = JTable::getInstance('address', 'Table');
		$fields = $selectableBase->getFields('register',$address,'address');
		$view->assign('fields', $fields);
		$view->assign('address', $address);

		//get layout settings
		$view->assign('storeProfile', J2StoreHelperCart::getStoreAddress());

		$showShipping = false;
		if($this->params->get('show_shipping_address', 0)) {
			$showShipping = true;
		}

		if ($isShippingEnabled = $cart_model->getShippingIsEnabled())
		{
			$showShipping = true;
		}
		$view->assign( 'showShipping', $showShipping );
		$view->assign('params', $this->params);
		$view->setLayout( 'checkout_register');

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
		$app->close();
	}

	function register_validate() {

		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$model = $this->getModel('checkout');
		$redirect_url = JRoute::_('index.php?option=com_j2store&view=checkout');
		$data = $app->input->getArray($_POST);
		$address_model = $this->getModel('address');
		$store_address = J2StoreHelperCart::getStoreAddress();
		require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/user.php');
		$userHelper = new J2StoreHelperUser;

		$json = array();

		// Validate if customer is already logged out.
		if ($user->id) {
			$json['redirect'] = $redirect_url;
		}

		// Validate cart has products and has stock.
		if (!J2StoreHelperCart::hasProducts()) {
			$json['redirect'] = $redirect_url;
		}

		if (!$json) {

			$selectableBase = J2StoreFactory::getSelectableBase();
			$json = $selectableBase->validate($data, 'register', 'address');
			//validate the password fields
			if ((JString::strlen($app->input->post->get('password')) < 4)) {
				$json['error']['password'] = JText::_('J2STORE_PASSWORD_REQUIRED');
			}

			if ($app->input->post->get('confirm') != $app->input->post->get('password')) {
				$json['error']['confirm'] = JText::_('J2STORE_PASSWORDS_DOESTNOT_MATCH');
			}

			//check email
			if($userHelper->emailExists($app->input->post->getString('email') )){
				$json['error']['email'] = JText::_('J2STORE_EMAIL_EXISTS');
			}

		}

		if (!$json) {
			$post = $app->input->getArray($_POST);

				//now create the user
				// create the details array with new user info
				$details = array(
						'email' =>  $app->input->getString('email'),
						'name' => $app->input->getString('first_name').' '.$app->input->getString('last_name'),
						'username' =>  $app->input->getString('email'),
						'password' => $app->input->getString('password'),
						'password2'=> $app->input->getString('confirm')
				);
				$msg = '';
				$user = $userHelper->createNewUser($details, $msg);

				$this->session->set('account', 'register', 'j2store');

				//now login the user
				if ( $userHelper->login(
							array('username' => $user->username, 'password' => $details['password'])
					)
				) {
					//$billing_address_id = $userHelper->addCustomer($post);
					$billing_address_id = $address_model->addAddress('billing');


					//check if we have a country and zone id's. If not use the store address
					$country_id = $app->input->post->getInt('country_id', '');
					if(empty($country_id)) {
						$country_id = $store_address->country_id;
					}

					$zone_id = $app->input->post->getInt('zone_id', '');
					if(empty($zone_id)) {
						$zone_id = $store_address->zone_id;
					}

					$postcode = $app->input->post->getString('zip');
					if(empty($postcode)) {
						$postcode = $store_address->store_zip;
					}

					$this->session->set('billing_address_id', $billing_address_id , 'j2store');
					$this->session->set('billing_country_id', $country_id, 'j2store');
					$this->session->set('billing_zone_id', $zone_id, 'j2store');

					$shipping_address = $app->input->post->get('shipping_address');
					if (!empty($shipping_address )) {
						$this->session->set('shipping_address_id', $billing_address_id, 'j2store');
						$this->session->set('shipping_country_id', $country_id, 'j2store');
						$this->session->set('shipping_zone_id', $zone_id, 'j2store');
						$this->session->set('shipping_postcode', $postcode, 'j2store');
					}
				} else {
					$json['redirect'] = $redirect_url;
				}

			$this->session->clear('guest', 'j2store');
			$this->session->clear('shipping_method', 'j2store');
			$this->session->clear('shipping_methods', 'j2store');
			$this->session->clear('payment_method', 'j2store');
			$this->session->clear('payment_methods', 'j2store');
		}
		echo json_encode($json);
		$app->close();
	}


	function guest() {
		$app = JFactory::getApplication();
		$cart_model = $this->getModel('mycart');
		$view = $this->getView( 'checkout', 'html' );
		$model = $this->getModel('checkout');
		$link = JRoute::_('index.php?option=com_j2store&view=mycart');

		$this->session->set('uaccount', 'guest', 'j2store');

		$products = $cart_model->getDataNew();
		try {
			J2StoreInventory::validateQuantityRestrictions($products);
		} catch (Exception $e) {
			$app->redirect($link, $e->getMessage());
		}

		//set guest varibale to session as the array, if it does not exist
		if(!$this->session->has('guest', 'j2store')) {
			$this->session->set('guest', array(), 'j2store');
		}
		$guest = $this->session->get('guest', array(), 'j2store');

		$data = array();

		$selectableBase = J2StoreFactory::getSelectableBase();
		$view->assign('fieldsClass', $selectableBase);

		$address = JTable::getInstance('address', 'Table');

		if (empty($guest['billing']['zip']) && $this->session->has('billing_postcode', 'j2store') ) {
			$guest['billing']['zip'] = $this->session->get('billing_postcode', '', 'j2store');
		}

		if (empty($guest['billing']['country_id']) && $this->session->has('billing_country_id', 'j2store')) {
			$guest['billing']['country_id'] = $this->session->get('billing_country_id', '', 'j2store');
		}

		if (empty($guest['billing']['zone_id']) && $this->session->has('billing_zone_id', 'j2store')) {
			$guest['billing']['zone_id'] = $this->session->get('billing_zone_id', '', 'j2store');
		}

		//bind the guest data to address table if it exists in the session

		if(isset($guest['billing']) && count($guest['billing'])) {
			$address->bind($guest['billing']);
		}

		$fields = $selectableBase->getFields('guest',$address,'address');
		$view->assign('fields', $fields);
		$view->assign('address', $address);

		//get layout settings
		$storeProfile = J2StoreHelperCart::getStoreAddress();
		$view->assign('storeProfile', J2StoreHelperCart::getStoreAddress());


		$showShipping = false;
		if($this->params->get('show_shipping_address', 0)) {
			$showShipping = true;
		}

		if ($isShippingEnabled = $cart_model->getShippingIsEnabled())
		{
			$showShipping = true;
		}
		$view->assign( 'showShipping', $showShipping );

		$data['shipping_required'] = $showShipping;

		if (isset($guest['shipping_address'])) {
			$data['shipping_address'] = $guest['shipping_address'];
		} else {
			$data['shipping_address'] = true;
		}
		$view->assign( 'data', $data);

		$view->setLayout( 'checkout_guest');

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
		$app->close();

	}

	function guest_validate() {

		$app = JFactory::getApplication();
		$cart_helper = new J2StoreHelperCart();
		$address_model = $this->getModel('address');
		$model = $this->getModel('checkout');
		$redirect_url = JRoute::_('index.php?option=com_j2store&view=checkout');
		$data = $app->input->getArray($_POST);
		$store_address = J2StoreHelperCart::getStoreAddress();
		//initialise guest value from session
		$guest = $this->session->get('guest', array(), 'j2store');

		$json = array();

		// Validate if customer is logged in.
		if (JFactory::getUser()->id) {
			$json['redirect'] = $redirect_url;
		}

		// Validate cart has products and has stock.
		if ((!$cart_helper->hasProducts())) {
			$json['redirect'] = $redirect_url;
		}

		// Check if guest checkout is avaliable.
		//TODO prevent if products have downloads also
		if (!$this->params->get('allow_guest_checkout')) {
			$json['redirect'] = $redirect_url;
		}

		if (!$json) {

			$selectableBase =J2StoreFactory::getSelectableBase();
			$json = $selectableBase->validate($data, 'guest', 'address');
		}

		if (!$json) {
			//now assign the post data to the guest billing array.
			foreach($data as $key=>$value) {
				$guest['billing'][$key] = $value;
			}

			//check if we have a country and zone id's. If not use the store address
			$country_id = $app->input->post->getInt('country_id', '');
			if(empty($country_id)) {
				$country_id = $store_address->country_id;
			}

			$zone_id = $app->input->post->getInt('zone_id', '');
			if(empty($zone_id)) {
				$zone_id = $store_address->zone_id;
			}

			$postcode = $app->input->post->get('zip');
			if(empty($postcode)) {
				$postcode = $store_address->store_zip;
			}

			///returns an object
			$country_info = $model->getCountryById($country_id);

			//save to address table before you proceed.
			$address_model->addAddress('billing', $guest['billing']);

			if ($country_info) {
				$guest['billing']['country_name'] = $country_info->country_name;
				$guest['billing']['iso_code_2'] = $country_info->country_isocode_2;
				$guest['billing']['iso_code_3'] = $country_info->country_isocode_3;
			} else {
				$guest['billing']['country_name'] = '';
				$guest['billing']['iso_code_2'] = '';
				$guest['billing']['iso_code_3'] = '';
			}

			$zone_info = $model->getZonesById($zone_id);

			if ($zone_info) {
				$guest['billing']['zone_name'] = $zone_info->zone_name;
				$guest['billing']['zone_code'] = $zone_info->zone_code;
			} else {
				$guest['billing']['zone_name'] = '';
				$guest['billing']['zone_code'] = '';
			}

			if ($app->input->getInt('shipping_address')) {
				$guest['shipping_address'] = true;
			} else {
				$guest['shipping_address'] = false;
			}

			// Default billing address
			$this->session->set('billing_country_id', $country_id, 'j2store');
			$this->session->set('billing_zone_id', $zone_id, 'j2store');


			if ($guest['shipping_address']) {

				foreach($data as $key=>$value) {
					$guest['shipping'][$key] = $value;
				}

				//save to address table before you proceed.
				$address_model->addAddress('shipping', $guest['shipping']);

				if ($country_info) {
					$guest['shipping']['country_name'] = $country_info->country_name;
					$guest['shipping']['iso_code_2'] = $country_info->country_isocode_2;
					$guest['shipping']['iso_code_3'] = $country_info->country_isocode_3;
				} else {
					$guest['shipping']['country_name'] = '';
					$guest['shipping']['iso_code_2'] = '';
					$guest['shipping']['iso_code_3'] = '';
				}

				if ($zone_info) {
					$guest['shipping']['zone_name'] = $zone_info->zone_name;
					$guest['shipping']['zone_code'] = $zone_info->zone_code;
				} else {
					$guest['shipping']['zone_name'] = '';
					$guest['shipping']['zone_code'] = '';
				}
				// Default Shipping Address
				$this->session->set('shipping_country_id', $country_id, 'j2store');
				$this->session->set('shipping_zone_id', $zone_id, 'j2store');
				$this->session->set('shipping_postcode', $postcode, 'j2store');
			}
			//now set the guest values to the session
			$this->session->set('guest', $guest, 'j2store');
			$this->session->set('account', 'guest', 'j2store');

			$this->session->clear('shipping_method', 'j2store');
			$this->session->clear('shipping_methods', 'j2store');
			$this->session->clear('payment_method', 'j2store');
			$this->session->clear('payment_methods', 'j2store');
		}
		echo json_encode($json);
		$app->close();

	}

	function guest_shipping() {

		$app = JFactory::getApplication();
		$cart_model = $this->getModel('mycart');
		$view = $this->getView( 'checkout', 'html' );
		$model = $this->getModel('checkout');
		$guest = $this->session->get('guest', array(), 'j2store');

		$data = array();

		$selectableBase = J2StoreFactory::getSelectableBase();
		$view->assign('fieldsClass', $selectableBase);

		$address = JTable::getInstance('address', 'Table');


		if (empty($guest['shipping']['zip']) && $this->session->has('shipping_postcode', 'j2store') ) {
			$guest['shipping']['zip'] = $this->session->get('shipping_postcode', '', 'j2store');
		}

		if (empty($guest['shipping']['country_id']) && $this->session->has('shipping_country_id', 'j2store')) {
			$guest['shipping']['country_id'] = $this->session->get('shipping_country_id', '', 'j2store');
		}

		if (empty($guest['shipping']['zone_id']) && $this->session->has('shipping_zone_id', 'j2store')) {
			$guest['shipping']['zone_id'] = $this->session->get('shipping_zone_id', '', 'j2store');
		}

		//bind the guest data to address table if it exists in the session

		if(isset($guest['shipping']) && count($guest['shipping'])) {
			$address->bind($guest['shipping']);
		}
		$fields = $selectableBase->getFields('guest_shipping',$address,'address');
		$view->assign('fields', $fields);
		$view->assign('address', $address);

		//get layout settings
		$storeProfile = J2StoreHelperCart::getStoreAddress();
		$view->assign('storeProfile', J2StoreHelperCart::getStoreAddress());

		$view->assign( 'data', $data);

		$view->setLayout( 'checkout_guest_shipping');

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
		$app->close();

	}

	function guest_shipping_validate() {
		$app = JFactory::getApplication();
		$cart_helper = new J2StoreHelperCart();
		$address_model = $this->getModel('address');
		$model = $this->getModel('checkout');
		$redirect_url = JRoute::_('index.php?option=com_j2store&view=checkout');
		$data = $app->input->getArray($_POST);
		$store_address = J2StoreHelperCart::getStoreAddress();
		//initialise guest value from session
		$guest = $this->session->get('guest', array(), 'j2store');
		$json = array();

		// Validate if customer is logged in.
		if (JFactory::getUser()->id) {
			$json['redirect'] = $redirect_url;
		}

		// Validate cart has products and has stock.
		if ((!$cart_helper->hasProducts())) {
			$json['redirect'] = $redirect_url;
		}

		// Check if guest checkout is avaliable.
		//TODO prevent if products have downloads also
		if (!$this->params->get('allow_guest_checkout')) {
			$json['redirect'] = $redirect_url;
		}

		if (!$json) {
			$selectableBase = J2StoreFactory::getSelectableBase();
			$json = $selectableBase->validate($data, 'guest_shipping', 'address');
		}

		if(!$json) {

			//now assign the post data to the guest billing array.
			foreach($data as $key=>$value) {
				$guest['shipping'][$key] = $value;
			}

			//check if we have a country and zone id's. If not use the store address
			$country_id = $app->input->post->getInt('country_id', '');
			if(empty($country_id)) {
				$country_id = $store_address->country_id;
			}

			$zone_id = $app->input->post->getInt('zone_id', '');
			if(empty($zone_id)) {
				$zone_id = $store_address->zone_id;
			}

			$postcode = $app->input->post->get('zip');
			if(empty($postcode)) {
				$postcode = $store_address->store_zip;
			}

			//save to address table before you proceed.
			$address_model->addAddress('shipping', $guest['shipping']);

			//now get the country info
			//returns an object
			$country_info = $model->getCountryById($country_id);


			if ($country_info) {
				$guest['shipping']['country_name'] = $country_info->country_name;
				$guest['shipping']['iso_code_2'] = $country_info->country_isocode_2;
				$guest['shipping']['iso_code_3'] = $country_info->country_isocode_3;
			} else {
				$guest['shipping']['country_name'] = '';
				$guest['shipping']['iso_code_2'] = '';
				$guest['shipping']['iso_code_3'] = '';
			}

			$zone_info = $model->getZonesById($zone_id);

			if ($zone_info) {
				$guest['shipping']['zone_name'] = $zone_info->zone_name;
				$guest['shipping']['zone_code'] = $zone_info->zone_code;
			} else {
				$guest['shipping']['zone_name'] = '';
				$guest['shipping']['zone_code'] = '';
			}
			// Default Shipping Address
			$this->session->set('shipping_country_id', $app->input->getInt('country_id'), 'j2store');
			$this->session->set('shipping_zone_id', $app->input->getInt('zone_id'), 'j2store');
			$this->session->set('shipping_postcode', $app->input->getString('zip'), 'j2store');

			//now set the guest values to the session
			$this->session->set('guest', $guest, 'j2store');

			$this->session->clear('shipping_method', 'j2store');
			$this->session->clear('shipping_methods', 'j2store');

		}
		echo json_encode($json);
		$app->close();
	}

	function billing_address() {

		$app = JFactory::getApplication();
		$address = $this->getModel('address')->getSingleAddressByUserID();
		$view = $this->getView( 'checkout', 'html' );
		$model = $this->getModel('checkout');
		$cart_model = $this->getModel('mycart');

		$link = JRoute::_('index.php?option=com_j2store&view=mycart');
		$products = $cart_model->getDataNew();
		try {
			J2StoreInventory::validateQuantityRestrictions($products);
		} catch (Exception $e) {
			$app->redirect($link, $e->getMessage());
		}

		//get the billing address id from the session
		if ($this->session->has('billing_address_id', 'j2store')) {
			$billing_address_id = $this->session->get('billing_address_id', '', 'j2store');
		} else {
			$billing_address_id = isset($address->id)?$address->id:'';
		}

		$view->assign('address_id', $billing_address_id);

		if ($this->session->has('billing_country_id', 'j2store')) {
			$billing_country_id = $this->session->get('billing_country_id', '', 'j2store');
		} else {
			$billing_country_id = isset($address->country_id)?$address->country_id:'';
		}

		if ($this->session->has('billing_zone_id', 'j2store')) {
			$billing_zone_id = $this->session->get('billing_zone_id', '', 'j2store');
		} else {
			$billing_zone_id = isset($address->zone_id)?$address->zone_id:'';
		}
		$view->assign('zone_id', $billing_zone_id);

		//get all address
		$addresses = $this->getModel('address')->getAddresses();
		$view->assign('addresses', $addresses);

		$selectableBase = J2StoreFactory::getSelectableBase();
		$view->assign('fieldsClass', $selectableBase);
		$address_table = JTable::getInstance('address', 'Table');
		$fields = $selectableBase->getFields('billing',$address,'address');
		$view->assign('fields', $fields);
		$view->assign('address', $address_table);

		//get layout settings
		$view->assign('storeProfile', J2StoreHelperCart::getStoreAddress());
		$view->setLayout( 'checkout_billing');

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
		$app->close();
	}

	//validate billing address

	function billing_address_validate() {

		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$address_model = $this->getModel('address');
		$redirect_url = JRoute::_('index.php?option=com_j2store&view=checkout');
		$data = $app->input->getArray($_POST);
		$json = array();
		$store_address = J2StoreHelperCart::getStoreAddress();

		$selectableBase = J2StoreFactory::getSelectableBase();

		$json = array();

		// Validate if customer is logged or not.
		if (!$user->id) {
			$json['redirect'] = $redirect_url;
		}

		// Validate cart has products and has stock.
		if (!J2StoreHelperCart::hasProducts()) {
			$json['redirect'] = $redirect_url;
		}



		//Has the customer selected an existing address?
		$selected_billing_address =$app->input->getString('billing_address');
		if (isset($selected_billing_address ) && $app->input->getString('billing_address') == 'existing') {
			$selected_address_id =	$app->input->getInt('address_id');
			if (empty($selected_address_id)) {
				$json['error']['warning'] = JText::_('J2STORE_ADDRESS_SELECTION_ERROR');
			} elseif (!in_array($app->input->getInt('address_id'), array_keys($address_model->getAddresses('id')))) {
				$json['error']['warning'] = JText::_('J2STORE_ADDRESS_SELECTION_ERROR');
			} else {
				// Default Payment Address
				$address_info = $address_model->getAddress($app->input->getInt('address_id'));
			}

			if (!$json) {
				$this->session->set('billing_address_id', $app->input->getInt('address_id'), 'j2store');

				if ($address_info) {
					$this->session->set('billing_country_id',$address_info['country_id'], 'j2store');
					$this->session->set('billing_zone_id',$address_info['zone_id'], 'j2store');
				} else {
					$this->session->clear('billing_country_id', 'j2store');
					$this->session->clear('billing_zone_id', 'j2store');
				}
				$this->session->clear('payment_method', 'j2store');
				$this->session->clear('payment_methods', 'j2store');
			}
		} else {

			if (!$json) {
				$json = $selectableBase->validate($data, 'billing', 'address');

				if(!$json) {
					$address_id = $address_model->addAddress('billing');
					//now get the address and save to session
					$address_info = $address_model->getAddress($address_id);

					//check if we have a country and zone id's. If not use the store address
					$country_id = $app->input->post->getInt('country_id', '');
					if(empty($country_id)) {
						$country_id = $store_address->country_id;
					}

					$zone_id = $app->input->post->getInt('zone_id', '');
					if(empty($zone_id)) {
						$zone_id = $store_address->zone_id;
					}
					$this->session->set('billing_address_id', $address_info['id'], 'j2store');
					$this->session->set('billing_country_id', $country_id, 'j2store');
					$this->session->set('billing_zone_id',$zone_id, 'j2store');
					$this->session->clear('payment_method', 'j2store');
					$this->session->clear('payment_methods', 'j2store');
				}

			}

		}
		echo json_encode($json);
		$app->close();

	}

	//shipping address

	function shipping_address() {

		$app = JFactory::getApplication();
		$address = $this->getModel('address')->getSingleAddressByUserID();
		$view = $this->getView( 'checkout', 'html' );
		$model = $this->getModel('checkout');

		//get the billing address id from the session
		if ($this->session->has('shipping_address_id', 'j2store')) {
			$shipping_address_id = $this->session->get('shipping_address_id', '', 'j2store');
		} else {
			$shipping_address_id = $address->id;
		}

		$view->assign('address_id', $shipping_address_id);

		if ($this->session->has('shipping_postcode', 'j2store')) {
			$shipping_postcode = $this->session->get('shipping_postcode', '', 'j2store');
		} else {
			$shipping_postcode = $address->zip;
		}

		if ($this->session->has('shipping_country_id', 'j2store')) {
			$shipping_country_id = $this->session->get('shipping_country_id', '', 'j2store');
		} else {
			$shipping_country_id = $address->country_id;
		}

		if ($this->session->has('shipping_zone_id', 'j2store')) {
			$shipping_zone_id = $this->session->get('shipping_zone_id', '', 'j2store');
		} else {
			$shipping_zone_id = $address->zone_id;
		}
		$view->assign('zone_id', $shipping_zone_id);

		//get all address
		$addresses = $this->getModel('address')->getAddresses();
		$view->assign('addresses', $addresses);

		$selectableBase = J2StoreFactory::getSelectableBase();
		$view->assign('fieldsClass', $selectableBase);
		$address_table = JTable::getInstance('address', 'Table');
		$fields = $selectableBase->getFields('shipping',$address,'address');
		$view->assign('fields', $fields);
		$view->assign('address', $address_table);

		//get layout settings
		$view->assign('storeProfile', J2StoreHelperCart::getStoreAddress());


		$view->setLayout( 'checkout_shipping');

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
		$app->close();
	}

function shipping_address_validate() {

	$app = JFactory::getApplication();
	$user = JFactory::getUser();
	$address_model = $this->getModel('address');
	$redirect_url = JRoute::_('index.php?option=com_j2store&view=checkout');
	$cart_model = $this->getModel('mycart');
	$data = $app->input->getArray($_POST);
	$json = array();
	$store_address = J2StoreHelperCart::getStoreAddress();

	$selectableBase = J2StoreFactory::getSelectableBase();

	// Validate if customer is logged or not.
	if (!$user->id) {
		$json['redirect'] = $redirect_url;
	}
	// Validate if shipping is required. If not the customer should not have reached this page.
	$showShipping = false;

	if($this->params->get('show_shipping_address', 0)) {
		$showShipping = true;
	}

	if ($isShippingEnabled = $cart_model->getShippingIsEnabled())
	{
		$showShipping = true;
	}


	if ($showShipping == false) {
		$json['redirect'] = $redirect_url;
	}

	// Validate cart has products and has stock.
	if (!J2StoreHelperCart::hasProducts()) {

		$json['redirect'] = $redirect_url;
	}
	// TODO Validate minimum quantity requirments.

	//Has the customer selected an existing address?
	$selected_shipping_address =$app->input->getString('shipping_address');
	if (isset($selected_shipping_address ) && $app->input->getString('shipping_address') == 'existing') {
		$selected_address_id =	$app->input->getInt('address_id');
		if (empty($selected_address_id)) {
			$json['error']['warning'] = JText::_('J2STORE_ADDRESS_SELECTION_ERROR');
		} elseif (!in_array($app->input->getInt('address_id'), array_keys($address_model->getAddresses('id')))) {
			$json['error']['warning'] = JText::_('J2STORE_ADDRESS_SELECTION_ERROR');
		} else {
			// Default shipping Address. returns associative list of single record
			$address_info = $address_model->getAddress($app->input->getInt('address_id'));
		}

		if (!$json) {
			$this->session->set('shipping_address_id', $app->input->getInt('address_id'), 'j2store');

			if ($address_info) {
				$this->session->set('shipping_country_id',$address_info['country_id'], 'j2store');
				$this->session->set('shipping_zone_id',$address_info['zone_id'], 'j2store');
				$this->session->set('shipping_postcode',$address_info['zip'], 'j2store');
			} else {
				$this->session->clear('shipping_country_id', 'j2store');
				$this->session->clear('shipping_zone_id', 'j2store');
				$this->session->clear('shipping_postcode', 'j2store');
			}
			$this->session->clear('shipping_method', 'j2store');
			$this->session->clear('shipping_methods', 'j2store');
		}
	} else {
		if (!$json) {
			$json = $selectableBase->validate($data, 'shipping', 'address');

			if(!$json) {

				$address_id = $address_model->addAddress('shipping');
				//now get the address and save to session
				$address_info = $address_model->getAddress($address_id);

				//check if we have a country and zone id's. If not use the store address
				$country_id = $app->input->post->getInt('country_id', '');
				if(empty($country_id)) {
					$country_id = $store_address->country_id;
				}

				$zone_id = $app->input->post->getInt('zone_id', '');
				if(empty($zone_id)) {
					$zone_id = $store_address->zone_id;
				}

				$postcode= $app->input->post->get('zip');
				if(empty($postcode)) {
					$postcode = $store_address->zip;
				}

				$this->session->set('shipping_address_id', $address_info['id'], 'j2store');
				$this->session->set('shipping_country_id',$country_id, 'j2store');
				$this->session->set('shipping_zone_id',$zone_id, 'j2store');
				$this->session->set('shipping_postcode',$postcode, 'j2store');
				$this->session->clear('shipping_method', 'j2store');
				$this->session->clear('shipping_methods', 'j2store');
			}

		}

	}

	echo json_encode($json);
	$app->close();
}

//shipping and payment method
//TODO:: after developing shipping options, divide this function into two

	function shipping_payment_method() {
		$app = JFactory::getApplication();
		$view = $this->getView( 'checkout', 'html' );
		$task = JRequest::getVar('task');
		$model		= $this->getModel('checkout');
		$cart_helper = new J2StoreHelperCart();
		$cart_model = $this->getModel('mycart');

		if (!$cart_helper->hasProducts())
		{
			$msg = JText::_('J2STORE_NO_ITEMS_IN_CART');
			$link = JRoute::_('index.php?option=com_j2store&view=mycart');
			$app->redirect($link, $msg);
		}

		//prepare order
		$order= $this->_order;
		$order = $this->populateOrder(false);
		// get the order totals
		$order->calculateTotals();
//print_r($order->order_discount);
//print_r($order->order_tax);


		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin ('j2store');

		//custom fields
		$selectableBase = J2StoreFactory::getSelectableBase();
		$view->assign('fieldsClass', $selectableBase);
		$address_table = JTable::getInstance('address', 'Table');
		$fields = $selectableBase->getFields('payment',$address,'address');
		$view->assign('fields', $fields);
		$view->assign('address', $address_table);

		//get layout settings
		$view->assign('storeProfile', J2StoreHelperCart::getStoreAddress());

		//shipping
		$showShipping = false;

		if($this->params->get('show_shipping_address', 0)) {
			$showShipping = true;
		}

		if ($isShippingEnabled = $cart_model->getShippingIsEnabled())
		{
			$showShipping = true;
			//$this->setShippingMethod();
		}
		$view->assign( 'showShipping', $showShipping );

		if($showShipping)
		{
			$rates = $this->getShippingRates();

			$shipping_layout = "shipping_yes";
		//	if (!$this->session->has('shipping_address_id', 'j2store'))
		//	{
		//		$shipping_layout = "shipping_calculate";
		//	}

			$shipping_method_form = $this->getShippingHtml( $shipping_layout );
			$view->assign( 'showShipping', $showShipping );
			$view->assign( 'shipping_method_form', $shipping_method_form );

			$view->assign( 'rates', $rates );
		}


		//process payment plugins
		$showPayment = true;
		if ((float)$order->order_total == (float)'0.00')
		{
			$showPayment = false;
		}
		$view->assign( 'showPayment', $showPayment );

		require_once (JPATH_SITE.'/components/com_j2store/helpers/plugin.php');
		$payment_plugins = J2StoreHelperPlugin::getPluginsWithEvent( 'onJ2StoreGetPaymentPlugins' );



		$plugins = array();
		if ($payment_plugins)
		{
			foreach ($payment_plugins as $plugin)
			{
				$results = $dispatcher->trigger( "onJ2StoreGetPaymentOptions", array( $plugin->element, $order ) );
				if (in_array(true, $results, true))
				{
					$plugins[] = $plugin;
				}
			}
		}

		if (count($plugins) == 1)
		{
			$plugins[0]->checked = true;
			//ob_start();
			$html = $this->getPaymentForm( $plugins[0]->element, true);
			//$html = json_decode( ob_get_contents() );
			//ob_end_clean();
			$view->assign( 'payment_form_div', $html);
		}

		//print_r($plugins);
		$view->assign('plugins', $plugins);
		//also set the payment methods to session



		//terms and conditions
		if( $this->params->get('termsid') ){
			$tos_link = JRoute::_('index.php?option=com_content&view=article&tmpl=component&id='.$this->params->get('termsid'));
		}else{
			$tos_link=null;
		}

		$view->assign( 'tos_link', $tos_link);

		//Get and Set Model
		$view->setModel( $model, true );
		$view->assign( 'order', $order );
		$view->assign('params', $this->params);
		$view->setLayout( 'checkout_shipping_payment');
		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
		$app->close();

	}

	function shipping_payment_method_validate() {

		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$model		= $this->getModel('checkout');
		$cart_helper = new J2StoreHelperCart();
		$cart_model = $this->getModel('mycart');
		$address_model = $this->getModel('address');
		$redirect_url = JRoute::_('index.php?option=com_j2store&view=checkout');
		//now get the values posted by the plugin, if any
		$values = $app->input->getArray($_POST);
		$json = array();

		//first validate custom fields
		$selectableBase = J2StoreFactory::getSelectableBase();
		$json = $selectableBase->validate($values, 'payment', 'address');

		if (!$json) {
			//validate weather the customer is logged in
			$billing_address = '';
			if ($user->id && $this->session->has('billing_address_id', 'j2store')) {
				$billing_address = $address_model->getAddress($this->session->get('billing_address_id', '', 'j2store'));
			} elseif ($this->session->has('guest', 'j2store')) {
				$guest = $this->session->get('guest', array(), 'j2store');
				$billing_address = $guest['billing'];
			}

			if (empty($billing_address)) {
				$json['redirect'] = $redirect_url;
			}

			//cart has products?
			if(!$cart_helper->hasProducts()) {
				$json['redirect'] = $redirect_url;
			}

			if (!$json) {

				$isShippingEnabled = $cart_model->getShippingIsEnabled();
				//validate selection of shipping methods and set the shipping rates
				if($this->params->get('show_shipping_address', 0) || $isShippingEnabled ) {
					//shipping is required.

					if ($user->id && $this->session->has('shipping_address_id', 'j2store')) {
						$shipping_address = $address_model->getAddress($this->session->get('shipping_address_id', '', 'j2store'));
					} elseif ($this->session->has('guest', 'j2store')) {
						$guest = $this->session->get('guest', array(), 'j2store');
						$shipping_address = $guest['shipping'];
					}

					//check if shipping address id is set in session. If not, redirect
					if(empty($shipping_address)) {
						$json['error']['shipping'] = JText::_('J2STORE_CHECKOUT_ERROR_SHIPPING_ADDRESS_NOT_FOUND');
						$json['redirect'] = $redirect_url;
					}

					try {
						$this->validateSelectShipping($values);
					} catch (Exception $e) {
						$json['error']['shipping'] = $e->getMessage();
					}

					if(!$json) {


							$shipping_values = array();
							$shipping_values['shipping_price']    = isset($values['shipping_price']) ? $values['shipping_price'] : 0;
							$shipping_values['shipping_extra']   = isset($values['shipping_extra']) ? $values['shipping_extra'] : 0;
							$shipping_values['shipping_code']     = isset($values['shipping_code']) ? $values['shipping_code'] : '';
							$shipping_values['shipping_name']     = isset($values['shipping_name']) ? $values['shipping_name'] : '';
							$shipping_values['shipping_tax']      = isset($values['shipping_tax']) ? $values['shipping_tax'] : 0;
							$shipping_values['shipping_plugin']     = isset($values['shipping_plugin']) ? $values['shipping_plugin'] : '';
							//set the shipping method to session
							$this->session->set('shipping_method',$shipping_values['shipping_plugin'], 'j2store');
							$this->session->set('shipping_values',$shipping_values, 'j2store');


					}

				}

			}

			//validate selection of payment methods
			if (!$json) {

				//payment validation had to be done only when the order value is greater than zero
				//prepare order
				$order= $this->_order;
				$order = $this->populateOrder(false);
				// get the order totals
				$order->calculateTotals();
				$showPayment = true;
				if ((float)$order->order_total == (float)'0.00')
				{
					$showPayment = false;
				}



				if($showPayment) {
					$payment_plugin = $app->input->getString('payment_plugin');
					if (!isset($payment_plugin)) {
						$json['error']['warning'] = JText::_('J2STORE_CHECKOUT_ERROR_PAYMENT_METHOD');
					} elseif (!isset($payment_plugin )) {
						$json['error']['warning'] = JText::_('J2STORE_CHECKOUT_ERROR_PAYMENT_METHOD');
					}
					//validate the selected payment
					try {
						$this->validateSelectPayment($payment_plugin, $values);
					} catch (Exception $e) {
						$json['error']['warning'] = $e->getMessage();
					}

				}

				if($this->params->get('show_terms', 0) && $this->params->get('terms_display_type', 'link') =='checkbox' ) {
					$tos_check = $app->input->get('tos_check');
					if (!isset($tos_check)) {
						$json['error']['warning'] = JText::_('J2STORE_CHECKOUT_ERROR_AGREE_TERMS');
					}
				}

				if (!$json) {

					$payment_plugin = $app->input->getString('payment_plugin');
					//set the payment plugin form values in the session as well.
					$this->session->set('payment_values', $values, 'j2store');
					$this->session->set('payment_method', $payment_plugin, 'j2store');
					$this->session->set('customer_note', strip_tags($app->input->getString('customer_note')), 'j2store');
				}
			}
		}
		echo json_encode($json);
		$app->close();
	}

	function confirm() {

		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$db = JFactory::getDbo();
		$dispatcher    = JDispatcher::getInstance();
		JPluginHelper::importPlugin ('j2store');
		$view = $this->getView( 'checkout', 'html' );
		$model		= $this->getModel('checkout');
		$cart_helper = new J2StoreHelperCart();
		$cart_model = $this->getModel('mycart');
		$address_model = $this->getModel('address');
		$orders_model = $this->getModel('orders');
		$redirect_url = JRoute::_('index.php?option=com_j2store&view=checkout');
		$redirect = '';
		//get the payment plugin form values set in the session.
		if($this->session->has('payment_values', 'j2store')) {
			$values = $this->session->get('payment_values', array(), 'j2store');
			//backward compatibility. TODO: change the way the plugin gets its data
			foreach($values as $name=>$value) {
				$app->input->set($name, $value);
			}
		}
		//prepare order
		$order= $this->_order;
		$order = $this->populateOrder(false);
		// get the order totals
		$order->calculateTotals();

		//set shiping address
		if($user->id && $this->session->has('shipping_address_id', 'j2store')) {
			$shipping_address = $address_model->getAddress($this->session->get('shipping_address_id', '', 'j2store'));
		} elseif($this->session->has('guest', 'j2store')) {
			$guest = $this->session->get('guest', array(), 'j2store');
			if($guest['shipping']) {
				$shipping_address = $guest['shipping'];
			}

		}else{
			$shipping_address = array();
		}

		//validate shipping
		$showShipping = false;
		if ($isShippingEnabled = $cart_model->getShippingIsEnabled())
		{
			if (empty($shipping_address)) {
				$redirect = $redirect_url;
			}
			$showShipping = true;
			if($this->session->has('shipping_values', 'j2store')) {

				//set the shipping methods
				$shipping_values = $this->session->get('shipping_values', array(), 'j2store');
				$this->setShippingMethod($shipping_values);

			}

		}else {
			$this->session->clear('shipping_method', 'j2store');
			$this->session->clear('shipping_values', 'j2store');
		}
		$view->assign( 'showShipping', $showShipping );

		//process payment plugins
		$showPayment = true;
		if ((float)$order->order_total == (float)'0.00')
		{
			$showPayment = false;
		}
		$view->assign( 'showPayment', $showPayment );

		// Validate if billing address has been set.

		if ($user->id && $this->session->has('billing_address_id', 'j2store')) {
			$billing_address = $address_model->getAddress($this->session->get('billing_address_id', '', 'j2store'));
		} elseif ($this->session->has('guest', 'j2store')) {
			$guest = $this->session->get('guest', array(), 'j2store');
			$billing_address = $guest['billing'];
		}

		if (empty($billing_address)) {
			$redirect = $redirect_url;
		}

		// Validate if payment method has been set.
		if ($showPayment == true && !$this->session->has('payment_method', 'j2store')) {
			$redirect = $redirect_url;

			If(!$this->validateSelectPayment($this->session->get('payment_method', '', 'j2store'), $values)) {
				$redirect = $redirect_url;
			}

		}


		// Validate cart has products and has stock.
		if (!$cart_helper->hasProducts()) {
			$redirect = $redirect_url;
		}
		//minimum order value check
		if(!$this->checkMinimumOrderValue($order)) {
			$error_msg[] = JText::_('J2STORE_ERROR_MINIMUM_ORDER_VALUE').J2StorePrices::number($this->params->get('global_minordervalue'));
			$redirect = $redirect_url;
		}

		if(!$redirect) {

			$order_id = time();

			//generate invoice number
			$invoice = $orders_model->createInvoiceNumber($order_id);
			$values['order_id'] = $order_id;
			$order->invoice_number = $invoice->number;
			$order->invoice_prefix = $invoice->prefix;

			$user_email = ($user->id)?$user->email:$billing_address['email'];
			$values['user_email']=$user_email;

			// Save the orderitems with  status
			if (!$this->saveOrderItems($values))
			{	// Output error message and halt
				$error_msg[] = $this->getError();
			}

			// Save the orderfiles
			if (!$this->saveOrderFiles($values))
			{
				$error_msg[] = $this->getError();
			}

			$orderpayment_type = $this->session->get('payment_method', '', 'j2store');
			//trigger onK2StoreBeforePayment event
			if ($showPayment == true && !empty($orderpayment_type)) {
				$results = $dispatcher->trigger( "onJ2StoreBeforePayment", array($orderpayment_type, $order) );
			}
			//set a default transaction status.
			$transaction_status = JText::_( "J2STORE_TRANSACTION_INCOMPLETE" );

			// in the case of orders with a value of 0.00, use custom values
			if ( (float) $order->order_total == (float)'0.00' )
			{
				$orderpayment_type = 'free';
				$transaction_status = JText::_( "J2STORE_TRANSACTION_COMPLETE" );
			}

			//set order values
			$order->user_id = $user->id;
			$order->ip_address = $_SERVER['REMOTE_ADDR'];

			//generate a unique hash
			$order->token = JApplication::getHash($order_id);
			//user email
			$user_email = ($user->id)?$user->email:$billing_address['email'];
			$order->user_email = $user_email;

			//get the customer note
			$customer_note = $this->session->get('customer_note', '', 'j2store');
			$order->customer_note = $customer_note;
			$order->customer_language = $lang->getTag();
			$order->customer_group = implode(',',$user->getAuthorisedGroups());

			// Save an order with an Incomplete status
			$order->order_id = $order_id;
			$order->orderpayment_type = $orderpayment_type; // this is the payment plugin selected
			$order->transaction_status = $transaction_status; // payment plugin updates this field onPostPayment
			$order->order_state_id = 5; // default incomplete order state
			$order->orderpayment_amount = $order->order_total; // this is the expected payment amount.  payment plugin should verify actual payment amount against expected payment amount

			//get currency id, value and code and store it
			$currency = J2StoreFactory::getCurrencyObject();
			$order->currency_id = $currency->getId();
			$order->currency_code = $currency->getCode();
			$order->currency_value = $currency->getValue($currency->getCode());

			//save whether to show shipping address or not
			if($showShipping) {
				$order->is_shippable = 1;
			}else {
				$order->is_shippable = 0;
			}

			if ($order->save())
			{
				//set values for orderinfo table

				// send the order_id and orderpayment_id to the payment plugin so it knows which DB record to update upon successful payment
				$values["order_id"]             = $order->order_id;
				//$values["orderinfo"]            = $order->orderinfo;
				$values["orderpayment_id"]      = $order->id;
				$values["orderpayment_amount"]  = $order->orderpayment_amount;

				// Save the orderitems with  status
				if (!$this->saveOrderTax($values))
				{	// Output error message and halt
				$error_msg[] = $this->getError();
				}


				if($billing_address) {

					//dump all billing fields as json as it may contain custom field values as well
					$uset_account_type = '';
					if ($this->session->has('uaccount', 'j2store')) {
						$uset_account_type = $this->session->get('uaccount', 'billing', 'j2store');
					}
					if($uset_account_type == 'register' ) {
						$type= 'register';
					}elseif($uset_account_type == 'guest' ) {
						$type= 'guest';
					}elseif($uset_account_type == 'login' ) {
						$type= 'billing';
					}else {
						$type= 'billing';
					}
					$values['orderinfo']['all_billing']= $db->escape($this->processCustomFields($type, $billing_address));


					foreach ($billing_address as $key=>$value) {
						$values['orderinfo']['billing_'.$key] = $value;
						//legacy compatability for payment plugins
						$values['orderinfo'][$key] = $value;
					}
					$values['orderinfo']['country'] = $billing_address['country_name'];
					$values['orderinfo']['state'] = $billing_address['zone_name'];
				}

				if(isset($shipping_address) && is_array($shipping_address)) {

					//dump all shipping fields as json as it may contain custom field values as well
					if($uset_account_type == 'guest' ) {
						$type= 'guest_shipping';
					}else {
						$type= 'shipping';
					}

					$values['orderinfo']['all_shipping']= $db->escape($this->processCustomFields($type, $shipping_address));

					foreach ($shipping_address as $key=>$value) {
						$values['orderinfo']['shipping_'.$key] = $value;
					}
				}

				//now dump all payment_values as well. Because we may have custom fields there to
				if($this->session->has('payment_values', 'j2store')) {
					$pay_values = $this->session->get('payment_values', array(), 'j2store');
					$values['orderinfo']['all_payment']= $db->escape($this->processCustomFields('payment', $pay_values));
				}

				$values['orderinfo']['user_email'] = $user_email;
				$values['orderinfo']['user_id'] = $user->id;
				$values['orderinfo']['order_id'] = $order->order_id;
				$values['orderinfo']['orderpayment_id'] = $order->id;

				try {
					$this->saveOrderInfo($values['orderinfo']);
				} catch (Exception $e) {
					$redirect = $redirect_url;
					echo $e->getMessage()."\n";
				}


				//save shipping info
				if ( isset( $order->shipping ) && !$this->saveOrderShippings( $shipping_values ))
				{
					// TODO What to do if saving order shippings fails?
					$error = true;
				}


			} else {
				// Output error message and halt
				JError::raiseNotice( 'J2STORE_ERROR_SAVING_ORDER', $order->getError() );
				$redirect = $redirect_url;
			}
			// IMPORTANT: Store the order_id in the user's session for the postPayment "View Invoice" link

			$app->setUserState( 'j2store.order_id', $order->order_id );
			$app->setUserState( 'j2store.orderpayment_id', $order->id );
			$app->setUserState( 'j2store.order_token', $order->token);
			// in the case of orders with a value of 0.00, we redirect to the confirmPayment page
			if ( (float) $order->order_total == (float)'0.00' )
			{
				$free_redirect = JRoute::_( 'index.php?option=com_j2store&view=checkout&task=confirmPayment' );
				$view->assign('free_redirect', $free_redirect);
			}

			$payment_plugin = $this->session->get('payment_method', '', 'j2store');
			$values['payment_plugin'] =$payment_plugin;
			$results = $dispatcher->trigger( "onJ2StorePrePayment", array( $payment_plugin, $values ) );

			// Display whatever comes back from Payment Plugin for the onPrePayment
			$html = "";
			for ($i=0; $i<count($results); $i++)
			{
			$html .= $results[$i];
			}

			//check if plugins set a redirect
			if($this->session->has('plugin_redirect', 'j2store') ) {
					$redirect = $this->session->get('plugin_redirect', '', 'j2store');
			}

			$view->assign('plugin_html', $html);

			$summary = $this->getOrderSummary();
			$view->assign('orderSummary', $summary);

		}
			// Set display
			$view->setLayout('checkout_confirm');
			$view->set( '_doTask', true);
			$view->assign('order', $order);
			$view->assign('redirect', $redirect);
			$view->setModel( $model, true );
			ob_start();
			$view->display();
			$html = ob_get_contents();
			ob_end_clean();
			echo $html;
			$app->close();
	}

	public function processCustomFields($type, $data) {
		$selectableBase = J2StoreFactory::getSelectableBase();
		$address = JTable::getInstance('address', 'Table');
		$orderinfo = JTable::getInstance('Orderinfo', 'Table');
		$fields = $selectableBase->getFields($type,$address,'address');
		$values = array();
		foreach ($fields as $fieldName => $oneExtraField) {
			if(isset($data[$fieldName])) {
				if(!property_exists($orderinfo, $type.'_'.$fieldName) && !property_exists($orderinfo, 'user_'.$fieldName ) && $fieldName !='country_id' && $fieldName != 'zone_id' && $fieldName != 'option' && $fieldName !='task' && $fieldName != 'view' ) {
					$values[$fieldName]['label'] =$oneExtraField->field_name;
					$values[$fieldName]['value'] = $data[$fieldName];
				}
			}
		}
		$registry = new JRegistry();
		$registry->loadArray($values);
		$json = $registry->toString('JSON');
		return $json;

	}

	public function ajaxGetZoneList() {

		$app = JFactory::getApplication();
		$model = $this->getModel('checkout');
		$post = JRequest::get('post');
		$country_id = $post['country_id'];
		$zone_id = $post['zone_id'];
		$name=$post['field_name'];;
		$id=$post['field_id'];
		if($country_id) {
			$zones = $model->getZoneList($name,$id,$country_id,$zone_id);
			echo $zones;
		}
		$app->close();
	}

	function getOrderSummary()
	{
		// get the order object
		$order= $this->_order;
		$model = $this->getModel('mycart');
		$view = $this->getView( 'checkout', 'html' );
		$view->set( '_controller', 'checkout' );
		$view->set( '_view', 'checkout' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', true);
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );

		$show_tax = $this->params->get('show_tax_total');
		$view->assign( 'show_tax', $this->params->get('show_tax_total'));
		$view->assign( 'params', $this->params);
		$view->assign( 'order', $order );

		$orderitems = $order->getItems();
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

        //get order taxes
		$ordertaxes = $order->getOrderTax();

		// Checking whether shipping is required
		$showShipping = false;

		if ($isShippingEnabled = $model->getShippingIsEnabled())
		{
			$showShipping = true;
			$view->assign( 'shipping_total', $order->getShippingTotal() );
		}
		$view->assign( 'showShipping', $showShipping );

		$view->assign( 'ordertaxes', $ordertaxes);
		$view->assign( 'orderitems', $orderitems );
		$view->setLayout( 'cartsummary' );

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	function populateOrder($guest = false)
	{
		$order= $this->_order;
		//$order->shipping_method_id = $this->defaultShippingMethod;
		$items = J2StoreHelperCart::getProducts();
		foreach ($items as $item)
		{
			$order->addItem( $item );
		}
		// get the order totals
		$order->calculateTotals();

		return $order;
	}


	function checkMinimumOrderValue($order) {



		$min_value = $this->params->get('global_minordervalue');
		if(!empty($min_value)) {
			if($order->order_subtotal >= $min_value) {
			 return true;
			} else {
			 return false;
			}
		} else {
			return true;
		}
	}


	//hipping method set

	/**
	 * Sets the selected shipping method
	 *
	 * @return unknown_type
	 */
	function setShippingMethod($values)
	{

		$app = JFactory::getApplication();
		// get the order object so we can populate it
		$order = $this->_order; // a TableOrders object (see constructor)

		// set the shipping method
		$order->shipping = new JObject();
		$order->shipping->shipping_price      = $values['shipping_price'];
		$order->shipping->shipping_extra      = $values['shipping_extra'];
		$order->shipping->shipping_code      = $values['shipping_code'];
		$order->shipping->shipping_name       = $values['shipping_name'];
		$order->shipping->shipping_tax        = $values['shipping_tax'];
		$order->shipping->shipping_type		  = $values['shipping_plugin'];

		// get the order totals
		$order->calculateTotals();

		return;
	}




	function getShippingHtml( $layout='shipping_yes' )
	{
		$order= $this->_order;

		$html = '';
		$model = $this->getModel( 'Checkout', 'J2StoreModel' );
		$view   = $this->getView( 'checkout', 'html' );
		$view->set( '_controller', 'checkout' );
		$view->set( '_view', 'checkout' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', true);
		$view->setModel( $model, true );
		$view->setLayout( $layout );
		$rates = array();

	 switch (strtolower($layout))
        {
            case "shipping_calculate":
                break;
            case "shipping_no":
                break;
            case "shipping_yes":
            default:
                $rates = $this->getShippingRates();
                $default_rate = array();

                if (count($rates) == 1)
                {
                    $default_rate = $rates[0];
                }
                $view->assign( 'rates', $rates );
                $view->assign( 'default_rate', $default_rate );
                break;
        }

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	/**
	 * Gets the applicable rates
	 *
	 * @return array
	 */
	public function getShippingRates()
	{
		static $rates;

		if (empty($rates) || !is_array($rates))
		{
			$rates = array();
		}

		if (!empty($rates))
		{
			return $rates;
		}
		require_once (JPATH_SITE.'/components/com_j2store/helpers/plugin.php');
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/models');
		$model = JModelLegacy::getInstance('Shipping', 'J2StoreModel');
		$model->setState('filter_enabled', '1');
		$plugins = $model->getList();

		$dispatcher = JDispatcher::getInstance();

		$rates = array();

		// add taxes, even thought they aren't displayed
		$order_tax = 0;
		$orderitems = $this->_order->getItems();
		foreach( $orderitems as $item )
		{
			$this->_order->order_subtotal += $item->orderitem_tax;
			$order_tax += $item->orderitem_tax;
		}

		if ($plugins)
		{
			foreach ($plugins as $plugin)
			{

				$shippingOptions = $dispatcher->trigger( "onJ2StoreGetShippingOptions", array( $plugin->element, $this->_order ) );

				if (in_array(true, $shippingOptions, true))
				{
					$results = $dispatcher->trigger( "onJ2StoreGetShippingRates", array( $plugin->element, $this->_order ) );

					foreach ($results as $result)
					{
						if(is_array($result))
						{
							foreach( $result as $r )
							{
								$extra = 0;
								// here is where a global handling rate would be added
							//	if ($global_handling = $this->defines->get( 'global_handling' ))
							//	{
							//		$extra = $global_handling;
							//	}
								$r['extra'] += $extra;
								$r['total'] += $extra;
								$rates[] = $r;
							}
						}
					}
				}
			}
		}

		$this->_order->order_subtotal -= $order_tax;

		return $rates;
	}


	function getPaymentForm($element='', $plain_format=false)
	{
		$app = JFactory::getApplication();
		$values = JRequest::get('post');
		$html = '';
		$text = "";
		$user = JFactory::getUser();
		if (empty($element)) {
			$element = JRequest::getVar( 'payment_element' );
		}
		$results = array();
		$dispatcher    = JDispatcher::getInstance();
		JPluginHelper::importPlugin ('j2store');

		$results = $dispatcher->trigger( "onJ2StoreGetPaymentForm", array( $element, $values ) );
		for ($i=0; $i<count($results); $i++)
		{
			$result = $results[$i];
			$text .= $result;
		}

		$html = $text;
		if($plain_format) {
			return $html;
		} else {

		// set response array
		$response = array();
		$response['msg'] = $html;

		// encode and echo (need to echo to send back to browser)
		echo json_encode($response);
		$app->close();
		}
		//return;
	}

	/**
	 * Saves each individual item in the order to the DB
	 *
	 * @return unknown_type
	 */
	function saveOrderItems($values)
	{
		$order= $this->_order;
		$order_id = $values['order_id'];
		//review things once again
		$cart_helper = new J2StoreHelperCart();
		$cart_model = $this->getModel('mycart');
//		$reviewitems = $cart_helper->getProductsInfo();

	//	foreach ($reviewitems as $reviewitem)
	//	{
	//		$order->addItem( $reviewitem );
	//	}

		$order->order_state_id = $this->initial_order_state;
		$order->calculateTotals();


		$items = $order->getItems();
		//print_r($items ); exit;
		if (empty($items) || !is_array($items))
		{
			$this->setError( "saveOrderItems:: ".JText::_( "J2STORE_ORDER_SAVE_INVALID_ITEMS" ) );
			return false;
		}

		$error = false;
		$errorMsg = "";
		foreach ($items as $item)
		{
			$item->order_id = $order_id;
			$attributes = '';
			$attributes =$item->orderitem_attributes;
			if (!$item->save())
			{
				// track error
				$error = true;
				$errorMsg .= $item->getError();
			}
			else
			{
				// Save the attributes also
				if (!empty($attributes))
				{
					//$attributes = explode(',', $item->orderitem_attributes);
					//first we got to convert the JSON-structured attribute options into an object
					$registry = new JRegistry;
					$registry->loadString(stripslashes($attributes), 'JSON');
					$product_options = $registry->toObject();

					foreach ($product_options as $attribute)
					{
						unset($productattribute);
						unset($orderitemattribute);
						//we first have to load the product options table to get the data. Just for a cross check
						//TODO do we need this? the mycart model already has the data and we mapped it to orderitem_attributes in JSON format.
						//$productattribute = $cart_model->getCartProductOptions($attribute->product_option_id, $item->product_id);
						$orderitemattribute = JTable::getInstance('OrderItemAttributes', 'Table');
						$orderitemattribute->orderitem_id = $item->orderitem_id;
						//this is the product option id
						$orderitemattribute->productattributeoption_id = $attribute->product_option_id;
						$orderitemattribute->productattributeoptionvalue_id = $attribute->product_optionvalue_id;
						//product option name. Dont confuse this with the option value name
						$orderitemattribute->orderitemattribute_name = $attribute->name;
						$orderitemattribute->orderitemattribute_value = $attribute->option_value;
						//option price
						$orderitemattribute->orderitemattribute_price = $attribute->price;
						//$orderitemattribute->orderitemattribute_code = $productattribute->productattributeoption_code;
						$orderitemattribute->orderitemattribute_prefix = $attribute->price_prefix;
						$orderitemattribute->orderitemattribute_type = $attribute->type;
						$orderitemattribute->orderitemattribute_code = $attribute->option_sku;
						if (!$orderitemattribute->save())
						{
							// track error
							$error = true;
							$errorMsg .= $orderitemattribute->getError();
						}

					}
				}
			}
		}

		if ($error)
		{

			$this->setError( $errorMsg );
			return false;
		}
		return true;
	}

	function saveOrderTax($values) {

		//get the model
		$cart_model = $this->getModel('mycart');
		$totals = $cart_model->getTotals();

		//store the order tax
		if(isset($totals['taxes']) && is_array($totals['taxes'])) {
			foreach($totals['taxes'] as $tax) {
				unset($ordertax);
				$ordertax = JTable::getInstance('ordertax', 'Table');
				$ordertax->order_id = $values['order_id'];
				$ordertax->ordertax_title = $tax['title'];
				$ordertax->ordertax_percent = $tax['percent'];
				$ordertax->ordertax_amount = $tax['value'];
				$ordertax->store();
			}

		}
	return true;
	}

	function saveOrderFiles($values){

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$user = JFactory::getUser();

		$query->select('pf.productfile_id, oi.orderitem_id');
		$query->from('#__j2store_orderitems AS oi');
		$query->where('oi.order_id = '.$values['order_id']);
		$query->where('pf.purchase_required=1');
		$query->join('LEFT', '`#__j2store_productfiles` AS pf ON pf.product_id = oi.product_id');
		$db->setQuery($query);
		$file_items = $db->loadObjectList();

		if(count($file_items)) {
			foreach ($file_items as $file) {
				if($file->productfile_id) {
					unset($row);
					$row = JTable::getInstance('orderfiles','Table');
					$row->orderitem_id=$file->orderitem_id;
					$row->productfile_id=$file->productfile_id;
					$row->limit_count=0;
					$row->user_id=$user->id ;
					$row->user_email=$values['user_email'];

					if ( !$row->save() )
					{
						$messagetype = 'notice';
						$message = JText::_( 'J2STORE_ERROR_SAVING_FILES_FAILED' )." - ".$row->getError();
						$this->setError( $message );
						return false;
					}
				}
			}
		}
		return true;
	}


	function saveOrderInfo($values){

		$row = JTable::getInstance('orderinfo','Table');

		if (!$row->bind($values)) {
			throw new Exception($row->getError());
			return false;
		}

		if (!$row->check()) {
			throw new Exception($row->getError());
			return false;
		}

		if (!$row->store()) {
			throw new Exception($row->getError());
			return false;
		}

		return true;
	}


	 function saveOrderShippings( $values )
    	{
        $order = $this->_order;

        $shipping_type = isset($values['shipping_plugin']) ? $values['shipping_plugin'] : '';
		if(!empty($shipping_type)) {
	        $row = JTable::getInstance('OrderShippings', 'Table');
	        $row->order_id = $order->order_id;
	        $row->ordershipping_type = $values['shipping_plugin'];
	        $row->ordershipping_price = $values['shipping_price'];
	        $row->ordershipping_name = $values['shipping_name'];
	        $row->ordershipping_code = $values['shipping_code'];
	        $row->ordershipping_tax = $values['shipping_tax'];
	        $row->ordershipping_extra = $values['shipping_extra'];

	        if (!$row->save($row))
	        {
	            $this->setError( $row->getError() );
	            return false;
	        }

	        // Let the plugin store the information about the shipping
	        if (isset($values['shipping_plugin']))
	        {
	            $dispatcher = JDispatcher::getInstance();
	            $dispatcher->trigger( "onJ2StorePostSaveShipping", array( $values['shipping_plugin'], $row ) );
	        }
		}

        return true;
    }


	function validateSelectPayment($payment_plugin, $values) {

		$response = array();
		$response['msg'] = '';
		$response['error'] = '';

		$dispatcher    = JDispatcher::getInstance();
		JPluginHelper::importPlugin ('j2store');

		//verify the form data
		$results = array();
		$results = $dispatcher->trigger( "onJ2StoreGetPaymentFormVerify", array( $payment_plugin, $values) );

		for ($i=0; $i<count($results); $i++)
		{
			$result = $results[$i];
			if (!empty($result->error))
			{
				$response['msg'] =  $result->message;
				$response['error'] = '1';
			}

		}
		if($response['error']) {
			throw new Exception($response['msg']);
			return false;
		} else {
			return true;
		}
		return false;
	}


	function validateSelectShipping($values) {

		$error = 0;

		if (isset($values['shippingrequired']))
		{
			if ($values['shippingrequired'] == 1 && empty($values['shipping_plugin']))
			{
				throw new Exception(JText::_('J2STORE_CHECKOUT_SELECT_A_SHIPPING_METHOD'));
				return false;
			}
		}

		//if order value is zero, then return true
		$order = $this->_order;

		// get the items and add them to the order
		$items = J2StoreHelperCart::getProducts();
		foreach ($items as $item)
		{
			$order->addItem( $item );
		}
		$order->calculateTotals();
		if ( (float) $order->order_total == (float) '0.00' )
		{
			return true;
		}

		//trigger the plugin's validation function
		// no matter what, fire this validation plugin event for plugins that extend the checkout workflow
		$results = array();
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger( "onValidateSelectShipping", array( $values ) );

		for ($i=0; $i<count($results); $i++)
		{
			$result = $results[$i];
			if (!empty($result->error))
			{
				throw new Exception($result->message);
				return false;
			}

		}

		 if($error == '1')
        {
            return false;
        }

        return true;
	}


	/**
	 * This method occurs after payment is attempted,
	 * and fires the onPostPayment plugin event
	 *
	 * @return unknown_type
	 */
	function confirmPayment()
	{
		$app =JFactory::getApplication();
		$orderpayment_type = $app->input->getString('orderpayment_type');

		// Get post values
		$values = $app->input->getArray($_POST);
		//backward compatibility for payment plugins
		foreach($values as $name=>$value) {
			$app->input->set($name, $value);
		}

		//set the guest mail to null if it is present
		//check if it was a guest checkout
		$account = $this->session->get('account', 'register', 'j2store');

		// get the order_id from the session set by the prePayment
		$orderpayment_id = (int) $app->getUserState( 'j2store.orderpayment_id' );
		if($account != 'guest') {
			$order_link = 'index.php?option=com_j2store&view=orders&task=view&id='.$orderpayment_id;
		} else {
			$guest_token  = $app->getUserState( 'j2store.order_token' );
			$order_link = 'index.php?option=com_j2store&view=orders&task=view';

			//assign to another session variable, for security reasons
			if($this->session->has('guest', 'j2store')) {
				$guest = $this->session->get('guest', array(), 'j2store');
				$this->session->set('guest_order_email', $guest['billing']['email']);
				$this->session->set('guest_order_token', $guest_token);
			}
		}

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin ('j2store');

		$html = "";
		$order= $this->_order;
		$order->load( array('id'=>$orderpayment_id));

		// free product? set the state to confirmed and save the order.
		if ( (!empty($orderpayment_id)) && (float) $order->order_total == (float)'0.00' )
		{
			$order->order_state = trim(JText::_('CONFIRMED'));
			$order->order_state_id = '1'; // PAYMENT RECEIVED.
			if($order->save()) {
				// remove items from cart
				J2StoreHelperCart::removeOrderItems( $order->id );
			}
			//send email
			require_once (JPATH_SITE.'/components/com_j2store/helpers/orders.php');
			J2StoreOrdersHelper::sendUserEmail($order->user_id, $order->order_id, $order->transaction_status, $order->order_state, $order->order_state_id);

		}
		else
		{
			// get the payment results from the payment plugin
			$results = $dispatcher->trigger( "onJ2StorePostPayment", array( $orderpayment_type, $values ) );

			// Display whatever comes back from Payment Plugin for the onPrePayment
			for ($i=0; $i<count($results); $i++)
			{
				$html .= $results[$i];
			}

			// re-load the order in case the payment plugin updated it
			$order->load( array('id'=>$orderpayment_id) );
		}

		// $order_id would be empty on posts back from Paypal, for example
		if (isset($orderpayment_id))
		{

			//unset a few things from the session.
			$this->session->clear('shipping_method', 'j2store');
			$this->session->clear('shipping_methods', 'j2store');
			$this->session->clear('payment_method', 'j2store');
			$this->session->clear('payment_methods', 'j2store');
			$this->session->clear('payment_values', 'j2store');
			$this->session->clear('guest', 'j2store');
			$this->session->clear('customer_note', 'j2store');

			//save the coupon to the order_coupons table for tracking and unset session.
			if($this->session->has('coupon', 'j2store')) {
					$coupon_info = J2StoreHelperCart::getCoupon($this->session->get('coupon', '', 'j2store'));
					if($coupon_info) {
						$order_coupons = JTable::getInstance('OrderCoupons', 'Table');
						$order_coupons->set('coupon_id', $coupon_info->coupon_id);
						$order_coupons->set('orderpayment_id', $orderpayment_id);
						$order_coupons->set('customer_id', JFactory::getUser()->id);
						$order_coupons->set('amount', $order->order_discount);
						$order_coupons->set('created_date', JFactory::getDate()->toSql());
						$order_coupons->store();
					}
			}

			//clear the session
			$this->session->clear('coupon', 'j2store');

			//trigger onAfterOrder plugin event
			$results = $dispatcher->trigger( "onJ2StoreAfterPayment", array($order) );
			foreach($results as $result) {
				$html .= $result;
			}

			// Set display
			$view = $this->getView( 'checkout', 'html' );
			$view->setLayout('postpayment');
			$view->set( '_doTask', true);

			$params = $params = JComponentHelper::getParams('com_j2store');
			if($params->get('show_postpayment_orderlink', 1)) {
				$view->assign('order_link', $order_link);
			} else {
				$view->assign('order_link', '');
			}

			$view->assign('plugin_html', $html);

			// Get and Set Model
			$model = $this->getModel('checkout');
			$view->setModel( $model, true );

			$view->display();
		}
		return;
	}

	public function getCountry() {
		$app = JFactory::getApplication();
		$model = $this->getModel('checkout');
		$country_id =$app->input->get('country_id');
		$json = array();
		$country_info = $model->getCountryById($country_id);
		if ($country_info) {
		$zones = $this->getModel('checkout')->getZonesByCountryId($app->input->get('country_id'));

			$json = array(
					'country_id'        => $country_info->country_id,
					'name'              => $country_info->country_name,
					'iso_code_2'        => $country_info->country_isocode_2,
					'iso_code_3'        => $country_info->country_isocode_3,
					'zone'              => $zones
			);
		}

		echo json_encode($json);
		$app->close();
	}

	public function getTerms() {

		$app = JFactory::getApplication();
		$id = $app->input->getInt('article_id');
		require_once (JPATH_COMPONENT_ADMINISTRATOR.'/library/j2item.php' );
		$j2item = new J2StoreItem();
		$data = $j2item->display($id);
		$view = $this->getView( 'checkout', 'html' );
		$view->set( '_controller', 'checkout' );
		$view->set( '_view', 'checkout' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', true);
		$view->assign( 'html', $data);
		$view->setLayout( 'checkout_terms' );
		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
		$app->close();
	}


}

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
//load the cart data
require_once (JPATH_COMPONENT.'/helpers/cart.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/tax.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/inventory.php');
class J2StoreControllerMyCart extends J2StoreController
{

	private $_data = array();

	var $tax = null;

	public function __construct($config = array())
		{
			parent::__construct($config);
			JFactory::getDocument()->setCharset('utf-8');
			JResponse::setHeader('X-Cache-Control', 'False', true);
			$this->tax = new J2StoreTax();
			//language
			$language = JFactory::getLanguage();
			/* Set the base directory for the language */
			$base_dir = JPATH_SITE;
			/* Load the language. IMPORTANT Becase we use ajax to load cart */
			$language->load('com_j2store', $base_dir, $language->getTag(), true);
		}

	function display($cachable = false, $urlparams = array()) {

		//initialist system objects
		$app = JFactory::getApplication();
		$session=  JFactory::getSession();
		J2StoreUtilities::cleanCache();
		$params = JComponentHelper::getParams('com_j2store');
		$view = $this->getView( 'mycart', 'html' );
		$view->set( '_view', 'mycart' );
		//get post vars
		$post = $app->input->getArray($_POST);
		$store = J2StoreHelperCart::getStoreAddress();

		$model = $this->getModel('Mycart');

        if (J2StoreHelperCart::hasProducts()) {
        	$items = $model->getDataNew();
        } else {
        	$items = array();
        }

       //validate stock
        if(count($items) && $params->get('enable_inventory', 0) && J2STORE_PRO == 1) {
        	if(!$params->get('allow_backorder', 0)) {
        		$result = $model->hasStock();
        	//	if($result->status == false && $result->redirect ) {
			//		$app->redirect('index.php', JText::_('J2STORE_OUT_OF_STOCK'));
			//	}
        	}
        }
        $items = $model->getDataNew();
        //coupon
        $post_coupon = $app->input->getString('coupon', '');
        //first time applying? then set coupon to session
        if (isset($post_coupon) && !empty($post_coupon)) {
        	try {
        		if($this->validateCoupon()) {
        			$session->set('coupon', $post_coupon, 'j2store');
        			$msg = JText::_('J2STORE_COUPON_APPLIED_SUCCESSFULLY');
        		}
        	} catch(Exception $e) {
        		$msg = $e->getMessage();
        	}
        	$this->setRedirect( JRoute::_( "index.php?option=com_j2store&view=mycart"), $msg);
        }

        if ($post_coupon) {
        	$view->assign( 'coupon', $post_coupon);
        } elseif ($session->has('coupon', 'j2store')) {
        	$view->assign( 'coupon', $session->get('coupon', '', 'j2store'));
        } else {
        	$view->assign( 'coupon', '');
        }



		//shipping tax calculator
		//get countries
		$db = JFactory::getDbo();
		$db->setQuery($db->getQuery(true)->select('country_id, country_name')->from('#__j2store_countries'));
		$countries = $db->loadObjectList();

		$country_id = $app->input->getInt('country_id');

		if (isset($country_id)) {
			$session->set('billing_country_id', $country_id, 'j2store');
    		$session->set('shipping_country_id', $country_id, 'j2store');
		} elseif ($session->has('shipping_country_id', 'j2store')) {
			$country_id = $session->get('shipping_country_id', '', 'j2store');
		} else {
			$country_id = $store->country_id;
		}

		$countryList = JHtml::_('select.genericlist', $countries, 'country_id', $attribs = null, $optKey = 'country_id', $optText = 'country_name', $country_id, $idtag = 'cart_country', $translate = false);

		$zone_id = $app->input->getInt('zone_id');
		if (isset($zone_id)) {
			$session->set('billing_zone_id', $zone_id, 'j2store');
    		$session->set('shipping_zone_id', $zone_id, 'j2store');
		} elseif($session->has('shipping_zone_id', 'j2store')) {
			$zone_id = $session->get('shipping_zone_id', '', 'j2store');
		} else {
			$zone_id = $store->zone_id;
		}

		$postcode = $app->input->getString('postcode');

		if (isset($postcode )) {
			$session->set('shipping_postcode', $postcode, 'j2store');
		} elseif ($session->has('shipping_postcode', 'j2store')) {
			$postcode = $session->get('shipping_postcode', '', 'j2store');
		} else {
			$postcode = $store->store_zip;
		}

		$view->assign( 'countryList', $countryList);
		$view->assign( 'country_id', $country_id);
		$view->assign( 'zone_id', $zone_id);
		$view->assign( 'postcode', $postcode);



		//do we have shipping methods
		if($session->has('shipping_methods', 'j2store')) {
			$view->assign( 'shipping_methods', $session->get('shipping_methods', array(), 'j2store'));
		}
		//assign a single selected method if it had been selected
		if($session->has('shipping_method', 'j2store')) {
			$view->assign( 'shipping_method', $session->get('shipping_method', array(), 'j2store'));
		} else {
			$view->assign( 'shipping_method', array());
		}
		
		//assign a single selected method if it had been selected
		if($session->has('shipping_values', 'j2store')) {
			$view->assign( 'shipping_values', $session->get('shipping_values', array(), 'j2store'));
		} else {
			$view->assign( 'shipping_values', array());
		}

		$cartobject = $model->checkItems($items, $params->get('show_tax_total'));

		$totals = $model->getTotals();

		$sendBaskethtml="";

		JPluginHelper::importPlugin('j2store');
		$results = $app->triggerEvent('onJ2StoreAfterDisplayCart',array($items));
		$J2StoreAfterDisplayCart = trim(implode("\n", $results));
		$view->assign('onJ2StoreAfterDisplayCart',$J2StoreAfterDisplayCart);
		
		$view->assign( 'cartobj', $cartobject);
		$view->assign( 'totals', $totals);
		$view->assign( 'model', $model);
		$view->assign( 'params', $params );
		if(isset($post['return'])) {
			$view->assign( 'return', $post['return']);
		}
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', true);
		$view->setModel( $model, true );
		$view->setLayout( 'default');
		$view->display();

	}


	function add() {
		$app = JFactory::getApplication();
		JFactory::getDocument()->setCharset('utf-8');
		$params = JComponentHelper::getParams('com_j2store');
		$model = $this->getModel('mycart');
		require_once(JPATH_COMPONENT.'/helpers/cart.php');
		$cart_helper = new J2StoreHelperCart();
		$error = array();
		$json = array();
		//get the product id
		$product_id = $app->input->getInt('product_id', 0);

		//no product id?. return an error
		if(empty($product_id)) {
			$error['error']['product']=JText::_('J2STORE_ADDTOCART_ERROR_MISSING_PRODUCT_ID');
			echo json_encode($error);
			$app->close();
		}

		//Ok. we have a product id. so proceed.
		//get the quantity
		$quantity = $app->input->get('product_qty');
		if (isset($quantity )) {
			$quantity = $quantity;
		} else {
			$quantity = 1;
		}

		$product = $cart_helper->getItemInfo($product_id);


		//get the product options
		$options = $app->input->get('product_option', array(0), 'ARRAY');
		if (isset($options )) {
			$options =  array_filter($options );
		} else {
			$options = array();
		}
		$product_options = $model->getProductOptions($product_id);

		//iterate through stored options for this product and validate
		foreach($product_options as $product_option) {
			if ($product_option['required'] && empty($options[$product_option['product_option_id']])) {
				$json['error']['option'][$product_option['product_option_id']] = JText::sprintf('J2STORE_ADDTOCART_PRODUCT_OPTION_REQUIRED', $product_option['option_name']);
			}
		}
		$post['product_id'] = $product_id;
		$post['quantity'] = $quantity;
		$post['options'] = $options;
		$post['product_options'] = $product_options;
		//trigger a plugin event
		$dispatcher    = JDispatcher::getInstance();
		JPluginHelper::importPlugin ('j2store');
		$results = $dispatcher->trigger('onJ2StoreBeforeAddtocart', array($post));
		foreach($results as $result) {
			$json['error'] = $result;
		}
		//validation is ok. Now add the product to the cart.
		if(!$json) {
			$cart_helper->add($product_id, $quantity, $options);
			$product_info = J2StoreHelperCart::getItemInfo($product_id);
			$cart_link = JRoute::_('index.php?option=com_j2store&view=mycart');
			$json['success'] = true;
			$json['successmsg'] =  $product_info->product_name.JText::_('J2STORE_ADDTOCART_ADDED_TO_CART');

			//$total =  J2StoreHelperCart::getTotal();
			$totals = $model->getTotals();
			if($params->get('auto_calculate_tax', 1)) {
				$total = $totals['total'];
			} else {
				$total = $totals['total_without_tax'];
			}

			$product_count = J2StoreHelperCart::countProducts();
			//get product total
			$json['total'] = JText::sprintf('J2STORE_CART_TOTAL', $product_count, J2StorePrices::number($total));

			//do we have to redirect to the cart
			if($params->get('popup_style', 1)==3) {
				$json['redirect'] = $cart_link;
			}

		} else {

			//do we have to redirect
		//	$url = 'index.php?option=com_content&view=article&id='.$product_id;
		//	$json['redirect'] = JRoute::_(urlencode($url));
		}
		echo json_encode($json);
		$app->close();
	}


	function ajaxmini() {
			//initialise system objects
			$app = JFactory::getApplication();
		$document	= JFactory::getDocument();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__modules')->where('module='.$db->q('mod_j2store_cart'))->where('published=1');
		$db->setQuery($query);
		$modules = $db->loadObjectList();

		$renderer	= $document->loadRenderer('module');
		if (count($modules) < 1)
		{
			//echo '';
			$app->close();
		} else {
			foreach($modules as $module) {
				$app->setUserState( 'mod_j2store_mini_cart.isAjax', '1' );
				echo $renderer->render($module);
			}
			$app->close();

		}
		$app->close();
	}

	function displayCart()
	{
		$app = JFactory::getApplication();
		$document	= JFactory::getDocument();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__modules')->where('module='.$db->q('mod_j2store_detailcart'))->where('published=1');
		$db->setQuery($query);
		$modules = $db->loadObjectList();

		$renderer	= $document->loadRenderer('module');
		if (count($modules) < 1)
		{
			//echo '';
			$app->close();
		} else {
			foreach($modules as $module) {
				$app->setUserState( 'mod_j2storecart.isAjax', '1' );
				echo $renderer->render($module);
			}
			$app->close();

		}
		$app->close();
	}

	 /**
     *
     * @return unknown_type
     */
    function update()
    {
        $app = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_j2store');
        $model 	= $this->getModel('mycart');
        $cart_helper = new J2StoreHelperCart();
		$errors= array();
        $key = $app->input->getString('key');

        $quantities = $app->input->get('quantity', array(0), 'ARRAY');
        $original_quantities = $app->input->get('original_quantities', array(0), 'ARRAY');

	   	$msg = JText::_('J2STORE_CART_UPDATED');

        $remove = $app->input->get('remove');
        $removeCoupon = $app->input->getInt('removeCoupon', 0);

        if ($remove)
        {
        	$model->remove($key);
        }elseif($removeCoupon) {
        	$model->removeCoupon();
        }
        else
        {


			if(count($errors) < 1) {
				//validation passed
				foreach ($quantities as $key=>$value)
				{
					$model->update($key, $value);

				}
			} else {
				//validation failed
				$msg = JText::_('J2STORE_OUT_OF_STOCK_PRODUCTS');
			}

       }

        if($remove || $removeCoupon) {
			$items = $model->getDataNew();

			$cartobject = $model->checkItems($items, $params->get('show_tax_total'));

			$view = $this->getView( 'mycart', 'html' );
			$view->set( '_view', 'mycart' );
			$view->set( '_doTask', true);
			$view->set( 'hidemenu', true);
			$view->setLayout( 'default');
			$totals = $model->getTotals();

			JPluginHelper::importPlugin('j2store');
			$results = $app->triggerEvent('onJ2StoreAfterDisplayCart',array($items));
			$J2StoreAfterDisplayCart = trim(implode("\n", $results));
			$view->assign('onJ2StoreAfterDisplayCart',$J2StoreAfterDisplayCart);

			$view->assign( 'cartobj', $cartobject);
			$view->assign( 'totals', $totals);
			$view->setModel( $model, true );
			$view->assign( 'params', $params );
			$view->assign( 'remove', $remove);

			ob_start();
			$view->display();
			$html = ob_get_contents();
			ob_end_clean();
			echo $html;
			$app->close();
		}

        $redirect = JRoute::_( "index.php?option=com_j2store&view=mycart");
       	$this->setRedirect( $redirect, $msg);
    }

    function validateCoupon() {

    	$app = JFactory::getApplication();
    	$coupon_info = J2StoreHelperCart::getCoupon($app->input->getString('coupon', ''));

    	if($coupon_info ) {
    		return true;
    	} else {
			throw new Exception(JText::_('J2STORE_COUPON_INVALID'));
    		return false;
    	}
    }

    function setcurrency() {

    	$app = JFactory::getApplication();
    	$currency = J2StoreFactory::getCurrencyObject();
    	$post = $app->input->getArray($_POST);
    	if(isset($post['currency_code'])) {
    		$currency->set($post['currency_code']);
    	}

    	//get the redirect
    	if(isset($post['redirect'])) {
    		$url = base64_decode($post['redirect']);
    	} else {
    		$url = 'index.php';
    	}

    	$app->redirect($url);
    }

    function estimate() {

    	$app = JFactory::getApplication();
    	$session = JFactory::getSession();
    	$country_id = $app->input->getInt('country_id', 0);
    	$zone_id = $app->input->getInt('zone_id', 0);
    	$postcode  = $app->input->getString('postcode', 0);
    	$cart_model = $this->getModel('mycart');
    	$checkout_model = $this->getModel('checkout');

    	if($country_id || $zone_id) {
    		if($country_id) {
    			$session->set('billing_country_id', $country_id, 'j2store');
    			$session->set('shipping_country_id', $country_id, 'j2store');
    		}

    		if($zone_id) {
    			$session->set('billing_zone_id', $zone_id, 'j2store');
    			$session->set('shipping_zone_id', $zone_id, 'j2store');
    		}
    	}

    	if($postcode) {
    		$session->set('shipping_postcode', $postcode, 'j2store');
    	}
    	$showShipping = false;
    	if ($isShippingEnabled = $cart_model->getShippingIsEnabled())
    	{
    		$showShipping = true;
    	}

    	if($showShipping)
    	{
    		$rates = $checkout_model->getShippingRates();
    		$session->set('shipping_methods', $rates, 'j2store');
    		if(count($rates) < 1) {
    			$session->set('shipping_method', array(), 'j2store');
    		}
    	}

    	$url = JRoute::_('index.php?option=com_j2store&view=mycart');
    	echo json_encode(array('redirect'=>$url));
    	$app->close();

    }

    function shippingUpdate() {

    	$app = JFactory::getApplication();
    	$session = JFactory::getSession();
    	$values = $app->input->getArray($_POST);

    	$shipping_values = array();
    	$shipping_values['shipping_price']    = isset($values['shipping_price']) ? $values['shipping_price'] : 0;
    	$shipping_values['shipping_extra']   = isset($values['shipping_extra']) ? $values['shipping_extra'] : 0;
    	$shipping_values['shipping_code']     = isset($values['shipping_code']) ? $values['shipping_code'] : '';
    	$shipping_values['shipping_name']     = isset($values['shipping_name']) ? $values['shipping_name'] : '';
    	$shipping_values['shipping_tax']      = isset($values['shipping_tax']) ? $values['shipping_tax'] : 0;
    	$shipping_values['shipping_plugin']     = isset($values['shipping_plugin']) ? $values['shipping_plugin'] : '';

    	$session->set('shipping_method', $shipping_values['shipping_plugin'], 'j2store');
    	$session->set('shipping_values', $shipping_values, 'j2store');

    	$redirect = JRoute::_('index.php?option=com_j2store&view=mycart');
    	echo json_encode(array('redirect'=>$redirect));
    	$app->close();
    }
}

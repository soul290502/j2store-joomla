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

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/shipping.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/toolbar.php');
require_once(JPATH_SITE.'/components/com_j2store/helpers/cart.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/prices.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/tax.php');
JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
class plgJ2StoreShipping_Standard extends J2StoreShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
    var $_element   = 'shipping_standard';

    /**
     * Overriding
     *
     * @param $options
     * @return unknown_type
     */
    function onJ2StoreGetShippingView( $row )
    {
    	if (!$this->_isMe($row))
    	{
    		return null;
    	}

    	$html = $this->viewList();

    	return $html;
    }

    /**
     * Validates the data submitted based on the suffix provided
     * A controller for this plugin, you could say
     *
     * @param $task
     * @return html
     */
    function viewList()
    {

    	$app = JFactory::getApplication();
    	$html = "";
    	JToolBarHelper::title(JText::_('J2STORE_SHIPM_SHIPPING_METHODS').'-'.JText::_('plg_j2store_'.$this->_element),'j2store-logo');
    	J2StoreToolBar::_custom('newMethod', 'new', 'new', 'JTOOLBAR_NEW', false, false, 'shippingTask');
    	J2StoreToolBar::_custom('delete', 'delete', 'delete', 'JTOOLBAR_DELETE', false, false, 'shippingTask');
    	//JToolBarHelper::trash('delete', 'JTOOLBAR_DELETE');
    	JToolBarHelper::cancel( 'cancel', 'JTOOLBAR_CLOSE' );

    	$vars = new JObject();
    	$vars->state = $this->_getState();

    	$this->includeCustomModel('ShippingMethods');
    	$this->includeCustomTables();
    	$model = JModelLegacy::getInstance('ShippingMethods', 'J2StoreModel');
    	$list = $model->getList();
    	$vars->list = $list;
    	$id = $app->input->getInt('id', '0');
    	$form = array();
    	$form['action'] = "index.php?option=com_j2store&view=shipping&task=view&id={$id}";
    	$vars->form = $form;
    	$vars->sid = $id;
    	$html = $this->_getLayout('default', $vars);

    	return $html;
    }



    /**
     *
     * @param $element
     * @param $values
     */
    function onJ2StoreGetShippingRates($element, $order)
    {
    	// Check if this is the right plugin
    	if (!$this->_isMe($element))
    	{
    		return null;
    	}

    	$vars = array();

    	$this->includeJ2StoreTables();
    	$this->includeCustomTables();
    	$this->includeCustomModel('ShippingMethods');
    	$this->includeCustomModel('ShippingRates');
		//set the address
		$order->setAddress();
		$geozones_taxes = array();
	//	$geozones_taxes = $order->getBillingGeoZones();
    	$geozones = $order->getShippingGeoZones();
    	$gz_array = array();
    	foreach ($geozones as $geozone)
    	{
    		$gz_array[] = $geozone->geozone_id;
    	}

    	$rates = array();
    	$model = JModelLegacy::getInstance('ShippingMethods', 'J2StoreModel');
    	$model->setState( 'filter_enabled', '1' );
    	$model->setState( 'filter_subtotal', $order->order_subtotal );
    	if ($methods = $model->getList())
    	{
    		foreach( $methods as $method )
    		{
    			//check if there is an override
    			if($method->address_override == 'store') {
    				//there is an override.
    				//so set the shipping address to store and get the geozones afresh
    				$order->setAddress('store');

    			} else {
    				$order->setAddress();
    			}
    			$geozones = $order->getShippingGeoZones();
    			$gz_array = array();
    			foreach ($geozones as $geozone)
    			{
    				$gz_array[] = $geozone->geozone_id;
    			}
    			// filter the list of methods according to geozone
    			$ratemodel = JModelLegacy::getInstance('ShippingRates', 'J2StoreModel');
    			$ratemodel->setState('filter_shippingmethod', $method->shipping_method_id);
    			$ratemodel->setState('filter_geozones', $gz_array);
    			if ($ratesexist = $ratemodel->getList())
    			{
    				$total = $this->getTotal($method->shipping_method_id, $geozones, $order->getItems(), $geozones_taxes );
    				if ($total)
    				{
    					$total->shipping_method_type = $method->shipping_method_type;
    					$rates[] = $total;
    				}
    			}
    		}
    	}

    	$i = 0;
    	foreach( $rates as $rate )
    	{
    		$vars[$i]['element'] = $this->_element;
    		$vars[$i]['name'] = addslashes(JText::_($rate->shipping_method_name));
    		$vars[$i]['type'] = $rate->shipping_method_type;
    		$vars[$i]['code'] = $rate->shipping_rate_id;
    		$vars[$i]['price'] = $rate->shipping_rate_price;
    		$vars[$i]['tax'] = round($rate->shipping_tax_total, 2);
    		$vars[$i]['extra'] = $rate->shipping_rate_handling;
    		$vars[$i]['total'] = $rate->shipping_rate_price + $rate->shipping_rate_handling + round($rate->shipping_tax_total, 2);
    		$i++;
    	}
//print_r($vars);
    	return $vars;

    }

    /**
     *
     * Returns an object with the total cost of shipping for this method and the array of geozones
     *
     * @param unknown_type $shipping_method_id
     * @param array $geozones
     * @param unknown_type $orderItems
     * @param unknown_type $order_id
     */
    protected function getTotal( $shipping_method_id, $geozones, $orderItems, $geozones_taxes )
    {
    	$return = new JObject();
    	$return->shipping_rate_id         = '0';
    	$return->shipping_rate_price      = '0.00000';
    	$return->shipping_rate_handling   = '0.00000';
    	$return->shipping_tax_rates        = '0.00000';
    	$return->shipping_tax_total       = '0.00000';

    	$rate_exists = false;
    	$geozone_rates = array();


    	//include custom modals
    	$this->includeCustomModel('ShippingMethods');
    	$this->includeCustomModel('ShippingRates');
    	// cast product_id as an array
    	$orderItems = (array) $orderItems;

    	// determine the shipping method type
    	$this->includeCustomTables('shipping_standard');
    	$this->includeCustomTables();
    	$shippingmethod = JTable::getInstance( 'ShippingMethods', 'Table' );
    	$shippingmethod->load( $shipping_method_id );

    	if (empty($shippingmethod->shipping_method_id))
    	{
    		// TODO if this is an object, setError, otherwise return false, or 0.000?
    		$return->setError( JText::_('J2STORE_UNDEFINED_SHIPPING_METHOD') );
    		return $return;
    	}

    	//initiliase cart helper
    	$carthelper = new J2StoreHelperCart();

    	//initliase cart model
    	JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_j2store/models');
    	$cart_model = new J2StoreModelMyCart();

    	switch($shippingmethod->shipping_method_type)
    	{
    		case "2":
    			// 2 = per order - price based
    			// Get the total of the order, and find the rate for that
    			$total = 0;
    			//foreach ($orderItems as $item)
    		//	{
    		//		$total += $item->orderitem_final_price;
    		//	}
    			$order_ships = false;
    			$products = $cart_model->getDataNew();
    			foreach($products as $product) {

    				//check if shipping is enabled for this item
    				if(!empty($product['shipping'])) {
    					$order_ships = true;
    					$total += $product['total']; // product total
    				}
    			}

    			if($order_ships) {
	    			foreach ($geozones as $geozone)
	    			{
	    				unset($rate);

	    				$geozone_id = $geozone->geozone_id;
	    				if (empty($geozone_rates[$geozone_id]) || !is_array($geozone_rates[$geozone_id]))
	    				{
	    					$geozone_rates[$geozone_id] = array();
	    				}

	    			//	JModelLegacy::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/models' );
	    				$model = JModelLegacy::getInstance('ShippingRates', 'J2StoreModel');
	    				$model->setState('filter_shippingmethod', $shipping_method_id);
	    				$model->setState('filter_geozone', $geozone_id);
	    				$model->setState('filter_weight', $total); // Use weight as total

	    				$items = $model->getList();

	    				if (count($items) < 1)
	    				{
	    					//return JTable::getInstance('ShippingRates', 'Table');
	    				} else {

	    				$rate = $items[0];
	    				$geozone_rates[$geozone_id]['0'] = $rate;

	    				// if $rate->shipping_rate_id is empty, then no real rate was found
	    				if (!empty($rate->shipping_rate_id))
	    				{
	    					$rate_exists = true;
	    				}

	    				$geozone_rates[$geozone_id]['0']->qty = '1';
	    				$geozone_rates[$geozone_id]['0']->shipping_method_type = $shippingmethod->shipping_method_type;
	    				}
	    			}
    			}
    			break;
    		case "1":
    			// 1 = per order - quantity based
    			// first, get the total quantity of shippable items for the entire order
    			// then, figure out the rate for this number of items (use the weight range field) + geozone
    		case "0":
    			// 0 = per order - flat rate
    		case "5":
    			// 5 = per order - weight based

    			// if any of the products in the order require shipping
    			$sum_weight = 0;
    			$count_shipped_items = 0;
    			$order_ships = false;
    			/*
    			foreach ($orderItems as $item)
    			{
    				// find out if the order ships
    				// and while looping through, sum the weight of all shippable products in the order
    				$pid = $item->product_id;
    				$product = JTable::getInstance( 'Prices', 'Table' );
    				$product->load( array('article_id'=>$pid ));

    				if (!empty($product->item_shipping))
    				{
    					$product_id = $item->product_id;
    					$order_ships = true;
    					$sum_weight += ($product->item_weight * $item->orderitem_quantity);
    					$count_shipped_items += $item->orderitem_quantity;
    				}
    			}
				*/

    			$products = $cart_model->getDataNew();

    			foreach($products as $product) {

    				//check if shipping is enabled for this item
    				if(!empty($product['shipping'])) {
    					$product_id = $product['product_id'];
    					$order_ships = true;
    					$sum_weight += $product['weight_total']; // we already have a weight total. So we dont have to multiply weight*quantity again
    					$count_shipped_items += $product['quantity'];
    				}
    			}

    			if ($order_ships)
    			{
    				foreach ($geozones as $geozone)
    				{
    					unset($rate);

    					$geozone_id = $geozone->geozone_id;
    					if (empty($geozone_rates[$geozone_id]) || !is_array($geozone_rates[$geozone_id]))
    					{
    						$geozone_rates[$geozone_id] = array();
    					}

    					switch( $shippingmethod->shipping_method_type )
    					{
    						case "0":
    							// don't use weight, just do flat rate for entire order
    							// regardless of weight and regardless of the number of items
    							$rate = $this->getRate( $shipping_method_id, $geozone_id, $product_id );
    							break;
    						case "1":
    							// get the shipping rate for the entire order using the count of all products in the order that ship
    							$rate = $this->getRate( $shipping_method_id, $geozone_id, $product_id, '1', $count_shipped_items );
    							break;
    						default:
    							// get the shipping rate for the entire order using the sum weight of all products in the order that ship
    							$rate = $this->getRate( $shipping_method_id, $geozone_id, $product_id, '1', $sum_weight );
    							break;
    					}
    					$geozone_rates[$geozone_id]['0'] = $rate;

    					// if $rate->shipping_rate_id is empty, then no real rate was found
    					if (!empty($rate->shipping_rate_id))
    					{
    						$rate_exists = true;
    					}

    					$geozone_rates[$geozone_id]['0']->qty = '1';
    					$geozone_rates[$geozone_id]['0']->shipping_method_type = $shippingmethod->shipping_method_type;
    				}
    			}
    			break;
    		case "6":
    		case "4":
    		case "3":
    			// 6 = per item - price based, a percentage of the product's price
    			// 4 = per item - weight based
    			// 3 = per item - flat rate

    			$rates = array();

    			/*
    			foreach ($orderItems as $item)
    			{
    			//	print_r($item);
    				$pid = $item->product_id;
    				$qty = $item->orderitem_quantity;
    				$attribs = $item->orderitem_attributes;
    				$hash = $pid.$attribs;
    				foreach ($geozones as $geozone)
    				{
    					unset($rate);

    					$geozone_id = $geozone->geozone_id;
    					if (empty($geozone_rates[$geozone_id]) || !is_array($geozone_rates[$geozone_id]))
    					{
    						$geozone_rates[$geozone_id] = array();
    					}
    					// $geozone_rates[$geozone_id][$pid] contains the shipping rate object for ONE product_id at this geozone.
    					// You need to multiply by the quantity later
    					$rate = $this->getRate( $shipping_method_id, $geozone_id, $pid, $shippingmethod->shipping_method_type );

    					if ($shippingmethod->shipping_method_type == '6')
    					{
    						// the rate is a percentage of the product's price
    						$rate->shipping_rate_price = ($rate->shipping_rate_price/100) * $item->orderitem_final_price;

    						$geozone_rates[$geozone_id][$hash] = $rate;
    						$geozone_rates[$geozone_id][$hash]->shipping_method_type = $shippingmethod->shipping_method_type;
    						$geozone_rates[$geozone_id][$hash]->qty = '1'; // If the method_type == 6, qty should be 1 (we don't need to multiply later, in the "calc for the entire method", since this is a percentage of the orderitem_final_price)

    					//if weight based per item, we need to use weight
    					}elseif($shippingmethod->shipping_method_type == '4')
    					{
    						$rate = $this->getRate( $shipping_method_id, $geozone_id, $pid, '1');
    						$geozone_rates[$geozone_id][$hash] = $rate;
    						$geozone_rates[$geozone_id][$hash]->shipping_method_type = $shippingmethod->shipping_method_type;
    						$geozone_rates[$geozone_id][$hash]->qty = $qty;
    					}
    					else
    					{
    						$geozone_rates[$geozone_id][$hash] = $rate;
    						$geozone_rates[$geozone_id][$hash]->shipping_method_type = $shippingmethod->shipping_method_type;
    						$geozone_rates[$geozone_id][$hash]->qty = $qty;
    					}

    					// if $rate->shipping_rate_id is empty, then no real rate was found
    					if (!empty($rate->shipping_rate_id))
    					{
    						$rate_exists = true;
    					}
    				}
    			}
    			*/
				$products = $cart_model->getDataNew();
    			foreach ($products as $product)
    			{
    				//	print_r($item);
    				$pid = $product['product_id'];
    				$qty = $product['quantity'];
    				$hash = $product['key'];

    				foreach ($geozones as $geozone)
    				{
    					unset($rate);

    					$geozone_id = $geozone->geozone_id;
    					if (empty($geozone_rates[$geozone_id]) || !is_array($geozone_rates[$geozone_id]))
    					{
    						$geozone_rates[$geozone_id] = array();
    					}
    					// $geozone_rates[$geozone_id][$pid] contains the shipping rate object for ONE product_id at this geozone.
    					// You need to multiply by the quantity later
    					$rate = $this->getRate( $shipping_method_id, $geozone_id, $pid, $shippingmethod->shipping_method_type );

    					//price per item
    					if ($shippingmethod->shipping_method_type == '6')
    					{
    						// the rate is a percentage of the product's price
    						$rate->shipping_rate_price = ($rate->shipping_rate_price/100) * $item->orderitem_final_price;

    						$geozone_rates[$geozone_id][$hash] = $rate;
    						$geozone_rates[$geozone_id][$hash]->shipping_method_type = $shippingmethod->shipping_method_type;
    						$geozone_rates[$geozone_id][$hash]->qty = '1'; // If the method_type == 6, qty should be 1 (we don't need to multiply later, in the "calc for the entire method", since this is a percentage of the orderitem_final_price)

    						//weight per item

    						//if weight based per item, we need to use weight.
    						//Per product weight (including the option weight) is already present in the products array. So pass it.
    					}elseif($shippingmethod->shipping_method_type == '4')
    					{
    						$rate = $this->getRate( $shipping_method_id, $geozone_id, $pid, '1', $product['weight']);
    						$geozone_rates[$geozone_id][$hash] = $rate;
    						$geozone_rates[$geozone_id][$hash]->shipping_method_type = $shippingmethod->shipping_method_type;
    						$geozone_rates[$geozone_id][$hash]->qty = $qty;
    					}
    					else
    					{
    						//obviously, this is flat rate per item
    						$geozone_rates[$geozone_id][$hash] = $rate;
    						$geozone_rates[$geozone_id][$hash]->shipping_method_type = $shippingmethod->shipping_method_type;
    						$geozone_rates[$geozone_id][$hash]->qty = $qty;
    					}

    					// if $rate->shipping_rate_id is empty, then no real rate was found
    					if (!empty($rate->shipping_rate_id))
    					{
    						$rate_exists = true;
    					}
    				}
    			}

    			break;
    		default:
    			$this->setError( JText::_('J2STORE_INVALID_SHIPPING_METHOD_TYPE') );
    			return false;
    			break;
    	}

    	if (!$rate_exists)
    	{
    		$this->setError( JText::_('J2STORE_NO_RATE_FOUND') );
    		return false;
    	}

    	$shipping_tax_rates = array();
    	$shipping_method_price = 0;
    	$shipping_method_handling = 0;
    	$shipping_method_tax_total = 0;
		$j2tax = new J2StoreTax();

	    	// now calc tax for the entire method
	    	foreach ($geozone_rates as $geozone_id=>$geozone_rate_array)
	    	{

	    		foreach ($geozone_rate_array as $geozone_rate)
	    		{
					if($shippingmethod->tax_class_id) {
						/*
	    				$tax_rates = $this->getGeozoneTax( $shippingmethod->tax_class_id, $geozone_id );
		    			if($tax_rates) {
		    				$shipping_tax_rates[$geozone_id] = 0;
							foreach($tax_rates as $tax_rate) {
		    					$shipping_tax_rates[$geozone_id] += $tax_rate->tax_percent;
		    					$shipping_method_tax_total += ($shipping_tax_rates[$geozone_id]/100) * (($geozone_rate->shipping_rate_price * $geozone_rate->qty ) + $geozone_rate->shipping_rate_handling);
		    				}
		    			}
		    			*/
						$value = ($geozone_rate->shipping_rate_price * $geozone_rate->qty ) + $geozone_rate->shipping_rate_handling;
						$tax_rates = $j2tax->getRates($shippingmethod->tax_class_id);
						$shipping_tax_rates[$geozone_id] = 0;
						foreach ($tax_rates as $tax_rate) {
							$shipping_tax_rates[$geozone_id] += $tax_rate['rate'];
						}
						$shipping_method_tax_total += $j2tax->getTax($value, $shippingmethod->tax_class_id);

	    			}


	    			$shipping_method_price += ($geozone_rate->shipping_rate_price * $geozone_rate->qty);
	    			$shipping_method_handling += $geozone_rate->shipping_rate_handling;
	    		}
    		}

    	// return formatted object
	    $return->shipping_rate_price    = $shipping_method_price;
	    $return->shipping_rate_handling = $shipping_method_handling;
	    $return->shipping_tax_rates     = $shipping_tax_rates;
	    $return->shipping_tax_total     = $shipping_method_tax_total;
	    $return->shipping_method_id     = $shipping_method_id;
	    $return->shipping_method_name   = $shippingmethod->shipping_method_name;

	  //  print_r($return);
    	return $return;
    }

    function getGeoZoneTax($tax_class_id, $geozone_id) {

    	$db = JFactory::getDbo();
    	$query = $db->getQuery(true);

    	//first get taxrates associated to this tax profile.

    	$query->select('taxrule.*')->from('#__j2store_taxrules AS taxrule')
    			->where('taxprofile_id='.$tax_class_id);
    	$db->setQuery($query);
    	$taxrules = $db->loadObjectList();

    	$tax_rates = array();

    	if($taxrules) {
	    	foreach($taxrules as $taxrule) {
	    		$query = $db->getQuery(true);
	    		$query->select('*')->from('#__j2store_taxrates')
	    		->where('taxrate_id='.$taxrule->taxrate_id)
	    		->where('geozone_id='.$geozone_id );
	    		$db->setQuery($query);
	    		$item = $db->loadObject();
				if(isset($item)) {
	    			$tax_rates[] = $item;
				}
	    	}
    	}

    	return $tax_rates;
    }


    /**
     * Returns the shipping rate for an item
     * Going through this helper enables product-specific flat rates in the future...
     *
     * @param int $shipping_method_id
     * @param int $geozone_id
     * @param int $product_id
     * @return object
     */
    public function getRate( $shipping_method_id, $geozone_id, $product_id='', $use_weight='0', $weight='0' )
    {

    	$this->includeJ2StoreTables();
    	$this->includeCustomTables();
    	$this->includeCustomModel('ShippingMethods');
    	$this->includeCustomModel('ShippingRates');
    	// TODO Give this better error reporting capabilities
    	//JModelLegacy::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/models' );
    	$model = JModelLegacy::getInstance('ShippingRates', 'J2StoreModel');
    	$model->setState('filter_shippingmethod', $shipping_method_id);
    	$model->setState('filter_geozone', $geozone_id);

    	$product = J2StorePrices::getJ2Product($product_id);

    	if (empty($product->article_id))
    	{
    		return JTable::getInstance('ShippingRates', 'Table');
    	}
    	if (empty($product->item_shipping))
    	{
    		// product doesn't require shipping, therefore cannot impact shipping costs
    		return JTable::getInstance('ShippingRates', 'Table');
    	}

    	if (!empty($use_weight) && $use_weight == '1')
    	{
    		if(!empty($weight))
    		{
    			$model->setState('filter_weight', $weight);
    		}
    		else
    		{
    			$model->setState('filter_weight', $product->item_weight);
    		}
    	}
    	$items = $model->getList();

    	if (empty($items))
    	{
    		return JTable::getInstance('ShippingRates', 'Table');
    	}

    	return $items[0];
    }


}


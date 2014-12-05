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



// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
JLoader::register( 'J2StoreTable', JPATH_ADMINISTRATOR.'/components/com_j2store/tables/_base.php' );
require_once (JPATH_SITE.'/components/com_j2store/helpers/cart.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/prices.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/tax.php');
class TableOrders extends J2StoreTable
{

	 /** @var array An array of J2StoreTableOrderItems objects */
    protected $_items = array();

    /** @var array holding country id and zone id for billing */
    protected $_billing_address = null;

    /** @var array holding country id and zone id for billing */
    protected $_shipping_address = null;

    /** @var array      tax & shipping geozone objects */
    protected $_billing_geozones = array();
    protected $_shipping_geozones = array();

    /** @var array      The shipping totals JObjects */
    protected $_shipping_totals = array();

    /** @var array An array of J2StoreTableTaxRates objects (the unique taxrates for this order) */
    protected $_product_taxrates = array();

    /** @var array An array of tax amounts, indexed by tax_rate_id */
    protected $_taxes = array();

    /**
     *
     * @var array holding the orderinfo table
     */
    protected $_order_info = array();

	/**
	* @param database A database connector object
	*/
	 function TableOrders ( &$db )
	{
		parent::__construct('#__j2store_orders', 'id', $db );
	}

		/**
	 * Loads the Order object with values from the DB tables
	 */
    function load( $oid=null, $reset=true )
    {
    	if ($return = parent::load($oid, $reset))
    	{
    		// TODO populate the protected vars with the info from the db
    	}
    	return $return;
    }

	function check()
	{
        $db         = $this->getDBO();
        $nullDate   = $db->getNullDate();
	    if (empty($this->created_date) || $this->created_date == $nullDate)
        {
            $date = JFactory::getDate();
            $this->created_date = $date->toSql();
        }
		return true;
	}



	 function addItem( $item )
    {
        $orderItem = JTable::getInstance('OrderItems', 'Table');
        if (is_array($item))
        {
            $orderItem->bind( $item );
        }
        elseif (is_object($item) && is_a($item, 'TableOrderItems'))
        {
            $orderItem = $item;
        }
        elseif (is_object($item))
        {
            $orderItem->product_id = $item->product_id;
            $orderItem->orderitem_quantity = $item->orderitem_quantity;
            $orderItem->orderitem_attributes = $item->orderitem_attributes;
        }
        else
        {
            $orderItem->product_id = $item;
            $orderItem->orderitem_quantity = '1';
            $orderItem->orderitem_attributes = '';
        }

        // Use hash to separate items when customer is buying the same product from multiple vendors
        // and with different attribs
			$hash = intval($orderItem->product_id).".".$orderItem->orderitem_attributes;

        if (!empty($this->_items[$hash]))
        {
            // merely update quantity if item already in list
            $this->_items[$hash]->orderitem_quantity += $orderItem->orderitem_quantity;
        }
            else
        {
            $this->_items[$hash] = $orderItem;
        }

    }


    function calculateTotals()
    {
        // get the subtotal first.
        // if there are per_product coupons and coupons_before_tax, the orderitem_final_price will be adjusted
        // and ordercoupons created
    	// then calculate the tax
    	$this->calculateTaxTotals();

        $this->calculateProductTotals();


       // then calculate shipping total
        $this->calculateShippingTotals();

        // then calculate discount
      //  $this->calculateDiscountTotals();

        // sum totals
        $total =
            $this->order_subtotal
            + $this->order_tax
            + $this->order_shipping
            + $this->order_shipping_tax
            - $this->order_discount
            + $this->order_surcharge
            ;

        // set object properties
		$this->order_total      = $total;
    }


     /**
     * Calculates the product total (aka subtotal)
     * using the array of items in the order object
     *
     * @return unknown_type
     */
    function calculateProductTotals()
    {
    	$app = JFactory::getApplication();
    	$session = JFactory::getSession();
    	$tax = new J2StoreTax();
        $subtotal = 0.00;
        $this->_taxes = J2StoreHelperCart::getTaxes();

        // TODO Must decide what we want these methods to return; for now, null
        $items = $this->getItems();
        if (!is_array($items))
        {
            $this->order_subtotal = $subtotal;
            return;
        }

        // calculate product subtotal
        foreach ($items as $item)
        {

			//$item->orderitem_final_price;
		    // track the subtotal
            $subtotal += $item->orderitem_final_price;
        }

        // set subtotal
        $this->order_subtotal   = $subtotal;


         //coupon
        if($session->has('coupon', 'j2store')) {
        	$coupon_info = J2StoreHelperCart::getCoupon($session->get('coupon', '', 'j2store'));

        	if ($coupon_info) {
        		$discount_total = 0;

        		if (!$coupon_info->product) {
        			$sub_total = $this->order_subtotal;
        		} else {
        			$sub_total = 0;
        			foreach ($items as $item) {
        				if (in_array($item->product_id, $coupon_info->product)) {
        					$sub_total += $item->orderitem_final_price;
        				}
        			}
        		}

        		if ($coupon_info->value_type == 'F') {
        			$coupon_info->value = min($coupon_info->value, $sub_total);
        		}

        		//maximum value restriction. If set then we need to check
        		if ($coupon_info->value_type == 'P' && !empty($coupon_info->max_value) && (float) $coupon_info->max_value > 0) {
        			//calculate the actual discount
        			require_once (JPATH_SITE.'/components/com_j2store/models/mycart.php');
        			$cart_model = new J2StoreModelMyCart();
        			$actual_discount = $cart_model->getDiscountTotal();
        			//is the actual discount greater than the max value
        			if($actual_discount > 0 && $actual_discount > (float) $coupon_info->max_value) {

        				//set the coupon to be of fixed value
        				$coupon_info->value = (float) $coupon_info->max_value;
        				$coupon_info->value_type = 'F';
        			}
        		}

        		foreach ($items as $item) {
        			$discount = 0;

        			if (!$coupon_info->product) {
        				$status = true;
        			} else {
        				if (in_array($item->product_id, $coupon_info->product)) {
        					$status = true;
        				} else {
        					$status = false;
        				}
        			}

        			if ($status) {
        				if ($coupon_info->value_type == 'F') {
        					$discount = $coupon_info->value * ($item->orderitem_final_price / $sub_total);
        				} elseif ($coupon_info->value_type == 'P') {
        					$discount = $item->orderitem_final_price / 100 * $coupon_info->value;
        				}

        				//get tax profile id. We need to adjust tax against coupons
        				$tax_profile_id = J2StorePrices::getTaxProfileId($item->product_id);

        				if ($tax_profile_id) {
        					$this->_product_taxrates = $tax->getRateArray($item->orderitem_final_price, $tax_profile_id);
        					$tax_rates = $tax->getRateArray($item->orderitem_final_price - ($item->orderitem_final_price - $discount), $tax_profile_id);
        					foreach ($tax_rates as $tax_rate) {
        						//	if ($tax_rate['value_type'] == 'P') {
        						$this->_taxes[$tax_rate['taxrate_id']] -= $tax_rate['amount'];
        						$this->_product_taxrates[$tax_rate['taxrate_id']]['amount'] -=$tax_rate['amount'];
        						//	}
        					}
        				}
        			}
        			$item->orderitem_discount = $discount;
        			$discount_total += $discount;

        			//adjust the tax
        			$product_tax_totals = 0;
        			foreach ($this->_product_taxrates as $product_tax_rate) {
        				$product_tax_totals+=$product_tax_rate['amount'];
        			}
        			if($tax_profile_id) {
        				$item->orderitem_tax = $product_tax_totals;
        			}
        		}
			$this->order_discount = $discount_total > ($this->order_subtotal + $this->order_tax) ? $this->order_subtotal + $this->order_tax : $discount_total;
        	}
        }

        //tax override. If there is a coupon. we need to do this
        $tax_total = 0;
        foreach ($this->_taxes as $key => $value) {
        	if ($value > 0) {
        		$tax_total += $value;
        	}
        }
        $this->order_tax =$tax_total;
    }

      /**
     * Calculates the tax totals for the order
     * using the array of items in the order object
     *
     * @return unknown_type
     */
    function calculateTaxTotals()
    {
		$t = new J2StoreTax();
		$tax_total = 0.00;

        $items= $this->getItems();
        if (!is_array($items))
        {
            $this->order_tax = $tax_total;
            return;
        }

        foreach ($items as $key=>$item)
        {
            $orderitem_tax = 0;

            //$product_tax_rate = J2StorePrices::getItemTax($item->product_id);
            //$orderitem_tax += $product_tax_rate * $item->orderitem_final_price;
            // track the total tax for this item
            $orderitem_tax += $t->getProductTax($item->orderitem_final_price, $item->product_id);
            $item->orderitem_tax = $orderitem_tax;
            // track the running total
            $tax_total += $item->orderitem_tax;
        }
    	 $this->order_tax = $tax_total;
    }

      function calculateShippingTotals()
    {
    $order_shipping     = 0.00;
        $order_shipping_tax = 0.00;

        $items = $this->getItems();

        if (!is_array($items) || !$this->shipping)
        {
            $this->order_shipping       = $order_shipping;
            $this->order_shipping_tax   = $order_shipping_tax;
            return;
        }


        $shipping_totals = array();

        $this->order_shipping       = $this->shipping->shipping_price + $this->shipping->shipping_extra;
        $this->order_shipping_tax   = $this->shipping->shipping_tax;
        if( $this->order_shipping < 0)
        {
        	$this->order_shipping = 0.00;
        }
        // set object properties
		$this->order_shipping       = $this->shipping->shipping_price + $this->shipping->shipping_extra;

		$this->shipping_method_id   = 0;
    }

    /**
     * Calculates the per_order coupon discount for the order
     * and the total post-tax/shipping discount
     * and sets order->order_discount
     *
     * @return unknown_type
     */
    function calculateDiscountTotals()
    {
    	$this->_taxes = J2StoreHelperCart::getTaxes();
		$session = JFactory::getSession();
		$tax = new J2StoreTax();
		JModelLegacy::addIncludePath( JPATH_SITE.'/components/com_j2store/models' );
		$model = JModelLegacy::getInstance('MyCart', 'J2StoreModel');
		$products = $model->getDataNew();

		if($session->has('coupon', 'j2store')) {
    		$coupon_info = J2StoreHelperCart::getCoupon($session->get('coupon', '', 'j2store'));

    		if ($coupon_info) {
    			$discount_total = 0;

    			if (!$coupon_info->product) {
    				$sub_total =J2StoreHelperCart::getSubTotal();
    			} else {
    				$sub_total = 0;
    				foreach ($products as $product) {
    					if (in_array($product['product_id'], $coupon_info->product)) {
    						$sub_total += $product['total'];
    					}
    				}
    			}

    			if ($coupon_info->value_type == 'F') {
    				$coupon_info->value = min($coupon_info->value, $sub_total);
    			}

    			//maximum value restriction. If set then we need to check
    			if ($coupon_info->value_type == 'P' && !empty($coupon_info->max_value) && (float) $coupon_info->max_value > 0) {
    				//calculate the actual discount
    				require_once (JPATH_SITE.'/components/com_j2store/models/mycart.php');
    				$cart_model = new J2StoreModelMyCart();
    				$actual_discount = $cart_model->getDiscountTotal();
    				//is the actual discount greater than the max value
    				if($actual_discount > 0 && $actual_discount > (float) $coupon_info->max_value) {

    					//set the coupon to be of fixed value
    					$coupon_info->value = (float) $coupon_info->max_value;
    					$coupon_info->value_type = 'F';
    				}
    			}

    			foreach ($products as $product) {
    				$discount = 0;

    				if (!$coupon_info->product) {
    					$status = true;
    				} else {
    					if (in_array($product['product_id'], $coupon_info->product)) {
    						$status = true;
    					} else {
    						$status = false;
    					}
    				}

    				if ($status) {
    					if ($coupon_info->value_type == 'F') {
    						$discount = $coupon_info->value * ($product['total'] / $sub_total);
    					} elseif ($coupon_info->value_type == 'P') {
    						$discount = $product['total'] / 100 * $coupon_info->value;
    					}

    					if ($product['tax_profile_id']) {

    						$tax_rates = $tax->getRateArray($product['total'] - ($product['total'] - $discount), $product['tax_profile_id']);
    						foreach ($tax_rates as $tax_rate) {
    							//	if ($tax_rate['value_type'] == 'P') {
    							$this->_taxes[$tax_rate['taxrate_id']] -= $tax_rate['amount'];
    							//	}
    						}
    					}
    				}

    				$discount_total += $discount;
    			}
    		}
    	}

        // store the total amount of the discount
        //set the total as equal to the order_subtotal + order_tax if its greater than the sum of the two
        $this->order_discount = $discount_total > ($this->order_subtotal + $this->order_tax) ? $this->order_subtotal + $this->order_tax : $discount_total;

    }

	  function getItems()
    {
        // TODO once all references use this getter, we can do fun things with this method, such as fire a plugin event
        JModelLegacy::addIncludePath( JPATH_SITE.DS.'components'.DS.'com_j2store'.DS.'models' );
        // if empty($items) && !empty($this->order_id), then this is an order from the db,
        // so we grab all the orderitems from the db
        if (empty($this->_items) && !empty($this->id))
        {
            // TODO Do this?  How will this impact Site::J2StoreControllerCheckout->saveOrderItems()?
            //retrieve the order's items
            $model = JModelLegacy::getInstance( 'OrderItems', 'J2StoreModel' );
            $model->setState( 'filter_orderid', $this->order_id);
            $model->setState( 'order', 'tbl.orderitem_name' );
            $model->setState( 'direction', 'ASC' );
            $orderitems = $model->getList();
            foreach ($orderitems as $orderitem)
            {
                unset($table);
                $table = JTable::getInstance( 'OrderItems', 'Table' );
                $table->load( $orderitem->orderitem_id );
                $this->addItem( $table );
            }
        }

        $items= $this->_items;
        if (!is_array($items))
        {
            $items = array();
        }
        $this->_items = $items;
        return $this->_items;
    }


    function getOrderTax() {

    	JModelLegacy::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/models' );
    	$ordertaxes = array();
    	if (!empty($this->id))
    	{
    		//retrieve the order tax
    		$model = JModelLegacy::getInstance( 'OrderTax', 'J2StoreModel' );
    		$model->setState( 'filter_orderid', $this->order_id);
    		//$model->setState( 'order', 'tbl.orderitem_name' );
    		$model->setState( 'direction', 'ASC' );
    		$ordertaxes = $model->getList();
    	}
    	return $ordertaxes;
    }

     function getInvoiceNumber( $refresh=false )
    {
        if (empty($this->_order_number) || $refresh)
        {
            $nullDate   = $this->_db->getNullDate();
            if (empty($this->created_date) || $this->created_date == $nullDate)
            {
                $date = JFactory::getDate();
                $this->created_date = $date->toSql();
            }
            $order_date = JHTML::_('date', $this->created_date, '%Y%m%d');
            $order_time = JHTML::_('date', $this->created_date, '%H%M%S');
            $user_id = $this->user_id;
            $this->_order_number = $order_date.'-'.$order_time.'-'.$user_id;
        }

        return $this->_order_number;
    }

      /**
     * Gets the order's shipping total object
     *
     * @return object
     */
    function getShippingTotal( $refresh=false )
    {
    	return $this->_shipping_total;
    }

    function save($src=array(), $orderingFilter = '', $ignore = '')
	{
        if ($return = $this->saveBase())
        {
            // create the order_number when the order is saved
            if (empty($this->order_id) && !empty($this->id))
            {
                $this->order_id = time();
                $this->store();
            }

            // TODO All of the protected vars information could be saved here instead, no?
        }
        return $return;
	}


	/**
	 * Gets the order's shipping geozones
	 *
	 * @return unknown_type
	 */
	function getShippingGeoZones()
	{
		return $this->_shipping_geozones;
	}


	function setAddress($override='no') {
		$session = JFactory::getSession();
		$address = $this->getStoreAddress();
		if ($session->has('shipping_country_id', 'j2store') || $session->has('shipping_zone_id', 'j2store')) {
			$this->setShippingAddress($session->get('shipping_country_id', '', 'j2store'), $session->get('shipping_zone_id', '', 'j2store'), $session->get('shipping_postcode', '', 'j2store'));
		} else {
		//	$this->setShippingAddress($address->country_id, $address->zone_id, $address->store_zip);
		}

		if ($session->has('billing_country_id', 'j2store') || $session->has('billing_zone_id', 'j2store')) {
			$this->setBillingAddress($session->get('billing_country_id', '', 'j2store'), $session->get('billing_zone_id', '', 'j2store'));
		} else {
			$this->setBillingAddress($address->country_id, $address->zone_id);
		}
		$this->setStoreAddress($address->country_id, $address->zone_id);
		//address override
		if($override == 'store') {
			$this->setShippingAddress($address->country_id, $address->zone_id, $address->store_zip);
		}

		$this->setGeozones();

		}

		function getStoreAddress() {

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__j2store_storeprofiles');
			$query->where('state=1');
			$query->order('store_id ASC LIMIT 1');
			$db->setQuery($query);
			$item	=	$db->loadObject();
			return $item;
		}


	/**
	 * Based on the object's addresses,
	 * sets the shipping and billing geozones
	 *
	 * @return unknown_type
	 */
	function setGeozones( $geozones=null, $type='billing' )
	{
		if (!empty($geozones))
		{
			switch ($type)
			{
				case "shipping":
					default:
					$this->_shipping_geozones = $geozones;
					break;
				case "billing":
					$this->_billing_geozones = $geozones;
					break;
			}
		}
		else
		{
			require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/shipping.php');
			if (!empty($this->_billing_address))
			{
				$this->_billing_geozones = $this->getGeoZones( $this->_billing_address['country_id'], $this->_billing_address['zone_id'], '1');
			}
			if (!empty($this->_shipping_address))
			{
				$this->_shipping_geozones = $this->getGeoZones( $this->_shipping_address['country_id'], $this->_shipping_address['zone_id'], '2', $this->_shipping_address['postal_code'] );
			}
		}
	}


	public function setShippingAddress($country_id, $zone_id, $postal_code) {
		$this->_shipping_address = array(
				'country_id' => $country_id,
				'zone_id'    => $zone_id,
				'postal_code'    => $postal_code
		);
	}

	public function setBillingAddress($country_id, $zone_id) {
		$this->_billing_address = array(
				'country_id' => $country_id,
				'zone_id'    => $zone_id
		);
	}

	public function setStoreAddress($country_id, $zone_id) {
		$this->_store_address = array(
				'country_id' => $country_id,
				'zone_id'    => $zone_id
		);
	}

	/**
	 * Gets the order billing address
	 * @return unknown_type
	 */
	function getBillingAddress()
	{
		// TODO If $this->_billing_address is null, attempt to populate it with the orderinfo fields, or using the billing_address_id (if present)
		return $this->_billing_address;
	}

	/**
	 * Gets the order shipping address
	 * @return unknown_type
	 */
	function getShippingAddress()
	{
		// TODO If $this->_shipping_address is null, attempt to populate it with the orderinfo fields, or using the shipping_address_id (if present)
		return $this->_shipping_address;
	}



	public function getGeoZones( $country_id, $zone_id, $geozonetype='2', $zip_code = null, $update = false )
	{
		$return = array();
		if (empty($zone_id) && empty($country_id))
		{
			return $return;
		}

		static $geozones = null; // static array for caching results
		if( $geozones === null )
			$geozones = array();

		if( $zip_code === null )
			$zip_code = 0;

		if( isset( $geozones[$geozonetype][$zone_id][$zip_code] ) && !$update )
			return $geozones[$geozonetype][$zone_id][$zip_code];


		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('gz.*,gzr.*')->from('#__j2store_geozones AS gz')
			->leftJoin('#__j2store_geozonerules AS gzr ON gzr.geozone_id = gz.geozone_id')
			->where('gzr.country_id='.$db->q($country_id).' AND (gzr.zone_id=0 OR gzr.zone_id='.$db->q($zone_id).')');

		if($zip_code)
		{
			//TODO add filter by postcode
		}
		$db->setQuery($query);
		$items = $db->loadObjectList();

		if (!empty($items))
		{
			$return = $items;
		}
		$geozones[$geozonetype][$zone_id][$zip_code] = $return;
		return $return;
	}

	function setOrderInfo() {

		$user = JFactory::getUser();
		$session = JFactory::getSession();

		require_once (JPATH_SITE.'/components/com_j2store/models/address.php');
		$address_model = new J2StoreModelAddress();

		$billing_address = array();

		if ($user->id && $session->has('billing_address_id', 'j2store')) {
			$billing_address = $address_model->getAddress($session->get('billing_address_id', '', 'j2store'));
		} elseif ($session->has('guest', 'j2store')) {
			$guest = $session->get('guest', array(), 'j2store');
			$billing_address = $guest['billing'];
		}

		$shipping_address = array();

		if($user->id && $session->has('shipping_address_id', 'j2store')) {
			$shipping_address = $address_model->getAddress($session->get('shipping_address_id', '', 'j2store'));
		} elseif($session->has('guest', 'j2store')) {
			$guest = $session->get('guest', array(), 'j2store');
			if($guest['shipping']) {
				$shipping_address = $guest['shipping'];
			}

		}

		$values= array();
		if(count($billing_address)) {
			foreach ($billing_address as $key=>$value) {
				$values['billing_'.$key] = $value;
			}
		}

		if(count($shipping_address)) {
			foreach ($shipping_address as $key=>$value) {
				$values['shipping_'.$key] = $value;
			}
		}

		$orderInfo = JTable::getInstance('OrderInfo', 'Table');
		$orderInfo->bind($values);
		$this->_order_info = $orderInfo;
	}

	function getOrderInfo() {

		return $this->_order_info;
	}


	function saveBase()
	{


		$this->_isNew = false;
		$key = $this->getKeyName();
		if (empty($this->$key))
		{
			$this->_isNew = true;
		}

		if ( !$this->check() )
		{
			return false;
		}
		if ( !$this->store() )
		{
			return false;
		}

		if ( !$this->checkin() )
		{
			$this->setError( $this->_db->stderr() );
			return false;
		}

		$this->setError('');

		return true;
	}

function store($updateNulls = false) {

	if ($return = parent::store($updateNulls = false))
	{
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin ('j2store');
		$dispatcher->trigger( "onJ2StoreAfterOrder", array( $this->id));
	}
	return true;
}

}

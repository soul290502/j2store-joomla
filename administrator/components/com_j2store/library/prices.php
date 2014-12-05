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

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/base.php');
class J2StorePrices
{
	protected static $_product = array();

	public static function getPrice( $id, $quantity = '1')
	{
		// $sets[$id][$quantity][$group_id][$date]
		static $sets;

		if ( !is_array( $sets ) )
		{
			$sets = array( );
		}

		$price = null;
		if ( empty( $id ) )
		{
			return $price;
		}

		$user = JFactory::getUser();
		$groups = implode(',',$user->getAuthorisedGroups());

		if ( !isset( $sets[$id][$quantity][$groups] ) )
		{

			( int ) $quantity;
			if ( $quantity <= '0' )
			{
				$quantity = '1';
			}

			$product = J2StorePrices::getJ2Product($id);

			$item = new JObject;
			//1. base price
			$item->product_price = $product->item_price;
			$item->product_baseprice = $product->item_price;

			//2. special/offer price if any
			if(isset($product->special_price) && $product->special_price > 0){
				$item->product_specialprice = (float) $product->special_price;
				$item->product_price =(float) $product->special_price;
			}

			//3. price based on the group

			$group_price=J2StorePrices::getGroupPrice($id);
			if(isset($group_price) && ($group_price)){
				$item->product_customer_groupprice = $group_price;
				//we have a group price for this product. So set the special price to null.
				$item->product_specialprice = null;
				// this will be the product price as well as the base price
				$item->product_baseprice = $item->product_price =(float) $group_price;
			}

			//3. price range based on the date and the quantity
			$price_range = J2StorePrices::getPriceRange($id,$quantity);
			if( $price_range > 0.000){
				$item->product_price = $price_range;
			}

			$sets[$id][$quantity][$groups] = $item;
		}

		return $sets[$id][$quantity][$groups];
	}


	/**
	 *
	 * @return unknown_type
	 */
	public static function getItemPrice(&$id)
	{
		$item=null;
		$item = J2StorePrices::getJ2Product($id);
		if(!empty($item))
			return $item->item_price;
		else
			return null;
	}

	public static function getSpecialPrice($id)
	{
		$item=null;
		$item = J2StorePrices::getJ2Product($id);
		if(!empty($item) && isset($item->special_price))
			return $item->special_price;
		else
			return null;
	}


	public static function getPriceRange($product_id,$quantity)
	{
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$date= $db->Quote(JFactory::getDate()->toSql());

		$query->select('pr.price');
		$query->from('#__j2store_productprices as pr');
		$query->where('pr.product_id='.$product_id);
		$query->where('pr.quantity_start <='.$quantity);
		$query->order('pr.quantity_start DESC');
		$db->setQuery($query);
		$price_range=$db->loadResult();
		return $price_range;
	}


	public static function getItemEnabled(&$id)
	{
		$item=null;
		$item = J2StorePrices::getJ2Product($id);

		if(!empty($item))
			return $item->product_enabled;
		else
			return null;
	}

/*
	public static function number($amount, $options='')
    {
        // default to whatever is in config
		$config = JComponentHelper::getParams('com_j2store');
        $options = (array) $options;
        $post = '';
        $pre = '';

        $default_currency = $config->get('currency_code', 'USD');
        $num_decimals = isset($options['num_decimals']) ? $options['num_decimals'] : $config->get('currency_num_decimals', '2');
        $thousands = isset($options['thousands']) ? $options['thousands'] : $config->get('currency_thousands', ',');
        $decimal = isset($options['decimal']) ? $options['decimal'] : $config->get('currency_decimal', '.');
        $currency_symbol = isset($options['currency']) ? $options['currency'] : $config->get('currency', '$');
        $currency_position = isset($options['currency_position']) ? $options['currency_position'] : $config->get('currency_position', 'pre');
        if($currency_position == 'post') {
			$post = $currency_symbol;
		} else {
			$pre = $currency_symbol;
		}

        $return = $pre.number_format($amount, $num_decimals, $decimal, $thousands).$post;

        return $return;
    }
*/
	public static function number($amount, $currency = '', $value = '', $format = true) {
		if($value == 0) {
			$value = '';
		}
		//backward compatibility
		if(is_array($currency)) {
			$currency = '';
		}

		$currencyobject = J2StoreFactory::getCurrencyObject();
		return $currencyobject->format($amount, $currency, $value, $format);
	}

	public static function getJ2Product($id) {

			if(!isset(self::$_product[$id])) {
				$db		= JFactory::getDbo();
				$query	= $db->getQuery(true);
				$query->select('*');
				$query->from('#__j2store_prices as a');
				$query->where('a.article_id='.$id);
				$db->setQuery($query);
				self::$_product[$id] = $db->loadObject();
			}

		return self::$_product[$id];
	}

	public static function _getJ2Item($id) {
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);

			//$query->select('article_id,item_price,item_tax,item_shipping');
			$query->select('*');
			$query->from('#__j2store_prices as a');
			$query->where('a.article_id='.$id);
			$db->setQuery($query);
			$item=$db->loadObject();
		return $item;
	}


	public static function getTaxProfileId($product_id){
		$item = J2StorePrices::getJ2Product($product_id);
		return $item->item_tax;
	}

	public static function getGroupPrice($product_id){
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$groups = implode(',',$user->getAuthorisedGroups());
		$query = $db->getQuery(true)->select("gp.customer_group_price")->from("#__j2store_groupprices as gp")
		->where("gp.product_id=".$product_id)
		->where("gp.customer_group_id IN ({$groups})");
		//echo $query;
		$db->setQuery($query);
		$result = $db->loadResult();
		return $result;
	}
}


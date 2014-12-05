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

//class to manage inventory

// no direct access
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/version.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/j2item.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/version.php');
JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
require_once (JPATH_SITE.'/components/com_j2store/models/mycart.php');
class J2StoreInventory {

public static function setInventory($orderpayment_id, $order_state_id) {

		//only reduce the inventory if the order is successful. 1==CONFIRMED.
		//do it only once.
		$app = JFactory::getApplication();
		JPluginHelper::importPlugin ('j2store');

		if($order_state_id == 1) {

			require_once(JPATH_SITE.'/components/com_j2store/models/orders.php');
			$model =  new J2StoreModelOrders();
			//lets set the id first
			$model->setId($orderpayment_id);

			$orderTable = $model->getTable( 'orders' );
			$orderTable->load( $model->getId() );
			$order = $model->getItem();

			//trigger the plugin
			$app->triggerEvent( "onJ2StoreBeforeInventory", array($order->id));
			//Do it once and set that the stock is adjusted
			if($order->stock_adjusted != 1 && is_array($order->orderitems)) {
				foreach($order->orderitems as $item) {
					J2StoreInventory::updateProductQuantities($item);
				}
				$orderTable->stock_adjusted = 1;
				$orderTable->store();
				//trigger the plugin
				$app->triggerEvent( "onJ2StoreAfterInventory", array($orderTable->id) );

			}
		} else {
			return;
		}
		return;
	}

public static function updateProductQuantities($item, $delta='-') {

		$productQuantities = JTable::getInstance('ProductQuantities', 'Table');
		$productQuantities->load( array('product_id'=>$item->product_id, 'product_attributes'=>$item->attributes_csv), true);

		$productsTable = JTable::getInstance( 'Prices', 'Table' );
		$productsTable->load(array('article_id'=>$item->product_id));
		// Check if it has inventory enabled
		if (!$productsTable->manage_stock  || empty($productQuantities->product_id))
		{
			return;
		}

		switch ($delta)
		{
			case "+":
				$new_quantity = $productQuantities->quantity + $item->orderitem_quantity;
				break;
			case "-":
			default:
				$new_quantity = $productQuantities->quantity - $item->orderitem_quantity;
				break;
		}

		// no product made infinite accidentally
		if ($new_quantity < 0)
		{
			$new_quantity = 0;
		}

		$productQuantities->quantity = $new_quantity;
		$productQuantities->save();
		return true;
	}

	/**
	 * @deprecated This method has been depricated since 2.6.12
	 * @param number $product_id
	 * @param number $qty
	 * @return boolean
	 */

	public static function validateStock($product_id, $qty=1) {

		$params = JComponentHelper::getParams('com_j2store');

		//if inventory is not enabled, return true
		if(!$params->get('enable_inventory', 0)) {
			return true;
		}

		//if backorder is allowed, dont check anything. just return true
	//	if($params->get('allow_backorder', 0)) {
	//		return true;
	//	}

		if(J2STORE_PRO != 1) {
			return true;
		}

		$stock = J2StoreInventory::getStock($product_id);

		//if manage stock is set to no, then return true. we dont need to track inventory for this item.

		if((int)$stock->manage_stock < 1) {
			return true;
		}

		//if stock has reached the min out qty
		if($stock->quantity <= $stock->min_out_qty) {
			return false;
		}

		//if stock has reached the min out qty
		if($qty > $stock->quantity) {
			return false;
		}

		if($stock->quantity < 0) {
			return false;
		}

		return true;
	}

	public static function getStock($product_id) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__j2store_prices');
		$query->where('article_id='.$db->quote($product_id));
		$db->setQuery($query);
		$stock = $db->loadObject();
		if(!isset($stock) || J2STORE_PRO != 1 ) {
			$stock = JTable::getInstance('Prices', 'Table');
		}

		//prepare data. We may have some settings in the store global
		require_once(JPATH_SITE.'/components/com_j2store/helpers/cart.php');

		$store_config = J2StoreHelperCart::getStoreAddress();

		if($stock->use_store_config_min_out_qty > 0) {
			$stock->min_out_qty = (float) $store_config->store_min_out_qty;
		}

		if($stock->use_store_config_min_sale_qty > 0) {
			$stock->min_sale_qty = (float) $store_config->store_min_sale_qty;
		}

		if($stock->use_store_config_max_sale_qty > 0) {
			$stock->max_sale_qty = (float) $store_config->store_max_sale_qty;
		}

		if($stock->use_store_config_notify_qty > 0) {
			$stock->notify_qty = (float) $store_config->store_notify_qty;
		}

		return $stock;
	}

	public static function isAllowed($item, $qty=1, $attributes_csv='') {

		$params = JComponentHelper::getParams('com_j2store');
		//set the result object
		$result = new JObject();
		$result->backorder = false;
		//we always want to allow users to buy. so initialise to 1.
		$result->can_allow = 1;

		//if basic version return true
		if(J2STORE_PRO != 1) {
			$result->can_allow = 1;
			return $result;
		}

		//first check if global inventory is enabled.

		if(!$params->get('enable_inventory', 0)) {
			//if not enabled, allow adding and return here
			$result->can_allow = 1;
			return $result;
		}
		//global inventory enabled. Now check at the product level.
		if(is_object($item->product->stock)) {
			$stock = $item->product->stock;
		} else {
			$stock = J2StoreInventory::getStock($item->product_id);
		}

		if((int)$stock->manage_stock < 1) {
			//Stock disabled. Return true
			$result->can_allow = 1;
			return $result;
		}

		if(empty($attributes_csv)) {
			$model =  new J2StoreModelMyCart();
			//get attributes
			$attributes = $model->getProductOptions($item->product_id);
			$list = array( );
			foreach ($attributes as $option) {

				if($option['type'] == 'select' || $option['type'] == 'radio' ) {
					$option['product_option_id'];
					//get the default attributes
					foreach($option['optionvalue'] as $optionvalue) {
						if($optionvalue['product_optionvalue_default']) {
							//we have a default option
							$list[$option['product_option_id']] = $optionvalue['product_optionvalue_id'];
						}
					}
					if(empty($list[$option['product_option_id']])) {
						$list[$option['product_option_id']] = $option['optionvalue'][0]['product_optionvalue_id'];
					}
				}
			}
			sort( $list);
			$attributes_csv = implode( ',', $list);
		}

		$availableQuantity = J2StoreInventory::getAvailableQuantity( $item->product_id, $attributes_csv );
		if ( $availableQuantity->manage_stock && $qty > $availableQuantity->quantity )
		{
			$result->can_allow = 0;
		}

		$quantity_min = 1;
		if ($stock->min_out_qty)
		{
			$quantity_min = $stock->min_out_qty;
		}

		if($quantity_min >= $availableQuantity->quantity) {
			$result->can_allow = 0;
		}

		if($availableQuantity->quantity <= 0) {
			$result->can_allow = 0;
		}

		//TODO:: is there a better way of doing this. We are checking the product's total stock
		if($item->product_stock > 0 ) {
			$result->can_allow = 1;
		} else {
			$result->can_allow = 0;
		}

		//if backorder is allowed, set it and override to allow adding
		if($params->get('enable_inventory', 0) && $params->get('allow_backorder', 0)) {
			$result->backorder = true;
		}

		return $result;
	}


	public static function validateQuantityRestrictions($products) {

		if(J2STORE_PRO != 1) return true;
		
		$error = '';
		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

				//validate only if it is set
				if(isset($product['stock']->min_sale_qty) && $product['stock']->min_sale_qty > 0) {
					if ($product['stock']->min_sale_qty > $product_total) {
						$error .= JText::sprintf('J2STORE_MINIMUM_QUANTITY_REQUIRED', $product['name'], (int) $product['stock']->min_sale_qty, $product_total );

					}
				}

				if(isset($product['stock']->max_sale_qty) && $product['stock']->max_sale_qty > 0) {
					if ($product_total > $product['stock']->max_sale_qty ) {
						$error .=  JText::sprintf('J2STORE_MAXIMUM_QUANTITY_WARNING', $product['name'], (int) $product['stock']->max_sale_qty, $product_total);
					}
				}
			}

		if(!empty($error)) {
			throw new Exception($error);
			return false;
		}

		return true;
	}

 public static function getAvailableQuantity( $id, $attribute )
    {

    	$db = JFactory::getDbo();
    	$query = $db->getQuery(true);
        $tableQuantity = JTable::getInstance( 'ProductQuantities', 'Table' );
        $tableProduct = JTable::getInstance( 'Prices', 'Table' );
		$J2Item = J2StoreItem::getArticle($id);

        $tableProduct->load(array('article_id'=>$id));
        if ( !isset( $tableProduct->manage_stock) ||  $tableProduct->manage_stock = 0)
        {
            $tableProduct->quantity = '9999';
            return $tableProduct;
        }

        $select[] = "quantities.quantity";
        $select[] = "price.manage_stock";

        $query->select( $select );
        $query->from( $tableProduct->getTableName( ) . " AS price" );

        $leftJoinCondition = $tableQuantity->getTableName( ) . " as quantities ON price.article_id = quantities.product_id ";
        $query->leftJoin( $leftJoinCondition );

        $whereClause[] = "quantities.product_id = " . ( int ) $id;
        $whereClause[] = "quantities.product_attributes='" . $attribute . "'";
        $whereClause[] = "price.manage_stock =1 ";
        $query->where( $whereClause, "AND" );

        $db = JFactory::getDBO( );
        $db->setQuery( ( string ) $query );
        $item = $db->loadObject( );

        if ( empty( $item ) )
        {
            $return = new JObject( );
            $return->product_id = $id;
            $return->product_name = $J2Item->title;
            $return->quantity = 0;
            $return->manage_stock = $tableProduct->manage_stock;
            return $return;
        }

        return $item;
    }

}
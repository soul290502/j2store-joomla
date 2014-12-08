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
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.filter.filterinput' );
jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
JLoader::register('J2StoreModel',  JPATH_ADMINISTRATOR.'/components/com_j2store/models/model.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/inventory.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/tax.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/version.php');
class J2StoreModelMyCart extends J2StoreModel {

	private $_product_id;
	private $_data = array();

	function __construct($config = array())
	{
		parent::__construct($config);
	}

	function getDataNew()
	{
		require_once (JPATH_SITE.'/components/com_j2store/helpers/cart.php');

		$session = JFactory::getSession();
		// Lets load the content if it doesn't already exist
		if (empty($this->_data) && count($session->get('j2store_cart')))
		{

			foreach ($session->get('j2store_cart') as $key => $quantity) {

				$product = explode(':', $key);
				$product_id = $product[0];
				$stock = true;

				// Options
				if (isset($product[1])) {
					$options = unserialize(base64_decode($product[1]));
				} else {
					$options = array();
				}

				//now get product details
				$product_info = J2StoreHelperCart::getItemInfo($product_id);

				//now get product options
				if($product_info) {
					$option_price = 0;
					$option_weight = 0;
					$option_data = array();

					foreach ($options as $product_option_id => $option_value) {

						$product_option = $this->getCartProductOptions($product_option_id , $product_id);

						if ($product_option) {
							if ($product_option->type == 'select' || $product_option->type == 'radio') {

								//ok now get product option values
								$product_option_value = $this->getCartProductOptionValues($product_option->product_option_id, $option_value );

								if ($product_option_value) {

									//option price
									if ($product_option_value->product_optionvalue_prefix == '+') {
										$option_price += $product_option_value->product_optionvalue_price;
									} elseif ($product_option_value->product_optionvalue_prefix == '-') {
										$option_price -= $product_option_value->product_optionvalue_price;
									}

									//options weight
									if ($product_option_value->product_optionvalue_weight_prefix == '+') {
										$option_weight += $product_option_value->product_optionvalue_weight;
									} elseif ($product_option_value->product_optionvalue_weight_prefix == '-') {
										$option_weight -= $product_option_value->product_optionvalue_weight;
									}


									$option_data[] = array(
											'product_option_id'       => $product_option_id,
											'product_optionvalue_id' => $option_value,
											'option_id'               => $product_option->option_id,
											'optionvalue_id'         => $product_option_value->optionvalue_id,
											'name'                    => $product_option->option_name,
											'option_value'            => $product_option_value->optionvalue_name,
											'type'                    => $product_option->type,
											'price'                   => $product_option_value->product_optionvalue_price,
											'price_prefix'            => $product_option_value->product_optionvalue_prefix,
											'weight'                   => $product_option_value->product_optionvalue_weight,
											'option_sku'               => $product_option_value->product_optionvalue_sku,
											'weight_prefix'            => $product_option_value->product_optionvalue_weight_prefix
									);
								}
							} elseif ($product_option->type == 'checkbox' && is_array($option_value)) {
								foreach ($option_value as $product_optionvalue_id) {
									$product_option_value = $this->getCartProductOptionValues($product_option->product_option_id, $product_optionvalue_id);

								if ($product_option_value) {

									//option price
									if ($product_option_value->product_optionvalue_prefix == '+') {
										$option_price += $product_option_value->product_optionvalue_price;
									} elseif ($product_option_value->product_optionvalue_prefix == '-') {
										$option_price -= $product_option_value->product_optionvalue_price;
									}

									//option weight

									if ($product_option_value->product_optionvalue_weight_prefix == '+') {
										$option_weight += $product_option_value->product_optionvalue_weight;
									} elseif ($product_option_value->product_optionvalue_weight_prefix == '-') {
										$option_weight -= $product_option_value->product_optionvalue_weight;
									}

										$option_data[] = array(
												'product_option_id'       => $product_option_id,
												'product_optionvalue_id' => $product_optionvalue_id,
												'option_id'               => $product_option->option_id,
												'optionvalue_id'         => $product_option_value->optionvalue_id,
												'name'                    => $product_option->option_name,
												'option_value'            => $product_option_value->optionvalue_name,
												'type'                    => $product_option->type,
												'price'                   => $product_option_value->product_optionvalue_price,
												'price_prefix'            => $product_option_value->product_optionvalue_prefix,
												'weight'                   => $product_option_value->product_optionvalue_weight,
												'option_sku'                => $product_option_value->product_optionvalue_sku,
												'weight_prefix'            => $product_option_value->product_optionvalue_weight_prefix
											);
									}
								}
							} elseif ($product_option->type == 'text' || $product_option->type == 'textarea' || $product_option->type == 'date' || $product_option->type == 'datetime' || $product_option->type == 'time') {
								$option_data[] = array(
										'product_option_id'       => $product_option_id,
										'product_optionvalue_id' => '',
										'option_id'               => $product_option->option_id,
										'optionvalue_id'         => '',
										'name'                    => $product_option->option_name,
										'option_value'            => $option_value,
										'type'                    => $product_option->type,
										'price'                   => '',
										'price_prefix'            => '',
										'weight'                   => '',
										'weight_prefix'            => ''
								);
							}
						}
					}

					//get the product price

					//base price
					$price = $product_info->price;

					//we may have special price or discounts. so check
					$price_override = J2StorePrices::getPrice($product_info->product_id, $quantity);

					if(isset($price_override) && !empty($price_override)) {
						$price = $price_override->product_price;
					}

					$this->_data[$key] = array(
							'key'             => $key,
							'product_id'      =>  $product_info->product_id,
							'name'            =>  $product_info->product_name,
							'model'           =>  $product_info->product_sku,
							'raw_options'          => $options,
							'option'          => $option_data,
							'option_price'    => $option_price,
							'quantity'        => $quantity,
							'stock'			  => $product_info->stock,
							'tax_profile_id'  => $product_info->tax_profile_id,
							'shipping' 		  => $product_info->item_shipping,
							'price'           => ($price + $option_price),
							'total'           => ($price + $option_price) * $quantity,
							'weight'          => ($product_info->item_weight + $option_weight),
							'weight_total'    => ($product_info->item_weight + $option_weight) * $quantity,
							'option_weight'   => ($option_weight * $quantity),
							'weight_class_id' => $product_info->item_weight_class_id,
							'length'          => $product_info->item_length,
							'width'           => $product_info->item_width,
							'height'          => $product_info->item_height,
							'length_class_id' => $product_info->item_length_class_id

					);

				} // end of product info if
				else {
					$this->remove($key);
				}
			}
		}
		return $this->_data;
	}

	 public function getShippingIsEnabled()
    {
	   	$model = JModelLegacy::getInstance( 'MyCart', 'J2StoreModel');
		$list = $model->getDataNew();

    	// If no item in the list, return false
        if ( empty( $list ) )
        {
          	return false;
        }

        require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/j2item.php');
        $product_helper = new J2StoreItem();
        foreach ($list as $item)
        {
           	$shipping = $product_helper->isShippingEnabled($item['product_id']);
        	if ($shipping)
        	{
        	    return true;
        	}
        }

        return false;
    }

    function getProductOptions($product_id) {

    	static $osets;

    	if ( !is_array( $osets) )
    	{
    		$osets = array( );
    	}
    	if ( !isset( $osets[$product_id])) {

	    	//first get the product options
	    	$db = JFactory::getDbo();
	    	$product_option_data = array();
	    	$query = $db->getQuery(true);
	    	$query->select('po.*');
	    	$query->from('#__j2store_product_options AS po');
	    	$query->where('po.product_id='.$product_id);

	    	//join the options table to get the name
	    	$query->select('o.option_name, o.type');
	    	$query->join('LEFT', '#__j2store_options AS o ON po.option_id=o.option_id');
	    	$query->where('o.state=1');
	    	$query->order('po.product_option_id ASC');

	    	$db->setQuery($query);
	    	$product_options = $db->loadObjectList();
			//now prepare to get the product option values
	    	foreach($product_options as $product_option) {

	    		//if multiple choices available, then we got to get them
	    		if ($product_option->type == 'select' || $product_option->type == 'radio' || $product_option->type == 'checkbox') {

	    			$product_option_value_data = array();

	    			$product_option_values = $this->getProductOptionValues($product_option->product_option_id, $product_option->product_id);

	    			foreach ($product_option_values as $product_option_value) {
	    				if(!isset($product_option_value->product_optionvalue_default)) {
	    					$product_option_value->product_optionvalue_default = '';
	    				}
	    				$product_option_value_data[] = array(
	    						'product_optionvalue_id' 		=> $product_option_value->product_optionvalue_id,
	    						'optionvalue_id'         		=> $product_option_value->optionvalue_id,
	    						'optionvalue_name'       		=> $product_option_value->optionvalue_name,
	    						'product_optionvalue_price' 	=> $product_option_value->product_optionvalue_price,
	    						'product_optionvalue_prefix'	=> $product_option_value->product_optionvalue_prefix,
	    						'product_optionvalue_weight' 	=> $product_option_value->product_optionvalue_weight,
	    						'product_optionvalue_sku' 	=> $product_option_value->product_optionvalue_sku,
	    						'product_optionvalue_weight_prefix'	=> $product_option_value->product_optionvalue_weight_prefix,
	    						'product_optionvalue_default'	=> $product_option_value->product_optionvalue_default
	    				);

	    			}

	    			$product_option_data[] = array(
	    					'product_option_id' => $product_option->product_option_id,
	    					'option_id'         => $product_option->option_id,
	    					'option_name'		=> $product_option->option_name,
	    					'type'              => $product_option->type,
	    					'optionvalue'       => $product_option_value_data,
	    					'required'          => $product_option->required
	    			);

	    		} else {

	    			//if no option values are present, then
	    			$product_option_data[] = array(
	    					'product_option_id' => $product_option->product_option_id,
	    					'option_id'         => $product_option->option_id,
	    					'option_name'		=> $product_option->option_name,
	    					'type'              => $product_option->type,
	    					'optionvalue'       => '',
	    					'required'          => $product_option->required
	    			);
	    		} //endif
	    	} //end product option foreach
		$osets[$product_id] = $product_option_data;
    	}
    	return $osets[$product_id];
    }

    function getProductOptionValues($product_option_id, $product_id) {

    	static $ovsets;

    	if ( !is_array( $ovsets) )
    	{
    		$ovsets = array( );
    	}
    	if ( !isset( $ovsets[$product_option_id][$product_id])) {
	    	//first get the product options
	    	$db = JFactory::getDbo();
	    	$query = $db->getQuery(true);
	    	$query->select('pov.*');
	    	$query->from('#__j2store_product_optionvalues AS pov');
	    	$query->where('pov.product_id='.$product_id);
	    	$query->where('pov.product_option_id='.$product_option_id);

	    	//join the optionvalues table to get the name
	    	$query->select('ov.optionvalue_id, ov.optionvalue_name');
	    	$query->join('LEFT', '#__j2store_optionvalues AS ov ON pov.optionvalue_id=ov.optionvalue_id');
	    	$query->order('pov.ordering ASC');

	    	$db->setQuery($query);
	    	$ovsets[$product_option_id][$product_id] = $db->loadObjectList();
    	}

    	return $ovsets[$product_option_id][$product_id];
    }

    function getCartProductOptions($product_option_id, $product_id) {
    	static $posets;

    	if ( !is_array( $posets) )
    	{
    		$posets = array( );
    	}
    	if ( !isset( $posets[$product_option_id][$product_id])) {
	    	$db = JFactory::getDbo();
	    	$query = $db->getQuery(true);
	    	$query->select('po.*');
	    	$query->from('#__j2store_product_options AS po');
	    	$query->where('po.product_option_id='.$product_option_id);
	    	$query->where('po.product_id='.$product_id);

	    	//join the options table to get the name
	    	$query->select('o.option_name, o.type');
	    	$query->join('LEFT', '#__j2store_options AS o ON po.option_id=o.option_id');
	    	$query->order('o.ordering ASC');
	    	$db->setQuery($query);

	    	$product_option = $db->loadObject();
	    	$posets[$product_option_id][$product_id] = $product_option;
    	}
    return $posets[$product_option_id][$product_id];
    }

    function getCartProductOptionValues($product_option_id, $option_value ) {

    	static $ovsets;

    	if ( !is_array( $ovsets) )
    	{
    		$ovsets = array( );
    	}
    	if ( !isset( $ovsets[$product_option_id][$option_value])) {
	    	//first get the product options
	    	$db = JFactory::getDbo();
	    	$query = $db->getQuery(true);
	    	$query->select('pov.*');
	    	$query->from('#__j2store_product_optionvalues AS pov');
	    	$query->where('pov.product_optionvalue_id='.$option_value);
	    	$query->where('pov.product_option_id='.$product_option_id);

	    	//join the optionvalues table to get the name
	    	$query->select('ov.optionvalue_id, ov.optionvalue_name');
	    	$query->join('LEFT', '#__j2store_optionvalues AS ov ON pov.optionvalue_id=ov.optionvalue_id');
	    	$query->order('pov.ordering ASC');

	    	$db->setQuery($query);
	    	$ovsets[$product_option_id][$option_value] = $db->loadObject();
    	}
    	return $ovsets[$product_option_id][$option_value];
    }


    public function update($key, $qty) {
    	$cart = JFactory::getSession()->get('j2store_cart');
    	if ((int)$qty && ((int)$qty > 0)) {
    		$cart[$key] = (int)$qty;
    	} else {
    		$this->remove($key);
    	}
    	JFactory::getSession()->set('j2store_cart', $cart);
    	$this->_data = array();
    }

    public function remove($key) {
    	$cart = JFactory::getSession()->get('j2store_cart');
    	if (isset($cart[$key])) {
    		unset($cart[$key]);
    	}
    	JFactory::getSession()->set('j2store_cart', $cart);
    	$this->_data = array();
    }

    public function removeCoupon() {
    	$session = JFactory::getSession();
    	if($session->has('coupon', 'j2store')) {
    		$session->set('coupon', '', 'j2store');
    	}
    }

    public function clear() {
    	JFactory::getSession()->set('j2store_cart', array());
    	$this->_data = array();
    }


    public function hasStock() {

    	$session = JFactory::getSession();
    	$result = new JObject();
    	$result->status = true;
    	$result->redirect = false;
    	$no_stock = array();

    	if(J2STORE_PRO == 0) {
    		return $result;
    	}

    	$products = $this->getDataNew();
    	$allOptions = $this->getAllAttributes($products);
    	foreach($products as $product) {
    		$attribs = $existingOptions = $this->getExistingOptions($product);
    		if($attribs) {
				sort($attribs);
				$attribute_csv = implode(',', $attribs);
				$qty = $allOptions[$attribute_csv];
    		} else {
    			$qty = $product['quantity'];
    		}
    		if(!$this->hasProductStock($product['product_id'], $existingOptions, $qty, $product['key'], true, $type="")) {
    			$no_stock[]=$product['key'];

    		}
    	}
    	if(count($no_stock)) {
    		foreach($no_stock as $key) {
    			$this->remove($key);
    		}
    	//	$result->status=false;
    	//	$result->redirect = true;
    	}
    	return $result;
    }

    function getExistingOptions($product) {
    	//prepare options
    	$existingOptions = array();
    	foreach($product['option'] as $option_data) {
    		if($option_data['type'] == 'select' || $option_data['type'] == 'radio' ) {
    			$existingOptions[$option_data['product_option_id']] = $option_data['product_optionvalue_id'];
    		}
    	}
		return $existingOptions;
    }

    public function hasProductStock($product_id,  $options=array(), $qty=0, $key='', $isTotal=false, $type="update", $original_qty=0) {
    	$params = JComponentHelper::getParams('com_j2store');
    	$status = true;
		$inventory = new J2StoreInventory();
    	//check if backorder is enabled. If yes, then return true
    	if($params->get('allow_backorder', 0)) {
    		return $status;
    	}

    	//get the existing cart items
    	$products = $this->getDataNew();

    	//get existing items in a CSV format
    	$allOptions = $this->getAllAttributes($products);

    	if($type == 'add' || $type == 'update') {
    		//this request either comes from adding a product to cart or update
    		if(count($options)) {
				$options = $this->cleanAttributes($options, $product_id);
    		}
    	}

    	$attributes_csv = '';
    	if(count($options)) {
    		$attribs = $options;
    		sort($attribs);
    		$attributes_csv = implode(',', $attribs);
    	}
	//echo $attributes_csv;

    	//when we recheck options, we need to get the correct quantity
    	if($type == 'update') {
    		$difference = $qty-$original_qty;
    		if (count ( $options )) {
    			if(isset ( $allOptions [$attributes_csv] )) {
    				$existing_qty= $allOptions [$attributes_csv];
    				$qty = $existing_qty+$difference;
    			}
    		}
    	}
			// get the right quantity for checking
    	if($type == 'add') {
			if (count ( $options )) {
				if( isset ( $allOptions [$attributes_csv] )) {
					$qty = $allOptions [$attributes_csv] + $qty;
				}
			} else {
				$product_total = 0;
				foreach ( $products as $product_2 ) {
					if ($product_2 ['product_id'] == $product_id) {
						$product_total += $product_2 ['quantity'];
					}
				$qty = $product_total + $qty;
				}
			}
		}

    	$availableQuantity = J2StoreInventory::getAvailableQuantity( $product_id, $attributes_csv );
    	if ( $availableQuantity->manage_stock && $qty > $availableQuantity->quantity ) {
    		$status = false;
    	} else {
    		$status = true;
    	}
    	return $status;
    }

    function cleanAttributes($options, $product_id) {
    	$newOptions = array();
		foreach($options as $product_option_id=>$option_value) {
			$product_option = $this->getCartProductOptions($product_option_id, $product_id);
			if($product_option->type == 'select' || $product_option->type == 'radio') {
				$newOptions[$product_option_id]=$option_value;
			}
    	}
    	return $newOptions;

    }
    function getAllAttributes($cartitems) {

    	$nopt = array();
    	foreach ($cartitems as $product) {

    		$newOptions = array();
    		foreach($product['option'] as $opt) {
    			if($opt['type'] == 'select' || $opt['type'] == 'radio') {
    				//if(in_array($opt['product_optionvalue_id'], $options)) {
    					$newOptions[$opt['product_option_id']]=$opt['product_optionvalue_id'];
    				//}
    			}
    		}
    		sort($newOptions);
    		$attributes_csv = implode(',', $newOptions);
    		if(!isset($nopt[$attributes_csv])) {
    			$nopt[$attributes_csv] = 0;
    		}
    		$nopt[$attributes_csv]+=$product['quantity'];
    	}
		return $nopt;
    }

    function processAttributeOptions($attributes, $product) {

    	require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/attributestock.php');
    	$db = JFactory::getDbo();
    	$query = $db->getQuery(true)->select('product_attributes, quantity')->from('#__j2store_productquantities')
    	->where('product_id='.$db->q($product->product_id));
    	$attribute_list = $db->setQuery($query)->loadColumn();
    	if(count($attribute_list)) {
    		$query = $db->getQuery(true)->select('quantity')->from('#__j2store_productquantities')
    		->where('product_id='.$db->q($product->product_id));
    		$qty_list = $db->setQuery($query)->loadColumn();
    	}
		if(count($attribute_list) && count($qty_list)) {
			$att = new J2StoreAttributeStock($attribute_list, $qty_list);
			$unavailable_attribs = $att->getNonAvailableAttribute();

			$new_attributes = array();
			foreach ($attributes as $option) {

				$new_optionvalues = array();
					if($option['type'] == 'select' || $option['type'] == 'radio') {
						foreach($option['optionvalue'] as $key=>$optionvalue) {
							if(!in_array($optionvalue['product_optionvalue_id'], $unavailable_attribs)) {
								$new_optionvalues[] = $optionvalue;
							}
						}
						$option['optionvalue'] = $new_optionvalues;
					}

				$new_attributes[] = $option;
			}

			return $new_attributes;
		}

    	return $attributes;
    }


    function hasMinimumQuantity($product, $qty=1,  $isTotal=false) {

    	//first get the minimum restriction
    	//$stock = J2StoreInventory::getStock($product->product_id);
    	$stock = $product->stock;

    	//if not set return true
    	if(!isset($stock->min_sale_qty) && $stock->min_sale_qty < 1) {
    		return true;
    	}

    	//if one always return true;
    	if($stock->min_sale_qty == 1 && $qty == 1) {
    		return true;
    	}

    	$product_total = 0;
    	$items = $this->getDataNew();
    	foreach ($items as $product_2) {
    		if ($product_2['product_id'] == $product->product_id) {
    			$product_total += $product_2['quantity'];
    		}
    	}

    	if($isTotal) {
    		//are we checking total qty from update? then override product total
    		$new_product_total = $qty;
    	} else {

    		$new_product_total =$product_total+$qty;
    	}

    	if ($stock->min_sale_qty > $new_product_total) {
    		throw new Exception( JText::sprintf('J2STORE_MINIMUM_QUANTITY_REQUIRED', $product->product_name, (int) $stock->min_sale_qty, $product_total));
    		return false;
    	}
    	return true;
    }


    function hasMaximumQuantity($product, $qty=1, $isTotal=false) {

    	//first get the minimum restriction
    	//$stock = J2StoreInventory::getStock($product->product_id);
    	$stock = $product->stock;

    	//if not set return true

    	if(!isset($stock->max_sale_qty) || $stock->max_sale_qty < 1) {
    		return true;
    	}

    	$product_total = 0;
    	$items = $this->getDataNew();
    	foreach ($items as $product_2) {
    		if ($product_2['product_id'] == $product->product_id) {
    			$product_total += $product_2['quantity'];
    		}
    	}

    	if($isTotal) {
    		//are we checking total qty from update? then override product total
    		$new_product_total = $qty;
    	} else {
    		$new_product_total =$product_total+$qty;
    	}

    	if ($new_product_total > $stock->max_sale_qty) {
    		throw new Exception( JText::sprintf('J2STORE_MAXIMUM_QUANTITY_WARNING', $product->product_name, (int) $stock->max_sale_qty, $product_total));
    		return false;
    	}
    	return true;
    }

    /**
     *
     * Method to check config, user group and product state (if recurs).
     * Then get right values accordingly
     * @param array $items - cart items
     * @param boolean - config to show tax or not
     * @return object
     */
    function checkItems( &$items, $show_tax=false)
    {
    	if (empty($items)) { return array(); }
		$params = JComponentHelper::getParams('com_j2store');
		$tax = new J2StoreTax();
    	$cartdata['products'] = array();

    	foreach ($items as $product) {
    		$product_total = 0;

    		foreach ($items as $product_2) {
    			if ($product_2['product_id'] == $product['product_id']) {
    				$product_total += $product_2['quantity'];
    			}
    		}

    		//options
    		$option_data = array();

    		foreach ($product['option'] as $option) {

    			$value = $option['option_value'];
    			$option_sku = isset($option['option_sku'])?$option['option_sku']:'';
    			$option_data[] = array(
    					'name'  => $option['name'],
    					'option_sku'  => $option_sku,
    					'value' => $value
    			);
    		}

    		// Display prices
    		$price = $tax->calculate($product['price'], $product['tax_profile_id'], $params->get('show_tax_total'));

    		$total = $tax->calculate($product['price'], $product['tax_profile_id'], $params->get('show_tax_total')) * $product['quantity'];

    		$tax_amount = $tax->getTax($product['price'], $product['tax_profile_id']);
    		$cartdata['products'][] = array(
    				'key'      => $product['key'],
    				'product_id'     => $product['product_id'],
    				'product_name'     => $product['name'],
    				'product_model'    => $product['model'],
    				'product_qty_total'    => $product_total,
    				'product_options'   => $option_data,
    				'quantity' => $product['quantity'],
    				'stock'    => $product['stock'],
    				'tax_amount'    => $tax_amount,
    				'price'    => $price,
    				'price_without_tax'    => $product['price'],
    				'total'    => $total,
    				'total_without_tax'    => $product['price']*$product['quantity']
    		);

    	}
    	$cartObj = JArrayHelper::toObject($cartdata['products']);
    	return $cartObj;
    }



    function getTotals() {
    	$app = JFactory::getApplication();
    	$session = JFactory::getSession();
    	$products =$this->getDataNew();
    	$tax = new J2StoreTax();
    	$total_data = array();
    	$total = 0;

    	//products
    	$total_data['products'] = $products;

    	//sub total
    	$total_data['subtotal'] = J2StoreHelperCart::getSubtotal();
    	$total +=$total_data['subtotal'];
    	//taxes
    	$tax_data = array();
    	$taxes = J2StoreHelperCart::getTaxes();

    	//coupon
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
    				$actual_discount = $this->getDiscountTotal();
    				//is the actual discount greater than the max value
    				if($actual_discount > 0 && $actual_discount > (float) $coupon_info->max_value) {
    					$coupon_info->value = (float) $coupon_info->max_value;
    					$coupon_info->value_type = 'F';
    				}
    			}

				$product_array2 = array();
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
    							$taxes[$tax_rate['taxrate_id']] -= $tax_rate['amount'];
    							//	}
    						}
    					}
    				}

    				$discount_total += $discount;
    			}

    			$total_data['coupon'] = array(
    					'title'      => JText::sprintf('J2STORE_COUPON_TITLE', $session->get('coupon', '', 'j2store')),
    					'value'      => -$discount_total
    			);

    			//$total_data['coupon'] = $coupon_data;
    			//less the coupon discount in the total
    			$total -= $discount_total;
    		}

    	}

    	if($session->has('shipping_values', 'j2store')) {
    		$shipping = $session->get('shipping_values', array(), 'j2store');
    		if(count($shipping) && isset($shipping['shipping_name']) ) {
    			$total_data['shipping_amount'] = $shipping['shipping_price']+$shipping['shipping_extra'];
    			$total_data['shipping_tax'] = $shipping['shipping_tax'];
    			$total_data['shipping_total'] = $shipping['shipping_price']+$shipping['shipping_extra']+$shipping['shipping_tax'];
    			$total_data['shipping_name'] = $shipping['shipping_name'];
    			$total +=$total_data['shipping_total'];
    		}
    	}

    	$total_data['total_without_tax'] = $total;

    	//taxes
    	foreach ($taxes as $key => $value) {
    		if ($value > 0) {
    			$tax_data[]= array(
    					'title'      => $tax->getRateName($key),
    					'percent'      => $tax->getPercent($key),
    					'value'      => $value
    			);
    			$total += $value;
    		}
    	}
    	$total_data['taxes'] = $tax_data;

    	$total_data['total'] = $total;

    	return $total_data;
    }


    public function getDiscountTotal() {

    	$app = JFactory::getApplication();
    	$session = JFactory::getSession();
    	$discount_total = 0;

    	if($session->has('coupon', 'j2store')) {

    		$coupon_info = J2StoreHelperCart::getCoupon($session->get('coupon', '', 'j2store'));

    		if ($coupon_info) {
    			$products =$this->getDataNew();


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
    			$product_array2 = array();
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

    				}

    				$discount_total += $discount;
    			}

    		}

    	}

    	return $discount_total;
    }

}

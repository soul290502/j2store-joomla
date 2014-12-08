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
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_SITE.'/components/com_j2store/models/_base.php' );

class J2StoreModelOrders extends J2StoreModelBase {

	public function getList($refresh = false)
	{
		if (empty( $this->_list ))
		{
			$list = parent::getList($refresh = false);
			if( empty( $list ) ){
				return array();
			}
			$this->_list = $list;
		}

		return $this->_list;
	}


	protected function _buildQueryWhere($query)
	{
		$filter     = $this->getState('filter');
		$filter_orderstate	= $this->getState('filter_orderstate');
		$filter_userid	= $this->getState('filter_userid');
		$filter_user	= $this->getState('filter_user');
		$filter_ordernumber    = $this->getState('filter_ordernumber');
		$filter_orderstates = $this->getState('filter_orderstates');
		$restrict_filter_orderstate	= $this->getState('restrict_filter_orderstate');
		$params = JComponentHelper::getParams('com_j2store');

		if ($filter)
		{
			$key	= $this->_db->Quote('%'.$this->_db->escape( trim( strtolower( $filter ) ) ).'%');

			$where = array();

			$where[] = 'LOWER(tbl.order_id) LIKE '.$key;
			$where[] = 'LOWER(oi.billing_first_name) LIKE '.$key;
			$where[] = 'LOWER(oi.billing_last_name) LIKE '.$key;
			$where[] = 'LOWER(u.email) LIKE '.$key;
			$where[] = 'LOWER(u.username) LIKE '.$key;
			$where[] = 'LOWER(u.name) LIKE '.$key;

			$query->where('('.implode(' OR ', $where).')');
		}


		if (strlen($filter_user))
		{
			$key	= $this->_db->Quote('%'.$this->_db->escape( trim( strtolower( $filter_user ) ) ).'%');

			$where = array();
			$where[] = 'LOWER(oi.billing_first_name) LIKE '.$key;
			$where[] = 'LOWER(oi.billing_last_name) LIKE '.$key;
			$where[] = 'LOWER(u.email) LIKE '.$key;
			$where[] = 'LOWER(u.username) LIKE '.$key;
			$where[] = 'LOWER(u.name) LIKE '.$key;
			$where[] = 'LOWER(u.id) LIKE '.$key;
			$query->where('('.implode(' OR ', $where).')');
		}

		if (strlen($filter_orderstate))
		{
			$query->where('tbl.order_state_id = '.$this->_db->Quote($filter_orderstate));
		}

		if (is_array($filter_orderstates) && !empty($filter_orderstates))
		{
			$query->where('tbl.order_state_id IN('.implode(",", $filter_orderstates).')' );
		}

		if (strlen($filter_userid))
		{
			$query->where('tbl.user_id = '.$this->_db->Quote($filter_userid));
		}

		if(strlen($restrict_filter_orderstate)) {
		//if(JFactory::getApplication()->isSite() && $params->get('show_incomplete_orders', 0) == 0) {
			$query->where('tbl.order_state_id !='.$this->_db->Quote($restrict_filter_orderstate));
		}
	}

	protected function _buildQueryFields($query)
	{
		$field = array();

		$field[] = " tbl.* ";
		$field[] ="CASE WHEN tbl.invoice_prefix IS NULL or tbl.invoice_number = 0 THEN
						tbl.id
  					ELSE
						CONCAT_WS('', tbl.invoice_prefix, tbl.invoice_number)
					END
				 	AS invoice";
		$field[] = " u.name AS user_name ";
		$field[] = " u.username AS user_username ";
		$field[] = " u.email ";
		$field[] = " oi.user_email";
		$field[] = " oi.billing_first_name";
		$field[] = " oi.billing_last_name";
		$field[] = " oi.billing_address_1";
		$field[] = " oi.billing_address_2";
		$field[] = " oi.billing_city";
		$field[] = " oi.billing_zip";
		$field[] = " oi.billing_zone_name";
		$field[] = " oi.billing_country_name";
		$field[] = " oi.billing_phone_1";
		$field[] = " oi.billing_phone_2";
		$field[] = " oi.billing_company";
		$field[] = " oi.billing_tax_number";
		$field[] = " oi.shipping_first_name";
		$field[] = " oi.shipping_last_name";
		$field[] = " oi.shipping_address_1";
		$field[] = " oi.shipping_address_2";
		$field[] = " oi.shipping_city";
		$field[] = " oi.shipping_zip";
		$field[] = " oi.shipping_zone_name";
		$field[] = " oi.shipping_country_name";
		$field[] = " oi.shipping_phone_1";
		$field[] = " oi.shipping_phone_2";
		$field[] = " oi.shipping_company";
		$field[] = " oi.shipping_tax_number";
		$field[] = " oi.all_billing";
		$field[] = " oi.all_shipping";
		$field[] = " oi.all_payment";
	//	$field[] = " os.ordershipping_type";
	//	$field[] = " os.ordershipping_price";
	//	$field[] = " os.ordershipping_name";
	//	$field[] = " os.ordershipping_code";
	//	$field[] = " os.ordershipping_tax";
	//	$field[] = " os.ordershipping_extra";
		//		$field[] = " ui.address_1 ";
		//		$field[] = " ui.address_2 ";
		//		$field[] = " ui.city ";
		//		$field[] = " ui.zip ";
		//	$field[] = " ui.state ";
		//	$field[] = " ui.country ";
		//	$field[] = " ui.phone_1 ";
		//	$field[] = " ui.phone_2 ";
		//	$field[] = " ui.fax ";
		//	$field[] = " ui.first_name as first_name";
		//	$field[] = " ui.last_name as last_name";
		//	$field[] = " uiz.zone_name as state";
		//	$field[] = " uic.country_name as country";

		$field[] = "
				(
				SELECT
				COUNT(*)
				FROM
				#__j2store_orderitems AS items
				WHERE
				items.order_id = tbl.order_id
				)
				AS items_count
				";
		$field[] = "os.*";
		$query->select( $field );
	}

	protected function _buildQueryJoins($query)
	{
		$query->join('LEFT', '#__j2store_orderinfo AS oi ON oi.order_id = tbl.order_id');
	//	$query->join('LEFT', '#__j2store_ordershippings AS os ON os.order_id = tbl.id');
		//		$query->join('LEFT', '#__j2store_countries AS uic ON ui.country_id = uic.country_id');
		//		$query->join('LEFT', '#__j2store_countries AS uiz ON ui.zone_id = tbl.country_id');
		$query->join('LEFT', '#__users AS u ON u.id = tbl.user_id');
		$query->leftJoin('#__j2store_orderstatuses as os ON tbl.order_state_id = os.orderstatus_id');

	}

	protected function _buildQueryOrder($query)
	{
		$order      = $this->_db->escape( $this->getState('order') );
		$direction  = $this->_db->escape( strtoupper($this->getState('direction') ) );
		if ($order)
		{
			$query->order("$order $direction");
		}
		else
		{
			$query->order("tbl.id ASC");
		}
	}

	public function getItem($pk=null, $refresh=false, $emptyState=true )
	{
		if (empty( $this->_item ))
		{

			JModelLegacy::addIncludePath( JPATH_SITE.'/components/com_j2store/models' );

			$query = $this->getQuery();

			// TODO Make this respond to the model's state, so other table keys can be used
			// perhaps depend entirely on the _buildQueryWhere() clause?
			$keyname = $this->getTable()->getKeyName();
			$value	= $this->_db->Quote( $this->getId() );
			$query->where( "tbl.$keyname = $value" );
			$this->_db->setQuery( (string) $query );
			$item = $this->_db->loadObject();
			if ($item)
			{

				//retrieve the order's items
				$model = JModelLegacy::getInstance( 'OrderItems', 'J2StoreModel' );
				$model->setState( 'filter_orderid', $item->order_id);
				$model->setState( 'order', 'tbl.orderitem_name' );
				$model->setState( 'direction', 'ASC' );
				$item->orderitems = $model->getList();
				foreach ($item->orderitems as $orderitem)
				{
					$model = JModelLegacy::getInstance( 'OrderItemAttributes', 'J2StoreModel' );
					$model->setState( 'filter_orderitemid', $orderitem->orderitem_id);
					$attributes = $model->getList();
					$attributes_names = array();
					$attributes_codes = array();
					$attributes_csv = array();
					foreach ($attributes as $attribute)
					{
						// store a csv of the attrib names
						$attributes_names[] = JText::_( $attribute->orderitemattribute_name );
						if($attribute->orderitemattribute_code)
							$attributes_codes[] = JText::_( $attribute->orderitemattribute_code );

						if($attribute->orderitemattribute_type == 'select' || $attribute->orderitemattribute_type == 'radio') {
							$attributes_csv[] = $attribute->productattributeoptionvalue_id;
						}

					}
					$orderitem->attributes_names = implode(', ', $attributes_names);
					$orderitem->attributes_codes = implode(', ', $attributes_codes);
					sort($attributes_csv);
					$orderitem->attributes_csv = implode(',', $attributes_csv);

					// adjust the price
					$orderitem->orderitem_price = $orderitem->orderitem_price + floatval($orderitem->orderitem_attributes_price);
				}


			}

			$this->_item = $item;
		}

		return $this->_item;
	}


	function loadFromToken($token, $email) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from('#__j2store_orders');
		$query->where('user_email='.$db->quote($email));
		$query->where('token='.$db->quote($token));
		$db->setQuery($query);
		$result = $db->loadResult();
		return $result;
	}

	function getShippingInfo($order_id) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__j2store_ordershippings');
		$query->where('order_id='.$db->q($order->order_id));
		$db->setQuery($query);
		$result = $db->loadObject();
		if($result && !empty($result->ordershipping_id)) {
			return $result;
		} else {
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__j2store_ordershippings');
			$query->where('order_id='.$db->q($order->id));
			$db->setQuery($query);
			$result = $db->loadObject();
			if($result) {
				return $result;
			} else {
				return JTable::getInstance('OrderShippings', 'Table');
			}

		}
	}

	function createInvoiceNumber($order_id) {

		$db = JFactory::getDbo();
		$status = true;

		$invoice= new JObject();
		$invoice->number = 0;
		$invoice->prefix = '';

		require_once(JPATH_SITE.'/components/com_j2store/helpers/cart.php');
		$store = J2StoreHelperCart::getStoreAddress();
		if(!isset($store->store_invoice_prefix) || empty($store->store_invoice_prefix)) {
			//backward compatibility. If no prefix is set, retain the invoice number is the table primary key.
			$status = false;
		}

		if($status) {
			//get the last row
			$query = $db->getQuery(true)->select('MAX(invoice_number) AS invoice_number')
			->from('#__j2store_orders')->where('invoice_prefix='.$db->q($store->store_invoice_prefix));
			$db->setQuery($query);
			$row = $db->loadObject();
			if(isset($row->invoice_number) && $row->invoice_number) {
				$invoice_number = $row->invoice_number+1;
			}else {
				$invoice_number =1;
			}

			$invoice->number = $invoice_number;
			$invoice->prefix = $store->store_invoice_prefix;
		}
		return $invoice;
	}

	function executePlugins(&$order) {
		if(!is_object($order->event)) {
			$order->event = new JObject();
		}
		$app = JFactory::getApplication();
		JPluginHelper::importPlugin('j2store');
		//triggering shipping plugin events

		//before shipping display
		$results = $app->triggerEvent('onJ2StoreBeforeShippingDisplay', array($order));
		$order->event->J2StoreBeforeShippingDisplay =trim(implode("\n", $results));

		//after shipping display
		$results = $app->triggerEvent('onJ2StoreAfterShippingDisplay', array($order));
		$order->event->J2StoreAfterShippingDisplay =trim(implode("\n", $results));

	}
}

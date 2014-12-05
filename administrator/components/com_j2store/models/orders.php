<?php
/*------------------------------------------------------------------------
# com_j2store - J2 Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/



// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/prices.php');
require_once(JPATH_SITE.'/components/com_j2store/helpers/orders.php');
/**
 *
 * @package		Joomla
 * @subpackage	J2Store
 * @since 1.5
 */
class J2StoreModelOrders extends J2StoreModel
{
	/**
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	var $fields = array();

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		$mainframe = JFactory::getApplication();
		$option = 'com_j2store';
		$ns = $option.'.orders';
		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( $ns.'.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}


	/**
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data;
	}

	/**
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method to get a pagination object
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	function _buildQuery()
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Get the WHERE and ORDER BY clauses for the query
		$query->select('a.*,
				CASE WHEN a.invoice_prefix IS NULL or a.invoice_number = 0 THEN
						a.id
  					ELSE
						CONCAT_WS("", a.invoice_prefix, a.invoice_number)
					END
				 	AS invoice
				')->from('#__j2store_orders AS a');
		//users table
		$query->select(' u.name AS buyer, u.email as bemail')
			 ->leftJoin('#__users AS u ON u.id = a.user_id');

		//orderinfo
		$query->select('oi.user_email, oi.user_id as oiuserid, oi.billing_first_name, oi.billing_last_name, oi.all_billing, oi.all_shipping, oi.all_payment')
		->leftJoin('#__j2store_orderinfo AS oi ON oi.order_id = a.order_id');

		//orderstatus
		$query->select('os.*')
		->leftJoin('#__j2store_orderstatuses AS os ON os.orderstatus_id = a.order_state_id');

		//filter for itemised report
		$product_id = $app->input->get('product_id', 0);
		$attribute = $app->input->getString('attribute', '');
		if($product_id > 0 && !empty($attribute)) {
			$query->rightJoin('#__j2store_orderitems AS orderitem ON orderitem.order_id = a.order_id');
		}
		$this->_buildContentWhere($query);
		$this->_buildContentOrderBy($query);
		return $query;
	}

	function _buildContentOrderBy($query)
	{
				$mainframe = JFactory::getApplication();
		$option = 'com_j2store';
		$ns = $option.'.orders';
		$filter_order		= $mainframe->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'a.id',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$query->order($filter_order.' '.$filter_order_Dir)->order('a.created_date');

	}

	function _buildContentWhere($query)
	{
		$mainframe = JFactory::getApplication();
		$option = 'com_j2store';
		$ns = $option.'.orders';
		$db					=JFactory::getDBO();
		$filter_order		= $mainframe->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'a.id',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$filter_orderstate	= $mainframe->getUserStateFromRequest( $ns.'filter_orderstate',	'filter_orderstate',	'',			'int' );
		$search				= $mainframe->getUserStateFromRequest( $ns.'search',			'search',			'',				'string' );
		if (strpos($search, '"') !== false) {
			$search = str_replace(array('=', '<'), '', $search);
		}
		$search = trim(JString::strtolower($search));

		$where = array();

		if ($search) {
			$query->where('LOWER(a.order_id) LIKE '.$db->Quote( '%'.$db->escape( $search, true ).'%', false ).
					'OR LOWER(a.id) LIKE '.$db->Quote( '%'.$db->escape( $search, true ).'%', false ).
			           'OR LOWER(a.transaction_status) LIKE '.$db->Quote( '%'.$db->escape( $search, true ).'%', false ).
						'OR LOWER(a.order_state) LIKE '.$db->Quote( '%'.$db->escape( $search, true ).'%', false ).
						'OR LOWER(oi.user_email) LIKE '.$db->Quote( '%'.$db->escape( $search, true ).'%', false ).
						'OR LOWER(oi.billing_first_name) LIKE '.$db->Quote( '%'.$db->escape( $search, true ).'%', false ).
						'OR LOWER(oi.billing_last_name) LIKE '.$db->Quote( '%'.$db->escape( $search, true ).'%', false ).
						'OR LOWER(CONCAT_WS("", a.invoice_prefix, a.invoice_number)) LIKE '.$db->Quote( '%'.$db->escape( $search, true ).'%', false ).
			           ' OR LOWER(a.orderpayment_type) LIKE '.$db->Quote( '%'.$db->escape( $search, true ).'%', false ));
		}

		if($filter_orderstate) {
			$query->where('a.order_state_id = '.$db->q($filter_orderstate));

		}

		//filter by product id
		$product_id = $mainframe->input->get('product_id', 0);
		$attribute = $mainframe->input->getString('attribute', '');
		if($product_id && !empty($attribute)) {
			$attribute = base64_decode($attribute);
				$query->where('orderitem.product_id='.(int)$product_id);
				$query->where('orderitem.orderitem_attributes LIKE '.$db->Quote( '%'.$db->escape( $attribute, true ).'%', false ));
		}

	}

	function delete($cid = array())
	{
		$result = false;

		if (count( $cid ))
		{
			JArrayHelper::toInteger($cid);

			//lets first  delete the order attributes if any
			$this->_deleteOrderAttributes($cid);

			//let us first delete the order items for this order
			$result = $this->_deleteOrderItems($cid);

			//now delete order info
			$this->_deleteOrderInfo($cid);

			$this->_deleteOrderShipping($cid);

			$cids = implode( ',', $cid );

			if($result) {
				$query = 'DELETE FROM #__j2store_orders'
					. ' WHERE id IN ( '.$cids.' )';
				$this->_db->setQuery( $query );
				if(!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			} else {
				$this->setError(JText::_('J2STORE_DELETE_FAILED'));
				return false;
			}
		}

		return true;
	}


	function _deleteOrderItems($cid) {

		foreach($cid as $id) {

			$order_id = $this->_getOrderID($id);

			if($order_id) {
				$query = 'DELETE FROM #__j2store_orderitems'
					. ' WHERE order_id = '.$this->_db->Quote($this->_db->escape( $order_id, true ),false);
				$this->_db->setQuery( $query );
				if(!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			} else {
				return false;
			}

		}	//end of foreach

		return true;

	}


	function _deleteOrderAttributes($cid) {

		foreach($cid as $id) {

			$order_item_ids = $this->_getOrderItemIDs($id);
			if(count($order_item_ids)) {

				foreach($order_item_ids as $orderitem_id) {
				 $query = 'DELETE FROM #__j2store_orderitemattributes'
					. ' WHERE orderitem_id = '. (int) $orderitem_id;
				$this->_db->setQuery( $query );
				$this->_db->query();
				}
			}

		}	//end of foreach

		return true;

	}


	function _deleteOrderInfo($cid) {
		$error = true;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		foreach($cid as $id) {
			$query->delete('#__j2store_orderinfo')->where('orderpayment_id='.(int) $id);
			$db->setQuery($query);
			try {
				$db->query();
			} catch (Exception $e) {
				//TODO: can we do something here
			}
		}

		return true;
	}

	function _deleteOrderShipping($cid) {

		$db = JFactory::getDbo();
		foreach($cid as $id) {
			$order_id = $this->_getOrderID($id);
			$query = $db->getQuery(true);
			$query->delete('#__j2store_ordershippings')->where('order_id='.$db->q($order_id));
			$db->setQuery($query);
			try {
				$db->execute();
			} catch (Exception $e) {
				//TODO: can we do something here
			}
		}
	}

	function _getOrderID($id) {

			$db = JFactory::getDBO();
			$query = "SELECT order_id FROM #__j2store_orders WHERE id={$id}";
			$db->setQuery($query);
			return $db->loadResult();

	}

	function _getOrderItemIDs($id) {

		//first get the order_id
		$order_id = $this->_getOrderID($id);

		//get the order item ids
		$db = JFactory::getDBO();
		$query = "SELECT orderitem_id FROM #__j2store_orderitems WHERE order_id=".$db->Quote($order_id);
		$db->setQuery($query);
		return $db->loadResultArray();
	}

	function getOrders() {

		//get filters
		$limitstart = 0;
		$limit = $this->getState('filter_limit', 0);

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('o.*');
		$query->from('#__j2store_orders AS o');
		$query->select('oi.user_email AS oi_user_email');
		$query->join('LEFT', '#__j2store_orderinfo AS oi ON o.order_id=oi.order_id');
		$query->order('o.created_date DESC')->order('o.id DESC');
		$db->setQuery($query, $limitstart, $limit);
		return $db->loadObjectList();
	}

	function getOrdersTotal() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$state = $this->getFilterValues();

		if($state->moneysum == 1) {
			$query->select('SUM(tbl.orderpayment_amount)');
		} else {
			$query->select('COUNT(*)');
		}

		$query->from('#__j2store_orders AS tbl');
		$this->_buildTotalQueryWhere($query);
		//echo $query;
		$db->setQuery($query);
		return $db->loadResult();
	}

	function _buildTotalQueryWhere($query){
		$db = JFactory::getDbo();
		jimport('joomla.utilities.date');
		$state = $this->getFilterValues();
		//order status

		if($state->paystate) {
			$states_temp = explode(',', $state->paystate);
			$states = array();
			foreach($states_temp as $s) {
				$s = strtoupper($s);
				//5=incomplete, 4=pending, 3=failed, 1=confirmed
				if(!in_array($s, array(1,3,4,5))) continue;
				$states[] = $db->q($s);
			}
			if(!empty($states)) {
				$query->where(
						$db->qn('tbl').'.'.$db->qn('order_state_id').' IN ('.
						implode(',',$states).')'
				);
			}
		}


		if($state->paykey) {
			$query->where(
					$db->qn('tbl').'.'.$db->qn('orderpayment_type').' LIKE '.
					$db->q('%'.$state->paykey.'%')
			);
		}

		//since
		$since = trim($state->since);
		if(empty($since) || ($since == '0000-00-00') || ($since == '0000-00-00 00:00:00')) {
			$since = '';
		} else {
			$regex = '/^\d{1,4}(\/|-)\d{1,2}(\/|-)\d{2,4}[[:space:]]{0,}(\d{1,2}:\d{1,2}(:\d{1,2}){0,1}){0,1}$/';
			if(!preg_match($regex, $since)) {
				$since = '2001-01-01';
			}
			$jFrom = new JDate($since);
			$since = $jFrom->toUnix();
			if($since == 0) {
				$since = '';
			} else {
				$since = $jFrom->toSql();
			}
			// Filter from-to dates
			$query->where(
					$db->qn('tbl').'.'.$db->qn('created_date').' >= '.
					$db->q($since)
			);
		}

		// "Until" queries
		$until = trim($state->until);
		if(empty($until) || ($until == '0000-00-00') || ($until == '0000-00-00 00:00:00')) {
			$until = '';
		} else {
			$regex = '/^\d{1,4}(\/|-)\d{1,2}(\/|-)\d{2,4}[[:space:]]{0,}(\d{1,2}:\d{1,2}(:\d{1,2}){0,1}){0,1}$/';
			if(!preg_match($regex, $until)) {
				$until = '2037-01-01';
			}
			$jFrom = new JDate($until);
			$until = $jFrom->toUnix();
			if($until == 0) {
				$until = '';
			} else {
				$until = $jFrom->toSql();
			}
			$query->where(
					$db->qn('tbl').'.'.$db->qn('created_date').' <= '.
					$db->q($until)
			);
		}
		// No-zero toggle
		if(!empty($state->nozero)) {
			$query->where(
					$db->qn('tbl').'.'.$db->qn('orderpayment_amount').' > '.
					$db->q('0')
			);
		}

		//from invoice number
		if($state->frominvoice) {
			$query->where(
					$db->qn('tbl').'.'.$db->qn('id').' >= '.$db->q($state->frominvoice)
					);
		}

		//to invoice number
		if($state->toinvoice) {
			$query->where(
					$db->qn('tbl').'.'.$db->qn('id').' <= '.$db->q($state->toinvoice)
			);
		}

	}

	private function getFilterValues()
	{
			return (object)array(
				'search'		=> $this->getState('search',null,'string'),
				'title'			=> $this->getState('title',null,'string'),
				'user_id'		=> $this->getState('user_id',null,'int'),
				'paystate'		=> $this->getState('paystate',null,'int'),
				'processor'		=> $this->getState('processor',null,'string'),
				'paykey'		=> $this->getState('paykey',null,'string'),
				'since'			=> $this->getState('since',null,'string'),
				'until'			=> $this->getState('until',null,'string'),
				'groupbydate'	=> $this->getState('groupbydate',null,'int'),
				'groupbylevel'	=> $this->getState('groupbylevel',null,'int'),
				'moneysum'		=> $this->getState('moneysum',null,'int'),
				'coupon_id'		=> $this->getState('coupon_id',null,'int'),
				'nozero'		=> $this->getState('nozero',null,'int'),
				'frominvoice'		=> $this->getState('frominvoice',null,'int'),
				'toinvoice'		=> $this->getState('toinvoice',null,'int')
		);
	}

	/**
	 * Magic getter; allows to use the name of model state keys as properties
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) {
		return $this->getState($name);
	}

	/**
	 * Magic setter; allows to use the name of model state keys as properties
	 * @param string $name
	 * @return mixed
	 */
	public function __set($name, $value) {
		return $this->setState($name, $value);
	}

	/**
	 * Magic caller; allows to use the name of model state keys as methods to
	 * set their values.
	 *
	 * @param string $name
	 * @param mixed $arguments
	 * @return FOFModel
	 */
	public function __call($name, $arguments) {
		$arg1 = array_shift($arguments);
		$this->setState($name, $arg1);
		return $this;
	}

	public function clearState()
	{
		if(version_compare(JVERSION, '1.6.0', 'ge')) {
			$this->state = new JObject();
		} else {
			$this->_state = new JObject();
		}

		return $this;
	}

}

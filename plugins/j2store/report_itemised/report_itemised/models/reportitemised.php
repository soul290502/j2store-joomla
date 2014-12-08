<?php
/*------------------------------------------------------------------------
# com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Gokila Priya - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

require_once(JPATH_SITE.'/components/com_j2store/models/_base.php');

class J2storeModelReportItemised extends J2storeModel
{
	/*
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

	/**
	 * Constructor
	 *
	 * @since 2.5
	 */
	function __construct()
	{
		parent::__construct();

		$mainframe = JFactory::getApplication();
		$option = 'com_j2store';
		$ns = $option.'.report';

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( $ns .'.limitstart', 'limitstart', 0, 'int' );
		//$filter_order	= $mainframe->getUserStateFromRequest( $ns .'.filter_order', 'filter_order', 'oi.order_id', '' );

		$filter_order =  $mainframe->input->getString('filter_order','oi.order_id');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $ns .'.filter_order_Dir', 'filter_order_Dir', 'ASC', '' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}


	/**
	 *
	 * @access public
	 * @return array
	 */
	public function getData()
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
	/**
	 * Method to buildQuery
	 * @return Query object
	 */
	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query

		$query = JFactory::getDbo()->getQuery(true);
		$query->select('oi.*');
		$query->select('count(oi.product_id) AS count');
		$query->select('SUM(oi.orderitem_quantity) AS sum');
		$query->from('#__j2store_orderitems AS oi');
		$query->leftJoin('#__content AS product ON product.id=oi.product_id');
		$query->select('category.title AS category_name');
		$query->leftJoin('#__categories AS category ON category.id=product.catid');

		$this->_buildContentWhere($query);
		$query->group('oi.product_id,oi.orderitem_attributes');
		return $query;
	}


	function _buildContentWhere($query)
	{
		$mainframe = JFactory::getApplication();
		$option = 'com_j2store';
		$ns = $option.'.report';
		$db					=JFactory::getDBO();
		$filter_order		= $mainframe->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'oi.order_id',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'ASC',				'word' );
		$filter_orderstate	= $mainframe->getUserStateFromRequest( $ns.'filter_orderstate',	'filter_orderstate',	'',			'word' );
		$search				= $mainframe->getUserStateFromRequest( $ns.'filter_search',			'filter_search',			'',				'string' );
		if (strpos($search, '"') !== false) {
			$search = str_replace(array('=', '<'), '', $search);
		}
		$search = JString::strtolower($search);

		$where = array();

		if ($search) {
			$where[] = 'LOWER(oi.orderitem_name) LIKE '.$db->Quote( '%'.$db->escape( $search, true ).'%', false );
			           //'OR LOWER(category.name) LIKE '.$db->Quote( '%'.$db->escape( $search, true ).'%', false );
		}

		if($filter_orderstate) {
			if($filter_orderstate == 'Confirmed') {
				$where[] = 'a.order_state = '.$db->Quote($db->escape( $filter_orderstate, true ),false);
			} else if($filter_orderstate == 'Pending') {
				$where[] = 'a.order_state = '.$db->Quote($db->escape( $filter_orderstate, true ),false);
			} else if($filter_orderstate == 'Failed') {
				$where[] = 'a.order_state = '.$db->Quote($db->escape( $filter_orderstate, true ),false);
			}
		}
		foreach($where as $w) {
			$query->where($w);
		}
		if(!empty($filter_order))
		$query->order($filter_order.'  '.$filter_order_Dir);

		$query->order('oi.order_id');
		return;
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

	/**
	 * Method to get Processed array of data for Export
	 * @param object array $data
	 * return array
	 */
	public function export($data){
		$status;
		$export_data = array();
		foreach($data as $i => $item){
			$export_data[$i]['product_id']= $item->product_id;
			$export_data[$i]['product_name']= $item->orderitem_name;
			$option =array();
			if(isset($item->orderitem_attribute_names) && $item->orderitem_attribute_names){
				$attributes =json_decode(stripcslashes($item->orderitem_attribute_names));
				$string = '';
				foreach($attributes as $a =>$attr){
					$string .=$attr->name.' : '.$attr->value;
				}
				$export_data[$i]['item_option'] = $string;
			}
			$export_data[$i]['category_name']= $item->category_name;
			$export_data[$i]['product_qty'] =$item->sum;
			$export_data[$i]['no_of_orders']= $item->count;
		}
		require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/csv.php');
		$exporter = new J2StoreCSVExport();
		$exporter->headerAry =  $this->getHeaderfields($export_data);
		$exporter->dataAry = $export_data;


		$exporter->filename = 'j2store_report_itemised_export_';
		$exporter->csv();
		$exporter->download();
		return $exporter->filepath;
	}

	/**
	 * Method to get Header Fileds for file Export
	 * @return array;
	 */
	public function getHeaderfields($export_data){
	$lang = JFactory::getLanguage()->load('plg_j2store_report_itemised', JPATH_ADMINISTRATOR);
	$data =array();
	$data[]=JText::_("PLG_J2STORE_PRODUCT_ID");
	$data[] = JText::_("PLG_J2STORE_PRODUCT_NAME");
	$data[] = JText::_("PLG_J2STORE_PRODUCT_OPTIONS");
	$data[] = JText::_("PLG_J2STORE_CATEGORY");
	$data[] = JText::_("PLG_J2STORE_REPORTS_ITEMISED_QUANTITY");
	$data[] = JText::_("PLG_J2STORE_REPORTS_ITEMISED_PURCHASES");
	return $data;
	}

}

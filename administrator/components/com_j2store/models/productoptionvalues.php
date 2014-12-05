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

jimport('joomla.application.component.model');
JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
class J2StoreModelProductOptionValues extends J2StoreModel
{

	/**
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 *
	 * @var array
	 */
	var $_data = null;

	function __construct()
	{
		parent::__construct();

		$mainframe = JFactory::getApplication();
		$option = 'com_j2store';
		$id = JRequest::getVar('product_option_id', 0, 'get', 'int');
		$this->setId((int)$id);

		$ns = $option.'.productoptionvalues';
		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( $ns.'.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);


	}

	function setId($id)
	{
		// Set taxprofile id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	function getId() {
		return $this->_id;
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
	 * @return array
	 */
	function getAllData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			//$this->_data = $this->_getList($query);
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObjectList();

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
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = ' SELECT a.* '
			. ' FROM #__j2store_product_optionvalues AS a '
			. $where
			. $orderby
		;

		return $query;
	}

	function _buildContentOrderBy()
	{
				$mainframe = JFactory::getApplication();
		$option = 'com_j2store';
		$ns = $option.'.productoptionvalues';
		$filter_order		= $mainframe->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'a.ordering',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );

		$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir;

		return $orderby;
	}

	function _buildContentWhere()
	{

		$mainframe = JFactory::getApplication();
		$option = 'com_j2store';
		$db					=JFactory::getDBO();
		$ns = $option.'.productoptionvalues';
		$filter_order		= $mainframe->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'a.ordering',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );

		$where = array();

		$where[] = 'a.product_option_id='.$db->Quote($this->_id);

		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );

		return $where;
	}

	function getProductOptions() {

		//filters
		$filter_id	    = $this->getId();
		$filter_product	    = $this->getState('filter_product');
		$filter_option_type = $this->getState('filter_option_type');
		$filter_array	    = $this->getState('filter_array', 0);
		//null the state
		$this->setState('filter_array', 0);

		$query = $this->_db->getQuery(true);
		$query->select('po.*');
		$query->from('#__j2store_product_options AS po');
		$query->select('o.type, o.option_name');
		$query->join('LEFT', '#__j2store_options AS o ON po.option_id = o.option_id');
		$query->where('o.state=1');

		if($filter_id) {
			$query->where('po.product_option_id='.$this->_db->q($this->getId()));
		}
		if($filter_product) {
			$query->where('po.product_id='.$this->_db->q($filter_product));
		}

		if($filter_option_type) {
			if($filter_option_type == 'select or radio') {
				$query->where('(o.type='.$this->_db->q('select').' OR o.type='.$this->_db->q('radio').')');
			} else {
				$query->where('(o.type='.$this->_db->q($filter_option_type));
			}
		}
		$this->_db->setQuery($query);
		if($filter_array) {
			$row = $this->_db->loadObjectList();
		} else {
			$row = $this->_db->loadObject();
		}
		return $row;
	}

	function getProductOptionValues() {

		//filters
		$filter_id	    = $this->getId();
		$filter_product	    = $this->getState('filter_product');
		$filter_productoption = $this->getState('filter_productoption');

		$query = $this->_db->getQuery(true);
		$query->select('pov.*');
		$query->from('#__j2store_product_optionvalues AS pov');
		$query->select('ov.optionvalue_name');
		$query->join('LEFT', '#__j2store_optionvalues AS ov ON pov.optionvalue_id = ov.optionvalue_id');

		if($filter_product) {
			$query->where('pov.product_id='.$this->_db->q($filter_product));
		}

		if($filter_productoption) {
			$query->where('pov.product_option_id='.$this->_db->q($filter_productoption));
		}

		$this->_db->setQuery($query);
		$row = $this->_db->loadObjectList();
		return $row;
	}

	function getOptionValues($option_id) {

		$query = $this->_db->getQuery(true);
		$query->select('ov.*');
		$query->from('#__j2store_optionvalues AS ov');
		$query->where('ov.option_id='.$option_id);
		$query->order('ov.ordering ASC');
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();
		return $rows;
	}

}

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

/**
 *
 * @package		Joomla
 * @subpackage	J2Store
 * @since 1.5
 */
class J2StoreModelFields extends J2StoreModel
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
		$ns='com_j2store.fields';

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( $ns.'.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}
	public function getTable($type = 'field', $prefix = 'Table', $config = array())
	{

		return JTable::getInstance($type, $prefix, $config);
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
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = ' SELECT a.*'
			. ' FROM #__j2store_field AS a '
			. $where
			. $orderby
		;

		return $query;
	}

	function _buildContentOrderBy()
	{
				$mainframe = JFactory::getApplication();
		$option = 'com_j2store';
		$ns='com_j2store.fields';

		$filter_order		= $mainframe->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'a.field_id',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'ASC',				'word' );

		$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' , a.field_id';

		return $orderby;
	}

	function _buildContentWhere()
	{
		$mainframe = JFactory::getApplication();
		$option = 'com_j2store';
		$ns='com_j2store.fields';
		$db					=JFactory::getDBO();
		$filter_order		= $mainframe->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'a.option_id',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$search				= $mainframe->getUserStateFromRequest( $ns.'search',			'search',			'',				'string' );
		if (strpos($search, '"') !== false) {
			$search = str_replace(array('=', '<'), '', $search);
		}
		$search = JString::strtolower($search);

		$where = array();

		if ($search) {
			$where[] = 'LOWER(a.field_namekey) LIKE '.$db->Quote( '%'.$db->escape( $search, true ).'%', false )
						.'OR LOWER(a.field_name) LIKE '.$db->Quote( '%'.$db->escape( $search, true ).'%', false )
						;
		}

		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );

		return $where;
	}


	function delete($cid = array())
	{
		$table = $this->getTable('field');
		if (count( $cid ))
		{
			foreach($cid as $id) {
				if(!$table->delete($id)) {
					$this->setError($table->getError());
					return false;
				}
			}

		}

		return true;
	}

}

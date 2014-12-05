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
class J2StoreModelOrderTax extends J2StoreModelBase
{

	protected function _buildQueryWhere($query)
	{
		$filter     	= $this->getState('filter');
		$filter_orderid	= $this->getState('filter_orderid');
		$filter_title  = $this->getState('filter_title');

		if ($filter)
		{
			$key	= $this->_db->Quote('%'.$this->_db->escape( trim( strtolower( $filter ) ) ).'%');

			$where = array();
			$where[] = 'LOWER(tbl.ordertax_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.ordertax_title) LIKE '.$key;
			$where[] = 'LOWER(tbl.ordertax_amount) LIKE '.$key;

			$query->where('('.implode(' OR ', $where).')');
		}

		if ($filter_title)
		{
			$key    = $this->_db->Quote('%'.$this->_db->escape( trim( strtolower( $filter_title ) ) ).'%');
			$query->where('LOWER(tbl.ordertax_title) LIKE '.$key);
		}

		if ($filter_orderid)
		{
			$query->where('tbl.order_id = '.$this->_db->Quote($filter_orderid));
		}

	}

	protected function _buildQueryFields($query)
	{
		$field = array();

		$field[] = " tbl.* ";

		$query->select( $field );
	}

	public function getList($refresh = false)
	{
		$list = parent::getList($refresh = false);

		// If no item in the list, return an array()
		if( empty( $list ) ){
			return array();
		}

		return $list;
	}
}

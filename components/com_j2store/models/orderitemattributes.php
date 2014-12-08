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
require_once(JPATH_SITE.DS.'components'.DS.'com_j2store'.DS.'models'.DS.'_base.php' );
class J2StoreModelOrderItemAttributes extends J2StoreModelBase
{
	protected function _buildQueryWhere($query)
	{
		$filter     = $this->getState('filter');
		$filter_orderitemid = $this->getState('filter_orderitemid');

		if ($filter)
		{
			$key	= $this->_db->Quote('%'.$this->_db->escape( trim( strtolower( $filter ) ) ).'%');

			$where = array();
			$where[] = 'LOWER(tbl.orderitemattribute_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.orderitemattribute_name) LIKE '.$key;

			$query->where('('.implode(' OR ', $where).')');
		}
		if ($filter_orderitemid)
		{
			$query->where('tbl.orderitem_id = '.$filter_orderitemid);
		}

	}

}

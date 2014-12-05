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

class J2StoreModelReport extends J2StoreModelBase{

	function __construct()
	{
		parent::__construct();

		$app = JFactory::getApplication();
		$option = 'com_j2store';
		$ns = $option.'.report';
		// Get the pagination request variables
		$filter_search = $app->input->getString('filter_search','');
        $filter_name      = $app->getUserStateFromRequest($ns.'orderitem_name', 'filter_name', '', '');
        $filter_date      = $app->getUserStateFromRequest($ns.'modified_date', 'filter_date', '', '');
        $filter_order_id  = $app->getUserStateFromRequest($ns.'order_id', 'filter_order_id', '', '');
        $filter_order  = $app->getUserStateFromRequest($ns.'order_id', 'filter_order', '', '');
        $filter_order_Dir  = $app->getUserStateFromRequest($ns.'filter_order_Dir', 'filter_order_dir', '', '');

		/* // In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart); */
        $this->setState('filter_search',  $filter_search);
		$this->setState('filter_name',  $filter_name);
		$this->setState('filter_date', $filter_date );
		$this->setState('filter_order_id',$filter_order_id);
		$this->setState('filter_order',$filter_order);
		$this->setState('filter_order_Dir',$filter_order_Dir);


	}


	protected function _buildQueryWhere($query)
	{

		$filter     = $this->getState('filter');
		$filter_search     = $this->getState('filter_search');
		$filter_id_from = $this->getState('filter_id_from');
		$filter_id_to   = $this->getState('filter_id_to');
		$filter_name    = $this->getState('filter_name');
		$filter_enabled    = $this->getState('filter_enabled');
		$filter_order    = $this->getState('filter_order');
		$filter_order_dir    = $this->getState('filter_order_dir');


		if ($filter)
		{
			$key	= $this->_db->Quote('%'.$this->_db->escape( trim( strtolower( $filter ) ) ).'%');
			$where = array();
			$where[] = 'LOWER(tbl.id) LIKE '.$key;
			$where[] = 'LOWER(tbl.name) LIKE '.$key;
			$query->where('('.implode(' OR ', $where).')');
		}

		if ($filter_search)
		{
			$key	= $this->_db->Quote('%'.$this->_db->escape( trim( strtolower( $filter ) ) ).'%');
			$where = array();
			$where[] = 'LOWER(orderinfo.billing_first_name) LIKE '.$key;
			$where[] = 'LOWER(orderinfo.billing_first_name) LIKE '.$key;
			$where[] = 'LOWER(tbl.orderitem_name) LIKE '.$key;
			$query->where('('.implode(' OR ', $where).')');
		}

		if (strlen($filter_id_from))
		{
			if (strlen($filter_id_to))
			{
				$query->where('tbl.id >= '.(int) $filter_id_from);
			}
			else
			{
				$query->where('tbl.id = '.(int) $filter_id_from);
			}
		}
		if (strlen($filter_id_to))
		{
			$query->where('tbl.id <= '.(int) $filter_id_to);
		}
		if (strlen($filter_enabled))
		{

			$query->where('tbl.enabled = 1');
		}
		if ($filter_name)
		{
			$key    = $this->_db->q('%'.$this->_db->escape( trim( strtolower( $filter_name ) ) ).'%');
			$where = array();
			$where[] = 'LOWER(tbl.name) LIKE '.$key;
			$query->where('('.implode(' OR ', $where).')');
		}

		// force returned records to only be j2store shipping
		$query->where("tbl.folder = 'j2store'");
		$query->where("tbl.element LIKE 'report_%'");

	}

	public function getList($refresh = false)
	{
		$list = parent::getList($refresh);
		foreach($list as $item)
		{
			$item->id = $item->extension_id;
			$item->link = 'index.php?option=com_j2store&view=report&task=view&id='.$item->id;
			$item->link_edit = 'index.php?option=com_j2store&view=report&task=edit&id='.$item->id;
			$item->plugin_link_edit="index.php?option=com_plugins&task=plugin.edit&extension_id={$item->id}";
		}


		return $list;
	}

	public function getItem($pk=null, $refresh=false, $emptyState=true)
	{
		if ($item = parent::getItem($pk, $refresh, $emptyState))
		{
			$formdata = new JRegistry;
			$formdata -> loadString($item -> params);
			$item -> data = $formdata -> toArray('data');

		}
		return $item;
	}


}

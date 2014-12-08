<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/


/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/components/com_j2store/models/_base.php');

class J2StoreModelShippingMethods extends J2StoreModelBase
{
    public $cache_enabled = false;

    protected function _buildQueryWhere($query)
    {
		$filter         = $this->getState('filter');
		$filter_id_from = $this->getState('filter_id_from');
		$filter_id_to   = $this->getState('filter_id_to');
		$filter_name    = $this->getState('filter_name');
		$filter_enabled = $this->getState('filter_enabled');
		$filter_taxclass = $this->getState('filter_taxclass');
		$filter_shippingtype = $this->getState('filter_shippingtype');
		$filter_subtotal = $this->getState('filter_subtotal');

        if ($filter)
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
            $where = array();
            $where[] = 'LOWER(tbl.shipping_method_id) LIKE '.$key;
            $where[] = 'LOWER(tbl.shipping_method_name) LIKE '.$key;
            $query->where('('.implode(' OR ', $where).')');
        }

        if (strlen($filter_enabled))
        {
            $query->where('tbl.published = '.$filter_enabled);
        }

        if (strlen($filter_id_from))
        {
            if (strlen($filter_id_to))
            {
                $query->where('tbl.shipping_method_id >= '.(int) $filter_id_from);
            }
                else
            {
                $query->where('tbl.shipping_method_id = '.(int) $filter_id_from);
            }
        }

        if (strlen($filter_id_to))
        {
            $query->where('tbl.shipping_method_id <= '.(int) $filter_id_to);
        }

        if (strlen($filter_name))
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_name ) ) ).'%');
            $query->where('LOWER(tbl.shipping_method_name) LIKE '.$key);
        }

        if (strlen($filter_taxclass))
        {
            $query->where('tbl.tax_class_id = '.(int) $filter_taxclass);
        }

        if (strlen($filter_shippingtype))
        {
            $query->where('tbl.shipping_method_type = '.(int) $filter_shippingtype);
        }

        if ( strlen($filter_subtotal ))
        {
        	$query->where('tbl.subtotal_minimum <= '. $filter_subtotal);
        	$query->where('( ( tbl.subtotal_maximum = 0.00000 ) OR ( tbl.subtotal_maximum = -1 ) OR ( ( tbl.subtotal_maximum != 0.00000 AND tbl.subtotal_maximum != -1 ) AND ( tbl.subtotal_maximum >= '.$filter_subtotal.' ) ) )');
        }

    }

    protected function _buildQueryJoins($query)
    {
        $query->join('LEFT', '#__j2store_taxprofiles AS taxclass ON tbl.tax_class_id = taxclass.taxprofile_id');
    }

    protected function _buildQueryFields($query)
    {
        $field = array();
        $field[] = " taxclass.taxprofile_name ";

        $query->select( $this->getState( 'select', 'tbl.*' ) );
        $query->select( $field );
    }

    public function getList($refresh = false)
    {
        $list = parent::getList($refresh);

    	// If no item in the list, return an array()
        if( empty( $list ) ){
        	return array();
        }
        foreach($list as $item)
        {
        	$item->link = 'index.php?option=com_j2store&view=shipping&task=view&id='.JFactory::getApplication()->input->getInt('id').'&shippingTask=view&sid='.$item->shipping_method_id;
        }
        return $list;
    }
}

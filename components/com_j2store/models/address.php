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

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
class J2StoreModelAddress extends J2StoreModel {

public function getList()
    {
        $list = parent::getList();

        // If no item in the list, return an array()
        if( empty( $list ) ){
        	return array();
        }

        foreach($list as $item)
        {
            $item->link = 'index.php?option=com_j2store&view=addresses&task=edit&id='.$item->address_id;
        }
        return $list;
    }


    public function getShippingAddress() {

		$user =	JFactory::getUser();
		$db = JFactory::getDBO();

		$query = "SELECT * FROM #__j2store_address WHERE user_id={$user->id}";
		$db->setQuery($query);
		return $db->loadObject();

	 }

  function addAddress($type='billing', $data=array()) {

   	$app = JFactory::getApplication();
   	$db = JFactory::getDBO();
   	$user = JFactory::getUser();

   	if(isset($data) && count($data)) {
   		$post = $data;
   	} else {
   		$post = $app->input->getArray($_POST);
   	}

   	foreach($post as $key=>$value) {
   		//in case the value is an array, store as a json encoded message
   		if(is_array($value)) {
   			$post[$key] = $db->escape(json_encode($value));
   		}
   	}

   	//first save data to the address table
   	$row = JTable::getInstance('Address', 'Table');

   	//set the id so that it updates the record rather than changing
   	if (!$row->bind($post)) {
   		$this->setError($row->getError());
   		return false;
   	}

   	if($user->id) {
   		$row->user_id = $user->id;
   		$row->email = $user->email;
   	}

   	$row->type = $type;

   	if (!$row->store()) {
   		$this->setError($row->getError());
   		return false;
   	}

   	return $row->id;

   }

   function getAddress($address_id) {
   	$db = JFactory::getDBO();
   	$where = array();
   	$where[] = 'tbl.id='.(int) $address_id;
   	$query = $this->getAddressQuery($where);
   	$db->setQuery($query);
   	return $db->loadAssoc();
   }

   function getSingleAddressByUserID() {
   	$user = JFactory::getUser();
   	$db = JFactory::getDBO();
   	$where = array();
   	$where[] = 'tbl.user_id='.(int) $user->id;
   	$query = $this->getAddressQuery($where);
   	$db->setQuery($query);
   	return $db->loadObject();
   }

   function getAddresses($key='') {
    $user = JFactory::getUser();
   	$db = JFactory::getDBO();
   	$where = array();
   	$where[] = 'tbl.user_id='.(int) $user->id;
   	$query = $this->getAddressQuery($where);
   	$db->setQuery($query);
   	return $db->loadAssocList($key);
   }


   function getAddressByEmail() {
   	$db = JFactory::getDBO();
   	$where = array();
	$where[] = 'tbl.email='.$this->_db->quote(JFactory::getApplication()->input->getString('email'));
   	$query = $this->getAddressQuery($where);
   	$db->setQuery($query);
   	return $db->loadAssocList();
   }

   function getAddressQuery($where) {
   	$db = JFactory::getDBO();
   	$query = $db->getQuery(true);
   	$query->select('tbl.*,c.country_name,z.zone_name');
   	$query->from('#__j2store_address AS tbl');
	$query->leftJoin('#__j2store_countries AS c ON tbl.country_id=c.country_id');
	$query->leftJoin('#__j2store_zones AS z ON tbl.zone_id=z.zone_id');
	foreach($where as $condition){
		$query->where($condition);
	}
	return $query;
   }

   function getCountry($address_id) {
   	$db = JFactory::getDBO();
   	$where = array();
   	$where[] = 'tbl.id='.(int) $address_id;
   	$query = $this->getAddressQuery($where);
   	$db->setQuery($query);
   	return $db->loadAssoc();
   }

}

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
require_once(JPATH_COMPONENT_ADMINISTRATOR.'/library/selectable/base.php');
class J2StoreModelField extends J2StoreModel
{
	/**
	 * Coupon id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * TaxProfile data
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid', array(0), '', 'array');
		$edit	= JRequest::getVar('edit',true);
		if($edit)
			$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the a_option identifier
	 *
	 * @access	public
	 * @param	int a_option identifier
	 */
	function setId($id)
	{
		// Set a_option id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	public function getTable($type = 'field', $prefix = 'Table', $config = array())
	{

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get a a_option
	 *
	 * @since 1.5
	 */
	function &getData()
	{
		// Load the a_option data
		if ($this->_loadData())
		{
			// Initialize some variables

		}
		else  $this->_initData();

		return $this->_data;
	}


	/**
	 * Method to (un)publish field
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function publish($cid = array(), $publish = 1)
	{
		$user 	=JFactory::getUser();

		if (count( $cid ))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__j2store_fields'
				. ' SET state = '.(int) $publish
				. ' WHERE field_id IN ( '.$cids.' )'
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to load a_option data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT a.* FROM #__j2store_fields AS a' .
					' WHERE a.field_id = '.(int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the a_option data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$table = $this->getTable('field');
			$this->_data	= $table;
			return (boolean) $this->_data;
		}
		return true;
	}


	function save() {

		$selectableBase = new J2StoreSelectableBase();

		$result = $selectableBase->save();
		if($result) {
			//get process field because result is true
			$data = $selectableBase->fielddata;
			$table = $this->getTable();
			$table->bind($data);
			$table->store();
			return $table->field_id;
		} else {
			//error get it
			$errors = $selectableBase->errors;
			$error = implode(',', $errors);
			throw new Exception($error );

			return false;
		}

	}





}
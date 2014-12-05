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
 * @since 2.5
 */
class J2StoreModelOption extends J2StoreModel
{
	/**
	 * TaxProfile id
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
	 * @since 2.5
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

	/**
	 * Method to get a a_option
	 *
	 * @since 2.5
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
	 * Method to remove a a_option
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function delete($cid = array())
	{
		$result = false;
		$error = '';
		//first we have to loop through the ids and check if we have product options associated with this.
		foreach($cid as $option_id) {

			//query the product options table to see if this option id exists
			$query = $this->_db->getQuery(true);
			$query->select('*');
			$query->from('#__j2store_product_options');
			$query->where('option_id='.$option_id);
			$this->_db->setQuery($query);
			$rows = $this->_db->loadObjectList();
			if(isset($rows) && count($rows)) {
				$error .= JText::_('J2STORE_OPTION_CANNOT_BE_DELETED');
			} else {
				if($this->getTable()->delete($option_id)) {
					//deleted options. Delete option values if there are any.
					$q = 'DELETE FROM #__j2store_optionvalues WHERE option_id='.$option_id;
					$this->_db->setQuery( $q );
					$this->_db->query();
				} else {
					$error .= $this->_db->getErrorMsg();
				}

			}
		}

		if($error) {
			$this->setError($error);
			return false;
		}
		return true;
	}

	/**
	 * Method to (un)publish a a_option
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

			$query = 'UPDATE #__j2store_options'
				. ' SET state = '.(int) $publish
				. ' WHERE option_id IN ( '.$cids.' )'
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
			$query = 'SELECT a.* FROM #__j2store_options AS a' .
					' WHERE a.option_id = '.(int) $this->_id;
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
			$a_option = new stdClass();
			$a_option->option_id			= 0;
			$a_option->option_unique_name		= null;
			$a_option->option_name		= null;
			$a_option->type           = null;
			$a_option->published			= 0;
			$this->_data					= $a_option;
			return (boolean) $this->_data;
		}
		return true;
	}
	function getOptionValues($option_id){

		$db= JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("optionvalue. *");
		$query->from("#__j2store_optionvalues as optionvalue");
		$query->where('optionvalue.option_id='.$db->q($option_id));
		$db->setQuery($query);
		return $db->loadObjectList();
	}


}

<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/
// No direct access
defined('_JEXEC') or die;

/**
 *
 * @package		Joomla.Administrator
 * @subpackage	com_j2store
 * @since		3.2
 */
class TableOption extends JTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__j2store_options', 'option_id', $db);
	}

	function check()
	{
		if(empty($this->option_unique_name)) {
			print_r($this);
			exit;
			$this->setError( JText::_("J2STORE_OPTION_UNIQUE_NAME_MUST_HAVE_A_TITLE") );
			return false;
		}
		/** check for existing name */
		$query = 'SELECT option_id FROM #__j2store_options WHERE option_unique_name = '.$this->_db->Quote($this->option_unique_name);
		$this->_db->setQuery($query);

		$xid = (int) $this->_db->loadResult();
		if ($xid && $xid != (int) $this->option_id) {
			$this->setError(JText::_('J2STORE_OPTION_WARNING_SAME_NAME'));
			return false;
		}

		if(empty($this->option_name)) {
			$this->setError( JText::_("J2STORE_OPTION_NAME_MUST_HAVE_A_TITLE") );
			return false;
		}

		return true;
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param	array		Named array
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @see		JTable:bind
	 * @since	1.5
	 */

	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;	// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k) {
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else {
				$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				return false;
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k.'='.implode(' OR '.$k.'=', $pks);

		// Update the publishing state for rows with the given primary keys.
		$this->_db->setQuery(
				'UPDATE `'.$this->_tbl.'`' .
				' SET `state` = '.(int) $state .
				' WHERE ('.$where.')' .
				$checkin
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin the rows.
			foreach($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks)) {
			$this->state = $state;
		}

		$this->setError('');
		return true;
	}

	public function save($src, $orderingFilter = '', $ignore = ''){
		$status = true;
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
		if(parent::save($src, $orderingFilter, $ignore)){
			if($this->option_id){
			if(isset($src['option_value'])  && count($src['option_value'])){
				$ovTable = JTable::getInstance('optionvalues','Table');
				$status = true;
				foreach($src['option_value'] as $optionvalue){
					$optionvalue['option_id']=$this->option_id;
					if(!$ovTable->save($optionvalue)){
						$status = false;
					}
				}
			}
			}
		}

		return $status;
	}

	public function delete($pk = null){
		$status = true;
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
			if(parent::delete($pk)){
				// for option values
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query ="DELETE FROM #__j2store_optionvalues  WHERE  option_id =".$pk;
				$db->setQuery($query);
         		$status = $db->execute();

				//for product option table
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query ="DELETE FROM #__j2store_product_options  WHERE  option_id =".$pk;
				$db->setQuery($query);
				$status = $db->execute();

				//for product optionvalues
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query ="DELETE FROM #__j2store_product_optionvalues  WHERE  option_id =".$pk;
				$db->setQuery($query);
				$status = $db->execute();
			}
			return $status ;
	}

}
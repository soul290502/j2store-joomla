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



// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class TableTaxProfile extends JTable
{

	/** @var int Primary key */
	var $taxprofile_id = null;

	/** @var int */
	var $taxprofile_name = null;

	/** @var int */
	var $tax_percent = null;

	/** @var int */
	var $published = null;

	/**
	 * @param database A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__j2store_taxprofiles', 'taxprofile_id', $db );
	}

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
	/**
	 * Method to save Both the Parent Table & Child Table
	 * params array
	 * return boolean
	 * (non-PHPdoc)
	 * @see JTable::save()
	 */
	public function save($src, $orderingFilter = '', $ignore = ''){
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
		$status = true;
		if(parent::save($src, $orderingFilter, $ignore)){
			// after save parent save return primary key value
			// save the table only pk value exists
			if($this->taxprofile_id){
				if(isset($src['tax-to-taxrule-row'])  && count($src['tax-to-taxrule-row'])){
					$trTable = JTable::getInstance('taxrule','Table');
					$status = true;
					foreach($src['tax-to-taxrule-row'] as $taxrate){
						$taxrate['taxprofile_id']=$this->taxprofile_id;

						try {
							$trTable->save($taxrate);
						}catch (Exception $e){
							$status = false;
						}
						if(!$status) break;
					}
				}
			}
		}

		return $status;

	}

	/**
	 * Method to delete the taxprofile & child Table
	 * @params primary key
	 * (non-PHPdoc)
	 * @see JTable::delete()
	 */
	public function delete($pk = null){
		 $status = true;
		//echo $pk; exit;
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
		if(parent::delete($pk)){
			// for option values
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query ="DELETE FROM #__j2store_taxrules  WHERE  taxprofile_id =".$pk;
			$db->setQuery($query);
			$status =$db->execute();
		}
		return $status;
	}


}


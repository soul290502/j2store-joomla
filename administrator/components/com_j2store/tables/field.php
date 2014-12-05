<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class TableField extends JTable
{

	/**
	* @param database A database connector object
	*/
	function __construct(&$db)
	{
		parent::__construct('#__j2store_field', 'field_id', $db );
	}


	function check()
	{
		if (empty($this->coupon_name))
		{
			$this->setError( JText::_( "Coupon name Required" ) );
			return false;
		}
		if (empty($this->coupon_code))
		{
			$this->setError( JText::_( "Coupon Code Required" ) );
			return false;
		}
		if (empty($this->value))
		{
			$this->setError( JText::_( "Coupon value Required" ) );
			return false;
		}
		if (empty($this->value_type))
		{
			$this->setError( JText::_( "Coupon type Required" ) );
			return false;
		}

		return true;
	}

	public function required($pks = null, $state = 1, $userId = 0)
	{

		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$state = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				return false;
			}
		}

		// Update the publishing state for rows with the given primary keys.
		$query = $this->_db->getQuery(true)
			->update($this->_tbl)
			->set('field_required = ' . (int) $state);


		// Build the WHERE clause for the primary keys.
		$query->where($k . ' = ' . implode(' OR ' . $k . ' = ', $pks));

		$this->_db->setQuery($query);
		$this->_db->execute();


		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->field_required = $state;
		}

		$this->setError('');
		return true;
	}
}
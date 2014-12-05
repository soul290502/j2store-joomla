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
JLoader::register( 'J2StoreTable', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_j2store'.DS.'tables'.DS.'_base.php' );

class TableAddress extends J2StoreTable
{

	/**
	 * @param database A database connector object
	 */

	function __construct(&$db)
	{

		parent::__construct('#__j2store_address', 'id', $db );
	}


	/**
	 * Checks the entry to maintain DB integrity
	 * @return unknown_type
	 */
	function check()
	{
		$params = JComponentHelper::getParams('com_j2store');
		/*if (empty($this->user_id))
		 {
		$this->setError( "User Required" );
		return false;
		}
		*/
		if($this->type=='billing') {
			if (empty($this->first_name))
			{
				$this->setError( "First Name Required" );
				return false;
			}

			if($params->get('bill_lname')==1)
				if (empty($this->last_name))
				{
					$this->setError( "Last Name Required" );
					return false;
				}

				if($params->get('bill_addr_line1')==1)
				{
					if (empty($this->address_1))
					{
						$this->setError( "At Least One Address Line is Required" );
						return false;
					}
				}
				if($params->get('bill_city')==1)
				{
					if (empty($this->city))
					{
						$this->setError( "City Required" );
						return false;
					}
				}
				if($params->get('bill_zip')==1)
				{
					if (empty($this->zip))
					{
						$this->setError( "Zip/Postal code Required" );
						return false;
					}
				}
				if($params->get('bill_country_zone')==1)
				{
					if (empty($this->country_id))
					{
						$this->setError( "Country Required" );
						return false;
					}
				}
				if($params->get('bill_phone1')==1)
				{
					if (empty($this->phone_1))
					{
						$this->setError( "At least one phone number required" );
						return false;
					}
				}

				return true;
		}

		if($this->type=='shipping') {
			if (empty($this->first_name))
			{
				$this->setError( "First Name Required" );
				return false;
			}

			if($params->get('ship_lname')==1)
				if (empty($this->last_name))
				{
					$this->setError( "Last Name Required" );
					return false;
				}

				if($params->get('ship_addr_line1')==1)
				{
					if (empty($this->address_1))
					{
						$this->setError( "At Least One Address Line is Required" );
						return false;
					}
				}
				if($params->get('ship_city')==1)
				{
					if (empty($this->city))
					{
						$this->setError( "City Required" );
						return false;
					}
				}
				if($params->get('ship_zip')==1)
				{
					if (empty($this->zip))
					{
						$this->setError( "Zip/Postal code Required" );
						return false;
					}
				}
				if($params->get('ship_country_zone')==1)
				{
					if (empty($this->country_id))
					{
						$this->setError( "Country Required" );
						return false;
					}
				}
				if($params->get('ship_phone1')==1)
				{
					if (empty($this->phone_1))
					{
						$this->setError( "At least one phone number required" );
						return false;
					}
				}

				return true;
		}



	}

}


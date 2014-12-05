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


/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/tables/_base.php' );
class TableProductQuantities extends J2StoreTable
{

	function TableProductQuantities(&$db )
	{
		$tbl_key    = 'productquantity_id';
        $tbl_suffix = 'productquantities';
        $this->set( '_suffix', $tbl_suffix );
       	$name 		= 'j2store';

		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );
	}

	function check()
	{
		if (empty($this->product_id))
		{
			$this->setError( JText::_('J2STORE_PRODUCT_REQUIRED') );
			return false;
		}

		return true;
	}

}
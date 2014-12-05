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


/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );
JLoader::register( 'J2StoreTable', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_j2store'.DS.'tables'.DS.'_base.php' );


class TableOrderItemAttributes extends J2StoreTable
{
	function TableOrderItemAttributes( &$db )
	{
		$tbl_key 	= 'orderitemattribute_id';
		$tbl_suffix = 'orderitemattributes';
		$name 		= 'j2store';

		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );
	}

	function check()
	{
		return true;
	}
}

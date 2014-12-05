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

class TableOrderShippings extends JTable
{
	function TableOrderShippings ( &$db )
	{
		$tbl_key 	= 'ordershipping_id';
		$tbl_suffix = 'ordershippings';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'j2store';

		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );
	}

	function check()
	{
        $db         = $this->getDBO();
        $nullDate   = $db->getNullDate();
	    if (empty($this->created_date) || $this->created_date == $nullDate)
        {
            $this->created_date =  JFactory::getDate()->toSql();
        }
		return true;
	}
}
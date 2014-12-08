<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/


/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

class TableShippingMethods extends JTable
{
    function TableShippingMethods ( $db )
    {

        $tbl_key    = 'shipping_method_id';
        $tbl_suffix = 'shippingmethods';
        $this->set( '_suffix', $tbl_suffix );
        $name       = 'j2store';

        parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );
    }

    function check()
    {

    	if(empty($this->shipping_method_name)) {
    		throw new Exception(JText::_('J2STORE_SHIPPING_METHOD_NAME_REQUIRED'));
    		return false;
    	}

        if ((float) $this->subtotal_maximum == (float) '0.00000')
        {
            $this->subtotal_maximum = '-1';
        }
        return true;
    }

}

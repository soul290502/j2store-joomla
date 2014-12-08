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



// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the J2Store component
 *
 * @static
 * @package		Joomla
 * @subpackage	J2Store
 * @since 1.0
*/
JLoader::register('J2StoreView',  JPATH_ADMINISTRATOR.'/components//com_j2store/views/view.php');
class J2StoreViewOrders extends J2StoreView
{

}
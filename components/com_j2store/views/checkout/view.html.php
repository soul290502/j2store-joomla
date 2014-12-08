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
class J2StoreViewCheckout extends J2StoreView
{
	 function display($tpl = null) {

		$params = JComponentHelper::getParams('com_j2store');
		$this->assignRef('params',$params);

		JHTML::_('behavior.formvalidation');

		parent::display($tpl);
	}



}

<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * HTML View class for the HelloWorld Component
 */
class J2StoreViewCoupons extends J2StoreView
{

function display($tpl = null) {

		//add toolbar
		$this->addToolBar();
		$toolbar = new J2StoreToolBar();
		$toolbar->renderLinkbar();
		parent::display($tpl);
	}

	function addToolBar() {
		JToolBarHelper::title(JText::_('J2STORE_COUPONS'),'j2store-logo');
	}
}

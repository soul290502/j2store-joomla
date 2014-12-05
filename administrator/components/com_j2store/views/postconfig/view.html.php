<?php

/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    priya bose - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/



// no direct access
defined('_JEXEC') or die('Restricted access');
class J2StoreViewPostconfig extends J2StoreView
{
	function display($tpl=null)
	{
		JFormHelper::addFormPath(JPATH_ADMINISTRATOR.'/components/com_j2store/models/forms');
		JFormHelper::addFieldPath(JPATH_ADMINISTRATOR.'/components/com_j2store/models/fields');
		$this->form = JForm::getInstance('storeprofile', 'storeprofile');
		$this->addToolBar();
		parent::display();
	}

	protected function addToolBar() {
		// setting the title for the toolbar string as an argument
		JToolBarHelper::title(JText::_('J2STORE_POST_CONFIG'),'j2store-logo');
	}
}
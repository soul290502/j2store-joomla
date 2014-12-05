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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class J2StoreViewMigrate extends J2StoreView
{

	function display($tpl = null) {

		$app = JFactory::getApplication();
		$option = 'com_j2store';

		$model = $this->getModel();

		//is it ok to migrate
		if(!$model->canMigrate() || J2StoreVersion::getPreviousVersion() != '2.0.2' || J2STORE_ATTRIBUTES_MIGRATED==1){
			$msg = JText::_('J2STORE_MIGRATE_CURRENT_VERSION');
			$app->redirect('index.php?option=com_j2store&view=cpanel', $msg);
		}

		$db		=JFactory::getDBO();
		$params = JComponentHelper::getParams('com_j2store');

		// Get data from the model
		$items		=  $this->get( 'Data');
		$total = count($items);
		$this->assignRef('items',		$items);
		$this->assignRef('total',		$total);

		$this->addToolBar();
		$toolbar = new J2StoreToolBar();
        $toolbar->renderLinkbar();

		parent::display($tpl);
	}

	function addToolBar() {
		JToolBarHelper::title(JText::_('J2STORE_MIGRATE'),'j2store-logo');
	}

}

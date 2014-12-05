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

jimport( 'joomla.application.component.view');

/**
 *
 * @static
 * @package		Joomla
 * @subpackage	J2Store
 * @since 1.0
*/
class J2StoreViewAddress extends J2StoreView
{

	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$option = 'com_j2store';

		if($this->getLayout() == 'form') {
			$model	= $this->getModel('');
			//get the address
			$address	= $this->get('Data');
			$this->assignRef('address',		$address);

			$this->addToolBar();
			//J2StoreSubmenuHelper::addSubmenu($vName = 'addresses');

		}

		parent::display($tpl);
	}


	function addToolBar() {

		JToolBarHelper::title(JText::_('Edit Address'),'j2store-logo');

		// Set toolbar items for the page
		$edit		= JRequest::getVar('edit',true);
		$text = !$edit ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Address' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::back();
		JToolBarHelper::divider();
		JToolBarHelper::save();
	}

}

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

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * HTML View class for the J2Store Component
*/
class J2StoreViewZone extends J2StoreView
{
	protected $form;
	protected $item;
	protected $state;


	function display($tpl = null)
	{
		$this->form	= $this->get('Form');
		// Get data from the model
		$this->item = $this->get('Item');
		// inturn calls getState in parent class and populateState() in model
		$this->state = $this->get('State');
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'zones.php');
		$model = new J2StoreModelZones;
		$countries = $model->getCountries();
		//generate country filter list
		$lists = array();
		$country_options = array();
		$country_options[] = JHTML::_('select.option', '', JText::_('J2STORE_SELECT_COUNTRY'));
		foreach($countries as $row) {
			$country_options[] =  JHTML::_('select.option', $row->country_id, $row->country_name);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		//add toolbar
		$this->addToolBar();
		$toolbar = new J2StoreToolBar();
		$toolbar->renderLinkbar();
		// Display the template
		parent::display($tpl);
		// Set the document
		$this->setDocument();
	}

	protected function addToolBar() {
		// setting the title for the toolbar string as an argument
		JToolBarHelper::title(JText::_('J2STORE_ZONES'),'j2store-logo');
		JToolBarHelper::save('zone.save', 'JTOOLBAR_SAVE');

		if (empty($this->item->zone_id))  {
			JToolBarHelper::cancel('zone.cancel','JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('zone.cancel', 'JTOOLBAR_CLOSE');
		}
	}

	protected function setDocument() {
		// get the document instance
		$document = JFactory::getDocument();
		// setting the title of the document
		$document->setTitle(JText::_('J2STORE_ZONE'));

	}
}

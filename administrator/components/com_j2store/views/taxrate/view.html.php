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
class J2StoreViewTaxrate extends J2StoreView
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
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'taxrates.php');
		$model = new J2StoreModelTaxRates;
		$geozones = $model->getGeoZones();
		//generate geozone filter list
		$lists = array();
		$geozone_options = array();
		$geozone_options[] = JHTML::_('select.option', '', JText::_('J2STORE_SELECT_COUNTRY'));
		foreach($geozones as $row) {
			$geozone_options[] =  JHTML::_('select.option', $row->geozone_id, $row->geozone_name);
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
		JToolBarHelper::title(JText::_('J2STORE_TAXRATES'),'j2store-logo');
		JToolBarHelper::save('taxrate.save', 'JTOOLBAR_SAVE');

		if (empty($this->item->taxrate_id))  {
			JToolBarHelper::cancel('taxrate.cancel','JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('taxrate.cancel', 'JTOOLBAR_CLOSE');
		}
	}

	protected function setDocument() {
		// get the document instance
		$document = JFactory::getDocument();
		// setting the title of the document
		$document->setTitle(JText::_('J2STORE_TAXRATE'));

	}
}

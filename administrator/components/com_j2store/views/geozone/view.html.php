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
class J2StoreViewGeozone extends J2StoreView
{
	protected $form;
	protected $item;
	protected $state;

	function display($tpl = null)
	{

		$app = JFactory::getApplication();
		$country_id = $app->input->getInt('country_id',1);
		$view = $app->input->getWord('view', 'cpanel');
		$model = $this->getModel('geozone');
		$path = JPATH_ADMINISTRATOR.'/components/com_j2store/views/'.JString::strtolower($view).'/tmpl';
		$this->addTemplatePath($path);
		$this->form	= $this->get('Form');
		// Get data from the model
		$this->model = $this->getModel('geozone');
		$this->item = $this->get('Item');

		//$this->geozonerules = $this->get('GeoZoneRules');
		$this->geozonerules = $model->getGeoZoneRules($this->item->geozone_id);
		// inturn calls getState in parent class and populateState() in model
		$this->state = $this->get('State');

		//get list of countries
		$country_options= $this->get('CountryOptions');

		//generate country filter list
		$lists = array();
		$lists['country'] = JHTML::_('select.genericlist', $country_options, 'jform[country_id]', '', 'value', 'text', $this->state->get('filter.country_options'));
		$this->lists = $lists;
		$this->countryList = $this->model->getCountryList();
		$this->countries =$this->model->getCountry();
		$this->zones =$this->model->getZone($country_id);
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
		//Display the template

		parent::display($tpl);
		// Set the document
		$this->setDocument();
	}

	protected function addToolBar() {
		// setting the title for the toolbar string as an argument
		JToolBarHelper::title(JText::_('J2STORE_GEOZONES'),'j2store-logo');
		JToolBarHelper::apply('geozone.apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::save('geozone.save', 'JTOOLBAR_SAVE');

		if (empty($this->item->geozone_id))  {
			JToolBarHelper::cancel('geozone.cancel','JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('geozone.cancel', 'JTOOLBAR_CLOSE');
		}
	}

	protected function setDocument() {
		// get the document instance
		$document = JFactory::getDocument();
		// setting the title of the document
		$document->setTitle(JText::_('J2STORE_GEOZONE'));
	}
}

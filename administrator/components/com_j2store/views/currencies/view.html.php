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

class J2StoreViewCurrencies extends J2StoreView
{
	protected $items;
	protected $pagination;
	protected $state;


	function display($tpl = null)
	{
		//run the update if auto update is enabled
		require_once (JPATH_SITE.'/components/com_j2store/helpers/cart.php');
		$storeprofile = J2StoreHelperCart::getStoreAddress();
		if($storeprofile->config_currency_auto) {
			$model = $this->getModel('currencies');
			$model->updateCurrencies();
		}
		// Get data from the model
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		// inturn calls getState in parent class and populateState() in model
		$this->state = $this->get('State');
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
		$this->setDocument();
	}

	protected function addToolBar() {
		// setting the title for the toolbar string as an argument
		JToolBarHelper::title(JText::_('J2STORE_CURRENCY'),'j2store-logo');
		$state	= $this->get('State');
		JToolBarHelper::back();
		JToolBarHelper::divider();
		// check permissions for the users
		JToolBarHelper::addNew('currency.add','JTOOLBAR_NEW');
		JToolBarHelper::divider();
		JToolBarHelper::editList('currency.edit','JTOOLBAR_EDIT');
		JToolBarHelper::divider();
		JToolBarHelper::custom('currencies.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
		JToolBarHelper::divider();
		JToolBarHelper::custom('currencies.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		JToolBarHelper::divider();
		JToolBarHelper::deleteList();

	}

	protected function setDocument() {
		// get the document instance
		$document = JFactory::getDocument();
		// setting the title of the document
		$document->setTitle(JText::_('J2STORE_CURRENCY'));

	}


}

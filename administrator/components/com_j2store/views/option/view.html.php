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

class J2StoreViewOption extends J2StoreView
{

	function display($tpl = null) {

		$db		=JFactory::getDBO();
		$uri	=JFactory::getURI();
		$model		= $this->getModel('option');
		$params = JComponentHelper::getParams('com_j2store');
		// get order data
		$data	= $this->get('Data');
		$data->optionvalues = $model->getOptionValues($data->option_id);
		$isNew		= ($data->option_id < 1);
		if($isNew) {
			$data->state = 1;

		}

		$lists = array();
		$arr = array(JHTML::_('select.option', '0', JText::_('J2STORE_NO') ),
					JHTML::_('select.option', '1', JText::_('J2STORE_YES') )	);
		$lists['published'] = JHTML::_('select.genericlist', $arr, 'state', null, 'value', 'text', $data->state);

		$this->assignRef('data',	$data);
		$this->assignRef('lists',	$lists);
		$this->assignRef('params',	$params);

		$this->addToolBar();
		$toolbar = new J2StoreToolBar();
        $toolbar->renderLinkbar();

		parent::display($tpl);
	}

	function addToolBar() {

		JToolBarHelper::title(JText::_('J2STORE_EDIT_PRODUCT_OPTION'),'j2store-logo');

		// Set toolbar items for the page
		$edit		= JRequest::getVar('edit',true);
		$text = !$edit ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'J2STORE_PRODUCT_OPTION' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::apply('apply');
		JToolBarHelper::save();
		if (!$edit)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}

	}

}

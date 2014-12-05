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
class J2StoreViewFields extends J2StoreView
{
function display($tpl = null) {

		$mainframe = JFactory::getApplication();
		$option = 'com_j2store';
		$ns='com_j2store.fields';

		$db		=JFactory::getDBO();
		$uri	=JFactory::getURI();
		$params = JComponentHelper::getParams('com_j2store');

		$filter_order		= $mainframe->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'a.field_id',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'ASC',				'word' );
		$filter_orderstate	= $mainframe->getUserStateFromRequest( $ns.'filter_orderstate',	'filter_orderstate',	'', 'string' );

		$search				= $mainframe->getUserStateFromRequest( $ns.'search',			'search',			'',				'string' );
		if (strpos($search, '"') !== false) {
			$search = str_replace(array('=', '<'), '', $search);
		}
		$search = JString::strtolower($search);

		// Get data from the model
		$items		=  $this->get( 'Data');
		$total		=  $this->get( 'Total');
		$pagination =  $this->get( 'Pagination' );

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		// search filter
		$lists['search']= $search;
		$this->assignRef('lists',		$lists);

		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->params = $params;

		$model = $this->getModel();

		$this->addToolBar();
		$toolbar = new J2StoreToolBar();
        $toolbar->renderLinkbar();

		parent::display($tpl);
	}

	function addToolBar() {
		JToolBarHelper::title(JText::_('J2STORE_FIELDS'),'j2store-logo');
		JToolBarHelper::editList();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList();

	}
}

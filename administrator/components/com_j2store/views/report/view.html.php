<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Gokila Priya - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');


class J2StoreViewReport extends J2StoreView
{

	function display($tpl = null) {

		require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_j2store'.DS.'library'.DS.'prices.php');
		$mainframe = JFactory::getApplication();
		$option = 'com_j2store';
		$ns = $option.'.report';
		$db		=JFactory::getDBO();
		$uri	=JFactory::getURI();
		$task = $mainframe->input->getWord('task', '');


		$filter_order		= $mainframe->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'tbl.id',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$filter_orderstate	= $mainframe->getUserStateFromRequest( $ns.'filter_orderstate',	'filter_orderstate',	'', 'string' );
		$search				= $mainframe->getUserStateFromRequest( $ns.'search',			'search',			'',				'string' );
		if (strpos($search, '"') !== false) {
			$search = str_replace(array('=', '<'), '', $search);
		}
		$search = JString::strtolower($search);

		// Get data from the model
		$model =$this->getModel('report');
		$items = $model->getList();
		$total		=  $this->get( 'Total');
		$pagination =  $this->get( 'Pagination' );
		$javascript 	= 'onchange="document.adminForm.submit();"';

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		// search filter
		$lists['search']= $search;

		$this->assignRef('lists',		$lists);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		$this->params = $params = JComponentHelper::getParams('com_j2store');
		$this->addToolBar();
		$toolbar = new J2StoreToolBar();
		$toolbar->renderLinkbar();
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('J2STORE_REPORTS'),'j2store-logo');
		JToolBarHelper::back('J2STORE_BACK_TO_DASHBOARD', 'index.php?option=com_j2store&view=cpanel');
	}
}

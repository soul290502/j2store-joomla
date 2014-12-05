<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

class J2StoreViewShipping extends J2StoreView
{

	function display($tpl = null) {

		$mainframe = JFactory::getApplication();
		$option = 'com_j2store';
		$ns = 'com_j2store.shipping';
		$task = $mainframe->input->getCmd('task');

			$db		=JFactory::getDBO();
			$uri	=JFactory::getURI();
			$params = JComponentHelper::getParams('com_j2store');

			$filter_order		= $mainframe->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'tbl.id',	'cmd' );
			$filter_order_Dir	= $mainframe->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
			$filter_orderstate	= $mainframe->getUserStateFromRequest( $ns.'filter_orderstate',	'filter_orderstate',	'', 'string' );
			$filter_name = $mainframe->getUserStateFromRequest( $ns.'filter_name',		'filter_name',		'tbl.name',	'cmd' );

			$search				= $mainframe->getUserStateFromRequest( $ns.'search',			'search',			'',				'string' );
			if (strpos($search, '"') !== false) {
				$search = str_replace(array('=', '<'), '', $search);
			}
			$search = JString::strtolower($search);

			$model = $this->getModel('shipping');
			// Get data from the model
			$items		=  $model->getList();
			$total		=  $model->getTotal();
			$pagination =  $model->getPagination();

			// table ordering
			$lists['order_Dir'] = $filter_order_Dir;
			$lists['order'] = $filter_order;
			$lists['filter_name'] = $filter_name;

			// search filter
			$lists['search']= $search;

			$this->assignRef('lists',		$lists);
			$this->assignRef('items',		$items);
			$this->assignRef('pagination',	$pagination);

			if(JFactory::getApplication()->input->getInt('id')) {
				$row = $this->getModel()->getItem();
				$import = JPluginHelper::importPlugin( 'j2store', $row->element );
			}
			JToolBarHelper::title(JText::_('J2STORE_SHIPM_SHIPPING_METHODS'),'j2store-logo');
			$toolbar = new J2StoreToolBar();
			$toolbar->renderLinkbar();

			parent::display($tpl);
	}

	/**
	 *
	 * @param $tpl
	 * @return unknown_type
	 */
	function getLayoutVars($tpl=null)
	{
		$layout = $this->getLayout();
		switch(strtolower($layout))
		{
			case "view":
				$this->set( 'leftMenu', 'leftmenu_configuration' );
				$this->_form($tpl);
				break;
			case "form":
				JRequest::setVar('hidemainmenu', '1');
				$this->_form($tpl);
				break;
			case "default":
			default:
				$this->set( 'leftMenu', 'leftmenu_configuration' );
				$this->_default($tpl);
				break;
		}
	}

	function _form($tpl=null)
	{
       parent::_form($tpl);

	   $row = $this->getModel()->getItem();
		$import = JPluginHelper::importPlugin( 'j2store', $row->element );
	}

}

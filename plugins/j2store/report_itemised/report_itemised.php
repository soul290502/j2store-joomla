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

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/report.php');

class plgJ2StoreReport_itemised extends J2StoreReportPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
    var $_element   = 'report_itemised';

    /**
     * Overriding
     *
     * @param $options
     * @return unknown_type
     */
    function onJ2StoreGetReportView( $row )
    {
    	if (!$this->_isMe($row))
    	{
    		return null;
    	}

    	$html = $this->viewList();

    	return $html;
    }

    /**
     * Validates the data submitted based on the suffix provided
     * A controller for this plugin, you could say
     *
     * @param $task
     * @return html
     */
    function viewList()
    {
    	$app = JFactory::getApplication();
    	$option = 'com_j2store';
    	$ns = $option.'.report';
    	$html = "";
    	JToolBarHelper::title(JText::_('J2STORE_REPORT').'-'.JText::_('PLG_J2STORE_'.strtoupper($this->_element)),'j2store-logo');
    	J2StoreToolBar::_custom('export', 'new', 'new', 'PLG_J2STORE_EXPORT_ITEMISED', false, false, 'reportTask');

	   	$vars = new JObject();
	   	$this->includeCustomModel('Reportitemised');
    	$this->includeCustomTables();
    	$model = JModelLegacy::getInstance('Reportitemised', 'J2StoreModel');
    	$model->setState('filter_search', $app->input->getString('filter_search'));
    	$model->setState('filter_orderstatus', $app->input->getString('filter_orderstatus'));
    	$model->setState('filter_order', $app->input->getString('filter_order'));
    	$model->setState('filter_order_Dir', $app->input->getString('filter_order_Dir'));

    	$list = $model->getData();

		$vars->state=$model->getState();
    	$vars->list = $list;
    	$vars->total = $model->getTotal();
    	$vars->pagination = $model->getPagination();
    	$vars->orderStatus = $this->getOrderStatus();

    	$id = $app->input->getInt('id', '0');
    	$vars->id = $id;
    	$form = array();
    	$form['action'] = "index.php?option=com_j2store&view=report&task=view&id={$id}";
    	$vars->form = $form;
    	$html = $this->_getLayout('default', $vars);
    	return $html;
    }

    public function getOrderStatus(){
    	$db = JFactory::getDbo();
    	$query = $db->getQuery(true);
    	$query->select("*")->from("#__j2store_orderstatuses");
    	$db->setQuery($query);
    	return $row = $db->loadObjectList();
    }





}


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



// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class J2StoreViewCpanel extends J2StoreView
{

function display($tpl = null) {

		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__j2store_storeprofiles');
		$db->setQuery($query);
		$rows =	$db->loadObjectList();



		if(count($rows) < 1) {
			$redirect_url = 'index.php?option=com_j2store&view=postconfig';
			$app->redirect($redirect_url );
		}

		$model = $this->getModel('cpanel');
		$model->checkCurrency();

		$params = JComponentHelper::getParams('com_j2store');

		$xmlfile = JPATH_ADMINISTRATOR.'/components/com_j2store/manifest.xml';
		$row = new JObject();
		$data = JApplicationHelper::parseXMLInstallFile($xmlfile);
			foreach($data as $key => $value) {
						$row->$key = $value;
				}
        $this->assignRef('params', $params);
        $this->assignRef('row', $row);
        require_once (JPATH_COMPONENT_ADMINISTRATOR.'/models/orders.php' );
        require_once (JPATH_COMPONENT_ADMINISTRATOR.'/library/prices.php' );
        $order_model = new J2StoreModelOrders();
        $order_model->setState('filter_limit', 5);
       	$latest_items= $order_model->getOrders();
       	$this->orders =$latest_items;
		$this->order_model = $order_model;
		$this->params = $params;
        $this->addToolBar();
        $toolbar = new J2StoreToolBar();
        $toolbar->renderLinkbar();
		parent::display($tpl);
	}

	function addToolBar() {
		JToolBarHelper::title(JText::_('J2STORE_DASHBOARD'),'j2store-logo');
		JToolBarHelper::preferences('com_j2store', '500', '850');
	}

}
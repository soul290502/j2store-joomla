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

require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/shippingcontroller.php');

class J2StoreControllerShippingStandard extends J2StoreControllerShippingPlugin
{

	var $_element   = 'shipping_standard';

	/**
	 * constructor
	 */
	function __construct()
	{
		$app = JFactory::getApplication();
		$values = $app->input->getArray($_POST);

		parent::__construct();
		JModelLegacy::addIncludePath(JPATH_SITE.'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/models');
		JTable::addIncludePath(JPATH_SITE.'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/tables');
		JFactory::getLanguage()->load('plg_j2store_'.$this->_element, JPATH_ADMINISTRATOR);
		$this->registerTask( 'newMethod', 'newMethod' );
	}

	/**
	 * Gets the plugin's namespace for state variables
	 * @return string
	 */
	function getNamespace()
	{
		$app = JFactory::getApplication();
		$ns = $app->getName().'::'.'com.j2store.plugin.shipping.standard';
		return $ns;
	}

	function newMethod(){
		return $this->view();
	}


	function publish() {

		$return = 0;
		$post = JFactory::getApplication()->input->getArray($_POST);
		$this->includeCustomTables();
		$table = JTable::getInstance('ShippingMethods', 'Table');
		$table->load($post['smid']);
		if($table->shipping_method_id == $post['smid']) {
			if($table->published == 1) {
				$table->published = 0;
			}elseif($table->published == 0) {
				$table->published = 1;
				$return = 1;
			}
			$table->store();
		}
		echo $return;
		JFactory::getApplication()->close();
	}

	function save(){

		$app = JFactory::getApplication();
		$sid = $app->input->get('shipping_method_id');

		$values = $app->input->getArray($_POST);

		$this->includeCustomTables();
		$table = JTable::getInstance('ShippingMethods', 'Table');

		$table->bind($values);

		try {
			$table->save($values);
			$link = $this->baseLink();
			$this->messagetype 	= 'message';
			$this->message  	= JText::_('J2STORE_ALL_CHANGES_SAVED');
		} catch(Exception $e) {
			$link = $this->baseLink().'&shippingTask=view&sid='.$sid;
			$this->messagetype 	= 'error';
			$this->message 		= JText::_('J2STORE_SAVE_FAILED').$e->getMessage();

		}
		$redirect = JRoute::_( $link, false );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}


	function setRates()
	{

		$app = JFactory::getApplication();
		$this->includeCustomModel('ShippingRates');
		$sid = $app->input->getInt('sid');

		$this->includeCustomTables();
		$row = JTable::getInstance('ShippingMethods', 'Table');
		$row->load($sid);

		$model = JModelLegacy::getInstance('ShippingRates', 'J2StoreModel');
		$model->setState('filter_shippingmethod', $sid);
		$ns = 'com_j2store.shippingrates';

		$state = array();

		$state['limit']  	= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$state['limitstart'] = $app->getUserStateFromRequest($ns.'limitstart', 'limitstart', 0, 'int');

		$filter_order		= $app->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'a.ordering',	'cmd' );
		$filter_order_Dir	= $app->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$state['id']        = JRequest::getVar('id', JRequest::getVar('id', '', 'get', 'int'), 'post', 'int');

		foreach ($state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		$items = $model->getList();
		$total		=  $model->getTotal();
		$pagination =  $model->getPagination();
		//form
		$form = array();
		$form['action'] = $this->baseLink();
		JToolBarHelper::title(JText::_('J2STORE_SHIPM_SHIPPING_METHODS'),'j2store-logo');
		// view
		$view = $this->getView( 'ShippingMethods', 'html' );
		$view->hidemenu = true;
		$view->hidestats = true;
		$view->setModel( $model, true );
		$view->assign('row', $row);
		$view->assign('items', $items);
		$view->assign( 'total', $total );
		$view->assign( 'pagination', $pagination );
		$view->assign( 'lists', $lists );
		$view->assign('form2', $form);
		$view->assign('baseLink', $this->baseLink());
		$view->assign( 'lists', $lists );
		$view->setLayout('setrates');
		$view->display();
		return;
	}

	function cancel(){
		$redirect = $this->baseLink();
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, '', '' );
	}

	function view()
	{

		$app = JFactory::getApplication();
		require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/select.php');
		J2StoreToolBar::_custom( 'save', 'save', 'save', 'JTOOLBAR_SAVE', false, 'shippingTask' );
		J2StoreToolBar::_custom( 'cancel', 'cancel', 'cancel', 'JTOOLBAR_CLOSE', false, 'shippingTask' );
		JToolBarHelper::title(JText::_('J2STORE_SHIPM_SHIPPING_METHODS'),'j2store-logo');

		$id = $app->input->getInt('id', '0');
		$sid = $app->input->getInt('sid', '0');
		$this->includeCustomModel('ShippingMethods');
		$this->includeCustomTables();
		$model = JModelLegacy::getInstance('ShippingMethods', 'J2StoreModel');
		$model->setId((int)$sid);

		$item = $model->getItem();

		if(!isset($item)) {
			$item = JTable::getInstance('ShippingMethods', 'Table');
		}

		$data = array();

		$data ['published'] = JHTML::_('select.booleanlist',  'published', 'class=""', $item->published );
		$data ['taxclass'] =  J2StoreSelect::taxclass($item->tax_class_id, 'tax_class_id');
		$data ['shippingtype'] =  J2StoreSelect::shippingtype( $item->shipping_method_type, 'shipping_method_type', '', 'shipping_method_type', false );

		$options=array();
		$options[]= JHtml::_('select.option', 'no', JText::_('JNO'));
		$options[]= JHtml::_('select.option', 'store', JText::_('J2STORE_SHIPPING_STORE_ADDRESS'));
		$data ['address_override'] = JHtmlSelect::genericlist($options, 'address_override', array(), 'value', 'text', $item->address_override);

		// Form
		$form = array();
		$form['action'] = $this->baseLink();
		$form['shippingTask'] = 'save';
		//We are calling a view from the ShippingMethods we isn't actually the same  controller this has, however since all it does is extend the base view it is
		// all good, and we don't need to remake getView()
		$view = $this->getView( 'ShippingMethods', 'html' );
		$view->hidemenu = true;
		$view->hidestats = true;
		//$view->setTask(true);
		$view->setModel( $model, true );
		$view->assign('item', $item);
		$view->assign('data', $data );
		$view->assign('form2', $form);
		$view->setLayout('view');
		$view->display();
	}

	/**
	 * Deletes a shipping method
	 */
	function delete()
	{
		$error = false;
		$this->messagetype	= '';
		$this->message 		= '';
		$app = JFactory::getApplication();
		$model = $this->getModel('shippingmethods');
		$row = JTable::getInstance('ShippingMethods', 'Table');

		$cids = $app->input->get('cid', array (), 'array');
		if(count($cids) ) {
			foreach ($cids as $cid)
			{
				if (!$row->delete($cid))
				{
					$this->message .= $row->getError();
					$this->messagetype = 'notice';
					$error = true;
				}
			}

			if ($error)
			{
				$this->message = JText::_('J2STORE_ERROR') . " - " . $this->message;
			}
			else
			{
				$this->message = JText::_('J2STORE_ITEMS_DELETED');
			}
		} else {
			$this->messagetype = 'warning';
			$this->message = JText::_('J2STORE_SELECT_ITEM_TO_DELETE');
		}

		$this->redirect = $this->baseLink();
		$this->setRedirect( $this->redirect, $this->message, $this->messagetype );
	}

	/**
	 * Creates a shipping rate and redirects
	 *
	 * @return unknown_type
	 */
	function createrate()
	{
		$app = JFactory::getApplication();
		$this->includeCustomModel('shippingrates');
		$this->includeCustomTables();
		$model  = $this->getModel( 'shippingrates');

		$row = $model->getTable();
		$row->bind($app->input->getArray($_POST));
		if (!$row->save() )	{
			$this->messagetype  = 'notice';
			$this->message      = JText::_('J2STORE_SAVE_FAILED')." - ".$row->getError();
		}

		$redirect = $this->baseLink()."&shippingTask=setrates&sid={$row->shipping_method_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Saves the properties for all prices in list
	 *
	 * @return unknown_type
	 */
	function saverates()
	{
		$app = JFactory::getApplication();
		$error = false;
		$this->messagetype  = '';
		$this->message      = '';

		$this->includeCustomModel('ShippingRates');
		$this->includeCustomTables();
		$model = $this->getModel('shippingrates');
		$row = $model->getTable();

		$cids = $app->input->get('cid', array(0),  'array');
		$geozones = $app->input->get('geozone', array(0),  'array');
		$prices = $app->input->get('price', array(0),  'array');
		$weight_starts = $app->input->get('weight_start', array(0),  'array');
		$weight_ends = $app->input->get('weight_end', array(0),  'array');
		$handlings = $app->input->get('handling', array(0), 'array');

		foreach ($cids as $cid)
		{
			$row->load( $cid );
			$row->geozone_id = $geozones[$cid];
			$row->shipping_rate_price = $prices[$cid];
			$row->shipping_rate_weight_start = $weight_starts[$cid];
			$row->shipping_rate_weight_end = $weight_ends[$cid];
			$row->shipping_rate_handling = $handlings[$cid];

			if (!$row->save())
			{
				$this->message .= $row->getError();
				$this->messagetype = 'notice';
				$error = true;
			}
		}

		if ($error)
		{
			$this->message = JText::_('J2STORE_ERROR') . " - " . $this->message;
		}
		else
		{
			$this->message = "";
		}

		$redirect = $this->baseLink()."&shippingTask=setrates&sid={$row->shipping_method_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Deletes a shipping rate and redirects
	 *
	 * @return unknown_type
	 */
	function deleterate()
	{
		$model  = $this->getModel( 'shippingrates');

		$cids = JFactory::getApplication()->input->get('cid', array(0), 'array');

		foreach ($cids as $cid)
		{
			$row = $model->getTable();
			$row->load( $cid );

			if (!$row->delete())
			{
				$this->message .= $row->getError();
				$this->messagetype = 'notice';
				$error = true;
			}
		}

		if ($error)
		{
			$this->message = JText::_('J2STORE_ERROR') . " - " . $this->message;
		}
		else
		{
			$this->message = "";
		}

		$redirect = $this->baseLink()."&shippingTask=setrates&sid={$row->shipping_method_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}
}

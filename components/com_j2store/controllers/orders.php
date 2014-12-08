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

jimport('joomla.application.component.controller');
JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/tables' );
class J2StoreControllerOrders extends J2StoreController
{

	function __construct()
	{

		parent::__construct();

	}

	function display($cachable = false, $urlparams = array()) {

		$app = JFactory::getApplication();
		$format = $app->input->getWord('format', 'html');
		if($format != 'raw') {
			$user = JFactory::getUser();
			$params = JComponentHelper::getParams('com_j2store');

			$session = JFactory::getSession();

			$guest_token = $session->get('guest_order_token');
			$guest_order_email = $session->get('guest_order_email');
			if (empty($user->id) && (empty($guest_token) || empty($guest_order_email)) )
			{

				$view = $this->getView( 'orders', 'html' );
				$view->set( '_controller', 'orders' );
				$view->set( '_view', 'orders' );
				$view->set( '_doTask', true);
				$view->set( 'hidemenu', false);
				$view->assign( 'params', $params );
				$view->setLayout( 'form' );
				$view->display();

				//if there there is a registered user, then take him direct to the orders
			}elseif ($user->id)  {
				if(isset($guest_token)) {
					$session->set('guest_order_token', NULL);
				}
				$this->listOrders();
				// if its guest
			} elseif ($guest_token && $guest_order_email) {
				$this->view();
			}
			return null;
		} else {
			$view = $this->getView( 'orders', 'raw' );
			$view->display();
		}
	}

	function listOrders() {

		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$params = JComponentHelper::getParams('com_j2store');
		//only registered users are allowed
		if (empty($user->id))
		{
			$url = JRoute::_( "index.php?option=com_j2store&view=orders" );
			$app->redirect( $url);
			return;
		}

		$model  = $this->getModel('orders');
		$ns = 'com_j2store.orders';

		$state = $this->_setModelState();
		$limit		= $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$limitstart	= $app->getUserStateFromRequest( $ns.'.limitstart', 'limitstart', 0, 'int' );
		//$limitstart	= (empty($_GET['limitstart'])) ? 0 : $app->getUserStateFromRequest($ns.'.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$state['limit']  	= $limit;
		$state['limitstart'] = $limitstart;
		$state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.'.$model->getTable()->getKeyName(), 'cmd');
		$state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'ASC', 'word');

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		if(JFactory::getApplication()->isSite() && $params->get('show_incomplete_orders', 0) == 0) {
			$model->setState('restrict_filter_orderstate', 5);
		}

		$model->setState('filter_userid', $user->id);
		$orders = $model->getList();
		$view = $this->getView( 'orders', 'html' );
		$view->set( '_controller', 'orders' );
		$view->set( '_view', 'orders' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', false);
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState());
		$view->assign( 'orders', $orders );
		$view->assign( 'params', $params );
		$view->assign( 'pagination', $model->getPagination());
		$view->setLayout( 'default' );
		$this->_setModelState();
		$view->display();

	}

	function view()
	{
		// initialise variables
		$app = JFactory::getApplication();
		$user_id = JFactory::getUser()->id;
		$session = JFactory::getSession();
		$params = JComponentHelper::getParams('com_j2store');
		$guest_token = $session->get('guest_order_token');
		$guest_order_email = $session->get('guest_order_email');

		if(empty($user_id)) {
			if (empty($guest_token) || empty($guest_order_email))
			{

				$this->messagetype  = 'notice';
				$this->message      = JText::_( 'J2STORE_ORDER_INVALID' );
				$redirect = "index.php?option=com_j2store&view=orders";
				$redirect = JRoute::_( $redirect, false );
				$this->setRedirect( $redirect, $this->message, $this->messagetype );
				return;
			}
		}

		// if the user cannot view order, fail
		$model  = $this->getModel('orders');
		$order = $model->getTable( 'orders' );
		$view = $this->getView( 'orders', 'html' );

		if($user_id) {
			$model->getId();
			$order->load( $model->getId() );
		} elseif ($guest_token && $guest_order_email) {
			$id = $model->loadFromToken($guest_token, $guest_order_email);

			if($id) {
				$model->setId($id);
				$order->load( $model->getId() );
				$view->assign('guest', true);
			} else {
				// an error occured.
				$this->messagetype  = 'notice';
				$this->message      = JText::_( 'J2STORE_ORDER_INVALID' );

				//null the value of the token
				$session->set('guest_order_token', '');
				$redirect = "index.php?option=com_j2store&view=orders";
				$redirect = JRoute::_( $redirect, false );
				$this->setRedirect( $redirect, $this->message, $this->messagetype );
				return;
			}

		}
		if(JFactory::getApplication()->isSite() && $params->get('show_incomplete_orders', 0) == 0) {
			$model->setState('restrict_filter_orderstate', 5);
		}
		$orderitems = $order->getItems();
		$row = $model->getItem();
		$ordertaxes = $order->getOrderTax();
		//check current user is the owner of this order. One more check.
		$error = '';
		if($user_id) {
			if($row->user_id != $user_id) {
				$error = JText::_( 'J2STORE_ORDER_INVALID' );
			}
		}else {

			if($row->user_email != $guest_order_email || $row->token != $guest_token) {
				$error = JText::_( 'J2STORE_ORDER_INVALID' );
			}

		}
		if(!empty($error)) {

			$this->messagetype  = 'notice';
			$this->message      = $error;
			$redirect = "index.php?option=com_j2store&view=orders";
			$redirect = JRoute::_( $redirect, false );
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}
		$shipping_info = $model->getShippingInfo($row);
		//all is good now. So proceed.

		$view->set( '_controller', 'orders' );
		$view->set( '_view', 'orders' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', false);
		$view->setModel( $model, true );
		$view->assign( 'row', $row );
		$view->assign( 'shipping_info', $shipping_info);
		$show_tax = $params->get('show_tax_total');
		$view->assign( 'show_tax', $show_tax );
		foreach ($orderitems as &$item)
		{
			$item->orderitem_price = $item->orderitem_price + floatval( $item->orderitem_attributes_price );
			$taxtotal = 0;
			if($show_tax)
			{
				$taxtotal = ($item->orderitem_tax / $item->orderitem_quantity);
			}
			$item->orderitem_price = $item->orderitem_price + $taxtotal;
			$item->orderitem_final_price = $item->orderitem_price * $item->orderitem_quantity;
			$order->order_subtotal += ($taxtotal * $item->orderitem_quantity);
		}
		$model->executePlugins($order);
		$view->assign( 'order', $order );
		$view->assign( 'ordertaxes', $ordertaxes );
		$view->assign( 'params', $params );
		$view->assign('currency', J2StoreFactory::getCurrencyObject());
		$view->setLayout( 'view' );
		$this->_setModelState();
		$view->display();
	}

	function printOrder()
	{
		// initialise variables
		$user_id = JFactory::getUser()->id;
		$session = JFactory::getSession();
		$params = JComponentHelper::getParams('com_j2store');
		$guest_token = $session->get('guest_order_token');
		$guest_order_email = $session->get('guest_order_email');
		if (empty($user_id) && (empty($guest_token) || empty($guest_order_email)) )
		{

			$this->messagetype  = 'notice';
			$this->message      = JText::_( 'J2STORE_ORDER_INVALID' );
			$redirect = "index.php?option=com_j2store&view=orders";
			$redirect = JRoute::_( $redirect, false );
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}

		// if the user cannot view order, fail
		$model  = $this->getModel('orders');
		$order = $model->getTable( 'orders' );
		$view = $this->getView( 'orders', 'html' );

		if($user_id) {
			$order->load( $model->getId() );
		} elseif ($guest_token && $guest_order_email) {
			$id = $model->loadFromToken($guest_token, $guest_order_email);

			if($id) {
				$model->setId($id);
				$order->load( $model->getId() );
				$view->assign('guest', true);
			} else {
				// an error occured.
				$this->messagetype  = 'notice';
				$this->message      = JText::_( 'J2STORE_ORDER_INVALID' );

				//null the value of the token
				$session->set('guest_order_token', '');
				$redirect = "index.php?option=com_j2store&view=orders";
				$redirect = JRoute::_( $redirect, false );
				$this->setRedirect( $redirect, $this->message, $this->messagetype );
				return;
			}

		}
		if(JFactory::getApplication()->isSite() && $params->get('show_incomplete_orders', 0) == 0) {
			$model->setState('restrict_filter_orderstate', 5);
		}
		$orderitems = $order->getItems();
		$row = $model->getItem();
		$ordertaxes = $order->getOrderTax();
		//check current user is the owner of this order. One more check.
		$error = '';
		if($user_id) {
			if($row->user_id != $user_id) {
				$error = JText::_( 'J2STORE_ORDER_INVALID' );
			}
		}else {

			if($row->user_email != $guest_order_email || $row->token != $guest_token) {
				$error = JText::_( 'J2STORE_ORDER_INVALID' );
			}

		}
		if(!empty($error)) {

			$this->messagetype  = 'notice';
			$this->message      = $error;
			$redirect = "index.php?option=com_j2store&view=orders";
			$redirect = JRoute::_( $redirect, false );
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}
		$shipping_info = $model->getShippingInfo($row);
		//all is good now. So proceed.

		$view->set( '_controller', 'orders' );
		$view->set( '_view', 'orders' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', false);
		$view->setModel( $model, true );
		$view->assign( 'row', $row );
		$view->assign( 'shipping_info', $shipping_info);

		$show_tax = $params->get('show_tax_total');
		$view->assign( 'show_tax', $show_tax );
		foreach ($orderitems as &$item)
		{
			$item->orderitem_price = $item->orderitem_price + floatval( $item->orderitem_attributes_price );
			$taxtotal = 0;
			if($show_tax)
			{
				$taxtotal = ($item->orderitem_tax / $item->orderitem_quantity);
			}
			$item->orderitem_price = $item->orderitem_price + $taxtotal;
			$item->orderitem_final_price = $item->orderitem_price * $item->orderitem_quantity;
			$order->order_subtotal += ($taxtotal * $item->orderitem_quantity);
		}

		$view->assign( 'ordertaxes', $ordertaxes );
		$view->assign( 'order', $order );
		$view->assign( 'params', $params );
		$view->setLayout( 'print' );
		$this->_setModelState();
		$view->display();
	}

	function _setModelState()
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_j2store');
		$model = $this->getModel('orders');
		$ns = 'com_j2store.orders';

		$state = array();
		$state['limit']  	= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$state['limitstart'] = (empty($_GET['limitstart'])) ? 0 : $app->getUserStateFromRequest($ns.'.limitstart', 'limitstart', 0, 'int');
		$state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.'.$model->getTable()->getKeyName(), 'cmd');
		$state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'ASC', 'word');
		$state['filter']    = $app->getUserStateFromRequest($ns.'.filter', 'filter', '', 'string');
		$state['filter_enabled'] 	= $app->getUserStateFromRequest($ns.'enabled', 'filter_enabled', '', '');
		$state['id']        = JRequest::getVar('id', JRequest::getVar('id', '', 'get', 'int'), 'post', 'int');


		// adjust offset for when filter has changed
		if (
				$app->getUserState( $ns.'orderstate' ) != $app->getUserStateFromRequest($ns.'orderstate', 'filter_orderstate', '', '')
		)
		{
			$state['limitstart'] = '0';
		}

		$state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.created_date', 'cmd');
		$state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'DESC', 'word');

		$state['filter_orderstate'] = $app->getUserStateFromRequest($ns.'orderstate', 'filter_orderstate', '', 'string');

		$state['filter_userid']     = JFactory::getUser()->id;
		$filter_userid = $app->getUserStateFromRequest($ns.'userid', 'filter_userid', JFactory::getUser()->id, 'int');

		$state['filter_total']      = $app->getUserStateFromRequest($ns.'total', 'filter_total', '', 'float');

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}
		return $state;
	}



	function guestentry() {

		//check token
		JRequest::checkToken() or jexit('Invalid Token');
		$app = JFactory::getApplication();
		$post = JRequest::get('post');
		$email = JRequest::getVar('email', '', 'post', 'string');
		$token = JRequest::getVar('order_token', '', 'post', 'string');
		if(empty($email) || empty($token)) {
			$link = JRoute::_('index.php?option=com_j2store&view=orders');
			$msg = JText::_('J2STORE_ORDERS_GUEST_VALUES_REQUIRED');
			$app->redirect($link, $msg);
		}

		//checks
		if(filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {

			$session = JFactory::getSession();
			$session->set('guest_order_email', $email);
			$session->set('guest_order_token', $token);

		} else {

			$link = JRoute::_('index.php?option=com_j2store&view=orders');
			$msg = JText::_('J2STORE_ORDERS_GUEST_INVALID_EMAIL');
			$app->redirect($link, $msg);
		}

		$url = JRoute::_('index.php?option=com_j2store&view=orders&task=view');
		$this->setRedirect($url);
		return;
	}

}
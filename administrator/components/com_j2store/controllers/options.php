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

jimport('joomla.application.component.controller');


class J2StoreControllerOptions extends J2StoreController {

	function __construct($config = array())
	{
		parent::__construct($config);
	//	print_r(JRequest::get('post')); exit;
		// Register Extra tasks
		$this->registerTask( 'add',  'display' );
		$this->registerTask( 'edit', 'display' );
		$this->registerTask( 'apply', 'save' );

	}

    function display($cachable = false, $urlparams = array()) {

        switch($this->getTask())
		{
			case 'add'     :
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'layout', 'edit'  );
				JRequest::setVar( 'view'  , 'option');
				JRequest::setVar( 'edit', false );

			} break;
			case 'edit'    :
			{

				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'layout', 'edit'  );
				JRequest::setVar( 'view'  , 'option');
				JRequest::setVar( 'edit', true );

			} break;
		}
	    parent::display();
    }

    function save() {

		$app = JFactory::getApplication();
		$task = $app->input->getString('task');
		$post	= $app->input->getArray($_POST);
		JRequest::checkToken() or jexit('Invalid Token');
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
		$optionTable =JTable::getInstance('option','Table');
		if ($optionTable->save($post)) {
			$msg = JText::_( 'J2STORE_OPTION_SAVED' );
		} else {
			$msg = $optionTable->getError();
		}
		switch ($task){
			case 'apply':
				$link = 'index.php?option=com_j2store&view=options&task=edit&cid[]='.$optionTable->option_id;
				break;
			case 'save':
				$link = 'index.php?option=com_j2store&view=options';
				break;
		}

		$this->setRedirect($link, $msg);
	}


	function saveorder() {

		$db = JFactory::getDBO();
		$cid = JRequest::getVar('cid', array(0), 'post', 'array');
		$total = count($cid);
		$order = JRequest::getVar('order', array(0), 'post', 'array');
		JArrayHelper::toInteger($order, array(0));
		$model =  $this->getModel('option');
		$row = $model->getTable();
		$groupings = array();
		for ($i = 0; $i < $total; $i++) {
			$row->load((int) $cid[$i]);
			if ($row->ordering != $order[$i]) {
				$row->ordering = $order[$i];
				if (!$row->store()) {
					JError::raiseError(500, $db->getErrorMsg());
				}
			}
		}
		$cache = JFactory::getCache('com_j2store');
		$cache->clean();
		$msg = JText::_('J2STORE_NEW_ORDERING_SAVED');
		$this->setRedirect('index.php?option=com_j2store&view=options', $msg);
	}


	function orderup() {
		JRequest::checkToken() or jexit('Invalid Token');
		$cid = JRequest::getVar('cid');
		$model = $this->getModel('option');
		$row = $model->getTable();
		$row->load($cid[0]);
		$row->move(-1);
		$row->reorder();
		$cache = JFactory::getCache('com_j2store');
		$cache->clean();
		$msg = JText::_('J2STORE_NEW_ORDERING_SAVED');
		$this->setRedirect('index.php?option=com_j2store&view=options', $msg);
	}

	function orderdown() {
		JRequest::checkToken() or jexit('Invalid Token');
		$cid = JRequest::getVar('cid');
		$model = $this->getModel('option');
		$row = $model->getTable();
		$row->load($cid[0]);
		$row->move(1);
		$row->reorder();
		$cache = JFactory::getCache('com_j2store');
		$cache->clean();
		$msg = JText::_('J2STORE_NEW_ORDERING_SAVED');
		$this->setRedirect('index.php?option=com_j2store&view=options', $msg);
	}

    function remove()
	{

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );


		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'J2STORE_SELECT_AN_ITEM_TO_DELETE' ) );
		}
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');

		$table = JTable::getInstance('option','Table');
		for($a=0; $a < count($cid); $a++ ){
			if(!$table->delete($cid[$a])) {
				$msg = $table->getError();
			} else {
				$msg = JText::_('J2STORE_DELETED_ITEMS');
			}
		}
		$this->setRedirect( 'index.php?option=com_j2store&view=options', $msg);
	}

	function publish()
	{

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'J2STORE_SELECT_AN_ITEM_TO_PUBLISH' ) );
		}

		$model = $this->getModel('option');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_j2store&view=options' );
	}


	function unpublish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'J2STORE_SELECT_AN_ITEM_TO_PUBLISH' ) );
		}

		$model = $this->getModel('option');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_j2store&view=options' );
	}

	function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Checkin the j2store


		$this->setRedirect( 'index.php?option=com_j2store&view=options' );
	}



	 function deleteoptionvalue(){

		$app = JFactory::getApplication();
		$option_value_id = $app->input->getInt('optionvalue_id');

		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables/');
		$optionValue = JTable::getInstance('optionvalues','Table');
		if($optionValue->delete($option_value_id)){
			$msg = JText::_('J2STORE_OPTION_VALUE_DELETED_SUCCESSFULLY');
		}
		echo json_encode($msg);
		$app->close();

	}
	public static function getOptions() {
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$q = $app->input->post->get('q');
		$query = $db->getQuery(true);
		$query->select('option_id, option_unique_name, option_name');
		$query->from('#__j2store_options');
		$query->where('LOWER(option_unique_name) LIKE '.$db->Quote( '%'.$db->escape( $q, true ).'%', false ));
		$query->where('state=1');
		$db->setQuery($query);
		$result = $db->loadObjectList();
		echo json_encode($result);
		$app->close();
	}

}
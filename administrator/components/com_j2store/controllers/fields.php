<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014-19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
class J2StoreControllerFields extends J2StoreController
{

	function __construct($config = array())
	{
		parent::__construct($config);
			//print_r(JRequest::get('post')); exit;
		// Register Extra tasks
		$this->registerTask( 'add',  'display' );
		$this->registerTask( 'edit', 'display' );
		$this->registerTask( 'apply', 'save' );
		$this->registerTask( 'unpublish',  'publish' );
		$this->registerTask( 'notrequired',  'required' );
	}

	function display($cachable = false, $urlparams = array()) {
		$app = JFactory::getApplication();

		switch($this->getTask())
		{
			case 'add'     :
				{
					$msg = JText::_('J2STORE_AVAILABLE_IN_PRO_VERSION');
					$link = 'index.php?option=com_j2store&view=fields';
					$this->setRedirect($link, $msg);

				} break;
			case 'edit'    :
				{
					$field_id = $app->input->getInt('field_id');
					if(empty($field_id)) {
						$msg = JText::_('J2STORE_AVAILABLE_IN_PRO_VERSION');
						$link = 'index.php?option=com_j2store&view=fields';
						$this->setRedirect($link, $msg);
					}
					$app->input->set( 'hidemainmenu', 1 );
					$app->input->set( 'layout', 'edit'  );
					$app->input->set( 'view'  , 'field');
					$app->input->set( 'edit', true );

				} break;
		}
		parent::display($cachable = false, $urlparams = array());
	}

	function save() {
		$app = JFactory::getApplication();
		$field_id = $app->input->getInt('field_id');
		JRequest::checkToken() or jexit('Invalid Token');
		$model = $this->getModel('field');
		try {
			$field_id = $model->save();
			$msg = JText::_( 'J2STORE_FIELD_SAVED' );
		}catch (Exception $e) {
			$msg = $e->getMessage();
		}

		if($this->getTask() == 'apply') {
			if($field_id) {
				$link = 'index.php?option=com_j2store&view=field&task=edit&layout=edit&field_id='.$field_id;
			} else {
				$link = 'index.php?option=com_j2store&view=field&task=add';
			}

		} else {
			$link = 'index.php?option=com_j2store&view=fields';
		}
		$this->setRedirect($link, $msg);
	}

	function publish()
	{

		$app = JFactory::getApplication();
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$cid = $app->input->get( 'cid', array(), 'array' );
		$values = array('publish' => 1, 'unpublish' => 0);
		$task     = $this->getTask();
		$value   = JArrayHelper::getValue($values, $task, 0, 'int');

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'J2STORE_SELECT_AN_ITEM_TO_PUBLISH' ) );
		}

		$table = $this->getModel('field')->getTable();
		if(!$table->publish($cid, $value)) {
			echo "<script> alert('".$table->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_j2store&view=fields' );
	}

	function required()
	{

		$app = JFactory::getApplication();
		$ids        = $app->input->get('cid', array(), '', 'array');

		if (count( $ids ) < 1) {
			JError::raiseError(500, JText::_( 'J2STORE_SELECT_AN_ITEM' ) );
		}

		$values = array('required' => 1, 'notrequired' => 0);
		$task     = $this->getTask();
		$value   = JArrayHelper::getValue($values, $task, 0, 'int');
		$table = $this->getModel('field')->getTable();
		if (!$table->required($ids, $value)) {
			echo "<script> alert('".$table->getError(true)."'); window.history.go(-1); </script>\n";
		}
		$this->setRedirect( 'index.php?option=com_j2store&view=fields' );
	}


	function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		// Checkin the weblink
		$this->setRedirect( 'index.php?option=com_j2store&view=fields' );
	}



	function remove(){

		$model = $this->getModel('field');
		$table = $model->getTable();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		//print_r($table); exit;
		$cids = JFactory::getApplication()->input->get('cid', array(0), 'ARRAY');

		$field_ids = implode(',', $cids);

		//first check if the fields are core
		$query->select('*')->from('#__j2store_field')->where('field_id IN ('.$field_ids.')');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$error = 0;
		foreach($rows as $row) {

			if($row->field_core != 1) {

				//first delete the column in the address table
				$query = 'ALTER TABLE #__j2store_address DROP '.$row->field_namekey;
				$db->setQuery($query);
				try {
					$db->query();
					if(!$table->delete($row->field_id)) {
						$error = 1;
					}
				} catch (Exception $e) {
					$error = 1;
				}

			}
		}

		if($error) {
			$msg = JText::_('J2STORE_ERROR_DELETING');
		} else {
			$msg = JText::sprintf('COM_J2STORE_N_ITEMS_DELETED', count($cids));
		}
		$this->setRedirect( 'index.php?option=com_j2store&view=fields', $msg);
	}

}

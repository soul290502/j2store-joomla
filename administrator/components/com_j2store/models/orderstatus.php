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

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

class J2storeModelOrderstatus extends JModelAdmin {


public function getForm($data = array(), $loadData = true)
{
	// Initialise variables.
	$app	= JFactory::getApplication();
	// Get the form.
	$form = $this->loadForm('com_j2store.orderstatus', 'orderstatus', array('control' => 'jform', 'load_data' => $loadData));

	if (empty($form)) {
		return false;
	}

	return $form;
}

public function getTable($type = 'orderstatus', $prefix = 'Table', $config = array())
{

	return JTable::getInstance($type, $prefix, $config);
}


/* public function getItem($pk = null)
{
	if ($item = parent::getItem($pk)) {
		// Convert the params field to an array.
	}

	return $item;
}
 */

protected function loadFormData()
{
	// Check the session for previously entered form data.
	$data = JFactory::getApplication()->getUserState('com_j2store.edit.orderstatus.data', array());

	if (empty($data)) {
		$data = $this->getItem();

		// Prime some default values.
		if ($this->getState('orderstatus.id') == 0) {
			$app = JFactory::getApplication();
			// set the id here if it does not work.
			//$data->set('pro_id', JRequest::getInt('pro_id', $app->getUserState('com_jsecommerce.profile.filter.category_id')));
		}
	}

	return $data;
}
}
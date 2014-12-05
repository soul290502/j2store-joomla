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
jimport('joomla.application.component.controlleradmin');

class J2StoreControllerGeoZones extends JControllerAdmin
{

	function __construct($config = array())
	{
		parent::__construct($config);
		// Register Extra tasks

		$this->registerTask( 'trash', 'remove' );
		$this->registerTask( 'delete', 'remove' );
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param	string	$name	The name of the model.
	 * @param	string	$prefix	The prefix for the PHP class name.
	 *
	 * @return	JModel
	 * @since	1.6
	 */
	public function getModel($name = 'GeoZone', $prefix = 'J2StoreModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = JFactory::getApplication()->input;
		$pks = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		JFactory::getApplication()->close();
	}

	function remove()
	{		// Check for request forgeries

	JRequest::checkToken() or jexit( 'Invalid Token' );
	$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
	JArrayHelper::toInteger($cid);
	if (count( $cid ) < 1) {
		JError::raiseError(500, JText::_( 'J2STORE_SELECT_AN_ITEM_TO_DELETE' ) );
	}
	JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');

	$table = JTable::getInstance('geozone','Table');
	for($a=0; $a < count($cid); $a++ ){
		if(!$table->delete($cid[$a])) {
			$msg = $table->getError();
		} else {
			$msg = JText::_('J2STORE_GEOZONE_DELETED_SUCCESSFULLY');
		}
	}
	$this->setRedirect( 'index.php?option=com_j2store&view=geozones', $msg);
	}


}

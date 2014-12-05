<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controllerform');

class J2StoreControllerTaxProfile extends JControllerForm
{
	function __construct($config = array())
	{
		parent::__construct($config);
		// Register Extra tasks
		$this->registerTask( 'save', 'save' );
		$this->registerTask( 'apply', 'save' );
		$this->registerTask( 'trash', 'remove' );
		$this->registerTask( 'delete', 'remove' );
	}


	/**
	 * Method to save data
	 * @params taxprofile data
	 * return boolean
	 * (non-PHPdoc)
	 * @see JControllerForm::save()
	 */
	function save($key = null, $urlVar = null){
		$app = JFactory::getApplication();
		$task = $app->input->getString('task');
		$post = $app->input->getArray($_POST);
		$data = $post['jform'];
		if(isset($post['tax-to-taxrule-row'])){
			$data['tax-to-taxrule-row'] = $post['tax-to-taxrule-row'];
		}
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables/');
		$taxprofile =JTable::getInstance('taxprofile','Table');
		if($taxprofile->save($data)){
			$msg = JText::_( 'J2STORE_TAXPROFILE_SAVED');
			$msgType = 'message';
		} else {
			$msg = JText::_ ( 'J2STORE_TAXPROFILE_SAVE_ERROR' );
			$msgType = 'error';
		}
		switch ($task){
			case 'apply':
				$link = 'index.php?option=com_j2store&view=taxprofile&task=taxprofile.edit&taxprofile_id='.$taxprofile->taxprofile_id;
				break;
			case 'save':
				$link = 'index.php?option=com_j2store&view=taxprofiles&task=display';
				break;
		}
		$this->setRedirect($link, $msg, $msgType);
	}


	function deleteTaxRule() {
		$app = JFactory::getApplication();
		$taxrule_id = $app->input->getInt('taxrule_id');
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables/');
		$taxrule =JTable::getInstance('taxrule','Table');
		$response = array();
		try {
			$taxrule->delete($taxrule_id);
			$response['success'] =JText::_('J2STORE_TAXRULE_DELETED_SUCCESSFULLY');
		}catch (Exception $e) {
			$response['error'] =JText::_('J2STORE_TAXRULE_DELETE_FAILED');
		}
		echo json_encode($response );
		$app->close();

	}

}

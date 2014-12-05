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
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

class J2StoreControllerAddress extends J2StoreController {

	function display($cachable = false, $urlparams = array()) {

		switch($this->getTask())
		{
			case 'edit'    :
				{
					JRequest::setVar( 'hidemainmenu', 1 );
					JRequest::setVar( 'layout', 'form'  );
					JRequest::setVar( 'view'  , 'address');
					JRequest::setVar( 'edit', true );
					$model = $this->getModel('address');

				}
				break;
					
		}
		parent::display();
	}


	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$post	= JRequest::get('post');

		//print_r($post); exit;
		$cid	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$post['id'] = (int) $cid[0];

		$model = $this->getModel('address');

		if ($model->store($post)) {
			$msg = JText::_( 'Address Saved' );
		} else {
			$msg = JText::_( 'Error Saving address' );
		}

		$link = 'index.php?option=com_j2store&view=addresses';
		$this->setRedirect($link, $msg);
	}


}
?>

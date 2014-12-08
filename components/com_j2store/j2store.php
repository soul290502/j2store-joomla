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
$app = JFactory::getApplication();
//j3 compatibility
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

JLoader::register('J2StoreController', JPATH_COMPONENT.'/controllers/controller.php');
JLoader::register('J2StoreModel',  JPATH_ADMINISTRATOR.'/components/com_j2store/models/model.php');
JLoader::register('J2StoreView',  JPATH_ADMINISTRATOR.'/components/com_j2store/views/view.php');

require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/base.php');
require_once (JPATH_SITE.'/components/com_j2store/helpers/utilities.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/prices.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/strapper.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/version.php');
J2StoreStrapper::addJS();
J2StoreStrapper::addCSS();

// Require specific controller if requested
$controller = $app->input->getWord('view', 'mycart');
$task = $app->input->getWord('task');

jimport('joomla.filesystem.file');
jimport('joomla.html.parameter');
if (JFile::exists(JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php')) {
	require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');
	$classname = 'J2StoreController'.ucfirst($controller);
	$controller = new $classname();
	$controller->execute($task);
	$controller->redirect();
}
else {
	JError::raiseError(404, JText::_('J2STORE_NOT_FOUND'));
}
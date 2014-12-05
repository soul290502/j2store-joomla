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
JHTML::_('behavior.tooltip');
jimport('joomla.application.component.controller');
$app = JFactory::getApplication();

//j3 compatibility
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}
JLoader::register('J2StoreController', JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/controller.php');
JLoader::register('J2StoreView',JPATH_ADMINISTRATOR.'/components/com_j2store/views/view.php');
JLoader::register('J2StoreModel', JPATH_ADMINISTRATOR.'/components/com_j2store/models/model.php');
require_once (JPATH_SITE.'/components/com_j2store/helpers/utilities.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/base.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/toolbar.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/version.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/strapper.php');
$version = new J2StoreVersion();
$version->load_version_defines();
J2StoreStrapper::addJS();
J2StoreStrapper::addCSS();
//handle live update
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/liveupdate/liveupdate.php';
if($app->input->getCmd('view','') == 'liveupdate') {
	LiveUpdate::handleRequest();
	return;
}

/*
 * Make sure the user is authorized to view this page
*/
$controller = $app->input->getWord('view', 'cpanel');
if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/'.$controller.'.php')
		&& $controller !='countries' && $controller !='zones'
		&& $controller !='country' && $controller !='zone'
		&& $controller !='taxprofiles' && $controller !='taxprofile'
		&& $controller !='taxrates' && $controller !='taxrate'
		&& $controller !='geozones' && $controller !='geozone'
		&& $controller !='geozonerules' && $controller !='geozonerule'
		&& $controller !='storeprofiles' && $controller !='storeprofile'
		&& $controller !='lengths' && $controller !='length'
		&& $controller !='weights' && $controller !='weight'
		&& $controller !='currencies' && $controller !='currency'
		&& $controller !='emailtemplates' && $controller !='emailtemplate'
		&& $controller !='orderstatuses' && $controller !='orderstatus'
)

{
	require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/'.$controller.'.php');
	$classname = 'J2StoreController'.ucwords($controller);
	$controller = new $classname();

} else {
	$controller = JControllerLegacy::getInstance('J2Store');
}
$controller->execute($app->input->getCmd('task'));
$controller->redirect();

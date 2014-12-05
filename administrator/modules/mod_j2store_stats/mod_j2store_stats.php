<?php
/*------------------------------------------------------------------------
# mod_j2store_cart - J2 Store Cart
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/



// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once( dirname(__FILE__).'/helper.php' );
JFactory::getLanguage()->load('com_j2store', JPATH_SITE);

$moduleclass_sfx = $params->get('moduleclass_sfx','');
$link_type = $params->get('link_type','link');
require_once (JPATH_COMPONENT_ADMINISTRATOR.'/models/orders.php' );
$order_model = new J2StoreModelOrders();
require( JModuleHelper::getLayoutPath('mod_j2store_stats') );
?>

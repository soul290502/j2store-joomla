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

jimport( 'joomla.plugin.plugin' );
jimport('joomla.html.parameter');

class plgSystemJ2Store extends JPlugin {

	function plgSystemJ2Store( &$subject, $config ){
		parent::__construct( $subject, $config );
		//load language
		$this->loadLanguage('com_j2store', JPATH_SITE);
		//if($this->_mainframe->isAdmin())return;

	}

	function onAfterRoute() {

		$mainframe = JFactory::getApplication();
		//JHtml::_('behavior.framework');
		//JHtml::_('behavior.modal');
		require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/popup.php');
		require_once (JPATH_SITE.'/components/com_j2store/helpers/modules.php');
		$document =JFactory::getDocument();
		$baseURL = JURI::root();
		$script = "
		if(typeof(j2storeURL) == 'undefined') {
		var j2storeURL = '{$baseURL}';
		}
		";
		$document->addScriptDeclaration($script);


	}
}
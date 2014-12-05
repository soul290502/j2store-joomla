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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

class J2StoreController extends J2StoreController
{
	/**
	 * display task
	 *
	 * @return void
	 */
	function display($cachable = false, $urlparams = array())
	{
		// set default view if not set
		$app = JFactory::getApplication();
		$app->input->set('view', $app->input->getWord('view', 'cpanel'));
		// call parent behavior
		parent::display($cachable, $urlparams);
		return $this;
	}
}

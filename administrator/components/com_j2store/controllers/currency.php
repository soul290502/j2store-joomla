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


/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controllerform');

class J2StoreControllerCurrency extends JControllerForm
{

	function save($key = null, $urlVar = null) {
		if(parent::save($key = null, $urlVar = null)) {
			require_once (JPATH_SITE.'/components/com_j2store/helpers/cart.php');
			$storeprofile = J2StoreHelperCart::getStoreAddress();

			if($storeprofile->config_currency_auto) {
				$model = $this->getModel('currencies');
				$model->updateCurrencies(true);
			}

		}
	}
}

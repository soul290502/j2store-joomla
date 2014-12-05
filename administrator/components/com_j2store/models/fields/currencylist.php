<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');


class JFormFieldCurrencyList extends JFormFieldList {

	protected $type = 'CurrencyList';

	public function getInput() {

		require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/models/currencies.php');
		$model = new J2StoreModelCurrencies;
		$currencies = $model->getCurrencies();
		//generate country filter list
		$currency_options = array();
		foreach($currencies as $row) {
			$currency_options[] =  JHTML::_('select.option', $row->currency_code, $row->currency_title);
		}

		return JHTML::_('select.genericlist', $currency_options, $this->name, 'onchange=', 'value', 'text', $this->value);
	}

}

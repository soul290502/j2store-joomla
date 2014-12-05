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


class JFormFieldCountryList extends JFormFieldList {

	protected $type = 'CountryList';

	public function getInput() {

		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'zones.php');
		$model = new J2StoreModelZones;
		$countries = $model->getCountries();
		//generate country filter list
		$country_options = array();
		$country_options[] = JHTML::_('select.option', '', JText::_('J2STORE_SELECT_COUNTRY'));
		foreach($countries as $row) {
			$country_options[] =  JHTML::_('select.option', $row->country_id, $row->country_name);
		}

		return JHTML::_('select.genericlist', $country_options, $this->name, 'onchange=', 'value', 'text', $this->value);
	}

}

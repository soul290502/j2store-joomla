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


class JFormFieldGeoZoneList extends JFormFieldList {

	protected $type = 'GeoZoneList';

	public function getInput() {

		require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/models/taxrates.php');
		$model = new J2StoreModelTaxRates;
		$countries = $model->getGeoZones();
		//generate geozone filter list
		$geozone_options = array();
		$geozone_options[] = JHTML::_('select.option', '', JText::_('J2STORE_SELECT_GEOZONE'));
		foreach($countries as $row) {
			$geozone_options[] =  JHTML::_('select.option', $row->geozone_id, $row->geozone_name);
		}

		return JHTML::_('select.genericlist', $geozone_options, $this->name, 'onchange=', 'value', 'text', $this->value);
	}

}

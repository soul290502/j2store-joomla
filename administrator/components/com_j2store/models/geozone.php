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

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Jsecommerce model.
 *
 * @package		Joomla.Site
 * @subpackage	com_jsecommerce
 * @since 2.5
*/
class J2StoreModelGeoZone extends JModelAdmin
{
	protected $text_prefix = 'COM_J2STORE';

	protected $view_list = 'geozones';

	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();
		// Get the form.
		$form = $this->loadForm('com_j2store.geozone', 'geozone', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	public function getTable($type = 'geozone', $prefix = 'Table', $config = array())
	{

		return JTable::getInstance($type, $prefix, $config);
	}

	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) {
			// Convert the params field to an array.
		}

		return $item;
	}


	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_j2store.edit.geozone.data', array());

		if (empty($data)) {
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('geozone.id') == 0) {
				$app = JFactory::getApplication();
				// set the id here if it does not work.
				//$data->set('pro_id', JRequest::getInt('pro_id', $app->getUserState('com_jsecommerce.profile.filter.category_id')));
			}
		}

		return $data;
	}

	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

	}

	// geo zone Rule functions
	public function saveGeoZoneRule(){

		$app=JFactory::getApplication();
		$data = $app->input->post->get('jform',array(),'array');

		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__j2store_geozonerules AS gr');
		$query->where('gr.geozone_id='.$db->escape($data['geozone_id']));
		$query->where('gr.country_id='.$db->escape($data['country_id']));
		$query->where('gr.zone_id='.$db->escape($data['zone_id']));
		$db->setQuery($query);
		$result = $db->loadResult();

		if($result==1){
			$this->setError(JText::_('J2STORE_GEOZONERULE_ALREADY_EXISTS'));
			return false;
		}

		$geoZoneRule = $this->getTable('geoZoneRule');

		if (!$geoZoneRule->bind($data))
		{
			$this->setError($geoZoneRule->getError());
			return false;
		}

		if (!$geoZoneRule->check())
		{

			$this->setError($geoZoneRule->getError());
			return false;

		}

		if (!$geoZoneRule->store())
		{

			$this->setError($geoZoneRule->getError());
			return false;
		}
		$app->input->set('geozonerule_id',$geoZoneRule->geozonerule_id);
		return true;

	}

	public function getGeoZoneRules ($geozone_id){
		$app = JFactory::getApplication();
		$geozone_id = $app->input->get('geozone_id',0);
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('gr.geozonerule_id as id,gr.country_id,c.country_name as country, z.zone_name as zone,gr.zone_id');
		$query->from('#__j2store_geozonerules AS gr');
		$query->join('LEFT','#__j2store_countries AS c ON c.country_id=gr.country_id');
		$query->join('LEFT','#__j2store_zones AS z ON z.zone_id=gr.zone_id');

		$query->where('gr.geozone_id='.$geozone_id);

		$query->order('gr.ordering');
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getGeoZoneRule($gid){
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('gr.geozonerule_id as id, c.country_name as country, z.zone_name as zone,gr.zone_id,gr.country_id,gr.geozone_id');
		$query->from('#__j2store_geozonerules AS gr');
		$query->join('LEFT','#__j2store_countries AS c ON c.country_id=gr.country_id');
		$query->join('LEFT','#__j2store_zones AS z ON z.zone_id=gr.zone_id');
		$query->where('gr.geozonerule_id='.$gid);
		$db->setQuery($query);
		return $db->loadObject();
	}


	public function getCountry() {
		$app = JFactory::getApplication();
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('a.country_id,a.country_name');
		$query->from('#__j2store_countries AS a');
		$query->where('state = 1');
		$query->order('a.country_name');
		$db->setQuery($query);
		$countries = $db->loadObjectList();
		$array =array();

		foreach($countries as $country)
		{
			$array[$country->country_id] = $country->country_name;
		}
		return $array;
	}

	public function getCountryList() {
		$app = JFactory::getApplication();
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('a.country_id,a.country_name');
		$query->from('#__j2store_countries AS a');
		$query->where('state = 1');
		$db->setQuery($query);
		return $countries = $db->loadObjectList();
	}

	function getZoneList($country_id){
		$app = JFactory::getApplication();
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('a.zone_id,a.zone_name');
		$query->from('#__j2store_zones AS a');
		$query->where('state = 1');
		$query->order('a.zone_name');
		$db->setQuery($query);
		return  $db->loadObjectList();
	}

	public function getZone($country_id) {
		$app = JFactory::getApplication();
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('a.zone_id,a.zone_name');
		$query->from('#__j2store_zones AS a');
		$query->where('state = 1');
		$query->order('a.zone_name');
		$query->where('a.country_id='.$country_id);
		$db->setQuery($query);
		$zones = $db->loadObjectList();
		$json = array();
		foreach($zones as $zone){
			$json[$zone->zone_id] =	$zone->zone_name;
		}
		return $json;
	}



	/* public function getCountries(){
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('a.country_id,a.country_name');
		$query->from('#__j2store_countries AS a');
		$query->where('state = 1');
		$query->order('a.country_name');
		$db->setQuery($query);
		$countries = $db->loadObjectList();
		$array =array();
		foreach($countries as $country){
			//$array[] ="<option value='$country->country_id'>" . $country->country_name . "</option>";
			$array[$country->country_id] = $country->country_name;
		}
		return json_encode($array);
	} */

	public function getZones($country_id=1){
		// Create a new query object.
		$country_id =1;
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('a.zone_id,a.zone_name');
		$query->from('#__j2store_zones AS a');
		$query->where('state = 1');
		$query->where('a.country_id='.$country_id);
		$query->order('a.zone_name');
		$db->setQuery($query);
		$zones = $db->loadObjectList();
		$array =array();
		foreach($zones as $zone){
			$array[] ="<option value='$zone->zone_id'>" . $zone->zone_name . "</option>";
		}
		return json_encode($array);
	}



	public function getCountryOptions(){
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('a.country_id,a.country_name');
		$query->from('#__j2store_countries AS a');
		$query->where('state = 1');
		$query->order('a.country_name');
		$db->setQuery($query);
		$countries = $db->loadObjectList();

		$country_options = array();
		$country_options[] = JHTML::_('select.option', '', JText::_('J2STORE_SELECT_COUNTRY'));
		foreach($countries as $item) {
			$country_options[] =  JHTML::_('select.option', $item->country_id, $item->country_name);
		}

		return $country_options;
	}

}

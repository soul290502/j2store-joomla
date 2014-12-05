<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014-19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controllerform');

class J2StoreControllerGeoZone extends JControllerForm

{
	function __construct($config = array())
	{
		parent::__construct($config);
		// Register Extra tasks
		$this->registerTask( 'save', 'save' );
		$this->registerTask( 'apply', 'save' );
		$this->registerTask( 'trash', 'remove' );
		$this->registerTask( 'delete', 'remove' );
	}

	function save($key = null, $urlVar = null) {
		$app = JFactory::getApplication ();
		$task = $app->input->getString ( 'task' );
		$post = $app->input->getArray ( $_POST );

		$data = $post ['jform'];
		if (isset ( $post ['zone_to_geo_zone'] )) {
			$data ['zone_to_geo_zone'] = $post ['zone_to_geo_zone'];
		}
		JTable::addIncludePath ( JPATH_ADMINISTRATOR . '/components/com_j2store/tables/' );
		$geozone = JTable::getInstance ( 'geozone', 'Table' );
		if ($geozone->save ( $data )) {
			$msg = JText::_ ( 'J2STORE_GEOZONE_SAVED' );
			$msgType = 'message';
		} else {
			$msg = JText::_ ( 'J2STORE_GEOZONE_SAVE_ERROR' );
			$msgType = 'error';
		}
		switch ($task) {
			case 'apply' :
				$link = 'index.php?option=com_j2store&view=geozone&task=geozone.edit&geozone_id=' . $geozone->geozone_id;
				break;
			case 'save' :
				$link = 'index.php?option=com_j2store&view=geozones&task=display';
				break;
		}

		$this->setRedirect ( $link, $msg, $msgType);
	}

	function getZone()
	{

		$app=JFactory::getApplication();
		$data = $app->input->post->get('jform',array(),'array');
		$country_id =isset($data['country_id'])?$data['country_id']:$app->input->getInt('country_id', '0');

		//$country_id = isset($data['country_id'])?$data['country_id']:0;
		$zone_id = isset($data['zone_id'])?$data['zone_id']:$app->input->getInt('zone_id');
		$z_fname =isset($data['field_name'])?$data['field_name']:$app->input->getString('field_name');
		$z_id = isset($data['field_id'])?$data['field_id']:$app->input->getString('field_id');
		/*$z_fname=$data['field_name'];
		$z_id=$data['field_id'];*/

		// based on the country id, get zones and generate a select box
		if(!empty($country_id))
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('zone_id,zone_name');
			$query->from('#__j2store_zones');
			$query->where('country_id='.$country_id);
			$db->setQuery((string)$query);
			$zoneList = $db->loadObjectList();
			$options = array();
			$options[] = JHtml::_('select.option', 0,JTEXT::_('J2STORE_ALL_ZONES'));
			if ($zoneList)
			{
				foreach($zoneList as $zone)
				{
					// this is only to generate the <option> tag inside select tag da i have told n times
					$options[] = JHtml::_('select.option', $zone->zone_id,$zone->zone_name);
				}
			}
			// now we must generate the select list and echo that... wait
			//$z_fname='jform[state_id]';
			$zoneList = JHtml::_('select.genericlist', $options, $z_fname, '', 'value', 'text',$zone_id,$z_id);
			echo $zoneList;
		}
		$app->close();
	}




	/**
	 * Method to delete
	 * Geo Rule of GeoZones
	 * @params
	 */

	function removeGeozoneRule(){

		$app = JFactory::getApplication();
		$post = $app->input->getArray($_POST);
		$georule_id = $post['rule_id'];
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables/');
		$georuleTable = JTable::getInstance('geozonerule','Table');
		$json=array();
		if(!$georuleTable->delete($georule_id)){
			$json['msg'] = $georuleTable->getError();
		}else{
			$json['msg'] = JText::_('J2STORE_GEORULE_DELETED_SUCCESSFULLY');
		}
		echo json_encode($json);
		$app->close();

	}



}

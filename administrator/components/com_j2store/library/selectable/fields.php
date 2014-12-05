<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
#loosely based on Hikashop field class
-------------------------------------------------------------------------*/


/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/selectable/base.php');

class J2StoreSelectableFields {

	protected static $instance;
	var $allValues;
	var $externalValues;

	function __construct($args=array()) {
		$this->externalValues = null;
	}

	public static function getInstance()
	{
		if (!is_object(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	function load($type=''){
		$this->allValues = array();
		$this->allValues["text"] = JText::_('J2STORE_TEXT');
		$this->allValues["email"] = JText::_('J2STORE_EMAIL');
	//	$this->allValues["link"] = JText::_('J2STORE_LINK');
		$this->allValues["textarea"] = JText::_('J2STORE_TEXTAREA');
		$this->allValues["wysiwyg"] = JText::_('J2STORE_WYSIWYG');
		$this->allValues["radio"] = JText::_('J2STORE_RADIO');
		$this->allValues["checkbox"] = JText::_('J2STORE_CHECKBOX');
		$this->allValues["singledropdown"] = JText::_('J2STORE_SINGLEDROPDOWN');
	//	$this->allValues["multipledropdown"] = JText::_('J2STORE_MULTIPLEDROPDOWN');
		$this->allValues["zone"] = JText::_('J2STORE_ZONELIST');
		$this->allValues["date"] = JText::_('J2STORE_DATE');
		$this->allValues["time"] = JText::_('J2STORE_TIME');
		$this->allValues["datetime"] = JText::_('J2STORE_DATETIME');
		$this->allValues["customtext"] = JText::_('J2STORE_CUSTOM_TEXT');

		if($this->externalValues == null) {
			$this->externalValues = array();
			JPluginHelper::importPlugin('j2store');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onJ2StoreFieldsLoad', array( &$this->externalValues ) );

			if(!empty($this->externalValues)) {
				foreach($this->externalValues as $value) {
					if(substr($value->name,0,4) != 'plg.')
						$value->name = 'plg.'.$value->name;
					$this->allValues[$value->name] = $value->text;
				}
			}
		}
	}

	function addJS(){
		$externalJS = '';
		if(!empty($this->externalValues)){
			foreach($this->externalValues as $value) {
				$externalJS .= "\r\n\t\t\t".$value->js;
			}
		}
		$js = "function updateFieldType(){
			newType = document.getElementById('fieldtype').value;
			hiddenAll = new Array('multivalues','cols','rows','size','required','format','zone','coupon','default','customtext','columnname','filtering','maxlength','allow','readonly');
			allTypes = new Array();
			allTypes['text'] = new Array('size','required','default','columnname','filtering','maxlength','readonly');
			allTypes['email'] = new Array('size','required','default','columnname','filtering','maxlength','readonly');
			allTypes['link'] = new Array('size','required','default','columnname','filtering','maxlength','readonly');
			allTypes['textarea'] = new Array('cols','rows','required','default','columnname','filtering','readonly','maxlength');
			allTypes['wysiwyg'] = new Array('cols','rows','required','default','columnname','filtering');
			allTypes['radio'] = new Array('multivalues','required','default','columnname');
			allTypes['checkbox'] = new Array('multivalues','required','default','columnname');
			allTypes['singledropdown'] = new Array('multivalues','required','default','columnname');
			allTypes['multipledropdown'] = new Array('multivalues','size','default','columnname');
			allTypes['date'] = new Array('required','format','size','default','columnname','allow');
			allTypes['time'] = new Array('required','format','size','default','columnname','allow');
			allTypes['datetime'] = new Array('required','format','size','default','columnname','allow');
			allTypes['zone'] = new Array('required','zone','default','columnname');
			allTypes['file'] = new Array('required','default','columnname');
			allTypes['image'] = new Array('required','default','columnname');
			allTypes['coupon'] = new Array('size','required','default','columnname');
			allTypes['customtext'] = new Array('customtext');".$externalJS."
			for (var i=0; i < hiddenAll.length; i++){
				$$('tr[class='+hiddenAll[i]+']').each(function(el) {
					el.style.display = 'none';
				});
			}
			for (var i=0; i < allTypes[newType].length; i++){
				$$('tr[class='+allTypes[newType][i]+']').each(function(el) {
					el.style.display = '';
				});
			}
		}
		window.addEvent('domready', function(){ updateFieldType(); });";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
	}

	public function display($map,$value,$type){
		$this->load($type);
		$this->addJS();

		$this->values = array();
		foreach($this->allValues as $oneType => $oneVal){
			$this->values[] = JHTML::_('select.option', $oneType,$oneVal);
		}

		return JHTML::_('select.genericlist', $this->values, $map , 'size="1" onchange="updateFieldType();"', 'value', 'text', (string) $value,'fieldtype');
	}
}

class j2storeZoneType {
	function load($form=false){
		$this->values = array();
		if(!$form){
			$this->values[] = JHTML::_('select.option', '', JText::_('J2STORE_ALL_ZONES') );
		}
		$this->values[] = JHTML::_('select.option', 'country',JText::_('J2STORE_COUNTRIES'));
		$this->values[] = JHTML::_('select.option', 'zone',JText::_('J2STORE_ZONES'));
	}

	function display($map,$value,$form=false){
		$this->load($form);
		$dynamic = ($form ? '' : 'onchange="document.adminForm.submit( );"');
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"'. $dynamic, 'value', 'text', $value );
	}
}


class j2storeCountryType{
	var $type = 'country';
	var $published = false;
	var $allName = 'J2STORE_ALL_ZONES';
	var $country_name = '';
	var $country_id = '';

	function load(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		if($this->type == 'country') {
			$query->select('a.*')->from('#__j2store_countries AS a');
			$query->where('a.state=1')
				->order('a.country_name ASC');
		} elseif($this->type == 'zone') {
			$query->select('a.*')->from('#__j2store_zones AS a');
			$query->where('a.state=1')
				->order('a.zone_name ASC');
			if(isset($this->country_id)) {
				$query->where('a.country_id='.$this->country_id);
			}
		}
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	function display($map, $value, $form = true, $options = 'class="inputbox" size="1"',$id=false){
		$countries = $this->load();
		$this->values = array();
		if($form){
			$this->values[] = JHTML::_('select.option', '0', JText::_($this->allName) );
			//$options .= ' onchange="document.adminForm.submit( );"';
		}
		foreach($countries as $country){
			$this->values[] = JHTML::_('select.option', $country->country_id, $country->country_name);
		}
		return JHTML::_('select.genericlist', $this->values, $map, $options, 'value', 'text', (int)$value, $id );
	}


	function displayZone($map, $value, $form = true, $options = 'class="inputbox" size="1"',$id=false){
		$zones = $this->load();
		$this->values = array();
		if($form){
			$this->values[] = JHTML::_('select.option', '0', JText::_('J2STORE_SELECT_STATE') );
			//$options .= ' onchange="document.adminForm.submit( );"';
		}
		foreach($zones as $zone){
			$this->values[] = JHTML::_('select.option', $zone->zone_id, $zone->zone_name);
		}
		return JHTML::_('select.genericlist', $this->values, $map, $options, 'value', 'text', (int)$value, $id );
	}

}

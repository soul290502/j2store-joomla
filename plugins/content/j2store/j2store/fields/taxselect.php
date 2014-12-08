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

// no direct access
defined('_JEXEC') or die('Restricted access');

class JFormFieldTaxSelect extends JFormFieldList
{
	protected $type = 'taxselect';

	function getInput(){

		$lists = $this->_getSelectProfiles($this->name, $this->value);
		return $lists;
	}

	function _getSelectProfiles($fieldName, $default) {

		$db = JFactory::getDBO();
		$option ='';



		$query = 'SELECT taxprofile_id AS value, taxprofile_name AS text FROM #__j2store_taxprofiles WHERE state=1 ORDER BY taxprofile_id';
		$db->setQuery( $query );
		$taxprofiles = $db->loadObjectList();

		$types[] 		= JHTML::_('select.option',  '0', JText::_( 'J2STORE_SELECT_TAXPROFILE' ) );
		foreach( $taxprofiles as $item )
		{
			$types[] = JHTML::_('select.option',  $item->value, JText::_( $item->text ) );
		}

		$lists 	= JHTML::_('select.genericlist',   $types, $fieldName, 'class="inputbox list" size="1" '.$option.'', 'value', 'text', $default );

		return $lists;

	}
}

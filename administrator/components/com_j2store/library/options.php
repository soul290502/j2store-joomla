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


class J2StoreHelperOptions {


	public static function convertKeysToText($value) {
		$text = '';
		switch($value) {

			case 'select':
				$text = JText::_('J2STORE_SELECT');
				break;
			case 'radio':
				$text = JText::_('J2STORE_RADIO');
				break;
			case 'checkbox':
				$text = JText::_('J2STORE_CHECKBOX');
				break;
			case 'text':
				$text = JText::_('J2STORE_TEXT');
				break;
			case 'textarea':
				$text = JText::_('J2STORE_TEXTAREA');
				break;
			case 'date':
					$text = JText::_('J2STORE_DATE');
					break;
			case 'time':
				$text = JText::_('J2STORE_TIME');
				break;
			case 'datetime':
				$text = JText::_('J2STORE_DATETIME');
				break;
			default:
				$text = $value;
				break;

		}
		return $text;
	}

	public static function getProductOptionValues($option_id, $default, $product_optionvalue_id) {

		//load option values for this option id from the general and pass the default value to it.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('ov.optionvalue_id,ov.optionvalue_name');
		$query->from('#__j2store_optionvalues AS ov');
		$query->where('ov.option_id='.$option_id);
		$query->order('ov.ordering ASC');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$list = array ();
		if(count($rows)) {
			foreach($rows as $row) {
				$options[] = JHtmlSelect::option($row->optionvalue_id, $row->optionvalue_name);
			}
			$name = "optionvalue_id[{$product_optionvalue_id}]";
			$attribs = array('class' => 'inputbox', 'size'=>'1', 'title'=>JText::_('J2STORE_SELECT_AN_OPTION'));
			$list = JHtmlSelect::genericlist($options, $name, $attribs, 'value', 'text', $default, 'optionvalue_id');
		}
		return $list;
	}

}
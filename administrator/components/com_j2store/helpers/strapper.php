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

class J2StoreStrapper {

	public static function addJS() {
		$mainframe = JFactory::getApplication();
		$j2storeparams = JComponentHelper::getParams('com_j2store');
		$document =JFactory::getDocument();

		//load name spaced jquery only for j 2.5
		if (!version_compare(JVERSION, '3.0', 'ge'))
		{
			if($j2storeparams->get('load_jquery', 1)) {
				$document->addScript(JURI::root(true).'/media/j2store/js/j2storejq.js');
			}
			$document->addScript(JURI::root(true).'/media/j2store/js/bootstrap.min.js');
		} else {
			JHtml::_('jquery.framework');
			JHtml::_('bootstrap.framework');
		}
		//load name spaced jqueryui
		//load name spacer
		$document->addScript(JURI::root(true).'/media/j2store/js/j2store.namespace.js');
		$document->addScript(JURI::root(true).'/media/j2store/js/j2storejqui.js');
		$document->addScript(JUri::root(true).'/media/j2store/js/jquery-ui-timepicker-addon.js');

		if($mainframe->isAdmin()) {
			$document->addScript(JURI::root(true).'/media/j2store/js/jquery.validate.min.js');
			$document->addScript(JURI::root(true).'/media/j2store/js/j2store_admin.js');
		}
		else {
	//		$document->addScript(JUri::root(true).'/media/j2store/js/jquery-ui-timepicker-addon.js');
			$document->addScript(JURI::root(true).'/media/j2store/js/j2store.js');
		}

	}

	public static function addCSS() {
		$mainframe = JFactory::getApplication();
		$j2storeparams = JComponentHelper::getParams('com_j2store');
		$document =JFactory::getDocument();

		if (!version_compare(JVERSION, '3.0', 'ge'))
		{
			if($mainframe->isAdmin()) {
				//always load bootstrap for J 2.5 admin side
				$document->addStyleSheet(JURI::root(true).'/media/j2store/css/bootstrap.min.css');
			}

			//for site side, check if the param is enabled.
			if($mainframe->isSite() && $j2storeparams->get('load_bootstrap', 1)) {
				$document->addStyleSheet(JURI::root(true).'/media/j2store/css/bootstrap.min.css');
			}
		}

		if($mainframe->isAdmin()) {
			$document->addStyleSheet(JURI::root(true).'/media/j2store/css/jquery-ui-custom.css');
			$document->addStyleSheet(JURI::root(true).'/media/j2store/css/j2store_admin.css');
		}
		else {
		//	$document->addStyleSheet(JURI::root(true).'/media/j2store/css/jquery-ui-custom.css');

			// Add related CSS to the <head>
			if ($document->getType() == 'html' && $j2storeparams->get('j2store_enable_css')) {

				$template = self::getDefaultTemplate();

				jimport('joomla.filesystem.file');
				// j2store.css
				if(JFile::exists(JPATH_SITE.'/templates/'.$template .'/css/j2store.css'))
					$document->addStyleSheet(JURI::root(true).'/templates/'.$template .'/css/j2store.css');
				else
					$document->addStyleSheet(JURI::root(true).'/media/j2store/css/j2store.css');

			} else {
				$document->addStyleSheet(JURI::root(true).'/media/j2store/css/j2store.css');
			}
	}

	}
	
	public static function getDefaultTemplate() {
	
		static $tsets;
	
		if ( !is_array( $tsets ) )
		{
			$tsets = array( );
		}
		$id = 1;
		if(!isset($tsets[$id])) {
			$db = JFactory::getDBO();
			$query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home=1";
			$db->setQuery( $query );
			$tsets[$id] = $db->loadResult();
		}
		return $tsets[$id];
	}

	public static function getTimePickerScript($date_format='', $time_format='', $prefix='j2store', $isAdmin=false) {

		//initialise the date/time picker
		if($isAdmin) {
			$document =JFactory::getDocument();
			$document->addScript(JUri::root(true).'/media/j2store/js/jquery-ui-timepicker-addon.js');
			$document->addStyleSheet(JURI::root(true).'/media/j2store/css/jquery-ui-custom.css');
		}

		if(empty($date_format)) {
			$date_format = 'yy-mm-dd';
		}

		if(empty($time_format)) {
			$time_format = 'HH:mm';
		}

		$element_date = $prefix.'_date';
		$element_time = $prefix.'_time';
		$element_datetime = $prefix.'_datetime';

		//localisation
		$currentText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_CURRENT_TEXT'));
		$closeText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_CLOSE_TEXT'));
		$timeOnlyText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_CHOOSE_TIME'));
		$timeText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_TIME'));
		$hourText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_HOUR'));
		$minuteText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_MINUTE'));
		$secondText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_SECOND'));
		$millisecondText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_MILLISECOND'));
		$timezoneText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_TIMEZONE'));

		$localisation ="
		currentText: '$currentText',
		closeText: '$closeText',
		timeOnlyTitle: '$timeOnlyText',
		timeText: '$timeText',
		hourText: '$hourText',
		minuteText: '$minuteText',
		secondText: '$secondText',
		millisecText: '$millisecondText',
		timezoneText: '$timezoneText'
		";

		$timepicker_script ="
		if(typeof(j2store) == 'undefined') {
		var j2store = {};
	}

	if(typeof(jQuery) != 'undefined') {
	jQuery.noConflict();
	}

	if(typeof(j2store.jQuery) == 'undefined') {
	j2store.jQuery = jQuery.noConflict();
	}

	if(typeof(j2store.jQuery) != 'undefined') {

	(function($) {
	$(document).ready(function(){
	//date, time, datetime
	if ($.browser.msie && $.browser.version == 6) {
	$('.$element_date, .$element_datetime, .$element_time').bgIframe();
	}

		$('.$element_date').datepicker({dateFormat: '$date_format'});
				$('.$element_datetime').datetimepicker({
						dateFormat: '$date_format',
						timeFormat: '$time_format',
						$localisation
	});

						$('.$element_time').timepicker({timeFormat: '$time_format', $localisation});

	});
	})(j2store.jQuery);
	}
			";

			return $timepicker_script;

	}


}
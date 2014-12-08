<?php
/*------------------------------------------------------------------------
# com_j2store - J2Store v 1.0
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die;
/**
 * Metics Form Field class for the J2Store component
 */
class JFormFieldJ2Plugins extends JFormField
{
	/**
	* The field type.
	*
	* @var		string
	*/
	protected $type = 'J2Plugins';

	protected function getInput() {

		$app = JFactory::getApplication();
		$product_id = $app->input->getInt('id');

		JPluginHelper::importPlugin('j2store');
		$results = $app->triggerEvent('onJ2StoreProductFormInput', array($product_id, $this));
		return trim(implode('/n', $results));

	}

	protected function getLabel() {

		$app = JFactory::getApplication();
		$product_id = $app->input->getInt('id');

		JPluginHelper::importPlugin('j2store');
		$results = $app->triggerEvent('onJ2StoreProductFormLabel', array($product_id, $this));
		return trim(implode('/n', $results));
	}

}
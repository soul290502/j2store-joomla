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


class JFormFieldWeightList extends JFormFieldList {

	protected $type = 'WeightList';

	public function getInput() {

		require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/models/weights.php');
		$model = new J2StoreModelWeights;
		$lengths = $model->getWeights();
		//generate country filter list
		$length_options = array();
		foreach($lengths as $row) {
			$length_options[] =  JHTML::_('select.option', $row->weight_class_id, $row->weight_title);
		}

		return JHTML::_('select.genericlist', $length_options, $this->name, 'onchange=', 'value', 'text', $this->value);
	}

}

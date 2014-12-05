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


class JFormFieldLengthList extends JFormFieldList {

	protected $type = 'LengthList';

	public function getInput() {

		require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/models/lengths.php');
		$model = new J2StoreModelLengths;
		$lengths = $model->getLengths();
		//generate country filter list
		$length_options = array();
		foreach($lengths as $row) {
			$length_options[] =  JHTML::_('select.option', $row->length_class_id, $row->length_title);
		}

		return JHTML::_('select.genericlist', $length_options, $this->name, 'onchange=', 'value', 'text', $this->value);
	}

}

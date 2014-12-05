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


class JFormFieldShippingType extends JFormFieldList {

	protected $type = 'ShippingType';

	public function getInput() {

		require_once(JPATH_COMPONENT_ADMINISTRATOR.'/library/select.php');
		$html = J2StoreSelect::shippingtype($this->value, $this->name);
		return $html;
	}

}

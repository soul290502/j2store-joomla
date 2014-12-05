<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controllerform');

class J2StoreControllerStoreProfile extends JControllerForm
{
	function save($key = null, $urlVar = null) {
		if(parent::save($key = null, $urlVar = null)) {
			$post = JFactory::getApplication()->input->getArray($_POST);
			if($post['config_currency_auto']) {
				$model = $this->getModel('currencies');
				$model->updateCurrencies(true);
			}

		}
	}


	function populatedata() {

		$app = JFactory::getApplication();
		$store_id = $app->input->getInt('store_id', 0);
		$post = $app->input->get('jform', array(), 'array');
		$error = false;
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
		$item = JTable::getInstance('StoreProfile', 'Table');

		if(isset($store_id)) {
			$post['store_id'] = $store_id;
		}

		try {
			$item->bind($post);
		} catch (Exception $e) {
			$error = $e->getMessage();
		}

		$register_layout = '<div class="row-fluid">
		<div class="span5">[first_name] [last_name] [email] [phone_1] [phone_2] [password] [confirm_password]</div>
		<div class="span5">[company] [tax_number] [address_1] [address_2] [city] [zip] [country_id] [zone_id]</div>
		</div>';

		$billing_layout ='<div class="row-fluid">
		<div class="span6">[first_name] [last_name] [phone_1] [phone_2] [company] [tax_number]</div>
		<div class="span6">[address_1] [address_2] [city] [zip] [country_id] [zone_id]</div>
		</div>';

		$shipping_layout ='<div class="row-fluid">
		<div class="span6">[first_name] [last_name] [phone_1] [phone_2] [company]</div>
		<div class="span6">[address_1] [address_2] [city] [zip] [country_id] [zone_id]</div>
		</div>';

		$guest_layout = '<div class="row-fluid">
		<div class="span6">[first_name] [last_name] [email] [phone_1] [phone_2] [country_id] [zone_id] </div>
		<div class="span6">[company] [tax_number] [address_1] [address_2] [city] [zip] </div>
		</div>';

		$guest_shipping_layout = '<div class="row-fluid">
		<div class="span6">[first_name] [last_name] [phone_1] [phone_2] [country_id] [zone_id]</div>
		<div class="span6">[company] [address_1] [address_2] [city] [zip]</div>
		</div>';

		$item->store_register_layout = $register_layout;
		$item->store_billing_layout = $billing_layout;
		$item->store_shipping_layout = $shipping_layout;
		$item->store_guest_layout = $guest_layout;
		$item->store_guest_shipping_layout = $guest_shipping_layout;
		$item->state = 1;
		try {
			$item->store();
		} catch (Exception $e) {
			echo $error = $e->getMessage();
		}

		if($error) {
			$msg = JText::_('J2STORE_ERROR_SAVE');
		} else {
			$msg = JText::_('J2STORE_ALL_CHANGES_SAVED');
		}

		$this->setRedirect('index.php?option=com_j2store&view=storeprofile&task=storeprofile.edit&store_id='.$item->store_id, $msg);
	}

}
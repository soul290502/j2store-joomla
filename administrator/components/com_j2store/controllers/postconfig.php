<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    priyabose - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/



// no direct access
defined('_JEXEC') or die('Restricted access');
class J2StoreControllerPostconfig extends J2StoreController
{

	function save() {

		$app = JFactory::getApplication();
		$post = $app->input->getArray($_POST);
		$json = array();

		if(empty($post['store_name'])) {
			$json['error']['store_name'] = JText::_('J2STORE_FIELD_REQUIRED');
		}

		if(empty($post['store_address_1'])) {
			$json['error']['store_address_1'] = JText::_('J2STORE_FIELD_REQUIRED');
		}

		if(empty($post['store_city'])) {
			$json['error']['store_city'] = JText::_('J2STORE_FIELD_REQUIRED');
		}

		if(empty($post['store_zip'])) {
			$json['error']['store_zip'] = JText::_('J2STORE_FIELD_REQUIRED');
		}

		if(empty($post['country_id'])) {
			$json['error']['country_id'] = JText::_('J2STORE_FIELD_REQUIRED');
		}

		if(empty($post['config_currency'])) {
			$json['error']['config_currency'] = JText::_('J2STORE_FIELD_REQUIRED');
		}

		if(!$json) {
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
			$table = JTable::getInstance('Storeprofile', 'Table');

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

			$post['store_register_layout'] = $register_layout;
			$post['store_billing_layout'] = $billing_layout;
			$post['store_shipping_layout']= $shipping_layout;
			$post['store_guest_layout'] = $guest_layout;
			$post['store_guest_shipping_layout'] = $guest_shipping_layout;

			$post['state'] = 1;
			$table->bind($post);
			$table->store();
			$json['redirect'] = 'index.php?option=com_j2store&view=cpanel';
		}
		echo json_encode($json);
		$app->close();

	}

}
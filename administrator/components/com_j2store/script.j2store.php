<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
 # ------------------------------------------------------------------------
 # author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
 # copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
 # @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 # Websites: http://j2store.org
 # Technical Support:  Forum - http://j2store.org/forum/index.html
 -------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
class Com_J2StoreInstallerScript {

	/** @var string The component's name */
	protected $_extension_name = 'com_j2store';
	private $RemovePlugins = array(
			'user' => array(
					'j2store'
			)
	);

	private $RemoveFilesAdmin = array(
			'controllers' => array(
					'shippingmethods'
			),
			'models' => array(
					'shippingmethods',
					'shippingrates'
			),
			'views' => array(
					'shippingrates'
			),
			'tables' => array(
					'shippingmethods',
					'shippingrates'
			)

	);

	private $RemoveFilesSite = array();

	function preflight( $type, $parent ) {
		$jversion = new JVersion();
		//check for minimum requirement
		// abort if the current Joomla release is older
		if( version_compare( $jversion->getShortVersion(), '2.5.6', 'lt' ) ) {
			Jerror::raiseWarning(null, 'Cannot install J2Store in a Joomla release prior to 2.5.6');
			return false;
		}

		// Only allow to install on Joomla! 2.5.0 or later with PHP 5.3.0 or later
		if(defined('PHP_VERSION')) {
			$version = PHP_VERSION;
		} elseif(function_exists('phpversion')) {
			$version = phpversion();
		} else {
			$version = '5.0.0'; // all bets are off!
		}

		if(!version_compare($version, '5.3.1', 'ge')) {
			$msg = "<p>You need PHP 5.3.1 or later to install this component</p>";
			if(version_compare(JVERSION, '3.0', 'gt'))
			{
				JLog::add($msg, JLog::WARNING, 'jerror');
			}
			else
			{
				JError::raiseWarning(100, $msg);
			}
			return false;
		}

		// Bugfix for "Can not build admin menus"
		if(in_array($type, array('install')))
		{
			$this->_bugfixDBFunctionReturnedNoError();
		} elseif ($type != 'discover_install')
		{
			$this->_bugfixCantBuildAdminMenus();
			$this->_resetLiveUpdate();
		}


		//check j2store
		$xmlfile = JPATH_ADMINISTRATOR.'/components/com_j2store/manifest.xml';
		if(JFile::exists($xmlfile)) {
			$xml = JFactory::getXML($xmlfile);
			$version=(string)$xml->version;

			//check for minimum requirement
			// abort if the current J2Store release is older
			if( version_compare( $version, '2.0.2', 'lt' ) ) {
				Jerror::raiseWarning(null, 'You should first upgrade to J2Store 2.0.2 and then install the latest version. Otherwise, the changes made till 2.0.2 wont be reflected in your install');
				return false;
			}


			//if exists, get the privious version.
			$previous_version = $this->_getPreviousVersion();
			//if null, or version not equal to 2.0.2 update the files. But if the version is 2.0.2, then the user is re-installing
			if(is_null($previous_version) || $previous_version !='2.0.2' ) {
				$file = JPATH_ADMINISTRATOR.'/components/com_j2store/pre-version.txt';
				$buffer = $version;
				JFile::write($file, $buffer);
			}
		}

	}

	function install() {

		$this->_doDBChanges('install');
		$this->_modifyExistingTables('install');
	}

	function update($parent) {
		$this->_doDBChanges('update');
		$this->_modifyExistingTables('update');
		$this->_doCompatibilityChecks();
	}

	public function postflight($type, $parent)
	{
		$db = JFactory::getDBO();
		$app = JFactory::getApplication('site');
		$status = new stdClass;
		$status->modules = array();
		$status->plugins = array();
		$src = $parent->getParent()->getPath('source');
		$manifest = $parent->getParent()->manifest;
		$modules = $manifest->xpath('modules/module');
		foreach ($modules as $module)
		{
			$name = (string)$module->attributes()->module;
			$client = (string)$module->attributes()->client;
			$modulePublished = (string)$module->attributes()->publish;
			$modulePosition = (string)$module->attributes()->position;
			if (is_null($client))
			{
				$client = 'site';
			}
			$path = $src.'/modules/'.$name;
			$installer = new JInstaller;
			$result = $installer->install($path);

			if($client == 'administrator') {
					//auto publish the admin modules
					$sql = $db->getQuery(true)
							->update($db->qn('#__modules'))
							->set($db->qn('position').' = '.$db->q($modulePosition))
							->where($db->qn('module').' = '.$db->q($name));
						if($modulePublished) {
							$sql->set($db->qn('published').' = '.$db->q('1'));
						}
						$db->setQuery($sql);
						$db->execute();

						// Link to all pages
						$query = $db->getQuery(true);
						$query->select('id')->from($db->qn('#__modules'))
							->where($db->qn('module').' = '.$db->q($name));
						$db->setQuery($query);
						$moduleid = $db->loadResult();

						$query = $db->getQuery(true);
						$query->select('*')->from($db->qn('#__modules_menu'))
							->where($db->qn('moduleid').' = '.$db->q($moduleid));
						$db->setQuery($query);
						$assignments = $db->loadObjectList();
						$isAssigned = !empty($assignments);
						if(!$isAssigned) {
							$o = (object)array(
								'moduleid'	=> $moduleid,
								'menuid'	=> 0
							);
							$db->insertObject('#__modules_menu', $o);
						}

			}

			$status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);
		}

		$plugins = $manifest->xpath('plugins/plugin');
		foreach ($plugins as $plugin)
		{
			$name = (string)$plugin->attributes()->plugin;
			$group = (string)$plugin->attributes()->group;
			$path = $src.'/plugins/'.$group;
			if (JFolder::exists($src.'/plugins/'.$group.'/'.$name))
			{
				$path = $src.'/plugins/'.$group.'/'.$name;
			}
			$installer = new JInstaller;
			$result = $installer->install($path);

			if($type !='update') {
				$query = "UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element=".$db->Quote($name)." AND folder=".$db->Quote($group);
				$db->setQuery($query);
				$db->query();
			}

			$status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
		}
		$query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home=1";
		$db->setQuery( $query );
		$template = $db->loadResult();
		//rename the override folder if exists
		$src = JPATH_SITE.'/templates/'.$template.'/html/com_j2store';
		$dest = JPATH_SITE.'/templates/'.$template.'/html/old_com_j2store';
		require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/version.php');
		if(J2StoreVersion::getPreviousVersion() == '2.0.2' &&
			JFolder::exists($src )
				) {
			JFolder::move($src, $dest);
		}

		//remove obsolete plugins
		$this->_removeObsoletePlugins($parent);

		//remove obsolete files
		$this->_removeObsoleteFiles($parent);

		//rebuild the menu
		$this->_rebuildMenus();
		$this->installationResults($status);
		// Kill update site
		$this->_killUpdateSite();

		$this->_configMigration($type, $parent);

	}

	public function uninstall($parent)
	{
		$db = JFactory::getDBO();
		$status = new stdClass;
		$status->modules = array();
		$status->plugins = array();
		$manifest = $parent->getParent()->manifest;
		$plugins = $manifest->xpath('plugins/plugin');
		foreach ($plugins as $plugin)
		{
			$name = (string)$plugin->attributes()->plugin;
			$group = (string)$plugin->attributes()->group;
			$query = "SELECT `extension_id` FROM #__extensions WHERE `type`='plugin' AND element = ".$db->Quote($name)." AND folder = ".$db->Quote($group);
			$db->setQuery($query);
			$extensions = $db->loadColumn();
			if (count($extensions))
			{
				foreach ($extensions as $id)
				{
					$installer = new JInstaller;
					$result = $installer->uninstall('plugin', $id);
				}
				$status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
			}

		}
		$modules = $manifest->xpath('modules/module');
		foreach ($modules as $module)
		{
			$name = (string)$module->attributes()->module;
			$client = (string)$module->attributes()->client;
			$db = JFactory::getDBO();
			$query = "SELECT `extension_id` FROM `#__extensions` WHERE `type`='module' AND element = ".$db->Quote($name)."";
			$db->setQuery($query);
			$extensions = $db->loadColumn();
			if (count($extensions))
			{
				foreach ($extensions as $id)
				{
					$installer = new JInstaller;
					$result = $installer->uninstall('module', $id);
				}
				$status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);
			}

		}
		$this->uninstallationResults($status);
	}

	private function _doDBChanges($type='') {

		$db = JFactory::getDbo();
		//get the table list
		$tables = $db->getTableList();
		//get prefix
		$prefix = $db->getPrefix();

		//store profile
		if(!in_array($prefix.'j2store_storeprofiles', $tables)){
			$query = "
			CREATE TABLE IF NOT EXISTS `#__j2store_storeprofiles` (
			  `store_id` int(11) NOT NULL AUTO_INCREMENT,
			  `store_name` varchar(255) NOT NULL,
			  `store_desc` varchar(255) NOT NULL,
			  `store_address_1` varchar(255) NOT NULL,
			  `store_address_2` varchar(255) NOT NULL,
			  `store_city` varchar(255) NOT NULL,
			  `store_zip` varchar(255) NOT NULL,
			  `country_id` int(11) NOT NULL,
			  `zone_id` int(11) NOT NULL,
			  `country_name` varchar(255) NOT NULL,
			  `zone_name` varchar(255) NOT NULL,
			  `config_currency` varchar(255) NOT NULL,
			  `config_currency_auto` tinyint(1) NOT NULL,
			  `config_length_class_id` int(11) NOT NULL COMMENT 'FK to length class table',
			  `config_weight_class_id` int(11) NOT NULL COMMENT 'FK to weight class table',
			  `config_default_category` int(11) NOT NULL,
			  `state` int(11) NOT NULL,
			  `ordering` int(11) NOT NULL,
			  `store_min_out_qty` varchar(255) NOT NULL,
			  `store_min_sale_qty` varchar(255) NOT NULL,
			  `store_max_sale_qty` varchar(255) NOT NULL,
			  `store_notify_qty` varchar(255) NOT NULL,
			  `store_register_layout` longtext NOT NULL,
			  `store_billing_layout` longtext NOT NULL,
			  `store_shipping_layout` longtext NOT NULL,
			  `store_guest_layout` longtext NOT NULL,
			  `store_guest_shipping_layout` longtext NOT NULL,
			  `store_payment_layout` longtext NOT NULL,
			   `store_invoice_prefix` varchar(255) NOT NULL,
			  PRIMARY KEY (`store_id`)
			) DEFAULT CHARSET=utf8;
			";
			$this->_executeQuery($query);
		}

		//geozonerules
		if(!in_array($prefix.'j2store_geozonerules', $tables)){
			$query = "
			CREATE TABLE IF NOT EXISTS `#__j2store_geozonerules` (
			`geozonerule_id` int(11) NOT NULL AUTO_INCREMENT,
			`geozone_id` int(11) NOT NULL,
			`country_id` int(11) NOT NULL,
			`zone_id` int(11) NOT NULL,
			`ordering` int(11) NOT NULL,
			PRIMARY KEY (`geozonerule_id`)
			)DEFAULT CHARSET=utf8;
			";
			$this->_executeQuery($query);
		}

		//geozones
		if(!in_array($prefix.'j2store_geozones', $tables)){
			$query = "
			CREATE TABLE IF NOT EXISTS `#__j2store_geozones` (
			`geozone_id` int(11) NOT NULL AUTO_INCREMENT,
			`geozone_name` varchar(255) NOT NULL,
			`state` int(11) NOT NULL,
			`ordering` int(11) NOT NULL,
			PRIMARY KEY (`geozone_id`)
			)DEFAULT CHARSET=utf8;
			";
			$this->_executeQuery($query);
		}

		//tax rates
		if(!in_array($prefix.'j2store_taxrates', $tables)){
			$query = "
			CREATE TABLE IF NOT EXISTS `#__j2store_taxrates` (
			`taxrate_id` int(11) NOT NULL AUTO_INCREMENT,
			`geozone_id` int(11) NOT NULL,
			`taxrate_name` varchar(255) NOT NULL,
			`tax_percent` decimal(11,3) NOT NULL,
			`state` int(11) NOT NULL,
			`ordering` int(11) NOT NULL,
			PRIMARY KEY (`taxrate_id`)
			)DEFAULT CHARSET=utf8;
			";
			$this->_executeQuery($query);
		}

		//tax rules
		if(!in_array($prefix.'j2store_taxrules', $tables)){
			$query = "
			CREATE TABLE IF NOT EXISTS `#__j2store_taxrules` (
			`taxrule_id` int(11) NOT NULL AUTO_INCREMENT,
			`taxprofile_id` int(11) NOT NULL,
			`taxrate_id` int(11) NOT NULL,
			`address` varchar(255) NOT NULL,
			`ordering` int(11) NOT NULL,
			`state` int(11) NOT NULL,
			PRIMARY KEY (`taxrule_id`)
			)DEFAULT CHARSET=utf8;
			";
			$this->_executeQuery($query);
		}

		//product dicount prices

		if(!in_array($prefix.'j2store_productprices', $tables)){
			$query = "
			CREATE TABLE IF NOT EXISTS `#__j2store_productprices` (
			`productprice_id` int(11) NOT NULL AUTO_INCREMENT,
			`product_id` int(11) NOT NULL,
			`quantity_start` int(11) NOT NULL,
			`condition` varchar(255) NOT NULL,
			`quantity_end` int(11) NOT NULL,
			`publish_up` datetime NOT NULL,
			`publish_down` datetime NOT NULL,
			`state` int(11) NOT NULL COMMENT 'publish or unpublish',
			`price` decimal(12,3) NOT NULL,
			`ordering` int(11) NOT NULL,
			PRIMARY KEY (`productprice_id`)
			) DEFAULT CHARSET=utf8;
			";
			$this->_executeQuery($query);
		}

		//product options, values - since j2store 2.5
		if(!in_array($prefix.'j2store_options', $tables)){
			$query = "
			CREATE TABLE IF NOT EXISTS `#__j2store_options` (
			`option_id` int(11) NOT NULL AUTO_INCREMENT,
			`type` varchar(255) NOT NULL,
			`option_unique_name` varchar(255) NOT NULL,
			`option_name` varchar(255) NOT NULL,
			`ordering` int(11) NOT NULL,
			`state` int(11) NOT NULL,
			PRIMARY KEY (`option_id`)
			) DEFAULT CHARSET=utf8;
			";

			$this->_executeQuery($query);
		}

		if(!in_array($prefix.'j2store_optionvalues', $tables)){
			$query = "
			CREATE TABLE IF NOT EXISTS `#__j2store_optionvalues` (
			`optionvalue_id` int(11) NOT NULL AUTO_INCREMENT,
			`option_id` int(11) NOT NULL,
			`optionvalue_name` varchar(255) NOT NULL,
			`ordering` int(11) NOT NULL,
			PRIMARY KEY (`optionvalue_id`)
			) DEFAULT CHARSET=utf8;
			";

			$this->_executeQuery($query);
		}


		if(!in_array($prefix.'j2store_product_options', $tables)){
			$query = "
			CREATE TABLE IF NOT EXISTS `#__j2store_product_options` (
			`product_option_id` int(11) NOT NULL AUTO_INCREMENT,
			`product_id` int(11) NOT NULL,
			`option_id` int(11) NOT NULL,
			`option_value` varchar(255) NOT NULL,
			`required` tinyint(1) NOT NULL,
			PRIMARY KEY (`product_option_id`)
			) DEFAULT CHARSET=utf8;
			";
			$this->_executeQuery($query);
		}

		if(!in_array($prefix.'j2store_product_optionvalues', $tables)){
			$query = "
			CREATE TABLE IF NOT EXISTS `#__j2store_product_optionvalues` (
			`product_optionvalue_id` int(11) NOT NULL AUTO_INCREMENT,
			`product_option_id` int(11) NOT NULL,
			`product_id` int(11) NOT NULL,
			`option_id` int(11) NOT NULL,
			`optionvalue_id` int(11) NOT NULL,
			`product_optionvalue_price` decimal(11,3) NOT NULL,
			`product_optionvalue_prefix` varchar(255) CHARACTER SET utf8 NOT NULL,
			`product_optionvalue_weight` decimal(15,8) NOT NULL,
  			`product_optionvalue_weight_prefix` varchar(1) NOT NULL,
			`ordering` int(11) NOT NULL,
			PRIMARY KEY (`product_optionvalue_id`)
			) DEFAULT CHARSET=utf8;
			";
			$this->_executeQuery($query);
		}

		if(!in_array($prefix.'j2store_productquantities', $tables)){
			$query = "
			CREATE TABLE IF NOT EXISTS `#__j2store_productquantities` (
			`productquantity_id` int(11) NOT NULL AUTO_INCREMENT,
			`product_attributes` text NOT NULL COMMENT 'A CSV of productattributeoption_id values, always in numerical order',
			`product_id` int(11) NOT NULL,
			`quantity` int(11) NOT NULL,
			PRIMARY KEY (`productquantity_id`),
			KEY `product_id` (`product_id`)
			) DEFAULT CHARSET=utf8;
			";
			$this->_executeQuery($query);
		}

		if(!in_array($prefix.'j2store_coupons', $tables)){
			$query = "
			CREATE TABLE IF NOT EXISTS `#__j2store_coupons` (
			`coupon_id` int(11) NOT NULL AUTO_INCREMENT,
			`coupon_name` varchar(255) NOT NULL,
			`coupon_code` varchar(255) NOT NULL,
			`state` tinyint(2) NOT NULL,
			`value` int(11) NOT NULL,
			`value_type` char(1) NOT NULL,
			`max_uses` int(11) NOT NULL,
			`logged` int(11) NOT NULL,
			`max_customer_uses` int(11) NOT NULL,
			`valid_from` datetime NOT NULL,
			`valid_to` datetime NOT NULL,
			`product_category` varchar(255) NOT NULL,
			PRIMARY KEY (`coupon_id`)
			)DEFAULT CHARSET=utf8;
			";
			$this->_executeQuery($query);
		}


		if(!in_array($prefix.'j2store_order_coupons', $tables)){
			$query = "
			CREATE TABLE IF NOT EXISTS `#__j2store_order_coupons` (
			`order_coupon_id` int(11) NOT NULL AUTO_INCREMENT,
			`coupon_id` int(11) NOT NULL,
			`orderpayment_id` int(11) NOT NULL,
			`customer_id` int(11) NOT NULL,
			`amount` decimal(11,5) NOT NULL,
			`created_date` datetime NOT NULL,
			PRIMARY KEY (`order_coupon_id`)
			) DEFAULT CHARSET=utf8;
			";
			$this->_executeQuery($query);
		}



		if(!in_array($prefix.'j2store_lengths', $tables)){
			$query = "
			CREATE TABLE IF NOT EXISTS `#__j2store_lengths` (
				`length_class_id` int(11) NOT NULL AUTO_INCREMENT,
				`length_title` varchar(255) NOT NULL,
				`length_unit` varchar(4) NOT NULL,
				`length_value` decimal(15,8) NOT NULL,
				`state` tinyint(1) NOT NULL,
				PRIMARY KEY (`length_class_id`)
			) DEFAULT CHARSET=utf8;
			";
			$this->_executeQuery($query);

			//dump some default data
			$query = "
			INSERT IGNORE INTO `#__j2store_lengths` (`length_class_id`, `length_title`, `length_unit`, `length_value`, `state`) VALUES
			(1, 'Centimetre', 'cm', 1.00000000, 1),
			(2, 'Inch', 'in', 0.39370000, 1),
			(3, 'Millimetre', 'mm', 10.00000000, 1);
			";
			$this->_executeQuery($query);

		}


		if(!in_array($prefix.'j2store_weights', $tables)){
			$query = "
			CREATE TABLE IF NOT EXISTS `#__j2store_weights` (
			`weight_class_id` int(11) NOT NULL AUTO_INCREMENT,
			`weight_title` varchar(255) NOT NULL,
			`weight_unit` varchar(4) NOT NULL,
			`weight_value` decimal(15,8) NOT NULL,
			`state` tinyint(1) NOT NULL,
			PRIMARY KEY (`weight_class_id`)
			) DEFAULT CHARSET=utf8;
			";
			$this->_executeQuery($query);

			//dump some default data
			$query = "
			INSERT IGNORE INTO `#__j2store_weights` (`weight_class_id`, `weight_title`, `weight_unit`, `weight_value`, `state`) VALUES
				(1, 'Kilogram', 'kg', 1.00000000, 1),
				(2, 'Gram', 'g', 1000.00000000, 1),
				(3, 'Ounce', 'oz', 35.27400000, 1);
			";
			$this->_executeQuery($query);


		}

		if(!in_array($prefix.'j2store_ordershippings', $tables)){
			$query = "
				CREATE TABLE IF NOT EXISTS `#__j2store_ordershippings` (
						`ordershipping_id` int(11) NOT NULL AUTO_INCREMENT,
						`order_id` varchar(255) NOT NULL,
						`ordershipping_type` varchar(255) NOT NULL DEFAULT '' COMMENT 'Element name of shipping plugin',
						`ordershipping_price` decimal(15,5) DEFAULT '0.00000',
						`ordershipping_name` varchar(255) NOT NULL DEFAULT '',
						`ordershipping_code` varchar(255) NOT NULL DEFAULT '',
						`ordershipping_tax` decimal(15,5) DEFAULT '0.00000',
						`ordershipping_extra` decimal(15,5) DEFAULT '0.00000',
						`ordershipping_tracking_id` mediumtext NOT NULL,
						`created_date` datetime NOT NULL COMMENT 'GMT',
						PRIMARY KEY (`ordershipping_id`),
						KEY `idx_order_shipping_order_id` (`order_id`)
				) DEFAULT CHARSET=utf8;
		";
			$this->_executeQuery($query);
		}


		//email templates
		if(!in_array($prefix.'j2store_emailtemplates', $tables)){
			$query = "
			CREATE TABLE IF NOT EXISTS `#__j2store_emailtemplates` (
				  `emailtemplate_id` int(11) NOT NULL AUTO_INCREMENT,
				  `email_type` varchar(255) NOT NULL,
				  `orderstatus_id` varchar(255) NOT NULL,
  				  `group_id` varchar(255) NOT NULL,
  				  `paymentmethod` varchar(255) NOT NULL,
				  `subject` varchar(255) NOT NULL,
				  `body` text NOT NULL,
				  `language` varchar(10) NOT NULL DEFAULT '*',
				  `state` tinyint(4) NOT NULL,
				  `ordering` int(11) NOT NULL,
				  PRIMARY KEY (`emailtemplate_id`)
			) DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;

			";

			$this->_executeQuery($query);

		}

		//currency
		if(!in_array($prefix.'j2store_currency', $tables)){
			$query = "
				CREATE TABLE IF NOT EXISTS `#__j2store_currency` (
				`currency_id` int(11) NOT NULL AUTO_INCREMENT,
				`currency_title` varchar(32) NOT NULL,
				`currency_code` varchar(3) NOT NULL,
				`currency_position` varchar(12) NOT NULL,
				`currency_symbol` varchar(255) NOT NULL,
				`currency_num_decimals` int(4) NOT NULL,
				`currency_decimal` varchar(12) NOT NULL,
				`currency_thousands` char(1) NOT NULL,
				`currency_value` float(15,8) NOT NULL,
				`state` smallint(1) NOT NULL,
				`currency_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				PRIMARY KEY (`currency_id`)
				) DEFAULT CHARSET=utf8;
				";
			$this->_executeQuery($query);

			if($type=='install') {
				$query="
						INSERT IGNORE INTO `#__j2store_currency` (`currency_id`, `currency_title`, `currency_code`, `currency_position`, `currency_symbol`, `currency_num_decimals`, `currency_decimal`, `currency_thousands`, `currency_value`, `state`, `currency_modified`) VALUES
						(1, 'US Dollar', 'USD', 'pre', '$', 2, '.', ',', 1.00000000, 1, '2013-11-27 21:09:28');
						";
				$this->_executeQuery($query);
			}
		}

		//add the field table
		if(!in_array($prefix.'j2store_field', $tables)){
			$query = "
			CREATE TABLE `#__j2store_field` (
			`field_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
			`field_table` varchar(50) DEFAULT NULL,
			`field_name` varchar(250) NOT NULL,
			`field_namekey` varchar(50) NOT NULL,
			`field_type` varchar(50) DEFAULT NULL,
			`field_value` longtext NOT NULL,
			`published` tinyint(3) unsigned NOT NULL DEFAULT '1',
			`ordering` smallint(5) unsigned DEFAULT '99',
			`field_options` text,
			`field_core` tinyint(3) unsigned NOT NULL DEFAULT '0',
			`field_required` tinyint(3) unsigned NOT NULL DEFAULT '0',
			`field_default` varchar(250) DEFAULT NULL,
			`field_access` varchar(255) NOT NULL DEFAULT 'all',
			`field_categories` varchar(255) NOT NULL DEFAULT 'all',
			`field_with_sub_categories` tinyint(1) NOT NULL DEFAULT '0',
			`field_frontend` tinyint(3) unsigned NOT NULL DEFAULT '0',
			`field_backend` tinyint(3) unsigned NOT NULL DEFAULT '1',
			`field_display` text NOT NULL,
			`field_display_billing` int(11) NOT NULL,
			`field_display_register` smallint(5) NOT NULL DEFAULT '0',
			`field_display_shipping` int(11) NOT NULL,
			`field_display_guest` smallint(5) NOT NULL DEFAULT '0',
			`field_display_guest_shipping` smallint(5) NOT NULL DEFAULT '0',
			`field_display_payment` int(11) NOT NULL,
			PRIMARY KEY (`field_id`),
			UNIQUE KEY `field_namekey` (`field_namekey`)
			) AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
			";

			$this->_executeQuery($query);

		$query ="
		INSERT IGNORE INTO `#__j2store_field` (`field_id`, `field_table`, `field_name`, `field_namekey`, `field_type`, `field_value`, `published`, `ordering`, `field_options`, `field_core`, `field_required`, `field_default`, `field_access`, `field_categories`, `field_with_sub_categories`, `field_frontend`, `field_backend`, `field_display`, `field_display_billing`, `field_display_register`, `field_display_shipping`, `field_display_guest`, `field_display_guest_shipping`, `field_display_payment`) VALUES
		('1', 'address', 'First Name', 'first_name', 'text', '', '1', '99', 'a:9:{s:12:\"errormessage\";s:22:\"This field is required\";s:9:\"filtering\";s:1:\"1\";s:9:\"maxlength\";s:1:\"0\";s:4:\"size\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:9:\"zone_type\";s:7:\"country\";s:6:\"format\";s:0:\"\";s:8:\"readonly\";s:1:\"0\";}', '1', '1', '', 'all', 'all', '0', '0', '1', '', '1', '1', '1', '1', '1', '0'),
		('2', 'address', 'Last Name', 'last_name', 'text', '', '1', '99', 'a:9:{s:12:\"errormessage\";s:22:\"This field is required\";s:9:\"filtering\";s:1:\"1\";s:9:\"maxlength\";s:1:\"0\";s:4:\"size\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:9:\"zone_type\";s:7:\"country\";s:6:\"format\";s:0:\"\";s:8:\"readonly\";s:1:\"0\";}', '1', '1', '', 'all', 'all', '0', '0', '1', '', '1', '1', '1', '1', '1', '0'),
		('3', 'address', 'Email', 'email', 'email', '', '1', '99', 'a:9:{s:12:\"errormessage\";s:36:\"J2STORE_VALIDATION_ENTER_VALID_EMAIL\";s:9:\"filtering\";s:1:\"1\";s:9:\"maxlength\";s:1:\"0\";s:4:\"size\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:9:\"zone_type\";s:7:\"country\";s:6:\"format\";s:0:\"\";s:8:\"readonly\";s:1:\"0\";}', '1', '1', '', 'all', 'all', '0', '0', '1', '', '0', '1', '0', '1', '0', '0'),
		('4', 'address', 'Address Line 1', 'address_1', 'text', '', '1', '99', 'a:9:{s:12:\"errormessage\";s:22:\"This field is required\";s:9:\"filtering\";s:1:\"1\";s:9:\"maxlength\";s:1:\"0\";s:4:\"size\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:9:\"zone_type\";s:7:\"country\";s:6:\"format\";s:0:\"\";s:8:\"readonly\";s:1:\"0\";}', '1', '1', '', 'all', 'all', '0', '0', '1', '', '1', '1', '1', '1', '1', '0'),
		('5', 'address', 'Address Line 2', 'address_2', 'text', '', '1', '99', 'a:9:{s:12:\"errormessage\";s:0:\"\";s:9:\"filtering\";s:1:\"1\";s:9:\"maxlength\";s:1:\"0\";s:4:\"size\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:9:\"zone_type\";s:7:\"country\";s:6:\"format\";s:0:\"\";s:8:\"readonly\";s:1:\"0\";}', '1', '0', '', 'all', 'all', '0', '0', '1', '', '1', '1', '1', '1', '1', '0'),
		('6', 'address', 'City', 'city', 'text', '', '1', '99', 'a:9:{s:12:\"errormessage\";s:22:\"This field is required\";s:9:\"filtering\";s:1:\"1\";s:9:\"maxlength\";s:1:\"0\";s:4:\"size\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:9:\"zone_type\";s:7:\"country\";s:6:\"format\";s:0:\"\";s:8:\"readonly\";s:1:\"0\";}', '1', '1', '', 'all', 'all', '0', '0', '1', '', '1', '1', '1', '1', '1', '0'),
		('7', 'address', 'Postcode', 'zip', 'text', '', '1', '99', 'a:9:{s:12:\"errormessage\";s:22:\"This field is required\";s:9:\"filtering\";s:1:\"1\";s:9:\"maxlength\";s:1:\"0\";s:4:\"size\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:9:\"zone_type\";s:7:\"country\";s:6:\"format\";s:0:\"\";s:8:\"readonly\";s:1:\"0\";}', '1', '1', '', 'all', 'all', '0', '0', '1', '', '1', '1', '1', '1', '1', '0'),
		('8', 'address', 'Telephone', 'phone_1', 'text', '', '1', '99', 'a:9:{s:12:\"errormessage\";s:0:\"\";s:9:\"filtering\";s:1:\"1\";s:9:\"maxlength\";s:1:\"0\";s:4:\"size\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:9:\"zone_type\";s:7:\"country\";s:6:\"format\";s:0:\"\";s:8:\"readonly\";s:1:\"0\";}', '1', '0', '', 'all', 'all', '0', '0', '1', '', '1', '1', '1', '1', '1', '0'),
		('9', 'address', 'Mobile', 'phone_2', 'text', '', '1', '99', 'a:9:{s:12:\"errormessage\";s:22:\"This field is required\";s:9:\"filtering\";s:1:\"1\";s:9:\"maxlength\";s:1:\"0\";s:4:\"size\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:9:\"zone_type\";s:7:\"country\";s:6:\"format\";s:0:\"\";s:8:\"readonly\";s:1:\"0\";}', '1', '1', '', 'all', 'all', '0', '0', '1', '', '1', '1', '1', '1', '1', '0'),
		('10', 'address', 'Company Name', 'company', 'text', '', '1', '99', 'a:9:{s:12:\"errormessage\";s:0:\"\";s:9:\"filtering\";s:1:\"1\";s:9:\"maxlength\";s:1:\"0\";s:4:\"size\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:9:\"zone_type\";s:7:\"country\";s:6:\"format\";s:0:\"\";s:8:\"readonly\";s:1:\"0\";}', '1', '0', '', 'all', 'all', '0', '0', '1', '', '1', '1', '1', '1', '1', '0'),
		('11', 'address', 'VAT/Tax Number', 'tax_number', 'text', '', '1', '99', 'a:9:{s:12:\"errormessage\";s:0:\"\";s:9:\"filtering\";s:1:\"1\";s:9:\"maxlength\";s:1:\"0\";s:4:\"size\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:9:\"zone_type\";s:7:\"country\";s:6:\"format\";s:0:\"\";s:8:\"readonly\";s:1:\"0\";}', '1', '0', '', 'all', 'all', '0', '0', '1', '', '1', '1', '0', '1', '0', '0'),
		('12', 'address', 'Country', 'country_id', 'zone', '', '1', '99', 'a:9:{s:12:\"errormessage\";s:22:\"This field is required\";s:9:\"filtering\";s:1:\"1\";s:9:\"maxlength\";s:1:\"0\";s:4:\"size\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:9:\"zone_type\";s:7:\"country\";s:6:\"format\";s:0:\"\";s:8:\"readonly\";s:1:\"0\";}', '1', '1', '222', 'all', 'all', '0', '0', '1', '', '1', '1', '1', '1', '1', '0'),
		('13', 'address', 'Zone/State', 'zone_id', 'zone', '', '1', '99', 'a:9:{s:12:\"errormessage\";s:0:\"\";s:9:\"filtering\";s:1:\"1\";s:9:\"maxlength\";s:1:\"0\";s:4:\"size\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:9:\"zone_type\";s:4:\"zone\";s:6:\"format\";s:0:\"\";s:8:\"readonly\";s:1:\"0\";}', '1', '1', '0', 'all', 'all', '0', '0', '1', '', '1', '1', '1', '1', '1', '0');
		";
		$this->_executeQuery($query);
		}

		if(!in_array($prefix.'j2store_orderstatuses', $tables)){
			$query ="
			CREATE TABLE IF NOT EXISTS `#__j2store_orderstatuses` (
					`orderstatus_id` int(11) NOT NULL AUTO_INCREMENT,
					`orderstatus_name` varchar(32) NOT NULL,
					`orderstatus_cssclass` text NOT NULL,
					`orderstatus_core` int(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`orderstatus_id`)
			) DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;
			";
			$this->_executeQuery($query);

			$query ="
			INSERT IGNORE INTO `#__j2store_orderstatuses` (`orderstatus_id`, `orderstatus_name`, `orderstatus_cssclass`, `orderstatus_core`) VALUES
			(1, 'J2STORE_CONFIRMED', 'label-success', 1),
			(2, 'J2STORE_PROCESSED', 'label-info', 1),
			(3, 'J2STORE_FAILED', 'label-important', 1),
			(4, 'J2STORE_PENDING', 'label-warning', 1),
			(5, 'J2STORE_INCOMPLETE', 'label-important', 1);
			";

			$this->_executeQuery($query);
		}

		if(!in_array($prefix.'j2store_ordertax', $tables)){
			$query ="
				CREATE TABLE IF NOT EXISTS `#__j2store_ordertax` (
				`ordertax_id` int(11) NOT NULL AUTO_INCREMENT,
				`order_id` varchar(255) NOT NULL,
				`ordertax_title` varchar(255) NOT NULL,
				`ordertax_percent` decimal(12,4) NOT NULL,
				`ordertax_amount` decimal(12,4) NOT NULL,
				PRIMARY KEY (`ordertax_id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
				";
			$this->_executeQuery($query);
		}

		if(!in_array($prefix.'j2store_groupprices', $tables)){
			$query ="
				CREATE TABLE IF NOT EXISTS `#__j2store_groupprices` (
				  `groupprice_id` int(11) NOT NULL AUTO_INCREMENT,
				  `product_id` int(11) NOT NULL,
				  `customer_group_id` int(11) NOT NULL,
				  `customer_group_price` decimal(15,8) NOT NULL,
				  PRIMARY KEY (`groupprice_id`),
				  UNIQUE KEY `customer_group` (`product_id`,`customer_group_id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
				";
			$this->_executeQuery($query);
		}


	} //end of dbChanges function

	private function _modifyExistingTables($type='') {

		$db = JFactory::getDbo();
		//get the table list
		$tables = $db->getTableList();
		//get prefix
		$prefix = $db->getPrefix();

		//address
		if(in_array($prefix.'j2store_address', $tables)){
			$fields = $db->getTableColumns('#__j2store_address');

			if (!array_key_exists('email', $fields)) {
				$query = "ALTER TABLE #__j2store_address ADD `email` varchar(255) NOT NULL AFTER `last_name`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('zone_id', $fields)) {
				$query = "ALTER TABLE #__j2store_address ADD `zone_id` varchar(255) NOT NULL AFTER `zip`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('country_id', $fields)) {
				$query = "ALTER TABLE #__j2store_address ADD `country_id` varchar(255) NOT NULL AFTER `zone_id`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('type', $fields)) {
				$query = "ALTER TABLE #__j2store_address ADD `type` varchar(255) NOT NULL AFTER `fax`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('company', $fields)) {
				$query = "ALTER TABLE #__j2store_address ADD `company` varchar(255) NOT NULL AFTER `fax`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('tax_number', $fields)) {
				$query = "ALTER TABLE #__j2store_address ADD `tax_number` varchar(255) NOT NULL AFTER `company`";
				$this->_executeQuery($query);
			}
		}



		//tax profiles
		if(in_array($prefix.'j2store_taxprofiles', $tables)){
			$fields = $db->getTableColumns('#__j2store_taxprofiles');

			if (!array_key_exists('taxprofile_id', $fields) && array_key_exists('id', $fields) ) {

				//we have the old table. drop it
				$query = "DROP TABLE #__j2store_taxprofiles";
				$this->_executeQuery($query);

				//create a new one
				$query = "CREATE TABLE IF NOT EXISTS `#__j2store_taxprofiles` (
				`taxprofile_id` int(11) NOT NULL AUTO_INCREMENT,
				`taxprofile_name` varchar(255) NOT NULL,
				`state` int(11) NOT NULL,
				`ordering` int(11) NOT NULL,
				PRIMARY KEY (`taxprofile_id`)
				) DEFAULT CHARSET=utf8;
				";
				$this->_executeQuery($query);
			}

		}

		//modify storeprofile table
		if(in_array($prefix.'j2store_storeprofiles', $tables)){
			$fields = $db->getTableColumns('#__j2store_storeprofiles');

			if (!array_key_exists('store_address_1', $fields)) {
				$query = "ALTER TABLE #__j2store_storeprofiles ADD `store_address_1` varchar(255) NOT NULL AFTER `store_desc`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('store_address_2', $fields)) {
				$query = "ALTER TABLE #__j2store_storeprofiles ADD `store_address_2` varchar(255) NOT NULL AFTER `store_address_1`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('store_city', $fields)) {
				$query = "ALTER TABLE #__j2store_storeprofiles ADD `store_city` varchar(255) NOT NULL AFTER `store_address_2`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('store_zip', $fields)) {
				$query = "ALTER TABLE #__j2store_storeprofiles ADD `store_zip` varchar(255) NOT NULL AFTER `store_city`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('config_length_class_id', $fields)) {
				$query = "ALTER TABLE #__j2store_storeprofiles ADD `config_length_class_id` int(11) NOT NULL AFTER `zone_name`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('config_weight_class_id', $fields)) {
				$query = "ALTER TABLE #__j2store_storeprofiles ADD `config_weight_class_id` int(11) NOT NULL AFTER `config_length_class_id`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('config_default_category', $fields)) {
				$query = "ALTER TABLE #__j2store_storeprofiles ADD `config_default_category` int(11) NOT NULL AFTER `config_weight_class_id`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('store_min_out_qty', $fields)) {
				$query = "ALTER TABLE #__j2store_storeprofiles ADD `store_min_out_qty` varchar(255) NOT NULL AFTER `ordering`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('store_min_sale_qty', $fields)) {
				$query = "ALTER TABLE #__j2store_storeprofiles ADD `store_min_sale_qty` varchar(255) NOT NULL AFTER `store_min_out_qty`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('store_max_sale_qty', $fields)) {
				$query = "ALTER TABLE #__j2store_storeprofiles ADD `store_max_sale_qty` varchar(255) NOT NULL AFTER `store_min_sale_qty`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('store_notify_qty', $fields)) {
				$query = "ALTER TABLE #__j2store_storeprofiles ADD `store_notify_qty` varchar(255) NOT NULL AFTER `store_max_sale_qty`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('config_currency', $fields)) {
				$query = "ALTER TABLE #__j2store_storeprofiles ADD `config_currency` varchar(255) NOT NULL AFTER `zone_name`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('config_currency_auto', $fields)) {
				$query = "ALTER TABLE #__j2store_storeprofiles ADD `config_currency_auto` smallint(5) NOT NULL AFTER `config_currency`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('store_register_layout', $fields)) {
				$this->_executeQuery("ALTER TABLE #__j2store_storeprofiles ADD `store_register_layout` longtext NOT NULL AFTER `store_notify_qty`");
			}

			if (!array_key_exists('store_billing_layout', $fields)) {
				$this->_executeQuery("ALTER TABLE #__j2store_storeprofiles ADD `store_billing_layout` longtext NOT NULL AFTER `store_register_layout`");
			}

			if (!array_key_exists('store_shipping_layout', $fields)) {
				$this->_executeQuery("ALTER TABLE #__j2store_storeprofiles ADD `store_shipping_layout` longtext NOT NULL AFTER `store_billing_layout`");
			}

			if (!array_key_exists('store_guest_layout', $fields)) {
				$this->_executeQuery("ALTER TABLE #__j2store_storeprofiles ADD `store_guest_layout` longtext NOT NULL AFTER `store_shipping_layout`");
			}

			if (!array_key_exists('store_guest_shipping_layout', $fields)) {
				$this->_executeQuery("ALTER TABLE #__j2store_storeprofiles ADD `store_guest_shipping_layout` longtext NOT NULL AFTER `store_guest_layout`");
			}

			if (!array_key_exists('store_payment_layout', $fields)) {
				$this->_executeQuery("ALTER TABLE #__j2store_storeprofiles ADD `store_payment_layout` longtext NOT NULL AFTER `store_guest_shipping_layout`");
			}

			if (!array_key_exists('store_invoice_prefix', $fields)) {
				$query = "ALTER TABLE #__j2store_storeprofiles ADD `store_invoice_prefix` varchar(255) NOT NULL AFTER `store_payment_layout`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('config_continue_shopping_url', $fields)) {
				$query = "ALTER TABLE #__j2store_storeprofiles ADD `config_continue_shopping_url` varchar(255) NOT NULL AFTER `config_default_category`";
				$this->_executeQuery($query);
			}

		}

		//modify price table
		if(in_array($prefix.'j2store_prices', $tables)){
			$fields = $db->getTableColumns('#__j2store_prices');


			if (!array_key_exists('product_enabled', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `product_enabled` tinyint(2) NOT NULL AFTER `item_shipping`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('special_price', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `special_price` varchar(255) NOT NULL AFTER `item_price`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('item_sku', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `item_sku` varchar(255) NOT NULL AFTER `product_enabled`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('params', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `params` text NOT NULL AFTER `item_sku`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('item_length', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `item_length` decimal(15,8) NOT NULL AFTER `item_sku`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('item_width', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `item_width` decimal(15,8) NOT NULL AFTER `item_length`";
				$this->_executeQuery($query);
			}
			if (!array_key_exists('item_height', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `item_height` decimal(15,8) NOT NULL AFTER `item_width`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('item_length_class_id', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `item_length_class_id` int(11) NOT NULL AFTER `item_width`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('item_weight', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `item_weight` decimal(15,8) NOT NULL AFTER `item_length_class_id`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('item_weight_class_id', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `item_weight_class_id` int(11) NOT NULL AFTER `item_weight`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('manage_stock', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `manage_stock` smallint(5) NOT NULL DEFAULT '0' AFTER `item_weight_class_id`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('min_out_qty', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `min_out_qty` decimal(12,4) NOT NULL AFTER `manage_stock`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('use_store_config_min_out_qty', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `use_store_config_min_out_qty` smallint(5) NOT NULL DEFAULT '1' AFTER `min_out_qty`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('min_sale_qty', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `min_sale_qty` decimal(12,4) NOT NULL AFTER `use_store_config_min_out_qty`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('use_store_config_min_sale_qty', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `use_store_config_min_sale_qty` smallint(5) NOT NULL DEFAULT '1' AFTER `min_sale_qty`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('max_sale_qty', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `max_sale_qty` decimal(12,4) NOT NULL AFTER `use_store_config_min_sale_qty`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('use_store_config_max_sale_qty', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `use_store_config_max_sale_qty` smallint(5) NOT NULL DEFAULT '1' AFTER `max_sale_qty`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('notify_qty', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `notify_qty` decimal(12,4) NOT NULL AFTER `use_store_config_max_sale_qty`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('use_store_config_notify_qty', $fields)) {
				$query = "ALTER TABLE #__j2store_prices ADD `use_store_config_notify_qty` smallint(5) NOT NULL DEFAULT '1' AFTER `notify_qty`";
				$this->_executeQuery($query);
			}

		}

		//modify order table
		if(in_array($prefix.'j2store_orders', $tables)){
			$fields = $db->getTableColumns('#__j2store_orders');

			if (!array_key_exists('user_email', $fields)) {
				$query = "ALTER TABLE #__j2store_orders ADD `user_email` varchar(255) NOT NULL AFTER `user_id`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('token', $fields)) {
				$query = "ALTER TABLE #__j2store_orders ADD `token` varchar(255) NOT NULL AFTER `user_email`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('customer_language', $fields)) {
				$query = "ALTER TABLE #__j2store_orders ADD `customer_language` varchar(255) NOT NULL AFTER `customer_note`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('order_shipping_tax', $fields)) {
				$query = "ALTER TABLE #__j2store_orders ADD `order_shipping_tax` decimal(10,2) NOT NULL AFTER `user_email`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('stock_adjusted', $fields)) {
				$query = "ALTER TABLE #__j2store_orders ADD `stock_adjusted` smallint(5) NOT NULL AFTER `order_state_id`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('currency_id', $fields)) {
				$query = "ALTER TABLE #__j2store_orders ADD `currency_id` int(11) NOT NULL AFTER `order_discount`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('currency_code', $fields)) {
				$query = "ALTER TABLE #__j2store_orders ADD `currency_code` varchar(5) NOT NULL AFTER `currency_id`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('currency_value', $fields)) {
				$query = "ALTER TABLE #__j2store_orders ADD `currency_value` decimal(15,8) NOT NULL AFTER `currency_code`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('order_surcharge', $fields)) {
				$query = "ALTER TABLE #__j2store_orders ADD `order_surcharge` decimal(10,2) NOT NULL AFTER `order_discount`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('invoice_prefix', $fields)) {
				$query = "ALTER TABLE #__j2store_orders ADD `invoice_prefix` varchar(255) NOT NULL AFTER `order_id`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('invoice_number', $fields)) {
				$query = "ALTER TABLE #__j2store_orders ADD `invoice_number` int(11) NOT NULL AFTER `invoice_prefix`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('customer_group', $fields)) {
				$query = "ALTER TABLE #__j2store_orders ADD `customer_group` varchar(255) NOT NULL AFTER `customer_language`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('is_shippable', $fields)) {
				$query = "ALTER TABLE #__j2store_orders ADD `is_shippable` varchar(255) NOT NULL AFTER `customer_group`";
				$this->_executeQuery($query);
			}

		}

		//orders table modifications
		if(in_array($prefix.'j2store_orderinfo', $tables)){
			$fields = $db->getTableColumns('#__j2store_orderinfo');

			if (!array_key_exists('all_billing', $fields)) {
				$query = "ALTER TABLE #__j2store_orderinfo ADD `all_billing` longtext NOT NULL AFTER `user_id`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('all_shipping', $fields)) {
				$query = "ALTER TABLE #__j2store_orderinfo ADD `all_shipping` longtext NOT NULL AFTER `all_billing`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('all_payment', $fields)) {
				$query = "ALTER TABLE #__j2store_orderinfo ADD `all_payment` longtext NOT NULL AFTER `all_shipping`";
				$this->_executeQuery($query);
			}

		}


		//orderitem attributes
		if(in_array($prefix.'j2store_orderitemattributes', $tables)){
			$fields = $db->getTableColumns('#__j2store_orderitemattributes');

			if (!array_key_exists('productattributeoption_id', $fields)) {
				$query = "ALTER TABLE #__j2store_orderitemattributes ADD `productattributeoption_id` int(11) NOT NULL AFTER `orderitem_id`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('productattributeoptionvalue_id', $fields)) {
				$query = "ALTER TABLE #__j2store_orderitemattributes ADD `productattributeoptionvalue_id` int(11) NOT NULL AFTER `productattributeoption_id`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('orderitemattribute_name', $fields)) {
				$query = "ALTER TABLE #__j2store_orderitemattributes ADD `orderitemattribute_name` varchar(255) NOT NULL AFTER `productattributeoptionvalue_id`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('orderitemattribute_value', $fields)) {
				$query = "ALTER TABLE #__j2store_orderitemattributes ADD `orderitemattribute_value` varchar(255) NOT NULL AFTER `orderitemattribute_name`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('orderitemattribute_type', $fields)) {
				$query = "ALTER TABLE #__j2store_orderitemattributes ADD `orderitemattribute_type` varchar(255) NOT NULL AFTER `orderitemattribute_prefix`";
				$this->_executeQuery($query);
			}

		}


		//shipping methods table

		if(in_array($prefix.'j2store_shippingmethods', $tables)){
			$fields = $db->getTableColumns('#__j2store_shippingmethods');

			//change id to shipping_method_id
			if (array_key_exists('id', $fields)) {
				$query = "ALTER TABLE #__j2store_shippingmethods CHANGE `id` `shipping_method_id` INT(11) NOT NULL AUTO_INCREMENT";
				$this->_executeQuery($query);

				//change the primary key
				$query ="ALTER TABLE #__j2store_shippingmethods DROP PRIMARY KEY, ADD PRIMARY KEY (`shipping_method_id`)";
				$this->_executeQuery($query);

			}

			if (!array_key_exists('tax_class_id', $fields)) {
				$query = "ALTER TABLE #__j2store_shippingmethods ADD `tax_class_id` int(11) NOT NULL AFTER `shipping_method_type`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('address_override', $fields)) {
				$query = "ALTER TABLE #__j2store_shippingmethods ADD `address_override` varchar(255) NOT NULL AFTER `tax_class_id`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('subtotal_minimum', $fields)) {
				$query = "ALTER TABLE #__j2store_shippingmethods ADD `subtotal_minimum` decimal(15,3) NOT NULL AFTER `address_override`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('subtotal_maximum', $fields)) {
				$query = "ALTER TABLE #__j2store_shippingmethods ADD `subtotal_maximum` decimal(15,3) NOT NULL AFTER `subtotal_minimum`";
				$this->_executeQuery($query);
			}

		}

		//shipping rates
		if(in_array($prefix.'j2store_shippingrates', $tables)){
			$fields = $db->getTableColumns('#__j2store_shippingrates');

			//change id to shipping_method_id
			if (!array_key_exists('geozone_id', $fields)) {
				$query = "ALTER TABLE #__j2store_shippingrates ADD `geozone_id` int(11) NOT NULL AFTER `shipping_method_id`";
				$this->_executeQuery($query);
			}
		}

		//product option values
		if(in_array($prefix.'j2store_product_optionvalues', $tables)){
			$fields = $db->getTableColumns('#__j2store_product_optionvalues');

			//change id to shipping_method_id
			if (!array_key_exists('product_optionvalue_weight', $fields)) {
				$query = "ALTER TABLE #__j2store_product_optionvalues ADD `product_optionvalue_weight` decimal(15,8) NOT NULL AFTER `product_optionvalue_prefix`";
				$this->_executeQuery($query);
			}

			//change id to shipping_method_id
			if (!array_key_exists('product_optionvalue_weight_prefix', $fields)) {
				$query = "ALTER TABLE #__j2store_product_optionvalues ADD `product_optionvalue_weight_prefix` varchar(1) NOT NULL AFTER `product_optionvalue_weight`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('product_optionvalue_sku', $fields)) {
				$query = "ALTER TABLE #__j2store_product_optionvalues ADD `product_optionvalue_sku` varchar(255) NOT NULL AFTER `product_optionvalue_weight_prefix`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('product_optionvalue_default', $fields)) {
				$query = "ALTER TABLE #__j2store_product_optionvalues ADD `product_optionvalue_default` varchar(255) NOT NULL AFTER `product_optionvalue_sku`";
				$this->_executeQuery($query);
			}

		}

		//product files
		if(in_array($prefix.'j2store_productfiles', $tables)){
			$fields = $db->getTableColumns('#__j2store_productfiles');

			//change id to shipping_method_id
			if (!array_key_exists('download_total', $fields)) {
				$query = "ALTER TABLE #__j2store_productfiles ADD `download_total` int(11) NOT NULL AFTER `product_id`";
				$this->_executeQuery($query);
			}
		}

		//coupons
		if(in_array($prefix.'j2store_coupons', $tables)){
			$fields = $db->getTableColumns('#__j2store_coupons');

			if (!array_key_exists('products', $fields)) {
				$query = "ALTER TABLE #__j2store_coupons ADD `products` varchar(255) NOT NULL AFTER `product_category`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('min_subtotal', $fields)) {
				$query = "ALTER TABLE #__j2store_coupons ADD `min_subtotal` varchar(255) NOT NULL AFTER `products`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('max_value', $fields)) {
				$query = "ALTER TABLE #__j2store_coupons ADD `max_value` varchar(255) NOT NULL AFTER `value_type`";
				$this->_executeQuery($query);
			}

		}

		//email templates
		if(in_array($prefix.'j2store_emailtemplates', $tables)){
			$fields = $db->getTableColumns('#__j2store_emailtemplates');

			if (!array_key_exists('orderstatus_id', $fields)) {
				$query = "ALTER TABLE #__j2store_emailtemplates ADD `orderstatus_id` varchar(255) NOT NULL AFTER `email_type`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('group_id', $fields)) {
				$query = "ALTER TABLE #__j2store_emailtemplates ADD `group_id` varchar(255) NOT NULL AFTER `orderstatus_id`";
				$this->_executeQuery($query);
			}

			if (!array_key_exists('paymentmethod', $fields)) {
				$query = "ALTER TABLE #__j2store_emailtemplates ADD `paymentmethod` varchar(255) NOT NULL AFTER `group_id`";
				$this->_executeQuery($query);
			}

		}

		//ordershippings
		if(in_array($prefix.'j2store_ordershippings', $tables)){
			$fields = $db->getTableColumns('#__j2store_ordershippings');

			if (array_key_exists('order_id', $fields)) {
				$query = "ALTER TABLE #__j2store_ordershippings MODIFY `order_id` varchar(255)";
				$this->_executeQuery($query);
			}
		}

	}

	private function _doCompatibilityChecks() {

		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__j2store_productquantities');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if(count($rows)) {
			foreach($rows as $row) {

				//migrate manage stock to the prices table
				if($row->product_id > 0) {
					unset($product);
					$product = JTable::getInstance('Prices', 'Table');
					$product->load(array('article_id'=>$row->product_id));
					if($row->product_id == $product->article_id) {
						if(isset($row->manage_stock)) $product->manage_stock = $row->manage_stock;
						if(isset($row->min_out_qty)) $product->min_out_qty = $row->min_out_qty;
						if(isset($row->use_store_config_min_out_qty)) $product->use_store_config_min_out_qty = $row->use_store_config_min_out_qty;

						if(isset($row->min_sale_qty)) $product->min_sale_qty = $row->min_sale_qty;
						if(isset($row->use_store_config_min_sale_qty)) $product->use_store_config_min_sale_qty = $row->use_store_config_min_sale_qty;

						if(isset($row->max_sale_qty)) $product->max_sale_qty = $row->max_sale_qty;
						if(isset($row->use_store_config_max_sale_qty)) $product->use_store_config_max_sale_qty = $row->use_store_config_max_sale_qty;

						if(isset($row->notify_qty)) $product->notify_qty = $row->notify_qty;
						if(isset($row->use_store_config_notify_qty)) $product->use_store_config_notify_qty = $row->use_store_config_notify_qty;

						$product->store();
					}
				}

			}

		}

		//remove the columns
		$sql = "
			ALTER TABLE #__j2store_productquantities
				DROP `manage_stock`,
				DROP `min_out_qty`,
				DROP `use_store_config_min_out_qty`,
				DROP `min_sale_qty`,
				DROP `use_store_config_min_sale_qty`,
				DROP `max_sale_qty`,
				DROP `use_store_config_max_sale_qty`,
				DROP `notify_qty`,
				DROP `use_store_config_notify_qty`
			;
				";
		$this->_executeQuery($sql);

	}

	private function _configMigration($type, $parent) {
		$db = JFactory::getDbo();
		//migrate the currency params from general options to currency table
		if($type=='update') {

			// Load the component parameters, not using JComponentHelper to avoid conflicts ;)
			JLoader::import('joomla.html.parameter');
			JLoader::import('joomla.application.component.helper');

			$sql = $db->getQuery(true)
			->select($db->qn('params'))
			->from($db->qn('#__extensions'))
			->where($db->qn('type').' = '.$db->q('component'))
			->where($db->qn('element').' = '.$db->q($this->_extension_name));
			$db->setQuery($sql);
			$rawparams = $db->loadResult();
			$params = new JRegistry();
			if(version_compare(JVERSION, '3.0', 'ge')) {
				$params->loadString($rawparams, 'JSON');
			} else {
				$params->loadJSON($rawparams);
			}

			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
			$query = $db->getQuery(true);

			//copy params only when the currency table is empty. Otherwise, the user is either doing a fresh install or already updated
			$query->select('*')->from('#__j2store_currency');
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if(count($rows) < 1) {

				//store data to the currency table only if data exists in params.
				$currency_code = $params->get('currency_code');
				if(isset($currency_code) && strlen($currency_code)) {
					$item = JTable::getInstance('Currency', 'Table');
					$item->currency_title = $currency_code;
					$item->currency_code = $currency_code;
					$item->currency_position = $params->get('currency_position');
					$item->currency_symbol = $params->get('currency');
					$item->currency_num_decimals = $params->get('currency_num_decimals')?$params->get('currency_num_decimals'):'2';
					$item->currency_decimal = $params->get('currency_decimal')?$params->get('currency_decimal'):'.';
					$item->currency_thousands = $params->get('currency_thousands');
					$item->currency_value = '1.00000'; //default currency is one always
					$item->currency_modified = JFactory::getDate()->toSql();
					$item->state = 1;
					$item->store();
					$currency_id = $item->currency_id;

					//now update the store profiles table

					//first get the active store profile
					$query = $db->getQuery(true);
					$query->select('*');
					$query->from('#__j2store_storeprofiles');
					$query->where('state=1');
					$query->order('store_id ASC LIMIT 1');
					$db->setQuery($query);
					$row =	$db->loadObject();
					if($row->store_id) {
						$store = JTable::getInstance('Storeprofile', 'Table');
						$store->load($row->store_id);
						if($currency_id) {
							$store->config_currency = $db->q($currency_code);
							$store->config_currency_auto = 1;
							$store->store();
						}

					}

					//now we have to update all the previous order records with the currency value 1
					$sql = $db->getQuery(true);
					$sql->select('*')->from('#__j2store_orders');
					$db->setQuery($sql);
					$orders = $db->loadObjectList();

					//if we have order records
					if(count($orders)) {
						$query = $db->getQuery(true);
						$query->update('#__j2store_orders')
						->set('currency_id='.$currency_id)
						->set('currency_code='.$db->q($currency_code))
						->set('currency_value=1');
						$db->setQuery($query);
						$db->execute();
					}

				}
			}

		}

		//check email templates
		$query = $db->getQuery(true)->select('*')->from('#__j2store_emailtemplates');
		$emailtemplates = $db->setQuery($query)->loadObjectList();

		if(count($emailtemplates ) < 1) {
			//dump some default data
			$query = "
				INSERT IGNORE INTO `#__j2store_emailtemplates` (`emailtemplate_id`, `email_type`, `orderstatus_id`, `group_id`, `paymentmethod`, `subject`, `body`, `language`, `state`, `ordering`) VALUES
				(1, '', '*', '*', '*', 'Hello [BILLING_FIRSTNAME] [BILLING_LASTNAME], your order has been placed with [SITENAME]', '<table style=\"width: 100%;\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\r\n<tbody>\r\n<tr valign=\"top\">\r\n<td rowspan=\"1\" colspan=\"2\">\r\n<p>Hello [BILLING_FIRSTNAME] [BILLING_LASTNAME], we thank you for placing an order with [SITENAME]. Your Order ID is:<strong>[ORDERID]</strong>. We have now started processing your order. The details of your order are as follows:</p>\r\n</td>\r\n</tr>\r\n<tr valign=\"top\">\r\n<td>\r\n<h3>Order Information</h3>\r\n<p><strong>Order ID: </strong>[ORDERID]</p>\r\n<p><strong>Invoice Number: </strong>[INVOICENO]</p>\r\n<p><strong>Date: </strong>[ORDERDATE]</p>\r\n<p><strong>Order Amount: </strong>[ORDERAMOUNT]</p>\r\n<p><strong>Order Status: </strong>[ORDERSTATUS]</p>\r\n<p><strong> </strong></p>\r\n</td>\r\n<td>\r\n<h3>Customer Information</h3>\r\n<p>[BILLING_FIRSTNAME] [BILLING_LASTNAME]</p>\r\n<p>[BILLING_ADDRESS_1] [BILLING_ADDRESS_2]</p>\r\n<p>[BILLING_CITY], [BILLING_ZIP]</p>\r\n<p>[BILLING_STATE] [BILLING_COUNTRY]</p>\r\n<p>[BILLING_PHONE] [BILLING_MOBILE]</p>\r\n<p>[BILLING_COMPANY]</p>\r\n</td>\r\n</tr>\r\n<tr valign=\"top\">\r\n<td>\r\n<h3>Payment Information</h3>\r\n<p><strong>Payment Type: </strong>[PAYMENT_TYPE]</p>\r\n</td>\r\n<td>\r\n<h3>Shipping Information</h3>\r\n<p>[SHIPPING_FIRSTNAME] [SHIPPING_LASTNAME]</p>\r\n<p>[SHIPPING_ADDRESS_1]  [SHIPPING_ADDRESS_2]</p>\r\n<p>[SHIPPING_CITY], [SHIPPING_ZIP]</p>\r\n<p>[SHIPPING_STATE] [SHIPPING_COUNTRY]</p>\r\n<p>[SHIPPING_PHONE] [SHIPPING_MOBILE]</p>\r\n<p>[SHIPPING_COMPANY]</p>\r\n<p>[SHIPPING_METHOD]</p>\r\n</td>\r\n</tr>\r\n<tr valign=\"top\">\r\n<td rowspan=\"1\" colspan=\"2\">\r\n<p>[ITEMS]</p>\r\n</td>\r\n</tr>\r\n<tr valign=\"top\">\r\n<td colspan=\"2\">\r\n<p>For any queries and details please get in touch with us. We will be glad to be of service. You can also view the order details by visiting [INVOICE_URL]</p>\r\n<p>You can use your email address and the following token to view the order [ORDER_TOKEN]</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>', '*', 1, 0);
				";
			$this->_executeQuery($query);
		}


	}

	private function _executeQuery($query) {

		$db = JFactory::getDbo();
		$db->setQuery($query);
		try {
			$db->execute();
		}catch (Exception $e) {
			//do nothing. we dont want to fail the install process.
		}


	}

	private function _removeObsoletePlugins($parent)
	{
		$src = $parent->getParent()->getPath('source');
		$db = JFactory::getDbo();

		foreach($this->RemovePlugins as $folder => $plugins) {
			foreach($plugins as $plugin) {
				$sql = $db->getQuery(true)
				->select($db->qn('extension_id'))
				->from($db->qn('#__extensions'))
				->where($db->qn('type').' = '.$db->q('plugin'))
				->where($db->qn('element').' = '.$db->q($plugin))
				->where($db->qn('folder').' = '.$db->q($folder));
				$db->setQuery($sql);
				$id = $db->loadResult();
				if($id)
				{
					$installer = new JInstaller;
					$result = $installer->uninstall('plugin',$id,1);
				}
			}
		}
	}

	private function _removeObsoleteFiles($parent)
	{

		if(count($this->RemoveFilesAdmin)) {
			foreach($this->RemoveFilesAdmin as $folder => $files) {
				if($folder!='views') {
					foreach($files as $filename) {
						if(JFile::exists(JPATH_ADMINISTRATOR.'/components/com_j2store/'.$folder.'/'.$filename.'.php')) {
							try {
								JFile::delete(JPATH_ADMINISTRATOR.'/components/com_j2store/'.$folder.'/'.$filename.'.php');
							 } catch (Exception $exc) {
								//if error, dont sweat about
							 }
						}
					}
				}

				if($folder=='views') {
					foreach($files as $filename) {
						if(JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_j2store/'.$folder.'/'.$filename)) {
							try {
							JFolder::delete(JPATH_ADMINISTRATOR.'/components/com_j2store/'.$folder.'/'.$filename);
							} catch (Exception $exc) {
								//if error, dont sweat about
							}
						}
					}
				}
			}
		}

		if(count($this->RemoveFilesSite)) {
			foreach($this->RemoveFilesSite as $folder => $files) {
				if($folder!='views') {
					foreach($files as $filename) {

						if(JFile::exists(JPATH_SITE.'/components/com_j2store/'.$folder.'/'.$filename.'.php')) {
							try {
							JFile::delete(JPATH_SITE.'/components/com_j2store/'.$folder.'/'.$filename.'.php');
							} catch (Exception $exc) {
								//if error, dont sweat about
							}
						}
					}
				}

				if($folder=='views') {
					foreach($files as $filename) {
						if(JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_j2store/'.$folder.'/'.$filename)) {
							try {
								JFolder::delete(JPATH_ADMINISTRATOR.'/components/com_j2store/'.$folder.'/'.$filename);
							} catch (Exception $exc) {
								//if error, dont sweat about
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Joomla! 1.6+ bugfix for "DB function returned no error"
	 */
	private function _bugfixDBFunctionReturnedNoError()
	{
		$db = JFactory::getDbo();

		// Fix broken #__assets records
		$query = $db->getQuery(true);
		$query->select('id')
		->from('#__assets')
		->where($db->qn('name').' = '.$db->q($this->_extension_name));
		$db->setQuery($query);
		$ids = $db->loadColumn();
		if(!empty($ids)) foreach($ids as $id) {
			$query = $db->getQuery(true);
			$query->delete('#__assets')
			->where($db->qn('id').' = '.$db->q($id));
			$db->setQuery($query);
			$db->query();
		}

		// Fix broken #__extensions records
		$query = $db->getQuery(true);
		$query->select('extension_id')
		->from('#__extensions')
		->where($db->qn('element').' = '.$db->q($this->_extension_name));
		$db->setQuery($query);
		$ids = $db->loadColumn();
		if(!empty($ids)) foreach($ids as $id) {
			$query = $db->getQuery(true);
			$query->delete('#__extensions')
			->where($db->qn('extension_id').' = '.$db->q($id));
			$db->setQuery($query);
			$db->query();
		}

		// Fix broken #__menu records
		$query = $db->getQuery(true);
		$query->select('id')
		->from('#__menu')
		->where($db->qn('type').' = '.$db->q('component'))
		->where($db->qn('menutype').' = '.$db->q('main'))
		->where($db->qn('link').' LIKE '.$db->q('index.php?option='.$this->_extension_name));
		$db->setQuery($query);
		$ids = $db->loadColumn();
		if(!empty($ids)) foreach($ids as $id) {
			$query = $db->getQuery(true);
			$query->delete('#__menu')
			->where($db->qn('id').' = '.$db->q($id));
			$db->setQuery($query);
			$db->query();
		}
	}


	/**
	 * Joomla! 1.6+ bugfix for "Can not build admin menus"
	 */
	private function _bugfixCantBuildAdminMenus()
	{
		$db = JFactory::getDbo();

		// If there are multiple #__extensions record, keep one of them
		$query = $db->getQuery(true);
		$query->select('extension_id')
		->from('#__extensions')
		->where($db->qn('element').' = '.$db->q($this->_extension_name));
		$db->setQuery($query);
		$ids = $db->loadColumn();
		if(count($ids) > 1) {
			asort($ids);
			$extension_id = array_shift($ids); // Keep the oldest id

			foreach($ids as $id) {
				$query = $db->getQuery(true);
				$query->delete('#__extensions')
				->where($db->qn('extension_id').' = '.$db->q($id));
				$db->setQuery($query);
				$db->query();
			}
		}

		// @todo

		// If there are multiple assets records, delete all except the oldest one
		$query = $db->getQuery(true);
		$query->select('id')
		->from('#__assets')
		->where($db->qn('name').' = '.$db->q($this->_extension_name));
		$db->setQuery($query);
		$ids = $db->loadObjectList();
		if(count($ids) > 1) {
			asort($ids);
			$asset_id = array_shift($ids); // Keep the oldest id

			foreach($ids as $id) {
				$query = $db->getQuery(true);
				$query->delete('#__assets')
				->where($db->qn('id').' = '.$db->q($id));
				$db->setQuery($query);
				$db->query();
			}
		}

		// Remove #__menu records for good measure!
		$query = $db->getQuery(true);
		$query->select('id')
		->from('#__menu')
		->where($db->qn('type').' = '.$db->q('component'))
		->where($db->qn('menutype').' = '.$db->q('main'))
		->where($db->qn('link').' LIKE '.$db->q('index.php?option='.$this->_extension_name));
		$db->setQuery($query);
		$ids1 = $db->loadColumn();
		if(empty($ids1)) $ids1 = array();
		$query = $db->getQuery(true);
		$query->select('id')
		->from('#__menu')
		->where($db->qn('type').' = '.$db->q('component'))
		->where($db->qn('menutype').' = '.$db->q('main'))
		->where($db->qn('link').' LIKE '.$db->q('index.php?option='.$this->_extension_name.'&%'));
		$db->setQuery($query);
		$ids2 = $db->loadColumn();
		if(empty($ids2)) $ids2 = array();
		$ids = array_merge($ids1, $ids2);
		if(!empty($ids)) foreach($ids as $id) {
			$query = $db->getQuery(true);
			$query->delete('#__menu')
			->where($db->qn('id').' = '.$db->q($id));
			$db->setQuery($query);
			$db->query();
		}
	}

	private function _rebuildMenus() {

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('extension_id')
		->from('#__extensions')
		->where($db->qn('element').' = '.$db->q($this->_extension_name));
		$db->setQuery($query);
		$extension_id = $db->loadResult();
		if($extension_id) {
			$query = $db->getQuery(true);
			$query->select('*')
			->from('#__menu')
			->where($db->qn('type').' = '.$db->q('component'))
			->where($db->qn('menutype').' != '.$db->q('main'))
			->where($db->qn('link').' LIKE '.$db->q('index.php?option='.$this->_extension_name.'&%'));
			$db->setQuery($query);
			$menus = $db->loadObjectList();

			if(count($menus)) {
				foreach($menus as $menu){
					if($menu->component_id != $extension_id) {
						$table = JTable::getInstance('Menu', 'JTable', array());
						$table->load($menu->id);
						$table->component_id= $extension_id;
						if(!$table->store()) {
							//dont do anything stupid. Just return true. This can be done manually too.
							return true;
						}
					}
				}
			}
		}

		return true;
	}


	private function _getPreviousVersion() {

		jimport('joomla.filesystem.file');
		$target = JPATH_ADMINISTRATOR.'/components/com_j2store/pre-version.txt';
		$version = null;
		if(JFile::exists($target)) {
			$rawData = JFile::read($target);
			$info = explode("\n", $rawData);
			$version = trim($info[0]);
		}
		return $version;

	}

	private function installationResults($status)
	{
		$language = JFactory::getLanguage();
		$language->load('com_j2store');
			        $rows = 0; ?>
			        <img src="<?php echo JURI::root(true); ?>/media/j2store/images/j2store-logo.png" width="109" height="48" alt="J2Store Component" align="right" />
			         <div class="alert alert-block alert-danger">
		        		<?php echo JText::_('J2STORE_ATTRIBUTE_MIGRATION_ALERT'); ?>
		        </div>
			        <h2><?php echo JText::_('J2STORE_INSTALLATION_STATUS'); ?></h2>
			        <table class="adminlist table table-striped">
			            <thead>
			                <tr>
			                    <th class="title" colspan="2"><?php echo JText::_('J2STORE_EXTENSION'); ?></th>
			                    <th width="30%"><?php echo JText::_('J2STORE_STATUS'); ?></th>
			                </tr>
			            </thead>
			            <tfoot>
			                <tr>
			                    <td colspan="3"></td>
			                </tr>
			            </tfoot>
			            <tbody>
			                <tr class="row0">
			                    <td class="key" colspan="2"><?php echo 'J2Store '.JText::_('J2STORE_COMPONENT'); ?></td>
			                    <td><strong><?php echo JText::_('J2STORE_INSTALLED'); ?></strong></td>
			                </tr>
			                <?php if (count($status->modules)): ?>
			                <tr>
			                    <th><?php echo JText::_('J2STORE_MODULE'); ?></th>
			                    <th><?php echo JText::_('J2STORE_CLIENT'); ?></th>
			                    <th></th>
			                </tr>
			                <?php foreach ($status->modules as $module): ?>
			                <tr class="row<?php echo(++$rows % 2); ?>">
			                    <td class="key"><?php echo $module['name']; ?></td>
			                    <td class="key"><?php echo ucfirst($module['client']); ?></td>
			                    <td><strong><?php echo ($module['result'])?JText::_('J2STORE_INSTALLED'):JText::_('K2_NOT_INSTALLED'); ?></strong></td>
			                </tr>
			                <?php endforeach; ?>
			                <?php endif; ?>
			                <?php if (count($status->plugins)): ?>
			                <tr>
			                    <th><?php echo JText::_('J2STORE_PLUGIN'); ?></th>
			                    <th><?php echo JText::_('J2STORE_GROUP'); ?></th>
			                    <th></th>
			                </tr>
			                <?php foreach ($status->plugins as $plugin): ?>
			                <tr class="row<?php echo(++$rows % 2); ?>">
			                    <td class="key"><?php echo ucfirst($plugin['name']); ?></td>
			                    <td class="key"><?php echo ucfirst($plugin['group']); ?></td>
			                    <td><strong><?php echo ($plugin['result'])?JText::_('J2STORE_INSTALLED'):JText::_('J2STORE_NOT_INSTALLED'); ?></strong></td>
			                </tr>
			                <?php endforeach; ?>
			                <?php endif; ?>
			            </tbody>
			        </table>
			    <?php
			    }

			    private function uninstallationResults($status)
			    {
			    $language = JFactory::getLanguage();
			    $language->load('com_j2store');
			    $rows = 0;
			 ?>
			        <h2><?php echo JText::_('J2STORE_REMOVAL_STATUS'); ?></h2>
			        <table class="adminlist">
			            <thead>
			                <tr>
			                    <th class="title" colspan="2"><?php echo JText::_('J2STORE_EXTENSION'); ?></th>
			                    <th width="30%"><?php echo JText::_('J2STORE_STATUS'); ?></th>
			                </tr>
			            </thead>
			            <tfoot>
			                <tr>
			                    <td colspan="3"></td>
			                </tr>
			            </tfoot>
			            <tbody>
			                <tr class="row0">
			                    <td class="key" colspan="2"><?php echo 'J2Store '.JText::_('J2STORE_COMPONENT'); ?></td>
			                    <td><strong><?php echo JText::_('J2STORE_REMOVED'); ?></strong></td>
			                </tr>
			                <?php if (count($status->modules)): ?>
			                <tr>
			                    <th><?php echo JText::_('J2STORE_MODULE'); ?></th>
			                    <th><?php echo JText::_('J2STORE_CLIENT'); ?></th>
			                    <th></th>
			                </tr>
			                <?php foreach ($status->modules as $module): ?>
			                <tr class="row<?php echo(++$rows % 2); ?>">
			                    <td class="key"><?php echo $module['name']; ?></td>
			                    <td class="key"><?php echo ucfirst($module['client']); ?></td>
			                    <td><strong><?php echo ($module['result'])?JText::_('J2STORE_REMOVED'):JText::_('J2STORE_NOT_REMOVED'); ?></strong></td>
			                </tr>
			                <?php endforeach; ?>
			                <?php endif; ?>

			                <?php if (count($status->plugins)): ?>
			                <tr>
			                    <th><?php echo JText::_('J2STORE_PLUGIN'); ?></th>
			                    <th><?php echo JText::_('J2STORE_GROUP'); ?></th>
			                    <th></th>
			                </tr>
			                <?php foreach ($status->plugins as $plugin): ?>
			                <tr class="row<?php echo(++$rows % 2); ?>">
			                    <td class="key"><?php echo ucfirst($plugin['name']); ?></td>
			                    <td class="key"><?php echo ucfirst($plugin['group']); ?></td>
			                    <td><strong><?php echo ($plugin['result'])?JText::_('J2STORE_REMOVED'):JText::_('J2STORE_NOT_REMOVED'); ?></strong></td>
			                </tr>
			                <?php endforeach; ?>
			                <?php endif; ?>
			            </tbody>
			        </table>
			    <?php
			    }

			    /**
			     * Remove the update site specification from Joomla! – we no longer support
			     * that misbehaving crap, thank you very much...
			     */
			    private function _killUpdateSite()
			    {
			    	// Get some info on all the stuff we've gotta delete
			    	$db = JFactory::getDbo();
			    	$query = $db->getQuery(true)
			    	->select(array(
			    			$db->qn('s').'.'.$db->qn('update_site_id'),
			    			$db->qn('e').'.'.$db->qn('extension_id'),
			    			$db->qn('e').'.'.$db->qn('element'),
			    			$db->qn('s').'.'.$db->qn('location'),
			    	))
			    	->from($db->qn('#__update_sites').' AS '.$db->qn('s'))
			    	->join('INNER',$db->qn('#__update_sites_extensions').' AS '.$db->qn('se').' ON('.
			    			$db->qn('se').'.'.$db->qn('update_site_id').' = '.
			    			$db->qn('s').'.'.$db->qn('update_site_id')
			    			.')')
			    			->join('INNER',$db->qn('#__extensions').' AS '.$db->qn('e').' ON('.
			    					$db->qn('e').'.'.$db->qn('extension_id').' = '.
			    					$db->qn('se').'.'.$db->qn('extension_id')
			    					.')')
			    					->where($db->qn('s').'.'.$db->qn('type').' = '.$db->q('extension'))
			    					->where($db->qn('e').'.'.$db->qn('type').' = '.$db->q('component'))
			    					->where($db->qn('e').'.'.$db->qn('element').' = '.$db->q($this->_extension_name))
			    					;
			    					$db->setQuery($query);
			    					$oResult = $db->loadObject();

			    					// If no record is found, do nothing. We've already killed the monster!
			    					if(is_null($oResult)) return;

			    					// Delete the #__update_sites record
			    					$query = $db->getQuery(true)
			    					->delete($db->qn('#__update_sites'))
			    					->where($db->qn('update_site_id').' = '.$db->q($oResult->update_site_id));
			    					$db->setQuery($query);
			    					try {
			    						$db->query();
			    					} catch (Exception $exc) {
			    						// If the query fails, don't sweat about it
			    					}

			    					// Delete the #__update_sites_extensions record
			    					$query = $db->getQuery(true)
			    					->delete($db->qn('#__update_sites_extensions'))
			    					->where($db->qn('update_site_id').' = '.$db->q($oResult->update_site_id));
			    					$db->setQuery($query);
			    					try {
			    						$db->query();
			    					} catch (Exception $exc) {
			    						// If the query fails, don't sweat about it
			    					}

			    					// Delete the #__updates records
			    					$query = $db->getQuery(true)
			    					->delete($db->qn('#__updates'))
			    					->where($db->qn('update_site_id').' = '.$db->q($oResult->update_site_id));
			    					$db->setQuery($query);
			    					try {
			    						$db->query();
			    					} catch (Exception $exc) {
			    						// If the query fails, don't sweat about it
			    					}
			    }

			    /**
			     * Deletes the Live Update information, forcing its reload during the first
			     * run of the component. This makes sure that the Live Update doesn't show
			     * an update available right after installing the component.
			     */
			    private function _resetLiveUpdate()
			    {
			    	// Load the component parameters, not using JComponentHelper to avoid conflicts ;)
			    	JLoader::import('joomla.html.parameter');
			    	JLoader::import('joomla.application.component.helper');
			    	$db = JFactory::getDbo();
			    	$sql = $db->getQuery(true)
			    	->select($db->qn('params'))
			    	->from($db->qn('#__extensions'))
			    	->where($db->qn('type').' = '.$db->q('component'))
			    	->where($db->qn('element').' = '.$db->q($this->_extension_name));
			    	$db->setQuery($sql);
			    	$rawparams = $db->loadResult();
			    	$params = new JRegistry();
			    	if(version_compare(JVERSION, '3.0', 'ge')) {
			    		$params->loadString($rawparams, 'JSON');
			    	} else {
			    		$params->loadJSON($rawparams);
			    	}

			    	// Reset the liveupdate key
			    	$params->set('liveupdate', null);

			    	// Save the modified component parameters
			    	$data = $params->toString();
			    	$sql = $db->getQuery(true)
			    	->update($db->qn('#__extensions'))
			    	->set($db->qn('params').' = '.$db->q($data))
			    	->where($db->qn('type').' = '.$db->q('component'))
			    	->where($db->qn('element').' = '.$db->q($this->_extension_name));

			    	$db->setQuery($sql);
			    	$db->execute();
			    }

}
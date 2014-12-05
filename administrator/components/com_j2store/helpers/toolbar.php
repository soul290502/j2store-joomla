<?php
/*------------------------------------------------------------------------
# com_j2store - J2 Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi- Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/


// No direct access
defined('_JEXEC') or die;

/**
 * Submenu helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_j2store
 * @since		2.5
 */


if (version_compare(JVERSION, '3.0', 'ge'))
{
	require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/toolbar30.php');
	class J2StoreToolBar extends J2StoreToolBar30
	{

		public static function &getAnInstance($option = null, $config = array()) {

			if (!class_exists( $className )) {
				$className = 'J2StoreToolbar';
			}
			$instance = new $className($config);

			return $instance;

		}


		public function __construct($config = array()) {}

		}

		class JToolbarButtonJ2Store extends JToolbarButtonJ2Store30 {

		}

}
else
{
	require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/toolbar25.php');
	class J2StoreToolBar extends J2StoreToolBar25
	{

		public static function &getAnInstance($option = null, $config = array()) {

			if (!class_exists( $className )) {
				$className = 'J2StoreToolbar';
			}
			$instance = new $className($config);

			return $instance;

		}


		public function __construct($config = array()) {}
		}

		class JButtonJ2Store extends JToolbarButtonJ2Store25 {

		}

}
<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/


// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');

if (version_compare(JVERSION, '3.0', 'ge'))
{
	class J2StoreModel extends JModelLegacy
	{
		public static function addIncludePath($path = '', $prefix = '')
		{
			return parent::addIncludePath($path, $prefix);
		}

	}

}
else
{
	class J2StoreModel extends JModel
	{
		public static function addIncludePath($path = '', $prefix = '')
		{
			return parent::addIncludePath($path, $prefix);
		}

	}

}
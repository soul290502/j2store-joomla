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


/*
 * 	Base class for loading all J2Store libraries
 *
 *  Since 2.6
 *
 */

abstract class J2StoreFactory {

	/**
	 * Global curency object
	 *
	 * @var    J2StoreCurrency
	 * @since  2.6
	 */
	public static $currency = null;

	/**
	 * Global weight object
	 *
	 * @var    J2StoreWeight
	 * @since  2.6
	 */
	public static $weight = null;

	/**
	 * Global length object
	 *
	 * @var    J2StoreLength
	 * @since  2.6
	 */

	public static $length = null;

	/**
	 * Global fields base object
	 *
	 * @var    J2StoreSelectableBase
	 * @since  2.6
	 */

	public static $sbase = null;

	/**
	 * Global fields object
	 *
	 * @var    J2StoreSelectableFields
	 * @since  2.6
	 */


	public static $fields = null;


	public static function getCurrencyObject() {

		if (!self::$currency)
		{
			require_once ('currency.php');
			self::$currency = J2StoreCurrency::getInstance();
		}

		return self::$currency;
	}

	public static function getWeightObject() {

		if (!self::$weight)
		{
			require_once ('weight.php');
			self::$weight = J2StoreWeight::getInstance();
		}

		return self::$weight;
	}

	public static function getLengthObject() {

		if(!self::$length)
		{
			require_once ('length.php');
			self::$length = J2StoreLength::getInstance();
		}
		return self::$length;
	}


	public static function getSelectableBase() {

		if (!self::$sbase)
		{
			require_once ('selectable/base.php');
			self::$sbase = J2StoreSelectableBase::getInstance();
		}

		return self::$sbase;
	}

	public static function getSelectableFields() {

		if (!self::$fields)
		{
			require_once ('selectable/fields.php');
			self::$fields = J2StoreSelectableFields::getInstance();
		}

		return self::$fields;
	}

}
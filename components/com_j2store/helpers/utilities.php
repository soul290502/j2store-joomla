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
Class J2StoreUtilities {

	public static function number($amount, $options=array())
	{
		// default to whatever is in config
	 	$config = JComponentHelper::getParams('com_j2store');
		$options = (array) $options;
		$post = '';
		$pre = '';

		$default_currency = $config->get('currency_code', 'USD');
		$num_decimals = isset($options['num_decimals']) ? $options['num_decimals'] : $config->get('currency_num_decimals', '2');
		$thousands = isset($options['thousands']) ? $options['thousands'] : $config->get('currency_thousands', ',');
		$decimal = isset($options['decimal']) ? $options['decimal'] : $config->get('currency_decimal', '.');
		$currency_symbol = isset($options['currency']) ? $options['currency'] : $config->get('currency', '$');
		$currency_position = isset($options['currency_position']) ? $options['currency_position'] : $config->get('currency_position', 'pre');
		if($currency_position == 'post') {
			$post = $currency_symbol;
		} else {
			$pre = $currency_symbol;
		}

		//$return = $pre.number_format($amount, $num_decimals, $decimal, $thousands).$post;
		$return = number_format($amount, $num_decimals, $decimal, $thousands);
		return $return;
	}

	/**
	 * getItemName() - Get the name of an item.
	 *
	 * @param string $order_code The order code of the item.
	 */
public static function getItemName($order_code) {
		require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/libraries/j2item.php');
		$article_tbl = J2StoreItem::getArticle($order_code);
		return $article_tbl->title;
	}


public static function cleanCache() {

	$cache = JFactory::getCache();
	$cache->clean();
	//$cache->gc();
	}

}

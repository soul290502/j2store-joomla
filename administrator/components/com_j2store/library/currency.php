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

class J2StoreCurrency {
  	private $code;
  	private $currencies = array();
  	private $input;
  	private $session;
	/*
	 * J2StoreCurrency instance
	 *
	 * since 2.6
	 */

  	protected static $instance;

  	public function __construct() {

  		require_once (JPATH_SITE.'/components/com_j2store/helpers/cart.php');
  		$storeprofile = J2StoreHelperCart::getStoreAddress();
  		$this->config = $storeprofile;
		$this->session = JFactory::getSession();
		$this->input = JFactory::getApplication()->input;

		$rows = self::getCurrencyList();

    	foreach ($rows as $result) {
      		$this->currencies[$result['currency_code']] = $result;
    	}
    	$currency = $this->input->get('currency');

		if (isset($currency) && (array_key_exists($currency, $this->currencies))) {
			$this->set($currency);
    	} elseif ($this->session->has('currency', 'j2store') && (array_key_exists($this->session->get('currency', '', 'j2store'), $this->currencies))) {
      		$this->set($this->session->get('currency', '', 'j2store'));
    	} else {
      		$this->set($this->config->config_currency);
    	}
  	}

  	public static function getInstance()
  	{
  		if (!is_object(self::$instance))
  		{
  			self::$instance = new J2StoreCurrency();
  		}

  		return self::$instance;
  	}


  	public static function getCurrencyList() {
		$db = JFactory::getDbo();
  		$query = $db->getQuery(true);
  		$query->select('*')->from('#__j2store_currency');
  		$db->setQuery($query);
  		$rows = $db->loadAssocList();
  		return $rows;
  	}

  	public function set($currency) {
    	$this->code = $currency;

    	if (!$this->session->has('currency', 'j2store') || ($this->session->get('currency', '', 'j2store') != $currency)) {
      		$this->session->set('currency', $currency, 'j2store');
    	}
  	}

  	public function format($number, $currency = '', $value = '', $format = true) {
		if ($currency && $this->has($currency)) {
			$currency_position  = $this->currencies[$currency]['currency_position'];
			$currency_symbol  = $this->currencies[$currency]['currency_symbol'];
      		$decimal_place = $this->currencies[$currency]['currency_num_decimals'];
    	} else {
    		$currency_position  = $this->currencies[$this->code]['currency_position'];
    		$currency_symbol  = $this->currencies[$this->code]['currency_symbol'];
      		$decimal_place = $this->currencies[$this->code]['currency_num_decimals'];

			$currency = $this->code;
    	}

    	if ($value) {
      		$value = $value;
    	} else {
      		$value = $this->currencies[$currency]['currency_value'];
    	}

    	if ($value) {
      		$value = (float)$number * $value;
    	} else {
      		$value = $number;
    	}

    	$string = '';

    	if (($currency_position == 'pre') && ($format)) {
      		$string .= '<span class="j2store-currency-symbol">'.$currency_symbol.'</span>';
    	}

		if ($format) {
			$decimal_point = $this->currencies[$currency]['currency_decimal'];
		} else {
			$decimal_point = '.';
		}

		if ($format) {
			$thousand_point = $this->currencies[$currency]['currency_thousands'];
		} else {
			$thousand_point = '';
		}

    	$string .= number_format(round($value, (int)$decimal_place), (int)$decimal_place, $decimal_point, $thousand_point);

  		if (($currency_position == 'post') && ($format)) {
      		$string .= '<span class="j2store-currency-symbol">'.$currency_symbol.'</span>';
    	}

    	return $string;
  	}

  	public function convert($value, $from, $to) {
		if (isset($this->currencies[$from])) {
			$from = $this->currencies[$from]['currency_value'];
		} else {
			$from = 0;
		}

		if (isset($this->currencies[$to])) {
			$to = $this->currencies[$to]['currency_value'];
		} else {
			$to = 0;
		}

		return $value * ($to / $from);
  	}

  	public function getId($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['currency_id'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['currency_id'];
		} else {
			return 0;
		}
  	}

	public function getSymbol($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['currency_symbol'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['currency_symbol'];
		} else {
			return '';
		}
  	}

	public function getSymbolPosition($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['currency_position'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['currency_position'];
		} else {
			return 'pre';
		}
  	}

	public function getDecimalPlace($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['currency_num_decimals'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['currency_num_decimals'];
		} else {
			return 0;
		}
  	}

  	public function getCode() {
    	return $this->code;
  	}

  	public function getValue($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['currency_value'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['currency_value'];
		} else {
			return 0;
		}
  	}

  	public function has($currency) {
    	return isset($this->currencies[$currency]);
  	}
}
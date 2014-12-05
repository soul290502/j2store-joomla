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
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/prices.php');

final class J2StoreTax {
	private $shipping_address;
	private $billing_address;
	private $store_address;
	private $session_data;
	public static $store = null;

	public function __construct() {
		$app = JFactory::getApplication();
		$config = JComponentHelper::getParams('com_j2store');

		$session = JFactory::getSession();

		if ($session->has('shipping_country_id', 'j2store') || $session->has('shipping_zone_id', 'j2store')) {
			$this->setShippingAddress($session->get('shipping_country_id', '', 'j2store'), $session->get('shipping_zone_id', '', 'j2store'));
		} elseif ($config->get('config_tax_default') == 'shipping') {
			$this->setShippingAddress(J2StoreTax::getStoreAddress()->country_id, J2StoreTax::getStoreAddress()->zone_id);
		}

		if ($session->has('billing_country_id', 'j2store') || $session->has('billing_zone_id', 'j2store')) {
			$this->setBillingAddress($session->get('billing_country_id', '', 'j2store'), $session->get('billing_zone_id', '', 'j2store'));
		} elseif ($config->get('config_tax_default') == 'billing') {
			$this->setBillingAddress(J2StoreTax::getStoreAddress()->country_id, J2StoreTax::getStoreAddress()->zone_id);
		}
		// intialize session data with store's country and zone ids
		//$session->set('j2store_address', null);
		//$this->intializeStoreAddress();
		$this->setStoreAddress(J2StoreTax::getStoreAddress()->country_id, J2StoreTax::getStoreAddress()->zone_id);

  	}

  public static function getStoreAddress() {

  	if(!isset(self::$store)) {
  		$db = JFactory::getDbo();
  		$query = $db->getQuery(true);
  		$query->select('country_id,zone_id');
  		$query->from('#__j2store_storeprofiles');
  		$query->where('state=1');
  		$query->order('store_id ASC LIMIT 1');
  		$db->setQuery($query);
		self::$store =	$db->loadObject();
		}
	return self::$store;
  }

  	public static function setAddressInSession($address_values, $type, $override=false){

  		$session = JFactory::getSession();
  		if($override==true ) {
  			$session->set($type.'_country_id', $address_values['country_id'], 'j2store');
  			$session->set($type.'_zone_id', $address_values['zone_id'], 'j2store');
  		}
  	}

	public function setShippingAddress($country_id, $zone_id) {
		$this->shipping_address = array(
			'country_id' => $country_id,
			'zone_id'    => $zone_id
		);
	}

	public function setBillingAddress($country_id, $zone_id) {
		$this->billing_address = array(
			'country_id' => $country_id,
			'zone_id'    => $zone_id
		);
	}

	public function setStoreAddress($country_id, $zone_id) {
		$this->store_address = array(
			'country_id' => $country_id,
			'zone_id'    => $zone_id
		);
	}

  	public function calculate($value, $taxprofile_id, $calculate = true) {
		if ($taxprofile_id && $calculate) {
			$amount = $this->getTax($value, $taxprofile_id);

			return $value + $amount;
		} else {
      		return $value;
    	}
  	}

  	/*
  	 * pass the product price and the product price and get the tax,
  	 * internally gets the taxprofile id and get the tax
  	 * */
  	public function getProductTax($product_price, $product_id) {
  		$amount = 0;
  		$taxprofile_id = J2StorePrices::getTaxProfileId($product_id);

  		$rates = $this->getRates($taxprofile_id);

  		$tax_rates = $this->getTaxRates($product_price, $rates);

  		//print_r($tax_rates);

  		foreach ($tax_rates as $tax_rate) {
  			$amount += $tax_rate['amount'];
  		}

  		return $amount;
  	}

  	public function getTax($value, $taxprofile_id) {
		$amount = 0;

		$rates = $this->getRates($taxprofile_id);

		$tax_rates = $this->getTaxRates($value, $rates);

		foreach ($tax_rates as $tax_rate) {
			$amount += $tax_rate['amount'];
		}

		return $amount;
  	}


  	public function getRateArray($value, $taxprofile_id) {

  		$rates = $this->getRates($taxprofile_id);

  		$tax_rates = $this->getTaxRates($value, $rates);

  		return $tax_rates;
  	}


	public function getRateName($taxrate_id) {
		static $sets;

		if ( !is_array( $sets ) )
		{
			$sets = array( );
		}
		if ( !isset( $sets[$taxrate_id] ) )
		{
			$query ="SELECT taxrate_name AS name FROM #__j2store_taxrates WHERE taxrate_id = '" . (int)$taxrate_id . "'";
			$db = JFactory::getDbo();
	  		$db->setQuery($query);
	  		$result = $db->loadResult();

			if (isset($result)) {
				$sets[$taxrate_id] = $result;
			} else {
				$sets[$taxrate_id] = false;
			}
		}
		return $sets[$taxrate_id];
	}

	public function getPercent($taxrate_id) {

		static $taxsets;

		if ( !is_array( $taxsets) )
		{
			$taxsets= array( );
		}
		if ( !isset( $taxsets[$taxrate_id] ) )
		{
			$query ="SELECT tax_percent AS percent FROM #__j2store_taxrates WHERE taxrate_id = '" . (int)$taxrate_id . "'";
			$db = JFactory::getDbo();
			$db->setQuery($query);
			$result = $db->loadResult();
			if (isset($result)) {
				$taxsets[$taxrate_id] = $result;
			} else {
				$taxsets[$taxrate_id] ='';
			}
		}
		return $taxsets[$taxrate_id];
	}

    public function getRates($taxprofile_id) {
		$tax_rates = array();

		if ($this->shipping_address) {
			$taxrates_items = $this->getTaxRateItems('shipping', $this->shipping_address['country_id'], $this->shipping_address['zone_id'], $taxprofile_id);
			if(isset($taxrates_items)){
			foreach ($taxrates_items as $trate) {
				$tax_rates[$trate->taxrate_id] = array(
					'taxrate_id' => $trate->taxrate_id,
					'name'        => $trate->name,
					'rate'        => $trate->rate
				);
			}
			}
		}

		if ($this->billing_address) {
			$taxrates_items = $this->getTaxRateItems('billing', $this->billing_address['country_id'], $this->billing_address['zone_id'], $taxprofile_id);
			if(isset($taxrates_items)){
				foreach ($taxrates_items as $trate) {
					$tax_rates[$trate->taxrate_id] = array(
							'taxrate_id' => $trate->taxrate_id,
							'name'        => $trate->name,
							'rate'        => $trate->rate
					);
				}
			}
		}

		if ($this->store_address) {
			$taxrates_items = $this->getTaxRateItems('store', $this->store_address['country_id'], $this->store_address['zone_id'], $taxprofile_id);
			if(isset($taxrates_items)){

				foreach ($taxrates_items as $trate) {
					$tax_rates[$trate->taxrate_id] = array(
							'taxrate_id' => $trate->taxrate_id,
							'name'        => $trate->name,
							'rate'        => $trate->rate
					);
				}
			}
		}

		return $tax_rates;

	}

	public function getTaxRateItems($address_type, $country_id, $zone_id, $taxprofile_id) {

		static $ratesets;

		if ( !is_array( $ratesets) )
		{
			$ratesets= array( );
		}
		if ( !isset( $ratesets[$address_type][$country_id][$zone_id][$taxprofile_id]) )
		{
			$db = JFactory::getDbo();
			$query = "SELECT tr2.taxrate_id, tr2.taxrate_name AS name, tr2.tax_percent AS rate FROM "
				. " #__j2store_taxrules tr1 LEFT JOIN "
				. " #__j2store_taxrates tr2 ON (tr1.taxrate_id = tr2.taxrate_id) LEFT JOIN "
				. " #__j2store_geozonerules z2gz ON (tr2.geozone_id = z2gz.geozone_id) LEFT JOIN "
				. " #__j2store_geozones gz ON (tr2.geozone_id = gz.geozone_id) WHERE tr1.taxprofile_id = " . (int)$taxprofile_id
				. " AND tr1.address = ".$db->q($address_type)
				. " AND z2gz.country_id = " . (int)$country_id
				. " AND (z2gz.zone_id = 0 OR z2gz.zone_id = " . (int)$zone_id
				. ") ORDER BY tr1.ordering ASC";
			$db->setQuery($query);
			$ratesets[$address_type][$country_id][$zone_id][$taxprofile_id] = $db->loadObjectList();
		}
		return $ratesets[$address_type][$country_id][$zone_id][$taxprofile_id];
	}

	public function getTaxRates($value, $tax_rates) {

		$tax_rate_data = array();

		foreach ($tax_rates as $tax_rate) {
			if (isset($tax_rate_data[$tax_rate['taxrate_id']])) {
				$amount = $tax_rate_data[$tax_rate['taxrate_id']]['amount'];
			} else {
				$amount = 0;
			}

			$amount += ($value / 100 * $tax_rate['rate']);

			$tax_rate_data[$tax_rate['taxrate_id']] = array(
					'taxrate_id' => $tax_rate['taxrate_id'],
					'name'        => $tax_rate['name'],
					'rate'        => $tax_rate['rate'],
					'amount'      => $amount
			);
		}

		return $tax_rate_data;


	}

  	public function has($taxprofile_id) {
		return isset($this->taxes[$taxprofile_id]);
  	}

  	function executeQuery($query){
  		$db = JFactory::getDbo();
  		$db->setQuery($query);
  		return $db->loadObjectList();
  	}

  	public function setTaxProperties($type, $country_id, $zone_id) {

  		$address_values = array();
  		$address_values['country_id'] = $country_id;
  		$address_values['zone_id'] = $zone_id;
  		J2StoreTax::setAddressInSession($address_values,$type,true);
  	}
}
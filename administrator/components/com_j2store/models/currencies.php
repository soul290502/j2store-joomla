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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.modellist');

class J2StoreModelCurrencies extends JModelList {

	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$published = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_j2store');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('currency_id', 'asc');
	}


	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.state');
		return parent::getStoreId($id);
	}

	public function getCurrencies() {

		$query = $this->_db->getQuery(true);
		$query->select('*')->from('#__j2store_currency')->where('state=1');
		$this->_db->setQuery($query);
		if($rows= $this->_db->loadObjectList()) {
			return $rows;
		} else {
			return array();
		}

	}

	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select(
				$this->getState(
						'list.select',
						'*'
				)
		);

		$query->from('#__j2store_currency');

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('state = '.(int) $published);
		} else if ($published === '') {
			$query->where('(state IN (0, 1))');
		}
		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('currency_id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('(currency_title LIKE '.$search.
						' OR currency_code LIKE '.$search.')'
						);
			}
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');

		if($orderCol == 'currency_id' ) {
			$orderCol = 'currency_id '.$orderDirn.', currency_id';
		} else {
			$orderCol = 'currency_id '.$orderDirn.', currency_id';
		}

		$query->order($db->escape($orderCol.' '.$orderDirn));

		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}

	public function updateCurrencies($force = false) {
		if (extension_loaded('curl')) {
			$data = array();

			require_once (JPATH_SITE.'/components/com_j2store/helpers/cart.php');
			$storeprofile = J2StoreHelperCart::getStoreAddress();
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('*')->from('#__j2store_currency')->where('currency_code !='.$db->q($storeprofile->config_currency));

			if($force) {
				$query->where('currency_modified='.  $db->q(date('Y-m-d H:i:s', strtotime('-1 day'))));
			}
			$db->setQuery($query);
			$rows = $db->loadAssocList();

			foreach ($rows as $result) {
				$data[] = $storeprofile->config_currency . $result['currency_code'] . '=X';
			}

			$curl = curl_init();

			curl_setopt($curl, CURLOPT_URL, 'http://download.finance.yahoo.com/d/quotes.csv?s=' . implode(',', $data) . '&f=sl1&e=.csv');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

			$content = curl_exec($curl);

			curl_close($curl);

			$lines = explode("\n", trim($content));

			foreach ($lines as $line) {
				$currency = utf8_substr($line, 4, 3);
				$value = utf8_substr($line, 11, 6);

				if ((float)$value) {
					$query = $db->getQuery(true);
					$query->update('#__j2store_currency')->set('currency_value ='.$db->q((float)$value))
							->set('currency_modified='.$db->q(date('Y-m-d H:i:s')))
							->where('currency_code='.$db->q($currency));
					$db->setQuery($query);
					$db->query();
				}
			}

			//update the default currency
			$query = $db->getQuery(true);
			$query->update('#__j2store_currency')->set('currency_value ='.$db->q('1.00000'))
			->set('currency_modified='.$db->q(date('Y-m-d H:i:s')))
			->where('currency_code='.$db->q($storeprofile->config_currency));
			$db->setQuery($query);
			$db->query();
		}
	}
}

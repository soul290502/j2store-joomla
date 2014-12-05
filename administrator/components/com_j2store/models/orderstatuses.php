<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Priya bose - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

/** Check to ensure this file is included in Joomla! */
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.modellist');

/**
 *
 * @package		Joomla
 * @subpackage	J2Store
 * @since 2.5
*/
class J2StoreModelOrderstatuses extends JModelList
	{

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
			parent::populateState('a.store_id', 'asc');
		}


		protected function getStoreId($id = '')
		{
			// Compile the store id.
			$id.= ':' . $this->getState('filter.search');
			$id.= ':' . $this->getState('filter.state');
			return parent::getStoreId($id);
		}



		protected function getListQuery()
		{
			// Create a new query object.
			$db		= $this->getDbo();
			$query	= $db->getQuery(true);
			$query->select(
					$this->getState(
							'list.select',
							'a.*'
					)
			);

			$query->from('#__j2store_orderstatuses AS a');

			// Filter by search in title
			$search = $this->getState('filter.search');
			if (!empty($search)) {
				if (stripos($search, 'id:') === 0) {
					$query->where('a.orderstatus_id = '.(int) substr($search, 3));
				} else {
					$search = $db->Quote('%'.$db->escape($search, true).'%');
					$query->where('(a.orderstatus_name LIKE '.$search.')');
				}
			}

			// Add the list ordering clause.
			$orderCol	= $this->state->get('list.ordering');
			$orderDirn	= $this->state->get('list.direction');

			if($orderCol == 'a.orderstatus_id' ) {
				$orderCol = 'a.orderstatus_id '.$orderDirn.', a.orderstatus_id';
			} else {
				$orderCol = 'a.orderstatus_id '.$orderDirn.', a.orderstatus_id';
			}

			$query->order($db->escape($orderCol.' '.$orderDirn));

			//echo nl2br(str_replace('#__','jos_',$query));
			return $query;
		}

		public function getOrderStatuses() {

			$query = $this->_db->getQuery(true);
			$query->select('*')->from('#__j2store_orderstatuses');
			$this->_db->setQuery($query);
			if($rows= $this->_db->loadObjectList()) {
				return $rows;
			} else {
				return array();
			}

		}

		public function getOrderStateByID($order_state_id) {
			$query = $this->_db->getQuery(true);
			$query->select('*')->from('#__j2store_orderstatuses')->where('orderstatus_id='.$this->_db->q($order_state_id));
			$this->_db->setQuery($query);
			return $this->_db->loadObject();
		}

}

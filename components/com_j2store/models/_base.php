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

jimport( 'joomla.filter.filterinput' );
jimport( 'joomla.application.component.model' );
//JLoader::register( 'J2StoreQuery', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_j2store'.DS.'library'.DS.'query.php' );
JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');

class J2StoreModelBase extends J2StoreModel
{
	var $_filterinput = null; // instance of JFilterInput
	public $cache_enabled = true;
	public $cache_lifetime = '900';
	var $_item = null;
	function __construct($config = array())
	{
		parent::__construct($config);
		$this->_filterinput = &JFilterInput::getInstance();
	}


	/**
	 * Method to set the identifier
	 *
	 * @access	public
	 * @param	int identifier
	 * @return	void
	 */
	public function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Gets the identifier, setting it if it doesn't exist
	 * @return unknown_type
	 */
	public function getId()
	{
		if (empty($this->_id))
		{
			$id = JFactory::getApplication()->input->getInt('id');
			//$id = JRequest::getVar( 'id', JRequest::getVar( 'id', '0', 'post', 'int' ), 'get', 'int' );
			$array = JRequest::getVar('cid', array( $id ), 'post', 'array');
			$this->setId( (int) $array[0] );
		}
		return $this->_id;
	}


	/**
	 * Gets a property from the model's state, or the entire state if no property specified
	 * @param $property
	 * @param $default
	 * @param string The variable type {@see JFilterInput::clean()}.
	 *
	 * @return unknown_type
	 */
	public function getState( $property=null, $default=null, $return_type='default' )
	{
		$return = ($property === null) ? $this->state : $this->state->get($property, $default);
		return $this->_filterinput->clean( $return, $return_type );
	}


	/**
	 * Gets the model's query, building it if it doesn't exist
	 * @return valid query object
	 */

	public function getQuery($refresh = false)
	{
		if (empty( $this->_query ) || $refresh)
		{
			$this->_query = $this->_buildQuery($refresh);
		}
		return $this->_query;
	}

	/**
	 * Sets the model's query
	 * @param $query	A valid query object
	 * @return valid query object
	 */
	public function setQuery( $query )
	{
		$this->_query = $query;
		return $this->_query;
	}

	/**
	 * Retrieves the data for a paginated list
	 * @return array Array of objects containing the data from the database
	 */
	public function getList($refresh = false)
	{
		if (empty( $this->_list ) || $refresh)
		{
			$query = $this->getQuery($refresh);
			$this->_list = $this->_getList( (string) $query, $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_list;
	}


	/**
	 * Paginates the data
	 * @return array Array of objects containing the data from the database
	 */
	public function getPagination()
	{
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}

	/**
	 * Retrieves the count
	 * @return array Array of objects containing the data from the database
	 */
	public function getTotal()
	{
		if (empty($this->_total))
		{
			$this->_total = $this->getResult();
		}
		return $this->_total;
	}


	/**
	 * Builds a generic SELECT query
	 *
	 * @return  string  SELECT query
	 */
	protected function _buildQuery( $refresh=false )
	{
		if (!empty($this->_query) && !$refresh)
		{
			return $this->_query;
		}

		//$query = new J2StoreQuery();
		$query = JFactory::getDbo()->getQuery(true);

		$this->_buildQueryFields($query);
		$this->_buildQueryFrom($query);
		$this->_buildQueryJoins($query);
		$this->_buildQueryWhere($query);
		$this->_buildQueryGroup($query);
		$this->_buildQueryHaving($query);
		$this->_buildQueryOrder($query);

		return $query;
	}

	/**
	 * Builds SELECT fields list for the query
	 */
	protected function _buildQueryFields($query)
	{
		$query->select( $this->getState( 'select', 'tbl.*' ) );
	}


	/**
	 * Builds FROM tables list for the query
	 */
	protected function _buildQueryFrom($query)
	{
		$name = $this->getTable()->getTableName();
		$query->from($name.' AS tbl');
	}

	/**
	 * Builds JOINS clauses for the query
	 */
	protected function _buildQueryJoins($query)
	{
	}

	/**
	 * Builds WHERE clause for the query
	 */
	protected function _buildQueryWhere($query)
	{
	}

	/**
	 * Builds a GROUP BY clause for the query
	 */
	protected function _buildQueryGroup($query)
	{
	}

	/**
	 * Builds a HAVING clause for the query
	 */
	protected function _buildQueryHaving($query)
	{
	}

	/**
	 * Builds a generic ORDER BY clause based on the model's state
	 */
	protected function _buildQueryOrder($query)
	{
		$order      = $this->_db->escape( $this->getState('order') );
		$direction  = $this->_db->escape( strtoupper( $this->getState('direction') ) );

		if ($order)
		{
			$query->order("$order $direction");
		}

		// TODO Find an abstract way to determine if order is a valid field in query
		// if (in_array($order, $this->getTable()->getColumns())) does not work
		// because you could be ordering by a field from one of the JOINed tables
		if (in_array('ordering', array_keys($this->_db->getTableColumns($this->getTable()->getTableName()))))
		{
			$query->order('ordering ASC');
		}
	}

	/*
	 * Builds a generic SELECT COUNT(*) query
	*/
	protected function _buildResultQuery()
	{
		//$query = new J2StoreQuery();
		$query = $this->_db->getQuery(true);
		$query->select( $this->getState( 'select', 'COUNT(*)' ) );

		$this->_buildQueryFrom($query);
		$this->_buildQueryJoins($query);
		$this->_buildQueryWhere($query);
		$this->_buildQueryGroup($query);
		$this->_buildQueryHaving($query);
		return $query;
	}

	public function getResult( $refresh=false )
	{
		if (empty($this->_result) || $refresh)
		{
			$query = $this->getResultQuery( $refresh );
			$this->_db->setQuery( (string) $query );
			$this->_result = $this->_db->loadResult();
		}
		return $this->_result;
	}

	/**
	 * Gets the model's query, building it if it doesn't exist
	 * @return valid query object
	 */
	public function getResultQuery( $refresh=false )
	{
		if (empty( $this->_resultQuery ) || $refresh )
		{
			$this->_resultQuery = $this->_buildResultQuery();
		}
		return $this->_resultQuery;
	}

	/**
	 * Sets the model's query
	 * @param $query	A valid query object
	 * @return valid query object
	 */
	public function setResultQuery( $query )
	{
		$this->_resultQuery = $query;
		return $this->_resultQuery;
	}


	public function getItem( $pk=null, $refresh=false, $emptyState=true )
	{
		if (empty($this->_item) || $refresh)
		{
			if (is_bool($pk)) {
				// backwards compatibility
				$refresh = $pk;
				$pk = null;
			}
			$cache_key = $pk ? $pk : $this->getID();

			$classname = strtolower( get_class($this) );
			$cache = JFactory::getCache( $classname . '.item', '' );
			$cache->setCaching($this->cache_enabled);
			$cache->setLifeTime($this->cache_lifetime);
			$item = $cache->get($cache_key);
			if (!$item || $refresh)
			{
				$item = $this->_getItem( $pk, $refresh, $emptyState );

				if (!empty($item))
				{
					$this->prepareItem( $item, 0, $refresh );
				}

				$cache->store($item, $cache_key);

			}

			$this->_item = $item;

		}

		return $this->_item;
	}


	protected function _getItem( $pk=null, $refresh=false, $emptyState=true )
	{
		$cache_key = $pk ? $pk : $this->getID();


		if ($emptyState)
		{
			$this->emptyState();
		}

		$query = $this->getQuery($refresh);
		$keyname = $this->getTable()->getKeyName();
		$value  = $this->_db->Quote( $cache_key );
		$query->where( "tbl.$keyname = $value" );
		$this->_db->setQuery( (string) $query );

		$item = $this->_db->loadObject();
		return $item;
	}

	/**
	 * Set basic properties for the item, whether in a list or a singleton
	 *
	 * @param unknown_type $item
	 * @param unknown_type $key
	 * @param unknown_type $refresh
	 */
	protected function prepareItem( $item, $key=0, $refresh=false )
	{
		if (!empty($this->_objectClass) && !is_a($item, $this->_objectClass)) {
			$clone = $item;
			$item = $this->getTable();
			foreach (get_object_vars($clone) as $prop=>$def)
			{
				$item->$prop = $clone->$prop;
			}
		}

		$dispatcher = JDispatcher::getInstance( );
		$dispatcher->trigger( 'onPrepare' . $this->getTable( )->get( '_suffix' ), array( $item ) );
	}

	/**
	 * Empties the state
	 *
	 * @return unknown_type
	 */
	public function emptyState()
	{
		$state = JArrayHelper::fromObject( $this->getState() );
		foreach ($state as $key=>$value)
		{
			if (substr($key, '0', '1') != '_')
			{
				$this->setState( $key, '' );
			}
		}
		return $this->getState();
	}

	/**
	 * Clean the cache
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function clearCache()
	{
			$classname = strtolower( get_class($this) );
			parent::cleanCache($classname . '.item');
			parent::cleanCache($classname . '.list');
			parent::cleanCache($classname . '.list-totals');
	}


}

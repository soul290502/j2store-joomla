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

jimport('joomla.application.component.model');


/* require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/prices.php');
require_once (JPATH_SITE.'/components/com_j2store/helpers/cart.php');
require_once (JPATH_SITE.'/components/com_j2store/helpers/downloads.php'); */


/**
 *
 * @package		Joomla
 * @subpackage	J2Store
 * @since 2.5
*/
class J2StoreModelProducts extends J2StoreModel
	{
		/**
		  * @var array
		 */

		var $_data = null;

		/**
		 *
		 * @var email
		 */
		var $id = null;

		/**
		* @var integer
		*/
		var $_total = null;

		/**
		 * Pagination object
		 *
		 * @var object
		 */
		var $_pagination = null;
		var $_article_type = 0;


		/**
		 * Constructor
		 *
		 * @since 2.5
		 */
	function __construct()
		{
			parent::__construct();

			$app=JFactory::getApplication();

			$option = 'com_j2store';

			$ns = $option.'.products';



			// Get the pagination request variables
			$limit		= $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg('list_limit'), 'int' );
			//$limit		= $app->getUserStateFromRequest( $ns, 'limit', 'limit');
			$limitstart	= $app->getUserStateFromRequest( $ns.'.limitstart', 'limitstart', 0, 'int' );

			$article_type = $app->getUserStateFromRequest($ns . '.filter_article_type','filter_article_type');
			$categoryId = $app->getUserStateFromRequest($ns . '.filter.category_id','filter_category_id');


			// In case limit has been changed, adjust limitstart accordingly
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('filter.category_id', $categoryId);
			$this->setState('limit', $limit);
			$this->setState('limitstart', $limitstart);
			$this->setState('filter_article_type', $article_type);

			$this->_article_type=$article_type;

			// Assining the id
			$this->_id = $app->input->getInt('id');
		}




		/**
		 *
		 * @access public
		 * @return array
		 */
		function getData()
		{
			// Lets load the content if it doesn't already exist
			if (empty($this->_data))
			{
				$query = $this->_buildQuery();
				$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
			}

			return $this->_data;
		}




		/**
		 *
		 * @access public
		 * @return integer
		 */
		function getTotal()
		{
			// Lets load the content if it doesn't already exist
			if (empty($this->_total))
			{
				$query = $this->_buildQuery();
				$this->_total = $this->_getListCount($query);
			}

			return $this->_total;
		}

		/**
		 * Method to get a pagination object
		 *
		 * @access public
		 * @return integer
		 */
		function getPagination()
		{
			// Lets load the content if it doesn't already exist
			if (empty($this->_pagination))
			{
				jimport('joomla.html.pagination');
				$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
			}

			return $this->_pagination;
		}

		function _buildQuery()
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('p.*')->from('#__content AS p');

			// Get the WHERE and ORDER BY clauses for the query
			if(($this->getState('filter.category_id')))
			{
				$query->where('p.catid='.$this->getState('filter.category_id'));
			}

			$this->_buildContentJoin($query);

			$this->_buildContentWhere($query);
			$this->_buildContentOrderBy($query );


			return $query;
		}



		private function _buildContentJoin($query) {


			$query->select('price.*');
			if($this->_article_type==1)
				{
					$query->innerJoin('#__j2store_prices AS price ON price.article_id=p.id');
				}
			else{
					$query->leftJoin('#__j2store_prices AS price ON price.article_id=p.id');
				}

		}

		function _buildContentOrderBy($query)
		{
			$mainframe = JFactory::getApplication();
			$option = 'com_j2store';
			$ns='com_j2store.products';


			$filter_order		= $mainframe->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'p.ordering',	'cmd' );
			$filter_order_Dir	= $mainframe->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'ASC',				'word' );

			if(!empty($filter_order)) {
			$query->order($filter_order.' '.$filter_order_Dir);
			}
			$query->order('p.id');

		}

		function _buildContentWhere($query)
		{
			$user=JFactory::getUser();
			$mainframe = JFactory::getApplication();
			$option = 'com_j2store';
			$ns='com_j2store.products';
			$db					=JFactory::getDBO();
			//$categoryId;
			$filter_order		= $mainframe->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'p.id',	'cmd' );
			$filter_order_Dir	= $mainframe->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
			$search				= $mainframe->getUserStateFromRequest( $ns.'search',			'search',			'',				'string' );
			if (strpos($search, '"') !== false) {
				$search = str_replace(array('=', '<'), '', $search);
			}
			$search = JString::strtolower($search);


			if ($search) {
				$query->where('LOWER(p.title) LIKE '.$db->Quote( '%'.$db->escape( $search, true ).'%', false ));
			}

			if(!$user->authorise('core.admin'))
			{
				$groups=implode(',',$user->getAuthorisedViewLevels());
				$query->where('p.access IN (' . $groups.')');
			}
		}

		/**
		 * Method to (un)publish a a_option
		 *
		 * @access	public
		 * @return	boolean	True on success
		 * @since	1.5
		 */
		function publish($cid = array(), $publish = 1)
		{

			if (count( $cid ))
			{
				JArrayHelper::toInteger($cid);
				$cids = implode( ',', $cid );
				$query = $this->_db->getQuery(true)->update('#__j2store_prices')
						->set('product_enabled='.(int) $publish)
						->where('article_id IN ('.$cids.')');
				$this->_db->setQuery( $query );
				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}

			return true;
		}

		/*
		 * Method to save Product Prices Quantities
		 * @params data array type
		 * @returns boolean
		 */
		function saveAll($data)
		{
			$app = JFactory::getApplication();

			foreach($data['product'] as $value){
				unset($prices_row);
				unset($qty_row);
				$prices_row=JTable::getInstance('prices','Table');
				$qty_row=JTable::getInstance('productquantities','Table');

				if( ($value['price_id'] > 0 ) && ($value['item_price'] > 0 || !empty($value['item_sku']) || $value['product_enabled'] >= 0) )
				{
					$prices_row->load($value['price_id']);

					if(!$prices_row->bind($value))
					{
						return false;
					}


					if(!$prices_row->check($value))
					{
						return false;
					}

					if($value['product_enabled'] == 0) {

						$prices_row->product_enabled = 0;
					}

					if(!$prices_row->store($value))
					{
						return false;
					}
				}
			}
			return true;
		}

}

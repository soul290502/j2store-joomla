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


//no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

class J2StoreModelPaImport extends J2StoreModel
{

	/**
	 * Product Id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * data
	 *
	 * @var array
	 */
	var $_data = null;

	var $_namespace = 'com_j2store.paimport';

	function __construct()
	{
		parent::__construct();

		$app = JFactory::getApplication();

		$this->setId($app->input->getInt('product_id'));

		// Get the pagination request variables
		$limit		= $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$limitstart	= $app->getUserStateFromRequest( $this->_namespace.'.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

	}

	function setId($id)
	{
		// Set taxprofile id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	function getId() {
		return $this->_id;
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
		// Get the WHERE and ORDER BY clauses for the query
		$query->select('p.id, p.title')->from('#__content AS p');
		$this->_buildContentWhere($query);
		$this->_buildContentOrderBy($query);
		return $query;
	}

	function _buildContentOrderBy($query)
	{
		$app = JFactory::getApplication();

		$filter_order		= $app->getUserStateFromRequest( $this->_namespace.'filter_order',		'filter_order',		'p.ordering',	'cmd' );
		$filter_order_Dir	= $app->getUserStateFromRequest( $this->_namespace.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$query->order($filter_order.' '.$filter_order_Dir);
	}

	function _buildContentWhere($query)
	{
		$app = JFactory::getApplication();
		$filter_order		= $app->getUserStateFromRequest( $this->_namespace.'filter_order',		'filter_order',		'p.ordering',	'cmd' );
		$filter_order_Dir	= $app->getUserStateFromRequest( $this->_namespace.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );

		$query->where('p.id IN (SELECT product_id FROM #__j2store_product_options)');
		$query->where('p.id !='.$this->_id);
	}

	function getProductOptions($product_id) {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('po.*, o.type, o.option_unique_name, o.option_name');
		$query->from('#__j2store_product_options AS po');
		$query->where('po.product_id='.$db->quote($product_id));
		$query->order('po.product_option_id ASC');

		$query->leftJoin('#__j2store_options AS o ON po.option_id=o.option_id');

		$db->setQuery($query);
		return $db->loadObjectList();
	}

	function getProductOptionValues($product_option_id) {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('pov.*, ov.optionvalue_name');
		$query->from('#__j2store_product_optionvalues AS pov');
		$query->where('pov.product_option_id='.$db->quote($product_option_id));
		$query->order('pov.ordering ASC');

		$query->leftJoin('#__j2store_optionvalues AS ov ON pov.optionvalue_id=ov.optionvalue_id');
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	/**
	 * copy the attributes and options.
	 *
	 * @source_product_id  int  Source product id.
	 * @dest_product_id  int  Destination product id.
	 *
	 * @since   2.7
	 */

	function importAttributeFromProduct($source_product_id, $dest_product_id) {

		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');

		//first get the attributes of source product

		$source_attributes = $this->getProductOptions($source_product_id);

		if(count($source_attributes) < 1) {
			$this->setError(JText::_('J2STORE_PAI_PRODUCT_DONT_HAVE_ATTRIBUTES'));
			return false;
		}

		//now we have the product options. Loop to insert them
		foreach ($source_attributes as $s_attribute) {

			unset($sa_item);

			//load source first
			$sa_item = JTable::getInstance('ProductOptions', 'Table');
			$sa_item->load($s_attribute->product_option_id);

			//now copy it
			$dest_row = JTable::getInstance('ProductOptions', 'Table');
			$dest_row  = $sa_item;
			$dest_row->product_option_id = NULL;
			$dest_row->product_id = $dest_product_id;
			$dest_row->store();
			//	$dest_row->reorder();
			//now copy the product option values
			$source_attribute_options = $this->getProductOptionValues($s_attribute->product_option_id);

			if(count($source_attribute_options)) {
				foreach ($source_attribute_options as $sa_option) {

					unset($sao_item);
					//load source
					$sao_item = JTable::getInstance('ProductOptionValues', 'Table');
					$sao_item->load($sa_option->product_optionvalue_id);

					//now copy it;

					$dest_sao_row = JTable::getInstance('ProductOptionValues', 'Table');
					$dest_sao_row = $sao_item;

					$dest_sao_row->product_optionvalue_id = NULL;
					$dest_sao_row->product_option_id = $dest_row->product_option_id;
					$dest_sao_row->product_id = $dest_row->product_id;
					$dest_sao_row->store();
					//$dest_sao_row->reorder();
				}
			}
		}
		return true;
	}
}
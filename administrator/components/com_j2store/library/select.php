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
jimport('joomla.html.html');
//jimport('joomla.html.html.select');
if (!version_compare(JVERSION, '3.0', 'ge'))
{
	require_once (JPATH_SITE.'/libraries/joomla/html/html/select.php');
}
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/prices.php');
class J2StoreSelect extends JHtmlSelect
{
	/**
	 * Generates a +/- select list for pao prefixes
	 *
	 * @param unknown_type $selected
	 * @param unknown_type $name
	 * @param unknown_type $attribs
	 * @param unknown_type $idtag
	 * @param unknown_type $allowAny
	 * @param unknown_type $title
	 * @return unknown_type
	 */
	public static function productattributeoptionprefix( $selected, $name = 'filter_prefix', $attribs = array('class' => 'j2storeprefix', 'size' => '1'), $idtag = null, $allowAny = false, $title = 'Select Prefix' )
	{
		$list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -" );
		}

		$list[] = JHTML::_('select.option',  '+', "+" );
		$list[] = JHTML::_('select.option',  '-', "-" );

		return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
	}

	/**
	 * Generates a selectlist for the specified Product Attribute
	 *
	 * @param unknown_type $productattribute_id
	 * @param unknown_type $selected
	 * @param unknown_type $name
	 * @param unknown_type $attribs
	 * @param unknown_type $idtag
	 * @return unknown_type
	 */

	public static function productattributeoptions( $productattribute_id, $selected, $name = 'filter_pao', $attribs = array('class' => 'inputbox', 'size' => '1'), $idtag = null, $required=0, $opt_selected = array())
	{
		$list = array();
		$j2storeparams   = JComponentHelper::getParams('com_j2store');
		JModelLegacy::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_j2store'.DS.'models' );
		$model = JModelLegacy::getInstance( 'ProductAttributeOptions', 'J2StoreModel' );
		$model->setId($productattribute_id );
		$model->setState('order', 'a.ordering');
		$items = $model->getAllData();

		//now pass it to a view and get things done

		JLoader::register( "J2StoreViewMyCart", JPATH_SITE."/components/com_j2store/views/mycart/view.html.php" );

		$config = array();
		$config['base_path'] = JPATH_SITE."/components/com_j2store";

		// finds the default Site template
		$db = JFactory::getDBO();
		$query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home=1";
		$db->setQuery( $query );
		$template = $db->loadResult();

		jimport('joomla.filesystem.file');
		if (JFile::exists(JPATH_SITE.'/templates/'.$template.'/html/com_j2store/mycart/attributeradio.php'))
		{
			// (have to do this because we load the same view from the admin-side Orders view, and conflicts arise)
			$config['template_path'] = JPATH_SITE.'/templates/'.$template.'/html/com_j2store/mycart';
		}

		$view = new J2StoreViewMyCart( $config );
		$view->addTemplatePath(JPATH_SITE.'/templates/'.$template.'/html/com_j2store/mycart');
		require_once(JPATH_SITE.'/components/com_j2store/models/mycart.php');
		$cartmodel =  new J2StoreModelMyCart();

		$view->set( '_controller', 'mycart' );
		$view->set( '_view', 'mycart' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', false);
		$view->setModel( $model, true );
		$view->assign( 'productattribute_id', $productattribute_id);
		$view->assign( 'name', $name);
		$view->assign( 'selected', $selected);
		$view->assign( 'attribs', $attribs);
		$view->assign( 'idTag', $idtag);
		$view->assign( 'required', $required);
		$view->assign( 'items', $items);
		$view->assign( 'params', $j2storeparams);
		$view->setLayout( 'attributeselect' );

		//$this->_setModelState();
		ob_start();
		$view->display();
		$html .= ob_get_contents();
		ob_end_clean();
		return $html;

	}

	public static function radio_productattributeoptions($productattribute_id, $selected, $name = 'filter_pao', $attribs = array('class' => 'inputbox'), $idtag = null, $required=0, $opt_selected = array())
	{

		$list = array();
		$app = JFactory::getApplication();
		$j2storeparams   = JComponentHelper::getParams('com_j2store');
		JModelLegacy::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/models' );
		$model = JModelLegacy::getInstance( 'ProductAttributeOptions', 'J2StoreModel' );
		$model->setId($productattribute_id );
		$model->setState('order', 'a.ordering');
		$items = $model->getAllData();

		//now pass it to a view and get things done

		JLoader::register( "J2StoreViewMyCart", JPATH_SITE."/components/com_j2store/views/mycart/view.html.php" );

		$config = array();
		$config['base_path'] = JPATH_SITE."/components/com_j2store";

		// finds the default Site template
		$db = JFactory::getDBO();
		$query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home=1";
		$db->setQuery( $query );
		$template = $db->loadResult();

		jimport('joomla.filesystem.file');
		if (JFile::exists(JPATH_SITE.'/templates/'.$template.'/html/com_j2store/mycart/attributeradio.php'))
		{
			// (have to do this because we load the same view from the admin-side Orders view, and conflicts arise)
			$config['template_path'] = JPATH_SITE.'/templates/'.$template.'/html/com_j2store/mycart';
		}

		$view = new J2StoreViewMyCart( $config );
		$view->addTemplatePath(JPATH_SITE.'/templates/'.$template.'/html/com_j2store/mycart');
		require_once(JPATH_SITE.DS.'components'.DS.'com_j2store'.DS.'models'.DS.'mycart.php');
		$cartmodel =  new J2StoreModelMyCart();

		$view->set( '_controller', 'mycart' );
		$view->set( '_view', 'mycart' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', false);
		$view->setModel( $model, true );
		$view->assign( 'productattribute_id', $productattribute_id);
		$view->assign( 'name', $name);
		$view->assign( 'idTag', $idtag);
		$view->assign( 'required', $required);
		$view->assign( 'attribs', $attribs);
		$view->assign( 'items', $items);
		$view->assign( 'params', $j2storeparams);
		$view->setLayout( 'attributeradio' );

		//$this->_setModelState();
		ob_start();
		$view->display();
		$html .= ob_get_contents();
		ob_end_clean();
		return $html;


	}

	public function getAttributeDisplayFormat($attribute_id) {
		$row = self::getAttribute($attribute_id);
		return $row->productattribute_display_type;
	}

	public function getAttributeRequired($attribute_id) {
		$row = self::getAttribute($attribute_id);
		return $row->productattribute_required;
	}

	public function getAttributeName($attribute_id) {
		$row = self::getAttribute($attribute_id);
		return $row->productattribute_name;
	}


	protected function getAttribute($attribute_id) {

		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
		$row = JTable::getInstance('ProductAttributes', 'Table');
		$row->load($attribute_id);
		return $row;
	}


	/**
	 * Generates shipping method type list
	 *
	 * @param string The value of the HTML name attribute
	 * @param string Additional HTML attributes for the <select> tag
	 * @param mixed The key that is selected
	 * @returns string HTML for the radio list
	 */
	public static function shippingtype( $selected, $name = 'filter_shipping_method_type', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'J2STORE_SELECT_SHIPPING_TYPE')
	{
		$list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -" );
		}
		require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/shipping.php');
		$items = J2StoreShipping::getTypes();
		foreach ($items as $item)
		{
			$list[] = JHTML::_('select.option', $item->id, $item->title );
		}

		return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
	}

	/**
	 * Generates a selectlist for shipping methods
	 *
	 * @param unknown_type $selected
	 * @param unknown_type $name
	 * @param unknown_type $attribs
	 * @param unknown_type $idtag
	 * @return unknown_type
	 */
	public static function shippingmethod( $selected, $name = 'filter_shipping_method', $attribs = array('class' => 'inputbox'), $idtag = null )
	{
		$list = array();

		JModelLegacy::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/models' );
		$model = JModel::getInstance( 'shippingmethods', 'J2StoreModel' );
		$model->setState('filter_enabled', true);
		$items = $model->getList();
		foreach (@$items as $item)
		{
			$list[] =  self::option( $item->shipping_method_id, JText::_($item->shipping_method_name));
		}
		return JHTML::_('select.radiolist', $list, $name, $attribs, 'value', 'text', $selected, $idtag);
	}

	public static function taxclass($default, $name) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('taxprofile_id as value, taxprofile_name as text')->from('#__j2store_taxprofiles')
		->where('state=1');
		$db->setQuery($query);
		$array = $db->loadObjectList();
		$options[] = JHtml::_( 'select.option', 0, JText::_('J2STORE_SELECT_OPTION'));
		foreach( $array as $data) {
			$options[] = JHtml::_( 'select.option', $data->value, $data->text);
		}
		return	JHtml::_('select.genericlist', $options, $name, 'class="inputbox"', 'value', 'text', $default);
	}

	public static function geozones($default, $name) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('geozone_id as value, geozone_name as text')->from('#__j2store_geozones')
		->where('state=1');
		$db->setQuery($query);
		$array = $db->loadObjectList();
		$options[] = JHtml::_( 'select.option', 0, JText::_('J2STORE_SRATE_SELECT_GEOZONE'));
		foreach( $array as $data) {
			$options[] = JHtml::_( 'select.option', $data->value, $data->text);
		}
		return	JHtml::_('select.genericlist', $options, $name, 'class="inputbox"', 'value', 'text', $default);

	}

}

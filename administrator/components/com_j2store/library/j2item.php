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


/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/version.php');
class J2StoreItem
{
	/**
	 *
	 * @return unknown_type
	 */
	public static function display( $articleid )
	{
		$html = '';
		if(empty($articleid)) {
			return;
		}
		$item = self::getArticle($articleid);
		$mainframe = JFactory::getApplication();
		// Return html if the load fails
		if (!$item->id)
		{
			return $html;
		}

		$item->title = JFilterOutput::ampReplace($item->title);

		$item->text = '';

		$item->text = $item->introtext . chr(13).chr(13) . $item->fulltext;

		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$params		=$mainframe->getParams('com_content');

		// Use param for displaying article title
		$j2store_params = JComponentHelper::getParams('com_j2store');
		$show_title = $j2store_params->get('show_title', $params->get('show_title') );
		if ($show_title)
		{
			$html .= "<h3>{$item->title}</h3>";
		}
		$html .= $item->introtext;

		return $html;
	}


	public static function getJ2Image($id, $jparams) {

		$app = JFactory::getApplication();
		$item = self::getArticle($id);
		$item_image =new JRegistry();
		$item_image->loadString($item->images, 'JSON');
		/*
		 * JRegistry Object ( [data:protected] => stdClass Object ( [image_intro] => [float_intro] =>
		 		* [image_intro_alt] => [image_intro_caption] =>
		 		* [image_fulltext] => images/sampledata/parks/landscape/120px_rainforest_bluemountainsnsw.jpg [float_fulltext] =>
		 		* [image_fulltext_alt] => [image_fulltext_caption] => ) )
		* */
		if ($jparams->get('show_thumb_cart') == 'fulltext' && $item_image->get('image_fulltext') ) {
			$image = '<img src="'.JURI::root().$item_image->get('image_fulltext').
			'" alt="'.$item_image->get('image_fulltext_alt').
			'" title="'.$item_image->get('image_fulltext_caption').
			'" id="itemImg'.$jparams->get('cartimage_size','small').'" />';

		} else 	if ($jparams->get('show_thumb_cart') == 'intro' && $item_image->get('image_intro') ) {
			$image = '<img src="'.JURI::root().$item_image->get('image_intro').
			'" alt="'.$item_image->get('image_intro_alt').
			'" title="'.$item_image->get('image_intro_caption').
			'" id="itemImg'.$jparams->get('cartimage_size','small').'" />';

		} else 	if ($jparams->get('show_thumb_cart') == 'within_text') {
			$image_path = J2StoreItem::getImages($item->introtext);
			$image = '<img src="'.$image_path.
			'" id="itemImg'.$jparams->get('cartimage_size','small').'" />';
		} else {
			$image = '';
		}

		return $image;

	}

	public static function getImages($text) {
		$matches = array();
		preg_match("/\<img.+?src=\"(.+?)\".+?\/>/", $text, $matches);
		$images = '';
		$images = false;
		$paths = array();
		if (isset($matches[1])) {

			$image_path = $matches[1];

			//joomla 1.5 only
			$full_url = JURI::base();

			//remove any protocol/site info from the image path
			$parsed_url = parse_url($full_url);

			$paths[] = $full_url;
			if (isset($parsed_url['path']) && $parsed_url['path'] != "/") $paths[] = $parsed_url['path'];


			foreach ($paths as $path) {
				if (strpos($image_path,$path) !== false) {
					$image_path = substr($image_path,strpos($image_path, $path)+strlen($path));
				}
			}

			// remove any / that begins the path
			if (substr($image_path, 0 , 1) == '/') $image_path = substr($image_path, 1);

			//if after removing the uri, still has protocol then the image
			//is remote and we don't support thumbs for external images
			if (strpos($image_path,'http://') !== false ||
					strpos($image_path,'https://') !== false) {
				return false;
			}

			$images = JURI::Root(True)."/".$image_path;
		}
		return $images;
	}

	public static function isShippingEnabled($product_id) {
		$row = J2StoreItem::_getJ2Item($product_id);
		if($row->item_shipping) {
			return true;
		}
		return false;
	}

	public static function _getJ2Item($id) {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('*');
		$query->from('`#__j2store_prices`');
		$query->where('article_id='.$id);

		$db->setQuery($query);
		$item=$db->loadObject();
		return $item;
	}

	public static function getTaxProfileId($product_id){
		$row = J2StoreItem::_getJ2Item($product_id);
		return $row->item_tax;
	}

	public static function getArticle($id) {
		static $sets;

		if ( !is_array( $sets ) )
		{
			$sets = array( );
		}
		if ( !isset( $sets[$id] ) )
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')->from('#__content')->where('id='.$id);
			$db->setQuery($query);
			$sets[$id] = $db->loadObject();
		}
		return $sets[$id];
	}



	/**
	 * Given a multi-dimensional array,
	 * this will find all possible combinations of the array's elements
	 *
	 * Given:
	 *
	 * $traits = array
	 * (
	 *   array('Happy', 'Sad', 'Angry', 'Hopeful'),
	 *   array('Outgoing', 'Introverted'),
	 *   array('Tall', 'Short', 'Medium'),
	 *   array('Handsome', 'Plain', 'Ugly')
	 * );
	 *
	 * Returns:
	 *
	 * Array
	 * (
	 *      [0] => Happy,Outgoing,Tall,Handsome
	 *      [1] => Happy,Outgoing,Tall,Plain
	 *      [2] => Happy,Outgoing,Tall,Ugly
	 *      [3] => Happy,Outgoing,Short,Handsome
	 *      [4] => Happy,Outgoing,Short,Plain
	 *      [5] => Happy,Outgoing,Short,Ugly
	 *      etc
	 * )
	 *
	 * @param string $string   The result string
	 * @param array $traits    The multi-dimensional array of values
	 * @param int $i           The current level
	 * @param array $return    The final results stored here
	 * @return array           An Array of CSVs
	 */
	static function getCombinations1( $string, $traits, $i, &$return )
	{
		if ( $i >= count( $traits ) )
		{
			$return[] = str_replace( ' ', ',', trim( $string ) );
		}
		else
		{
			foreach ( $traits[$i] as $trait )
			{
				J2StoreItem::getCombinations( "$string $trait", $traits, $i + 1, $return );
			}
		}
	}

	public static function getCombinations($traits)
	{
		$max_attribute_combination=1;
		foreach ( $traits as $trait )
		{
			$max_attribute_combination=$max_attribute_combination*count($trait);
		}
		for($i=0;$i<$max_attribute_combination;$i++)
		{
			$output="";
			$quotient = $i;

			foreach ( array_reverse($traits) as $trait )
			{
				$divisor=count($trait);
				$remainder = $quotient % $divisor ;
				$quotient = $quotient / $divisor ;
				$output= $trait[$remainder].','.$output;
			}
			$result[]=trim($output,",");
		}
		return $result;
	}

	/**
	 * Will return all the CSV combinations possible from a product's attribute options
	 *
	 * @param unknown_type $product_id
	 * @param $attributeOptionId
	 * @return unknown_type
	 */
	static function getProductAttributeCSVs( $product_id, $attributeOptionId = '0' )
	{
		$return = array( );
		$traits = array( );

		JModelLegacy::addIncludePath( JPATH_ADMINISTRATOR .'/components/com_j2store/models' );
		// get all productattributes
		$model = JModelLegacy::getInstance( 'ProductOptionValues', 'J2StoreModel' );
		$model->setState( 'filter_product', $product_id );
		$model->setState( 'filter_option_type', 'select or radio');
		$model->setState( 'filter_array', 1);
		if ( $attributes = $model->getProductOptions( ) )
		{
			foreach ( $attributes as $attribute )
			{
				$paoModel = JModelLegacy::getInstance( 'ProductOptionValues', 'J2StoreModel' );
				$paoModel->setState( 'filter_productoption', $attribute->product_option_id);
				if ( $paos = $paoModel->getProductOptionValues( ) )
				{
					$options = array( );
					foreach ( $paos as $pao )
					{
						// Genrate the arrray of single value with the id of newly created attribute option
						if ( $attributeOptionId == $pao->product_optionvalue_id)
						{
							$newOption = array( );
							$newOption[] = ( string ) $attributeOptionId;
							$options = $newOption;
							break;
						}

						$options[] = $pao->product_optionvalue_id;
					}
					$traits[] = $options;
				}
			}
		}
		// run recursive function on the data
		//J2StoreItem::getCombinations( "", $traits, 0, $return );
		$return = J2StoreItem::getCombinations($traits);

		// before returning them, loop through each record and sort them
		$result = array( );
		foreach ( $return as $csv )
		{
			$values = explode( ',', $csv );
			sort( $values );
			$result[] = implode( ',', $values );
		}

		return $result;
	}

	/**
	 * Given a product_id and vendor_id
	 * will perform a full CSV reconciliation of the _productquantities table
	 *
	 * @param $product_id
	 * @param $vendor_id
	 * @param $attributeOptionId
	 * @return unknown_type
	 */
	static function doProductQuantitiesReconciliation( $product_id, $vendor_id = '0', $attributeOptionId = '0' )
	{
		if ( empty( $product_id ) )
		{
			return false;
		}
		if(J2STORE_PRO != 1) return false;

		$params = JComponentHelper::getParams('com_j2store');
		if(!$params->get('enable_inventory', 0)) return false;

		$csvs = J2StoreItem::getProductAttributeCSVs( $product_id, $attributeOptionId );
		JModelLegacy::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/models' );
		$model = JModelLegacy::getInstance( 'ProductQuantities', 'J2StoreModel' );
		$model->setState( 'filter_productid', $product_id );
		$items = $model->getList( );

		$results = J2StoreItem::reconcileProductAttributeCSVs( $product_id, $vendor_id, $items, $csvs );
	}

	/**
	 * Adds any necessary _productsquantities records
	 *
	 * @param unknown_type $product_id     Product ID
	 * @param unknown_type $vendor_id      Vendor ID
	 * @param array $items                 Array of productQuantities objects
	 * @param unknown_type $csvs           CSV output from getProductAttributeCSVs
	 * @return array $items                Array of objects
	 */
	static function reconcileProductAttributeCSVs( $product_id, $vendor_id, $items, $csvs )
	{
		// remove extras
		$done = array( );
		foreach ( $items as $key => $item )
		{
			if ( !in_array( $item->product_attributes, $csvs ) || in_array( $item->product_attributes, $done ) )
			{
				$row = JTable::getInstance( 'ProductQuantities', 'Table' );
				if ( !$row->delete( $item->productquantity_id ) )
				{
					JError::raiseNotice( '1', $row->getError( ) );
				}
				unset( $items[$key] );
			}
			$done[] = $item->product_attributes;
		}

		// add new ones
		$existingEntries = J2StoreItem::getColumn( $items, 'product_attributes' );
		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/tables' );
		foreach ( $csvs as $csv )
		{
			if ( !in_array( $csv, $existingEntries ) )
			{
				$row = JTable::getInstance( 'ProductQuantities', 'Table' );
				$row->product_id = $product_id;
				$row->product_attributes = $csv;
				if ( !$row->save( ) )
				{
					JError::raiseNotice( '1', $row->getError( ) );
				}
				$items[] = $row;
			}
		}
		return $items;
	}

	/**
	 * Extracts a column from an array of arrays or objects
	 *
	 * @static
	 * @param	array	$array	The source array
	 * @param	string	$index	The index of the column or name of object property
	 * @return	array	Column of values from the source array
	 * @since	1.5
	 */
	public static function getColumn(&$array, $index)
	{
		$result = array();

		if (is_array($array))
		{
			foreach (@$array as $item)
			{
				if (is_array($item) && isset($item[$index]))
				{
					$result[] = $item[$index];
				}
				elseif (is_object($item) && isset($item->$index))
				{
					$result[] = $item->$index;
				}
			}
		}
		return $result;
	}


}


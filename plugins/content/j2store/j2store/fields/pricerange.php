<?php
/*------------------------------------------------------------------------
# com_j2store - J2Store v 1.0
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die;
/**
 * Product Options Select Form Field class for the J2Store component
 */
class JFormFieldPriceRange extends JFormFieldText
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'PriceRange';

	protected function getInput() {

		$app = JFactory::getApplication();
		$fieldName = $this->fieldname;

 		//get libraries
 		$html ='';

 		$html .='<table class="table j2store_itemoptions"><tr><td>';

 		$cid = $app->input->get('id', 0);
 		if($cid) {
 			$link = 'index.php?option=com_j2store&view=products&task=setpricerange&id='.$cid.'&tmpl=component';
 			//let us first get Product Attribute Names
 			$attributes = $this->getProductPriceRange($cid);
 			if(!empty($attributes)) {
 				$html .=$attributes;
 			}

 			$html .= J2StorePopup::popup( $link, JText::_( "J2STORE_PLG_CONTENT_PR_ADD_REMOVE_PRICERANGE" ) );
 			$html .= '<small>'.JText::_('J2STORE_PLG_CONTENT_PR_NOTE').'</small>';
 		} else {
 			$html .= JText::_('J2STORE_PLG_CONTENT_PR_CLICK_SAVE_FILL_RANGE');
 		}
 		$html .= '</td></tr></table>';
 		return $html;

	 }

	 function getProductPriceRange($product_id) {

	 	$db = JFactory::getDBO();
	 	$query = 'SELECT a.* FROM #__j2store_productprices AS a WHERE a.product_id='. (int) $product_id
	 	.' ORDER BY a.ordering ASC'
	 	;
	 	$db->setQuery($query);
	 	$rows = $db->loadObjectList();
	 	$html= '';

	 	if(count($rows)) {
	 		$html .='<table class="adminlist table table-striped j2store"  id="j2store_table">
	 		<thead>
	 		<th>'.JText::_('J2STORE_PR_QUANTITY_START').'</th>
	 		<th>'.JText::_('J2STORE_PR_PRICE').'</th>
	 		</thead>
	 		<tbody>';
	 		foreach($rows as $row) {
	 			//lets get a list of attribute options for each attribute

	 			$html .='<tr>';
	 			$html .='<td>'.$row->quantity_start.'&nbsp;&nbsp;';
	 			$html .=JText::_('J2STORE_PR_AND_'.$row->condition).'</td>';
	 			$html .='<td>'.$row->price.'</td>';
	 			$html .='</tr>';
	 		}

	 		$html .='</tbody>';
	 		$html .='</table>';
	 	}
	 	return $html;
	 }

 }
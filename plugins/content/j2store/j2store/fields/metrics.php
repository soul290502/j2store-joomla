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
 * Metics Form Field class for the J2Store component
 */
class JFormFieldMetrics extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Metrics';

	protected function getInput() {

		$app = JFactory::getApplication();
		$fieldName = $this->fieldname;

 		//get libraries
 		$html ='';

 		$html .='<table id="attribute_options_table" class="adminlist table table-striped table-bordered j2store_metrics"><tr><td>';
 		$cid = $app->input->get('id', 0);
		if($cid) {
 			$row = $this->getData($cid);
			//dimentions
 			$html .='<label>'.JText::_('J2STORE_METRICS_DIMENTIONS').'</label>';
 			$html .="<input class='' name='jform[attribs][item_metrics][item_length]' value='{$row->item_length}' />";
 			$html .="<input class='' name='jform[attribs][item_metrics][item_width]' value='{$row->item_width}' />";
 			$html .="<input class='' name='jform[attribs][item_metrics][item_height]' value='{$row->item_height}' />";
 			//length class
 			$html .='</tr><tr><td>';
 			$html .='<label>'.JText::_('J2STORE_METRICS_LENGTH_CLASS').'</label>';
 			$html .= $this->getLengthClass($cid);
 			$html .='</tr><tr><td>';

 			//weight
 			$html .='<label>'.JText::_('J2STORE_METRICS_WEIGHT').'</label>';
 			$html .="<input class='' name='jform[attribs][item_metrics][item_weight]' value='{$row->item_weight}' />";
 			$html .='</tr><tr><td>';

 			//weight class
 			$html .='<label>'.JText::_('J2STORE_METRICS_WEIGHT_CLASS').'</label>';
 			$html .= $this->getWeightClass($cid);

		} else {
			$html .= JText::_('J2STORE_METRICS_SAVE_TO_ADD');
		}

		$html .= '</td></tr></table>';

 		return $html;

	 }

	 protected function getData($product_id) {

	 	$row = JTable::getInstance('Prices', 'Table');
	 	if($product_id) {
	 		$row->load(array('article_id'=>$product_id));
	 	}
	 	return $row;
	 }

	 protected function getLengthClass($product_id) {
	 	$product = $this->getData($product_id);

	 	require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/models/lengths.php');
		$model = new J2StoreModelLengths;
		$lengths = $model->getLengths();
		//generate country filter list
		$length_options = array();
			$length_options[] = JHTML::_('select.option', 0, JText::_('J2STORE_METRICS_SELECT_LENGTH_CLASS'));
		foreach($lengths as $row) {
			$length_options[] =  JHTML::_('select.option', $row->length_class_id, $row->length_title);
		}

		return JHTML::_('select.genericlist', $length_options, 'jform[attribs][item_metrics][item_length_class_id]', 'onchange=', 'value', 'text', $product->item_length_class_id);
	 }

	 protected function getWeightClass($product_id) {
	 	$product = $this->getData($product_id);
	 	require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/models/weights.php');
	 	$model = new J2StoreModelWeights;
	 	$weights = $model->getWeights();
	 	//generate country filter list
	 	$weight_options = array();
	 	$weight_options[] = JHTML::_('select.option', 0, JText::_('J2STORE_METRICS_SELECT_WEIGHT_CLASS'));
	 	foreach($weights as $row) {
	 		$weight_options[] =  JHTML::_('select.option', $row->weight_class_id, $row->weight_title);
	 	}

	 	return JHTML::_('select.genericlist', $weight_options, 'jform[attribs][item_metrics][item_weight_class_id]', 'onchange=', 'value', 'text', $product->item_weight_class_id);

	 }
}
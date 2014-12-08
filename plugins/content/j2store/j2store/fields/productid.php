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
defined('_JEXEC') or die;

// import the list field type
jimport('joomla.form.helper');
/**
 * Product ID Field class for the J2Store component
 */
class JFormFieldProductID extends JFormFieldText
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'ProductID';

	protected function getInput()
	 {
	 	$app = JFactory::getApplication();
	 	$product_id = $app->input->get('id');
	 	$html = '';
		JPluginHelper::importPlugin('j2store');
	 	//trigger plugin event
		$results = $app->triggerEvent('onJ2StoreBeforeProductForm', array($product_id));
		$html .= trim(implode("\n", $results));

	 	$html .='<table class="adminlist table table-striped">';
	 	if(isset($product_id)){
	 		$html .= '<tr><td>';
	 		$html .= '<label class="j2store_product_id">';
	 		$html .= $product_id;
	 		$html .= '</label>';
	 		$html .= '</td></tr><tr><td>';

	 		$html .= JText::_('PLG_J2STORE_PRODUCT_SHORT_TAG').": {j2storecart $product_id}";
	 		$html .= '&nbsp;&nbsp;';
	 		$html .= JHtml::tooltip(JText::_('PLG_J2STORE_PRODUCT_SHORT_TAG_HELP'), JText::_('PLG_J2STORE_PRODUCT_SHORT_TAG'),'tooltip.png', '', '', false);
	 		$html .= '</td></tr>';
	 	} else {
	 		$html .= '<div class="alert alert-info">';
	 		$html .= JText::_('PLG_J2STORE_PRODUCT_ID_DESC');
	 		$html .= '</div>';
	 	}
	 	$html .= '</table>';
		return $html;
	}
}

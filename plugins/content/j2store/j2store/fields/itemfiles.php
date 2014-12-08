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
 * TaxSelect Form Field class for the J2Store component
 */
class JFormFieldItemFiles extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'ItemFiles';

function getInput() {

//fetchElement($name, $value, &$node, $control_name){
		$app = JFactory::getApplication();
		$fieldName = $this->fieldname;

		//get libraries
		$html ='';

		$html .='<table  class="adminlist table j2store_itemfiles"><tr><td>';

		$cid = JRequest::getVar('id');
		if($cid) {
			$link = 'index.php?option=com_j2store&view=products&task=setfiles&id='.$cid.'&tmpl=component';

			//let us first get Product Attribute Names
			$files = $this->getProductFiles($cid);
			if(!empty($files)) {
				$html .=$files;
			}

		//$html .= J2StorePopup::popup( $link, JText::_( "PLG_J2STORE_ADD_REMOVE_FILES" ), array('onclose' => '\function(){j2storeNewModal(\''.JText::_('Saving the Product Files...').'\'); Joomla.submitbutton(\'apply\');}') );
		$html .= J2StorePopup::popup( $link, JText::_( "PLG_J2STORE_ADD_REMOVE_FILES" ) );
		$html .= JText::_('PLG_J2STORE_FILES_NOTE');
		//	$html.='<a rel="{handler: "iframe", size: {x: 800, y: 500}}" onclick="IeCursorFix(); return false;" href="index.php?option=com_j2store&view=products&task=setfiles&id='.$cid.'&tmpl=component" title="Files" class="modal-button">Files</a>';

		} else {
			$html .= JText::_('PLG_J2STORE_CLICK_TO_UPLOAD_FILES');
		}
		$html .= '</td></tr></table>';

		JPluginHelper::importPlugin('j2store');
		//trigger plugin event
		$results = $app->triggerEvent('onJ2StoreAfterProductForm', array($cid));
		$html .= trim(implode("\n", $results));

		//echo $html.'ss';exit;
		return $html;
	}


	function getProductFiles($product_id) {

		$db = JFactory::getDBO();
		$query = 'SELECT a.* FROM #__j2store_productfiles AS a WHERE a.product_id='. (int) $product_id
				 .' ORDER BY a.ordering'
		;
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$html= '';

		if(count($rows)) {
			$html .='<h4>'.JText::_('J2STORE_PFILE_CURRENT_FILES').'</h4>';
		$html .='<table class="adminlist table table-striped" id="j2store_files_table">
			<thead>
			<th>'.JText::_('J2STORE_PLG_CONTENT_FILE_LABEL').'</th>
			<th>'.JText::_('J2STORE_PLG_CONTENT_MAX_DOWNLOADS').'</th>
			<th>'.JText::_('J2STORE_PLG_CONTENT_FILE_ENABLED').'</th>
			<th>'.JText::_('J2STORE_PLG_CONTENT_PURCHASE_NEEDED').'</th>
			</thead>
			<tbody>';
			foreach($rows as $row) {

				$html .='<tr>';
				$html .='<td>'.$row->product_file_display_name.'</td>';
				$html .='<td>'.(($row->download_limit==-1)?JText::_('J2STORE_PLG_CONTENT_UNLIMITED_DOWNLOADS'):$row->download_limit).'</td>';
				$state =($row->state)?JText::_('J2STORE_YES'):JText::_('J2STORE_NO');
				$html .='<td class="'.(($row->state)?'enabled':'disabled').'" >'.$state .' </td>';
				$purchase =($row->purchase_required)?JText::_('J2STORE_YES'):JText::_('J2STORE_NO');
				$html .='<td class="'.(($row->purchase_required)?'enabled':'disabled').'" >'.$purchase .' </td>';
				$html .='</tr>';
			}

			$html .='</tbody>';
			$html .='</table>';
		}
		return $html;
	}

}

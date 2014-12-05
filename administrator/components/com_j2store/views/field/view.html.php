<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * HTML View class for the HelloWorld Component
 */
class J2StoreViewField extends J2StoreView
{
	// Overwriting JView display method
	function display($tpl = null)
	{

		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$model		= $this->getModel('field');
		$params = JComponentHelper::getParams('com_j2store');
		$selectableBase = J2StoreFactory::getSelectableBase();

		 $fieldid = $app->input->getInt('field_id');

		if(!empty($fieldid)) {
			$field = $selectableBase->getField($fieldid);
			$data = null;
			$allFields = $selectableBase->getFields('', $data, $field->field_table);
		} else {
			$field = $model->getTable();
			$field->field_table = 'address';
			$field->field_published = 1;
			$field->field_type = 'text';
			$field->field_backend = 1;
			$allFields = array();
		}
		$this->allFields = $allFields;
		$this->field = $field;

		$this->fieldsClass = $selectableBase;

		$lists = array();

		//get the field type
		$fieldtype =  J2StoreFactory::getSelectableFields();
		$this->assignRef('fieldtype', $fieldtype);

		$script = 'function addLine(){
		var myTable=window.document.getElementById("tablevalues");
		var newline = document.createElement(\'tr\');
		var column = document.createElement(\'td\');
		var column2 = document.createElement(\'td\');
		var column3 = document.createElement(\'td\');
		var input = document.createElement(\'input\');
		var input2 = document.createElement(\'input\');
		var input3 = document.createElement(\'select\');
		var option1 = document.createElement(\'option\');
		var option2 = document.createElement(\'option\');
		input.type = \'text\';
		input2.type = \'text\';
		option1.value= \'0\';
		option2.value= \'1\';
		input.name = \'field_values[title][]\';
		input2.name = \'field_values[value][]\';
		input3.name = \'field_values[disabled][]\';
		option1.text= \''.JText::_('J2STORE_NO',true).'\';
		option2.text= \''.JText::_('J2STORE_YES',true).'\';
		try { input3.add(option1, null); } catch(ex) { input3.add(option1); }
		try { input3.add(option2, null); } catch(ex) { input3.add(option2); }
		column.appendChild(input);
		column2.appendChild(input2);
		column3.appendChild(input3);
		newline.appendChild(column);
		newline.appendChild(column2);
		newline.appendChild(column3);
		myTable.appendChild(newline);
		}

		function deleteRow(divName,inputName,rowName){
			var d = document.getElementById(divName);
			var olddiv = document.getElementById(inputName);
			if(d && olddiv){
				d.removeChild(olddiv);
				document.getElementById(rowName).style.display="none";
			}
			return false;
		}

		function setVisible(value){
			if(value=="product" || value=="item" || value=="category"){
				document.getElementById(\'category_field\').style.display = "";
			}else{
				document.getElementById(\'category_field\').style.display = \'none\';
			}
		}';

		$doc->addScriptDeclaration($script);

	//	$lists['value_type'] = JHTML::_('select.radiolist', $value_type_options, 'value_type', null, 'value', 'text', $data->value_type);

	//	$logged_options = array(JHTML::_('select.option', '0', JText::_('No') ),
	//			JHTML::_('select.option', '1', JText::_('Yes') )	);
	//	$lists['logged'] = JHTML::_('select.radiolist', $logged_options, 'logged', null, 'value', 'text', $data->logged);

		//country, zone type
		$zoneType = new j2storeZoneType();
		$this->assignRef('zoneType', $zoneType);

		$this->assignRef('lists',	$lists);
		$this->assignRef('params',	$params);

		$this->addToolBar();
		$toolbar = new J2StoreToolBar();
        $toolbar->renderLinkbar();

		parent::display($tpl);

	}

	protected function addToolBar() {
			 // setting the title for the toolbar string as an argument
			   JToolBarHelper::title(JText::_('J2STORE_FIELDS'),'j2store-logo');

				// Set toolbar items for the page
				$edit		= JRequest::getVar('edit',true);
				$text = !$edit ? JText::_( 'New' ) : JText::_( 'Edit' );
				JToolBarHelper::apply('apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save();
				if (!$edit)  {
					JToolBarHelper::cancel();
				} else {
					// for existing items the button is renamed `close`
					JToolBarHelper::cancel( 'cancel', 'JTOOLBAR_CLOSE' );
				}


		 }
}

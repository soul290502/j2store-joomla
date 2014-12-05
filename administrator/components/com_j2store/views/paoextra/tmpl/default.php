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
$state = @$this->state;
$items = @$this->items;
$row = @$this->row;
$action = JRoute::_( 'index.php?option=com_j2store&view=products&task=setpaoextra&tmpl=component&id='.$row->productattributeoption_id);
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/select.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/popup.php');

?>


<h3>
	<?php echo JText::_( 'J2STORE_PAO_SET_EXTRA_FOR' ); ?>
	:
	<?php echo $row->productattributeoption_name; ?>
</h3>

<form action="<?php echo $action; ?>" method="post" name="adminForm"
	enctype="multipart/form-data">

	<div class="note_green row-fluid">
		<div>
			<button class="btn btn-primary"
				onclick="document.getElementById('task').value='savepaoextra'; document.adminForm.submit();">
				<?php echo JText::_('J2STORE_SAVE_CHANGES'); ?>
			</button>
		</div>

		<table class="adminlist table table-striped" style="clear: both;">
			<tr>
				<td><label for="productattributeoption_short_desc"><?php echo JText::_('J2STORE_PRODUCTATTRIBUTEOPTION_SHORT_DESC_LABEL');?>
				</label> <?php
				$editor =JFactory::getEditor();
				echo $editor->display('productattributeoption_short_desc', $row->productattributeoption_short_desc, '550', '200', '60', '20', false);
				?>
				</td>
			</tr>
			<tr>
				<td><label for="productattributeoption_long_desc"><?php echo JText::_('J2STORE_PRODUCTATTRIBUTEOPTION_LONG_DESC_LABEL');?>
				</label> <?php
				$editor =JFactory::getEditor();
				echo $editor->display('productattributeoption_long_desc', $row->productattributeoption_long_desc, '550', '200', '60', '20', false);
				?>
				</td>
			</tr>
			<tr>
				<td><label for="productattributeoption_ref"><?php echo JText::_('J2STORE_PRODUCTATTRIBUTEOPTION_REF_LABEL');?>
				</label> <?php
				$editor =JFactory::getEditor();
				echo $editor->display('productattributeoption_ref', $row->productattributeoption_ref, '550', '200', '60', '20', false);
				?>
				</td>
			</tr>

		</table>
	</div>
	<input type="hidden" name="id"
		value="<?php echo $row->productattributeoption_id; ?>" /> <input
		type="hidden" name="task" id="task" value="setpaoextra" />

</form>

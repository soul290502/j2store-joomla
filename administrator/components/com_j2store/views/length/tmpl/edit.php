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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$action = JRoute::_('index.php?option=com_j2store&view=length');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
?>
<div class="j2store">
<form action="<?php echo $action; ?>" method="post" name="adminForm"
	id="adminForm" class="form-validate">
	<fieldset class="fieldset">
			<legend>
				<?php echo JText::_('J2STORE_LENGTHS'); ?>
			</legend>
			<table>
				<tr>
					<td><?php echo $this->form->getLabel('length_title'); ?>
					</td>
					<td><?php echo $this->form->getInput('length_title'); ?>
					<small class="muted"><?php echo JText::_('J2STORE_LENGTH_TITLE_DESC')?></small>
					</td>
				</tr>

				<tr>
					<td><?php echo $this->form->getLabel('length_unit'); ?>
					</td>
					<td><?php echo $this->form->getInput('length_unit'); ?>
					<small class="muted"><?php echo JText::_('J2STORE_LENGTH_UNIT_DESC')?></small>
					</td>
				</tr>

				<tr>
					<td><?php echo $this->form->getLabel('length_value'); ?>
					</td>
					<td><?php echo $this->form->getInput('length_value'); ?>
					<small class="muted"><?php echo JText::_('J2STORE_LENGTH_VALUE_DESC')?></small>
					</td>
				</tr>

				<tr>
					<td><?php echo $this->form->getLabel('state'); ?>
					</td>
					<td><?php echo $this->form->getInput('state'); ?>
					</td>
				</tr>

			</table>
		</fieldset>
	<input type="hidden" name="option" value="com_j2store"> <input
		type="hidden" name="length_class_id"
		value="<?php echo $this->item->length_class_id; ?>"> <input type="hidden"
		name="task" value="">
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>

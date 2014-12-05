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

$action = JRoute::_('index.php?option=com_j2store&view=weight');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
?>
<div class="j2store">
<form action="<?php echo $action; ?>" method="post" name="adminForm"
	id="adminForm" class="form-validate">
	<fieldset class="fieldset">
			<legend>
				<?php echo JText::_('J2STORE_WEIGHTS'); ?>
			</legend>
			<table>
				<tr>
					<td><?php echo $this->form->getLabel('weight_title'); ?>
					</td>
					<td><?php echo $this->form->getInput('weight_title'); ?>
					<small class="muted"><?php echo JText::_('J2STORE_WEIGHT_TITLE_DESC')?></small>
					</td>
				</tr>

				<tr>
					<td><?php echo $this->form->getLabel('weight_unit'); ?>
					</td>
					<td><?php echo $this->form->getInput('weight_unit'); ?>
					<small class="muted"><?php echo JText::_('J2STORE_WEIGHT_UNIT_DESC')?></small>
					</td>
				</tr>

				<tr>
					<td><?php echo $this->form->getLabel('weight_value'); ?>
					</td>
					<td><?php echo $this->form->getInput('weight_value'); ?>
					<small class="muted"><?php echo JText::_('J2STORE_WEIGHT_VALUE_DESC')?></small>
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
		type="hidden" name="weight_class_id"
		value="<?php echo $this->item->weight_class_id; ?>"> <input type="hidden"
		name="task" value="">
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>

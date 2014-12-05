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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$action = JRoute::_('index.php?option=com_j2store&view=currency');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
?>
<div class="j2store">
<form action="<?php echo $action; ?>" method="post" name="adminForm"
	id="adminForm" class="form-validate">
	<fieldset class="fieldset">
			<legend>
				<?php echo JText::_('J2STORE_CURRENCY'); ?>
			</legend>
			<table>
				<tr>
					<td><?php echo $this->form->getLabel('currency_title'); ?>
					</td>
					<td><?php echo $this->form->getInput('currency_title'); ?>
					<small class="muted"><?php echo JText::_('J2STORE_CURRENCY_TITLE_DESC')?></small>
					</td>
				</tr>

				<tr>
					<td><?php echo $this->form->getLabel('currency_code'); ?>
					</td>
					<td><?php echo $this->form->getInput('currency_code'); ?>
					<small class="muted"><?php echo JText::_('J2STORE_CURRENCY_CODE_DESC')?></small>
					</td>
				</tr>

				<tr>
					<td><?php echo $this->form->getLabel('currency_symbol'); ?>
					</td>
					<td><?php echo $this->form->getInput('currency_symbol'); ?>
					<small class="muted"><?php echo JText::_('J2STORE_CURRENCY_SYMBOl_DESC')?></small>
					</td>
				</tr>

				<tr>
					<td><?php echo $this->form->getLabel('currency_position'); ?>
					</td>
					<td><?php echo $this->form->getInput('currency_position'); ?>
					<small class="muted"><?php echo JText::_('J2STORE_CURRENCY_POSITION_DESC')?></small>
					</td>
				</tr>

				<tr>
					<td><?php echo $this->form->getLabel('currency_num_decimals'); ?>
					</td>
					<td><?php echo $this->form->getInput('currency_num_decimals'); ?>
					<small class="muted"><?php echo JText::_('J2STORE_CURRENCY_NUM_DECIMALS_DESC')?></small>
					</td>
				</tr>

				<tr>
					<td><?php echo $this->form->getLabel('currency_decimal'); ?>
					</td>
					<td><?php echo $this->form->getInput('currency_decimal'); ?>
					<small class="muted"><?php echo JText::_('J2STORE_CURRENCY_DECIMAL_SEPARATOR_DESC')?></small>
					</td>
				</tr>

				<tr>
					<td><?php echo $this->form->getLabel('currency_thousands'); ?>
					</td>
					<td><?php echo $this->form->getInput('currency_thousands'); ?>
					<small class="muted"><?php echo JText::_('J2STORE_CURRENCY_THOUSANDS_DESC')?></small>
					</td>
				</tr>

				<tr>
					<td>
					<?php echo $this->form->getLabel('currency_value'); ?>
					</td>
					<td>
					<?php echo $this->form->getInput('currency_value'); ?>
						<?php echo JHtml::tooltip(JText::_('J2STORE_CURRENCY_VALUE_HELP'), JText::_('J2STORE_CURRENCY_VALUE_LABEL'),'tooltip.png', '', '', false);
						?>
					<small class="muted"><?php echo JText::_('J2STORE_CURRENCY_VALUE_DESC')?></small>
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
		type="hidden" name="currency_id"
		value="<?php echo $this->item->currency_id; ?>"> <input type="hidden"
		name="task" value="">
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>

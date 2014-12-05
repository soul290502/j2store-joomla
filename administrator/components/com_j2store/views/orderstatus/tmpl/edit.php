<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Gokila Priya - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

//$action = JRoute::_('index.php?option=com_j2store&view=orderstatus');
$action = JRoute::_('index.php?option=com_j2store&view=orderstatus');
JHtml::_('behavior.keepalive');
?>
<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm">
    				<table>
					<tr>
						<td><?php echo $this->form->getLabel('orderstatus_name'); ?>
						</td>
						<td><?php echo $this->form->getInput('orderstatus_name'); ?>
						</td>
					</tr>
					<tr>
						<td><?php echo $this->form->getLabel('orderstatus_cssclass'); ?>
						</td>
						<td><?php echo $this->form->getInput('orderstatus_cssclass'); ?>
						</td>
					</tr>


				</table>

<input type="hidden" name="option" value="com_j2store">
<input type="hidden" name="orderstatus_id"	value="<?php echo $this->item->orderstatus_id; ?>">
<input type="hidden" name="task" value="">
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

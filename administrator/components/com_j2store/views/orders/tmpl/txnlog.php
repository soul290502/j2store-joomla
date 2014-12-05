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

// no direct access
defined('_JEXEC') or die('Restricted access');
$row = $this->row;
?>
<div class="j2store">
<div class="row-fluid">
	<div class="span12">
		<h3>
			<?php echo JText::_('J2STORE_TRANSACTION_LOG_HEADER'); ?>
			&nbsp;
			<?php echo $row->order_id; ?>
		</h3>
		<div class="alert alert-info">
			<?php echo JText::_('J2STORE_TRANSACTION_LOG_HELP_MSG');?>
		</div>
		<ul>
			<li><?php echo JText::_('J2STORE_ORDER_TRANSACTION_STATUS'); ?>
				<div class="alert alert-warning">
					<small><?php echo JText::_('J2STORE_ORDER_TRANSACTION_STATUS_HELP_MSG'); ?>
					</small>
				</div>
				<p>
					<?php echo JText::_($row->transaction_status); ?>
				</p>
			</li>
			<li><?php echo JText::_('J2STORE_ORDER_TRANSACTION_DETAILS'); ?> <br />
				<div class="alert alert-warning">
					<small><?php echo JText::_('J2STORE_ORDER_TRANSACTION_DETAILS_HELP_MSG'); ?>
					</small>
				</div>
				<p>
					<?php echo JText::_($row->transaction_details); ?>
				</p>
			</li>

		</ul>
	</div>
</div>
</div>
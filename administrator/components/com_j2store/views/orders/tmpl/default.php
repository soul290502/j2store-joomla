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

?>
<div class="j2store">
<form action="index.php?option=com_j2store&view=orders" method="post"
	name="adminForm" id="adminForm">
	<table class="adminlist table table-striped">
		<tr>
			<td align="left" width="100%"><?php echo JText::_( 'J2STORE_FILTER_SEARCH' ); ?>:
				<input type="text" name="search" id="search"
				value="<?php echo htmlspecialchars($this->lists['search']);?>"
				class="text_area" onchange="document.adminForm.submit();" />
				<button class="btn btn-success" onclick="this.form.submit();">
					<?php echo JText::_( 'J2STORE_FILTER_GO' ); ?>
				</button>
				<button class="btn btn-inverse"
					onclick="document.getElementById('search').value='';this.form.submit();">
					<?php echo JText::_( 'J2STORE_FILTER_RESET' ); ?>
				</button>
			</td>
			<td nowrap="nowrap"><?php
			echo $this->lists['orderstate'];
			?>
			</td>
		</tr>
	</table>

	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th width="5"><?php echo JText::_( 'J2STORE_NUM' ); ?>
				</th>
				<th width="20"><input type="checkbox" name="checkall-toggle"
					value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
					onclick="Joomla.checkAll(this)" />
				</th>
				<th class="title"><?php echo JHTML::_('grid.sort',  'J2STORE_INVOICE_NO', 'invoice',$this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<?php if($this->params->get('show_unique_orderid', 0)): ?>
				<th class="title"><?php echo JHTML::_('grid.sort',  'J2STORE_ORDER_ORDER_ID', 'a.order_id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<?php endif; ?>
				<th width="15%" class="title"><?php echo JHTML::_('grid.sort',  'J2STORE_ORDER_BUYER_NAME', 'a.user_id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>

				<th width="15%" class="title"><?php echo JText::_('J2STORE_ORDER_EMAIL'); ?>
				</th>

				<th width="15%"><?php echo JHTML::_('grid.sort',  'J2STORE_ORDER_AMOUNT', 'a.orderpayment_amount', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th width="15%"><?php echo JHTML::_('grid.sort',  'J2STORE_ORDER_PAYMENT_TYPE', 'a.orderpayment_type', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<!--
			<th width="15%">
				<?php echo JHTML::_('grid.sort',  'J2STORE_ORDER_TRANSACTION_STATUS', 'a.transaction_status', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			 -->
				<th width="15%"><?php echo JHTML::_('grid.sort',  'J2STORE_ORDER_ORDER_STATUS', 'a.order_state_id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="9"><?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
			$k = 0;
			for ($i=0, $n=count( $this->items ); $i < $n; $i++)
			{
				$row = &$this->items[$i];
				$link 	= JRoute::_( 'index.php?option=com_j2store&view=orders&task=view&id='. $row->id );

				//$checked 	= JHTML::_('grid.checkedout',   $row, $i );
				$checked = JHTML::_('grid.id', $i, $row->id );

				?>

			<tr class="<?php echo "row$k"; ?>">
				<td><?php echo $this->pagination->getRowOffset( $i ); ?>
				</td>
				<td><?php echo $checked; ?>
				</td>
				<td>
					<span class="editlinktip hasTip"
						title="<?php echo JText::_( 'J2STORE_ORDER_VIEW' );?>::<?php echo $this->escape($row->order_id); ?>">
						<a href="<?php echo $link ?>"><?php echo $this->escape($row->invoice); ?></a>
					</span>
				</td>
				<?php if($this->params->get('show_unique_orderid', 0)): ?>
				<td><span class="editlinktip hasTip"
					title="<?php echo JText::_( 'J2STORE_ORDER_VIEW' );?>::<?php echo $this->escape($row->order_id); ?>">
						<a href="<?php echo $link ?>"> <?php echo $this->escape($row->order_id); ?>
					</a>
				</span>
				</td>
 				<?php endif; ?>
				<td align="center">
				<?php echo $row->billing_first_name .' '.$row->billing_last_name; ?></td>

				<td align="center"><?php if($row->bemail) {
					echo $row->bemail;
				}else {
					echo $row->user_email;
				}
				?>
				</td>

				<td align="center"><?php echo J2StorePrices::number( $row->orderpayment_amount, $row->currency_code, $row->currency_value ); ?>
				</td>
				<td align="center"><?php echo JText::_($row->orderpayment_type); ?>
				</td>
				<!--
			<td align="center">
				<?php echo JText::_($row->transaction_status); ?>
			</td>
			 -->



				<td align="center">

				<span class="label <?php echo $row->orderstatus_cssclass;?> order-state-label">
				<?php
				if(JString::strlen($row->order_state) > 0) {
					echo JText::_($row->order_state);
				} else {
					echo JText::_('J2STORE_PAYSTATUS_INCOMPLETE');
				}
				?>
				</span>

				</td>
			</tr>
			<?php
			$k = 1 - $k;
			}
			?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="com_j2store" /> <input
		type="hidden" name="view" value="orders" /> <input type="hidden"
		name="task" value="" /> <input type="hidden" name="boxchecked"
		value="0" /> <input type="hidden" name="filter_order"
		value="<?php echo $this->lists['order']; ?>" /> <input type="hidden"
		name="filter_order_Dir"
		value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>

<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');
?>
<?php if($orders):?>
<div class="j2store_latest_orders">
	<h3><?php echo JText::_('J2STORE_LATEST_ORDERS'); ?></h3>
	<table class="adminlist table table-striped table-bordered">
		<thead>
		<th><?php echo JText::_('J2STORE_DATE')?></th>
			<th><?php echo JText::_('J2STORE_INVOICE_NO')?></th>
			<th><?php echo JText::_('J2STORE_EMAIL')?></th>
			<th><?php echo JText::_('J2STORE_AMOUNT')?></th>

		</thead>
		<tbody>
			<?php foreach($orders as $order):
			if(isset($order->invoice_number) && $order->invoice_number > 0) {
				$invoice_number = $order->invoice_prefix.$order->invoice_number;
			}else {
				$invoice_number = $order->id;
			}
			$link 	= 'index.php?option=com_j2store&view=orders&task=view&id='. $order->id;
			?>
			<tr>
				<td><?php echo JHTML::_('date', $order->created_date, $params->get('date_format', JText::_('DATE_FORMAT_LC1'))); ?>
				</td>
				<td><strong><a href="<?php echo $link; ?>"><?php echo $invoice_number; ?></a></strong></td>
				<td><?php echo $order->oi_user_email; ?></td>
				<td><?php echo J2StorePrices::number( $order->orderpayment_amount, $order->currency_code, $order->currency_value ); ?>
				</td>

			</tr>
			<?php endforeach;?>
		</tbody>

	</table>


</div>
<?php endif;?>

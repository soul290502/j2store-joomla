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



defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/popup.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/select.php');

$items = $this->items;
$row = $this->row;
$form = @$this->form2;
$baseLink = $this->baseLink;
?>
<div class="j2store">
<h3>
<?php echo JText::_( "J2STORE_SRATE_SET_RATE_FOR" ); ?>:<?php echo $row->shipping_method_name; ?>
</h3>
<form action="<?php echo JRoute::_( $form['action'] )?>" method="post" id="adminForm" name="adminForm" enctype="multipart/form-data">
	<table class="adminlist table table-striped">
		<tr>
			<td align="left" width="100%"></td>
			<td nowrap="nowrap">
				<table class="adminlist">
					<thead>
						<tr>
							<th><?php echo JText::_( "J2STORE_SRATE_GEOZONES" ); ?></th>


            			<?php if($row->shipping_method_type == 1
            				|| $row->shipping_method_type == 2
            				|| $row->shipping_method_type == 4 ||
            				$row->shipping_method_type == 5 ):?>

							<th><?php echo JText::_( "J2STORE_SRATE_RANGE" ); ?></th>

						<?php endif; ?>

							<th><?php echo JText::_( "J2STORE_SFR_SHIPPING_RATE_PRICE" ); ?></th>
							<th><?php echo JText::_( "J2STORE_SRATE_HANDLING_FEE" ); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
	            	<tr>
            		<td>
                		<?php echo J2StoreSelect::geozones("", "geozone_id"); ?>
                		<input type="hidden" name="shipping_method_id" value="<?php echo $row->shipping_method_id; ?>" />
            		</td>

            		<?php if($row->shipping_method_type == 1
            				|| $row->shipping_method_type == 2
            				|| $row->shipping_method_type == 4 ||
            				$row->shipping_method_type == 5 ):?>

            				<td>
            					<input id="shipping_rate_weight_start"
								name="shipping_rate_weight_start" value="" size="5" /> <?php echo JText::_("J2STORE_SRATE_TO"); ?>
								<input id="shipping_rate_weight_end"
								name="shipping_rate_weight_end" value="" size="5" />
							</td>
						<?php endif; ?>

							<td><input id="shipping_rate_price" name="shipping_rate_price"
								value="" />
							</td>
							<td><input id="shipping_rate_handling"
								name="shipping_rate_handling" value="" />
							</td>
							<td>
								<input
								class="btn btn-primary" type="button"
								onclick="document.getElementById('shippingTask').value='createrate'; document.adminForm.submit();"
								value="<?php echo JText::_('J2STORE_SRATE_CREATE_RATE'); ?>"
								class="button" />
							</td>
            	</tr>
            	</tbody>
            	</table>
            </td>
        </tr>
    </table>
    	<div class="pull-right">
		<button class="btn btn-primary"
			onclick="document.getElementById('checkall-toggle').checked=true; j2storeCheckAll(document.adminForm); document.getElementById('shippingTask').value='saverates'; document.adminForm.submit();">
			<?php echo JText::_('J2STORE_SAVE_CHANGES'); ?>
		</button>
	</div>
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th style="width: 20px;"><input type="checkbox" id="checkall-toggle"
					name="checkall-toggle" value=""
					title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
					onclick="Joomla.checkAll(this)" />
				</th>
				<th style="text-align: center;">
                    <?php echo JText::_('J2STORE_SRATE_GEOZONES'); ?>
                </th>


            	<?php if($row->shipping_method_type == 1
            		|| $row->shipping_method_type == 2
            		|| $row->shipping_method_type == 4
            		|| $row->shipping_method_type == 5
            	):?>

				<th style="text-align: center;"><?php echo JText::_('J2STORE_SRATE_RANGE'); ?>
				</th>
				<?php endif;?>


				<th style="text-align: center;"><?php echo JText::_('J2STORE_SFR_SHIPPING_RATE_PRICE'); ?>
				</th>

				<th style="text-align: center;"><?php echo JText::_('J2STORE_SRATE_HANDLING_FEE'); ?>
				</th>
			</tr>
		</thead>
        <tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) :
        	$checked = JHTML::_('grid.id', $i, $item->shipping_rate_id);
        ?>
            <tr class='row<?php echo $k; ?>'>
				<td style="text-align: center;"> <?php echo $checked; ?></td>
                <td style="text-align: center;">
                    <?php echo J2StoreSelect::geozones($item->geozone_id, "geozone[{$item->shipping_rate_id}]"); ?>
                    <br />
                    [<a href="<?php echo $baseLink; ?>&shippingTask=deleterate&cid[]=<?php echo $item->shipping_rate_id; ?>&return=<?php echo base64_encode($baseLink."&shippingTask=setrates&sid={$row->shipping_method_id}&tmpl=component"); ?>">
						<?php echo JText::_( "J2STORE_SRATE_DELETE_RATE" ); ?>
					</a> ]
                </td>

				<?php if($row->shipping_method_type == 1
            		|| $row->shipping_method_type == 2
            		|| $row->shipping_method_type == 4
            		|| $row->shipping_method_type == 5
            	):?>
				<td style="text-align: center;"><input type="text"
					name="weight_start[<?php echo $item->shipping_rate_id; ?>]"
					value="<?php echo $item->shipping_rate_weight_start; ?>" /> <?php echo JText::_("J2STORE_SRATE_TO"); ?>
					<input type="text"
					name="weight_end[<?php echo $item->shipping_rate_id; ?>]"
					value="<?php echo $item->shipping_rate_weight_end; ?>" />
				</td>
				<?php endif; ?>

				<td style="text-align: center;"><input type="text"
					name="price[<?php echo $item->shipping_rate_id; ?>]"
					value="<?php echo $item->shipping_rate_price; ?>" />
				</td>
				<td style="text-align: center;"><input type="text"
					name="handling[<?php echo $item->shipping_rate_id; ?>]"
					value="<?php echo $item->shipping_rate_handling; ?>" />
				</td>

			</tr>
			<?php $i=$i+1; $k = (1 - $k); ?>
			<?php endforeach; ?>

			<?php if (!count(@$items)) : ?>
			<tr>
				<td colspan="10" align="center">
					<?php echo JText::_('J2STORE_NO_ITEMS_FOUND'); ?>
				</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="20">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
	</table>

	<input type="hidden" name="order_change" value="0" />
	<input type="hidden" name="sid" value="<?php echo $row->shipping_method_id; ?>" />
	<input type="hidden" name="task" id="task" value="view" />
	<input type="hidden" name="shippingTask" id="shippingTask" value="setrates" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
</div>
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
//J2StoreSubmenuHelper::addSubmenu($vName = 'shippingmethods');
?>
<div class="j2store">
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div id="j2store_shipping_method_item" class="col width-60">

		<fieldset>
			<legend>
				<?php echo JText::_('J2STORE_SHIPM_SHIPPING_DETAILS'); ?>
			</legend>

			<table class="admintable">

				<tr>
					<td width="100" align="right" class="key"><label
						for="shipping_method_name"> <?php echo JText::_( 'J2STORE_SHIPM_SHIPPING_METHOD' ); ?>:
					</label>
					</td>
					<td><input type="text" name="shipping_method_name"
						id="shipping_method_name" size="32" maxlength="250"
						value="<?php  echo $this->item->shipping_method_name;?>" />
					</td>
				</tr>

				<tr>
					<td width="100" align="right" class="key"><label
						for="shipping_method_type"> <?php echo JText::_( 'J2STORE_SHIPM_SHIPPING_TYPE' ); ?>:
					</label>
					</td>
					<td><select id="shipping_method_type" name="shipping_method_type">
							<option selected="selected" value="0">
								<?php echo JText::_('J2STORE_SHIPM_FLAT_RATE_PER_ORDER');?>
							</option>
							<option value="1">
								<?php echo JText::_('J2STORE_SHIPM_QUANTITY_BASED_PER_ORDER');?>
							</option>
							<option value="2">
								<?php echo JText::_('J2STORE_SHIPM_PRICE_BASED_PER_ORDER');?>
							</option>
					</select>
					</td>
				</tr>

				<tr>
					<td valign="top" align="right" class="key"><?php echo JText::_( 'J2STORE_SHIPM_STATE' ); ?>:
					</td>
					<td><?php echo $this->lists['published']; ?>
					</td>
				</tr>
			</table>

		</fieldset>

	</div>
	<input type="hidden" name="option" value="com_j2store" /> <input
		type="hidden" name="view" value="shippingmethods" /> <input
		type="hidden" name="cid[]"
		value="<?php echo $this->shippingmethods->id; ?>" /> <input
		type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>


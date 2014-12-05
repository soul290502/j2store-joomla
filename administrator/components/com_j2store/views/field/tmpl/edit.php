<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$action=JRoute::_('index.php?option=com_j2store&view=fields');
?>
<script>

</script>
<div class="j2store j2store-fields">
	<form name="adminForm" id="adminForm" method="post"
		class="form-validate" enctype="multipart/form-data"
		action="<?php echo $action; ?>">
	<div class="row-fluid">
		<div class="span8">

		<fieldset>
			<legend>
				<?php echo JText::_('J2STORE_ADD_CUSTOM_FIELD');?>
			</legend>

			<table class="table table-bordered">
				<tr>
					<td class="key"><label><?php echo JText::_('J2STORE_CUSTOM_FIELDS_NAME');?> </label>
					</td>
					<td><input type="text" name="data[field][field_name]" id="name"
						class="inputbox" size="40"
						value="<?php echo $this->field->field_name; ?>" />
					</td>
				</tr>

				<tr>
					<td class="key"><?php echo JText::_( 'J2STORE_CUSTOM_FIELDS_TABLE' ); ?>
					</td>
					<td><?php
						echo $this->field->field_table.'<input type="hidden" name="data[field][field_table]" value="'.$this->field->field_table.'" />';
					 ?></td>
				</tr>

				<tr class="columnname">
					<td class="key">
						<?php echo JText::_( 'J2STORE_CUSTOM_FIELDS_COLUMN' ); ?>
					</td>
					<td>
					<?php if(empty($this->field->field_id)){?>
						<input type="text" name="data[field][field_namekey]" id="namekey" class="inputbox" size="40" value="" />
					<?php }else { echo $this->field->field_namekey; } ?>
					</td>
				</tr>

				<tr>
					<td class="key">
						<?php echo JText::_( 'J2STORE_CUSTOM_FIELD_TYPE' ); ?>
					</td>
					<td>
						<?php
						if(!empty($this->field->field_type) && $this->field->field_type=='customtext'){
							$this->fieldtype->addJS();
							echo $this->field->field_type.'<input type="hidden" id="fieldtype" name="data[field][field_type]" value="'.$this->field->field_type.'" />';
						}else{
							echo $this->fieldtype->display('data[field][field_type]',@$this->field->field_type,@$this->field->field_table);
						}?>
					</td>
				</tr>

				<tr class="required">
					<td class="key">
							<?php echo JText::_( 'J2STORE_CUSTOM_FIELDS_REQUIRED' ); ?>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "data[field][field_required]" , '', $this->field->field_required); ?>
					</td>
				</tr>

				<tr class="required">
					<td class="key">
							<?php echo JText::_( 'J2STORE_CUSTOM_FIELD_ERROR' ); ?>
					</td>
					<td>
						<input type="text" id="errormessage" size="80" name="field_options[errormessage]" value="<?php echo $this->escape($this->field->field_options['errormessage']); ?>"/>
					</td>
				</tr>
				<tr class="default">
					<td class="key">
						<?php echo JText::_( 'J2STORE_CUSTOM_FIELD_DEFAULT' ); ?>
					</td>
					<td>
						<?php echo $this->fieldsClass->display($this->field,@$this->field->field_default,'data[field][field_default]',false,'',true,$this->allFields); ?>
					</td>
				</tr>

				<tr class="multivalues">
					<td class="key" valign="top">
						<?php echo JText::_( 'J2STORE_CUSTOM_FIELD_VALUES' ); ?>
					</td>
					<td>
						<table id="hikashop_field_values_table" class="hikaspanleft table table-striped table-hover"><tbody id="tablevalues">
						<tr>
							<td><?php echo JText::_('J2STORE_CUSTOM_FIELD_VALUE')?></td>
							<td><?php echo JText::_('J2STORE_CUSTOM_FIELD_TITLE'); ?></td>
							<td><?php echo JText::_('J2STORE_CUSTOM_FIELD_DISABLED'); ?></td>
						</tr>
						<?php
							if(!empty($this->field->field_value) && is_array($this->field->field_value) AND $this->field->field_type!='zone'){
								foreach($this->field->field_value as $title => $value){
									$no_selected = 'selected="selected"';
									$yes_selected = '';
									if((int)$value->disabled){
										$no_selected = '';
										$yes_selected = 'selected="selected"';
									}
						?>
												<tr>
													<td><input type="text" name="field_values[title][]" value="<?php echo $this->escape($title); ?>" /></td>
													<td><input type="text" name="field_values[value][]" value="<?php echo $this->escape($value->value); ?>" /></td>
													<td><select name="field_values[disabled][]" class="inputbox">
														<option <?php echo $no_selected; ?> value="0"><?php echo JText::_('J2STORE_NO'); ?></option>
														<option <?php echo $yes_selected; ?> value="1"><?php echo JText::_('J2STORE_YES'); ?></option>
													</select></td>
												</tr>
						<?php } }?>
						<tr>
							<td><input type="text" name="field_values[title][]" value="" /></td>
							<td><input type="text" name="field_values[value][]" value="" /></td>
							<td>
								<select name="field_values[disabled][]" class="inputbox">
									<option selected="selected" value="0"><?php echo JText::_('J2STORE_NO'); ?></option>
									<option value="1"><?php echo JText::_('J2STORE_YES'); ?></option>
								</select>
							</td>
						</tr>
						</tbody></table>
						<a onclick="addLine();return false;" href='#' title="<?php echo $this->escape(JText::_('J2STORE_CUSTOM_FIELD_ADDVALUE')); ?>"><?php echo JText::_('J2STORE_CUSTOM_FIELD_ADDVALUE'); ?></a>
					</td>
				</tr>

				<tr class="filtering">
					<td class="key">
						<?php echo JText::_( 'J2STORE_CUSTOM_FIELD_INPUT_FILTERING' ); ?>
					</td>
					<td>
						<?php
						if(!isset($this->field->field_options['filtering'])) $this->field->field_options['filtering'] = 1;
						echo JHTML::_('select.booleanlist', "field_options[filtering]" , '',$this->field->field_options['filtering']); ?>
					</td>
				</tr>

				<tr class="maxlength">
					<td class="key">
						<?php echo JText::_( 'J2STORE_CUSTOM_FIELD_MAXLENGTH' ); ?>
					</td>
					<td>
						<input type="text"  size="10" name="field_options[maxlength]" id="cols" class="inputbox" value="<?php echo (int)@$this->field->field_options['maxlength']; ?>"/>
					</td>
				</tr>

				<tr class="size">
					<td class="key">
						<?php echo JText::_( 'J2STORE_CUSTOM_FIELD_SIZE' ); ?>
					</td>
					<td>
						<input type="text" id="size" size="10" name="field_options[size]" value="<?php echo $this->escape(@$this->field->field_options['size']); ?>"/>
					</td>
				</tr>

				<tr class="rows">
					<td class="key">
						<?php echo JText::_( 'J2STORE_CUSTOM_FIELD_ROWS' ); ?>
					</td>
					<td>
						<input type="text"  size="10" name="field_options[rows]" id="rows" class="inputbox" value="<?php echo $this->escape(@$this->field->field_options['rows']); ?>"/>
					</td>
				</tr>

				<tr class="cols">
					<td class="key">
						<?php echo JText::_( 'J2STORE_CUSTOM_FIELD_COLUMNS' ); ?>
					</td>
					<td>
						<input type="text"  size="10" name="field_options[cols]" id="cols" class="inputbox" value="<?php echo $this->escape(@$this->field->field_options['cols']); ?>"/>
					</td>
				</tr>

				<tr class="zone">
					<td class="key">
						<?php echo JText::_( 'J2STORE_CUSTOM_FIELD_ZONE' ); ?>
					</td>
					<td>
						<?php echo $this->zoneType->display("field_options[zone_type]",@$this->field->field_options['zone_type'],true);?>
					</td>
				</tr>

				<tr class="format">
					<td class="key">
						<?php echo JText::_( 'J2STORE_CUSTOM_FIELD_FORMAT' ); ?>
					</td>
					<td>
						<input type="text" id="format" name="field_options[format]" value="<?php echo $this->escape(@$this->field->field_options['format']); ?>"/>
					</td>
				</tr>
				<tr class="customtext">
					<td class="key">
						<?php echo JText::_( 'J2STORE_CUSTOM_TEXT' ); ?>
					</td>
					<td>
						<textarea cols="50" rows="10" name="fieldcustomtext"><?php echo @$this->field->field_options['customtext']; ?></textarea>
					</td>
				</tr>

				<tr class="readonly">
					<td class="key">
						<?php echo JText::_( 'J2STORE_CUSTOM_FIELD_READONLY' ); ?>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "field_options[readonly]" , '',@$this->field->field_options['readonly']); ?>
					</td>
				</tr>

			</table>
		</fieldset>
		</div>
		<div class="span4">
			<fieldset>
			<legend><?php echo JText::_('J2STORE_STATUS')?></legend>
			<table class="table table-bordered">

				<tr>
					<td class="key"><label for="state"> <?php echo JText::_('J2STORE_PUBLISH');?>
					</label></td>
					<td><?php echo JHTML::_('select.booleanlist', 'published', '', $this->field->published); ?>
					</td>
				</tr>
			</table>
			</fieldset>

			<fieldset>
			<legend><?php echo JText::_('J2STORE_CUSTOM_FIELD_DISPLAY_SETTINGS')?></legend>
			<p class="muted"> <small><?php echo JText::_('J2STORE_CUSTOM_FIELD_DISPLAY_HELP');?></small></span>
			<table class="table table-bordered">

					<tr>
						<td class="key"><?php echo JText::_( 'J2STORE_STORE_REGISTER_LAYOUT_LABEL' ); ?></td>
						<td><?php echo JHTML::_('select.booleanlist', "data[field][field_display_register]" , '',@$this->field->field_display_register); ?></td>
					</tr>

					<tr>
						<td class="key"><?php echo JText::_( 'J2STORE_STORE_BILLING_LAYOUT_LABEL' ); ?></td>
						<td><?php echo JHTML::_('select.booleanlist', "data[field][field_display_billing]" , '',@$this->field->field_display_billing); ?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_( 'J2STORE_STORE_SHIPPING_LAYOUT_LABEL' ); ?></td>
						<td><?php echo JHTML::_('select.booleanlist', "data[field][field_display_shipping]" , '',@$this->field->field_display_shipping); ?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_( 'J2STORE_STORE_GUEST_LAYOUT_LABEL' ); ?></td>
						<td><?php echo JHTML::_('select.booleanlist', "data[field][field_display_guest]" , '',@$this->field->field_display_guest); ?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_( 'J2STORE_STORE_GUEST_SHIPPING_LAYOUT_LABEL' ); ?></td>
						<td><?php echo JHTML::_('select.booleanlist', "data[field][field_display_guest_shipping]" , '',@$this->field->field_display_guest_shipping); ?></td>
					</tr>

					<tr>
						<td class="key"><?php echo JText::_( 'J2STORE_STORE_PAYMENT_LAYOUT_LABEL' ); ?></td>
						<td><?php echo JHTML::_('select.booleanlist', "data[field][field_display_payment]" , '',@$this->field->field_display_payment); ?></td>
					</tr>
			</table>
			</fieldset>

			<?php if(!empty($this->field->field_id)) : ?>
			<fieldset class="adminform">
				<legend><?php echo JText::_('PREVIEW'); ?></legend>
				<table class="admintable table">
					<tr>
						<td class="key"><?php $this->fieldsClass->suffix='_preview'; echo $this->fieldsClass->getFieldName($this->field); ?></td>
						<td><?php echo $this->fieldsClass->display($this->field,$this->field->field_default, $this->field->field_namekey, false,'',true,$this->allFields); ?></td>
					</tr>
				</table>
			</fieldset>
		<?php endif; ?>

		</div> <!--  end of span -->

		</div> <!-- end of row -->

		<input type="hidden" name="field_id" value="<?php echo $this->field->field_id;?>" />
		<input type="hidden" name="option" value="com_j2store" />
		<input type="hidden" name="view" value="fields" />
		<input type="hidden" name="task" value="" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>

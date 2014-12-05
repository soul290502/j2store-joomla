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

$action = JRoute::_('index.php?option=com_j2store&view=taxprofile');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');

?>

<script>
function saveTaxRule(taxrate_id, address) {
	if(taxrate_id=='' || address==''){
		jQuery('#taxruleError').html('invalid selection');
		jQuery('.error').fadeIn();
		return false;
		}


		var data = {
			jform : {
				taxrule_id : <?php echo $this->item->id;?>,
				taxprofile_id : <?php echo $this->item->taxprofile_id;?>,
				taxrate_id : taxrate_id,
				address : address
			}
		};

		var taxrate = jQuery("#jformtaxrate_id").children("option").filter(":selected").text() ;
		var zone = jQuery("#jform_address").children("option").filter(":selected").text() ;

		jQuery.ajax({
					type : "POST",
					url : "<?php echo JURI::base();?>index.php?option=com_j2store&view=taxprofile&task=taxprofile.addTaxRule",
					data : data,
					dataType: "json",
					success : function(response) {

						if (response.error != 1) {
							var gzr_id= response.taxrule_id;
							window.parent.jQuery('#taxrate_'+gzr_id).html(taxrate);
							window.parent.jQuery('#address_'+gzr_id).html(address);

							// intialize squeeze box again for edit button to work
							window.parent.SqueezeBox.initialize({});
							window.parent.SqueezeBox.assign($$('a.modal'), {
							parse: 'rel'
							});
							window.parent.SqueezeBox.close();

						} else {
							jQuery('#taxruleError').html(response.errorMessage);
							jQuery('.error').fadeIn();
						}
					}
				});

		return false;
	}

			jQuery(document).on('click', '#CreateTaxRule', function() {
				saveTaxRule(jQuery('#jformtaxrate_id').val(),jQuery('#jformaddress').val());
			});


</script>

<div class="j2store">
<div id="taxprofile_edit">

	<fieldset>
		<legend>
			<?php echo JText::_('J2STORE_TAXPROFILE_TAXRULE'); ?>
		</legend>
		<table>
			<tr>
				<td><?php echo $this->lists['taxrate']; ?>
				</td>
				<td><?php echo $this->lists['address']; ?>
				</td>

				<td id="zoneError">
					<div class="error alert alert-danger" style="display: none;">
						<?php echo JText::_('J2STORE_ERROR'); ?>
						<i class="icon-cancel pull-right" style="align: right;"
							onclick="jQuery(this).parent().fadeOut();"> </i> <br />
						<hr />
						<div id="taxruleError"></div>
					</div>
				</td>
			</tr>

			<tr>
				<td></td>
				<td><input type="button" id="CreateTaxRule"
					value="<?php echo JText::_('JTOOLBAR_SAVE'); ?>" class="btn btn-success" />
				</td>
				<td><input type="button" onclick="window.parent.SqueezeBox.close();"
					value="<?php echo JText::_('JTOOLBAR_CANCEL'); ?>" class="btn btn-success" /></td>
			</tr>

		</table>
	</fieldset>
</div>
</div>
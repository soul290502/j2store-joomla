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
$html = $this->storeProfile->store_guest_layout;

	if(empty($html) || JString::strlen($html) < 5) {
		//we dont have a profile set in the store profile. So use the default one.
		$html = '<div class="row-fluid">
		<div class="span6">[first_name] [last_name] [email] [phone_1] [phone_2] [country_id] [zone_id] </div>
		<div class="span6">[company] [tax_number] [address_1] [address_2] [city] [zip] </div>
		</div>';
	}
	//first find all the checkout fields
	preg_match_all("^\[(.*?)\]^",$html,$checkoutFields, PREG_PATTERN_ORDER);

//print_r($this->address);
$allFields = $this->fields;
?>
  <?php foreach ($this->fields as $fieldName => $oneExtraField): ?>
						<?php
						$onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';
						//echo $this->fieldsClass->display($oneExtraField,@$this->address->$fieldName,$fieldName,false);
						if(property_exists($this->address, $fieldName)) {
						 	$html = str_replace('['.$fieldName.']',$this->fieldsClass->getFormatedDisplay($oneExtraField,$this->address->$fieldName, $fieldName,false, $options = '', $test = false, $allFields, $allValues = null),$html);
						}
						?>
  <?php endforeach; ?>

  <?php
  //check for unprocessed fields. If the user forgot to add the fields to the checkout layout in store profile, we probably have some.
  $unprocessedFields = array();
  foreach($this->fields as $fieldName => $oneExtraField) {
  	if(!in_array($fieldName, $checkoutFields[1])) {
  		$unprocessedFields[$fieldName] = $oneExtraField;
  	}
  }

    //now we have unprocessed fields. remove any other square brackets found.
  preg_match_all("^\[(.*?)\]^",$html,$removeFields, PREG_PATTERN_ORDER);
  foreach($removeFields[1] as $fieldName) {
  	$html = str_replace('['.$fieldName.']', '', $html);
  }

  ?>

  <?php echo $html; ?>

 <?php if(count($unprocessedFields)): ?>
 <div class="row-fluid">
  <div class="span12">
  <?php $uhtml = '';?>
 <?php foreach ($unprocessedFields as $fieldName => $oneExtraField): ?>
						<?php
						$onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';
						//echo $this->fieldsClass->display($oneExtraField,@$this->address->$fieldName,$fieldName,false);
						if(property_exists($this->address, $fieldName)) {
						 	$uhtml .= $this->fieldsClass->getFormatedDisplay($oneExtraField,$this->address->$fieldName, $fieldName,false, $options = '', $test = false, $allFields, $allValues = null);
						 	$uhtml .='<br />';
						}
						?>
  <?php endforeach; ?>
  <?php echo $uhtml; ?>
  </div>
</div>
<?php endif; ?>

<?php if ($this->showShipping) { ?>
<div class="span11 shipping-make-same" style="clear: both; padding-top: 15px;">
  <?php if ($this->data['shipping_address']) { ?>
  <input type="checkbox" name="shipping_address" value="1" id="shipping" checked="checked" />
  <?php } else { ?>
  <input type="checkbox" name="shipping_address" value="1" id="shipping" />
  <?php } ?>
  <label for="shipping"><?php echo JText::_('J2STORE_MAKE_SHIPPING_SAME'); ?></label>
  <br />
  <br />
</div>
<?php } ?>
<div class="buttons row-fluid">
  <div class="right span12 pull-right">
    <input type="button" value="<?php echo JText::_('J2STORE_CHECKOUT_CONTINUE'); ?>" id="button-guest" class="button btn btn-primary" />
  </div>
</div>
<input type="hidden" name="option" value="com_j2store" />
<input type="hidden" name="view" value="checkout" />
<input type="hidden" name="task" value="guest_validate" />

<script type="text/javascript"><!--
(function($) {
$('#billing-address select[name=\'country_id\']').bind('change', function() {
	if (this.value == '') return;
	$.ajax({
		url: 'index.php?option=com_j2store&view=checkout&task=getCountry&country_id=' + this.value,
		dataType: 'json',
		beforeSend: function() {
			$('#billing-address select[name=\'country_id\']').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
		},
		complete: function() {
			$('.wait').remove();
		},
		success: function(json) {
			if (json['postcode_required'] == '1') {
				$('#payment-postcode-required').show();
			} else {
				$('#payment-postcode-required').hide();
			}

			html = '<option value=""><?php echo JText::_('J2STORE_SELECT'); ?></option>';

			if (json['zone'] != '') {
				for (i = 0; i < json['zone'].length; i++) {
        			html += '<option value="' + json['zone'][i]['zone_id'] + '"';

					if (json['zone'][i]['zone_id'] == '<?php echo $this->address->zone_id; ?>') {
	      				html += ' selected="selected"';
	    			}

	    			html += '>' + json['zone'][i]['zone_name'] + '</option>';
				}
			} else {
				html += '<option value="0" selected="selected"><?php echo JText::_('J2STORE_CHECKOUT_ZONE_NONE'); ?></option>';
			}

			$('#billing-address select[name=\'zone_id\']').html(html);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});
})(j2store.jQuery);

(function($) {
	if($('#billing-address select[name=\'country_id\']').length > 0) {
		$('#billing-address select[name=\'country_id\']').trigger('change');
	}
})(j2store.jQuery);
//--></script>
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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

?>
<div id="j2store-cart-modules">
<?php if($this->params->get('show_tax_calculator', 1)): ?>
<label>
<input type="radio" name="next" value="shipping" id="shipping_estimate" />
<?php echo JText::_('J2STORE_CART_TAX_SHIPPING_CALCULATOR_HEADING'); ?>
</label>
<form action="<?php echo JRoute::_('index.php');?>" method="post" id="shipping-estimate-form" >
<div id="shipping" class="content" style="display:none;">
      <table>
        <tr>
          <td><span class="required">*</span> <?php echo JText::_('J2STORE_SELECT_A_COUNTRY'); ?></td>
          <td><?php echo $this->countryList; ?></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo JText::_('J2STORE_STATE_PROVINCE'); ?></td>
          <td><select name="zone_id">
            </select></td>
        </tr>
        <tr>
          <td><?php echo JText::_('J2STORE_POSTCODE'); ?></td>
          <td><input type="text" name="postcode" value="<?php echo $this->postcode; ?>" /></td>
        </tr>
      </table>
      <input type="button" value="<?php echo JText::_('J2STORE_CART_CALCULATE_TAX_SHIPPING'); ?>" id="button-quote" class="btn btn-primary" />
 </div>
 	<input type="hidden" name="option" value="com_j2store" />
 	<input type="hidden" name="view" value="mycart" />
 	<input type="hidden" name="task" value="estimate" />
 </form>
<?php endif; ?>
 </div>

 <script type="text/javascript"><!--
j2store.jQuery('input[name=\'next\']').bind('click', function() {
	j2store.jQuery('#j2store-cart-modules > div').hide();
	j2store.jQuery('#' + this.value).slideToggle('slow');
});
//--></script>

 
 <script type="text/javascript"><!--

 (function($) {
	 $(document).on('click', '#button-quote', function() {
		 var values = $('#shipping-estimate-form').serializeArray();
		 $.ajax({
				url:'index.php?option=com_j2store&view=mycart&task=estimate',
				type: 'post',
				data: values,
				dataType: 'json',
				beforeSend: function() {
					$('#button-quote').after('<span class="wait">&nbsp;<img src="media/j2store/images/loader.gif" alt="" /></span>');
				},
				complete: function() {
					$('.wait').remove();
				},
				success: function(json) {
					if (json['redirect']) {
						window.location.href = json['redirect'];
					}
				}
		 });	

	 });

 })(j2store.jQuery);
 	 
(function($) {
$('select[name=\'country_id\']').bind('change', function() {
	$.ajax({
		url:'index.php?option=com_j2store&view=checkout&task=getCountry&country_id=' + this.value,
		dataType: 'json',
		beforeSend: function() {
			$('select[name=\'country_id\']').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
		},
		complete: function() {
			$('.wait').remove();
		},
		success: function(json) {

			html = '<option value=""><?php echo JText::_('J2STORE_SELECT'); ?></option>';

			if (json['zone'] != '') {
				for (i = 0; i < json['zone'].length; i++) {
        			html += '<option value="' + json['zone'][i]['zone_id'] + '"';

					if (json['zone'][i]['zone_id'] == '<?php echo $this->zone_id; ?>') {
	      				html += ' selected="selected"';
	    			}

	    			html += '>' + json['zone'][i]['zone_name'] + '</option>';
				}
			} else {
				html += '<option value="0" selected="selected"><?php echo JText::_('J2STORE_CHECKOUT_ZONE_NONE'); ?></option>';
			}

			$('select[name=\'zone_id\']').html(html);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});

$('select[name=\'country_id\']').trigger('change');

})(j2store.jQuery);
//--></script>
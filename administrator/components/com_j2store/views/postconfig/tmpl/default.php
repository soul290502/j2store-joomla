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
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/strapper.php');
J2StoreStrapper::addJS();
J2StoreStrapper::addCSS();
JHtml::_('behavior.keepalive');
?>
<div id="j2store-post-config" class="j2store">
	<div class="row-fluid">
		<div class="span12">
		<h3><?php echo JText::_('J2STORE_POST_CONFIG_TITLE_STORE_INFORMATION');?></h3>
		<div class="alert alert-block alert-danger">
			<p><?php echo JText::_('J2STORE_POST_CONFIG_HELP_TEXT'); ?></p>
		</div>
		<form method="post" action="index.php" name="adminForm" id="adminForm">
			<input type="hidden" name="option" value="com_j2store" />
			<input type="hidden" name="view" value="postconfig" />
			<input type="hidden" name="task" value="save" />

			<table>
							<tr>
								<td><?php echo $this->form->getLabel('store_name'); ?>
								</td>
								<td><?php echo $this->form->getInput('store_name'); ?>
								</td>
							</tr>

							<tr>
								<td><?php echo $this->form->getLabel('store_address_1'); ?>
								</td>
								<td><?php echo $this->form->getInput('store_address_1'); ?>
								</td>
							</tr>
								<tr>
								<td><?php echo $this->form->getLabel('store_address_2'); ?>
								</td>
								<td><?php echo $this->form->getInput('store_address_2'); ?>
								</td>
							</tr>
								<tr>
								<td><?php echo $this->form->getLabel('store_city'); ?>
								</td>
								<td><?php echo $this->form->getInput('store_city'); ?>
								</td>
							</tr>
							<tr>
								<td><?php echo $this->form->getLabel('store_zip'); ?>
								</td>
								<td><?php echo $this->form->getInput('store_zip'); ?>
								</td>
							</tr>
							<tr>
								<td><?php echo $this->form->getLabel('country_id'); ?>
								</td>
								<td>
								<?php echo $this->form->getInput('country_id'); ?>
								<br />
								<div class="alert alert-info"><?php echo JText::_('J2STORE_STOREPROFILE_COUNTRY_HELP_TEXT');?></div>
								</td>

							</tr>

							<tr>
								<td><?php echo $this->form->getLabel('zone_id'); ?>
								</td>
								<td id="zoneContainer"><?php // echo $this->form->getInput('zone_id'); ?>
								<br />
								<div class="alert alert-info"><?php echo JText::_('J2STORE_STOREPROFILE_ZONE_HELP_TEXT');?></div></td>
							</tr>
							<tr>
							<td><?php echo $this->form->getLabel('config_currency'); ?>
							</td>
							<td><?php echo $this->form->getInput('config_currency'); ?>
							<br />
							<div class="alert alert-info"><?php echo JText::_('J2STORE_STORE_DEFAULT_CURRENCY_DESC');?></div>
							</td>
						</tr>
					</table>
					<div class="pull-right">
					<input type="button" id="button-save-postconfig" class="btn btn-primary btn-large" value="<?php echo JText::_('J2STORE_SAVE_AND_CONTINUE');?>" />
					<a class="btn btn-warning btn-small" href="index.php?option=com_j2store&view=storeprofiles" ><?php echo JText::_('J2STORE_TAKE_ME_TO_DASHBOARD')?></a>
					</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
  <!--
  if(typeof(j2store) == 'undefined') {
		var j2store = {};
	}
	if(typeof(j2store.jQuery) == 'undefined') {
		j2store.jQuery = jQuery.noConflict();
	}

 function getAjaxZone(field_name, field_id, country_value, default_zid) {

	 (function($) {
		var data = {
			jform : {
				country_id : country_value,
				zone_id : default_zid,
				field_name : field_name,
				field_id : field_id
			}
		};

		$.ajax({
					type : "POST",
					url : "<?php echo JURI::base();?>index.php?option=com_j2store&view=geozone&task=geozone.getZone",
					data : data,
					success : function(response) {
						$('#zoneContainer').html(response);
						if (response.error != 1) {
							$('#zoneContainer').html(response.success);
						} else {
							$('#zoneError').html(response.errorMessage);
						}
					}
				});

		return false;
	  })(j2store.jQuery);
	}


  (function($) {
  	$(document).ready(function(){
	  var zone_id =0;
	  if($('#country_id')) {
			getAjaxZone('zone_id','zone_id', $('#country_id').val(), zone_id);

			$("#country_id").bind('change load', function(){
				getAjaxZone('zone_id','zone_id', $('#country_id').val(), zone_id);
			});
		}
  });


  		$(document).on('click', '#button-save-postconfig', function() {
  			$.ajax({
  				url: 'index.php',
  				type: 'post',
  				data: $('#j2store-post-config input[type=\'text\'], #j2store-post-config input[type=\'checkbox\']:checked, #j2store-post-config input[type=\'radio\']:checked, #j2store-post-config input[type=\'hidden\'], #j2store-post-config select, #j2store-post-config textarea'),
  				dataType: 'json',
  				beforeSend: function() {
  					$('#j2store-post-config').attr('disabled', true);
  					$('#j2store-post-config').after('<span class="wait">&nbsp;<img src="media/j2store/images/loading.gif" alt="" /></span>');
  				},
  				complete: function() {
  					$('#j2store-post-config').attr('disabled', false);
  					$('.wait').remove();
  				},
  				success: function(json) {
  					$('.warning, .j2error').remove();
  					if (json['redirect']) {
  						location = json['redirect'];
  					} else if (json['error']) {

  						$.each( json['error'], function( key, value ) {
  							if (value) {
  								$('#j2store-post-config #'+key).after('<br class="j2error" /><span class="j2error">' + value + '</span>');
  							}
  						});

  					}

  				}
  			});
  		});


  })(j2store.jQuery);



</script>
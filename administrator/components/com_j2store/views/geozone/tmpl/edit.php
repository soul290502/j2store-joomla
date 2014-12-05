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

$action = JRoute::_('index.php?option=com_j2store&view=geozone');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
?>

<div class="j2store">

<form action="<?php echo JRoute::_('index.php?option=com_j2store&view=geozone'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

	<div id="geozone_edit">
		<fieldset class="fieldset">
			<legend>
				<?php echo JText::_('J2STORE_GEOZONE'); ?>
			</legend>
			<table>
				<tr>
					<td><?php echo $this->form->getLabel('geozone_name'); ?>
					</td>
					<td><?php echo $this->form->getInput('geozone_name'); ?> <?php echo $this->form->getInput('geozone_id'); ?>
					</td>
				</tr>
				<tr>
					<td><?php echo $this->form->getLabel('state'); ?>
					</td>
					<td><?php echo $this->form->getInput('state'); ?>
					</td>
				</tr>
				<tr><td colspan="2"><div class="j2storehelp alert alert-info"><?php echo JText::_('J2STORE_GEOZONE_HELP_TEXT'); ?></div></td></tr>
			</table>

		</fieldset>

		<table id="geozone_rule_table" class="table table-stripped table-bordered">
	<h4><?php echo JText::_('J2STORE_GEOZONE_COUNTRIES_AND_ZONES');?></h4>
		<thead>
			<th><?php echo JText::_('J2STORE_COUNTRY');?></th>
			<th><?php echo JText::_('J2STORE_ZONE');?></th>
			<th></th>
		</thead>
		<?php $zone_to_geo_zone_row = 0; ?>
		 <?php foreach ($this->geozonerules as $geozonerule) : ?>
          <tbody id="zone-to-geo-zone-row<?php echo $zone_to_geo_zone_row; ?>">
            <tr>
              <td class="left">
              <?php

					$option =array();
						foreach($this->countryList as $clist){
							$option [] = JHtml::_('select.option',$clist->country_id,$clist->country_name);
						}
						echo JHTML::_('select.genericlist', $option, 'zone_to_geo_zone['.$zone_to_geo_zone_row.'][country_id]',null, 'value', 'text',$geozonerule->country_id);
				?>
                </td>
                <td>
                <select name="zone_to_geo_zone[<?php echo $zone_to_geo_zone_row; ?>][zone_id]" id="zone<?php echo $zone_to_geo_zone_row; ?>">
                </select>
                </td>
                <input type="hidden" name="zone_to_geo_zone[<?php echo $zone_to_geo_zone_row; ?>][geozonerule_id]" value="<?php echo $geozonerule->id; ?>" />
              	<td class="left"><a onclick="j2storeRemoveZone(<?php echo $geozonerule->id; ?>, <?php echo $zone_to_geo_zone_row; ?>);" class="button"><?php echo JText::_('J2STORE_REMOVE'); ?></a></td>
            </tr>
          </tbody>
          <?php $zone_to_geo_zone_row++; ?>
          <?php endforeach; ?>
		<tfoot>
            <tr>
            <td colspan="2"></td>
              <td><a class="btn btn-primary" onclick="j2storeAddGeoZone();" class="button"><?php echo JText::_('J2STORE_GEOZONE_ADD_COUNTRY_OR_ZONE'); ?></a></td>
            </tr>
          </tfoot>
	</table>


	</div>
	<input type="hidden" name="option" value="com_j2store">
	<input	type="hidden" name="geozone_id"	value="<?php echo $this->item->geozone_id; ?>">
	<input type="hidden" name="task" value="">
	<input type="hidden" name="view" value="geozone"/>
		<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
<script type="text/javascript"><!--
var geozone_name;
Joomla.submitbutton=function (task){


	if(task == 'geozone.save' || task =='geozone.apply'){

		geozone_name = document.getElementById('jform_geozone_name').value;
			if(geozone_name){
				Joomla.submitform(task);
                return true;
			}else{
				alert('<?php echo JText::_('J2STORE_GEOZONE_NAME_DESC'); ?> <?php echo JText::_('J2STORE_CONF_REQUIRED');?>');
				return false;
			}
	}else if(task == 'geozone.cancel'){
			Joomla.submitform(task);
	}

};



(function($) {

	$('#zone-id').load('index.php?option=com_j2store&view=geozone&task=geozone.getZone&country_id=' + $('#country-id').attr('value') + '&zone_id=0');
})(j2store.jQuery);
//--></script>
	<?php $zone_to_geo_zone_row = 0; ?>
	<?php foreach ($this->geozonerules as $geozonerule) : ?>
	<script type="text/javascript"><!--
	(function($) {
		$('#zone<?php echo $zone_to_geo_zone_row; ?>').load('index.php?option=com_j2store&view=geozone&task=geozone.getZone&country_id=<?php echo $geozonerule->country_id; ?>&zone_id=<?php echo $geozonerule->zone_id; ?>');

		$('#zone_to_geo_zone<?php echo $zone_to_geo_zone_row;?>country_id').change(function(){

			var zone_id = $('#zone_to_geo_zone<?php echo $zone_to_geo_zone_row;?>zone_id').val();
			var field_name ='zone_to_geo_zone[<?php echo $zone_to_geo_zone_row;?>][zone_id]';
			var field_id = 'zone_to_geo_zone<?php echo $zone_to_geo_zone_row;?>zone_id';
				$.ajax({
					method:'post',
					url:'index.php?option=com_j2store&view=geozone&task=geozone.getZone&country_id='+this.value+'&zone_id='+zone_id+'field_name='+field_name+'&filed_id='+field_id,
				}).done(function(response) {
					$('#zone_to_geo_zone<?php echo $zone_to_geo_zone_row;?>zone_id').html(response);
				});
		})

	})(j2store.jQuery);
	//--></script>
	<?php $zone_to_geo_zone_row++; ?>
	<?php endforeach; ?>

<script type="text/javascript"><!--
var zone_to_geo_zone_row = <?php echo $zone_to_geo_zone_row; ?>;

function j2storeAddGeoZone() {
	(function($) {
	html  = '<tbody id="zone-to-geo-zone-row' + zone_to_geo_zone_row + '">';
	html += '<tr>';
	html += '<td class="left"><select name="zone_to_geo_zone[' + zone_to_geo_zone_row + '][country_id]" id="country' + zone_to_geo_zone_row + '" onchange="j2store.jQuery(\'#zone' + zone_to_geo_zone_row + '\').load(\'index.php?option=com_j2store&view=geozone&task=geozone.getZone&country_id=\' + this.value + \'&zone_id=0\');">';
	<?php   foreach ($this->countries as $key=>$value) { ?>
	html += '<option value="<?php echo $key; ?>"><?php  echo addslashes($value); ?></option>';
	<?php } ?>
	html += '</select></td>';
	html += '<td class="left"><select name="zone_to_geo_zone[' + zone_to_geo_zone_row + '][zone_id]" id="zone' + zone_to_geo_zone_row + '"></select>';
	html += '<input type="hidden" name="zone_to_geo_zone['+ zone_to_geo_zone_row + '][geozonerule_id]" value="" /></td>';
	html += '<td class="left"><a onclick="j2store.jQuery(\'#zone-to-geo-zone-row' + zone_to_geo_zone_row + '\').remove();" class="button"><?php echo JText::_('J2STORE_REMOVE'); ?></a></td>';
	html += '</tr>';
	html += '</tbody>';

	$('#geozone_rule_table > tfoot').before(html);

	$('#zone' + zone_to_geo_zone_row).load('index.php?option=com_j2store&view=geozones&task=geozone.getZone&country_id=' + $('#country' + zone_to_geo_zone_row).attr('value') + '&zone_id=0');

	zone_to_geo_zone_row++;
	})(j2store.jQuery);

}


function j2storeRemoveZone(geozonerule_id, zone_to_geo_zone_row) {
	(function($) {
		$('.j2storealert').remove();
		$.ajax({
			method:'post',
			url:'index.php?option=com_j2store&view=geozone&task=geozone.removeGeozoneRule',
			data:{'rule_id':geozonerule_id},
			dataType:'json'
		}).done(function(response) {
			$('#zone-to-geo-zone-row'+zone_to_geo_zone_row).remove();
			$('#geozone_rule_table').before('<div class="j2storealert alert alert-block">'+response.msg+'</div>');
		});
	})(j2store.jQuery);
}



//--></script>





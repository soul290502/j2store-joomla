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

//JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHTML::_('script','system/multiselect.js',false,true);
$action = JRoute::_('index.php?option=com_j2store&view=zones');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$listOrder == 'a.zone_id';

$saveOrder	= $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_j2store&task=zones.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'zoneList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields = $this->getSortFields();
?>

<script type="text/javascript">
	Joomla.orderTable = function() {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<div class="j2store">
<form action="<?php echo $action;?>" name="adminForm" class="adminForm" id="adminForm" method="post">

	<table class="adminlist table table-striped " >
		<tr>

			<!-- search filter -->
			<td>
			<!-- search filter -->
		  <label for="filter_search" >
		  		<?php echo JText::_('J2STORE_FILTER_SEARCH');?> </label>
				<input type="text" name="filter_search" value="<?php echo $this->state->get('filter.search'); ?>" id="search"/>
				<button class="btn btn-success" onclick="this.form.submit();"><?php echo JText::_( 'J2STORE_FILTER_GO' ); ?></button>
				<button class="btn btn-inverse" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'J2STORE_FILTER_RESET' ); ?></button>
			</td>

			<td>
			    <?php echo $this->lists['country_options']; ?>
			<!-- select for state -->
					   <select name="filter_published" class="inputbox" onchange="this.form.submit()">
							<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
							<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true);?>
						</select>
			 </td>
		</tr>
	</table>

		   <table class="adminlist table table-striped" id="zoneList">
			<thead>
			<th width="1%" class="nowrap center hidden-phone"><?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
						</th>

				<th width="1px">
				<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="id">
					<?php echo JHtml::_('grid.sort',  'J2STORE_ZONE_ID', 'a.zone_id', $listDirn, $listOrder); ?>
				</th>
				<th class="name">
					<?php echo JHtml::_('grid.sort',  'J2STORE_ZONE_NAME', 'a.zone_name', $listDirn, $listOrder); ?>
				</th>

				<th class="code">
					<?php echo JHtml::_('grid.sort',  'J2STORE_ZONE_CODE', 'a.zone_code', $listDirn, $listOrder); ?>
				</th>
				<th class="country_name">
					<?php echo JHtml::_('grid.sort',  'J2STORE_COUNTRY_NAME', 'c.country_name', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort',  'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
				</th>

			</thead>

			<tfoot>
			<tr>
				<td colspan="7">
					<?php echo $this->pagination->getListFooter(); ?>

				</td>
			</tr>
		</tfoot>

			<tbody>
				<?php
				 foreach ($this->items as $i => $item)

				  {

				  $ordering	= ($listOrder == 'a.zone_id');
				  $canChange	= 1;
				  $canCheckin=1;
				  $canEdit=1;
				 	  ?>
				<tr class="row<?php echo $i%2; ?>" sortable-group-id="1">
				<td class="order nowrap center hidden-phone"><?php if ($canChange) :
						$disableClassName = '';
						$disabledLabel	  = '';
						if (!$saveOrder) :
						$disabledLabel    = JText::_('JORDERINGDISABLED');
						$disableClassName = 'inactive tip-top';
						endif; ?> <span
							class="sortable-handler hasTooltip <?php echo $disableClassName?>"
							title="<?php echo $disabledLabel?>"> <i class="icon-menu"></i>
						</span> <input type="text" style="display: none" name="order[]"
							size="5" value="<?php echo $item->ordering;?>"
							class="width-20 text-area-order " /> <?php else : ?> <span
							class="sortable-handler inactive"> <i class="icon-menu"></i>
						</span> <?php endif; ?>
						</td>

					 <td><?php echo JHtml::_('grid.id',$i,$item->zone_id); ?> </td>
					 <td> <?php echo $item->zone_id; ?></td>
				     <td> <?php echo $this->escape($item->zone_name);?> </td>
				     <td> <?php echo $this->escape($item->zone_code );?></td>
				    <td> <?php echo $this->escape($item->country_name);?> </td>
					<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->state, $i, 'zones.', $canChange, 'cb'); ?>
				</td>

				 <?php
				  } ?>

			</tbody>
		  </table>
		  <input type="hidden" name="option" value="com_j2store" />
		  <input type="hidden" name="view" value="zones" />
		 <input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>

		</form>
</div>

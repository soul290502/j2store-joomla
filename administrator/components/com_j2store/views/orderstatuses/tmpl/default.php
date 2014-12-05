<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author   priya bose - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
$action = JRoute::_('index.php?option=com_j2store&view=storeprofiles');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$listOrder == 'a.store_id';

$saveOrder	= $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_j2store&task=storeprofiles.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'storeprofileList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

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

	<form action="index.php?option=com_j2store&view=orderstatuses" method="post"
	name="adminForm" id="adminForm">

	 	<input type="hidden" name="task" value="" />
	 	<input type="hidden" name="option" value="com_j2store" />
	 	<input type="hidden" name="view" value="orderstatuses" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php // echo JHtml::_('form.token'); ?>
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

		</tr>
	</table>

	<h3><?php echo JText::_('J2STORE_ORDERSTATUSES');?></h3>
	<table id="orderStatusList" class="adminlist table table-striped table-bordered">
			<!-- <table class="adminlist table table-striped table-bordered"> -->
			<thead>
				<th width="1px">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />

				</th>

				<th>
					<?php echo JHtml::_('grid.sort',  'J2STORE_ORDERSTATUS_ID', 'os.orderstatus_id',$listDirn, $listOrder);?>
				</th>

				<th>
					<?php echo JHtml::_('grid.sort','J2STORE_ORDERSTATUS_ORDER_NAME', 'os.orderstatus_name', $listDirn, $listOrder);?>
				</th>
				<th>
					<?php echo JText::_('J2STORE_ORDERSTATUSES_CSS_CLASS');?>
				</th>

				<th>
					<?php echo JText::_('J2STORE_CUSTOM_FIELDS_CORE');?>
				 </th>
			</thead>
			<tfoot>
			<tr>
				<td colspan="9">
					<?php  echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$i=0;
				foreach($this->items as $orderStatus):?>
			<tr class="row<?php echo $i%2 ;?>">
				<td>
				<?php if($orderStatus->orderstatus_core !=1 ):?>
					<?php echo JHtml::_('grid.id',$i,$orderStatus->orderstatus_id); ?>
				<?php endif; ?>

				</td>
				<td><?php echo $orderStatus->orderstatus_id;?></td>
				<td>
						<span class="label <?php echo $orderStatus->orderstatus_cssclass;?>"><?php echo JText::_($orderStatus->orderstatus_name);?></span>
						<a href="<?php echo JRoute::_('index.php?option=com_j2store&view=orderstatus&task=orderstatus.edit&orderstatus_id='.$orderStatus->orderstatus_id)?>" >
						[<?php echo JText::_('J2STORE_EDIT')?>]
						</a>
				</td>
				<td><?php echo $orderStatus->orderstatus_cssclass;?></td>

				<td>
				<?php if($orderStatus->orderstatus_core): ?>
					<label class="label label-success"><?php echo JText::_('J2STORE_CUSTOM_FIELDS_CORE'); ?></label>
				<?php else: ?>
				 	<label class="label label-warning"><?php echo JText::_('J2STORE_CUSTOM_FIELDS_NOT_CORE');?></label>
				 <?php endif; ?>
				 </td>
			</tr>
			<?php $i++;?>
			<?php endforeach;?>
		</tbody>
			</table>


	</form>
</div>


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
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
$db = JFactory::getDbo();
?>
<?php $state = $vars->state;

$listOrder = $state->get('filter_order');
$listDirn = $state->get('filter_order_Dir');
?>
<?php $form = $vars->form;
?>
<?php $items = $vars->list;?>
<div class="j2store">
	<div class="alert alert-block alert-info">
	<?php echo JText::_('PLG_J2STORE_REPORT_ITEMISED_EXPORT_HELP');?>
	</div>
	<form action="<?php echo $form['action'];?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<table class="adminlist table table-striped ">
			<tr>
				<td>
					<?php echo JText::_( 'J2STORE_FILTER_SEARCH' ); ?>:
					<input type="text" name="filter_search" id="search" value="<?php echo htmlspecialchars($state->get('filter_search'));?>" class="text_area" onchange="document.adminForm.submit();" />
					<button class="btn btn-success" onclick="this.form.submit();"><?php echo JText::_( 'J2STORE_FILTER_GO' ); ?></button>
					<button class="btn btn-inverse" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'J2STORE_FILTER_RESET' ); ?></button>
				</td>
				<td>
					<?php  echo $vars->pagination->getLimitBox();?>
				</td>
			</tr>
		  </table>
		  <table id="optionsList" class="adminlist table table-bordered table-striped " >
			<thead>
				<tr>
				<th>#</th>
				<th class="name">
					<?php
						echo JHtml::_('grid.sort',  'PLG_J2STORE_PRODUCT_ID', 'oi.product_id', $state->get('filter_order_Dir'), $state->get('filter_order')); ?>
				</th>

				<th class="name">
					<?php echo JHtml::_('grid.sort',  'PLG_J2STORE_PRODUCT_NAME', 'oi.orderitem_name', $state->get('filter_order_Dir'), $state->get('filter_order')); ?>
				</th>
				<th class="name">
					<?php echo JText::_('PLG_J2STORE_PRODUCT_OPTIONS');?>
				</th>
				<th class="name">
					<?php echo JHtml::_('grid.sort',  'PLG_J2STORE_CATEGORY', 'category_name', $state->get('filter_order_Dir'), $state->get('filter_order')); ?>
				</th>
				<th class="name">
					<?php echo JHtml::_('grid.sort',  'PLG_J2STORE_REPORTS_ITEMISED_QUANTITY', 'sum', $state->get('filter_order_Dir'), $state->get('filter_order')); ?>
				</th>
				<th class="id">
					<?php echo JHtml::_('grid.sort',  'PLG_J2STORE_REPORTS_ITEMISED_PURCHASES', 'count', $state->get('filter_order_Dir'), $state->get('filter_order')); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="9">
						<?php echo $vars->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php if($items) : ?>
					<?php foreach ($items as $i => $item):?>
				<tr class="row<?php echo $i%2; ?>">
			   	<td><?php echo $i+1; ?></td>
				<td><?php echo $item->product_id;?></td>
				<td>
					<?php echo $item->orderitem_name;?>
				</td>
				<td>
					<?php
						if(isset($item->orderitem_attribute_names) && $item->orderitem_attribute_names):
						$attributes =json_decode(stripslashes($item->orderitem_attribute_names));
						foreach($attributes as $attr):?>
							<small><strong><?php echo $attr->name;?> :</strong> <?php echo $attr->value?></small><br/>
						<?php endforeach;?>
					<?php endif;?>
				</td>
				<td><?php echo $db->escape($item->category_name);?> </td>
				<td> <?php  echo $db->escape($item->sum);?> </td>
				<td> <?php echo $db->escape($item->count);?> </td>
				<?php endforeach; ?>
			<?php else: ?>
				 <td colspan="9"><?php echo JText::_('J2STORE_NO_ITEMS_FOUND'); ?></td>
			<?php endif; ?>
			</tr>
			</tbody>
		  </table>
		 <input type="hidden" name="view" value="report"/>
		 <input type="hidden" name="task" value="view" />
		 <input type="hidden" name="reportTask" value="" />
		 <input type="hidden" name="id" value=" <?php echo $vars->id; ?>" />
	 	<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
		</form>
</div>
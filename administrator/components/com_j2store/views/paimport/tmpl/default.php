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


//no direct access
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/prices.php');
//this is necessary for sorting
$task='setpaimport';
$action = JRoute::_( 'index.php?option=com_j2store&view=products&task=setpaimport&tmpl=component&product_id='.$this->row->id);
?>
<h3><?php echo JText::_( "J2STORE_PAI_IMPORT_PRODUCT_OPTIONS_FOR" ); ?>:<?php echo $this->row->title; ?></h3>

	<?php if(count($this->items)):?>
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
		enctype="multipart/form-data">

		<div class="row-fluid">
			<div>
				<button class="btn btn-primary"
					onclick="document.getElementById('task').value='importattributes'; document.adminForm.submit();">
					<?php echo JText::_('J2STORE_PAI_IMPORT_PRODUCT_OPTIONS'); ?>
				</button>
			</div>
			<br />
			<div class="alert alert-block alert-info"><?php echo JText::_('J2STORE_PAI_IMPORT_PRODUCT_OPTIONS_HELP_TEXT');?></div>
			<table class="adminlist table table-striped">
				<thead>
					<tr>
						<th style="width: 20px;"><input type="checkbox"
							name="checkall-toggle" value="" onclick="Joomla.checkAll(this);" />
						</th>
						<th style="text-align: left;"><?php echo JHTML::_('grid.sort',  'J2STORE_PRODUCT_ID', 'p.id',  $this->lists['order_Dir'], $this->lists['order'], $task ); ?>
						</th>
						<th style="text-align: left;"><?php echo JHTML::_('grid.sort',  'J2STORE_PRODUCT_NAME', 'p.title',  $this->lists['order_Dir'], $this->lists['order'], $task ); ?>
						</th>
						<th style="text-align: left;"><?php echo JText::_( "J2STORE_PRODUCT_OPTION" ); ?>
						</th>

					</tr>
				</thead>
				<tbody>

					<?php $i=0; $k=0; ?>
					<?php foreach (@$this->items as $item) :
					$checked = JHTML::_('grid.id', $i, $item->id);
					$attributes = $this->model->getProductOptions($item->id);
	    ?>
					<tr class='row<?php echo $k; ?>'>
						<td style="text-align: center;"><?php
					 //echo JHTML::_('grid.checkedout',   $item, $i );
						echo $checked;
						?>
						</td>
						<td style="text-align: left;"><?php echo $item->id?>
						</td>

						<td style="text-align: left;"><?php echo $item->title; ?>
						</td>

						<td style="text-align: left;">
					 <?php if(count($attributes)) : ?>
					 		<ol>

					 	<?php foreach($attributes as $attribute) : ?>
					 		<li> <?php echo $attribute->option_name; ?> &nbsp;(<?php echo $attribute->option_unique_name; ?>)</li>

					 		<?php
					 		//now get the options for this attribute
					 		$a_options = $this->model->getProductOptionValues($attribute->product_option_id);
					 		?>
					 		<?php if(count($a_options)) : ?>
					 				<strong> <?php echo JText::_('J2STORE_PAI_IMPORT_VALUES_FOR_THIS_OPTION'); ?></strong>
					 				<ol>
					 				<?php foreach ($a_options as $a_option) : ?>
									<li>
										<span><?php echo $a_option->optionvalue_name; ?> </span>
										<span>
										<?php echo $a_option->product_optionvalue_prefix; ?>&nbsp;<?php echo J2StorePrices::number($a_option->product_optionvalue_price); ?>
										</span>
									</li>
									<?php endforeach; ?>
									</ol>
					 		<?php endif; ?>

					 	<?php endforeach; ?>
					 	</ol>
					 <?php endif; ?>
						</td>
					</tr>
					<?php $i=$i+1; $k = (1 - $k); ?>
					<?php endforeach; ?>

				</tbody>
				<tfoot>
					<tr>
						<td colspan="4"><?php echo @$this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
			</table>

			<input type="hidden" name="order_change" value="0" /> <input
				type="hidden" name="product_id" value="<?php echo $this->row->id; ?>" /> <input
				type="hidden" name="task" id="task" value="setpaimport" /> <input
				type="hidden" name="option" value="com_j2store" /> <input
				type="hidden" name="view" value="products" /> <input type="hidden"
				name="boxchecked" value="" /> <input type="hidden"
				name="filter_order" value="<?php echo $this->lists['order']; ?>" />
			<input type="hidden" name="filter_order_Dir"
				value="<?php echo $this->lists['order_Dir']; ?>" />
		</div>
	</form>
	<?php else: ?>
	<div>
		<?php echo JText::_('J2STORE_NO_ITEMS_FOUND'); ?>
	</div>
	<?php endif; ?>
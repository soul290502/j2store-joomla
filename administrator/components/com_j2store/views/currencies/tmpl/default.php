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
$action = JRoute::_('index.php?option=com_j2store&view=currencies');

?>

<div class="j2store">
<form action="<?php echo $action;?>" name="adminForm" class="adminForm" id="adminForm" method="post">

	<table class="adminlist table table-stripped" >
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
			<!-- select for state -->
			  <label for="filter_published" >
		  		<?php echo JText::_('JSTATUS');?> </label>
					   <select name="filter_published" class="inputbox" onchange="this.form.submit()">
							<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
							<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true);?>
						</select>
			 </td>
		</tr>
	</table>

		   <table class="adminlist table table-striped">
			<thead>
				<th>
				<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="name">
					<?php echo JText::_('J2STORE_CURRENCY_TITLE_LABEL'); ?>
				</th>
				<th class="">
					<?php echo JText::_('J2STORE_CURRENCY_CODE_LABEL'); ?>
				</th>
				<th class="">
					<?php echo JText::_('J2STORE_CURRENCY_FORMAT_OPTIONS'); ?>
				</th>
				<th class="">
					<?php echo JText::_('J2STORE_CURRENCY_VALUE_LABEL'); ?>
				</th>
				<th>
					<?php echo JText::_('J2STORE_CURRENCY_MODIFIED_LABEL'); ?>
				</th>
				<th>
					<?php echo JText::_('JPUBLISHED'); ?>
				</th>
			</thead>

			<tfoot>
			<tr>
				<td colspan="9">
					<?php echo $this->pagination->getListFooter(); ?>

				</td>
			</tr>
		</tfoot>

			<tbody>
				<?php
				 foreach ($this->items as $i => $item)

				  {
				 	  ?>
				<tr class="row<?php echo $i%2; ?>" sortable-group-id="1">
					 <td><?php echo JHtml::_('grid.id',$i,$item->currency_id); ?> </td>
					 <td> <?php echo $item->currency_title; ?></td>
					 <td>
					 <a href="index.php?option=com_j2store&view=currency&task=currency.edit&currency_id=<?php echo $item->currency_id;?>">
					 <strong>
					 <?php echo $item->currency_code; ?>
					 </strong>
					 </a>
					 </td>

					 <td>

					  <?php
					 $position = '';
					 if($item->currency_position == 'pre') {
						$position = JText::_('J2STORE_CURRENCY_FRONT');
						}elseif($item->currency_position == 'post') {
							$position = JText::_('J2STORE_CURRENCY_END');
						}
					 ?>
					     <dl class="dl-horizontal">
					     	<dt><?php echo JText::_('J2STORE_CURRENCY_SYMBOL_LABEL')?></dt>
						    <dd><?php echo $item->currency_symbol; ?></dd>
					     	<dt><?php echo JText::_('J2STORE_CURRENCY_NUM_DECIMALS_LABEL')?></dt>
						    <dd><?php echo $item->currency_num_decimals; ?></dd>
						    <dt><?php echo JText::_('J2STORE_CURRENCY_POSITION_LABEL')?></dt>
						    <dd><?php echo $position; ?></dd>
						    <dt><?php echo JText::_('J2STORE_CURRENCY_DECIMAL_SEPARATOR_LABEL')?></dt>
						    <dd><?php echo $item->currency_decimal; ?></dd>
						    <dt><?php echo JText::_('J2STORE_CURRENCY_THOUSANDS_LABEL')?></dt>
						    <dd><?php echo $item->currency_thousands; ?></dd>
					    </dl>
					</td>
					 <td> <?php echo $item->currency_value; ?></td>
					 <td> <?php echo $item->currency_modified; ?></td>
					<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->state, $i, 'currencies.', 1, 'cb'); ?>
					</td>

				 <?php
				  } ?>

			</tbody>
		  </table>
		  <input type="hidden" name="option" value="com_j2store" />
		  <input type="hidden" name="view" value="currencies" />
		 <input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
		</form>
</div>

<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_COMPONENT_ADMINISTRATOR.'/library/prices.php' );
JHtml::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/helpers/html');
$action = JRoute::_('index.php?option=com_j2store&view=fields');
$listOrder	= $this->lists['order'];
$listDirn	= $this->lists['order_Dir'];
$saveOrder	= $listOrder == 'a.field_id';
?>
<div class="j2store">

<div class="alert alert-info">
<?php if(J2STORE_PRO !=1): ?>
	<?php echo JText::_('J2STORE_CUSTOM_FIELDS_BASIC_VERSION_NOTICE'); ?>
	<a class="btn btn-warning" href="http://j2store.org/download.html" target="_blank">Subscribe & Upgrade</a>
<?php else: ?>
	<?php echo JText::_('J2STORE_CUSTOM_FIELDS_HELPER_TEXT'); ?>
<?php endif; ?>
</div>


<h3><?php echo JText::_('J2STORE_CUSTOM_FIELDS');?></h3>
<form action="<?php echo $action;?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<table class="table">
		<tr>
				<td align="left" width="100%">
				<?php echo JText::_( 'J2STORE_FILTER_SEARCH' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo htmlspecialchars($this->lists['search']);?>" class="text_area" onchange="document.adminForm.submit();" />
				<button class="btn btn-success" onclick="this.form.submit();"><?php echo JText::_( 'J2STORE_FILTER_GO' ); ?></button>
				<button class="btn btn-inverse" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'J2STORE_FILTER_RESET' ); ?></button>
				</td>
			</tr>
		   </table>

		  <table id="fieldsList" class="adminlist table table-striped">

			<thead>
			<tr>

				<th>#</th>
				<th width="1px">
				<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="name">
					<?php echo JHtml::_('grid.sort',  'J2STORE_CUSTOM_FIELDS_NAMEKEY', 'a.field_namekey', $this->lists['order_Dir'], $this->lists['order']); ?>
				</th>
				<th class="name">
					<?php echo JHtml::_('grid.sort',  'J2STORE_CUSTOM_FIELDS_NAME', 'a.field_name', $this->lists['order_Dir'], $this->lists['order']); ?>
				</th>
				<th class="name">
					<?php echo JHtml::_('grid.sort',  'J2STORE_CUSTOM_FIELDS_REQUIRED', 'a.field_required', $this->lists['order_Dir'], $this->lists['order']); ?>
				</th>

				<th width="5%">
					<?php echo JHtml::_('grid.sort',  'JPUBLISHED', 'a.field_published', $this->lists['order_Dir'], $this->lists['order']); ?>
				</th>
				<th class="name center">
					<?php echo JHtml::_('grid.sort',  'J2STORE_CUSTOM_FIELDS_CORE', 'a.field_core', $this->lists['order_Dir'], $this->lists['order']); ?>
				</th>

			</tr>
			</thead>

			<tfoot>
			<tr>
				<td colspan="9">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php if($this->items) : ?>
			<?php $canChange = 1; ?>
				<?php foreach ($this->items as $i => $item): ?>
				<tr class="row<?php echo $i%2; ?>">

				<td><?php echo $i+1; ?></td>

				 <td>
				 <?php
				 if(!$item->field_core){
				 	echo JHtml::_('grid.id',$i,$item->field_id);
				 } else {
				 	echo '<div style="display:none;">';
				 		echo JHtml::_('grid.id',$i,$item->field_id);
				 	echo '</div>';
				 }
				 ?>

				 </td>
				 <td>
				  <a href="index.php?option=com_j2store&view=fields&task=edit&field_id=<?php echo $item->field_id; ?>">
					  <?php echo $item->field_namekey;?>
				  </a>
				  </td>
				  <td> <strong><?php echo $this->escape($item->field_name);?></strong>  </td>

				  <td>
				  	<?php if($item->field_type != 'customtext' && $item->field_type != 'link'): ?>
				  		<?php echo JHtml::_('j2store.required', $item->field_required, $i, 1); ?>
				  	<?php else: ?>
				  		<span class="label label-warning"><?php echo JText::_('J2STORE_NA');?></span>
				  	<?php endif; ?>
				  	</td>

				  	<td>
				  		<?php echo JHtml::_('jgrid.published', $item->published, $i, '', 1, 'cb'); ?>
				  	</td>

				    <td class="center">
				    	<?php
				    	if($item->field_core) {
				    		$text = JText::_('J2STORE_CUSTOM_FIELDS_CORE');
				    	} else {
				    		$text = JText::_('J2STORE_CUSTOM_FIELDS_NOT_CORE');
				    	}

				    	?>
				    	<span style="color:#fff; padding: 5px;" class="field-core label <?php echo ($item->field_core)?'label-success':'label-warning'; ?>"><?php echo $text; ?></span>
					</td>

			</tr>
			<?php endforeach;?>
			<?php else: ?>
			<tr><td colspan="9">
				<?php echo JText::_('J2STORE_NO_ITEMS_FOUND'); ?>
				</td>
				</tr>
			<?php endif;?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="com_j2store" />
		<input type="hidden" name="view" value="fields" />
		 <input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
</form>
</div>
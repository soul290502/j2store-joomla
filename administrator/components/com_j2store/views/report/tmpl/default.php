<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/
 defined('_JEXEC') or die('Restricted access'); ?>
<?php $items = $this->items; ?>
<div class="j2store">
<h3><?php echo JText::_('J2STORE_REPORTS'); ?></h3>
<form action="<?php echo JRoute::_( 'index.php?option=com_j2store&view=report')?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<table class="adminlist table table-striped">
		<tr>
			<td align="left" width="100%"><?php echo JText::_( 'J2STORE_FILTER_SEARCH' ); ?>:
				<input type="text" name="search" id="search"
				value="<?php echo htmlspecialchars($this->lists['search']);?>"
				class="text_area" onchange="document.adminForm.submit();" />
				<button class="btn btn-success" onclick="this.form.submit();">
					<?php echo JText::_( 'J2STORE_FILTER_GO' ); ?>
				</button>
				<button class="btn btn-inverse"
					onclick="document.getElementById('search').value='';this.form.submit();">
					<?php echo JText::_( 'J2STORE_FILTER_RESET' ); ?>
				</button>
			</td>
		</tr>
	</table>

	<table class="adminlist table table-striped table-bordered" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 5px;">
                	<?php echo JText::_('J2STORE_NUM'); ?>
                </th>
                <th style="text-align: left;">
                	<?php echo JHTML::_('grid.sort',  'J2STORE_REPORT_NAME', 'tbl.name', $this->lists['order_Dir'], $this->lists['order']); ?>
                </th>
                <th style="text-align: center; width: 100px;">
                 <?php echo JHTML::_('grid.sort',  'J2STORE_STATUS_LABEL', 'tbl.enabled', $this->lists['order_Dir'], $this->lists['order'] ); ?>
                </th>
        </thead>
        <tfoot>
			<tr>
				<td colspan="4"><?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
        <tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
        <?php
        //load plugin languages
        JFactory::getLanguage()->load('plg_j2store_'.$item->element, JPATH_ADMINISTRATOR);
        ?>
            <tr class='row<?php echo $k; ?>'>
				<td align="center">
					<?php echo $i + 1; ?>
                    <div style="display: none;">
                    <input type="checkbox" onclick="isChecked(this.checked);" value="<?php echo $item->id; ?>" name="cid[]" id="cb<?php echo $i; ?>">
                    </div>
				</td>
				<td style="text-align: left;">
					<?php echo JText::_($item->name); ?>
					<a class="link" href="<?php echo $item->link; ?>">
						<?php echo JText::_('J2STORE_REPORT_VIEW_REPORT')?>
					</a>
					<br />
					<p class="muted"><?php echo JText::_('J2STORE_REPORT_VIEW_REPORT_HELP_TEXT')?></p>

                </td>
              <td style="text-align: center;">
                <?php echo JHtml::_('jgrid.published', $item->enabled, $i, '', 1, 'cb'); ?>
                </td>
			</tr>
			<?php $i=$i+1; $k = (1 - $k); ?>
			<?php endforeach; ?>

			<?php if (!count($items)) : ?>
			<tr>
				<td colspan="10" align="center">
					<?php echo JText::_('J2STORE_NO_ITEMS_FOUND'); ?>
				</td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<input type="hidden" name="order_change" value="0" />
	<input type="hidden" name="id" value="" />
	<input type="hidden" name="task" id="task" value="" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
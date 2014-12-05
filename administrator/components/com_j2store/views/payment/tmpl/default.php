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

<div class="j2store">
 <h3><?php echo JText::_('J2STORE_PAYMENT_PLUGINS'); ?></h3>
 <?php if(isset($this->warning)): ?>
 	<div class="alert alert-danger">
 		<?php echo $this->warning; ?>
 	</div>

 <?php endif; ?>
<div class="alert">
	<p><?php echo JText::_('J2STORE_PAYMENT_PLUGIN_HELP'); ?></p>
</div>


<?php
if(count($this->items)):
?>
<form action="<?php echo JRoute::_( 'index.php?option=com_j2store&view=payment')?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
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
                <th>
               	 	<?php echo JHTML::_('grid.sort',  'J2STORE_PAYMENT_PLUGIN_ID', 'tbl.id', $this->lists['order_Dir'], $this->lists['order']); ?>
                </th>
                <th style="text-align: left;">
                	<?php echo JHTML::_('grid.sort',  'J2STORE_PAYMENT_PLUGIN_NAME', 'tbl.name', $this->lists['order_Dir'], $this->lists['order']); ?>
                </th>
                <th></th>
                <th style="text-align: center; width: 100px;">
                 <?php echo JHTML::_('grid.sort',  'J2STORE_STATUS_LABEL', 'tbl.enabled', $this->lists['order_Dir'], $this->lists['order'] ); ?>
                </th>
        </thead>
        <tfoot>
			<tr>
				<td colspan="5"><?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
        <tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$this->items as $item) : ?>
        <?php
        //load plugin languages
        JFactory::getLanguage()->load($item->name, JPATH_ADMINISTRATOR, $lang = null, $reload = false);
        ?>
            <tr class='row<?php echo $k; ?>'>
				<td align="center">
					<?php echo $i + 1; ?>
                    <div style="display: none;">
                    <input type="checkbox" onclick="isChecked(this.checked);" value="<?php echo $item->id; ?>" name="cid[]" id="cb<?php echo $i; ?>">
                    </div>
				</td>
				<td style="text-align: center;">
					<a href="<?php echo $item->link_edit; ?>">
						<?php echo $item->id; ?>
					</a>
				</td>
				<td style="text-align: left;">
					<?php echo JText::_(JString::strtoupper($item->name)); ?>
					[
					<a class="link" target="_target" href="<?php echo $item->link_edit; ?>">
						<?php echo JText::_('J2STORE_PAYMENT_PLUGIN_CONTROLS')?>
					</a>
					]
                </td>

                <td>
                <?php
                $xmlfile = JPATH_SITE.'/plugins/j2store/'.$item->element.'/'.$item->element.'.xml';
                $version = '';
                if(JFile::exists($xmlfile)) {
                	$xml = JFactory::getXML($xmlfile);
                	$version=(string)$xml->version;
                }
                ?>
                <dl class="dl-horizontal">
                	<?php if($item->element != 'payment_offline' && $item->element != 'payment_sagepay') : ?>
						<?php if(isset($this->update[$item->element]['version'])
								&& ($this->update[$item->element]['version'] > $version)) : ?>
                			<dt><span class="text-error"><?php echo JText::_('J2STORE_PAYMENT_PLUGIN_NEW_VERSION')?>:</span></dt>
                			<dd>
                			<span class="text-error"><?php echo $this->update[$item->element]['version']; ?>
                			&nbsp;<a class="btn btn-danger" href="http://j2store.org/my-account/my-downloads.html" target="_blank"><?php echo JText::_('J2STORE_DOWNLOAD')?></a>
                			</dd>
                		<?php endif; ?>
                	<?php endif; ?>

                	<dt><?php echo JText::_('J2STORE_PAYMENT_PLUGIN_CURRENT_VERSION')?>:</dt>
                	<dd><?php echo $version; ?></dd>
                </dl>
			</td>

            <td style="text-align: center;">
                <?php echo JHtml::_('jgrid.published', $item->enabled, $i, '', 1, 'cb'); ?>
             </td>
			</tr>
			<?php $i=$i+1; $k = (1 - $k); ?>
			<?php endforeach; ?>

			<?php if (!count($this->items)) : ?>
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
<?php endif; ?>
</div>
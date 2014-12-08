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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

//print_r($this->files);

?>
<?php if(count(JModuleHelper::getModules('j2store-downloads-top')) > 0 ): ?>
<div class="j2store_modules">
	<?php echo J2StoreHelperModules::loadposition('j2store-downloads-top'); ?>
</div>
<?php endif; ?>

<?php if(count($this->files)):?>
<h2>
	<?php echo JText::_('J2STORE_DOWNLOADS'); ?>
</h2>
<table
	class="adminlist table table-striped table-bordered j2store_table_downloads">

	<thead>
		<th><?php echo JText::_('J2STORE_INVOICE_NO'); ?></th>
		<th><?php echo JText::_('J2STORE_PRODUCT_NAME'); ?></th>
		<th><?php echo JText::_('J2STORE_FILE_NAME'); ?></th>
		<th><?php echo JText::_('J2STORE_DOWNLOAD_LIMIT'); ?></th>
		<th><?php echo JText::_('J2STORE_DOWNLOAD_LINK'); ?></th>
	</thead>
	<?php

	foreach ($this->files as $orderfile) {

		foreach($orderfile as $file) {

		if(isset($file->invoice_number) && $file->invoice_number > 0) {
			$invoice_number = $file->invoice_prefix.$file->invoice_number;
		}else {
			$invoice_number = $file->id;
		}

			if($file->limit_count < $file->download_limit OR $file->download_limit==-1)
			{
				?>
	<tr>
		<td><?php echo $invoice_number;?></td>
		<td><?php echo $file->orderitem_name;?></td>
		<td><?php echo $file->product_file_display_name;?></td>
		<td><span id="dlimit_<?php echo $file->orderfile_id; ?>"> <?php
		if($file->download_limit>=1){
			echo $file->limit_count;
			?>
		</span> &nbsp; <?php
		echo JText::_('J2STORE_OUT_OF'); echo $file->download_limit;
		}
		else if($file->download_limit==-1){
			echo JText::_('J2STORE_UNLIMITED');
		}
		?>
		</td>
		<td><a
			href="<?php echo JRoute::_('index.php?option=com_j2store&view=downloads&task=getfile&ofile_id='.$file->orderfile_id.'&pfile_id='.$file->productfile_id.'&'. JSession::getFormToken() .'=1') ;?>">
				<?php echo JText::_('J2STORE_CLICK_TO_DOWNLOAD'); ?>
		</a>
		</td>
	</tr>
	<?php
			}// end if
		}

	} ?>
</table>
<?php else:?>
<div class="j2store_nodownloads">
	<?php echo JText::_('J2STORE_NO_DOWNLOADS');?>
</div>
<?php endif;?>

<?php if(count(JModuleHelper::getModules('j2store-downloads-bottom')) > 0 ): ?>
<div class="j2store_modules">
	<?php echo J2StoreHelperModules::loadposition('j2store-downloads-bottom'); ?>
</div>
<?php endif; ?>

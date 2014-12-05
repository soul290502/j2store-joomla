<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die;
$doc = JFactory::getDocument();
$doc->addScript(JURI::root(true).'/media/j2store/js/jqueryFileTree.js');
$doc->addStyleSheet(JURI::root(true).'/media/j2store/css/jqueryFileTree.css');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/popup.php');
$params = JComponentHelper::getParams('com_j2store');
$root = $params->get('attachmentfolderpath');
$root ='/'.trim(JPath::clean($root) , '/\\' ).'/';
JHtml::_('behavior.tooltip');
$listOrder	= $this->state->get('list.ordering');
//if(empty($listOrder))  $listOrder = 'a.ordering';
$listDirn	= $this->state->get('list.direction');
?>
<script type="text/javascript">
			(function($) {
			$(document).ready( function() {
				$('#fileTreeDemo_1').fileTree({ script: 'index.php?option=com_j2store&view=products&task=getFiles&tmpl=component' }, function(file) {
					$('#savename').val(file);
					$('#myFileModal').modal('hide');
				});
			});
			})(j2store.jQuery);
	</script>
<?php if(version_compare(JVERSION, '3.0', 'lt')):?>
<style type="text/css">
/*bootstrap modal */

.j2store-modal .modal-open .dropdown-menu {
    z-index: 2050;
}
.j2store-modal .modal-open .dropdown.open {
}
.j2store-modal .modal-open .popover {
    z-index: 2060;
}
.j2store-modal .modal-open .tooltip {
    z-index: 2080;
}

.j2store-modal .modal-backdrop {
    background-color: #000000;
    bottom: 0;
    left: 0;
    position: fixed;
    right: 0;
    top: 0;
    z-index: 1040;
}
.j2store-modal .modal-backdrop.fade {
    opacity: 0;
}
.j2store-modal .modal-backdrop, .modal-backdrop.fade.in {
    opacity: 0.8;
}
.j2store-modal .modal {
    background-clip: padding-box;
    background-color: #FFFFFF;
    border: 1px solid rgba(0, 0, 0, 0.3);
    border-radius: 6px 6px 6px 6px;
    box-shadow: 0 3px 7px rgba(0, 0, 0, 0.3);
    left: 50%;
    margin: -250px 0 0 -280px;
    overflow: auto;
    position: fixed;
    top: 50%;
    width: 560px;
    z-index: 1050;
}
.j2store-modal.fade {
    top: -25%;
    transition: opacity 0.3s linear 0s, top 0.3s ease-out 0s;
}
.j2store-modal.in {
    top: 50%;
}
.j2store-modal .modal-header {
    border-bottom: 1px solid #EEEEEE;
    padding: 9px 15px;
}
.j2store-modal .modal-header .close {
    margin-top: 2px;
}
.j2store-modal .modal-header h3 {
    line-height: 30px;
    margin: 0;
}
.j2store-modal .modal-body {
    max-height: 400px;
    overflow-y: auto;
    padding: 15px;
    text-align: left;
}
.j2store-modal .modal-form {
    margin-bottom: 0;
}
.j2store-modal .modal-footer {
    background-color: #F5F5F5;
    border-radius: 0 0 6px 6px;
    border-top: 1px solid #DDDDDD;
    box-shadow: 0 1px 0 #FFFFFF inset;
    margin-bottom: 0;
    padding: 14px 15px 15px;
    text-align: right;
}

.j2store-modal .modal-footer:before, .j2store-modal .modal-footer:after {
    content: "";
    display: table;
    line-height: 0;
}

.j2store-modal .modal-footer:after {
    clear: both;
}
.j2store-modal .modal-footer .btn + .btn {
    margin-bottom: 0;
    margin-left: 5px;
}

.j2store-modal .modal-footer .btn-group .btn + .btn {
    margin-left: -1px;
}

</style>
<?php endif; ?>

<?php if(version_compare(JVERSION, '3.0', 'ge')):?>
<div class="j2store">
<?php endif;?>
<h3>
	<?php echo JText::_( 'J2STORE_PFILE_SET_FILES_FOR' ); ?>
	:
	<?php echo $this->row->title; ?>
</h3>
<div class="myform">
<form action="<?php echo $this->_action;  ?>" method="post"
	name="adminForm" id="adminForm" enctype="multipart/form-data">

		<h4>
			<?php echo JText::_('J2STORE_PFILE_ADD_NEW_FILE'); ?>
		</h4>
		<table class="adminlist table table-striped">
			<thead>
				<tr>
					<th><?php echo JText::_( "J2STORE_PFILE_FNAME" ); ?></th>
					<th><?php echo JText::_( "J2STORE_PFILE_PURECHASE_REQUIRED" ); ?></th>
					<th><?php echo JText::_( 'J2STORE_PFILE_ENABLED' ); ?></th>
					<th><?php echo JText::_( 'J2STORE_PFILE_FILE_LOCATION' ); ?></th>
					<th><?php echo JText::_( 'J2STORE_PFILE_MAX_DL_LIMIT' ); ?></th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="text-align: center;"><input type="text" id="displayname"
						name="displayname" value="" />
					</td>
					<td><input type="radio" id="purchase_required"
						name="purchase_required" value="1"> <?php echo JText::_('J2STORE_YES'); ?></input>
						<input type="radio" id="purchase_required"
						name="purchase_required" value="0"> <?php echo JText::_('J2STORE_NO'); ?></input>
					</td>
					<td><input type="radio" id="state" name="state" value="1"> <?php echo JText::_('J2STORE_YES'); ?></input>
						<input type="radio" id="state" name="state" value="0"> <?php echo JText::_('J2STORE_NO'); ?></input>
					</td>
					<td>
					<input type="text" id="savename" name="savename" value="" />
					<button class="btn btn-primary" data-toggle="modal" data-target="#myFileModal">
						<?php echo JText::_('J2STORE_CHOOSE_FILE');?>
					</button>

					</td>
					<td><input type="text" id="download_limit" name="download_limit"
						value="-1" />
					</td>
					<td>
						<button class="btn btn-primary"
							onclick="document.getElementById('task').value='createfile'; document.adminForm.submit();">
							<?php echo JText::_('J2STORE_PFILE_CREATE'); ?>
						</button>

					</td>
				</tr>
			</tbody>
		</table>
		<div class="pull-right">
			<button class="btn btn-info"
				onclick="document.getElementById('task').value='savefiles'; document.getElementById('checkall-toggle').checked=true; j2storeCheckAll(document.adminForm); document.adminForm.submit();">
				<?php echo JText::_('J2STORE_SAVE_CHANGES'); ?>
			</button>
		</div>
		<div class="reset"></div>
		<h4>
			<?php echo JText::_('J2STORE_PFILE_CURRENT_FILES'); ?>
		</h4>
		<table class="adminlist table table-striped">
			<thead>
				<tr>
					<th width="1%"><input type="checkbox" id="checkall-toggle"
						name="checkall-toggle" value=""
						title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
						onclick="Joomla.checkAll(this)" />
					</th>
					<th width="20%"><?php echo JHtml::_('grid.sort',  'J2STORE_PFILE_FDISP_NAME', 'a.productfile_name', $listDirn, $listOrder); ?>
					</th>
					<th width="7%"><?php echo JHtml::_('grid.sort',  'J2STORE_PFILE_PURECHASE_REQUIRED', 'a.purchase_required', $listDirn, $listOrder); ?>
					</th>
					<th width="7%"><?php echo JHtml::_('grid.sort',  'J2STORE_PFILE_ENABLED', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th width="7%"><?php echo JHtml::_('grid.sort',  'J2STORE_PFILE_MAX_DL_LIMIT2', 'a.download_limit', $listDirn, $listOrder); ?>
					</th>
					<th width="5%"><?php echo JHtml::_('grid.sort',  'J2STORE_ORDERING', 'a.ordering', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="10"><?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>


			<tbody>
				<?php
				//check if items exist
				if(count($this->items) > 0):
				foreach ($this->items as $i => $item) :
				$ordering	= ($listOrder == 'a.ordering');
				$user = JFactory::getUser();
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center"><?php echo JHtml::_('grid.id', $i, $item->productfile_id); ?>
					</td>
					<td style="text-align: left;"><input type="text"
						name="product_file_display_name[<?php echo $item->productfile_id; ?>]"
						value="<?php echo $item->product_file_display_name; ?>" /><br />
						filename : <?php echo $item->product_file_save_name; ?>
					</td>
					<td style="text-align: left;"><input type="radio"
						id="product_file_purchase_required[<?php echo $item->productfile_id; ?>]"
						name="product_file_purchase_required[<?php echo $item->productfile_id; ?>]"
						value="1"
						<?php if($item->purchase_required) echo 'checked="checked"'; ?>> <?php echo JText::_('J2STORE_YES'); ?></input>
						<input type="radio"
						id="product_file_purchase_required[<?php echo $item->productfile_id; ?>]"
						name="product_file_purchase_required[<?php echo $item->productfile_id; ?>]"
						value="0"
						<?php if(!$item->purchase_required) echo 'checked="checked"'; ?>>
						<?php echo JText::_('J2STORE_NO'); ?></input>
					</td>
					<td style="text-align: left;"><input type="radio"
						id="product_file_state[<?php echo $item->productfile_id; ?>]"
						name="product_file_state[<?php echo $item->productfile_id; ?>]"
						value="1" <?php if($item->state) echo 'checked="checked"'; ?>> <?php echo JText::_('J2STORE_YES'); ?></input>
						<input type="radio"
						id="product_file_state[<?php echo $item->productfile_id; ?>]"
						name="product_file_state[<?php echo $item->productfile_id; ?>]"
						value="0" <?php if(!$item->state) echo 'checked="checked"'; ?>> <?php echo JText::_('J2STORE_NO'); ?></input>
					</td>
					<td><input type="text"
						id="product_file_download_limit[<?php echo $item->productfile_id; ?>]"
						name="product_file_download_limit[<?php echo $item->productfile_id; ?>]"
						value="<?php echo $item->download_limit; ?>" />
					</td>
					<td style="text-align: center;"><input type="text"
						name="product_file_ordering[<?php echo $item->productfile_id; ?>]"
						value="<?php echo $item->ordering; ?>" size="10" />
					</td>
					<td style="text-align: center;">[<a
						href="index.php?option=com_j2store&view=products&task=deletefile&id=<?php echo $this->product_id; ?>&cid[]=<?php echo $item->productfile_id; ?>&return=<?php echo base64_encode("index.php?option=com_j2store&view=products&task=setfiles&id={$this->product_id}&tmpl=component"); ?>">
							<?php echo JText::_( "J2STORE_PFILE_DELETE_FILE" ); ?>
					</a> ]
					</td>

				</tr>
				<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<div>
			<input type="hidden" name="id"
				value="<?php echo $this->product_id; ?>" /> <input type="hidden"
				name="product_id" value="<?php echo $this->product_id; ?>" /> <input
				type="hidden" name="option"
				value="<?php echo JRequest::getCmd('option'); ?>"> <input
				type="hidden" name="task" id="task" value="setfiles" /> <input
				type="hidden" name="boxchecked" value="0" /> <input type="hidden"
				name="filter_order" value="<?php echo $listOrder; ?>" /> <input
				type="hidden" name="filter_order_Dir"
				value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>

</form>

</div>
<div style="clear:both;"></div>

<!-- Modal -->
	<div class="j2store-modal">
					<div style="display:none" class="modal fade" id="myFileModal" tabindex="-1" role="dialog" aria-labelledby="myFileModalLabel" aria-hidden="true">
					  <div class="modal-dialog">
					    <div class="modal-content">
					      <div class="modal-header">
					        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					        <h4 class="modal-title" id="myFileModalLabel"><?php echo JText::_('J2STORE_CHOOSE_FILE'); ?></h4>
					      </div>
					      <div class="modal-body">
					        <div id="fileTreeDemo_1" class="demo"></div>
					      </div>
					      <div class="modal-footer">
					        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo JText::_('J2STORE_CLOSE')?></button>
					      </div>
					    </div><!-- /.modal-content -->
					  </div><!-- /.modal-dialog -->
					</div><!-- /.modal -->
	</div>
<?php if(version_compare(JVERSION, '3.0', 'ge')):?>
</div>
<?php endif; ?>

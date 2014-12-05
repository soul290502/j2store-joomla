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
defined('_JEXEC') or die('Restricted access');

?>
<form action="index.php" method="post" name="adminForm">

	<table cellpadding="0" cellspacing="0" border="0" style="width: 100%"
		id="k2InfoPage">
		<tr>
			<td>
				<fieldset class="adminform">
					<legend>
						<?php echo JText::_('System information');?>
					</legend>
					<table class="adminlist">
						<thead>
							<tr>
								<th><?php echo JText::_('Check'); ?></th>
								<th><?php echo JText::_('Result');?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th colspan="2">&nbsp;</th>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td><strong><?php echo JText::_('Web Server');?> </strong></td>
								<td><?php echo $this->server; ?></td>
							</tr>
							<tr>
								<td><strong><?php echo JText::_('PHP version');?> </strong></td>
								<td><?php echo $this->php_version; ?></td>
							</tr>
							<tr>
								<td><strong><?php echo JText::_('MySQL version');?> </strong></td>
								<td><?php echo $this->db_version; ?></td>
							</tr>
							<tr>
								<td><strong><?php echo JText::_('GD image library');?> </strong>
								</td>
								<td><?php if ($this->gd_check) {
									$gdinfo=gd_info(); echo $gdinfo["GD Version"];
								} else echo JText::_('Disabled'); ?>
								</td>
							</tr>
							<tr>
								<td><strong><?php echo JText::_('Multibyte string support');?> </strong>
								</td>
								<td><?php if ($this->mb_check) echo JText::_('Enabled'); else echo JText::_('Disabled'); ?>
								</td>
							</tr>
							<tr>
								<td><strong><?php echo JText::_('Upload limit');?> </strong></td>
								<td><?php echo ini_get('upload_max_filesize'); ?></td>
							</tr>
							<tr>
								<td><strong><?php echo JText::_('Memory limit');?> </strong></td>
								<td><?php echo ini_get('memory_limit'); ?></td>
							</tr>
							<tr>
								<td><strong><?php echo JText::_('Open remote files (allow_url_fopen)');?>
								</strong></td>
								<td><?php echo (ini_get('allow_url_fopen'))? JText::_('Yes'):JText::_('No'); ?>
								</td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			
			<td>
		
		</tr>
	</table>
</form>
<div class="clr"></div>


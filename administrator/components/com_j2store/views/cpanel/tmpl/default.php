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

// no direct access
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_SITE.'/components/com_j2store/helpers/modules.php');
?>
<?php if(version_compare(phpversion(), '5.3.0', 'lt')):?>
<div class="alert alert-block alert-danger">
<?php echo JText::_('J2STORE_PHP_OUTDATED_VERSION'); ?>
</div>
<?php endif; ?>

<?php if($this->params->get('show_addtocart', 0)): ?>
<div class="alert alert-block alert-info">
<strong><?php echo JText::_('J2STORE_CATALOG_MODE_ENABLED_ALERT');?></strong>
</div>
<?php endif;?>


<div class="j2store">
<div id="cpanel" class="j2storeAdminCpanel row-fluid">
	<?php if($this->params->get('show_quicktips', 1)):?>
		<div class="row-fluid">
		<div class="span12">
		<?php echo $this->loadTemplate('info'); ?>
		</div>
		</div>
	<?php endif; ?>
		<div class="row-fluid">
			<div class="span8">
				<div id="j2storeQuickIcons">
					<?php echo $this->loadTemplate('quickicons'); ?>
				 </div>
				 <div class="row-fluid">
				 	<div class="span12">
				 		<div class="row-fluid">
				 		   <!-- Statistics-->
							<div class="span5 statistics">
								<?php echo $this->loadTemplate('statistics'); ?>
							</div>
							<!-- Latest orders -->
							<div class="span7 pull-right latest_orders">
									<?php echo $this->loadTemplate('latest'); ?>
							</div>

						</div>
				</div>
			</div>
			</div>
			<div class="span4"><?php echo $this->loadTemplate('update'); ?> </div>
		</div>
</div>



</div>
<div class="clr"></div>
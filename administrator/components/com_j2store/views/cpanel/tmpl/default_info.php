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
require_once (JPATH_SITE.'/components/com_j2store/helpers/cart.php');
$store_address = J2StoreHelperCart::getStoreAddress();
?>
<div class="j2store_quicktips">
<div class="alert alert-block alert-danger">
<ol>
<li>
<strong>
<?php echo JText::_('J2STORE_MULTICURRENCY_PLUGIN_UPDATE_ALERT');?>
</strong>
</li>
<?php if(!isset($store_address->store_name)):?>
	<li><b><?php echo JText::_('J2STORE_STORE_PROFILE_NOT_SET');?></b></li>
<?php endif; ?>
<?php if(!isset($store_address->config_currency)):?>
	<li>
	<strong>
	<?php echo JText::_('J2STORE_DEFAULT_CURRENCY_NOT_SET');?>
	</strong>
	</li>
<?php endif; ?>
</ol>
</div>
<?php if(J2StoreVersion::getPreviousVersion() == '2.0.2'): ?>
<div class="alert alert-block alert-danger">
	<strong>
	<?php echo JText::_('J2STORE_MIGRATE_WARNING'); ?>
	</strong>
	<br />
	<a class="btn btn-info" href="http://www.j2store.org/blog/28-migrate-j2store-from-2-0-2-to-2-5.html" target="_blank"><?php echo JText::_('J2STORE_QUICK_TIPS_READ_MORE'); ?></a>
	<a class="btn btn-success" href="http://www.j2store.org/blog/28-migrate-j2store-from-2-0-2-to-2-5.html" target="_blank"><?php echo JText::_('J2STORE_QUICK_TIPS_WATCH_VIDEO'); ?></a>
</div>
<?php endif; ?>

<?php if(J2StoreVersion::getPreviousVersion() == '2.0.2' && J2STORE_ATTRIBUTES_MIGRATED==false) : ?>
<div class="alert alert-block alert-danger">
	<strong>
	<?php echo JText::_('J2STORE_ATTRIBUTE_MIGRATION_ALERT'); ?>
	</strong>
</div>
		<div class="alert alert-info">
			<strong>
			<?php echo JText::_('J2STORE_MIGRATE_ATTRIBUTES_CPANEL_INFO')?>
			</strong>
		</div>
<?php endif; ?>

	<h3>
		<?php echo JText::_('J2STORE_QUICK_TIPS'); ?>
	</h3>
	<ol>
		<li><?php echo JText::_('J2STORE_QUICK_TIPS_SET_UP_STORE'); ?></li>
		<li><?php echo JText::_('J2STORE_QUICK_TIPS_SET_UP_CURRENCY'); ?></li>
		<li><?php echo JText::_('J2STORE_QUICK_TIPS_SET_UP_ADMIN_EMAIL'); ?></li>
		<li><?php echo JText::_('J2STORE_QUICK_TIPS_SET_UP_PRICE_DISPLAY'); ?>
		</li>
		<li><?php echo JText::_('J2STORE_QUICK_TIPS_SET_UP_ARTICLE_OPTIONS'); ?>
		</li>
		<li><?php echo JText::_('J2STORE_QUICK_TIPS_SET_UP_PRICE_TIPS'); ?></li>
		<li><?php echo JText::_('J2STORE_QUICK_TIPS_SET_UP_DONE'); ?></li>

	</ol>
	<span class="learn_more"><a href="http://j2store.org" target="_blank"><?php echo JText::_('J2STORE_QUICK_TIPS_READ_MORE'); ?>
	</a> </span>

</div>

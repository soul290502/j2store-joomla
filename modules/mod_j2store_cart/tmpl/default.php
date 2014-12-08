<?php
/*------------------------------------------------------------------------
# mod_j2store_cart - J2 Store Cart
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/


// no direct access
defined('_JEXEC') or die('Restricted access');
$app = JFactory::getApplication();
$ajax = $app->getUserState('mod_j2store_mini_cart.isAjax');
$hide = false;
if($params->get('check_empty',0) && $list['product_count'] < 1) {
$hide = true;
}
?>

	<?php if(!$ajax): ?>
	<div id="miniJ2StoreCart">
	<?php endif; ?>
		<?php if(!$hide): ?>
			<?php if($list['product_count'] > 0): ?>
				<?php echo JText::sprintf('J2STORE_CART_TOTAL', $list['product_count'], J2StorePrices::number($list['total'])); ?>
			<?php else : ?>
					<?php echo JText::_('J2STORE_NO_ITEMS_IN_CART'); ?>
			<?php endif; ?>

			<div class="j2store-minicart-button">
			<?php if($link_type =='link'):?>
			<a class="link" href="<?php echo JRoute::_('index.php?option=com_j2store&view=mycart');?>">
			<?php echo JText::_('J2STORE_VIEW_CART');?>
			</a>
			<?php else: ?>
			<input type="button" class="btn btn-primary button" onClick="window.location='<?php echo JRoute::_('index.php?option=com_j2store&view=mycart');?>'"
			value="<?php echo JText::_('J2STORE_VIEW_CART');?>"
			/>
			<?php endif;?>
			</div>
		<?php endif; ?>
			<?php if(!$ajax):?>
		</div>
			<?php else: ?>
				<?php $app->setUserState('mod_j2store_mini_cart.isAjax', 0); ?>
			<?php endif; ?>

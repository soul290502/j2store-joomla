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
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div class="j2store">
<?php if(!$this->redirect && !isset($this->free_redirect)): ?>
<!--    ORDER SUMMARY   -->
	<div class="j2storeOrderSummary">
		<?php echo $this->orderSummary; ?>
	</div>

		<!--    PAYMENT METHOD   -->
	<h3>
		<?php echo JText::_("J2STORE_PAYMENT_METHOD"); ?>
	</h3>
	<div class="payment">
	<?php echo $this->plugin_html; ?>
	</div>

<?php elseif(isset($this->free_redirect) && JString::strlen($this->free_redirect) > 5): ?>
<div class="j2storeOrderSummary">
		<?php echo $this->orderSummary; ?>
	</div>
<form action="<?php echo JRoute::_('index.php?option=com_j2store&view=checkout&task=confirmPayment') ?>" method="post" >
<input type="submit" class="btn btn-primary" value="<?php echo JText::_('J2STORE_PLACE_ORDER'); ?>" />

<input type="hidden" name="option" value="com_j2store" />
<input type="hidden" name="view" value="checkout" />
<input type="hidden" name="task" value="confirmPayment" />
</form>
<?php else: ?>
<script type="text/javascript"><!--
location ='<?php echo $this->redirect; ?>';
//--></script>
<?php endif;?>
</div>
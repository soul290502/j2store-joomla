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
<?php
$rates = array();
foreach($vars->rates as $rate){
	$r = new JObject;
	$r->value = $rate->shipping_rate_id;
	$r->text = J2StorePrices::number($rate->shipping_rate_price);
	$rates[] = &$r;
}
?>
<div class="shipping_rates">
<?php
echo JHTML::_( 'select.radiolist', $rates, 'shipping_rate', array() );
?>
</div>
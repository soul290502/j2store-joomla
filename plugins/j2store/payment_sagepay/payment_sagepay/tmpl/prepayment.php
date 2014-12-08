<?php
/*
 * --------------------------------------------------------------------------------
   Weblogicx India  - J2 Store v 3.0 - Payment Plugin - SagePay
 * --------------------------------------------------------------------------------
 * @package		Joomla! 2.5x
 * @subpackage	J2 Store
 * @author    	Weblogicx India http://www.weblogicxindia.com
 * @copyright	Copyright (c) 2010 - 2015 Weblogicx India Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link		http://weblogicxindia.com
 * --------------------------------------------------------------------------------
*/

//no direct access
defined('_JEXEC') or die('Restricted access'); 



?>

<style type="text/css">
    #sagepay_form { width: 100%; }
    #sagepay_form td { padding: 5px; }
    #sagepay_form .field_name { font-weight: bold; }
</style>

<form action="<?php echo JRoute::_( "index.php?option=com_j2store&view=checkout" ); ?>" method="post" name="adminForm" enctype="multipart/form-data">

    <div class="note">
        <?php echo JText::_( "J2STORE_SAGEPAY_PAYMENT_STANDARD_PREPARATION_MESSAGE" ); ?>
        
        <table id="sagepay_form">            
            <tr>
                <td class="field_name"><?php echo JText::_( 'Credit Card Holder' ) ?></td>
                <td><?php echo $vars->cardholder; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'Credit Card Type' ) ?></td>
                <td><?php echo $vars->cardtype; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'Card Number' ) ?></td>
                <td>************<?php echo $vars->cardnum_last4; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'J2STORE_SAGEPAY_EXPIRATION_DATE' ) ?></td>
                <td><?php echo $vars->cardexp; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'J2STORE_SAGEPAY_CARD_CVV' ) ?></td>
                <td>****</td>
            </tr>
        </table>
    </div>

    <input type='hidden' name='cardholder' value='<?php echo @$vars->cardholder; ?>'>
    <input type='hidden' name='cardtype' value='<?php echo @$vars->cardtype; ?>'>
    <input type='hidden' name='cardnum' value='<?php echo @$vars->cardnum; ?>'>
    <input type='hidden' name='cardexp' value='<?php echo @$vars->cardexp; ?>'>
    <input type='hidden' name='cardcvv' value='<?php echo @$vars->cardcvv; ?>'>
    
     <?php if( $vars->cardstart) : ?>
     <input type='hidden' name='cardstart' value='<?php echo @$vars->cardstart; ?>'>
	<?php endif;?>
            
     <?php if( $vars->cardissue) : ?>
     <input type='hidden' name='cardissue' value='<?php echo @$vars->cardissue; ?>'>
	 <?php endif;?>
            
    <input type="submit" class="btn btn-primary button" value="<?php echo JText::_('J2STORE_SAGEPAY_CLICK_TO_COMPLETE_ORDER'); ?>" />

    <input type='hidden' name='order_id' value='<?php echo @$vars->order_id; ?>'>
    <input type='hidden' name='orderpayment_id' value='<?php echo @$vars->orderpayment_id; ?>'>
    <input type='hidden' name='orderpayment_type' value='<?php echo @$vars->orderpayment_type; ?>'>
    <input type='hidden' name='task' value='confirmPayment'>
    <input type='hidden' name='paction' value='process'>
    
    <?php echo JHTML::_( 'form.token' ); ?>
</form>
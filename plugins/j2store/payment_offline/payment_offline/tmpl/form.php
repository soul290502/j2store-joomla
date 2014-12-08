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


defined('_JEXEC') or die('Restricted access'); ?>

<?php echo JText::_($vars->onselection_text); ?>

<table class="adminlist table">
<tbody>
<?php if(!empty($vars->payment_method)): ?>
<tr>
    <td class="key" style="width: 100px; text-align: right;">
        <?php echo JText::_( "J2STORE_OFFLINE_PAYMENT_METHOD" ); ?>
    </td>
    <td>
        <?php echo $vars->payment_method; ?>
    </td>
</tr>
<?php endif; ?>
</tbody>
</table>

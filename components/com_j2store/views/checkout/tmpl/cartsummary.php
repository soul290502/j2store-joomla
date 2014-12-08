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
$state = @$this->state;
$order = @$this->order;
$items = @$this->orderitems;
$cart_edit_link = JRoute::_('index.php?option=com_j2store&view=mycart');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/prices.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/j2item.php');
$com_path = JPATH_SITE.'/components/com_content/';
require_once $com_path.'router.php';
require_once $com_path.'helpers/route.php';

?>
		  <h3><?php echo JText::_('J2STORE_CARTSUMMARY'); ?></h3>
           <table id="cart" class="adminlist table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th><?php echo JText::_( "J2STORE_CARTSUMMARY_PRODUCTS" ); ?></th>
                    <th><?php echo JText::_( "J2STORE_CARTSUMMARY_TOTAL" ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php $i=0; $k=0;?>
            <?php foreach ($items as $item) : ?>
				<?php
				$article = J2StoreItem::getArticle($item->product_id);
				$link = ContentHelperRoute::getArticleRoute($article->id, $article->catid, $article->language);
				$link = JRoute::_($link);
				?>

                <tr class="row<?php echo $k; ?>">
                    <td>
                        <a href="<?php echo $link; ?>"><?php echo $item->orderitem_name; ?></a>
                        x <?php echo $item->orderitem_quantity; ?>
                        <br/>

                        <?php if (!empty($item->orderitem_attribute_names)) : ?>
                            <?php
                            	//first convert from JSON to array

                            	$registry = new JRegistry;
                            	$registry->loadString(stripslashes($item->orderitem_attribute_names), 'JSON');
                            	$product_options = $registry->toObject();
                            ?>
                            	<?php foreach ($product_options as $option) : ?>
             				   - <small>
             				   	<?php echo $option->name; ?>: <?php echo $option->value; ?>
             				   	<?php if(isset($option->option_sku) && JString::strlen($option->option_sku) > 0):?>
             				   		(<?php echo JText::_('J2STORE_SKU'); ?> : <?php echo $option->option_sku; ?>)
             				   	<?php endif; ?>
             				   </small><br />

            				   <?php endforeach; ?>
                            <br/>
                        <?php endif; ?>

                            <?php echo JText::_( "J2STORE_ITEM_PRICE" ); ?>:
                            <?php echo J2StorePrices::number($item->orderitem_price); ?>

                    </td>
                    <td style="text-align: right;">
                        <?php echo J2StorePrices::number($item->orderitem_final_price); ?>

                    </td>
                </tr>
            <?php ++$i; $k = (1 - $k); ?>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
               	<tr class="cart_subtotal">
                    <td style="font-weight: bold;">
                        <?php echo JText::_( "J2STORE_CART_SUBTOTAL" ); ?>
                    </td>
                    <td style="text-align: right;">
                        <?php echo J2StorePrices::number($order->order_subtotal); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        <table class="table table-bordered cart-total" style="text-align: right;">
        	<!-- Shipping cost -->
        	<?php if (!empty($this->showShipping)): ?>
        	<tr>
        		<td><?php echo JText::_("J2STORE_CART_SHIPPING_AND_HANDLING"); ?></td>
        		<td><?php echo J2StorePrices::number($order->order_shipping); ?></td>
        	</tr>
        	<?php endif; ?>

        	<!-- Shipping tax -->

        	<?php if (!empty($order->order_shipping_tax)): ?>
        	<tr>
        		<td><?php echo JText::_("J2STORE_CART_SHIPPING_TAX"); ?></td>
        		<td><?php echo J2StorePrices::number($order->order_shipping_tax); ?></td>
        	</tr>
        	<?php endif; ?>

        	<!-- Surcharge -->
        	<?php if ($order->order_surcharge > 0): ?>
        	<tr>
        		<td><?php echo JText::_("J2STORE_CART_SURCHARGE"); ?></td>
        		<td><?php echo J2StorePrices::number($order->order_surcharge); ?></td>
        	</tr>
        	<?php endif; ?>


        	<!-- discount -->
        	<?php if ($order->order_discount > 0): ?>
        	<tr>
        		<td>
        		 <?php echo "(-)"; ?>
        		<?php echo JText::_("J2STORE_CART_DISCOUNT"); ?>
        		</td>
        		<td><?php echo J2StorePrices::number($order->order_discount); ?></td>
        	</tr>
        	<?php endif; ?>

        	<!-- Tax -->
        	<?php if ($order->order_tax > 0): ?>
        	<?php
        	if (!empty($this->show_tax)) {
        		$title = JText::_("J2STORE_CART_PRODUCT_TAX_INCLUDED");
        	}else {
				$title = JText::_("J2STORE_CART_PRODUCT_TAX");
        	}
        	?>
        	<tr>
        		<td>
        		<?php echo $title;  ?>:
        		<br />
        		<?php
        		if( isset($this->ordertaxes) && is_array($this->ordertaxes)) {
					$last = count($this->ordertaxes); $i= 1;
					foreach ($this->ordertaxes as $ordertax) {
						echo JText::_($ordertax->ordertax_title);
						echo ' ( '.floatval($ordertax->ordertax_percent).' % )';
						if($i != $last) echo '<br />';
						$i++;
					}
				}
				?>
        		</td>
        		<td>
        		<?php
        			if( isset($this->ordertaxes) && is_array($this->ordertaxes)) {
						echo '<br />';
						$i = 1;
						foreach ($this->ordertaxes as $ordertax) {
							echo J2StorePrices::number($ordertax->ordertax_amount);
							if($i != $last) echo '<br />';
							$i++;
						}
					} else {
						echo J2StorePrices::number($order->order_tax);
					}
				?>

        		</td>
        	</tr>
        	<?php endif; ?>
        	<tr>
                	<td style="font-weight: bold; white-space: nowrap;">
                        <?php echo JText::_( "J2STORE_CART_GRANDTOTAL" ); ?>
                    </td>
                    <td style="text-align: right;">
                        <?php echo J2StorePrices::number($order->order_total); ?>
                    </td>
                </tr>

        </table>
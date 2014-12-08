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
require_once (JPATH_SITE.'/components/com_j2store/helpers/cart.php');
class modJ2StoreCartHelper {

	public static function getItems() {

		$list = array();

		$j2params = JComponentHelper::getParams('com_j2store');

		if(J2StoreHelperCart::hasProducts()) {
			require_once(JPATH_SITE.'/components/com_j2store/models/mycart.php');
			$cart_model = new J2StoreModelMyCart();
			$totals = $cart_model->getTotals();
			$product_count = J2StoreHelperCart::countProducts();

			if($j2params->get('auto_calculate_tax', 1)) {
				$total = $totals['total'];
			} else {
				$total = $totals['total_without_tax'];
			}
			$list['total'] = $total;
			$list['product_count'] = $product_count;
			//$html = JText::sprintf('J2STORE_CART_TOTAL', $product_count, J2StorePrices::number($total));
		} else {
			$list['total'] = 0;
			$list['product_count'] = 0;
			//$html = JText::_('J2STORE_NO_ITEMS_IN_CART');
		}

		return $list;
	}
}
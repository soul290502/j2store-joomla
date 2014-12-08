<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once(JPATH_SITE.'/components/com_j2store/helpers/orders.php');

if($type=='billing') {
	$field = 'all_billing';
}elseif($type=='shipping') {
	$field = 'all_shipping';
}elseif($type=='payment') {
	$field = 'all_payment';
}
$registry = new JRegistry();

$fields = array();
if(!empty($row->$field) && JString::strlen($row->$field) > 0) {
	$registry->loadString(stripslashes($row->$field), 'JSON');
	$custom_fields = $registry->toObject();
	if(isset($custom_fields) && count($custom_fields)) {
		foreach($custom_fields as $namekey=>$field) {
			if(!property_exists($row, $type.'_'.$namekey) && !property_exists($row, 'user_'.$namekey) && $namekey !='country_id' && $namekey != 'zone_id' && $namekey != 'option' && $namekey !='task' && $namekey != 'view' ) {
				$fields[$namekey] = $field;
			}
		}

	}
}
?>

<?php if(isset($fields) && count($fields)) :?>
<?php foreach($fields as $namekey=>$field) : ?>
	<?php if(is_object($field)): ?>
		<dt><?php echo JText::_($field->label); ?>:</dt>
		<dd>
		<?php
		if(is_array($field->value)) {
			echo '<br />';
			foreach($field->value as $value) {
				echo '- '.JText::_($value).'<br/>';
			}

		}elseif(J2StoreOrdersHelper::isJson(stripslashes($field->value))) {
			$json_values = json_decode(stripslashes($field->value));

		if(is_array($json_values)) {
			foreach($json_values as $value){
				echo '- '.JText::_($value).'<br/>';
			}
		} else {
				echo JText::_($field->value);
			}

		} else {
			echo JText::_($field->value);
		}
		?>
		</dd>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
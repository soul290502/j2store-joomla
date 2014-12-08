<?php
/**
 * --------------------------------------------------------------------------------
 * Payment Plugin - Paymill
 * --------------------------------------------------------------------------------
 * @package     Joomla 2.5 -  3.x
 * @subpackage  J2 Store
 * @author      J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2014-19 J2Store . All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */

// No direct access

defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR . '/components/com_j2store/library/plugins/payment.php';
require_once JPATH_SITE . '/components/com_j2store/helpers/utilities.php';
require_once  JPATH_SITE.'/components/com_j2store/helpers/orders.php';
require_once   JPATH_SITE.'/components/com_j2store/helpers/cart.php';
jimport('joomla.application.component.helper');

/**
	* plgJ2StorePayment_paymill class.
	*
	* @category   PHP
	* @package    Paymill
	* @author     J2Store <support@j2store.org>
	* @author     J2Store <support@j2store.org>
	* @copyright  2006-2013 J2Store
	* @license    J2Store Licence
	* @link       j2store.org
	* @since      new
 * */

class plgJ2StorePayment_paymill extends J2StorePaymentPlugin
{
/**
 * @var $_element  string  Should always correspond with the plugin's filename,
 * forcing it to be unique
 * */

	public $_element = 'payment_paymill';
	private $public_key = '';
	private $private_key = '';
	public $code_arr = array();
	private $_isLog = false;
	var $_j2version = null;

/**
	* Constructs a PHP_CodeSniffer object.
	*
	* @param   string  $subject  The number of spaces each tab represents.
	* @param   string  $config   The charset of the sniffed files.
	*
	* @see process()
 * */

	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage('', JPATH_ADMINISTRATOR);
		$this->_j2version = $this->getVersion();
		$this->code_arr = array (
		'internal_server_error'       => addslashes(JText::_('INTERNAL_SERVER_ERROR')),
		'invalid_public_key'    	  => addslashes(JText::_('FEEDBACK_CONFIG_ERROR_PUBLICKEY')),
		'unknown_error'               => addslashes(JText::_('UNKNOWN_ERROR')),
		'invalid_payment_data'        => addslashes(JText::_('INVALID_PAYMENT_DATA')),
		'3ds_cancelled'               => addslashes(JText::_('3DS_CANCELLED')),
		'field_invalid_card_number'   => addslashes(JText::_('FEEDBACK_ERROR_CREDITCARD_NUMBER')),
		'field_invalid_card_exp_year' => addslashes(JText::_('FIELD_INVALID_CARD_EXP_YEAR')),
		'field_invalid_card_exp_month' => addslashes(JText::_('FIELD_INVALID_CARD_EXP_MONTH')),
		'field_invalid_card_exp'      => addslashes(JText::_('FIELD_INVALID_CARD_EXP')),
		'field_invalid_card_cvc'      => addslashes(JText::_('FEEDBACK_ERROR_CREDITCARD_CVC')),
		'field_invalid_card_holder'   => addslashes(JText::_('FEEDBACK_ERROR_CREDITCARD_HOLDER')),
		'field_invalid_amount_int'    => addslashes(JText::_('FIELD_INVALID_AMOUNT_INT')),
		'field_invalid_amount'        => addslashes(JText::_('FIELD_INVALID_AMOUNT')),
		'field_invalid_currency'      => addslashes(JText::_('FIELD_INVALID_CURRENCY')),
		'field_invalid_account_number' => addslashes(JText::_('FIELD_INVALID_AMOUNT_NUMBER')),
		'field_invalid_account_holder' => addslashes(JText::_('FIELD_INVALID_ACCOUNT_HOLDER')),
		'field_invalid_bank_code'     => addslashes(JText::_('FEEDBACK_ERROR_DIRECTDEBIT_BANKCODE'))
		);

		$mode = $this->params->get('sandbox', 0);
		if(!$mode) {
			$this->public_key = trim($this->params->get('live_public_key'));
			$this->private_key = trim($this->params->get('live_private_key'));
		} else {
			$this->public_key = trim($this->params->get('test_public_key'));
			$this->private_key = trim($this->params->get('test_private_key'));
		}
}

/**
	* set currency and amount.
	*
	* @param   array  $data  form post data.
	*
	* @return  string   HTML to display
	* @return  void
	*
	* @see process()
 * */

	public function _prePayment( $data )
	{
		$jinput = JFactory::getApplication()->input;

		// Prepare the payment form
		$vars = new JObject;


		$vars->url = JRoute::_("index.php?option=com_j2store&view=checkout");
		$vars->order_id = $data['order_id'];
		$vars->orderpayment_id = $data['orderpayment_id'];
		$vars->orderpayment_type = $this->_element;

		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
		$order = JTable::getInstance('Orders', 'Table');
		$order->load($data['orderpayment_id']);

		$currency_values= $this->getCurrency($order);
		$amount = $this->getAmount($order->orderpayment_amount, $currency_values['currency_code'], $currency_values['currency_value'], $currency_values['convert']);
		$vars->amount = $amount*100;

		$vars->currency_code =$currency_values['currency_code'];

		$vars->cardholder = $jinput->getString("cardholder");
		$vars->payment_mode = $jinput->getString('paymill_payment_mode');

		// Cerdit card
		$vars->cardnum = $jinput->getString("cardnum");
		$vars->cardmonth = $jinput->getString("month");
		$vars->cardyear = $jinput->getString("year");

		$vars->cardcvv = $jinput->getString("cardcvv");
		$vars->cardnum_last4 = substr($jinput->get("cardnum"), -4);

		// Debit card
		$vars->accnum = $jinput->getString("accnum");
		$vars->accnum_last4 = substr($jinput->getString("accnum"), -4);
		$vars->banknum = $jinput->getString("banknum");
		$vars->country = $jinput->getString("country");


		$vars->public_key = $this->public_key;
		$vars->private_key = $this->private_key;

		$vars->display_name = $this->params->get('display_name', 'PLG_J2STORE_PAYMENT_PAYMILL');
		$vars->onbeforepayment_text = $this->params->get('onbeforepayment', '');
		$vars->button_text = $this->params->get('button_text', 'J2STORE_PLACE_ORDER');
		$vars->sandbox = $this->params->get('sandbox', 0);
		// Lets check the values submitted
		$html = $this->_getLayout('prepayment', $vars);

		return $html;
	}

/**
	* Processes the payment form
	* and returns HTML to be displayed to the user
	* generally with a success/failed message
	*
	* @param   array  $data  form post data.
	*
	* @return  string   HTML to display
	* @return  void
	*
	* @see process()
 * */

	public function _postPayment( $data )
	{
		// Process the payment
		$app = JFactory::getApplication();
		$vars = new JObject();
		$paction = $app->input->getString('paction');

		switch ($paction)
		{
			case 'display':
        		$html = JText::_($this->params->get('onafterpayment', ''));
        		$html .= $this->_displayArticle();
        		break;
			case 'process':
				$result = $this->_process();
				echo json_encode($result);
				$app->close();
				break;
			default:
				$vars->message = JText::_($this->params->get('onerrorpayment', ''));
				$html = $this->_getLayout('message', $vars);
				break;
		}

		return $html;
	}

/**
	* Prepares variables and
	* Renders the form for collecting payment info
	*
	* @param   array  $data  form post data.
	*
	* @return  string   unknown_type.
	*
	* @return  void
	*
	* @see process()
 * */

	public function _renderForm($data)
	{
		$vars = new JObject();
		$vars->prepop = array();
		$vars->public_key = $this->public_key;
		$vars->onselection_text = $this->params->get('onselection', '');
		$html = $this->_getLayout('form', $vars);

		return $html;
	}

/**
	* Verifies that all the required form fields are completed
	* if any fail verification, set
	* $object->error = true
	* $object->message .= '<li>x item failed verification</li>'
	*
	* @param   array  $submitted_values  form post data.
	*
	* @return  string   unknown_type.
	*
	* @return  void
	*
	* @see process()
 * */

	public function _verifyForm($submitted_values)
	{
		$object = new JObject();
		$object->error = false;
		$object->message = '';

	if($submitted_values['paymill_payment_mode'] == 'cc') {
		foreach ($submitted_values as $key => $value)
		{
			switch ($key)
			{
				case "cardholder":
					if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
					:
					$object->error = true;
					$object->message .= "<li>" . JText::_("J2STORE_PAYMILL_VALIDATION_ENTER_CARDHOLDER_NAME") . "</li>";
					endif;
					break;
				case "cardnum":
					if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
					:
					{
						$object->error = true;
						$object->message .= "<li>" . JText::_("J2STORE_PAYMILL_VALIDATION_ENTER_CREDITCARD") . "</li>";
					}
					endif;
					break;
				case "month":
					if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
					:
					{
						$object->error = true;
						$object->message .= "<li>" . JText::_("J2STORE_PAYMILL_VALIDATION_ENTER_EXPIRY_MONTH") . "</li>";
					}
					endif;
					break;
				case "year":
					if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
					:
					{
						$object->error = true;
						$object->message .= "<li>" . JText::_("J2STORE_PAYMILL_VALIDATION_ENTER_EXPIRY_YEAR") . "</li>";
					}
					endif;
					break;
				case "cardcvv":
					if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
					:
					{
						$object->error = true;
						$object->message .= "<li>" . JText::_("J2STORE_PAYMILL_VALIDATION_ENTER_CARD_CVV") . " </li>";
					}
					endif;
					break;
					default:
					break;
			}
		}

	}else {

		foreach ($submitted_values as $key => $value)
		{
			switch ($key)
			{
				case "cardholder":
					if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
						:
						$object->error = true;
					$object->message .= "<li>" . JText::_("J2STORE_PAYMILL_MESSAGE_ACCOUNT_HOLDER_NAME_REQUIRED") . "</li>";
					endif;
					break;
				case "accnum":
					if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
						:
						$object->error = true;
						$object->message .= "<li>" . JText::_("J2STORE_PAYMILL_MESSAGE_BANK_ACCOUNT_NUMBER_REQUIRED") . "</li>";
					endif;
					break;
				case "banknum":
					if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
						:
						{
							$object->error = true;
							$object->message .= "<li>" . JText::_("J2STORE_PAYMILL_MESSAGE_BANK_CODE_REQUIRED") . "</li>";
						}
						endif;
						break;

				default:
					break;
			}
		}



	}
	return $object;
}

/**
	* Processes the payment
	* This method process only real time (simple) payments
	*
	* @return  string   unknown_type.
	*
	* @return  string
	*
	* @access protected
 * */

	public function _process()
	{
		if (! JRequest::checkToken())
		{
			return $this->_renderHtml(JText::_('J2STORE_PAYMILL_INVALID_TOKEN'));
		}

		$app = JFactory::getApplication();
		$data = $app->input->getArray($_POST);
		$json = array();
		$errors = array();

		// Get order information
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_j2store/tables');
		$order = JTable::getInstance('Orders', 'Table');
		$order->load($data['orderpayment_id']);

		// Check for exisiting things
		if (empty($order->order_id))
		{
			$json['error'] = JText::_('J2STORE_PAYMILL_INVALID_ORDER');
		}
		if(empty($data['token'])) {
			$json['error'] = JText::_('J2STORE_PAYMILL_TOKEN_MISSING');
		}

		if(!$json) {

			$currency_values= $this->getCurrency($order);
			$amount = $this->getAmount($order->orderpayment_amount, $currency_values['currency_code'], $currency_values['currency_value'], $currency_values['convert'])*100;

			try {
			require (JPath::clean(dirname(__FILE__) . "/library/autoload.php"));
			$request = new Paymill\Request($this->private_key);
			$request->setSource('J2Store');
			$transaction = new Paymill\Models\Request\Transaction();
			$transaction->setAmount($amount) // e.g. "4200" for 42.00 EUR
				->setCurrency($currency_values['currency_code'])
				->setToken($data['token'])
				->setDescription(JText::_('J2STORE_PAYMILL_ORDER_DESCRIPTION'));

				$response = $request->create($transaction);
				$paymentId = $response->getId();
				$responseCode = $response->getResponseCode();
				$raw = $request->getLastResponse();
				$rawResponse= $raw['body']['data'];
				$transaction_details = $this->_getFormattedTransactionDetails($rawResponse);

				$order->transaction_id = $paymentId;
				$order->transaction_details = $transaction_details;
				$order->transaction_status = $rawResponse['status'];

				$sendEmail = false;
						if (isset($rawResponse['error']))
						{
							$order->order_state_id = 3;
							$order->order_state = JText::_('J2STORE_DECLINED');
							$errors[] = $resp['error'];
						}
						elseif (strtolower($rawResponse['status']) == 'closed')
						{
							$order->order_state_id = 1;
							$order->order_state = JText::_('J2STORE_COMPLETED');
							$sendEmail = true;
						}
						elseif (strtolower($rawResponse['status']) == 'pending')
						{
							$order->order_state_id = 4;
							$order->order_state = JText::_('J2STORE_PENDING');
						}
						elseif (strtolower($rawResponse['status']) == 'failed')
						{
							$order->order_state_id = 3;
							$order->order_state = JText::_('J2STORE_FAILED');
						}
						else
						{
							$order->order_state_id = 3;
							$order->order_state = JText::_('J2STORE_FAILED');
							$errors[] = JText::_("J2STORE_PAYMILL_ERROR_PROCESSING_PAYMENT");
						}

						// save the orderpayment
						if(!$order->save())
						{
							$errors[] = $order->getError();
						}

			}catch(PaymillException $e){
				//Do something with the error informations below
				$e->getResponseCode();
				$e->getStatusCode();
				$errMsg = $e->getErrorMessage();
				$errors[] = $errMsg;
				$this->_log($errMsg, 'payment response error');
			}

			if (empty($errors))
			{
				// remove items from cart
				J2StoreHelperCart::removeOrderItems( $order->id );

				if($sendEmail) {
					//let us inform the user that the payment is successful
					J2StoreOrdersHelper::sendUserEmail($order->user_id, $order->order_id, $order->order_state, $order->order_state, $order->order_state_id);
				}

				$json['success']  = JText::_($this->params->get('onafterpayment', ''));
				$json['redirect'] = JRoute::_('index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type='.$this->_element.'&paction=display');
			}

			if(count($errors)) {
				$json['error'] = implode("\n", $errors);
			}

		}

		return $json;

	}


/**
	* Simple logger
	*
	* @param   string  $text  text
	* @param   string  $type  message
	*
	* @return void
	*
	* @access protected
 * */

	public function _log($text, $type = 'message')
	{
		if ($this->_isLog)
		:
		{
			$file = JPATH_ROOT . "/cache/{$this->_element}.log";
			$date = JFactory::getDate();

			$f = fopen($file, 'a');
			fwrite($f, "\n\n" . $date->toFormat('%Y-%m-%d %H:%M:%S'));
			fwrite($f, "\n" . $type . ': ' . $text);
			fclose($f);
		}
		endif;
	}

/**
 * Proceeds the simple payment
 *
 * @param   array  $data  data
 *
 * @return   object  Message  object
 *
 * @access protected
 * */

	private function _getFormattedTransactionDetails($data)
	{
		$separator = "\n";
		$formatted = array();

		foreach ($data as $key => $value)
		{
			if(is_array($value)) {
				foreach ($value as $k => $v) {
					if(is_array($v)) $v = implode(',', $v);
					$formatted[] = $k . ' = ' . $v;
				}
			}
			elseif ($key != 'view' && $key != 'layout')
			{
				$formatted[] = $key . ' = ' . $value;
			}

		}

		return count($formatted) ? implode("\n", $formatted) : '';
	}

	function getCurrency($order) {
		$results = array();
		$convert = false;
		$params = JComponentHelper::getParams('com_j2store');

		if( version_compare( $this->_j2version, '2.6.7', 'lt' ) ) {
			$currency_code = $params->get('currency_code', 'USD');
			$currency_value = 1;
		} else {
			$currency_code = $order->currency_code;
			$currency_value = $order->currency_value;
		}
		$results['currency_code'] = $currency_code;
		$results['currency_value'] = $currency_value;
		$results['convert'] = $convert;

		return $results;
	}

	function getAmount($value, $currency_code, $currency_value, $convert=false) {

		if( version_compare( $this->_j2version, '2.6.7', 'lt' ) ) {
			return J2StoreUtilities::number( $value, array( 'thousands'=>'', 'num_decimals'=>'2', 'decimal'=>'.') );
		} else {
			include_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/base.php');
			$currencyObject = J2StoreFactory::getCurrencyObject();
			$amount = $currencyObject->format($value, $currency_code, $currency_value, false);
			return $amount;
		}

	}

	function getVersion() {

		if(is_null($this->_j2version)) {
			$xmlfile = JPATH_ADMINISTRATOR.'/components/com_j2store/manifest.xml';
			$xml = JFactory::getXML($xmlfile);
			$this->_j2version=(string)$xml->version;
		}
		return $this->_j2version;
	}

}

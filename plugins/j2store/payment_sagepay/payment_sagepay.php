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

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/payment.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/base.php');
require_once (JPATH_SITE.'/components/com_j2store/helpers/utilities.php');

class plgJ2StorePayment_sagepay extends J2StorePaymentPlugin

{/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
    var $_element    = 'payment_sagepay';
    var $login_id    = '';
    var $tran_key    = '';
    var $_j2version = null;
    var $_isLog      = false;

    function plgJ2StorePayment_sagepay(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );

        $this->login_id = $this->_getParam( 'merchant_email' );
        $this->tran_key = $this->_getParam( 'enc_password' );
        $this->_j2version = $this->getVersion();
	}


    /**
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _prePayment( $data )
    {
    	$app = JFactory::getApplication();
        // prepare the payment form
        $vars = new JObject();

        //now we have everthing in the data. We need to generate some more sagepay specific things.

        //lets get vendorname

        $vars->url = JRoute::_( "index.php?option=com_j2store&view=checkout" );
        $vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
        $vars->orderpayment_type = $this->_element;

        $vars->cardholder = $app->input->getString("cardholder", '');
        $vars->cardtype = $app->input->getString("cardtype");
        $vars->cardnum = $app->input->getInt("cardnum");
        $month=$app->input->getInt("month");
        $year=$app->input->getInt("year");
        $card_exp = $month.''.$year;
        $vars->cardexp = $card_exp;

        $vars->cardcvv = $app->input->getString("cardcvv");
        $vars->cardnum_last4 = substr( $vars->cardnum, -4 );

        //lets check the values submitted

        $html = $this->_getLayout('prepayment', $vars);
        return $html;
    }

    /**
     * Processes the payment form
     * and returns HTML to be displayed to the user
     * generally with a success/failed message
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _postPayment( $data )
    {
        // Process the payment
        $vars = new JObject();

        $app =JFactory::getApplication();
        $paction = JRequest::getVar( 'paction' );

        switch ($paction)
        {
            case 'process_recurring':
                // TODO Complete this
                // $this->_processRecurringPayment();
                $app->close();
              break;
            case 'process':
                $vars->message = $this->_process();
                $html = $this->_getLayout('message', $vars);
                 $html .= $this->_displayArticle();
              break;
            default:
                $vars->message = JText::_( 'J2STORE_SAGEPAY_MESSAGE_INVALID_ACTION' );
                $html = $this->_getLayout('message', $vars);
              break;
        }

        return $html;
    }

    /**
     * Prepares variables and
     * Renders the form for collecting payment info
     *
     * @return unknown_type
     */
    function _renderForm( $data )
    {
        $vars = new JObject();
        $vars->prepop = array();
        $vars->cctype_input   = $this->_cardTypesField();

        $html = $this->_getLayout('form', $vars);

        return $html;
    }

    /**
     * Verifies that all the required form fields are completed
     * if any fail verification, set
     * $object->error = true
     * $object->message .= '<li>x item failed verification</li>'
     *
     * @param $submitted_values     array   post data
     * @return unknown_type
     */
    function _verifyForm( $submitted_values )
    {
        $object = new JObject();
        $object->error = false;
        $object->message = '';
        $user = JFactory::getUser();


        foreach ($submitted_values as $key=>$value)
        {
            switch ($key)
            {
                case "cardholder":
                    if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_( "J2STORE_SAGEPAY_MESSAGE_CARD_HOLDER_NAME_REQUIRED" )."</li>";
                    }
                  break;
               case "cardtype":
                    if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_( "J2STORE_SAGEPAY_MESSAGE_CARD_TYPE_INVALID" )."</li>";
                    }
                  break;
                case "cardnum":
                    if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_( "J2STORE_SAGEPAY_MESSAGE_CARD_NUMBER_INVALID" )."</li>";
                    }
                  break;
                 case "month":
                    if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_( "J2STORE_SAGEPAY_MESSAGE_CARD_EXPIRATION_DATE_INVALID" )."</li>";
                    }
                  break;
                case "year":
                    if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_( "J2STORE_SAGEPAY_MESSAGE_CARD_EXPIRATION_DATE_INVALID" )."</li>";
                    }
                  break;
                case "cardcvv":
                    if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
                    {
                        $object->error = true;
                        $object->message .= "<li>".JText::_( "J2STORE_SAGEPAY_MESSAGE_CARD_CVV_INVALID" )."</li>";
                    }
                  break;
                default:
                  break;
            }
        }

        return $object;
    }

    /**
     * Generates a dropdown list of valid CC types
     * @param $fieldname
     * @param $default
     * @param $options
     * @return unknown_type
     * <option value="VISA">VISA Credit</option>
						<option value="DELTA">VISA Debit</option>
						<option value="UKE">VISA Electron</option>
						<option value="MC">MasterCard</option>
						<option value="MAESTRO">Maestro</option>
						<option value="AMEX">American Express</option>
						<option value="DC">Diner's Club</option>
						<option value="JCB">JCB Card</option>
						<option value="LASER">Laser</option>
						<option value="SOLO">Solo</option>
						<option value="PAYPAL">PayPal</option>
						</select>
     *
     *
     */
    function _cardTypesField( $field='cardtype', $default='', $options='' )
    {
        $types = array();
        $types[] = JHTML::_('select.option', 'VISA', JText::_( "J2STORE_SAGEPAY_VISA" ) );
        $types[] = JHTML::_('select.option', 'MC', JText::_( "J2STORE_SAGEPAY_MASTERCARD" ) );
        $types[] = JHTML::_('select.option', 'MAESTRO', JText::_( "J2STORE_SAGEPAY_MAESTRO" ) );
        $types[] = JHTML::_('select.option', 'AMEX', JText::_( "J2STORE_SAGEPAY_AMERICANEXPRESS" ) );
        $types[] = JHTML::_('select.option', 'DC', JText::_( "J2STORE_SAGEPAY_DINERSCLUB" ) );
        $types[] = JHTML::_('select.option', 'JCB', JText::_( "J2STORE_SAGEPAY_JCB" ) );
        $types[] = JHTML::_('select.option', 'LASER', JText::_( "J2STORE_SAGEPAY_LASER" ) );
        $types[] = JHTML::_('select.option', 'SOLO', JText::_( "J2STORE_SAGEPAY_SOLO" ) );

        $return = JHTML::_('select.genericlist', $types, $field, $options, 'value','text', $default);
        return $return;
    }

    /**
     * Formats the value of the card expiration date
     *
     * @param string $format
     * @param $value
     * @return string|boolean date string or false
     * @access protected
     */
    function _getFormattedCardExprDate($format, $value)
    {
        // we assume we received a $value in the format MMYY
        $month = substr($value, 0, 2);
        $year = substr($value, 2);

        if (strlen($value) != 4 || empty($month) || empty($year) || strlen($year) != 2) {
            return false;
        }

        $date = date($format, mktime(0, 0, 0, $month, 1, $year));
        return $date;
    }

    /**
     * Gets the gateway URL
     *
     * @param string $type Simple or subscription
     * @return string
     * @access protected
     */
    function _getActionUrl($type = 'simple')
    {
        if ($type == 'simple')
        {
            $url  = $this->params->get('sandbox') ? 'https://test.sagepay.com/gateway/service/vspdirect-register.vsp' : 'https://live.sagepay.com/gateway/service/vspdirect-register.vsp';
        }
            else
        {
            // recurring billing url
            $url  = $this->params->get('sandbox') ? 'https://test.sagepay.com/gateway/service/vspdirect-register.vsp' : 'https://live.sagepay.com/gateway/service/vspdirect-register.vsp';
        }

        return $url;
    }

    /**
     * Gets a value of the plugin parameter
     *
     * @param string $name
     * @param string $default
     * @return string
     * @access protected
     */
    function _getParam($name, $default = '')
    {
        $sandbox_param = "sandbox_$name";
        $sb_value = $this->params->get($sandbox_param);

        if ($this->params->get('sandbox') && !empty($sb_value)) {
            $param = $this->params->get($sandbox_param, $default);
        }
        else {
            $param = $this->params->get($name, $default);
        }

        return $param;
    }


    /**
     * Processes the payment
     *
     * This method process only real time (simple) payments
     *
     * @return string
     * @access protected
     */
    function _process()
    {
        /*
         * perform initial checks
         */
        if ( ! JRequest::checkToken() ) {
            return $this->_renderHtml( JText::_( 'J2STORE_SAGEPAY_INVALID_TOKEN' ) );
        }
		$app = JFactory::getApplication();
        $data = $app->input->getArray($_POST);

        // get order information
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/tables' );
        $order = JTable::getInstance('Orders', 'Table');
        $order->load( $data['orderpayment_id'] );

        //check for exisiting things
     if ( empty($order->order_id) ) {
            return JText::_( 'J2STORE_SAGEPAY_INVALID_ORDER' );
        }

        if ( empty($this->login_id)) {
            return JText::_( 'J2STORE_SAGEPAY_MESSAGE_MISSING_LOGIN_ID' );
        }
        if ( empty($this->tran_key)) {
            return JText::_( 'J2STORE_SAGEPAY_MESSAGE_MISSING_TRANSACTION_KEY' );
        }

        // prepare the form for submission to sage pay
        $process_vars = $this->_getProcessVars($data, $order);

        return $this->_processSimplePayment($process_vars);

    }

    /**
     * Prepares parameters for the payment processing
     *
     * @param object $data Post variables
     * @param string $auth_net_login_id
     * @param string $auth_net_tran_key
     * @return array
     * @access protected
     */
    function _getProcessVars($data, $order)
    {

		// joomla info
        $user =JFactory::getUser();
        $j2store_params = JComponentHelper::getParams('com_j2store');
        $sage_userid                = $user->id;
        $sage_card_holder              = $data['cardholder'];
        $sage_card_num              = str_replace(" ", "", str_replace("-", "", $data['cardnum'] ) );

        //get start date
     //   if($data['cartstart']) {                                                                         // "5424000000000015";
	//		$sage_card_start_date              = $this->_getFormattedCardExprDate('my', $data['cardstart'] ); // "1209";
	//	}

        $sage_card_exp_date              = $this->_getFormattedCardExprDate('my', $data['cardexp'] ); // "1209";
        $sage_cvv                   = $data['cardcvv']; //"";
        $sage_card_type                   = $data['cardtype']; //"";

        // order info
        $orderinfo = $this->_getOrderInfo($order->id);


        if(!empty($orderinfo->billing_first_name)) {
        	$sage_fname                 = $orderinfo->billing_first_name; //"Charles D.";
        	$sage_lname                 = $orderinfo->billing_last_name; // "Gaulle";
        	$sage_address1               = $orderinfo->billing_address_1;
        	$sage_address2 				=$orderinfo->billing_address_2;
        	//"342 N. Main Street #150";
        	$sage_city                  = $orderinfo->billing_city; //"Ft. Worth";
        	$sage_state                 = $orderinfo->billing_zone_name; //"TX";
        	$sage_zip                   = $orderinfo->billing_zip; //"12345";
        	$sage_country               = $this->_getCountryCode($orderinfo->billing_country_id)->country_isocode_2; // "US";
        	$sage_phone					= (!empty($orderinfo->billing_phone_1))?$orderinfo->billing_phone_1:$orderinfo->billing_phone_2;
        } else
        	//use shipping address
        {
        	$sage_fname                 = $orderinfo->shipping_first_name; //"Charles D.";
        	$sage_lname                 = $orderinfo->shipping_last_name; // "Gaulle";
        	$sage_address1               = $orderinfo->shipping_address_1;
        	$sage_address2               = $orderinfo->shipping_address_2;
        	//"342 N. Main Street #150";
        	$sage_city                  = $orderinfo->shipping_city; //"Ft. Worth";
        	$sage_state                 = $orderinfo->shipping_zone_name; //"TX";
        	$sage_zip                   = $orderinfo->shipping_zip; //"12345";
        	$sage_country               = $this->_getCountryCode($orderinfo->shipping_country_id)->country_isocode_2; // "US";
        	$sage_phone					= (!empty($orderinfo->shipping_phone_1))?$orderinfo->shipping_phone_1:$orderinfo->shipping_phone_2;
        }

        $sage_useremail=$orderinfo->user_email;

        $sagepay_email = $this->login_id;
        $sagepay_pass = $this->tran_key;

        $currency_values= $this->getCurrency($order);
        $amount = $this->getAmount($order->orderpayment_amount, $currency_values['currency_code'], $currency_values['currency_value'], $currency_values['convert']);


	$basket="";
	//total lines in the cart
	$basket .="1:";
	//name of the product
	$basket .=$order->order_id.":";
	//qty
	$basket .="1:";
	//single item value
	$basket .=$amount.":";

	//single item tax
	$basket .=":";

	//single item total
	$basket .=$amount .":";

	//Line total
	$basket .=$amount;


// basket format	4:Pioneer NSDV99 DVD-Surround Sound System:1:424.68:74.32:499.00: 499.00

	$shipping_country = $this->_getCountryCode($orderinfo->shipping_country_id)->country_isocode_2;

        // put all values into an array
        $sagepay_values             = array
        (
            "VPSProtocol"               => "2.23",
            "TxType"             		=> "PAYMENT",
            "AccountType" 				=> "E",
            "Apply3DSecure"             => "0",
            "Vendor"          			=> $sagepay_email,
            "VendorTxCode"          	=> $order->order_id,
            "Amount"              		=> $amount,
            "Currency"            		=> $currency_values['currency_code'],
            "Description"      			=> $order->order_id,
            "CardHolder"            	=> $sage_card_holder,
            "CardNumber"            	=> $sage_card_num,
            "ExpiryDate"              	=> $sage_card_exp_date,
            "CV2"           			=> $sage_cvv,
            "CardType"             		=> $sage_card_type,
         	"BillingSurname"			=> $sage_lname,
            "BillingFirstnames"			=> $sage_fname,
            "BillingAddress1"			=> $sage_address1,
            "BillingAddress2"			=> $sage_address2,
            "BillingCity"				=> $sage_city,
            "BillingPostCode"			=> $sage_zip,
			"BillingCountry"			=> $sage_country,
			"BillingPhone"				=> $sage_phone,
        	"DeliverySurname"	=> $orderinfo->shipping_last_name,
        	"DeliveryFirstnames"	=> $orderinfo->shipping_first_name,
        	"DeliveryAddress1"	=> $orderinfo->shipping_address_1,
        	"DeliveryCity"	=> $orderinfo->shipping_city,
        	"DeliveryPostCode"	=> $orderinfo->shipping_zip,
        	"DeliveryCountry"	=> $shipping_country,
            "CustomerEMail"             => $sage_useremail,
            "Basket"               		=> $basket,
            "GiftAidPayment"            => "0",
            "ClientIPAddress"           => $_SERVER['REMOTE_ADDR']
        );

        //add optional fields
      //  if($this->partner_id) {
	//		$sagepay_values["ReferrerID"] =$this->partner_id;
	//	}

    //    if($sage_card_start_date) {
	//		$sagepay_values["StartDate"] =$sage_card_start_date;
	//	}

     //   if($data['cardissue']) {
	//		$sagepay_values["IssueNumber"] =$data['cardissue'];
	//	}

        return $sagepay_values;
    }

    /**
     * Sends a request to the server using cURL
     *
     * @param string $url
     * @param string $content
     * @param arrray $http_headers (optional)
     * @return string
     * @access protected
     */
    function _sendRequest($url, $content, $http_headers = array())
    {
		// Set a one-minute timeout for this script
		set_time_limit(60);
		$output = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,30);
        //The next two lines must be present for the kit to work with newer version of cURL
		//You should remove them if you have any problems in earlier versions of cURL
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);

        if (is_array($http_headers) && count($http_headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
        }

        $resp = curl_exec($ch);

		//Send the request and store the result in an array

	//Split response into name=value pairs
	$response = explode(chr(10), $resp);
	// $response = preg_split('/$\R?^/m', $resp);
	// Check that a connection was made
	if (curl_error($ch)){
		// If it wasn't...
		$output['Status'] = "FAIL";
		$output['StatusDetail'] = curl_error($ch);
	}

	// Close the cURL session
	 curl_close ($ch);

	// Tokenise the response
	for ($i=0; $i<count($response); $i++){
		// Find position of first "=" character
		$splitAt = strpos($response[$i], "=");
		// Create an associative (hash) array with key/value pairs ('trim' strips excess whitespace)
		$output[trim(substr($response[$i], 0, $splitAt))] = trim(substr($response[$i], ($splitAt+1)));
	} // END for ($i=0; $i<count($response); $i++)

	// Return the output
	return $output;

    }

    /**
     * Simple logger
     *
     * @param string $text
     * @param string $type
     * @return void
     */
    function _log($text, $type = 'message')
    {
        if ($this->_isLog) {
            $file = JPATH_ROOT . "/cache/{$this->_element}.log";
            $date = JFactory::getDate();

            $f = fopen($file, 'a');
            fwrite($f, "\n\n" . $date->format('Y-m-d H:M:S'));
            fwrite($f, "\n" . $type . ': ' . $text);
            fclose($f);
        }
    }

    /**
     * Processes a simple (non-recurring payment)
     * by sending data to auth.net and interpreting the response
     * and managing the order as required
     *
     * @param array $authnet_values
     * @return string
     * @access protected
     */
    function _processSimplePayment($sagepay_values)
    {
        $html = '';

        // prepare the array for posting to authorize.net
        $fields = '';
        foreach( $sagepay_values as $key => $value ) {
            $fields .= "$key=" . urlencode( $value ) . "&";
        }

        // send a request
        $resp = $this->_sendRequest($this->_getActionUrl('simple'), rtrim( $fields, "& " ));

       //exit;
        // evaluate the response
        $evaluateResponse = $this->_evaluateSimplePaymentResponse( $resp, $sagepay_values );
        $html = $evaluateResponse;

        return $html;
    }
   //voveran
    /**
     * Proceeds the simple payment
     *
     * @param string $resp
     * @param array $submitted_values
     * @return object Message object
     * @access protected
     */
    function _evaluateSimplePaymentResponse( $resp, $submitted_values )
    {
        $object = new JObject();
        $object->message = '';
        $html = '';
        $errors = array();
        $payment_status = JText::_('J2STORE_INCOMPLETE');
		$user =JFactory::getUser();

        // Evaluate a typical response from sage pay
			$this->_log($resp['Status']);
            switch ($resp['Status'])
            {
                case 'OK':
                  // Approved
                   $payment_status = JText::_('J2STORE_COMPLETED');

                 break;

                 case 'MALFORMED':
                 case 'INVALID':
                 case 'NOTAUTHED':
                 case 'REJECTED':
                 case 'ERROR':
				  {
                     // Declined
                      $payment_status = JText::_('J2STORE_DECLINED');
                       $errors[] = $resp['Status'];
                   }
                   break;

                        default:
                           // Error
                            $payment_status = JText::_('J2STORE_ERROR');
                            $order_status = JText::_('J2STORE_INCOMPLETE');
                            $errors[] = JText::_( "J2STORE_SAGEPAY_ERROR_PROCESSING_PAYMENT" );
                          break;
                    }

        // =======================
        // verify & create payment
        // =======================

			$orderpayment_id = $this->_getOrderPaymentId($submitted_values['VendorTxCode']);

            // check that payment amount is correct for order_id
            JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/tables' );
            $orderpayment = JTable::getInstance('Orders', 'Table');
            $orderpayment->load( $orderpayment_id);
            if (empty($orderpayment->order_id))
            {
                // TODO fail
            }
            $orderpayment->transaction_details  = $this->_getFormattedTransactionDetails($resp);
            $orderpayment->transaction_id       = $resp['VPSTxId'];
            $orderpayment->transaction_status   = $payment_status;


            //set a default status to it
			$orderpayment->order_state = JText::_('J2STORE_PENDING'); // PENDING
			$orderpayment->order_state_id = 4; // PENDING

            // set the order's new status and update quantities if necessary
            if (count($errors))
            {
                // if an error occurred
                $orderpayment->order_state  = trim(JText::_('J2STORE_FAILED')); // FAILED
                 $orderpayment->order_state_id = 3; // FAILED
            }
                else
            {
				$orderpayment->order_state  = trim(JText::_('J2STORE_CONFIRMED')); // Payment received and CONFIRMED
				 $orderpayment->order_state_id = 1; // CONFIRMED
				 JLoader::register( 'J2StoreHelperCart', JPATH_SITE.'/components/com_j2store/helpers/cart.php');
				// remove items from cart
        	    J2StoreHelperCart::removeOrderItems( $orderpayment->id );
				//$this->setOrderPaymentReceived( $orderpayment->order_id );
            }

            // save the order
            if (!$orderpayment->save())
            {
                $errors[] = $orderpayment->getError();
            }

            if (empty($errors))
            {
            	 // let us inform the user that the payment is successful
        		require_once (JPATH_SITE.'/components/com_j2store/helpers/orders.php');
        		J2StoreOrdersHelper::sendUserEmail($orderpayment->user_id, $orderpayment->order_id, $payment_status, $orderpayment->order_state, $orderpayment->order_state_id);

                $return = JText::_( "J2STORE_SAGEPAY_MESSAGE_PAYMENT_SUCCESS" );
                return $return;
            } else {
            	$error = count($errors) ? implode("\n", $errors) : '';
            	$this->_sendErrorEmails($error, $orderpayment->transaction_details);
            }

            return count($errors) ? implode("\n", $errors) : '';

        // ===================
        // end custom code
        // ===================
    }


     function _getFormattedTransactionDetails( $data )
    {
        $separator = "\n";
        $formatted = array();

        foreach ($data as $key => $value)
        {
            if ($key != 'view' && $key != 'layout')
            {
                $formatted[] = $key . ' = ' . $value;
            }
        }

        return count($formatted) ? implode("\n", $formatted) : '';
    }

    /**
     * Sends error messages to site administrators
     *
     * @param string $message
     * @param string $paymentData
     * @return boolean
     * @access protected
     */
    function _sendErrorEmails($message, $paymentData)
    {
    	$mainframe =JFactory::getApplication();

    	// grab config settings for sender name and email
    	$config     = JComponentHelper::getParams('com_j2store');
    	$mailfrom   = $config->get( 'emails_defaultemail', $mainframe->getCfg('mailfrom') );
    	$fromname   = $config->get( 'emails_defaultname', $mainframe->getCfg('fromname') );
    	$sitename   = $config->get( 'sitename', $mainframe->getCfg('sitename') );
    	$siteurl    = $config->get( 'siteurl', JURI::root() );

    	$recipients = $this->_getAdmins();
    	$mailer =JFactory::getMailer();

    	$subject = JText::sprintf('J2STORE_SAGEPAY_EMAIL_PAYMENT_NOT_VALIDATED_SUBJECT', $sitename);

    	foreach ($recipients as $recipient)
    	{
    		$mailer = JFactory::getMailer();
    		$mailer->addRecipient( $recipient->email );

    		$mailer->setSubject( $subject );
    		$mailer->setBody( JText::sprintf('J2STORE_SAGEPAY_EMAIL_PAYMENT_FAILED_BODY', $recipient->name, $sitename, $siteurl, $message, $paymentData) );
    		$mailer->setSender(array( $mailfrom, $fromname ));
    		$sent = $mailer->send();
    	}

    	return true;
    }

    /**
     * Gets admins data
     *
     * @return array|boolean
     * @access protected
     */
    function _getAdmins()
    {
    	$db =JFactory::getDBO();
    	$query = $db->getQuery(true);
    	$query->select('u.name,u.email');
    	$query->from('#__users AS u');
    	$query->join('LEFT', '#__user_usergroup_map AS ug ON u.id=ug.user_id');
    	$query->where('u.sendEmail = 1');
    	$query->where('ug.group_id = 8');

    	$db->setQuery($query);
    	$admins = $db->loadObjectList();
    	if ($error = $db->getErrorMsg()) {
    		JError::raiseError(500, $error);
    		return false;
    	}

    	return $admins;
    }

    function _getOrderPaymentId($order_id) {

		$db = JFactory::getDBO();
		$query = 'SELECT id FROM #__j2store_orders WHERE order_id='.$db->quote($order_id);
		$db->setQuery($query);
		return $db->loadResult();

	}

	function _getOrderInfo($orderpayment_id) {

		$db = JFactory::getDBO();
		$query = 'SELECT * FROM #__j2store_orderinfo WHERE orderpayment_id='.$db->Quote($orderpayment_id);
		$db->setQuery($query);
		return $db->loadObject();
	}

	function _getCountryCode($country_id) {
		$db = JFactory::getDBO();
		$query = 'SELECT * FROM #__j2store_countries WHERE country_id='.$db->quote($country_id);
		$db->setQuery($query);
		$row = $db->loadObject();
		return $row;
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

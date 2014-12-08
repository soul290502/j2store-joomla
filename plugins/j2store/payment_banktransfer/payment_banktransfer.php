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

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/payment.php');

class plgJ2StorePayment_banktransfer extends J2StorePaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
    var $_element    = 'payment_banktransfer';

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 2.5
	 */
	function plgJ2StorePayment_banktransfer(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( 'com_j2store', JPATH_ADMINISTRATOR );
	}


    /**
     * Prepares the payment form
     * and returns HTML Form to be displayed to the user
     * generally will have a message saying, 'confirm entries, then click complete order'
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _prePayment( $data )
    {
        // prepare the payment form

        $vars = new JObject();
        $vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
        $vars->orderpayment_amount = $data['orderpayment_amount'];
        $vars->orderpayment_type = $this->_element;
        $vars->bank_information = $this->params->get('bank_information', '');

        $vars->display_name = $this->params->get('display_name', JText::_( "PLG_J2STORE_PAYMENT_BANKTRANSFER"));
        $vars->onbeforepayment_text = $this->params->get('onbeforepayment', '');
        $vars->button_text = $this->params->get('button_text', 'J2STORE_PLACE_ORDER');
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
        $app = JFactory::getApplication();
        $vars = new JObject();
        $html = '';
        $orderpayment_id = $app->input->getInt('orderpayment_id');

        // load the orderpayment record and set some values
        JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/tables' );
        $orderpayment = JTable::getInstance('Orders', 'Table');

        $orderpayment->load( $orderpayment_id );
        if($orderpayment->id == $orderpayment_id) {

        	$bank_information = $this->params->get('bank_information', '');

        	//we have to save the bank information in the customer note because that is the only field availale to display now
        	//TODO: Trigger a plugin event so that you can show custom info depending on the payment plugin.

        	//get the customer note. We dont want to overwrite it.

        	if(JString::strlen($bank_information) > 5) {
        		$customer_note = $orderpayment->customer_note;

	        	$html ='<br />';
	        	$html .='<strong>'.JText::_('J2STORE_BANK_TRANSFER_INSTRUCTIONS').'</strong>';
	        	$html .='<br />';
	        	$html .=$bank_information;

	        	$orderpayment->customer_note =$customer_note.$html;
        	}

	        $payment_status = $this->getPaymentStatus($this->params->get('payment_status', 4));

    	   $orderpayment->transaction_status = $payment_status;
	       $orderpayment->order_state = $payment_status;
           $orderpayment->order_state_id = $this->params->get('payment_status', 4); // DEFAULT: PENDING

       // save the orderpayment
        if ($orderpayment->save()) {
			JLoader::register( 'J2StoreHelperCart', JPATH_SITE.'/components/com_j2store/helpers/cart.php');
			 // remove items from cart
            J2StoreHelperCart::removeOrderItems( $orderpayment->id );
        }
        else
        {
        	$errors[] = $orderpayment->getError();
        }

         // let us inform the user that the order is successful
        require_once (JPATH_SITE.'/components/com_j2store/helpers/orders.php');
        J2StoreOrdersHelper::sendUserEmail($orderpayment->user_id, $orderpayment->order_id, $orderpayment->transaction_status, $orderpayment->order_state, $orderpayment->order_state_id);
        $vars->onafterpayment_text = $this->params->get('onafterpayment', '');
        // display the layout
        $html = $this->_getLayout('postpayment', $vars);

        // append the article with banktransfer payment information
        $html .= $this->_displayArticle();
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
    	$user = JFactory::getUser();
        $vars = new JObject();
        $vars->onselection_text = $this->params->get('onselection', '');
        $html = $this->_getLayout('form', $vars);
        return $html;
    }

    function getPaymentStatus($payment_status) {
    	$status = '';
    	switch($payment_status) {

    		case 1:
    			$status = JText::_('J2STORE_CONFIRMED');
    			break;

    		case 3:
    			$status = JText::_('J2STORE_FAILED');
    			break;

    		default:
    		case 4:
    			$status = JText::_('J2STORE_PENDING');
    			break;
    	}
    	return $status;
    }
}

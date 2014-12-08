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

defined ( '_JEXEC' ) or die ( 'Restricted access' );

jimport ( 'joomla.application.component.model' );
require_once(JPATH_SITE.'/components/com_j2store/helpers/plugin.php');
class J2StoreModelCallback extends J2StoreModel {

	function runCallback($method) {

		$rawDataPost = JRequest::get ( 'POST', 2 );
		$rawDataGet = JRequest::get ( 'GET', 2 );
		$data = array_merge ( $rawDataGet, $rawDataPost );

		// Some plugins result in an empty Itemid being added to the request
		// data, screwing up the payment callback validation in some cases (e.g.
		// PayPal).
		if (array_key_exists ( 'Itemid', $data )) {
			if (empty ( $data ['Itemid'] )) {
				unset ( $data ['Itemid'] );
			}
		}

		//$plugins = J2StoreHelperPlugin::getPlugins('j2store');
		JPluginHelper::importPlugin ('j2store');
		$app = JFactory::getApplication ();

		//run custom triggers

		$eventName = 'onJ2Store'.ucfirst($method);

		$app->triggerEvent( $eventName, array ($data));
		//run the post payment trigger
		$jResponse = $app->triggerEvent ( 'onJ2StorePostPayment', array (
				$method,
				$data
		) );
		if (empty ( $jResponse ))
			return false;

		$status = false;

		foreach ( $jResponse as $response ) {
			$status = $status || $response;
		}

		return $status;
	}
}
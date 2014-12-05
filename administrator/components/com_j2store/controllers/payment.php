<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/
/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

class J2StoreControllerPayment extends J2StoreController
{
	/**
	 * constructor
	 */
	function __construct()
	{

		parent::__construct();

		$this->set('suffix', 'payment');
	}

    /**
     * Sets the model's state
     *
     * @return array()
     */
    function _setModelState()
    {
    	$state = array();
       // $state = parent::_setModelState();
        $app = JFactory::getApplication();
        $model = $this->getModel( 'payment');
        $ns = 'com_j2store.payment';

        $state['filter_id_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
        $state['filter_id_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
        $state['filter_name']         = $app->getUserStateFromRequest($ns.'name', 'filter_name', '', '');

        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );
        }
        return $state;
    }


    function publish()
    {

    	$app = JFactory::getApplication();
    	// Check for request forgeries
    	JRequest::checkToken() or jexit( 'Invalid Token' );

    	$cid = $app->input->get( 'cid', array(), 'array' );
    	JArrayHelper::toInteger($cid);

    	if (count( $cid ) < 1) {
    		JError::raiseError(500, JText::_( 'J2STORE_SELECT_AN_ITEM_TO_PUBLISH' ) );
    	}

    	$table = $this->getModel('payment')->getTable();
    	if($table->load($cid[0])) {
    		$table->enabled = 1;
    		$table->store();
    	} else {
			echo "<script> alert('".$table->getError(true)."'); window.history.go(-1); </script>\n";
		}

    	$this->setRedirect( 'index.php?option=com_j2store&view=payment' );
    }

    function unpublish()
    {

    	$app = JFactory::getApplication();
    	// Check for request forgeries
    	JRequest::checkToken() or jexit( 'Invalid Token' );

    	$cid = $app->input->get( 'cid', array(), 'array' );
    	JArrayHelper::toInteger($cid);

    	if (count( $cid ) < 1) {
    		JError::raiseError(500, JText::_( 'J2STORE_SELECT_AN_ITEM_TO_UNPUBLISH' ) );
    	}


    	$table = $this->getModel('payment')->getTable();
    	if($table->load($cid[0])) {
    		$table->enabled = 0;
    		$table->store();
    	} else {
			echo "<script> alert('".$table->getError(true)."'); window.history.go(-1); </script>\n";
		}

    	$this->setRedirect( 'index.php?option=com_j2store&view=payment' );
    }

}

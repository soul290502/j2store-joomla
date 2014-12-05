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

class J2StoreControllerShipping extends J2StoreController
{
	/**
	 * constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->set('suffix', 'shipping');
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
        $model = $this->getModel( 'shipping');
        $ns = 'com_j2store.shipping';

        $state['filter_id_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
        $state['filter_id_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
        $state['filter_name']         = $app->getUserStateFromRequest($ns.'name', 'filter_name', '', '');

        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );
        }
        return $state;
    }


    /**
     * Will execute a task within a shipping plugin
     *
     * (non-PHPdoc)
     * @see application/component/JController::execute()
     */
    function execute( $task )
    {


    	$app = JFactory::getApplication();
    	$shippingTask = $app->input->getCmd('shippingTask', '');
    	$values = $app->input->getArray($_POST);
    	//print_r($values);

    	// Check if we are in a shipping method view. If it is so,
    	// Try lo load the shipping plugin controller (if any)
    	if ( $task  == "view" && $shippingTask != '' )
    	{
    		$model = $this->getModel('Shipping', 'J2StoreModel');

    		$id = $app->input->getInt('id', '0');

    		if(!$id)
    			parent::execute($task);

    		$model->setId($id);

			// get the data
			// not using getItem here to enable ->checkout (which requires JTable object)
			$row = $model->getTable();
			$row->load( (int) $model->getId() );
    		$element = $row->element;

			// The name of the Shipping Controller should be the same of the $_element name,
			// without the shipping_ prefix and with the first letter Uppercase, and should
			// be placed into a controller.php file inside the root of the plugin
			// Ex: shipping_standard => J2StoreControllerShippingStandard in shipping_standard/controller.php
			$controllerName = str_ireplace('shipping_', '', $element);
			$controllerName = ucfirst($controllerName);

	    	 $path = JPATH_SITE.'/plugins/j2store/';


	    	$controllerPath = $path.$element.'/'.$element.'/controller.php';

			if (file_exists($controllerPath)) {
				require_once $controllerPath;
			} else {
				$controllerName = '';
			}

			$className    = 'J2StoreControllerShipping'.$controllerName;

			if ($controllerName != '' && class_exists($className)){

	    		// Create the controller
				$controller   = new $className( );

				// Add the view Path
				$controller->addViewPath($path);

				// Perform the requested task
				$controller->execute( $shippingTask );

				// Redirect if set by the controller
				$controller->redirect();

			} else{
				parent::execute($task);
			}
    	} else{
    		parent::execute($task);
    	}
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

    	$table = $this->getModel('shipping')->getTable();
    	if($table->load($cid[0])) {
    		$table->enabled = 1;
    		$table->store();
    	} else {
			echo "<script> alert('".$table->getError(true)."'); window.history.go(-1); </script>\n";
		}

    	$this->setRedirect( 'index.php?option=com_j2store&view=shipping' );
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


    	$table = $this->getModel('shipping')->getTable();
    	if($table->load($cid[0])) {
    		$table->enabled = 0;
    		$table->store();
    	} else {
			echo "<script> alert('".$table->getError(true)."'); window.history.go(-1); </script>\n";
		}

    	$this->setRedirect( 'index.php?option=com_j2store&view=shipping' );
    }

    function view()
    {
    	$model = $this->getModel( 'shipping' );
    	$model->getId();
    	$row = $model->getItem();

    	$view   = $this->getView( 'shipping', 'html' );
    	$view->setModel( $model, true );
    	$view->assign( 'row', $row );
    	$view->setLayout( 'view' );

    	$model->emptyState();
    	$this->_setModelState();
    	// TODO take into account the $cachable value, as in $this->display();

    	$view->display();
    }

}

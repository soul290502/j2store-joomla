<?php
/*------------------------------------------------------------------------
# com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Gokila Priya - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class J2StoreControllerReport extends J2StoreController
{
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
        $model = $this->getModel( 'report');
        $ns = 'com_j2store.report';

        $state['filter_name']    = $app->getUserStateFromRequest($ns.'orderitem_name', 'filter_name', '', '');
        $state['filter_date']      = $app->getUserStateFromRequest($ns.'modified_date', 'filter_date', '', '');
        $state['filter_order_id']         = $app->getUserStateFromRequest($ns.'order_id', 'filter_order_id', '', '');
        $state['filter_order']         = $app->getUserStateFromRequest($ns.'tbl.order_id', 'filter_order', '', '');
        $state['filter_order_Dir']         = $app->getUserStateFromRequest($ns.'tbl.order_Dir', 'filter_order_Dir', '', '');

        foreach ($state as $key=>$value)
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
    	$reportTask = $app->input->getCmd('reportTask', '');
    	$values = $app->input->getArray($_POST);

    	// Check if we are in a shipping method view. If it is so,
    	// Try lo load the shipping plugin controller (if any)
    	if ( $task  == "view" && $reportTask != '' )
    	{
    		$model = $this->getModel('report', 'J2StoreModel');

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
			$controllerName = str_ireplace('report_', '', $element);
			$controllerName = ucfirst($controllerName);

	    	 $path = JPATH_SITE.'/plugins/j2store/';

	    	$controllerPath = $path.$element.'/'.$element.'/controller.php';

			if (file_exists($controllerPath)) {
				require_once $controllerPath;
			} else {
				$controllerName = '';
			}

			$className    = 'J2StoreControllerReport'.$controllerName;

			if ($controllerName != '' && class_exists($className)){

	    		// Create the controller
				$controller   = new $className( );

				// Add the view Path
				$controller->addViewPath($path);

				// Perform the requested task
				$controller->execute( $reportTask );

				// Redirect if set by the controller
				$controller->redirect();

			} else{
				parent::execute($task);
			}
    	} else{
    		parent::execute($task);
    	}
    }

    function view()
    {
    	$model = $this->getModel( 'report' );
    	$model->getId();
    	$row = $model->getItem();
    	$view   = $this->getView( 'report', 'html' );
    	$view->setModel( $model, true );
    	$view->assign( 'row', $row );
    	$view->setLayout( 'view' );
    	$model->emptyState();
    	$this->_setModelState();
	   	// TODO take into account the $cachable value, as in $this->display();

    	$view->display();
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

    	$table = $this->getModel('report')->getTable();
    	if($table->load($cid[0])) {
    		$table->enabled = 1;
    		$table->store();
    	} else {
    		echo "<script> alert('".$table->getError(true)."'); window.history.go(-1); </script>\n";
    	}

    	$this->setRedirect( 'index.php?option=com_j2store&view=report' );
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


    	$table = $this->getModel('report')->getTable();
    	if($table->load($cid[0])) {
    		$table->enabled = 0;
    		$table->store();
    	} else {
    		echo "<script> alert('".$table->getError(true)."'); window.history.go(-1); </script>\n";
    	}

    	$this->setRedirect( 'index.php?option=com_j2store&view=report' );
    }

}

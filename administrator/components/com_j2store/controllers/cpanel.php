<?php
/*------------------------------------------------------------------------
# com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/



// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class J2StoreControllerCpanel extends J2StoreController {

    function display($cachable = false, $urlparams = array()) {
    	JFactory::getApplication()->input->set('view', 'cpanel');
        parent::display();
    }

}

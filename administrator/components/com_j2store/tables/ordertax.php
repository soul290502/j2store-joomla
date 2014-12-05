<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/

// No direct access
defined('_JEXEC') or die;

/**
 * Weblink Table class
 *
 * @package		Joomla.Administrator
 * @subpackage	com_j2store
 * @since		1.5
 */
class TableOrdertax extends JTable
{


	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__j2store_ordertax', 'ordertax_id', $db);
	}

}
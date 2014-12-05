<?php
/*
 * --------------------------------------------------------------------------------
Weblogicx India  - J2Store
* --------------------------------------------------------------------------------
* @package		Joomla! 2.5x
* @subpackage	J2 Store
* @author    	Weblogicx India http://www.weblogicxindia.com
* @copyright	Copyright (c) 2010 - 2015 Weblogicx India Ltd. All rights reserved.
* @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
* @link		http://weblogicxindia.com
* --------------------------------------------------------------------------------
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class TableOrderFiles extends JTable
{

	/**
	 * @param database A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__j2store_orderfiles', 'orderfile_id', $db );
	}

	function save($src=array(), $orderingFilter = '', $ignore = '')
	{
		$this->_isNew = false;
		$key = $this->getKeyName();
		if (empty($this->$key))
		{
			$this->_isNew = true;
		}

		if ( !$this->check() )
		{
			return false;
		}

		if ( !$this->store() )
		{
			return false;
		}

		if ( !$this->checkin() )
		{
			$this->setError( $this->_db->stderr() );
			return false;
		}

		$this->setError('');

		// TODO Move ALL onAfterSave plugin events here as opposed to in the controllers, duh
		//$dispatcher = JDispatcher::getInstance();
		//$dispatcher->trigger( 'onAfterSave'.$this->get('_suffix'), array( $this ) );
		return true;
	}

}
?>

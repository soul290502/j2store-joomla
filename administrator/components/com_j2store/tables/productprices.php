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
 *
 * @package		Joomla.Administrator
 * @subpackage	com_j2store
 * @since		3.2
 */
class TableProductPrices extends JTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__j2store_productprices', 'productprice_id', $db);
	}


	function save()
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

		$this->reorder();


		$this->setError('');

		// TODO Move ALL onAfterSave plugin events here as opposed to in the controllers, duh
		//$dispatcher = JDispatcher::getInstance();
		//$dispatcher->trigger( 'onAfterSave'.$this->get('_suffix'), array( $this ) );
		return true;
	}

	function reorder()
	{
		parent::reorder('product_id = '.$this->_db->Quote($this->product_id) );
	}

}
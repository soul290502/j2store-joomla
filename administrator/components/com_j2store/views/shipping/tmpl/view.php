<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access'); ?>
<?php $row = $this->row; ?>

	<h3>
	    <?php echo JText::_($row->name); ?>
	</h3>

	<?php
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger( 'onJ2StoreGetShippingView', array( $row ) );

        for ($i=0; $i<count($results); $i++)
        {
            $result = $results[$i];
            echo $result;
        }
	?>
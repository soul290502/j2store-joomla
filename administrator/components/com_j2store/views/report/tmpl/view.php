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

?>
<?php $row = $this->row;

?>

	<h3>
	    <?php echo JText::_($row->name); ?>
	</h3>

	<?php
		JPluginHelper::importPlugin('j2store');
		$dispatcher = JDispatcher::getInstance();

		$results = JFactory::getApplication()->triggerEvent( 'onJ2StoreGetReportView', array( $row ) );

        for ($i=0; $i<count($results); $i++)
        {
            $result = $results[$i];
            echo $result;
        }
	?>
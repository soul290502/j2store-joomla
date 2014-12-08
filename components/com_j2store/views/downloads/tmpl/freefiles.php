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


/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

?>

<?php if(count($this->files)): ?>
<div class="j2store_downloads_list">
	<span class="j2store_downloads_list_text"><?php echo JText::_('J2STORE_DOWNLOADS'); ?>
	</span>
	<ul>
		<?php foreach ($this->files as $file): 
		$link = JRoute::_('index.php?option=com_j2store&view=downloads&task=getfreefile&pfile_id='.$file->productfile_id);
		?>
		<li><a href="<?php echo $link ?>"><?php echo $file->display_name; ?> </a>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
<div class="clr"></div>
<?php endif; ?>

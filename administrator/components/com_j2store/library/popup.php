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
defined('_JEXEC') or die('Restricted access');

class J2StorePopup {


	public static function popup( $url, $text, $options = array() )
	{

		require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/browser.php');
		$params = JComponentHelper::getParams('com_j2store');
		$html = "";

		if(!empty($options['onclose']))
		{
			//	JHTML::_('behavior.modal', 'a.modal', array('onClose'=> $options['onclose']) );
		}
		else
		{
			if (!empty($options['update']))
			{
				//		    JHTML::_('behavior.modal', 'a.modal', array('onClose'=>'\function(){j2storeUpdate();}') );
			}
			else
			{
				//	    JHTML::_('behavior.modal', 'a.modal');
			}
		}

		// set the $handler_string based on the user's browser
		if(!empty($options['onclose'])) {
			$handler_string = "{handler:'iframe',size:{x: window.innerWidth-80, y: window.innerHeight-80}, onClose: function(){j2storeNewModal('Refreshing the window...'); submitbutton('apply');}}";
		} else {
			$handler_string = "{handler:'iframe',size:{x: window.innerWidth-80, y: window.innerHeight-80}}";
		}

		$browser = new J2StoreBrowser();
		if ( $browser->getBrowser() == J2StoreBrowser::BROWSER_IE )
		{
			// if IE, use
			if(!empty($options['onclose'])) {
				$handler_string = "{handler:'iframe',size:{x:document.documentElement.­clientWidth-80, y: document.documentElement.­clientHeight-80} onClose: function(){j2storeNewModal('Refreshing the window...'); submitbutton('apply');}}";
			} else {
				$handler_string = "{handler:'iframe',size:{x:document.documentElement.­clientWidth-80, y: document.documentElement.­clientHeight-80}}";
			}
		}

		$handler = (!empty($options['img']))
		? "{handler:'image'}"
		  : $handler_string;

		$lightbox_width = $params->get('lightbox_width');
		if(empty($options['width']) && !empty($lightbox_width))
			$options['width'] = $lightbox_width;

		if(!empty($options['width']))
		{
			if (empty($options['height']))
				$options['height'] = 480;

			$handler = "{handler: 'iframe', size: {x: ".$options['width'].", y: ".$options['height']. "}}";
		}

		$class = (!empty($options['class'])) ? $options['class'] : '';

		$html	= "<a class=\"modal\" href=\"$url\" rel=\"$handler\" >\n";
		$html 	.= "<span class=\"".$class."\" >\n";
		$html   .= "$text\n";
		$html 	.= "</span>\n";
		$html	.= "</a>\n";

		return $html;
	}

}

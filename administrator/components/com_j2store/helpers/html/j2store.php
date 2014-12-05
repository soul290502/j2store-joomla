<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/
defined('_JEXEC') or die;
abstract class JHtmlJ2Store
{
	static function required1($value = 0, $i)
	{
		$states = array(
				0=>
					array(
						'disabled.png',
						'field.required',
						'',
						'Toggle to approve'
					),
				1=>
					 array(
					 		'tick.png',
					 		'field.notrequired',
					 		'',
					 		'Toggle to unapprove'
					 	),
				 );
		$state   = JArrayHelper::getValue($states, (int) $value, $states[1]);
		$html    = JHtml::_('image', 'admin/'.$state[0], JText::_($state[2]), NULL, true);
		$html    = '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.JText::_($state[3]).'">'. $html.'</a>';
		return $html;
	}


	public static function required($value, $i, $enabled = true, $checkbox = 'cb')
	{
		$states = array(
				1 => array(
						'notrequired',
						'J2STORE_FIELD_REQUIRED',
						'J2STORE_FIELD_MAKE_NOTREQUIRED',
						'J2STORE_FIELD_REQUIRED',
						true,
						'publish',
						'publish'
				),
				0 => array(
						'required',
						'J2STORE_FIELD_NOTREQUIRED',
						'J2STORE_FIELD_MAKE_REQUIRED',
						'J2STORE_FIELD_NOTREQUIRED',
						true,
						'unpublish',
						'unpublish'
				),
		);

		return JHtml::_('jgrid.state', $states, $value, $i, '', $enabled, true, $checkbox);
	}


}

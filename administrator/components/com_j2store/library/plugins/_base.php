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


/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

/** Import library dependencies */
jimport('joomla.event.plugin');
jimport('joomla.utilities.string');

class J2StorePluginBase extends JPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element    = '';

	var $_row    = '';

	/**
	 * Checks to make sure that this plugin is the one being triggered by the extension
	 *
	 * @access public
	 * @return mixed Parameter value
	 * @since 2.5
	 */
	function _isMe( $row )
	{
		$element = $this->_element;

		$success = false;
		if (is_object($row) && !empty($row->element) && $row->element == $element )
		{
			$success = true;
		}

		if (is_string($row) && $row == $element ) {
			$success = true;
		}

		return $success;
	}

	protected function _getMe() {
		if (empty($this -> _row)) {
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
			$table = JTable::getInstance('Shipping', 'Table');
			$table -> load(array('element' => $this -> _element, 'folder' => 'j2store'));
			$this -> _row = $table;

		}
		return $this -> _row;
	}

	/**
	 * Prepares variables for the form
	 *
	 * @return string   HTML to display
	 */
	function _renderForm($data)
	{
		$vars = new JObject();
		$html = $this->_getLayout('form', $vars);
		return $html;
	}

	/**
	 * Prepares the 'view' tmpl layout
	 *
	 * @param array
	 * @return string   HTML to display
	 */
	function _renderView( $options)
	{
		$vars = new JObject();
		$html = $this->_getLayout('view', $vars);
		return $html;
	}

	/**
	 * Wraps the given text in the HTML
	 *
	 * @param string $text
	 * @return string
	 * @access protected
	 */
	function _renderMessage($message = '')
	{
		$vars = new JObject();
		$vars->message = $message;
		$html = $this->_getLayout('message', $vars);
		return $html;
	}

	/**
	 * Gets the parsed layout file
	 *
	 * @param string $layout The name of  the layout file
	 * @param object $vars Variables to assign to
	 * @param string $plugin The name of the plugin
	 * @param string $group The plugin's group
	 * @return string
	 * @access protected
	 */
	function _getLayout($layout, $vars = false, $plugin = '', $group = 'j2store' )
	{
		if (empty($plugin))
		{
			$plugin = $this->_element;
		}

		ob_start();
		$layout = $this->_getLayoutPath( $plugin, $group, $layout );
		include($layout);
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}


	/**
	 * Get the path to a layout file
	 *
	 * @param   string  $plugin The name of the plugin file
	 * @param   string  $group The plugin's group
	 * @param   string  $layout The name of the plugin layout file
	 * @return  string  The path to the plugin layout file
	 * @access protected
	 */
	function _getLayoutPath($plugin, $group, $layout = 'default')
	{
		$app = JFactory::getApplication();

		// get the template and default paths for the layout
		$templatePath = JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'plugins'.DS.$group.DS.$plugin.DS.$layout.'.php';
		$defaultPath = JPATH_SITE.DS.'plugins'.DS.$group.DS.$plugin.DS.$plugin.DS.'tmpl'.DS.$layout.'.php';

		// if the site template has a layout override, use it
		jimport('joomla.filesystem.file');
		if (JFile::exists( $templatePath ))
		{
			return $templatePath;
		}
		else
		{
			return $defaultPath;
		}
	}

	/**
	 * This displays the content article
	 * specified in the plugin's params
	 *
	 * @return unknown_type
	 */

	function _displayArticle()
	{
		$html = '';

		$articleid = $this->params->get('articleid');
		if ($articleid)
		{
			require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_j2store'.DS.'library'.DS.'j2item.php');
			$html = J2StoreItem::display( $articleid );
		}

		return $html;
	}

	/**
	 * Checks for a form token in the request
	 * Using a suffix enables multi-step forms
	 *
	 * @param string $suffix
	 * @return boolean
	 */
	function _checkToken( $suffix='', $method='post' )
	{
		$token  = JUtility::getToken();
		$token .= ".".strtolower($suffix);
		if (JRequest::getVar( $token, '', $method, 'alnum' ))
		{
			return true;
		}
		return false;
	}

	/**
	 * Generates an HTML form token and affixes a suffix to the token
	 * enabling the form to be identified as a step in a process
	 *
	 * @param string $suffix
	 * @return string HTML
	 */
	function _getToken( $suffix='' )
	{
		$token  = JUtility::getToken();
		$token .= ".".strtolower($suffix);
		$html  = '<input type="hidden" name="'.$token.'" value="1" />';
		$html .= '<input type="hidden" name="tokenSuffix" value="'.$suffix.'" />';
		return $html;
	}

	/**
	 * Gets the suffix affixed to the form's token
	 * which helps identify which step this is
	 * in a multi-step process
	 *
	 * @return string
	 */
	function _getTokenSuffix( $method='post' )
	{
		$suffix = JRequest::getVar( 'tokenSuffix', '', $method );
		if (!$this->_checkToken($suffix, $method))
		{
			// what to do if there isn't this suffix's token in the request?
			// anything?
		}
		return $suffix;
	}

	/**
	 * Make the standard J2Store Tables avaiable in the plugin
	 */
	protected function includeJ2StoreTables() {
		// Include J2Store Tables Classes
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_j2store/tables');
	}

	/**
	 * Include a particular J2Store Model
	 * @param $name the name of the mode (ex: products)
	 */
	protected function includeJ2StoreModel($name, $admin=true) {
		if ($admin)
			$base_path=JPATH_ADMINISTRATOR;
		else
			$base_path=JPATH_SITE;
		JModelAdmin::addIncludePath($base_path.'/components/com_j2store/models/'.strtolower($name));
	}

	/**
	 * Include a particular Custom Model
	 * @param $name the name of the model
	 * @param $plugin the name of the plugin in which the model is stored
	 * @param $group the group of the plugin
	 */
	protected function includeCustomModel($name, $plugin = '', $group = 'j2store') {
		if (empty($plugin)) {
			$plugin = $this -> _element;
		}

			if (!class_exists('J2StoreModel' . $name))
				JLoader::import('plugins.' . $group . '.' . $plugin . '.' . $plugin . '.models.' . strtolower($name), JPATH_SITE);
	}

	/**
	 * add a user-defined table to list of available tables (including the j2store tables
	 * @param $plugin the name of the plugin in which the table is stored
	 * @param $group the group of the plugin
	 */
	protected function includeCustomTables($plugin = '', $group = 'j2store') {

		if (empty($plugin)) {
			$plugin = $this -> _element;
		}

		$this ->includeJ2StoreTables();
		$customPath = JPATH_SITE. '/plugins/'.$group.'/'.$plugin . '/'. $plugin.'/tables';
		JTable::addIncludePath($customPath);
	}

	/**
	 * Include a particular Custom View
	 * @param $name the name of the view
	 * @param $plugin the name of the plugin in which the view is stored
	 * @param $group the group of the plugin
	 */
	protected function includeCustomView($name, $plugin = '', $group = 'j2store') {
		if (empty($plugin)) {
			$plugin = $this -> _element;
		}
			if (!class_exists('J2StoreView' . $name))
				JLoader::import('plugins.' . $group . '.' . $plugin . '.' . $plugin . '.views.' . strtolower($name), JPATH_SITE);
	}
}

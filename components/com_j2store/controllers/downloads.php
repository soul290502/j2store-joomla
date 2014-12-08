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
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_j2store'.DS.'tables');

class J2StoreControllerDownloads extends J2StoreController
{

	function __construct()
	{

		parent::__construct();

	}

	function display($cachable = false, $urlparams = array()) {

		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if (empty(JFactory::getUser()->id))
		{
			$url = JRoute::_( "index.php?option=com_j2store&view=downloads" );
			$redirect = "index.php?option=com_users&view=login&return=".base64_encode( $url );
			$redirect = JRoute::_( $redirect, false );
			JFactory::getApplication()->redirect( $redirect );
			return;
		}

		$params = JComponentHelper::getParams('com_j2store');
		$model  = $this->getModel('downloads');
		$ns = 'com_j2store.downloads';

		$files= $model->getItems();
		$registry = new JRegistry();
		$registry->loadArray($files);
		$files = $registry->toObject();
		//print_r($files);
		//exit;
		//$freefiles=$model->getFreeFiles();

		//$files=$this->process($files,$freefiles);

		$view = $this->getView( 'downloads', 'html' );
		$view->set( '_controller', 'downloads' );
		$view->set( '_view', 'downloads' );
		$view->set( '_doTask', true);

		$view->set( 'hidemenu', false);
		$view->setModel( $model, true );
		$view->assign( 'files', $files );
		$view->assign( 'params', $params );
		$view->setLayout( 'default' );

		//	$view->display();
		parent::display();

	}

	function getfile(){

		JRequest::checkToken( 'get' ) or die( 'Invalid Token' );

		//get system objects and variables
		jimport('joomla.filesystem.file');
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_j2store');

		JPluginHelper::importPlugin('j2store');
		$dispatcher = JDispatcher::getInstance();

		$orderfile_id=JRequest::getVar('ofile_id',0,'get');
		$productfile_id=JRequest::getVar('pfile_id',0,'get');

		//do some basic checks
		if(!$user->id) {
			$msg = JText::_('J2STORE_MUST_LOGIN');
			$link = JRoute::_('index.php');
			$app->redirect($link, $msg);
		}

		if($orderfile_id < 1 || $productfile_id < 1) {
			$msg = JText::_('J2STORE_INVALID_FILE');
			$link = JRoute::_('index.php?option=com_j2store&view=downloads');
			$app->redirect($link, $msg);
		}

		//get order files table

		$orderfile = JTable::getInstance('orderfiles','Table');
		$orderfile->load($orderfile_id);

		//get product files
		$productfile = JTable::getInstance('productfiles','Table');
		$productfile->load($productfile_id);

		//once again check the limits

		if(($orderfile->limit_count < $productfile->download_limit) || $productfile->download_limit==-1) {

			$dispatcher->trigger('onJ2StoreBeforeDownload',  array($orderfile, &$productfile, &$params));

		/* 	$path = trim($params->get('attachmentfolderpath'));
			$savepath = $path.DS.'products';
			$file = $savepath.DS.$productfile->product_id.DS.$productfile->product_file_save_name;
 */
			$path = $params->get('attachmentfolderpath');
			//$savepath = $path.DS.'products';
			$file = JPath::clean($path.'/'.$productfile->product_file_save_name);
			//if does not exists, check inside the web root
			if(!JFile::exists($file)) {
				$path = JPATH_ROOT.'/'.$path;
				$file = JPath::clean($path.'/'.$productfile->product_file_save_name);
			}

			//legacy compatibility
			if(!JFile::exists($file)) {
				$path = trim($params->get('attachmentfolderpath'));
				$savepath = $path.DS.'products';
				$file = $savepath.DS.$productfile->product_id.DS.$productfile->product_file_save_name;
			}

			$file = JPath::clean($file);

			if (JFile::exists($file)) {
				$dispatcher->trigger('onJ2StoreAfterDownload',  array($orderfile, $productfile, &$params));
				if ($app->isSite()) {
					$this->hit($orderfile_id, $productfile->productfile_id);
				}

				// only show errors and remove warnings from corrupting file
				error_reporting(E_ERROR);

				ob_clean();
				if (connection_status()!=0) return(FALSE);

				$fn = basename($file);
				header("Cache-Control: no-store, no-cache, must-revalidate");
				header("Cache-Control: post-check=0, pre-check=0", false);
				header("Pragma: no-cache");
				header("Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")+2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
				header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
				header("Content-Transfer-Encoding: binary");

				//TODO:  Not sure of this is working
				if (function_exists('mime_content_type')) {
					$ctype = mime_content_type($file);
				}
				else if (function_exists('finfo_file')) {
					$finfo    = finfo_open(FILEINFO_MIME);
					$ctype = finfo_file($finfo, $file);
					finfo_close($finfo);
				}
				else {
					$ctype = "application/octet-stream";
				}

				header('Content-Type: ' . $ctype);

				if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
				{
					/*//workaround for IE filename bug with multiple periods / multiple dots in filename
					 //that adds square brackets to filename - eg. setup.abc.exe becomes setup[1].abc.exe*/
					$iefilename = preg_replace('/\./', '%2e', $fn, substr_count($fn, '.') - 1);
					header("Content-Disposition: attachment; filename=\"$iefilename\"");
				}
				else
				{
					header("Content-Disposition: attachment; filename=\"$fn\"");
				}

				header("Accept-Ranges: bytes");

				$range = 0; // default to begining of file
				//TODO make the download speed configurable
				$size=filesize($file);

				//check if http_range is set. If so, change the range of the download to complete.
				if(isset($_SERVER['HTTP_RANGE']))
				{
					list($a, $range)=explode("=",$_SERVER['HTTP_RANGE']);
					str_replace($range, "-", $range);
					$size2=$size-1;
					$new_length=$size-$range;
					header("HTTP/1.1 206 Partial Content");
					header("Content-Length: $new_length");
					header("Content-Range: bytes $range$size2/$size");
				}
				else
				{
					$size2=$size-1;
					header("HTTP/1.0 200 OK");
					header("Content-Range: bytes 0-$size2/$size");
					header("Content-Length: ".$size);
				}

				//check to ensure it is not an empty file so the feof does not get stuck in an infinte loop.
				if ($size == 0 ) {
					JError::raiseError (500, 'ERROR.ZERO_BYE_FILE');
					exit;
				}
				set_magic_quotes_runtime(0); // in case someone has magic quotes on. Which they shouldn't as good practice.

				// we should check to ensure the file really exits to ensure feof does not get stuck in an infite loop, but we do so earlier on, so no need here.
				$fp=fopen("$file","rb");

				//go to the start of missing part of the file
				fseek($fp,$range);
				if (function_exists("set_time_limit"))
					set_time_limit(0);
				while(!feof($fp) && connection_status() == 0)
				{
					//reset time limit for big files
					if (function_exists("set_time_limit"))
						set_time_limit(0);
					print(fread($fp,1024*8));
					flush();
					ob_flush();
				}
				sleep(1);
				fclose($fp);
				return((connection_status()==0) and !connection_aborted());

			} else {
				$msg=JText::_('J2STORE_FILE_DOES_NOT_EXIST');
				$link = JRoute::_('index.php?option=com_j2store&view=downloads');
				$app->redirect($link,$msg);
			}

		} else {
			$msg=JText::_('J2STORE_FILE_LIMIT_EXCEEDED');
			$link = JRoute::_('index.php?option=com_j2store&view=downloads');
			$app->redirect($link,$msg);
		}
		$app->close();
	}

	function hit($orderfile_id, $productfile_id){

		//add to limit count
		$orderfile = JTable::getInstance('orderfiles','Table');
		$orderfile->load($orderfile_id);
		$orderfile->limit_count = $orderfile->limit_count+1;
		$orderfile->store();

		//add product download count
		$productfile = JTable::getInstance('productfiles','Table');
		$productfile ->load($productfile_id);

		$productfile->download_total = $productfile->download_total+1;
		$productfile->store();
		return true;
	}

	function getfreefile(){

		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_j2store');
		$productfile_id=JRequest::getVar('pfile_id',0,'get');

		//get product files
		$productfile = JTable::getInstance('productfiles','Table');
		$productfile->load($productfile_id);

		$path = $params->get('attachmentfolderpath');
		//$savepath = $path.DS.'products';
		$file = JPath::clean($path.'/'.$productfile->product_file_save_name);
		//if does not exists, check inside the web root
		if(!JFile::exists($file)) {
			$path = JPATH_ROOT.'/'.$path;
			$file = JPath::clean($path.'/'.$productfile->product_file_save_name);
		}

		//legacy compatibility
		if(!JFile::exists($file)) {
			$path = trim($params->get('attachmentfolderpath'));
			$savepath = $path.DS.'products';
			$file = $savepath.DS.$productfile->product_id.DS.$productfile->product_file_save_name;
		}

		if (JFile::exists($file)) {

			// only show errors and remove warnings from corrupting file
			error_reporting(E_ERROR);

			ob_clean();
			if (connection_status()!=0) return(FALSE);

			$fn = basename($file);
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header("Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")+2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
			header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
			header("Content-Transfer-Encoding: binary");

			//TODO:  Not sure of this is working
			if (function_exists('mime_content_type')) {
				$ctype = mime_content_type($file);
			}
			else if (function_exists('finfo_file')) {
				$finfo    = finfo_open(FILEINFO_MIME);
				$ctype = finfo_file($finfo, $file);
				finfo_close($finfo);
			}
			else {
				$ctype = "application/octet-stream";
			}

			header('Content-Type: ' . $ctype);

			if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
			{
				/*//workaround for IE filename bug with multiple periods / multiple dots in filename
				 //that adds square brackets to filename - eg. setup.abc.exe becomes setup[1].abc.exe*/
				$iefilename = preg_replace('/\./', '%2e', $fn, substr_count($fn, '.') - 1);
				header("Content-Disposition: attachment; filename=\"$iefilename\"");
			}
			else
			{
				header("Content-Disposition: attachment; filename=\"$fn\"");
			}

			header("Accept-Ranges: bytes");

			$range = 0; // default to begining of file
			//TODO make the download speed configurable
			$size=filesize($file);

			//check if http_range is set. If so, change the range of the download to complete.
			if(isset($_SERVER['HTTP_RANGE']))
			{
				list($a, $range)=explode("=",$_SERVER['HTTP_RANGE']);
				str_replace($range, "-", $range);
				$size2=$size-1;
				$new_length=$size-$range;
				header("HTTP/1.1 206 Partial Content");
				header("Content-Length: $new_length");
				header("Content-Range: bytes $range$size2/$size");
			}
			else
			{
				$size2=$size-1;
				header("HTTP/1.0 200 OK");
				header("Content-Range: bytes 0-$size2/$size");
				header("Content-Length: ".$size);
			}

			//check to ensure it is not an empty file so the feof does not get stuck in an infinte loop.
			if ($size == 0 ) {
				JError::raiseError (500, 'ERROR.ZERO_BYE_FILE');
				exit;
			}
			set_magic_quotes_runtime(0); // in case someone has magic quotes on. Which they shouldn't as good practice.

			// we should check to ensure the file really exits to ensure feof does not get stuck in an infite loop, but we do so earlier on, so no need here.
			$fp=fopen("$file","rb");

			//go to the start of missing part of the file
			fseek($fp,$range);
			if (function_exists("set_time_limit"))
				set_time_limit(0);
			while(!feof($fp) && connection_status() == 0)
			{
				//reset time limit for big files
				if (function_exists("set_time_limit"))
					set_time_limit(0);
				print(fread($fp,1024*8));
				flush();
				ob_flush();
			}
			sleep(1);
			fclose($fp);
			return((connection_status()==0) and !connection_aborted());
		}
		$app->close();
	}

}
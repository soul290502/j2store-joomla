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
jimport( 'joomla.application.component.model' );
jimport( 'joomla.application.component.view' );

class J2StoreDownloads
{


	public static function getDownloadHtml($product_id){

		$app = JFactory::getApplication();
		$html = '';
		JLoader::register( "J2StoreViewDownloads", JPATH_SITE."/components/com_j2store/views/downloads/view.html.php" );
		$layout = 'freefiles';
		$view = new J2StoreViewDownloads( );
		//$view->_basePath = JPATH_ROOT.DS.'components'.DS.'com_j2store';
		$view->addTemplatePath(JPATH_SITE.'/components/com_j2store/views/downloads/tmpl');
		$view->addTemplatePath(JPATH_SITE.'/templates/'.$app->getTemplate().'/html/com_j2store/downloads');

		require_once (JPATH_SITE.'/components/com_j2store/models/downloads.php');
		$model =  new J2StoreModelDownloads();

		$files = $model->getFreeFiles($product_id);
		$view->assign( '_basePath', JPATH_SITE.'/components/com_j2store' );
		$view->set( '_controller', 'downloads' );
		$view->set( '_view', 'downloads' );
		$view->set( '_doTask', true );
		$view->set( 'hidemenu', true );
		$view->setModel( $model, true );
		$view->setLayout( $layout );
		$view->assign( 'product_id', $product_id);
		$config = JComponentHelper::getParams('com_j2store');
		$view->assign( 'params', $config );
		$view->assign( 'files', $files);
		ob_start( );
		$view->display( );
		$html = ob_get_contents( );
		ob_end_clean( );

		return $html;

	}


}
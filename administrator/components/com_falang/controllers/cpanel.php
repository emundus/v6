<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

class CpanelController extends JControllerLegacy  {

	function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask( 'show',  'display' );

		// ensure DB cache table is created and up to date
		JLoader::import( 'helpers.controllerHelper',FALANG_ADMINPATH);
		//v 1.4 remove cache table creation and check
        //JLoader::import( 'classes.JCacheStorageJFDB',FALANG_ADMINPATH);
        //FalangControllerHelper::_checkDBCacheStructure();
		FalangControllerHelper::_checkDBStructure();
		//FalangControllerHelper::_checkPlugin();//not necessary with Joomla 4
		FalangControllerHelper::_checkAdvancedRouter();

	}

	/**
	 * Standard display control structure
	 * 
	 */
	function display($cachable = false, $urlparams = array())
	{
		$this->view =  $this->getView('cpanel');
		parent::display();
	}
	
	function cancel()
	{
		$this->setRedirect( 'index.php?option=com_falang' );
	}

    function checkUpdates() {
        //force information reload
        $updateInfo = LiveUpdate::getUpdateInformation(true);
        //send json response
        $document = JFactory::getDocument();
        $document->setMimeEncoding('application/json');

        if ($updateInfo->hasUpdates) {
            $msg = JText::_('COM_FALANG_CPANEL_OLD_VERSION').'<a href="index.php?option=com_falang&view=liveupdate"/> '.JText::_('COM_FALANG_CPANEL_UPDATE_LINK').'</a>';
            echo json_encode(array('update' => "true",'version' => $updateInfo->version, 'message' => $msg));
        } else {
            $msg = JText::_('COM_FALANG_CPANEL_LATEST_VERSION');
            echo json_encode(array('update' => "false",'version' => $updateInfo->version, 'message' => $msg));
        }
        return true;
    }

}

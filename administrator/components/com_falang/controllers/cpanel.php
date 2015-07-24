<?php
/**
 * @version		3.0
 * @package		Joomla
 * @subpackage	Falang
 * @author      Stéphane Bouey
 * @copyright	Copyright (C) 2012 Faboba
 * @license		GNU/GPL, see LICENSE.php
 */


defined( '_JEXEC' ) or die;

require_once JPATH_ROOT.'/administrator/components/com_falang/legacy/controller.php';

class CpanelController extends LegacyController  {

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

        if( !FalangControllerHelper::_testSystemBotState() ) {
            //todo mettre l'affichage dans la vue
            $msg = '<div class="alert alert-warning">';
            $msg .= '<h4>'.JText::_('COM_FALANG_TEST_SYSTEM_WARNING').'</h4>';
            $msg .= '<p>'.JText::_('COM_FALANG_TEST_SYSTEM_WARNING_MSG').'</p>';
            $msg .= '</div>';

            echo $msg;
        }

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
        $document =& JFactory::getDocument();
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

?>

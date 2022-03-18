<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link       http://www.emundus.fr
 *
 * @license     GNU/GPL
 * @author      HUBINET Brice
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

class EmundusControllerSync extends JControllerLegacy {
    private $_user = null;
    private $_db = null;
    private $m_sync = null;

    public function __construct($config = array()) {
        parent::__construct($config);

        require_once (JPATH_COMPONENT.DS.'models'.DS.'sync.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');

        $this->_user  = JFactory::getSession()->get('emundusUser');
        $this->_db    = JFactory::getDBO();
        $this->m_sync = new EmundusModelSync();
    }

    public function getconfig(){
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $type = $jinput->getString('type', null);

            $config = $this->m_sync->getConfig($type);

            $tab = array('status' => 1, 'msg' => JText::_('CONFIG_SAVED'), 'data' => json_decode($config));
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function saveconfig(){
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $config = $jinput->getString('config', null);
            $type = $jinput->getString('type', null);

            $saved = $this->m_sync->saveConfig($config,$type);

            $tab = array('status' => 1, 'msg' => JText::_('CONFIG_SAVED'), 'data' => $saved);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getdocuments(){
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $documents = $this->m_sync->getDocuments();

            $tab = array('status' => 1, 'msg' => JText::_('CONFIG_SAVED'), 'data' => $documents);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getemundustags(){
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $tags = $this->m_sync->getEmundusTags();

            $tab = array('status' => 1, 'msg' => JText::_('CONFIG_SAVED'), 'data' => $tags);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatedocumentsync(){
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $did = $jinput->getString('did', null);
            $sync = $jinput->getString('sync', null);

            $updated = $this->m_sync->updateDocumentSync($did,$sync);

            $tab = array('status' => 1, 'msg' => JText::_('CONFIG_SAVED'), 'data' => $updated);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatedocumentsyncmethod(){
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $did = $jinput->getString('did', null);
            $sync_method = $jinput->getString('sync_method', null);

            $updated = $this->m_sync->updateDocumentSyncMethod($did,$sync_method);

            $tab = array('status' => 1, 'msg' => JText::_('CONFIG_SAVED'), 'data' => $updated);
        }
        echo json_encode((object)$tab);
        exit;
    }
}

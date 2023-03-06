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

class EmundusControllerSync extends JControllerLegacy
{
    private $_user = null;
    private $_db = null;
    private $m_sync = null;

    public function __construct($config = array())
    {
        parent::__construct($config);

        require_once(JPATH_COMPONENT . DS . 'models' . DS . 'sync.php');
        require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'access.php');

        $this->_user = JFactory::getSession()->get('emundusUser');
        $this->_db = JFactory::getDBO();
        $this->m_sync = new EmundusModelSync();
    }

    public function getconfig()
    {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_('ACCESS_DENIED'));
        } else {
            $jinput = JFactory::getApplication()->input;
            $type = $jinput->getString('type', null);

            if (!empty($type)) {
                $config = $this->m_sync->getConfig($type);
                $tab = array('status' => 1, 'msg' => JText::_('CONFIG_SAVED'), 'data' => json_decode($config));
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('MISSING_PARAMS'));
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function saveconfig()
    {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $config = $jinput->getString('config', null);
            $type = $jinput->getString('type', null);

            $saved = $this->m_sync->saveConfig($config, $type);

            $tab = array('status' => 1, 'msg' => JText::_('CONFIG_SAVED'), 'data' => $saved);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getaspects()
    {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $aspects = $this->m_sync->getAspects();
            $tab = array('status' => 1, 'msg' => JText::_('ASPECTS_FOUND'), 'data' => $aspects);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function uploadaspectfile()
    {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $file = $_FILES['file'];

            $aspects = $this->m_sync->uploadAspectFile($file);
            $tab = array('status' => !empty($aspects), 'msg' => JText::_('ASPECTS_UPLOADED'), 'data' => $aspects);
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function updateaspectlistfromfile()
    {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $file = $_FILES['file'];

            $aspects = $this->m_sync->updateAspectListFromFile($file);
            $tab = array('status' => !empty($aspects), 'msg' => JText::_('ASPECTS_UPDATED'), 'data' => $aspects);
        }

        echo json_encode((object)$tab);
        exit;
    }


    public function getdocuments()
    {
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

    public function getemundustags()
    {
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

    public function getsetuptags()
    {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'tags.php');
            $helper = new EmundusHelperTags();
            $tags = $helper->getTags();

            $tab = array('status' => 1, 'msg' => JText::_('TAGS_RETRIEVED'), 'data' => $tags);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatedocumentsync()
    {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $did = $jinput->getString('did', null);
            $sync = $jinput->getString('sync', null);

            $updated = $this->m_sync->updateDocumentSync($did, $sync);

            $tab = array('status' => 1, 'msg' => JText::_('CONFIG_SAVED'), 'data' => $updated);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatedocumentsyncmethod()
    {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $did = $jinput->getString('did', null);
            $sync_method = $jinput->getString('sync_method', null);

            $updated = $this->m_sync->updateDocumentSyncMethod($did, $sync_method);

            $tab = array('status' => 1, 'msg' => JText::_('CONFIG_SAVED'), 'data' => $updated);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function issyncmoduleactive()
    {
        $sync_active = $this->m_sync->isSyncModuleActive();

        $tab = array('status' => 1, 'msg' => JText::_('CONFIG_SAVED'), 'data' => $sync_active);
        echo json_encode((object)$tab);
        exit;
    }

    public function getsynctype(): string
    {
        $upload_id = JFactory::getApplication()->input->getInt('upload_id', null);
        $tab = array(
            'status' => 1,
            'msg' => JText::_('SYNC_TYPE_FOUND'),
        );

        if (!empty($upload_id)) {
            $sync_type = $this->m_sync->getSyncType($upload_id);

            if (!empty($sync_type)) {
                $tab['data'] = $sync_type;
            } else {
                $tab['status'] = 0;
                $tab['msg'] = JText::_('SYNC_TYPE_NOT_FOUND');
            }
        } else {
            $tab['status'] = 0;
            $tab['msg'] = JText::_('MISSING_UPLOAD_ID');
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function getsynchronizestate()
    {
        $upload_id = JFactory::getApplication()->input->getInt('upload_id', null);
        $tab = array(
            'status' => 0,
            'msg' => JText::_('SYNC_STATE_NOT_FOUND'),
        );

        if (!empty($upload_id)) {
            $sync_state = $this->m_sync->getUploadSyncState($upload_id);

            $tab['status'] = 1;
            $tab['msg'] = JText::_('SYNC_STATE_FOUND');
            $tab['data'] = $sync_state;
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function synchronizeattachments()
    {
        $updated = array();
        $upload_ids = JFactory::getApplication()->input->get('upload_ids', array(), 'array');
        $upload_ids = json_decode($upload_ids[0]);

        if (!empty($upload_ids) && is_array($upload_ids)) {
            $updated = $this->m_sync->synchronizeAttachments($upload_ids);
            $tab = array('status' => 1, 'msg' => JText::_('CONFIG_SAVED'), 'data' => $updated);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('MISSING_UPLOAD_IDS'));
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function deleteattachments()
    {
        $upload_ids = JFactory::getApplication()->input->get('upload_ids', array(), 'array');
        $upload_ids = json_decode($upload_ids[0]);

        if (!empty($upload_ids) && is_array($upload_ids)) {
            $updated = $this->m_sync->deleteAttachments($upload_ids);
            $tab = array('status' => 1, 'msg' => JText::_('ATTACHMENTS_SYNC_DELETED'), 'data' => $updated);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('MISSING_UPLOAD_IDS'));
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function checkattachmentsexists()
    {
        $upload_ids = JFactory::getApplication()->input->get('upload_ids', array(), 'array');
        $upload_ids = json_decode($upload_ids[0]);

        if (!empty($upload_ids)) {
            $attachments_exists = $this->m_sync->checkAttachmentsExists($upload_ids);

            $tab['status'] = 1;
            $tab['msg'] = JText::_('ATTACHMENT_FOUND');
            $tab['data'] = $attachments_exists;
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function getattachmentaspectsconfig()
    {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status' => 0, 'msg' => JText::_('ACCESS_DENIED'));
        } else {
            $attachmentId = JFactory::getApplication()->input->getInt('attachmentId', 0);

            if (!empty($attachmentId)) {
                $tab = array('status' => 1, 'msg' => JText::_('ATTACHMENT_ASPECTS_CONFIG_FOUND'));
                $tab['data'] = $this->m_sync->getAttachmentAspectsConfig($attachmentId);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('MISSING_ATTACHMENT_ID'));
            }
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function saveattachmentaspectsconfig()
    {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status' => 0, 'msg' => JText::_('ACCESS_DENIED'));
        } else {
            $attachmentId = JFactory::getApplication()->input->getInt('attachmentId', 0);
            $config = JFactory::getApplication()->input->getString('config', '');

            if (!empty($attachmentId)) {
                $tab = array('status' => 1, 'msg' => JText::_('ATTACHMENT_ASPECTS_CONFIG_SAVED'));
                $tab['data'] = $this->m_sync->saveAttachmentAspectsConfig($attachmentId, $config);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('MISSING_PARAMS'));
            }
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function getnodeid()
    {
        $response = array('status' => 0, 'msg' => JText::_('ACCESS_DENIED'));
        $user = JFactory::getUser();

        if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $upload_id = JFactory::getApplication()->input->getInt('uploadId', 0);

            if (!empty($upload_id)) {
                $node_id = $this->m_sync->getNodeId($upload_id);

                $eMConfig = JComponentHelper::getParams('com_emundus');
                $external_storage_ged_alfresco_base_url = $eMConfig->get('external_storage_ged_alfresco_base_url', '');
                $external_storage_ged_alfresco_site = $eMConfig->get('external_storage_ged_alfresco_site', '');

                $response['data'] =  $external_storage_ged_alfresco_base_url . 'share/page/site/' . $external_storage_ged_alfresco_site . '/document-details?nodeRef=workspace://SpacesStore/' . $node_id;
                $response['status'] = 1;
                $response['msg'] = 'Success';
            } else {
                $response['msg'] = JText::_('MISSING_PARAMS');
            }
        }

        echo json_encode((object)$response);
        exit;
    }
}

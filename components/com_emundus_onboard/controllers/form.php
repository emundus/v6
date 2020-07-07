<?php

/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      James Dean
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Program Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusonboardControllerform extends JControllerLegacy {

    var $model = null;
    public function __construct($config = array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        parent::__construct($config);
        $this->model = $this->getModel('form');
    }

    public function getformcount() {
        $user = JFactory::getUser();
        $m_forms = $this->model;
        $filterCount = $_GET['filterCount'];
	    $rechercheCount = $_GET['rechercheCount'];

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $forms = $m_forms->getFormCount($user->id, $filterCount, $rechercheCount);

            if ($forms > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_RETRIEVED'), 'data' => $forms);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_FORM'), 'data' => $forms);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function getallform() {
        $user = JFactory::getUser();
        $m_forms = $this->model;

	    $page = $_GET['offset'];
	    $lim = $_GET['limit'];
        $filter = $_GET['filter'];
	    $sort = $_GET['sort'];
	    $recherche = $_GET['recherche'];

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $forms = $m_forms->getAllForms($user->id, $filter, $sort, $recherche, $lim, $page);

            if (count($forms) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_RETRIEVED'), 'data' => $forms);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_FORM'), 'data' => $forms);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getallformpublished() {
        $user = JFactory::getUser();
        $m_forms = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $forms = $m_forms->getAllFormsPublished();

            if (count($forms) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_RETRIEVED'), 'data' => $forms);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_FORM'), 'data' => $forms);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function deleteform() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('id');
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $forms = $m_form->deleteForm($data);

            if ($forms) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_DELETED'), 'data' => $forms);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_DELETE_FORM'), 'data' => $forms);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function unpublishform() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('id');
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $forms = $m_form->unpublishForm($data);

            if ($forms) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_UNPUBLISHED'), 'data' => $forms);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UNPUBLISH_FORM'), 'data' => $forms);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function publishform() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('id');
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $forms = $m_form->publishForm($data);

            if ($forms) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_PUBLISHED'), 'data' => $forms);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_PUBLISH_FORM'), 'data' => $forms);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function duplicateform() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('id');
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $form = $m_form->duplicateForm($data);

            if ($form) {
                $tab = array('status' => 0, 'msg' => JText::_('FORM_DUPLICATED'), 'data' => $form);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_DUPLICATE_FORM'), 'data' => $form);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function createform() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('body');
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $result = $m_form->createProfile($data, $user->id, $user->name);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_FORM'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function updateform() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('body');
        $pid = $jinput->getRaw('pid');
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $result = $m_form->updateForm($pid, $data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('FORM'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getformbyid() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $id = $jinput->get->get('id');
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $form = $m_form->getFormById($id);
            if (!empty($form)) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_RETRIEVED'), 'data' => $form);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_FORM'), 'data' => $form);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getformcategories() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $form = $m_form->getFormCategories();
            if (!empty($form)) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_RETRIEVED'), 'data' => $form);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_FORM'), 'data' => $form);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getformtypes() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $form = $m_form->getFormTypes();
            if (!empty($form)) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_RETRIEVED'), 'data' => $form);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_FORM'), 'data' => $form);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getalldocuments() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $prid = $_GET['prid'];
        $cid = $_GET['cid'];

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $form = $m_form->getAllDocuments($prid,$cid);
            if (!empty($form)) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENTS_RETRIEVED'), 'data' => $form);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_DOCUMENTS'), 'data' => $form);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getundocuments() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $form = $m_form->getUnDocuments();
            if (!empty($form)) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENTS_RETRIEVED'), 'data' => $form);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_DOCUMENTS'), 'data' => $form);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatedocuments() {
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('body');
        $prid = $jinput->get('prid');
        $cid = $jinput->get('cid');
        $m_form = $this->model;

        $documents = $m_form->updateDocuments($data, $prid, $cid);

        if ($documents) {
            $tab = array('status' => 1, 'msg' => JText::_('DOCUMENTS_UPDATED'), 'data' => $documents);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_DOCUMENTS'), 'data' => $documents);
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function removedocument() {
        $jinput = JFactory::getApplication()->input;
        $did = $jinput->getRaw('did');
        $prid = $jinput->get('prid');
        $cid = $jinput->get('cid');
        $m_form = $this->model;

        $documents = $m_form->removeDocument($did, $prid, $cid);

        if ($documents) {
            $tab = array('status' => 1, 'msg' => JText::_('DOCUMENTS_UPDATED'), 'data' => $documents);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_DOCUMENTS'), 'data' => $documents);
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function reordermenu() {
        $jinput = JFactory::getApplication()->input;
        $menuId = $jinput->get('menuid');
        $allIds = $jinput->getRaw('allids');
        $m_form = $this->model;

        $documents = $m_form->reorderMenuItems($menuId, $allIds);

        if ($documents) {
            $tab = array('status' => 1, 'msg' => JText::_('MENU_REORDERED'), 'data' => $documents);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_REORDER_MENU'), 'data' => $documents);
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function modifymenuitem() {
        $jinput = JFactory::getApplication()->input;
        $itemId = $jinput->get('itemid');
        $itemToChange = $jinput->getRaw('itemtochange');
        $m_form = $this->model;

        $documents = $m_form->modifyMenuItem($itemId, $itemToChange);

        if ($documents) {
            $tab = array('status' => 1, 'msg' => JText::_('MENU_ITEM_MODIFIED'), 'data' => $documents);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_MODIFY_MENU_ITEM'), 'data' => $documents);
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function getmenu() {
        $menuId = $_GET['prid'];
        $m_form = $this->model;

        $documents = $m_form->getMenu($menuId);

        if ($documents) {
            $tab = array('status' => 1, 'msg' => JText::_('MENU_RETRIEVED'), 'data' => $documents);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_MENU'), 'data' => $documents);
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function getmenuitems() {
        $menutype = $_GET['menutype'];
        $m_form = $this->model;

        $documents = $m_form->getMenuItems($menutype);

        if ($documents) {
            $tab = array('status' => 1, 'msg' => JText::_('MENU_ITEMS_RETRIEVED'), 'data' => $documents);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_MENU_ITEMS'), 'data' => $documents);
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function getaliases() {
        $m_form = $this->model;

        $documents = $m_form->getAliases();

        if ($documents) {
            $tab = array('status' => 1, 'msg' => JText::_('MENU_ALIASES_RETRIEVED'), 'data' => $documents);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_MENU_ALIASES'), 'data' => $documents);
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function getgrouprights() {
        $groupId = $_GET['groupid'];
        $m_form = $this->model;

        $documents = $m_form->getGroupRights($groupId);

        if ($documents) {
            $tab = array('status' => 1, 'msg' => JText::_('GROUP_RIGHTS_RETRIEVED'), 'data' => $documents);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_GROUP_RIGHTS'), 'data' => $documents);
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function getactionslabels() {
        $jinput = JFactory::getApplication()->input;
        $actionIds = $jinput->getRaw('actionIds');
        $m_form = $this->model;

        $documents = $m_form->getActionsLabels($actionIds);

        if ($documents) {
            $tab = array('status' => 1, 'msg' => JText::_('ACTIONS_LABELS_RETRIEVED'), 'data' => $documents);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_ACTIONS_LABELS'), 'data' => $documents);
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function updategrouprights() {
        $jinput = JFactory::getApplication()->input;
        $groupRights = $jinput->getRaw('grouprights');
        $group_id = $jinput->get('groupid');
        $m_form = $this->model;

        $documents = $m_form->updateGroupRights($groupRights, $group_id);

        if ($documents) {
            $tab = array('status' => 1, 'msg' => JText::_('GROUP_RIGHTS_UPDATED'), 'data' => $documents);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_GROUP_RIGHTS'), 'data' => $documents);
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function getgroupids() {
        $m_form = $this->model;
        $campaign_id = $_GET['campaign'];

        $documents = $m_form->getGroupsIds();
        $groups = $m_form->getGroupsCampaign($campaign_id);
        $max = $m_form->maxGroup();

        if ($documents && $groups && $max) {
            $tab = array('status' => 1, 'msg' => JText::_('GROUP_IDS_RETRIEVED'), 'data' => $documents, 'max' => $max, 'groups' => $groups);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_GROUP_IDS'), 'data' => $documents, 'max' => $max, 'groups' => $groups);
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function deletegroup() {
        $m_form = $this->model;
        $groupid = $_POST['groupid'];

        $documents = $m_form->deleteGroup($groupid);

        if ($documents) {
            $tab = array('status' => 1, 'msg' => JText::_('GROUP_DELETED'), 'data' => $documents);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_DELETE_GROUP'), 'data' => $documents);
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function addgroup() {
        $m_form = $this->model;
        $jinput = JFactory::getApplication()->input;
        $groupid = $jinput->get('groupid');
        $campaign_id = $jinput->get('campaignid');
        $data = $jinput->getRaw('data');

        $documents = $m_form->addGroup($groupid, $campaign_id);
        $dataGroup = $m_form->updateGroupRights($data, $groupid);

        if ($documents) {
            $tab = array('status' => 1, 'msg' => JText::_('GROUP_DELETED'), 'data' => $documents, 'dataGroup' => $dataGroup);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_DELETE_GROUP'), 'data' => $documents, 'dataGroup' => $dataGroup);
        }

        echo json_encode((object)$tab);
        exit;
    }


     public function getFormsByProfileId() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $profile_id = $jinput->getRaw('profile_id');

        $m_form = $this->model;
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $form = $m_form->getFormsByProfileId($profile_id);

            if (!empty($form)) {
                $tab = array('status' => 1, 'msg' => 'worked', 'data' => $form);
            } else {
                $tab = array('status' => 0, 'msg' => 'Doesn t worked', 'data' => $form);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }
    public function getProfileLabelByProfileId() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $profile_id = $jinput->getRaw('profile_id');

        $m_form = $this->model;
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $form = $m_form->getProfileLabelByProfileId($profile_id);
            if (!empty($form)) {
                $tab = array('status' => 1, 'msg' => 'worked', 'data' => $form);
            } else {
                $tab = array('status' => 0, 'msg' => 'Doesn t worked', 'data' => $form);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilesbyform() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $profile_id = $jinput->getRaw('pid');

        $m_form = $this->model;
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $files = $m_form->getFilesByProfileId($profile_id);
            $tab = array('status' => 1, 'msg' => 'worked', 'data' => $files);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getassociatedcampaign() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $profile_id = $jinput->getRaw('pid');

        $m_form = $this->model;
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $campaigns = $m_form->getAssociatedCampaign($profile_id);
            $tab = array('status' => 1, 'msg' => 'worked', 'data' => $campaigns);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function affectcampaignstoform() {
        $user = JFactory::getUser();
        $m_form = $this->model;
        $jinput = JFactory::getApplication()->input;
        $prid = $jinput->getRaw('prid');
        $campaigns = $jinput->getRaw('campaigns');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->affectCampaignsToForm($prid, $campaigns);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }
}


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
 * Form Controller
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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_forms = $this->model;
	        $jinput = JFactory::getApplication()->input;
	        $filterCount = $jinput->getString('filterCount');
	        $rechercheCount = $jinput->getString('rechercheCount');

            $forms = $m_forms->getFormCount($filterCount, $rechercheCount);

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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_forms = $this->model;
	        $jinput = JFactory::getApplication()->input;

	        $page = $jinput->getInt('page');
	        $lim = $jinput->getInt('lim');
	        $filter = $jinput->getString('filter');
	        $sort = $jinput->getString('sort');
	        $recherche = $jinput->getString('recherche');

            $forms = $m_forms->getAllForms($filter, $sort, $recherche, $lim, $page);
            $formscanbeupdated = $m_forms->getFormsUpdated();

            if (count($forms) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_RETRIEVED'), 'data' => $forms, 'forms_updating' => $formscanbeupdated);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_FORM'), 'data' => $forms);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function getallformpublished() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $m_forms = $this->model;
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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getInt('id');
	        $m_form = $this->model;

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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getInt('id');
	        $m_form = $this->model;

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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getInt('id');
	        $m_form = $this->model;

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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getInt('id');
	        $m_form = $this->model;

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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getRaw('body');
	        $m_form = $this->model;

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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getRaw('body');
	        $pid = $jinput->getInt('pid');
	        $m_form = $this->model;

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

    public function updateformlabel() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

            $jinput = JFactory::getApplication()->input;
            $prid = $jinput->getInt('prid');
            $label = $jinput->getString('label');
            $m_form = $this->model;

            $result = $m_form->updateFormLabel($prid, $label);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_UPDATED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('FORM_NOT_UPDATED'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function getformbyid() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $id = $jinput->getInt('id');
	        $m_form = $this->model;

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


    public function getalldocuments() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_form = $this->model;

	        $jinput = JFactory::getApplication()->input;
	        $prid = $jinput->getInt('prid');
	        $cid = $jinput->getInt('cid');

            $form = $m_form->getAllDocuments($prid, $cid);
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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_form = $this->model;

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

    public function updatemandatory() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

            $jinput = JFactory::getApplication()->input;
            $did = $jinput->getInt('did');
            $prid = $jinput->getInt('prid');
            $cid = $jinput->getInt('cid');
            $m_form = $this->model;

            $documents = $m_form->updateMandatory($did,$prid,$cid);

            if ($documents) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENTS_UPDATED'), 'data' => $documents);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_DOCUMENTS'), 'data' => $documents);
            }
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function adddocument() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

            $jinput = JFactory::getApplication()->input;
            $did = $jinput->getInt('did');
            $prid = $jinput->getInt('prid');
            $cid = $jinput->getInt('cid');
            $m_form = $this->model;

            $documents = $m_form->addDocument($did, $prid, $cid);

            if ($documents) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENTS_UPDATED'), 'data' => $documents);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_DOCUMENTS'), 'data' => $documents);
            }
        }

        echo json_encode((object)$tab);
        exit;
    }


    public function removedocument() {

	    $user = JFactory::getUser();

	    if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
		    $result = 0;
		    $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
	    } else {

	        $jinput = JFactory::getApplication()->input;
	        $did = $jinput->getInt('did');
	        $prid = $jinput->getInt('prid');
	        $cid = $jinput->getInt('cid');
	        $m_form = $this->model;

	        $documents = $m_form->removeDocument($did, $prid, $cid);

	        if ($documents) {
	            $tab = array('status' => 1, 'msg' => JText::_('DOCUMENTS_UPDATED'), 'data' => $documents);
	        } else {
	            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_DOCUMENTS'), 'data' => $documents);
	        }
        }

        echo json_encode((object)$tab);
        exit;
    }


    public function deletedocument() {

        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

            $jinput = JFactory::getApplication()->input;
            $did = $jinput->getInt('did');
            $m_form = $this->model;

            $state = $m_form->deleteDocument($did);

            $tab = array('status' => $state, 'msg' => JText::_('DOCUMENT_DELETED'));

        }

        echo json_encode((object)$tab);
        exit;
    }


     public function getFormsByProfileId() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $profile_id = $jinput->getInt('profile_id');

	        $m_form = $this->model;
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

    public function getDocuments() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

            $jinput = JFactory::getApplication()->input;
            $profile_id = $jinput->getInt('pid');

            $m_form = $this->model;
            $documents = $m_form->getDocumentsByProfile($profile_id);

            if (!empty($documents)) {
                $tab = array('status' => 1, 'msg' => 'worked', 'data' => $documents);
            } else {
                $tab = array('status' => 0, 'msg' => 'Doesn t worked', 'data' => $documents);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function reorderDocuments() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

            $jinput = JFactory::getApplication()->input;
            $documents = $jinput->getRaw('documents');

            $m_form = $this->model;
            $documents = $m_form->reorderDocuments($documents);

            if (!empty($documents)) {
                $tab = array('status' => 1, 'msg' => 'worked', 'data' => $documents);
            } else {
                $tab = array('status' => 0, 'msg' => 'Doesn t worked', 'data' => $documents);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function removeDocumentFromProfile() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

            $jinput = JFactory::getApplication()->input;
            $did = $jinput->getInt('did');

            $m_form = $this->model;
            $result = $m_form->removeDocumentFromProfile($did);

            if (!empty($result)) {
                $tab = array('status' => 1, 'msg' => 'worked', 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => 'Doesn t worked', 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getgroupsbyform() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

            $jinput = JFactory::getApplication()->input;
            $form_id = $jinput->getInt('form_id');

            $m_form = $this->model;
            $form = $m_form->getGroupsByForm($form_id);

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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;
	        $profile_id = $jinput->getInt('profile_id');

	        $m_form = $this->model;
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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;
	        $profile_id = $jinput->getInt('pid');

	        $m_form = $this->model;
            $files = $m_form->getFilesByProfileId($profile_id);
            $tab = array('status' => 1, 'msg' => 'worked', 'data' => $files);
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function getassociatedcampaign() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;
	        $profile_id = $jinput->getInt('pid');

	        $m_form = $this->model;
            $campaigns = $m_form->getAssociatedCampaign($profile_id);
            $tab = array('status' => 1, 'msg' => 'worked', 'data' => $campaigns);
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function affectcampaignstoform() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $m_form = $this->model;
	        $jinput = JFactory::getApplication()->input;
	        $prid = $jinput->getInt('prid');
	        $campaigns = $jinput->getRaw('campaigns');

            $changeresponse = $m_form->affectCampaignsToForm($prid, $campaigns);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function getsubmittionpage(){
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $m_form = $this->model;
            $jinput = JFactory::getApplication()->input;
            $prid = $jinput->getInt('prid');

            $submittionpage = $m_form->getSubmittionPage($prid);
        }
        echo json_encode((object)$submittionpage);
        exit;
    }

    public function getAccess(){
        $user = JFactory::getUser();

        if (EmundusonboardHelperAccess::asAdministratorAccessLevel($user->id)) {
            $response = array('status' => 1, 'msg' => JText::_("ACCESS_SYSADMIN"), 'access' => true);
        } else {
            $response = array('status' => 0, 'msg' => JText::_("ACCESS_REFUSED"), 'access' => false);
        }
        echo json_encode((object)$response);
        exit;
    }
}


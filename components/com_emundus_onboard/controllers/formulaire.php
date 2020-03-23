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
class EmundusonboardControllerformulaire extends JControllerLegacy {

    var $model = null;
    public function __construct($config = array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        parent::__construct($config);
        $this->model = $this->getModel('form');
    }

    public function getformulairecount() {
        $user = JFactory::getUser();
        $m_forms = $this->model;
        $filterCount = $_GET['filterCount'];

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $forms = $m_forms->getFormCount($user->id, $filterCount);

            if ($forms > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_RETRIEVED'), 'data' => $forms);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_FORM'), 'data' => $forms);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function getallformulaire() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $m_forms = $this->model;

        $offset = $jinput->getRaw('offset', 0);
        $limit = $jinput->getRaw('limit', 0);
        $filter = $_GET['filter'];

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $forms = $m_forms->getAllForms($user->id, $limit, $offset, $filter);

            if (count($forms) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_RETRIEVED'), 'data' => $forms);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_FORM'), 'data' => $forms);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function deleteformulaire() {
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

    public function unpublishformulaire() {
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

    public function publishformulaire() {
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

    public function duplicateformulaire() {
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
                $this->getallforms();
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_DUPLICATE_FORM'), 'data' => $form);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function createformulaire() {

        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('body');
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $result = $m_form->createForm($data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_FORM'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function updateformulaire() {

        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('body');
        $code = $jinput->getRaw('code');
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $result = $m_form->updateForm($code, $data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('FORM'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getformulairebyid() {
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

    public function getformulairecategories() {
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

    public function getformulairetypes() {
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
}


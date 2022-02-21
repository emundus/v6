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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * FormBuilder Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusonboardControllerformbuilder extends JControllerLegacy {

    var $model = null;

    public function __construct($config = array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        parent::__construct($config);
        $this->model = $this->getModel('formbuilder');
    }

    /**
     * Update order of elements in a group
     *
     * @throws Exception
     */
    public function updateOrder() {

        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $elements = $jinput->getRaw('elements');
        $group_id = $jinput->getInt('group_id');
        $moved_el= $jinput->getRaw('moved_el');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $update = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $update = $m_form->updateOrder($elements, $group_id, $user->id, $moved_el);
        }
        echo json_encode((object)$update);
        exit;
    }

    /**
     * Update require of an element
     *
     * @throws Exception
     */
    public function changerequire() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $element = $jinput->getRaw('element');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->ChangeRequire($element,  $user->id);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }


    public function publishunpublishelement() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $element = $jinput->getInt('element');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $update = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $update = $m_form->publishUnpublishElement($element);
        }
        echo json_encode((object)$update);
        exit;
    }


    public function updateparams() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $element = $jinput->getRaw('element');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->UpdateParams($element,  $user->id);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function duplicateelement() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $eid = $jinput->getInt('id');
        $group = $jinput->getInt('group');
        $old_group = $jinput->getInt('old_group');
        $form_id = $jinput->getInt('form_id');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->duplicateElement($eid,$group,$old_group,$form_id);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    /**
     * Update translations of an element
     *
     * @throws Exception
     */
    public function formsTrad() {

        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $element = $jinput->getInt('element');
        $group = $jinput->getInt('group');
        $page = $jinput->getInt('page');
        $labelTofind = $jinput->getString('labelTofind');
        $newLabel = $jinput->getRaw('NewSubLabel');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $results = $m_form->formsTrad($labelTofind, $newLabel, $element, $group, $page);
            $changeresponse = array('status' => 1, 'msg' => 'Traductions effectués avec succès', 'data' => $results);
        }

        echo json_encode((object)$changeresponse);
        exit;
    }

    public function updateelementlabelwithouttranslation() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $eid = $jinput->getInt('eid');
            $label = $jinput->getString('label');

            $changeresponse = $m_form->updateElementWithoutTranslation($eid,$label);
        }

        echo json_encode((object)$changeresponse);
        exit;
    }

    public function updategrouplabelwithouttranslation() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $gid = $jinput->getInt('gid');
            $label = $jinput->getString('label');

            $changeresponse = $m_form->updateGroupWithoutTranslation($gid,$label);
        }

        echo json_encode((object)$changeresponse);
        exit;
    }

    public function updatepagelabelwithouttranslation() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $pid = $jinput->getInt('pid');
            $label = $jinput->getString('label');

            $changeresponse = $m_form->updatePageWithoutTranslation($pid,$label);
        }

        echo json_encode((object)$changeresponse);
        exit;
    }

    public function updatepageintrowithouttranslation() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $pid = $jinput->getInt('pid');
            $intro = $jinput->getString('label');

            $changeresponse = $m_form->updatePageIntroWithoutTranslation($pid,$intro);
        }

        echo json_encode((object)$changeresponse);
        exit;
    }

    /**
     * Return translation in current language of an array
     *
     * @throws Exception
     */
    public function getJTEXTA() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $toJTEXT = $jinput->getString('toJTEXT');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $getJtext = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $getJtext = $m_form->getJTEXTA($toJTEXT);
        }
        echo json_encode((object)$getJtext);
        exit;
    }


    /**
     * Return translation in current language of an unique text
     *
     * @throws Exception
     */
    public function getJTEXT() {

        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $toJTEXT = $jinput->getString('toJTEXT');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $getJtext = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $getJtext = $m_form->getJTEXT($toJTEXT);
        }
        echo json_encode((string)$getJtext);
        exit;
    }

    /**
     * Return translations of all elements present
     *
     * @throws Exception
     */
    public function getalltranslations() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $toJTEXT = $jinput->getString('toJTEXT');

        // Prepare languages
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_files = array();
        $Content_Folder = array();

        $languages = JLanguageHelper::getLanguages();

        foreach ($languages as $language) {
            $path_to_files[$language->sef] = $path_to_file . $language->lang_code . '.override.ini';
            $Content_Folder[$language->sef] = file_get_contents($path_to_files[$language->sef]);
        }

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $getJtext = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $getJtext = new stdClass();
            foreach ($languages as $language) {
                $getJtext->{$language->sef} = $m_form->getTranslation($toJTEXT,$language->lang_code);
            }
        }
        echo json_encode((object)$getJtext);
        exit;
    }


    public function createMenu() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $label = $jinput->getRaw('label');
        $intro = $jinput->getRaw('intro');
        $prid = $jinput->getInt('prid');
        $modelid = $jinput->getInt('modelid');
        $template = $jinput->getString('template');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            if ($modelid != -1) {
                $changeresponse = $m_form->createMenuFromTemplate($label, $intro, $modelid, $prid);
            } else {
                $changeresponse = $m_form->createMenu($label, $intro, $prid, $template);
            }
        }
        echo json_encode((object)$changeresponse);
        exit;
    }


    public function deletemenu() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $mid = $jinput->getInt('mid');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->deleteMenu($mid);
        }

        echo json_encode((object)$changeresponse);
        exit;
    }


    public function savemenuastemplate() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $menu = $jinput->getRaw('menu');
        $template = $jinput->getString('template');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->saveAsTemplate($menu,$template);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }


    public function createsimplegroup(){
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $fid = $jinput->getInt('fid');
        if($jinput->getRaw('label')){
            $label=$jinput->getRaw('label');
        } else{
            $label = array(
                'fr' => 'Nouveau groupe',
                'en' => 'New group'
            );
        }


        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->createGroup($label,$fid);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }


    public function deleteGroup() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $gid = $jinput->getInt('gid');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->deleteGroup($gid);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    /**
     * Return all wanted properties of a new created element
     *
     * @throws Exception
     */
    public function getElement() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $element = $jinput->getInt('element');
        $gid = $jinput->getInt('gid');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->getElement($element, $gid);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function retriveElementFormAssociatedDoc(){
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $docid = $jinput->getInt('docid');
        $gid = $jinput->getInt('gid');
        $changeresponse = $m_form->retriveElementFormAssociatedDoc($gid, $docid);
        echo json_encode((object)$changeresponse);
        exit;
    }


    public function createsimpleelement() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $gid = $jinput->getInt('gid');
        $plugin = $jinput->getString('plugin');
        if ($jinput->getString('attachementId')){

            $attachementId = $jinput->getString('attachementId');


        }


        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            if($attachementId) {
                $changeresponse = $m_form->createSimpleElement($gid, $plugin, $attachementId);
            } else {
                $changeresponse = $m_form->createSimpleElement($gid, $plugin,0);

            }
        }
        echo json_encode((object)$changeresponse);
        exit;
    }
    public function createsectionsimpleelements() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $gid = $jinput->getInt('gid');
        $plugin = $jinput->getString('plugins');



        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

                $changeresponse = $m_form->createSectionSimpleElements($gid,$plugin);


        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    /**
     * Create an element for evaluation grids
     *
     * @throws Exception
     */
    public function createcriteria() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $gid = $jinput->getInt('gid');
        $plugin = $jinput->getString('plugin');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->createSimpleElement($gid, $plugin, null, 1);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }


    public function deleteElement() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $element = $jinput->getInt('element');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->deleteElement($element);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }


    public function reordermenu() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $rgt = $jinput->getInt('rgt');
        $link = $jinput->getString('link');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->reorderMenu($link, $rgt);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }


    public function getGroupOrdering() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $gid = $jinput->getInt('gid');
        $fid = $jinput->getInt('fid');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->getGroupOrdering($gid,$fid);
        }
        echo $changeresponse;
        exit;
    }

    /**
     * Reorder groups of a page
     *
     * @throws Exception
     */
    public function reordergroups() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $groups = $jinput->getRaw('groups');
        $fid = $jinput->getInt('fid');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            foreach ($groups as $group) {
                $changeresponse[] = $m_form->reorderGroup($group['id'], $fid, $group['order']);
            }
        }

        echo json_encode((object)$changeresponse);
        exit;
    }

    /**
     * Return the pages templates
     */
    public function getPagesModel() {

        $user = JFactory::getUser();
        $m_form = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->getPagesModel();
        }

        echo json_encode((object)$changeresponse);
        exit;
    }

    /**
     * Check the visibility of groups linked to a campaign
     *
     * @throws Exception
     */
    public function checkconstraintgroup() {

        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $cid = $jinput->getInt('cid');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $m_form = $this->model;
            $visibility = $m_form->checkConstraintGroup($cid);

            $tab = array('status' => 1, 'msg' => 'worked', 'data' => $visibility);
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Check the visibility of groups linked to a campaign
     *
     * @throws Exception
     */
    public function checkvisibility() {
        $user = JFactory::getUser();

        $m_form = $this->model;
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $group = $jinput->getInt('group');
            $cid = $jinput->getInt('cid');
            $visibility = $m_form->checkVisibility($group,$cid);

            $tab = array('status' => 1, 'msg' => 'worked', 'data' => $visibility);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getdatabasesjoin() {
        $user = JFactory::getUser();

        $m_form = $this->model;
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $databases = $m_form->getDatabasesJoin();

            $tab = array('status' => 1, 'msg' => 'worked', 'data' => $databases);
        }
        echo json_encode((object)$tab);
        exit;
    }
    public function getdatabasesjoinOrdonancementColomns() {
        $user = JFactory::getUser();

        $m_form = $this->model;
        $jinput = JFactory::getApplication()->input;
        $data_base_name = $jinput->getString('database_name');
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $database_name_columns = $m_form->getDatabasesJoinOrdonancementColumns($data_base_name);

            $tab = array('status' => 1, 'msg' => 'worked', 'data' => $database_name_columns);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function enablegrouprepeat() {
        $user = JFactory::getUser();

        $m_form = $this->model;
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $gid = $jinput->getInt('gid');

            $state = $m_form->enableRepeatGroup($gid);

            $tab = array('status' => $state, 'msg' => 'worked');
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function disablegrouprepeat() {
        $user = JFactory::getUser();

        $m_form = $this->model;
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $gid = $jinput->getInt('gid');

            $state = $m_form->disableRepeatGroup($gid);

            $tab = array('status' => $state, 'msg' => 'worked');
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function displayhidegroup() {
        $user = JFactory::getUser();

        $m_form = $this->model;
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $gid = $jinput->getInt('gid');

            $state = $m_form->displayHideGroup($gid);

            $tab = array('status' => $state, 'msg' => 'worked');
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatemenulabel(){
        $user = JFactory::getUser();

        $m_form = $this->model;
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $label = $jinput->getRaw('label');
            $pid = $jinput->getString('pid');

            $state = $m_form->updateMenuLabel($label,$pid);

            $tab = array('status' => $state, 'msg' => 'worked');
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function gettestingparams(){
        $user = JFactory::getUser();

        $m_form = $this->model;
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $prid = $jinput->getInt('prid');
            $campaign_files = $m_form->getFormTesting($prid,$user->id);
            $tab = array('status' => true, 'user' => $user, 'campaign_files' => $campaign_files);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function createtestingfile(){
        $user = JFactory::getUser();

        $m_form = $this->model;
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $cid = $jinput->getInt('cid');
            $fnum = $m_form->createTestingFile($cid,$user->id);
            $tab = array('status' => true,'fnum' => $fnum);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function deletetestingfile(){
        $user = JFactory::getUser();

        $m_form = $this->model;
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $fnum = $jinput->getString('file');
            $status = $m_form->deleteFormTesting($fnum,$user->id);
            $tab = array('status' => $status,'userid' => $user->id);
        }
        echo json_encode((object)$tab);
        exit;
    }
}



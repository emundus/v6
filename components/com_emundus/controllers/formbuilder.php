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
class EmundusControllerFormbuilder extends JControllerLegacy {

    var $m_formbuilder = null;

    public function __construct($config = array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        $this->m_formbuilder = $this->getModel('formbuilder');

        parent::__construct($config);
    }

    public function updateOrder() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $update = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $elements = $jinput->getRaw('elements');
            $group_id = $jinput->getInt('group_id');
            $moved_el= $jinput->getRaw('moved_el');

            $update = $this->m_formbuilder->updateOrder($elements, $group_id, $user->id, $moved_el);
        }
        echo json_encode((object)$update);
        exit;
    }

    public function changerequire() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $element = $jinput->getRaw('element');

            $changeresponse = $this->m_formbuilder->ChangeRequire($element,  $user->id);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }


    public function publishunpublishelement() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $update = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $element = $jinput->getInt('element');

            $update = $this->m_formbuilder->publishUnpublishElement($element);
        }
        echo json_encode((object)$update);
        exit;
    }


    public function updateparams() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $element = $jinput->getRaw('element');

            $changeresponse = $this->m_formbuilder->UpdateParams($element,  $user->id);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function duplicateelement() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $eid = $jinput->getInt('id');
            $group = $jinput->getInt('group');
            $old_group = $jinput->getInt('old_group');
            $form_id = $jinput->getInt('form_id');

            $changeresponse = $this->m_formbuilder->duplicateElement($eid,$group,$old_group,$form_id);
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

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $element = $jinput->getInt('element');
            $group = $jinput->getInt('group');
            $page = $jinput->getInt('page');
            $labelTofind = $jinput->getString('labelTofind');
            $newLabel = $jinput->getRaw('NewSubLabel');

            $results = $this->m_formbuilder->formsTrad($labelTofind, $newLabel, $element, $group, $page);
            $changeresponse = array('status' => 1, 'msg' => 'Traductions effectués avec succès', 'data' => $results);
        }

        echo json_encode((object)$changeresponse);
        exit;
    }

    public function updateelementlabelwithouttranslation() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $eid = $jinput->getInt('eid');
            $label = $jinput->getString('label');

            $changeresponse = $this->m_formbuilder->updateElementWithoutTranslation($eid,$label);
        }

        echo json_encode((object)$changeresponse);
        exit;
    }

    public function updategrouplabelwithouttranslation() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $gid = $jinput->getInt('gid');
            $label = $jinput->getString('label');

            $changeresponse = $this->m_formbuilder->updateGroupWithoutTranslation($gid,$label);
        }

        echo json_encode((object)$changeresponse);
        exit;
    }

    public function updatepagelabelwithouttranslation() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $pid = $jinput->getInt('pid');
            $label = $jinput->getString('label');

            $changeresponse = $this->m_formbuilder->updatePageWithoutTranslation($pid,$label);
        }

        echo json_encode((object)$changeresponse);
        exit;
    }

    public function updatepageintrowithouttranslation() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $pid = $jinput->getInt('pid');
            $intro = $jinput->getString('label');

            $changeresponse = $this->m_formbuilder->updatePageIntroWithoutTranslation($pid,$intro);
        }

        echo json_encode((object)$changeresponse);
        exit;
    }

    public function getJTEXTA() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $getJtext = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $toJTEXT = $jinput->getString('toJTEXT');

            $getJtext = $this->m_formbuilder->getJTEXTA($toJTEXT);
        }
        echo json_encode((object)$getJtext);
        exit;
    }

    public function getJTEXT() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $getJtext = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $toJTEXT = $jinput->getString('toJTEXT');

            $getJtext = $this->m_formbuilder->getJTEXT($toJTEXT);
        }
        echo json_encode((string)$getJtext);
        exit;
    }

    public function getalltranslations() {
        $user = JFactory::getUser();

        // Prepare languages
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_files = array();
        $Content_Folder = array();

        $languages = JLanguageHelper::getLanguages();

        foreach ($languages as $language) {
            $path_to_files[$language->sef] = $path_to_file . $language->lang_code . '.override.ini';
            $Content_Folder[$language->sef] = file_get_contents($path_to_files[$language->sef]);
        }

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $getJtext = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $toJTEXT = $jinput->getString('toJTEXT');

            $getJtext = new stdClass();
            foreach ($languages as $language) {
                $getJtext->{$language->sef} = $this->m_formbuilder->getTranslation($toJTEXT,$language->lang_code);
            }
        }
        echo json_encode((object)$getJtext);
        exit;
    }


    public function createMenu() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $label = $jinput->getRaw('label');
            $intro = $jinput->getRaw('intro');
            $prid = $jinput->getInt('prid');
            $modelid = $jinput->getInt('modelid');
            $template = $jinput->getString('template');

            if ($modelid != -1) {
                $changeresponse = $this->m_formbuilder->createMenuFromTemplate($label, $intro, $modelid, $prid);
            } else {
                $changeresponse = $this->m_formbuilder->createMenu($label, $intro, $prid, $template);
            }
        }
        echo json_encode((object)$changeresponse);
        exit;
    }


    public function deletemenu() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $mid = $jinput->getInt('mid');

            $changeresponse = $this->m_formbuilder->deleteMenu($mid);
        }

        echo json_encode((object)$changeresponse);
        exit;
    }


    public function savemenuastemplate() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $menu = $jinput->getRaw('menu');
            $template = $jinput->getString('template');

            $changeresponse = $this->m_formbuilder->saveAsTemplate($menu,$template);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }


    public function createsimplegroup(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
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

            $changeresponse = $this->m_formbuilder->createGroup($label,$fid);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }


    public function deleteGroup() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $gid = $jinput->getInt('gid');

            $changeresponse = $this->m_formbuilder->deleteGroup($gid);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function getElement() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $element = $jinput->getInt('element');
            $gid = $jinput->getInt('gid');

            $changeresponse = $this->m_formbuilder->getElement($element, $gid);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function retriveElementFormAssociatedDoc(){
        $jinput = JFactory::getApplication()->input;

        $docid = $jinput->getInt('docid');
        $gid = $jinput->getInt('gid');

        $changeresponse = $this->m_formbuilder->retriveElementFormAssociatedDoc($gid, $docid);
        echo json_encode((object)$changeresponse);
        exit;
    }


    public function createsimpleelement() {
        $user = JFactory::getUser();


        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $gid = $jinput->getInt('gid');
            $plugin = $jinput->getString('plugin');
            if ($jinput->getString('attachementId')){
                $attachementId = $jinput->getString('attachementId');
            }

            if($attachementId) {
                $changeresponse = $this->m_formbuilder->createSimpleElement($gid, $plugin, $attachementId);
            } else {
                $changeresponse = $this->m_formbuilder->createSimpleElement($gid, $plugin,0);

            }
        }
        echo json_encode((object)$changeresponse);
        exit;
    }
    public function createsectionsimpleelements() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $gid = $jinput->getInt('gid');
            $plugin = $jinput->getString('plugins');

            $changeresponse = $this->m_formbuilder->createSectionSimpleElements($gid,$plugin);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function createcriteria() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $gid = $jinput->getInt('gid');
            $plugin = $jinput->getString('plugin');

            $changeresponse = $this->m_formbuilder->createSimpleElement($gid, $plugin, null, 1);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }


    public function deleteElement() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $element = $jinput->getInt('element');

            $changeresponse = $this->m_formbuilder->deleteElement($element);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }


    public function reordermenu() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $rgt = $jinput->getInt('rgt');
            $link = $jinput->getString('link');

            $changeresponse = $this->m_formbuilder->reorderMenu($link, $rgt);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }


    public function getGroupOrdering() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $gid = $jinput->getInt('gid');
            $fid = $jinput->getInt('fid');

            $changeresponse = $this->m_formbuilder->getGroupOrdering($gid,$fid);
        }
        echo $changeresponse;
        exit;
    }

    public function reordergroups() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $groups = $jinput->getRaw('groups');
            $fid = $jinput->getInt('fid');

            foreach ($groups as $group) {
                $changeresponse[] = $this->m_formbuilder->reorderGroup($group['id'], $fid, $group['order']);
            }
        }

        echo json_encode((object)$changeresponse);
        exit;
    }

    public function getPagesModel() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $this->m_formbuilder->getPagesModel();
        }

        echo json_encode((object)$changeresponse);
        exit;
    }

    public function checkconstraintgroup() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $cid = $jinput->getInt('cid');

            $visibility = $this->m_formbuilder->checkConstraintGroup($cid);

            $tab = array('status' => 1, 'msg' => 'worked', 'data' => $visibility);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function checkvisibility() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $group = $jinput->getInt('group');
            $cid = $jinput->getInt('cid');

            $visibility = $this->m_formbuilder->checkVisibility($group,$cid);

            $tab = array('status' => 1, 'msg' => 'worked', 'data' => $visibility);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getdatabasesjoin() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $databases = $this->m_formbuilder->getDatabasesJoin();

            $tab = array('status' => 1, 'msg' => 'worked', 'data' => $databases);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getdatabasesjoinOrdonancementColomns() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $data_base_name = $jinput->getString('database_name');

            $database_name_columns = $this->m_formbuilder->getDatabasesJoinOrdonancementColumns($data_base_name);

            $tab = array('status' => 1, 'msg' => 'worked', 'data' => $database_name_columns);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function enablegrouprepeat() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $gid = $jinput->getInt('gid');

            $state = $this->m_formbuilder->enableRepeatGroup($gid);

            $tab = array('status' => $state, 'msg' => 'worked');
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function disablegrouprepeat() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $gid = $jinput->getInt('gid');

            $state = $this->m_formbuilder->disableRepeatGroup($gid);

            $tab = array('status' => $state, 'msg' => 'worked');
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function displayhidegroup() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $gid = $jinput->getInt('gid');

            $state = $this->m_formbuilder->displayHideGroup($gid);

            $tab = array('status' => $state, 'msg' => 'worked');
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatemenulabel(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $label = $jinput->getRaw('label');
            $pid = $jinput->getString('pid');

            $state = $this->m_formbuilder->updateMenuLabel($label,$pid);

            $tab = array('status' => $state, 'msg' => 'worked');
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function gettestingparams(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $prid = $jinput->getInt('prid');

            $campaign_files = $this->m_formbuilder->getFormTesting($prid,$user->id);

            $tab = array('status' => true, 'user' => $user, 'campaign_files' => $campaign_files);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function createtestingfile(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $cid = $jinput->getInt('cid');

            $fnum = $this->m_formbuilder->createTestingFile($cid,$user->id);

            $tab = array('status' => true,'fnum' => $fnum);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function deletetestingfile(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $fnum = $jinput->getString('file');

            $status = $this->m_formbuilder->deleteFormTesting($fnum,$user->id);

            $tab = array('status' => $status,'userid' => $user->id);
        }
        echo json_encode((object)$tab);
        exit;
    }
}



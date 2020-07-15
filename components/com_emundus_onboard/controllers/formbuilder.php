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
 * formuairez Controller
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
        $group_id = $jinput->get('group_id');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $update = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $update = $m_form->updateOrder($elements, $group_id, $user->id);
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

    /**
     * Update the field type of an element
     *
     * @throws Exception
     */
    public function updatefieldtype() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $element = $jinput->getRaw('element');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->UpdateFieldType($element,  $user->id);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    /**
     * Update the publish status of an element
     *
     * @throws Exception
     */
    public function publishunpublishelement() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $element = $jinput->getRaw('element');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $update = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $update = $m_form->publishUnpublishElement($element);
        }
        echo json_encode((object)$update);
        exit;
    }

    /**
     * Update global params of an element
     *
     * @throws Exception
     */
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

    /**
     * Update sublabels of an element
     *
     * @throws Exception
     */
     public function SubLabelsxValues() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $element = $jinput->getRaw('element');
        $newLabel = $jinput->getRaw('NewSubLabel');

        $lang = JFactory::getLanguage();
        $locallang = $lang->getTag();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
	        $result = 0;
	        $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->SubLabelsxValues($element,  $locallang, $newLabel,  $user->id);
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
        $labelTofind = $jinput->getRaw('labelTofind');
        $newLabel = $jinput->getRaw('NewSubLabel');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
	        $result = 0;
	        $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->formsTrad($labelTofind, $newLabel);
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
        $toJTEXT = $jinput->getRaw('toJTEXT');

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
        $toJTEXT = $jinput->getRaw('toJTEXT');

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
        $toJTEXT = $jinput->getRaw('toJTEXT');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $getJtext = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $getJtext = new stdClass();
            $getJtext->fr = $m_form->getTranslationFr($toJTEXT);
            $getJtext->en = $m_form->getTranslationEn($toJTEXT);
        }
        echo json_encode((object)$getJtext);
        exit;
    }

    /**
     * Create a new page in a form
     *
     * @throws Exception
     */
     public function createMenu() {
         $user = JFactory::getUser();
         $m_form = $this->model;

         $jinput = JFactory::getApplication()->input;
         $label = $jinput->getRaw('label');
         $intro = $jinput->getRaw('intro');
         $prid = $jinput->getRaw('prid');
         $modelid = $jinput->getRaw('modelid');
         $template = $jinput->getRaw('template');

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

    /**
     * Delete a menu
     *
     * @throws Exception
     */
    public function deletemenu() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $mid = $jinput->getRaw('mid');

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
        $template = $jinput->getRaw('template');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->saveAsTemplate($menu,$template);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    /**
     * Create a group
     *
     * @throws Exception
     */
    public function createGroup(){
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $label = $jinput->getRaw('label');
        $fid = $jinput->getRaw('fid');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->createGroup($label, $fid);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    /**
     * Create a simple group with a default label
     *
     * @throws Exception
     */
    public function createsimplegroup(){
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $fid = $jinput->getRaw('fid');
        $label = array(
            'fr' => 'Nouveau groupe',
            'en' => 'New group'
        );

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->createGroup($label,$fid);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    /**
     * Delete a group
     *
     * @throws Exception
     */
    public function deleteGroup() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $gid = $jinput->getRaw('gid');

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
        $element = $jinput->getRaw('element');
        $gid = $jinput->getRaw('gid');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->getElement($element, $gid);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    /**
     * Create an element with default parameters
     *
     * @throws Exception
     */
    public function createsimpleelement() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $gid = $jinput->getRaw('gid');
        $plugin = $jinput->getRaw('plugin');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->createSimpleElement($gid,$plugin);
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
        $gid = $jinput->getRaw('gid');
        $plugin = $jinput->getRaw('plugin');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->createSimpleElement($gid, $plugin, 1);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    /**
     * Delete an element
     *
     * @throws Exception
     */
    public function deleteElement() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $element = $jinput->getRaw('element');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->deleteElement($element);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    /**
     * Reorder pages
     *
     * @throws Exception
     */
    public function reordermenu() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $rgt = $jinput->getRaw('rgt');
        $link = $jinput->getRaw('link');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_form->reorderMenu($link, $rgt);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    /**
     * Return the group ordering of a page
     *
     * @throws Exception
     */
    public function getGroupOrdering() {
        $user = JFactory::getUser();
        $m_form = $this->model;

        $jinput = JFactory::getApplication()->input;
        $gid = $jinput->getRaw('gid');
        $fid = $jinput->getRaw('fid');

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
        $fid = $jinput->getRaw('fid');

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
        $cid = $jinput->getRaw('cid');

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
	        $group = $jinput->getRaw('group');
	        $cid = $jinput->getRaw('cid');
            $visibility = $m_form->checkVisibility($group,$cid);

            $tab = array('status' => 1, 'msg' => 'worked', 'data' => $visibility);
        }
        echo json_encode((object)$tab);
        exit;
    }
}


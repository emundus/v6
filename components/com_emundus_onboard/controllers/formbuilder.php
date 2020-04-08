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

    public function ChangeRequire() {
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

    public function UpdateParams() {
        $user = JFactory::getUser();
        $m_form = $this->model;
        $jinput = JFactory::getApplication()->input;
        $element = $jinput->getRaw('element');
        $newLabel = $jinput->getRaw('newLabel');
        $lang = JFactory::getLanguage();
        $locallang = $lang->getTag();
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
        $result = 0;
        $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
        $changeresponse = $m_form->UpdateParams($element,  $user->id, $locallang, $newLabel);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function changeTradLabel() {
        $user = JFactory::getUser();
        $m_form = $this->model;
        $jinput = JFactory::getApplication()->input;
        $element = $jinput->getRaw('element');
        $newLabel = $jinput->getRaw('newLabel');
        $lang = JFactory::getLanguage();
        $locallang = $lang->getTag();
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
        $result = 0;
        $changeTradresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
        $changeTradresponse = $m_form->changeTradLabel($element, $locallang, $newLabel, $user);
        }
        echo json_encode((object)$changeTradresponse);
        exit;
    }


     public function SubLabelsxValues(){
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

     public function formsTrad(){
        $user = JFactory::getUser();
        $m_form = $this->model;
        $jinput = JFactory::getApplication()->input;
        $labelTofind = $jinput->getRaw('labelTofind');
        $newLabel = $jinput->getRaw('NewSubLabel');
        $lang = JFactory::getLanguage();
        $locallang = $lang->getTag();        
        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
        $result = 0;
        $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else { 
            $changeresponse = $m_form->formsTrad($labelTofind,  $locallang, $newLabel);
        }
        echo json_encode((object)$changeresponse);
        exit;
     }

      public function getJTEXTA(){
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


     public function getJTEXT(){
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
}


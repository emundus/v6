<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      Benjamin Rivalland
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusControllerTranslations extends JControllerLegacy {

    var $model = null;

    public function __construct($config = array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'translations.php');
        parent::__construct($config);
        $this->model = new EmundusModelTranslations;
    }

    public function checksetup() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        $result = $this->model->checkSetup();

        echo $result;
        exit;
    }

    public function configuresetup() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        $result = $this->model->configureSetup();

        echo $result;
        exit;
    }

    public function getdefaultlanguage(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        $result = $this->model->getDefaultLanguage();

        echo json_encode($result);
        exit;
    }

    public function getlanguages(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        $result = $this->model->getAllLanguages();

        echo json_encode($result);
        exit;
    }

    public function updatelanguage(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        $jinput = JFactory::getApplication()->input;
        $published = $jinput->getInt('published', 1);
        $lang_code = $jinput->getString('lang_code', null);
        $default = $jinput->getInt('default_lang', 0);

        $result = $this->model->updateLanguage($lang_code,$published,$default);
        $default_language = $this->model->getDefaultLanguage();
        $secondary_languages = $this->model->getPlatformLanguages();
        foreach ($secondary_languages as $key => $language){
            if($default_language->lang_code == $language){
                unset($secondary_languages[$key]);
            }
        }
        if(empty($secondary_languages)){
            $this->model->updateFalangModule(0);
        } else {
            $this->model->updateFalangModule(1);
        }

        echo json_encode($result);
        exit;
    }

    public function gettranslationsobjects(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        $result = $this->model->getTranslationsObject();

        echo json_encode($result);
        exit;
    }

    public function getdatas(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        $jinput = JFactory::getApplication()->input;
        $table = $jinput->get->getString('table', null);
        $reference_id = $jinput->get->getString('reference_id', null);
        $label = $jinput->get->getString('label', null);
        $filters = $jinput->get->getString('filters', null);

        $result = $this->model->getDatas($table,$reference_id,$label,$filters);

        echo json_encode($result);
        exit;
    }

    public function getchildrens(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        $jinput = JFactory::getApplication()->input;
        $table = $jinput->get->getString('table', null);
        $reference_id = $jinput->get->getInt('reference_id', null);
        $label = $jinput->get->getString('label', null);

        $result = $this->model->getChildrens($table,$reference_id,$label);

        echo json_encode($result);
        exit;
    }

    public function gettranslations(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        $jinput = JFactory::getApplication()->input;
        $default_lang = $jinput->get->getString('default_lang', null);
        $lang_to = $jinput->get->getString('lang_to', null);
        $references_table = $jinput->get->get('reference_table', null);
        $reference_id = $jinput->get->getString('reference_id', null);

        $translations = array();

        foreach ($references_table as $reference_table) {
            if(!empty($reference_table['join_table']) && !empty($reference_table['join_column']) && !empty($reference_table['reference_column'])){
                $reference_id = $this->model->getJoinReferenceId($reference_table['table'],$reference_table['reference_column'],$reference_table['join_table'],$reference_table['join_column'],$reference_id);
			}
            $results = $this->model->getTranslations('override', '*', '', '', $reference_table['table'], $reference_id, $reference_table['fields']);

            foreach ($results as $result) {
                if (in_array($result->tag, array_keys($translations[$result->reference_id]))) {
                    if ($result->lang_code == $default_lang) {
                        $translations[$result->reference_id][$result->tag]->default_lang = $result->override;
                    } elseif ($result->lang_code == $lang_to) {
                        $translations[$result->reference_id][$result->tag]->lang_to = $result->override;
                    }
                } else {
                    $translation = $result;
                    if ($result->lang_code == $default_lang) {
                        $translation->default_lang = $result->override;
                    } elseif ($result->lang_code == $lang_to) {
                        $translation->lang_to = $result->override;
                    }
                    $translations[$result->reference_id][$result->tag] = $translation;
                }
            }
        }

        echo json_encode($translations);
        exit;
    }

    public function inserttranslation(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        $jinput = JFactory::getApplication()->input;

        $override = $jinput->getString('value', null);
        $lang_to = $jinput->getString('lang_to', null);
        $reference_table = $jinput->getString('reference_table', null);
        $reference_id = $jinput->getInt('reference_id', 0);
        $tag = $jinput->getString('tag', null);

        $result = $this->model->insertTranslation($tag,$override,$lang_to,'','override',$reference_table,$reference_id);

        echo json_encode($result);
        exit;
    }

    public function updatetranslation(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        $jinput = JFactory::getApplication()->input;

        $override = $jinput->getString('value', null);
        $lang_to = $jinput->getString('lang_to', null);
        $reference_table = $jinput->getString('reference_table', null);
        $reference_id = $jinput->getInt('reference_id', 0);
        $tag = $jinput->getString('tag', null);

        $result = $this->model->updateTranslation($tag,$override,$lang_to,'override',$reference_table,$reference_id);

        echo json_encode($result);
        exit;
    }

    public function getfalangtranslations(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        $jinput = JFactory::getApplication()->input;
        $default_lang = $jinput->get->getString('default_lang', null);
        $lang_to = $jinput->get->getString('lang_to', null);
        $reference_table = $jinput->get->getString('reference_table', null);
        $reference_id = $jinput->get->getString('reference_id', null);
        $fields = $jinput->get->getString('fields', null);

        $result = $this->model->getTranslationsFalang($default_lang,$lang_to,$reference_id,$fields,$reference_table);

        echo json_encode($result);
        exit;
    }

    public function updatefalangtranslation(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        $jinput = JFactory::getApplication()->input;

        $value = $jinput->getString('value', null);
        $lang_to = $jinput->getString('lang_to', null);
        $reference_table = $jinput->getString('reference_table', null);
        $reference_id = $jinput->getInt('reference_id', 0);
        $field = $jinput->getString('field', null);

        $result = $this->model->updateFalangTranslation($value,$lang_to,$reference_table,$reference_id,$field);

        echo json_encode($result);
        exit;
    }

    public function getorphelins(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        $jinput = JFactory::getApplication()->input;

        $default_lang = $jinput->getString('default_lang', null);
        $lang_to = $jinput->getString('lang_to', null);

        $result = $this->model->getOrphelins($default_lang,$lang_to);

        echo json_encode($result);
        exit;
    }

    public function sendpurposenewlanguage(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        $jinput = JFactory::getApplication()->input;

        $language = $jinput->getString('suggest_language', null);
        $comment = $jinput->getString('comment', null);

        $result = $this->model->sendPurposeNewLanguage($language,$comment);

        echo json_encode($result);
        exit;
    }
}

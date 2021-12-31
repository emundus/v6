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

        echo json_encode($result);
        exit;
    }
}

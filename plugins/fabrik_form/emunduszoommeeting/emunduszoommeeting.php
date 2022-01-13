<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
* Create a Joomla user from the forms data
*
* @package     Joomla.Plugin
* @subpackage  Fabrik.form.juseremundus
* @since       3.0
*/

class PlgFabrik_FormEmunduszoommeeting extends plgFabrik_Form {
    public function onAfterProcess()
    {

        $db = JFactory::getDBO();
        $app = JFactory::getApplication();

        echo '<pre>'; var_dump('test'); echo '</pre>'; die;
    }
}
<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

// no direct access
defined('_JEXEC') || die;
jimport('joomla.plugin.plugin');

/**
 * Class DropfilesPluginBase
 */
class DropfilesPluginBase extends JPlugin
{

    /**
     * Theme name
     *
     * @var string
     */
    public $name;

    /**
     * Options
     *
     * @var array
     */
    protected $options;

    /**
     * DropfilesPluginBase constructor.
     *
     * @param string $subject Subject
     * @param array  $config  Config
     *
     * @return void
     */
    public function __construct(&$subject, $config = array())
    {
        JLoader::register('DropfilesBase', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php');
        DropfilesBase::setDefine();
        //Load language from non default positiond
        DropfilesBase::loadLanguage();
        parent::__construct($subject, $config);
    }

    /**
     * Get theme name
     *
     * @return array
     */
    public function onThemeName()
    {
        $doc = JFactory::getDocument();
        $uriBtn = JURI::root() . 'plugins/dropfilesthemes/' . $this->name . '/images/btn.png';
        $style = '.themesblock a.themebtn.' . $this->name . ' {background-image: url(' . $uriBtn . ') }';
        $doc->addStyleDeclaration($style);

        return array('name' => ucfirst($this->name), 'id' => $this->name);
    }


    /**
     * Load the form fields for the plugin
     *
     * @param string $theme Theme name
     * @param object $form  Form object
     *
     * @return null|void
     */
    public function onConfigForm($theme, &$form)
    {
        if ($theme === '') {
            $theme = 'default';
        }
        if ($theme !== '' && $theme !== $this->name) {
            return;
        }
        $formfile = JPATH_PLUGINS . DIRECTORY_SEPARATOR . $this->_type . DIRECTORY_SEPARATOR;
        $formfile = $formfile . $this->name . DIRECTORY_SEPARATOR . '/form.xml';
        if (!file_exists($formfile)) {
            return;
        }
        $form->loadFile($formfile);
    }


    /**
     * Add loading
     *
     * @return void
     */
    public function addScriptTagLoading()
    {
        $doc = JFactory::getDocument();
        $loading = "<div id='dropfiles-loading-wrap'><div class='dropfiles-loading'></div></div>";
        $loading .= "<div id='dropfiles-loading-tree-wrap'><div class='dropfiles-loading-tree-bg'></div></div>";
        $doc->addScriptDeclaration('jQuery(document).ready(function(){ jQuery("body").append("' . $loading . '")});');
        $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/frontend.js');
    }

    /**
     * Get dropfiles default themes
     *
     * @return array
     *
     * @since 5.1
     */
    public static function getDropfilesThemes()
    {
        return array('default', 'ggd', 'tree', 'table');
    }
}

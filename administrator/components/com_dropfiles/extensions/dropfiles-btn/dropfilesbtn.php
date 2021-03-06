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

/**
 * Class PlgButtonDropfilesbtn
 */
class PlgButtonDropfilesbtn extends JPlugin
{

    /**
     * Add button to Editor
     *
     * @var boolean
     */
    protected $do = true;

    /**
     * Constructor
     *
     * @param object $subject The object to observe
     * @param array  $config  An array that holds the plugin configuration
     *
     * @return JPlugin
     * @since  1.5
     */
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        JLoader::register('DropfilesBase', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php');
        if (!class_exists('DropfilesBase')) {
            $this->do = false;
        }
        $lang = JFactory::getLanguage();
        $lang->load('plg_editors-xtd_dropfilesbtn', JPATH_PLUGINS . '/editors-xtd/dropfilesbtn', null, true);
        $lang->load('plg_editors-xtd_dropfilesbtn.sys', JPATH_PLUGINS . '/editors-xtd/dropfilesbtn', null, true);

        // Access check.
        if (!JFactory::getUser()->authorise('core.manage', 'com_dropfiles')) {
            $this->do = false;
        }
    }


    /**
     * Display the button
     *
     * @param string $name Name
     *
     * @return string|JObject A four element array of (code)
     * @throws \Exception Throw when application can not start
     * @since  1.5
     */
    public function onDisplay($name)
    {
        if (!$this->do) {
            return '';
        }
        /*
         * Javascript to insert the link
         * View element calls jSelectArticle when an article is clicked
         * jSelectArticle creates the link tag, sends it to the editor,
         * and closes the select frame.
         */
        $js = "
        function jInsertCategory(html) {
            jInsertEditorText(html, '" . $name . "');
            SqueezeBox.close();
        }";

        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration($js);

        if (JFactory::getApplication()->isAdmin()) {
            $path_btn2_dropfiles = JURI::root(true) . '/components/com_dropfiles/assets/images/j_button2_dropfiles.png';
            $doc->addStyleDeclaration('.button2-left .dropfiles {
                        background: url(' . $path_btn2_dropfiles . ') 100% 0 no-repeat;
                    }');
        }

        if (DropfilesBase::isJoomla30()) {
            $doc->addStyleDeclaration('.icon-dropfiles:before {
                        content: "\2d";
                    }');
        }

        JHtml::_('behavior.modal');

        /*
         * Use the built-in element view to select the article.
         * Currently uses blank class.
         */
        $path = urlencode(JURI::root(true));

        $link = 'index.php?option=com_dropfiles&amp;view=dropfiles&amp;tmpl=component&amp;';
        $link .= JSession::getFormToken() . '=1&caninsert=1&e_name=' . $name . '&template=system&path=' . $path;


        $button = new JObject();
        $button->set('modal', true);
        $button->set('link', $link);
        $button->set('class', 'btn');
        $button->set('text', JText::_('PLG_DROPFILES_BUTTON'));
        $button->set('name', 'dropfiles');
        $handler_str = "{handler: 'iframe', size: {x: (window.getSize().x*80/100), y: (window.getSize().y-50)}}";
        $button->set('options', $handler_str);

        return $button;
    }
}

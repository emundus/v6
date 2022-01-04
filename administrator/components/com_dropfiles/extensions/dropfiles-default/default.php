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

//-- No direct access
defined('_JEXEC') || die('=;)');
jimport('joomla.filter.output');

$path_dropfilespluginbase = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesPluginBase.php';
JLoader::register('DropfilesPluginBase', $path_dropfilespluginbase);

/**
 * Content Plugin.
 */
class PlgDropfilesthemesDefault extends DropfilesPluginBase
{
    /**
     * Theme name
     *
     * @var string
     */
    public $name = 'default';

    /**
     * Show front Category
     *
     * @param array $options Options pass to theme
     *
     * @return null|string
     * @since  version
     */
    public function onShowFrontCategory($options)
    {
        $this->options = $options;
        if (isset($this->options['theme']) && $this->options['theme'] !== '' && $this->options['theme'] !== $this->name) {
            return null;
        }

        $this->componentParams = JComponentHelper::getParams('com_dropfiles');

        $classes = array(
            'DropfilesBase'   => JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php',
            'DropfilesHelper' => JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfiles.php'
        );

        $scripts = array(
            JURI::base('true') . '/components/com_dropfiles/assets/js/handlebars-v4.7.7.js',
            JURI::base('true') . '/components/com_dropfiles/assets/js/jaofoldertree.js',
            JURI::base('true') . '/components/com_dropfiles/assets/js/colorbox.init.js',
            JURI::base('true') . '/plugins/dropfilesthemes/default/js/script.js'
        );

        $styles = array(
            JURI::base('true') . '/components/com_dropfiles/assets/css/jaofoldertree.css',
            JURI::base('true') . '/components/com_dropfiles/assets/css/material-design-iconic-font.min.css',
            JURI::base('true') . '/plugins/dropfilesthemes/default/style_ver5.4.css'
        );

        // Register classes
        foreach ($classes as $className => $path) {
            JLoader::register($className, $path);
        }

        $doc = JFactory::getDocument();
        JHtml::_('jquery.framework');
        $this->addScriptTagLoading();

        // Load scripts
        foreach ($scripts as $path) {
            $doc->addScript($path);
        }

        // Load styles
        foreach ($styles as $path) {
            $doc->addStyleSheet($path);
        }

        $content = '';
        if (!empty($this->options['files']) || (int) DropfilesBase::loadValue($this->params, 'showsubcategories', 1) === 1) {
            $this->files = $this->options['files'];
            $this->category = $this->options['category'];
            if ($this->category) {
                $this->category->alias = JFilterOutput::stringURLSafe($this->category->title);
            }
            $this->categories = $this->options['categories'];

            if (!in_array($this->name, parent::getDropfilesThemes())) {
                if (is_array($this->params)) {
                    $this->params = (object) $this->params;
                } elseif ($this->params instanceof Joomla\Registry\Registry) {
                    $this->params = $this->params->toObject();
                }
            } else {
                $this->params = $this->options['params'];
            }

            if ((int) $this->componentParams->get('loadthemecategory', 1) === 1) {
                $this->params = $this->options['params'];
            }

            $this->viewfileanddowload = DropfilesBase::getAuthViewFileAndDownload();
            $canDo = DropfilesHelper::getActions();
            $this->user_id = null;
            if ((int)$canDo->get('core.edit')) {
                $this->user_id = JFactory::getUser()->id;
            }
            $this->urlmanage = JURI::root() . 'index.php?option=com_dropfiles&view=manage';

            $marginRight = (int) DropfilesBase::loadValue($this->params, 'marginright', 10);
            $marginLeft = (int) DropfilesBase::loadValue($this->params, 'marginleft', 10);

            $columnPercent = 50;
            $columns = (int) DropfilesBase::loadValue($this->params, 'columns', 2);
            if ($columns > 0) {
                switch ($columns) {
                    case 1:
                        $columnPercent = 100;
                        break;
                    case 3:
                        $columnPercent = 100 / 3 ;
                        break;
                    case 2:
                        break;
                }
            }

            $columnStyle = 'flex-basis: calc(' . $columnPercent . '% - ' . ($marginRight + $marginLeft) . 'px);';
            $columnStyle .= 'max-width: calc(' . $columnPercent . '% - ' . ($marginRight + $marginLeft) . 'px);';
            $style = '.dropfiles-content-default[data-category="'.$this->category->id.'"] .dropfiles_list .file,.dropfiles-content.dropfiles-content-default[data-category="'.$this->category->id.'"] .dropfilescategory:not(.backcategory), .dropfiles-content.dropfiles-content-default[data-category="'.$this->category->id.'"] .dropfilescategory_placeholder {margin : ';
            $style .= DropfilesBase::loadValue($this->params, 'margintop', 10) . 'px ';
            $style .= $marginRight . 'px ';
            $style .= DropfilesBase::loadValue($this->params, 'marginbottom', 10) . 'px ';
            $style .= $marginLeft . 'px !important;';
            $style .= '}';
            $style .= '.dropfiles-content-default[data-category="'.$this->category->id.'"] .dropfiles_list .file {' . $columnStyle . '}';
            $oneColumnStyle = 'flex-basis: calc(100% - ' . ($marginRight + $marginLeft) . 'px);';
            $oneColumnStyle .= 'max-width: calc(100% - ' . ($marginRight + $marginLeft) . 'px);';
            $style .= '@media only screen and (max-width: 900px) {';
            $style .= '.dropfiles-content-default[data-category="'.$this->category->id.'"] .dropfiles_list .file {' .$oneColumnStyle.'}';
            $style .= '}';
            $style .= ' .dropfiles-content-default[data-category="'.$this->category->id.'"] .file .downloadlink, .dropfiles-content-default[data-category="'.$this->category->id.'"] .download-all, .dropfiles-content-default[data-category="'.$this->category->id.'"] .download-selected {background-color:';
            $style .= DropfilesBase::loadValue($this->params, 'bgdownloadlink', '#76bc58') . ' !important;color:';
            $style .= DropfilesBase::loadValue($this->params, 'colordownloadlink', '#fff') . ' !important;}';
            $doc->addStyleDeclaration($style);

            ob_start();
            require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tpl.php';
            $content = ob_get_contents();
            ob_end_clean();
        }
        return $content;
    }
}

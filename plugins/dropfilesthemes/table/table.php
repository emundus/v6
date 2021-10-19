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
class PlgDropfilesthemesTable extends DropfilesPluginBase
{
    /**
     * Theme name
     *
     * @var string
     */
    public $name = 'table';

    /**
     * Show front category
     *
     * @param array $options Theme option
     *
     * @return null|string
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function onShowFrontCategory($options)
    {
        $this->options = $options;

        if (!isset($this->options['theme']) || $this->options['theme'] !== $this->name) {
            return null;
        }
        $doc = JFactory::getDocument();
        if (DropfilesBase::isJoomla40()) {
            JHtml::_('behavior.core');
        } else {
            JHtml::_('behavior.framework', true);
        }
        $this->componentParams = JComponentHelper::getParams('com_dropfiles');
        JLoader::register('DropfilesBase', JPATH_ADMINISTRATOR . '/components/com_droppics/classes/dropfilesBase.php');
        JLoader::register('DropfilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfiles.php');
        JHtml::_('jquery.framework');
        $this->addScriptTagLoading();
        $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/handlebars-v4.7.7.js');

        $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/jaofoldertree.js');
        $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/colorbox.init.js');
        $doc->addStyleSheet(JURI::base('true') . '/components/com_dropfiles/assets/css/jaofoldertree.css');
        $iconic_font = JURI::base('true') . '/components/com_dropfiles/assets/css/material-design-iconic-font.min.css';
        $doc->addStyleSheet($iconic_font);

        $doc->addScript(JURI::base('true') . '/plugins/dropfilesthemes/table/js/script.js');
        $doc->addScript(JURI::base('true') . '/plugins/dropfilesthemes/table/js/jquery.mediaTable.js');
        $doc->addStyleSheet(JURI::base('true') . '/plugins/dropfilesthemes/table/css/style_ver5.4.css');
        $doc->addStyleSheet(JURI::base('true') . '/plugins/dropfilesthemes/table/css/jquery.mediaTable.css');

        $this->componentParams = JComponentHelper::getParams('com_dropfiles');

        JText::script('COM_DROPFILES_DEFAULT_FRONT_COLUMNS');


        $content = '';
        if (!empty($this->options['files']) || (int) DropfilesBase::loadValue($this->params, 'table_showsubcategories', 1) === 1) {
            $this->files = $this->options['files'];
            $this->category = $this->options['category'];
            if ($this->category) {
                $this->category->alias = JFilterOutput::stringURLSafe($this->category->title);
            }
            $this->categories = $this->options['categories'];

            if (!in_array($this->name, parent::getDropfilesThemes())) {
                $this->params = $this->params->toObject();
            } else {
                $this->params = $this->options['params'];
            }

            if ((int) $this->componentParams->get('loadthemecategory', 1) === 1) {
                $this->params = $this->options['params'];
            }

            $style = ' .dropfiles-content-table[data-category="'.$this->category->id.'"] td .downloadlink, .dropfiles-content-table[data-category="'.$this->category->id.'"] .download-all, .dropfiles-content-table[data-category="'.$this->category->id.'"] .download-selected {background-color:';
            $style .= DropfilesBase::loadValue($this->params, 'table_bgdownloadlink', '#006DCC') . ' !important;color:';
            $style .= DropfilesBase::loadValue($this->params, 'table_colordownloadlink', '#fff') . ' !important;}';
            $doc->addStyleDeclaration($style);

            $this->tableclass = 'table table-bordered table-striped';
            $this->dropfilesclass = '';
            if (DropfilesBase::loadValue($this->params, 'table_stylingmenu', true)) {
                $this->dropfilesclass .= 'colstyle';
            }
            $this->viewfileanddowload = DropfilesBase::getAuthViewFileAndDownload();
            $canDo = DropfilesHelper::getActions();
            $this->user_id = null;
            if ((int)$canDo->get('core.edit')) {
                $this->user_id = JFactory::getUser()->id;
            }
            $this->urlmanage = JURI::root() . 'index.php?option=com_dropfiles&view=manage';

            ob_start();
            require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tpl.php';
            $content = ob_get_contents();
            ob_end_clean();
        }
        return $content;
    }
}

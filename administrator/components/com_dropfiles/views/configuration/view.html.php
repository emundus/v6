<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2016 JoomUnited (https://www.joomunited.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') || die;

/**
 * HTML View class for the Dropfiles component
 */
class DropfilesViewConfiguration extends JViewLegacy
{
    /**
     * Variable to hold params values
     *
     * @var array $params The data of params
     */
    protected $params;

    /**
     * Variable to hold form values
     *
     * @var array $form The data of form
     */
    protected $form;

    /**
     * Execute and display a template script.
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return mixed  A string if successful, otherwise a Error object.
     *
     * @throws Exception When error
     */
    public function display($tpl = null)
    {

        JHtml::_('jquery.framework');
        if (DropfilesBase::isJoomla40()) {
            $doc = JFactory::getDocument();
            $doc->addScript(JURI::root() . 'components/com_dropfiles/assets/js/jquery.minicolors.min.js');
            $doc->addStyleSheet(JURI::root() . 'components/com_dropfiles/assets/css/chosen.css');
            $doc->addScript(JURI::root() . 'components/com_dropfiles/assets/js/chosen.jquery.min.js');
            $doc->addStyleSheet(JURI::root() . 'components/com_dropfiles/assets/css/jquery.minicolors.css');
        } else {
            JHtml::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));
            JHtml::_('script', 'jui/chosen.jquery.min.js', false, true, false, false);
            JHtml::_('stylesheet', 'jui/chosen.css', false, true);
        }


        $document = JFactory::getApplication()->getDocument();
        // Load style
        $document->addStyleSheet('https://fonts.googleapis.com/icon?family=Material+Icons');
        $document->addStyleSheet(JUri::base() . 'components/com_dropfiles/assets/joomla-css-framework/css/style.css');
        $document->addStyleSheet(JUri::base() . 'components/com_dropfiles/assets/joomla-css-framework/css/waves.min.css');
        $document->addStyleSheet(JUri::base() . 'components/com_dropfiles/assets/joomla-css-framework/css/jquery.qtip.css');
        $document->addStyleSheet(JUri::base() . 'components/com_dropfiles/assets/css/configuration.css');
        // Load script
        $document->addScript(JUri::base() . 'components/com_dropfiles/assets/joomla-css-framework/js/velocity.min.js');
        $document->addScript(JUri::base() . 'components/com_dropfiles/assets/joomla-css-framework/js/tabs.js');
        $document->addScript(JUri::base() . 'components/com_dropfiles/assets/joomla-css-framework/js/script.js');
        $document->addScript(JUri::base() . 'components/com_dropfiles/assets/joomla-css-framework/js/waves.min.js');
        $document->addScript(JUri::base() . 'components/com_dropfiles/assets/joomla-css-framework/js/jquery.qtip.min.js');
        $document->addScript(JUri::base() . 'components/com_dropfiles/assets/js/configuration.js');

        $model = $this->getModel();
        //Load the data form
        $form = $model->getForm();
        $data = JComponentHelper::getParams('com_dropfiles');

        // Bind data
        if ($form && $data) {
            $form->bind($data);
        }

        $this->form = &$form;
        $this->params = &$data;

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return void
     *
     * @since 1.6
     */
    protected function addToolbar()
    {
        JToolbarHelper::title(JText::_('COM_DROPFILES_CONFIGURATION_TITLE'), 'ju_settings');

        // Generate html for toolbar button
        $html = array();
        $html[] = '<button onclick="Joomla.submitbutton(\'configuration.saveParams\');"  class="ju-button orange-button waves-effect waves-light">';
        $html[] = '<span class="icon-apply ju-icon orange-icon" aria-hidden="true"></span>' . JText::_('COM_DROPFILES_CONFIGURATION_SAVE_BUTTON_NAME');
        $html[] = '</button>';
        $html[] = '<button onclick="Joomla.submitbutton(\'configuration.saveParamsAndClose\');"  class="ju-button orange-outline-button orange-border waves-effect waves-light">';
        $html[] = '<span class="icon-save ju-icon orange-outline-icon" aria-hidden="true"></span>' . JText::_('COM_DROPFILES_CONFIGURATION_SAVE_AND_CLOSE_BUTTON_NAME');
        $html[] = '</button>';
        $html[] = '<button onclick="Joomla.submitbutton(\'configuration.closeConfiguration\');"  class="ju-button black-outline-button waves-effect waves-light">';
        $html[] = '<span class="icon-cancel ju-icon black-outline-icon" aria-hidden="true"></span>' . JText::_('COM_DROPFILES_CONFIGURATION_CANCEL_BUTTON_NAME');
        $html[] = '</button>';
        $toolbar = JToolBar::getInstance('toolbar');
        $toolbar->appendButton('Custom', implode('', $html));
    }
}

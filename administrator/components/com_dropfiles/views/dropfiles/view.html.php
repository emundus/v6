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


defined('_JEXEC') || die;

/**
 * Class DropfilesViewDropfiles
 */
class DropfilesViewDropfiles extends JViewLegacy
{
    /**
     * State
     *
     * @var string
     */
    protected $state;


    /**
     * Display the view
     *
     * @param null|string $tpl Template
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $this->canDo = DropfilesHelper::getActions();
        $this->params = JComponentHelper::getParams('com_dropfiles');
        $params = $this->params;
        $catsmanage = JFactory::getApplication()->input->getInt('site_catid', 0);
        $tasksmanage = JFactory::getApplication()->input->get('task', '');

        $model = $this->getModel('categories');
//                $this->setState('list.limit',100000000);
        JFactory::getApplication()->setUserState('list.limit', 100000);
        $this->categories = $model->getItems();
        $this->categories = $model->extractOwnCategories($this->categories);
        $mdFrontsearch = JModelLegacy::getInstance('frontsearch', 'dropfilesModel');
        $this->allCategories = $mdFrontsearch->getAllCategories();

        $modelFile = $this->getModel('file');
        $this->fieldSet = $modelFile->getForm()->getFieldset();

        $user = JFactory::getUser();
        $params = JComponentHelper::getParams('com_dropfiles');
        if ($params->get('import') && !$app->input->getBool('caninsert', 0) && $user->authorise('core.admin')) {
            $this->importFiles = true;
        } else {
            $this->importFiles = false;
        }
        $this->catid_active = 0;

        if ($tasksmanage && $tasksmanage === 'site_manage') {
            $this->catid_active = $catsmanage;
        }

        $this->setLayout($app->input->get('layout', 'default'));

        parent::display($tpl);

        $app = JFactory::getApplication();
        if ($app->isClient('administrator')) {
            $this->addToolbar();
        }
    }


    /**
     * Add toolbar
     *
     * @return void
     */
    protected function addToolbar()
    {
        $canDo = DropfilesHelper::getActions();

        JToolBarHelper::title(JText::_('COM_DROPFILES_MAIN_PAGE'), 'dropfiles.png');
        if ($canDo->get('core.admin')) {
            $toolbar = JToolBar::getInstance();
            $toolbar->appendButton(
                'Link',
                'config-options',
                '<span class="toolbar-title">' . JText::_('COM_DROPFILES_VIEW_CONFIGURATION') . '</span>',
                'index.php?option=com_dropfiles&amp;task=configuration.display'
            );
            $toolbar->appendButton(
                'Link',
                'chart-options',
                '<span class="toolbar-title">' . JText::_('COM_DROPFILES_DOWNLOAD_STATISTICS') . '</span>',
                'index.php?option=com_dropfiles&amp;view=statistics'
            );
        }
        JToolBarHelper::divider();
    }
}

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
 * Class DropfilesViewManage
 */
class DropfilesViewManage extends JViewLegacy
{
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

        $user = JFactory::getUser();
        $loginUserId = (int)$user->get('id');
        if (!$loginUserId) {
            $uri = JUri::getInstance();
            $return = base64_encode($uri->toString());
            $loginUrl = JRoute::_('index.php?option=com_users&view=login');
            if (strpos($loginUrl, '?') === false) {
                $loginUrl = $loginUrl . '?return='. $return;
            } else {
                $loginUrl = $loginUrl . '&return='. $return;
            }
            $app->redirect($loginUrl);
        }
        $catsmanage = JFactory::getApplication()->input->getInt('site_catid', 0);
        $tasksmanage = JFactory::getApplication()->input->get('task', '');

        $this->canDo = DropfilesHelper::getActions();
        $this->params = JComponentHelper::getParams('com_dropfiles');
        $params = $this->params;
        JModelLegacy::addIncludePath(JPATH_ROOT . '/administrator/components/com_dropfiles/models/', 'DropfilesModel');
        $model = JModelLegacy::getInstance('Categories', 'dropfilesModel');
        $mdFrontsearch = JModelLegacy::getInstance('frontsearch', 'dropfilesModel');
        $this->allCategories = $mdFrontsearch->getAllCategories();

        JFactory::getApplication()->setUserState('list.limit', 100000);
        $this->categories = $model->getItems();
        $this->categories = $model->extractOwnCategories($this->categories);

        $modelFile = JModelLegacy::getInstance('File', 'dropfilesModel');
        $this->fieldSet = $modelFile->getForm()->getFieldset();

        $user = JFactory::getUser();
        $params = JComponentHelper::getParams('com_dropfiles');
        if ($params->get('import') && !JFactory::getApplication()->input->getBool('caninsert', 0) &&
            $user->authorise('core.admin')) {
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
    }
}

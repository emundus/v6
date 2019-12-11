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
 * Class DropfilesViewSinglecategory
 */
class DropfilesViewSinglecategory extends JViewLegacy
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
            $app->redirect(JRoute::_('index.php?option=com_users&view=login'));
        }

        $catid = $app->input->getInt('catid', 0);

        $this->canDo = DropfilesHelper::getActions();
        $this->params = JComponentHelper::getParams('com_dropfiles');
        $params = $this->params;
        $modelCat = JModelLegacy::getInstance('Frontcategory', 'dropfilesModel');
        //$modelCat = $this->getModel('frontcategory');

        $this->categories = array($modelCat->getCategory($catid));


        $user = JFactory::getUser();
        $params = JComponentHelper::getParams('com_dropfiles');
        if ($params->get('import') && !$app->input->getBool('caninsert', 0) && $user->authorise('core.admin')) {
            $this->importFiles = true;
        } else {
            $this->importFiles = false;
        }

        $this->setLayout($app->input->get('layout', 'default'));

        parent::display($tpl);
    }
}

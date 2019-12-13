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

jimport('joomla.application.component.controllerlegacy');

/**
 * Class DropfilesController
 */
class DropfilesController extends JControllerLegacy
{

    /**
     * Default view for this controller
     *
     * @var string Default view
     */
    protected $default_view = 'dropfiles';

    /**
     * Method to display the view.
     *
     * @param boolean $cachable  If true, the view output will be cached
     * @param array   $urlparams An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
     *
     * @return \DropfilesController  A \DropfilesController object
     * @throws \Exception Throws if Application can not start
     * @since  1.0
     */
    public function display($cachable = false, $urlparams = array())
    {
        // Load the submenu.
        $app = JFactory::getApplication();
        DropfilesHelper::addSubmenu($app->input->getString('view', $this->default_view));

        $vName = $app->input->getString('view', $this->default_view);
        $layout = $app->input->getString('layout', 'default');

        if ($vName === 'dropfiles') {
            $view = $this->getView($vName, 'html');
            $modelCategory = $this->getModel('category');
            $view->setModel($modelCategory, false);
            $modelCategories = $this->getModel('categories');
            $view->setModel($modelCategories, false);
            $modelFiles = $this->getModel('files');
            $view->setModel($modelFiles, false);
            $modelFile = $this->getModel('file');
            $view->setModel($modelFile, false);
        } elseif ($vName === 'category' && $layout === 'default') {
            $view = $this->getView($vName, 'raw');
            $model = $this->getModel('category');
            $view->setModel($model, false);
        } elseif ($vName === 'files') {
            $view = $this->getView($vName, 'raw');
            $model = $this->getModel('category');
            $view->setModel($model, false);
        }
//        }
        parent::display($cachable, $urlparams);
        return $this;
    }

    /**
     * Edit htaccess
     *
     * @return void
     * @since  1.0
     */
    public function htaccessdo()
    {
        DropfilesBase::edithtaccess();
    }
}

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


jimport('joomla.application.component.controller');

/**
 * Class DropfilesController
 */
class DropfilesController extends JControllerLegacy
{
    /**
     * DropfilesController constructor.
     *
     * @param array $config Config
     */
//    public function __construct($config = array())
//    {
//       $view = JFactory::getApplication()->input->get('view');
//        if(!preg_match('/^front.*/', $view)){
//            $config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
//        }
//
//        parent::__construct($config);
//    }

    /**
     * Display dropfiles files
     *
     * @param boolean $cachable  Cachable
     * @param boolean $urlparams Url params
     *
     * @return DropfilesController
     * @throws Exception Throw when application can not start
     * @since  version
     */
    public function display($cachable = false, $urlparams = false)
    {
        // load the submenu.
        $app = JFactory::getApplication();
        $input_view = $app->input->get('view', $this->default_view);
        dropfileshelper::addsubmenu($input_view);

        $vname = $input_view;
        if ($vname === 'dropfiles') {
            $view = $this->getview($vname, 'html');
            $model = $this->getmodel('category');
            $view->setmodel($model, false);
        } elseif ($vname === 'frontfile' || $vname === 'frontfiles' || $vname === 'frontcategories') {
            $path_files = JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/files.php';
            jloader::register('dropfilesfileshelper', $path_files);
            $view = $this->getview($vname, 'json');
            $model = $this->getmodel('frontcategory');
            $view->setmodel($model, false);
        }
        parent::display($cachable, $urlparams);

        return $this;
    }
}

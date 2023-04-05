<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Controller;

use Joomla\CMS\MVC\Controller\BaseController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Media Manager Component Controller
 *
 * @since  4.0.0
 */
class DisplayController extends BaseController
{
    /**
     * The default view.
     *
     * @var    string
     * @since  1.6
     */
    protected $default_view = 'media';

    /**
     * Method to get a reference to the current view and load it if necessary.
     *
     * @param   string  $name    The view name. Optional, defaults to the controller name.
     * @param   string  $type    The view type. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for view. Optional.
     *
     * @return  \Joomla\CMS\MVC\View\ViewInterface  Reference to the view or an error.
     *
     * @since   3.0
     * @throws  \Exception
     */
    public function getView($name = '', $type = '', $prefix = '', $config = [])
    {
        // Force to load the admin view
        return parent::getView($name, $type, 'Administrator', $config);
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel|boolean  Model object on success; otherwise false on failure.
     *
     * @since   3.0
     */
    public function getModel($name = '', $prefix = '', $config = [])
    {
        // Force to load the admin model
        return parent::getModel($name, 'Administrator', $config);
    }
}

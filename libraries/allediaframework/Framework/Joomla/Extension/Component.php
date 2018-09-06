<?php
/**
 * @package   AllediaFramework
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC., All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework\Joomla\Extension;

defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use JControllerLegacy;
use JFactory;

/**
 * @deprecated Components should extends the AbstractComponent
 */
class Component extends Licensed
{
    /**
     * The main controller
     *
     * @var JControllerLegacy
     */
    protected $controller;

    /**
     * Class constructor that instantiate the free and pro library, if installed
     */
    public function __construct($namespace)
    {
        parent::__construct($namespace, 'component');

        $this->loadLibrary();
    }

    /**
     * Load the main controller
     *
     * @return void
     */
    public function loadController()
    {
        if (!isset($this->controller)) {
            jimport('legacy.controller.legacy');

            $this->controller = JControllerLegacy::getInstance($this->namespace);
        }
    }

    public function executeTask()
    {
        $app  = JFactory::getApplication();
        $task = $app->input->getCmd('task');

        $this->controller->execute($task);
        $this->controller->redirect();
    }
}

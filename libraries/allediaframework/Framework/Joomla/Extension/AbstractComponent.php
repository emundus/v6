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
use Alledia\Framework\Joomla\Model\Base as BaseModel;
use Alledia\Framework\Joomla\Table\Base as BaseTable;
use JModelLegacy;
use JTable;

abstract class AbstractComponent extends Licensed
{
    // @TODO: convert to protected and remove from the subclasses?
    private static $instance;

    /**
     * The main controller
     *
     * @var object
     */
    protected $controller;

    /**
     * Class constructor that instantiate the free and pro library, if installed
     */
    public function __construct($namespace)
    {
        parent::__construct($namespace, 'component');

        JModelLegacy::addIncludePath(JPATH_COMPONENT . '/models');
        JTable::addIncludePath(JPATH_COMPONENT . '/tables');

        $this->loadLibrary();
    }

    /**
     * Returns the instance of child classes
     *
     * @param string $namespace
     *
     * @return Object
     */
    public static function getInstance($namespace = null)
    {
        if (empty(static::$instance)) {
            static::$instance = new static($namespace);
        }

        return static::$instance;
    }

    public function init()
    {
        $app = Factory::getApplication();

        $this->loadController();
        $this->executeRedirectTask();
    }

    public function loadController()
    {
        if (!is_object($this->controller)) {
            $app    = Factory::getApplication();
            $client = $app->isAdmin() ? 'Admin' : 'Site';

            $controllerClass = 'Alledia\\' . $this->namespace . '\\' . ucfirst($this->license)
                . '\\Joomla\\Controller\\' . $client;
            require JPATH_COMPONENT . '/controller.php';

            $this->controller = $controllerClass::getInstance($this->namespace);
        }
    }

    public function executeRedirectTask()
    {
        $app  = Factory::getApplication();
        $task = $app->input->getCmd('task');

        $this->controller->execute($task);
        $this->controller->redirect();
    }

    public function getModel($type)
    {
        if ($this->isPro()) {
            $class = "Alledia\\{$this->namespace}\\Pro\\Joomla\\Model\\{$type}";
            if (class_exists($class)) {
                return new $class();
            }
        } else {
            $class = "Alledia\\{$this->namespace}\\Free\\Joomla\\Model\\{$type}";
            if (class_exists($class)) {
                return new $class();
            }
        }

        return BaseModel::getInstance($type, $this->namespace . 'Model');
    }

    public function getTable($type)
    {
        $db = Factory::getDbo();

        if ($this->isPro()) {
            $class = "Alledia\\{$this->namespace}\\Pro\\Joomla\\Table\\{$type}";
            if (class_exists($class)) {
                return new $class($db);
            }
        } else {
            $class = "Alledia\\{$this->namespace}\\Free\\Joomla\\Table\\{$type}";
            if (class_exists($class)) {
                return new $class($db);
            }
        }

        return BaseTable::getInstance($type, $this->namespace . 'Table');
    }
}

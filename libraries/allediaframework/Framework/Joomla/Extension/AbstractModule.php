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
use Joomla\Registry\Registry;
use JModuleHelper;

/**
 * @deprecated  1.4.1 Use AbstractFlexibleModule instead. This module doesn't
 * work with multiple modules in the same page because of the Singleton pattern.
 *
 */
abstract class AbstractModule extends Licensed
{
    // @TODO: convert to protected and remove from the subclasses?
    private static $instance;

    public $id;

    public $title;

    public $module;

    public $position;

    public $content;

    public $showtitle;

    public $params;

    public $menuid;

    public $name;

    public $style;


    /**
     * Class constructor that instantiate the free and pro library, if installed
     */
    public function __construct($namespace)
    {
        parent::__construct($namespace, 'module');

        $this->loadLibrary();
    }

    /**
     * Returns the instance of child classes
     *
     * @param string $namespace
     * @param object $module
     *
     * @return Object
     */
    public static function getInstance($namespace = null, $module = null)
    {
        if (empty(static::$instance)) {
            $instance = new static($namespace);

            if (is_object($module)) {
                $instance->id        = $module->id;
                $instance->title     = $module->title;
                $instance->module    = $module->module;
                $instance->position  = $module->position;
                $instance->content   = $module->content;
                $instance->showtitle = $module->showtitle;
                $instance->menuid    = $module->menuid;
                $instance->name      = $module->name;
                $instance->style     = $module->style;
                $instance->params    = new Registry($module->params);
            } else {
                // @TODO: Raise warning/Error
            }

            $instance->loadLanguage();

            static::$instance = $instance;

        }


        return static::$instance;
    }

    public function init()
    {
        require JModuleHelper::getLayoutPath('mod_' . $this->element, $this->params->get('layout', 'default'));
    }

    /**
     * Method to load the language files
     *
     * @return void
     */
    public function loadLanguage()
    {
        $language = Factory::getLanguage();
        $language->load($this->module, JPATH_SITE);
    }
}

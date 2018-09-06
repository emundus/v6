<?php
/**
 * @package   AllediaFramework
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC., All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework;

use Alledia\Framework\Joomla\Extension\Licensed;

defined('_JEXEC') or die();

abstract class Factory extends \JFactory
{
    /**
     * Instances of extensions
     *
     * @var array
     */
    protected static $extensionInstances = array();

    /**
     * Get an extension
     *
     * @param  string $namespace The extension namespace
     * @param  string $type      The extension type
     * @param  string $folder    The extension folder (plugins only)
     *
     * @return Licensed          The extension instance
     */
    public static function getExtension($namespace, $type, $folder = null)
    {
        $key = $namespace . $type . $folder;

        if (empty(self::$extensionInstances[$key])) {
            $instance = new Joomla\Extension\Licensed($namespace, $type, $folder);

            self::$extensionInstances[$key] = $instance;
        }

        return self::$extensionInstances[$key];
    }
}

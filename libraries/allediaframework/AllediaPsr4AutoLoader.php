<?php
/**
 * @package   AllediaFramework
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC., All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\Framework\AutoLoader;

defined('_JEXEC') or die();

if (!class_exists('\\Alledia\\Framework\\AutoLoader')) {
    require_once __DIR__ . '/Framework/AutoLoader.php';
}

/**
 * Class AllediaPsr4AutoLoader
 *
 * @deprecated See Alledia\Framework\AutoLoader
 */
class AllediaPsr4AutoLoader extends AutoLoader
{
    /**
     * @param string $prefix
     * @param string $baseDir
     * @param bool   $prepend
     *
     * @return void
     *
     * @deprecated
     */
    public function addNamespace($prefix, $baseDir, $prepend = false)
    {
        static::register($prefix, $baseDir, $prepend);
    }
}

<?php
/**
 * @package   AllediaInstaller
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC., All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework\Joomla\Extension;

use Alledia\Framework\Factory;

defined('_JEXEC') or die();

/**
 * Generic extension helper class
 */
abstract class Helper
{
    /**
     * Build a string representing the element
     *
     * @param string $type
     * @param string $element
     * @param string $folder
     *
     * @return string
     */
    public static function getFullElementFromInfo($type, $element, $folder = null)
    {
        $prefixes = array(
            'component' => 'com',
            'plugin'    => 'plg',
            'template'  => 'tpl',
            'library'   => 'lib',
            'cli'       => 'cli',
            'module'    => 'mod'
        );

        $fullElement = $prefixes[$type];

        if ($type === 'plugin') {
            $fullElement .= '_' . $folder;
        }

        $fullElement .= '_' . $element;

        return $fullElement;
    }

    /**
     * @param string $element
     *
     * @return array
     */
    public static function getExtensionInfoFromElement($element)
    {
        $result = array(
            'type'      => null,
            'name'      => null,
            'group'     => null,
            'prefix'    => null,
            'namespace' => null
        );

        $types = array(
            'com' => 'component',
            'plg' => 'plugin',
            'mod' => 'module',
            'lib' => 'library',
            'tpl' => 'template',
            'cli' => 'cli'
        );

        $element = explode('_', $element);

        $result['prefix'] = $element[0];

        if (array_key_exists($result['prefix'], $types)) {
            $result['type'] = $types[$result['prefix']];

            if ($result['prefix'] === 'plg') {
                $result['group'] = $element[1];
                $result['name']  = $element[2];
            } else {
                $result['name']  = $element[1];
                $result['group'] = null;
            }
        }

        $result['namespace'] = preg_replace_callback(
            '/^(os[a-z])(.*)/i',
            function ($matches) {
                return strtoupper($matches[1]) . $matches[2];
            },
            $result['name']
        );

        return $result;
    }

    /**
     * @param string $element
     *
     * @return bool
     */
    public static function loadLibrary($element)
    {
        $extension = static::getExtensionForElement($element);

        if (is_object($extension)) {
            return $extension->loadLibrary();
        }

        return false;
    }

    /**
     * @param string $element
     *
     * @return string
     */
    public static function getFooterMarkup($element)
    {
        if (is_string($element)) {
            $extension = static::getExtensionForElement($element);
        } elseif (is_object($element)) {
            $extension = $element;
        }

        if (!empty($extension)) {
            return $extension->getFooterMarkup();
        }

        return '';
    }

    /**
     * @param string $element
     *
     * @return Licensed
     */
    public static function getExtensionForElement($element)
    {
        $info = static::getExtensionInfoFromElement($element);

        if (!empty($info['type']) && !empty($info['namespace'])) {
            return Factory::getExtension($info['namespace'], $info['type'], $info['group']);
        }

        return null;
    }
}

<?php
/**
 * @package   Gantry
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   GNU/GPLv2 and later
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die;

/**
 * Gantry package installer script.
 */
class Pkg_GantryInstallerScript
{
    /**
     * List of supported versions. Newest version first!
     * @var array
     */
    protected $versions = array(
        'PHP' => array (
            '5.2' => '5.2.17',
            '0' => '7.0.30' // Preferred version
        ),
        'Joomla!' => array (
            '2.5' => '2.5.0',
            '0' => '3.9.3' // Preferred version
        )
    );
    /**
     * List of required PHP extensions.
     * @var array
     */
    protected $extensions = array ('xml', 'zlib');

    public function install($parent)
    {
        return true;
    }

    public function discover_install($parent)
    {
        return self::install($parent);
    }

    public function update($parent)
    {
        return self::install($parent);
    }

    public function uninstall($parent)
    {
        // Hack.. Joomla really doesn't give any information from the extension that's being uninstalled..
        $manifestFile = JPATH_MANIFESTS . '/packages/pkg_gantry.xml';
        if (is_file($manifestFile)) {
            $manifest = simplexml_load_file($manifestFile);
            $this->prepareExtensions($manifest, 0);
        }

        // Clear cached files.
        if (is_dir(JPATH_CACHE . '/gantry')) {
            JFolder::delete(JPATH_CACHE . '/gantry');
        }
        if (is_dir(JPATH_SITE . '/cache/gantry')) {
            JFolder::delete(JPATH_SITE . '/cache/gantry');
        }

        return true;
    }

    public function preflight($type, $parent)
    {
        /** @var JInstallerAdapter $parent */
        $manifest = $parent->getManifest();

        // Prevent installation if requirements are not met.
        $errors = $this->checkRequirements($manifest->version);
        if ($errors) {
            $app = JFactory::getApplication();

            foreach ($errors as $error) {
                $app->enqueueMessage($error, 'error');
            }
            return false;
        }

        // Disable and unlock existing extensions to prevent fatal errors (in the site).
        $this->prepareExtensions($manifest, 0);

        return true;
    }

    public function postflight($type, $parent)
    {
        $this->removeOldLibrary();

        // Clear Joomla system cache.
        /** @var JCache|JCacheController $cache */
        $cache = JFactory::getCache();
        $cache->clean('_system');

        // Clear Gantry cache.
        $path = JFactory::getConfig()->get('cache_path', JPATH_SITE . '/cache') . '/gantry';
        if (is_dir($path)) {
            JFolder::delete($path);
        }

        // Make sure that PHP has the latest data of the files.
        clearstatcache();

        // Remove all compiled files from opcode cache.
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        } elseif (function_exists('apc_clear_cache')) {
            @apc_clear_cache();
        }

        if ($type == 'uninstall') {
            return true;
        }

        /** @var JInstallerAdapter $parent */
        $manifest = $parent->getManifest();

        // Enable and lock extensions to prevent uninstalling them individually.
        $this->prepareExtensions($manifest, 1);

        // Make sure that all file formats used by Gantry 5 are editable from template manager.
        $this->adjustTemplateSettings();

        return true;
    }

    // Internal functions

    protected function removeOldLibrary()
    {
        $name = 'lib_gantry';

        // Joomla 3.9 does not like libraries with prefix, because of this library manifest file was renamed.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->delete('#__extensions')->where("{$db->quoteName('element')}={$db->quote($name)}");
        $db->setQuery($query);
        $db->execute();

        $filename = JPATH_ADMINISTRATOR . "/manifests/libraries/{$name}.xml";
        if (file_exists($filename)) {
            JFile::delete($filename);
        }
    }

    protected function prepareExtensions($manifest, $state = 1)
    {
        foreach ($manifest->files->children() as $file) {
            $attributes = $file->attributes();

            $search = array('type' => (string) $attributes->type, 'element' => (string) $attributes->id);

            $clientName = (string) $attributes->client;
            if (!empty($clientName)) {
                $client = JApplicationHelper::getClientInfo($clientName, true);
                $search +=  array('client_id' => $client->id);
            }

            $group = (string) $attributes->group;
            if (!empty($group)) {
                $search +=  array('folder' => $group);
            }

            $extension = JTable::getInstance('extension');

            if (!$extension->load($search)) {
                continue;
            }

            // Joomla 3.7 added a new package protection feature: only use individual protection in older versions.
            $extension->protected = version_compare(JVERSION, '3.7', '<') ? $state : 0;

            if (isset($attributes->enabled)) {
                $extension->enabled = $state ? (int) $attributes->enabled : 0;
            }

            $extension->store();
        }
    }

    protected function adjustTemplateSettings()
    {
        $extension = JTable::getInstance('extension');
        if (!$extension->load(array('type' => 'component', 'element' => 'com_templates'))) {
            return;
        }

        $params = new Joomla\Registry\Registry($extension->params);
        $params->set('source_formats', $this->addParam($params->get('source_formats'), array('scss', 'yaml', 'twig')));
        $params->set('font_formats', $this->addParam($params->get('font_formats'), array('eot', 'svg')));

        $extension->params = $params->toString();
        $extension->store();
    }

    protected function addParam($string, array $options)
    {
        $items = array_flip(explode(',', $string)) + array_flip($options);

        return implode(',', array_keys($items));
    }

    protected function checkRequirements($gantryVersion)
    {
        $results = array();
        $this->checkVersion($results, 'PHP', phpversion());
        $this->checkVersion($results, 'Joomla!', JVERSION);
        $this->checkExtensions($results, $this->extensions);

        return $results;
    }

    protected function checkVersion(array &$results, $name, $version)
    {
        $major = $minor = 0;
        foreach ($this->versions[$name] as $major => $minor) {
            if (!$major || version_compare($version, $major, '<')) {
                continue;
            }

            if (version_compare($version, $minor, '>=')) {
                return;
            }
            break;
        }

        if (!$major) {
            $minor = reset($this->versions[$name]);
        }

        $recommended = end($this->versions[$name]);

        if (version_compare($recommended, $minor, '>')) {
            $results[] = sprintf(
                '%s %s is not supported. Minimum required version is %s %s, but it is highly recommended to use %s %s or later version.',
                $name,
                $version,
                $name,
                $minor,
                $name,
                $recommended
            );
        } else {
            $results[] = sprintf(
                '%s %s is not supported. Please update to %s %s or later version.',
                $name,
                $version,
                $name,
                $minor
            );
        }
    }

    protected function checkExtensions(array &$results, $extensions)
    {
        foreach ($extensions as $name) {
            if (!extension_loaded($name)) {
                $results[] = sprintf("Required PHP extension '%s' is missing. Please install it into your system.", $name);
            }
        }
    }
}

<?php
/**
 * @package   AllediaInstaller
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC., All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework\Joomla\Extension;

defined('_JEXEC') or die();

jimport('joomla.filesystem.file');

use JFactory;
use Joomla\Registry\Registry;
use JFile;
use JFormFieldCustomFooter;
use SimpleXMLElement;

/**
 * Generic extension class
 *
 * @todo : Make this class compatible with non-Alledia extensions
 */
class Generic
{
    /**
     * The extension namespace
     *
     * @var string
     */
    protected $namespace;

    /**
     * The extension type
     *
     * @var string
     */
    protected $type;

    /**
     * The extension id
     *
     * @var int
     */
    protected $id;

    /**
     * The extension name
     *
     * @var string
     */
    protected $name;

    /**
     * The extension params
     *
     * @var Registry
     */
    public $params;

    /**
     * The extension enable state
     *
     * @var bool
     */
    protected $enabled;

    /**
     * The element of the extension
     *
     * @var string
     */
    protected $element;

    /**
     * Base path
     *
     * @var string
     */
    protected $basePath;

    /**
     * The manifest information
     *
     * @var object
     */
    public $manifest;

    /**
     * The manifest information as SimpleXMLElement
     *
     * @var SimpleXMLElement
     */
    public $manifestXml;

    /**
     * The config information
     *
     * @var SimpleXMLElement
     */
    public $config;

    /**
     * Class constructor, set the extension type.
     *
     * @param string $namespace The element of the extension
     * @param string $type      The type of extension
     * @param string $folder    The folder for plugins (only)
     */
    public function __construct($namespace, $type, $folder = '', $basePath = JPATH_SITE)
    {
        $this->type      = $type;
        $this->element   = strtolower($namespace);
        $this->folder    = $folder;
        $this->basePath  = rtrim($basePath, '/\\');
        $this->namespace = $namespace;

        $this->getManifest();

        $this->getDataFromDatabase();
    }

    /**
     * Get information about this extension from the database
     *
     * @return void
     */
    protected function getDataFromDatabase()
    {
        $element = $this->getElementToDb();

        // Load the extension info from database
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select(array(
                $db->qn('extension_id'),
                $db->qn('name'),
                $db->qn('enabled'),
                $db->qn('params')
            ))
            ->from('#__extensions')
            ->where($db->qn('type') . ' = ' . $db->q($this->type))
            ->where($db->qn('element') . ' = ' . $db->q($element));

        if ($this->type === 'plugin') {
            $query->where($db->qn('folder') . ' = ' . $db->q($this->folder));
        }

        $db->setQuery($query);
        $row = $db->loadObject();

        if (is_object($row)) {
            $this->id      = $row->extension_id;
            $this->name    = $row->name;
            $this->enabled = (bool)$row->enabled;
            $this->params  = new Registry($row->params);

        } else {
            $this->id      = null;
            $this->name    = null;
            $this->enabled = false;
            $this->params  = new Registry();
        }
    }

    /**
     * Check if the extension is enabled
     *
     * @return boolean True for enabled
     */
    public function isEnabled()
    {
        return (bool)$this->enabled;
    }

    /**
     * Get the path for the extension
     *
     * @return string The path
     */
    public function getExtensionPath()
    {
        $basePath = '';

        $folders = array(
            'component' => 'administrator/components/',
            'plugin'    => 'plugins/',
            'template'  => 'templates/',
            'library'   => 'libraries/',
            'cli'       => 'cli/',
            'module'    => 'modules/'
        );

        $basePath = $this->basePath . '/' . $folders[$this->type];

        switch ($this->type) {
            case 'plugin':
                $basePath .= $this->folder . '/';
                break;

            case 'module':
                if (!preg_match('/^mod_/', $this->element)) {
                    $basePath .= 'mod_';
                }
                break;

            case 'component':
                if (!preg_match('/^com_/', $this->element)) {
                    $basePath .= 'com_';
                }
                break;
        }

        $basePath .= $this->element;

        return $basePath;
    }

    /**
     * Get the full element
     *
     * @return string The full element
     */
    public function getFullElement()
    {
        return Helper::getFullElementFromInfo($this->type, $this->element, $this->folder);
    }

    /**
     * Get the element to match the database records.
     * Only components and modules have the prefix.
     *
     * @return string The element
     */
    public function getElementToDb()
    {
        $prefixes = array(
            'component' => 'com_',
            'module'    => 'mod_'
        );

        $fullElement = '';
        if (array_key_exists($this->type, $prefixes)) {
            if (!preg_match('/^' . $prefixes[$this->type] . '/', $this->element)) {
                $fullElement = $prefixes[$this->type];
            }
        }

        $fullElement .= $this->element;

        return $fullElement;
    }

    /**
     * Get manifest path for this extension
     *
     * @return string
     */
    public function getManifestPath()
    {
        $extensionPath = $this->getExtensionPath();

        // Templates or extension?
        if ($this->type === 'template') {
            $fileName = 'templateDetails.xml';
        } else {
            $fileName = $this->element . '.xml';

            if ($this->type === 'template') {
                $fileName = 'templateDetails.xml';
            }
        }

        $path = $extensionPath . "/{$fileName}";

        if (!file_exists($path)) {
            $path = $extensionPath . "/{$this->getElementToDb()}.xml";
        }


        return $path;
    }

    /**
     * Get extension manifest as SimpleXMLElement
     *
     * @param bool $force If true, force to load the manifest, ignoring the cached one
     *
     * @return SimpleXMLElement
     */
    public function getManifestAsSimpleXML($force = false)
    {
        if (!isset($this->manifestXml) || $force) {
            $path = $this->getManifestPath();

            if (JFile::exists($path)) {
                $this->manifestXml = simplexml_load_file($path);
            } else {
                $this->manifestXml = false;
            }
        }

        return $this->manifestXml;
    }

    /**
     * Get extension information
     *
     * @param bool $force If true, force to load the manifest, ignoring the cached one
     *
     * @return object
     */
    public function getManifest($force = false)
    {
        if (!isset($this->manifest) || $force) {
            $xml = $this->getManifestAsSimpleXML($force);
            if (!empty($xml)) {
                $this->manifest = (object)json_decode(json_encode($xml));
            } else {
                $this->manifest = false;
            }
        }

        return $this->manifest;
    }

    /**
     * Get extension config file
     *
     * @param bool $force Force to reload the config file
     *
     * @return SimpleXMLElement
     */
    public function getConfig($force = false)
    {
        if (!isset($this->config) || $force) {
            $this->config = null;

            $path = $this->getExtensionPath() . '/config.xml';

            if (file_exists($path)) {
                $this->config = simplexml_load_file($path);
            }
        }

        return $this->config;
    }

    /**
     * Returns the update URL from database
     *
     * @return string
     */
    public function getUpdateURL()
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('sites.location')
            ->from('#__update_sites AS sites')
            ->leftJoin('#__update_sites_extensions AS extensions ON (sites.update_site_id = extensions.update_site_id)')
            ->where('extensions.extension_id = ' . $this->id);

        $row = $db->setQuery($query)->loadResult();

        return $row;
    }

    /**
     * Set the update URL
     *
     * @param string $url
     */
    public function setUpdateURL($url)
    {
        $db = JFactory::getDbo();

        // Get the update site id
        $join  = $db->qn('#__update_sites_extensions') . ' AS extensions '
            . 'ON (sites.update_site_id = extensions.update_site_id)';
        $query = $db->getQuery(true)
            ->select('sites.update_site_id')
            ->from($db->qn('#__update_sites') . ' AS sites')
            ->leftJoin($join)
            ->where('extensions.extension_id = ' . $this->id);

        $siteId = (int)$db->setQuery($query)->loadResult();

        if (!empty($siteId)) {
            $query = $db->getQuery(true)
                ->update($db->qn('#__update_sites'))
                ->set($db->qn('location') . ' = ' . $db->q($url))
                ->where($db->qn('update_site_id') . ' = ' . $siteId);

            $db->setQuery($query)->execute();
        }
    }

    /**
     * Store the params on the database
     *
     * @return void
     */
    public function storeParams()
    {
        $db = JFactory::getDbo();

        $updateObject = (object)array(
            'params'       => $this->params->toString(),
            'extension_id' => $this->id
        );

        $db->updateObject('#__extensions', $updateObject, array('extension_id'));
    }

    /**
     * Get extension name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get extension id
     *
     * @return int
     */
    public function getId()
    {
        return (int)$this->id;
    }

    /**
     * @TODO: Move to the licensed class?
     *
     * @return string
     */
    public function getFooterMarkup()
    {
        // Check if we have a dedicated config.xml file
        $configPath = $this->getExtensionPath() . '/config.xml';
        if (JFile::exists($configPath)) {
            $config = $this->getConfig();

            if (is_object($config)) {
                $footerElement = $config->xpath('//field[@type="customfooter"]');
            }
        } else {
            $manifest = $this->getManifestAsSimpleXML();

            if (is_object($manifest)) {
                $footerElement = $manifest->xpath('//field[@type="customfooter"]');
            }
        }

        if (!empty($footerElement)) {
            if (!class_exists('JFormFieldCustomFooter')) {
                require_once $this->getExtensionPath() . '/form/fields/customfooter.php';
            }

            $field                = new JFormFieldCustomFooter();
            $field->fromInstaller = true;
            return $field->getInputUsingCustomElement($footerElement[0]);
        }

        return '';
    }

    /**
     * Returns the extension's version collected from the manifest file
     *
     * @return string The extension's version
     */
    public function getVersion()
    {
        if (!empty($this->manifest->version)) {
            return $this->manifest->version;
        }

        return null;
    }
}

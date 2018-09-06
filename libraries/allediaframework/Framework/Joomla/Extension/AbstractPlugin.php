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
use JPlugin;
use JFactory;

jimport('joomla.plugin.plugin');

abstract class AbstractPlugin extends JPlugin
{
    /**
     * Alledia Extension instance
     *
     * @var object
     */
    protected $extension;

    /**
     * Library namespace
     *
     * @var string
     */
    protected $namespace;

    /**
     * Method used to load the extension data. It is not on the constructor
     * because this way we can avoid to load the data if the plugin
     * will not be used.
     *
     * @return void
     */
    protected function init()
    {
        $this->loadExtension();

        // Load the libraries, if existent
        $this->extension->loadLibrary();

        $this->loadLanguage();
    }

    /**
     * Method to load the language files
     *
     * @return void
     */
    public function loadLanguage($extension = '', $basePath = JPATH_ADMINISTRATOR)
    {
        parent::loadLanguage($extension, $basePath);

        $systemStrings = 'plg_' . $this->_type . '_' . $this->_name . '.sys';
        parent::loadLanguage($systemStrings, $basePath);
    }

    /**
     * Method to load the extension data
     *
     * @return void
     */
    protected function loadExtension()
    {
        if (!isset($this->extension)) {
            $this->extension = Factory::getExtension($this->namespace, 'plugin', $this->_type);
        }
    }

    /**
     * Check if this extension is licensed as pro
     *
     * @return boolean True for pro version
     */
    protected function isPro()
    {
        return $this->extension->isPro();
    }
}

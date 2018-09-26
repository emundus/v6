<?php
/**
 * @package   AllediaInstaller
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC., All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework\Joomla\Extension;

defined('_JEXEC') or die();

use Alledia\Framework\AutoLoader;


/**
 * Licensed class, for extensions with Free and Pro versions
 */
class Licensed extends Generic
{
    /**
     * License type: free or pro
     *
     * @var string
     */
    protected $license;

    /**
     * The path for the pro library
     *
     * @var string
     */
    protected $proLibraryPath;

    /**
     * The path for the free library
     *
     * @var string
     */
    protected $libraryPath;

    /**
     * Class constructor, set the extension type.
     *
     * @param string $namespace The element of the extension
     * @param string $type      The type of extension
     * @param string $folder    The folder for plugins (only)
     */
    public function __construct($namespace, $type, $folder = '', $basePath = JPATH_SITE)
    {
        parent::__construct($namespace, $type, $folder, $basePath);

        $this->license = @strtolower($this->manifest->alledia->license);

        // Make sure we are using the correct namespace
        $this->namespace = @$this->manifest->alledia->namespace;

        $this->getLibraryPath();
        $this->getProLibraryPath();
    }

    /**
     * Check if the license is pro
     *
     * @return boolean True for pro license
     */
    public function isPro()
    {
        return $this->license === 'pro';
    }

    /**
     * Check if the license is free
     *
     * @return boolean True for free license
     */
    public function isFree()
    {
        return !$this->isPro();
    }

    /**
     * Get the include path for the include on the free library, based on the extension type
     *
     * @return string The path for pro
     */
    public function getLibraryPath()
    {
        if (empty($this->libraryPath)) {
            $basePath = $this->getExtensionPath();

            $this->libraryPath = $basePath . '/library';
        }

        return $this->libraryPath;
    }

    /**
     * Get the include path for the include on the pro library, based on the extension type
     *
     * @return string The path for pro
     */
    public function getProLibraryPath()
    {
        if (empty($this->proLibraryPath)) {
            $basePath = $this->getLibraryPath();

            $this->proLibraryPath = $basePath . '/Pro';
        }

        return $this->proLibraryPath;
    }

    /**
     * Loads the library, if existent (including the Pro Library)
     *
     * @return bool
     */
    public function loadLibrary()
    {
        $libraryPath = $this->getLibraryPath();

        // If we have a library path, lets load it
        if (file_exists($libraryPath)) {
            // Setup autoloaded libraries
            AutoLoader::register('Alledia\\' . $this->namespace, $libraryPath);

            return true;
        }

        return false;
    }
}

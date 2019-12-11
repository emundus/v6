<?php
/**
 * Dropfiles
 *
 * @version   1.0.0
 * @package   Dropfiles Links for JCE
 * @author    JoomUnited http://www.joomunited.com
 * @copyright Copyright © 2015 JoomUnited. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_WF_EXT') || die('Restricted Access!');
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps -- We must use this
/**
 * Class WFLinkBrowser_Dropfiles
 */
class WFLinkBrowser_Dropfiles extends JObject
{
    /**
     * Option
     *
     * @var array
     */
    public $option = array();

    /**
     * Adapters
     *
     * @var array
     */
    public $adapters = array();

    /**
     * WFLinkBrowser_Dropfiles constructor.
     *
     * @since version
     */
    public function __construct()
    {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        $path = dirname(__FILE__) . DS . 'dropfileslinks';
        $files = JFolder::files($path, '\.(php)$');

        if (!empty($files)) {
            foreach ($files as $file) {
                require_once($path . DS . $file);
                $classname        = 'Dropfileslinks' . ucfirst(basename($file, '.php'));
                $this->adapters[] = new $classname;
            }
        }
    }

    /**
     * Get Instance
     *
     * @return WFLinkBrowser_Dropfiles
     * @since  version
     */
    public function getInstance()
    {
        static $instance;

        if (!is_object($instance)) {
            $instance = new WFLinkBrowser_Dropfiles();
        }

        return $instance;
    }

    /**
     * Display
     *
     * @return void
     * @since  version
     */
    public function display()
    {
    }

    /**
     * Check enable
     *
     * @return mixed
     * @since  version
     */
    public function isEnabled()
    {
        $wf = WFEditorPlugin::getInstance();
        return $wf->checkAccess($wf->getName() . '.links.dropfileslinks', 1);
    }

    /**
     * Get option
     *
     * @return array
     * @since  version
     */
    public function getOption()
    {
        foreach ($this->adapters as $adapter) {
            $this->option[] = $adapter->getOption();
        }
        return $this->option;
    }

    /**
     * Get list
     *
     * @return string
     * @since  version
     */
    public function getList()
    {
        $list = '';

        foreach ($this->adapters as $adapter) {
            $list .= $adapter->getList();
        }
        return $list;
    }

    /**
     * Get Links
     *
     * @param object $args Arguments
     *
     * @return mixed
     * @since  version
     */
    public function getLinks($args)
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->getOption() === $args->option) {
                if (property_exists($args, 'task')) {
                    $task = $args->task;
                } else {
                    $task = 'getFiles';
                }

                if ($adapter->getTask() === $task) {
                    return $adapter->getLinks($args);
                }
            }
        }
    }
}

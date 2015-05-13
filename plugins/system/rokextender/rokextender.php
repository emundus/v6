<?php
/**
 * @version        2.0.0 October 31, 2012
 * @author        RocketTheme http://www.rockettheme.com
 * @copyright     Copyright (C) 2007 - 2012 RocketTheme, LLC
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * RokExtender Plugin
 */
class plgSystemRokExtender extends JPlugin
{
    /**
     * Loads all RokExtender plugins and fires onAfterInitialise if it needs to.
     *
     * @return void
     */
    public function onAfterInitialise()
    {
        jimport('joomla.filesystem.file');
        //get list of registered "files"
        $registered_files = explode(',', $this->params->get('registered', ''));
        foreach ($registered_files as $registered_file)
        {
            $dispatcher = JDispatcher::getInstance();
            $className = basename(trim($registered_file), '.php');
            $fullpath = JPATH_ROOT . $registered_file;
            if (file_exists($fullpath) && is_file($fullpath))
            {
                require_once($fullpath);
            }
            if (class_exists($className))
            {
                // Instantiate and register the plugin.
                $class = new $className($dispatcher);
                $args = array('event' => 'onafterinitialise');
                $class->update($args);
            }
        }
    }

    /**
     * Function to register a RokExtender Plugin file
     * @static
     * @param  string $path the path to the plugin file
     * @return bool true if registered successfully false if there was an error
     */
    public static function registerExtenderPlugin($path)
    {
        $db = JFactory::getDbo();
        $table = JTable::getInstance('extension');

        $id = $table->find(array('type' => 'plugin', 'element' => 'rokextender', 'folder' => 'system'));
        if (!$table->load($id))
        {
            //$this->setError($table->getError());
            return false;
        }

        $params = new JRegistry();
        $params->loadString($table->params);
        $registered_files = explode(',', $params->get('registered', ''));

        if (!in_array($path, $registered_files))
        {
            $registered_files[] = $path;
        }

        // clean up files not there
        $actually_there = $registered_files;

        foreach($registered_files as $registered_loc => $registered_file)
        {
            $fullpath = JPATH_ROOT . $registered_file;
            if (!file_exists($fullpath) || !is_file($fullpath)){
                unset($actually_there[$registered_loc]);
            }
        }
        $params->set('registered', implode(',', $actually_there));
        $table->params = $params->toString();

        // pre-save checks
        if (!$table->check())
        {
            //$this->setError($table->getError());
            return false;
        }

        // save the changes
        if (!$table->store())
        {
            //$this->setError($table->getError());
            return false;
        }
    }

    /**
     * Function to unregister a RokExtender Plugin file
     * @static
     * @param  string $path the path to the plugin file
     * @return bool true if unregistered successfully false if there was an error
     */
    public static function unregisterExtenderPlugin($path)
    {
        $db = JFactory::getDbo();
        $table = JTable::getInstance('extension');

        $id = $table->find(array('type' => 'plugin', 'element' => 'rokextender', 'folder' => 'system'));
        if (!$table->load($id))
        {
            //$this->setError($table->getError());
            return false;
        }

        $params = new JRegistry();
        $params->loadString($table->params);
        $registered_files = explode(',', $params->get('registered', ''));

        if (($loc = array_search($path, $registered_files)) !== false)
        {
            unset($registered_files[$loc]);
        }
        $params->set('registered', implode(',', $registered_files));
        $table->params = $params->toString();

        // pre-save checks
        if (!$table->check())
        {
            //$this->setError($table->getError());
            return false;
        }

        // save the changes
        if (!$table->store())
        {
            //$this->setError($table->getError());
            return false;
        }
    }
}
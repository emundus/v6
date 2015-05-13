<?php
/**
* @version   $Id$
* @package   Jumi
* @copyright Copyright (C) 2006 - 2011 Edvard Ananyan, Simon Poghosyan. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die('Restricted access');

class com_jumiInstallerScript {

    /**
     * method to install the component
     *
     * @return void
     */
    function install($parent) {
        // $parent is the class calling this method
        //$parent->getParent()->setRedirectURL('index.php?option=com_helloworld');

        // installing module
        $module_installer = new JInstaller;
        if(@$module_installer->install(dirname(__FILE__).DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'module'))
            echo 'Module install success', '<br />';
        else
            echo 'Module install failed', '<br />';

        // installing plugin
        $plugin_installer = new JInstaller;
        if($plugin_installer->install(dirname(__FILE__).DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'plugin'))
            echo 'Plugin install success', '<br />';
        else
            echo 'Plugin install failed', '<br />';

        // installing router
        $plugin_installer = new JInstaller;
        if($plugin_installer->install(dirname(__FILE__).DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'router'))
            echo 'Router install success', '<br />';
        else
            echo 'Router install failed', '<br />';

        // enabling plugin
        $db = JFactory::getDBO();
        $db->setQuery('update #__extensions set enabled = 1 where element = "jumi" and folder = "system"');
        $db->query();

        // enabling router
        $db->setQuery('update #__extensions set enabled = 1, ordering = 100 where element = "jumirouter" and folder = "system"');
        $db->query();
    }

    /**
     * method to uninstall the component
     *
     * @return void
     */
    function uninstall($parent) {
        // $parent is the class calling this method
        //echo '<p>' . JText::_('COM_HELLOWORLD_UNINSTALL_TEXT') . '</p>';

        $db = JFactory::getDBO();

        // uninstalling jumi module
        $db->setQuery("select extension_id from #__extensions where name = 'Jumi' and type = 'module' and element = 'mod_jumi'");
        $jumi_module = $db->loadObject();
        $module_uninstaller = new JInstaller;
        if(@$module_uninstaller->uninstall('module', $jumi_module->extension_id))
            echo 'Module uninstall success', '<br />';
        else {
            echo 'Module uninstall failed', '<br />';
        }

        // uninstalling jumi plugin
        $db->setQuery("select extension_id from #__extensions where name = 'System - Jumi' and type = 'plugin' and element = 'jumi'");
        $jumi_plugin = $db->loadObject();
        $plugin_uninstaller = new JInstaller;
        if($plugin_uninstaller->uninstall('plugin', $jumi_plugin->extension_id))
            echo 'Plugin uninstall success', '<br />';
        else
            echo 'Plugin uninstall failed', '<br />';

        // uninstalling jumi router
        $db->setQuery("select extension_id from #__extensions where name = 'System - Jumi Router' and type = 'plugin' and element = 'jumirouter'");
        $jumi_router = $db->loadObject();
        $plugin_uninstaller = new JInstaller;
        if($plugin_uninstaller->uninstall('plugin', $jumi_router->extension_id))
            echo 'Router uninstall success', '<br />';
        else
            echo 'Router uninstall failed', '<br />';
    }

    /**
     * method to update the component
     *
     * @return void
     */
    function update($parent) {
        // $parent is the class calling this method
        //echo '<p>' . JText::_('COM_HELLOWORLD_UPDATE_TEXT') . '</p>';
    }

    /**
     * method to run before an install/update/uninstall method
     *
     * @return void
     */
    function preflight($type, $parent) {
        // $parent is the class calling this method
        // $type is the type of change (install, update or discover_install)
        //echo '<p>' . JText::_('COM_HELLOWORLD_PREFLIGHT_' . $type . '_TEXT') . '</p>';
    }

    /**
     * method to run after an install/update/uninstall method
     *
     * @return void
     */
    function postflight($type, $parent) {
        // $parent is the class calling this method
        // $type is the type of change (install, update or discover_install)
        //echo '<p>' . JText::_('COM_HELLOWORLD_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
    }
}
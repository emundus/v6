<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for 
 *   optmized downloads
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall. All rights reserved.
 * @license GNU/GPLv3, See LICENSE file 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>. 
 * 
 * This plugin includes other copyrighted works. See individual 
 * files for details.
 */
defined('_JEXEC') or die('Restricted access');

class Pkg_jch_optimizeInstallerScript
{

        public function preflight($type, $parent)
        {
                $app = JFactory::getApplication();

                if ($type == 'install')
                {
                        if (version_compare(PHP_VERSION, '5.6.0', '<'))
                        {
                                $app->enqueueMessage('This plugin requires PHP 5.6.0 or greater to work. Your installed version is ' . PHP_VERSION, 'error');

                                return false;
                        }
                }
                
                $compatible = TRUE;
                $minimum_version = '3.6.5';

                if (version_compare(JVERSION, $minimum_version, '<'))
                {
                        $compatible = FALSE;
                }

                if (!$compatible)
                {
                        $app->enqueueMessage('JCH Optimize is not compatible with your version of Joomla!. This plugin requires v'. $minimum_version . ' or greater to work. Your installed version is ' . JVERSION, 'error');

                        return FALSE;
                }

                $manifest    = $parent->get('manifest');
                $new_variant = (string) $manifest->variant;

                $file = JPATH_PLUGINS . '/system/jch_optimize/jch_optimize.xml';

                if (file_exists($file))
                {
                        $xml         = JFactory::getXML($file);
                        $old_variant = (string) $xml->variant;

                        if ($old_variant == 'PRO' && $new_variant == 'FREE')
                        {
                                $app->enqueueMessage('You are trying to install the FREE version of JCH Optimize but you currently have the PRO version installed. You must uninstall the PRO version before you can install the FREE version.', 'error');

                                return false;
                        }
                }
        }

        /**
         * 
         * @param type $type
         * @param type $parent
         */
        public function postflight($type, $parent)
        {
                require_once(JPATH_ROOT . '/plugins/system/jch_optimize/jchoptimize/loader.php');
                require_once(JPATH_ROOT . '/plugins/system/jch_optimize/fields/autoorder.php'); 
                if ($type == 'install')
                {
                        JFormFieldAutoorder::fixFilePermissions(true);
			JFormFieldAutoorder::leverageBrowserCaching(true);
                }

                if ($type == 'update')
                {
                        JFormFieldAutoorder::cleanCache(true);

                        $params = JchPlatformPlugin::getPluginParams();

                        if ($params->get('bottom_js', '0') == '1')
                        {
                                $params->set('pro_bottom_js', '1');

                                JchPlatformPlugin::saveSettings($params);
                        }
                }

                JFormFieldAutoorder::orderPlugins(true);
        }

        /**
         * 
         * @param type $parent
         */
        public function uninstall($parent)
        {
                jimport('joomla.filesystem.folder');
                require_once(JPATH_ROOT . '/plugins/system/jch_optimize/jchoptimize/loader.php');
                require_once(JPATH_ROOT . '/plugins/system/jch_optimize/fields/autoorder.php');

                $sprites = JPATH_ROOT . '/images/jch-optimize';

                if (file_exists($sprites))
                {
                        JFolder::delete($sprites);
                }

		JFormFieldAutoorder::cleanCache(true);
		JchOptimizeAdmin::cleanHtaccess();
        }

}

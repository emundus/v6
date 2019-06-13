<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optmized downloads
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
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
 */
defined('_JEXEC') or die('Restricted access');

class JchPlatformPlugin implements JchInterfacePlugin
{

        protected static $plugin = null;

        /**
         * 
         * @return type
         */
        public static function getPluginId()
        {
                $plugin = static::loadjch();

                return $plugin->extension_id;
        }

        /**
         * 
         * @return type
         */
        public static function getPlugin()
        {
                $plugin = static::loadjch();

                return $plugin;
        }

        /**
         * 
         * @return type
         */
        private static function loadjch()
        {
                if (self::$plugin !== null)
                {
                        return self::$plugin;
                }

               // $cache = JchPlatformCache::getCacheObject('output');
               // $id    = 'jchoptimizeplugincache';

               // if (!self::$plugin = $cache->get($id))
               // {
                        $db    = JFactory::getDbo();
                        $query = $db->getQuery(true)
                                ->select('folder AS type, element AS name, params, extension_id')
                                ->from('#__extensions')
                                ->where('element = ' . $db->quote('jch_optimize'))
                                ->where('type = ' . $db->quote('plugin'));

                        self::$plugin = $db->setQuery($query)->loadObject();

               //         $cache->store(self::$plugin, $id);
               // }

                return self::$plugin;
        }

        /**
         * 
         */
        public static function getPluginParams()
        {
                static $params = null;

                if (is_null($params))
                {
                        $plugin       = self::getPlugin();
                        $pluginParams = new JRegistry();
                        $pluginParams->loadString($plugin->params);

                        $params = JchPlatformSettings::getInstance($pluginParams);
                }

                return $params;
        }

        /**
         * 
         * @param type $params
         */
        public static function saveSettings($params)
        {
                $oPlugin          = JchPlatformPlugin::getPlugin();
                $oPlugin->params  = $params->toArray();
                $oPlugin->name    = 'PLG_SYSTEM_JCH_OPTIMIZE';
                $oPlugin->element = 'jch_optimize';

                $oData = new JRegistry($oPlugin);
                $aData = $oData->toArray();

                $oController = new JControllerLegacy;

                $oController->addModelPath(JPATH_ADMINISTRATOR . '/components/com_plugins/models', 'PluginsModel');
                $oPluginModel = $oController->getModel('Plugin', 'PluginsModel');

                if ($oPluginModel->save($aData) === FALSE)
                {
                        JchOptimizeLogger::log(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $oPluginModel->getError()), $params);
                }
        }

}

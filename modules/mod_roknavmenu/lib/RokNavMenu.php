<?php
/**
 * @version   $Id: RokNavMenu.php 30073 2016-03-09 08:29:49Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
require_once(dirname(__FILE__) . "/librokmenu/includes.php");
require_once(dirname(__FILE__) . "/helper.php");

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

if (!class_exists('RokNavMenu'))
{
    class RokNavMenu extends RokMenu
    {
        const TOP_LEVEL_PARENT_ID = 1;
        static $themes = array();
        static $current_template;
        static $catalogs_loaded = false;

        public function __construct($args)
        {
            self::loadCatalogs();
            parent::__construct($args);
        }

        protected function getProvider()
        {
            require_once(dirname(__FILE__) . '/providers/RokMenuProviderJoomla16.php');
            return new RokMenuProviderJoomla16($this->args);
        }

        protected function getRenderer()
        {
            // if its a registered theme its a 2x theme
            if (array_key_exists('theme', $this->args) && array_key_exists($this->args['theme'], self::$themes))
            {
                $themeinfo = self::$themes[$this->args['theme']];
                $themeclass = $themeinfo['class'];

                $renderer = new RokNavMenu2XRenderer();

                $theme = new $themeclass();
                $renderer->setTheme($theme);
            }
            else
            {
                // its a 1x theme
                $renderer = new RokNavMenu1XRenderer();
            }
            return $renderer;
        }

        public function render()
        {
            $this->renderHeader();
            return $this->renderMenu();
        }

        public static function registerTheme($path, $name, $fullname, $themeClass)
        {
            $theme = array('name' => $name, 'fullname' => $fullname, 'path' => $path, 'class' => $themeClass);
            self::$themes[$name] = $theme;
        }

        public static function loadCatalogs()
        {
            if (!self::$catalogs_loaded) {
                // load the module themes catalog
                require_once(JPATH_ROOT . '/modules/mod_roknavmenu/themes/catalog.php');

                foreach (self::getTemplates() as $template) {
                    $template_theme_catalog = JPATH_ROOT . '/templates/' . $template . "/html/mod_roknavmenu/themes/catalog.php";
                    if (JFile::exists($template_theme_catalog)) {
                        //load the templates themes
                        include_once($template_theme_catalog);
                    }
                }
                self::$catalogs_loaded = true;
            }
        }

        protected static function getTemplates()
        {
            $Itemid = JFactory::getApplication()->input->getInt('Itemid');
            $templates = null;
            $db = JFactory::getDbo();

            if(!is_null($Itemid)){
                // Load specific style if one is assigned
                $query = $db->getQuery(true);
                $query->select('ts.template');
                $query->from('#__template_styles AS ts');
                $query->join('INNER','#__menu AS m ON ts.id=m.template_style_id');
                $query->where('m.id = '.$Itemid);
                $query->where('m.template_style_id != 0');

                $db->setQuery($query);
                $templates = $db->loadColumn();
            }
        	
	        if ($templates){
	        	return $templates;
	        }
	        	
            // Load styles normally if no specific style is assigned
            $query = $db->getQuery(true);
            $query->select('template');
            $query->from('#__template_styles');
            $query->where('home = 1');
            $query->where('client_id = 0');

            $db->setQuery($query);
            $templates = $db->loadColumn();
            return $templates;
        }

        /**
         * Load published modules
         *
         * @return	array
         */
        public static function &loadModules()
        {
            static $clean;

            if (isset($clean)) {
                return $clean;
            }

            $Itemid 	= JFactory::getApplication()->input->getInt('Itemid');
            $app		= JFactory::getApplication();
            $user		= JFactory::getUser();
            $groups		= implode(',', $user->getAuthorisedViewLevels());
            $lang 		= JFactory::getLanguage()->getTag();
            $clientId 	= (int) $app->getClientId();

            $cache 		= JFactory::getCache ('com_modules', '');
            $cacheid 	= md5(serialize(array($Itemid, $groups, $clientId, $lang)));

            if (!($clean = $cache->get($cacheid))) {
                $db	= JFactory::getDbo();

                $query = $db->getQuery(true);
                $query->select('id, title, module, position, content, showtitle, params, mm.menuid');
                $query->from('#__modules AS m');
                $query->join('LEFT','#__modules_menu AS mm ON mm.moduleid = m.id');
                $query->where('m.published = 1');

                $date = JFactory::getDate();
                $now = $date->toSql();
                $nullDate = $db->getNullDate();
                $query->where('(m.publish_up = '.$db->Quote($nullDate).' OR m.publish_up <= '.$db->Quote($now).')');
                $query->where('(m.publish_down = '.$db->Quote($nullDate).' OR m.publish_down >= '.$db->Quote($now).')');

                $query->where('m.access IN ('.$groups.')');
                $query->where('m.client_id = '. $clientId);
                $query->where('(mm.menuid = '. (int) $Itemid .' OR mm.menuid <= 0)');

                // Filter by language
                if ($app->isSite() && $app->getLanguageFilter()) {
                    $query->where('m.language IN (' . $db->Quote($lang) . ',' . $db->Quote('*') . ')');
                }

                $query->order('position, ordering');

                // Set the query
                $db->setQuery($query);
                $modules = $db->loadObjectList();
                $clean	= array();

                if($db->getErrorNum()){
                    JError::raiseWarning(500, JText::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $db->getErrorMsg()));
                    return $clean;
                }

                // Apply negative selections and eliminate duplicates
                $negId	= $Itemid ? -(int)$Itemid : false;
                $dupes	= array();
                for ($i = 0, $n = count($modules); $i < $n; $i++)
                {
                    $module = &$modules[$i];

                    // The module is excluded if there is an explicit prohibition, or if
                    // the Itemid is missing or zero and the module is in exclude mode.
                    $negHit	= ($negId === (int) $module->menuid)
                            || (!$negId && (int)$module->menuid < 0);

                    if (isset($dupes[$module->id]))
                    {
                        // If this item has been excluded, keep the duplicate flag set,
                        // but remove any item from the cleaned array.
                        if ($negHit) {
                            unset($clean[$module->id]);
                        }
                        continue;
                    }
                    $dupes[$module->id] = true;

                    // Only accept modules without explicit exclusions.
                    if (!$negHit)
                    {
                        //determine if this is a custom module
                        $file				= $module->module;
                        $custom				= substr($file, 0, 4) == 'mod_' ?  0 : 1;
                        $module->user		= $custom;
                        // Custom module name is given by the title field, otherwise strip off "com_"
                        $module->name		= $custom ? $module->title : substr($file, 4);
                        $module->style		= null;
                        $module->position	= strtolower($module->position);
                        $clean[$module->id]	= $module;
                    }
                }
                unset($dupes);
                // Return to simple indexing that matches the query order.
                $clean = array_values($clean);

                $cache->store($clean, $cacheid);
            }

            return $clean;
        }
    }


}

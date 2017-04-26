<?php
/**
 * @version   $Id: RokNavMenuEvents.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 */
class RokNavMenuEvents extends JPlugin
{

    public function onContentPrepareForm($form, $data)
    {


        $app = JFactory::getApplication();
        if (!$app->isAdmin()) return;

        $option = JFactory::getApplication()->input->get('option');
        $layout = JFactory::getApplication()->input->get('layout');
        $task = JFactory::getApplication()->input->get('task');
        $id = JFactory::getApplication()->input->getInt('id');


        $module = $this->getModuleType($data);


        if (in_array($option, array('com_modules', 'com_advancedmodules')) && $layout == 'edit' && $module == 'mod_roknavmenu')
        {

            require_once(JPATH_ROOT . '/modules/mod_roknavmenu/lib/RokNavMenu.php');
            require_once(JPATH_ROOT . '/modules/mod_roknavmenu/lib/RokSubfieldForm.php');
            JForm::addFieldPath(JPATH_ROOT . '/modules/mod_roknavmenu/fields');
            // Load 2x Catalog Themes
            RokNavMenu::loadCatalogs();
            // Load 1x Themes
            $this->registerOldThemes();

            foreach (RokNavMenu::$themes as $theme_name => $theme_info)
            {
                $params_file = $theme_info['path'] . "/parameters.xml";
                if (JFile::exists($params_file))
                {
                    $form->loadFile($params_file, false);
                }

                $fields_folder = $theme_info['path'] . "/fields";
                if (JFolder::exists($fields_folder))
                {
                    JForm::addFieldPath($fields_folder);
                }

                $language_path = $theme_info['path'] . "/language";
                if (JFolder::exists($language_path)){
                    $language =JFactory::getLanguage();
                    $language->load($theme_name ,$theme_info['path'], $language->getTag(), true);
                }

            }

            $subfieldform = RokSubfieldForm::getInstanceFromForm($form);

            if (!empty($data) && isset($data->params)) $subfieldform->setOriginalParams($data->params);

            if ($task == 'save' || $task == 'apply')
            {
                $subfieldform->makeSubfieldsVisable();
            }
        }
        else if ($option == 'com_menus' && $layout == 'edit'){
            JForm::addFieldPath(JPATH_ROOT . '/modules/mod_roknavmenu/fields');
            // Load 2x Catalog Themes
            require_once(JPATH_ROOT . "/modules/mod_roknavmenu/lib/RokNavMenu.php");
            RokNavMenu::loadCatalogs();
            // Load 1x Themes
            $this->registerOldThemes();
            foreach (RokNavMenu::$themes as $theme_name => $theme_info)
            {
                $item_file = $theme_info['path'] . "/item.xml";
                if (JFile::exists($item_file))
                {
                    $form->loadFile($item_file, true);
                }

                $fields_folder = $theme_info['path'] . "/fields";
                if (JFolder::exists($fields_folder))
                {
                    JForm::addFieldPath($fields_folder);
                }
            }
        }

    }

    function registerOldThemes()
    {
            $filter		= '.';
            $exclude	= array('.svn', 'CVS','.DS_Store','__MACOSX');

            // path to directory
            $template_themes_path = '/templates/'.$this->_getFrontSideTemplate().'/html/mod_roknavmenu/themes';
            $template_themes_full_path = JPath::clean(JPATH_ROOT.$template_themes_path);

            $module_themes_path = '/modules/mod_roknavmenu/themes';
            $module_themes_full_path = JPath::clean(JPATH_ROOT.$module_themes_path);


             /** Get the Template Themes parameters **/
            if (JFolder::exists($template_themes_full_path) && !JFile::exists($template_themes_full_path."/catalog.php")) {
                $folders = JFolder::folders($template_themes_full_path, $filter);
                if ( is_array($folders) )
                {
                    while (list($key, $val) = each($folders)) {
                        $folder =& $folders[$key];
                        if ($exclude)
                        {
                            if (preg_match( chr( 1 ) . $exclude . chr( 1 ), $folder ))
                            {
                                continue;
                            }
                        }

                        $theme_full_path = $template_themes_full_path.'/'.$folder;

                        $fullname = 'Template theme - '.$folder;
                        $class = 'RokNavMenuFormatterTemplate'.str_replace('-', '', $folder);
                        $name = $folder;

                        RokNavMenu::registerTheme($theme_full_path, $name, $fullname, $class);
                    }
                }
            }
             /** Get the Default Themes parameters **/
            if (JFolder::exists($module_themes_full_path) && !JFile::exists($module_themes_full_path."/catalog.php")) {
                $folders = JFolder::folders($module_themes_full_path, $filter);
                if ( is_array($folders) )
                {
                    while (list($key, $val) = each($folders)) {
                        $folder =& $folders[$key];
                        if ($exclude)
                        {
                            if (preg_match( chr( 1 ) . $exclude . chr( 1 ), $folder ))
                            {
                                continue;
                            }
                        }

                        $theme_full_path = $module_themes_full_path.'/'.$folder;

                        $fullname = 'Template theme - '.$folder;
                        $class = 'RokNavMenuFormatterTemplate'.str_replace('-', '', $folder);
                        $name = $folder;

                        RokNavMenu::registerTheme($theme_full_path, $name, $fullname, $class);
                    }
                }
            }
    }


    function _getFrontSideTemplate() {
		if (empty($this->_front_side_template)) {
			$db	= JFactory::getDbo();
            $query	= $db->getQuery(true);
			// Get the current default template
			$query->select('template');
			$query->from('#__template_styles');
			$query->where('client_id = 0 AND home = 1');
			$db->setQuery((string)$query);
			$defaultemplate = $db->loadResult();
			$this->_front_side_template = $defaultemplate;
		}
		return $this->_front_side_template;
	}


    protected function getModuleType(&$data)
    {
        if (is_array($data) && isset($data['module']))
        {
            return $data['module'];
        }
        elseif (is_array($data) && empty($data))
        {
            $form = JRequest::getVar('jform');
            if (is_array($form) && array_key_exists('module',$form))
            {
                return $form['module'];
            }
        }
        if (is_object($data) && method_exists( $data , 'get'))
        {
            return $data->get('module');
        }
        return '';
    }
}


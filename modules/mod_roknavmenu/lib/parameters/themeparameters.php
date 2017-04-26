<?php
/**
 * @version   $Id: themeparameters.php 18919 2014-02-20 23:10:04Z kevin $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a file list from a directory in the current templates directory
 */

class JElementThemeParameters extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'TemplateFilelist';

	var $_front_side_template;
	
	function fetchElement($name, $value, &$node, $control_name)
	{
		jimport( 'joomla.filesystem.folder' );
		jimport( 'joomla.filesystem.file' );
		$doc =JFactory::getDocument();
		$lang = JFactory::getLanguage();

		$parameter_sets = array();
        
		$filter		= $node->attributes('filter');
		$exclude	= $node->attributes('exclude');


        // Load 2x Catalog Themes
        require_once(dirname(__FILE__) . "/../RokNavMenu.php");
        RokNavMenu::setFrontSideTemplate();
        RokNavMenu::loadCatalogs();



        foreach(RokNavMenu::$themes as $theme_name => $theme_info){
            $lang_file_path = JPath::clean($theme_info['path'].'/language/'.$lang->_lang.'.ini');
            $this->loadLangFile($lang_file_path, $theme_info['fullname']);

            $parms_file_path = JPath::clean($theme_info['path'].'/parameters.xml');
            if (JFile::exists($parms_file_path)) {
		        $parameters = new JForm( $this->_parent->_raw, JPath::clean($parms_file_path));
			    $parameter_sets[$theme_name] = $parameters->getParams();
			}
        }


		
		// path to directory
		$template_themes_path = '/templates/'.$this->_getFrontSideTemplate().'/html/mod_roknavmenu/themes';
		$template_themes_full_path = JPath::clean(JPATH_ROOT.$template_themes_path);
		$template_theme_text = JText::_("Template theme");
		
		$module_themes_path = '/modules/mod_roknavmenu/themes'; 
		$module_themes_full_path = JPath::clean(JPATH_ROOT.$module_themes_path);
		$module_theme_text = JText::_("Default theme");
		
		$module_js_path = JURI::root(true).'/modules/mod_roknavmenu/lib/js';
		$doc->addScript($module_js_path."/switcher".$this->_getJSVersion().".js");
		$doc->addScriptDeclaration("window.addEvent('domready', function() {new NavMenuSwitcher('paramtheme');});");


		 /** Get the Template Themes parameters **/
		if (JFolder::exists($template_themes_full_path) && !JFile::exists($template_themes_full_path."/catalog.php")) {
			$folders = JFolder::folders($template_themes_full_path, $filter);
			if ( is_array($folders) )
			{
				reset($folders);
				while (list($key, $val) = each($folders)) {
					$folder =& $folders[$key];
					if ($exclude)
					{
						if (preg_match( chr( 1 ) . $exclude . chr( 1 ), $folder ))
						{
							continue;
						}
					}
					$theme_path = $template_themes_path.'/'.$folder;
					
					$langfile = JPath::clean(JPATH_ROOT.$theme_path.'/language/'.$lang->_lang.'.ini');
					if (JFile::exists($langfile)) {
						$lang->_load($langfile,'roknavmenu_theme_template_'.$folder);
					}
					
					$param_file_path =  $theme_path.'/parameters.xml';
					if (JFile::exists(JPath::clean(JPATH_ROOT.$param_file_path))) { 
						
						$parameters = new JForm( $this->_parent->_raw, JPath::clean(JPATH_ROOT.$param_file_path));
						$parameter_sets[$theme_path] = $parameters->getParams();
					}
				}
			}
		}
		 /** Get the Default Themes parameters **/
		if (JFolder::exists($module_themes_full_path) && !JFile::exists($module_themes_full_path."/catalog.php")) {
			$folders = JFolder::folders($module_themes_full_path, $filter);
			if ( is_array($folders) )
			{
				reset($folders);
				while (list($key, $val) = each($folders)) {
					$folder =& $folders[$key];
					if ($exclude)
					{
						if (preg_match( chr( 1 ) . $exclude . chr( 1 ), $folder ))
						{
							continue;
						}
					}

					$theme_path = $module_themes_path.'/'.$folder;

					$langfile = JPath::clean(JPATH_ROOT.$theme_path.'/language/'.$lang->_lang.'.ini');
					if (JFile::exists($langfile)) {
						$lang->_load($langfile,'roknavmenu_theme_module_'.$folder);
					}
					
					$param_file_path =  $theme_path.'/parameters.xml';
					
					$parameter_sets[$theme_path]  = array();
					if (JFile::exists(JPath::clean(JPATH_ROOT.$param_file_path))) { 	
						$parameters = new JForm( $this->_parent->_raw, JPath::clean(JPATH_ROOT.$param_file_path));
						$parameter_sets[$theme_path] = $parameters->getParams();
					}
				}
			}
		}
		$parameter_renders = array();
		reset($parameter_sets);
		
		$html = '';
		// render a parameter set
		while(list($key, $val) = each($parameter_sets)) {
			$params =& $parameter_sets[$key];
			$cls = basename($key);
			if (empty($params)){
				$html .= '<p class="'.$cls.'"><span>' . JText::_('ROKNAVMENU_MSG_NO_THEME_OPTIONS_AVAILABLE') . ' </span></p>';	
			}
			else { 
				//render an individual parameter
				for ($i=0; $i < count($params); $i++) { 
					$param =& $params[$i];
					$html .= '<p class="'.$cls.'"><span>'.$param[0].':</span>' .$param[1] . '</p>';
				}
			}
		}
		
		return $html;
	}

    function loadLangFile($langfile, $info){
        $lang = JFactory::getLanguage();
        if (JFile::exists($langfile)) {
	        $lang->_load($langfile,$info);
		}
    }
	
	function _getFrontSideTemplate() {
		if (empty($this->_front_side_template)) { 
			$db =JFactory::getDBO();
			// Get the current default template
            $query = ' SELECT template '
                    .' FROM #__template_styles '
                    .' WHERE client_id = 0 '
                    .' AND home = 1 ';
			$db->setQuery($query);
			$defaultemplate = $db->loadResult();
			$this->_front_side_template = $defaultemplate;
		}
		return $this->_front_side_template;
	}
	
	function _getJSVersion() {
		if (version_compare(JVERSION, '1.5', '>=') && version_compare(JVERSION, '1.6', '<')){
			if (JPluginHelper::isEnabled('system', 'mtupgrade')){
				return "-mt1.2";
			} else {
				return "";
			}
		} else {
			return "";
		}
	}
}

<?php
/**
 * @version   $Id: RokNavMenu1XRenderer.php 9687 2013-04-24 20:37:47Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
class RokNavMenu1XRenderer implements RokMenuRenderer {
    protected $provider;
    protected $args;

    public function __construct(){
        
    }
    
    /**
     * @param  array $args
     * @return void
     */
    public function setArgs(array &$args) {
        $this->args =& $args;
    }

    /**
     * @return array
     */
    public function getDefaults() {
        if (!isset($this->theme)) {
            return array();
        }
        return $this->theme->getDefaults();
    }

    /**
     * @return void
     */
    public function initialize(RokMenuProvider $provider) {
        $menu_data = $provider->getMenuTree();
        if (!empty($menu_data) && $menu_data !== false) {
            $menu = $this->convertNodes($menu_data);

            $menu_params = new JRegistry();
            $menu_params->loadArray($this->args);

            $menu = $this->getFormattedMenu($menu, $menu_params);
            $this->layout_path = $this->getLayoutPath($menu_params);
            $this->menu = &$menu;
        }
    }

    /**
     * @return string
     */
    public function renderHeader() {
        return '';
    }

    /**
     * @return string
     */
    public function renderMenu() {
        $menu =& $this->menu;
        require($this->layout_path);
    }

    /**
     * @return string
     */
    public function renderFooter() {
        return '';
    }

    protected function convertNodes(RokMenuNodeTree $menu) {

        $top = new RokNavMenuTree();
        $top->_params = $this->convertArgsToParams();

        $subnodes = array();
        // convert the nodes to an array of old school node types
        $itr = new RecursiveIteratorIterator($menu, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($itr as $tmp) {
            $node = new RokNavMenuNode();
            $node->id = $tmp->getId();
            $node->parent = $tmp->getParent();
            $node->title = $tmp->getTitle();
            $node->access = $tmp->getAccess();
            $node->link = $tmp->getLink();
            $node->level = $tmp->getLevel();
            $node->image = $tmp->getImage();
            $node->alias = $tmp->isAlias();
            $node->nav = $tmp->getTarget();
            $node->access = $tmp->getAccess();
            switch ($tmp->getTarget()) {
                case 'newnotool':
                    $node->displayType = 2;
                    break;
                case 'new':
                    $node->displayType = 1;
                    break;
                default:
                    $node->displayType = 0;
                    break;
            }
            $node->setParameters($tmp->getParams());
            $node->type = $tmp->getType();
            //$node->order = $item->ordering;

            foreach(explode(" ",$tmp->getListItemClasses()) as $class){
                $node->addListItemClass($class);
            }
            foreach(explode(" ",$tmp->getSpanClasses()) as $class){
                $node->addSpanClass($class);
            }
            foreach(explode(" ",$tmp->getLinkClasses()) as $class){
                $node->addLinkClass($class);
            }
            foreach($tmp->getLinkAttribsArray() as $name => $attrib){
                $node->addLinkAttrib($name, $attrib);
            }
            foreach($tmp->getLinkAdditionsArray() as $name => $value){
                $node->addLinkAddition($name, $value);
            }

            if ($node->parent == RokNavMenu::TOP_LEVEL_PARENT_ID){
                $node->_parentRef = $top;
                $top->_children[$node->id] = $node;

            }
            else {
                foreach ($subnodes as $subnode){
                    if ($node->parent == $subnode->id){
                        $subnode->addChild($node);
                        break;
                    }
                }
            }
            $subnodes[] = $node;
        }
        return $top;
    }

    protected function convertArgsToParams() {
        $params = new JRegistry();
        $params->loadArray($this->args);
        return $params;
    }


    public function getThemePath(&$params) {
        $default_module_theme_dir = JPath::clean('/modules/mod_roknavmenu/themes');
        $basic_theme = $default_module_theme_dir . '/default';
        $theme = $params->get('theme', $basic_theme);
        // Set the theme to the old school default theme if it exists
        if ($params->get('default_formatter', false)) {
            $theme = $default_module_theme_dir . '/' . $params->get('default_formatter', 'default');
        }
        return $theme;
    }

    public function getFormatterPath(&$params) {
        $app = JFactory::getApplication();
        $theme = $this->getThemePath($params);

        // Get the formatters path
        $formatter_path = JPath::clean(JPATH_ROOT . $params->get('theme', $theme) . "/formatter.php");

        $template_default_formatter_path = JPath::clean(JPATH_ROOT . '/templates/' . $app->getTemplate() . '/html/mod_roknavmenu/formatter.php');
        if (JFile::exists($template_default_formatter_path)) {
            $formatter_path = $template_default_formatter_path;
        }

        $template_formatter_path = JPath::clean(JPATH_ROOT . '/templates/' . $app->getTemplate() . '/html/mod_roknavmenu/themes/' . $theme . '/formatter.php');
        if (JFile::exists($template_formatter_path)) {
            $formatter_path = $template_formatter_path;
        }

        //see if the backwards compat custom_formatter is set.
        $template_formatter = $params->get('custom_formatter', "default");
        $template_named_formatter_path = JPath::clean(JPATH_ROOT . '/templates/' . $app->getTemplate() . '/html/mod_roknavmenu/formatters/' . $template_formatter . '.php');
        if (JFile::exists($template_named_formatter_path)) {
            $formatter_path = $template_named_formatter_path;
        }
        return $formatter_path;
    }

    public function getLayoutPath(&$params) {
        $app = JFactory::getApplication();
        $theme = $this->getThemePath($params);

        // Get the layout path
        $layout_path = JPath::clean(JPATH_ROOT . $theme . "/layout.php");

        $joomla_layout_path = JModuleHelper::getLayoutPath('mod_roknavmenu');
        if (JFile::exists($joomla_layout_path)) {
            $layout_path = $joomla_layout_path;
        }

        $template_layout_path = JPath::clean(JPATH_ROOT . '/templates/' . $app->getTemplate() . '/html/mod_roknavmenu/themes/' . $theme . '/layout.php');
        if (JFile::exists($template_layout_path)) {
            $layout_path = $template_layout_path;
        }

        //see if the backwards compat custom_formatter is set.
        if ($params->get('custom_layout', false)) {
            $template_layout = $params->get('custom_layout', "default");
            $template_named_layput_path = JPath::clean(JPATH_ROOT . '/templates/' . $app->getTemplate() . '/html/mod_roknavmenu/layouts/' . $template_layout . '.php');
            if (JFile::exists($template_named_layput_path)) {
                $layout_path = $template_named_layput_path;
            }
        }
        return $layout_path;
    }

    protected function getFormattedMenu($menu, &$params) {
        $app = JFactory::getApplication();
        // get the base menu data structure

        // Run the basic formatter
        $this->formatMenu($menu);

        $default_module_theme_dir = JPath::clean('/modules/mod_roknavmenu/themes');

        $theme = $this->getThemePath($params);

        $theme_name = basename($params->get('theme', $theme));

        $formatter_path = $this->getFormatterPath($params);

        //load the formatter
        require_once ($formatter_path);

        $theme_type = 'Template';
        // Find if this is a Default or Template theme
        if (dirname(JPath::clean($params->get('theme', $theme))) == $default_module_theme_dir) {
            $theme_type = 'Default';
        }

        // run the formatter class
        $theme_formatter_class = 'RokNavMenuFormatter' . str_replace('-', '', $theme_type . $theme_name);
        if (class_exists($theme_formatter_class)) {
            $formatter = new $theme_formatter_class();
            $formatter->format_tree($menu);
        }
        else if (class_exists('RokNavMenuFormatter')) {
            $formatter = new RokNavMenuFormatter();
            $formatter->format_tree($menu);
        }

        return $menu;
    }

    /**
     * Perform the basic common formatting to all menu nodes
     */
    protected function formatMenu(&$menu) {


        //set the active tree branch
        $site = new JSite();
        $joomlamenu = $site->getMenu();
        $active = $joomlamenu->getActive();
        if (isset($active) && isset($active->tree) && count($active->tree)) {
            reset($active->tree);
            while (list($key, $value) = each($active->tree)) {
                $active_node =& $active->tree[$key];
                $active_child =& $menu->findChild($active_node);
                if ($active_child !== false) {
                    $active_child->addListItemClass('active');
                }
            }
        }

        // set the current node
        if (isset($active)) {
            $current_child =& $menu->findChild($active->id);
            if ($current_child !== false && !$current_child->menualias) {
                $current_child->css_id = 'current';
            }
        }


        // Limit the levels of the tree is called for By limitLevels
        if ($menu->getParameter('limit_levels')) {
            $start = $menu->getParameter('startLevel');
            $end = $menu->getParameter('endLevel');

            //Limit to the active path if the start is more the level 0
            if ($start > 0) {
                $found = false;
                // get active path and find the start level that matches
                if (isset($active) && isset($active->tree) && count($active->tree)) {
                    reset($active->tree);
                    while (list($key, $value) = each($active->tree)) {
                        $active_child = $menu->findChild($active->tree[$key]);
                        if ($active_child != null && $active_child !== false) {
                            if ($active_child->level == $start - 1) {
                                $menu->resetTop($active_child->id);
                                $found = true;
                                break;
                            }
                        }
                    }
                }
                if (!$found) {
                    $menu->_children = array();
                }
            }
            //remove lower then the defined end level
            $menu->removeLevel($end);
        }

        // Remove the child nodes that were not needed to display unless showAllChildren is set
        $showAllChildren = $menu->getParameter('showAllChildren');
        if (!$showAllChildren) {
            if ($menu->hasChildren()) {
                reset($menu->_children);
                while (list($key, $value) = each($menu->_children)) {
                    $toplevel =& $menu->_children[$key];
                    if (isset($active) && isset($active->tree) && in_array($toplevel->id, $active->tree) !== false) {
                        $last_active =& $menu->findChild($active->tree[count($active->tree) - 1]);
                        if ($last_active !==  false) {
                            $toplevel->removeIfNotInTree($active->tree, $last_active->id);
                            //$toplevel->removeLevel($last_active->level+1);
                        }
                    }
                    else {
                        $toplevel->removeLevel($toplevel->level);
                    }
                }
            }
        }
    }

    protected function _getJSVersion() {
        if (version_compare(JVERSION, '1.5', '>=') && version_compare(JVERSION, '1.6', '<')) {
            if (JPluginHelper::isEnabled('system', 'mtupgrade')) {
                return "-mt1.2";
            } else {
                return "";
            }
        } else {
            return "";
        }
    }

}

<?php
/**
 * @version   $Id: gantrymenu.class.php 11319 2013-06-07 15:26:51Z steph $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

// no direct access
defined('GANTRY_VERSION') or die('Restricted access');

// Include the syndicate functions only once
gantry_import('facets.menu.gantrymenutree');

class GantryMenu
{

	var $_menudata = null;
	var $_formatter = null;
	var $_layout_path = null;

	var $__cacheables = array(
		'_menudata'
	);

	function __sleep()
	{
		return $this->__cacheables;
	}

	// static function to get an instance of the GantryMenu
	function &getInstance(GantryRegistry $params)
	{
		/** @var $gantry Gantry */
		global $gantry;
		$conf = JFactory::getConfig();
		if ($conf->get('caching') && $params->get("module_cache", 0)) {
			$user  = JFactory::getUser();
			$cache = JFactory::getCache('Gantry');
			$cache->setCaching(true);
			$cache->setLifeTime($gantry->get("cache-time", $conf->get('cachetime') * 60));
			$args     = array(&$params);
			$checksum = md5($params->toString());

			$gantrymenu = $cache->get(array(
			                               'GantryMenu',
			                               '_getInstance'
			                          ), $args, 'GantryMenu-' . $user->get('aid', 0) . '-' . $checksum);
		} else {
			$gantrymenu = GantryMenu::_getInstance($params);
		}

		return $gantrymenu;
	}

	static function &_getInstance($params)
	{
		$gantrymenu = new GantryMenu($params);
		return $gantrymenu;
	}

	function GantryMenu($params)
	{
		if (empty($params)) {
			$params = new GantryRegistry();
		}
		$params->def('menutype', 'mainmenu');
		$params->def('class_sfx', '');
		$params->def('menu_images', 0);

		// Added in 1.5
		$params->def('startLevel', 0);
		$params->def('endLevel', 0);
		$params->def('showAllChildren', 0);

		$this->_menudata = GantryMenu::_getMenuData($params);
	}


	function render($params)
	{

		$theme_name = $params->get('theme', 'basic');
		$this->_loadTheme($theme_name);

		// Run the basic formatter
		GantryMenu::_applyBasicFormatting($this->_menudata);

		if (!empty($this->_formatter)) {
			$this->_formatter->format_tree($this->_menudata);
		}

		// format the menu data $menu is passed to the layout
		$menu = &$this->_menudata;

		$menurender = "Unable to render menu missing layout.php for theme " . $theme_name;
		if (!empty($this->_layout_path) && file_exists($this->_layout_path) && is_readable($this->_layout_path)) {
			ob_start();
			require($this->_layout_path);
			$menurender = ob_get_contents();
			ob_end_clean();
		}
		return $menurender;
	}

	function _getMenuData(&$params)
	{
		$menu          = new GantryMenuTree();
		$menu->_params = &$params;
		$app = JFactory::getApplication();
		$items         = $app->getMenu();
		// Get Menu Items
		$rows     = $items->getItems('menutype', $params->get('menutype'));
		$maxdepth = $menu->getParameter('endLevel', 10);

		// Build Menu Tree root down (orphan proof - child might have lower id than parent)
		$user       = JFactory::getUser();
		$ids        = array();
		$ids[0]     = true;
		$last       = null;
		$unresolved = array();
		// pop the first item until the array is empty if there is any item
		if (is_array($rows)) {
			while (count($rows) && !is_null($row = array_shift($rows))) {
				$row->ionly = $params->get('menu_images_link');
				if (!$menu->addNode($params, $row)) {
					if (!array_key_exists($row->id, $unresolved) || $unresolved[$row->id] < $maxdepth) {
						array_push($rows, $row);
						if (!isset($unresolved[$row->id])) $unresolved[$row->id] = 1; else $unresolved[$row->id]++;
					}
				}
			}
		}
		return $menu;
	}


	function _loadTheme($theme_name)
	{
		/** @var $gantry Gantry */
		global $gantry;

		// Load up the theme info if there is not one already
		if (empty($this->_formatter)) {
			$theme_parent_paths = array(
				$gantry->templatePath . '/facets/menu/themes', $gantry->gantryPath . '/facets/menu/themes'
			);

			foreach ($theme_parent_paths as $theme_parent_path) {
				if (file_exists($theme_parent_path) && is_dir($theme_parent_path)) {
					$d = dir($theme_parent_path);
					while (false !== ($entry = $d->read())) {
						if ($entry != '.' && $entry != '..') {
							if ($entry == $theme_name && is_dir($theme_parent_path . '/' . $entry)) {
								$formatter_file  = $theme_parent_path . '/' . $entry . '/' . 'formatter.php';
								$layout_file     = $theme_parent_path . '/' . $entry . '/' . 'layout.php';
								$formatter_class = 'GantryMenuFormatter' . ucfirst($entry);
								if (!file_exists($formatter_file)) {
									return false;
								}
								// Load the Formatter File
								require_once($formatter_file);
								if (!class_exists($formatter_class)) {
									return false;
								}
								$this->_formatter = new $formatter_class();
								if (file_exists($layout_file)) {
									$this->_layout_path = $layout_file;
								}
								break(2); // exit top level foreach
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Perform the basic common formatting to all menu nodes
	 */
	function _applyBasicFormatting(&$menu)
	{
		//set the active tree branch
		$app = JFactory::getApplication();
		$joomlamenu         = $app->getMenu();
		$active     = $joomlamenu->getActive();
		if (isset($active) && isset($active->tree) && count($active->tree)) {
			reset($active->tree);
			while (list($key, $value) = each($active->tree)) {
				$active_node  =& $active->tree[$key];
				$active_child = $menu->findChild($active_node);
				if ($active_child !== false) {
					$active_child->addListItemClass('active');
				}
			}
		}

		// set the current node
		if (isset($active)) {
			$current_child = $menu->findChild($active->id);
			if ($current_child !== false && !$current_child->menualias) {
				$current_child->css_id = 'current';
			}
		}


		// Limit the levels of the tree is called for By limitLevels
		if ($menu->getParameter('limit_levels')) {
			$start = $menu->getParameter('startLevel');
			$end   = $menu->getParameter('endLevel');

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
						$last_active = $menu->findChild($active->tree[count($active->tree) - 1]);
						if ($last_active !== false) {
							$toplevel->removeIfNotInTree($active->tree, $last_active->id);
							//$toplevel->removeLevel($last_active->level+1);
						}
					} else {
						$toplevel->removeLevel($toplevel->level);
					}
				}
			}
		}
	}

}
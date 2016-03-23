<?php
/**
 * @version   $Id: gantrymenutree.class.php 5159 2012-11-13 23:04:58Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die('Restricted access');

gantry_import('facets.menu.gantrymenutreebase');
gantry_import('facets.menu.gantrymenunode');
/**
 * Rok Nav Menu Tree Class.
 */
class GantryMenuTree extends GantryMenuTreeBase
{
//	const MENUPARAM_MENU_IMAGES 		= "menu_images";
//	const MENUPARAM_LIMIT_LEVELS		= "limit_levels";
//	const MENUPARAM_START_LEVEL 		= "startLevel";
//	const MENUPARAM_END_LEVEL 			= "endLevel";
//	const MENUPARAM_SHOW_ALL_CHILDREN 	= "showAllChildren";
//	const MENUPARAM_TAG_ID 			= "tag_id";
//	const MENUPARAM_CLASS_SUFFIX 		= "class_sfx";
//	const MENUPARAM_MENU_IMAGES_LINK	= "menu_images_link";
//	const MENUPARAM_MAX_DEPTH 			= "maxdepth";


	function addNode(&$params, $item)
	{
		// Get menu item data

		$node                      = $this->_getItemData($params, $item);
		$node->_check_access_level = $params->get('check_access_level', null);
		if ($node !== false) {
			return $this->addChild($node);
		} else {
			return true;
		}

	}


	function resetTop($top_node_id)
	{
		$new_top_node = $this->findChild($top_node_id);
		if ($new_top_node !== false) {
			$this->id        = $new_top_node->id;
			$this->_children = $new_top_node->getChildren();
		} else {
			return false;
		}
	}

	function _getSecureUrl($url, $secure)
	{
		if ($secure == -1) {
			$url = str_replace('https://', 'http://', $url);
		} elseif ($secure == 1) {
			$url = str_replace('http://', 'https://', $url);
		}
		return $url;
	}

	function _getItemData(&$params, $item)
	{
		//Create the new Node
		$node = new GantryMenuNode();

		$tmp = null;
		// Menu Link is a special type that is a link to another item
		if ($item->type == 'menulink' || $item->type == 'alias') {
			$app   = JFactory::getApplication();
			$menu = $app->getMenu();
			if ($newItem = $menu->getItem(strlen($item->query['Itemid']) ? $item->query['Itemid'] : $item->params->get('aliasoptions'))) {
				$tmp            = clone($newItem);
				$tmp->name      = addslashes(htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'));
				$tmp->mid       = $item->id;
				$tmp->parent_id = $item->parent_id;
				$tmp->params    = $item->params;
				$tmp->url       = null;
				$tmp->nav       = 'current';
				$tmp->menualias = true;
			}
		}

		if (($item->type != 'menulink' && $item->type != 'alias') || (($item->type == 'menulink' || $item->type == 'alias') && $tmp == null)) {
			$tmp            = clone($item);
			$tmp->name      = addslashes(htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'));
			$tmp->mid       = $tmp->id;
			$tmp->url       = null;
			$tmp->nav       = 'current';
			$tmp->menualias = false;
		}


		$iParams = new JRegistry($tmp->params);

		if ($params->get('menu_images') && $iParams->get('menu_image') && $iParams->get('menu_image') != -1) {
			$image = JURI::base(true) . '/images/stories/' . $iParams->get('menu_image');
			if ($tmp->ionly) {
				$tmp->name = null;
			}
		} else {
			$image = null;
		}


		switch ($tmp->type) {
			case 'separator':
				$tmp->outtype = 'separator';
				break;
			case 'url':
				if ((strpos($tmp->link, 'index.php?') === 0) && (strpos($tmp->link, 'Itemid=') === false)) {
					$tmp->url = $tmp->link . '&amp;Itemid=' . $tmp->id;
				} else {
					$tmp->url = $tmp->link;
				}
				$tmp->outtype = 'menuitem';
				break;
			default :
				$router       = JSite::getRouter();
				$tmp->url     = $router->getMode() == JROUTER_MODE_SEF ? 'index.php?Itemid=' . $tmp->id : $tmp->link . '&Itemid=' . $tmp->id;
				$tmp->outtype = 'menuitem';
				break;
		}


		if ($tmp->url != null) {
			// set the target based on menu item options
			switch ($tmp->browserNav) {
				case 0:
					$tmp->nav = 'current';
					break;
				case 1:
					$tmp->nav = 'new';
					break;
				case 2:
					$tmp->url = str_replace('index.php', 'index2.php', $tmp->url);
					$tmp->nav = 'newnotool';
					break;
				default:
					$tmp->nav = 'current';
					break;
			}


			// Get the final URL
			if ($tmp->home == 1) { // Set Home Links to the Base
				$tmp->url = JURI::base();
			}

			if ($tmp->type != 'separator' && $tmp->type != 'url') {
				$iSecure = $iParams->get('secure', 0);

				if ($this->_params->get('url_type', 'relative') == 'full') {
					$url      = JRoute::_($tmp->url, true, $iSecure);
					$base     = (!preg_match("/^http/", $tmp->url)) ? preg_replace("#/$#", "", JURI::base(false)) : '';
					$routed   = $base . $url;
					$secure   = GantryMenuTree::_getSecureUrl($routed, $iSecure);
					$tmp->url = $secure;
				} else {
					$tmp->url = JRoute::_($tmp->url, true, $iSecure);
				}
			} else if ($tmp->type == 'url') {
				$tmp->url = str_replace('&', '&amp;', $tmp->url);
			} else {

			}
		}

		$node->id          = $tmp->mid;
		$node->parent      = $tmp->parent_id;
		$node->title       = $tmp->name;
		$node->access      = $tmp->access;
		$node->link        = $tmp->url;
		$node->level       = $item->level;
		$node->image       = $image;
		$node->alias       = $tmp->alias;
		$node->nav         = $tmp->nav;
		$node->displayType = $tmp->browserNav;


		$node->setParameters($tmp->params);
		$node->type = $tmp->outtype;
		//$node->order = $item->ordering;
		$node->addListItemClass("item" . $node->id);
		$node->addSpanClass($tmp->outtype);
		return $node;
	}
}

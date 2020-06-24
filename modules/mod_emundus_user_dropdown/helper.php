<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Site
 * @subpackage	mod_emundusmenu
 * @since		1.5
 */
class modEmundusUserDropdownHelper {

	static function getList($menu_name) {

		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		$items = $menu->getItems('menutype', $menu_name);

		$levels = JFactory::getUser()->getAuthorisedViewLevels();

		if ($items) {
			foreach ($items as $i => $item) {

				// Only get surface level menu items.
				if ($item->level > 1) {
					unset($items[$i]);
					continue;
				}

				// Check if user can access menu item.
				if (!in_array($item->access, $levels)) {
					continue;
				}

				// Hide hidden menu items.
				if ($item->params->get('menu_show', 0) !== 1) {
					unset($items[$i]);
					continue;
				}

				$item->flink = $item->link;

				// Reverted back for CMS version 2.5.6
				switch ($item->type) {
					case 'separator':
						// No further action needed.
						continue 2;

					case 'url':
						if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false)) {
							// If this is an internal Joomla link, ensure the Itemid is set.
							$item->flink = $item->link.'&Itemid='.$item->id;
						}
						break;

					case 'alias':
						// If this is an alias use the item id stored in the parameters to make the link.
						$item->flink = 'index.php?Itemid='.$item->params->get('aliasoptions');
						break;

					default:
						$router = JSite::getRouter();
						if ($router->getMode() == JROUTER_MODE_SEF) {
							$item->flink = 'index.php?Itemid='.$item->id;
						}
						else {
							$item->flink .= '&Itemid='.$item->id;
						}
						break;
				}

				if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false)) {
					$item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
				} else {
					$item->flink = JRoute::_($item->flink);
				}

				$item->title = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
				$item->anchor_css = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
				$item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
				$item->anchor_rel = htmlspecialchars($item->params->get('menu-anchor_rel', ''), ENT_COMPAT, 'UTF-8', false);
				$item->menu_image = $item->params->get('menu_image', '') ?
				htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
			}
		}

		return $items;
	}
}

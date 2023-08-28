<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * @package		Joomla.Site
 * @subpackage	mod_emundusmenu
 * @since		1.5
 */
class modEmundusUserDropdownHelper {

	static function getList($menu_name) {

		$app = Factory::getApplication();
        $is_sef = (bool)Factory::getApplication()->getConfig()->get('sef');
		$menu = $app->getMenu();

		$items = $menu->getItems('menutype', $menu_name);

		$levels = JFactory::getUser()->getAuthorisedViewLevels();

		if ($items) {
			foreach ($items as $i => $item) {
                $params = $item->getParams();

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
				if ($params->get('menu_show', 0) != 1) {
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
						$item->flink = 'index.php?Itemid='.$params->get('aliasoptions');
						break;

					default:
						if ($is_sef) {
							$item->flink = 'index.php?Itemid='.$item->id;
						}
						else {
							$item->flink .= '&Itemid='.$item->id;
						}
						break;
				}

				if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false)) {
					$item->flink = JRoute::_($item->flink, true, $params->get('secure'));
				} else {
					$item->flink = JRoute::_($item->flink);
				}

				$item->title = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
				$item->anchor_css = htmlspecialchars($params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
				$item->anchor_title = htmlspecialchars($params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
				$item->anchor_rel = htmlspecialchars($params->get('menu-anchor_rel', ''), ENT_COMPAT, 'UTF-8', false);
				$item->menu_image = $params->get('menu_image', '') ?
				htmlspecialchars($params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
			}
		}

		return $items;
	}


	static function isCampaignActive() {
		$db = JFactory::getDBO();

		try {

			$query = "SELECT COUNT(*) FROM `jos_emundus_setup_campaigns` WHERE `published` = 1 AND NOW() BETWEEN `start_date` AND `end_date`";
			$db->setQuery($query);
			$result = $db->loadResult();

			return $result > 0;

		} catch(Exception $e) {
			return false;
		}
	}

    static function getProfilePicture() {
        $db = JFactory::getDBO();

        try {
            $query = $db->getQuery(true);
            $query->select('profile_picture')
                ->from($db->quoteName('#__emundus_users'))
                ->where($db->quoteName('user_id') . ' = ' . $db->quote(JFactory::getUser()->id));
            $db->setQuery($query);
            return $db->loadResult();

        } catch(Exception $e) {
            return null;
        }
    }
}

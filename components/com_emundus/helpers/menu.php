<?php
/**
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2015 emundus.fr. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;

defined('_JEXEC') or die('Restricted access');

class EmundusHelperMenu {

	public static function buildMenuQuery($profile, $formids = null, $checklevel=true) {
		if (empty($profile)) {
	        return false;
        }
		$list = [];

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$user   = JFactory::getUser();
		$levels = [];
		if ($checklevel) {
			$levels = JAccess::getAuthorisedViewLevels($user->id);
		}

		$query->select('fbtables.id AS table_id, fbtables.form_id, fbforms.label, fbtables.db_table_name, CONCAT(menu.link,"&Itemid=",menu.id) AS link, menu.id, menu.title, profile.menutype, fbforms.params, menu.params as menu_params, menu.lft')
			->from($db->quoteName('#__menu','menu'))
			->innerJoin($db->quoteName('#__emundus_setup_profiles','profile').' ON '.$db->quoteName('profile.menutype').' = '.$db->quoteName('menu.menutype') . ' AND ' . $db->quoteName('profile.id') . ' = ' . $db->quote($profile))
			->innerJoin($db->quoteName('#__fabrik_forms','fbforms').' ON '.$db->quoteName('fbforms.id').' = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 4), "&", 1)')
			->innerJoin($db->quoteName('#__fabrik_lists','fbtables').' ON '.$db->quoteName('fbtables.form_id').' = '.$db->quoteName('fbforms.id'))
			->where($db->quoteName('menu.published') . ' = 1')
			->andWhere($db->quoteName('menu.parent_id') . ' != 1');
		if ($checklevel && !empty($levels)){
			$query->andWhere($db->quoteName('menu.access') . ' IN ('.implode(',', $levels).')');
		}

		if (!empty($formids) && $formids[0] != "") {
			$query->andWhere($db->quoteName('fbtables.id') . ' IN (' . implode(',', $formids) . ')');
		}
		$query->order('menu.lft');

		try {
			$db->setQuery( $query );
			$list = $db->loadObjectList();

			$query->clear()
				->select('fbtables.id AS table_id, fbtables.form_id, fbforms.label, fbtables.db_table_name, CONCAT(menu.link,"&Itemid=",menu.id) AS link, menu.id, menu.title, profile.menutype, fbforms.params, menu.params as menu_params, menu.lft')
				->from($db->quoteName('#__menu','menu'))
				->innerJoin($db->quoteName('#__emundus_setup_profiles','profile').' ON '.$db->quoteName('profile.menutype').' = '.$db->quoteName('menu.menutype') . ' AND ' . $db->quoteName('profile.id') . ' = ' . $db->quote($profile))
				->innerJoin($db->quoteName('#__fabrik_forms','fbforms').' ON '.$db->quoteName('fbforms.id').' = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 4), "&", 1)')
				->innerJoin($db->quoteName('#__fabrik_lists','fbtables').' ON '.$db->quoteName('fbtables.form_id').' = '.$db->quoteName('fbforms.id'))
				->where($db->quoteName('menu.published') . ' = 1')
				->andWhere($db->quoteName('menu.parent_id') . ' != 1');
			if($checklevel && !empty($levels)){
				$query->andWhere($db->quoteName('menu.access') . ' IN ('.implode(',', $levels).')');
			}

			if (!empty($formids) && $formids[0] != "") {
				$query->andWhere($db->quoteName('fbtables.form_id') . ' IN (' . implode(',', $formids) . ')');
			}
			$query->order('menu.lft');

			$db->setQuery( $query );
			$forms = $db->loadObjectList();

			// merge forms and lists
			$list = array_merge($list, $forms);

			// remove duplicates
			$ids = [];
			foreach ($list as $key => $item) {
				if (in_array($item->form_id, $ids)) {
					unset($list[$key]);
				} else {
					$ids[] = $item->form_id;
				}
			}

			// reorder the list by lft
			$sorted = [];
			foreach ($list as $item) {
				$sorted[$item->lft] = $item;
			}

			ksort($sorted);
			$list = array_values($sorted);
		} catch(Exception $e) {
			throw new $e->getMessage();
	    }

		return $list;
	}

	static function getUserApplicationMenu($profile, $formids = null) {
		$user   = JFactory::getUser();
		$levels = JAccess::getAuthorisedViewLevels($user->id);

		$_db = JFactory::getDBO();
		$query = 'SELECT fbtables.id AS table_id, fbtables.form_id, fbforms.label, fbtables.db_table_name, CONCAT(menu.link,"&Itemid=",menu.id) AS link, menu.id, menu.title, profile.menutype
		FROM #__menu AS menu
		INNER JOIN #__emundus_setup_profiles AS profile ON profile.menutype = menu.menutype AND profile.id = '.$profile.'
		INNER JOIN #__fabrik_forms AS fbforms ON fbforms.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 4), "&", 1)
		LEFT JOIN #__fabrik_lists AS fbtables ON fbtables.form_id = fbforms.id
		WHERE menu.published = 1 AND menu.parent_id != 1 AND menu.access IN ('.implode(',', $levels).')';
		if (!empty($formids) && $formids[0] != "") {
			$query .= ' AND fbtables.form_id IN('.implode(',',$formids).')';
		}
		$query .= ' ORDER BY menu.lft';

		try {
			$_db->setQuery($query);
			return $_db->loadObjectList();
		} catch(Exception $e) {
			throw new $e->getMessage();
		}
	}

	function buildMenuListQuery($profile) {
		$_db = JFactory::getDBO();
		$query = 'SELECT fbtables.db_table_name
		FROM #__menu AS menu
		INNER JOIN #__emundus_setup_profiles AS profile ON profile.menutype = menu.menutype AND profile.id = '.$profile.'
		INNER JOIN #__fabrik_forms AS fbforms ON fbforms.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 4), "&", 1)
		LEFT JOIN #__fabrik_lists AS fbtables ON fbtables.form_id = fbforms.id
		WHERE fbtables.published = 1 AND menu.parent_id !=1
		ORDER BY menu.lft';

		try {
	    	$_db->setQuery( $query );
			return $_db->loadResultArray();
	    } catch(Exception $e) {
	        throw new $e->getMessage();
	    }
	}

	static function getHomepageLink($default_link = null)
	{
		$menu = 'index.php';

		$activeLanguage = Factory::getLanguage()->getTag();
		$languages = LanguageHelper::getLanguages('lang_code');
		$defaultLanguage = ComponentHelper::getParams('com_languages')->get('site', 'fr-FR');
		$sef = '';
		if (isset($languages[$activeLanguage]) && $activeLanguage !== $defaultLanguage)
		{
			$sef = $languages[$activeLanguage]->sef;
		}

		$homepage_itemId = ComponentHelper::getParams('com_emundus')->get('logged_homepage_link', '');

		if(!empty($homepage_itemId)) {
			$menu = Factory::getApplication()->getMenu()->getItem($homepage_itemId);
			if(!empty($menu)) {
				$menu = $menu->alias;
			}
		}

		if(!in_array($default_link, ['/','index.php','']) && $default_link !== $menu) {
			$menu = $default_link;
		} else {
			$menu = !empty($sef) ? '/'.$sef.'/'.$menu : '/'.$menu;
		}

		return $menu;
	}
}
?>

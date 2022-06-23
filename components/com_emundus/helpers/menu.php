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
defined('_JEXEC') or die('Restricted access');

class EmundusHelperMenu {

	function buildMenuQuery($profile, $formids = null, $checklevel=true) {
	    if (empty($profile)) {
	        return false;
        }
		$user   = JFactory::getUser();
		if ($checklevel) {
			$levels = JAccess::getAuthorisedViewLevels($user->id);
			$and_level =  'AND menu.access IN ('.implode(',', $levels).')';
		}

		$_db = JFactory::getDBO();
		$query = 'SELECT fbtables.id AS table_id, fbtables.form_id, fbforms.label, fbtables.db_table_name, CONCAT(menu.link,"&Itemid=",menu.id) AS link, menu.id, menu.title, profile.menutype, fbforms.params
		FROM #__menu AS menu
		INNER JOIN #__emundus_setup_profiles AS profile ON profile.menutype = menu.menutype AND profile.id = '.$profile.'
		INNER JOIN #__fabrik_forms AS fbforms ON fbforms.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)
		LEFT JOIN #__fabrik_lists AS fbtables ON fbtables.form_id = fbforms.id
		WHERE (menu.published = 0 OR menu.published = 1) AND menu.parent_id !=1 '.$and_level;

		if (!empty($formids) && $formids[0] != "") {
			$query .= ' AND fbtables.form_id IN(' . implode(',', $formids) . ')';
		}
		$query .= ' ORDER BY menu.lft';

		try {
			$_db->setQuery( $query );
			return $_db->loadObjectList();
	    } catch(Exception $e) {
			throw new $e->getMessage();
	    }
	}

	function getUserApplicationMenu($profile, $formids = null) {
		$user   = JFactory::getUser();
		$levels = JAccess::getAuthorisedViewLevels($user->id);

		$_db = JFactory::getDBO();
		$query = 'SELECT fbtables.id AS table_id, fbtables.form_id, fbforms.label, fbtables.db_table_name, CONCAT(menu.link,"&Itemid=",menu.id) AS link, menu.id, menu.title, profile.menutype
		FROM #__menu AS menu
		INNER JOIN #__emundus_setup_profiles AS profile ON profile.menutype = menu.menutype AND profile.id = '.$profile.'
		INNER JOIN #__fabrik_forms AS fbforms ON fbforms.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)
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
		INNER JOIN #__fabrik_forms AS fbforms ON fbforms.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)
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
}
?>

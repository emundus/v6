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
class modEmundusProfileHelper {

    static function getProfilePicture() {
        $db = JFactory::getDBO();
		$pp = '';

        try {
            $query = $db->getQuery(true);
            $query->select('profile_picture')
                ->from($db->quoteName('#__emundus_users'))
                ->where($db->quoteName('user_id') . ' = ' . $db->quote(JFactory::getUser()->id));
            $db->setQuery($query);
            $pp = $db->loadResult();

			if(empty($pp)) {
				$pp = '';
			}

        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
        }

		return $pp;
    }
}

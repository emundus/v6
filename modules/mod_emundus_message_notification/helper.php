<?php
/**
 * @copyright      Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package        Joomla.Site
 * @subpackage     mod_emundusmenu
 * @since          1.5
 * author       James Dean
 */
class modEmundusMessageNotificationHelper
{

	public function getMessages($user)
	{

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('COUNT(message_id)')
			->from($db->quoteName('#__messages'))
			->where('(' . $db->quoteName('folder_id') . ' = 2 OR ' . $db->quoteName('folder_id') . ' = 3 ) AND ' . $db->quoteName('user_id_to') . ' = ' . $user . ' AND ' . $db->quoteName('state') . ' = 1');
		$db->setQuery($query);

		try {

			return $db->loadResult();

		}
		catch (Exception $e) {
			JLog::add('Error getting account type stats from mod_graphs helper at query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');
		}

	}

	public function getContacts($user)
	{

		$db = JFactory::getDbo();

		$query = "select jos_messages.*, user_from.name as name_from, sp_from.label as profile_from, user_to.name as name_to, sp_to.label as profile_to, uploadTo.attachment_id as photo_to, uploadFrom.attachment_id as photo_from
                from jos_messages
                LEFT JOIN jos_users user_to ON user_to.id = jos_messages.user_id_to 
                LEFT JOIN jos_users user_from ON user_from.id = jos_messages.user_id_from 
                LEFT JOIN jos_emundus_users eu_to ON eu_to.user_id = user_to.id
                LEFT JOIN jos_emundus_users eu_from ON eu_from.user_id = user_from.id
                LEFT JOIN jos_emundus_setup_profiles sp_to ON sp_to.id =  eu_to.profile
                LEFT JOIN jos_emundus_setup_profiles sp_from ON sp_from.id =  eu_from.profile
                LEFT JOIN jos_emundus_uploads uploadTo ON uploadTo.user_id = user_to.id AND uploadTo.attachment_id = 10
                LEFT JOIN jos_emundus_uploads uploadFrom ON uploadFrom.user_id = user_from.id AND uploadFrom.attachment_id = 10
                WHERE(least(`user_id_from`, `user_id_to`), greatest(`user_id_from`, `user_id_to`), `date_time`)       
                IN (
                    select 
                       least(`user_id_from`, `user_id_to`) as x, greatest(`user_id_from`, `user_id_to`) as y, 
                       max(`date_time`) as msg_time
                    from jos_messages 
                    WHERE (jos_messages.folder_id = 2 OR (jos_messages.folder_id = 3 AND `user_id_to` = " . $user . "))
                    AND (`user_id_to` = " . $user . " OR `user_id_from` = " . $user . ")
                    group by x, y
                )
                AND (`user_id_to` = " . $user . " OR `user_id_from` = " . $user . ")
                AND (jos_messages.folder_id = 2 OR (jos_messages.folder_id = 3 AND `user_id_to` = " . $user . "))
                ORDER BY jos_messages.date_time DESC 
                LIMIT 50";


		$db->setQuery($query);
		try {

			return $db->loadObjectList();

		}
		catch (Exception $e) {
			JLog::add('Error getting candidate file attachment name in model/messages at query: ' . $query, JLog::ERROR, 'com_emundus');

			return false;
		}
	}


}

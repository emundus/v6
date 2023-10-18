<?php
/**
 * @version		$Id: query.php 14401 2010-01-26 14:10:00Z guillossou $
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');
/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class EmundusHelperAccess {

	static function isAllowed($usertype, $allowed) {
		return in_array($usertype, $allowed);
	}

	static function isAllowedAccessLevel($user_id, $current_menu_access) {
		$user_access_level = JAccess::getAuthorisedViewLevels($user_id);
		return in_array($current_menu_access, $user_access_level);
	}

	static function asAdministratorAccessLevel($user_id) {
		return EmundusHelperAccess::isAllowedAccessLevel($user_id, 8);
	}

	static function asCoordinatorAccessLevel($user_id) {
		return EmundusHelperAccess::isAllowedAccessLevel($user_id, 7);
	}

    static function asManagerAccessLevel($user_id) {
        return EmundusHelperAccess::isAllowedAccessLevel($user_id, 17);
    }

    static function asPartnerAccessLevel($user_id) {
        return EmundusHelperAccess::isAllowedAccessLevel($user_id, 6);
    }

	static function asEvaluatorAccessLevel($user_id) {
		return (EmundusHelperAccess::isAllowedAccessLevel($user_id, 5) ||
                EmundusHelperAccess::isAllowedAccessLevel($user_id, 3) ||
                EmundusHelperAccess::isAllowedAccessLevel($user_id, 12) ||
                EmundusHelperAccess::isAllowedAccessLevel($user_id, 13));
	}

	static function asApplicantAccessLevel($user_id) {
		return EmundusHelperAccess::isAllowedAccessLevel($user_id, 4);
	}

	static function asPublicAccessLevel($user_id) {
		return EmundusHelperAccess::isAllowedAccessLevel($user_id, 1);
	}

	static function check_group($user_id, $group, $inherited) {
		// 1:Public / 2:Registered / 3:Author / 4:Editor / 5:Publisher / 6:Manager / 7:Administrator / 8:Super Users / 9:Guest / 10:Nobody
		if ($inherited) {
			//include inherited groups
			jimport( 'joomla.access.access' );
			$groups = JAccess::getGroupsByUser($user_id);
		} else {
			//exclude inherited groups
			$user = JFactory::getUser($user_id);
			$groups = isset($user->groups) ? $user->groups : array();
		}
		return (in_array($group, $groups))?true:0;
	}

	static function isAdministrator($user_id) {
		return EmundusHelperAccess::check_group($user_id, 8, false);
	}

	static function isCoordinator($user_id) {
		return EmundusHelperAccess::check_group($user_id, 7, false);
	}
	static function isPartner($user_id) {
		return (EmundusHelperAccess::check_group($user_id, 4, false) ||
                EmundusHelperAccess::check_group($user_id, 14, false) ||
                EmundusHelperAccess::check_group($user_id, 13, false));
	}

	static function isExpert($user_id) {
		return (EmundusHelperAccess::check_group($user_id, 14, false));
	}

	static function isEvaluator($user_id) {
		return (EmundusHelperAccess::check_group($user_id, 3, false) ||
                EmundusHelperAccess::check_group($user_id, 13, false));
	}

	static function isApplicant($user_id) {
		return (EmundusHelperAccess::check_group($user_id, 2, false) ||
                EmundusHelperAccess::check_group($user_id, 11, true));
	}

	static function isPublic($user_id) {
		return EmundusHelperAccess::check_group($user_id, 1, false);
	}

	/**
	 * Get the eMundus groups for a user.
	 *
	 *
	 * @param	int	$user			The user id.
	 *
	 * @return	array	The array of groups for user.
	 * @since	4.0
	*/
	static function getProfileAccess($user) {
		$db = JFactory::getDBO();
		$query = 'SELECT esg.profile_id FROM #__emundus_setup_groups as esg
					LEFT JOIN #__emundus_groups as eg on esg.id=eg.group_id
					WHERE esg.published=1 AND eg.user_id='.$user;
		$db->setQuery($query);
		return $db->loadResultArray();
	}

	/**
	 * Get action access right.
	 *
	 * @param int    $action_id Id of the action.
	 * @param string $crud      create/read/update/delete.
	 *
	 * @param null   $user_id   The user id.
	 * @param null   $fnum      File number of application
	 *
	 * @return    boolean    Has access or not
	 * @since    6.0
	 */
	static function asAccessAction($action_id, $crud, $user_id = null, $fnum = null) {

		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
		$m_users = new EmundusModelUsers();

		if (!is_null($fnum) && !empty($fnum)) {
			$canAccess = $m_users->getUserActionByFnum($action_id, $fnum, $user_id, $crud);
			if ($canAccess > 0) {
				return true;
			} elseif ($canAccess == 0 || $canAccess === null) {
				$groups = JFactory::getSession()->get('emundusUser')->emGroups;
				if (!empty($groups) && count($groups) > 0) {
					return EmundusHelperAccess::canAccessGroup($groups, $action_id, $crud, $fnum);
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return EmundusHelperAccess::canAccessGroup(JFactory::getSession()->get('emundusUser')->emGroups, $action_id, $crud);
		}
	}


	/**
	 * @param         $gids
	 * @param         $action_id
	 * @param         $crud
	 * @param   null  $fnum
	 *
	 * @return bool
	 *
	 * @since version
	 */
	static function canAccessGroup($gids, $action_id, $crud, $fnum = null) {

		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
		$m_users = new EmundusModelUsers();

		if (!is_null($fnum) && !empty($fnum)) {
			$accessList = $m_users->getGroupActions($gids, $fnum, $action_id, $crud);
			$canAccess = (!empty($accessList))?-1:null;
            if (count($accessList)>0) {
                foreach ($accessList as $access) {
                    if ($canAccess < intval($access[$crud])) {
                        $canAccess = $access[$crud];
                    }
                }
            }
			if ($canAccess > 0) {
				return true;
			} elseif ($canAccess == 0 || $canAccess === null) {
				// We filter the list of groups to take into account only the groups attached to the fnum's programme OR who are attached to no programme.
				$gids = $m_users->getEffectiveGroupsForFnum($gids, $fnum);
				return EmundusHelperAccess::canAccessGroup($gids, $action_id, $crud);
			} else {
				return false;
			}
		} else {
			$groupsActions = $m_users->getGroupsAcl($gids);
            if (!empty($groupsActions)) {
                foreach ($groupsActions as $action) {
                    if ($action['action_id'] == $action_id && $action[$crud] == 1) {
                        return true;
                    }
                }
            }
			return false;
		}
	}

	/**
	 * @param $user_id
	 *
	 * @return array|bool
	 *
	 * @since version
	 */
	public static function getUserFabrikGroups($user_id) {
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'groups.php');
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
		$m_groups = new EmundusModelGroups();
		$m_users = new EmundusModelUsers();

		$group_ids = $m_users->getUserGroups($user_id);
		// NOTE: The unorthodox array_keys_flip is actually faster than doing array_unique(). The first array_keys is because the function used returns an assoc array [id => name].
		return $m_groups->getFabrikGroupsAssignedToEmundusGroups(array_keys(array_flip(array_keys($group_ids))));
	}


	/**
	 * @param $user_id
	 *
	 * @return array|bool
	 *
	 * @since version
	 */
	public static function getUserAllowedAttachmentIDs($user_id) {
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
		$m_files = new EmundusModelFiles();
		$m_users = new EmundusModelUsers();

		$group_ids = $m_users->getUserGroups($user_id);
		// NOTE: The unorthodox array_keys_flip is actually faster than doing array_unique(). The first array_keys is because the function used returns an assoc array [id => name].
		return $m_files->getAttachmentsAssignedToEmundusGroups(array_keys(array_flip(array_keys($group_ids))));
	}


	/**
	 * @param $user_id
	 *
	 * @return bool
	 *
	 * @since version
	 */
	public static function isDataAnonymized($user_id) {
		JLog::addLogger(['text_file' => 'com_emundus.access.error.php'], JLog::ERROR, 'com_emundus');
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
		$m_users = new EmundusModelUsers();

		$group_ids = $m_users->getUserGroups($user_id);

		if (!empty($group_ids)) {
			// NOTE: The unorthodox array_keys_flip is actually faster than doing array_unique(). The first array_keys is because the function used returns an assoc array [id => name].
			$group_ids = array_keys(array_flip(array_keys($group_ids)));

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName('anonymize'))->from($db->quoteName('#__emundus_setup_groups'))->where($db->quoteName('id').' IN ('.implode(',', $group_ids).')');
			$db->setQuery($query);

			try {
				return in_array('1', $db->loadColumn());
			}
			catch (Exception $e) {
				JLog::add('Error seeing if user can access non anonymous data. -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
				return false;
			}
		}
		return false;
	}


	/**
	 *
	 * @return JCrypt
	 *
	 * @since version
	 */
	public static function getCrypt() {
		jimport('joomla.crypt.crypt');
		jimport('joomla.crypt.key');
		$config = JFactory::getConfig();
		$secret = $config->get('secret', '');

		if (trim($secret) == '') {
			throw new RuntimeException('You must supply a secret code in your Joomla configuration.php file');
		}

		$key   = new JCryptKey('simple', $secret, $secret);
		return new JCrypt(new JCryptCipherSimple, $key);
	}
}

<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 28/01/15
 * Time: 16:28
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
class EmundusModelActions extends JModelList {

	/**
	 * @param   bool  $echo  if true, echo output.
	 *
	 * @return bool
	 *
	 * @throws Exception
	 * @since version
	 */
	public function syncAllActions($echo = true) {
		try {
			$dbo = $this->getDbo();
			$queryGetMissingGroups = 'SELECT id FROM jos_emundus_setup_groups WHERE id NOT IN (SELECT group_id FROM jos_emundus_acl)';
			$queryActionID = "SELECT id FROM jos_emundus_setup_actions WHERE status >= 1";
			$groupAssocQuery = "select jega.fnum, jega.group_id, jega.action_id from jos_emundus_group_assoc as jega left join jos_emundus_setup_actions as jesa on jesa.id = jega.action_id where jesa.status = 1";
			$userAssocQuery = "select jega.fnum, jega.user_id, jega.action_id from jos_emundus_users_assoc as jega left join jos_emundus_setup_actions as jesa on jesa.id = jega.action_id where jesa.status = 1";
			$queryAcl = "select action_id, group_id from jos_emundus_acl";

			$dbo->setQuery($queryGetMissingGroups);
			$missingGroups = $dbo->loadColumn();
			if (!empty($missingGroups)) {
				$queryInsertMissingGroups = 'INSERT INTO `jos_emundus_acl` (`group_id`, `action_id`, `c`, `r`, `u`, `d`) VALUES ('.implode(',1,0,1,0,0),(',$missingGroups).',1,0,1,0,0)';
				$dbo->setQuery($queryInsertMissingGroups);
				$dbo->execute();
			}

			$dbo->setQuery($queryActionID);
			$actionsId = $dbo->loadColumn();
			$dbo->setQuery($queryAcl);
			$aclAction = $dbo->loadAssocList();
			$dbo->setQuery($groupAssocQuery);
			$arrayGroupAssoc = $dbo->loadAssocList();
			$dbo->setQuery($userAssocQuery);
			$arrayUserAssoc = $dbo->loadAssocList();
			$acl = array();
			$aclGroupAssoc = array();
			$aclUserAssoc = array();

			foreach ($aclAction as $action) {
				$acl[$action['group_id']][] = $action['action_id'];
			}
			foreach ($arrayGroupAssoc as $aga) {
				$aclGroupAssoc[$aga['fnum']][$aga['group_id']][] = $aga['action_id'];
			}
			foreach ($arrayUserAssoc as $aua) {
				$aclUserAssoc[$aua['fnum']][$aua['user_id']][] = $aua['action_id'];
			}
			foreach ($acl as $gId => $groupAction) {
				$acl[$gId] = array_diff($actionsId, $groupAction);
			}
			$queryActionID = "SELECT id FROM jos_emundus_setup_actions WHERE status = 1";
			$dbo->setQuery($queryActionID);
			$actionsId = $dbo->loadColumn();

			foreach ($aclGroupAssoc as $fnum => $groups) {
				foreach ($groups as $gid => $action) {
					$aclGroupAssoc[$fnum][$gid] = array_diff($actionsId, $action);
				}
			}
			foreach ($aclUserAssoc as $fnum => $users) {
				foreach ($users as $uid => $action) {
					$aclUserAssoc[$fnum][$uid] = array_diff($actionsId, $action);
				}
			}

			$canInsert = false;
			$insert = "INSERT INTO jos_emundus_acl (action_id, group_id, c, r, u, d) values ";
			$overload = array();
			foreach ($acl as $gid => $actions) {
				if (!empty($actions)) {
					if (count($actions) > count($overload)) {
						$overload = $actions;
					}
					$canInsert = true;
					foreach ($actions as $aid) {
						$insert .= "({$aid}, {$gid}, 0, 0, 0, 0),";
					}
				}
			}

			if ($canInsert) {
				$insert = rtrim($insert, ",");
				$dbo->setQuery($insert);
				if ($echo) {
					echo "<pre>group acl : ".$insert."</pre>";
				}
				$dbo->execute();
			}
			$canInsert = false;
			$insert = "INSERT INTO jos_emundus_group_assoc (fnum, action_id, group_id, c, r, u, d) values ";

			foreach ($aclGroupAssoc as $fnum => $groups) {
				foreach ($groups as $gid => $assocActions) {
					if (!empty($assocActions)) {
						$canInsert = true;
						foreach ($assocActions as $aid) {
							$insert .= "({$fnum}, {$aid}, {$gid}, 0, 0, 0, 0),";
						}
					}
				}
			}
			if ($canInsert) {
				$insert = rtrim($insert, ",");
				$dbo->setQuery($insert);
				if ($echo) {
					echo "<pre> insert group assoc : ".$insert."</pre>";
				}
				$dbo->execute();
			}
			$canInsert = false;
			$insert = "INSERT INTO jos_emundus_users_assoc (fnum, action_id, user_id, c, r, u, d) values ";

			foreach ($aclUserAssoc as $fnum => $users) {
				foreach ($users as $uid => $assocActions) {
					if (!empty($assocActions)) {
						foreach ($assocActions as $aid) {
							$user = JFactory::getUser($uid);
							if ($user->id > 0) {
								$canInsert = true;
								$insert .= "({$fnum}, {$aid}, {$uid}, 0, 0, 0, 0),";
							} elseif ($echo) {
								echo '<hr>'.JText::_('ERROR_USER_ID_NOT_FOUND').' : '.$uid;
							}
						}
					}
				}
			}

			if ($canInsert) {
				$insert = rtrim($insert, ",");
				$dbo->setQuery($insert);
				if ($echo) {
					echo "<pre>insert users assoc : ".$insert."</pre>";
				}
				$dbo->execute();
			}

			if ($echo) {
				echo "END";
			}

		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage());
			return false;
		}
		return true;
	}
}
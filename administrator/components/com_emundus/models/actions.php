<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 28/01/15
 * Time: 16:28
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );
class EmundusModelActions extends JModelList
{
	public function syncAllActions()
	{
		$dbo = $this->getDbo();
		$queryActionID = "SELECT id FROM jos_emundus_setup_actions WHERE status >= 1";
		$groupAssocQuery = "select jega.fnum, jega.group_id, jega.action_id from jos_emundus_group_assoc as jega left join jos_emundus_setup_actions as jesa on jesa.id = jega.action_id where jesa.status = 1";
		$userAssocQuery = "select jega.fnum, jega.user_id, jega.action_id from jos_emundus_users_assoc as jega left join jos_emundus_setup_actions as jesa on jesa.id = jega.action_id where jesa.status = 1";
		$queryAcl = "select action_id, group_id from jos_emundus_acl";

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

		foreach($aclAction as $action)
		{
			$acl[$action['group_id']][] = $action['action_id'];
		}
		foreach($arrayGroupAssoc as $aga)
		{
			$aclGroupAssoc[$aga['fnum']][$aga['group_id']][] = $aga['action_id'];
		}
		foreach($arrayUserAssoc as $aua)
		{
			$aclUserAssoc[$aua['fnum']][$aua['user_id']][] = $aua['action_id'];
		}

		foreach($acl as $gId => $groupAction)
		{
			$acl[$gId] = array_diff($actionsId, $groupAction);
		}
		$queryActionID = "SELECT id FROM jos_emundus_setup_actions WHERE status = 1";
		$dbo->setQuery($queryActionID);
		$actionsId = $dbo->loadColumn();

		foreach($aclGroupAssoc as $fnum => $groups)
		{
			foreach($groups as $gid => $action)
			{
				$aclGroupAssoc[$fnum][$gid] = array_diff($actionsId, $action);
			}
		}
		foreach($aclUserAssoc as $fnum => $users)
		{
			foreach($users as $uid => $action)
			{
				$aclUserAssoc[$fnum][$uid] = array_diff($actionsId, $action);
			}
		}

		$canInsert = false;
		$insert = "INSERT INTO jos_emundus_acl (action_id, group_id, c, r, u, d) values ";
		$overload = array();
		foreach($acl as $gid => $actions)
		{
			if(!empty($actions))
			{
				if(count($actions) > count($overload))
				{
					$overload = $actions;
				}
				$canInsert = true;
				foreach($actions as $aId)
				{
					$insert .= "({$aId}, {$gid}, 0, 0, 0, 0),";
				}
			}
		}

		if($canInsert)
		{
			$insert = rtrim($insert, ",");
			$dbo->setQuery($insert);
			echo "<pre>";
				echo "group acl";
				echo $insert;
			echo "</pre>";
			$dbo->execute();
		}
		$canInsert = false;
		$insert = "INSERT INTO jos_emundus_group_assoc (fnum, action_id, group_id, c, r, u, d) values ";

		foreach($aclGroupAssoc as $fnum => $groups)
		{
			foreach($groups as $gid => $assocActions)
			{
				if(!empty($assocActions))
				{
					$canInsert = true;
					foreach($assocActions as $aid)
					{
						$insert .= "({$fnum}, {$aid}, {$gid}, 0, 0, 0, 0),";
					}
				}
			}
		}
		if($canInsert)
		{
			$insert = rtrim($insert, ",");
			$dbo->setQuery($insert);
			echo "<pre>";
			echo "insert group assoc";
			echo $insert;
			echo "</pre>";
			$dbo->execute();
		}
		$canInsert = false;
		$insert = "INSERT INTO jos_emundus_users_assoc (fnum, action_id, user_id, c, r, u, d) values ";

		foreach($aclUserAssoc as $fnum => $users)
		{
			foreach($users as $uid => $assocActions)
			{
				if(!empty($assocActions))
				{
					$canInsert = true;
					foreach($assocActions as $aid)
					{
						$insert .= "({$fnum}, {$aid}, {$uid}, 0, 0, 0, 0),";
					}
				}
			}
		}
		if($canInsert)
		{
			$insert = rtrim($insert, ",");
			$dbo->setQuery($insert);
			echo "<pre>";
			echo "insert users assoc";
			echo $insert;
			echo "</pre>";
			$dbo->execute();
		}

	}
}
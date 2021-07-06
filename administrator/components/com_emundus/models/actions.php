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
	 * @param bool $echo if true, echo output.
	 * @param null $gid
	 *
	 * @return bool
	 *
	 * @throws Exception
	 * @since version
	 */
	public function syncAllActions($echo = true, $gid = null) {
		try {
			$dbo = $this->getDbo();
			
            $subQuery = $dbo->getQuery(true);
            $query = $dbo->getQuery(true);

            /* Get the missing groups */
            $subQuery
                ->select($dbo->quoteName('group_id'))
                ->from($dbo->quoteName('#__emundus_acl'));
            
            $query
                ->select($dbo->quoteName('id'))
                ->from($dbo->quoteName('#__emundus_setup_groups'))
                ->where($dbo->quoteName('id') . ' NOT IN (' . $subQuery .')');
                
            $dbo->setQuery($query);
            $missingGroups = $dbo->loadColumn();


            /* Get action IDs*/
            $query
                ->clear()
                ->select($dbo->quoteName('id'))
                ->from($dbo->quoteName('#__emundus_setup_actions'))
                ->where($dbo->quoteName('status') . ' >= 1');

            $dbo->setQuery($query);
            $actionsId = $dbo->loadColumn();

            /** Get all group assoc
             *  When using the $gid param, we only get the files linked to the group we are looking at
             */
            $query
                ->clear()
                ->select([$dbo->quoteName('jega.fnum'), $dbo->quoteName('jega.group_id'), $dbo->quoteName('jega.action_id')])
                ->from($dbo->quoteName('#__emundus_group_assoc', 'jega'))
                ->leftJoin($dbo->quoteName('#__emundus_setup_actions','jesa').' ON '.$dbo->quoteName('jesa.id').' = '.$dbo->quoteName('jega.action_id'))
                ->where($dbo->quoteName('jesa.status') . ' = 1');

            if (!empty($gid)) {
                $query
                    ->andWhere($dbo->quoteName('jega.group_id') . ' = ' . $gid);
            }

            $dbo->setQuery($query);
            $arrayGroupAssoc = $dbo->loadAssocList();

            /** Get all user assoc
             *  When using the $gid param, we only get the files linked to the group we are looking at
             */
            if (empty($gid)) {
                $query
                    ->clear()
                    ->select([$dbo->quoteName('jeua.fnum'), $dbo->quoteName('jeua.user_id'), $dbo->quoteName('jeua.action_id')])
                    ->from($dbo->quoteName('#__emundus_users_assoc', 'jeua'))
                    ->leftJoin($dbo->quoteName('#__emundus_setup_actions','jesa').' ON '.$dbo->quoteName('jesa.id').' = '.$dbo->quoteName('jeua.action_id'))
                    ->where($dbo->quoteName('jesa.status') . ' = 1');

                $dbo->setQuery($query);
                $arrayUserAssoc = $dbo->loadAssocList();
            } else {
                $arrayUserAssoc = [];
            }

            /* Get all actions in acl table */
            $query
                ->clear()
                ->select([$dbo->quoteName('action_id'), $dbo->quoteName('group_id')])
                ->from($dbo->quoteName('#__emundus_acl'));

            $dbo->setQuery($query);
            $aclAction = $dbo->loadAssocList();

            /* Insert missing groups*/
			if (!empty($missingGroups)) {

				$columns = ['group_id', 'action_id', 'c', 'r', 'u', 'd'];

				$query
                    ->clear()
                    ->insert($dbo->quoteName('#__emundus_acl'))
                    ->columns($dbo->quoteName($columns));
                    foreach ($missingGroups as $missingGroup) {
                        $query->values($missingGroup.',1,0,1,0,0');
                    }

                $dbo->setQuery($query);
                $dbo->execute();

			}


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
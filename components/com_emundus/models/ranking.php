<?php
/**
 * @version     1.39.0
 * @package     eMundus
 * @copyright   (C) 2024 eMundus LLC. All rights reserved.
 * @license     GNU General Public License
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

require_once(JPATH_ROOT . '/components/com_emundus/helpers/access.php');

class EmundusModelRanking extends JModelList
{
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    public function getFilesUserCanRank($user_id)
    {
        $files = [];

        $hierarchy = $this->getUserHierarchy($user_id);
        $status = $this->getStatusUserCanRank($user_id, $hierarchy);

        if ($status !== null) {
            $ids = $this->getAllFilesRankerCanAccessTo($user_id, $status);

            if (!empty($ids)) {
                $db = Factory::getDbo();
                $query = $db->getQuery(true);

                $query->select('CONCAT(applicant.firstname, " ", applicant.lastname) AS applicant, cc.id, cc.fnum, cr.rank, cr.locked')
                    ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
                    ->leftJoin($db->quoteName('#__emundus_users', 'applicant') . ' ON ' . $db->quoteName('cc.applicant_id') . ' = ' . $db->quoteName('applicant.id'))
                    ->leftJoin($db->quoteName('#__emundus_ranking', 'cr') . ' ON ' . $db->quoteName('cc.id') . ' = ' . $db->quoteName('cr.ccid'))
                    ->where($db->quoteName('cc.id') . ' IN (' . implode(',', $ids) . ')')
                    ->andWhere('(cr.user_id = ' . $db->quote($user_id) . ' AND cr.hierarchy_id = ' . $db->quote($hierarchy) . ') OR cr.id IS NULL');

                try {
                    $db->setQuery($query);
                    $files = $db->loadAssocList();
                } catch (Exception $e) {
                    $files = [];
                }

                foreach ($files as $key => $file) {
                    if (empty($file['locked']) && $file['locked'] != '0') {
                        $files[$key]['locked'] = 0;
                    }

                    if (empty($file['rank'])) {
                        $files[$key]['rank'] = -1; // -1 means not ranked
                    }
                }

                // order by rank
                usort($files, function ($a, $b) {
                    if ($a['rank'] == $b['rank']) {
                        return 0;
                    }
                    return ($a['rank'] < $b['rank']) ? -1 : 1;
                });
            }
        }

        return $files;
    }

    private function getStatusUserCanRank($user_id, $hierarchy = null)
    {
        $status = null;

        if (!empty($user_id)) {
            if (empty($hierarchy)) {
                $hierarchy = $this->getUserHierarchy($user_id);
            }

            $db = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->clear()
                ->select('status')
                ->from($db->quoteName('#__emundus_ranking_hierarchy', 'ech'))
                ->where($db->quoteName('ech.id') . ' = ' . $db->quote($hierarchy));

            $db->setQuery($query);
            $status = $db->loadResult();
        }

        return $status;
    }

    public function getUserHierarchy($user_id)
    {
        $hierarchy = 0;

        if (!empty($user_id)) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->clear()
                ->select('ech.id')
                ->from($db->quoteName('#__emundus_ranking_hierarchy', 'ech'))
                ->leftJoin($db->quoteName('#__emundus_users', 'eu') . ' ON ' . $db->quoteName('eu.profile') . ' = ' . $db->quoteName('ech.profile_id'))
                ->where($db->quoteName('eu.user_id') . ' = ' . $db->quote($user_id));

            $db->setQuery($query);
            $hierarchy = $db->loadResult();
        }

        return $hierarchy;
    }

    public function getAllFilesRankerCanAccessTo($user_id, $files_status = null)
    {
        $file_ids = [];

        if (!empty($user_id)) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('DISTINCT cc.id')
                ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
                ->leftJoin($db->quoteName('#__emundus_users_assoc', 'eua') . ' ON ' . $db->quoteName('cc.fnum') . ' = ' . $db->quoteName('eua.fnum'))
                ->where($db->quoteName('eua.user_id') . ' = ' . $db->quote($user_id))
                ->andWhere($db->quoteName('cc.applicant_id') . ' != ' . $db->quote($user_id))
                ->andWhere($db->quoteName('eua.action_id') . ' = 1')
                ->andWhere($db->quoteName('eua.r') . ' = 1')
                ->andWhere($db->quoteName('cc.published') . ' = 1');

            if (!is_null($files_status)) {
                $query->andWhere($db->quoteName('cc.status') . ' = ' . $db->quote($files_status));
            }

            try {
                $db->setQuery($query);
                $users_assoc_ccids = $db->loadColumn();
            } catch (Exception $e) {
                $users_assoc_ccids = [];
            }

            if (!empty($users_assoc_ccids)) {
                $file_ids = array_merge($file_ids, $users_assoc_ccids);
            }

            require_once(JPATH_ROOT . '/components/com_emundus/models/users.php');
            $m_users = new EmundusModelUsers();
            $groups = $m_users->getUserGroups($user_id, 'Column');

            if (!empty($groups)) {
                $query->clear()
                    ->select('DISTINCT cc.id')
                    ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
                    ->leftJoin($db->quoteName('#__emundus_group_assoc', 'ega') . ' ON ' . $db->quoteName('cc.fnum') . ' = ' . $db->quoteName('ega.fnum'))
                    ->where($db->quoteName('ega.group_id') . ' IN (' . implode(',', $groups) . ')')
                    ->andWhere($db->quoteName('cc.applicant_id') . ' != ' . $db->quote($user_id))
                    ->andWhere($db->quoteName('ega.action_id') . ' = 1')
                    ->andWhere($db->quoteName('ega.r') . ' = 1')
                    ->andWhere($db->quoteName('cc.published') . ' = 1');

                if (!is_null($files_status)) {
                    $query->andWhere($db->quoteName('cc.status') . ' = ' . $db->quote($files_status));
                }

                $db->setQuery($query);
                $group_assoc_ccids = $db->loadColumn();

                if (!empty($group_assoc_ccids)) {
                    $file_ids = array_merge($file_ids, $group_assoc_ccids);
                }
            }

            $programs = $m_users->getUserGroupsProgramme($user_id);
            if (!empty($programs)) {
                $query->clear()
                    ->select('DISTINCT cc.id')
                    ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
                    ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('cc.campaign_id') . ' = ' . $db->quoteName('esc.id'))
                    ->where($db->quoteName('esc.training') . ' IN (' . implode(',', $db->quote($programs)) . ')')
                    ->andWhere($db->quoteName('cc.applicant_id') . ' != ' . $db->quote($user_id))
                    ->andWhere($db->quoteName('cc.published') . ' = 1');

                if (!is_null($files_status)) {
                    $query->andWhere($db->quoteName('cc.status') . ' = ' . $db->quote($files_status));
                }

                $db->setQuery($query);
                $program_assoc_ccids = $db->loadColumn();

                if (!empty($program_assoc_ccids)) {
                    $file_ids = array_merge($file_ids, $program_assoc_ccids);
                }
            }

            $file_ids = array_unique($file_ids);
        }

        return $file_ids;
    }

    public function updateFileRanking($id, $user_id, $new_rank, $hierarchy_id)
    {
        $updated = false;

        if (!empty($id) && !empty($user_id) && !empty($new_rank) && !empty($hierarchy_id)) {
            // make sure ccid id not one of the user's file as applicant
            $db = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->clear()
                ->select('applicant_id')
                ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
                ->where($db->quoteName('cc.id') . ' = ' . $db->quote($id));

            $db->setQuery($query);
            $applicant_id = $db->loadResult();

            if ($applicant_id == $user_id) {
                throw new Exception('You cannot rank your own file');
            }

            $query->clear()
                ->select($db->quoteName('rank'))
                ->from($db->quoteName('#__emundus_ranking'))
                ->where($db->quoteName('ccid') . ' = ' . $db->quote($id))
                ->andWhere($db->quoteName('user_id') . ' = ' . $db->quote($user_id))
                ->andWhere($db->quoteName('hierarchy_id') . ' = ' . $db->quote($hierarchy_id));

            try {
                $db->setQuery($query);
                $old_rank = $db->loadResult();

                // if old rank is the same as new rank, do nothing
                if ($old_rank == $new_rank) {
                    return true;
                } else {
                    if ($new_rank == -1) {
                        // all ranks superior or equal to old rank should be decreased by 1
                        $query->clear()
                            ->update($db->quoteName('#__emundus_ranking'))
                            ->set($db->quoteName('rank') . ' = ' . $db->quoteName('rank') . ' - 1')
                            ->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id))
                            ->andWhere($db->quoteName('hierarchy_id') . ' = ' . $db->quote($hierarchy_id))
                            ->andWhere($db->quoteName('rank') . ' > ' . $db->quote($old_rank));

                        $db->setQuery($query);
                        $db->execute();
                    } else if ($old_rank == -1) {
                        // all ranks superior or equal to new rank should be increased by 1
                        $query->clear()
                            ->update($db->quoteName('#__emundus_ranking'))
                            ->set($db->quoteName('rank') . ' = ' . $db->quoteName('rank') . ' + 1')
                            ->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id))
                            ->andWhere($db->quoteName('hierarchy_id') . ' = ' . $db->quote($hierarchy_id))
                            ->andWhere($db->quoteName('rank') . ' >= ' . $db->quote($new_rank));

                        $db->setQuery($query);
                        $db->execute();
                    } else if ($old_rank > $new_rank) {
                        // all ranks between new rank and old rank should be increased by 1
                        $query->clear()
                            ->update($db->quoteName('#__emundus_ranking'))
                            ->set($db->quoteName('rank') . ' = ' . $db->quoteName('rank') . ' + 1')
                            ->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id))
                            ->andWhere($db->quoteName('hierarchy_id') . ' = ' . $db->quote($hierarchy_id))
                            ->andWhere($db->quoteName('rank') . ' >= ' . $db->quote($new_rank))
                            ->andWhere($db->quoteName('rank') . ' < ' . $db->quote($old_rank));

                        $db->setQuery($query);
                        $db->execute();
                    } else if ($old_rank < $new_rank) {
                        // all ranks between old rank and new rank should be decreased by 1
                        $query->clear()
                            ->update($db->quoteName('#__emundus_ranking'))
                            ->set($db->quoteName('rank') . ' = ' . $db->quoteName('rank') . ' - 1')
                            ->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id))
                            ->andWhere($db->quoteName('hierarchy_id') . ' = ' . $db->quote($hierarchy_id))
                            ->andWhere($db->quoteName('rank') . ' > ' . $db->quote($old_rank))
                            ->andWhere($db->quoteName('rank') . ' <= ' . $db->quote($new_rank));

                        $db->setQuery($query);
                        $db->execute();
                    }

                    $query->clear()
                        ->select('id')
                        ->from($db->quoteName('#__emundus_ranking'))
                        ->where($db->quoteName('ccid') . ' = ' . $db->quote($id))
                        ->andWhere($db->quoteName('user_id') . ' = ' . $db->quote($user_id))
                        ->andWhere($db->quoteName('hierarchy_id') . ' = ' . $db->quote($hierarchy_id));

                    $db->setQuery($query);
                    $ranking_id = $db->loadResult();

                    if (!empty($ranking_id)) {
                        $query->clear()
                            ->update($db->quoteName('#__emundus_ranking'))
                            ->set($db->quoteName('rank') . ' = ' . $db->quote($new_rank))
                            ->where($db->quoteName('id') . ' = ' . $db->quote($ranking_id));

                        $db->setQuery($query);
                        $updated = $db->execute();
                    } else {
                        $query->clear()
                            ->insert($db->quoteName('#__emundus_ranking'))
                            ->columns($db->quoteName('ccid') . ', ' . $db->quoteName('user_id') . ', ' . $db->quoteName('rank') . ', ' . $db->quoteName('hierarchy_id'))
                            ->values($db->quote($id) . ', ' . $db->quote($user_id) . ', ' . $db->quote($new_rank) . ', ' . $db->quote($hierarchy_id));

                        $db->setQuery($query);
                        $updated = $db->execute();
                    }
                }
            } catch (Exception $e) {
                throw new Exception('An error occurred while updating the file ranking.');
            }
        }

        return $updated;
    }

    public function toggleLockFilesOfHierarchyRanking($hierarchy_id, $user_id, $locked = 1)
    {
        $toggled = false;

        if (!empty($hierarchy_id) && !empty($user_id)) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->clear()
                ->update($db->quoteName('#__emundus_ranking'))
                ->set($db->quoteName('locked') . ' = ' . $db->quote($locked))
                ->where($db->quoteName('hierarchy_id') . ' = ' . $db->quote($hierarchy_id))
                ->andWhere($db->quoteName('user_id') . ' = ' . $db->quote($user_id));

            $db->setQuery($query);
            $toggled = $db->execute();
        }

        return $toggled;
    }
}
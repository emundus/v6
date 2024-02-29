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

require_once (JPATH_ROOT . '/components/com_emundus/helpers/access.php');

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

                $query->select('CONCAT(applicant.firstname, " ", applicant.lastname) AS applicant, cc.id, cc.fnum, cl.rank, cl.locked')
                    ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
                    ->leftJoin($db->quoteName('#__emundus_users', 'applicant') . ' ON ' . $db->quoteName('cc.applicant_id') . ' = ' . $db->quoteName('applicant.id'))
                    ->leftJoin($db->quoteName('#__emundus_classement', 'cl') . ' ON ' . $db->quoteName('cc.id') . ' = ' . $db->quoteName('cl.ccid'))
                    ->where($db->quoteName('cc.id') . ' IN (' . implode(',', $ids) . ')')
                    ->andWhere('(cl.user_id = ' . $db->quote($user_id) . ' AND cl.hierarchy_id = ' . $db->quote($hierarchy) .') OR cl.id IS NULL');

                try {
                    $db->setQuery($query);
                    $files = $db->loadAssocList();
                } catch (Exception $e) {
                    $files = [];
                }

                foreach($files as $key => $file) {
                    if (empty($file['locked']) && $file['locked'] != '0') {
                        $files[$key]['locked'] = 0;
                    }

                    if (empty($file['rank'])) {
                        $files[$key]['rank'] = -1; // -1 means not ranked
                    }
                }
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
                ->from($db->quoteName('#__emundus_classement_hierarchy', 'ech'))
                ->where($db->quoteName('ech.id') . ' = ' . $db->quote($hierarchy));

            $db->setQuery($query);
            $status = $db->loadResult();
        }

        return $status;
    }

    private function getUserHierarchy($user_id)
    {
        $hierarchy = 0;

        if (!empty($user_id)) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->clear()
                ->select('ech.id')
                ->from($db->quoteName('#__emundus_classement_hierarchy', 'ech'))
                ->leftJoin($db->quoteName('#__emundus_users', 'eu') . ' ON ' . $db->quoteName('eu.profile') . ' = ' . $db->quoteName('ech.profile_id'))
                ->where($db->quoteName('eu.user_id') . ' = ' . $db->quote($user_id));

            $db->setQuery($query);
            $hierarchy = $db->loadResult();
        }

        return $hierarchy;
    }

    private function getAllFilesRankerCanAccessTo($user_id, $files_status)
    {
        $file_ids = [];

        if (!empty($user_id)) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('DISTINCT cc.id')
                ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
                ->leftJoin($db->quoteName('#__emundus_users_assoc', 'eua') . ' ON ' . $db->quoteName('cc.fnum') . ' = ' . $db->quoteName('eua.fnum'))
                ->where($db->quoteName('eua.user_id') . ' = ' . $db->quote($user_id))
                ->andWhere($db->quoteName('eua.action_id') . ' = 1')
                ->andWhere($db->quoteName('eua.r') . ' = 1')
                ->andWhere($db->quoteName('cc.published') . ' = 1')
                ->andWhere($db->quoteName('cc.status') . ' = ' . $db->quote($files_status));

            try {
                $db->setQuery($query);
                $users_assoc_ccids = $db->loadColumn();
            } catch (Exception $e) {
                $users_assoc_ccids = [];
            }

            if (!empty($users_assoc_ccids)) {
                $file_ids = array_merge($file_ids, $users_assoc_ccids);
            }

            require_once (JPATH_ROOT.'/components/com_emundus/models/users.php');
            $m_users = new EmundusModelUsers();
            $groups = $m_users->getUserGroups($user_id, 'Column');

            if (!empty($groups)) {
                $query->clear()
                    ->select('DISTINCT cc.id')
                    ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
                    ->leftJoin($db->quoteName('#__emundus_group_assoc', 'ega') . ' ON ' . $db->quoteName('cc.fnum') . ' = ' . $db->quoteName('ega.fnum'))
                    ->where($db->quoteName('ega.group_id') . ' IN (' . implode(',', $groups) . ')')
                    ->andWhere($db->quoteName('ega.action_id') . ' = 1')
                    ->andWhere($db->quoteName('ega.r') . ' = 1')
                    ->andWhere($db->quoteName('cc.published') . ' = 1')
                    ->andWhere($db->quoteName('cc.status') . ' = ' . $db->quote($files_status));

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
                    ->where($db->quoteName('esc.training') . ' IN (' . $db->quote(implode(',', $programs)) . ')')
                    ->andWhere($db->quoteName('cc.published') . ' = 1')
                    ->andWhere($db->quoteName('cc.status') . ' = ' . $db->quote($files_status));

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
}
?>
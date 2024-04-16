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

    public $filters = [];
    private $h_files = null;

    private $db = null;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $session = Factory::getSession();
        $this->filters = $session->get('em-applied-filters', []);
        if (!class_exists('EmundusHelperFiles')) {
            require_once(JPATH_ROOT . '/components/com_emundus/helpers/files.php');
        }
        $this->h_files = new EmundusHelperFiles();
        $this->db = Factory::getDBO();

        JLog::addLogger(['text_file' => 'com_emundus.ranking.php'], JLog::ALL);
    }

    /**
     * @param $label
     * @param $status
     * @param $profile_id
     * @param $published
     * @return int
     * @throws Exception
     */
    public function createHierarchy($label, $status, $profile_id, $parent_hierarchy = 0, $published = 1, $visible_hierarchies = [])
    {
        $hierarchy_id = 0;

        if (!empty($label) && is_numeric($status) && !empty($profile_id)) {
            $query = $this->db->getQuery(true);

            $query->clear()
                ->select('id')
                ->from($this->db->quoteName('#__emundus_setup_profiles'))
                ->where($this->db->quoteName('id') . ' = ' . $this->db->quote($profile_id));

            try {
                $this->db->setQuery($query);
                $profile_id = $this->db->loadResult();
            } catch (Exception $e) {
                JLog::add('createHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                throw new Exception(Text::_('COM_EMUNDUS_RANKING_COULD_NOT_DETERMINE_PROFILE_EXISTENCE'));
            }

            if (empty($profile_id)) {
                throw new Exception(Text::_('COM_EMUNDUS_RANKING_PROFILE_DOES_NOT_EXIST'));
            }

            $query->clear()
                ->select('id')
                ->from($this->db->quoteName('#__emundus_ranking_hierarchy'))
                ->where($this->db->quoteName('profile_id') . ' = ' . $this->db->quote($profile_id))
                ->andWhere($this->db->quoteName('status') . ' = ' . $this->db->quote($status));

            try {
                $this->db->setQuery($query);
                $hierarchy_id = $this->db->loadResult();
            } catch (Exception $e) {
                JLog::add('createHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                throw new Exception(Text::_('COM_EMUNDUS_RANKING_COULD_NOT_DETERMINE_HIERARCHY_EXISTENCE'));
            }

            if (!empty($hierarchy_id)) {
                throw new Exception(Text::_('COM_EMUNDUS_RANKING_HIERARCHY_ALREADY_EXISTS_ON_STATE'));
            }

            $query->clear()
                ->insert($this->db->quoteName('#__emundus_ranking_hierarchy'))
                ->columns($this->db->quoteName('label') . ', ' . $this->db->quoteName('status') . ', ' . $this->db->quoteName('profile_id') . ', ' . $this->db->quoteName('parent_id') . ', ' . $this->db->quoteName('published'))
                ->values($this->db->quote($label) . ', ' . $this->db->quote($status) . ', ' . $this->db->quote($profile_id) . ', ' . $this->db->quote($parent_hierarchy) . ', ' . $this->db->quote($published));

            try {
                $this->db->setQuery($query);
                $this->db->execute();
                $hierarchy_id = $this->db->insertid();
            } catch (Exception $e) {
                JLog::add('createHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                throw new Exception(Text::_('COM_EMUNDUS_RANKING_COULD_NOT_CREATE_HIERARCHY'));
            }

            if (!empty($visible_hierarchies) && !empty($hierarchy_id)) {
                foreach ($visible_hierarchies as $visible_hierarchy_id) {
                    $query->clear()
                        ->insert($this->db->quoteName('#__emundus_ranking_hierarchy_view'))
                        ->columns($this->db->quoteName('hierarchy_id') . ', ' . $this->db->quoteName('visible_hierarchy_id'))
                        ->values($this->db->quote($hierarchy_id) . ', ' . $this->db->quote($visible_hierarchy_id));

                    try {
                        $this->db->setQuery($query);
                        $this->db->execute();
                    } catch (Exception $e) {
                        JLog::add('createHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                        continue;
                    }
                }
            }
        }

        return $hierarchy_id;
    }

    public function updateHierarchy($id, $params)
    {
        $updated = true;

        if (!empty($id) && !empty($params)) {
            $query = $this->db->getQuery(true);

            $columns_allowed = ['label', 'status', 'profile_id', 'parent_id', 'published'];
            $columns = array_keys($params);

            if (!empty(array_intersect($columns, $columns_allowed))) {
                $query->clear()
                    ->update($this->db->quoteName('#__emundus_ranking_hierarchy'));

                foreach ($params as $key => $value) {
                    if (in_array($key, $columns_allowed)) {
                        $query->set($this->db->quoteName($key) . ' = ' . $this->db->quote($value));
                    }
                }

                $query->where($this->db->quoteName('id') . ' = ' . $this->db->quote($id));

                try {
                    $this->db->setQuery($query);
                    $updated = $this->db->execute();
                } catch (Exception $e) {
                    JLog::add('updateHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                    throw new Exception(Text::_('COM_EMUNDUS_RANKING_COULD_NOT_UPDATE_HIERARCHY'));
                }
            }

            if ($updated && isset($params['visible_hierarchies'])) {
                $update_hierarchies = [];
                foreach ($params['visible_hierarchies'] as $visible_hierarchy_id) {
                    $query->clear()
                        ->select('id')
                        ->from($this->db->quoteName('#__emundus_ranking_hierarchy_view'))
                        ->where($this->db->quoteName('hierarchy_id') . ' = ' . $this->db->quote($id))
                        ->andWhere($this->db->quoteName('visible_hierarchy_id') . ' = ' . $this->db->quote($visible_hierarchy_id));

                    try {
                        $this->db->setQuery($query);
                        $existing_hierarchy = $this->db->loadResult();
                    } catch (Exception $e) {
                        JLog::add('updateHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                        continue;
                    }

                    if (!empty($existing_hierarchy)) {
                        continue;
                    }

                    $query->clear()
                        ->insert($this->db->quoteName('#__emundus_ranking_hierarchy_view'))
                        ->columns($this->db->quoteName('hierarchy_id') . ', ' . $this->db->quoteName('visible_hierarchy_id'))
                        ->values($this->db->quote($id) . ', ' . $this->db->quote($visible_hierarchy_id));

                    try {
                        $this->db->setQuery($query);
                        $update_hierarchies[] = $this->db->execute();
                    } catch (Exception $e) {
                        $update_hierarchies[] = false;
                        JLog::add('updateHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                    }
                }

                $updated = !in_array(false, $update_hierarchies);
            }
        }

        return $updated;
    }

    /**
     * @param $user_id
     * @param $page
     * @param $limit
     * @return array|mixed
     */
    public function getFilesUserCanRank($user_id, $page = 1, $limit = 10, $sort = 'ASC', $hierarchy_order_by = 'default')
    {
        $files = [
            'total' => 0,
            'data' => [],
            'maxRankValue' => -1,
        ];

        /**
         * Avoid SQL injections
         */
        if (!is_numeric($page) || !is_numeric($limit)) {
            throw new Exception('Invalid page or limit value');
        }
        if ($sort !== 'ASC' && $sort !== 'DESC') {
            $sort = 'ASC';
        }

        $hierarchy = $this->getUserHierarchy($user_id);
        $status = $this->getStatusUserCanRank($user_id, $hierarchy);

        if ($status !== null) {
            $ids = $this->getAllFilesRankerCanAccessTo($user_id);

            if (!empty($ids)) {
                $MAX_RANK_VALUE = 999999;

                $query = $this->db->getQuery(true);
                $offset = ($page - 1) * $limit;
                $files['total'] = count($ids);
                $query->clear()
                    ->select('MAX(' . $this->db->quoteName('rank') . ')')
                    ->from($this->db->quoteName('#__emundus_ranking'))
                    ->where($this->db->quoteName('ccid') . ' IN (' . implode(',', $ids) . ')')
                    ->andWhere($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user_id))
                    ->andWhere($this->db->quoteName('hierarchy_id') . ' = ' . $this->db->quote($hierarchy));

                try {
                    $this->db->setQuery($query);
                    $max = $this->db->loadResult();
                    if (!empty($max)) {
                        $files['maxRankValue'] = (int)$max;
                    }
                } catch (Exception $e) {
                    JLog::add('getFilesUserCanRank ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                    throw new Exception('An error occurred while fetching the files.' . $query->__toString());
                }

                $set_limit = true;
                if (!empty($hierarchy_order_by) && $hierarchy_order_by !== 'default' && $hierarchy_order_by != $hierarchy) {
                    $query->clear()
                        ->select('DISTINCT cc.id as ccid, IF(er.hierarchy_id = ' . $this->db->quote($hierarchy_order_by) . ', er.rank, -1) as `rank`')
                        ->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'))
                        ->leftJoin($this->db->quoteName('#__emundus_ranking', 'er') . ' ON ' . $this->db->quoteName('cc.id') . ' = ' . $this->db->quoteName('er.ccid'))
                        ->where($this->db->quoteName('cc.id') . ' IN (' . implode(',', $ids) . ')')
                        ->andWhere($this->db->quoteName('er.hierarchy_id') . ' = ' . $this->db->quote($hierarchy_order_by) . ' OR ' . $this->db->quoteName('cc.id') . ' NOT IN (
                            SELECT cc.id
                            FROM `jos_emundus_campaign_candidature` AS `cc`
                            LEFT JOIN `jos_emundus_ranking` AS `er` ON `cc`.`id` = `er`.`ccid`
                            WHERE `cc`.`id` IN (' . implode(',', $ids) . ')
                            AND `er`.`hierarchy_id` = ' . $this->db->quote($hierarchy_order_by) . ')'
                        )
                        ->setLimit($limit, $offset);

                    if ($sort === 'ASC') {
                        $query->order('IFNULL(IF(`er`.`hierarchy_id` = ' . $this->db->quote($hierarchy_order_by) . ' AND `rank` != -1, `rank`, null), ' . $MAX_RANK_VALUE . ') ASC');
                    } else {
                        $query->order('IFNULL(IF(`er`.`hierarchy_id` = ' . $this->db->quote($hierarchy_order_by) . ', `rank`, -1), -1) DESC');
                    }

                    $this->db->setQuery($query);
                    $ranks = $this->db->loadAssocList('ccid');

                    if (!empty($ranks)) {
                        $ids = array_keys($ranks);
                        $set_limit = false;
                    }
                }

                $query->clear()
                    ->select('er.id as rank_id, CONCAT(applicant.firstname, " ", applicant.lastname) AS applicant, cc.id, cc.fnum, er.rank, er.locked, cc.status')
                    ->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'))
                    ->leftJoin($this->db->quoteName('#__emundus_users', 'applicant') . ' ON ' . $this->db->quoteName('cc.applicant_id') . ' = ' . $this->db->quoteName('applicant.user_id'))
                    ->leftJoin($this->db->quoteName('#__emundus_ranking', 'er') . ' ON ' . $this->db->quoteName('cc.id') . ' = ' . $this->db->quoteName('er.ccid') . ' AND er.user_id = ' . $this->db->quote($user_id))
                    ->where($this->db->quoteName('cc.id') . ' IN (' . implode(',', $ids) . ')')
                    ->andWhere('(er.hierarchy_id = ' . $this->db->quote($hierarchy) . ') OR er.id IS NULL');

                if ($set_limit) {
                    $query->setLimit($limit, $offset);
                }

                if ($hierarchy_order_by === 'default' && $hierarchy_order_by != $hierarchy) {
                    if ($sort == 'ASC') {
                        $query->order('IFNULL(IF(er.rank > 0, er.rank, null), ' . $MAX_RANK_VALUE . ')' . $sort);
                    } else {
                        $query->order('IFNULL(er.rank, -1) ' . $sort);
                    }
                }

                try {
                    $this->db->setQuery($query);
                    $files['data'] = $this->db->loadAssocList();
                } catch (Exception $e) {
                    JLog::add('getFilesUserCanRank ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                    throw new Exception('An error occurred while fetching the files.' . $e->getMessage());
                }

                foreach ($files['data'] as $key => $file) {
                    if (empty($file['locked']) && $file['locked'] != '0') {
                        $files['data'][$key]['locked'] = 0;
                    }

                    if ($file['status'] != $status && $file['locked'] != 1) {
                        $files['data'][$key]['locked'] = 1;
                    }

                    if (empty($file['rank'])) {
                        $files['data'][$key]['rank'] = -1; // -1 means not ranked
                    }
                }

                if (!empty($hierarchy_order_by) && $hierarchy_order_by !== 'default' && $hierarchy_order_by != $hierarchy) {
                    foreach ($files['data'] as $key => $file) {
                        if (isset($ranks[$file['id']])) {
                            $files['data'][$key]['sort_rank'] = !empty($ranks[$file['id']]['rank']) ? $ranks[$file['id']]['rank'] : -1;
                        } else {
                            $files['data'][$key]['sort_rank'] = -1;
                        }

                        if ($files['data'][$key]['sort_rank'] == -1 && $sort == 'ASC') {
                            $files['data'][$key]['sort_rank'] = $MAX_RANK_VALUE;
                        }
                    }

                    // sort the files by rank
                    usort($files['data'], function ($a, $b) use ($sort) {
                        if ($sort == 'ASC') {
                            return $a['sort_rank'] <=> $b['sort_rank'];
                        } else {
                            return $b['sort_rank'] <=> $a['sort_rank'];
                        }
                    });
                } else {
                    usort($files['data'], function ($a, $b) use ($sort) {
                        if ($sort == 'ASC') {
                            return $a['rank'] <=> $b['rank'];
                        } else {
                            return $b['rank'] <=> $a['rank'];
                        }
                    });
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

            $query = $this->db->getQuery(true);

            $query->clear()
                ->select('status')
                ->from($this->db->quoteName('#__emundus_ranking_hierarchy', 'ech'))
                ->where($this->db->quoteName('ech.id') . ' = ' . $this->db->quote($hierarchy));

            $this->db->setQuery($query);
            $status = $this->db->loadResult();
        }

        return $status;
    }

    public function getUserHierarchy($user_id)
    {
        $hierarchy = 0;

        if (!empty($user_id)) {
            $query = $this->db->getQuery(true);

            $query->clear()
                ->select('ech.id')
                ->from($this->db->quoteName('#__emundus_ranking_hierarchy', 'ech'))
                ->leftJoin($this->db->quoteName('#__emundus_users', 'eu') . ' ON ' . $this->db->quoteName('eu.profile') . ' = ' . $this->db->quoteName('ech.profile_id'))
                ->where($this->db->quoteName('eu.user_id') . ' = ' . $this->db->quote($user_id));


            try {
                $this->db->setQuery($query);
                $hierarchy = $this->db->loadResult();
            } catch (Exception $e) {
                JLog::add('getUserHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
            }
        }

        return $hierarchy;
    }

    public function getAllFilesRankerCanAccessTo($user_id, $files_status = null)
    {
        $file_ids = [];

        if (!empty($user_id)) {

            $query = $this->db->getQuery(true);

            $query->select('DISTINCT cc.id')
                ->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'))
                ->leftJoin($this->db->quoteName('#__emundus_users_assoc', 'eua') . ' ON ' . $this->db->quoteName('cc.fnum') . ' = ' . $this->db->quoteName('eua.fnum'))
                ->where($this->db->quoteName('eua.user_id') . ' = ' . $this->db->quote($user_id))
                ->andWhere($this->db->quoteName('cc.applicant_id') . ' != ' . $this->db->quote($user_id))
                ->andWhere($this->db->quoteName('eua.action_id') . ' = 1')
                ->andWhere($this->db->quoteName('eua.r') . ' = 1')
                ->andWhere($this->db->quoteName('cc.published') . ' = 1');

            if (!is_null($files_status)) {
                $query->andWhere($this->db->quoteName('cc.status') . ' = ' . $this->db->quote($files_status));
            }

            try {
                $this->db->setQuery($query);
                $users_assoc_ccids = $this->db->loadColumn();
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
                    ->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'))
                    ->leftJoin($this->db->quoteName('#__emundus_group_assoc', 'ega') . ' ON ' . $this->db->quoteName('cc.fnum') . ' = ' . $this->db->quoteName('ega.fnum'))
                    ->where($this->db->quoteName('ega.group_id') . ' IN (' . implode(',', $groups) . ')')
                    ->andWhere($this->db->quoteName('cc.applicant_id') . ' != ' . $this->db->quote($user_id))
                    ->andWhere($this->db->quoteName('ega.action_id') . ' = 1')
                    ->andWhere($this->db->quoteName('ega.r') . ' = 1')
                    ->andWhere($this->db->quoteName('cc.published') . ' = 1');

                if (!is_null($files_status)) {
                    $query->andWhere($this->db->quoteName('cc.status') . ' = ' . $this->db->quote($files_status));
                }

                $this->db->setQuery($query);
                $group_assoc_ccids = $this->db->loadColumn();

                if (!empty($group_assoc_ccids)) {
                    $file_ids = array_merge($file_ids, $group_assoc_ccids);
                }
            }

            $programs = $m_users->getUserGroupsProgramme($user_id);
            if (!empty($programs)) {
                $query->clear()
                    ->select('DISTINCT cc.id')
                    ->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'))
                    ->leftJoin($this->db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $this->db->quoteName('cc.campaign_id') . ' = ' . $this->db->quoteName('esc.id'))
                    ->where($this->db->quoteName('esc.training') . ' IN (' . implode(',', $this->db->quote($programs)) . ')')
                    ->andWhere($this->db->quoteName('cc.applicant_id') . ' != ' . $this->db->quote($user_id))
                    ->andWhere($this->db->quoteName('cc.published') . ' = 1');

                if (!is_null($files_status)) {
                    $query->andWhere($this->db->quoteName('cc.status') . ' = ' . $this->db->quote($files_status));
                }

                $this->db->setQuery($query);
                $program_assoc_ccids = $this->db->loadColumn();

                if (!empty($program_assoc_ccids)) {
                    $file_ids = array_merge($file_ids, $program_assoc_ccids);
                }
            }

            $file_ids = array_unique($file_ids);
        }

        return $file_ids;
    }

    /**
     * @param $user_id
     * @return array
     */
    public function getOtherRankingsRankerCanSee($user_id)
    {
        $rankings = [];

        if (!empty($user_id)) {
            $hierarchies = $this->getHierarchiesUserCanSee($user_id);
            $ids = $this->getAllFilesRankerCanAccessTo($user_id);

            if (!empty($hierarchies) && !empty($ids)) {

                $query = $this->db->getQuery(true);

                foreach ($hierarchies as $hierarchy) {
                    $data = [
                        'hierarchy_id' => $hierarchy['id'],
                        'label' => $hierarchy['label'],
                        'files' => [],
                        'rankers' => []
                    ];

                    $query->clear()
                        ->select('CONCAT(applicant.firstname, " ", applicant.lastname) AS applicant, cc.id, cc.fnum, cr.rank, cr.locked, cr.user_id as ranker_id')
                        ->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'))
                        ->leftJoin($this->db->quoteName('#__emundus_users', 'applicant') . ' ON ' . $this->db->quoteName('cc.applicant_id') . ' = ' . $this->db->quoteName('applicant.user_id'))
                        ->leftJoin($this->db->quoteName('#__emundus_ranking', 'cr') . ' ON ' . $this->db->quoteName('cc.id') . ' = ' . $this->db->quoteName('cr.ccid'))
                        ->where('cc.id IN (' . implode(',', $ids) . ')')
                        ->andWhere($this->db->quoteName('cr.hierarchy_id') . ' = ' . $hierarchy['id']);

                    try {
                        $this->db->setQuery($query);
                        $data['files'] = $this->db->loadAssocList();
                    } catch (Exception $e) {
                        JLog::add('getOtherRankingsRankerCanSee ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                        throw new Exception('An error occurred while fetching the files.' . $e->getMessage());
                    }

                    $query->clear()
                        ->select('CONCAT(u.firstname, " ", u.lastname) AS name, r.user_id')
                        ->from($this->db->quoteName('#__emundus_ranking', 'r'))
                        ->leftJoin($this->db->quoteName('#__emundus_users', 'u') . ' ON ' . $this->db->quoteName('r.user_id') . ' = ' . $this->db->quoteName('u.user_id'))
                        ->where('r.ccid IN (' . implode(',', $ids) . ')')
                        ->andWhere($this->db->quoteName('r.hierarchy_id') . ' = ' . $hierarchy['id']);

                    try {
                        $this->db->setQuery($query);
                        $data['rankers'] = $this->db->loadAssocList('user_id');
                    } catch (Exception $e) {
                        JLog::add('getOtherRankingsRankerCanSee ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                        throw new Exception('An error occurred while fetching the files.');
                    }

                    $rankings[] = $data;
                }
            }
        }

        return $rankings;
    }

    /**
     * @param $user_id
     * @return array
     * @throws Exception
     */
    public function getHierarchiesUserCanSee($user_id)
    {
        $hierarchies = [];
        $user_hierarchy = $this->getUserHierarchy($user_id);

        if (!empty($user_hierarchy)) {

            $query = $this->db->getQuery(true);

            $query->clear()
                ->select('DISTINCT ' . $this->db->quoteName('erh.id') . ', ' . $this->db->quoteName('erh.label'))
                ->from($this->db->quoteName('#__emundus_ranking_hierarchy_view', 'erhv'))
                ->leftJoin($this->db->quoteName('#__emundus_ranking_hierarchy', 'erh') . ' ON ' . $this->db->quoteName('erhv.visible_hierarchy_id') . ' = ' . $this->db->quoteName('erh.id'))
                ->where($this->db->quoteName('erhv.hierarchy_id') . ' = ' . $this->db->quote($user_hierarchy))
                ->order($this->db->quoteName('erhv.ordering'));

            if (!empty($this->filters)) {
                // check if there is a filter on hierarchy_id and if so, add it to the query
                $subquery = $this->db->getQuery(true);
                foreach ($this->filters as $filter) {
                    $subquery->clear()
                        ->select('name')
                        ->from($this->db->quoteName('#__fabrik_elements'))
                        ->where($this->db->quoteName('id') . ' = ' . $this->db->quote($filter['id']));

                    $this->db->setQuery($subquery);
                    $element = $this->db->loadResult();

                    if ($element == 'hierarchy_id' && !empty($filter['value']) && $filter['value'] != 'all' && $filter['value'] != ['all']) {
                        $query->where($this->h_files->writeQueryWithOperator('erh.id', $filter['value'], $filter['operator']));
                    }
                }
            }

            try {
                $this->db->setQuery($query);
                $hierarchies = $this->db->loadAssocList();
            } catch (Exception $e) {
                JLog::add('getHierarchiesUserCanSee ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                throw new Exception('An error occurred while fetching the hierarchies.');
            }
        }

        return $hierarchies;
    }


    /**
     * @param $id
     * @param $user_id
     * @param $hierarchy_id
     * @return int
     */
    public function getFileRanking($id, $user_id, $hierarchy_id)
    {
        $rank = -1;

        if (!empty($id) && !empty($user_id) && !empty($hierarchy_id)) {
            $query = $this->db->getQuery(true);
            $query->clear()
                ->select($this->db->quoteName('rank'))
                ->from($this->db->quoteName('#__emundus_ranking'))
                ->where($this->db->quoteName('ccid') . ' = ' . $this->db->quote($id))
                ->andWhere($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user_id))
                ->andWhere($this->db->quoteName('hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id));

            try {
                $this->db->setQuery($query);
                $rank = (int)$this->db->loadResult();

                if ($rank < 1) {
                    $rank = -1;
                }
            } catch (Exception $e) {
                JLog::add('getFileRanking ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
            }
        }

        return $rank;
    }

    /**
     * @param $user_id
     * @param $hierarchy_id
     * @return array
     */
    public function getAllRankings($user_id, $hierarchy_id = null)
    {
        $rankings = [];

        if (!empty($user_id)) {
            if (empty($hierarchy_id)) {
                $hierarchy_id = $this->getUserHierarchy($user_id);
            }

            $query = $this->db->getQuery(true);
            $query->select('*')
                ->from($this->db->quoteName('#__emundus_ranking'))
                ->where($this->db->quoteName('user_id') . ' = ' . $user_id)
                ->andWhere($this->db->quoteName('hierarchy_id') . ' = ' . $hierarchy_id);

            try {
                $this->db->setQuery($query);
                $rankings = $this->db->loadAssocList();
            } catch (Exception $e) {
                JLog::add('Failed to get user rankings ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
            }
        }

        return $rankings;
    }

    /**
     * @param $id
     * @param $user_id
     * @param $new_rank
     * @param $hierarchy_id
     * @return false
     * @throws Exception
     */
    public function updateFileRanking($id, $user_id, $new_rank, $hierarchy_id)
    {
        $updated = false;

        if (!empty($id) && !empty($user_id) && !empty($new_rank) && !empty($hierarchy_id)) {
            $query = $this->db->getQuery(true);
            $query->clear()
                ->select($this->db->quoteName('applicant_id'))
                ->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'))
                ->where($this->db->quoteName('cc.id') . ' = ' . $this->db->quote($id));

            $this->db->setQuery($query);
            $applicant_id = $this->db->loadResult();

            if ($applicant_id == $user_id) {
                throw new Exception(Text::_('COM_EMUNDUS_RANKING_UPDATE_RANKING_ERROR_RANK_OWN_FILE'));
            }

            $query->clear()
                ->select($this->db->quoteName('cc.status'))
                ->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'))
                ->where($this->db->quoteName('cc.id') . ' = ' . $this->db->quote($id));
            $file_status = $this->db->setQuery($query)->loadResult();
            $status_user_can_rank = $this->getStatusUserCanRank($user_id, $hierarchy_id);

            if ($file_status == $status_user_can_rank) {
                $query->clear()
                    ->select($this->db->quoteName('id') . ', ' . $this->db->quoteName('rank') . ', ' . $this->db->quoteName('locked'))
                    ->from($this->db->quoteName('#__emundus_ranking'))
                    ->where($this->db->quoteName('ccid') . ' = ' . $this->db->quote($id))
                    ->andWhere($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user_id))
                    ->andWhere($this->db->quoteName('hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id));

                $this->db->setQuery($query);
                $ranking = $this->db->loadAssoc();

                if (!empty($ranking) && $ranking['locked'] == 1) {
                    throw new Exception(Text::_('COM_EMUNDUS_RANKING_UPDATE_RANKING_ERROR_LOCKED'));
                }

                $old_rank = !empty($ranking) && !empty($ranking['rank']) && $ranking['rank'] > 0 ? $ranking['rank'] : -1;

                if ($old_rank == $new_rank) {
                    $updated = true;
                } else {
                    // if the new rank is -1, we need to decrease all ranks above the old rank by 1, unless they are locked
                    if ($new_rank != -1) {
                        // does the rank i want to reach already taken by another file and locked ?
                        $query->clear()
                            ->select($this->db->quoteName('er.ccid') . ', ' . $this->db->quoteName('er.locked') . ', ' . $this->db->quoteName('cc.status'))
                            ->from($this->db->quoteName('#__emundus_ranking', 'er'))
                            ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature', 'cc') . ' ON ' . $this->db->quoteName('cc.id') . ' = ' . $this->db->quoteName('er.ccid'))
                            ->where($this->db->quoteName('er.rank') . ' = ' . $this->db->quote($new_rank))
                            ->andWhere($this->db->quoteName('er.hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id))
                            ->andWhere($this->db->quoteName('er.user_id') . ' = ' . $this->db->quote($user_id));

                        $this->db->setQuery($query);
                        $same_rank_data = $this->db->loadAssoc();

                        if (!empty($same_rank_data) && ($same_rank_data['locked'] == 1 || $same_rank_data['status'] != $status_user_can_rank)) {
                            throw new Exception(Text::_('COM_EMUNDUS_RANKING_UPDATE_RANKING_ERROR_RANK_UNREACHABLE'));
                        }

                        if (!empty($ranking) && !empty($ranking['id'])) {
                            $max_rank = $this->getMaxRankAvailable($hierarchy_id, $user_id, $ranking['id']);
                        } else {
                            $max_rank = $this->getMaxRankAvailable($hierarchy_id, $user_id);
                        }

                        if ($new_rank > $max_rank) {
                            throw new Exception(Text::_('COM_EMUNDUS_RANKING_UPDATE_RANKING_ERROR_NEW_RANK_UNREACHABLE'));
                        }
                    }

                    $re_arranged_ranking = [];
                    $locked_rank_positions = [];
                    if ($new_rank == -1) {
                        // all ranks superior or equal to old rank should be decreased by 1 unless they are locked
                        $query->clear()
                            ->select($this->db->quoteName('er.rank') . ', ' . $this->db->quoteName('er.id') . ', ' . $this->db->quoteName('er.locked') . ', ' . $this->db->quoteName('cc.status'))
                            ->from($this->db->quoteName('#__emundus_ranking', 'er'))
                            ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature', 'cc') . ' ON ' . $this->db->quoteName('cc.id') . ' = ' . $this->db->quoteName('er.ccid'))
                            ->where($this->db->quoteName('er.user_id') . ' = ' . $this->db->quote($user_id))
                            ->andWhere($this->db->quoteName('er.hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id))
                            ->andWhere($this->db->quoteName('er.rank') . ' > ' . $this->db->quote($ranking['rank']))
                            ->order($this->db->quoteName('er.rank') . ' ASC');

                        $this->db->setQuery($query);
                        $ranks = $this->db->loadAssocList();
                        $rank_to_apply = (int)$old_rank;

                        $locked_rank_positions = array_filter(array_map(function ($rank) use ($status_user_can_rank) {
                            return $rank['locked'] == 1 || $rank['status'] != $status_user_can_rank ? $rank['rank'] : null;
                        }, $ranks));

                        foreach ($ranks as $rank) {
                            if ($rank['locked'] != 1 && $rank['status'] == $status_user_can_rank) {
                                $re_arranged_ranking[$rank['id']] = $rank_to_apply;
                                $rank_to_apply++;

                                while (in_array($rank_to_apply, $locked_rank_positions)) {
                                    $rank_to_apply++;
                                }
                            }
                        }
                    } else if ($old_rank == -1) {
                        // all ranks superior or equal to new rank should be increased by 1 unless they are locked
                        $query->clear()
                            ->select($this->db->quoteName('er.rank') . ', ' . $this->db->quoteName('er.id') . ', ' . $this->db->quoteName('er.locked') . ', ' . $this->db->quoteName('cc.status'))
                            ->from($this->db->quoteName('#__emundus_ranking', 'er'))
                            ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature', 'cc') . ' ON ' . $this->db->quoteName('cc.id') . ' = ' . $this->db->quoteName('er.ccid'))
                            ->where($this->db->quoteName('er.user_id') . ' = ' . $this->db->quote($user_id))
                            ->andWhere($this->db->quoteName('er.hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id))
                            ->andWhere($this->db->quoteName('er.rank') . ' >= ' . $this->db->quote($new_rank))
                            ->order($this->db->quoteName('er.rank') . ' ASC');

                        $this->db->setQuery($query);
                        $ranks = $this->db->loadAssocList();
                        $rank_to_apply = $new_rank + 1;

                        $locked_rank_positions = array_filter(array_map(function ($rank) use ($status_user_can_rank) {
                            return $rank['locked'] == 1 || $rank['status'] != $status_user_can_rank ? $rank['rank'] : null;
                        }, $ranks));

                        while (in_array($rank_to_apply, $locked_rank_positions)) {
                            $rank_to_apply++;
                        }

                        foreach ($ranks as $rank) {
                            if ($rank['locked'] != 1 && $rank['status'] == $status_user_can_rank) {
                                $re_arranged_ranking[$rank['id']] = $rank_to_apply;
                                $rank_to_apply++;

                                while (in_array($rank_to_apply, $locked_rank_positions)) {
                                    $rank_to_apply++;
                                }
                            }
                        }
                    } else if ($old_rank > $new_rank) {
                        // all ranks between new rank and old rank should be increased by 1 unless they are locked
                        $query->clear()
                            ->select($this->db->quoteName('er.rank') . ', ' . $this->db->quoteName('er.id') . ', ' . $this->db->quoteName('er.locked') . ', ' . $this->db->quoteName('cc.status'))
                            ->from($this->db->quoteName('#__emundus_ranking', 'er'))
                            ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature', 'cc') . ' ON ' . $this->db->quoteName('cc.id') . ' = ' . $this->db->quoteName('er.ccid'))
                            ->where($this->db->quoteName('er.user_id') . ' = ' . $this->db->quote($user_id))
                            ->andWhere($this->db->quoteName('er.hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id))
                            ->andWhere($this->db->quoteName('er.rank') . ' >= ' . $this->db->quote($new_rank))
                            ->andWhere($this->db->quoteName('er.rank') . ' < ' . $this->db->quote($old_rank))
                            ->order($this->db->quoteName('er.rank') . ' DESC');

                        $this->db->setQuery($query);
                        $ranks = $this->db->loadAssocList();
                        $rank_to_apply = (int)$old_rank;

                        $locked_rank_positions = array_filter(array_map(function ($rank) use ($status_user_can_rank) {
                            return $rank['locked'] == 1 || $rank['status'] != $status_user_can_rank ? $rank['rank'] : null;
                        }, $ranks));

                        foreach ($ranks as $rank) {
                            if ($rank['locked'] != 1 && $rank['status'] == $status_user_can_rank) {
                                $re_arranged_ranking[$rank['id']] = $rank_to_apply;
                                $rank_to_apply--;

                                while (in_array($rank_to_apply, $locked_rank_positions)) {
                                    $rank_to_apply--;
                                }
                            }
                        }
                    } else if ($old_rank < $new_rank) {
                        // all ranks between old rank and new rank should be decreased by 1 unless they are locked
                        $query->clear()
                            ->select($this->db->quoteName('er.rank') . ', ' . $this->db->quoteName('er.id') . ', ' . $this->db->quoteName('er.locked') . ', ' . $this->db->quoteName('cc.status'))
                            ->from($this->db->quoteName('#__emundus_ranking', 'er'))
                            ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature', 'cc') . ' ON ' . $this->db->quoteName('cc.id') . ' = ' . $this->db->quoteName('er.ccid'))
                            ->where($this->db->quoteName('er.user_id') . ' = ' . $this->db->quote($user_id))
                            ->andWhere($this->db->quoteName('er.hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id))
                            ->andWhere($this->db->quoteName('er.rank') . ' > ' . $this->db->quote($old_rank))
                            ->andWhere($this->db->quoteName('er.rank') . ' <= ' . $this->db->quote($new_rank))
                            ->order($this->db->quoteName('er.rank') . ' ASC');

                        $this->db->setQuery($query);
                        $ranks = $this->db->loadAssocList();
                        $rank_to_apply = (int)$old_rank;

                        $locked_rank_positions = array_filter(array_map(function ($rank) use ($status_user_can_rank) {
                            return $rank['locked'] == 1 || $rank['status'] != $status_user_can_rank ? $rank['rank'] : null;
                        }, $ranks));

                        foreach ($ranks as $rank) {
                            if ($rank['locked'] != 1 && $rank['status'] == $status_user_can_rank) {
                                $re_arranged_ranking[$rank['id']] = $rank_to_apply;
                                $rank_to_apply++;

                                while (in_array($rank_to_apply, $locked_rank_positions)) {
                                    $rank_to_apply++;
                                }
                            }
                        }
                    }

                    foreach ($re_arranged_ranking as $rank_row_id => $new_rank_for_row) {
                        $query->clear()
                            ->update($this->db->quoteName('#__emundus_ranking'))
                            ->set($this->db->quoteName('rank') . ' = ' . $this->db->quote($new_rank_for_row))
                            ->where($this->db->quoteName('id') . ' = ' . $this->db->quote($rank_row_id));

                        try {
                            $this->db->setQuery($query);
                            $this->db->execute();
                        } catch (Exception $e) {
                            JLog::add('updateFileRanking ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                            throw new Exception('An error occurred while updating files ranking.');
                        }
                    }
                }

                $query->clear()
                    ->select('id')
                    ->from($this->db->quoteName('#__emundus_ranking'))
                    ->where($this->db->quoteName('ccid') . ' = ' . $this->db->quote($id))
                    ->andWhere($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user_id))
                    ->andWhere($this->db->quoteName('hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id));

                $this->db->setQuery($query);
                $ranking_id = $this->db->loadResult();

                if (!empty($ranking_id)) {
                    $query->clear()
                        ->update($this->db->quoteName('#__emundus_ranking'))
                        ->set($this->db->quoteName('rank') . ' = ' . $this->db->quote($new_rank))
                        ->where($this->db->quoteName('id') . ' = ' . $this->db->quote($ranking_id));

                    $this->db->setQuery($query);
                    $updated = $this->db->execute();
                } else {
                    $query->clear()
                        ->insert($this->db->quoteName('#__emundus_ranking'))
                        ->columns($this->db->quoteName('ccid') . ', ' . $this->db->quoteName('user_id') . ', ' . $this->db->quoteName('rank') . ', ' . $this->db->quoteName('hierarchy_id'))
                        ->values($this->db->quote($id) . ', ' . $this->db->quote($user_id) . ', ' . $this->db->quote($new_rank) . ', ' . $this->db->quote($hierarchy_id));

                    $this->db->setQuery($query);
                    $updated = $this->db->execute();
                }
            } else {
                throw new Exception(Text::_('COM_EMUNDUS_RANKING_UPDATE_RANKING_ERROR_RANK_NOT_ALLOWED_STATUS'));
            }
        }

        return $updated;
    }

    /**
     * Returns the max next position reachable.
     * If current max rank is x, then return x+1 (unless max is -1, return 1)
     * @param $hierarchy_id
     * @param $user_id
     * @return int
     */
    public function getMaxRankAvailable($hierarchy_id, $user_id, $rank_row_id = null)
    {
        $max_value_reachable = 1;

        if (!empty($hierarchy_id) && !empty($user_id)) {
            $query = $this->db->getQuery(true);
            $query->clear()
                ->select('MAX(' . $this->db->quoteName('rank') . ') as max')
                ->from($this->db->quoteName('#__emundus_ranking'))
                ->where($this->db->quoteName('hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id))
                ->andWhere($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user_id));

            if (!empty($rank_row_id)) {
                $query->andWhere($this->db->quoteName('id') . ' != ' . $rank_row_id);
            }

            $this->db->setQuery($query);
            $current_max_value = $this->db->loadResult();

            if ($current_max_value > 0) {
                $max_value_reachable = $current_max_value + 1;
            }
        }

        return $max_value_reachable;
    }

    /**
     * @param $hierarchy_id
     * @param $user_id
     * @param $locked
     * @return boolean
     */
    public function toggleLockFilesOfHierarchyRanking($hierarchy_id, $user_id, $locked = 1): bool
    {
        $toggled = false;

        if (!empty($hierarchy_id) && !empty($user_id)) {

            $query = $this->db->getQuery(true);

            $query->clear()
                ->update($this->db->quoteName('#__emundus_ranking'))
                ->set($this->db->quoteName('locked') . ' = ' . $this->db->quote($locked))
                ->where($this->db->quoteName('hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id))
                ->andWhere($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user_id));

            $this->db->setQuery($query);
            $toggled = $this->db->execute();

            if ($locked == 1 && $toggled) {
                /**
                 * Send email to parent id hierarchy to inform that the ranking has been locked
                 */
                $query->clear()
                    ->select('erh.parent_id, erh.label')
                    ->from($this->db->quoteName('#__emundus_ranking_hierarchy', 'erh'))
                    ->where($this->db->quoteName('erh.id') . ' = ' . $this->db->quote($hierarchy_id));

                $this->db->setQuery($query);
                $hierarchy_infos = $this->db->loadAssoc();

                if (!empty($hierarchy_infos['parent_id'])) {
                    $query->clear()
                        ->select('profile_id')
                        ->from($this->db->quoteName('#__emundus_ranking_hierarchy', 'erh'))
                        ->where($this->db->quoteName('erh.id') . ' = ' . $this->db->quote($hierarchy_infos['parent_id']));
                    $this->db->setQuery($query);
                    $profile_id = $this->db->loadResult();

                    $query->clear()
                        ->select('DISTINCT u.email')
                        ->from($this->db->quoteName('#__users', 'u'))
                        ->leftJoin($this->db->quoteName('#__emundus_users', 'eu') . ' ON ' . $this->db->quoteName('u.id') . ' = ' . $this->db->quoteName('eu.user_id'))
                        ->leftJoin($this->db->quoteName('#__emundus_users_profiles', 'eup') . ' ON ' . $this->db->quoteName('eup.user_id') . ' = ' . $this->db->quoteName('eu.user_id'))
                        ->where('(' . $this->db->quoteName('eu.profile') . ' = ' . $this->db->quote($profile_id) . ' OR ' . $this->db->quoteName('eup.profile_id') . ' = ' . $this->db->quote($profile_id) . ')')
                        ->andWhere('u.block = 0');

                    $this->db->setQuery($query);
                    $emails = $this->db->loadColumn();

                    if (!empty($emails)) {
                        require_once(JPATH_ROOT . '/components/com_emundus/models/emails.php');
                        $m_emails = new EmundusModelEmails();
                        $email_to_send = 'ranking_locked';

                        $query->clear()
                            ->select('firstname, lastname')
                            ->from($this->db->quoteName('#__emundus_users'))
                            ->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user_id));

                        $this->db->setQuery($query);
                        $user = $this->db->loadAssoc();

                        $post = [
                            'RANKER_NAME' => $user['firstname'] . ' ' . $user['lastname'],
                            'RANKER_HIERARCHY' => $hierarchy_infos['label'],
                        ];
                        foreach ($emails as $email) {
                            $m_emails->sendEmailNoFnum($email, $email_to_send, $post, $user_id);
                        }
                    }
                }
            }
        }

        return $toggled;
    }

    /**
     * @param $user_asking ,
     * @param $users , I can specify the users to ask to lock their rankings
     * @param $hierarchies , I can specify all users of a hierarchy to lock their rankings
     * @return array
     * @throws Exception if I try to ask rankings to be locked for a user or a hierarchy I am not allowed to
     */
    public function askUsersToLockRankings($user_asking, $users, $hierarchies): array
    {
        $response = [
            'asked' => false,
            'asked_to' => []
        ];

        if (empty($user_asking)) {
            throw new Exception(Text::_('USER_ASKING_TO_LOCK_MUST_BE_DEFINED'));
        }

        $ccids = $this->getAllFilesRankerCanAccessTo($user_asking);
        if (empty($ccids)) {
            throw new Exception(Text::_('USER_ASKING_TO_LOCK_FILES_BUT_HAS_NO_ACCESS'));
        }

        if (!empty($users) || !empty($hierarchies)) {
            $query = $this->db->getQuery(true);

            if (!empty($hierarchies)) {
                $hierarchies_user_as_access_to = $this->getHierarchiesUserCanSee($user_asking);
                $hierarchy_ids_user_as_access_to = array_map(function ($hierarchy) {
                    return $hierarchy['id'];
                }, $hierarchies_user_as_access_to);

                foreach ($hierarchies as $key => $hierarchy) {
                    if (!in_array($hierarchy, $hierarchy_ids_user_as_access_to)) {
                        unset($hierarchies[$key]);
                        // could log attempt to ask wrong hierarchy
                    }
                }

                if (!empty($hierarchies)) {
                    $query->clear()
                        ->select('DISTINCT user_id')
                        ->from($this->db->quoteName('#__emundus_ranking'))
                        ->where($this->db->quoteName('ccid') . ' IN (' . implode(',', $ccids) . ')')
                        ->andWhere($this->db->quoteName('hierarchy_id') . ' IN (' . implode(',', $hierarchies) . ')')
                        ->andWhere($this->db->quoteName('user_id') . ' != ' . $user_asking);

                    try {
                        $this->db->setQuery($query);
                        $hierarchy_users = $this->db->loadColumn();

                        $users = array_merge($users, $hierarchy_users);
                        $users = array_unique($users);
                    } catch (Exception $e) {
                        JLog::add('Failed to get users of hierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking');
                    }
                }
            }

            if (!empty($users)) {
                // keep only users I can ask to
                $query->clear()
                    ->select('DISTINCT u.email, u.id')
                    ->from($this->db->quoteName('#__emundus_ranking', 'er'))
                    ->leftJoin($this->db->quoteName('#__users', 'u') . ' ON ' . $this->db->quoteName('u.id') . ' = ' . $this->db->quoteName('er.user_id'))
                    ->where($this->db->quoteName('er.ccid') . ' IN (' . implode(',', $ccids) . ')')
                    ->andWhere($this->db->quoteName('er.user_id') . ' IN (' . implode(',', $users) . ')')
                    ->andWhere($this->db->quoteName('er.locked') . ' = 0');

                try {
                    $this->db->setQuery($query);
                    $user_emails = $this->db->loadAssoclist();
                } catch (Exception $e) {
                    JLog::add('Failed to get emails ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking');
                }

                if (!empty($user_emails)) {
                    require_once(JPATH_ROOT . '/components/com_emundus/models/emails.php');
                    $m_emails = new EmundusModelEmails();
                    $email_to_send = 'ask_lock_ranking';
                    $response['asked'] = true;

                    foreach ($user_emails as $user) {
                        $sent = $m_emails->sendEmailNoFnum($user['email'], $email_to_send, null, $user['id']);

                        if ($sent) {
                            $response['asked_to'][] = $user['email'];
                        }
                    }
                }
            }
        }

        return $response;
    }
}
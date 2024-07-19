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

    private $can_user_rank_himself = false;

    private $all_rights_profile = null;

    public $filters = [];
    private $h_files = null;

    private $logger = null;

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

        if (!class_exists('EmundusModelLogs')) {
            require_once(JPATH_ROOT . '/components/com_emundus/models/logs.php');
        }
        $this->logger = new EmundusModelLogs();

        $this->db = Factory::getDBO();

        $this->all_rights_profile = 2;

        JLog::addLogger(['text_file' => 'com_emundus.ranking.php'], JLog::ALL);
    }

    private function dispatchEvent($event, $args) {
        JPluginHelper::importPlugin('emundus');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger($event, $args);
        $dispatcher->trigger('callEventHandler', [$event, $args]);
    }

    /**
     * @param $label
     * @param $status
     * @param $profile_ids array
     * @param $published
     * @return int
     * @throws Exception
     */
    public function createHierarchy($label, $status, $profile_ids, $parent_hierarchy = 0, $published = 1, $visible_hierarchies = [], $visible_status = [])
    {
        $hierarchy_id = 0;

        if (!empty($label) && is_numeric($status) && !empty($profile_ids)) {
            $query = $this->db->getQuery(true);

            $query->clear()
                ->select('id')
                ->from($this->db->quoteName('#__emundus_setup_profiles'))
                ->where($this->db->quoteName('id') . ' IN (' . implode(',', $profile_ids) . ')');

            try {
                $this->db->setQuery($query);
                $profile_ids = $this->db->loadColumn();
            } catch (Exception $e) {
                JLog::add('createHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                throw new Exception(Text::_('COM_EMUNDUS_RANKING_COULD_NOT_DETERMINE_PROFILE_EXISTENCE'));
            }

            if (empty($profile_ids)) {
                throw new Exception(Text::_('COM_EMUNDUS_RANKING_PROFILE_DOES_NOT_EXIST'));
            }

            $query->clear()
                ->select('DISTINCT erh.id')
                ->from($this->db->quoteName('#__emundus_ranking_hierarchy', 'erh'))
                ->leftJoin($this->db->quoteName('#__emundus_ranking_hierarchy_profiles', 'erhp') . ' ON erhp.hierarchy_id = erh.id')
                ->where($this->db->quoteName('erhp.profile_id') . ' IN (' . implode(',', $profile_ids) . ')')
                ->andWhere($this->db->quoteName('erh.status') . ' = ' . $this->db->quote($status))
                ->group($this->db->quoteName('erh.id'));

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
                ->columns($this->db->quoteName('label') . ', ' . $this->db->quoteName('status') . ', ' . $this->db->quoteName('parent_id') . ', ' . $this->db->quoteName('published'))
                ->values($this->db->quote($label) . ', ' . $this->db->quote($status) . ', ' . $this->db->quote($parent_hierarchy) . ', ' . $this->db->quote($published));

            try {
                $this->db->setQuery($query);
                $this->db->execute();
                $hierarchy_id = $this->db->insertid();
            } catch (Exception $e) {
                JLog::add('createHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                throw new Exception(Text::_('COM_EMUNDUS_RANKING_COULD_NOT_CREATE_HIERARCHY'));
            }

            if (!empty($hierarchy_id)) {
                foreach($profile_ids as $profile_id) {
                    $query->clear()
                        ->insert($this->db->quoteName('#__emundus_ranking_hierarchy_profiles'))
                        ->columns($this->db->quoteName('hierarchy_id') . ', ' . $this->db->quoteName('profile_id'))
                        ->values($this->db->quote($hierarchy_id) . ', ' . $this->db->quote($profile_id));

                    try {
                        $this->db->setQuery($query);
                        $this->db->execute();
                    } catch (Exception $e) {
                        JLog::add('createHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                        continue;
                    } 
                }


                if (!empty($visible_hierarchies)) {
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

                if (!empty($visible_status)) {
                    foreach ($visible_status as $status) {
                        $query->clear()
                            ->insert($this->db->quoteName('#__emundus_ranking_hierarchy_visible_status'))
                            ->columns($this->db->quoteName('hierarchy_id') . ', ' . $this->db->quoteName('status'))
                            ->values($this->db->quote($hierarchy_id) . ', ' . $this->db->quote($status));

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
        }

        return $hierarchy_id;
    }

    public function updateHierarchy($id, $params)
    {
        $updated = true;

        if (!empty($id) && !empty($params)) {
            $query = $this->db->getQuery(true);

            $columns_allowed = ['label', 'status', 'parent_id', 'published'];
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

            if ($updated) {
                $updates = [];

                if (isset($params['profile_ids'])) {
                    // remove all profiles from the hierarchy
                    $query->clear()
                        ->delete($this->db->quoteName('#__emundus_ranking_hierarchy_profiles'))
                        ->where($this->db->quoteName('hierarchy_id') . ' = ' . $this->db->quote($id));

                    try {
                        $this->db->setQuery($query);
                        $this->db->execute();
                    } catch (Exception $e) {
                        JLog::add('updateHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                    }

                    // add the new profiles
                    foreach($params['profile_ids'] as $profile_id) {
                        $query->clear()
                            ->insert($this->db->quoteName('#__emundus_ranking_hierarchy_profiles'))
                            ->columns($this->db->quoteName('hierarchy_id') . ', ' . $this->db->quoteName('profile_id'))
                            ->values($this->db->quote($id) . ', ' . $this->db->quote($profile_id));

                        try {
                            $this->db->setQuery($query);
                            $updates[] = $this->db->execute();
                        } catch (Exception $e) {
                            JLog::add('updateHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                        }
                    }
                }

                if (isset($params['visible_hierarchies'])) {
                    // delete all visible hierarchies
                    $query->clear()
                        ->delete($this->db->quoteName('#__emundus_ranking_hierarchy_view'))
                        ->where($this->db->quoteName('hierarchy_id') . ' = ' . $this->db->quote($id));

                    try {
                        $this->db->setQuery($query);
                        $this->db->execute();
                    } catch (Exception $e) {
                        JLog::add('updateHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                    }

                    // add the new visible hierarchies
                    foreach ($params['visible_hierarchies'] as $visible_hierarchy_id) {
                        $query->clear()
                            ->insert($this->db->quoteName('#__emundus_ranking_hierarchy_view'))
                            ->columns($this->db->quoteName('hierarchy_id') . ', ' . $this->db->quoteName('visible_hierarchy_id'))
                            ->values($this->db->quote($id) . ', ' . $this->db->quote($visible_hierarchy_id));

                        try {
                            $this->db->setQuery($query);
                            $updates[] = $this->db->execute();
                        } catch (Exception $e) {
                            JLog::add('updateHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                        }
                    }
                }

                if (isset($params['visible_status'])) {
                    // delete all visible status
                    $query->clear()
                        ->delete($this->db->quoteName('#__emundus_ranking_hierarchy_visible_status'))
                        ->where($this->db->quoteName('hierarchy_id') . ' = ' . $this->db->quote($id));

                    try {
                        $this->db->setQuery($query);
                        $this->db->execute();
                    } catch (Exception $e) {
                        JLog::add('updateHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                    }

                    // add the new visible status
                    foreach ($params['visible_status'] as $status) {
                        $query->clear()
                            ->insert($this->db->quoteName('#__emundus_ranking_hierarchy_visible_status'))
                            ->columns($this->db->quoteName('hierarchy_id') . ', ' . $this->db->quoteName('status'))
                            ->values($this->db->quote($id) . ', ' . $this->db->quote($status));

                        try {
                            $this->db->setQuery($query);
                            $updates[] = $this->db->execute();
                        } catch (Exception $e) {
                            JLog::add('updateHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                        }
                    }

                }

                $updated = !in_array(false, $updates);
            }
        }

        return $updated;
    }

    /**
     * get hierarchy packages for a user
     */
    public function getUserPackages($user_id, $package_id = null) {
        $packages = [];

        if (!empty($user_id)) {
            $hierarchy_id = $this->getUserHierarchy($user_id);
            $hierarchy = $this->getHierarchyData($hierarchy_id);

            if ($hierarchy['package_by'] === 'jos_emundus_setup_campaigns.id') {
                $ccids = $this->getAllFilesRankerCanAccessTo($user_id, $hierarchy['id']);

                $query = $this->db->getQuery(true);
                $query->select('DISTINCT esc.id, esc.label, esc.start_date, esc.end_date, esp.id as programme_id, esp.label as programme_label, esp.programmes as group_id')
                    ->from($this->db->quoteName('#__emundus_setup_campaigns', 'esc'))
                    ->leftJoin($this->db->quoteName('#__emundus_setup_programmes', 'esp') . ' ON ' . $this->db->quoteName('esp.code') . ' = ' . $this->db->quoteName('esc.training'))
                    ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature', 'cc') . ' ON ' . $this->db->quoteName('esc.id') . ' = ' . $this->db->quoteName('cc.campaign_id'))
                    ->where($this->db->quoteName('cc.id') . ' IN (' . implode(',', $ccids) . ')')
                    ->andWhere($this->db->quoteName('esc.published') . ' = 1');

                if (!empty($package_id)) {
                    $query->andWhere($this->db->quoteName('esc.id') . ' = ' . $this->db->quote($package_id));
                }

                try {
                    $this->db->setQuery($query);
                    $packages = $this->db->loadAssocList();
                } catch (Exception $e) {
                    JLog::add('getHierarchyPackages ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                    $packages = [];
                }

                if (!empty($hierarchy['package_start_date_field'])) {
                    list($table, $column) = explode('.', $hierarchy['package_start_date_field']);
                    $package_column = $table === 'jos_emundus_setup_campaigns' ? 'id' : 'campaign_id';

                    foreach($packages as $key => $package) {
                        $query->clear()
                            ->select($this->db->quoteName($column))
                            ->from($this->db->quoteName($table))
                            ->where($this->db->quoteName($package_column) . ' = ' . $this->db->quote($package['id']));

                        try {
                            $this->db->setQuery($query);
                            $package_start_date = $this->db->loadResult();

                            if (!empty($package_start_date)) {
                                $packages[$key]['start_date'] = $package_start_date;
                            }
                        } catch (Exception $e) {
                            JLog::add('getHierarchyPackages ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                            $packages[$key]['start_date'] = null;
                        }
                    }
                }

                if (!empty($hierarchy['package_end_date_field'])) {
                    list($table, $column) = explode('.', $hierarchy['package_end_date_field']);
                    $package_column = $table === 'jos_emundus_setup_campaigns' ? 'id' : 'campaign_id';

                    foreach($packages as $key => $package) {
                        $query->clear()
                            ->select($this->db->quoteName($column))
                            ->from($this->db->quoteName($table))
                            ->where($this->db->quoteName($package_column) . ' = ' . $this->db->quote($package['id']));

                        try {
                            $this->db->setQuery($query);
                            $package_end_date = $this->db->loadResult();

                            if (!empty($package_end_date)) {
                                $packages[$key]['end_date'] = $package_end_date;
                            }
                        } catch (Exception $e) {
                            JLog::add('getHierarchyPackages ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                            $packages[$key]['end_date'] = null;
                        }
                    }
                }
            } else {
                $packages[] = [
                    'id' => 0,
                    'label' => 'All',
                    'start_date' => null,
                    'end_date' => null
                ];
            }
        }

        foreach($packages as $key => $package) {
            if (!empty($package['start_date'])) {
                $packages[$key]['start_time'] = strtotime($package['start_date']);
                $packages[$key]['start_date'] = EmundusHelperDate::displayDate($package['start_date'], 'd/m/Y H\hi', 0);
            }

            if (!empty($package['end_date'])) {
                $packages[$key]['end_time'] = strtotime($package['end_date']);
                $packages[$key]['end_date'] = EmundusHelperDate::displayDate($package['end_date'], 'd/m/Y H\hi', 0);

            }
        }

        return $packages;
    }

    public function getPackageIdOfFile($user, $ccid) {
        $package_id = 0;

        if (!empty($user) && !empty($ccid)) {
            $hierarchy_id = $this->getUserHierarchy($user);
            $hierarchy = $this->getHierarchyData($hierarchy_id);

            if ($hierarchy['package_by'] === 'jos_emundus_setup_campaigns.id') {
                $query = $this->db->getQuery(true);
                $query->select('esc.id')
                    ->from($this->db->quoteName('#__emundus_setup_campaigns', 'esc'))
                    ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature', 'cc') . ' ON ' . $this->db->quoteName('esc.id') . ' = ' . $this->db->quoteName('cc.campaign_id'))
                    ->where($this->db->quoteName('cc.id') . ' = ' . $this->db->quote($ccid))
                    ->andWhere($this->db->quoteName('esc.published') . ' = 1');

                try {
                    $this->db->setQuery($query);
                    $package_id = $this->db->loadResult();
                } catch (Exception $e) {
                    JLog::add('getPackageIdOfFile ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                    $package_id = 0;
                }
            }
        }

        return $package_id;
    }

    public function getFilesUserCanRankByPackage($user_id, $page = 1, $limit = 10, $sort = 'ASC', $hierarchy_order_by = 'default') {
        $files_by_package = [];
        $packages = $this->getUserPackages($user_id);

        if (empty($packages)) {
            $files_by_package[] = [
                'package' => [
                    'id' => 0,
                    'label' => JText::_('COM_EMUNDUS_RANKING_ALL'),
                    'start_date' => null,
                    'end_date' => null
                ],
                'files' => $this->getFilesUserCanRank($user_id, $page, $limit, $sort, $hierarchy_order_by)
            ];
        } else {
            foreach ($packages as $package) {
                $files_package = $this->getFilesUserCanRank($user_id, $page, $limit, $sort, $hierarchy_order_by, $package['id']);
                if ((!empty($package['start_time']) && $package['start_time'] > time()) || (!empty($package['end_time']) && $package['end_date'] < time())) {
                    foreach ($files_package['data'] as $key => $file) {
                        $files_package['data'][$key]['locked'] = 1;
                    }
                }

                $files_by_package[] = [
                    'package' => $package,
                    'files' => $files_package
                ];
            }
        }

        return $files_by_package;
    }

    /**
     * @param $user_id
     * @param $page
     * @param $limit
     * @return array|mixed
     */
    public function getFilesUserCanRank($user_id, $page = 1, $limit = 10, $sort = 'ASC', $hierarchy_order_by = 'default', $package_id = null)
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
            $ids = $this->getAllFilesRankerCanAccessTo($user_id, $hierarchy, $package_id);
            if (!empty($ids)) {
                $MAX_RANK_VALUE = 999999;

                $query = $this->db->getQuery(true);
                $offset = ($page - 1) * $limit;
                $files['total'] = count($ids);
                $query->clear()
                    ->select('MAX(' . $this->db->quoteName('rank') . ')')
                    ->from($this->db->quoteName('#__emundus_ranking'))
                    ->where($this->db->quoteName('ccid') . ' IN (' . implode(',', $ids) . ')')
                    ->andWhere($this->db->quoteName('hierarchy_id') . ' = ' . $this->db->quote($hierarchy));

                if (!empty($package_id)) {
                    $query->andWhere($this->db->quoteName('package') . ' = ' . $this->db->quote($package_id));
                }

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

                $query->clear()
                    ->select('er.id as rank_id, CONCAT(applicant.firstname, " ", applicant.lastname) AS applicant, cc.id, cc.fnum, er.rank, er.locked, cc.status')
                    ->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'))
                    ->leftJoin($this->db->quoteName('#__emundus_users', 'applicant') . ' ON ' . $this->db->quoteName('cc.applicant_id') . ' = ' . $this->db->quoteName('applicant.user_id'));

                // if the user has a hierarchy order by, we need to get the rank of the files in that hierarchy
                if (!empty($hierarchy_order_by) && $hierarchy_order_by !== 'default' && $hierarchy_order_by != $hierarchy) {
                    $leftJoin = $this->db->quoteName('#__emundus_ranking', 'er') . ' ON ' . $this->db->quoteName('cc.id') . ' = ' . $this->db->quoteName('er.ccid') . ' AND er.hierarchy_id  = ' . $hierarchy;

                    if (!empty($package_id)) {
                        $leftJoin .= ' AND ' . $this->db->quoteName('er.package') . ' = ' . $this->db->quote($package_id);
                    }

                    $query->leftJoin($leftJoin);
                    $sub_query = $this->db->getQuery(true);
                    $sub_query->clear()
                        ->select('DISTINCT cc.id as ccid, IF(er.hierarchy_id = ' . $this->db->quote($hierarchy_order_by) . ', er.rank, -1) as `rank`')
                        ->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'))
                        ->leftJoin($this->db->quoteName('#__emundus_ranking', 'er') . ' ON ' . $this->db->quoteName('cc.id') . ' = ' . $this->db->quoteName('er.ccid'))
                        ->where($this->db->quoteName('cc.id') . ' IN (' . implode(',', $ids) . ')')
                        ->andWhere('(' . $this->db->quoteName('er.hierarchy_id') . ' = ' . $this->db->quote($hierarchy_order_by) . ' 
                          '. ( !empty($package_id) ?  ' AND ' . $this->db->quoteName('er.package') . ' = ' . $this->db->quote($package_id)  : ' ')
                          .') OR ' . $this->db->quoteName('cc.id') . ' NOT IN (
                            SELECT cc.id
                            FROM `jos_emundus_campaign_candidature` AS `cc`
                            LEFT JOIN `jos_emundus_ranking` AS `er` ON `cc`.`id` = `er`.`ccid`
                            WHERE `cc`.`id` IN (' . implode(',', $ids) . ')
                            AND `er`.`hierarchy_id` = ' . $this->db->quote($hierarchy_order_by)
                            . ( !empty($package_id) ?  ' AND `er`.`package` = ' . $this->db->quote($package_id)  : ' ' )
                            . ')'
                        );

                    if ($limit !== -1) {
                        $sub_query->setLimit($limit, $offset);
                    }

                    if ($sort === 'ASC') {
                        $sub_query->order('IFNULL(IF(`er`.`hierarchy_id` = ' . $this->db->quote($hierarchy_order_by) . ' AND `rank` != -1, `rank`, null), ' . $MAX_RANK_VALUE . ') ASC');
                    } else {
                        $sub_query->order('IFNULL(IF(`er`.`hierarchy_id` = ' . $this->db->quote($hierarchy_order_by) . ', `rank`, -1), -1) DESC');
                    }

                    $this->db->setQuery($sub_query);
                    $ranks = $this->db->loadAssocList('ccid');

                    if (!empty($ranks)) {
                        $ids = array_keys($ranks);
                    }

                    $query->where($this->db->quoteName('cc.id') . ' IN (' . implode(',', $ids) . ')');

                    if ($sort == 'ASC') {
                        $query->order('IFNULL(IF(er.rank > 0, er.rank, null), ' . $MAX_RANK_VALUE . ')' . $sort);
                    } else {
                        $query->order('IFNULL(er.rank, -1) ' . $sort);
                    }
                } else {
                    $leftJoin = $this->db->quoteName('#__emundus_ranking', 'er') . ' ON ' . $this->db->quoteName('cc.id') . ' = ' . $this->db->quoteName('er.ccid') . ' AND `er`.`hierarchy_id` = ' . $this->db->quote($hierarchy);

                    if (!empty($package_id)) {
                        $leftJoin .= ' AND ' . $this->db->quoteName('er.package') . ' = ' . $this->db->quote($package_id);
                    }

                    $query->leftJoin($leftJoin);
                    $query->where($this->db->quoteName('cc.id') . ' IN (' . implode(',', $ids) . ')');

                    if ($limit !== -1) {
                        $query->setLimit($limit, $offset);
                    }

                    if ($sort === 'ASC') {
                        $query->order('IFNULL(IF(`rank` != -1, `rank`, null), ' . $MAX_RANK_VALUE . ') ASC');
                    } else {
                        $query->order('IFNULL(`rank`, -1) DESC');
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

                if (!empty($package_id)) {
                    $packages_data = $this->getUserPackages($user_id, $package_id);

                    if (!empty($packages_data)) {
                        // check package dates and lock files if necessary
                        if ((!empty($packages_data[0]['start_time']) && $packages_data[0]['start_time'] > time()) || (!empty($packages_data[0]['end_time']) && $packages_data[0]['end_time'] < time())) {
                            foreach ($files['data'] as $key => $file) {
                                $files['data'][$key]['locked'] = 1;
                            }
                        }
                    }
                }
            }
        }

        $this->dispatchEvent('onGetFilesUserCanRank', ['files' => &$files, 'user_id' => $user_id, 'page' => $page, 'limit' => $limit, 'sort' => $sort, 'hierarchy_order_by' => $hierarchy_order_by, 'package_id' => $package_id]);

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

    public function getHierarchyData($hierarchy_id) {
        $hierarchy = [];

        if (!empty($hierarchy_id)) {
            $query = $this->db->getQuery(true);

            $query->clear()
                ->select('erh.*, GROUP_CONCAT(erhp.profile_id) as profiles')
                ->from($this->db->quoteName('#__emundus_ranking_hierarchy', 'erh'))
                ->leftJoin($this->db->quoteName('#__emundus_ranking_hierarchy_profiles', 'erhp') . ' ON ' . $this->db->quoteName('erh.id') . ' = ' . $this->db->quoteName('erhp.hierarchy_id'))
                ->where($this->db->quoteName('erh.id') . ' = ' . $this->db->quote($hierarchy_id))
                ->group($this->db->quoteName('erh.id'));

            try {
                $this->db->setQuery($query);
                $hierarchy = $this->db->loadAssoc();
                $hierarchy['profiles'] = explode(',', $hierarchy['profiles']);
            } catch (Exception $e) {
                JLog::add('getUserHierarchyData ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
            }
        }

        return $hierarchy;
    }

    /*
     * Get the hierarchy of a user
     * @param $user_id
     * @param $search_current_profile, if true, it will search the hierarchy of the current profile in order to handle multiprofile users
     * @return int
     */
    public function getUserHierarchy($user_id, $search_current_profile = true)
    {
        $hierarchy = 0;

        if (!empty($user_id)) {
            $query = $this->db->getQuery(true);

            if ($search_current_profile) {
                $emundus_user = Factory::getSession()->get('emundusUser');
                $profile_id = $emundus_user->profile;

                $query->clear()
                    ->select('erh.id')
                    ->from($this->db->quoteName('#__emundus_ranking_hierarchy', 'erh'))
                    ->leftJoin($this->db->quoteName('#__emundus_ranking_hierarchy_profiles', 'erhp') . ' ON ' . $this->db->quoteName('erh.id') . ' = ' . $this->db->quoteName('erhp.hierarchy_id'))
                    ->where($this->db->quoteName('erhp.profile_id') . ' = ' . $this->db->quote($profile_id));

                $this->db->setQuery($query);
                $hierarchy = $this->db->loadResult();
            }

            if (empty($hierarchy)) {
                $query->clear()
                    ->select('erh.id')
                    ->from($this->db->quoteName('#__emundus_ranking_hierarchy', 'erh'))
                    ->leftJoin($this->db->quoteName('#__emundus_ranking_hierarchy_profiles', 'erhp') . ' ON ' . $this->db->quoteName('erh.id') . ' = ' . $this->db->quoteName('erhp.hierarchy_id'))
                    ->leftJoin($this->db->quoteName('#__emundus_users', 'eu') . ' ON ' . $this->db->quoteName('eu.profile') . ' = ' . $this->db->quoteName('erhp.profile_id'))
                    ->where($this->db->quoteName('eu.user_id') . ' = ' . $this->db->quote($user_id));

                try {
                    $this->db->setQuery($query);
                    $hierarchy = $this->db->loadResult();
                } catch (Exception $e) {
                    JLog::add('getUserHierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
                }
            }
        }

        return $hierarchy;
    }

    private function getStatusHierarchyCanSee($hierarchy_id) {
        $status = [];

        if (!empty($hierarchy_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('status')
                ->from('#__emundus_ranking_hierarchy_visible_status')
                ->where('hierarchy_id = ' . $db->quote($hierarchy_id));

            try {
                $db->setQuery($query);
                $status = $db->loadColumn();
            } catch (Exception $e) {
                JLog::add('failed to get visible status for hierarchy ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
            }
        }

        return $status;
    }

    public function getAllFilesRankerCanAccessTo($user_id, $hierarchy = null, $package = null)
    {
        $file_ids = [];

        if (!empty($user_id)) {
            if (empty($hierarchy)) {
                $hierarchy = $this->getUserHierarchy($user_id);
            }

            $visible_status = [];
            if (!empty($hierarchy)) {
                $visible_status = $this->getStatusHierarchyCanSee($hierarchy);
            }

            $query = $this->db->getQuery(true);
            $query->select('DISTINCT cc.id')
                ->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'))
                ->leftJoin($this->db->quoteName('#__emundus_users_assoc', 'eua') . ' ON ' . $this->db->quoteName('cc.fnum') . ' = ' . $this->db->quoteName('eua.fnum'))
                ->where($this->db->quoteName('eua.user_id') . ' = ' . $this->db->quote($user_id))
                ->andWhere($this->db->quoteName('eua.action_id') . ' = 1')
                ->andWhere($this->db->quoteName('eua.r') . ' = 1')
                ->andWhere($this->db->quoteName('cc.published') . ' = 1');

            if (!$this->can_user_rank_himself) {
                $query->andWhere($this->db->quoteName('cc.applicant_id') . ' != ' . $this->db->quote($user_id));
            }


            if (!empty($package)) {
                $data = $this->getHierarchyData($hierarchy);

                if ($data['package_by'] == 'jos_emundus_setup_campaigns.id') {
                    $query->andWhere($this->db->quoteName('cc.campaign_id') . ' = ' . $this->db->quote($package));
                }
            }

            if (!empty($visible_status)) {
                $query->andWhere($this->db->quoteName('cc.status') . ' IN (' . implode(',', $visible_status) . ')');
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
                    ->andWhere($this->db->quoteName('ega.action_id') . ' = 1')
                    ->andWhere($this->db->quoteName('ega.r') . ' = 1')
                    ->andWhere($this->db->quoteName('cc.published') . ' = 1');

                if (!$this->can_user_rank_himself) {
                    $query->andWhere($this->db->quoteName('cc.applicant_id') . ' != ' . $this->db->quote($user_id));
                }

                if (!empty($package)) {
                    $data = $this->getHierarchyData($hierarchy);

                    if ($data['package_by'] == 'jos_emundus_setup_campaigns.id') {
                        $query->andWhere($this->db->quoteName('cc.campaign_id') . ' = ' . $this->db->quote($package));
                    }
                }

                if (!empty($visible_status)) {
                    $query->andWhere($this->db->quoteName('cc.status') . ' IN (' . implode(',', $visible_status) . ')');
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
                    ->andWhere($this->db->quoteName('cc.published') . ' = 1');

                if (!$this->can_user_rank_himself) {
                    $query->andWhere($this->db->quoteName('cc.applicant_id') . ' != ' . $this->db->quote($user_id));
                }

                if (!empty($package)) {
                    $data = $this->getHierarchyData($hierarchy);

                    if ($data['package_by'] == 'jos_emundus_setup_campaigns.id') {
                        $query->andWhere($this->db->quoteName('cc.campaign_id') . ' = ' . $this->db->quote($package));
                    }
                }

                if (!empty($visible_status)) {
                    $query->andWhere($this->db->quoteName('cc.status') . ' IN (' . implode(',', $visible_status) . ')');
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
    public function getOtherRankingsRankerCanSee($user_id, $limit_hierarchy_ids = null, $package = null)
    {
        $rankings = [];

        if (!empty($user_id)) {
            $hierarchies = $this->getHierarchiesUserCanSee($user_id);

            if (isset($limit_hierarchy_ids)) {
                $hierarchies = array_filter($hierarchies, function ($hierarchy) use ($limit_hierarchy_ids) {
                    return in_array($hierarchy['id'], $limit_hierarchy_ids);
                });
            }


            $ids = $this->getAllFilesRankerCanAccessTo($user_id, null, $package);

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
     * @param $ids array of hierarchy ids, if empty, it will return all the hierarchies
     */
    public function getHierarchies($ids = []) {
        $hierarchies = [];

        $query = $this->db->getQuery(true);
        $query->clear()
            ->select('erh.*, GROUP_CONCAT(DISTINCT erhp.profile_id) as profiles, GROUP_CONCAT(DISTINCT erhv.visible_hierarchy_id) as visible_hierarchy_ids, GROUP_CONCAT(DISTINCT erhvs.status) as visible_status')
            ->from($this->db->quoteName('#__emundus_ranking_hierarchy', 'erh'))
            ->leftJoin($this->db->quoteName('#__emundus_ranking_hierarchy_profiles', 'erhp') . ' ON ' . $this->db->quoteName('erh.id') . ' = ' . $this->db->quoteName('erhp.hierarchy_id'))
            ->leftJoin($this->db->quoteName('#__emundus_ranking_hierarchy_view', 'erhv') . ' ON ' . $this->db->quoteName('erh.id') . ' = ' . $this->db->quoteName('erhv.hierarchy_id'))
            ->leftJoin($this->db->quoteName('#__emundus_ranking_hierarchy_visible_status', 'erhvs') . ' ON ' . $this->db->quoteName('erh.id') . ' = ' . $this->db->quoteName('erhvs.hierarchy_id'));

        if (!empty($ids)) {
            $query->where($this->db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
        }

        $query->group($this->db->quoteName('erh.id'));
        try {
            $this->db->setQuery($query);
            $hierarchies = $this->db->loadAssocList();
        } catch (Exception $e) {
            JLog::add('getHierarchies ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
        }

        foreach($hierarchies as $key => $hierarchy) {
            $hierarchies[$key]['profiles'] = !empty($hierarchy['profiles']) ? explode(',', $hierarchy['profiles']) : [];
            $hierarchies[$key]['visible_hierarchy_ids'] =  !empty($hierarchy['visible_hierarchy_ids']) ? explode(',', $hierarchy['visible_hierarchy_ids']) : [];
            $hierarchies[$key]['visible_status'] = !empty($hierarchy['visible_status']) ?  explode(',', $hierarchy['visible_status']) : [];
        }

        return $hierarchies;
    }

    public function deleteHierarchy($id) 
    {
        $deleted = false;

        if (!empty($id)) {
            $query = $this->db->getQuery(true);
            $query->delete('#__emundus_ranking_hierarchy')
                ->where('id = ' . $id);

            try {
                $this->db->setQuery($query);
                $deleted = $this->db->execute();
            } catch(Exception $e) {
                JLog::add('Delete ranking hierarchy ' . $id . ' failed ' . $e->getMessage(), JLog::ERROR, 'com_emundus.ranking.php');
            }
        }

        return $deleted;
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
    public function updateFileRanking($id, $user_id, $new_rank, $hierarchy_id, $package_id = 0)
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

            if ($applicant_id == $user_id && !$this->can_user_rank_himself) {
                throw new Exception(Text::_('COM_EMUNDUS_RANKING_UPDATE_RANKING_ERROR_RANK_OWN_FILE'));
            }

            $all_mighty_user = JFactory::getSession()->get('emundusUser')->profile == $this->all_rights_profile;

            $this->dispatchEvent('onBeforeUpdateFileRanking', ['id' => $id, 'user_id' => $user_id, 'new_rank' => $new_rank, 'hierarchy_id' => $hierarchy_id, 'package_id' => $package_id]);

            $query->clear()
                ->select($this->db->quoteName('cc.status'))
                ->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'))
                ->where($this->db->quoteName('cc.id') . ' = ' . $this->db->quote($id));
            $file_status = $this->db->setQuery($query)->loadResult();
            $status_user_can_rank = $this->getStatusUserCanRank($user_id, $hierarchy_id);

            if ($file_status == $status_user_can_rank || $all_mighty_user) {
                $query->clear()
                    ->select($this->db->quoteName('id') . ', ' . $this->db->quoteName('rank') . ', ' . $this->db->quoteName('locked'))
                    ->from($this->db->quoteName('#__emundus_ranking'))
                    ->where($this->db->quoteName('ccid') . ' = ' . $this->db->quote($id))
                    ->andWhere($this->db->quoteName('hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id));

                if (!empty($package_id)) {
                    $query->andWhere($this->db->quoteName('package') . ' = ' . $this->db->quote($package_id));
                }

                $this->db->setQuery($query);
                $ranking = $this->db->loadAssoc();

                if (!empty($ranking) && $ranking['locked'] == 1 && !$all_mighty_user) {
                    throw new Exception(Text::_('COM_EMUNDUS_RANKING_UPDATE_RANKING_ERROR_LOCKED'));
                }

                $old_rank = !empty($ranking) && !empty($ranking['rank']) && $ranking['rank'] > 0 ? $ranking['rank'] : -1;

                if ($old_rank == $new_rank) {
                    $updated = true;
                } else {
                    // different people can rank same files, so we dont get them by user but by their accessibility, hierarchy and package
                    $ids_user_can_rank = $this->getAllFilesRankerCanAccessTo($user_id, $hierarchy_id, $package_id);

                    // if the new rank is -1, we need to decrease all ranks above the old rank by 1, unless they are locked
                    if ($new_rank != -1) {
                        // does the rank i want to reach already taken by another file and locked ?
                        $query->clear()
                            ->select($this->db->quoteName('er.ccid') . ', ' . $this->db->quoteName('er.locked') . ', ' . $this->db->quoteName('cc.status'))
                            ->from($this->db->quoteName('#__emundus_ranking', 'er'))
                            ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature', 'cc') . ' ON ' . $this->db->quoteName('cc.id') . ' = ' . $this->db->quoteName('er.ccid'))
                            ->where($this->db->quoteName('er.rank') . ' = ' . $this->db->quote($new_rank))
                            ->andWhere($this->db->quoteName('er.hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id))
                            ->andWhere($this->db->quoteName('er.ccid') . ' IN (' . implode(',', $ids_user_can_rank) . ')');

                        if (!empty($package_id)) {
                            $query->andWhere($this->db->quoteName('package') . ' = ' . $this->db->quote($package_id));
                        }

                        $this->db->setQuery($query);
                        $same_rank_data = $this->db->loadAssoc();

                        if (!empty($same_rank_data) && ($same_rank_data['locked'] == 1 || $same_rank_data['status'] != $status_user_can_rank) && !$all_mighty_user) {
                            throw new Exception(Text::_('COM_EMUNDUS_RANKING_UPDATE_RANKING_ERROR_RANK_UNREACHABLE'));
                        }

                        if (!empty($ranking) && !empty($ranking['id'])) {
                            $max_rank = $this->getMaxRankAvailable($hierarchy_id, $user_id, $ranking['id'], $package_id);
                        } else {
                            $max_rank = $this->getMaxRankAvailable($hierarchy_id, $user_id, null, $package_id);
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
                            ->where($this->db->quoteName('er.ccid') . ' IN (' . implode(',', $ids_user_can_rank) . ')')
                            ->andWhere($this->db->quoteName('er.hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id))
                            ->andWhere($this->db->quoteName('er.rank') . ' > ' . $this->db->quote($ranking['rank']))
                            ->order($this->db->quoteName('er.rank') . ' ASC');

                        if (!empty($package_id)) {
                            $query->andWhere($this->db->quoteName('package') . ' = ' . $this->db->quote($package_id));
                        }

                        $this->db->setQuery($query);
                        $ranks = $this->db->loadAssocList();
                        $rank_to_apply = (int)$old_rank;

                        $locked_rank_positions = $all_mighty_user ? [] : array_filter(array_map(function ($rank) use ($status_user_can_rank) {
                            return $rank['locked'] == 1 || $rank['status'] != $status_user_can_rank ? $rank['rank'] : null;
                        }, $ranks));

                        foreach ($ranks as $rank) {
                            if (($rank['locked'] != 1 && $rank['status'] == $status_user_can_rank) || $all_mighty_user) {
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
                            ->where($this->db->quoteName('er.ccid') . ' IN (' . implode(',', $ids_user_can_rank) . ')')
                            ->andWhere($this->db->quoteName('er.hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id))
                            ->andWhere($this->db->quoteName('er.rank') . ' >= ' . $this->db->quote($new_rank))
                            ->order($this->db->quoteName('er.rank') . ' ASC');

                        if (!empty($package_id)) {
                            $query->andWhere($this->db->quoteName('package') . ' = ' . $this->db->quote($package_id));
                        }

                        $this->db->setQuery($query);
                        $ranks = $this->db->loadAssocList();
                        $rank_to_apply = $new_rank + 1;

                        $locked_rank_positions = $all_mighty_user ? [] : array_filter(array_map(function ($rank) use ($status_user_can_rank) {
                            return $rank['locked'] == 1 || $rank['status'] != $status_user_can_rank ? $rank['rank'] : null;
                        }, $ranks));

                        while (in_array($rank_to_apply, $locked_rank_positions)) {
                            $rank_to_apply++;
                        }

                        foreach ($ranks as $rank) {
                            if (($rank['locked'] != 1 && $rank['status'] == $status_user_can_rank) || $all_mighty_user) {
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
                            ->where($this->db->quoteName('er.ccid') . ' IN (' . implode(',', $ids_user_can_rank) . ')')
                            ->andWhere($this->db->quoteName('er.hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id))
                            ->andWhere($this->db->quoteName('er.rank') . ' >= ' . $this->db->quote($new_rank))
                            ->andWhere($this->db->quoteName('er.rank') . ' < ' . $this->db->quote($old_rank))
                            ->order($this->db->quoteName('er.rank') . ' DESC');

                        if (!empty($package_id)) {
                            $query->andWhere($this->db->quoteName('package') . ' = ' . $this->db->quote($package_id));
                        }

                        $this->db->setQuery($query);
                        $ranks = $this->db->loadAssocList();
                        $rank_to_apply = (int)$old_rank;

                        $locked_rank_positions = $all_mighty_user ? [] :  array_filter(array_map(function ($rank) use ($status_user_can_rank) {
                            return $rank['locked'] == 1 || $rank['status'] != $status_user_can_rank ? $rank['rank'] : null;
                        }, $ranks));

                        foreach ($ranks as $rank) {
                            if (($rank['locked'] != 1 && $rank['status'] == $status_user_can_rank) || $all_mighty_user) {
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
                            ->where($this->db->quoteName('er.ccid') . ' IN (' . implode(',', $ids_user_can_rank) . ')')
                            ->andWhere($this->db->quoteName('er.hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id))
                            ->andWhere($this->db->quoteName('er.rank') . ' > ' . $this->db->quote($old_rank))
                            ->andWhere($this->db->quoteName('er.rank') . ' <= ' . $this->db->quote($new_rank))
                            ->order($this->db->quoteName('er.rank') . ' ASC');

                        if (!empty($package_id)) {
                            $query->andWhere($this->db->quoteName('package') . ' = ' . $this->db->quote($package_id));
                        }

                        $this->db->setQuery($query);
                        $ranks = $this->db->loadAssocList();
                        $rank_to_apply = (int)$old_rank;

                        $locked_rank_positions = $all_mighty_user ? [] : array_filter(array_map(function ($rank) use ($status_user_can_rank) {
                            return $rank['locked'] == 1 || $rank['status'] != $status_user_can_rank ? $rank['rank'] : null;
                        }, $ranks));

                        foreach ($ranks as $rank) {
                            if (($rank['locked'] != 1 && $rank['status'] == $status_user_can_rank) || $all_mighty_user) {
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
                            ->set($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user_id))
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
                    $columns = ['ccid', 'user_id', 'rank', 'hierarchy_id'];
                    $values = [$id, $user_id, $new_rank, $hierarchy_id];

                    if (!empty($package_id)) {
                        $columns[] = 'package';
                        $values[] = $package_id;
                    }

                    $query->clear()
                        ->insert($this->db->quoteName('#__emundus_ranking'))
                        ->columns($this->db->quoteName($columns))
                        ->values(implode(',', $values));

                    $this->db->setQuery($query);
                    $updated = $this->db->execute();
                }

                if ($updated) {
                    $fnum = EmundusHelperFiles::getFnumFromId($id);
                    $user_to = EmundusHelperFiles::getApplicantIdFromFileId($id);
                    $action_id = $this->logger->getActionId('ranking');
                    $this->logger->log($user_id, $user_to, $fnum, $action_id, 'u', 'COM_EMUNDUS_RANKING_UPDATE_RANKING', json_encode(['old_rank' => $old_rank, 'new_rank' => $new_rank]));
                    $this->dispatchEvent('onAfterUpdateFileRanking', ['id' => $id, 'user_id' => $user_id, 'new_rank' => $new_rank, 'old_rank' => $old_rank, 'hierarchy_id' => $hierarchy_id, 'package_id' => $package_id]);
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
     * @param null $rank_row_id if we want to exclude a rank row from the calculation
     * @param null $package_id if we want to filter by package
     * @return int
     */
    public function getMaxRankAvailable($hierarchy_id, $user_id, $rank_row_id = null, $package_id = null)
    {
        $max_value_reachable = 1;

        if (!empty($hierarchy_id) && !empty($user_id)) {
            $rankable_ids = $this->getAllFilesRankerCanAccessTo($user_id, $hierarchy_id, $package_id);

            $query = $this->db->getQuery(true);
            $query->clear()
                ->select('MAX(' . $this->db->quoteName('rank') . ') as max')
                ->from($this->db->quoteName('#__emundus_ranking'))
                ->where($this->db->quoteName('hierarchy_id') . ' = ' . $this->db->quote($hierarchy_id))
                ->andWhere($this->db->quoteName('ccid') . ' IN (' . implode(',', $rankable_ids) . ')');

            if (!empty($rank_row_id)) {
                $query->andWhere($this->db->quoteName('id') . ' != ' . $rank_row_id);
            }

            if (!empty($package_id)) {
                $query->andWhere($this->db->quoteName('package') . ' = ' . $this->db->quote($package_id));
            }

            $this->db->setQuery($query);
            $current_max_value = $this->db->loadResult();

            if ($current_max_value > 0) {
                $max_value_reachable = $current_max_value + 1;
            }

            if ($max_value_reachable > sizeof($rankable_ids)) {
                $max_value_reachable =  sizeof($rankable_ids);
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
                        ->select('erhp.profile_id')
                        ->from($this->db->quoteName('#__emundus_ranking_hierarchy', 'erh'))
                        ->leftJoin($this->db->quoteName('#__emundus_ranking_hierarchy_profiles', 'erhp') . ' ON ' . $this->db->quoteName('erh.id') . ' = ' . $this->db->quoteName('erhp.hierarchy_id'))
                        ->where($this->db->quoteName('erh.id') . ' = ' . $this->db->quote($hierarchy_infos['parent_id']));
                    $this->db->setQuery($query);
                    $profile_ids = $this->db->loadColumn();

                    $query->clear()
                        ->select('DISTINCT u.email')
                        ->from($this->db->quoteName('#__users', 'u'))
                        ->leftJoin($this->db->quoteName('#__emundus_users', 'eu') . ' ON ' . $this->db->quoteName('u.id') . ' = ' . $this->db->quoteName('eu.user_id'))
                        ->leftJoin($this->db->quoteName('#__emundus_users_profiles', 'eup') . ' ON ' . $this->db->quoteName('eup.user_id') . ' = ' . $this->db->quoteName('eu.user_id'))
                        ->where('(' . $this->db->quoteName('eu.profile') . ' IN (' . implode(',' , $profile_ids) .  ') OR ' . $this->db->quoteName('eup.profile_id') . ' IN (' . implode(',' , $profile_ids) .  '))')
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

    /**
     * Returns a link to a csv file containing the ranking of the files
     * @param $user_id, the user id
     * @param $package_ids, the package ids to export
     * @param $hierachy_ids, the additional hieararchies we want to export
     * @param $columns, the additional columns we want to export
     * @return void
     */
    public function exportRanking($user_id, $package_ids, $hierachy_ids, $ordered_columns): string
    {
        $export_link = '';

        if (!empty($package_ids)) {
            $export_array = [];

            $files_by_package = [];
            foreach($package_ids as $package_id) {
                $files_by_package[$package_id] = $this->getFilesUserCanRank($user_id, 1, -1, 'ASC', 'default', $package_id)['data'];
            }

            if (!empty($files_by_package)) {
                $query = $this->db->getQuery(true);
                $query->select('name')
                    ->from('#__users')
                    ->where('id = ' . $user_id);

                $this->db->setQuery($query);
                $user_name = $this->db->loadResult();

                $user_packages = $this->getUserPackages($user_id);
                $fnums = [];
                $ccids = [];

                if (!class_exists('EmundusModelFiles')) {
                    require_once(JPATH_ROOT . '/components/com_emundus/models/files.php');
                }
                $m_files = new EmundusModelFiles();
                $states = $m_files->getAllStatus($user_id, 'step');

                $ordered_columns_keys = array_map(function($column) {
                    return $column['id'];
                }, $ordered_columns);

                foreach ($files_by_package as $package_id => $files) {
                    $package_label = '';
                    foreach ($user_packages as $user_package) {
                        if ($user_package['id'] == $package_id) {
                            $package_label = $user_package['label'];
                            break;
                        }
                    }

                    if (!empty($files)) {
                        $other_rankings_values = [];
                        if (!empty($hierachy_ids)) {
                            $other_rankings_values = $this->getOtherRankingsRankerCanSee($user_id, $hierachy_ids, $package_id);
                        }

                        foreach ($files as $index => $file) {
                            $file_data = [];
                            $fnums[$index] = $file['fnum'];
                            $ccids[$index] = $file['id'];

                            foreach($ordered_columns_keys as $column) {
                                switch($column) {
                                    case 'status':
                                        $file_data[] = $states[$file['status']]['value'];
                                        break;
                                    case 'package':
                                        $file_data[] = $package_label;
                                        break;
                                    case 'ranker':
                                        $file_data[] = $user_name;
                                        break;
                                    case 'rank':
                                        $file_data[] = empty($file[$column]) || $file[$column] == -1 ? Text::_('COM_EMUNDUS_RANKING_NOT_RANKED') : $file[$column];
                                        break;
                                    default:
                                        $file_data[] = $file[$column];
                                        break;
                                }
                            }

                            if (!empty($hierachy_ids)) {
                                foreach ($other_rankings_values as $other_ranking) {
                                    $other_ranking_index = array_search($file['id'], array_map(function($file) {
                                        return $file['id'];
                                    }, $other_ranking['files']));

                                    if ($other_ranking_index !== false) {
                                        $file_data[] = empty($other_ranking['files'][$other_ranking_index]['rank']) || $other_ranking['files'][$other_ranking_index]['rank'] == -1 ? Text::_('COM_EMUNDUS_RANKING_NOT_RANKED') : $other_ranking['files'][$other_ranking_index]['rank'];
                                        $file_data[] = $other_ranking['rankers'][$other_ranking['files'][$other_ranking_index]['ranker_id']]['name'];
                                    } else {
                                        $file_data[] = Text::_('COM_EMUNDUS_RANKING_NOT_RANKED');
                                        $file_data[] = '';
                                    }
                                }
                            }

                            $export_array[] = $file_data;
                        }
                    }
                }

                if (!empty($export_array)) {
                    $today  = date("MdYHis");
                    $name   = md5($today.rand(0,10));
                    $name   = 'classement-' . $name.'.csv';
                    $path = JPATH_SITE . '/tmp/' . $name;

                    if (!$csv_file = fopen($path, 'w+')) {
                        throw new Exception(Text::_('COM_EMUNDUS_EXPORTS_ERROR_CANNOT_CREATE_CSV_FILE'));
                    } else {
                        fprintf($csv_file, chr(0xEF).chr(0xBB).chr(0xBF));

                        $header = array_map(function($column) {
                            return Text::_($column['label']);
                        }, $ordered_columns);

                        foreach($hierachy_ids as $hierachy_id) {
                            $hierarchy_label = $this->getHierarchyData($hierachy_id)['label'];

                            $header[] = Text::_('COM_EMUNDUS_RANKING_EXPORT_RANKING') . ' ' . $hierarchy_label;
                            $header[] = Text::_('COM_EMUNDUS_RANKING_EXPORT_STATUS') . ' - ' . $hierarchy_label;
                        }

                        $this->dispatchEvent('onBeforeExportRanking', ['header' => &$header, 'lines' => &$export_array, 'fnums' => $fnums, 'ccids' => $ccids]);

                        fputcsv($csv_file, $header, ';');
                        foreach ($export_array as $line) {
                            fputcsv($csv_file, $line, ';');
                        }
                        fclose($csv_file);
                        $export_link = JUri::root() . 'tmp/' . $name;
                    }
                }
            }
        }

        return $export_link;
    }
}
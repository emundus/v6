<?php

namespace classes\files;

use JFactory;
use JLog;
use Throwable;

class Files
{
    protected \Joomla\CMS\User\User $current_user;
    protected array $rights;
    protected array $files = [];
    protected array $fnums = [];
    protected array $columns = [];
    protected array $filters = ['default_filters' => [], 'applied_filters' => []];
    protected array $campaigns = [];
    protected int $page = 0;
    protected int $limit = 10;
    protected int $total = 0;

    public function __construct()
    {
        JLog::addLogger(['text_file' => 'com_emundus.evaluations.php'], JLog::ERROR, 'com_emundus.evaluations');

        $this->current_user = JFactory::getUser();
        $this->files = [];
    }

    /**
     * @return \Joomla\CMS\User\User
     */
    public function getCurrentUser(): \Joomla\CMS\User\User
    {
        return $this->current_user;
    }

    /**
     * @param \Joomla\CMS\User\User $current_user
     */
    public function setCurrentUser(\Joomla\CMS\User\User $current_user): void
    {
        $this->current_user = $current_user;
    }

    /**
     * @return array
     */
    public function getRights(): array
    {
        return $this->rights;
    }

    /**
     * @param array $rights
     */
    public function setRights(array $rights): void
    {
        $this->rights = $rights;
    }

    public function setFiles(): void
    {
        $this->files = [];
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function setFnums(array $fnums): void
    {
        $this->fnums = $fnums;
    }

    /**
     * @return array
     */
    public function getFnums(): array
    {
        return $this->fnums;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param array $columns
     */
    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
        $this->setFilters();
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param array $filters
     */
    public function setFilters(): void
    {
        $Itemid = JFactory::getSession()->get('current_menu_id', 0);
        $menu = JFactory::getApplication()->getMenu();
        $params = $menu->getParams($Itemid)->get('params');

        if ($params->display_filters == 1) {
            if ($params->display_filter_fnum) {
                $fnums = $this->getFnums();

                if (!empty($fnums)) {
                    $values = array_map(function ($fnum) {
                        return ['value' => $fnum, 'label' => $fnum];
                    }, $fnums);
                    $this->filters['default_filters']['fnum'] = [
                        'id' => 'fnum',
                        'type' => 'select',
                        'label' => \JText::_('FNUM'),
                        'values' => $values,
                        'operators' => [
                            ['label' => \JText::_('COM_EMUNDUS_FILES_LIKE'), 'value' => 'IN'],
                            ['label' => \JText::_('COM_EMUNDUS_FILES_NOT_LIKE'), 'value' => 'NOT IN'],
                        ]
                    ];
                }
            }

            if ($params->display_filter_campaigns) {
                $campaigns = $this->getCampaigns();

                if (!empty($campaigns)) {
                    $this->filters['default_filters']['campaign_id'] = [
                        'id' => 'campaign_id',
                        'type' => 'select',
                        'label' => \JText::_('CAMPAIGN'),
                        'values' => $campaigns,
                        'operators' => [
                            ['label' => \JText::_('COM_EMUNDUS_FILES_LIKE'), 'value' => 'IN'],
                            ['label' => \JText::_('COM_EMUNDUS_FILES_NOT_LIKE'), 'value' => 'NOT IN'],
                        ]
                    ];
                }
            }

            if ($params->display_filter_steps) {
                $status = [];
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->select('step as value, value as label')
                    ->from($db->quoteName('#__emundus_setup_status'))
                    ->order('ordering ASC');

                try {
                    $db->setQuery($query);
                    $status = $db->loadAssocList();
                } catch (Exception $e) {
                    JLog::add('Failed to get campaigns for filters ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
                }

                if (!empty($status)) {
                    $this->filters['default_filters']['status'] = [
                        'id' => 'status',
                        'type' => 'select',
                        'label' => \JText::_('STATUS'),
                        'values' => $status,
                        'operators' => [
                            ['label' => \JText::_('COM_EMUNDUS_FILES_LIKE'), 'value' => 'IN'],
                            ['label' => \JText::_('COM_EMUNDUS_FILES_NOT_LIKE'), 'value' => 'NOT IN'],
                        ]
                    ];
                }
            }

            $columns = $this->getColumns();
            if (!empty($columns)) {
                foreach ($columns as $column) {
                    $operators = [['label' => \JText::_('EQUALS'), 'value' => '=']];

                    if (!empty($column->id) && $column->show_in_list_summary == 1 && !array_key_exists($column->id, $this->filters['default_filters'])) {
                        $type = $this->getFilterTypeFromFabrikElementPlugin($column->plugin);
                        if (!empty($type)) {
                            $values = $this->getValuesFromFabrikElement($column->id, $column->plugin, $type);
                            if ($type == 'date') {
                                $operators = [
                                    ['label' => \JText::_('COM_EMUNDUS_FILES_EQUALS'), 'value' => '='],
                                    ['label' => \JText::_('COM_EMUNDUS_FILES_INFERIOR'), 'value' => '<'],
                                    ['label' => \JText::_('COM_EMUNDUS_FILES_INFERIOR_OR_EQUAL'), 'value' => '<='],
                                    ['label' => \JText::_('COM_EMUNDUS_FILES_GREATER_OR_EQUAL'), 'value' => '>='],
                                    ['label' => \JText::_('COM_EMUNDUS_FILES_GREATER_THAN'), 'value' => '>']
                                ];
                            } else if ($type == 'field') {
                                $operators = [
                                    ['label' => \JText::_('COM_EMUNDUS_FILES_LIKE'), 'value' => 'LIKE'],
                                    ['label' => \JText::_('COM_EMUNDUS_FILES_NOT_LIKE'), 'value' => 'NOT LIKE'],
                                    ['label' => \JText::_('COM_EMUNDUS_FILES_EQUALS'), 'value' => '='],
                                    ['label' => \JText::_('COM_EMUNDUS_FILES_NOT_EQUALS'), 'value' => '!=']
                                ];
                            } else if ($type == 'select') {
                                if ($column->plugin == 'checkbox') {
                                    $operators = [['label' => \JText::_('COM_EMUNDUS_FILES_LIKE'), 'value' => 'LIKE']];
                                } else {
                                    $operators = [
                                        ['label' => \JText::_('COM_EMUNDUS_FILES_LIKE'), 'value' => 'IN'],
                                        ['label' => \JText::_('COM_EMUNDUS_FILES_NOT_LIKE'), 'value' => 'NOT IN'],
                                    ];
                                }
                            }

                            $this->filters['default_filters'][$column->id] = [
                                'id' => $column->id,
                                'type' => $type,
                                'label' => $column->label,
                                'values' => $values,
                                'operators' => $operators,
                                'selectedOperator' => $operators[0]['value']
                            ];
                        }
                    }
                }
            }
        } else {
            $this->filters['default_filters'] = [];
            $this->filters['applied_filters'] = [];
        }
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    public function getDefaultFilters()
    {
        $this->setFilters();

        return $this->filters['default_filters'];
    }

    private function getCampaigns(): array
    {
        if (empty($this->campaigns)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('DISTINCT esc.id as value, esc.label')
                ->from($db->quoteName('#__emundus_setup_campaigns', 'esc'))
                ->join('inner', $db->quoteName('#__emundus_setup_groups_repeat_course', 'esgrc') . ' ON esgrc.course = esc.training')
                ->join('inner', $db->quoteName('#__emundus_groups', 'eg') . ' ON eg.group_id = esgrc.parent_id')
                ->where('eg.user_id = ' . JFactory::getUser()->id)
                ->andWhere('esc.published = 1');

            try {
                $db->setQuery($query);
                $this->campaigns = $db->loadAssocList();
            } catch (Exception $e) {
                JLog::add('Problem when getting my campaigns : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.evaluations');
            }
        }

        return $this->campaigns;
    }

    public function applyFilters($filters)
    {
        $this->filters['applied_filters'] = $filters;
    }

    /**
     * @param int $total
     */
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function checkAccess($fnum): bool
    {
        $can_access = false;

        if (empty($this->files['fnums'])) {
            require_once(JPATH_ROOT . '/components/com_emundus/helpers/access.php');
            $can_access = \EmundusHelperAccess::asAccessAction(1, 'r', $this->current_user->id, $fnum);
        }

        if (!empty($this->files['fnums']) && in_array($fnum, $this->files['fnums'])) {
            $can_access = true;
        }

        return $can_access;
    }

    public function getAccess($fnum): array
    {
        $actions = [
            1 => [
                'r' => \EmundusHelperAccess::asAccessAction(1, 'r', $this->current_user->id, $fnum)
            ],
            4 => [
                'r' => \EmundusHelperAccess::asAccessAction(4, 'r', $this->current_user->id, $fnum),
                'u' => \EmundusHelperAccess::asAccessAction(4, 'u', $this->current_user->id, $fnum),
                'c' => \EmundusHelperAccess::asAccessAction(4, 'c', $this->current_user->id, $fnum),
            ],
            10 => [
                'r' => \EmundusHelperAccess::asAccessAction(10, 'r', $this->current_user->id, $fnum),
                'c' => \EmundusHelperAccess::asAccessAction(10, 'c', $this->current_user->id, $fnum),
                'u' => \EmundusHelperAccess::asAccessAction(10, 'u', $this->current_user->id, $fnum),
                'd' => \EmundusHelperAccess::asAccessAction(10, 'd', $this->current_user->id, $fnum),
            ]
        ];

        return $actions;
    }

    public function getOffset(): int
    {
        if (!empty($this->getPage())) {
            return $this->getPage() * $this->getLimit();
        } else {
            return $this->getPage();
        }
    }

    public function buildSelect($params, $status_access = false): array
    {
        $select = ['DISTINCT ecc.fnum', 'ecc.applicant_id', 'ecc.campaign_id as campaign', 'u.name as applicant_name'];

        if ($status_access) {
            $select[] = 'ess.value as status,ess.class as status_color';
        }

	    if(isset($params->display_filter_campaigns) && $params->display_filter_campaigns == 1){
		    $select[] = 'CONCAT(esc.label, "(", esc.year, ")") as campaign_label';
	    }

        if (isset($params->display_group_assoc) && $params->display_group_assoc == 1) {
            $select[] = 'group_concat(distinct esgrc.parent_id) as programs_groups';
            $select[] = 'group_concat(distinct ega.group_id) as fnums_groups';
            $select[] = 'group_concat(distinct eua.user_id) as users_assoc';
        }

        if (isset($params->display_tag_assoc) && $params->display_tag_assoc == 1) {
            $select[] = 'group_concat(distinct eta.id_tag) as tags_assoc';
        }

        return $select;
    }

    public function buildLeftJoin($params, $status_access): array
    {
        $db = JFactory::getDbo();

        $left_joins = [
            $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('ecc.applicant_id') . ' = ' . $db->quoteName('u.id'),
            $db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('ecc.campaign_id') . ' = ' . $db->quoteName('esc.id')
        ];
        if ($status_access) {
            $left_joins[] = $db->quoteName('#__emundus_setup_status', 'ess') . ' ON ' . $db->quoteName('ess.step') . ' = ' . $db->quoteName('ecc.status');
        }
        if ((isset($params->tags) && $params->tags !== '') || (isset($params->display_tag_assoc) && $params->display_tag_assoc == 1)) {
            $left_joins[] = $db->quoteName('#__emundus_tag_assoc', 'eta') . ' ON ' . $db->quoteName('eta.fnum') . ' = ' . $db->quoteName('ecc.fnum');
        }
        if (isset($params->display_group_assoc) && $params->display_group_assoc == 1) {
            $left_joins[] = $db->quoteName('#__emundus_group_assoc', 'ega') . ' ON ' . $db->quoteName('ega.fnum') . ' = ' . $db->quoteName('ecc.fnum');
            $left_joins[] = $db->quoteName('#__emundus_users_assoc', 'eua') . ' ON ' . $db->quoteName('eua.fnum') . ' = ' . $db->quoteName('ecc.fnum');
            $left_joins[] = $db->quoteName('#__emundus_setup_groups_repeat_course', 'esgrc') . ' ON ' . $db->quoteName('esgrc.course') . ' LIKE ' . $db->quoteName('esc.training');
        }

        return $left_joins;
    }

    public function buildWhere($params): array
    {
        $db = JFactory::getDbo();

        $wheres = [
            $db->quoteName('esc.published') . ' = 1'
        ];
        if (isset($params->status) && $params->status !== '') {
            $wheres[] = $db->quoteName('ecc.status') . ' IN (' . implode(',', $params->status) . ')';
        }

        if (isset($params->tags) && $params->tags !== '') {
            $wheres[] = $db->quoteName('eta.id_tag') . ' IN (' . implode(',', $params->tags) . ')';
        }

        if (isset($params->campaign_to_exclude) && $params->campaign_to_exclude !== '') {
            $wheres[] = $db->quoteName('ecc.campaign_id') . ' NOT IN (' . $params->campaign_to_exclude . ')';
        }

        if (!empty($params->status_to_exclude)) {
            $wheres[] = $db->quoteName('ecc.status') . ' NOT IN (' . implode(',', $params->status_to_exclude) . ')';
        }

        if (!empty($params->tags_to_exclude)) {
            $exclude_query = $db->getQuery(true);

            $exclude_query->select('eta.fnum')
                ->from('jos_emundus_tag_assoc eta')
                ->where('eta.id_tag IN (' . implode(',', $params->tags_to_exclude) . ')');
            $db->setQuery($exclude_query);
            $fnums_to_exclude = $db->loadColumn();

            if (!empty($fnums_to_exclude)) {
                $wheres[] = 'ecc.fnum NOT IN (' . implode(',', $fnums_to_exclude) . ')';
            }
        }

        return $wheres;
    }

    public function buildQuery($select, $left_joins = [], $wheres = [], $access = '', $limit = 0, $offset = 0, $return = 'object', $params = null)
    {
        $em_session = JFactory::getSession()->get('emundusUser');
        $user = JFactory::getUser();

        $db = JFactory::getDbo();
        $query_groups_allowed = $db->getQuery(true);
        $query_users_associated = $db->getQuery(true);
        $query_groups_associated = $db->getQuery(true);
        $query_groups_program_associated = $db->getQuery(true);

        if (isset($params->tags) && $params->tags !== '') {
            $left_joins[] = $db->quoteName('#__emundus_tag_assoc', 'eta') . ' ON ' . $db->quoteName('eta.fnum') . ' = ' . $db->quoteName('ecc.fnum');
        }

        try {
            $groups_allowed = [];
            if (!empty($em_session->emGroups)) {
                $query_groups_allowed->select('acl.group_id')
                    ->from($db->quoteName('#__emundus_acl', 'acl'));
                if (!empty($access)) {
                    $query_groups_allowed->where($access);
                }
                $query_groups_allowed->andWhere($db->quoteName('acl.group_id') . ' IN (' . implode(',', $db->quote($em_session->emGroups)) . ')');
                $db->setQuery($query_groups_allowed);
                $groups_allowed = $db->loadColumn();
            }

            if (!empty($groups_allowed)) {
                $query_groups_program_associated->select(implode(',', $select))
                    ->from($db->quoteName('#__emundus_setup_groups', 'eg'))
                    ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'esgrc') . ' ON ' . $db->quoteName('esgrc.parent_id') . ' = ' . $db->quoteName('eg.id'))
                    ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('esc.training') . ' = ' . $db->quoteName('esgrc.course'))
                    ->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'ecc') . ' ON ' . $db->quoteName('esc.id') . ' = ' . $db->quoteName('ecc.campaign_id'));
                foreach ($left_joins as $left_join) {
                    $query_groups_program_associated->leftJoin($left_join);
                }
                $query_groups_program_associated->where($db->quoteName('eg.id') . ' IN (' . implode(',', $db->quote($groups_allowed)) . ')')
                    ->andWhere($db->quoteName('ecc.published') . ' = 1')
                    ->andWhere($db->quoteName('esc.published') . ' = 1');
                foreach ($wheres as $where) {
                    $query_groups_program_associated->andWhere($where);
                }
            }

            $query_users_associated->select(implode(',', $select))
                ->from($db->quoteName('#__emundus_users_assoc', 'eua'))
                ->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'ecc') . ' ON ' . $db->quoteName('eua.fnum') . ' = ' . $db->quoteName('ecc.fnum'))
                ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('esc.id') . ' = ' . $db->quoteName('ecc.campaign_id'));
            foreach ($left_joins as $left_join) {
                $query_users_associated->leftJoin($left_join);
            }
            $query_users_associated->where($db->quoteName('eua.user_id') . ' = ' . $db->quote($user->id));
            if (!empty($access)) {
                $query_users_associated->andWhere($access);
            }
            $query_users_associated->andWhere($db->quoteName('ecc.published') . ' = 1');
            $query_users_associated->andWhere($db->quoteName('esc.published') . ' = 1');
            foreach ($wheres as $where) {
                $query_users_associated->andWhere($where);
            }

            if (!empty($groups_allowed)) {
                $query_groups_associated->union($query_groups_program_associated);
            }
            $query_groups_associated->union($query_users_associated);
            $query_groups_associated->select(implode(',', $select))
                ->from($db->quoteName('#__emundus_groups', 'eg'))
                ->leftJoin($db->quoteName('#__emundus_group_assoc', 'ega') . ' ON ' . $db->quoteName('ega.group_id') . ' = ' . $db->quoteName('eg.group_id'))
                ->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'ecc') . ' ON ' . $db->quoteName('ega.fnum') . ' = ' . $db->quoteName('ecc.fnum'))
                ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('esc.id') . ' = ' . $db->quoteName('ecc.campaign_id'));
            foreach ($left_joins as $left_join) {
                $query_groups_associated->leftJoin($left_join);
            }
            $query_groups_associated->where($db->quoteName('eg.user_id') . ' = ' . $db->quote($user->id));
            if (!empty($access)) {
                $query_groups_associated->andWhere($access);
            }
            $query_groups_associated->andWhere($db->quoteName('ecc.published') . ' = 1');
            $query_groups_associated->andWhere($db->quoteName('esc.published') . ' = 1');
            foreach ($wheres as $where) {
                $query_groups_associated->andWhere($where);
            }

            $db->setQuery($query_groups_associated, $offset, $limit);
            if ($return == 'object') {
                return $db->loadObjectList();
            } elseif ($return == 'assoc') {
                return $db->loadAssocList();
            } elseif ($return == 'column') {
                return $db->loadColumn();
            } elseif ($return == 'single_object') {
                return $db->loadObject();
            }
        } catch (Exception $e) {
            JLog::add('Problem when build query with error : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.evaluations');
            return 0;
        }
    }

    public function getFilesQuery($select, $left_joins = [], $wheres = [], $access = '', $limit = 0, $offset = 0, $return = 'object')
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $this->addQueryFilters($left_joins, $wheres);

            $query->select(implode(',', $select))
                ->from($db->quoteName('#__emundus_campaign_candidature', 'ecc'));
            foreach ($left_joins as $left_join) {
                $query->leftJoin($left_join);
            }
            $query->where($db->quoteName('ecc.published') . ' = 1');
            foreach ($wheres as $where) {
                $query->andWhere($where);
            }

            $query->group($db->quoteName('ecc.fnum'));
            $db->setQuery($query, $offset, $limit);

            if ($return == 'object') {
                return $db->loadObjectList();
            } elseif ($return == 'assoc') {
                return $db->loadAssocList();
            } elseif ($return == 'column') {
                return $db->loadColumn();
            } elseif ($return == 'single_object') {
                return $db->loadObject();
            }
        } catch (Exception $e) {
            JLog::add('Problem when build query with error : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.evaluations');
            return 0;
        }
    }

    public function buildAssocGroups($files)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            foreach ($files as $file) {
                $groups = [];
                $users = [];

                if (!empty($file->programs_groups)) {
                    if (empty($file->programs_groups)) {
                        $file->programs_groups = [];
                    } else {
                        $file->programs_groups = explode(',', $file->programs_groups);
                    }
                    if (empty($file->fnums_groups)) {
                        $file->fnums_groups = [];
                    } else {
                        $file->fnums_groups = explode(',', $file->fnums_groups);
                    }

                    $groups = array_unique(array_merge($file->programs_groups, $file->fnums_groups));
                    $groups = array_filter($groups, function ($group) {
                        return !empty($group);
                    });

                    if (!empty($groups)) {
                        $query->clear()
                            ->select('label,class')
                            ->from($db->quoteName('#__emundus_setup_groups'))
                            ->where($db->quoteName('id') . ' IN (' . implode(',', $groups) . ')');
                        $db->setQuery($query);
                        $groups = $db->loadObjectList();
                    }
                }

                if (!empty($file->users_assoc)) {
                    $users = explode(',', $file->users_assoc);
                    $users = array_filter($users, function ($user) {
                        return !empty($user);
                    });

                    if (!empty($users)) {
                        $query->clear()
                            ->select('concat(lastname," ",firstname) as label,"label-default" as class')
                            ->from($db->quoteName('#__emundus_users'))
                            ->where($db->quoteName('user_id') . ' IN (' . implode(',', $users) . ')');
                        $db->setQuery($query);
                        $users = $db->loadObjectList();
                    }
                }

                $file->assocs = array_merge($groups, $users);
            }

            return $files;
        } catch (Exception $e) {
            JLog::add('Problem when build query with error : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.evaluations');
            return $files;
        }
    }

    public function buildAssocTags($files)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            foreach ($files as $file) {
                $tags = [];

                if (!empty($file->tags_assoc) && count($file->tags_assoc) > 0) {
                    $query->clear()
                        ->select('label,class')
                        ->from($db->quoteName('#__emundus_setup_action_tag'))
                        ->where($db->quoteName('id') . ' IN (' . $file->tags_assoc . ')');
                    $db->setQuery($query);
                    $tags = $db->loadObjectList();
                }

                $file->tags = $tags;
            }

        } catch (Exception $e) {
            JLog::add('Problem when build query with error : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.evaluations');
        }
        return $files;
    }

    /**
     * @param string $element_plugin
     * @return string
     */
    private function getFilterTypeFromFabrikElementPlugin($element_plugin)
    {
        $type = '';

        if (!empty($element_plugin)) {
            switch ($element_plugin) {
                case 'radiobutton':
                case 'databasejoin':
                case 'cascadingdropdown':
                case 'dropdown':
                case 'checkbox':
                    $type = 'select';
                    break;
                case 'date':
                case 'jdate':
                case 'birthday':
                case 'years':
                    $type = 'date';
                    break;
                case 'field':
                case 'textarea':
                case 'calc':
                case 'display':
                case 'internalid':
                case 'user':
                default:
                    $type = 'field';
            }
        }

        return $type;
    }

    private function getValuesFromFabrikElement($element_id, $element_plugin, $type)
    {
        $values = [];

        if ($type === 'select') {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            switch ($element_plugin) {
                case 'databasejoin':
                    $query->select('params')
                        ->from('#__fabrik_elements')
                        ->where('id = ' . $element_id);

                    $db->setQuery($query);
                    $params = $db->loadResult();

                    if (!empty($params)) {
                        $params = json_decode($params, true);

                        if (!empty($params['join_db_name']) && !empty($params['join_key_column'])) {
                            $select = $params['join_key_column'] . ' AS value';

                            if (!empty($params['join_val_column_concat'])) {
                                $lang = substr(JFactory::getLanguage()->getTag(), 0, 2);
                                $params['join_val_column_concat'] = str_replace('{thistable}', $params['join_db_name'], $params['join_val_column_concat']);
                                $params['join_val_column_concat'] = str_replace('{shortlang}', $lang, $params['join_val_column_concat']);
                                $params['join_val_column_concat'] = 'CONCAT(' . $params['join_val_column_concat'] . ') as label';

                                if (preg_match_all('/[#_a-z]+\.[_a-z]+/', $params['join_val_column_concat'], $matches)) {
                                    foreach ($matches[0] as $match) {
                                        $params['join_val_column_concat'] = str_replace($match, $db->quoteName($match), $params['join_val_column_concat']);
                                    }
                                }
                                $select .= ', ' . $params['join_val_column_concat'];
                            } else {
                                $select .= ', ' . $db->quoteName($params['join_val_column'], 'label');
                            }

                            $query->clear()
                                ->select($select)
                                ->from($params['join_db_name']);

                            try {
                                $db->setQuery($query);
                                $values = $db->loadAssocList();
                            } catch (Exception $e) {
                                JLog::add('Failed to get filter values ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
                            }
                        }
                    }
                    break;
                case 'dropdown':
                case 'radiobutton':
                case 'checkbox':
                    $query->select('params')
                        ->from('#__fabrik_elements')
                        ->where('id = ' . $element_id);

                    $db->setQuery($query);
                    $params = $db->loadResult();

                    if (!empty($params)) {
                        $params = json_decode($params, true);

                        if (!empty($params['sub_options'])) {
                            foreach ($params['sub_options']['sub_values'] as $sub_opt_key => $sub_opt) {
                                $label = \JText::_($params['sub_options']['sub_labels'][$sub_opt_key]);
                                if ($sub_opt == 0) {
                                    $label = '';
                                }

                                $values[] = [
                                    'value' => $sub_opt,
                                    'label' => $label
                                ];
                            }
                        }
                    }
                    break;
            }
        }

        return $values;
    }

    public function getFile($fnum)
    {
        $db = JFactory::getDbo();

        $select = $this->buildSelect(false);
        $left_joins = $this->buildLeftJoin(null, false);
        $wheres[] = $db->quoteName('ecc.fnum') . ' LIKE ' . $db->quote($fnum);

        return $this->getFilesQuery($select, $left_joins, $wheres, '', 0, 0, 'single_object');
    }


    private function addQueryFilters(&$left_joins, &$wheres): void
    {
        $Itemid = JFactory::getSession()->get('current_menu_id', 0);
        $menu = JFactory::getApplication()->getMenu();
        $params = $menu->getParams($Itemid)->get('params');

        if ($params->display_filters == 1) {
            $filters = $this->getFilters();

            if (!empty($filters['applied_filters'])) {
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);

                $left_joins_already_used = [];
                foreach ($filters['applied_filters'] as $f_key => $filter) {
                    if (!empty($filter['id'] && isset($filter['selectedValue']))) {
                        if (is_numeric($filter['id'])) {
                            $query->clear()
                                ->select('jfe.plugin, jfe.name, jfe.group_id, jfl.db_table_name, jfe.params, jfg.params as group_params, jfl.id as list_id')
                                ->from('#__fabrik_elements as jfe')
                                ->join('inner', '#__fabrik_formgroup as jff ON jff.group_id = jfe.group_id')
                                ->join('inner', '#__fabrik_groups as jfg ON jff.group_id = jfg.id')
                                ->join('inner', '#__fabrik_lists as jfl ON jfl.form_id = jff.form_id')
                                ->where('jfe.id = ' . $filter['id']);

                            try {
                                $db->setQuery($query);
                                $element_data = $db->loadAssoc();
                            } catch (Exception $e) {
                                $element_data = [];
                            }

                            if (!empty($element_data)) {
                                $join_key = '';

                                $group_params = json_decode($element_data['group_params'], true);
                                if ($group_params['repeat_group_button'] == '1') {
                                    // get join table
                                    $query->clear()
                                        ->select('join_from_table, table_join, table_key, table_join_key')
                                        ->from($db->quoteName('#__fabrik_joins'))
                                        ->where('list_id = ' . $element_data['list_id'])
                                        ->andWhere('group_id = ' . $element_data['group_id']);

                                    try {
                                        $db->setQuery($query);
                                        $join = $db->loadAssoc();
                                    } catch (Exception $e) {
                                        $join = [];
                                    }

                                    if (!empty($join)) {
                                        $join_parent_key = 'lj_parent_' . $join['join_from_table'];
                                        $join_key = 'lj_child_' . $join['table_join'];

                                        if (!in_array($join['join_from_table'], $left_joins_already_used)) {
                                            $left_joins[] = $db->quoteName($join['join_from_table']) . 'AS ' . $join_parent_key . ' ON  ' . $join_parent_key . '.fnum = ecc.fnum';
                                            $left_joins_already_used[] = $join['join_from_table'];
                                        }

                                        if (!in_array($join['table_join'], $left_joins_already_used)) {
                                            $left_joins[] = $db->quoteName($join['table_join']) . 'AS ' . $join_key . ' ON  ' . $join_key . '.' . $join['table_join_key'] . ' = ' . $join_parent_key . '.' . $join['table_key'];
                                            $left_joins_already_used[] = $join['table_join'];
                                        }
                                    }
                                } else {
                                    if ($element_data['db_table_name'] == 'jos_emundus_evaluations') {
                                        $join_key = 'lj_' . $element_data['db_table_name'];

                                        if (!in_array($element_data['db_table_name'], $left_joins_already_used)) {
                                            $left_joins[] = $db->quoteName($element_data['db_table_name']) . 'AS ' . $join_key . ' ON  ' . $join_key . '.fnum = ecc.fnum AND ' . $join_key . '.user = ' . JFactory::getUser()->id;
                                            $left_joins_already_used[] = $element_data['db_table_name'];
                                        }
                                    } else if ($element_data['db_table_name'] != 'jos_emundus_campaign_candidature') {
                                        $join_key = 'lj_' . $element_data['db_table_name'];

                                        if (!in_array($element_data['db_table_name'], $left_joins_already_used)) {
                                            $left_joins[] = $db->quoteName($element_data['db_table_name']) . 'AS ' . $join_key . ' ON  ' . $join_key . '.fnum = ecc.fnum';
                                            $left_joins_already_used[] = $element_data['db_table_name'];
                                        }
                                    }
                                }

                                if ($element_data['db_table_name'] == 'jos_emundus_campaign_candidature') {
                                    $join_key = 'ecc';
                                }

                                if (!empty($join_key)) {
                                    if ($filter['type'] == 'select') {
                                        $values = [];
                                        foreach ($filter['selectedValue'] as $selected_value) {
                                            $values[] = $selected_value['value'];
                                        }

                                        if ($element_data['plugin'] == 'checkbox') {
                                            $where = $db->quoteName($join_key . '.' . $element_data['name']);
                                            foreach ($values as $i => $value) {
                                                if ($i == 0) {
                                                    $where .= " LIKE '%\"" . $value . "\"%'";
                                                } else {
                                                    $where .= " OR LIKE '%\"" . $value . "\"%'";
                                                }
                                            }

                                            $wheres[] = $where;
                                        } else {
                                            $imploded_values = implode(',', $db->quote($values));
                                            $wheres[] = $db->quoteName($join_key . '.' . $element_data['name']) . ' ' . $filter['selectedOperator'] . ' (' . $imploded_values . ')';
                                        }
                                    } else {
                                        $value = $db->quote($filter['selectedValue']);
                                        if (in_array($filter['selectedOperator'], ['LIKE', 'NOT LIKE'])) {
                                            $value = '"%' . $filter['selectedValue'] . '%"';
                                        }

                                        $wheres[] = $db->quoteName($join_key . '.' . $element_data['name']) . ' ' . $filter['selectedOperator'] . ' ' . $value;
                                    }
                                }
                            }
                        } else {
                            switch ($filter['id']) {
                                case 'status':
                                case 'campaign_id':
                                case 'fnum':
                                    $values = [];
                                    foreach ($filter['selectedValue'] as $selected_value) {
                                        $values[] = $selected_value['value'];
                                    }

                                    if (!empty($values)) {
                                        $wheres[] = $db->quoteName('ecc.' . $filter['id']) . ' ' . $filter['selectedOperator'] . ' (' . implode(',', $db->quote($values)) . ')';
                                    }
                                    break;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getComments($fnum): array
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $comments = [];

        try {
            $query->select('ec.id,ec.reason,ec.comment_body,ec.date,concat(eu.lastname," ",eu.firstname) as user,ec.user_id')
                ->from($db->quoteName('#__emundus_comments', 'ec'))
                ->leftJoin($db->quoteName('#__emundus_users', 'eu') . ' ON ' . $db->quoteName('eu.user_id') . ' = ' . $db->quoteName('ec.user_id'))
                ->where($db->quoteName('ec.fnum') . ' = ' . $db->quote($fnum));
            $db->setQuery($query);
            $comments = $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('Problem when get comments : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.evaluations');
        }

        return $comments;
    }

    public function getComment($cid): object
    {
        $comment = new \stdClass();

        if (!empty($cid)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('ec.id, ec.reason, ec.comment_body,ec.date,concat(eu.lastname," ",eu.firstname) as user')
                ->from($db->quoteName('#__emundus_comments', 'ec'))
                ->leftJoin($db->quoteName('#__emundus_users', 'eu') . ' ON ' . $db->quoteName('eu.user_id') . ' = ' . $db->quoteName('ec.user_id'))
                ->where($db->quoteName('ec.id') . ' = ' . $db->quote($cid));

            try {
                $db->setQuery($query);
                $comment = $db->loadObject();
            } catch (Exception $e) {
                JLog::add('Problem when get comment : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.evaluations');
            }
        }

        return $comment;
    }

    public function saveComment($fnum, $reason, $comment_body): object
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $comment = new \stdClass();

        try {
            $query->select('applicant_id')
                ->from($db->quoteName('#__emundus_campaign_candidature', 'ecc'))
                ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));
            $db->setQuery($query);
            $aid = $db->loadResult();


            $query->clear()
                ->insert($db->quoteName('#__emundus_comments'))
                ->set($db->quoteName('applicant_id') . ' = ' . $db->quote($aid))
                ->set($db->quoteName('user_id') . ' = ' . $db->quote($this->current_user->id))
                ->set($db->quoteName('fnum') . ' = ' . $db->quote($fnum))
                ->set($db->quoteName('reason') . ' = ' . $db->quote($reason))
                ->set($db->quoteName('date') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                ->set($db->quoteName('comment_body') . ' = ' . $db->quote($comment_body));
            $db->setQuery($query);
            $result = $db->execute();

            if ($result) {
                $last_comment_id = $db->insertid();

                $comment = $this->getComment($last_comment_id);
            }
        } catch (Exception $e) {
            JLog::add('Problem when save comment : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.evaluations');
        }

        return $comment;
    }

    public function deleteComment($cid): bool
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $result = false;

        try {
            $query->delete($db->quoteName('#__emundus_comments'))
                ->where($db->quoteName('id') . ' = ' . $cid);
            $db->setQuery($query);
            $result = $db->execute();
        } catch (Exception $e) {
            JLog::add('Problem when delete comment : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.evaluations');
        }

        return $result;
    }
}
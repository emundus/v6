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
    protected array $filters = [];
    protected int $page = 0;
    protected int $limit = 10;
	protected int $total = 0;

	public function __construct(){
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
        $this->setFilters($columns);
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
    public function setFilters(array $columns)
    {

        if (empty($this->filters)) {
            $this->filters = [
                'default_filters' => [],
                'selected_filters' => []
            ];
        }

        if (!empty($columns)) {
            foreach ($columns as $column) {
                if (!array_key_exists($column->id, $this->filters['default_filters'])) {
                    $type = $this->getFilterTypeFromFabrikElementPlugin($column->plugin);
                    $values = $this->getValuesFromFabrikElement($column->id, $column->plugin, $type);

                    $this->filters['default_filters'][$column->id] = [
                        'type' => $type,
                        'label' => $column->label,
                        'values' => $values,
                        'operators' => [] // todo: handle operators in filters
                    ];
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
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
		if(in_array($fnum,$this->files['fnums'])){
			$can_access = true;
		}

		return $can_access;
	}

	public function getAccess($fnum): array
	{
		$actions = [
			1 => [
				'r' => \EmundusHelperAccess::asAccessAction(1,'r',$this->current_user->id,$fnum)
			],
			4 => [
				'r' => \EmundusHelperAccess::asAccessAction(4,'r',$this->current_user->id,$fnum),
				'u' => \EmundusHelperAccess::asAccessAction(4,'u',$this->current_user->id,$fnum),
				'c' => \EmundusHelperAccess::asAccessAction(4,'c',$this->current_user->id,$fnum),
			],
			10 => [
				'r' => \EmundusHelperAccess::asAccessAction(10,'r',$this->current_user->id,$fnum),
				'c' => \EmundusHelperAccess::asAccessAction(10,'c',$this->current_user->id,$fnum),
				'u' => \EmundusHelperAccess::asAccessAction(10,'u',$this->current_user->id,$fnum),
			]
		];

		return $actions;
	}

	public function getOffset(){
		if(!empty($this->page)) {
			return $this->page * $this->limit;
		} else {
			return $this->page;
		}
	}

	public function buildSelect($status_access): array{
		$select = ['DISTINCT ecc.fnum', 'ecc.applicant_id', 'ecc.campaign_id as campaign', 'u.name as applicant_name'];

		if($status_access) {
			$select[] = 'ess.value as status,ess.class as status_color';
		}

		return $select;
	}

	public function buildLeftJoin($params,$status_access): array{
		$db = JFactory::getDbo();

		$left_joins = [
			$db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('ecc.applicant_id') . ' = ' . $db->quoteName('u.id'),
		];
		if($status_access) {
			$left_joins[] = $db->quoteName('#__emundus_setup_status', 'ess') . ' ON ' . $db->quoteName('ess.step') . ' = ' . $db->quoteName('ecc.status');
		}
		if (isset($params->tags) && $params->tags !== '') {
			$left_joins[] = $db->quoteName('#__emundus_tag_assoc','eta').' ON '.$db->quoteName('eta.fnum').' = '.$db->quoteName('ecc.fnum');
		}

		return $left_joins;
	}

	public function buildWhere($params): array{
		$db = JFactory::getDbo();

		$wheres = [];
		if (isset($params->status) && $params->status !== '') {
			$wheres[] = $db->quoteName('ecc.status') . ' IN (' . implode(',',$params->status) . ')';
		}

		if (isset($params->tags) && $params->tags !== '') {
			$wheres[] = $db->quoteName('eta.id_tag') . ' IN (' . implode(',',$params->tags) . ')';
		}

		if (isset($params->campaign_to_exclude) && $params->campaign_to_exclude !== '') {
			$wheres[] = $db->quoteName('ecc.campaign_id') . ' NOT IN (' . $params->campaign_to_exclude . ')';
		}

		if (!empty($params->status_to_exclude)) {
			$wheres[] = $db->quoteName('ecc.status') . ' NOT IN (' . implode(',',$params->status_to_exclude) . ')';
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

	public function buildQuery($select,$left_joins = [],$wheres = [],$access = '',$limit = 0,$offset = 0,$return = 'object'){
		$em_session = JFactory::getSession()->get('emundusUser');

		$db = JFactory::getDbo();
		$query_groups_allowed = $db->getQuery(true);
		$query_users_associated = $db->getQuery(true);
		$query_groups_associated = $db->getQuery(true);
		$query_groups_program_associated = $db->getQuery(true);

		try {
			$groups_allowed = [];
			if(!empty($em_session->emGroups)) {
				$query_groups_allowed->select('acl.group_id')
					->from($db->quoteName('#__emundus_acl', 'acl'));
					if(!empty($access)) {
						$query_groups_allowed->where($access);
					}
					$query_groups_allowed->andWhere($db->quoteName('acl.group_id') . ' IN (' . implode(',', $db->quote($em_session->emGroups)) . ')');
				$db->setQuery($query_groups_allowed);
				$groups_allowed = $db->loadColumn();
			}

			if(!empty($groups_allowed)) {
				$query_groups_program_associated->select(implode(',',$select))
					->from($db->quoteName('#__emundus_setup_groups', 'eg'))
					->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'esgrc') . ' ON ' . $db->quoteName('esgrc.parent_id') . ' = ' . $db->quoteName('eg.id'))
					->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('esc.training') . ' = ' . $db->quoteName('esgrc.course'))
					->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'ecc') . ' ON ' . $db->quoteName('esc.id') . ' = ' . $db->quoteName('ecc.campaign_id'));
				foreach ($left_joins as $left_join){
					$query_groups_program_associated->leftJoin($left_join);
				}
				$query_groups_program_associated->where($db->quoteName('eg.id') . ' IN (' . implode(',', $db->quote($groups_allowed)) .')')
					->andWhere($db->quoteName('ecc.published') . ' = 1');
				foreach ($wheres as $where){
					$query_groups_program_associated->andWhere($where);
				}
			}

			$query_users_associated->select(implode(',',$select))
				->from($db->quoteName('#__emundus_users_assoc','eua'))
				->leftJoin($db->quoteName('#__emundus_campaign_candidature','ecc').' ON '.$db->quoteName('eua.fnum').' = '.$db->quoteName('ecc.fnum'));
			foreach ($left_joins as $left_join){
				$query_users_associated->leftJoin($left_join);
			}
			$query_users_associated->where($db->quoteName('eua.user_id') . ' = ' . $db->quote($this->current_user->id));
			if(!empty($access)) {
				$query_users_associated->andWhere($access);
			}
			$query_users_associated->andWhere($db->quoteName('ecc.published') . ' = 1');
			foreach ($wheres as $where){
				$query_users_associated->andWhere($where);
			}

			if(!empty($groups_allowed)) {
				$query_groups_associated->union($query_groups_program_associated);
			}
			$query_groups_associated->union($query_users_associated);
			$query_groups_associated->select(implode(',',$select))
				->from($db->quoteName('#__emundus_groups','eg'))
				->leftJoin($db->quoteName('#__emundus_group_assoc','ega').' ON '.$db->quoteName('ega.group_id').' = '.$db->quoteName('eg.group_id'))
				->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'ecc') . ' ON ' . $db->quoteName('ega.fnum') . ' = ' . $db->quoteName('ecc.fnum'));
			foreach ($left_joins as $left_join){
				$query_groups_associated->leftJoin($left_join);
			}
			$query_groups_associated->where($db->quoteName('eg.user_id') . ' = ' . $db->quote($this->current_user->id));
			if(!empty($access)) {
				$query_groups_associated->andWhere($access);
			}
			$query_groups_associated->andWhere($db->quoteName('ecc.published') . ' = 1');
			foreach ($wheres as $where){
				$query_groups_associated->andWhere($where);
			}

			$db->setQuery($query_groups_associated,$offset,$limit);
			if($return == 'object'){
				return $db->loadObjectList();
			} elseif ($return == 'assoc') {
				return $db->loadAssocList();
			} elseif ($return == 'column') {
				return $db->loadColumn();
			} elseif ($return == 'single_object') {
				return $db->loadObject();
			}
		}
		catch (Exception $e) {
			JLog::add('Problem when build query with error : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.evaluations');
			return 0;
		}
	}

	public function getFilesQuery($select,$left_joins = [],$wheres = [],$access = '',$limit = 0,$offset = 0,$return = 'object'){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		try {
			$query->select(implode(',',$select))
				->from($db->quoteName('#__emundus_campaign_candidature','ecc'));
			foreach ($left_joins as $left_join){
				$query->leftJoin($left_join);
			}
			$query->where($db->quoteName('ecc.published') . ' = 1');
			foreach ($wheres as $where){
				$query->andWhere($where);
			}

			$db->setQuery($query,$offset,$limit);

			if($return == 'object'){
				return $db->loadObjectList();
			} elseif ($return == 'assoc') {
				return $db->loadAssocList();
			} elseif ($return == 'column') {
				return $db->loadColumn();
			} elseif ($return == 'single_object') {
				return $db->loadObject();
			}
		}
		catch (Exception $e) {
			JLog::add('Problem when build query with error : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.evaluations');
			return 0;
		}
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

    private function getValuesFromFabrikElement($element_id, $element_plugin, $type) {
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
                        $select = $params['join_key_column'] . ' as key';
                        $params = json_decode($params, true);

                        if (!empty($params['join_db_name']) && !empty($params['join_key_column'])) {
                            if (!empty($params['join_val_column_concat'])) {
                                $lang = substr(JFactory::getLanguage()->getTag(), 0, 2);
                                $params['join_val_column_concat'] = 'CONCAT(' . $params['join_val_column_concat'] . ') as value';
                                $params['join_val_column_concat'] = str_replace('{thistable}', $params['join_db_name'], $params['join_val_column_concat']);
                                $params['join_val_column_concat'] = str_replace('{shortlang}', $lang, $params['join_val_column_concat']);

                                $select .= ', ' . $params['join_val_column_concat'];
                            } else {
                                $select = ', ' . $db->quoteName($params['join_val_column'], 'value');
                            }


                            $query->clear()
                                ->select($select)
                                ->from($params['join_db_name']);

                            $db->setQuery($query);
                            $values = $db->loadAssocList($params['join_key_column'], 'value');
                        }
                    }
                   break;
            }
        }

        return $values;
    }

    public function addQueryFilters($query) {
        $filters = $this->getFilters();

        return $query;
    }

    public function getFile($fnum){
        $db = JFactory::getDbo();

        $select = $this->buildSelect(false);
        $left_joins = $this->buildLeftJoin(null,false);
        $wheres[] = $db->quoteName('ecc.fnum') . ' LIKE ' . $db->quote($fnum);

        return $this->getFilesQuery($select, $left_joins, $wheres,'',0,0,'single_object');
    }

}
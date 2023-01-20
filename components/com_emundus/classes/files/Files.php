<?php

namespace classes\files;

use JFactory;
use JLog;
use Throwable;

class Files
{
    protected \Joomla\CMS\User\User $current_user;
    protected array $rights;
    protected array $files;
    protected array $columns;
    protected int $page = 0;
    protected int $limit = 10;
	protected int $total;

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

	public function getOffset(){
		if(!empty($this->page)) {
			return $this->page * $this->limit;
		} else {
			return $this->page;
		}
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
			} else {
				return $db->loadAssocList();
			}
		}
		catch (Exception $e) {
			JLog::add('Problem when build query with error : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.evaluations');
			return 0;
		}

	}


}
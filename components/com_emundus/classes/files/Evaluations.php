<?php
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'classes' . DS . 'files' . DS . 'Files.php');

use classes\files\Files;

class Evaluations extends Files
{
	protected array $to_evaluate = [];
	protected array $evaluated = [];
	protected array $all = [];
	protected array $in_progress = [];
	protected string $selected_tab = 'all';

    public function __construct(){
        JLog::addLogger(['text_file' => 'com_emundus.evaluations.php'], JLog::ERROR, 'com_emundus.evaluations');

        $this->current_user = JFactory::getUser();
	    $this->files = [];
    }


    public function setFiles(): void
    {
	    $files_associated = [];

        $db = JFactory::getDbo();
	    $query = $db->getQuery(true);

		$read_access_file = $db->quoteName('action_id') . ' = ' . $db->quote(1) . ' AND ' . $db->quoteName('r') . ' = ' . $db->quote(1);
		$read_access_evaluation = $db->quoteName('action_id') . ' = ' . $db->quote(5) . ' AND ' . $db->quoteName('r') . ' = ' . $db->quote(1);
		$create_access_evaluation = $db->quoteName('action_id') . ' = ' . $db->quote(5) . ' AND ' . $db->quoteName('c') . ' = ' . $db->quote(1);

		$selected_tab = $this->getSelectedTab();

        require_once (JPATH_SITE.'/components/com_emundus/models/application.php');
        $m_application  = new EmundusModelApplication;

        require_once (JPATH_SITE.'/components/com_emundus/helpers/array.php');
        $h_array  = new EmundusHelperArray;

        try {
			$read_status_allowed = EmundusHelperAccess::asAccessAction(13,'r',JFactory::getUser()->id);

	        $Itemid = JFactory::getSession()->get('current_menu_id',0);
	        $menu = JFactory::getApplication()->getMenu();
	        $params = $menu->getParams($Itemid)->get('params');

	        $select_all = ['DISTINCT ecc.fnum'];
			$select_count = ['DISTINCT ecc.id as total'];

			$select = $this->buildSelect($params,$read_status_allowed);
			$left_joins = $this->buildLeftJoin($params,$read_status_allowed);
			$wheres = $this->buildWhere($params);

			parent::setFnums($this->buildQuery($select_all,[],$wheres,$read_access_file,0,0,'column',$params));
	        parent::setTotal(count($this->getFnums()));


			// Build WHERE to get differents groups of files
	        $wheres_to_evaluate = ['ecc.fnum NOT IN (SELECT fnum from jos_emundus_evaluations WHERE user = '.$db->quote(JFactory::getUser()->id).')'];
	        $wheres_to_evaluate[] = 'ecc.fnum IN ('.implode(',',$db->quote($this->getFnums())).')';

	        $wheres_evaluated = ['ecc.fnum IN (SELECT fnum from jos_emundus_evaluations WHERE user = '.$db->quote(JFactory::getUser()->id).')'];
	        $wheres_evaluated[] = 'ecc.fnum IN ('.implode(',',$db->quote($this->getFnums())).')';

	        $wheres_all = ['ecc.fnum IN ('.implode(',',$db->quote($this->getFnums())).')'];
			//

	        if($selected_tab == 'to_evaluate') {
				$files_associated = $this->getFilesQuery($select, $left_joins, $wheres_to_evaluate, $create_access_evaluation, $this->getLimit(), $this->getOffset());
			} elseif ($selected_tab == 'evaluated') {
				$files_associated = $this->getFilesQuery($select, $left_joins, $wheres_evaluated, $create_access_evaluation, $this->getLimit(), $this->getOffset());
			} elseif ($selected_tab == 'all') {
		        $files_associated = $this->getFilesQuery($select, $left_joins, $wheres_all, $read_access_evaluation, $this->getLimit(), $this->getOffset());
	        }

	        if(isset($params->display_group_assoc) && $params->display_group_assoc == 1){
		        $files_associated = $this->buildAssocGroups($files_associated);
	        }

            if(isset($params->display_tag_assoc) && $params->display_tag_assoc == 1 && EmundusHelperAccess::asAccessAction(14,'r',JFactory::getUser()->id)) {
                $files_associated = $this->buildAssocTags($files_associated);
            }


                // Get count of differents groups
	        $total_files_to_evaluate = $this->buildQuery($select_count,[],$wheres_to_evaluate,$read_access_file,0,0,'column',$params);
	        $to_evaluate = $this->getToEvaluate();
			if(empty($to_evaluate)){
				$to_evaluate['limit'] = 10;
				$to_evaluate['page'] = 0;
			}
	        $to_evaluate['total'] = sizeof($total_files_to_evaluate);
			$this->setToEvaluate($to_evaluate);

	        $total_files_evaluated = $this->buildQuery($select_count,[],$wheres_evaluated,$read_access_file,0,0,'column',$params);
	        $evaluated = $this->getEvaluated();
	        if(empty($evaluated)){
		        $evaluated['limit'] = 10;
		        $evaluated['page'] = 0;
	        }
	        $evaluated['total'] = sizeof($total_files_evaluated);
	        $this->setEvaluated($evaluated);

	        $total_files_all = $this->buildQuery($select_count,[],$wheres,$read_access_file,0,0,'column',$params);
	        $all = $this->getAll();
	        if(empty($all)){
		        $all['limit'] = 10;
		        $all['page'] = 0;
	        }
	        $all['total'] = sizeof($total_files_all);
	        $this->setAll($all);
			//

	        if(!empty($files_associated)) {
		        $fnums = [];
		        foreach ($files_associated as $file) {
			        if (!in_array($file->fnum, $fnums)) {
				        $fnums[] = $file->fnum;
			        }
		        }

		        $query->clear()
			        ->select('distinct esp.fabrik_group_id')
			        ->from($db->quoteName('#__emundus_setup_programmes', 'esp'))
			        ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('esp.code') . ' = ' . $db->quoteName('esc.training'))
			        ->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'ecc') . ' ON ' . $db->quoteName('esc.id') . ' = ' . $db->quoteName('ecc.campaign_id'))
			        ->where($db->quoteName('ecc.fnum') . ' IN (' . implode(',', $db->quote($fnums)) . ')');
		        $db->setQuery($query);
		        $eval_groups = $db->loadColumn();
		        $eval_groups = array_filter($eval_groups, function ($value) {
			        return !empty($value);
		        });

				if (empty($eval_groups)) {
					throw new ErrorException('COM_EMUNDUS_ERROR_NO_EVALUATION_GROUP');
				} else {
					$query->clear()
						->select('fe.id, fe.name, fe.label, fe.show_in_list_summary, fe.plugin, ffg.form_id')
						->from($db->quoteName('#__fabrik_elements', 'fe'))
						->leftJoin($db->quoteName('#__fabrik_formgroup', 'ffg') . ' ON ' . $db->quoteName('ffg.group_id') . ' = ' . $db->quoteName('fe.group_id'))
						->where($db->quoteName('fe.group_id') . ' IN (' . implode(',', $eval_groups) . ')');
					if (isset($params->more_elements) && $params->more_elements !== '') {
						$query->orWhere($db->quoteName('fe.id') . ' IN (' . $params->more_elements . ')');
					}
					$query->andWhere($db->quoteName('fe.published') . ' = 1');
					$db->setQuery($query);
					$eval_elements = $db->loadObjectList('name');

					$evaluations               = array();
					$more_elements_by_campaign = new stdClass;
					if (isset($params->more_elements_campaign)) {
						$more_elements_by_campaign = json_decode($params->more_elements_campaign);
					}

					foreach ($files_associated as $file) {
						$evaluation                 = new stdClass;
						$evaluation->fnum           = $file->fnum;
						$evaluation->student_id     = $file->applicant_id;
						$evaluation->campaign       = $file->campaign;
						$evaluation->applicant_name = '';
						if(!EmundusHelperAccess::isDataAnonymized(JFactory::getUser()->id)) {
							$evaluation->applicant_name = $file->applicant_name;
						}
						if(isset($file->assocs)){
							$evaluation->assocs = $file->assocs;
						}
						if(isset($file->tags)){
							$evaluation->tags = $file->tags;
						}
						if (isset($file->status)) {
							$evaluation->status       = $file->status;
							$evaluation->status_color = $file->status_color;
						}
						if (isset($file->campaign_label)) {
							$evaluation->campaign_label       = $file->campaign_label;
						}

						$key = false;
						if (!empty($more_elements_by_campaign->campaign)) {
							$key = array_search($file->campaign, $more_elements_by_campaign->campaign);
						}

						if ($key !== false) {
							$query->clear()
								->select('fe.id, fe.name, fe.label, fe.show_in_list_summary, fe.plugin, ffg.form_id')
								->from($db->quoteName('#__fabrik_elements', 'fe'))
								->leftJoin($db->quoteName('#__fabrik_formgroup', 'ffg') . ' ON ' . $db->quoteName('ffg.group_id') . ' = ' . $db->quoteName('fe.group_id'))
								->where($db->quoteName('fe.id') . ' IN (' . $more_elements_by_campaign->elements[$key] . ')');
							$db->setQuery($query);
							$more_elements = $db->loadObjectList('name');

							$eval_elements = array_merge($eval_elements, $more_elements);
						}

						foreach ($eval_elements as $elt) {
							$elt->label = JText::_($elt->label);
							if (!in_array($elt->name, ['fnum', 'student_id', 'campaign'])) {
								$evaluation->{$elt->name} = $m_application->getValuesByElementAndFnum($file->fnum, $elt->id, $elt->form_id, 0);
							}
						}

						$evaluations[] = $evaluation;
					}

					$evaluations                = $h_array->removeDuplicateObjectsByProperty($evaluations, 'fnum');
					$final_evaluations          = [];
					$final_evaluations['fnums'] = $this->getFnums();
					$final_evaluations['all']   = $evaluations;
				}
	        } else {
		        $final_evaluations          = [];
		        $final_evaluations['fnums'] = $this->getFnums();
		        $final_evaluations['all']   = [];
	        }
            $this->files = $final_evaluations;

	        if($read_status_allowed) {
				$status_column = new stdClass();
				$status_column->name = 'status';
				$status_column->show_in_list_summary = 1;
				$eval_elements[] = $status_column;
	        }
	        if(isset($params->display_group_assoc) && $params->display_group_assoc == 1) {
		        $assoc_column = new stdClass();
		        $assoc_column->name = 'assocs';
		        $assoc_column->show_in_list_summary = 1;
		        $eval_elements['assocs'] = $assoc_column;
	        }
            if(isset($params->display_tag_assoc) && $params->display_tag_assoc == 1 && EmundusHelperAccess::asAccessAction(14,'r',JFactory::getUser()->id)) {
                $tags_column = new stdClass();
                $tags_column->name = 'tags';
                $tags_column->show_in_list_summary = 1;
                $eval_elements['tags'] = $tags_column;
            }
	        if(isset($params->display_filter_campaigns) && $params->display_filter_campaigns == 1){
		        $campaign_label_column = new stdClass();
		        $campaign_label_column->name = 'campaign_label';
		        $campaign_label_column->label = JText::_('COM_EMUNDUS_CAMPAIGN');
		        $campaign_label_column->show_in_list_summary = 1;
		        $eval_elements[] = $campaign_label_column;
	        }
                parent::setColumns($eval_elements);
        } catch (Exception $e) {
            JLog::add('Problem to get files associated to user '.$this->current_user->id.' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.evaluations');

			if ($e->getMessage() === 'COM_EMUNDUS_ERROR_NO_EVALUATION_GROUP') {
				// throw the error, it has to be displayed to the user
				throw $e;
			}
        }
    }

    public function getEvaluationFormByFnum($fnum){
        try {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->clear()
                ->select('distinct esp.fabrik_group_id')
                ->from($db->quoteName('#__emundus_setup_programmes','esp'))
                ->leftJoin($db->quoteName('#__emundus_setup_campaigns','esc').' ON '.$db->quoteName('esp.code').' = '.$db->quoteName('esc.training'))
                ->leftJoin($db->quoteName('#__emundus_campaign_candidature','ecc').' ON '.$db->quoteName('esc.id').' = '.$db->quoteName('ecc.campaign_id'))
                ->where($db->quoteName('ecc.fnum') . ' LIKE ' . $db->quote($fnum));
            $db->setQuery($query);
            $eval_groups = $db->loadColumn();
            $eval_groups = array_filter($eval_groups, function($value) {
                return !empty($value);
            });

            $query->clear()
                ->select('form_id')
                ->from($db->quoteName('#__fabrik_formgroup'))
                ->where($db->quoteName('group_id') . ' IN (' . implode(',',$eval_groups) . ')');
            $db->setQuery($query);
            $form_id = $db->loadResult();

            return $form_id;
        } catch (Exception $e) {
            JLog::add('Problem when get evaluation form of fnum '.$fnum.' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.evaluations');
			return 0;
        }
    }

	public function getMyEvaluation($fnum){
		try {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->clear()
				->select('id')
				->from($db->quoteName('#__emundus_evaluations'))
				->where($db->quoteName('user') . ' = ' . JFactory::getUser()->id)
				->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));
			$db->setQuery($query);
			return $db->loadResult();
		}
		catch (Exception $e) {
			JLog::add('Problem when get evaluation form of fnum '.$fnum.' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.evaluations');
			return "";
		}
	}

	/**
	 * @return string
	 */
	public function getSelectedTab(): string
	{
		return $this->selected_tab;
	}

	/**
	 * @param string $selected_tab
	 */
	public function setSelectedTab(string $selected_tab): void
	{
		$this->selected_tab = $selected_tab;
	}

	/**
	 * @return array
	 */
	public function getToEvaluate(): array
	{
		return $this->to_evaluate;
	}

	/**
	 * @param   array  $to_evaluate
	 */
	public function setToEvaluate(array $to_evaluate): void
	{
		$this->to_evaluate = $to_evaluate;
	}

	/**
	 * @return array
	 */
	public function getEvaluated(): array
	{
		return $this->evaluated;
	}

	/**
	 * @param   array  $evaluated
	 */
	public function setEvaluated(array $evaluated): void
	{
		$this->evaluated = $evaluated;
	}

	/**
	 * @return array
	 */
	public function getInProgress(): array
	{
		return $this->in_progress;
	}

	/**
	 * @param   array  $in_progress
	 */
	public function setInProgress(array $in_progress): void
	{
		$this->in_progress = $in_progress;
	}

	/**
	 * @return array
	 */
	public function getAll(): array
	{
		return $this->all;
	}

	/**
	 * @param   array  $all
	 */
	public function setAll(array $all): void
	{
		$this->all = $all;
	}
	
	public function getLimit(): int{
		return !empty($this->{$this->selected_tab}['limit']) ? $this->{$this->selected_tab}['limit'] : 10;
	}

	public function setLimit(int $limit): void
	{
		$this->{$this->selected_tab}['limit'] = $limit;
		$this->setPage(0);
	}

	public function getPage(): int{
		return !empty($this->{$this->selected_tab}['page']) ? $this->{$this->selected_tab}['page'] : 0;
	}

	public function setPage(int $page): void
	{
		$this->{$this->selected_tab}['page'] = $page;
	}


    public function applyFilters($filters) {
        $this->filters['applied_filters'] = $filters;
    }
}
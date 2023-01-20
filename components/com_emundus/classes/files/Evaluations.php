<?php
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'classes' . DS . 'files' . DS . 'Files.php');

use classes\files\Files;

class Evaluations extends Files
{
	protected int $total_to_evaluate = 0;
	protected int $total_evaluated = 0;

    public function __construct(){
        JLog::addLogger(['text_file' => 'com_emundus.evaluations.php'], JLog::ERROR, 'com_emundus.evaluations');

        $this->current_user = JFactory::getUser();
	    $this->files = [];
    }


    public function setFiles(): void
    {
        $db = JFactory::getDbo();
	    $query = $db->getQuery(true);

		$read_access_evaluation = $db->quoteName('action_id') . ' = ' . $db->quote(5) . ' AND ' . $db->quoteName('r') . ' = ' . $db->quote(1);
		$create_access_evaluation = $db->quoteName('action_id') . ' = ' . $db->quote(5) . ' AND ' . $db->quoteName('c') . ' = ' . $db->quote(1);

        require_once (JPATH_SITE.'/components/com_emundus/models/application.php');
        $m_application  = new EmundusModelApplication;

        require_once (JPATH_SITE.'/components/com_emundus/helpers/array.php');
        $h_array  = new EmundusHelperArray;

        try {
	        $Itemid = JFactory::getSession()->get('current_menu_id',0);
	        $menu = JFactory::getApplication()->getMenu();
			$params = $menu->getParams($Itemid)->get('params');

			$read_status_allowed = EmundusHelperAccess::asAccessAction(13,'r',JFactory::getUser()->id);

            $fnums = [];

	        $select = ['DISTINCT ecc.fnum', 'ecc.applicant_id', 'ecc.campaign_id as campaign', 'u.name'];
	        $select_count = ['count(DISTINCT ecc.fnum) as total'];
	        $left_joins = [
				$db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('ecc.applicant_id') . ' = ' . $db->quoteName('u.id'),
	        ];
			if($read_status_allowed) {
				$select[] = 'ess.value as status,ess.class as status_color';
				$left_joins[] = $db->quoteName('#__emundus_setup_status', 'ess') . ' ON ' . $db->quoteName('ess.step') . ' = ' . $db->quoteName('ecc.status');
			}
			$wheres = [];
	        $wheres_to_evaluate = ['ecc.fnum NOT IN (SELECT fnum from jos_emundus_evaluations WHERE user = '.$db->quote(JFactory::getUser()->id).')'];

	        if (isset($params->status) && $params->status !== '') {
		        $wheres[] = $db->quoteName('ecc.status') . ' IN (' . implode(',',$params->status) . ')';
	        }

	        if (isset($params->tags) && $params->tags !== '') {
		        $wheres[] = $db->quoteName('#__emundus_tag_assoc','eta').' ON '.$db->quoteName('eta.fnum').' = '.$db->quoteName('ecc.fnum');
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

			$total_files_count = $this->buildQuery($select_count,$left_joins,$wheres,$read_access_evaluation);
			$total_files_to_evaluate = $this->buildQuery($select_count,$left_joins,$wheres_to_evaluate,$create_access_evaluation);
			$total_files_evaluated = $this->buildQuery($select_count,$left_joins,$wheres,$create_access_evaluation);
			$files_associated = $this->buildQuery($select,$left_joins,$wheres,$read_access_evaluation,$this->getLimit(),$this->getOffset());

	        $total_count = 0;
			foreach ($total_files_count as $total){
				$total_count += $total->total;
			}
			parent::setTotal($total_count);

            foreach ($files_associated as $file) {
                if (!in_array($file->fnum, $fnums)) {
                    $fnums[] = $file->fnum;
                }
            }

            $query->clear()
                ->select('distinct esp.fabrik_group_id')
                ->from($db->quoteName('#__emundus_setup_programmes','esp'))
                ->leftJoin($db->quoteName('#__emundus_setup_campaigns','esc').' ON '.$db->quoteName('esp.code').' = '.$db->quoteName('esc.training'))
                ->leftJoin($db->quoteName('#__emundus_campaign_candidature','ecc').' ON '.$db->quoteName('esc.id').' = '.$db->quoteName('ecc.campaign_id'))
                ->where($db->quoteName('ecc.fnum') . ' IN (' . implode(',',$db->quote($fnums)) . ')');
            $db->setQuery($query);
            $eval_groups = $db->loadColumn();
			$eval_groups = array_filter($eval_groups, function($value) {
				return !empty($value);
			});

            $query->clear()
                ->select('fe.id,fe.name,fe.label,fe.show_in_list_summary,ffg.form_id')
                ->from($db->quoteName('#__fabrik_elements','fe'))
                ->leftJoin($db->quoteName('#__fabrik_formgroup','ffg').' ON '.$db->quoteName('ffg.group_id').' = '.$db->quoteName('fe.group_id'))
                ->where($db->quoteName('fe.group_id') . ' IN (' . implode(',',$eval_groups) . ')');
            if (isset($params->more_elements) && $params->more_elements !== '') {
                $query->orWhere($db->quoteName('fe.id') . ' IN (' . $params->more_elements . ')');
            }
            $query->andWhere($db->quoteName('fe.published') . ' = 1');
            $db->setQuery($query);
            $eval_elements = $db->loadObjectList('name');

            $evaluations = array();
            $more_elements_by_campaign = new stdClass;
            if(isset($params->more_elements_campaign)) {
                $more_elements_by_campaign = json_decode($params->more_elements_campaign);
            }

            foreach ($files_associated as $file) {
                $evaluation = new stdClass;
                $evaluation->fnum = $file->fnum;
                $evaluation->student_id = $file->applicant_id;
                $evaluation->campaign_id = $file->campaign_id;
                $evaluation->applicant_name = $file->name;
				if(isset($file->status)){
					$evaluation->status = $file->status;
					$evaluation->status_color = $file->status_color;
				}

                $key = false;
                if(!empty($more_elements_by_campaign->campaign)) {
                    $key = array_search($file->campaign_id,$more_elements_by_campaign->campaign);
                }

                if($key !== false){
                    $query->clear()
                        ->select('fe.id,fe.name,fe.label,fe.show_in_list_summary,ffg.form_id')
                        ->from($db->quoteName('#__fabrik_elements','fe'))
                        ->leftJoin($db->quoteName('#__fabrik_formgroup','ffg').' ON '.$db->quoteName('ffg.group_id').' = '.$db->quoteName('fe.group_id'))
                        ->where($db->quoteName('fe.id') . ' IN (' . $more_elements_by_campaign->elements[$key] . ')');
                    $db->setQuery($query);
                    $more_elements = $db->loadObjectList('name');

                    $eval_elements = array_merge($eval_elements,$more_elements);
                }

                foreach ($eval_elements as $elt) {
                    $elt->label = JText::_($elt->label);
                    if (!in_array($elt->name,['fnum','student_id','campaign'])) {
                        $evaluation->{$elt->name} = $m_application->getValuesByElementAndFnum($file->fnum,$elt->id,$elt->form_id,0);
                    }
                }

                $evaluations[] = $evaluation;
            }

            $evaluations = $h_array->removeDuplicateObjectsByProperty($evaluations,'fnum');
			$final_evaluations = [];
			$final_evaluations['to_evaluate'] = [];
			$final_evaluations['evaluated'] = [];
			$final_evaluations['fnums'] = [];
			$final_evaluations['all'] = [];

			foreach ($evaluations as $evaluation){
				$final_evaluations['all'][] = $evaluation;
				$final_evaluations['fnums'][] = $evaluation->fnum;
				if(!empty($evaluation->time_date)){
					$final_evaluations['evaluated'][] = $evaluation;
				} else {
					$final_evaluations['to_evaluate'][] = $evaluation;
				}
			}

            $this->files = $final_evaluations;

	        if($read_status_allowed) {
				$status_column = new stdClass();
				$status_column->name = 'status';
				$status_column->show_in_list_summary = 0;
				$eval_elements[] = $status_column;
	        }
			parent::setColumns($eval_elements);
        } catch (Exception $e) {
            JLog::add('Problem to get files associated to user '.$this->current_user->id.' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.evaluations');
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
}
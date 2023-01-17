<?php
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'classes' . DS . 'files' . DS . 'Files.php');

use classes\files\Files;

class Evaluations extends Files
{
    public function __construct(){
        JLog::addLogger(['text_file' => 'com_emundus.evaluations.php'], JLog::ERROR, 'com_emundus.evaluations');

        $this->current_user = JFactory::getUser();
	    $this->files = [];
    }


    public function setFiles(): void
    {
	    $em_session = JFactory::getSession()->get('emundusUser');

        $db = JFactory::getDbo();
	    $query = $db->getQuery(true);
        $query_groups_allowed = $db->getQuery(true);
        $query_users_associated = $db->getQuery(true);
        $query_groups_associated = $db->getQuery(true);
        $query_groups_program_associated = $db->getQuery(true);

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
	        $groups_allowed = [];
			if(!empty($em_session->emGroups)) {
				$query_groups_allowed->select('acl.group_id')
					->from($db->quoteName('#__emundus_acl', 'acl'))
					->where($db->quoteName('acl.action_id') . ' = ' . $db->quote(5) . ' AND ' . $db->quoteName('acl.r') . ' = ' . $db->quote(1))
					->andWhere($db->quoteName('acl.group_id') . ' IN (' . implode(',', $db->quote($em_session->emGroups)) . ')');
				$db->setQuery($query_groups_allowed);
				$groups_allowed = $db->loadColumn();
			}

	        $select = ['DISTINCT ecc.fnum', 'ecc.applicant_id', 'ecc.campaign_id as campaign', 'u.name'];
	        $left_joins = [
				$db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('ecc.applicant_id') . ' = ' . $db->quoteName('u.id'),
	        ];
			if($read_status_allowed) {
				$select[] = 'ess.value as status,ess.class as status_color';
				$left_joins[] = $db->quoteName('#__emundus_setup_status', 'ess') . ' ON ' . $db->quoteName('ess.step') . ' = ' . $db->quoteName('ecc.status');
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
	        }

            $query_users_associated->select(implode(',',$select))
                ->from($db->quoteName('#__emundus_users_assoc','eua'))
                ->leftJoin($db->quoteName('#__emundus_campaign_candidature','ecc').' ON '.$db->quoteName('eua.fnum').' = '.$db->quoteName('ecc.fnum'));
	        foreach ($left_joins as $left_join){
		        $query_users_associated->leftJoin($left_join);
	        }
	        $query_users_associated->where($db->quoteName('eua.user_id') . ' = ' . $db->quote($this->current_user->id))
                ->andWhere($db->quoteName('eua.action_id') . ' = ' . $db->quote(5) . ' AND ' . $db->quoteName('eua.r') . ' = ' . $db->quote(1))
                ->andWhere($db->quoteName('ecc.published') . ' = 1');

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
	        $query_groups_associated->where($db->quoteName('eg.user_id') . ' = ' . $db->quote($this->current_user->id))
	            ->andWhere($db->quoteName('ega.action_id') . ' = ' . $db->quote(5) . ' AND ' . $db->quoteName('ega.r') . ' = ' . $db->quote(1))
                ->andWhere($db->quoteName('ecc.published') . ' = 1');

            if (isset($params->status) && $params->status !== '') {
	            $query_groups_associated->andWhere($db->quoteName('ecc.status') . ' IN (' . implode(',',$params->status) . ')');
            }

            if (isset($params->tags) && $params->tags !== '') {
	            $query_groups_associated->leftJoin($db->quoteName('#__emundus_tag_assoc','eta').' ON '.$db->quoteName('eta.fnum').' = '.$db->quoteName('ecc.fnum'))
                    ->andWhere($db->quoteName('eta.id_tag') . ' IN (' . implode(',',$params->tags) . ')');
            }

            if (isset($params->campaign_to_exclude) && $params->campaign_to_exclude !== '') {
	            $query_groups_associated->andWhere($db->quoteName('ecc.campaign_id') . ' NOT IN (' . $params->campaign_to_exclude . ')');
            }

            if (!empty($params->status_to_exclude)) {
	            $query_groups_associated->andWhere($db->quoteName('ecc.status') . ' NOT IN (' . implode(',',$params->status_to_exclude) . ')');
            }

            if (!empty($params->tags_to_exclude)) {
                $exclude_query = $db->getQuery(true);

                $exclude_query->select('eta.fnum')
                    ->from('jos_emundus_tag_assoc eta')
                    ->where('eta.id_tag IN (' . implode(',', $params->tags_to_exclude) . ')');
                $db->setQuery($exclude_query);
                $fnums_to_exclude = $db->loadColumn();

                if (!empty($fnums_to_exclude)) {
	                $query_groups_associated->where('ecc.fnum NOT IN (' . implode(',', $fnums_to_exclude) . ')');
                }
            }
            $db->setQuery($query_groups_associated);
            $files_associated = $db->loadObjectList();

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

			foreach ($evaluations as $evaluation){
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
        }
    }
}
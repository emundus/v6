<?php


class EmundusModelQcm extends JModelList {

    /**
     * Constructor
     *
     * @since 1.5
     */
    function __construct($model = 'qcm'){
        parent::__construct();
    }

    public function getQcm($formid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('*')
            ->from($db->quoteName('#__emundus_setup_qcm'))
            ->where($db->quoteName('form_id') . ' = ' . $db->quote($formid));

        try {
            $db->setQuery($query);
            return $db->loadObject();
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/qcm | Error when try to get qcm associated to form : ' . $formid . ' with query ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return new stdClass();
        }
    }

    public function getQcmApplicant($fnum){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('*')
            ->from($db->quoteName('#__emundus_qcm_applicants'))
            ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));

        try {
            $db->setQuery($query);
            return $db->loadObject();
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/qcm | Error when try to get qcm associated to applicant : ' . $fnum . ' with query ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return new stdClass();
        }
    }

    public function initQcmApplicant($fnum,$idqcm){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
        $m_profile = new EmundusModelProfile;

        $query
            ->select(['sq.count','qas.sectionid','group_concat(qaq.questionid) as questions'])
            ->from($db->quoteName('#__emundus_setup_qcm','sq'))
            ->leftJoin($db->quoteName('#__emundus_setup_qcm_repeat_sectionid','qas').' ON '.$db->quoteName('sq.id').' = '.$db->quoteName('qas.parent_id'))
            ->leftJoin($db->quoteName('#__emundus_setup_qcm_repeat_questionid','qaq').' ON '.$db->quoteName('sq.id').' = '.$db->quoteName('qaq.parent_id'))
            ->where($db->quoteName('sq.id') . ' = ' . $db->quote($idqcm))
            ->group('sq.id');

        try {
            $db->setQuery($query);
            $questions_assoc = $db->loadObject();

            if(!empty($questions_assoc->sectionid)){
                $query->clear()
                    ->select('id')
                    ->from($db->quoteName('#__emundus_qcm_questions'))
                    ->where($db->quoteName('section') . ' = ' . $db->quote($questions_assoc->sectionid));
                $db->setQuery($query);
                $questions = $db->loadColumn();
            } elseif (!empty($questions_assoc->questions)){
                $questions = explode(',',$questions_assoc->questions);
            }

            $random_questions = array();
            $key_questions_shuffled = array_rand($questions,(int)$questions_assoc->count);
            foreach ($key_questions_shuffled as $key){
                $random_questions[] = (int)$questions[$key];
            }

            $fnum_details = $m_profile->getFnumDetails($fnum);

            $query->clear()
                ->insert('#__emundus_qcm_applicants');
            $query->set($db->quoteName('fnum') . ' = ' . $db->quote($fnum_details['fnum']))
                ->set($db->quoteName('user') . ' = ' . $db->quote($fnum_details['user_id']))
                ->set($db->quoteName('questions') . ' = ' . $db->quote(implode(',',$random_questions)))
                ->set($db->quoteName('step') . ' = 0')
                ->set($db->quoteName('pending') . ' = 0')
                ->set($db->quoteName('qcmid') . ' = ' . $db->quote($idqcm));

            $db->setQuery($query);
            $db->execute();

            return $db->insertid();
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/qcm | Error when try to init qcm for applicant : ' . $fnum . ' with query ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return new stdClass();
        }
    }

    public function getQuestions($questions){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('qq.*,GROUP_CONCAT(qqr.id) as proposals_id,GROUP_CONCAT(qqr.proposals) as proposals_text')
            ->from($db->quoteName('#__emundus_qcm_questions','qq'))
            ->leftJoin($db->quoteName('jos_emundus_qcm_questions_765_repeat','qqr').' ON '.$db->quoteName('qq.id').' = '.$db->quoteName('qqr.parent_id'))
            ->where($db->quoteName('qq.id') . ' IN (' . $questions . ')')
            ->group('qq.id');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/qcm | Error when try to get questions : ' . $questions . ' with query ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return new stdClass();
        }
    }

    public function saveAnswer($question,$answers,$current_user,$formid,$module){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $fnum = $current_user->fnum;


        try {
            // Get table and group_repeat table
            $query->clear()
                ->select('db_table_name')
                ->from($db->quoteName('#__fabrik_lists'))
                ->where($db->quoteName('form_id') . ' = ' . $db->quote($formid));
            $db->setQuery($query);
            $table = $db->loadResult();

            $qcm = $this->getQcm($formid);
            $group_table = $table . '_' . $qcm->group_id . '_repeat';
            //

            $points = $this->checkPoints($answers,$question,$module);

            // Check if an existing answer exist else insert the answers
            $query->clear()
                ->select('id')
                ->from($db->quoteName($table))
                ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));
            $db->setQuery($query);
            $parent_id = $db->loadResult();

            if(empty($parent_id)){
                $query->clear()
                    ->insert($db->quoteName($table));
                $query->set($db->quoteName('time_date') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                    ->set($db->quoteName('fnum') . ' = ' . $db->quote($fnum))
                    ->set($db->quoteName('user') . ' = ' . $db->quote($current_user->id));
                $db->setQuery($query);
                $db->execute();
                $parent_id = $db->insertid();
            }

            $answers = implode(',',$answers);
            if(!empty($answers)) {
                $query->clear()
                    ->select('proposals')
                    ->from($db->quoteName('#__emundus_qcm_questions_765_repeat'))
                    ->where($db->quoteName('id') . ' IN (' . $answers . ')');
                $db->setQuery($query);
                $answers_text = implode(',', $db->loadColumn());
            }

            $columns = array('parent_id','question','answers','note','answers_text');
            $values = $parent_id .  ',' . $question . ',' . $db->quote($answers) . ',' . $points . ',' . $db->quote($answers_text);

            $query->clear()
                ->select('count(id)')
                ->from($db->quoteName($group_table))
                ->where($db->quoteName('parent_id') . ' = ' . $db->quote($parent_id))
                ->andWhere($db->quoteName('question') . ' = ' . $db->quote($question));
            $db->setQuery($query);
            $answers_existing = $db->loadResult();

            if($answers_existing == 0) {
                $query->clear()
                    ->insert($db->quoteName($group_table))
                    ->columns($columns)
                    ->values($values);
                $db->setQuery($query);
                $db->execute();
            }
            //

            // Update step of the user
            $query->clear()
                ->select('step')
                ->from($db->quoteName('#__emundus_qcm_applicants'))
                ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));

            $db->setQuery($query);
            $last_step = (int)$db->loadResult();
            $last_step++;

            $query->clear()
                ->update($db->quoteName('#__emundus_qcm_applicants'))
                ->set($db->quoteName('step') . ' = ' . $db->quote($last_step))
                ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));

            $db->setQuery($query);
            $db->execute();
            //

            return true;
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/qcm | Error when try to save answer : with query ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return new stdClass();
        }
    }

    public function checkPoints($answers,$question,$module){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $points = 0;
        $good_answers = [];

        try {
            // Get right and wrong points
            $query
                ->select('params')
                ->from($db->quoteName('#__modules'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($module));
            $db->setQuery($query);
            $qcm_module = json_decode($db->loadResult(), true);

            $right_answers = (float)$qcm_module['mod_em_qcm_points_right'];
            $wrong_answers = (float)$qcm_module['mod_em_qcm_points_wrong'];
            $missing_penalities = (float)$qcm_module['mod_em_qcm_points_missing_penalities'];
            $minimal_points = (float)$qcm_module['mod_em_qcm_points_minimal'];
            //

            // Get answers
            $query->clear()
                ->select('answers')
                ->from($db->quoteName('#__emundus_qcm_questions'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($question));
            $db->setQuery($query);
            $question_answers = explode(',',$db->loadResult());

            $query->clear()
                ->select('id')
                ->from($db->quoteName('#__emundus_qcm_questions_765_repeat'))
                ->where($db->quoteName('parent_id') . ' = ' . $db->quote($question));
            $db->setQuery($query);
            $proposals = $db->loadColumn();
            $proposals_key = array_keys($proposals);
            foreach ($proposals_key as $proposal_key){
                if(in_array($proposal_key+1,$question_answers)){
                    $good_answers[] = $proposals[$proposal_key];
                }
            }
            //

            foreach ($answers as $answer){
                if(in_array($answer,$good_answers)){
                    $points += $right_answers;
                } else {
                    if(($points -= $wrong_answers) < $minimal_points) {
                        $points = $minimal_points;
                    }
                }
            }

            foreach ($good_answers as $good_answer){
                if(!in_array($good_answer,$answers) && $points > $minimal_points){
                    $points -= $missing_penalities;
                }
            }

            return $points;
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/qcm | Error when try to check qcm points with query ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return new stdClass();
        }
    }

    public function updatePending($pending,$current_user){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $fnum = $current_user->fnum;

        try {
            $query->clear()
                ->update($db->quoteName('#__emundus_qcm_applicants'))
                ->set($db->quoteName('pending') . ' = ' . $db->quote($pending))
                ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e){
            JLog::add('component/com_emundus_onboard/models/qcm | Error when try to update qcm pending with query ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return new stdClass();
        }
    }
}

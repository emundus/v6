<?php
/**
 * @package    Joomla
 * @subpackage emundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
require_once (JPATH_COMPONENT.DS.'models'.DS.'profile.php');
require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');
require_once (JPATH_COMPONENT.DS.'models'.DS.'emails.php');
require_once (JPATH_COMPONENT.DS.'models'.DS.'users.php');
require_once (JPATH_COMPONENT.DS.'models'.DS.'evaluation.php');
require_once (JPATH_COMPONENT.DS.'models'.DS.'admission.php');
require_once (JPATH_COMPONENT.DS.'models'.DS.'interview.php');
require_once (JPATH_COMPONENT.DS.'models'.DS.'logs.php');
require_once (JPATH_COMPONENT.DS.'models'.DS.'campaign.php');

class EmundusViewApplication extends JViewLegacy {
    protected $_user = null;
    var $_db = null;
    var $student = null;

    protected $synthesis;

    function __construct($config = array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');

        $this->_user = JFactory::getSession()->get('emundusUser');
        $this->_db = JFactory::getDbo();
        parent::__construct($config);
    }

    function display($tpl = null) {
        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
        }

        $app = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_emundus');

        $jinput = $app->input;
        $fnum 	= $jinput->getString('fnum', null);
        $layout = $jinput->getString('layout', 0);
        $Itemid = $jinput->get('Itemid', 0);

        $m_profiles = new EmundusModelProfile();
        $fnumInfos = $m_profiles->getFnumDetails($fnum);

        $m_application = $this->getModel('Application');

        $expire = time()+60*60*24*30;
        setcookie("application_itemid", $jinput->getString('id', 0), $expire);

        if (EmundusHelperAccess::asAccessAction(1, 'r', $this->_user->id, $fnum)) {
            switch ($layout) {
                case "synthesis":
                    $synthesis = new stdClass();
                    $program = $m_application->getProgramSynthesis($fnumInfos['campaign_id']);
                    if (!empty($program->synthesis)) {
                        $campaignInfo = $m_application->getUserCampaigns($fnumInfos['applicant_id'], $fnumInfos['campaign_id']);
                        $m_email = new EmundusModelEmails();
                        $tag = array(
                            'FNUM' => $fnum,
                            'CAMPAIGN_NAME' => $fnumInfos['label'],
                            'CAMPAIGN_LABEL' => $fnumInfos['label'],
                            'APPLICATION_STATUS' => $fnumInfos['value'],
                            'APPLICATION_TAGS' => $fnum,
                            'APPLICATION_PROGRESS' => $fnumInfos['form_progress'],
                            'ATTACHMENT_PROGRESS' => $fnumInfos['attachment_progress']
                        );

                        $tags = $m_email->setTags(intval($fnumInfos['applicant_id']), $tag, $fnum, '', $program->synthesis);

                        $synthesis->program = $program;
                        $synthesis->camp = $campaignInfo;
                        $synthesis->fnum = $fnum;
                        $synthesis->block = preg_replace($tags['patterns'], $tags['replacements'], $program->synthesis);
                        $synthesis->block = $m_email->setTagsFabrik($synthesis->block, array($fnum));
                    }
                    $this->assignRef('synthesis', $synthesis);
                    break;

                case 'assoc_files':
                    $show_related_files = $params->get('show_related_files', 0);

                    if ($show_related_files || EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id) || EmundusHelperAccess::asManagerAccessLevel($this->_user->id)) {
                        $campaignInfo = $m_application->getUserCampaigns($fnumInfos['applicant_id']);
                    } else {
                        $campaignInfo = $m_application->getCampaignByFnum($fnum);
                    }

                    $assoc_files = new stdClass();
                    $assoc_files->camps = $campaignInfo;
                    $assoc_files->fnumInfos = $fnumInfos;
                    $assoc_files->fnum = $fnum;
                    $this->assignRef('assoc_files', $assoc_files);

                    break;

                case 'attachment':
                    if (EmundusHelperAccess::asAccessAction(4, 'r', $this->_user->id, $fnum)) {
                        EmundusModelLogs::log($this->_user->id, (int)substr($fnum, -7), $fnum, 4, 'r', 'COM_EMUNDUS_ACCESS_ATTACHMENT_READ');
                        $expert_document_id = $params->get('expert_document_id', '36');

                        $app = JFactory::getApplication();
                        $jinput = $app->input;
                        $search = $jinput->getString('search');

                        $m_files = new EmundusModelFiles;

                        $userAttachments = $m_application->getUserAttachmentsByFnum($fnum, $search);
                        $attachmentsProgress = $m_application->getAttachmentsProgress($fnum);
                        $nameCategory = $m_files->getAttachmentCategories();

                        $this->assignRef('userAttachments', $userAttachments);
                        $this->assignRef('student_id', $fnumInfos['applicant_id']);
                        $this->assignRef('attachmentsProgress', $attachmentsProgress);
                        $this->assignRef('expert_document_id', $expert_document_id);
                        $this->assignRef('nameCategory', $nameCategory);

                    } else {
                        echo JText::_("COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS");
                        exit();
                    }
                    break;

                case 'assessment':
                    if (EmundusHelperAccess::asAccessAction(1, 'r', $this->_user->id, $fnum)) {
                        $student = JFactory::getUser(intval($fnumInfos['applicant_id']));
                        $this->assignRef('campaign_id', $fnumInfos['campaign_id']);
                        $this->assignRef('student', $student);
                    } else {
                        echo JText::_("COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS");
                        exit();
                    }
                    break;

                case 'evaluation':
                    if (EmundusHelperAccess::asAccessAction(5, 'c', $this->_user->id, $fnum) || EmundusHelperAccess::asAccessAction(5, 'r', $this->_user->id, $fnum) || EmundusHelperAccess::asAccessAction(5, 'u', $this->_user->id, $fnum)) {
                        $params = JComponentHelper::getParams('com_emundus');
                        $can_copy_evaluations = $params->get('can_copy_evaluations', 0);
                        $multi_eval = $params->get('multi_eval', 0);

                        $this->student = JFactory::getUser(intval($fnumInfos['applicant_id']));
                        $m_evaluation = new EmundusModelEvaluation();

                        // get evaluation form ID
                        $formid = $m_evaluation->getEvaluationFormByProgramme($fnumInfos['training']);

                        $message = 'COM_EMUNDUS_EVALUATIONS_NO_EVALUATION_FORM_SET';
                        if (!empty($formid)) {
                            $evaluation = $m_evaluation->getEvaluationUrl($fnum,$formid);
                            $message = $evaluation['message'];
                            $this->url_form = $evaluation['url'];
                            $this->url_evaluation = JURI::base().'index.php?option=com_emundus&view=evaluation&layout=data&format=raw&Itemid='.$Itemid.'&cfnum='.$fnum;
                        } else {
                            $this->url_evaluation = '';
                            $this->url_form = '';
                        }

                        // This means that a previous evaluation of this user on any other programme can be copied to this one
                        if ($can_copy_evaluations == 1) {

                            if (EmundusHelperAccess::asAccessAction(1, 'u', JFactory::getUser()->id, $fnum) || EmundusHelperAccess::asAccessAction(5, 'c', JFactory::getUser()->id, $fnum)) {

                                $m_evaluation 	= new EmundusModelEvaluation;
                                $h_files 		= new EmundusHelperFiles;
                                $eval_fnums 	= array();
                                $evals 			= array();

                                // Gets all evaluations of this student
                                $user_evaluations = $m_evaluation->getEvaluationsByStudent($this->student->id);

                                foreach ($user_evaluations as $ue) {
                                    $eval_fnums[] = $ue->fnum;
                                }

                                // Evaluation fnums need to be made unique as it is possible to have multiple evals on one fnum by different people
                                $eval_fnums = array_unique($eval_fnums);

                                // Gets a title for the dropdown menu that is sorted like ['fnum']->['evaluator_id']->title
                                foreach ($eval_fnums as $eval_fnum) {
                                    $evals[] = $h_files->getEvaluation('simple',$eval_fnum);
                                }

                                $this->assignRef('evaluation_select', $evals);

                            } else {
                                echo JText::_("COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS");
                                exit();
                            }
                        }

                        $this->campaign_id = $fnumInfos['campaign_id'];
                        $this->assignRef('fnum', $fnum);
                        $this->assignRef('message', $message);

                        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
                        $mFile = new EmundusModelFiles();
                        $applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

                        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
                        EmundusModelLogs::log(JFactory::getUser()->id, $applicant_id, $fnum, 5, 'r', 'COM_EMUNDUS_ACCESS_EVALUATION_READ');
                    } else {
                        echo JText::_("COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS");
                        exit();
                    }
                    break;

                case 'decision':
                    if (EmundusHelperAccess::asAccessAction(29, 'r', $this->_user->id, $fnum)) {

                        // No call to EmundusModelLogs::log() because the logging in handled in a Fabrik script on form load.

                        $student = JFactory::getUser(intval($fnumInfos['applicant_id']));
                        $m_evaluation = new EmundusModelEvaluation();
                        $myEval = $m_evaluation->getDecisionFnum($fnum);

                        // get evaluation form ID
                        $formid = $m_evaluation->getDecisionFormByProgramme($fnumInfos['training']);

                        $url_form = '';
                        if (!empty($formid)) {
                            if (count($myEval) > 0) {

                                if (EmundusHelperAccess::asAccessAction(29, 'u', $this->_user->id, $fnum))
                                    $url_form = 'index.php?option=com_fabrik&c=form&view=form&formid='.$formid.'&rowid='.$myEval[0]->id.'&jos_emundus_final_grade___student_id[value]='.$student->id.'&jos_emundus_final_grade___campaign_id[value]='.$fnumInfos['campaign_id'].'&jos_emundus_final_grade___fnum[value]='.$fnum.'&student_id='.$student->id.'&tmpl=component&iframe=1';
                                elseif (EmundusHelperAccess::asAccessAction(29, 'r', $this->_user->id, $fnum))
                                    $url_form = 'index.php?option=com_fabrik&c=form&view=details&formid='.$formid.'&rowid='.$myEval[0]->id.'&jos_emundus_final_grade___student_id[value]='.$student->id.'&jos_emundus_final_grade___campaign_id[value]='.$fnumInfos['campaign_id'].'&jos_emundus_final_grade___fnum[value]='.$fnum.'&student_id='.$student->id.'&tmpl=component&iframe=1';

                            } else {

                                if (EmundusHelperAccess::asAccessAction(29, 'c', $this->_user->id, $fnum))
                                    $url_form = 'index.php?option=com_fabrik&c=form&view=form&formid='.$formid.'&rowid=&jos_emundus_final_grade___student_id[value]='.$student->id.'&jos_emundus_final_grade___campaign_id[value]='.$fnumInfos['campaign_id'].'&jos_emundus_final_grade___fnum[value]='.$fnum.'&student_id='.$student->id.'&tmpl=component&iframe=1';
                                elseif (EmundusHelperAccess::asAccessAction(29, 'r', $this->_user->id, $fnum))
                                    $url_form = 'index.php?option=com_fabrik&c=form&view=details&formid='.$formid.'&rowid='.$myEval[0]->id.'&jos_emundus_final_grade___student_id[value]='.$student->id.'&jos_emundus_final_grade___campaign_id[value]='.$fnumInfos['campaign_id'].'&jos_emundus_final_grade___fnum[value]='.$fnum.'&student_id='.$student->id.'&tmpl=component&iframe=1';

                            }

                            // get evaluation form ID
                            $formid_eval = $m_evaluation->getEvaluationFormByProgramme($fnumInfos['training']);
                            if (!empty($formid_eval)) {
                                $this->url_evaluation = JURI::base().'index.php?option=com_emundus&view=evaluation&layout=data&format=raw&Itemid='.$Itemid.'&cfnum='.$fnum;
                            }
                        }

                        $this->assignRef('campaign_id', $fnumInfos['campaign_id']);
                        $this->assignRef('student', $student);
                        $this->assignRef('fnum', $fnum);
                        $this->assignRef('url_form', $url_form);
                        $this->assignRef('$formid', $formid);

                        # ADD 29R HERE
                        # get FNUM INFO
                        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
                        $mFile = new EmundusModelFiles();
                        $applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

                        // TRACK THE LOGS
                        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
                        EmundusModelLogs::log(JFactory::getUser()->id, $applicant_id, $fnum, 29, 'r', 'COM_EMUNDUS_DECISION_READ');

                    } else {
                        echo JText::_("COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS");
                        exit();
                    }
                    break;

                case 'comment':
                    if (EmundusHelperAccess::asAccessAction(10, 'r', $this->_user->id, $fnum)) {

                        EmundusModelLogs::log($this->_user->id, (int)substr($fnum, -7), $fnum, 10, 'r', 'COM_EMUNDUS_ACCESS_COMMENT_FILE_READ');

                        $userComments = $m_application->getFileComments($fnum);

                        foreach ($userComments as $key => $comment) {
                            $comment->date = EmundusHelperDate::displayDate($comment->date, 'DATE_FORMAT_LC2');
                        }

                        $this->assignRef('userComments', $userComments);
                        $this->assignRef('fnum', $fnum);

                    } elseif (EmundusHelperAccess::asAccessAction(10, 'c', $this->_user->id, $fnum)) {

                        EmundusModelLogs::log($this->_user->id, (int)substr($fnum, -7), $fnum, 10, 'c', 'COM_EMUNDUS_ACCESS_COMMENT_FILE_CREATE');

                        $userComments = $m_application->getFileOwnComments($fnum,$this->_user->id);

                        foreach ($userComments as $key => $comment) {
                            $comment->date = EmundusHelperDate::displayDate($comment->date, 'DATE_FORMAT_LC2');
                        }

                        $this->assignRef('userComments', $userComments);
                        $this->assignRef('fnum', $fnum);
                    } else{
                        echo JText::_("COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS");
                        exit();
                    }
                    break;

                case 'logs':
                    if (EmundusHelperAccess::asAccessAction(37, 'r', $this->_user->id, $fnum)) {
                        EmundusModelLogs::log($this->_user->id, (int)substr($fnum, -7), $fnum, 37, 'r', 'COM_EMUNDUS_ACCESS_LOGS_READ');
						$m_logs = new EmundusModelLogs();

                        $fileLogs = $m_logs->getActionsOnFnum($fnum, null, null, ["c", "r", "u", "d"]);

                        foreach ($fileLogs as $key => $log) {
                            $log->timestamp = EmundusHelperDate::displayDate($log->timestamp);
                            $log->details = $m_logs->setActionDetails($log->action_id, $log->verb, $log->params);
                        }

                        $this->assignRef('fileLogs', $fileLogs);
                        $this->assignRef('fnum', $fnum);

                    } else{
                        echo JText::_("RESTRICTED_ACCESS");
                        exit();
                    }
                    break;

                case 'tag':
                    if (EmundusHelperAccess::asAccessAction(14, 'r', $this->_user->id, $fnum)) {

                        EmundusModelLogs::log($this->_user->id, (int)substr($fnum, -7), $fnum, 14, 'r', 'COM_EMUNDUS_ACCESS_TAGS_READ');

                        $m_files = new EmundusModelFiles();
                        $tags = $m_files->getTagsByFnum(array($fnum));
                        $alltags = $m_files->getAllTags();
                        $groupedTags = [];
                        foreach ($alltags as $tag) {
                            $groupedTags[$tag["category"]][] = ["id" => $tag["id"],"label" => $tag["label"]];
                        }

                        $this->assignRef('tags', $tags);
                        $this->assignRef('groupedTags', $groupedTags);
                        $this->assignRef('fnum', $fnum);

                    } else {
                        echo JText::_("COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS");
                        exit();
                    }
                    break;

                case 'form':
                    if (EmundusHelperAccess::asAccessAction(1, 'r', $this->_user->id, $fnum)) {

                        EmundusModelLogs::log($this->_user->id, (int)substr($fnum, -7), $fnum, 1, 'r', 'COM_EMUNDUS_ACCESS_FORM_READ');

                        $m_campaign = new EmundusModelCampaign;
	                    $m_user = new EmundusModelUsers;
	                    $applicant = $m_user->getUserById($fnumInfos['applicant_id']);
						if(!isset($applicant[0]->profile_picture) || empty($applicant[0]->profile_picture)){
							$applicant[0]->profile_picture = $m_user->getIdentityPhoto($fnum,$fnumInfos['applicant_id']);
						}

                        /* detect user_id from fnum */
                        $userId = $fnumInfos['applicant_id'];
                        $pid = (isset($fnumInfos['profile_id_form']) && !empty($fnumInfos['profile_id_form']))?$fnumInfos['profile_id_form']:$fnumInfos['profile_id'];

                        $this->assignRef('userid', $userId);

                        /* get all campaigns by user */
                        $campaignsRaw = $m_campaign->getCampaignByFnum($fnum);

                        /* get all profiles (order by step) by campaign */
                        $pidsRaw = $m_profiles->getProfilesIDByCampaign([$campaignsRaw->id],'object');

                        $noPhasePids = array();
                        $hasPhasePids = array();

                        foreach($pidsRaw as $pidRaw) {
                            if($pidRaw->pid === $pid) {
                                $dpid = $pidRaw;
                            }

                            if($pidRaw->phase === null) {
                                if($pidRaw->pid !== $pid) {
                                    $noPhasePids['no_step']['lbl'] = JText::_('COM_EMUNDUS_VIEW_FORM_OTHER_PROFILES');
                                    $noPhasePids['no_step']['data'][] = $pidRaw;
                                }
                            } else {
                                $hasPhasePids[] = $pidRaw;
                            }
                        }

                        $profiles_by_phase = array();

                        /* group profiles by phase */
                        foreach($hasPhasePids as $ppid) {
                            $profiles_by_phase['step_' . $ppid->phase]['lbl'] = $ppid->lbl;
                            $profiles_by_phase['step_' . $ppid->phase]['data'][] = $ppid;
                        }

                        $pids = array_merge($profiles_by_phase, $noPhasePids);


                        /* serialize $pids to json format */
                        $json = json_encode($pids);
                        $this->assignRef('pids', $json);

                        $this->assignRef('defaultpid', $dpid);

                        $formsProgress = $m_application->getFormsProgress($fnum);
                        $this->assignRef('formsProgress', $formsProgress);

                        $forms = $m_application->getForms(intval($fnumInfos['applicant_id']), $fnum, $pid);
                        $this->assignRef('forms', $forms);
                        $this->assignRef('applicant', $applicant[0]);

                    } else {
                        echo JText::_("COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS");
                        exit();
                    }
                    break;

                case 'share':
                    if (EmundusHelperAccess::asAccessAction(11, 'r', $this->_user->id, $fnum)) {

                        $access = $m_application->getAccessFnum($fnum);
                        $defaultActions = $m_application->getActions();
                        $canUpdateAccess = EmundusHelperAccess::asAccessAction(11, 'u', JFactory::getUser()->id, $fnum);
                        $this->assignRef('access', $access);
                        $this->assignRef('canUpdate', $canUpdateAccess);
                        $this->assignRef('defaultActions', $defaultActions);

                    } else {
                        echo JText::_("COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS");
                        exit();
                    }
                    break;

                case 'mail':
                    // This view gets a recap of all the emails sent to the User by the platform, requires applicant_email read rights.
                    if (EmundusHelperAccess::asAccessAction(9, 'r', $this->_user->id, $fnum)) {

                        EmundusModelLogs::log($this->_user->id, (int)substr($fnum, -7), $fnum, 9, 'r', 'COM_EMUNDUS_ACCESS_MAIL_APPLICANT_READ');

                        $m_emails = new EmundusModelEmails();
                        $messages = $m_emails->get_messages_to_from_user(intval($fnumInfos['applicant_id']));
                        $this->assignRef('messages', $messages);

                    } else {
                        echo JText::_("COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS");
                        exit();
                    }
                    break;

                case 'admission':
                    if (EmundusHelperAccess::asAccessAction(32, 'r', $this->_user->id, $fnum)) {
                        $student = JFactory::getUser(intval($fnumInfos['applicant_id']));

                        // No call to EmundusModelLogs::log() because the logging in handled in a Fabrik script on form load.

                        $m_admission = new EmundusModelAdmission();
                        $m_application = new EmundusModelApplication();
                        $m_files = new EmundusModelFiles();


                        $myAdmission_form_id = $m_files->getAdmissionFormidByFnum($fnum);
                        // get admission form ID
                        $admission_form = $m_admission->getAdmissionFormByProgramme($fnumInfos['training']);

                        if (!empty($admission_form)) {
                            $admission_row_id = $m_admission->getAdmissionId($admission_form->db_table_name,$fnum);
                        }

                        if (empty($myAdmission_form_id)) {
                            $html_form = '<p>'.JText::_('COM_EMUNDUS_NO_USER_ADMISSION_FORM').'</p>';
                        } else {
                            $html_form = $m_application->getFormByFabrikFormID($myAdmission_form_id, $student->id, $fnum);
                        }

                        $url_form = '';
                        if (!empty($admission_form->form_id)) {
                            if (EmundusHelperAccess::asAccessAction(32, 'u', $this->_user->id, $fnum)) {
                                $url_form = 'index.php?option=com_fabrik&c=form&view=form&formid='.$admission_form->form_id.'&rowid='.$admission_row_id.'&'.$admission_form->db_table_name.'___student_id[value]='.$student->id.'&'.$admission_form->db_table_name.'___campaign_id[value]='.$fnumInfos['campaign_id'].'&'.$admission_form->db_table_name.'___fnum[value]='.$fnum.'&student_id='.$student->id.'&tmpl=component&iframe=1';
                            } elseif (EmundusHelperAccess::asAccessAction(32, 'r', $this->_user->id, $fnum)) {
                                $url_form = 'index.php?option=com_fabrik&c=form&view=details&formid='.$admission_form->form_id.'&rowid='.$admission_row_id.'&'.$admission_form->db_table_name.'___student_id[value]='.$student->id.'&'.$admission_form->db_table_name.'___campaign_id[value]='.$fnumInfos['campaign_id'].'&'.$admission_form->db_table_name.'___fnum[value]='.$fnum.'&student_id='.$student->id.'&tmpl=component&iframe=1';
                            } elseif (EmundusHelperAccess::asAccessAction(32, 'c', $this->_user->id, $fnum)) {
                                $url_form = 'index.php?option=com_fabrik&c=form&view=form&formid='.$admission_form->form_id.'&rowid=&'.$admission_form->db_table_name.'___student_id[value]='.$student->id.'&'.$admission_form->db_table_name.'___campaign_id[value]='.$fnumInfos['campaign_id'].'&'.$admission_form->db_table_name.'___fnum[value]='.$fnum.'&student_id='.$student->id.'&tmpl=component&iframe=1';
                            }
                        }

                        $this->assignRef('campaign_id', $fnumInfos['campaign_id']);
                        $this->assignRef('student', $student);
                        $this->assignRef('fnum', $fnum);
                        $this->assignRef('html_form',$html_form);
                        $this->assignRef('url_form', $url_form);
                        $this->assignRef('$formid', $admission_form->form_id);

                        # ADD 32R HERE
                        # get FNUM INFO
                        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
                        $mFile = new EmundusModelFiles();
                        $applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

                        // TRACK THE LOGS
                        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
                        EmundusModelLogs::log(JFactory::getUser()->id, $applicant_id, $fnum, 32, 'r', 'COM_EMUNDUS_ADMISSION_READ');

                    } else {
                        echo JText::_("COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS");
                        exit();
                    }
                    break;

                case 'interview':
                    if (EmundusHelperAccess::asAccessAction(34, 'r', $this->_user->id, $fnum)) {

                        // No call to EmundusModelLogs::log() because the logging in handled in a Fabrik script on form load.

                        $params = JComponentHelper::getParams('com_emundus');
                        $multi_eval = $params->get('multi_eval', 0);

                        $this->student = JFactory::getUser(intval($fnumInfos['applicant_id']));

                        $m_interview = new EmundusModelInterview();
                        $myEval = $m_interview->getEvaluationsFnumUser($fnum, $this->_user->id);
                        $evaluations = $m_interview->getEvaluationsByFnum($fnum);

                        // get evaluation form ID
                        $formid = $m_interview->getInterviewFormByProgramme($fnumInfos['training']);


                        if (!empty($formid)) {

                            if (count($myEval) > 0) {

                                if (EmundusHelperAccess::asAccessAction(34, 'u', $this->_user->id, $fnum))
                                    $this->url_form = 'index.php?option=com_fabrik&c=form&view=form&formid='.$formid.'&rowid='.$myEval[0]->id.'&student_id='.$this->student->id.'&tmpl=component&iframe=1';
                                elseif (EmundusHelperAccess::asAccessAction(34, 'r', $this->_user->id, $fnum))
                                    $this->url_form = 'index.php?option=com_fabrik&c=form&view=details&formid='.$formid.'&rowid='.$myEval[0]->id.'&jos_emundus_evaluations___student_id[value]='.$this->student->id.'&jos_emundus_evaluations___campaign_id[value]='.$fnumInfos['campaign_id'].'&jos_emundus_evaluations___fnum[value]='.$fnum.'&student_id='.$this->student->id.'&tmpl=component&iframe=1';

                            } else {

                                if (EmundusHelperAccess::asAccessAction(34, 'c', $this->_user->id, $fnum)) {

                                    if ($multi_eval == 0 && count($evaluations) > 0 && EmundusHelperAccess::asAccessAction(34, 'u', $this->_user->id, $fnum)) {
                                        $this->url_form = 'index.php?option=com_fabrik&c=form&view=form&formid='.$formid.'&rowid='.$evaluations[0]->id.'&student_id='.$this->student->id.'&tmpl=component&iframe=1';
                                    } else {
                                        $this->url_form = 'index.php?option=com_fabrik&c=form&view=form&formid='.$formid.'&rowid=&jos_emundus_evaluations___student_id[value]='.$this->student->id.'&jos_emundus_evaluations___campaign_id[value]='.$fnumInfos['campaign_id'].'&jos_emundus_evaluations___fnum[value]='.$fnum.'&student_id='.$this->student->id.'&tmpl=component&iframe=1';
                                    }

                                } elseif (EmundusHelperAccess::asAccessAction(34, 'r', $this->_user->id, $fnum)) {
                                    $this->url_form = 'index.php?option=com_fabrik&c=form&view=details&formid='.$formid.'&rowid='.$evaluations[0]->id.'&jos_emundus_evaluations___student_id[value]='.$this->student->id.'&jos_emundus_evaluations___campaign_id[value]='.$fnumInfos['campaign_id'].'&jos_emundus_evaluations___fnum[value]='.$fnum.'&student_id='.$this->student->id.'&tmpl=component&iframe=1';
                                }
                            }

                            if (!empty($formid))
                                $this->url_evaluation = JURI::base().'index.php?option=com_emundus&view=evaluation&layout=data&format=raw&Itemid='.$Itemid.'&cfnum='.$fnum;

                        } else {
                            $this->url_evaluation = '';
                            $this->url_form = '';
                        }



                        $this->campaign_id = $fnumInfos['campaign_id'];
                        $this->assignRef('fnum', $fnum);
                    } else {
                        echo JText::_("COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS");
                        exit();
                    }
                    break;

            }

            $this->assignRef('_user', $this->_user);
            $this->assignRef('fnum', $fnum);
            $this->assignRef('sid', $fnumInfos['applicant_id']);

            parent::display($tpl);

        } else echo JText::_("COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS");
    }
}

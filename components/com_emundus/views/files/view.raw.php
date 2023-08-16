<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
/**
 * Created by eMundus.
 * User: brivalland
 * Date: 23/05/14
 * Time: 11:39
 * @package        Joomla
 * @subpackage    eMundus
 * @link        http://www.emundus.fr
 * @copyright    Copyright (C) 2006 eMundus. All rights reserved.
 * @license        GNU/GPL
 * @author        Benjamin Rivalland
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
class EmundusViewFiles extends JViewLegacy
{
	protected $itemId;
	protected $cfnum;

	protected JPagination $pagination;
	protected string $pageNavigation;

	protected array $lists;
	protected array $actions;
	protected array $users;
	protected $datas;
	protected string $delayAct;
	protected string $submitForm;
	protected array $accessObj;
	protected array $colsSup;

	protected array $groups;
	protected array $groupFnum;
	protected array $evalFnum;
	protected array $evals;
	protected object $actions_evaluators;
	protected string $hide_default_actions;

	protected array $items;
	protected string $display;
	protected string $fnum;

	protected array $code;
	protected array $fnum_assoc;
	protected string $filters;

	protected array $docs;
	protected array $prgs;
	protected string $fnums;

	public function __construct($config = array())
	{
		require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'list.php');
		require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'emails.php');
		require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'export.php');
		require_once(JPATH_COMPONENT . DS . 'models' . DS . 'users.php');
		require_once(JPATH_COMPONENT . DS . 'models' . DS . 'evaluation.php');                   require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');


        parent::__construct($config);
	}

	/** @noinspection PhpInconsistentReturnPointsInspection */
	public function display($tpl = null)
	{
		$current_user = JFactory::getUser();
		$h_files = new EmundusHelperFiles;

		if (!EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
			die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
		}

		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_emundus');
		$default_actions = $params->get('default_actions', 0);
		$hide_default_actions = $params->get('hide_default_actions', 0);

		$this->itemId = $app->input->getInt('Itemid', null);
		$this->cfnum = $app->input->getString('cfnum', null);
		$layout = $app->input->getString('layout', null);

		$m_files = new EmundusModelFiles();
		$h_files->setMenuFilter();

		switch ($layout) {
			case 'access':
				$fnums = $app->input->getString('users', null);
				$fnums_obj = (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

				if (@$fnums_obj[0] == 'all') {
					$fnums = $m_files->getAllFnums();
				} else {
					$fnums = array();
					foreach ($fnums_obj as $key => $value) {
						$fnums[] = @$value->fnum;
					}
				}

				$groupFnum = $m_files->getGroupsByFnums($fnums);
				$evalFnum = $m_files->getAssessorsByFnums($fnums);
				$users = $m_files->getFnumsInfos($fnums);
				$evalGroups = $m_files->getEvalGroups();
				$actions = $m_files->getAllActions();
				$actions_evaluators = json_decode($default_actions);

				$this->groups = $evalGroups['groups'];
				$this->groupFnum = $groupFnum;
				$this->evalFnum = $evalFnum;
				$this->users = $users;
				$this->evals = $evalGroups['users'];
				$this->actions = $actions;
				$this->actions_evaluators = $actions_evaluators;
				$this->hide_default_actions = $hide_default_actions;
				break;

			case 'menuactions':
				$fnum = $app->input->getString("fnum", "0");

				$display = $app->input->getString('display', 'none');
				$menu = @JFactory::getApplication()->getMenu();
				$current_menu = $menu->getActive();

				$Itemid = $app->input->getInt('Itemid', $current_menu->id);

				if (isset($current_menu) && !empty($current_menu)) {

					$params = $menu->getParams($Itemid);

					if ($fnum === "0") {
						$items = $h_files->getMenuList($params);
					} else {
						$items = $h_files->getMenuList($params, $fnum);
					}

					$this->items = $items;
					$this->display = $display;
					$this->fnum = $fnum;
				} else {
					echo JText::_('ERROR_MENU_ID_NOT_FOUND');
					return false;
				}
				break;

			case 'filters':
				$m_user = new EmundusModelUsers();

				$m_files->code = $m_user->getUserGroupsProgrammeAssoc($current_user->id);

				// get all fnums manually associated to user
				$groups = $m_user->getUserGroups($current_user->id, 'Column');
				$fnum_assoc_to_groups = $m_user->getApplicationsAssocToGroups($groups);
				$fnum_assoc = $m_user->getApplicantsAssoc($current_user->id);
				$m_files->fnum_assoc = array_merge($fnum_assoc_to_groups, $fnum_assoc);

				$this->code = $m_files->code;
				$this->fnum_assoc = $m_files->fnum_assoc;

				$filters = $h_files->resetFilter();
				$this->filters = $filters;
				break;

            case 'docs':
                $fnumsObj = $app->input->getString('fnums', "");

                if (!empty($fnumsObj)) {
                    $fnums = array();
	                if ($fnumsObj == 'all') {
		                $fnums = $m_files->getAllFnums();
	                }
					else
	                {
		                $fnumsObj = json_decode(stripslashes($fnumsObj), false, 512, JSON_BIGINT_AS_STRING);

		                foreach ($fnumsObj as $fObj)
		                {
			                if (EmundusHelperAccess::asAccessAction(27, 'c', JFactory::getUser()->id, $fObj->fnum))
			                {
				                $fnums[] = $fObj->fnum;
			                }
		                }
	                }
                    if (!empty($fnums)) {
                        $prgs = $m_files->getProgByFnums($fnums);
                        $docs = $m_files->getDocsByProg(key($prgs));
                    } else {
                        echo JText::_('ACCESS_DENIED');
                        exit();
                    }

                    $this->assignRef('docs', $docs);
                    $this->assignRef('prgs', $prgs);
                    $fnums_array = implode(',', $fnums);
                    $this->assignRef('fnums', $fnums_array);
                } else {
                    echo JText::_('COM_EMUNDUS_ONBOARD_NOFILES');
                    exit();
                }

                break;

			// Get list of application files
			default:
				$menu = $app->getMenu();
				$current_menu = $menu->getActive();

				$Itemid = $app->input->getInt('Itemid', $current_menu->id);
				$menu_params = $menu->getParams($Itemid);

				$columnSupl = explode(',', $menu_params->get('em_other_columns'));

				$m_user = new EmundusModelUsers();

				$m_files->code = $m_user->getUserGroupsProgrammeAssoc($current_user->id);

				// get all fnums manually associated to user
				$groups = $m_user->getUserGroups($current_user->id, 'Column');
				$fnum_assoc_to_groups = $m_user->getApplicationsAssocToGroups($groups);
				$fnum_assoc = $m_user->getApplicantsAssoc($current_user->id);
				$m_files->fnum_assoc = array_merge($fnum_assoc_to_groups, $fnum_assoc);

				$this->assignRef('code', $m_files->code);
				$this->assignRef('fnum_assoc', $m_files->fnum_assoc);

				// get applications files
				$users = $m_files->getUsers();

				// Get elements from model and proccess them to get an easy to use array containing the element type
				$elements = $m_files->getElementsVar();
				foreach ($elements as $elt) {
					$elt_name = $elt->tab_name . "___" . $elt->element_name;
					$eltarr[$elt_name] = [
						"plugin" 	=> $elt->element_plugin,
						"tab_name" 	=> $elt->tab_name,
						"params"  	=> $elt->element_attribs,
						"fabrik_id" => $elt->id
					];
				}

				if (isset($eltarr)) {
					$elements = $eltarr;
				}

				// Do not display photos unless specified in params
				$displayPhoto = false;

				$defaultElements = $this->get('DefaultElements');
			    $data = array(array('check' => '#', 'name' => JText::_('COM_EMUNDUS_FILES_APPLICATION_FILES'), 'status' => JText::_('COM_EMUNDUS_STATUS')));
				$fl = array();
				if (count($defaultElements) > 0) {
					foreach ($defaultElements as $elt) {
						$fl[$elt->tab_name . '___' . $elt->element_name] = $elt->element_label;
					}
				}

				$data[0] = array_merge($data[0], $fl);
				$fnumArray = [];
				$objAccess = [];
				$colsSup = [];

				if (!empty($users)) {
					$i = 1;
					$taggedFile = array();
					foreach ($columnSupl as $col) {
						$col = explode('.', $col);
						switch ($col[0]) {
							case 'evaluators':
							    $data[0]['EVALUATORS'] = JText::_('COM_EMUNDUS_EVALUATION_EVALUATORS');
								$colsSup['evaluators'] = $h_files->createEvaluatorList($col[1], $m_files);
								break;
							case 'overall':
								$data[0]['overall'] = JText::_('COM_EMUNDUS_EVALUATIONS_OVERALL');
								$colsSup['overall'] = array();
								break;
							case 'tags':
								$taggedFile = $m_files->getTaggedFile();
                                $data[0]['eta.id_tag'] = JText::_('COM_EMUNDUS_TAGS');
								$colsSup['id_tag'] = array();
								break;
							case 'access':
								$data[0]['access'] = JText::_('COM_EMUNDUS_ASSOCIATED_TO');
								$colsSup['access'] = array();
								break;
							case 'photos':
								$displayPhoto = true;
								break;
							case 'form_progress':
								$data[0]['form_progress'] = JText::_('COM_EMUNDUS_FORM_PROGRESS');
								$colsSup['form_progress'] = array();
								break;
							case 'attachment_progress':
								$data[0]['attachment_progress'] = JText::_('COM_EMUNDUS_ATTACHMENT_PROGRESS');
								$colsSup['attachment_progress'] = array();
								break;
                            case 'unread_messages':
                                $data[0]['unread_messages'] = JText::_('COM_EMUNDUS_UNREAD_MESSAGES');
                                $colsSup['unread_messages'] = array();
                                break;
                            case 'commentaire':
                                $data[0]['commentaire'] = JText::_('COM_EMUNDUS_COMMENTAIRE');
                                $colsSup['commentaire'] = array();
                                break;
                            case 'module':
								// Get every module without a positon.
								$mod_emundus_custom = array();
								foreach (JModuleHelper::getModules('') as $module) {
									if ($module->module == 'mod_emundus_custom' && ($module->menuid == 0 || $module->menuid == $app->input->get('Itemid', null))) {
										$mod_emundus_custom[$module->title] = $module->content;
										$data[0][$module->title] = JText::_($module->title);
										$colsSup[$module->title] = array();
									}
								}
								break;
							default:
								break;
						}
					}

					foreach ($users as $user) {
						$usObj = new stdClass();
						$usObj->val = 'X';
						$fnumArray[] = $user['fnum'];
						$line = array('check' => $usObj);

						if (array_key_exists($user['fnum'], $taggedFile)) {
							$class = $taggedFile[$user['fnum']]['class'];
							$usObj->class = $taggedFile[$user['fnum']]['class'];
						} else {
							$class = null;
							$usObj->class = null;
						}

						foreach ($user as  $key => $value) {
							$userObj = new stdClass();

							if ($key == 'fnum') {
								$userObj->val = $value;
								$userObj->class = $class;
								$userObj->type = 'fnum';
								if ($displayPhoto) {
									$userObj->photo = $h_files->getPhotos($value);
								}
                                $userObj->user = JFactory::getUser((int)$user['applicant_id']);
								$userObj->user->name = $user['name'];
								$line['fnum'] = $userObj;
							} elseif ($key == 'name' || $key == 'status_class' || $key == 'step' || $key == 'applicant_id' || $key == 'campaign_id' || $key == 'unread_messages' || $key == 'commentaire') {
								continue;
							} elseif (isset($elements) && in_array($key, array_keys($elements))) {
								$userObj->val 			= $value;
								$userObj->type 			= $elements[$key]['plugin'];
								$userObj->status_class 	= $user['status_class'];
								$userObj->id 			= $elements[$key]['fabrik_id'];
								$userObj->params 		= $elements[$key]['params'];
								$line[$key] 			= $userObj;

								// Radiobuttons are a strange beast, we need to get all of the values
								if ($userObj->type == 'radiobutton') {
									$params = json_decode($userObj->params);
									$userObj->radio = array_combine($params->sub_options->sub_labels, $params->sub_options->sub_values);
								}
							} else {
								$userObj->val = $value;
								$userObj->type = 'text';
								$userObj->status_class = $user['status_class'];
								$line[$key] = $userObj;
							}
						}

						if (count(@$colsSup) > 0) {
							foreach ($colsSup as $key => $obj) {
								$userObj = new stdClass();
								if (!is_null($obj)) {
									if (array_key_exists($user['fnum'], $obj)) {
										$userObj->val = $obj[$user['fnum']];
										$userObj->type = 'html';
										$userObj->fnum = $user['fnum'];
										$line[JText::_(strtoupper($key))] = $userObj;
									} else {
										$userObj->val = '';
										$userObj->type = 'html';
										$line[$key] = $userObj;
									}
								} elseif ($key === 'overall' || $key === 'id_tag' || $key === 'access' || (!empty($mod_emundus_custom) && array_key_exists($key, $mod_emundus_custom))) {
									$line[$key] = "";
								}
							}
						}
						$data[$line['fnum']->val . '-' . $i] = $line;
						$i++;
					}

					if (isset($colsSup['overall'])) {
						$m_evaluation = new EmundusModelEvaluation;
						$colsSup['overall'] = $m_evaluation->getEvaluationAverageByFnum($fnumArray);
					}

					if (isset($colsSup['id_tag'])) {
						$tags = $m_files->getTagsByFnum($fnumArray);
						$colsSup['id_tag'] = $h_files->createTagsList($tags);
					}

					if (isset($colsSup['access'])) {
						$objAccess = $m_files->getAccessorByFnums($fnumArray);
					}

					if (isset($colsSup['form_progress'])) {
						$forms_progress = $m_files->getFormProgress($fnumArray);
						$colsSup['form_progress'] = $h_files->createFormProgressList($forms_progress);
					}

					if (isset($colsSup['attachment_progress'])) {
						$attachments_progress = $m_files->getAttachmentProgress($fnumArray);
						$colsSup['attachment_progress'] = $h_files->createAttachmentProgressList($attachments_progress);
                    }

                    if (isset($colsSup['unread_messages'])) {
                        $unread_messages = array();
                        $unread_messages[] = $m_files->getUnreadMessages();
                        $unread_messages = $h_files->createUnreadMessageList($unread_messages[0]);

                        $keys = array_keys($unread_messages);
                        natsort($keys);

                        foreach ($keys as $k) {
                            $colsSup['unread_messages'][$k] = $unread_messages[$k];
                        }
                    }

                    if(isset($colsSup['commentaire'])) {
                        foreach($fnumArray as $fnum) {
                            $notifications_comments = sizeof($m_files->getCommentsByFnum([$fnum]));
                            $colsSup['commentaire'][$fnum] = '<p class="messenger__notifications_counter">'. $notifications_comments .'</p> ';
                        }
                    }

					if (!empty($mod_emundus_custom)) {
						foreach ($mod_emundus_custom as $key => $module) {
							if (isset($colsSup[$key])) {
								$colsSup[$key] = $h_files->createHTMLList($module, $fnumArray);
							}
						}
					}
				} else {
				    $data = JText::_('COM_EMUNDUS_NO_RESULT');
				}

				/* Get the values from the state object that were inserted in the model's construct function */
				$lists['order_dir'] = JFactory::getSession()->get('filter_order_Dir');
				$lists['order'] = JFactory::getSession()->get('filter_order');
				$this->lists = $lists;
				$this->pagination = $m_files->getPagination();
				$this->pageNavigation = $m_files->getPageNavigation();
				$this->users = $users;
				$this->datas = $data;

				$this->submitForm = EmundusHelperJavascript::onSubmitForm();
				$this->delayAct = EmundusHelperJavascript::delayAct();
				$this->accessObj = $objAccess;
				$this->colsSup = $colsSup;
				break;
		}

		parent::display($tpl);
	}
}

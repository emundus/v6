<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// no direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\User\UserFactoryInterface;

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
class EmundusViewDecision extends JViewLegacy
{
	private $app;
	private $_user;

	protected $itemId;
	protected $actions;
	protected $menuActions;
	protected $display;
	protected $code;
	protected $fnum_assoc;
	protected $filters;
	protected $formid;
	protected $form_url_edit;
	protected $lists;
	protected $pagination;
	protected $pageNavigation;
	protected $accessObj;
	protected $colsSup;
	protected $users;
	protected $data;

	public function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'list.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'emails.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'export.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'filters.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'files.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'users.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');

		$this->app = Factory::getApplication();
		if (version_compare(JVERSION, '4.0', '>')) {
			$this->_user = $this->app->getIdentity();
		}
		else {
			$this->_user = JFactory::getUser();
		}


		parent::__construct($config);
	}

	public function display($tpl = null)
	{
		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
		}

		$this->itemId = $this->app->input->getInt('Itemid', null);

		$menu         = $this->app->getMenu();
		$current_menu = $menu->getActive();
		$menu_params  = $menu->getParams($current_menu->id);
		$columnSupl   = explode(',', $menu_params->get('em_other_columns'));

		$jinput = $this->app->input;
		$layout = $jinput->getString('layout', 0);

		$m_files = new EmundusModelFiles();

		switch ($layout) {
			case 'menuactions':
				$this->display = $this->app->input->getString('display', 'none');

				$items   = EmundusHelperFiles::getMenuList($menu_params);
				$actions = $m_files->getAllActions();

				$this->menuActions = array();
				foreach ($items as $item) {
					if (!empty($item->note)) {
						$note = explode('|', $item->note);
						if ($actions[$note[0]][$note[1]] == 1) {
							$actions[$note[0]]['multi'] = $note[2];
							$actions[$note[0]]['grud']  = $note[1];
							$item->action               = $actions[$note[0]];
							$this->menuActions[]        = $item;
						}
					}
					else {
						$this->menuActions[] = $item;
					}
				}
				break;

			case 'filters':
				$m_user = new EmundusModelUsers();

				$m_files->code = $m_user->getUserGroupsProgrammeAssoc($this->_user->id);

				// get all fnums manually associated to user
				$groups               = $m_user->getUserGroups($this->_user->id, 'Column');
				$fnum_assoc_to_groups = $m_user->getApplicationsAssocToGroups($groups);
				$fnum_assoc           = $m_user->getApplicantsAssoc($this->_user->id);
				$m_files->fnum_assoc  = array_merge($fnum_assoc_to_groups, $fnum_assoc);

				$this->code       = $m_files->code;
				$this->fnum_assoc = $m_files->fnum_assoc;

				// reset filter
				$h_files       = new EmundusHelperFiles();
				$this->filters = $h_files->resetFilter();
				break;

			default :
				$jinput = $this->app->input;
				$cfnum  = $jinput->getString('cfnum', null);

				$params                        = ComponentHelper::getParams('com_emundus');
				$evaluators_can_see_other_eval = $params->get('evaluators_can_see_other_eval', 0);

				$m_decision = $this->getModel('Decision');
				$m_users    = new EmundusModelUsers();
				$m_files    = new EmundusModelFiles();
				$h_files    = new EmundusHelperFiles();

				$m_decision->code = $m_users->getUserGroupsProgrammeAssoc($this->_user->id);

				$groups                 = $m_users->getUserGroups($this->_user->id, 'Column');
				$fnum_assoc_to_groups   = $m_users->getApplicationsAssocToGroups($groups);
				$fnum_assoc             = $m_users->getApplicantsAssoc($this->_user->id);
				$m_decision->fnum_assoc = array_merge($fnum_assoc_to_groups, $fnum_assoc);

				$this->code       = $m_decision->code;
				$this->fnum_assoc = $m_decision->fnum_assoc;

				// Do not display photos unless specified in params
				$displayPhoto = false;

				// get applications files
				$this->users = $m_decision->getUsers($cfnum);

				// get evaluation form ID
				$this->formid        = $m_decision->getDecisionFormByProgramme();
				$form_url_view       = 'index.php?option=com_fabrik&c=form&view=details&formid=' . $this->formid . '&tmpl=component&iframe=1&rowid=';
				$this->form_url_edit = 'index.php?option=com_fabrik&c=form&view=form&formid=' . $this->formid . '&tmpl=component&iframe=1&rowid=';

				if (!empty($this->users)) {
					$taggedFile = $m_decision->getTaggedFile();

					// Columns
					$defaultElements = $this->get('DefaultElements');
					$this->data      = array(array('check' => '#', 'name' => JText::_('COM_EMUNDUS_FILES_APPLICATION_FILES'), 'c.status' => JText::_('COM_EMUNDUS_STATUS')));
					$fl              = array();

					// Get eval criterion
					if (count($defaultElements) > 0) {
						foreach ($defaultElements as $key => $elt) {
							$fl[$elt->tab_name . '.' . $elt->element_name] = $elt->element_label;
						}
					}
					$fl['jos_emundus_final_grade.user'] = JText::_('COM_EMUNDUS_DECISION_RECORDED_BY');

					// merge eval criterion on application files
					$this->data[0] = array_merge($this->data[0], $fl);

					$fnumArray = array();

					foreach ($columnSupl as $col) {
						$col = explode('.', $col);

						switch ($col[0]) {
							case 'evaluators':
								$this->data[0]['EVALUATORS'] = JText::_('COM_EMUNDUS_EVALUATION_EVALUATORS');
								$this->colsSup['evaluators'] = @EmundusHelperFiles::createEvaluatorList($col[1], $m_decision);
								break;
							case 'overall':
								$this->data[0]['overall'] = JText::_('COM_EMUNDUS_EVALUATIONS_OVERALL');
								$this->colsSup['overall'] = array();
								break;
							case 'tags':
								$taggedFile                  = $m_decision->getTaggedFile();
								$this->data[0]['eta.id_tag'] = JText::_('COM_EMUNDUS_TAGS');
								$this->colsSup['id_tag']     = array();
								break;
							case 'access':
								$this->data[0]['access'] = JText::_('COM_EMUNDUS_ASSOCIATED_TO');
								$this->colsSup['access'] = array();
								break;
							case 'photos':
								$displayPhoto = true;
								break;
							case 'module':
								// Get every module without a positon.
								$mod_emundus_custom = array();
								foreach (ModuleHelper::getModules('') as $module) {
									if ($module->module == 'mod_emundus_custom' && ($module->menuid == 0 || $module->menuid == $jinput->get('Itemid', null))) {
										$mod_emundus_custom[$module->title] = $module->content;
										$this->data[0][$module->title]      = JText::_($module->title);
										$this->colsSup[$module->title]      = array();
									}
								}
								break;
							default:
								break;
						}
					}

					$i = 0;
					foreach ($this->users as $user) {
						$usObj       = new stdClass();
						$usObj->val  = 'X';
						$fnumArray[] = $user['fnum'];
						$line        = array('check' => $usObj);

						if (array_key_exists($user['fnum'], $taggedFile)) {

							$class        = $taggedFile[$user['fnum']]['class'];
							$usObj->class = $taggedFile[$user['fnum']]['class'];

						}
						else {
							$class        = null;
							$usObj->class = null;
						}

						foreach ($user as $key => $value) {
							$userObj = new stdClass();

							if ($key == 'fnum') {
								$userObj->val   = $value;
								$userObj->class = $class;
								$userObj->type  = 'fnum';

								if ($displayPhoto) {
									$userObj->photo = EmundusHelperFiles::getPhotos($value);
								}

								if (version_compare(JVERSION, '4.0', '>')) {
									$userObj->user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById((int) substr($value, -7));
								}
								else {
									$userObj->user = Factory::getUser((int) substr($value, -7));
								}

								$userObj->user->name = $user['name'];
								$line['fnum']        = $userObj;
							}

							elseif ($key == 'name' || $key == 'status_class' || $key == 'step' || $key == 'overall') {
								continue;
							}

							elseif ($key == 'evaluator') {
								if ($evaluators_can_see_other_eval || EmundusHelperAccess::asAccessAction(29, 'r', $this->_user->id)) {
									$userObj->val = !empty($value) ? '<a href="' . $form_url_view . $user['evaluation_id'] . '"  target="_blank" data-remote="' . $form_url_view . $user['evaluation_id'] . '" id="em_form_eval_' . $i . '-' . $user['evaluation_id'] . '">
											<span class="glyphicon icon-eye-open" title="' . JText::_('COM_EMUNDUS_DETAILS') . '">  </span>
										</a>' . $value : '';
								}
								else {
									$userObj->val = $value;
								}

								$userObj->type     = 'html';
								$line['evaluator'] = $userObj;
							}
							else {
								$userObj->val          = $value;
								$userObj->type         = 'text';
								$userObj->status_class = $user['status_class'];
								$line[$key]            = $userObj;
							}
						}

						if (is_array($this->colsSup) && count($this->colsSup) > 0) {
							foreach ($this->colsSup as $key => $obj) {
								$userObj = new stdClass();
								if (!is_null($obj)) {
									if (array_key_exists($user['fnum'], $obj)) {
										$userObj->val                     = $obj[$user['fnum']];
										$userObj->type                    = 'html';
										$userObj->fnum                    = $user['fnum'];
										$line[JText::_(strtoupper($key))] = $userObj;
									}
									else {
										$userObj->val  = '';
										$userObj->type = 'html';
										$line[$key]    = $userObj;
									}
								}
								elseif (!empty($mod_emundus_custom) && array_key_exists($key, $mod_emundus_custom)) {
									$line[$key] = "";
								}
							}
						}
						$this->data[$line['fnum']->val . '-' . $i] = $line;
						$i++;
					}

					if (isset($this->colsSup['overall'])) {
						require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'evaluation.php');
						$m_evaluation             = new EmundusModelEvaluation();
						$this->colsSup['overall'] = $m_evaluation->getEvaluationAverageByFnum($fnumArray);
					}

					if (isset($this->colsSup['id_tag'])) {
						$tags                    = $m_files->getTagsByFnum($fnumArray);
						$this->colsSup['id_tag'] = EmundusHelperFiles::createTagsList($tags);
					}

					if (isset($this->colsSup['access'])) {
						$this->accessObj = $m_files->getAccessorByFnums($fnumArray);
					}

					if (!empty($mod_emundus_custom)) {
						foreach ($mod_emundus_custom as $key => $module) {
							if (isset($this->colsSup[$key])) {
								$this->colsSup[$key] = $h_files->createHTMLList($module, $fnumArray);
							}
						}
					}

				}
				else {
					$this->data = JText::_('COM_EMUNDUS_NO_RESULT');
				}

				/* Get the values from the state object that were inserted in the model's construct function */
				if (version_compare(JVERSION, '4.0', '>')) {
					$session = $this->app->getSession();
				}
				else {
					$session = JFactory::getSession();
				}

				$this->lists['order_dir'] = $session->get('filter_order_Dir');
				$this->lists['order']     = $session->get('filter_order');
				$this->pagination         = $this->get('Pagination');
				$this->pageNavigation     = $this->get('PageNavigation');
				break;
		}

		parent::display($tpl);
	}

}



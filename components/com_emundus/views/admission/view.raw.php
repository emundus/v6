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

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');

use Joomla\CMS\Factory;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Component\ComponentHelper;

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */

class EmundusViewAdmission extends JViewLegacy
{
	private $_user = null;
	private $_db = null;
	private $app = null;
	private $session = null;

	protected $itemId;
	protected $actions;

    protected array $items = array();
    protected string $display = 'none';
    protected array $code = array();
    protected array $fnum_assoc = array();
	protected string $filters = '';
	protected string $formid = '';
	protected string $form_url_edit = '';
	protected array $lists = array();
	protected Pagination $pagination;
	protected string $pageNavigation = '';
	protected array $objAccess = array();
	protected array $colsSup = array();
	protected array $users = array();
	protected array $data = array();

	public function __construct($config = array())
	{
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'list.php');
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'emails.php');
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'export.php');
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'files.php');
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

		$this->app = Factory::getApplication();
		if (version_compare(JVERSION, '4.0', '>'))
		{
			$this->_user = $this->app->getIdentity();
			$this->_db = JFactory::getContainer()->get('DatabaseDriver');
			$this->session = $this->app->getSession();
		} else {
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
			$this->session = JFactory::getSession();
		}

		parent::__construct($config);
	}

    public function display($tpl = null) {

        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            die (JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
        }

	    $this->itemId = $this->app->input->getInt('Itemid', null);

	    $menu 			= $this->app->getMenu();
		$current_menu  	= $menu->getActive();
		$menu_params 	= $menu->getParams($current_menu->id);
		$columnSupl 	= explode(',', $menu_params->get('em_other_columns'));

		$jinput 	= $this->app->input;
		$layout 	= $jinput->getString('layout', 0);

        $m_files = new EmundusModelFiles();

		switch ($layout) {
			case 'menuactions':
				$display = $jinput->getString('display', 'none');

				$items = EmundusHelperFiles::getMenuList($menu_params);
                $actions = $m_files->getAllActions();

				$menuActions = array();
				foreach ($items as $item) {

					if (!empty($item->note)) {
						$note = explode('|', $item->note);
						if ($actions[$note[0]][$note[1]] == 1) {
							$actions[$note[0]]['multi'] = $note[2];
							$actions[$note[0]]['grud'] = $note[1];
							$item->action = $actions[$note[0]];
							$menuActions[] = $item;
						}
					} else {
						$menuActions[] = $item;
					}
				}

				$this->items = $menuActions;
				$this->display = $display;
			break;

			default :
				$cfnum 	= $jinput->getString('cfnum', null);

				$params = ComponentHelper::getParams('com_emundus');
				$evaluators_can_see_other_eval = $params->get('evaluators_can_see_other_eval', 0);

				$m_admission = $this->getModel('Admission');
				$m_files = new EmundusModelFiles();
				$h_files = new EmundusHelperFiles();
				$m_user = new EmundusModelUsers();

                $m_admission->code = $m_user->getUserGroupsProgrammeAssoc($this->_user->id);
		        $groups = $m_user->getUserGroups($this->_user->id, 'Column');

        		$fnum_assoc_to_groups = $m_user->getApplicationsAssocToGroups($groups);
		        $fnum_assoc = $m_user->getApplicantsAssoc($this->_user->id);
		        $m_admission->fnum_assoc = array_merge($fnum_assoc_to_groups, $fnum_assoc);
				$this->code = $m_admission->code;
				$this->fnum_assoc = $m_admission->fnum_assoc;

				// reset filter
				$this->filters = $h_files->resetFilter();

				// Do not display photos unless specified in params
				$displayPhoto = false;

				// get applications files
				$users = $m_admission->getUsers($cfnum);

				// Get elements from model and proccess them to get an easy to use array containing the element type
				$elements = $m_admission->getElementsVar();
				foreach ($elements as $elt) {
					$elt_name = $elt->tab_name."___".$elt->element_name;
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

				// Columns
				$defaultElements = $this->get('DefaultElements');
				$data = array(array('check' => '#', 'name' => JText::_('COM_EMUNDUS_FILES_APPLICATION_FILES'), 'c.status' => JText::_('COM_EMUNDUS_STATUS')));
				$fl = array();

			    // Get admission criterion
				if (count($defaultElements)>0) {
					foreach ($defaultElements as $key => $elt) {
						$fl[$elt->tab_name . '.' . $elt->element_name] = $elt->element_label;
					}
				}
				$fl['jos_emundus_final_grade.user'] = JText::_('COM_EMUNDUS_DECISION_RECORDED_BY');
				// merge admission criterion on application files
				$data[0] = array_merge($data[0], $fl);
				$fnumArray = array();

			    // get admisson form ID
			    $formid = $m_admission->getAdmissionFormByProgramme();
				$this->formid = $formid;
			    $form_url_view = 'index.php?option=com_fabrik&c=form&view=details&formid='.$formid->formid.'&tmpl=component&iframe=1&rowid=';
			    $this->form_url_edit = 'index.php?option=com_fabrik&c=form&view=form&formid='.$formid->formid.'&tmpl=component&iframe=1&rowid=';

				if (!empty($users)) {
					$i = 0;
					foreach ($columnSupl as $col) {
						$col = explode('.', $col);
						switch ($col[0]) {
							case 'evaluators':
								$data[0]['EVALUATORS'] = JText::_('COM_EMUNDUS_EVALUATION_EVALUATORS');
								$this->colsSup['evaluators'] = EmundusHelperFiles::createEvaluatorList($col[1], $m_admission);
								break;
							case 'overall':
								$data[0]['overall'] = JText::_('COM_EMUNDUS_EVALUATIONS_OVERALL');
								$this->colsSup['overall'] = array();
								break;
							case 'tags':
								$taggedFile = $m_admission->getTaggedFile();
								$data[0]['eta.id_tag'] = JText::_('COM_EMUNDUS_TAGS');
								$this->colsSup['id_tag'] = array();
								break;
							case 'access':
								$data[0]['access'] = JText::_('COM_EMUNDUS_ASSOCIATED_TO');
								$this->colsSup['access'] = array();
								break;
							case 'photos':
								$displayPhoto = true;
								break;
							case 'module':
								// Get every module without a positon.
								$mod_emundus_custom = array();
								foreach (JModuleHelper::getModules('') as $module) {
									if ($module->module == 'mod_emundus_custom' && ($module->menuid == 0 || $module->menuid == $jinput->get('Itemid', null))) {
										$mod_emundus_custom[$module->title] = $module->content;
										$data[0][$module->title] = JText::_($module->title);
										$this->colsSup[$module->title] = array();
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

						if (isset($taggedFile) && array_key_exists($user['fnum'], $taggedFile)) {
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
								if ($displayPhoto)
									$userObj->photo = $h_files->getPhotos($value);
								$userObj->user = JFactory::getUser((int)substr($value, -7));
								$userObj->emUser = $m_user->getUserInfos((int)substr($value, -7));
								$line['fnum'] = $userObj;
							}

							elseif ($key == 'name' || $key == 'evaluation_id' || $key == 'admission_id' || $key == 'recorded_by' || $key == 'status_class' || $key == 'step') {
								continue;
							}

							elseif ($key == 'evaluator') {

								if ($evaluators_can_see_other_eval || EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
									$userObj->val = !empty($value)?'<a href="#" data-toggle="modal" data-target="#basicModal" data-remote="'.$form_url_view.$user['evaluation_id'].'" id="em_form_eval_'.$i.'-'.$user['evaluation_id'].'">
											<span class="glyphicon icon-eye-open" title="'.JText::_('COM_EMUNDUS_DETAILS').'">  </span>
										</a>'.$value:'';
								} else $userObj->val = $value;

								$userObj->type = 'html';
								$line['evaluator'] = $userObj;
							}

							elseif (isset($elements) && in_array($key, array_keys($elements))) {

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

								$userObj->val 			= $value;
								$userObj->type 			= 'text';
								$userObj->status_class 	= $user['status_class'];
								$line[$key] 			= $userObj;
							}
						}

						foreach ($this->colsSup as $key => $obj) {
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
								} elseif (!empty($mod_emundus_custom) && array_key_exists($key, $mod_emundus_custom)) {
									$line[$key] = "";
								}
							}

						$data[$line['fnum']->val.'-'.$i] = $line;
						$i++;
					}

					if (isset($this->colsSup['overall'])) {
						require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
						$m_evaluation = new EmundusModelEvaluation();
						$this->colsSup['overall'] = $m_evaluation->getEvaluationAverageByFnum($fnumArray);
					}

					if (isset($this->colsSup['id_tag'])) {
						$tags = $m_files->getTagsByFnum($fnumArray);
						$this->colsSup['id_tag'] = @EmundusHelperFiles::createTagsList($tags);
					}

                    if (isset($this->colsSup['access'])) {
					    $this->objAccess = $m_files->getAccessorByFnums($fnumArray);
				    }

					if (!empty($mod_emundus_custom)) {
						foreach ($mod_emundus_custom as $key => $module) {
							if (isset($this->colsSup[$key])) {
								$this->colsSup[$key] = $h_files->createHTMLList($module, $fnumArray);
							}
						}
					}

				} else {
					$data = JText::_('COM_EMUNDUS_NO_RESULT');
				}

		    $lists['order_dir'] = $this->session->get( 'filter_order_Dir' );
			$lists['order']     = $this->session->get( 'filter_order' );
			$this->lists = $lists;

		    $this->pagination = $this->get('Pagination');
		    $this->pageNavigation = $this->get('PageNavigation');

			$this->users = $users;
			$this->data = $data;

			break;
		}
		parent::display($tpl);
	}
}



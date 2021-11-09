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
//error_reporting(E_ALL);
jimport( 'joomla.application.component.view');
/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */

class EmundusViewAdmission extends JViewLegacy
{
	var $_user = null;
	var $_db = null;
	protected $itemId;
	protected $actions;

	public function __construct($config = array())
	{
		/*require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');*/
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'users.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');

		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();

		parent::__construct($config);
	}

    public function display($tpl = null) {

        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id))
            die (JText::_('RESTRICTED_ACCESS'));

	    $this->itemId = JFactory::getApplication()->input->getInt('Itemid', null);

	    $menu 			= @JFactory::getApplication()->getMenu();
		$current_menu  	= $menu->getActive();
		$menu_params 	= $menu->getParams(@$current_menu->id);
		$columnSupl 	= explode(',', $menu_params->get('em_other_columns'));

		$jinput 	= JFactory::getApplication()->input;
		$layout 	= $jinput->getString('layout', 0);

        $m_files = new EmundusModelFiles();

		switch ($layout) {
			case 'menuactions':
				$display = JFactory::getApplication()->input->getString('display', 'none');

				$items = @EmundusHelperFiles::getMenuList($menu_params);
                //$actions = @EmundusHelperFiles::getActionsACL();
                $actions = $m_files->getAllActions();

				$menuActions = array();
				foreach ($items as $key => $item) {

					if (!empty($item->note)) {
						$note = explode('|', $item->note);
						if ($actions[$note[0]][$note[1]] == 1) {
							$actions[$note[0]]['multi'] = $note[2];
							$actions[$note[0]]['grud'] = $note[1];
							$item->action = $actions[$note[0]];
							$menuActions[] = $item;
						}
					} else $menuActions[] = $item;
				}

				$this->assignRef('items', $menuActions);
				$this->assignRef('display', $display);
			break;

			default :
				$jinput = JFactory::getApplication()->input;
				$cfnum 	= $jinput->getString('cfnum', null);

				$params = JComponentHelper::getParams('com_emundus');
				$evaluators_can_see_other_eval = $params->get('evaluators_can_see_other_eval', 0);

				$m_admission = $this->getModel('Admission');
				$m_files = new EmundusModelFiles();
				$h_files = new EmundusHelperFiles();
				$m_user = new EmundusModelUsers();

                $m_admission->code = $m_user->getUserGroupsProgrammeAssoc($this->_user->id);
                //$m_admission->fnum_assoc = $m_user->getApplicantsAssoc($this->_user->id);
                // get all fnums manually associated to user
		        $groups = $m_user->getUserGroups($this->_user->id, 'Column');

        		$fnum_assoc_to_groups = $m_user->getApplicationsAssocToGroups($groups);
		        $fnum_assoc = $m_user->getApplicantsAssoc($this->_user->id);
		        $m_admission->fnum_assoc = array_merge($fnum_assoc_to_groups, $fnum_assoc);
                $this->assignRef('code', $m_admission->code);
                $this->assignRef('fnum_assoc', $m_admission->fnum_assoc);

				// reset filter
				$filters = $h_files->resetFilter();
				$this->assignRef('filters', $filters);

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
				$data = array(array('check' => '#', 'name' => JText::_('APPLICATION_FILES'), 'c.status' => JText::_('STATUS')));
				$fl = array();

			    // Get admission criterion
				if (count($defaultElements)>0) {
					foreach ($defaultElements as $key => $elt) {
						$fl[$elt->tab_name . '.' . $elt->element_name] = $elt->element_label;
					}
				}
				$fl['jos_emundus_final_grade.user'] = JText::_('RECORDED_BY');
				// merge admission criterion on application files
				$data[0] = array_merge($data[0], $fl);
				$fnumArray = array();

			    // get admisson form ID
			    $formid = $m_admission->getAdmissionFormByProgramme();
			    $this->assignRef('formid', $formid);
			    $form_url_view = 'index.php?option=com_fabrik&c=form&view=details&formid='.$formid->formid.'&tmpl=component&iframe=1&rowid=';
			    $form_url_edit = 'index.php?option=com_fabrik&c=form&view=form&formid='.$formid->formid.'&tmpl=component&iframe=1&rowid=';
			    $this->assignRef('form_url_edit', $form_url_edit);

				if (!empty($users)) {
					$i = 0;
					foreach ($columnSupl as $col) {
						$col = explode('.', $col);
						switch ($col[0]) {
							case 'evaluators':
								$data[0]['EVALUATORS'] = JText::_('EVALUATORS');
								$colsSup['evaluators'] = @EmundusHelperFiles::createEvaluatorList($col[1], $m_admission);
								break;
							case 'overall':
								$data[0]['overall'] = JText::_('EVALUATION_OVERALL');
								$colsSup['overall'] = array();
								break;
							case 'tags':
								$taggedFile = $m_admission->getTaggedFile();
								$data[0]['eta.id_tag'] = JText::_('TAGS');
								$colsSup['id_tag'] = array();
								break;
							case 'access':
								$data[0]['access'] = JText::_('COM_EMUNDUS_ASSOCIATED_TO');
								$colsSup['access'] = array();
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
											<span class="glyphicon icon-eye-open" title="'.JText::_('DETAILS').'">  </span>
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

						if (count(@$colsSup)>0) {
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
								} elseif (!empty($mod_emundus_custom) && array_key_exists($key, $mod_emundus_custom)) {
									$line[$key] = "";
								}
							}
						}
						$data[$line['fnum']->val.'-'.$i] = $line;
						$i++;
					}

					if (isset($colsSup['overall'])) {
						require_once (JPATH_COMPONENT.DS.'models'.DS.'evaluation.php');
						$m_evaluation = new EmundusModelEvaluation();
						$colsSup['overall'] = $m_evaluation->getEvaluationAverageByFnum($fnumArray);
					}

					if (isset($colsSup['id_tag'])) {
						$tags = $m_files->getTagsByFnum($fnumArray);
						$colsSup['id_tag'] = @EmundusHelperFiles::createTagsList($tags);
					}

                    if (isset($colsSup['access'])) {
					    $objAccess = $m_files->getAccessorByFnums($fnumArray);
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
		    $lists['order_dir'] = JFactory::getSession()->get( 'filter_order_Dir' );
			$lists['order']     = JFactory::getSession()->get( 'filter_order' );
		    $this->assignRef('lists', $lists);
		   /* $this->assignRef('actions', $actions);*/
		    $pagination = $this->get('Pagination');
		    $this->assignRef('pagination', $pagination);
		    $pageNavigation = $this->get('PageNavigation');
		    $this->assignRef('pageNavigation', $pageNavigation);
			$this->assignRef('accessObj', $objAccess);
			$this->assignRef('colsSup', $colsSup);
			$this->assignRef('users', $users);
			$this->assignRef('datas', $data);

			break;
		}
		parent::display($tpl);
	}
}



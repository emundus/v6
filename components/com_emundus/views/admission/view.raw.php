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
        require_once (JPATH_COMPONENT.DS.'models'.DS.'users.php');

		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}

    public function display($tpl = null) {

        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id))
            die (JText::_('RESTRICTED_ACCESS'));

	    $this->itemId = JFactory::getApplication()->input->getInt('Itemid', null);

	    $menu 			= @JSite::getMenu();
		$current_menu  	= $menu->getActive();
		$menu_params 	= $menu->getParams(@$current_menu->id);
		$columnSupl 	= explode(',', $menu_params->get('em_actions'));
		$em_blocks_names = explode(',', $menu_params->get('em_blocks_names'));

		$jinput 	= JFactory::getApplication()->input;
		$layout 	= $jinput->getString('layout', 0);

		switch ($layout) {
			case 'menuactions': 
				$display = JFactory::getApplication()->input->getString('display', 'none'); 
			
				$items = @EmundusHelperFiles::getMenuList($menu_params);
				$actions = @EmundusHelperFiles::getActionsACL();

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

				$admission = $this->getModel('Admission');
                $userModel = new EmundusModelUsers();

                $admission->code = $userModel->getUserGroupsProgrammeAssoc($this->_user->id);
                //$admission->fnum_assoc = $userModel->getApplicantsAssoc($this->_user->id);
                // get all fnums manually associated to user
		        $groups = $userModel->getUserGroups($this->_user->id, 'Column');
				
        		$fnum_assoc_to_groups = $userModel->getApplicationsAssocToGroups($groups);
		        $fnum_assoc = $userModel->getApplicantsAssoc($this->_user->id);
		        $admission->fnum_assoc = array_merge($fnum_assoc_to_groups, $fnum_assoc);
                $this->assignRef('code', $admission->code);
                $this->assignRef('fnum_assoc', $admission->fnum_assoc);

				// reset filter
				$filters = @EmundusHelperFiles::resetFilter();
				$this->assignRef('filters', $filters);

				// get applications files
				$users = $admission->getUsers($cfnum);

				// Get elements from model and proccess them to get an easy to use array containing the element type
				$elements = $admission->getElementsVar();
				foreach ($elements as $elt) {
					$elt_name = $elt->tab_name."___".$elt->element_name;
					$eltarr[$elt_name] = [
						"plugin" 	=> $elt->element_plugin,
						"tab_name" 	=> $elt->tab_name,
						"params"  	=> $elt->element_attribs,
						"fabrik_id" => $elt->id
					];
				}
				$elements = $eltarr;

				// Columns
				$defaultElements = $this->get('DefaultElements');
				$data = array(array('check' => '#', 'u.name' => JText::_('APPLICATION_FILES'), 'c.status' => JText::_('STATUS')));
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

			    // get admisson form ID
			    $formid = $admission->getAdmissionFormByProgramme();
			    $this->assignRef('formid', $formid);
			    $form_url_view = 'index.php?option=com_fabrik&c=form&view=details&formid='.$formid.'&tmpl=component&iframe=1&rowid=';
			    $form_url_edit = 'index.php?option=com_fabrik&c=form&view=form&formid='.$formid.'&tmpl=component&iframe=1&rowid=';
			    $this->assignRef('form_url_edit', $form_url_edit);

				if (!empty($users)) {
					//$i = 1;
					$taggedFile = $admission->getTaggedFile();
					foreach ($columnSupl as $col) {
						$col = explode('.', $col);
						switch ($col[0]) {
							case 'photos':
								$colsSup['photos'] = @EmundusHelperFiles::getPhotos();
								$data[0]['PHOTOS'] = JText::_('PHOTOS');
								break;
							case 'evaluators':
								$data[0]['EVALUATORS'] = JText::_('EVALUATORS');
								$colsSup['evaluators'] = @EmundusHelperFiles::createEvaluatorList($col[1], $admission);
								break;
						}
					}
					if (in_array('overall', $em_blocks_names))
						$data[0]['overall'] = JText::_('EVALUATION_OVERALL');

					$i = 0;
					foreach ($users as $user) {
						$usObj = new stdClass();
						$usObj->val = 'X';
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
								$line['fnum'] = $userObj;
							} 
							
							elseif ($key == 'name') continue;
						    
							elseif ($key == 'evaluator') {
								
								if ($evaluators_can_see_other_eval || EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
									$userObj->val = !empty($value)?'<a href="#" data-toggle="modal" data-target="#basicModal" data-remote="'.$form_url_view.$user['evaluation_id'].'" id="em_form_eval_'.$i.'-'.$user['evaluation_id'].'">
											<span class="glyphicon icon-eye-open" title="'.JText::_('DETAILS').'">  </span>
										</a>'.$value:'';								
								} else $userObj->val = $value;

								$userObj->type = 'html';
								$line['evaluator'] = $userObj;
							}

							elseif ($key == 'status_class') continue;

							elseif (isset($elements) && in_array($key, array_keys($elements))) {
								
								$userObj->val 			= $value;
								$userObj->type 			= $elements[$key]['plugin'];
								$userObj->status_class 	= $user['status_class'];
								$userObj->id 			= $elements[$key]['fabrik_id'];
								$userObj->params 		= $elements[$key]['params'];
								$line[$key] 			= $userObj;
							
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
								}

							}
						}
						$data[$line['fnum']->val.'-'.$i] = $line;
						$i++;
					}

				} else $data = JText::_('NO_RESULT');

			/* Get the values from the state object that were inserted in the model's construct function */
		    $lists['order_dir'] = JFactory::getSession()->get( 'filter_order_Dir' );
			$lists['order']     = JFactory::getSession()->get( 'filter_order' );
		    $this->assignRef('lists', $lists);
		   /* $this->assignRef('actions', $actions);*/
		    $pagination = $this->get('Pagination');
		    $this->assignRef('pagination', $pagination);

			$this->assignRef('users', $users);
			$this->assignRef('datas', $data);

			break;
		}
		parent::display($tpl);
	}
}



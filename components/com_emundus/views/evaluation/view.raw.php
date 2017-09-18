<?php
/**
 * @package    eMundus
 * @subpackage Components
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
 
class EmundusViewEvaluation extends JViewLegacy
{
	var $_user = null;
	var $_db = null;
	protected $itemId;
	protected $actions;

	public function __construct($config = array())
	{
		/*require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');*/
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

    public function display($tpl = null)
    {

	    $this->itemId = JFactory::getApplication()->input->getInt('Itemid', null);

	    $menu = @JSite::getMenu();
		$current_menu  = $menu->getActive();
		$menu_params = $menu->getParams(@$current_menu->id);
		
		$columnSupl = explode(',', $menu_params->get('em_other_columns'));
		$jinput = JFactory::getApplication()->input;
		$layout = $jinput->getString('layout', 0);

		switch  ($layout)
		{
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
					} else 
						$menuActions[] = $item;
				}

				$this->assignRef('items', $menuActions);
				$this->assignRef('display', $display);
			break;

			default :
				$jinput = JFactory::getApplication()->input;
				$cfnum = $jinput->getString('cfnum', null);

				$params = JComponentHelper::getParams('com_emundus');
				$evaluators_can_see_other_eval = $params->get('evaluators_can_see_other_eval', 0);

				$evaluation = $this->getModel('Evaluation');
                $userModel = new EmundusModelUsers();

                $evaluation->code = $userModel->getUserGroupsProgrammeAssoc($this->_user->id);
                //$evaluation->fnum_assoc = $userModel->getApplicantsAssoc($this->_user->id);
                // get all fnums manually associated to user
		        $groups = $userModel->getUserGroups($this->_user->id, 'Column');
        		$fnum_assoc_to_groups = $userModel->getApplicationsAssocToGroups($groups);
		        $fnum_assoc = $userModel->getApplicantsAssoc($this->_user->id);
		        $evaluation->fnum_assoc = array_merge($fnum_assoc_to_groups, $fnum_assoc);
                $this->assignRef('code', $evaluation->code);
                $this->assignRef('fnum_assoc', $evaluation->fnum_assoc);

				// reset filter
				$filters = @EmundusHelperFiles::resetFilter();
				$this->assignRef('filters', $filters);

				// Do not display photos unless specified in params
				$displayPhoto = false;

				// get applications files
				$users = $evaluation->getUsers($cfnum);

				// Columns
				$defaultElements = $this->get('DefaultElements');
				$datas = array(array('check' => '#', 'u.name' => JText::_('APPLICATION_FILES'), 'c.status' => JText::_('STATUS')));
				$fl = array();

			    // Get eval crieterion
				if (count($defaultElements)>0) {
					foreach ($defaultElements as $key => $elt)
					{
						$fl[$elt->tab_name . '.' . $elt->element_name] = $elt->element_label;
					}
				}
				$fl['jos_emundus_evaluations.user'] = JText::_('EVALUATOR');
				// merge eval criterion on application files
			    $datas[0] = array_merge($datas[0], $fl);

			    // get evaluation form ID
			    $formid = $evaluation->getEvaluationFormByProgramme();
			    $this->assignRef('formid', $formid);
			    $form_url_view = 'index.php?option=com_fabrik&c=form&view=details&formid='.$formid.'&tmpl=component&iframe=1&rowid=';
			    $form_url_edit = 'index.php?option=com_fabrik&c=form&view=form&formid='.$formid.'&tmpl=component&iframe=1&rowid=';
			    $this->assignRef('form_url_edit', $form_url_edit);
			    //$form_url_add  = 'index.php?option=com_fabrik&c=form&view=form&formid=29&tableid=31&rowid=&jos_emundus_evaluations___student_id[value]=2778&jos_emundus_evaluations___campaign_id[value]=55&jos_emundus_evaluations___fnum[value]=2014092516382300000550002778&student_id=2778&tmpl=component&iframe=1';

				if (!empty($users)) {
					
					//$i = 1;
					$taggedFile = $evaluation->getTaggedFile();
					foreach ($columnSupl as $col) {
						$col = explode('.', $col);
						switch ($col[0]) {
							case 'evaluators':
								$data[0]['EVALUATORS'] = JText::_('EVALUATORS');
								$colsSup['evaluators'] = @EmundusHelperFiles::createEvaluatorList($col[1], $model);
								break;
							case 'overall':
								$data[0]['overall'] = JText::_('EVALUATION_OVERALL');
								break;
							case 'tags':
								$taggedFile = $model->getTaggedFile();
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
						}
					}

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
								if ($displayPhoto) 
									$userObj->photo = EmundusHelperFiles::getPhotos($value);
								$userObj->user = JFactory::getUser((int)substr($value, -7));
								$line['fnum'] = $userObj;
							
							} 
							
							elseif ($key == 'name' || $key == 'status_class' || $key == 'step')
						    	continue;

							elseif ($key == 'evaluator') {
								
								if ($formid > 0 && !empty($value)) {
								
									if ($evaluators_can_see_other_eval)
										$link_view = '<a href="'.$form_url_view.$user['evaluation_id'].'" data-toggle="modal" data-target="#basicModal" data-remote="'.$form_url_view.$user['evaluation_id'].'" id="em_form_eval_'.$i.'-'.$user['evaluation_id'].'"><span class="glyphicon icon-eye-open" title="'.JText::_('DETAILS').'">  </span></a>';
								
									if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) 
										$link_edit = '<a href="'.$form_url_edit.$user['evaluation_id'].'" target="_blank"><span class="glyphicon icon-edit" title="'.JText::_('EDIT').'"> </span></a>';
								
									$userObj->val = @$link_view.' '.@$link_edit.' '.$value;
								
								} else $userObj->val = $value;

								$userObj->type = 'html';
								$line['evaluator'] = $userObj;
							
							} else {

								$userObj->val = $value;
								$userObj->type = 'text';
								$userObj->status_class = $user['status_class'];
								$line[$key] = $userObj;
							
							}
						
						} if (count(@$colsSup) > 0) {
							
							foreach ($colsSup as $key => $obj) {
								
								$userObj = new stdClass();
								if (!is_null($obj)) {
									
									if(array_key_exists($user['fnum'], $obj)) {

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
						$datas[$line['fnum']->val.'-'.$i] = $line;
						$i++;
					}

				} else $datas = JText::_('NO_RESULT');


			/* Get the values from the state object that were inserted in the model's construct function */
		    $lists['order_dir'] = JFactory::getSession()->get( 'filter_order_Dir' );
			$lists['order']     = JFactory::getSession()->get( 'filter_order' );
		    $this->assignRef('lists', $lists);
		   /* $this->assignRef('actions', $actions);*/
		    $pagination = $this->get('Pagination');
		    $this->assignRef('pagination', $pagination);

			$this->assignRef('users', $users);
			$this->assignRef('datas', $datas);

		break;
	}
	parent::display($tpl);
}

}



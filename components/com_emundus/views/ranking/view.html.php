<?php
 /**
 * @package     Joomla
 * @subpackage  eMundus
 * @link       http://www.decisionpublique.fr
 * @copyright   Copyright (C) 2013 eMundus. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
 
// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');
jimport( 'joomla.utilities.date' );
/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
 
class EmundusViewRanking extends JViewLegacy
{
    var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
	
	function display($tpl = null)
    {
		$document = JFactory::getDocument();
		JHTML::_('behavior.modal');
		JHTML::_('behavior.tooltip'); 
		JHTML::stylesheet( JURI::Base().'media/com_emundus/css/emundus.css' );
		JHTML::stylesheet( JURI::Base().'media/com_emundus/css/menu_style.css' );
	
		$menu = JSite::getMenu();
		$current_menu  = $menu->getActive();
		$menu_params = $menu->getParams($current_menu->id);
		$access = !empty($current_menu)?$current_menu->access:0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id, $access)) die(JText::_('ACCESS_DENIED'));
		
		//$isallowed = EmundusHelperAccess::isAllowed($this->_user->usertype,$allowed);
		//$this->assignRef( 'isallowed', $isallowed );
		
				//Filters
		$tables 		= explode(',', $menu_params->get('em_tables_id')); //41
		$filts_names 	= explode(',', $menu_params->get('em_filters_names'));
		$filts_values	= explode(',', $menu_params->get('em_filters_values'));
		$filts_types  	= explode(',', $menu_params->get('em_filters_options'));
		$filts_details	= array('profile'			=> '',
								'evaluator'			=> '',
								'evaluator_group'	=> '',
								'schoolyear'		=> '',
								'missing_doc'		=> NULL,
								'complete'			=> NULL,
								'finalgrade'		=> '',
								'validate'			=> NULL,
								'other'				=> '',
								'adv_filter'		=> '');
		$filts_options 	= array('profile'			=> NULL,
							  	'evaluator'			=> NULL,
							  	'evaluator_group'	=> NULL,
							  	'schoolyear'		=> NULL,
							  	'missing_doc'		=> NULL,
							  	'complete'			=> NULL,
							  	'finalgrade'		=> NULL,
							  	'validate'			=> NULL,
							  	'other'				=> NULL,
							  	'adv_filter'		=> NULL);
		$validate_id  	= explode(',', $menu_params->get('em_validate_id'));
		$actions  		= explode(',', $menu_params->get('em_actions'));
		$i = 0;
		foreach ($filts_names as $filt_name) {
			if (array_key_exists($i, $filts_values))
				$filts_details[$filt_name] = $filts_values[$i];
			else
				$filts_details[$filt_name] = '';
			if (array_key_exists($i, $filts_types))
				$filts_options[$filt_name] = $filts_types[$i];
			else
				$filts_options[$filt_name] = '';
			$i++;
		}
		unset($filts_names); unset($filts_values); unset($filts_types);

		$filters = EmundusHelperFilters::createFilterBlock($filts_details, $filts_options, $tables);
		$this->assignRef('filters', $filters);
		unset($filts_details); unset($filts_options);
		
		$users= $this->get('Users');
		$this->assignRef( 'users', $users );

		$engaged = EmundusHelperList::getEngaged($users);
		$this->assignRef( 'engaged', $engaged );
		
		//Call the state object 
		$state = $this->get( 'state' );
		// Get the values from the state object that were inserted in the model's construct function 
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
        $this->assignRef( 'lists', $lists );
		
        $pagination = $this->get('Pagination');
        $this->assignRef('pagination', $pagination);
		
		$current_schoolyear = implode(', ',$this->get('CurrentCampaign'));
		$this->assignRef( 'current_schoolyear', $current_schoolyear );
		
		// Columns
		$appl_cols = $this->get('ApplicantColumns');
		$filter_cols = $this->get('SelectList'); 
		$eval_cols = $this->get('EvalColumns');
		$rank_cols = $this->get('RankingColumns');
		
		$header_values = EmundusHelperList::aggregation($appl_cols, $filter_cols, $eval_cols, $rank_cols);
		$this->assignRef( 'header_values', $header_values );
		
		//Export
		$options = array('zip', 'xls');
		if($this->_user->profile!=16)
			$export_icones = EmundusHelperExport::export_icones($options);
		$this->assignRef('export_icones', $export_icones);
		unset($options);
		
		//Email
		if(EmundusHelperAccess::isAdministrator($this->_user->id) || EmundusHelperAccess::isCoordinator($this->_user->id)) {
			if($this->_user->profile!=16){
				$options = array('applicants');
				$email_applicant = EmundusHelperEmails::createEmailBlock($options);
				unset($options);
			}
		}
		else $email_applicant = '';
		$this->assignRef('email', $email_applicant);
		
		//List
		$selection = EmundusHelperList::createSelectionBlock($users);
		$this->assignRef('selection', $selection);
		
		$options = array('checkbox', 'gender', 'details', 'selection_outcome');
		$actions = EmundusHelperList::createActionsBlock($users, $options);
		$this->assignRef('actions', $actions);
		unset($options);
		
		// Schoolyears 
		$schoolyears = EmundusHelperFilters::getSchoolyears();
		$this->assignRef('schoolyears', $schoolyears);
		
		//Profile
		$profile = EmundusHelperList::createProfileBlock($users, 'profile');
		$this->assignRef('profile', $profile);
		$result_for = EmundusHelperList::createProfileBlock($users, 'result_for');
		$this->assignRef('result_for', $result_for);
		$final_grade = EmundusHelperFilters::getFinal_grade();
		$sub_labels = explode('|', $final_grade['final_grade']['sub_labels']);
		$sub_values = explode('|', $final_grade['final_grade']['sub_values']);
		$fg = array_combine($sub_values, $sub_labels);
		$this->assignRef('fg', $fg);

		// Javascript
       // JHTML::script( 'joomla.javascript.js', JURI::Base().'includes/js/' );
		$onSubmitForm = EmundusHelperJavascript::onSubmitForm();
		$this->assignRef('onSubmitForm', $onSubmitForm);
		$addElement = EmundusHelperJavascript::addElement();
		$this->assignRef('addElement', $addElement);
		$addElementOther = EmundusHelperJavascript::addElementOther($tables);
		$this->assignRef('addElementOther', $addElementOther);
		$delayAct = EmundusHelperJavascript::delayAct();
		$this->assignRef('delayAct', $delayAct);
		
		parent::display($tpl);
    }
}
?>
<?php
/**
 * Created by eMundus.
 * User: brivalland
 * Date: 03/05/16
 * Time: 11:39
 * @package        Joomla
 * @subpackage    eMundus
 * @link        http://www.emundus.fr
 * @copyright    Copyright (C) 2006 eMundus. All rights reserved.
 * @license        GNU/GPL
 * @author        Benjamin Rivalland
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
 
class EmundusViewAmetys extends JViewLegacy
{
	//protected $itemId;
	protected $actions;

	public function __construct($config = array())
	{
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'profile.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'users.php');
		
		parent::__construct($config);
	}

    public function display($tpl = null)
    {
    	$current_user = JFactory::getUser();	
	   	$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_emundus');

	    $this->itemId = $app->input->getInt('Itemid', null);
	    $this->cfnum = $app->input->getString('cfnum', null);
	    $layout = $app->input->getString('layout', null);
	    $model = $this->getModel('Ametys');

		switch  ($layout)
		{
			// get access list for application file
			case 'access':
				$fnums = $app->input->getString('users', null);
				$fnums_obj = (array) json_decode(stripslashes($fnums)); 

			    if(@$fnums_obj[0] == 'all')
					$fnums = $model->getAllFnums();
			    else {
			        $fnums = array();
			        foreach ($fnums_obj as $key => $value) {
			        	$fnums[] = @$value->fnum;
			        }
			    }

			    $groupFnum = $model->getGroupsByFnums($fnums);
			    $evalFnum = $model->getAssessorsByFnums($fnums);
				$users = $model->getFnumsInfos($fnums);
			    $evalGroups = $model->getEvalGroups();
			    $actions = $model->getAllActions();
			    $actions_evaluators = json_decode($default_actions);

			    $this->assignRef('groups', $evalGroups['groups']);
			    $this->assignRef('groupFnum', $groupFnum);
			    $this->assignRef('evalFnum', $evalFnum);
			    $this->assignRef('users', $users);
			    $this->assignRef('evals', $evalGroups['users']);
			    $this->assignRef('actions', $actions);
			    $this->assignRef('actions_evaluators', $actions_evaluators);
			break;

			// get list of application files
			default :
			    $menu = @JSite::getMenu();
			    $current_menu  = $menu->getActive();
			    $menu_params = $menu->getParams($current_menu->id);

		    break;
	    }

		parent::display($tpl);

	}

}



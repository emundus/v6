<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * eMundus Component Controller
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
//error_reporting(E_ALL);
class EmundusControllerDecision extends JControllerLegacy
{
    var $_user = null;
    var $_db = null;

    public function __construct($config = array())
    {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');
		require_once (JPATH_COMPONENT . '/models/decision.php');

        $this->_user = JFactory::getSession()->get('emundusUser');
        $this->_db = JFactory::getDBO();

        parent::__construct($config);
    }

    public function display($cachable = false, $urlparams = false)
    {
        // Set a default view if none exists
        if ( ! JRequest::getCmd( 'view' ) )
        {
            $default = 'files';
            JRequest::setVar('view', $default );
        }
        parent::display();
    }

////// EMAIL APPLICANT WITH CUSTOM MESSAGE///////////////////
    public function applicantEmail() {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
        @EmundusHelperEmails::sendApplicantEmail();
    }

    public function clear() {
        @EmundusHelperFiles::clear();
        echo json_encode((object)(array('status' => true)));
        exit;
    }

    public function setfilters() {
        $jinput = JFactory::getApplication()->input;
        $filterName = $jinput->getString('id', null);
        $elements = $jinput->getString('elements', null);
        $multi = $jinput->getString('multi', null);

	    $h_files = new EmundusHelperFiles;
	    $h_files->clearfilter();

        if ($multi == "true") {
            $filterval = $jinput->get('val', array(), 'ARRAY');
        } else {
            $filterval = $jinput->getString('val', null);
        }

        $session = JFactory::getSession();
        $params = $session->get('filt_params');

        if ($elements == 'false') {
            $params[$filterName] = $filterval;
        } else {
            $vals = (array)json_decode(stripslashes($filterval));
            if (count($vals) > 0) {
                foreach ($vals as $val) {
                    if ($val->adv_fil) {
	                    $params['elements'][$val->name]['value'] = $val->value;
	                    $params['elements'][$val->name]['select'] = $val->select;
                    } else {
	                    $params[$val->name] = $val->value;
                    }
                }
            } else {
	            $params['elements'][$filterName]['value'] = $filterval;
            }
        }

        $session->set('filt_params', $params);

        echo json_encode((object)(array('status' => true)));
        exit();
    }

    public function loadfilters() {
        try {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('id', null);

	        $session = JFactory::getSession();

	        $h_files = new EmundusHelperFiles;
	        $filter = $h_files->getEmundusFilters($id);
            $params = (array) json_decode($filter->constraints);
            $params['select_filter'] = $id;
            $params =  json_decode($filter->constraints, true);

            $session->set('select_filter', $id);
            if(isset($params['filter_order']))
            {
	            $session->set('filter_order', $params['filter_order']);
	            $session->set('filter_order_Dir', $params['filter_order_Dir']);
            }
	        $session->set('filt_params', $params['filter']);
            if(!empty($params['col']))
	            $session->set('adv_cols', $params['col']);

            echo json_encode((object)(array('status' => true)));
            exit();
        }
        catch(Exception $e)
        {
            throw new Exception;
        }
    }

    public function order()
    {
        $jinput = JFactory::getApplication()->input;
        $order = $jinput->getString('filter_order', null);
        $ancientOrder = JFactory::getSession()->get('filter_order');
        $params = JFactory::getSession()->get('filt_params');
        JFactory::getSession()->set('filter_order', $order);
        $params['filter_order'] = $order;

        if($order == $ancientOrder)
        {
            if(JFactory::getSession()->get('filter_order_Dir') == 'desc')
            {
                JFactory::getSession()->set('filter_order_Dir', 'asc');
                $params['filter_order_Dir'] = 'asc';
            }
            else
            {
                JFactory::getSession()->set('filter_order_Dir', 'desc');
                $params['filter_order_Dir'] = 'desc';
            }
        }
        else
        {
            JFactory::getSession()->set('filter_order_Dir', 'asc');
            $params['filter_order_Dir'] = 'asc';
        }
        JFactory::getSession()->set('filt_params', $params);
        echo json_encode((object)(array('status' => true)));
        exit;
    }

    public function setlimit()
    {
        $jinput = JFactory::getApplication()->input;
        $limit = $jinput->getInt('limit', null);

        JFactory::getSession()->set('limit', $limit);
        JFactory::getSession()->set('limitstart', 0);

        echo json_encode((object)(array('status' => true)));
        exit;
    }

    public function savefilters()
    {
        $name           = JRequest::getVar('name', null, 'POST', 'none',0);
        $current_user   = JFactory::getUser();
        $user_id        = $current_user->id;
        $itemid         = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $filt_params    = JFactory::getSession()->get('filt_params');
        $adv_params     = JFactory::getSession()->get('adv_cols');
        $constraints    = array('filter'=>$filt_params, 'col'=>$adv_params);

        $constraints = json_encode($constraints);

        if (empty($itemid))
            $itemid = JRequest::getVar('Itemid', null, 'POST', 'none',0);

        $time_date = (date('Y-m-d H:i:s'));

        $query = "INSERT INTO #__emundus_filters (time_date,user,name,constraints,item_id) values('".$time_date."',".$user_id.",'".$name."',".$this->_db->quote($constraints).",".$itemid.")";
        $this->_db->setQuery( $query );

        try {
            $this->_db->Query();
            $query = 'select f.id, f.name from #__emundus_filters as f where f.time_date = "'.$time_date.'" and user = '.$user_id.' and name="'.$name.'" and item_id="'.$itemid.'"';
            $this->_db->setQuery($query);
            $result = $this->_db->loadObject();
            echo json_encode((object)(array('status' => true, 'filter' => $result)));
            exit;

        } catch (Exception $e) {
            echo json_encode((object)(array('status' => false)));
            exit;
        }
    }

    public function deletefilters()
    {
        $jinput = JFactory::getApplication()->input;
        $filter_id = $jinput->getInt('id', null);

        $query="DELETE FROM #__emundus_filters WHERE id=".$filter_id;
        $this->_db->setQuery( $query );
        $result=$this->_db->Query();

        if ($result!=1) {
            echo json_encode((object)(array('status' => false)));
            exit;
        } else {
            echo json_encode((object)(array('status' => true)));
            exit;
        }
    }

    public function setlimitstart()
    {
        $jinput = JFactory::getApplication()->input;
        $limistart = $jinput->getInt('limitstart', null);
        $limit = intval(JFactory::getSession()->get('limit'));
        $limitstart = ($limit != 0 ? ($limistart > 1 ? (($limistart - 1) * $limit) : 0) : 0);
        JFactory::getSession()->set('limitstart', $limitstart);

        echo json_encode((object)(array('status' => true)));
        exit;
    }

    public function getadvfilters()
    {
        try
        {
            $elements = @EmundusHelperFiles::getElements();

            echo json_encode((object)(array('status' => true, 'default' => JText::_('COM_EMUNDUS_PLEASE_SELECT'), 'defaulttrash' => JText::_('REMOVE_SEARCH_ELEMENT'), 'options' => $elements)));
            exit;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }
/*
    public function getbox()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('id', null);
            $index = $jinput->getInt('index', null);
            $params = JFactory::getSession()->get('filt_params');
            $element = @EmundusHelperFiles::getElementsName($id);
            $key = $element[0]->tab_name . '.' . $element[0]->element_name;
            $params['elements'][$key] = '';

            if(!JFactory::getSession()->has('adv_cols'))
            {
                $advCols = array($index => $id);
            }
            else
            {
                $advCols = JFactory::getSession()->get('adv_cols');
                $lastId = $advCols[$index];
                if (!in_array($id, $advCols))
                {
                    $advCols[$index] = $id;
                }
                if(array_key_exists($index, $advCols))
                {
                    $lastElt = @EmundusHelperFiles::getElementsName($lastId);
                    unset($params['elements'][$lastElt[0]->tab_name . '.' . $lastElt[0]->element_name]);
                }
            }
            JFactory::getSession()->set('filt_params', $params);
            JFactory::getSession()->set('adv_cols', $advCols);

            $html= @EmundusHelperFiles::setSearchBox($element[0], '', $element[0]->tab_name . '.' . $element[0]->element_name, $index);

            echo json_encode((object)(array('status' => true, 'default' => JText::_('COM_EMUNDUS_PLEASE_SELECT'), 'defaulttrash' => JText::_('REMOVE_SEARCH_ELEMENT'), 'html' => $html)));
            exit;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function deladvfilter()
    {
        $jinput = JFactory::getApplication()->input;
        $name = $jinput->getString('elem', null);
        $id = $jinput->getInt('id',null);
        $params = JFactory::getSession()->get('filt_params');
        $advCols = JFactory::getSession()->get('adv_cols');
        unset($params['elements'][$name]);
        unset($advCols[$id]);
        JFactory::getSession()->set('filt_params', $params);
        JFactory::getSession()->set('adv_cols', $advCols);


        echo json_encode((object)(array('status' => true)));
        exit;
    }
*/
    public function addcomment()
    {
        $jinput     = JFactory::getApplication()->input;
        $user       = JFactory::getUser()->id;
        $fnums      = $jinput->getString('fnums', null);
        $title      = $jinput->getString('title', '');
        $comment    = $jinput->getString('comment', null);
        $fnums      = (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);
        $appModel   = new EmundusModelApplication();


        if (is_array($fnums)) {
            foreach ($fnums as $fnum) {
                if (EmundusHelperAccess::asAccessAction(10, 'c', $user, $fnum)) {
                    $aid = intval(substr($fnum, 21, 7));
                    $res = $appModel->addComment((array('applicant_id' => $aid, 'user_id' => $user, 'reason' => $title, 'comment_body' => $comment, 'fnum' => $fnum)));
                    if ($res !== true && !is_numeric($res)) {
                        echo json_encode((array('status' => false, 'msg' => JText::_('COM_EMUNDUS_ERROR'))));
                        exit;
                    }
                }
            }

            echo json_encode((array('status' => true, 'msg' => JText::_('COM_EMUNDUS_COMMENTS_SUCCESS'))));
            exit;

        } else {
            //all result find by the request
            $model = $this->getmodel('Files');
            $fnums = $model->getAllFnums();
            foreach ($fnums as $fnum) {
                if (EmundusHelperAccess::asAccessAction(10, 'c', $user, $fnum)) {
                    $aid = intval(substr($fnum, 14, count($fnum)));
                    $appModel->addComment((array('applicant_id' => $aid, 'user_id' => $user, 'reason' => $title, 'comment_body' => $comment, 'fnum' => $fnum)));
                }
            }
        }
    }

    public function getevsandgroups()
    {
	    $response = ['status' => false, 'code' => 403, 'msg' => JText::_('ACCESS_DENIED')];

	    if (EmundusHelperAccess::asPartnerAccessLevel(JFactory::getUser()->id)) {
		    $m_files = $this->getModel('Files');
		    $evalGroups = $m_files->getEvalGroups();
		    $actions = $m_files->getAllActions('1,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18');
		    $response = [
			    'status' => true,
			    'code' => 200,
			    'groups' => $evalGroups['groups'],
			    'users' => $evalGroups['users'],
			    'actions' => $actions,
			    'group' => JText::_('COM_EMUNDUS_GROUPS_GROUP_EVAL'),
			    'eval' => JText::_('COM_EMUNDUS_EVALUATION_EVALUATORS'),
			    'select_group' => JText::_('COM_EMUNDUS_GROUPS_PLEASE_SELECT_GROUP'),
			    'select_eval' => JText::_('COM_EMUNDUS_GROUPS_PLEASE_SELECT_ASSESSOR'),
			    'check' => JText::_('COM_EMUNDUS_ACCESS_CHECK_ACL'),
			    'create' => JText::_('COM_EMUNDUS_ACCESS_CREATE'),
			    'retrieve' => JText::_('COM_EMUNDUS_ACCESS_RETRIEVE'),
			    'update' => JText::_('COM_EMUNDUS_ACCESS_UPDATE'),
			    'delete' => JText::_('COM_EMUNDUS_ACTIONS_DELETE'),
		    ];
	    }

	    echo json_encode((object)$response);
	    exit;
    }

    public function gettags()
    {
	    $response = ['status' => false, 'code' => 403, 'msg' => JText::_('ACCESS_DENIED'), 'tags' => null];
	    $user = JFactory::getUser();

	    if (EmundusHelperAccess::asAccessAction(14, 'c', $user->id)) {
		    $m_files = $this->getModel('Files');
		    $response['tags'] = $m_files->getAllTags();

		    if (!empty($response['tags'])) {
			    $response['code'] = 200;
			    $response['status']  = true;
			    $response['msg'] = JText::_('SUCCESS');
			    $response['tag'] = JText::_('COM_EMUNDUS_TAGS');
			    $response['select_tag'] = JText::_('COM_EMUNDUS_FILES_PLEASE_SELECT_TAG');
		    } else {
			    $response['code'] = 500;
			    $response['msg'] = JText::_('FAIL');
		    }
	    }

	    echo json_encode((object)$response);
	    exit;
    }

    /**
     * Add a tag to an application
     */
	public function tagfile() {
		$response = ['status' => false, 'code' => 403, 'msg' => JText::_('BAD_REQUEST')];

		$jinput = JFactory::getApplication()->input;
		$fnums  = $jinput->getString('fnums', null);
		$tag    = $jinput->get('tag', null);

		if (!empty($fnums) && !empty($tag)) {
			$m_files = $this->getModel('Files');
			$fnums = ($fnums == 'all') ? $m_files->getAllFnums() : (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

			if (!empty($fnums)) {
				$validFnums = [];
				foreach ($fnums as $fnum) {
					if ($fnum != 'em-check-all' && EmundusHelperAccess::asAccessAction(14, 'c', $this->_user->id, $fnum)) {
						$validFnums[] = $fnum;
					}
				}
				unset($fnums);
				$response['status'] = $m_files->tagFile($validFnums, $tag);

				if ($response['status']) {
					$response['code'] = 200;
					$response['msg'] = JText::_('COM_EMUNDUS_TAGS_SUCCESS');
					$response['tagged'] = $validFnums;
				} else {
					$response['code'] = 500;
					$response['msg'] = JText::_('FAIL');
				}
			}
		}

		echo json_encode((object)($response));
		exit;
	}


    public function deletetags()
    {
        $jinput = JFactory::getApplication()->input;
        $fnums  = $jinput->getString('fnums', null);
        $tags    = $jinput->getVar('tag', null);

        $fnums = ($fnums=='all')?'all':(array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

        $m_files = $this->getModel('Files');
        $m_application = new EmundusModelApplication();

        if ($fnums == "all") {
            $fnums = $m_files->getAllFnums();
		}

        foreach ($fnums as $fnum)
        {
            foreach ($tags as $tag){
                $hastags = $m_files->getTagsByIdFnumUser($tag, $fnum, $this->_user->id);
                if($hastags){
                    $result = $m_application->deleteTag($tag, $fnum);
                }else{
                    if(EmundusHelperAccess::asAccessAction(14, 'd', $this->_user->id, $fnum))
                    {
                        $result = $m_application->deleteTag($tag, $fnum);
                    }
                }
            }
        }
        unset($fnums);
        unset($tags);

        echo json_encode((object)(array('status' => true, 'msg' => JText::_('COM_EMUNDUS_TAGS_DELETE_SUCCESS'))));
        exit;
    }

    public function share()
    {
        $jinput = JFactory::getApplication()->input;
        $fnums = $jinput->getString('fnums', null);
        $actions = $jinput->getString('actions', null);
        $groups = $jinput->getString('groups', null);
        $evals = $jinput->getString('evals', null);

        $actions = (array) json_decode(stripslashes($actions));
        $fnums = (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);
        $model = $this->getModel('Files');
        if(is_array($fnums))
        {
            $validFnums = array();
            foreach($fnums as $fnum)
            {
                if(EmundusHelperAccess::asAccessAction(11, 'c', $this->_user->id, $fnum))
                {
                    $validFnums[] = $fnum;
                }
            }
            unset($fnums);
            if(!empty($groups))
            {
                $groups = (array) json_decode(stripslashes($groups));
                $res = $model->shareGroups($groups, $actions, $validFnums);
            }

            if(!empty($evals))
            {
                $evals = (array) json_decode(stripslashes($evals));
                $res = $model->shareUsers($evals, $actions, $validFnums);
            }

            if($res !== false)
            {
                $msg = JText::_('COM_EMUNDUS_ACCESS_SHARE_SUCCESS');
            }
            else
            {
                $msg = JText::_('COM_EMUNDUS_ACCESS_SHARE_ERROR');
            }
        }
        else
        {
            $fnums = $model->getAllFnums();
            $validFnums = array();
            foreach($fnums as $fnum)
            {
                if(EmundusHelperAccess::asAccessAction(11, 'c', $this->_user->id, $fnum))
                {
                    $validFnums[] = $fnum;
                }
            }
            unset($fnums);
            if($groups !== null)
            {
                $groups = (array) json_decode(stripslashes($groups));
                $res = $model->shareGroups($groups, $actions, $validFnums);
            }

            if($evals !== null)
            {
                $evals = (array) json_decode(stripslashes($evals));
                $res = $model->shareUsers($evals, $actions, $validFnums);
            }

            if($res !== false)
            {
                $msg = JText::_('COM_EMUNDUS_ACCESS_SHARE_SUCCESS');
            }
            else
            {
                $msg = JText::_('COM_EMUNDUS_ACCESS_SHARE_ERROR');

            }
        }
        echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
        exit;
    }

    public function getstate()
    {
        $model = $this->getModel('Files');


        $states = $model->getAllStatus();

        echo json_encode((object)(array('status' => true,
                                        'states' => $states,
                                        'state' => JText::_('COM_EMUNDUS_STATE'),
                                        'select_state' => JText::_('PLEASE_SELECT_STATE'))));
        exit;
    }

    public function updatestate()
    {
        $jinput = JFactory::getApplication()->input;
        $fnums = $jinput->getString('fnums', null);
        $state = $jinput->getInt('state', null);

        $fnums = (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);
        $model = $this->getModel('Files');
        if(is_array($fnums))
        {
            $validFnums = array();

            foreach($fnums as $fnum)
            {
                if(EmundusHelperAccess::asAccessAction(13, 'u', $this->_user->id, $fnum))
                {
                    $validFnums[] = $fnum;
                }
            }
            $res =  $model->updateState($validFnums, $state);
        }
        else
        {
            $fnums = $model->getAllFnums();
            $validFnums = array();

            foreach($fnums as $fnum)
            {
                if(EmundusHelperAccess::asAccessAction(13, 'u', $this->_user->id, $fnum))
                {
                    $validFnums[] = $fnum;
                }
            }
            $res = $model->updateState($validFnums, $state);

        }

        if($res !== false)
        {
            $msg = JText::_('COM_EMUNDUS_APPLICATION_STATE_SUCCESS');
        }
        else
        {
            $msg = JText::_('STATE_ERROR');

        }
        echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
        exit;
    }

    public function unlinkevaluators()
    {
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);
        $id = $jinput->getint('id', null);
        $group = $jinput->getString('group', null);

        $model = $this->getModel('Files');
        if($group == "true")
        {
            $res = $model->unlinkEvaluators($fnum, $id, true);
        }
        else
        {
            $res = $model->unlinkEvaluators($fnum, $id, false);
        }

        if($res)
        {
            $msg = JText::_('SUCCESS_SUPPR_EVAL');
        }
        else
        {
            $msg = JText::_('ERROR_SUPPR_EVAL');
        }

        echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
        exit;
    }

    public function getfnuminfos()
    {
	    if (!class_exists('EmundusControllerFiles'))
		    require_once(JPATH_ROOT.'/components/com_emundus/controllers/files.php');

	    $c_files = new EmundusControllerFiles();
	    $response = $c_files->getfnuminfos();

	    echo json_encode((object)$response);
        exit;
    }

    public function deletefile()
    {
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);
        $model = $this->getModel('Files');
        if (EmundusHelperAccess::asAccessAction(1, 'd', $this->_user->id, $fnum))
            $res = $model->changePublished($fnum);
        else
            $res = false;

        $result = array('status' => $res);

        echo json_encode((object)$result);
        exit;
    }

    public function getformelem()
    {
        $model = new EmundusModelDecision();

		$defaultElements = $model->getDecisionElementsName(0, 1);
        $elements = EmundusHelperFilters::getElements();
        $res = array('status' => true, 'elts' => $elements, 'defaults' => $defaultElements);

        echo json_encode((object)$res);
        exit;
    }

    function pdf(){
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);
        $student_id = $jinput->getString('student_id', null);

        if( !EmundusHelperAccess::asAccessAction(8, 'c', $this->_user->id, $fnum) )
            die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));

	    require_once (JPATH_COMPONENT . '/models/profile.php');
	    require_once (JPATH_COMPONENT . '/models/campaign.php');
        $m_profile = new EmundusModelProfile();
        $m_campaign = new EmundusModelCampaign();

        if (!empty($fnum)) {
            $candidature = $m_profile->getFnumDetails($fnum);
            $campaign = $m_campaign->getCampaignByID($candidature['campaign_id']);
            $name = $fnum.'-evaluation.pdf';
            $tmpName = JPATH_SITE.DS.'tmp'.DS.$name;
        }

        $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_evaluation'.$campaign['training'].'.php';

        if (!file_exists($file)) {
            $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_evaluation.php';
        }

        if (!file_exists(EMUNDUS_PATH_ABS.$student_id)) {
            mkdir(EMUNDUS_PATH_ABS.$student_id);
            chmod(EMUNDUS_PATH_ABS.$student_id, 0755);
        }

        require_once($file);
        pdf_evaluation(!empty($student_id)?$student_id:$this->_user->id, $fnum, true, null);

        exit();
    }

    function pdf_decision(){
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);
        $student_id = $jinput->getString('student_id', null);

        if( !EmundusHelperAccess::asAccessAction(8, 'c', $this->_user->id, $fnum) )
            die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));

	    require_once (JPATH_COMPONENT . '/models/profile.php');
	    require_once (JPATH_COMPONENT . '/models/campaign.php');
        $m_profile = new EmundusModelProfile();
        $m_campaign = new EmundusModelCampaign();

        if (!empty($fnum)) {
            $candidature = $m_profile->getFnumDetails($fnum);
            $campaign = $m_campaign->getCampaignByID($candidature['campaign_id']);
        }

        $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_decision_'.$campaign['training'].'.php';

        if (!file_exists($file)) {
            $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_decision.php';
        }

        if (!file_exists(EMUNDUS_PATH_ABS.$student_id)) {
            mkdir(EMUNDUS_PATH_ABS.$student_id);
            chmod(EMUNDUS_PATH_ABS.$student_id, 0755);
        }

        require_once($file);
        pdf_decision(!empty($student_id)?$student_id:$this->_user->id, $fnum);

        exit();
    }

    public function return_bytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last)
        {
            // Le modifieur 'G' est disponible depuis PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    public function sortArrayByArray($array,$orderArray)
    {
        $ordered = array();
        foreach($orderArray as $key)
        {
            if(array_key_exists($key,$array))
            {
                $ordered[$key] = $array[$key];
                unset($array[$key]);
            }
        }
        return $ordered + $array;
    }

    public function sortObjectByArray($object, $orderArray)
    {
        $ordered = array();
        $properties=get_object_vars($object);
        return $this->sortArrayByArray($properties,$orderArray);
    }

    public function create_file_csv() {
        $today = date_default_timezone_get();
        $name = md5($today.rand(0,10));
        $name = $name.'.csv';
        $chemin = JPATH_SITE.DS.'tmp'.DS.$name;

        if (!$fichier_csv = fopen($chemin, 'w+')){
            $result = array('status' => false, 'msg' => JText::_('ERROR_CANNOT_OPEN_FILE').' : '.$chemin);
            echo json_encode((object) $result);
            exit();
        }

        fprintf($fichier_csv, chr(0xEF).chr(0xBB).chr(0xBF));
        if (!fclose($fichier_csv)) {
            $result = array('status' => false, 'msg'=>JText::_('COM_EMUNDUS_EXPORTS_ERROR_CANNOT_CLOSE_CSV_FILE'));
            echo json_encode((object) $result);
            exit();
        }
        $result = array('status' => true, 'file' => $name);
        echo json_encode((object) $result);
        exit();
    }

    public function getfnums_csv() {
        $jinput = JFactory::getApplication()->input;
        $fnums = $jinput->getVar('fnums', null);
		$fnums = ($fnums=='all')?'all':(array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

        $model = $this->getModel('Files');
        if($fnums == "all")
        {
            $fnums = $model->getAllFnums();
        }
        $validFnums = array();
        foreach($fnums as $fnum)
        {
            if(EmundusHelperAccess::asAccessAction(13, 'u', $this->_user->id, $fnum)&& $fnum != 'em-check-all-all' && $fnum != 'em-check-all')
            {
                $validFnums[] = $fnum;
            }
        }
        $totalfile = sizeof($validFnums);
        $session = JFactory::getSession();
        $session->set('fnums_export', $validFnums);
        $result = array('status' => true, 'totalfile'=> $totalfile);
        echo json_encode((object) $result);
        exit();
    }

    public function getcolumn($elts) {
        return(array) json_decode(stripcslashes($elts));
    }

    public function generate_array() {
        $current_user = JFactory::getUser();

        if (!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
	        die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
        }

        $m_files = $this->getModel('Files');
        $m_application = new EmundusModelApplication();

        $session = JFactory::getSession();
        $fnums = $session->get('fnums_export');
		if (count($fnums) == 0) {
			$fnums = array($session->get('application_fnum'));
		}

        $jinput = JFactory::getApplication()->input;

        $file = $jinput->getVar('file', null, 'STRING');
        $totalfile = $jinput->getVar('totalfile', null);
        $start = $jinput->getInt('start', 0);
        $limit = $jinput->getInt('limit', 0);
        $nbcol = $jinput->getVar('nbcol', 0);
        $elts = $jinput->getString('elts', null);
        $objs = $jinput->getString('objs', null);

        $col = $this->getcolumn($elts);

        $colsup  = $this->getcolumn($objs);
        $colOpt = array();
        if (!$csv = fopen(JPATH_SITE.DS.'tmp'.DS.$file, 'a')){
            $result = array('status' => false, 'msg' => JText::_('ERROR_CANNOT_OPEN_FILE').' : '.$file);
            echo json_encode((object) $result);
            exit();
        }

        $elements = @EmundusHelperFiles::getElementsName(implode(',',$col));

        // re-order elements
        $ordered_elements = array();
        foreach ($col as $c) {
            $ordered_elements[$c] = $elements[$c];
        }
        $fnumsArray = $m_files->getFnumArray($fnums, $ordered_elements, 0, $start, $limit);

        // On met a jour la liste des fnums traités
        $fnums = array();
        foreach ($fnumsArray as $fnum) {
            array_push($fnums, $fnum['fnum']);
        }
        foreach ($colsup as $col) {
            $col = explode('.', $col);
            switch ($col[0]) {
                case "photo":
	                $photos = $m_files->getPhotos($fnums);
	                if (count($photos) > 0) {
		                $pictures = array();
		                foreach ($photos as $photo) {

			                $folder = JURI::base().EMUNDUS_PATH_REL.$photo['user_id'];

			                $link = '=HYPERLINK("'.$folder.'/tn_'.$photo['filename'] . '","'.$photo['filename'].'")';
			                $pictures[$photo['fnum']] = $link;
		                }
		                $colOpt['PHOTO'] = $pictures;
	                } else {
		                $colOpt['PHOTO'] = array();
	                }
                    break;
                case "forms":
                    $colOpt['forms'] = $m_application->getFormsProgress($fnums);
                    break;
                case "attachment":
                    $colOpt['attachment'] = $m_application->getAttachmentsProgress($fnums);
                    break;
                case "assessment":
                    $colOpt['assessment'] = @EmundusHelperFiles::getEvaluation('text', $fnums);
                    break;
                case "comment":
                    $colOpt['comment'] = $m_files->getCommentsByFnum($fnums);
                    break;
                case 'evaluators':
                    $colOpt['evaluators'] = @EmundusHelperFiles::createEvaluatorList($col[1], $m_files);
                    break;
            }
        }

        $status = $m_files->getStatusByFnums($fnums);
        $line = "";
        $element_csv=array();
        $i = $start;

        // On traite les en-têtes
        if ($start == 0) {
            $line = JText::_('COM_EMUNDUS_FILE_F_NUM')."\t".JText::_('COM_EMUNDUS_STATUS')."\t".JText::_('COM_EMUNDUS_FORM_LAST_NAME')."\t".JText::_('COM_EMUNDUS_FORM_FIRST_NAME')."\t".JText::_('COM_EMUNDUS_EMAIL')."\t".JText::_('COM_EMUNDUS_CAMPAIGN')."\t";
            $nbcol = 6;
            foreach ($ordered_elements as $fLine) {
                if ($fLine->element_name != 'fnum' && $fLine->element_name != 'code' && $fLine->element_name != 'campaign_id') {
                    $line .= $fLine->element_label . "\t";
                    $nbcol++;
                }
            }
            foreach ($colsup as $kOpt => $vOpt) {
                if ($vOpt=="forms" || $vOpt=="attachment") {
                    $line .= $vOpt . "(%)\t";
                } else {
                    $line .= $vOpt . "\t";
                }

                $nbcol++;
            }

            // On met les en-têtes dans le CSV
            $element_csv[] = $line;
            $line = "";
        }

        // On parcours les fnums
        foreach ($fnumsArray as $fnum) {
            // On traitre les données du fnum
            foreach($fnum as $k => $v) {
                if ($k != 'code' && $k != 'campaign_id' && $k != 'jos_emundus_campaign_candidature___campaign_id' && $k != 'c___campaign_id') {
                    if ($k === 'fnum') {
                        $line .= $v."\t";
                        $line .= $status[$v]['value']."\t";
                        $uid = intval(substr($v, 21, 7));
                        $userProfil = JUserHelper::getProfile($uid)->emundus_profile;
                        $line .= strtoupper($userProfil['lastname'])."\t";
                        $line .= $userProfil['firstname']."\t";
                    } elseif ($k === 'jos_emundus_evaluations___user') {
                        $line .= strip_tags(JFactory::getUser($v)->name)."\t";
                    } else {
                        $line .= strip_tags($v)."\t";
                    }
                }
            }
            // On ajoute les données supplémentaires
            foreach($colOpt as $kOpt => $vOpt) {
                switch ($kOpt) {
                    case "PHOTO":
                        $line .= JText::_('photo') . "\t";
                        break;
                    case "forms":
	                case "attachment":
                        if (array_key_exists($fnum['fnum'],$vOpt)) {
                            $val = $vOpt[$fnum['fnum']];
                            $line .= $val . "\t";
                        } else {
                            $line .= "\t";
                        }
                        break;
                    case "assessment":
                        $eval = '';
                        if (array_key_exists($fnum['fnum'],$vOpt)) {
                            $evaluations = $vOpt[$fnum['fnum']];
                            foreach ($evaluations as $evaluation) {
                                $eval .= $evaluation;
                                $eval .= chr(10) . '______' . chr(10);
                            }
                            $line .= $eval . "\t";
                        } else {
                            $line .= "\t";
                        }
                        break;
                    case "comment":
                        $comments = "";
                        if (array_key_exists($fnum['fnum'],$vOpt)) {
                            foreach ($colOpt['comment'] as $comment) {
                                if ($comment['fnum'] == $fnum['fnum']) {
                                    $comments .= $comment['reason'] . " | " . $comment['comment_body'] . "\rn";
                                }
                            }
                            $line .= $comments . "\t";
                        } else {
                            $line .= "\t";
                        }
                        break;
                    case 'evaluators':
                        if (array_key_exists($fnum['fnum'],$vOpt)) {
                            $line .= $vOpt[$fnum['fnum']] . "\t";
                        } else {
                            $line .= "\t";
                        }
                        break;
                }
            }
            // On met les données du fnum dans le CSV
            $element_csv[] = $line;
            $line ="";
            $i++;
        }
        // On remplit le fichier CSV
        foreach ($element_csv as $data) {
            $res = fputcsv($csv, explode("\t",$data),"\t");
            if (!$res) {
                $result = array('status' => false, 'msg'=>JText::_('ERROR_CANNOT_WRITE_TO_FILE'.' : '.$csv));
                echo json_encode((object) $result);
                exit();
            }
        }
        if (!fclose($csv)) {
            $result = array('status' => false, 'msg'=>JText::_('COM_EMUNDUS_EXPORTS_ERROR_CANNOT_CLOSE_CSV_FILE'));
            echo json_encode((object) $result);
            exit();
        }

        $start = $i;
        $dataresult = array('start' => $start, 'limit'=>$limit, 'totalfile'=> $totalfile,'methode'=>0,'elts'=>$elts, 'objs'=> $objs, 'nbcol' => $nbcol,'file'=>$file );
        $result = array('status' => true, 'json' => $dataresult);
        echo json_encode((object) $result);
        exit();
    }

    function get_mime_type($filename, $mimePath = '../etc') {
        $fileext = substr(strrchr($filename, '.'), 1);
        if (empty($fileext)) return (false);
        $regex = "/^([\w\+\-\.\/]+)\s+(\w+\s)*($fileext\s)/i";
        $lines = file("$mimePath/mime.types");
        foreach($lines as $line) {
            if (substr($line, 0, 1) == '#') continue; // skip comments
            $line = rtrim($line) . " ";
            if (!preg_match($regex, $line, $matches)) continue; // no match to the extension
            return ($matches[1]);
        }
        return (false); // no match at all
    }

    public function download()
    {
        $jinput = JFactory::getApplication()->input;

        $name = $jinput->getString('name', null);

        $file = JPATH_SITE.DS.'tmp'.DS.$name;

        if (file_exists($file)) {
            $mime_type = $this->get_mime_type($file);
            header('Content-type: application/'.$mime_type);
            header('Content-Disposition: inline; filename='.basename($file));
            header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: pre-check=0, post-check=0, max-age=0');
            header('Pragma: anytextexeptno-cache', true);
            header('Cache-control: private');
            header('Expires: 0');
            //header('Content-Transfer-Encoding: binary');
            //header('Content-Length: ' . filesize($file));
            //header('Accept-Ranges: bytes');

            ob_clean();
            flush();
            readfile($file);
            exit;
        } else {
            echo JText::_('COM_EMUNDUS_EXPORTS_FILE_NOT_FOUND').' : '.$file;
        }
    }
    /*
    *   Create a zip file containing all documents attached to application fil number
    */
    function export_zip($fnums)
    {
        $view           = JRequest::getCmd( 'view' );
        $current_user   = JFactory::getUser();
        if ((!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) &&
            $view != 'renew_application'
        )
            die( JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS') );

        require_once(JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        require_once(JPATH_SITE.DS.'libraries'.DS.'emundus'.DS.'pdf.php');

        $zip = new ZipArchive();

        $nom = date("Y-m-d").'_'.rand(1000,9999).'_x'.(count($fnums)-1).'.zip';
        $path = JPATH_SITE.DS.'tmp'.DS.$nom;
        $model = $this->getModel('Files');
        $files = $model->getFilesByFnums($fnums);

        if(file_exists($path))
            unlink($path);

        $users = array();
        foreach($fnums as $fnum)
        {
            $sid = intval(substr($fnum, -7));
            $users[$fnum] = JFactory::getUser($sid);

            if (!is_numeric($sid) || empty($sid)) {
                continue;
            }

            if($zip->open($path, ZipArchive::CREATE) == TRUE)
            {
                $dossier = EMUNDUS_PATH_ABS.$users[$fnum]->id.DS;

                application_form_pdf($users[$fnum]->id, $fnum, false);
                $application_pdf = 'application.pdf';

                $filename = $fnum.'_'.$users[$fnum]->name.DS.$application_pdf;

                if(!$zip->addFile($dossier.DS.$application_pdf, $filename)) {
                    echo "-".$dossier.$filename;
                    continue;

                }

                $zip->close();
            } else {
                die ("ERROR");
            }
        }

        if($zip->open($path, ZipArchive::CREATE) == TRUE)
        {
            $todel = array();
            $i=0;
            $error=0;
            foreach($files as $key => $file)
            {
                $filename = $file['fnum'].'_'.$users[$file['fnum']]->name.DS.$file['filename'];

                $dossier = EMUNDUS_PATH_ABS.$users[$file['fnum']]->id.DS;

                if(!$zip->addFile($dossier.$file['filename'], $filename)) {
                    echo "-".$dossier.$file['filename'];
                    continue;

                }
            }

            $zip->close();

        } else {
            die ("ERROR");
        }

        return $nom;
    }

    public function getDecisionFormUrl()
    {
        $response = ['status' => false, 'code' => 403, 'msg' => JText::_('ACCESS_DENIED')];
        $current_user = JFactory::getUser();

        if (EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
            $response = ['status' => true, 'code' => 200];
            $jinput = JFactory::getApplication()->input;
            $fnum = $jinput->getString('fnum', null);

            $h_files = new EmundusHelperFiles();
            $response['url'] = $h_files->getDecisionFormUrl($fnum, $current_user->id);
        }

        echo json_encode((object)$response);
        exit;
    }
}

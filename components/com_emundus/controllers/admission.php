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

use Joomla\CMS\Factory;

/**
 * eMundus Component Controller
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class EmundusControllerAdmission extends JControllerLegacy
{
	protected $app;

	private $user;
	private $_db;
	private $session;

	public function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'files.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'filters.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'list.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'emails.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'export.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'menu.php');

		$this->app = Factory::getApplication();
		if (version_compare(JVERSION, '4.0', '>'))
		{
			$this->user = $this->app->getIdentity();
			$this->session   = $this->app->getSession();
			$this->_db = Factory::getContainer()->get('DatabaseDriver');
		}
		else
		{
			$this->user = Factory::getUser();
			$this->session   = Factory::getSession();
			$this->_db = Factory::getDBO();
		}

        parent::__construct($config);
    }

    public function display($cachable = false, $urlparams = false) {

		if (!$this->input->get('view'))
		{
            $default = 'files';
			$this->input->set('view', $default);
        }
        parent::display();

    }

    // EMAIL APPLICANT WITH CUSTOM MESSAGE
    public function applicantEmail() {
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'emails.php');
        @EmundusHelperEmails::sendApplicantEmail();
    }

    public function clear() {
        @EmundusHelperFiles::clear();
        echo json_encode((object)(array('status' => true)));
        exit;
    }


	public function setfilters()
	{
		$filterName = $this->input->getString('id', null);
		$elements   = $this->input->getString('elements', null);
		$multi      = $this->input->getString('multi', null);

        @EmundusHelperFiles::clearfilter();

        if ($multi == "true") {
			$filterval = $this->input->get('val', array(), 'ARRAY');
		}
		else
		{
			$filterval = $this->input->getString('val', null);
        }

		$params = $this->session->get('filt_params');

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

		$this->session->set('filt_params', $params);
		$this->session->set('limitstart', 0);
        echo json_encode((object)(array('status' => true)));
        exit();
    }

	public function loadfilters()
	{
		$id = $this->input->getInt('id', null);

            $filter = @EmundusHelperFiles::getEmundusFilters($id);
            $params = (array) json_decode($filter->constraints);
            $params['select_filter'] = $id;
            $params =  json_decode($filter->constraints, true);

		$this->session->set('select_filter', $id);
		if (isset($params['filter_order']))
		{
			$this->session->set('filter_order', $params['filter_order']);
			$this->session->set('filter_order_Dir', $params['filter_order_Dir']);
            }

		$this->session->set('filt_params', $params['filter']);
            if (!empty($params['col']))
		{
			$this->session->set('adv_cols', $params['col']);
		}

            echo json_encode((object)(array('status' => true)));
            exit();
    }

	public function order()
	{
		$order = $this->input->getString('filter_order', null);

		$ancientOrder = $this->session->get('filter_order');
		$params       = $this->session->get('filt_params');
		$this->session->set('filter_order', $order);
        $params['filter_order'] = $order;

        if ($order == $ancientOrder) {

			if ($this->session->get('filter_order_Dir') == 'desc')
			{
				$this->session->set('filter_order_Dir', 'asc');
                $params['filter_order_Dir'] = 'asc';
			}
			else
			{
				$this->session->set('filter_order_Dir', 'desc');
                $params['filter_order_Dir'] = 'desc';
            }

		}
		else
		{
			$this->session->set('filter_order_Dir', 'asc');
            $params['filter_order_Dir'] = 'asc';
        }

		$this->session->set('filt_params', $params);
        echo json_encode((object)(array('status' => true)));
        exit;
    }

    public function setlimit()
    {
		$limit = $this->input->getInt('limit', null);

		$this->session->set('limit', $limit);
		$this->session->set('limitstart', 0);

        echo json_encode((object)(array('status' => true)));
        exit;
    }

    public function savefilters()
    {
		$name         = $this->input->get('name', null, 'POST', 'none', 0);
		$user_id      = $this->user->id;
		$itemid       = $this->input->get('Itemid', null, 'GET', 'none', 0);
		$filt_params  = $this->session->get('filt_params');
		$adv_params   = $this->session->get('adv_cols');
        $constraints    = array('filter'=>$filt_params, 'col'=>$adv_params);

        $constraints = json_encode($constraints);

        if (empty($itemid))
			$itemid = $this->input->get('Itemid', null, 'POST', 'none', 0);

        $time_date = (date('Y-m-d H:i:s'));

        $query = "INSERT INTO #__emundus_filters (time_date,user,name,constraints,item_id) values('".$time_date."',".$user_id.",'".$name."',".$this->_db->quote($constraints).",".$itemid.")";

		try
		{
        $this->_db->setQuery( $query );
			$this->_db->execute();

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
		$filter_id   = $this->input->getInt('id', null);

        $query="DELETE FROM #__emundus_filters WHERE id=".$filter_id;
        $this->_db->setQuery( $query );
		$result = $this->_db->execute();

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
		$limistart   = $this->input->getInt('limitstart', null);

		$limit      = intval($this->session->get('limit'));
        $limitstart = ($limit != 0 ? ($limistart > 1 ? (($limistart - 1) * $limit) : 0) : 0);

		$this->session->set('limitstart', $limitstart);

        echo json_encode((object)(array('status' => true)));
        exit;
    }

    public function getadvfilters() {
        $h_files = new EmundusHelperFiles;
        try {

            $elements = $h_files->getElements();
            echo json_encode((object)([
                'status'        => true,
                'default'       => JText::_('COM_EMUNDUS_PLEASE_SELECT'),
                'defaulttrash'  => JText::_('REMOVE_SEARCH_ELEMENT'),
                'options'       => $elements
            ]));
            exit;

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function addcomment()
    {
		$user        = $this->user->id;
		$fnums       = $this->input->getString('fnums', null);
		$title       = $this->input->getString('title', '');
		$comment     = $this->input->getString('comment', null);

        $fnums      = (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

        $m_application = new EmundusModelApplication();

        if (is_array($fnums)) {

            foreach ($fnums as $fnum) {
                if (EmundusHelperAccess::asAccessAction(10, 'c', $user, $fnum)) {

                    $aid = intval(substr($fnum, 21, 7));
                    $res = $m_application->addComment((array('applicant_id' => $aid, 'user_id' => $user, 'reason' => $title, 'comment_body' => $comment, 'fnum' => $fnum)));

                    if ($res !== true && !is_numeric($res)) {
                        echo json_encode(([
                            'status'    => false,
                            'msg'       => JText::_('COM_EMUNDUS_ERROR')
                        ]));
                        exit;
                    }

                }
            }

            echo json_encode(([
                'status'    => true,
                'msg'       => JText::_('COM_EMUNDUS_COMMENTS_SUCCESS')
            ]));
            exit;

        } elseif($fnums == 'all') {
            //all result find by the request
            $m_files = new EmundusModelFiles();
            $m_application = new EmundusModelApplication();

            $fnums = $m_files->getAllFnums();
            foreach ($fnums as $fnum) {
                if (EmundusHelperAccess::asAccessAction(10, 'c', $user, $fnum)) {
                    $aid = intval(substr($fnum, 14, count($fnum)));
                    $m_application->addComment((array('applicant_id' => $aid, 'user_id' => $user, 'reason' => $title, 'comment_body' => $comment, 'fnum' => $fnum)));
                }
            }
        }
    }

    public function getevsandgroups() {
	    $response = ['status' => false, 'code' => 403, 'msg' => JText::_('ACCESS_DENIED')];

		if (EmundusHelperAccess::asPartnerAccessLevel($this->user->id))
		{
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

		if (EmundusHelperAccess::asAccessAction(14, 'c', $this->user->id))
		{
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


		$fnums = $this->input->getString('fnums', null);
		$tag   = $this->input->get('tag', null);

		if (!empty($fnums) && !empty($tag)) {
			$m_files = $this->getModel('Files');
			$fnums = ($fnums == 'all') ? $m_files->getAllFnums() : (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

			if (!empty($fnums)) {
				$validFnums = [];
				foreach ($fnums as $fnum) {
					if ($fnum != 'em-check-all' && EmundusHelperAccess::asAccessAction(14, 'c', $this->user->id, $fnum))
					{
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

		$fnums = $this->input->getString('fnums', null);
		$tags  = $this->input->getVar('tag', null);

         $fnums = ($fnums=='all')?'all':(array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

         $m_files = new EmundusModelFiles();
         $m_application = new EmundusModelApplication();

         if ($fnums == "all")
             $fnums = $m_files->getAllFnums();

         foreach ($fnums as $fnum)
         {
             foreach ($tags as $tag){
				$hastags = $m_files->getTagsByIdFnumUser($tag, $fnum, $this->user->id);
                 if($hastags){
                     $result = $m_application->deleteTag($tag, $fnum);
                 }else{
					if (EmundusHelperAccess::asAccessAction(14, 'd', $this->user->id, $fnum))
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
		$fnums       = $this->input->getString('fnums', null);
		$actions     = $this->input->getString('actions', null);
		$groups      = $this->input->getString('groups', null);
		$evals       = $this->input->getString('evals', null);

        $actions    = (array) json_decode(stripslashes($actions));
        $fnums      = (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);
        $m_files    = $this->getModel('Files');

        if (is_array($fnums)) {

            $validFnums = array();
            foreach ($fnums as $fnum) {
				if (EmundusHelperAccess::asAccessAction(11, 'c', $this->user->id, $fnum))
                    $validFnums[] = $fnum;
            }

            unset($fnums);
            if (!empty($groups)) {
                $groups = (array) json_decode(stripslashes($groups));
                $res = $m_files->shareGroups($groups, $actions, $validFnums);
            }

            if (!empty($evals)) {
                $evals = (array) json_decode(stripslashes($evals));
                $res = $m_files->shareUsers($evals, $actions, $validFnums);
            }

            if ($res !== false)
                $msg = JText::_('COM_EMUNDUS_ACCESS_SHARE_SUCCESS');
            else
                $msg = JText::_('COM_EMUNDUS_ACCESS_SHARE_ERROR');

        } elseif($fnums == 'all') {

            $fnums = $m_files->getAllFnums();
            $validFnums = array();
            foreach ($fnums as $fnum) {
				if (EmundusHelperAccess::asAccessAction(11, 'c', $this->user->id, $fnum))
                    $validFnums[] = $fnum;
            }

            unset($fnums);
            if ($groups !== null) {
                $groups = (array) json_decode(stripslashes($groups));
                $res = $m_files->shareGroups($groups, $actions, $validFnums);
            }

            if ($evals !== null) {
                $evals = (array) json_decode(stripslashes($evals));
                $res = $m_files->shareUsers($evals, $actions, $validFnums);
            }

            if ($res !== false)
                $msg = JText::_('COM_EMUNDUS_ACCESS_SHARE_SUCCESS');
            else
                $msg = JText::_('COM_EMUNDUS_ACCESS_SHARE_ERROR');
        }

        echo json_encode((object)([
            'status'    => $res,
            'msg'       => $msg
        ]));
        exit;
    }

    public function getstate() {
        $m_files = $this->getModel('Files');
        $states  = $m_files->getAllStatus();

        echo json_encode((object)([
            'status'        => true,
            'states'        => $states,
            'state'         => JText::_('COM_EMUNDUS_STATE'),
            'select_state'  => JText::_('PLEASE_SELECT_STATE')
        ]));
        exit;
    }

	public function updatestate()
	{
		$fnums = $this->input->getString('fnums', null);
		$state = $this->input->getInt('state', null);

        $fnums = (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

        $m_files = $this->getModel('Files');

        if (is_array($fnums)) {

            $validFnums = array();

            foreach ($fnums as $fnum) {
				if (EmundusHelperAccess::asAccessAction(13, 'u', $this->user->id, $fnum))
                    $validFnums[] = $fnum;
            }
            $res = $m_files->updateState($validFnums, $state);

        } elseif($fnums == 'all') {

            $fnums = $m_files->getAllFnums();
            $validFnums = array();

            foreach ($fnums as $fnum) {
				if (EmundusHelperAccess::asAccessAction(13, 'u', $this->user->id, $fnum))
                    $validFnums[] = $fnum;
            }
            $res = $m_files->updateState($validFnums, $state);
        }

        if ($res !== false)
            $msg = JText::_('COM_EMUNDUS_APPLICATION_STATE_SUCCESS');
        else
            $msg = JText::_('STATE_ERROR');

        echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
        exit;
    }

	public function unlinkevaluators()
	{

		$fnum  = $this->input->getString('fnum', null);
		$id    = $this->input->getint('id', null);
		$group = $this->input->getString('group', null);

        $m_files = $this->getModel('Files');

        if ($group == "true")
            $res = $m_files->unlinkEvaluators($fnum, $id, true);
        else
            $res = $m_files->unlinkEvaluators($fnum, $id, false);

        if ($res)
            $msg = JText::_('SUCCESS_SUPPR_EVAL');
        else
            $msg = JText::_('ERROR_SUPPR_EVAL');

        echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
        exit;
    }

	public function getfnuminfos()
	{

		$fnum = $this->input->getString('fnum', null);

        $res        = false;
        $fnumInfos  = null;

        if ($fnum != null) {
            $m_files    = $this->getModel('Files');
            $fnumInfos  = $m_files->getFnumInfos($fnum);

            if ($fnum !== false)
                $res = true;
        }

		$this->session->set('application_fnum', $fnum);
        echo json_encode((object)(array('status' => $res, 'fnumInfos' => $fnumInfos)));
        exit;
    }

	public function deletefile()
	{

		$fnum = $this->input->getString('fnum', null);

        $m_files  = $this->getModel('Files');

		if (EmundusHelperAccess::asAccessAction(1, 'd', $this->user->id, $fnum))
            $res = $m_files->changePublished($fnum);
        else
            $res = false;

        $result = array('status' => $res);

        echo json_encode((object)$result);
        exit;
    }

	public function getformelem()
	{

		$form = $this->input->getString('form', null);
		$code = $this->input->get('code', null);
        $code = explode(',', $code);

		require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'admission.php');
        $m_admission = new EmundusModelAdmission();
        $h_files = new EmundusHelperFiles;

        $defaultElements = $m_admission->getAdmissionElementsName(0, 1, $code);
        if (!empty($defaultElements)) {
            foreach ($defaultElements as $kde => $de) {
                if ($de->element_name == 'id' || $de->element_name == 'fnum' || $de->element_name == 'student_id' || $de->element_name == 'user') {
	                unset($defaultElements[$kde]);
                }
            }
        }

        if ($form == "admission") {
	        $elements = $m_admission->getApplicantAdmissionElementsName(0, 0, $code);
        } elseif ($form == "decision") {
	        $elements = $m_admission->getAdmissionElementsName(0, 0, $code);
        } else {
	        $elements = $h_files->getElements();
        }

        $res = [
            'status'    => true,
            'elts'      => $elements,
            'defaults'  => $defaultElements
        ];

        echo json_encode((object)$res);
        exit;
    }

	function pdf_admission()
	{
		$fnum        = $this->input->getString('fnum', null);
		$student_id  = $this->input->getString('student_id', null);

		if (!EmundusHelperAccess::asAccessAction(8, 'c', $this->user->id, $fnum))
		{
			if (EmundusHelperAccess::asApplicantAccessLevel($this->user->id))
			{
				$student_id = $this->user->id;
        }
			else
			{
                die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
            }
        }

		require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'profile.php');
		require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'campaign.php');

        $m_profile  = new EmundusModelProfile();
        $m_campaign = new EmundusModelCampaign();

        if (!empty($fnum)) {
            $candidature    = $m_profile->getFnumDetails($fnum);
            $campaign       = $m_campaign->getCampaignByID($candidature['campaign_id']);
        }

        $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_admission_'.$campaign['training'].'.php';

        if (!file_exists($file))
            $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_admission.php';

        if (!file_exists(EMUNDUS_PATH_ABS.$student_id)) {
            mkdir(EMUNDUS_PATH_ABS.$student_id);
            chmod(EMUNDUS_PATH_ABS.$student_id, 0755);
        }

        require_once($file);
		pdf_admission(!empty($student_id) ? $student_id : $this->user->id, $fnum);

        exit();
    }

    public function return_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch ($last) {
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

    public function sortArrayByArray($array,$orderArray) {

        $ordered = array();
        foreach ($orderArray as $key) {
            if (array_key_exists($key,$array)) {
                $ordered[$key] = $array[$key];
                unset($array[$key]);
            }
        }
        return $ordered + $array;
    }

    public function sortObjectByArray($object, $orderArray) {
        $ordered = array();
        $properties=get_object_vars($object);
        return $this->sortArrayByArray($properties,$orderArray);
    }

    public function create_file_csv() {

        $today  = date_default_timezone_get();
        $name   = md5($today.rand(0,10));
        $name   = $name.'.csv';
        $chemin = JPATH_SITE.DS.'tmp'.DS.$name;

        if (!$fichier_csv = fopen($chemin, 'w+')) {
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


		$fnums_post  = $this->input->getVar('fnums', null);
        $fnums_array = ($fnums_post=='all')?'all':(array) json_decode(stripslashes($fnums_post), false, 512, JSON_BIGINT_AS_STRING);
        $m_files = $this->getModel('Files');

        if ($fnums_array == 'all') {
            $fnums = $m_files->getAllFnums();
        } else {
            $fnums = array();
            foreach ($fnums_array as $key => $value) {
                $fnums[] = $value;
            }
        }

        $validFnums = array();
        foreach ($fnums as $fnum) {
			if (EmundusHelperAccess::asAccessAction(13, 'u', $this->user->id, $fnum) && $fnum != 'em-check-all-all' && $fnum != 'em-check-all')
                $validFnums[] = $fnum;
        }
        $totalfile = sizeof($validFnums);

		$this->session = $this->session;
		$this->session->set('fnums_export', $validFnums);

        $result = array('status' => true, 'totalfile'=> $totalfile);
        echo json_encode((object) $result);
        exit();
    }

    public function getcolumn($elts) {
        return(array) json_decode(stripcslashes($elts));
    }

	public function generate_array()
	{

		if (!@EmundusHelperAccess::asPartnerAccessLevel($this->user->id))
            die( JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS') );

        $m_files        = new EmundusModelFiles();
        $m_application  = new EmundusModelApplication();

		$fnums   = $this->session->get('fnums_export');
		if (count($fnums) == 0)
		{
			$fnums = array($this->session->get('application_fnum'));
		}


		$file      = $this->input->getVar('file', null, 'STRING');
		$totalfile = $this->input->getVar('totalfile', null);
		$start     = $this->input->getInt('start', 0);
		$limit     = $this->input->getInt('limit', 0);
		$nbcol     = $this->input->getVar('nbcol', 0);
		$elts      = $this->input->getString('elts', null);
		$objs      = $this->input->getString('objs', null);

        $col = $this->getcolumn($elts);

        $colsup = $this->getcolumn($objs);
        $colOpt = array();
        if (!$csv = fopen(JPATH_SITE.DS.'tmp'.DS.$file, 'a')) {
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
        $fnumsArray = $m_files->getFnumArray($fnums, $ordered_elements, 0, $start, $limit, 0);

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
        $line ="";
        $element_csv=array();
        $i = $start;

        // On traite les en-têtes
        if ($start == 0) {
            $line=JText::_('COM_EMUNDUS_FILE_F_NUM')."\t".JText::_('COM_EMUNDUS_STATUS')."\t".JText::_('COM_EMUNDUS_FORM_LAST_NAME')."\t".JText::_('COM_EMUNDUS_FORM_FIRST_NAME')."\t".JText::_('COM_EMUNDUS_EMAIL')."\t".JText::_('COM_EMUNDUS_CAMPAIGN')."\t";
            $nbcol = 6;

            foreach ($ordered_elements as $fKey => $fLine) {
                if ($fLine->element_name != 'fnum' && $fLine->element_name != 'code' && $fLine->element_name != 'campaign_id') {
                    $line .= $fLine->element_label . "\t";
                    $nbcol++;
                }
            }

            foreach ($colsup as $kOpt => $vOpt) {
                if ($vOpt=="forms" || $vOpt=="attachment")
                    $line .= $vOpt . "(%)\t";
                else
                    $line .= $vOpt . "\t";

                $nbcol++;
            }

            // On met les en-têtes dans le CSV
            $element_csv[] = $line;
            $line = "";
        }

        // On parcours les fnums
        foreach ($fnumsArray as $fnum) {
            // On traitre les données du fnum
            foreach ($fnum as $k => $v) {
                if ($k != 'code' && $k != 'campaign_id' && $k != 'jos_emundus_campaign_candidature___campaign_id' && $k != 'jos_emundus_final_grade___campaign_id' && $k != 'c___campaign_id') {

                    if ($k === 'fnum') {

                        $line .= $v."\t";
                        $line .= $status[$v]['value']."\t";
                        $uid = intval(substr($v, 21, 7));
                        $userProfil = JUserHelper::getProfile($uid)->emundus_profile;
                        $line .= strtoupper($userProfil['lastname'])."\t";
                        $line .= $userProfil['firstname']."\t";

                    } elseif ($k === 'jos_emundus_evaluations___user' || $k === "user")
                        $line .= strip_tags(JFactory::getUser($v)->name)."\t";
                    else
                        $line .= strip_tags($v)."\t";
                }
            }

            // On ajoute les données supplémentaires
            foreach ($colOpt as $kOpt => $vOpt) {
                switch ($kOpt) {

                    case "PHOTO":
                        if (array_key_exists($fnum['fnum'],$vOpt)) {
                            $val = $vOpt[$fnum['fnum']];
                            // Img comes in form of html tag
                            $dom_document = new DOMDocument();
                            $xpath = new DOMXPath($dom_document->loadHTML($val));
                            $src = $xpath->evaluate("string(//img/@src)");
                            $line .= $src . "\t";
                            // This only prints the link to the image, in order to add an img to the csv you have to superpose it over a cell
                        } else {
                            $line .= "\t";
                        }
                        break;

                    case "forms":
                        if (array_key_exists($fnum['fnum'],$vOpt)) {
                            $val = $vOpt[$fnum['fnum']];
                            $line .= $val . "\t";
                        } else {
                            $line .= "\t";
                        }
                        break;

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
        foreach ($element_csv as $data)
        {
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

        if (empty($fileext))
            return (false);

        $regex = "/^([\w\+\-\.\/]+)\s+(\w+\s)*($fileext\s)/i";
        $lines = file("$mimePath/mime.types");

        foreach($lines as $line) {
            if (substr($line, 0, 1) == '#')
                continue; // skip comments

            $line = rtrim($line) . " ";

            if (!preg_match($regex, $line, $matches))
                continue; // no match to the extension

            return ($matches[1]);
        }
        return (false); // no match at all
    }

	public function download()
	{

		$name = $this->input->getString('name', null);

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
    function export_zip($fnums) {
		$view         = $this->input->get('view');

		if ((!@EmundusHelperAccess::asPartnerAccessLevel($this->user->id)) && $view != 'renew_application')
		{
            die( JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS') );
		}

		require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'helpers' . DS . 'access.php');
        require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php');

        $zip = new ZipArchive();

        $nom        = date("Y-m-d").'_'.rand(1000,9999).'_x'.(count($fnums)-1).'.zip';
        $path       = JPATH_SITE.DS.'tmp'.DS.$nom;

        $m_files    = $this->getModel('Files');
        $files      = $m_files->getFilesByFnums($fnums);

        if (file_exists($path))
            unlink($path);

        $users = array();
        foreach ($fnums as $fnum) {
            $sid = intval(substr($fnum, -7));
            $users[$fnum] = JFactory::getUser($sid);

            if (!is_numeric($sid) || empty($sid))
                continue;

			if ($zip->open($path, ZipArchive::CREATE) == true)
			{
                $dossier = EMUNDUS_PATH_ABS.$users[$fnum]->id.DS;

                application_form_pdf($users[$fnum]->id, $fnum, false);
                $application_pdf = 'application.pdf';

                $filename = $fnum.'_'.$users[$fnum]->name.DS.$application_pdf;

                if (!$zip->addFile($dossier.DS.$application_pdf, $filename)) {
                    echo "-".$dossier.$filename;
                    continue;
                }

                $zip->close();
            } else die ("ERROR");
        }

		if ($zip->open($path, ZipArchive::CREATE) == true)
		{
            $todel = array();
            $i=0;
            $error=0;
            foreach ($files as $key => $file) {
                $filename = $file['fnum'].'_'.$users[$file['fnum']]->name.DS.$file['filename'];

                $dossier = EMUNDUS_PATH_ABS.$users[$file['fnum']]->id.DS;

                if (!$zip->addFile($dossier.$file['filename'], $filename)) {
                    echo "-".$dossier.$file['filename'];
                    continue;
                }
            }
            $zip->close();
        } else die ("ERROR");

        return $nom;
    }

    function changeadmissionvalue() {

        JFactory::getDocument()->setMimeEncoding( 'application/json' );
        JResponse::setHeader('Content-Disposition','attachment;filename="toggle_radio.json"');


		$fnum  = $this->input->getString('fnum', null);
		$fid   = $this->input->getString('fabrik_id', null);
		$value = $this->input->getString('value', null);

        $m_admission = new EmundusModelAdmission();

        // Check if fnum is found in DB table in order to determine if we do a create or update
        $exists = $m_admission->getAdmissionFnum($fnum);
        if (!isset($exists) || empty($exists))
            $create = true;
        else $create = false;

        if ($create === true) {

			if (!EmundusHelperAccess::asAccessAction(32, 'c', $this->user->id, $fnum))
                die('ACCESS DENIED');

            $m_admission->setAdmissionByFabrikElementsId($fnum, $fid, $value);
            $data = [ 'status' => true ];

        } else {

			if (!EmundusHelperAccess::asAccessAction(32, 'u', $this->user->id, $fnum))
                die('ACCESS DENIED');

            $m_admission->updateAdmissionByFabrikElementsId($fnum, $fid, $value);
            $data = [ 'status' => true ];

        }

        echo json_encode( $data );
        JFactory::getApplication()->close();
    }
}

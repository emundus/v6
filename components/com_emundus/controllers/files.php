<?php
/**
 * @package    Joomla
 * @subpackage eMundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
//use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

jimport('joomla.application.component.controller');
jimport( 'joomla.user.helper' );

/**
 * eMundus Component Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 */
//error_reporting(E_ALL);
/**
 * Class EmundusControllerFiles
 */
class EmundusControllerFiles extends JControllerLegacy
{
    /**
     * @var JUser|null
     */
    var $_user = null;
    /**
     * @var JDatabase|null
     */
    var $_db = null;

    /**
     * @param array $config
     */
    public function __construct($config = array())
    {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'files.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'filters.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'list.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'emails.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'export.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'admission.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');

        $this->_user = JFactory::getSession()->get('emundusUser');

        $this->_db = JFactory::getDBO();

        parent::__construct($config);
    }

    /**
     * @param bool $cachable
     * @param bool $urlparams
     */
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

    function data_to_img($match) {
	    list(, $img, $type, $base64, $end) = $match;

	    $bin = base64_decode($base64);
	    $md5 = md5($bin);   // generate a new temporary filename
	    $fn = "tmp/$md5.$type";
	    file_exists($fn) or file_put_contents($fn, $bin);

	    return "$img$fn$end";  // new <img> tag
	}

////// EMAIL APPLICANT WITH CUSTOM MESSAGE///////////////////
    /**
     *
     */
    public function applicantemail()
    {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'emails.php');
        $h_emails = new EmundusHelperEmails;
        $h_emails->sendApplicantEmail();
    }

    /**
     *
     */
    public function groupmail()
    {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'emails.php');
        $h_emails = new EmundusHelperEmails;
        $h_emails->sendGroupEmail();
    }

    /**
     *
     */
    public function clear()
    {
        $h_files = new EmundusHelperFiles;
        $h_files->clear();
        echo json_encode((object)(array('status' => true)));
        exit;
    }

    /**
     *
     */
    public function setfilters() {
        $jinput     = JFactory::getApplication()->input;
        $filterName = $jinput->getString('id', null);
        $elements   = $jinput->getString('elements', null);
        $multi      = $jinput->getString('multi', null);

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

    /**
     * @throws Exception
     */
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

            if (isset($params['filter_order'])) {
                $session->set('filter_order', $params['filter_order']);
                $session->set('filter_order_Dir', $params['filter_order_Dir']);
            }

            $session->set('filt_params', $params['filter']);

            if (!empty($params['col']))
                $session->set('adv_cols', $params['col']);

            echo json_encode((object)(array('status' => true)));
            exit();

        } catch(Exception $e) {
            throw new Exception;
        }
    }

    /**
     *
     */
    public function order() {
        $jinput = JFactory::getApplication()->input;
        $order = $jinput->getString('filter_order', null);

        $session = JFactory::getSession();
        $ancientOrder = $session->get('filter_order');
        $params = $session->get('filt_params');
        $session->set('filter_order', $order);

        $params['filter_order'] = $order;

        if ($order == $ancientOrder) {

            if ($session->get('filter_order_Dir') == 'desc') {

                $session->set('filter_order_Dir', 'asc');
                $params['filter_order_Dir'] = 'asc';

            } else {

                $session->set('filter_order_Dir', 'desc');
                $params['filter_order_Dir'] = 'desc';

            }

        } else {

            $session->set('filter_order_Dir', 'asc');
            $params['filter_order_Dir'] = 'asc';

        }

        $session->set('filt_params', $params);
        echo json_encode((object)(array('status' => true)));
        exit;
    }

    /**
     *
     */
    public function setlimit() {
        $jinput = JFactory::getApplication()->input;
        $limit = $jinput->getInt('limit', null);

        $session = JFactory::getSession();
        $session->set('limit', $limit);
        $session->set('limitstart', 0);

        echo json_encode((object)(array('status' => true)));
        exit;
    }

    /**
     *
     */
    public function savefilters() {
        $name = JRequest::getVar('name', null, 'POST', 'none',0);
        $current_user = JFactory::getUser();
        $user_id = $current_user->id;
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);

        $session = JFactory::getSession();
        $filt_params = $session->get('filt_params');
        $adv_params = $session->get('adv_cols');
        $constraints = array('filter'=>$filt_params, 'col'=>$adv_params);

        $constraints = json_encode($constraints);

        if (empty($itemid)) {
	        $itemid = JRequest::getVar('Itemid', null, 'POST', 'none', 0);
        }

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

    /**
     *
     */
    public function deletefilters() {
        $jinput = JFactory::getApplication()->input;
        $filter_id = $jinput->getInt('id', null);

        $query = "DELETE FROM #__emundus_filters WHERE id=".$filter_id;
        $this->_db->setQuery($query);
        $result = $this->_db->Query();

        if ($result != 1) {
            echo json_encode((object)(array('status' => false)));
            exit;
        } else {
            echo json_encode((object)(array('status' => true)));
            exit;
        }
    }

    /**
     *
     */
    public function setlimitstart()
    {
        $jinput = JFactory::getApplication()->input;
        $limistart = $jinput->getInt('limitstart', null);
        $session = JFactory::getSession();
        $limit = intval($session->get('limit'));
        $limitstart = ($limit != 0 ? ($limistart > 1 ? (($limistart - 1) * $limit) : 0) : 0);
        $session->set('limitstart', $limitstart);

        echo json_encode((object)(array('status' => true)));
        exit;
    }

    /**
     * @throws Exception
     */
    public function getadvfilters() {
        try {
            $elements = @EmundusHelperFiles::getElements();
            echo json_encode((object)(array('status' => true, 'default' => JText::_('PLEASE_SELECT'), 'defaulttrash' => JText::_('REMOVE_SEARCH_ELEMENT'), 'options' => $elements)));
            exit;
        } catch(Exception $e) {
	        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }

    /**
     * @throws Exception
     */
    public function getbox() {
        try {
            $jinput = JFactory::getApplication()->input;
            $id     = $jinput->getInt('id', null);
            $index  = $jinput->getInt('index', null);

            $session = JFactory::getSession();
            $params = $session->get('filt_params');

            $h_files = new EmundusHelperFiles;
            $element = $h_files->getElementsName($id);

            $tab_name = (isset($element[$id]->table_join)?$element[$id]->table_join:$element[$id]->tab_name);
            $key = $tab_name.'.'.$element[$id]->element_name;
            $params['elements'][$key] = '';

            $advCols = $session->get('adv_cols');

            if (!$session->has('adv_cols') || count($advCols) == 0) {
                $advCols = array($index => $id);
            } else {
                $advCols = $session->get('adv_cols');
                if (isset($advCols[$index])) {
                    $lastId = @$advCols[$index];
                    if (!in_array($id, $advCols)) {
                        $advCols[$index] = $id;
                    }
                    if (array_key_exists($index, $advCols)) {
                        $lastElt = $h_files->getElementsName($lastId);
                        $tab_name = (isset($lastElt[$lastId]->table_join)?$lastElt[$lastId]->table_join:$lastElt[$lastId]->tab_name);
                        unset($params['elements'][$tab_name . '.' . $lastElt[$lastId]->element_name]);
                    }
                } else {
	                $advCols[$index] = $id;
                }
            }
            $session->set('filt_params', $params);
            $session->set('adv_cols', $advCols);

            $html = $h_files->setSearchBox($element[$id], '', $tab_name . '.' . $element[$id]->element_name, $index);

            echo json_encode((object)(array('status' => true, 'default' => JText::_('PLEASE_SELECT'), 'defaulttrash' => JText::_('REMOVE_SEARCH_ELEMENT'), 'html' => $html)));
            exit;
        } catch(Exception $e) {
	        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }

    /**
     *
     */
    public function deladvfilter() {
        $jinput = JFactory::getApplication()->input;
        $name   = $jinput->getString('elem', null);
        $id     = $jinput->getInt('id',null);

        $session = JFactory::getSession();
        $params = $session->get('filt_params');
        $advCols = $session->get('adv_cols');
        unset($params['elements'][$name]);
        unset($advCols[$id]);
        $session->set('filt_params', $params);
        $session->set('adv_cols', $advCols);

        echo json_encode((object)(array('status' => true)));
        exit;
    }

    /**
     * Add a comment on a file.
     * @since 6.0
     */
    public function addcomment() {

        $jinput = JFactory::getApplication()->input;
        $user   = JFactory::getUser()->id;
        $fnums  = $jinput->getString('fnums', null);
        $title  = $jinput->getString('title', '');
        $comment = $jinput->getString('comment', null);

        $fnums = (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);
        $fnumErrorList = [];
        $m_application = $this->getModel('Application');

        foreach ($fnums as $fnum) {
            if (EmundusHelperAccess::asAccessAction(10, 'c', $user, $fnum)) {
                $aid = intval(substr($fnum, 21, 7));
                $res = $m_application->addComment((array('applicant_id' => $aid, 'user_id' => $user, 'reason' => $title, 'comment_body' => $comment, 'fnum' => $fnum)));
                if (empty($res)) {
                    $fnumErrorList[] = $fnum;
                }
            } else {
                $fnumErrorList[] = $fnum;
            }
        }

        if(empty($fnumErrorList)) {
            echo json_encode((object)(array('status' => true, 'msg' => JText::_('COMMENT_SUCCESS'), 'id' => $res)));
        } else {
            echo json_encode((object)(array('status' => false, 'msg' => JText::_('ERROR'). implode(', ', $fnumErrorList))));
        }
        exit;
    }

    /*
     * Gets all tags.
     * @since 6.0
     */
    public function gettags() {
        $m_files = $this->getModel('Files');
        $tags = $m_files->getAllTags();

        echo json_encode((object)(array('status' => true,
            'tags' => $tags,
            'tag' => JText::_('TAGS'),
            'select_tag' => JText::_('PLEASE_SELECT_TAG'))));
        exit;
    }

    /**
     * Add a tag to an application.
     * @since 6.0
     */
    public function tagfile() {
        $jinput = JFactory::getApplication()->input;
        $fnums  = $jinput->getString('fnums', null);
        $tag    = $jinput->get('tag', null);
        $fnums = ($fnums=='all')?'all':(array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);
        $m_files = $this->getModel('Files');

        if ($fnums == "all") {
	        $fnums = $m_files->getAllFnums();
        }

        $validFnums = array();

        foreach ($fnums as $fnum) {
            if ($fnum != 'em-check-all' && EmundusHelperAccess::asAccessAction(14, 'c', $this->_user->id, $fnum)) {
                $validFnums[] = $fnum;
            }
        }
        unset($fnums);
        $m_files->tagFile($validFnums, $tag);
        $tagged = $m_files->getTaggedFile($tag);

        echo json_encode((object)(array('status' => true, 'msg' => JText::_('TAG_SUCCESS'), 'tagged' => $tagged)));
        exit;
    }

    public function deletetags() {

        $jinput = JFactory::getApplication()->input;
        $fnums  = $jinput->getString('fnums', null);
        $tags    = $jinput->getVar('tag', null);

        $fnums = ($fnums=='all')?'all':(array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

        $m_files = $this->getModel('Files');
        $m_application = $this->getModel('application');

        if ($fnums == "all") {
	        $fnums = $m_files->getAllFnums();
        }

        foreach ($fnums as $fnum) {
            if ($fnum != 'em-check-all') {
                foreach ($tags as $tag) {
                    $hastags = $m_files->getTagsByIdFnumUser($tag, $fnum, $this->_user->id);
                    if  ($hastags) {
                        $m_application->deleteTag($tag, $fnum);
                    } else {
                        if (EmundusHelperAccess::asAccessAction(14, 'd', $this->_user->id, $fnum)) {
                            $m_application->deleteTag($tag, $fnum);
                        }
                    }
                }
            }
        }
        unset($fnums);
        unset($tags);

        echo json_encode((object)(array('status' => true, 'msg' => JText::_('TAGS_DELETE_SUCCESS'))));
        exit;
    }


    /**
     *
     */
    public function share() {
        $jinput = JFactory::getApplication()->input;
        $actions = $jinput->getString('actions', null);
        $groups = $jinput->getString('groups', null);
        $evals = $jinput->getString('evals', null);
        $notify = $jinput->getVar('notify', 'false');

        $actions = (array) json_decode(stripslashes($actions));

        $m_files = $this->getModel('Files');

	$fnums_post = $jinput->getString('fnums', null);
        $fnums_array = ($fnums_post=='all')?'all':(array) json_decode(stripslashes($fnums_post), false, 512, JSON_BIGINT_AS_STRING);

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
            if (EmundusHelperAccess::asAccessAction(11, 'c', $this->_user->id, $fnum) && $fnum != 'em-check-all') {
                $validFnums[] = $fnum;
            }
        }

        unset($fnums);
        if (count($validFnums) > 0) {
            if (!empty($groups)) {
                $groups = (array) json_decode(stripslashes($groups));
                $res = $m_files->shareGroups($groups, $actions, $validFnums);
            }

            if (!empty($evals)) {
                $evals = (array) json_decode(stripslashes($evals));
                $res = $m_files->shareUsers($evals, $actions, $validFnums);
            }

            if ($res !== false) {
                $msg = JText::_('SHARE_SUCCESS');
            } else {
                $msg = JText::_('SHARE_ERROR');
            }
        } else {
            $fnums = $m_files->getAllFnums();
            if ($groups !== null) {
                $groups = (array) json_decode(stripslashes($groups));
                $res = $m_files->shareGroups($groups, $actions, $fnums);
            }

            if ($evals !== null) {
                $evals = (array) json_decode(stripslashes($evals));
                $res = $m_files->shareUsers($evals, $actions, $fnums);
            }

            if ($res !== false) {
                $msg = JText::_('SHARE_SUCCESS');
            } else {
                $msg = JText::_('SHARE_ERROR');
            }
        }

        if ($notify !== 'false' && $res !== false && !empty($evals)) {

        	if (empty($fnums)) {
        		$fnums = $validFnums;
	        }

			require_once (JPATH_COMPONENT.DS.'controllers'.DS.'messages.php');
	        require_once (JPATH_COMPONENT.DS.'models'.DS.'users.php');
	        require_once (JPATH_COMPONENT.DS.'models'.DS.'profile.php');

	        $c_messages = new EmundusControllerMessages();
			$m_users = new EmundusModelUsers();
			$m_profile = new EmundusModelProfile();

	        $evals = $m_users->getUsersByIds($evals);

	        $menu = JFactory::getApplication()->getMenu();

	        $fnums = $m_files->getFnumsInfos($fnums);

			foreach ($evals as $eval) {

				$menutype = $m_profile->getProfileByApplicant($eval->id)['menutype'];
				$items = $menu->getItems('menutype', $menutype);

				if (empty($items)) {
					echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
					exit;
				}

				// We're getting the first link in the user's menu that's from com_emundus, which is PROBABLY a files/evaluation view, but this does not guarantee it.
				$index = 0;
				foreach ($items as $k => $item) {
					if ($item->component === 'com_emundus') {
						$index = $k;
						break;
					}
				}

				if (JFactory::getConfig()->get('sef') == 1) {
					$userLink = $items[$index]->alias;
				} else {
					$userLink = $items[$index]->link.'&Itemid='.$items[0]->id;
				}

				$fnumList = '<ul>';
				foreach ($fnums as $fnum) {
					$fnumList .= '<li><a href="'.JURI::base().$userLink.'#'.$fnum['fnum'].'|open">'.$fnum['name'].' ('.$fnum['fnum'].')</a></li>';
				}
				$fnumList .= '</ul>';

				$post = [
					'FNUMS' => $fnumList,
					'NAME' => $eval->name,
					'SITE_URL' => JURI::base()
				];
				$c_messages->sendEmailNoFnum($eval->email, 'share_with_evaluator', $post);
			}
        }

        echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
        exit;
    }

    /**
     *
     */
    public function getstate() {
        $m_files = $this->getModel('Files');
        $states = $m_files->getAllStatus();

        echo json_encode((object)(array('status' => true,
            'states' => $states,
            'state' => JText::_('STATE'),
            'select_state' => JText::_('PLEASE_SELECT_STATE'))));
        exit;
    }

    /**
     *
     */
    public function getpublish() {
        $publish = array (
            0 =>
                array (
                    'id' =>  '1',
                    'step' =>  '1' ,
                    'value' => JText::_('PUBLISHED') ,
                    'ordering' =>  '1'
                ),
            1 =>
                array (
                    'id' =>  '0',
                    'step' =>  '0' ,
                    'value' => JText::_('ARCHIVED') ,
                    'ordering' =>  '2'
                ),
            3 =>
                array (
                    'id' =>  '3',
                    'step' =>  '-1' ,
                    'value' => JText::_('TRASHED') ,
                    'ordering' =>  '3'
                )
        );

        echo json_encode((object)(array('status' => true,
            'states' => $publish,
            'state' => JText::_('PUBLISH'),
            'select_publish' => JText::_('PLEASE_SELECT_PUBLISH'))));
        exit;
    }

    /**
     *
     */
    public function updatestate() {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'files.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'messages.php');
        $app    = JFactory::getApplication();
        $jinput = $app->input;
        $fnums  = $jinput->getString('fnums', null);
        $state  = $jinput->getInt('state', null);

        $h_files    = new EmundusHelperFiles();
        $m_messages = new EmundusModelMessages();
        $m_files = $this->getModel('Files');

        $get_candidate_attachments = $h_files->tableExists('#__emundus_setup_emails_repeat_candidate_attachment');
        $get_letters_attachments = $h_files->tableExists('#__emundus_setup_emails_repeat_letter_attachment');


        $email_from_sys = $app->getCfg('mailfrom'); 

        if($fnums == "all") {
            $fnums = $m_files->getAllFnums();
        }
        
        if (!is_array($fnums)) {
            $fnums = (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);
        }

        if (count($fnums) == 0 || !is_array($fnums)) {
            $res = false;
            $msg = JText::_('STATE_ERROR');

            echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
            exit;
        }

        $validFnums = array();

        foreach ($fnums as $fnum) {
            if (EmundusHelperAccess::asAccessAction(13, 'u', $this->_user->id, $fnum)) {
	            $validFnums[] = $fnum;
            }
        }

        $fnumsInfos = $m_files->getFnumsInfos($validFnums);
        $res        = $m_files->updateState($validFnums, $state);
        $msg = '';

        if ($res !== false) {
            $m_application = $this->getModel('application');
            $status = $m_files->getStatus();
            // Get all codes from fnum
            $code = array();
            foreach ($fnumsInfos as $fnum) {
                $code[] = $fnum['training'];
                $step = $fnum['step'];
                
                $row = array('applicant_id' => $fnum['applicant_id'],
                    'user_id' => $this->_user->id,
                    'reason' => JText::_('STATUS'),
                    'comment_body' => $fnum['value'].' ('.$fnum['step'].') '.JText::_('TO').' '.$status[$state]['value'].' ('.$state.')',
                    'fnum' => $fnum['fnum']
                );
                $m_application->addComment($row);
            }

            //*********************************************************************
            // Get triggered email
            include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
            $m_email = new EmundusModelEmails;
            $trigger_emails = $m_email->getEmailTrigger($state, $code, 1);
            $toAttach = [];

            if (count($trigger_emails) > 0) {

                foreach ($trigger_emails as $trigger_email) {

                    // Manage with default recipient by programme
                    foreach ($trigger_email as $code => $trigger) {

/* BAD IDEA In that place, we do not known the FNUM for file generation                        
                        
                        $email_id = array_keys($trigger_emails);
                        $get_candidate_attachments = true;
                        $get_letters_attachments = true;
                        $template = $m_messages->getEmail($email_id[0], $get_candidate_attachments, $get_letters_attachments);
                        $attachments[]=$template->letter_attachments;

                        // Files generated using the Letters system. Requires attachment creation and doc generation rights.
                        if (EmundusHelperAccess::asAccessAction(4, 'c') && EmundusHelperAccess::asAccessAction(27, 'c') && !empty($attachments)) {

                            // Get from DB and generate using the tags.
                            foreach ($attachments as $setup_letter) {

                                $letter = $m_messages->get_letter($setup_letter);

                                // We only get the letters if they are for that particular programme.
                                if ($letter && in_array($code, explode('","',$letter->training))) {

                                    // Some letters are only for files of a certain status, this is where we check for that.
                                    if ($letter->status != null && !in_array($step, explode(',',$letter->status))) {
                                        continue;
                                    }

                                    // A different file is to be generated depending on the template type.
                                    switch ($letter->template_type) {

                                        case '1':
                                            // This is a static file, we just need to find its path add it as an attachment.
                                            if (file_exists(JPATH_BASE.$letter->file)) {
                                                $toAttach[] = JPATH_BASE.$letter->file;
                                            }
                                            break;

                                        case '2':
                                            // This is a PDF to be generated from HTML.
                                            require_once (JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php');

                                            $path = generateLetterFromHtml($letter, $fnum, $fnum->applicant_id, $fnum->training);

                                            if ($path && file_exists($path)) {
                                                $toAttach[] = $path;
                                            }
                                            break;

                                        case '3':
                                            // This is a DOC template to be completed with applicant information.
                                            $path = $m_messages->generateLetterDoc($letter, $fnum);

                                            if ($path && file_exists($path)) {
                                                $toAttach[] = $path;
                                            }
                                            break;

                                        default:
                                            break;
                                    }

                                }
                            }
                        }
*/
                        if ($trigger['to']['to_applicant'] == 1) {

                            // Manage with selected fnum
                            foreach ($fnumsInfos as $file) {
                            	if ($file['training'] != $code) {
                            		continue;
	                            }
                            	
                                $mailer = JFactory::getMailer();

                                $post = array('FNUM' => $file['fnum'],'CAMPAIGN_LABEL' => $file['campaign_label'], 'CAMPAIGN_END' => $file['end_date']);
                                $tags = $m_email->setTags($file['applicant_id'], $post);

                                $from       = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['emailfrom']);
                                $from_id    = 62;
                                $fromname   = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['name']);
                                $to         = $file['email'];
                                $subject    = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['subject']);
                                $body = $trigger['tmpl']['message'];


                                // Add the email template model.
                                if (!empty($trigger['tmpl']['template']))
                                    $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $trigger['tmpl']['template']);

	                            $body = preg_replace($tags['patterns'], $tags['replacements'], $body);
	                            $body = $m_email->setTagsFabrik($body, array($file['fnum']));

                                // If the email sender has the same domain as the system sender address.
                                if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1))
                                    $mail_from_address = $from;
                                else
                                    $mail_from_address = $email_from_sys;

                                // Set sender
                                $sender = [
                                    $mail_from_address,
                                    $fromname
                                ];

                                $mailer->setSender($sender);
                                $mailer->addReplyTo($from, $fromname);
                                $mailer->addRecipient($to);
                                $mailer->setSubject($subject);
                                $mailer->isHTML(true);
                                $mailer->Encoding = 'base64';
                                $mailer->setBody($body);
                                $mailer->addAttachment($toAttach);

                                $send = $mailer->Send();
                                if ($send !== true) {
                                    $msg .= '<div class="alert alert-dismissable alert-danger">'.JText::_('EMAIL_NOT_SENT').' : '.$to.' '.$send->__toString().'</div>';
                                    JLog::add($send->__toString(), JLog::ERROR, 'com_emundus.email');
                                } else {
                                    $message = array(
                                        'user_id_from' => $from_id,
                                        'user_id_to' => $file['applicant_id'],
                                        'subject' => $subject,
                                        'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.'</i><br>'.$body
                                    );
                                    $m_email->logEmail($message);
                                    $msg .= JText::_('EMAIL_SENT').' : '.$to.'<br>';
                                    JLog::add($to.' '.$body, JLog::INFO, 'com_emundus.email');
                                }
                            }
                        }

                        foreach ($trigger['to']['recipients'] as $key => $recipient) {
                            $mailer = JFactory::getMailer();

                            $post = array();
                            $tags = $m_email->setTags($recipient['id'], $post);

                            $from       = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['emailfrom']);
                            $from_id    = 62;
                            $fromname   = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['name']);
                            $to         = $recipient['email'];
                            $subject    = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['subject']);
                            $body       = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['message']);
                            $body       = $m_email->setTagsFabrik($body, $validFnums);

                            // If the email sender has the same domain as the system sender address.
                            if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1))
                                $mail_from_address = $from;
                            else
                                $mail_from_address = $email_from_sys;

                            // Set sender
                            $sender = [
                                $mail_from_address,
                                $fromname
                            ];

                            $mailer->setSender($sender);
                            $mailer->addReplyTo($from, $fromname);
                            $mailer->addRecipient($to);
                            $mailer->setSubject($subject);
                            $mailer->isHTML(true);
                            $mailer->Encoding = 'base64';
                            $mailer->setBody($body);
                            $mailer->addAttachment($toAttach);

                            $send = $mailer->Send();
                            if ($send !== true) {
                                $msg .= '<div class="alert alert-dismissable alert-danger">'.JText::_('EMAIL_NOT_SENT').' : '.$to.' '.$send->__toString().'</div>';
                                JLog::add($send->__toString(), JLog::ERROR, 'com_emundus.email');
                            } else {
                                $message = array(
                                    'user_id_from' => $from_id,
                                    'user_id_to' => $recipient['id'],
                                    'subject' => $subject,
                                    'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.'</i><br>'.$body
                                );
                                $m_email->logEmail($message);
                                $msg .= JText::_('EMAIL_SENT').' : '.$to.'<br>';
                                JLog::add($to.' '.$body, JLog::INFO, 'com_emundus.email');
                            }
                        }
                    }
                }
            }
            //
            //***************************************************

            $msg .= JText::_('STATE_SUCCESS');
        } else { 
            $msg .= JText::_('STATE_ERROR');
        }

        echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
        exit;
    }

    public function updatepublish() {
        $jinput     = JFactory::getApplication()->input;

        $publish    = $jinput->getInt('publish', null);

        $m_files = $this->getModel('Files');

        $fnums_post = $jinput->getString('fnums', null);
        $fnums_array = ($fnums_post=='all')?'all':(array) json_decode(stripslashes($fnums_post), false, 512, JSON_BIGINT_AS_STRING);

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
            if (EmundusHelperAccess::asAccessAction(13, 'u', $this->_user->id, $fnum))
                $validFnums[] = $fnum;
        }
        $res = $m_files->updatePublish($validFnums, $publish);

        if ($res !== false) {
            // Get all codes from fnum
            $fnumsInfos = $m_files->getFnumsInfos($validFnums);
            $code = array();
            foreach ($fnumsInfos as $fnum) {
                $code[] = $fnum['training'];
            }
            $msg = JText::_('STATE_SUCCESS');
        } else $msg = JText::_('STATE_ERROR');

        echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
        exit;
    }

    /**
     *
     */
    public function unlinkevaluators() {
        $jinput = JFactory::getApplication()->input;
        $fnum   = $jinput->getString('fnum', null);
        $id     = $jinput->getint('id', null);
        $group  = $jinput->getString('group', null);

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

    /**
     *
     */
    public function getfnuminfos() {
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->get->getString('fnum', null);

        $res = false;
        $fnumInfos = null;

        if ($fnum != null) {
            $m_files = $this->getModel('Files');
            $fnumInfos = $m_files->getFnumInfos($fnum);
            if ($fnum !== false)
                $res = true;
        }

        JFactory::getSession()->set('application_fnum', $fnum);
        echo json_encode((object)(array('status' => $res, 'fnumInfos' => $fnumInfos)));
        exit;
    }

    /**
     *
     */
    public function deletefile()
    {
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);
        $m_files = $this->getModel('Files');
        if (EmundusHelperAccess::asAccessAction(1, 'd', $this->_user->id, $fnum))
            $res = $m_files->changePublished($fnum);
        else
            $res = false;

        $result = array('status' => $res);
        echo json_encode((object)$result);
        exit;
    }

    /**
     *
     */
    public function removefile() {
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->post->getString('fnum', null);

        $m_files = $this->getModel('Files');
        if (EmundusHelperAccess::asAccessAction(1, 'd', $this->_user->id, $fnum)) {
            $res = $m_files->deleteFile($fnum);
        } else {
            $res = false;
        }

        $result = array('status' => $res);
        echo json_encode((object)$result);
        exit;
    }

    /**
     *
     */
    public function getformelem() {
        //Filters
        $m_files = $this->getModel('Files');
        $h_files = new EmundusHelperFiles;

        $jinput = JFactory::getApplication()->input;
        $code = $jinput->getVar('code', null);
        $camp = $jinput->getVar('camp', null);

        $code = explode(",", $code);
        $camp = explode(",", $camp);

        $defaultElements    = $m_files->getDefaultElements();
        $elements           = $h_files->getElements($code, $camp);
        //var_dump($elements);
        $res = array('status' => true, 'elts' => $elements, 'defaults' => $defaultElements);
        echo json_encode((object)$res);
        exit;
    }

    /**
     *
     
    public function send_elements()
    {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
        $current_user = JFactory::getUser();

        if (!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
            die (JText::_('RESTRICTED_ACCESS') );
        }

        $jinput = JFactory::getApplication()->input;
		
        $m_files  = $this->getModel('Files');

        $fnums_post = $jinput->getVar('fnums', null);
        $fnums_array = ($fnums_post=='all')?'all':(array) json_decode(stripslashes($fnums_post), false, 512, JSON_BIGINT_AS_STRING);

        if ($fnums_array == 'all') {
            $fnums = $m_files->getAllFnums();
		} else {
            $fnums = array();
            foreach ($fnums_array as $key => $value) {
                $fnums[] = $value->fnum;
            }
        }

        $validFnums = array();
        foreach ($fnums as $fnum) {
            if (EmundusHelperAccess::asAccessAction(6, 'c', $this->_user->id, $fnum) && $fnum != 'em-check-all-all' && $fnum != 'em-check-all')
                $validFnums[] = $fnum;
        }
        $elts = $jinput->getString('elts', null);

        $elts = (array) json_decode(stripcslashes($elts));

        $objs = $jinput->getString('objs', null);
        $objs = (array) json_decode(stripcslashes($objs));

        $methode = $jinput->getString('methode', 0);

        // export Excel
        $name = $this->export_xls($validFnums, $objs, $elts, $methode);

        $result = array('status' => true, 'name' => $name);
        echo json_encode((object) $result);
        exit();
    }
*/
    /**
     *
     */
    public function zip() {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

        $current_user = JFactory::getUser();

        if (!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id))
            die( JText::_('RESTRICTED_ACCESS') );

        $jinput = JFactory::getApplication()->input;
        $forms      = $jinput->getInt('forms', 0);
        $attachment = $jinput->getInt('attachment', 0);
        $assessment = $jinput->getInt('assessment', 0);
        $decision   = $jinput->getInt('decision', 0);
        $admission  = $jinput->getInt('admission', 0);
        $formids    = $jinput->getVar('formids', null);
        $attachids  = $jinput->getVar('attachids', null);
        $options    = $jinput->getVar('options', null);

        $m_files  = $this->getModel('Files');

        $fnums_post = $jinput->getVar('fnums', null);
        $fnums_array = ($fnums_post=='all')?'all':(array) json_decode(stripslashes($fnums_post), false, 512, JSON_BIGINT_AS_STRING);

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
            if (EmundusHelperAccess::asAccessAction(6, 'c', $this->_user->id, $fnum))
                $validFnums[] = $fnum;
        }


        if (extension_loaded('zip'))
            $name = $this->export_zip($validFnums, $forms, $attachment, $assessment, $decision, $admission, $formids, $attachids, $options);
        else
            $name = $this->export_zip_pcl($validFnums);

        echo json_encode((object) array('status' => true, 'name' => $name));
        exit();
    }

    /**
     * @param $val
     * @return int|string
     */
    public function return_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);

        switch( $last) {
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

    /**
     * @param $array
     * @param $orderArray
     * @return array
     */
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

    /**
     * @param $object
     * @param $orderArray
     * @return array
     */
    public function sortObjectByArray($object, $orderArray) {
        $properties = get_object_vars($object);
        return sortArrayByArray($properties,$orderArray);
    }

    /**
     * Create temp CSV file for XLS extraction
     * @return String json
     */
    public function create_file_csv() {
        $today  = date("MdYHis");
        $name   = md5($today.rand(0,10));
        $name   = $name.'.csv';
        $chemin = JPATH_BASE.DS.'tmp'.DS.$name;

        if (!$fichier_csv = fopen($chemin, 'w+')) {
            $result = array('status' => false, 'msg' => JText::_('ERROR_CANNOT_OPEN_FILE').' : '.$chemin);
            echo json_encode((object) $result);
            exit();
        }

        fprintf($fichier_csv, chr(0xEF).chr(0xBB).chr(0xBF));
        if (!fclose($fichier_csv)) {
            $result = array('status' => false, 'msg'=>JText::_('ERROR_CANNOT_CLOSE_CSV_FILE'));
            echo json_encode((object) $result);
            exit();
        }

        $result = array('status' => true, 'file' => $name);
        echo json_encode((object) $result);
        exit();
    }

    /**
     * Create temp PDF file for PDF extraction
     * @return String json
     */
    public function create_file_pdf() {
        $today  = date("MdYHis");
        $name   = md5($today.rand(0,10));
        $name   = $name.'-applications.pdf';

        $result = array('status' => true, 'file' => $name);
        echo json_encode((object) $result);
        exit();
    }

    public function getfnums_csv() {
        $jinput = JFactory::getApplication()->input;
        $ids = $jinput->getVar('ids', null);
        $ids = (array) json_decode(stripslashes($ids));

        $m_files = $this->getModel('Files');

        $fnums_post = $jinput->getVar('fnums', null);
        $fnums_array = ($fnums_post=='all')?'all':(array) json_decode(stripslashes($fnums_post), false, 512, JSON_BIGINT_AS_STRING);

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
            if (EmundusHelperAccess::asAccessAction(1, 'r', $this->_user->id, $fnum)&& $fnum != 'em-check-all-all' && $fnum != 'em-check-all') {
	            $validFnums[] = $fnum;
            }
        }
        $totalfile = count($validFnums);

        $session = JFactory::getSession();
        $session->set('fnums_export', $validFnums);

        $result = array('status' => true, 'totalfile'=> $totalfile, 'ids'=> $ids);
        echo json_encode((object) $result);
        exit();
    }

    public function getfnums() {
        $jinput = JFactory::getApplication()->input;
        $ids    = $jinput->getVar('ids', null);

        $action_id  = $jinput->getVar('action_id', null);
        $crud       = $jinput->getVar('crud', null);

        $m_files = $this->getModel('Files');

        $fnums_post = $jinput->getVar('fnums', null);
        $fnums_array = ($fnums_post=='all')?'all':(array) json_decode(stripslashes($fnums_post), false, 512, JSON_BIGINT_AS_STRING);

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
            if (EmundusHelperAccess::asAccessAction($action_id, $crud, $this->_user->id, $fnum)&& $fnum != 'em-check-all-all' && $fnum != 'em-check-all')
                $validFnums[] = $fnum;
        }
        $totalfile = count($validFnums);

        $session = JFactory::getSession();
        $session->set('fnums_export', $validFnums);

        $result = array('status' => true, 'totalfile'=> $totalfile, 'ids'=> $ids);
        echo json_encode((object) $result);
        exit();
    }

    public function getcolumn($elts) {
        return(array) json_decode(stripcslashes($elts));
    }

	/**
	 * Add lines to temp CSV file
	 * @return String json
	 * @throws Exception
	 */
    public function generate_array() {
        $current_user = JFactory::getUser();

        if (!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
            die(JText::_('RESTRICTED_ACCESS'));
        }

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $eval_can_see_eval = $eMConfig->get('evaluators_can_see_other_eval', 0);

        $m_files = $this->getModel('Files');
        $m_application = $this->getModel('Application');
        $m_users = $this->getModel('Users');

        $session = JFactory::getSession();
        $fnums = $session->get('fnums_export');
	    $anonymize_data = false;

        if (count($fnums) == 0) {
            $fnums = array($session->get('application_fnum'));
        }

        $jinput     = JFactory::getApplication()->input;
        $file       = $jinput->get('file', null, 'STRING');
        $totalfile  = $jinput->get('totalfile', null);
        $start      = $jinput->getInt('start', 0);
        $limit      = $jinput->getInt('limit', 0);
        $nbcol      = $jinput->get('nbcol', 0);
        $elts       = $jinput->getString('elts', null);
        $objs       = $jinput->getString('objs', null);
        $opts       = $jinput->getString('opts', null);
        $methode    = $jinput->getString('methode', null);
        $objclass   = $jinput->get('objclass', null);
        $excel_file_name = $jinput->get('excelfilename', null);

        $opts = $this->getcolumn($opts);
        $col = $this->getcolumn($elts);
        $colsup = $this->getcolumn($objs);
        $colOpt = array();

        if (!$csv = fopen(JPATH_BASE.DS.'tmp'.DS.$file, 'a')) {
            $result = array('status' => false, 'msg' => JText::_('ERROR_CANNOT_OPEN_FILE').' : '.$file);
            echo json_encode((object) $result);
            exit();
        }

        $h_files = new EmundusHelperFiles;
        $elements = $h_files->getElementsName(implode(',',$col));

        // re-order elements
        $ordered_elements = array();
        foreach ($col as $c) {
            $ordered_elements[$c] = $elements[$c];
        }

        $fnumsArray = $m_files->getFnumArray($fnums, $ordered_elements, $methode, $start, $limit, 0);
        // On met a jour la liste des fnums traités
        $fnums = array();
        foreach ($fnumsArray as $fnum) {
            array_push($fnums, $fnum['fnum']);
        }

        foreach ($colsup as $col) {
            $col = explode('.', $col);

            switch ($col[0]) {
                case "photo":
	                $anonymize_data = EmundusHelperAccess::isDataAnonymized(JFactory::getUser()->id);
	                if (!$anonymize_data) {
	                	$allowed_attachments = EmundusHelperAccess::getUserAllowedAttachmentIDs(JFactory::getUser()->id);
	                	if ($allowed_attachments === true || in_array('10', $allowed_attachments)) {
			                $photos = $m_files->getPhotos($fnums);
			                if (count($photos) > 0) {
				                $pictures = array();
				                foreach ($photos as $photo) {
					                $folder = JURI::base().EMUNDUS_PATH_REL.$photo['user_id'];
					                $link = '=HYPERLINK("'.$folder.'/tn_'.$photo['filename'].'","'.$photo['filename'].'")';
					                $pictures[$photo['fnum']] = $link;
				                }
				                $colOpt['PHOTO'] = $pictures;
			                } else {
				                $colOpt['PHOTO'] = array();
			                }
		                }
	                }
                    break;
                case "forms":
                    foreach ($fnums as $fnum) {
                        $formsProgress[$fnum] = $m_application->getFormsProgress($fnum);
                    }
                    if (!empty($formsProgress)) {
	                    $colOpt['forms'] = $formsProgress;
                    }
                    break;
                case "attachment":
                    foreach ($fnums as $fnum) {
                        $attachmentProgress[$fnum] = $m_application->getAttachmentsProgress($fnum);
                    }
                    if (!empty($attachmentProgress)) {
	                    $colOpt['attachment'] = $attachmentProgress;
                    }
                    break;
                case "assessment":
                    $colOpt['assessment'] = $h_files->getEvaluation('text', $fnums);
                    break;
                case "comment":
                    $colOpt['comment'] = $m_files->getCommentsByFnum($fnums);
                    break;
                case 'evaluators':
                    $colOpt['evaluators'] = $h_files->createEvaluatorList($col[1], $m_files);
                    break;
                case 'tags':
                    $colOpt['tags'] = $m_files->getTagsByFnum($fnums);
                    break;
	            case 'group-assoc':
	            	$colOpt['group-assoc'] = $m_files->getAssocByFnums($fnums, true, false);
	            	break;
	            case 'user-assoc':
	            	$colOpt['user-asoc'] = $m_files->getAssocByFnums($fnums, false, true);
	            	break;
	            case 'overall':
		            require_once (JPATH_COMPONENT.DS.'models'.DS.'evaluation.php');
		            $m_evaluations = new EmundusModelEvaluation();
		            $colOpt['overall'] = $m_evaluations->getEvaluationAverageByFnum($fnums);
		            // Because the result can be empty and thus the fnum not set in the $colOpt array :
	                foreach ($fnums as $fnum) {
	                	if (!isset($colOpt['overall'][$fnum])) {
			                $colOpt['overall'][$fnum] = '';
		                }
	                }
	            	break;
            }
        }
        $status = $m_files->getStatusByFnums($fnums);
        $line = "";
        $element_csv = array();
        $i = $start;

        // Here we filter elements which are already present but under a different name or ID, by looking at tablename___element_name.
        $elts_present = [];
        foreach ($ordered_elements as $elt_id => $o_elt) {
        	$element = $o_elt->tab_name.'___'.$o_elt->element_name;
        	if (in_array($element, $elts_present)) {
        		unset($ordered_elements[$elt_id]);
	        } else {
        		$elts_present[] = $element;
	        }
        }
        
        // On traite les en-têtes
        if ($start == 0) {

	        $anonymize_data = EmundusHelperAccess::isDataAnonymized(JFactory::getUser()->id);
	        if ($anonymize_data) {
		        $line = JText::_('F_NUM')."\t".JText::_('STATUS')."\t".JText::_('PROGRAMME')."\t";
	        } else {
		        $line = JText::_('F_NUM')."\t".JText::_('STATUS')."\t".JText::_('LAST_NAME')."\t".JText::_('FIRST_NAME')."\t".JText::_('EMAIL')."\t".JText::_('PROGRAMME')."\t";
	        }
            
            $nbcol = 6;
	        $date_elements = [];
            foreach ($ordered_elements as $fLine) {
                if ($fLine->element_name != 'fnum' && $fLine->element_name != 'code' && $fLine->element_label != 'Programme' && $fLine->element_name != 'campaign_id') {
                    if (count($opts) > 0 && $fLine->element_name != "date_time" && $fLine->element_name != "date_submitted") {
                        if (in_array("form-title", $opts) && in_array("form-group", $opts)) {
                            $line .= JText::_($fLine->form_label)." > ".JText::_($fLine->group_label)." > ".preg_replace('#<[^>]+>#', ' ', JText::_($fLine->element_label)). "\t";
                            $nbcol++;
                        } elseif (count($opts) == 1) {
                            if (in_array("form-title", $opts)) {
                                $line .= JText::_($fLine->form_label)." > ".preg_replace('#<[^>]+>#', ' ', JText::_($fLine->element_label)). "\t";
                                $nbcol++;
                            } elseif (in_array("form-group", $opts)) {
                                $line .= JText::_($fLine->group_label)." > ".preg_replace('#<[^>]+>#', ' ', JText::_($fLine->element_label)). "\t";
                                $nbcol++;
                            }
                        }
                    } else {

                    	if ($fLine->element_plugin == 'date') {
                    		$params = json_decode($fLine->element_attribs);
                    		$date_elements[$fLine->tab_name.'___'.$fLine->element_name] = $params->date_form_format;
	                    }
                    	
                        $line .= preg_replace('#<[^>]+>#', ' ', JText::_($fLine->element_label)). "\t";
                        $nbcol++;
                    }
                }
            }

            foreach ($colsup as $kOpt => $vOpt) {
                if ($vOpt == "forms" || $vOpt == "attachment") {
	                $line .= $vOpt."(%)\t";
                } else {
	                $line .= '"'.preg_replace("/\r|\n|\t/", "", $vOpt).'"'."\t";
                }
                $nbcol++;
            }

            // On met les en-têtes dans le CSV
            $element_csv[] = $line;
            $line = "";
        }


        //check if evaluator can see others evaluators evaluations
        if (@EmundusHelperAccess::isEvaluator($current_user->id) && !@EmundusHelperAccess::isCoordinator($current_user->id)) {
            $user = $m_users->getUserById($current_user->id);
            $evaluator = $user[0]->lastname." ".$user[0]->firstname;
            if ($eval_can_see_eval == 0 && !empty($objclass) && in_array("emundusitem_evaluation otherForm", $objclass)) {
                foreach ($fnumsArray as $idx => $d) {
                    foreach ($d as $k => $v) {
                        if ($k === 'jos_emundus_evaluations___user' && strcasecmp($v, $evaluator) != 0) {
                            foreach($fnumsArray[$idx] as $key => $value) {
                                if (substr($key, 0, 26) === "jos_emundus_evaluations___") {
	                                $fnumsArray[$idx][$key] = JText::_('NO_RIGHT');
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // On parcours les fnums
        foreach ($fnumsArray as $fnum) {
            // On traite les données du fnum
            foreach ($fnum as $k => $v) {
                if ($k != 'code' && strpos($k, 'campaign_id') === false) {

                    if ($k === 'fnum') {
                        $line .= "'".$v."\t";
                        $line .= $status[$v]['value']."\t";
                        $uid = intval(substr($v, 21, 7));
                        if (!$anonymize_data) {
	                        $userProfil = $m_users->getUserById($uid)[0];
	                        $line .= $userProfil->lastname."\t";
	                        $line .= $userProfil->firstname."\t";
                        }
                        
                    } else {
                    	
                        if ($v == "") {
	                        $line .= " "."\t";
                        } elseif ($v[0] == "=" || $v[0] == "-") {
                            if (count($opts) > 0 && in_array("upper-case", $opts)) {
                                $line .= " ".mb_strtoupper($v)."\t";
                            } else {
                                $line .= " ".$v."\t";
                            }
                        } else {

                        	if (!empty($date_elements[$k])) {
		                        if ($v === '0000-00-00 00:00:00') {
			                        $v = '';
		                        } else {
			                        $v = date($date_elements[$k], strtotime($v));
		                        }
								$line .= preg_replace("/\r|\n|\t/", "", $v)."\t";
	                        } elseif (count($opts) > 0 && in_array("upper-case", $opts)) {
                                $line .= JText::_(preg_replace("/\r|\n|\t/", "", mb_strtoupper($v)))."\t";
                            } else {
                                $line .= JText::_(preg_replace("/\r|\n|\t/", "", $v))."\t";
                            }
                        }
                    }
                }
            }

            // On ajoute les données supplémentaires
            foreach ($colOpt as $kOpt => $vOpt) {
                switch ($kOpt) {
                    case "PHOTO":
	                case "forms":
	                case "attachment":
	                case 'evaluators':
                        if (array_key_exists($fnum['fnum'], $vOpt)) {
                            $line .= $vOpt[$fnum['fnum']]."\t";
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
                        if (!empty($vOpt)) {
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

                    case "tags":
                        $tags = "";

                        foreach ($colOpt['tags'] as $tag) {
                            if ($tag['fnum'] == $fnum['fnum']) {
                                $tags .= $tag['label'] . ", ";
                            }
                        }
                        $line .= $tags . "\t";
                        break;

	                default:
	                	$line .= $vOpt[$fnum['fnum']]."\t";
                        break;
                }
            }
            // On met les données du fnum dans le CSV
            $element_csv[] = $line;
            $line = "";
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
            $result = array('status' => false, 'msg'=>JText::_('ERROR_CANNOT_CLOSE_CSV_FILE'));
            echo json_encode((object) $result);
            exit();
        }

        $start = $i;

        $dataresult = array('start' => $start, 'limit'=>$limit, 'totalfile'=> $totalfile,'methode'=>$methode,'elts'=>$elts, 'objs'=> $objs, 'nbcol' => $nbcol,'file'=>$file, 'excelfilename'=>$excel_file_name );
        $result = array('status' => true, 'json' => $dataresult);
        echo json_encode((object) $result);
        exit();
    }

    public function getformslist() {

        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');

        $m_profile = new EmundusModelProfile();
        $m_campaign = new EmundusModelCampaign();
        $h_menu = new EmundusHelperMenu();

        $jinput     = JFactory::getApplication()->input;
        $code    = $jinput->getVar('code', null);
        $camp    = $jinput->getVar('camp', null);


        $code = explode(',', $code);
        $camp = explode(',', $camp);

        $profile = $m_profile->getProfileIDByCourse($code, $camp);
        $pages = $h_menu->buildMenuQuery((int)$profile[0]);

        if($camp[0] != 0)
            $campaign = $m_campaign->getCampaignsByCourseCampaign($code[0], $camp[0]);
        else
            $campaign = $m_campaign->getCampaignsByCourse($code[0]);


        $html1 = '';
        $html2 = '';

        for ($i = 0; $i < count($pages); $i++) {
            if ($i < count($pages)/2)
                $html1 .= '<input class="em-ex-check" type="checkbox" value="'.$pages[$i]->form_id."|".$code[0]."|".$camp[0].'" name="'.$pages[$i]->label.'" id="'.$pages[$i]->form_id."|".$code[0]."|".$camp[0].'" /><label for="'.$pages[$i]->form_id."|".$code[0]."|".$camp[0].'">'.JText::_($pages[$i]->label).'</label><br/>';
            else
                $html2 .= '<input class="em-ex-check" type="checkbox" value="'.$pages[$i]->form_id."|".$code[0]."|".$camp[0].'" name="'.$pages[$i]->label.'" id="'.$pages[$i]->form_id."|".$code[0]."|".$camp[0].'" /><label for="'.$pages[$i]->form_id."|".$code[0]."|".$camp[0].'">'.JText::_($pages[$i]->label).'</label><br/>';
        }
        //var_dump($camp[0]);
        $html = '<div class="panel panel-default pdform">
                    <div class="panel-heading">
                        <button type="button" class="btn btn-info btn-xs" title="'.JText::_('COM_EMUNDUS_SHOW_ELEMENTS').'" style="float:left;" onclick="showelts(this, '."'felts-".$code[0].$camp[0]."'".')">
                        <span class="glyphicon glyphicon-plus"></span>
                        </button>&ensp;&ensp;
                        <b>'.$campaign['label'].' ('.$campaign['year'].')</b>
                    </div>
                    <div class="panel-body" id="felts-'.$code[0].$camp[0].'" style="display:none;">
                        <table><tr><td>'.$html1.'</td><td style="padding-left:80px;">'.$html2.'</td></tr></table>
                    </div>
                </div>';

        echo json_encode((object)(array('status' => true, 'html' => $html)));
        exit;
    }

    public function getdoctype() {

        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
	    require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

        $jinput = JFactory::getApplication()->input;
        $code = $jinput->getVar('code', null);
        $camp = $jinput->getVar('camp', null);
        $code = explode(',', $code);
        $camp = explode(',', $camp);

        $m_profile = new EmundusModelProfile();
        $m_campaign = new EmundusModelCampaign();
        $h_files = new EmundusHelperFiles();

        $profile = $m_profile->getProfileIDByCourse($code, $camp);
        $docs = $h_files->getAttachmentsTypesByProfileID((int)$profile[0]);

        // Sort the docs out that are not allowed to be exported by the user.
	    $allowed_attachments = EmundusHelperAccess::getUserAllowedAttachmentIDs(JFactory::getUser()->id);
	    if ($allowed_attachments !== true) {
		    foreach ($docs as $key => $doc) {
			    if (!in_array($doc->id, $allowed_attachments)) {
				    unset($docs[$key]);
			    }
		    }
	    }

        if ($camp[0] != 0) {
	        $campaign = $m_campaign->getCampaignsByCourseCampaign($code[0], $camp[0]);
        } else {
	        $campaign = $m_campaign->getCampaignsByCourse($code[0]);
        }

        $html1 = '';
        $html2 = '';
        for ($i = 0; $i < count($docs); $i++) {
            if ($i < count($docs) / 2) {
	            $html1 .= '<input class="em-ex-check" type="checkbox" value="'.$docs[$i]->id."|".$code[0]."|".$camp[0].'" name="'.$docs[$i]->value.'" id="'.$docs[$i]->id."|".$code[0]."|".$camp[0].'" /><label for="'.$docs[$i]->id."|".$code[0]."|".$camp[0].'">'.JText::_($docs[$i]->value).'</label><br/>';
            } else {
	            $html2 .= '<input class="em-ex-check" type="checkbox" value="'.$docs[$i]->id."|".$code[0]."|".$camp[0].'" name="'.$docs[$i]->value.'" id="'.$docs[$i]->id."|".$code[0]."|".$camp[0].'" /><label for="'.$docs[$i]->id."|".$code[0]."|".$camp[0].'">'.JText::_($docs[$i]->value).'</label><br/>';
            }
        }

        $html = '<div class="panel panel-default pdform">
                    <div class="panel-heading">
                        <button type="button" class="btn btn-info btn-xs" title="'.JText::_('COM_EMUNDUS_SHOW_ELEMENTS').'" style="float:left;" onclick="showelts(this, '."'aelts-".$code[0].$camp[0]."'".')">
                        <span class="glyphicon glyphicon-plus"></span>
                        </button>&ensp;&ensp;
                        <b>'.$campaign['label'].' ('.$campaign['year'].')</b>
                    </div>
                    <div class="panel-body" id="aelts-'.$code[0].$camp[0].'" style="display:none;">
                        <table><tr><td>'.$html1.'</td><td style="padding-left:80px;">'.$html2.'</td></tr></table>
                    </div>
                </div>';

        echo json_encode((object)(array('status' => true, 'html' => $html)));
        exit;
    }

	/**
	 * Add lines to temp PDF file
	 * @return String json
	 * @throws Exception
	 */
    public function generate_pdf() {
        $current_user = JFactory::getUser();

        if (!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
	        die(JText::_('RESTRICTED_ACCESS'));
        }

        $m_files = $this->getModel('Files');

        $session = JFactory::getSession();
        $fnums_post = $session->get('fnums_export');
        
        if (count($fnums_post) == 0) {
	     $fnums_post = array($session->get('application_fnum'));
        }

        $jinput     = JFactory::getApplication()->input;
        $file       = $jinput->getVar('file', null, 'STRING');
        $totalfile  = $jinput->getVar('totalfile', null);
        $start      = $jinput->getInt('start', 0);
        $limit      = $jinput->getInt('limit', 1);
        $forms      = $jinput->getInt('forms', 0);
        $attachment = $jinput->getInt('attachment', 0);
        $assessment = $jinput->getInt('assessment', 0);
        $decision   = $jinput->getInt('decision', 0);
        $admission  = $jinput->getInt('admission', 0);
        $ids        = $jinput->getVar('ids', null);
        $formid     = $jinput->getVar('formids', null);
        $attachid   = $jinput->getVar('attachids', null);
        $option     = $jinput->getVar('options', null);

        $formids    = explode(',', $formid);
        $attachids  = explode(',', $attachid);
        $options    = explode(',', $option);

        $validFnums = array();
        foreach ($fnums_post as $fnum) {
            if (EmundusHelperAccess::asAccessAction(8, 'c', $this->_user->id, $fnum)) {
	            $validFnums[] = $fnum;
            }
        }

        $fnumsInfo = $m_files->getFnumsInfos($validFnums);

        //////////////////////////////////////////////////////////////////////////////////////
        // Generate filename from emundus config ONLY if one file is selected
        //
        if (count($validFnums) == 1) {
            $eMConfig = JComponentHelper::getParams('com_emundus');
            $application_form_name = $eMConfig->get('application_form_name', "application_form_pdf");

            if ($application_form_name != "application_form_pdf") {

                require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
                $m_emails = new EmundusModelEmails;
                
                $fnum = $validFnums[0];
                $post = array(
                        'FNUM' => $fnum,
                        'CAMPAIGN_YEAR' => $fnumsInfo[$fnum]['year']
                    );
                $tags = $m_emails->setTags($fnumsInfo[$fnum]['applicant_id'], $post);
                
                // Format filename
                $application_form_name = preg_replace($tags['patterns'], $tags['replacements'], $application_form_name);
                $application_form_name = $m_emails->setTagsFabrik($application_form_name, array($fnum)); 
                $application_form_name = $m_emails->stripAccents($application_form_name);
                $application_form_name = preg_replace('/[^A-Za-z0-9 _.-]/','', $application_form_name);
                $application_form_name = preg_replace('/\s/', '', $application_form_name);
                $application_form_name = strtolower($application_form_name);

                if ($file != $application_form_name.'.pdf' && file_exists(JPATH_BASE.DS.'tmp'.DS.$application_form_name.'.pdf')) {
                    unlink(JPATH_BASE.DS.'tmp'.DS.$application_form_name.'.pdf');
                }

                $file = $application_form_name.'.pdf';
            }
        }
        ////////////////////////////////////////////////////////////
        if (file_exists(JPATH_BASE . DS . 'tmp' . DS . $file)) {
	        $files_list = array(JPATH_BASE.DS.'tmp'.DS.$file);
        } else {
	        $files_list = array();
        }


        for ($i = $start; $i < ($start+$limit) && $i < $totalfile; $i++) {
            $fnum = $validFnums[$i];
            if (is_numeric($fnum) && !empty($fnum)) {
                if (isset($forms)) {
                    $forms_to_export = array();
                    if (!empty($formids)) {
                        foreach ($formids as $fids) {
                            $detail = explode("|", $fids);
                            if ((!empty($detail[1]) && $detail[1] == $fnumsInfo[$fnum]['training']) && ($detail[2] == $fnumsInfo[$fnum]['campaign_id'] || $detail[2] == "0")) {
                            	$forms_to_export[] = $detail[0];
                            }
                        }
                    }
                    if ($forms || !empty($forms_to_export)) {
	                    $files_list[] = EmundusHelperExport::buildFormPDF($fnumsInfo[$fnum], $fnumsInfo[$fnum]['applicant_id'], $fnum, $forms, $forms_to_export, $options);
                    }
                }

                if ($attachment || !empty($attachids)) {
                    $tmpArray = array();
                    $m_application = $this->getModel('application');
                    $attachment_to_export = array();
                    foreach ($attachids as $aids) {
                        $detail = explode("|", $aids);
                        if ((!empty($detail[1]) && $detail[1] == $fnumsInfo[$fnum]['training']) && ($detail[2] == $fnumsInfo[$fnum]['campaign_id'] || $detail[2] == "0")) {
	                        $attachment_to_export[] = $detail[0];
                        }
                    }
                    if ($attachment || !empty($attachment_to_export)) {
                        $files = $m_application->getAttachmentsByFnum($fnum, $ids, $attachment_to_export);
                        if ($options[0] != "0") {
                            $files_list[] = EmundusHelperExport::buildHeaderPDF($fnumsInfo[$fnum], $fnumsInfo[$fnum]['applicant_id'], $fnum, $options);
                        }
                        $files_export = EmundusHelperExport::getAttachmentPDF($files_list, $tmpArray, $files, $fnumsInfo[$fnum]['applicant_id']);
                    }
                }

                if ($assessment)
                    $files_list[] = EmundusHelperExport::getEvalPDF($fnum, $options);

                if ($decision)
                    $files_list[] = EmundusHelperExport::getDecisionPDF($fnum, $options);

                if ($admission)
                    $files_list[] = EmundusHelperExport::getAdmissionPDF($fnum, $options);

                if (($forms != 1) && $formids[0] == "" && ($attachment != 1) && ($attachids[0] == "") && ($assessment != 1) && ($decision != 1) && ($admission != 1) && ($options[0] != "0"))
                    $files_list[] = EmundusHelperExport::buildHeaderPDF($fnumsInfo[$fnum], $fnumsInfo[$fnum]['applicant_id'], $fnum, $options);

            }

        }
        $start = $i;

        if (count($files_list) > 0) {

            // all PDF in one file
            require_once(JPATH_LIBRARIES . DS . 'emundus' . DS . 'fpdi.php');

            $pdf = new ConcatPdf();

            $pdf->setFiles($files_list);

            $pdf->concat();

            if (isset($tmpArray)) {
                foreach ($tmpArray as $fn) {
                    unlink($fn);
                }
            }
            $pdf->Output(JPATH_BASE . DS . 'tmp' . DS . $file, 'F');

            $start = $i;

            $dataresult = [
                'start' => $start, 'limit' => $limit, 'totalfile' => $totalfile, 'forms' => $forms, 'formids' => $formid, 'attachids' => $attachid,
                'options' => $option, 'attachment' => $attachment, 'assessment' => $assessment, 'decision' => $decision,
                'admission' => $admission, 'file' => $file, 'ids' => $ids, 'path'=>JURI::base(), 'msg' => JText::_('FILES_ADDED')//.' : '.$fnum
            ];
            $result = array('status' => true, 'json' => $dataresult);

        } else {

            $dataresult = [
                'start' => $start, 'limit' => $limit, 'totalfile' => $totalfile, 'forms' => $forms, 'formids' => $formid, 'attachids' => $attachid,
                'options' => $option, 'attachment' => $attachment, 'assessment' => $assessment, 'decision' => $decision,
                'admission' => $admission, 'file' => $file, 'ids' => $ids, 'msg' => JText::_('FILE_NOT_FOUND')
            ];

            $result = array('status' => false, 'json' => $dataresult);
        }
        echo json_encode((object) $result);
        exit();
    }


    public function export_xls_from_csv() {
        /** PHPExcel */
        require_once (JPATH_LIBRARIES . '/emundus/vendor/autoload.php');

        $jinput = JFactory::getApplication()->input;
        $csv = $jinput->getVar('csv', null);
        $nbcol = $jinput->getVar('nbcol', 0);
        $nbrow = $jinput->getVar('start', 0);
        $excel_file_name = $jinput->getVar('excelfilename', null);
        $objReader =\PhpOffice\PhpSpreadsheet\IOFactory::createReader("Csv");
        $objReader->setDelimiter("\t");
        $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Excel colonne
        $colonne_by_id = array();
        for ($i = ord("A"); $i <= ord("Z"); $i++) {
            $colonne_by_id[]=chr($i);
        }

        for ($i = ord("A"); $i <= ord("Z"); $i++) {
            for ($j = ord("A"); $j <= ord("Z"); $j++) {
                $colonne_by_id[]=chr($i).chr($j);
                if (count($colonne_by_id) == $nbrow) break;
            }
        }

        // Set properties
        $objPHPExcel->getProperties()->setCreator("eMundus SAS : http://www.emundus.fr/");
        $objPHPExcel->getProperties()->setLastModifiedBy("eMundus SAS");
        $objPHPExcel->getProperties()->setTitle("eMmundus Report");
        $objPHPExcel->getProperties()->setSubject("eMmundus Report");
        $objPHPExcel->getProperties()->setDescription("Report from open source eMundus plateform : http://www.emundus.fr/");
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('Extraction');
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $objPHPExcel->getActiveSheet()->freezePane('A2');

        $objReader->loadIntoExisting(JPATH_BASE.DS."tmp".DS.$csv, $objPHPExcel);

        $objConditional1 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $objConditional1->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS)
            ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_EQUAL)
            ->addCondition('0');
        $objConditional1->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');

        $objConditional2 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $objConditional2->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS)
            ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_EQUAL)
            ->addCondition('100');
        $objConditional2->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF00FF00');

        $objConditional3 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $objConditional3->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS)
            ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_EQUAL)
            ->addCondition('50');
        $objConditional3->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');

        $i = 0;
        //FNUM
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('30');
        $objPHPExcel->getActiveSheet()->getStyle('A2:A'.($nbrow+ 1))->getNumberFormat()->setFormatCode( \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $i++;
        //STATUS
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('20');
        $i++;
        //LASTNAME
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('20');
        $i++;
        //FIRSTNAME
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('20');
        $i++;
        //EMAIL
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('40');
        //$objPHPExcel->getActiveSheet()->getStyle('E2:E'.($nbrow+ 1))->getNumberFormat()->setFormatCode( PHPExcel_Style_Font::UNDERLINE_SINGLE );
        $i++;
        //CAMPAIGN
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('40');
        $i++;

        for ($i; $i<$nbcol; $i++) {
            $value = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($i, 1)->getValue();

            if ($value=="forms(%)" || $value=="attachment(%)") {
                $conditionalStyles = $objPHPExcel->getActiveSheet()->getStyle($colonne_by_id[$i].'1')->getConditionalStyles();
                array_push($conditionalStyles, $objConditional1);
                array_push($conditionalStyles, $objConditional2);
                array_push($conditionalStyles, $objConditional3);
                $objPHPExcel->getActiveSheet()->getStyle($colonne_by_id[$i].'1')->setConditionalStyles($conditionalStyles);
                $objPHPExcel->getActiveSheet()->duplicateConditionalStyle($conditionalStyles,$colonne_by_id[$i].'1:'.$colonne_by_id[$i].($nbrow+ 1));
            }
            $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('30');
        }

        $randomString = JUserHelper::genRandomPassword(20);
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, "Xlsx");
        $objWriter->save(JPATH_BASE . DS . 'tmp' . DS . $excel_file_name . '_' . $nbrow . 'rows_' . $randomString . '.xlsx');
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
        $link = $excel_file_name.'_'.$nbrow.'rows_'.$randomString.'.xlsx';
        if (!unlink(JPATH_BASE.DS."tmp".DS.$csv)) {
            $result = array('status' => false, 'msg'=>'ERROR_DELETE_CSV');
            echo json_encode((object) $result);
            exit();
        }

        $session = JFactory::getSession();
        $session->clear('fnums_export');
        $result = array('status' => true, 'link' => $link);
        echo json_encode((object) $result);
        exit();

    }

	/**
	 * @param        $fnums
	 * @param        $objs
	 * @param        $element_id
	 * @param   int  $methode
	 *
	 * @return string
	 *
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 * @since version
	 */
    public function export_xls($fnums, $objs, $element_id, $methode=0) {
        $current_user = JFactory::getUser();

        if (!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
	        die(JText::_('RESTRICTED_ACCESS'));
        }

        @set_time_limit(10800);
        jimport( 'joomla.user.user' );
        error_reporting(0);
        /** PHPExcel*/
        require_once (JPATH_LIBRARIES . '/emundus/vendor/autoload.php'); 

        $m_files = $this->getModel('Files');
        $h_files = new EmundusHelperFiles;

        $elements   = $h_files->getElementsName(implode(',',$element_id));
        $fnumsArray = $m_files->getFnumArray($fnums, $elements, $methode);
        $status     = $m_files->getStatusByFnums($fnums);

        $menu = @JFactory::getApplication()->getMenu();
        $current_menu  = $menu->getActive();
        $menu_params = $menu->getParams($current_menu->id);

        $columnSupl = explode(',', $menu_params->get('em_actions'));
        $columnSupl = array_merge($columnSupl, $objs);
        $colOpt = array();

        $m_application = $this->getModel('Application');

        foreach ($columnSupl as $col) {
            $col = explode('.', $col);
            switch ($col[0]) {
                case "photo":
                    $colOpt['PHOTO'] = $h_files->getPhotos();
                    break;
                case "forms":
                    $colOpt['forms'] = $m_application->getFormsProgress($fnums);
                    break;
                case "attachment":
                    $colOpt['attachment'] = $m_application->getAttachmentsProgress($fnums);
                    break;
                case "assessment":
                    $colOpt['assessment'] = $h_files->getEvaluation('text', $fnums);
                    break;
                case "comment":
                    $colOpt['comment'] = $m_files->getCommentsByFnum($fnums);
                    break;
                case 'evaluators':
                    $colOpt['evaluators'] = $h_files->createEvaluatorList($col[1], $m_files);
                    break;
	            case 'group-assoc':
		            $colOpt['group-assoc'] = $m_files->getAssocByFnums($fnums, true, false);
		            break;
	            case 'user-assoc':
		            $colOpt['user-asoc'] = $m_files->getAssocByFnums($fnums, false, true);
		            break;
	            case 'overall':
	            	require_once (JPATH_COMPONENT.DS.'models'.DS.'evaluation.php');
	            	$m_evaluations = new EmundusModelEvaluation();
	            	$colOpt['overall'] = $m_evaluations->getEvaluationAverageByFnum($fnums);
	            	break;
            }
        }

        // Excel colonne
        $colonne_by_id = array();
        for ($i = ord("A"); $i <= ord("Z"); $i++) {
            $colonne_by_id[] = chr($i);
        }
        for ($i = ord("A"); $i <= ord("Z"); $i++) {
            for ($j = ord("A"); $j <= ord("Z"); $j++) {
                $colonne_by_id[] = chr($i).chr($j);
                if (count($colonne_by_id) == count($fnums)) {
                    break;
                }
            }
        }
        // Create new PHPExcel object
        $objPHPSpreadsheet = new Spreadsheet();
        // Initiate cache

        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array('memoryCacheSize' => '32MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        // Set properties
        $objPHPSpreadsheet->getProperties()->setCreator("eMundus SAS : http://www.emundus.fr/");
        $objPHPSpreadsheet->getProperties()->setLastModifiedBy("eMundus SAS");
        $objPHPSpreadsheet->getProperties()->setTitle("eMmundus Report");
        $objPHPSpreadsheet->getProperties()->setSubject("eMmundus Report");
        $objPHPSpreadsheet->getProperties()->setDescription("Report from open source eMundus plateform : http://www.emundus.fr/");


        $objPHPSpreadsheet->setActiveSheetIndex(0);
        $objPHPSpreadsheet->getActiveSheet()->setTitle('Extraction');
        $objPHPSpreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPSpreadsheet->getDefaultStyle()->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $objPHPSpreadsheet->getActiveSheet()->freezePane('A2');

        $i = 0;
        $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($i, 1, JText::_('F_NUM'));
        $objPHPSpreadsheet->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('40');
        $i++;
        $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($i, 1, JText::_('STATUS'));
        $objPHPSpreadsheet->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('40');
        $i++;
        $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($i, 1, JText::_('LAST_NAME'));
        $objPHPSpreadsheet->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('30');
        $i++;
        $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($i, 1, JText::_('FIRST_NAME'));
        $objPHPSpreadsheet->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('30');
        $i++;
        $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($i, 1, JText::_('EMAIL'));
        $objPHPSpreadsheet->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('30');
        $i++;
        $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($i, 1, JText::_('CAMPAIGN'));
        $objPHPSpreadsheet->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('30');
        $i++;

        foreach ($elements as $fLine) {
            if ($fLine->element_name != 'fnum' && $fLine->element_name != 'code' && $fLine->element_name != 'campaign_id') {
                $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($i, 1, $fLine->element_label);
                $objPHPSpreadsheet->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('30');
                $i++;
            }
        }

        foreach ($colOpt as $kOpt => $vOpt) {
            $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($i, 1, JText::_(strtoupper($kOpt)));
            $objPHPSpreadsheet->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('30');
            $i++;
        }

        $line = 2;
        foreach ($fnumsArray as $fnunLine) {
            $col = 0;
            foreach ($fnunLine as $k => $v) {
                if ($k != 'code' && strpos($k, 'campaign_id')===false) {

                    if ($k === 'fnum') {
                        $objPHPSpreadsheet->getActiveSheet()->setCellValueExplicitByColumnAndRow($col, $line, (string) $v, PHPExcel_Cell_DataType::TYPE_STRING);
                        $col++;
                        $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $status[$v]['value']);
                        $col++;
                        $uid = intval(substr($v, 21, 7));
                        $userProfil = JUserHelper::getProfile($uid)->emundus_profile;
                        $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $line, strtoupper($userProfil['lastname']));
                        $col++;
                        $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $userProfil['firstname']);
                        $col++;
                    } else {
                        $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $v);
                        $col++;
                    }
                }
            }

            foreach ($colOpt as $kOpt => $vOpt) {
                switch ($kOpt) {
                    case "photo":
                        $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $line, JText::_('PHOTO'));
                        break;
	                case "attachment":
	                case "forms":
                        $val = $vOpt[$fnunLine['fnum']];
                        $objPHPSpreadsheet->getActiveSheet()->getStyle($colonne_by_id[$col].':'.$colonne_by_id[$col])->getAlignment()->setWrapText(true);
                        if ($val == 0) {
                            $rgb = 'FF6600';
                        } elseif ($val == 100) {
                            $rgb = '66FF66';
                        } elseif ($val == 50) {
                            $rgb = 'FFFF00';
                        } else {
                            $rgb = 'FFFFFF';
                        }
                        $objPHPSpreadsheet->getActiveSheet()->getStyle($colonne_by_id[$col].$line)->applyFromArray(
                            [
                            	'fill' => [
                            		'filltype' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'color' => ['argb' => 'FF'.$rgb]
	                            ],
                            ]
                        );
                        $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $val.'%');
                        $objPHPSpreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
                        break;
	                case "assessment":
                        $eval = '';
                        $evaluations = $vOpt[$fnunLine['fnum']];
                        foreach ($evaluations as $evaluation) {
                            $eval .= $evaluation;
                            $eval .= chr(10).'______'.chr(10);
                        }
                        $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $eval);
                        break;
                    case "comment":
                        $comments="";
                        foreach ($colOpt['comment'] as $comment) {
                            if ($comment['fnum'] == $fnunLine['fnum']) {
                                $comments .= $comment['reason'] . " | " . $comment['comment_body']."\rn";
                            }
                        }
                        $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $comments);
                        break;
                    case 'evaluators':
                        $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $vOpt[$fnunLine['fnum']]);
                        break;
	                case 'group-assoc':
		                $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $line, JText::_('COM_EMUNDUS_ASSOCIATED_GROUPS'));
		                break;
	                case 'user-assoc':
		                $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $line, JText::_('COM_EMUNDUS_ASSOCIATED_USERS'));
		                break;
	                case 'overall':
		                $objPHPSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $line, JText::_('EVALUATION_OVERALL'));
		                break;
                }
                $col++;
            }
            $line++;
        }

        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xls($objPHPSpreadsheet);

        $objWriter->save(JPATH_BASE.DS.'tmp'.DS.JFactory::getUser()->id.'_extraction.xls');
        return JFactory::getUser()->id.'_extraction.xls';
    }


    /**
     * @param $filename
     * @param string $mimePath
     * @return bool
     */
    function get_mime_type($filename, $mimePath = '../etc') {
        $fileext = substr(strrchr($filename, '.'), 1);

        if (empty($fileext)) {
        	return false;
        }

        $regex = "/^([\w\+\-\.\/]+)\s+(\w+\s)*($fileext\s)/i";
        $lines = file("$mimePath/mime.types");
        foreach($lines as $line) {
            if (substr($line, 0, 1) == '#') {
            	continue;
            }
            $line = rtrim($line) . " ";
            if (!preg_match($regex, $line, $matches)) {
            	continue;
            }
            return $matches[1];
        }
        return false;
    }

    /**
     *
     */
    public function download()
    {
        $jinput = JFactory::getApplication()->input;

        $name = $jinput->getString('name', null);

        $file = JPATH_BASE.DS.'tmp'.DS.$name;

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
            echo JText::_('FILE_NOT_FOUND').' : '.$file;
        }
    }

    /**
     *  Create a zip file containing all documents attached to application fil number
     * @param array $fnums
     * @return string
     */
    function export_zip($fnums, $form_post = 1, $attachment = 1, $assessment = 1, $decision = 1, $admission = 1, $form_ids = null, $attachids = null, $options = null, $acl_override = false) {
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $view = JRequest::getCmd( 'view' );
        $current_user = JFactory::getUser();

        if ((!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) && $view != 'renew_application' && !$acl_override) {
	        die(JText::_('RESTRICTED_ACCESS'));
        }

        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');

        $m_emails = new EmundusModelEmails;

        $zip = new ZipArchive();
        $nom = date("Y-m-d").'_'.rand(1000,9999).'_x'.(count($fnums)-1).'.zip';

        $path = JPATH_BASE.DS.'tmp'.DS.$nom;
        $m_files = $this->getModel('Files');

        $fnumsInfo = $m_files->getFnumsInfos($fnums);

        if (file_exists($path)) {
	        unlink($path);
        }

        $users = array();

        foreach ($fnums as $fnum) {

            $sid = intval(substr($fnum, -7));
            $users[$fnum] = JFactory::getUser($sid);

            if (!is_numeric($sid) || empty($sid)) {
	            continue;
            }

            if ($zip->open($path, ZipArchive::CREATE) == TRUE) {

                $dossier = EMUNDUS_PATH_ABS.$users[$fnum]->id.DS;

                /// Build filename from tags, we are using helper functions found in the email model, not sending emails ;)
                $post = array(
                        'FNUM' => $fnum,
                        'CAMPAIGN_YEAR' => $fnumsInfo[$fnum]['year']
                    );
                $tags = $m_emails->setTags($users[$fnum]->id, $post);
                $application_form_name = $eMConfig->get('application_form_name', "application_form_pdf");
                $application_form_name = preg_replace($tags['patterns'], $tags['replacements'], $application_form_name);
                $application_form_name = $m_emails->setTagsFabrik($application_form_name, array($fnum));

                if ($application_form_name == "application_form_pdf") {
                    $application_form_name = $users[$fnum]->name.'_'.$fnum;
                }

                // Format filename
                $application_form_name = $m_emails->stripAccents($application_form_name);
                $application_form_name = preg_replace('/[^A-Za-z0-9 _.-]/','', $application_form_name);
                $application_form_name = preg_replace('/\s/', '', $application_form_name);
                $application_form_name = strtolower($application_form_name);

                $application_pdf = $application_form_name . '_applications.pdf';

                $files_list = array();

                if (isset($form_post)) {
                    $forms_to_export = array();
                    if (!empty($form_ids)) {
                        foreach ($form_ids as $fids) {
                            $detail = explode("|", $fids);
                            if ($detail[1] == $fnumsInfo[$fnum]['training'] && ($detail[2] == $fnumsInfo[$fnum]['campaign_id'] || $detail[2] == "0")) {
                                $forms_to_export[] = $detail[0];
                            }
                        }
                    }

                    if ($form_post || !empty($forms_to_export)) {
	                    $files_list[] = EmundusHelperExport::buildFormPDF($fnumsInfo[$fnum], $users[$fnum]->id, $fnum, $form_post, $forms_to_export, $options);
                    }
                }

                if ($assessment) {
	                $files_list[] = EmundusHelperExport::getEvalPDF($fnum, $options);
                }
                
                if ($decision) {
	                $files_list[] = EmundusHelperExport::getDecisionPDF($fnum, $options);
                }

                if ($admission) {
	                $files_list[] = EmundusHelperExport::getAdmissionPDF($fnum, $options);
                }
                
                if (count($files_list) > 0) {
                    // all PDF in one file
                    require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'fpdi.php');
                    $pdf = new ConcatPdf();

                    $pdf->setFiles($files_list);
                    $pdf->concat();

                    $pdf->Output($dossier . $application_pdf, 'F');

                    $filename = $application_form_name . DS . $application_pdf;

                    if (!$zip->addFile($dossier . $application_pdf, $filename)) {
	                    continue;
                    }
                }
                
                if ($attachment || !empty($attachids)) {
                    $attachment_to_export = array();
                    if (!empty($attachids)) {
                        foreach($attachids as $aids){
                            $detail = explode("|", $aids);
                            if ($detail[1] == $fnumsInfo[$fnum]['training'] && ($detail[2] == $fnumsInfo[$fnum]['campaign_id'] || $detail[2] == "0")) {
	                            $attachment_to_export[] = $detail[0];
                            }
                        }
                    }
                    
                    $fnum = explode(',', $fnum);
                    if ($attachment || !empty($attachment_to_export)) {
                        $files = $m_files->getFilesByFnums($fnum, $attachment_to_export);
                        $file_ids = array();

                        foreach($files as $file) {
	                        $file_ids[] = $file['attachment_id'];
                        }
                        
                        $setup_attachments = $m_files->getSetupAttachmentsById($attachment_to_export);
                        if (!empty($setup_attachments) && !empty($files)) {
                            foreach($setup_attachments as $att) {
                                if (!empty($files)) {
                                    foreach ($files as $file) {
                                        if ($file['attachment_id'] == $att['id']) {
                                            $filename = $application_form_name . DS . $file['filename'];
                                            $dossier = EMUNDUS_PATH_ABS . $users[$file['fnum']]->id . DS;
                                            if (file_exists($dossier . $file['filename'])) {
                                                if (!$zip->addFile($dossier . $file['filename'], $filename)) {
                                                    continue;
                                                }
                                            } else {
                                                $zip->addFromString($filename."-missing.txt", '');
                                            }
                                        } elseif (!in_array($att['id'], $file_ids)) {
                                            $zip->addFromString($application_form_name.DS.str_replace('_', "", $att['lbl'])."-notfound.txt", '');
                                        }
                                    }
                                } elseif (empty($files)) {
                                    foreach ($setup_attachments as $att) {
                                        $zip->addFromString($application_form_name . DS .str_replace('_', "", $att['lbl']) ."-notfound.txt", '');
                                    }
                                }
                            }
                        } elseif (!empty($files)) {
                            foreach ($files as $file) {
                                $filename = $application_form_name . DS . $file['filename'];
                                $dossier = EMUNDUS_PATH_ABS . $users[$file['fnum']]->id . DS;
                                if (file_exists($dossier . $file['filename'])) {
                                    if (!$zip->addFile($dossier . $file['filename'], $filename)) {
                                        continue;
                                    }
                                } else {
                                    $zip->addFromString($filename."-missing.txt", '');
                                }
                            }
                        } elseif (empty($files)) {
                            foreach ($setup_attachments as $att) {
                                $zip->addFromString($application_form_name . DS .str_replace('_', "", $att['lbl']) ."-notfound.txt", '');
                            }
                        }
                    }
                }
                $zip->close();

            } else {
                die("ERROR");
            }
        }
        return $nom;
    }

    /*
    *   Create a zip file containing all documents attached to application fil number
    */
    /**
     * @param $fnums
     * @return string
     */
    function export_zip_pcl($fnums)
    {
        $view           = JRequest::getCmd( 'view' );
        $current_user   = JFactory::getUser();

        if ((!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) && $view != 'renew_application')
            die( JText::_('RESTRICTED_ACCESS') );

        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
        require_once(JPATH_BASE.DS.'libraries'.DS.'emundus'.DS.'pdf.php');
        require_once(JPATH_BASE.DS.'libraries'.DS.'pclzip-2-8-2'.DS.'pclzip.lib.php');


        $nom = date("Y-m-d").'_'.rand(1000,9999).'_x'.(count($fnums)-1).'.zip';
        $path = JPATH_BASE.DS.'tmp'.DS.$nom;

        $zip = new PclZip($path);

        $m_files = $this->getModel('Files');
        $files = $m_files->getFilesByFnums($fnums);

        if(file_exists($path))
            unlink($path);

        $users = array();
        foreach ($fnums as $fnum) {
            $sid = intval(substr($fnum, -7));
            $users[$fnum] = JFactory::getUser($sid);

            if (!is_numeric($sid) || empty($sid))
                continue;

            $dossier = EMUNDUS_PATH_ABS.$users[$fnum]->id;
            $dir = $fnum.'_'.$users[$fnum]->name;
            application_form_pdf($users[$fnum]->id, $fnum, false);
            $application_pdf = $fnum.'_application.pdf';

            $zip->add($dossier.DS.$application_pdf, PCLZIP_OPT_REMOVE_ALL_PATH, PCLZIP_OPT_ADD_PATH, $dir);

        }


        foreach ($files as $key => $file) {
            $dir = $file['fnum'].'_'.$users[$file['fnum']]->name;
            $dossier = EMUNDUS_PATH_ABS.$users[$file['fnum']]->id.DS;
            $zip->add($dossier.$file['filename'], PCLZIP_OPT_REMOVE_ALL_PATH, PCLZIP_OPT_ADD_PATH, $dir);
        }

        return $nom;
    }

    /*
     *   Get evaluation Fabrik formid by fnum
     *
     *
     */
    function getformid() {
        $current_user = JFactory::getUser();

        if (!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id))
            die( JText::_('RESTRICTED_ACCESS') );

        $jinput = JFactory::getApplication()->input;
        $fnum   = $jinput->getString('fnum', null);

        $m_files = $this->getModel('Files');
        $res    = $m_files->getFormidByFnum($fnum);

        $formid = ($res>0)?$res:29;

        $result = array('status' => true, 'formid' => $formid);
        echo json_encode((object) $result);
        exit();
    }

   /*
     *   Get evaluation Fabrik formid by fnum
     *
     *
     */
    function getdecisionformid() {
        $current_user = JFactory::getUser();

        if (!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id))
            die( JText::_('RESTRICTED_ACCESS') );

        $jinput = JFactory::getApplication()->input;
        $fnum   = $jinput->getString('fnum', null);

        $m_files = $this->getModel('Files');
        $res    = $m_files->getDecisionFormidByFnum($fnum);

        $formid = ($res>0)?$res:29;

        $result = array('status' => true, 'formid' => $formid);
        echo json_encode((object) $result);
        exit();
    }


    /*
    *   Get my evaluation by fnum
    */
    /**
     *
     */
    function getevalid() {
        $current_user = JFactory::getUser();

        if (!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id))
            die( JText::_('RESTRICTED_ACCESS') );

        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);

        $m_evaluation = $this->getModel('Evaluation');
        $myEval = $m_evaluation->getEvaluationsFnumUser($fnum, $current_user->id);
        $evalid = ($myEval[0]->id>0)?$myEval[0]->id:-1;

        $result = array('status' => true, 'evalid' => $evalid);
        echo json_encode((object) $result);
        exit();
    }

    public function getdocs()
    {
        $jinput = JFactory::getApplication()->input;
        $code = $jinput->getString('code', "");
        $m_files = $this->getModel('Files');

        $res = new stdClass();
        $res->status = true;
        $res->options = $m_files->getDocsByProg($code);

        echo json_encode($res);
        exit();
    }


    /** Generate a new document from the many templates.
     * @throws Exception
     */
    public function generatedoc() {
        $jinput = JFactory::getApplication()->input;
        $fnums = $jinput->post->getString('fnums', "");
        $code = $jinput->post->getString('code', "");
        $idTmpl = $jinput->post->getString('id_tmpl', "");
        $canSee = $jinput->post->getInt('cansee', 0);

        $fnumsArray = explode(",", $fnums);

        $m_files = $this->getModel('Files');
        $m_evaluation = $this->getModel('Evaluation');
        $m_emails = $this->getModel('Emails');

        $user = JFactory::getUser();

        $fnumsArray = $m_files->checkFnumsDoc($code, $fnumsArray);
        $tmpl = $m_evaluation->getLettersTemplateByID($idTmpl);
        $attachInfos = $m_files->getAttachmentInfos($tmpl[0]['attachment_id']);

        $res = new stdClass();
        $res->status = true;
        $res->files = [];
        $fnumsInfos = $m_files->getFnumsTagsInfos($fnumsArray);

        switch ($tmpl[0]['template_type']) {

            case 1:
                // Simple FILE.
                $file = JPATH_BASE . $tmpl[0]['file'];
                if (file_exists($file)) {
                    foreach ($fnumsArray as $fnum) {
                        if (isset($fnumsInfos[$fnum])) {
                            $name = $attachInfos['lbl'] . '_' . date('Y-m-d_H-i-s') . '.' . pathinfo($file)['extension'];
                            $path = EMUNDUS_PATH_ABS . $fnumsInfos[$fnum]['applicant_id'] . DS . $name;
                            if (copy($file, $path)) {
                                $url = JURI::base().EMUNDUS_PATH_REL . $fnumsInfos[$fnum]['applicant_id'] . '/';
                                $upId = $m_files->addAttachment($fnum, $name, $fnumsInfos[$fnum]['applicant_id'], $fnumsInfos[$fnum]['campaign_id'], $tmpl[0]['attachment_id'], $attachInfos['description']);

                                $res->files[] = array('filename' => $name, 'upload' => $upId, 'url' => $url,);
                            }
                        }
                    }
                } else {
                    $res->status = false;
                    $res->msg = JText::_("ERROR_CANNOT_GENERATE_FILE");
                }

                echo json_encode($res);
                break;

            case 2:

                // PDF From HTML.
                foreach ($fnumsArray as $fnum) {
                    if (isset($fnumsInfos[$fnum])) {
                        $post = [
                            'TRAINING_CODE' => $fnumsInfos[$fnum]['campaign_code'],
                            'TRAINING_PROGRAMME' => $fnumsInfos[$fnum]['campaign_label'],
                            'CAMPAIGN_LABEL' => $fnumsInfos[$fnum]['campaign_label'],
                            'CAMPAIGN_YEAR' => $fnumsInfos[$fnum]['campaign_year'],
                            'USER_NAME' => $fnumsInfos[$fnum]['applicant_name'],
                            'USER_EMAIL' => $fnumsInfos[$fnum]['applicant_email'],
                            'FNUM' => $fnum
                        ];

                        // Generate PDF
                        $tags = $m_emails->setTags($fnumsInfos[$fnum]['applicant_id'], $post, $fnum);

                        require_once(JPATH_LIBRARIES . DS . 'emundus' . DS . 'MYPDF.php');
                        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                        $pdf->SetCreator(PDF_CREATOR);
                        $pdf->SetAuthor($user->name);
                        $pdf->SetTitle($tmpl[0]['title']);

                        // Set margins
                        $pdf->SetMargins(5, 40, 5);
                        $pdf->footer = $tmpl[0]["footer"];

                        // Get logo
                        preg_match('#src="(.*?)"#i', $tmpl[0]['header'], $tab);
                        $pdf->logo = JPATH_BASE . DS . $tab[1];

                        // Get footer.
                        preg_match('#src="(.*?)"#i', $tmpl[0]['footer'], $tab);
                        $pdf->logo_footer = JPATH_BASE . DS . @$tab[1];
                        unset($logo, $logo_footer);

                        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

                        // Set default monospaced font
                        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                        // Set default font subsetting mode
                        $pdf->setFontSubsetting(true);

                        // Set font
                        $pdf->SetFont('freeserif', '', 8);

                        $htmldata = $m_emails->setTagsFabrik($tmpl[0]["body"], array($fnum));
                        // clean html
                        $htmldata = preg_replace($tags['patterns'], $tags['replacements'], preg_replace("/<span[^>]+\>/i", "", preg_replace("/<\/span\>/i", "", preg_replace("/<br[^>]+\>/i", "<br>", $htmldata))));
                        // base64 images to link
                        $htmldata = preg_replace_callback('#(<img\s(?>(?!src=)[^>])*?src=")data:image/(gif|png|jpeg);base64,([\w=+/]++)("[^>]*>)#', function($match) {
														    list(, $img, $type, $base64, $end) = $match;

														    $bin = base64_decode($base64);
														    $md5 = md5($bin);   // generate a new temporary filename
														    $fn = "tmp/$md5.$type";
														    file_exists($fn) or file_put_contents($fn, $bin);

														    return "$img$fn$end";  // new <img> tag
														}, $htmldata);
                        $htmldata = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $htmldata);

                        $pdf->AddPage();

                        // Print text using writeHTMLCell()
                        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $htmldata, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);

						$rand = rand(0, 1000000);
						if (!file_exists(EMUNDUS_PATH_ABS . $fnumsInfos[$fnum]['applicant_id'])) {
						    mkdir(EMUNDUS_PATH_ABS . $fnumsInfos[$fnum]['applicant_id'], 0775);
						}

                        $anonymize_data = EmundusHelperAccess::isDataAnonymized(JFactory::getUser()->id);
                        if (!$anonymize_data) {
                            $name = $this->sanitize_filename($fnumsInfos[$fnum]['applicant_name']).$attachInfos['lbl']."-".md5($rand.time()).".pdf";
                        } else {
                            $name = $this->sanitize_filename($fnumsInfos[$fnum]).$attachInfos['lbl']."-".md5($rand.time()).".pdf";
                        }

                        $path = EMUNDUS_PATH_ABS . $fnumsInfos[$fnum]['applicant_id'] . DS . $name;
                        $url = JURI::base().EMUNDUS_PATH_REL . $fnumsInfos[$fnum]['applicant_id'] . '/';
                        $upId = $m_files->addAttachment($fnum, $name, $fnumsInfos[$fnum]['applicant_id'], $fnumsInfos[$fnum]['campaign_id'], $tmpl[0]['attachment_id'], $attachInfos['description'], $canSee);

                        $pdf->Output($path, 'F');
                        $res->files[] = array('filename' => $name, 'upload' => $upId, 'url' => $url);
                    }
                }
                unset($pdf, $path, $name, $url, $upIdn);
                echo json_encode($res);
                break;

            case 3:
                // template DOCX
                //require_once JPATH_LIBRARIES . DS . 'vendor' . DS . 'autoload.php';
                require_once (JPATH_LIBRARIES . '/emundus/vendor/autoload.php');
                require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'export.php');

                $m_export = new EmundusModelExport;

                $eMConfig = JComponentHelper::getParams('com_emundus');
                $gotenberg_activation = $eMConfig->get('gotenberg_activation', 0);

                $const = array('user_id' => $user->id, 'user_email' => $user->email, 'user_name' => $user->name, 'current_date' => date('d/m/Y', time()));

                // Special tags which require a bit more work.
                $special = ['user_dob_age', 'evaluation_radar'];

                try {
                    $phpWord = new \PhpOffice\PhpWord\PhpWord();
                    $preprocess = $phpWord->loadTemplate(JPATH_BASE . $tmpl[0]['file']);
                    $tags = $preprocess->getVariables();
                    $idFabrik = array();
                    $setupTags = array();
                    foreach ($tags as $i => $val) {
                        $tag = strip_tags($val);
                        if (is_numeric($tag)) {
                            $idFabrik[] = $tag;
                        } else {
                            $setupTags[] = $tag;
                        }
                    }

                    if (!empty($idFabrik)) {
                        $fabrikElts = $m_files->getValueFabrikByIds($idFabrik);
                    } else {
                        $fabrikElts = array();
                    }

                    $fabrikValues = array();
                    foreach ($fabrikElts as $elt) {
                        $params = json_decode($elt['params']);
                        $groupParams = json_decode($elt['group_params']);
                        $isDate = ($elt['plugin'] == 'date');
                        $isDatabaseJoin = ($elt['plugin'] === 'databasejoin');

                        if (@$groupParams->repeat_group_button == 1 || $isDatabaseJoin) {
                            $fabrikValues[$elt['id']] = $m_files->getFabrikValueRepeat($elt, $fnumsArray, $params, $groupParams->repeat_group_button == 1);
                        } else {
                            if ($isDate) {
                                $fabrikValues[$elt['id']] = $m_files->getFabrikValue($fnumsArray, $elt['db_table_name'], $elt['name'], $params->date_form_format);
                            } else {
                                $fabrikValues[$elt['id']] = $m_files->getFabrikValue($fnumsArray, $elt['db_table_name'], $elt['name']);
                            }
                        }

                        if ($elt['plugin'] == "checkbox" || $elt['plugin'] == "dropdown") {

                            foreach ($fabrikValues[$elt['id']] as $fnum => $val) {

                                if ($elt['plugin'] == "checkbox") {
                                    $val = json_decode($val['val']);
                                } else {
                                    $val = explode(',', $val['val']);
                                }

                                if (count($val) > 0) {
                                    foreach ($val as $k => $v) {
                                        $index = array_search(trim($v), $params->sub_options->sub_values);
                                        $val[$k] = $params->sub_options->sub_labels[$index];
                                    }
                                    $fabrikValues[$elt['id']][$fnum]['val'] = implode(", ", $val);
                                } else {
                                    $fabrikValues[$elt['id']][$fnum]['val'] = "";
                                }

                            }

                        } elseif ($elt['plugin'] == "birthday") {

                            foreach ($fabrikValues[$elt['id']] as $fnum => $val) {
                                $val = explode(',', $val['val']);
                                foreach ($val as $k => $v) {
                                    $val[$k] = date($params->details_date_format, strtotime($v));
                                }
                                $fabrikValues[$elt['id']][$fnum]['val'] = implode(",", $val);
                            }

                        } else {
                            if (@$groupParams->repeat_group_button == 1 || $isDatabaseJoin) {
                                $fabrikValues[$elt['id']] = $m_files->getFabrikValueRepeat($elt, $fnumsArray, $params, $groupParams->repeat_group_button == 1);
                            } else {
                                $fabrikValues[$elt['id']] = $m_files->getFabrikValue($fnumsArray, $elt['db_table_name'], $elt['name']);
                            }
                        }
                    }

                    foreach ($fnumsArray as $fnum) {

                        $preprocess = new \PhpOffice\PhpWord\TemplateProcessor(JPATH_BASE . $tmpl[0]['file']);
                        if (isset($fnumsInfos[$fnum])) {
                            foreach ($setupTags as $tag) {
                                $val = "";
                                $lowerTag = strtolower($tag);

                                if (array_key_exists($lowerTag, $const)) {

                                    $preprocess->setValue($tag, $const[$lowerTag]);

                                } elseif (in_array($lowerTag, $special)) {

                                    // Each tag has it's own logic requiring special work.
                                    switch ($lowerTag) {

                                        // dd-mm-YYYY (YY)
                                        case 'user_dob_age':
                                            $birthday = $m_files->getBirthdate($fnum, 'd/m/Y', true);
                                            $preprocess->setValue($tag, $birthday->date . ' (' . $birthday->age . ')');
                                            break;

                                        default:
                                            $preprocess->setValue($tag, '');
                                            break;
                                    }

                                } elseif (!empty(@$fnumsInfos[$fnum][$lowerTag])) {
                                    $preprocess->setValue($tag, @$fnumsInfos[$fnum][$lowerTag]);
                                } else {
                                    $tags = $m_emails->setTagsWord(@$fnumsInfos[$fnum]['applicant_id'], ['FNUM' => $fnum], $fnum, '');
                                    $i = 0;
                                    foreach ($tags['patterns'] as $value) {
                                        if ($value == $tag) {
                                            $val = $tags['replacements'][$i];
                                            break;
                                        }
                                        $i++;
                                    }
                                    $preprocess->setValue($tag, htmlspecialchars($val));
                                }
                            }
                            foreach ($idFabrik as $id) {
                                if (isset($fabrikValues[$id][$fnum])) {
                                    $value = str_replace('\n', ', ', $fabrikValues[$id][$fnum]['val']);
                                    $preprocess->setValue($id, htmlspecialchars($value));
                                } else {
                                    $preprocess->setValue($id, '');
                                }
                            }

                            $rand = rand(0, 1000000);
                            if (!file_exists(EMUNDUS_PATH_ABS . $fnumsInfos[$fnum]['applicant_id'])) {
                                mkdir(EMUNDUS_PATH_ABS . $fnumsInfos[$fnum]['applicant_id'], 0775);
                            }

                            $filename = $this->sanitize_filename($fnumsInfos[$fnum]['applicant_name']).$attachInfos['lbl']."-".md5($rand . time()).".docx";

                            $preprocess->saveAs(EMUNDUS_PATH_ABS.$fnumsInfos[$fnum]['applicant_id'].DS.$filename);

                            if($gotenberg_activation == 1 && $tmpl[0]['pdf'] == 1){
                                //convert to PDF
                                $src = EMUNDUS_PATH_ABS.$fnumsInfos[$fnum]['applicant_id'].DS.$filename;
                                $dest = str_replace('.docx', '.pdf', $src);
                                $filename = str_replace('.docx', '.pdf', $filename);
                                $res = $m_export->toPdf($src, $dest, $fnum);
                            }

                            $upId = $m_files->addAttachment($fnum, $filename, $fnumsInfos[$fnum]['applicant_id'], $fnumsInfos[$fnum]['campaign_id'], $tmpl[0]['attachment_id'], $attachInfos['description']);

                            $res->files[] = array('filename' => $filename, 'upload' => $upId, 'url' => JURI::base().EMUNDUS_PATH_REL . $fnumsInfos[$fnum]['applicant_id'] . '/',);
                        }
                        unset($preprocess);
                    }
                    echo json_encode($res);

                } catch (Exception $e) {
                    $res->status = false;
                    $res->msg = JText::_("AN_ERROR_OCURRED") . ':' . $e->getMessage();
                    echo json_encode($res);
                    exit();
                }
                break;

        case 4 :
            //require_once JPATH_LIBRARIES.DS.'phpspreadsheet'.DS.'phpspreadsheet.php';
            //require JPATH_LIBRARIES . '/emundus/vendor/autoload.php';
require_once (JPATH_LIBRARIES . '/emundus/vendor/autoload.php');

            $inputFileName = JPATH_BASE . $tmpl[0]['file'];
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

            $reader->setIncludeCharts(true);
            $spreadsheet = $reader->load($inputFileName);

            $const = array('user_id' => $user->id, 'user_email' => $user->email, 'user_name' => $user->name, 'current_date' => date('d/m/Y', time()));

            // Special tags which require a bit more work.
            $special = ['user_dob_age'];

            foreach ($fnumsArray as $fnum) {
                if (isset($fnumsInfos[$fnum])) {

                    $preprocess = $spreadsheet->getAllSheets(); //Search in each sheet of the workbook

                    foreach ($preprocess as $sheet) {
                        foreach ($sheet->getRowIterator() as $row) {
                            $cellIterator = $row->getCellIterator();
                            foreach ($cellIterator as $cell) {

                                $cell->getValue();

                                $regex = '/\$\{(.*?)}|\[(.*?)]/';
                                preg_match_all($regex, $cell, $matches);

                                $idFabrik = array();
                                $setupTags = array();

                                foreach ($matches[1] as $i => $val) {

                                    $tag = strip_tags($val);

                                    if (is_numeric($tag)) {
                                        $idFabrik[] = $tag;
                                    } else {
                                        $setupTags[] = $tag;
                                    }
                                }

                                if (!empty($idFabrik)) {
                                    $fabrikElts = $m_files->getValueFabrikByIds($idFabrik);
                                } else {
                                    $fabrikElts = array();
                                }

                                $fabrikValues = $this->getValueByFabrikElts($fabrikElts, $fnumsArray);

                                foreach ($setupTags as $tag) {
                                    $val = "";
                                    $lowerTag = strtolower($tag);

                                    if (array_key_exists($lowerTag, $const)) {
                                        $cell->setValue($const[$lowerTag]);
                                    } elseif (in_array($lowerTag, $special)) {

                                        // Each tag has it's own logic requiring special work.
                                        switch ($lowerTag) {

                                            // dd-mm-YYYY (YY)
                                            case 'user_dob_age':
                                                $birthday = $m_files->getBirthdate($fnum, 'd/m/Y', true);
                                                $cell->setValue($birthday->date . ' (' . $birthday->age . ')');
                                                break;

                                            default:
                                                $cell->setValue('');
                                                break;
                                        }

                                    } elseif (!empty(@$fnumsInfos[$fnum][$lowerTag])) {
                                        $cell->setValue(@$fnumsInfos[$fnum][$lowerTag]);
                                    } else {
                                        $tags = $m_emails->setTagsWord(@$fnumsInfos[$fnum]['applicant_id'], ['FNUM' => $fnum], $fnum, '');
                                        $i = 0;
                                        foreach ($tags['patterns'] as $value) {
                                            if ($value == $tag) {
                                                $val = $tags['replacements'][$i];
                                                break;
                                            }
                                            $i++;
                                        }
                                        $cell->setValue(htmlspecialchars($val));
                                    }
                                }
                                foreach ($idFabrik as $id) {

                                    if (isset($fabrikValues[$id][$fnum])) {
                                        $value = str_replace('\n', ', ', $fabrikValues[$id][$fnum]['val']);
                                        $cell->setValue(htmlspecialchars($value));
                                    } else {
                                        $cell->setValue('');
                                    }

                                }
                            }
                        }

                    }
                    $rand = rand(0, 1000000);
                    if (!file_exists(EMUNDUS_PATH_ABS . $fnumsInfos[$fnum]['applicant_id'])) {
                        mkdir(EMUNDUS_PATH_ABS . $fnumsInfos[$fnum]['applicant_id'], 0775);
                    }

                    $filename = $this->sanitize_filename($fnumsInfos[$fnum]['applicant_name']).$attachInfos['lbl']."-".md5($rand . time()).".xlsx";

                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                    $writer->setIncludeCharts(true);

                    $writer->save(EMUNDUS_PATH_ABS . $fnumsInfos[$fnum]['applicant_id'] . DS . $filename);

                    $upId = $m_files->addAttachment($fnum, $filename, $fnumsInfos[$fnum]['applicant_id'], $fnumsInfos[$fnum]['campaign_id'], $tmpl[0]['attachment_id'], $attachInfos['description']);

                    $res->files[] = array('filename' => $filename, 'upload' => $upId, 'url' => JURI::base().EMUNDUS_PATH_REL.$fnumsInfos[$fnum]['applicant_id'].'/',);
                }
            }
            echo json_encode($res);
        break;
        }
    }

    private function sanitize_filename($name) {
    	return strtolower(preg_replace(['([\40])', '([^a-zA-Z0-9-])','(-{2,})'], ['_','','_'], preg_replace('/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/', '$1', htmlentities($name, ENT_NOQUOTES, 'UTF-8'))));
    }

    public function exportzipdoc() {
        $jinput = JFactory::getApplication()->input;
        $idFiles = explode(",", $jinput->getStrings('ids', ""));
        $m_files = $this->getModel('Files');
        $files = $m_files->getAttachmentsById($idFiles);

        $nom = date("Y-m-d").'_'.md5(rand(1000,9999).time()).'_x'.(count($files)-1).'.zip';
        $path = JPATH_BASE.DS.'tmp'.DS.$nom;

        if (extension_loaded('zip')) {
            $zip = new ZipArchive();

            if ($zip->open($path, ZipArchive::CREATE) == TRUE) {
                foreach ($files as $key => $file) {
                    $filename = EMUNDUS_PATH_ABS.$file['user_id'].DS.$file['filename'];
                    if (!$zip->addFile($filename, $file['filename'])) {
                        continue;
                    }
                }
                $zip->close();
            } else {
                die ("ERROR");
            }

        } else {
            require_once(JPATH_BASE.DS.'libraries'.DS.'pclzip-2-8-2'.DS.'pclzip.lib.php');
            $zip = new PclZip($path);

            foreach ($files as $key => $file) {
                $user = JFactory::getUser($file['user_id']);
                $dir = $file['fnum'].'_'.$user->name;
                $filename = EMUNDUS_PATH_ABS.$file['user_id'].DS.$file['filename'];

                $zip->add($filename, PCLZIP_OPT_REMOVE_ALL_PATH, PCLZIP_OPT_ADD_PATH, $dir);

                if (!$zip->addFile($filename, $file['filename'])) {
                    continue;
                }
            }
        }

        $mime_type = $this->get_mime_type($path);
        header('Content-type: application/'.$mime_type);
        header('Content-Disposition: inline; filename='.basename($path));
        header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
        header('Pragma: anytextexeptno-cache', true);
        header('Cache-control: private');
        header('Expires: 0');
        ob_clean();
        flush();
        readfile($path);
        exit;
    }

    public function exportonedoc() {
        //require_once JPATH_LIBRARIES.DS.'vendor'.DS.'autoload.php';
require_once (JPATH_LIBRARIES . '/emundus/vendor/autoload.php');

        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            $rendererName = \PhpOffice\PhpWord\Settings::PDF_RENDERER_TCPDF;
            \PhpOffice\PhpWord\Settings::setPdfRenderer($rendererName, JPATH_LIBRARIES . DS . 'emundus' . DS . 'tcpdf');
        }

        $jinput = JFactory::getApplication()->input;
        $idFiles = explode(",", $jinput->getStrings('ids', ""));
        $m_files = $this->getModel('Files');
        $files = $m_files->getAttachmentsById($idFiles);
        $nom = date("Y-m-d").'_'.md5(rand(1000,9999).time()).'_x'.(count($files)-1).'.pdf';
        $path = JPATH_BASE.DS.'tmp'.DS.$nom;

        $wordPHP = new \PhpOffice\PhpWord\PhpWord();

        $docs = array();
        foreach ($files as $key => $file) {
            $filename = EMUNDUS_PATH_ABS.$file['user_id'].DS.$file['filename'];
            $tmpName = JPATH_BASE.DS.'tmp'.DS.$file['filename'];
            $document = $wordPHP->loadTemplate($filename);
            $document->saveAs($tmpName); // Save to temp file

            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                $wordPHP = \PhpOffice\PhpWord\IOFactory::load($tmpName); // Read the temp file
                $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($wordPHP, 'PDF');

                $xmlWriter->save($tmpName.'.pdf');  // Save to PDF
            }

            $docs[] = $tmpName.'.pdf';
            unlink($tmpName); // Delete the temp file
        }
        require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'fpdi.php');
        $pdf = new ConcatPdf();
        $pdf->setFiles($docs);
        $pdf->concat();
        if (isset($docs)) {
            foreach ($docs as $fn) {
                unlink($fn);
            }
        }
        ob_end_clean();
        $pdf->Output($path, 'I');
        exit;
    }

    public function getPDFProgrammes() {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        $html = '';
        $session = JFactory::getSession();
        $jinput = JFactory::getApplication()->input;
        $m_files = new EmundusModelFiles;
		
	$fnums_post = $jinput->getVar('checkInput', null);
        $fnums_array = ($fnums_post=='all')?'all':(array) json_decode(stripslashes($fnums_post), false, 512, JSON_BIGINT_AS_STRING);

        if ($fnums_array == 'all') {
            $fnums = $m_files->getAllFnums();
		} else {
            $fnums = array();
            foreach ($fnums_array as $key => $value) {
                $fnums[] = $value;
            }
        }


        $m_campaigns = new EmundusModelCampaign;

        if (!empty($fnums)) {
            foreach ($fnums as $fnum) {
                if ($fnum != "em-check-all") {
                    $campaign  = $m_campaigns->getCampaignByFnum($fnum);
                    $programme = $m_campaigns->getProgrammeByCampaignID((int)$campaign->id);
                    $option = '<option value="'.$programme['code'].'">'.$programme['label'].'</option>';
                    if (strpos($html, $option) === false) {
                        $html .= $option;
                    }
                }
            }
        }

        echo json_encode((object)(array('status' => true, 'html' => $html)));
        exit;
    }

    public function getPDFCampaigns() {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        $html = '';
        $session     = JFactory::getSession();
        $jinput      = JFactory::getApplication()->input;
        $m_files = new EmundusModelFiles;

        $code        = $jinput->getString('code', null);

        $fnums_post = $jinput->getVar('checkInput', null);
        $fnums_array = ($fnums_post=='all')?'all':(array) json_decode(stripslashes($fnums_post), false, 512, JSON_BIGINT_AS_STRING);

        if ($fnums_array == 'all') {
            $fnums = $m_files->getAllFnums();
		} else {
            $fnums = array();
            foreach ($fnums_array as $key => $value) {
                $fnums[] = $value;
            }
        }

        $m_campaigns = new EmundusModelCampaign;
        $nbcamp = 0;
        if (!empty($fnums)) {

            foreach ($fnums as $fnum) {
                $campaign  = $m_campaigns->getCampaignByFnum($fnum);
                if ($campaign->training == $code) {
                    $nbcamp += 1;
                    $option = '<option value="'.$campaign->id.'">'.$campaign->label.' ('.$campaign->year.')</option>';
                    if (strpos($html, $option) === false) {
                        $html .= $option;
                    }
                }

            }
        }

        echo json_encode((object)(array('status' => true, 'html' => $html, 'nbcamp' => $nbcamp)));
        exit;
    }


    public function getProgrammes(){
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
        $html = '';
        $session     = JFactory::getSession();
        $filt_params = $session->get('filt_params');

        $h_files = new EmundusHelperFiles;
        $programmes = $h_files->getProgrammes($filt_params['programme']);

        $nbprg = count($programmes);
        if (empty($filt_params)){
            $params['programme'] = $programmes;
            $session->set('filt_params', $params);
        }
        foreach ($programmes as $p) {
            if ($nbprg == 1) {
                $html .= '<option value="'.$p->code.'" selected>'.$p->label.' - '.$p->code.'</option>';
            } else {
                $html .= '<option value="'.$p->code.'">'.$p->label.' - '.$p->code.'</option>';
            }
        }

        echo json_encode((object)(array('status' => true, 'html' => $html, 'nbprg' => $nbprg)));
        exit;
    }
    public function getProgramCampaigns(){
        $html = '';

        $h_files = new EmundusHelperFiles;
        $jinput = JFactory::getApplication()->input;
        $code       = $jinput->getString('code', null);
        $campaigns = $h_files->getProgramCampaigns($code);

        $nbcamp = count($campaigns);
        foreach ($campaigns as $c) {
            if ($nbcamp == 1) {
                $html .= '<option value="'.$c->id.'" selected>'.$c->label.' - '.$c->training.' ('.$c->year.')</option>';
            } else {
                $html .= '<option value="'.$c->id.'">'.$c->label.' - '.$c->training.' ('.$c->year.')</option>';
            }
        }

        echo json_encode((object)(array('status' => true, 'html' => $html, 'nbcamp' => $nbcamp)));
        exit;
    }

    public function saveExcelFilter() {
        $current_user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $name = $jinput->getString('filt_name', null);
        $itemid = $jinput->get->get('Itemid', null);

        $params = $jinput->getString('params', null);
        $constraints = json_encode(array('excelfilter'=>$params));

        $h_files = new EmundusHelperFiles;
        if (empty($itemid)) {
            $itemid = $jinput->post->get('Itemid', null);
        }

        $time_date = (date('Y-m-d H:i:s'));
        $result = $h_files->saveExcelFilter($current_user->id, $name, $constraints, $time_date, $itemid);

        echo json_encode((object)(array('status' => true, 'filter' => $result)));
        exit;
    }

    public function getExportExcelFilter() {
        $user_id  = JFactory::getUser()->id;

        $h_files = new EmundusHelperFiles;
        $filters = $h_files->getExportExcelFilter($user_id);

        echo json_encode((object)(array('status' => true, 'filter' => $filters)));
        exit;
    }

    public function checkforms(){
        $user_id   = JFactory::getUser()->id;
        $jinput    = JFactory::getApplication()->input;
        $code      = $jinput->getString('code', null);

        $m_eval = new EmundusModelEvaluation;
        $m_adm = new EmundusModelAdmission;

        $eval = $m_eval->getGroupsEvalByProgramme($code);
        $dec = $m_eval->getGroupsDecisionByProgramme($code);
        $adm = $m_adm->getGroupsAdmissionByProgramme($code);
        $adm .= $m_adm->getGroupsApplicantAdmissionByProgramme($code);

        $hasAccessAtt  = EmundusHelperAccess::asAccessAction(4,  'r', $user_id);
        $hasAccessEval = EmundusHelperAccess::asAccessAction(5,  'r', $user_id);
        $hasAccessDec  = EmundusHelperAccess::asAccessAction(29, 'r', $user_id);
        $hasAccessAdm  = EmundusHelperAccess::asAccessAction(32, 'r', $user_id);
        $hasAccessTags = EmundusHelperAccess::asAccessAction(14, 'r', $user_id);

        $showatt = 0;
        $showeval = 0;
        $showdec  = 0;
        $showadm  = 0;
        $showtag  =0;

        if ($hasAccessAtt) {
            $showatt = 1;
        }
        if (!empty($eval) && $hasAccessEval) {
            $showeval = 1;
        }
        if (!empty($dec) && $hasAccessDec) {
            $showdec = 1;
        }
        if (!empty($adm) && $hasAccessAdm) {
            $showadm = 1;
        }
        if ($hasAccessTags) {
            $showtag = 1;
        }

        echo json_encode((object)(array('status' => true,'att' => $showatt, 'eval' => $showeval, 'dec' => $showdec, 'adm' => $showadm, 'tag' => $showtag)));
        exit;

    }

    /**
     * Generates or (if it exists already) loads the PDF for a certain GesCOF product.
     */
    public function getproductpdf() {

        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'export.php');

        $h_export = new EmundusHelperExport();

        $jinput = JFactory::getApplication()->input;
        $product_code = $jinput->post->get('product_code', null);

        $filename = DS.'images'.DS.'product_pdf'.DS.'formation-'.$product_code.'.pdf';

        // PDF is rebuilt every time, this is because the information on the PDF probably changes ofter.
        if (file_exists(JPATH_BASE.$filename)) {
            unlink(JPATH_BASE.$filename);
        }

        // The PDF template is saved in the Joomla backoffice as an article.
        $article = $h_export->getArticle(58);

        if (empty($article)) {
            echo json_encode((object)['status' => false, 'msg' => 'Article not found.']);
            exit;
        }

        $query = $this->_db->getQuery(true);
        $query
            ->select([
                $this->_db->quoteName('p.label','name'), $this->_db->quoteName('p.numcpf','cpf'), $this->_db->quoteName('p.prerequisite','prerec'), $this->_db->quoteName('p.audience','audience'), $this->_db->quoteName('p.tagline','tagline'), $this->_db->quoteName('p.objectives','objectives'), $this->_db->quoteName('p.content','content'), $this->_db->quoteName('p.manager_firstname','manager_firstname'), $this->_db->quoteName('p.manager_lastname','manager_lastname'), $this->_db->quoteName('p.pedagogie', 'pedagogie'), $this->_db->quoteName('p.partner', 'partner'), $this->_db->quoteName('p.evaluation', 'evaluation'),
                $this->_db->quoteName('t.label','theme'), $this->_db->quoteName('t.color','class'),
                $this->_db->quoteName('tu.price','price'), $this->_db->quoteName('tu.session_code','session_code'), $this->_db->quoteName('tu.date_start', 'date_start'), $this->_db->quoteName('tu.date_end', 'date_end'), $this->_db->quoteName('tu.days','days'), $this->_db->quoteName('tu.hours','hours'), $this->_db->quoteName('tu.time_in_company', 'time_in_company'), $this->_db->quoteName('tu.min_occupants','min_o'), $this->_db->quoteName('tu.max_occupants','max_o'), $this->_db->quoteName('tu.occupants','occupants'), $this->_db->quoteName('tu.location_city','city'), $this->_db->quoteName('tu.tax_rate','tax_rate'), $this->_db->quoteName('tu.intervenant', 'intervenant'), $this->_db->quoteName('tu.label', 'session_label')
            ])
            ->from($this->_db->quoteName('#__emundus_setup_programmes','p'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_thematiques','t').' ON '.$this->_db->quoteName('t.id').' = '.$this->_db->quoteName('p.programmes'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_teaching_unity','tu').' ON '.$this->_db->quoteName('tu.code').' = '.$this->_db->quoteName('p.code'))
            ->where($this->_db->quoteName('p.code').' LIKE '.$this->_db->quote($product_code).' AND '.$this->_db->quoteName('tu.published').' = 1 AND '.$this->_db->quoteName('tu.date_start').' >= '.date("Y-m-d"))
            ->order($this->_db->quoteName('tu.date_start').' ASC');
        $this->_db->setQuery($query);

        try {
            $product = $this->_db->loadAssocList();
        } catch (Exception $e) {
            echo json_encode((object)['status' => false, 'msg' => 'Error getting product information.']);
            exit;
        }

        setlocale(LC_ALL, 'fr_FR.utf8');
        $sessions = "<ul>";
        foreach ($product as $session) {
            if(strtotime($session['date_end']) >= strtotime("now") ) {

                $start_month = date('m',strtotime($session['date_start']));
                $end_month = date('m',strtotime($session['date_end']));
                $start_year = date('y',strtotime($session['date_start']));
                $end_year = date('y',strtotime($session['date_end']));

                if (intval($session['days']) == 1) {

                    $sessions .= '<li>Le '.strftime('%e',strtotime($session['date_start']))." ".strftime('%B',strtotime($session['date_end']))." ".date('Y',strtotime($session['date_end']));

                } else {

                    if ($start_month == $end_month && $start_year == $end_year) {
                        $sessions .= '<li>'.strftime('%e',strtotime($session['date_start'])) . " au " . strftime('%e',strtotime($session['date_end'])) . " " . strftime('%B',strtotime($session['date_end'])) . " " . date('Y',strtotime($session['date_end']));
                    } elseif ($start_month != $end_month && $start_year == $end_year) {
                        $sessions .= '<li>'.strftime('%e',strtotime($session['date_start'])) . " " . strftime('%B',strtotime($session['date_start'])) . " au " . strftime('%e',strtotime($session['date_end'])) . " " . strftime('%B',strtotime($session['date_end'])) . " " . date('Y',strtotime($session['date_end']));
                    } elseif (($start_month != $end_month && $start_year != $end_year) || ($start_month == $end_month && $start_year != $end_year)) {
                        $sessions .= '<li>'.strftime('%e',strtotime($session['date_start'])) . " " . strftime('%B',strtotime($session['date_end'])) . " " . date('Y',strtotime($session['date_start'])) . " au " . strftime('%e',strtotime($session['date_end'])) . " " . strftime('%B',strtotime($session['date_end'])) . " " . date('Y',strtotime($session['date_end']));
                    }
                }

                $sessions .= ' à '.ucfirst(str_replace(' cedex','',mb_strtolower($session['city']))).' : '.$session['price'].' € '.(!empty($session['tax_rate'])?'HT':'net de taxe').'</li>';
            }
        }
        $sessions .= '</ul>';

        $partner = str_replace(' ', '-', trim(strtolower($product[0]['partner'])));
        if (!empty($partner)) {
            $partner = '<img src="images/custom/ccirs/partenaires/'.$partner.'.png" height="30">';
        } else {
            $partner = '';
        }

        if (!empty($product[0]['days']) && !empty($product[0]['hours'])) {
            $days = $product[0]['days'].' '.((intval($product[0]['days']) > 1)?'jours':'jour')." pour un total de : ".$product[0]['hours']." heures";
            if (!empty($session['time_in_company'])) {
                $days .= ' '.$product[0]['time_in_company'];
            }
        } else {
            $days = 'Aucune information disponible.';
        }

        // Build the variables found in the article.
        $post = [
            '/{PARTNER_LOGO}/' => $partner,
            '/{PRODUCT_CODE}/' => str_replace('FOR', '', $product_code),
            '/{PRODUCT_NAME}/' => ucfirst(mb_strtolower($product[0]['session_label'])),
            '/{PRODUCT_OBJECTIVES}/' => $product[0]['objectives'],
            '/{PRODUCT_PREREQUISITES}/' => $product[0]['prerec'],
            '/{PRODUCT_AUDIENCE}/' => $product[0]['audience'],
            '/{PRODUCT_CONTENT}/' => $product[0]['content'],
            '/{PRODUCT_MANAGER}/' => $product[0]['manager_firstname'].' '.mb_strtoupper($product[0]['manager_lastname']),
            '/{EXPORT_DATE}/' => date('d F Y'),
            '/{DAYS}/' => $days,
            '/{SESSIONS}/' => $sessions,
            '/{EFFECTIFS}/' => 'Mini : '.$product[0]['min_o'].' - Maxi : '.$product[0]['max_o'],
            '/{INTERVENANT}/' => (!empty($product[0]['intervenant']))?$product[0]['intervenant']:'Formateur consultant sélectionné par la CCI pour son expertise dans ce domaine',
            '/{PEDAGOGIE}/' => $product[0]['pedagogie'],
            '/{CPF}/' => (!empty($product[0]['cpf']))?'<h2 style="padding-left: 30px;">'.JText::_('CODE').'</h2><p style="padding-left: 30px;">'.$product[0]['cpf'].' </p>':'',
            '/{EVALUATION}/' => $product[0]['evaluation']
        ];

        $export_date = strftime('%e')." ".strftime('%B')." ".date('Y');

        $body = html_entity_decode(preg_replace('~<(\w+)[^>]*>(?>[\p{Z}\p{C}]|<br\b[^>]*>|&(?:(?:nb|thin|zwnb|e[nm])sp|zwnj|#xfeff|#xa0|#160|#65279);)*</\1>~iu', '', preg_replace(array_keys($post), $post, preg_replace("/<br[^>]+\>/i", "<br>", $article))));
        $footer = '<hr><p>La CCI Rochefort et Saintonge (CCIRS) se réserve le droit d’adapter les informations de cette fiche.</br>
					La CCIRS est un organisme de formation enregistré sous le numéro 5417 P00 1017, certifié « Facilitateur en Acquisition de Compétences » sous la référence CPS FAC 041</p>
					<p>{PRODUCT_MANAGER} - competencesetformation@rochefort.cci.fr - 05 46 84 70 92 - www.competencesetformation.fr</p> <p>Fiche pédagogique éditée le '.$export_date.' - ';
        $footer = html_entity_decode(preg_replace('~<(\w+)[^>]*>(?>[\p{Z}\p{C}]|<br\b[^>]*>|&(?:(?:nb|thin|zwnb|e[nm])sp|zwnj|#xfeff|#xa0|#160|#65279);)*</\1>~iu', '', preg_replace(array_keys($post), $post, preg_replace("/<br[^>]+\>/i", "<br>", $footer))));

        require_once (JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php');
        $filename = generatePDFfromHTML($body, $filename, $footer);

        if ($filename == false) {
            echo json_encode((object)['status' => false, 'msg' => 'Error generating PDF.']);
            exit;
        } else {
            echo json_encode((object)['status' => true, 'filename' => $filename.'?'.uniqid()]);
            exit;
        }

    }


    public function getValueByFabrikElts($fabrikElts, $fnumsArray) {
        $m_files = $this->getModel('Files');

        $fabrikValues = null;
        foreach ($fabrikElts as $elt) {

            $params = json_decode($elt['params']);
            $groupParams = json_decode($elt['group_params']);
            $isDate = ($elt['plugin'] == 'date');
            $isDatabaseJoin = ($elt['plugin'] === 'databasejoin');

            if (@$groupParams->repeat_group_button == 1 || $isDatabaseJoin) {
                $fabrikValues[$elt['id']] = $m_files->getFabrikValueRepeat($elt, $fnumsArray, $params, $groupParams->repeat_group_button == 1);
            } else {
                if ($isDate) {
                    $fabrikValues[$elt['id']] = $m_files->getFabrikValue($fnumsArray, $elt['db_table_name'], $elt['name'], $params->date_form_format);
                } else {
                    $fabrikValues[$elt['id']] = $m_files->getFabrikValue($fnumsArray, $elt['db_table_name'], $elt['name']);
                }
            }

            if ($elt['plugin'] == "checkbox" || $elt['plugin'] == "dropdown") {

                foreach ($fabrikValues[$elt['id']] as $fnum => $val) {

                    if ($elt['plugin'] == "checkbox") {
                        $val = json_decode($val['val']);
                    } else {
                        $val = explode(',', $val['val']);
                    }

                    if (count($val) > 0) {
                        foreach ($val as $k => $v) {
                            $index = array_search(trim($v), $params->sub_options->sub_values);
                            $val[$k] = $params->sub_options->sub_labels[$index];
                        }
                        $fabrikValues[$elt['id']][$fnum]['val'] = implode(", ", $val);
                    } else {
                        $fabrikValues[$elt['id']][$fnum]['val'] = "";
                    }

                }

            } elseif ($elt['plugin'] == "birthday") {

                foreach ($fabrikValues[$elt['id']] as $fnum => $val) {
                    $val = explode(',', $val['val']);
                    foreach ($val as $k => $v) {
                        $val[$k] = date($params->details_date_format, strtotime($v));
                    }
                    $fabrikValues[$elt['id']][$fnum]['val'] = implode(",", $val);
                }

            } else {
                if (@$groupParams->repeat_group_button == 1 || $isDatabaseJoin) {
                    $fabrikValues[$elt['id']] = $m_files->getFabrikValueRepeat($elt, $fnumsArray, $params, $groupParams->repeat_group_button == 1);
                } else {
                    $fabrikValues[$elt['id']] = $m_files->getFabrikValue($fnumsArray, $elt['db_table_name'], $elt['name']);
                }
            }
        }
        return $fabrikValues;
    }

    public function exportfile() {

        $jinput = JFactory::getApplication()->input;
        $fnums = $jinput->post->getString('fnums');
        $type = $jinput->post->getString('type');

        if (empty($fnums)) {
	        echo json_encode((object)(array('status' => false, 'msg' => JText::_('FILES_EXPORTED_TO_EXTERNAL_ERROR'))));
	        exit;
        }

        $fnums = (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

        JPluginHelper::importPlugin('emundus');
        $dispatcher = JEventDispatcher::getInstance();

        $status = $dispatcher->trigger('onExportFiles', array($fnums, $type));
        if (is_array($status) && !in_array(false, $status)) {
            $msg = JText::_('FILES_EXPORTED_TO_EXTERNAL');
            $result = true;
        } else {
            $msg = JText::_('FILES_EXPORTED_TO_EXTERNAL_ERROR');
            $result = false;
        }

        echo json_encode((object)(array('status' => $result, 'msg' => $msg)));
        exit;
    }
}





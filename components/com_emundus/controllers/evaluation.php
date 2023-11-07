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

jimport('joomla.application.component.controller');

use Joomla\CMS\Factory;

/**
 * eMundus Component Controller
 *
 * @package    Joomla.emundus
 * @subpackage Components
 */
class EmundusControllerEvaluation extends JControllerLegacy
{
	protected $app;

    private $_user;
	private $_db;
	private $_session;

    public function __construct($config = array())
    {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'files.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'filters.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'list.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'emails.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'export.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');

		$this->app = Factory::getApplication();
		$this->_db = Factory::getDbo();
		$this->_user = $this->app->getIdentity();
		$this->_session = $this->app->getSession();

        parent::__construct($config);
    }

    public function display($cachable = false, $urlparams = false)
    {
        // Set a default view if none exists
        if (!$this->input->get( 'view' )){
            $default = 'files';
            $this->input->set('view', $default );
        }
        parent::display();
    }


    public function applicantEmail()
    {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'emails.php');
        EmundusHelperEmails::sendApplicantEmail();
    }

    public function clear()
    {
        EmundusHelperFiles::clear();
        echo json_encode((object)(array('status' => true)));
        exit;
    }

    public function setfilters() {

        $filterName = $this->input->getString('id', null);
        $elements = $this->input->getString('elements', null);
        $multi = $this->input->getString('multi', null);

        EmundusHelperFiles::clearfilter();

        if ($multi == "true") {
            $filterval = $this->input->get('val', array(), 'ARRAY');
        } else {
            $filterval = $this->input->getString('val', null);
        }

        $params = $this->_session->get('filt_params');

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

        $this->_session->set('filt_params', $params);
        $this->_session->set('limitstart', 0);
        echo json_encode((object)(array('status' => true)));
        exit();
    }

    public function loadfilters()
    {
        try
        {

            $id = $this->input->getInt('id', null);
            $filter = EmundusHelperFiles::getEmundusFilters($id);
            $params = (array) json_decode($filter->constraints);
            $params['select_filter'] = $id;
            $params =  json_decode($filter->constraints, true);

            $this->_session->set('select_filter', $id);
            if(isset($params['filter_order']))
            {
                $this->_session->set('filter_order', $params['filter_order']);
                $this->_session->set('filter_order_Dir', $params['filter_order_Dir']);
            }
            $this->_session->set('filt_params', $params['filter']);
            if(!empty($params['col']))
                $this->_session->set('adv_cols', $params['col']);

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

        $order = $this->input->getString('filter_order', null);
        $ancientOrder = $this->_session->get('filter_order');
        $params = $this->_session->get('filt_params');
        $this->_session->set('filter_order', $order);
        $params['filter_order'] = $order;

        if($order == $ancientOrder)
        {
            if($this->_session->get('filter_order_Dir') == 'desc')
            {
                $this->_session->set('filter_order_Dir', 'asc');
                $params['filter_order_Dir'] = 'asc';
            }
            else
            {
                $this->_session->set('filter_order_Dir', 'desc');
                $params['filter_order_Dir'] = 'desc';
            }
        }
        else
        {
            $this->_session->set('filter_order_Dir', 'asc');
            $params['filter_order_Dir'] = 'asc';
        }
        $this->_session->set('filt_params', $params);
        echo json_encode((object)(array('status' => true)));
        exit;
    }

    public function setlimit()
    {

        $limit = $this->input->getInt('limit', null);

        $this->_session->set('limit', $limit);
        $this->_session->set('limitstart', 0);

        echo json_encode((object)(array('status' => true)));
        exit;
    }

    public function savefilters()
    {
        $name = $this->input->get('name', null, 'POST', 'none',0);
        $itemid = $this->input->get('Itemid', null, 'GET', 'none',0);

        $filt_params = $this->_session->get('filt_params');
        $adv_params = $this->_session->get('adv_cols');
        $constraints = array('filter'=>$filt_params, 'col'=>$adv_params);

        $constraints = json_encode($constraints);

        if(empty($itemid))
        {
            $itemid = $this->input->get('Itemid', null, 'POST', 'none',0);
        }

        $time_date = (date('Y-m-d H:i:s'));

        $query = "INSERT INTO #__emundus_filters (time_date,user,name,constraints,item_id) values('".$time_date."',".$this->_user->id.",'".$name."',".$this->_db->quote($constraints).",".$itemid.")";
        $this->_db->setQuery( $query );

        try
        {
            $this->_db->Query();
            $query = 'select f.id, f.name from #__emundus_filters as f where f.time_date = "'.$time_date.'" and user = '.$this->_user->id.' and name="'.$name.'" and item_id="'.$itemid.'"';
            $this->_db->setQuery($query);
            $result = $this->_db->loadObject();
            echo json_encode((object)(array('status' => true, 'filter' => $result)));
            exit;

        }
        catch (Exception $e)
        {
            echo json_encode((object)(array('status' => false)));
            exit;
        }
    }

    public function deletefilters()
    {

        $filter_id = $this->input->getInt('id', null);

        $query="DELETE FROM #__emundus_filters WHERE id=".$filter_id;
        $this->_db->setQuery( $query );
        $result=$this->_db->Query();

        if($result!=1)
        {
            echo json_encode((object)(array('status' => false)));
            exit;
        }
        else
        {
            echo json_encode((object)(array('status' => true)));
            exit;
        }
    }

    public function setlimitstart()
    {

        $limistart = $this->input->getInt('limitstart', null);
        $limit = intval($this->_session->get('limit'));
        $limitstart = ($limit != 0 ? ($limistart > 1 ? (($limistart - 1) * $limit) : 0) : 0);
        $this->_session->set('limitstart', $limitstart);

        echo json_encode((object)(array('status' => true)));
        exit;
    }

    public function getadvfilters()
    {
        try
        {
            $elements = EmundusHelperFiles::getElements();

            echo json_encode((object)(array('status' => true, 'default' => JText::_('COM_EMUNDUS_PLEASE_SELECT'), 'defaulttrash' => JText::_('REMOVE_SEARCH_ELEMENT'), 'options' => $elements)));
            exit;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function addcomment()
    {
		$fnums = $this->input->getString('fnums', null);
        $title = $this->input->getString('title', '');
        $comment = $this->input->getString('comment', null);
        $fnums = ($fnums=='all')?'all':(array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus' . '/models/application.php');
        $appModel = $this->getModel('Application');


        if (is_array($fnums)) {
            foreach($fnums as $fnum) {
                if(EmundusHelperAccess::asAccessAction(10, 'c', $this->_user->id, $fnum)) {
                    $aid = intval(substr($fnum, 21, 7));
                    $res = $appModel->addComment((array('applicant_id' => $aid, 'user_id' => $this->_user->id, 'reason' => $title, 'comment_body' => $comment, 'fnum' => $fnum)));
                    if($res !== true && !is_numeric($res))
                    {
                        echo json_encode((array('status' => false, 'msg' => JText::_('ERROR'))));
                        exit;
                    }
                }
            }

            echo json_encode((array('status' => true, 'msg' => JText::_('COM_EMUNDUS_COMMENTS_SUCCESS'))));
            exit;

        } elseif($fnums == 'all') {
            //all result find by the request
            $m_files = $this->getmodel('Files');
            $fnums = $m_files->getAllFnums();
            foreach($fnums as $fnum) {
                if(EmundusHelperAccess::asAccessAction(10, 'c', $this->_user->id, $fnum)) {
                    $aid = intval(substr($fnum, 14, count($fnum)));
                    $appModel->addComment((array('applicant_id' => $aid, 'user_id' => $this->_user->id, 'reason' => $title, 'comment_body' => $comment, 'fnum' => $fnum)));
                }
            }
        }
    }

    public function getevsandgroups()
    {
	    $response = ['status' => false, 'code' => 403, 'msg' => JText::_('ACCESS_DENIED')];

		if (EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
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

	    if (EmundusHelperAccess::asAccessAction(14, 'c', $this->_user->id)) {
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

		$fnums  = $this->input->getString('fnums', null);
		$tag    = $this->input->get('tag', null);

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
         $fnums  = $this->input->getString('fnums', null);
         $tags    = $this->input->getVar('tag', null);

         $fnums = ($fnums=='all') ? 'all' : (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

		 if (!empty($fnums)) {
			 $m_files = $this->getModel('Files');

			 if ($fnums == "all") {
				 $fnums = $m_files->getAllFnums();
			 }

			 require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . '/models/application.php');
			 $m_application = $this->getModel('Application');

			 foreach ($fnums as $fnum) {
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
		 }
         unset($fnums);
         unset($tags);

         echo json_encode((object)(array('status' => true, 'msg' => JText::_('COM_EMUNDUS_TAGS_DELETE_SUCCESS'))));
         exit;
     }

    public function share()
    {

        $fnums      = $this->input->getString('fnums', null);
        $actions    = $this->input->getString('actions', null);
        $groups     = $this->input->getString('groups', null);
        $evals      = $this->input->getString('evals', null);

        $actions    = (array) json_decode(stripslashes($actions));
        $fnums = ($fnums=='all')?'all':(array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

        $m_files = $this->getModel('Files');

        if (is_array($fnums)) {

            $validFnums = array();
            foreach ($fnums as $fnum) {
                if (EmundusHelperAccess::asAccessAction(11, 'c', $this->_user->id, $fnum))
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

                if (EmundusHelperAccess::asAccessAction(11, 'c', $this->_user->id, $fnum))
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
        echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
        exit;
    }

    public function getstate()
    {
        $m_files = $this->getModel('Files');


        $states = $m_files->getAllStatus();

        echo json_encode((object)(array('status' => true,
                                        'states' => $states,
                                        'state' => JText::_('COM_EMUNDUS_STATE'),
                                        'select_state' => JText::_('PLEASE_SELECT_STATE'))));
        exit;
    }

    public function updatestate() {
	    $fnums  = $this->input->getString('fnums', null);
	    $state  = $this->input->getInt('state', null);

	    $email_from_sys = $app->getCfg('mailfrom');
	    $fnums = ($fnums=='all')?'all':(array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

	    $m_files = $this->getModel('Files');
	    $validFnums = array();

	    if ($fnums == "all") {
		    $fnums = $m_files->getAllFnums();
		}

	    foreach ($fnums as $fnum) {
		    if (EmundusHelperAccess::asAccessAction(13, 'u', $this->_user->id, $fnum))
			    $validFnums[] = $fnum;
	    }

	    $fnumsInfos = $m_files->getFnumsInfos($validFnums);
	    $res        = $m_files->updateState($validFnums, $state);
	    $msg = '';

	    if ($res !== false) {
		    require_once (JPATH_BASE.DS.'components'.DS.'com_emundus' . '/models/application.php');
		    $m_application = $this->getModel('Application');
		    $status = $m_files->getStatus();
		    // Get all codes from fnum
		    $code = array();
		    foreach ($fnumsInfos as $fnum) {
			    $code[] = $fnum['training'];
			    $row = [
			    	'applicant_id' => $fnum['applicant_id'],
	                 'user_id' => $this->_user->id,
	                 'reason' => JText::_('COM_EMUNDUS_STATUS'),
	                 'comment_body' => $fnum['value'].' ('.$fnum['step'].') '.JText::_('COM_EMUNDUS_TO').' '.$status[$state]['value'].' ('.$state.')',
	                 'fnum' => $fnum['fnum']
			    ];
			    $m_application->addComment($row);
		    }
		    //*********************************************************************
		    // Get triggered email
            include_once(JPATH_SITE.'/components/com_emundus/models/emails.php');
		    $m_email = $this->getModel('Emails');
		    $trigger_emails = $m_email->getEmailTrigger($state, $code, 1);

		    if (count($trigger_emails) > 0) {
                include_once(JPATH_SITE.'/components/com_emundus/helpers/emails.php');
                $h_email = new EmundusHelperEmails;

			    foreach ($trigger_emails as $trigger_email_id => $trigger_email) {

				    // Manage with default recipient by programme
				    foreach ($trigger_email as $code => $trigger) {

					    if ($trigger['to']['to_applicant'] == 1) {

						    // Manage with selected fnum
						    foreach ($fnumsInfos as $file) {
                                $can_send_mail = $h_email->assertCanSendMailToUser($file['applicant_id'], $file['fnum']);
                                if (!$can_send_mail) {
                                   continue;
                                }
                                
                                $mailer = JFactory::getMailer();
							    $post = array('FNUM' => $file['fnum']);
							    $tags = $m_email->setTags($file['applicant_id'], $post, $file['fnum'], '', $trigger['tmpl']['emailfrom'].$trigger['tmpl']['name'].$trigger['tmpl']['subject'].$trigger['tmpl']['message']);

							    $from       = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['emailfrom']);
							    $from_id    = 62;
							    $fromname   = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['name']);
							    $to         = $file['email'];
							    $subject    = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['subject']);
							    $body       = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['message']);
							    $body       = $m_email->setTagsFabrik($body, array($file['fnum']));

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

							    $send = $mailer->Send();
							    if ($send !== true) {
								    $msg .= '<div class="alert alert-dismissable alert-danger">'.JText::_('COM_EMUNDUS_MAILS_EMAIL_NOT_SENT').' : '.$to.' '.$send->__toString().'</div>';
								    JLog::add($send->__toString(), JLog::ERROR, 'com_emundus.email');
							    } else {
								    $message = array(
									    'user_id_from' => $from_id,
									    'user_id_to' => $file['applicant_id'],
									    'subject' => $subject,
									    'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('COM_EMUNDUS_APPLICATION_SENT').' '.JText::_('COM_EMUNDUS_TO').' '.$to.'</i><br>'.$body,
									    'email_id' => $trigger_email_id
								    );
								    $m_email->logEmail($message, $file['fnum']);
								    $msg .= JText::_('COM_EMUNDUS_MAILS_EMAIL_SENT').' : '.$to.'<br>';
								    JLog::add($to.' '.$body, JLog::INFO, 'com_emundus.email');
							    }
						    }
					    }

					    foreach ($trigger['to']['recipients'] as $key => $recipient) {
                            $can_send_mail = $h_email->assertCanSendMailToUser($recipient['id']);
                            if (!$can_send_mail) {
                                continue;
                            }

						    $mailer = JFactory::getMailer();

						    $tags = $m_email->setTags($recipient['id'], array(), null, '', $trigger['tmpl']['emailfrom'].$trigger['tmpl']['name'].$trigger['tmpl']['subject'].$trigger['tmpl']['message']);

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

						    $send = $mailer->Send();
						    if ($send !== true) {
							    $msg .= '<div class="alert alert-dismissable alert-danger">'.JText::_('COM_EMUNDUS_MAILS_EMAIL_NOT_SENT').' : '.$to.' '.$send->__toString().'</div>';
							    JLog::add($send->__toString(), JLog::ERROR, 'com_emundus.email');
							    //die();
						    } else {
							    $message = [
								    'user_id_from' => $from_id,
								    'user_id_to' => $recipient['id'],
								    'subject' => $subject,
								    'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('COM_EMUNDUS_APPLICATION_SENT').' '.JText::_('COM_EMUNDUS_TO').' '.$to.'</i><br>'.$body,
								    'email_id' => $trigger_email_id
							    ];
							    $m_email->logEmail($message, $fnum['fnum']);
							    $msg .= JText::_('COM_EMUNDUS_MAILS_EMAIL_SENT').' : '.$to.'<br>';
							    JLog::add($to.' '.$body, JLog::INFO, 'com_emundus.email');
						    }
					    }
				    }
			    }
		    }
		    //
		    //***************************************************

		    $msg .= JText::_('COM_EMUNDUS_APPLICATION_STATE_SUCCESS');
	    } else $msg .= JText::_('STATE_ERROR');

	    echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
	    exit;
    }

    public function unlinkevaluators() {

        $fnum   = $this->input->getString('fnum', null);
        $id     = $this->input->getint('id', null);
        $group  = $this->input->getString('group', null);

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
        $res = false;
        $fnumInfos = null;

        if ($fnum != null)
        {
            $m_files = $this->getModel('Files');
            $fnumInfos = $m_files->getFnumInfos($fnum);
            if($fnum !== false)
                $res = true;
        }
        $this->_session->set('application_fnum', $fnum);
        echo json_encode((object)(array('status' => $res, 'fnumInfos' => $fnumInfos)));
        exit;
    }

    public function deletefile()
    {

        $fnum   = $this->input->getString('fnum', null);

        $m_files = $this->getModel('Files');

        if (EmundusHelperAccess::asAccessAction(1, 'd', $this->_user->id, $fnum))
            $res = $m_files->changePublished($fnum);
        else
            $res = false;

        $result = array('status' => $res);

        echo json_encode((object)$result);
        exit;
    }

    public function getformelem() {
        //Filters

        $code   = $this->input->getVar('code', null);
        $code = explode(',', $code);


        $m_evaluation = $this->getModel('Evaluation');
        $defaultElements = $m_evaluation->getEvaluationElementsName(0, 1, $code);
        if (!empty($defaultElements)) {
            foreach ($defaultElements as $kde => $de) {
                if ($de->element_name == 'id' || $de->element_name == 'fnum' || $de->element_name == 'student_id') {
	                unset($defaultElements[$kde]);
                }
            }
        }
        $elements = EmundusHelperFiles::getElements();
        $res = array('status' => true, 'elts' => $elements, 'defaults' => $defaultElements);
        echo json_encode((object)$res);
        exit;
    }

    // Function called by ajax on views->application->tmpl->evaluation
    // Gets the elements and their values of an application by fnum and evaluator
    public function getevalcopy() {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'files.php');

        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asPartnerAccessLevel($user->id))
            die (JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));

        $h_files = new EmundusHelperFiles;
        $m_files = $this->getModel('files');


        $fnum       = $this->input->getVar('fnum', null);
        $evaluator  = $this->input->getVar('evaluator', null);

        if (isset($fnum, $evaluator) && !empty($fnum) && !empty($evaluator)) {

            $evaluation = $h_files->getEvaluation('html',$fnum);
            $evaluation = $evaluation[$fnum][$evaluator];

            $form = $m_files->getEvalByFnumAndEvaluator($fnum, $evaluator);
            $formID = $form[0]['id'];

            $result = ['status' => true, 'evaluation' => $evaluation, 'formID' => $formID];

        } else $result = ['status' => false];

        echo json_encode((object) $result);
        exit();
    }

    // Function called by an Ajax script, copies a row in the evaluations table
    public function copyeval() {


	    $user = JFactory::getUser();

        if (!EmundusHelperAccess::asPartnerAccessLevel($user->id))
            die (JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));


        $fromID = $this->input->getVar('from', null);
        $toID   = $this->input->getVar('to', null);
        $fnum   = $this->input->getVar('fnum', null);
        $studID = $this->input->getVar('student', null);

        if (isset($fromID, $fnum) && !empty($fromID) && !empty($fnum)) {

            $m_evaluation = $this->getModel('Evaluation');
            $res = $m_evaluation->copyEvaluation($fromID, $toID, $fnum, $studID, $user->id);

            $result = ['status' => $res];

        } else $result = ['status' => false ];

        echo json_encode((object) $result);
        exit();
    }

    function pdf(){
	    require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
	    require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');


        $fnum = $this->input->getString('fnum', null);
        $student_id = $this->input->getInt('student_id', $this->input->getInt('user', $this->_user->id));

        if (!EmundusHelperAccess::asAccessAction(8, 'c', $this->_user->id, $fnum) )
            die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));

        $m_profile = $this->getModel('Profile');
        $m_campaign = $this->getModel('Campaign');

        if (!empty($fnum)) {
            $candidature = $m_profile->getFnumDetails($fnum);
            $campaign = $m_campaign->getCampaignByID($candidature['campaign_id']);
            $name = 'evaluation-'.$fnum.'.pdf';
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
        pdf_evaluation($student_id, $fnum, true, $name);

        exit();
    }

    function delevaluation(){



        $fnum = $this->input->getString('fnum', null);
        $ids = $this->input->getString('ids', null);
        $ids = json_decode(stripslashes($ids));
        $res = new stdClass();

        $m_evaluation = $this->getModel('Evaluation');
        foreach($ids as $id)
        {
            $eval =   $m_evaluation->getEvaluationById($id);
            if(EmundusHelperAccess::asAccessAction(5 ,'d', JFactory::getUser()->id, $fnum)){
                $m_evaluation->delevaluation($id);

                # get logged user id    JFactory::getUser()->id
                # get fnum              $fnum
                # get applicant id      $applicant_id

                // TRACK THE LOGS
                require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
                $user = JFactory::getUser();    # logged user

                require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
                $mFile = $this->getModel('Files');
                $applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

                EmundusModelLogs::log($user->id, $applicant_id, $fnum, 5, 'd', 'COM_EMUNDUS_ACCESS_EVALUATION_DELETE');

                $res->status = true;
            }else{
                $eval =   $m_evaluation->getEvaluationById($id);
                if($eval->user == JFactory::getUser()->id){
                    $m_evaluation->delevaluation($id);

                    # get logged user id    JFactory::getUser()->id
                    # get fnum              $fnum
                    # get applicant id      $applicant_id

                    // TRACK THE LOGS
                    require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
                    $user = JFactory::getUser();    # logged user

                    require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
                    $mFile = $this->getModel('Files');
                    $applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

                    EmundusModelLogs::log(JFactory::getUser()->id, $applicant_id, $fnum, 5, 'd', 'COM_EMUNDUS_ACCESS_EVALUATION_DELETE');

                    $res->status = true;
                }else{
                    $res->status = false;
                    $res->msg = JText::_("ACCESS_DENIED");
                }
            }


        }
        echo json_encode($res);
        exit();

    }

    function pdf_decision(){

        $fnum = $this->input->getString('fnum', null);
        $student_id = $this->input->getString('student_id', null);

        if( !EmundusHelperAccess::asAccessAction(8, 'c', $this->_user->id, $fnum) )
            die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));

		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
        $m_profile = $this->getModel('Profile');
        $m_campaign = $this->getModel('Campaign');

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

        $fnums = $this->input->get('fnums', null);
        $fnums = ($fnums=='all')?'all':(array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);
        $m_files = $this->getModel('Files');

		if($fnums == "all"){
            $fnums = $m_files->getAllFnums();
        }

		$validFnums = array();

		foreach($fnums as $fnum)
        {
            if(EmundusHelperAccess::asAccessAction(1, 'r', $this->_user->id, $fnum)&& $fnum != 'em-check-all-all' && $fnum != 'em-check-all')
            {
                $validFnums[] = $fnum;
            }
        }
        $totalfile = sizeof($validFnums);

        $this->_session->set('fnums_export', $validFnums);
        $result = array('status' => true, 'totalfile'=> $totalfile);
        echo json_encode((object) $result);
        exit();
    }

    public function getcolumn($elts) {
        return(array) json_decode(stripcslashes($elts));
    }

    public function generate_array() {

        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            die( JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS') );
        }

        $m_files = $this->getModel('Files');
	    require_once (JPATH_BASE.DS.'components'.DS.'com_emundus' . '/models/application.php');
	    $m_application = $this->getModel('Application');


        $fnums = $this->_session->get('fnums_export');



        $file = $this->input->getVar('file', null, 'STRING');
        $totalfile = $this->input->getVar('totalfile', null);
        $start = $this->input->getInt('start', 0);
        $limit = $this->input->getInt('limit', 0);
        $nbcol = $this->input->getVar('nbcol', 0);
        $elts = $this->input->getString('elts', null);
        $objs = $this->input->getString('objs', null);

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
        $fnumsArray = $m_files->getFnumArray($fnums, $ordered_elements, 0, $start, $limit, 0);

        // On met a jour la liste des fnums traités
        $fnums = array();
        foreach ($fnumsArray as $fnum) {
            $fnums[] = $fnum['fnum'];
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
        if ($start==0) {
            $line=JText::_('COM_EMUNDUS_FILE_F_NUM')."\t".JText::_('COM_EMUNDUS_STATUS')."\t".JText::_('COM_EMUNDUS_FORM_LAST_NAME')."\t".JText::_('COM_EMUNDUS_FORM_FIRST_NAME')."\t".JText::_('COM_EMUNDUS_EMAIL')."\t".JText::_('COM_EMUNDUS_CAMPAIGN')."\t";
            $nbcol = 6;
            foreach ($ordered_elements as $fKey => $fLine) {
                //if ($fLine->element_name != 'fnum' && $fLine->element_name != 'code' && $fLine->element_name != 'campaign_id') {
                    $line .= strip_tags($fLine->element_label) . "\t";
                    $nbcol++;
                //}
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
            foreach($fnum as $k => $v)
            {
                if ($k != 'code' && $k != 'campaign_id' && $k != 'jos_emundus_campaign_candidature___campaign_id' && $k != 'c___campaign_id') {
                    if($k === 'fnum')
                    {
                        $line .= $v."\t";
                        $line .= $status[$v]['value']."\t";
                        $uid = intval(substr($v, 21, 7));
                        $userProfil = JUserHelper::getProfile($uid)->emundus_profile;
                        $lastname = (!empty($userProfil['lastname']))?$userProfil['lastname']:JFactory::getUser($uid)->name;
                        $line .= strtoupper($lastname)."\t";
                        $line .= $userProfil['firstname']."\t";
                    }
                    else
                    {
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
        foreach ($element_csv as $data)
        {
            $res = fputcsv($csv, explode("\t",$data),"\t");
            if( !$res) {
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
        //var_dump($result);
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
    function export_zip($fnums)
    {
        $view           = $this->input->get( 'view' );
        if ((!@EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) &&
            $view != 'renew_application'
        )
            die( JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS') );

        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
        require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php');

        $zip = new ZipArchive();

        $nom = date("Y-m-d").'_'.rand(1000,9999).'_x'.(count($fnums)-1).'.zip';
        $path = JPATH_SITE.DS.'tmp'.DS.$nom;
        $m_files = $this->getModel('Files');
        $files = $m_files->getFilesByFnums($fnums);

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

    // controller of get all letters
    public function getattachmentletters() {

        $fnums = $this->input->getRaw('fnums', null);

        $m_evaluation = $this->getModel('Evaluation');
        $attachment_letters = $m_evaluation->getLettersByFnums($fnums, true);

        if (!empty($attachment_letters)) {
            $result = array('status' => true, 'attachment_letters' => $attachment_letters);
        } else {
            $result = array('status' => false, 'attachment_letters' => null);
        }
        echo json_encode((object) $result);
        exit;
    }

    public function getmyevaluations() {

        $campaign = $this->input->getInt('campaign');
        $module = $this->input->getInt('module');

		$m_evaluation = $this->getModel('Evaluation');
        $files_to_evaluate = $m_evaluation->getMyEvaluations($this->_user->id,$campaign,$module);

        if (!empty($files_to_evaluate)) {
            $result = array('status' => true, 'files' => $files_to_evaluate);
        } else {
            $result = array('status' => false, 'files' => []);
        }
        echo json_encode((object) $result);
        exit;
    }

    public function getcampaignstoevaluate() {

        $module = $this->input->getInt('module');

		$m_evaluation = $this->getModel('Evaluation');
        $campaigns = $m_evaluation->getCampaignsToEvaluate($this->_user->id,$module);

        if (!empty($campaigns)) {
            $result = array('status' => true, 'campaigns' => $campaigns);
        } else {
            $result = array('status' => false, 'campaigns' => []);
        }
        echo json_encode((object) $result);
        exit;
    }
}

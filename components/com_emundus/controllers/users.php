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

/**
 * users Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      2.0.0
 */
class EmundusControllerUsers extends JControllerLegacy {
	private $_user = null;
	private $_db = null;
	private $_userModel = null;

	public function __construct($config = array()){
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'users.php');

		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');

		$this->_user = JFactory::getSession()->get('emundusUser');
		$this->_db = JFactory::getDBO();
		$this->_userModel = new EmundusModelUsers();

		parent::__construct($config);
	}

	public function display($cachable = false, $urlparams = false)  {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'users';
			JRequest::setVar('view', $default );
		}
		if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id))
			parent::display();
		else echo JText::_('ACCESS_DENIED');
    }
	public function adduser() {

		// add to jos_emundus_users; jos_users; jos_emundus_groups; jos_users_profiles; jos_users_profiles_history
		$current_user = JFactory::getUser();
		//$db = JFactory::getDBO();

		if (!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id) && !EmundusHelperAccess::isPartner($current_user->id)) {
			echo json_encode((object)array('status' => false, 'uid' => $uid, 'msg' => JText::_('ACCESS_DENIED')));
			exit;
		}

		$itemid 		= JRequest::getVar('Itemid', null, 'POST', 'none',0);
		$firstname 		= JRequest::getVar('firstname', null, 'POST', 'none',0);
		$lastname 		= JRequest::getVar('lastname', null, 'POST', 'none',0);
		$username 		= JRequest::getVar('login', null, 'POST', 'none',0);
		$name 			= strtolower($firstname).' '.strtoupper($lastname);
		$email 			= JRequest::getVar('email', null, 'POST', 'none',0);
		$profile 		= JRequest::getVar('profile', null, 'POST', 'none',0);
		$oprofiles 		= JRequest::getVar('oprofiles', null, 'POST', 'string',0);
		$jgr 			= JRequest::getVar('jgr', null, 'POST', 'none',0);
		$univ_id 		= JRequest::getVar('university_id', null, 'POST', 'none',0);
		$groups 		= JRequest::getVar('groups', null, 'POST', 'string',0);
		$campaigns 		= JRequest::getVar('campaigns', null, 'POST', 'string',0);
		$news			= JRequest::getVar('newsletter', null, 'POST', 'string',0);

		$password 	= JUserHelper::genRandomPassword();
		$user 		= clone(JFactory::getUser(0));

		if (preg_match('/^[0-9a-zA-Z\_\@\-\.]+$/', $username) !== 1) {
			echo json_encode((object)array('status' => false, 'msg' => JText::_('USERNAME_NOT_GOOD')));
			exit;
		}
		if (preg_match('/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-z\-0-9]+\.)+[a-z]{2,}))$/', $email) !== 1) {
			echo json_encode((object)array('status' => false, 'msg' => JText::_('MAIL_NOT_GOOD')));
			exit;
		}

		$user->name=$name;
		$user->username=$username;
		$user->email=$email;
		$user->password=md5($password);
		$user->registerDate=date('Y-m-d H:i:s');
		$user->lastvisitDate=date('Y-m-d H:i:s');
		$user->groups = array($jgr);
		$user->block=0;

		$other_param['firstname'] 		= $firstname;
		$other_param['lastname'] 		= $lastname;
		$other_param['profile'] 		= $profile;
		$other_param['em_oprofiles'] 	= !empty($oprofiles) ? explode(',', $oprofiles): $oprofiles;
		$other_param['univ_id'] 		= $univ_id;
		$other_param['em_groups'] 		= !empty($groups) ? explode(',', $groups): $groups;
		$other_param['em_campaigns'] 	= !empty($campaigns) ? explode(',', $campaigns): $campaigns;
		$other_param['news'] 			= $news;

		$m_users = new EmundusModelUsers();
		$acl_aro_groups = $m_users->getDefaultGroup($profile);
		$user->groups = $acl_aro_groups;

		$usertype = $m_users->found_usertype($acl_aro_groups[0]);
		$user->usertype = $usertype;

		$uid = $m_users->adduser($user, $other_param);

		if (is_array($uid)) {
			$uid['status'] = false;
			echo json_encode((object) $uid);
			exit;
		}
			//echo $uid;
		if (!mkdir(EMUNDUS_PATH_ABS.$uid, 0755) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$uid.DS.'index.html'))
			echo json_encode((object)array('status' => false, 'uid' => $uid, 'msg' => JText::_('CANT_CREATE_USER_FOLDER_CONTACT_ADMIN')));

		// Envoi de la confirmation de création de compte par email
		/*
         * @var EmundusModelEmails $m_emails
		 *  */
        $m_emails 	= $this->getModel('emails');
		$email 	= $m_emails->getEmail('new_account');
        $mailer = JFactory::getMailer();
        $post 	= array('PASSWORD' => $password);
        $tags 	= $m_emails->setTags($user->id, $post, null, $password);

        $from 		= preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
        $fromname 	= preg_replace($tags['patterns'], $tags['replacements'], $email->name);
        $to 		= $file['email'];
        $subject 	= preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
        $body 		= preg_replace($tags['patterns'], $tags['replacements'], $email->message);
        $body 		= $m_emails->setTagsFabrik($body);

        $app = JFactory::getApplication();
		$email_from_sys = $app->getCfg('mailfrom');

		// If the email sender has the same domain as the system sender address.
		if (!empty($email->emailfrom) && substr(strrchr($email->emailfrom, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1))
			$mail_from_address = $email->emailfrom;
 		else
			$mail_from_address = $email_from_sys;

        $sender = [
            $email_from_address,
            $fromname
		];

        $mailer->setSender($sender);
        $mailer->addReplyTo($email->emailfrom, $email->name);
        $mailer->addRecipient($user->email);
        $mailer->setSubject($email->subject);
        $mailer->isHTML(true);
        $mailer->Encoding = 'base64';
        $mailer->setBody($body);

        try {
			$send = $mailer->Send();

			if ($send === false){
				JLog::add('No email configuration!', JLog::ERROR, 'com_emundus.email');
			} else {
				$message = array(
					'user_id_from' => $current_user->id,
					'user_id_to' => $uid,
					'subject' => $email->subject,
					'message' => $body
				);
				$m_emails->logEmail($message);
			}

		} catch (Exception $e) {

			echo json_encode((object)array('status' => false, 'msg' => JText::_('EMAIL_NOT_SENT')));
			JLog::add($e->__toString(), JLog::ERROR, 'com_emundus.email');
			exit();

		}

		echo json_encode((object)array('status' => true, 'msg' => JText::_('USER_CREATED')));
		exit;
	}

	public function delincomplete() {
		if(!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}

		$query = 'SELECT u.id FROM #__users AS u LEFT JOIN #__emundus_declaration AS d ON u.id=d.user WHERE u.usertype = "Registered" AND d.user IS NULL';
		$this->_db->setQuery($query);
		$this->delusers($this->_db->loadResultArray());
	}

	public function delrefused() {
		if(!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}

		$this->_db->setQuery('SELECT student_id FROM #__emundus_final_grade WHERE Final_grade=2 AND type_grade ="candidature"');
		$users = $this->_db->loadResultArray();
		$this->delusers($this->_db->loadResultArray());
	}

	public function delnonevaluated() { /* ----------------- */
		if(!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}

		$this->_db->setQuery('SELECT u.id FROM #__users AS u LEFT JOIN #__emundus_final_grade AS efg ON u.id=efg.student_id WHERE u.usertype = "Registered" AND efg.student_id IS NULL');
		$users = $this->_db->loadResultArray();
		$this->delusers($this->_db->loadResultArray());
	}

	public function archive() {
		//$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$itemid=JSite::getMenu()->getActive()->id;

		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		if(!empty($ids)) {
			foreach ($ids as $id) {
				$query = 'UPDATE #__emundus_users SET profile=999 WHERE user_id='.$id;
				$this->_db->setQuery($query);
				$this->_db->Query() or die($this->_db->getErrorMsg());

				$this->blockuser($id);
			}
		}

		$this->setRedirect('index.php?option=com_emundus&view=users&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
	}

	public function lastSavedFilter(){

		$query="SELECT MAX(id) FROM #__emundus_filters";
		$this->_db->setQuery( $query );
		$result=$this->_db->loadResult();
		echo $result;
	}

	public function getConstraintsFilter(){
		$filter_id = JRequest::getVar('filter_id', null, 'POST', 'none',0);

		$query="SELECT constraints FROM #__emundus_filters WHERE id=".$filter_id;
		// echo $query;
		$this->_db->setQuery( $query );
		$result=$this->_db->loadResult();
		echo $result;
	}

	////// EXPORT SELECTED XLS ///////////////////
	public function export_selected_xls(){
	     $cids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		 $page= JRequest::getVar('limitstart',0,'get');
		 if(!empty($cids)){
		 	$this->export_to_xls($cids);
		} else {
			$this->setRedirect("index.php?option=com_emundus&view=users&limitstart=".$page,JText::_("NO_ITEM_SELECTED"),'error');
		}
	}

   ////// EXPORT ALL XLS ///////////////////
	public function export_account_to_xls($reqids=array(),$el=array()) {
		$cid = JRequest::getVar('ud', null, 'POST', 'array', 0);
		require_once(JPATH_BASE.DS.'libraries'.DS.'emundus'.DS.'export_xls'.DS.'xls_users.php');
		export_xls($cid, array());
	}

	public function export_zip() {
		require_once('libraries/emundus/zip.php');
		$db	= JFactory::getDBO();
		$cid = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		JArrayHelper::toInteger( $cid, 0 );

		if (count( $cid ) == 0) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			exit;
		}
		zip_file($cid);
		exit;
	}

	public function addsession(){
		global $option;
		$select_filter = JRequest::getVar('select_id', null, 'GET', 'none',0);
		$mainframe = JFactory::getApplication();
		$mainframe->setUserState( $option."select_filter", $select_filter );
	}


	/////////////Nouvelle Gestion /////////////////


	public function clear()
	{
		@EmundusHelperFiles::clear();
		echo json_encode((object)(array('status' => true)));
		exit;
	}


	public function setfilters() {
		try {
			$jinput = JFactory::getApplication()->input;
			$filterName = $jinput->getString('id', null);
			$elements = $jinput->getString('elements', null);
			$multi = $jinput->getString('multi', null);

			@EmundusHelperFiles::clearfilter();

			if ($multi == "true")
				$filterval = $jinput->get('val', array(), 'ARRAY');
			else
				$filterval = $jinput->getString('val', null);

			$session = JFactory::getSession();
			$params = $session->get('filt_params');

			if ($elements == 'false') {
				$params[$filterName] = $filterval;
			} else {
				$vals = (array)json_decode(stripslashes($filterval));

				if (isset($vals[0]->name)) {
					foreach ($vals as $val) {
						if ($val->adv_fil)
							$params['elements'][$val->name] = $val->value;
						else
							$params[$val->name] = $val->value;
					}
				} else $params['elements'][$filterName] = $filterval;
			}
			$session->set('filt_params', $params);

			$session->set('limitstart', 0);
			echo json_encode((object)(array('status' => true)));
			exit();
		} catch (Exception $e) {
			error_log($e->getMessage(), 0);
			error_log($e->getLine(), 0);
			error_log($e->getTraceAsString(), 0);
			throw new JDatabaseException;
		}
	}

	public function loadfilters()
	{
		try {

			$jinput = JFactory::getApplication()->input;
			$id = $jinput->getInt('id', null);
			$filter = @EmundusHelperFiles::getEmundusFilters($id);
			$params = (array) json_decode($filter->constraints);
			$params['select_filter'] = $id;
			$params =  json_decode($filter->constraints, true);

			JFactory::getSession()->set('select_filter', $id);
			if (isset($params['filter_order'])) {
				JFactory::getSession()->set('filter_order', $params['filter_order']);
				JFactory::getSession()->set('filter_order_Dir', $params['filter_order_Dir']);
			}
			JFactory::getSession()->set('filt_params', $params['filter']);

			echo json_encode((object)(array('status' => true)));
			exit();

		} catch(Exception $e) {
			throw new Exception;
		}
	}

	public function order() {
		$jinput = JFactory::getApplication()->input;
		$order 	= $jinput->getString('filter_order', null);

		$ancientOrder = JFactory::getSession()->get('filter_order');
		$params = JFactory::getSession()->get('filt_params');
		JFactory::getSession()->set('filter_order', $order);
		$params['filter_order'] = $order;

		if ($order == $ancientOrder) {
			if (JFactory::getSession()->get('filter_order_Dir') == 'desc') {
				JFactory::getSession()->set('filter_order_Dir', 'asc');
				$params['filter_order_Dir'] = 'asc';
			} else {
				JFactory::getSession()->set('filter_order_Dir', 'desc');
				$params['filter_order_Dir'] = 'desc';
			}
		} else {
			JFactory::getSession()->set('filter_order_Dir', 'asc');
			$params['filter_order_Dir'] = 'asc';
		}
		JFactory::getSession()->set('filt_params', $params);
		echo json_encode((object)(array('status' => true)));
		exit;
	}

	public function setlimit() {
		$jinput = JFactory::getApplication()->input;
		$limit = $jinput->getInt('limit', null);

		JFactory::getSession()->set('limit', $limit);
		JFactory::getSession()->set('limitstart', 0);

		echo json_encode((object)(array('status' => true)));
		exit;
	}

	public function savefilters() {
		$current_user = JFactory::getUser();
		$user_id = $current_user->id;

		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$name 	= JRequest::getVar('name', null, 'POST', 'none',0);

		$filt_params 	= JFactory::getSession()->get('filt_params');
		$adv_params 	= JFactory::getSession()->get('adv_cols');
		$constraints 	= array('filter'=>$filt_params, 'col'=>$adv_params);

		$constraints = json_encode($constraints);

		if(empty($itemid))
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

	public function deletefilters() {
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

	public function setlimitstart() {
		$jinput = JFactory::getApplication()->input;
		$limistart = $jinput->getInt('limitstart', null);
		$limit = intval(JFactory::getSession()->get('limit'));
		$limitstart = ($limit != 0 ? ($limistart > 1 ? (($limistart - 1) * $limit) : 0) : 0);
		JFactory::getSession()->set('limitstart', $limitstart);

		echo json_encode((object)(array('status' => true)));
		exit;
	}

	public function addgroup() {
		$jinput 	= JFactory::getApplication()->input;
		$gname 		= $jinput->getString('gname', null);
		$actions 	= $jinput->getString('actions', null);
		$progs 		= $jinput->getString('gprog', null);
		$gdesc 		= $jinput->getString('gdesc', null);
		$actions 	= (array) json_decode(stripslashes($actions));
		$m_users 	= new EmundusModelUsers();
		$res 		= $m_users->addGroup($gname, $gdesc, $actions, explode(',', $progs));

		if ($res !== false)
			$msg = JText::_('GROUP_ADDED');
		else
			$msg = JText::_('AN_ERROR_OCCURED');

		echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
		exit;
	}

	public function changeblock() {
		$user = JFactory::getUser();
		//temid=JSite::getMenu()->getActive()->id;
		if (!EmundusHelperAccess::asAdministratorAccessLevel($user->id) && !EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}

		$jinput = JFactory::getApplication()->input;
		$users 	= $jinput->getString('users', null);
		$state 	= $jinput->getInt('state', null);

		$m_users = new EmundusModelUsers();
		if ($users === 'all') {
			$us = $m_users->getUsers();
			$users = array();
			foreach ($us as $u) {
				$users[] = $u->id;
			}
		} else $users = (array) json_decode(stripslashes($users));

		$res = $m_users->changeBlock($users, $state);

		if ($res !== false) {
			$res = true;
			$msg = JText::_('');
		} else $msg = JText::_('AN_ERROR_OCCURED');

		echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
		exit;
	}

	public function affectgroups() {
		$jinput = JFactory::getApplication()->input;
		$users = $jinput->getString('users', null);

		$groups = $jinput->getString('groups', null);
		$m_users = new EmundusModelUsers();

		if ($users === 'all' ) {
			$us = $m_users->getUsers();
			$users = array();
			foreach ($us as $u) {
				$users[] = $u->id;
			}
		}
		else $users = (array) json_decode(stripslashes($users));

		$users = $m_users->getNonApplicantId($users);
		$res = $m_users->affectToGroups($users, explode(',', $groups));

		if ($res === true) {
			$res = true;
			$msg = JText::_('USERS_AFFECTED_SUCCESS');
		} elseif($res === 0)
			$msg = JText::_('NO_GROUP_AFFECTED');
		else
			$msg = JText::_('AN_ERROR_OCCURED');


		echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
		exit;
	}

	public function edituser() {
		$current_user = JFactory::getUser();
		if (!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id) && !EmundusHelperAccess::asAccessAction(12, 'u') && !EmundusHelperAccess::asAccessAction(20, 'u')) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}

		$newuser['id'] 				= JRequest::getVar('id', null, 'POST', '', 0);
		$newuser['firstname'] 		= JRequest::getVar('firstname', null, 'POST', '', 0);
		$newuser['lastname'] 		= JRequest::getVar('lastname', null, 'POST', '', 0);
		$newuser['username'] 		= JRequest::getVar('login', null, 'POST', '', 0);
		$newuser['name'] 			= $newuser['firstname'].' '.$newuser['lastname'];
		$newuser['email'] 			= JRequest::getVar('email', null, 'POST', '', 0);
		$newuser['profile'] 		= JRequest::getVar('profile', null, 'POST', '', 0);
		$newuser['em_oprofiles']	= JRequest::getVar('oprofiles', null, 'POST', 'string',0);
		$newuser['groups'] 			= array(JRequest::getVar('jgr', null, 'POST', '', 0));
		$newuser['university_id'] 	= JRequest::getVar('university_id', null, 'POST', '', 0);
		$newuser['em_campaigns'] 	= JRequest::getVar('campaigns', null, 'POST', '', 0);
		$newuser['em_groups'] 		= JRequest::getVar('groups', null, 'POST', '', 0);
		$newuser['news'] 			= JRequest::getVar('newsletter', null, 'POST', 'string',0);

		if (preg_match('/^[0-9a-zA-Z\_\@\-\.]+$/', $newuser['username']) !== 1) {
			echo json_encode((object)array('status' => false, 'msg' => 'LOGIN_NOT_GOOD'));
			exit;
		}
		if (preg_match('/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-z\-0-9]+\.)+[a-z]{2,}))$/', $newuser['email']) !== 1) {
			echo json_encode((object)array('status' => false, 'msg' => 'MAIL_NOT_GOOD'));
			exit;
		}

		$m_users = new EmundusModelUsers();
		$res = $m_users->editUser($newuser);

		if ($res === true || !is_array($res)) {
			$res = true;
			$msg = JText::_('USERS_EDITED');
		} else {
			if (is_array($res)) {
				$res['status'] = false;
				echo json_encode((object)($res));
				exit;
			}
			else $msg = JText::_('AN_ERROR_OCCURED');
		}
		echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
		exit;
	}


	public function deleteusers() {
		$jinput = JFactory::getApplication()->input;
		$users = $jinput->getString('users', null);
		$m_users = new EmundusModelUsers();
		if ($users === 'all') {
			$us = $m_users->getUsers();
			$users = array();
			foreach ($us as $u) {
				$users[] = $u->id;
			}
		} else $users = (array) json_decode(stripslashes($users));

		$res = true;
		$msg = JText::_('COM_EMUNDUS_USERS_DELETED');

		foreach ($users as $user) {
			if (is_numeric($user)) {
				$u = JUser::getInstance($user);
				if (!$u->delete()) {
					$res = false;
					$msg = $u->getError();
				}
			}
		}
		echo json_encode((object)array('status' => $res, 'msg' => $msg));
		exit;
	}

    public function sendpasswd(){
        include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');

        $current_user = JFactory::getUser();

        if (!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id)) {
            echo json_encode((object)array('status' => false));
            exit;
        }
        $uid 		= JFactory::getApplication()->input->getInt('uid', null);
        $recipient 	= JFactory::getUser($uid);
        $chars 		= "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $passwd 	= substr( str_shuffle( $chars ), 0, 8);
        $passwd_md5 = md5($passwd);

        $m_users = new EmundusModelUsers();
        $res = $m_users->setNewPasswd($uid, $passwd_md5);

        if (!$res) {
            $msg = JText::_('COM_EMUNDUS_CANNOT_SET_NEW_PASSWORD');
            echo json_encode((object)array('status' => $res, 'msg' => $msg));
            exit;
        } else {
            $emails = new EmundusModelEmails;
            $mailer = JFactory::getMailer();
            $email = $emails->getEmail("new_account");

            $post = array();
            $tags = $emails->setTags($uid, $post, null, $passwd);
/*
            $from = $email->emailfrom;
            $from_id = $current_user->id;
            $fromname =$email->name;
            $to = $recipient->email;
            $subject = $email->subject;
            $body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);
*/
            $from = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
            $from_id = $current_user->id;
            $fromname = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
            $to = $recipient->email;
            $subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
            $body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);
            $body = $emails->setTagsFabrik($body);


            $app = JFactory::getApplication();
	        $email_from_sys = $app->getCfg('mailfrom');

	        // If the email sender has the same domain as the system sender address.
            if (!empty($email->emailfrom) && substr(strrchr($email->emailfrom, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1))
                $mail_from_address = $email->emailfrom;
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
                $res = false;
                $msg = JText::_('COM_EMUNDUS_ERROR_CANNOT_SEND_EMAIL').' : '.$send->__toString();
	            JLog::add($send->__toString(), JLog::ERROR, 'com_emundus.email');
            } else {
                $message = array(
                    'user_id_from' => $from_id,
                    'user_id_to' => $recipient->id,
                    'subject' => $subject,
                    'message' => $body
                );
                $emails->logEmail($message);

                $res = true;
                $msg = JText::_('COM_EMUNDUS_EMAIL_SENT');
            }
        }

        echo json_encode((object)array('status' => $res, 'msg' => $msg));
        exit;
    }
	// Edit actions rights for group
	public function setgrouprights(){
		$current_user = JFactory::getUser();
        $msg ='';

		if (!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id) && !EmundusHelperAccess::isPartner($current_user->id)) {
			echo json_encode((object)array('status' => false));
			exit;
		}

		$id 	= JFactory::getApplication()->input->getInt('id', null);
		$action = JFactory::getApplication()->input->get('action', null, 'WORD');
		$value 	= JFactory::getApplication()->input->getInt('value', '');

		$m_users = new EmundusModelUsers();
		$res = $m_users->setGroupRight($id, $action, $value);

		echo json_encode((object)array('status' => $res, 'msg' => $msg));
		exit;
	}
}
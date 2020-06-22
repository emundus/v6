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
	private $m_user = null;

	public function __construct($config = array()) {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'users.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'logs.php');

		$this->_user  = JFactory::getSession()->get('emundusUser');
		$this->_db    = JFactory::getDBO();
		$this->m_user = new EmundusModelUsers();

		parent::__construct($config);
	}


	public function display($cachable = false, $urlparams = false)  {
		// Set a default view if none exists
		if (!JRequest::getCmd( 'view' )) {
			$default = 'users';
			JRequest::setVar('view', $default );
		}

		if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id))
			parent::display();
		else
			echo JText::_('ACCESS_DENIED');
    }



	public function adduser() {

		// add to jos_emundus_users; jos_users; jos_emundus_groups; jos_users_profiles; jos_users_profiles_history
		$current_user = JFactory::getUser();

		if (!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id) && !EmundusHelperAccess::isPartner($current_user->id)) {
			echo json_encode((object)array('status' => false, 'uid' => $current_user->id, 'msg' => JText::_('ACCESS_DENIED')));
			exit;
		}

		$jinput = JFactory::getApplication()->input;
		$firstname = $jinput->post->get('firstname', null, null);
		$lastname = $jinput->post->get('lastname', null, null);
		$username = $jinput->post->get('login', null, null);
		$name = strtolower($firstname).' '.strtoupper($lastname);
		$email = $jinput->post->get('email', null, null);
		$profile = $jinput->post->get('profile', null, null);
		$oprofiles = $jinput->post->get('oprofiles', null, 'string');
		$jgr = $jinput->post->get('jgr', null, null);
		$univ_id = $jinput->post->get('university_id', null, null);
		$groups = $jinput->post->get('groups', null, 'string');
		$campaigns = $jinput->post->get('campaigns', null, 'string');
		$news = $jinput->post->get('newsletter', null, 'string');
		$ldap = $jinput->post->get('ldap', 0, null);

		// If we are creating a new user from the LDAP system, he does not have a password.
		if ($ldap == 0) {
			$password = JUserHelper::genRandomPassword();
		}

		$user = clone(JFactory::getUser(0));

		if (preg_match('/^[0-9a-zA-Z\_\@\-\.]+$/', $username) !== 1) {
			echo json_encode((object)array('status' => false, 'msg' => JText::_('USERNAME_NOT_GOOD')));
			exit;
		}
		if (preg_match('/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-z\-0-9]+\.)+[a-z]{2,}))$/', $email) !== 1) {
			echo json_encode((object)array('status' => false, 'msg' => JText::_('MAIL_NOT_GOOD')));
			exit;
		}

		$user->name = $name;
		$user->username = $username;
		$user->email = $email;
		if ($ldap == 0) {
			$user->password = md5($password);
		}
		$user->registerDate = date('Y-m-d H:i:s');
		$user->lastvisitDate = date('Y-m-d H:i:s');
		$user->groups = array($jgr);
		$user->block = 0;

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

		if (!mkdir(EMUNDUS_PATH_ABS.$uid, 0755) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$uid.DS.'index.html')) {
			echo json_encode((object) array('status' => false, 'uid' => $uid, 'msg' => JText::_('CANT_CREATE_USER_FOLDER_CONTACT_ADMIN')));
		}

		// Envoi de la confirmation de création de compte par email
		$m_emails = $this->getModel('emails');

		// If we are creating an ldap account, we need to send a different email.
		if ($ldap == 1) {
			$email = $m_emails->getEmail('new_ldap_account');
		} else {
			$email = $m_emails->getEmail('new_account');
		}

		$mailer = JFactory::getMailer();
		if ($ldap == 0) {
			$post = array('PASSWORD' => $password);
			$tags = $m_emails->setTags($user->id, $post, null, $password);
		} else {
			$tags = $m_emails->setTags($user->id, array(), null, null);
		}

        $from = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
        $fromname = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
        $subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
        $body = $email->message;

        if (!empty($email->Template)) {
	        $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $email->Template);
        }
		$body = preg_replace($tags['patterns'], $tags['replacements'], $body);
		$body = $m_emails->setTagsFabrik($body);

        $app = JFactory::getApplication();
		$email_from_sys = $app->getCfg('mailfrom');

		// If the email sender has the same domain as the system sender address.
		if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
			$mail_from_address = $from;
		} else {
			$mail_from_address = $email_from_sys;
		}

        $sender = [
            $mail_from_address,
            $fromname
		];

        $mailer->setSender($sender);
        $mailer->addReplyTo($email->emailfrom, $email->name);
        $mailer->addRecipient($user->email);
        $mailer->setSubject($subject);
        $mailer->isHTML(true);
        $mailer->Encoding = 'base64';
        $mailer->setBody($body);

        try {
			$send = $mailer->Send();

			if ($send === false) {
				JLog::add('No email configuration!', JLog::ERROR, 'com_emundus.email');
			} else {
				if (JComponentHelper::getParams('com_emundus')->get('logUserEmail', '0') == '1') {
					$message = array(
						'user_id_from' => $current_user->id,
						'user_id_to' => $uid,
						'subject' => $email->subject,
						'message' => $body
					);
					$m_emails->logEmail($message);
				}
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
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}

		$query = 'SELECT u.id FROM #__users AS u LEFT JOIN #__emundus_declaration AS d ON u.id=d.user WHERE u.usertype = "Registered" AND d.user IS NULL';
		$this->_db->setQuery($query);
		$this->delusers($this->_db->loadResultArray());
	}

	public function delrefused() {
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}

		$this->_db->setQuery('SELECT student_id FROM #__emundus_final_grade WHERE Final_grade=2 AND type_grade ="candidature"');
		$this->delusers($this->_db->loadResultArray());
	}

	public function delnonevaluated() {
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}

		$this->_db->setQuery('SELECT u.id FROM #__users AS u LEFT JOIN #__emundus_final_grade AS efg ON u.id=efg.student_id WHERE u.usertype = "Registered" AND efg.student_id IS NULL');
		$this->delusers($this->_db->loadResultArray());
	}

	public function archive() {
		$itemid = JFactory::getApplication()->getMenu()->getActive()->id;

		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		if (!empty($ids)) {
			foreach ($ids as $id) {
				$query = 'UPDATE #__emundus_users SET profile=999 WHERE user_id='.$id;
				$this->_db->setQuery($query);
				$this->_db->Query() or die($this->_db->getErrorMsg());

				$this->blockuser($id);
			}
		}

		$this->setRedirect('index.php?option=com_emundus&view=users&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
	}

	public function lastSavedFilter() {
		$query="SELECT MAX(id) FROM #__emundus_filters";
		$this->_db->setQuery( $query );
		$result = $this->_db->loadResult();
		echo $result;
	}

	public function getConstraintsFilter() {
		$filter_id = JRequest::getVar('filter_id', null, 'POST', 'none',0);

		$query = "SELECT constraints FROM #__emundus_filters WHERE id=".$filter_id;
		$this->_db->setQuery( $query );
		echo $this->_db->loadResult();
	}

	////// EXPORT SELECTED XLS ///////////////////
	public function export_selected_xls() {
	     $cids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		 $page= JRequest::getVar('limitstart',0,'get');
		 if (!empty($cids)) {
		 	$this->export_to_xls($cids);
		} else {
			$this->setRedirect("index.php?option=com_emundus&view=users&limitstart=".$page,JText::_("NO_ITEM_SELECTED"),'error');
		}
	}

   ////// EXPORT ALL XLS ///////////////////
	public function export_account_to_xls($reqids = array(), $el = array()) {
		$cid = JRequest::getVar('ud', null, 'POST', 'array', 0);
		require_once(JPATH_BASE.DS.'libraries'.DS.'emundus'.DS.'export_xls'.DS.'xls_users.php');
		export_xls($cid, array());
	}

	public function export_zip() {
		require_once('libraries/emundus/zip.php');
		$cid = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		JArrayHelper::toInteger($cid, 0);

		if (count( $cid ) == 0) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			exit;
		}
		zip_file($cid);
		exit;
	}

	public function addsession() {
		global $option;
		$select_filter = JRequest::getVar('select_id', null, 'GET', 'none',0);
		$mainframe = JFactory::getApplication();
		$mainframe->setUserState( $option."select_filter", $select_filter );
	}


	/////////////Nouvelle Gestion /////////////////
	public function clear() {
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

	public function loadfilters() {
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
		$name = JRequest::getVar('name', null, 'POST', 'none',0);

		$filt_params = JFactory::getSession()->get('filt_params');
		$adv_params = JFactory::getSession()->get('adv_cols');
		$constraints = array('filter'=>$filt_params, 'col'=>$adv_params);

		$constraints = json_encode($constraints);

		if (empty($itemid))
			$itemid = JRequest::getVar('Itemid', null, 'POST', 'none',0);

		$time_date = (date('Y-m-d H:i:s'));

		$query = "INSERT INTO #__emundus_filters (time_date,user,name,constraints,item_id) values('".$time_date."',".$user_id.",'".$name."',".$this->_db->quote($constraints).",".$itemid.")";
		$this->_db->setQuery($query);

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

		$jinput = JFactory::getApplication()->input;
		$gname = $jinput->getString('gname', null);
		$actions = $jinput->getString('actions', null);
		$progs = $jinput->getString('gprog', null);
		$gdesc = $jinput->getString('gdesc', null);
		$actions = (array) json_decode(stripslashes($actions));

		$m_users = new EmundusModelUsers();
		$res = $m_users->addGroup($gname, $gdesc, $actions, explode(',', $progs));

		if ($res !== false) {
			$msg = JText::_('GROUP_ADDED');
		} else {
			$msg = JText::_('AN_ERROR_OCCURED');
		}

		echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
		exit;
	}

	public function changeblock() {
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asAdministratorAccessLevel($user->id) && !EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}

		$jinput = JFactory::getApplication()->input;
		$users 	= $jinput->getString('users', null);
		$state 	= $jinput->getInt('state', null);

		$m_users = new EmundusModelUsers();


		if ($users === 'all') {

			$us = $m_users->getUsers(0,0);
			$users = array();

			foreach ($us as $u) {
				$users[] = $u->id;
			}

		} else {
			$users = (array) json_decode(stripslashes($users));
		}

		$res = $m_users->changeBlock($users, $state);

		if ($res !== false) {
			$res = true;
			$msg = JText::_('COM_EMUNDUS_ACTIVATE_ACCOUNT');
		} else $msg = JText::_('AN_ERROR_OCCURED');

		echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
		exit;
	}

	public function affectgroups() {
		$jinput = JFactory::getApplication()->input;
		$users = $jinput->getString('users', null);

		$groups = $jinput->getString('groups', null);
		$m_users = new EmundusModelUsers();

		if ($users === 'all') {
			$us = $m_users->getUsers(0,0);
			$users = array();
			foreach ($us as $u) {
				$users[] = $u->id;
			}
		} else {
		    $users = (array) json_decode(stripslashes($users));
		}

		$users = array_filter($users, function ($user) {
		    return $user !== 'em-check-all';
		});

        $users = $m_users->getNonApplicantId($users);
		$res = $m_users->affectToGroups($users, explode(',', $groups));

		if ($res === true) {
			$res = true;
			$msg = JText::_('USERS_AFFECTED_SUCCESS');
		} elseif ($res === 0) {
			$msg = JText::_('NO_GROUP_AFFECTED');
		} else {
			$msg = JText::_('AN_ERROR_OCCURED');
		}

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
		
		if (preg_match('/^[0-9a-zA-Z\_\@\-\.\+]+$/', $newuser['username']) !== 1) {
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

		if (!EmundusHelperAccess::asAccessAction(12, 'd') && !EmundusHelperAccess::asAccessAction(20, 'd')) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}

		$jinput = JFactory::getApplication()->input;
		$users = $jinput->getString('users', null);

		$m_users = new EmundusModelUsers();
		if ($users === 'all') {
			$us = $m_users->getUsers(0,0);

			$users = array();
			foreach ($us as $u) {
				$users[] = $u->id;
			}

		} else {
			$users = (array) json_decode(stripslashes($users));
		}

		$res = true;
		$msg = JText::_('COM_EMUNDUS_USERS_DELETED');
		$users_id = "";
		foreach ($users as $user) {
			if (is_numeric($user)) {
				$u = JUser::getInstance($user);
				$count = $m_users->countUserEvaluations($user);

				if ($count > 0) {
					/** user disactivation */
					$m_users->changeBlock(array($user),1);
					$users_id .= $user." ,";
					$res = false;
				} else {
					$u->delete();
					EmundusModelLogs::log($this->_user->id, $user, null, 20, 'd', 'COM_EMUNDUS_LOGS_DELETE_USER');
				}
			}
		}

		if ($users_id != "") {
			$msg = JText::sprintf('THIS_USER_CAN_NOT_BE_DELETED', $users_id);
		} 
		echo json_encode((object) array('status' => $res, 'msg' => $msg));

		exit;
	}

    public function regeneratepassword() {

        include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
        require_once(JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'controllers'.DS.'messages.php');

        jimport('joomla.user.helper');

	    if (!EmundusHelperAccess::asAccessAction(12, 'u') && !EmundusHelperAccess::asAccessAction(20, 'u')) {
		    echo json_encode((object)array('status' => false));
		    exit;
	    }

        $id = JFactory::getApplication()->input->getInt('user', null); //get id from the ajax request
        $user = new EmundusModelUsers(); // Instanciation of object from user model
        $users = $user->getUsersById($id); // get user from uid
        foreach ($users as $selectUser) {

            $passwd = JUserHelper::genRandomPassword(8); //generate a random password
            $passwd_md5 = JUserHelper::hashPassword($passwd); // hash the random password

            $m_users = new EmundusModelUsers();
            $res = $m_users->setNewPasswd($id, $passwd_md5); //update password
            $post = [ // values tout change in the bdd with key => values
                'PASSWORD' => $passwd
            ];
            if (!$res) {
                $msg = JText::_('COM_EMUNDUS_CANNOT_SET_NEW_PASSWORD');
                echo json_encode((object)array('status' => false, 'msg' => $msg));
                exit;
            } else {
                $c_messages = new EmundusControllerMessages();
                $lbl = 'regenerate_password';

                $c_messages->sendEmailNoFnum($selectUser->email, $lbl, $post);

                if ($c_messages != true) {
                    $msg = JText::_('EMAIL_NOT_SENT');

                } else {
                    $msg = JText::_('EMAIL_SENT');
                }
            }
        }

        echo json_encode((object)array('status' => true, 'msg'=>$msg));
        exit;
    }

	// Edit actions rights for group
	public function setgrouprights() {
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

	/**
	 * Search the LDAP for a user to add.
	 */
	public function ldapsearch () {

		if (!EmundusHelperAccess::asAccessAction(12, 'c')) {
			echo json_encode((object)array('status' => false));
			exit;
		}

		$m_users = new EmundusModelusers();

		$search = JFactory::getApplication()->input->getString('search', null);

		$return = $m_users->searchLDAP($search);

		// If no users are found :O or the LDAP is broken
		if (!$return) {
			echo json_encode((object) ['status' => false, 'msg' => 'Failed to connect to the ldap']);
			exit;
		}

		// Iterate through all of the LDAP search results and check if they exist already.
		$users = [];
		if (is_array($return->users)) {
			foreach ($return->users as $user) {

				// TODO: Implement getting the user photo.
				if (!empty($user['jpegPhoto'])) {
					$user['jpegPhoto'] = null;
				}

				// Certain users have a binary certificate file which breaks the JSON parsing as it is not UTF-8.
				if (!empty($user['userCertificate;binary'])) {
					$user["userCertificate;binary"] = null;
				}

				if (JUserHelper::getUserId($user['uid'][0]) > 0) {
					$user['exists'] = true;
				} else {
					$user['exists'] = false;
				}

				$users[] = $user;
			}
		}

		$response = json_encode((object) ['status' => $return->status, 'ldapUsers' => $users, 'count' => count($users)]);
		if (!$response) {
			echo json_encode((object) ['status' => false, 'msg' => 'Information retrieved from LDAP is of incorrect format.']);
			exit;
		}

		echo $response;
		exit;
	}

	/**
	 * Method to request a password reset. Taken from Joomla and adapted for eMundus.
	 *
	 * @return  boolean
	 *
	 * @throws Exception
	 * @since   3.9.11
	 */
	public function passrequest() {

		// Check the request token.
		$this->checkToken('post');

		$m_users = new EmundusModelusers();
		$data = JFactory::getApplication()->input->post->get('jform', array(), 'array');

		// Submit the password reset request.
		$return	= $m_users->passwordReset($data);

		// Check for a hard error.
		if ($return->status === false) {

			// The request failed.
			// Go back to the request form.
			$message = JText::sprintf('COM_USERS_RESET_REQUEST_FAILED', $return->message);
			$this->setRedirect('index.php?option=com_users&view=reset', $message, 'notice');
			return false;

		} else {

			// The request succeeded.
			// Proceed to step two.
			$this->setRedirect('index.php?option=com_users&view=reset&layout=confirm');
			return true;

		}
	}
}

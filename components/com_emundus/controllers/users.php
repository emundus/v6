<?php
/**
 * @package    Joomla
 * @subpackage eMundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/

// No direct access

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

require_once (JPATH_SITE . '/components/com_emundus/helpers/date.php');

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

	public function __construct($config = array()) {
		require_once (JPATH_COMPONENT.'/helpers/filters.php');
		require_once (JPATH_COMPONENT.'/helpers/files.php');
		require_once (JPATH_COMPONENT.'/helpers/access.php');
        require_once (JPATH_COMPONENT.'/helpers/date.php');
		require_once (JPATH_COMPONENT.'/models/users.php');
		require_once (JPATH_COMPONENT.'/models/logs.php');

		$this->_user  = JFactory::getSession()->get('emundusUser');
		$this->_db    = JFactory::getDBO();

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
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));
		$current_user = JFactory::getUser();

		if (EmundusHelperAccess::asAccessAction(12, 'c')) {
			$jinput = JFactory::getApplication()->input;
			$username = $jinput->post->get('login', null, null);
			$email = $jinput->post->get('email', null, null);

			if (!empty($email) && !empty($username)) {
				$firstname = $jinput->post->get('firstname', null, null);
				$lastname = $jinput->post->get('lastname', null, null);

				$params = [
					'firstname' => $jinput->post->get('firstname', null, null),
					'lastname' => $lastname,
					'username' => $username,
					'name' => ucfirst($firstname) . ' ' . strtoupper($lastname),
					'email' => $email,
					'profile' => $jinput->post->get('profile', null, null),
					'oprofiles' => $jinput->post->get('oprofiles', null, 'string'),
					'jgr' => $jinput->post->get('jgr', null, null),
					'univ_id' => $jinput->post->get('university_id', null, null),
					'groups' => $jinput->post->get('groups', null, 'string'),
					'campaigns' => $jinput->post->get('campaigns', null, 'string'),
					'news' => $jinput->post->get('newsletter', null, 'string'),
					'ldap' => $jinput->post->get('ldap', 0, null)
				];

				if (!class_exists('EmundusModelUsers')) {
					require_once(JPATH_ROOT . '/components/com_emundus/models/users.php');
				}
				$m_users = new EmundusModelUsers();

				try {
					$added = $m_users->addUserFromParams($params, $current_user);

					if ($added) {
						$response['status'] = true;
						$response['msg'] = JText::_('COM_EMUNDUS_USERS_USER_CREATED');
					} else {
						$response['msg'] = JText::_('COM_EMUNDUS_USERS_USER_NOT_CREATED');
					}
				} catch (Exception $e) {
					$response['msg'] = $e->getMessage();
				}
			}
		}

		echo json_encode($response);
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
		require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'export_xls'.DS.'xls_users.php');
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
			JError::raiseWarning( 500, JText::_( 'COM_EMUNDUS_ERROR_NO_ITEMS_SELECTED' ) );
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
        $h_files = new EmundusHelperFiles();
        $h_files->clear();
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
			$msg = JText::_('COM_EMUNDUS_GROUPS_GROUP_ADDED');
		} else {
			$msg = JText::_('COM_EMUNDUS_ERROR_OCCURED');
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
            if(count($users) > 1){
                if($state === 1) {
                    $msg = JText::_('COM_EMUNDUS_USERS_BLOCK_ACCOUNT_MULTI');
                } else {
                    $msg = JText::_('COM_EMUNDUS_USERS_UNBLOCK_ACCOUNT_MULTI');
                }
            } else {
                if($state === 1) {
                    $msg = JText::_('COM_EMUNDUS_USERS_BLOCK_ACCOUNT_SINGLE');
                } else {
                    $msg = JText::_('COM_EMUNDUS_USERS_UNBLOCK_ACCOUNT_SINGLE');
                }
            }
		} else $msg = JText::_('COM_EMUNDUS_ERROR_OCCURED');

		echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
		exit;
	}

    public function changeactivation() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asAdministratorAccessLevel($user->id) && !EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
            return;
        }

        $jinput = JFactory::getApplication()->input;
        $users 	= $jinput->getString('users', null);
        $state 	= $jinput->getInt('state', null);

        if($state == 0){
            $state = 1;
        } else {
            $state = -1;
        }

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

        $res = $m_users->changeActivation($users, $state);

        if ($res !== false) {
            $res = true;
            if(count($users) > 1){
                $msg = JText::_('COM_EMUNDUS_USERS_ACTIVATE_ACCOUNT_MULTI');
            } else {
                $msg = JText::_('COM_EMUNDUS_USERS_ACTIVATE_ACCOUNT_SINGLE');
            }
        } else $msg = JText::_('COM_EMUNDUS_ERROR_OCCURED');

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
		    return $user !== 'em-check-all' && is_numeric($user);
		});

        $users = $m_users->getNonApplicantId($users);
		$res = $m_users->affectToGroups($users, explode(',', $groups));

		if ($res === true) {
			$res = true;
			$msg = JText::_('COM_EMUNDUS_GROUPS_USERS_AFFECTED_SUCCESS');
		} elseif ($res === 0) {
			$msg = JText::_('COM_EMUNDUS_GROUPS_NO_GROUP_AFFECTED');
		} else {
			$msg = JText::_('COM_EMUNDUS_ERROR_OCCURED');
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
		$newuser['same_login_email']= JFactory::getApplication()->input->post->getInt('sameLoginEmail', null);
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
		if (!filter_var($newuser['email'], FILTER_VALIDATE_EMAIL)) {
			echo json_encode((object)array('status' => false, 'msg' => JText::_('COM_EMUNDUS_USERS_ERROR_NOT_A_VALID_EMAIL')));
			exit;
		}

		$m_users = new EmundusModelUsers();
		$res = $m_users->editUser($newuser);

		if ($res === true || !is_array($res)) {
			$res = true;
			$msg = JText::_('COM_EMUNDUS_USERS_EDITED');

			$e_user = JFactory::getSession()->get('emundusUser');
			if ($e_user->id == $newuser['id']) {
				$e_user->firstname = $newuser['firstname'];
				$e_user->lastname = $newuser['lastname'];
				$e_user->email = $newuser['email'];
				JFactory::getSession()->set('emundusUser', $e_user);
			}
		} else {
			if (is_array($res)) {
				$res['status'] = false;
				echo json_encode((object)($res));
				exit;
			}
			else $msg = JText::_('COM_EMUNDUS_ERROR_OCCURED');
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
				$count += $m_users->countUserDecisions($user);

				if ($count > 0) {
					/** user disactivation */
					$m_users->changeBlock(array($user),1);
					$users_id .= $user." ,";
					$res = false;
				} else {
					$u->delete();
					EmundusModelLogs::log($this->_user->id, $user, null, 20, 'd', 'COM_EMUNDUS_ADD_USER_DELETE');
				}
			}
		}

		if ($users_id != "") {
			$msg = JText::sprintf('COM_EMUNDUS_USERS_THIS_USER_CAN_NOT_BE_DELETED', $users_id);
		}
		echo json_encode((object) array('status' => $res, 'msg' => $msg));

		exit;
	}

	// Edit actions rights for group
	public function setgrouprights() {
		$current_user = JFactory::getUser();
        $msg ='';

		if (!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id) && !EmundusHelperAccess::isPartner($current_user->id)) {
			$msg = JText::_('ACCESS_DENIED');
		    echo json_encode((object)array('status' => false, 'msg'=>$msg));
			exit;
		}

		$id 	= JFactory::getApplication()->input->getInt('id', null);
		$action = JFactory::getApplication()->input->get('action', null, 'WORD');
		$value 	= JFactory::getApplication()->input->getInt('value', '');

		$m_users = new EmundusModelUsers();
		$res = $m_users->setGroupRight($id, $action, $value);

        try {
            require_once (JPATH_ROOT . '/administrator/components/com_emundus/helpers/update.php');
            EmundusHelperUpdate::clearJoomlaCache('mod_menu');
        } catch (Exception $e) {
            JLog::add('Cannot clear cache : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
        }



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

		$m_users = new EmundusModelusers();
		$response = array('status' => true, 'msg' => '');

		// Check the request token.
		if(JFactory::getUser()->guest)
		{
			$this->checkToken('post');

			$data = JFactory::getApplication()->input->post->get('jform', array(), 'array');

			$return = $m_users->passwordReset($data);

			// Check for a hard error.
			if ($return->status === false) {
				// The request failed.
				// Go back to the request form.
				$message = JText::sprintf('COM_USERS_RESET_REQUEST_FAILED', $return->message);
				$this->setRedirect('index.php?option=com_users&view=reset', $message, 'notice');

			} else {
				// The request succeeded.
				// Proceed to step two.
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=reset&layout=confirm'));
			}
		} elseif(EmundusHelperAccess::asAccessAction(12,'u') || EmundusHelperAccess::asAccessAction(20, 'u')) {
			$response['msg'] = JText::_('COM_EMUNDUS_USERS_RESET_REQUEST_LINK_SENDED');
			$users = JFactory::getApplication()->input->post->getString('users', null);
			if ($users === 'all') {
				$us = $m_users->getUsers(0,0);

				$users = array();
				foreach ($us as $u) {
					$users[] = $u->id;
				}
			} else {
				$users = (array) json_decode(stripslashes($users));
			}

			foreach ($users as $user)
			{
				$data = array();
				$data['email'] = JFactory::getUser($user)->email;

				$return = $m_users->passwordReset($data, 'COM_USERS_EMAIL_PASSWORD_RESET_SUBJECT_FOR_OTHER', 'COM_USERS_EMAIL_PASSWORD_RESET_BODY_FOR_OTHER');
				if($return->status === false) {
					$response['status'] = false;
					$response['msg'] = $return->msg;
				}
			}
		} else {
			$response['status'] = false;
			$response['msg'] = JText::_('ACCESS_DENIED');
		}

		if(!JFactory::getUser()->guest)
		{
			echo json_encode($response);
			exit;
		}
	}

	public function getuserbyid()
	{
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));
		$current_user = JFactory::getUser()->id;

		$id = JFactory::getApplication()->input->getInt('id', $current_user);
		if (!empty($id)) {
			if ($id == $current_user || EmundusHelperAccess::asPartnerAccessLevel($current_user)) {
				$m_users = new EmundusModelUsers();
				$users = $m_users->getUserById($id);

				if (!empty($users)) {
					foreach($users as $key => $user) {
						if (isset($user->password)) {
							unset($user->password);
							$users[$key] = $user;
						}
					}

					$response['user'] = $users;
					$response['status'] = true;
					$response['msg'] = JText::_('SUCCESS');
				}
			}
		}

		echo json_encode($response);
		exit;
	}

	public function getUserNameById() {
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));
		$current_user = JFactory::getUser()->id;

		$id = JFactory::getApplication()->input->getInt('id', $current_user);
		if (!empty($id)) {
			if ($id == $current_user || EmundusHelperAccess::asPartnerAccessLevel($current_user)) {
				$m_users = new EmundusModelUsers();
				$username = $m_users->getUserNameById($id);

				if (!empty($username)) {
					$response['user'] = $username;
					$response['status'] = true;
					$response['msg'] = JText::_('SUCCESS');
				}
			}
		}

		echo json_encode($response);
		exit;
	}

	public function getattachmentaccessrights()
	{
		$rights = array();

		$fnum = JFactory::getApplication()->input->getString('fnum', null);

        $rights['canCreate'] = EmundusHelperAccess::asAccessAction(4, 'c', $this->_user->id, $fnum);
        $rights['canDelete'] = EmundusHelperAccess::asAccessAction(4, 'd', $this->_user->id, $fnum);
		$rights['canUpdate'] = EmundusHelperAccess::asAccessAction(4, 'u', $this->_user->id, $fnum);
		$rights['canExport'] = EmundusHelperAccess::asAccessAction(8, 'c', $this->_user->id, $fnum);

		echo json_encode(array('status' => true, 'rights' => $rights));
		exit;
	}

    public function getprofileform() {
        $m_users = new EmundusModelUsers();
        $form = $m_users->getProfileForm();

        echo json_encode(array('status' => true, 'form' => $form));
        exit;
    }

    public function getprofilegroups() {
        $formid = JFactory::getApplication()->input->getInt('formid', null);
        if(!empty($formid)) {
            $m_users = new EmundusModelUsers();
            $groups = $m_users->getProfileGroups($formid);
        } else {
            $groups = [];
        }

        echo json_encode(array('status' => true, 'groups' => $groups));
        exit;
    }

    public function getprofileelements(){
        $groupid = JFactory::getApplication()->input->getInt('groupid', null);
        if(!empty($groupid)) {
            $m_users = new EmundusModelUsers();
            $elements = $m_users->getProfileElements($groupid);
        } else {
            $elements = [];
        }

        echo json_encode(array('status' => true, 'elements' => $elements));
        exit;
    }

    public function getprofileattachments(){
        $m_users = new EmundusModelUsers();
        $attachments = $m_users->getProfileAttachments(JFactory::getUser()->id);

        echo json_encode(array('status' => true, 'attachments' => $attachments));
        exit;
    }

    public function getprofileattachmentsallowed() {
        $m_users = new EmundusModelUsers();
        $attachments = $m_users->getProfileAttachmentsAllowed();

        echo json_encode(array('status' => true, 'attachments' => $attachments));
        exit;
    }

    public function uploaddefaultattachment() {
        $user = JFactory::getUser();

        $jinput = JFactory::getApplication()->input;
        $file = $jinput->files->get('file');
        $attachment_id = $jinput->getInt('attachment_id');
        $attachment_label = $jinput->getString('attachment_lbl');

        if(isset($file)) {
            $root_dir = "images/emundus/files/" . $user->id;
            $target_dir = $root_dir . '/default_attachments/';

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

            if(!file_exists($target_dir)){
                mkdir($target_dir);
            }

            $target_file = $target_dir . basename($user->id . '-' . $attachment_id . '-' .strtolower(substr($attachment_label,1)) . '-'  . time() . '.' . $ext);

            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                $m_users = new EmundusModelUsers();
                $uploaded = $m_users->addDefaultAttachment($user->id,$attachment_id,$target_file);

                $result = array('status' => $uploaded);
            } else {
                $result = array('status' => false);
            }
        } else {
            $result = array('status' => false);
        }
        echo json_encode((object)$result);
        exit;
    }

    public function deleteprofileattachment(){
        $user = JFactory::getUser();

        $jinput = JFactory::getApplication()->input;
        $id = $jinput->getInt('id', null);
        $filename = $jinput->getString('filename');

        if(!empty($id)) {
            $m_users = new EmundusModelUsers();
            $deleted = $m_users->deleteProfileAttachment($id,$user->id);

            if($deleted && !empty($filename)){
                unlink(JPATH_SITE . DS . $filename);
            }
        } else {
            $deleted = false;
        }

        echo json_encode(array('status' => true, 'deleted' => $deleted));
        exit;
    }

    public function uploadprofileattachmenttofile(){
        $jinput = JFactory::getApplication()->input;
        $aids = $jinput->getString('aids');

        $current_user = JFactory::getUser();

        if(!empty($aids)) {
            $m_users = new EmundusModelUsers();
            $copied = $m_users->uploadProfileAttachmentToFile($this->_user->fnum,$aids,$current_user->id);
        } else {
            $copied = false;
        }

        echo json_encode(array('status' => true, 'copied' => $copied));
        exit;
    }

    public function uploadfileattachmenttoprofile(){
        $jinput = JFactory::getApplication()->input;
        $aid = $jinput->getInt('aid');

        $current_user = JFactory::getUser();

        if(!empty($aid)) {
            $m_users = new EmundusModelUsers();
            $copied = $m_users->uploadFileAttachmentToProfile($this->_user->fnum,$aid,$current_user->id);
        } else {
            $copied = false;
        }

        echo json_encode(array('status' => true, 'copied' => $copied));
        exit;
    }

	public function updateprofilepicture() {
		$user = JFactory::getUser();

		$jinput = JFactory::getApplication()->input;
		$file = $jinput->files->get('file');

		if(isset($file)) {
			$root_dir = "images/emundus/files/" . $user->id;
			$target_dir = $root_dir . '/profile/';
			if(!file_exists($root_dir)){
				mkdir($root_dir);
			}
			if(!file_exists($target_dir)){
				mkdir($target_dir);
			}

			$ext = pathinfo($file['name'], PATHINFO_EXTENSION);

			$target_file = $target_dir . basename('profile.' . $ext);

			if (move_uploaded_file($file["tmp_name"], $target_file)) {
				$m_users = new EmundusModelUsers();
				$uploaded = $m_users->updateProfilePicture($user->id,$target_file);

				$result = array('status' => $uploaded, 'profile_picture' => $target_file);
			} else {
				$result = array('status' => false);
			}
		} else {
			$result = array('status' => false);
		}
		echo json_encode((object)$result);
		exit;
	}

    public function activation()
    {
        require_once(JPATH_COMPONENT . '/models/user.php');
        $m_user = new EmundusModelUser();

	    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

		if(!empty($email)) {
			$user = JFactory::getUser();
			$uid = $user->id;

			if (!empty($uid)) {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				// check user is not already activated
				$query->select('activation')
					->from('#__users')
					->where('id = '. $uid);

				try {
					$db->setQuery($query);
					$activation = $db->loadResult();
				} catch (Exception $e) {
					JLog::add('Error checking if user is already activated or not : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
					echo json_encode((object)(array('status' => false, 'msg' => JText::_('COM_EMUNDUS_FAILED_TO_CHECK_ACTIVATION'))));
					exit();
				}

				if ($activation == '-1') {
					$query->clear()
						->select('count(id)')
						->from($db->quoteName('#__users'))
						->where($db->quoteName('email') . ' LIKE ' . $db->quote($email))
						->andWhere($db->quoteName('id') . ' <> ' . $db->quote($uid));
					$db->setQuery($query);

					try {
						$email_alreay_use = $db->loadResult();
					} catch (Exception $e) {
						JLog::add('Error getting email already use: ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
						echo json_encode((object)(array('status' => false, 'msg' => JText::_('COM_EMUNDUS_MAIL_ERROR_TRYING_TO_GET_EMAIL_ALREADY_USE'))));
						exit();
					}

					if (!$email_alreay_use) {
						$query->clear()
							->select($db->quoteName('params'))
							->from($db->quoteName('#__users'))
							->where($db->quoteName('id') . ' = ' . $db->quote($uid));
						$db->setQuery($query);
						$result = $db->loadObject();

						$token = json_decode($result->params);
						$token = $token->emailactivation_token;

						$emailSent = $m_user->sendActivationEmail($user->getProperties(), $token, $email);

						if ($user->email != $email) {
							$m_user->updateEmailUser($user->id, $email);
						}
						if ($emailSent) {
							echo json_encode((object)(array('status' => true, 'msg' => JText::_('COM_EMUNDUS_MAIL_SUCCESSFULLY_SENT'))));
							exit();
						} else {
							echo json_encode((object)(array('status' => false, 'msg' => JText::_('COM_EMUNDUS_MAIL_ERROR_AT_SEND'))));
							exit();
						}
					} else {
						echo json_encode((object)(array('status' => false, 'msg' => JText::_('COM_EMUNDUS_MAIL_ALREADY_USE'))));
						exit();
					}
				} else {
					echo json_encode((object)(array('status' => false, 'msg' => JText::_('COM_EMUNDUS_ALREADY_ACTIVATED_USER'))));
					exit();
				}
			} else {
				echo json_encode((object)(array('status' => false, 'msg' => JText::_('EMPTY_CURRENT_USER'))));
				exit();
			}
		} else {
			echo json_encode((object)(array('status' => false, 'msg' => JText::_('INVALID_EMAIL'))));
			exit();
		}
    }

    public function updateemundussession(){
        $jinput = JFactory::getApplication()->input;
        $param = $jinput->getString('param', null);
        $value = $jinput->getBool('value', null);

        $session = JFactory::getSession();
        $e_session = $session->get('emundusUser');

        $e_session->{$param} = $value;
        $session->set('emundusUser', $e_session);

        echo json_encode(array('status' => true));
        exit;
    }

    public function addapplicantprofile(){
        $user = JFactory::getUser();

        $session = JFactory::getSession();
        $e_session = $session->get('emundusUser');

        $already_applicant = false;
        foreach ($e_session->emProfiles as $profile){
            if($profile->published == 1){
                $already_applicant = true;
                $app_profile = $profile;
                break;
            }
        }

        if(!$already_applicant) {
            $m_users = new EmundusModelUsers();
            $app_profile = $m_users->addApplicantProfile($user->id);

            $e_session->profile = $app_profile->id;
            $e_session->emProfiles[] = $app_profile;
            $e_session->menutype = null;
            $e_session->first_logged = true;
            $session->set('emundusUser', $e_session);
        } else {
            $e_session->profile = $app_profile->id;
            $e_session->menutype = null;
            $session->set('emundusUser', $e_session);
        }

        echo json_encode(array('status' => true));
        exit;
    }

    public function affectjoomlagroups(){
        if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $jinput = JFactory::getApplication()->input;

            $params = $jinput->getArray();
            $users = json_decode($params['users'], true);
            $groups = explode(',', $params['groups']);

            if (!empty($users) && !empty($groups)) {
                $m_users = new EmundusModelUsers();
                $affected = $m_users->affectToJoomlaGroups($users, $groups);
            } else {
                $affected = false;
            }

            $tab = array('status' => $affected, 'msg' => JText::_("GROUPS_AFFECTED"));
        } else {
            $tab = array('status' => false, 'msg' => JText::_("ACCESS_DENIED"));
        }

        echo json_encode($tab);
        exit;
    }


    public function activation_anonym_user()
    {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $user_id = $jinput->getInt('user_id', 0);
        $token = $jinput->getString('token', '');

        if (!empty($token) && !empty($user_id)) {
            $m_users = new EmundusModelUsers();
            $valid = $m_users->checkTokenCorrespondToUser($token, $user_id);

            if ($valid) {
                $updated = $m_users->updateAnonymUserAccount($token, $user_id);

                if ($updated) {
                    $app->enqueueMessage(JText::_('COM_EMUNDUS_USERS_ANONYM_USER_ACTIVATION_SUCCESS'), 'success');
                } else {
                    $app->enqueueMessage(JText::_('COM_EMUNDUS_USERS_FAILED_TO_ACTIVATE_USER'), 'warning');
                }
                $app->redirect('/');
            } else {
                JLog::add("WARNING! Wrong paramters together, token $token and user_id $user_id from" . $_SERVER['REMOTE_ADDR'], JLog::WARNING, 'com_emundus.error');
            }
        } else {
            JLog::add('WARNING! Attempt to activate anonym user without necessary parameters from ' . $_SERVER['REMOTE_ADDR'], JLog::WARNING, 'com_emundus.error');
        }
    }

	public function getCurrentUser()
    {
        $currentUser = JFactory::getUser();

        if (!EmundusHelperAccess::asPartnerAccessLevel($currentUser->id)) {
            return false;
        }

        echo json_encode($currentUser);
        exit;
    }

	function getcurrentprofile()
	{
		$response = ['status' => false, 'msg' => JText::_('ACCESS_DENIED')];
		$user = JFactory::getUser();

		if (!$user->guest) {
			$em_users = JFactory::getSession()->get('emundusUser');
			$m_users = $this->getModel('Users');

			if (!empty($em_users->profile)) {
				$response['data'] = $m_users->getProfileDetails($em_users->profile);
				$response['status'] = true;
				$response['msg'] = JText::_('COM_EMUNDUS_SUCCESS');
			} else {
				$response['msg'] = 'No profile found';
			}
		}

		echo json_encode((object)$response);
		exit;
	}

    /**
     * @return void
     *
     * @description Export users' selected data. Extracted data are also selected by the user.
     *
     * @throws Exception
     */
    public function exportusers()
    {
		$current_user = Factory::getUser();
        if (!EmundusHelperAccess::asAccessAction(12, 'r',$current_user->id)) {
            $this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
            return;
        }

        $m_users = new EmundusModelUsers();

        // Retrieve the users' data to extract (indicated by the checkboxes checked)
        $checkboxes = $this->input->getString('checkboxes');
        $users = $this->input->getString('users', null);

        $checkboxes = (array)json_decode(stripslashes($checkboxes));

        // If 'all' is choosed, it's necessary to retrieve the ids
        if ($users === 'all') {
            $all_users = $m_users->getUsers(0, 0);
            $user_ids = array();
            foreach ($all_users as $user) {
                $user_ids[] = $user->id;
            }
        } else {
            $user_ids = (array)json_decode(stripslashes($users));
        }

        $user_details = array();
        foreach ($user_ids as $uid) {
            $user_details[] = $m_users->getUserDetails($uid);
        }

        // Fill CSV
        $export_filename = 'export_users_'. $current_user->id .'_' . date('Y-m-d_H:i') . '.csv';
        $path = JPATH_SITE . '/tmp/' . $export_filename;

        $seen_keys = [];
        $headers = array();

        // Fill keys
        $csv_file = fopen($path, 'w');

        $seen_keys[] = 'COM_EMUNDUS_USERNAME';
        $headers[] = Text::_('COM_EMUNDUS_USERNAME');

        foreach ($user_details as $user_detail) {
            foreach ($user_detail as $key => $value) {
                if (!in_array($key, $seen_keys) && $checkboxes[$key]) {
                    $seen_keys[] = $key;
                    $headers[] = Text::_(strtoupper($key));
                }
            }
        }
        fputcsv($csv_file, $headers,';');
        //

        // Retrieve all the value of users' data necessary
        foreach ($user_details as $user_detail) {
            $userData = array();
            foreach ($user_detail as $key => $value) {
                if (in_array($key, $seen_keys)) {
                    if ($key === 'COM_EMUNDUS_USERNAME') {
                        // We force to put Username as the first column
                        array_unshift($userData, $value);
                    } else if ($key === 'COM_EMUNDUS_FIRSTNAME' || $key === 'COM_EMUNDUS_LASTNAME') {
                        $userData[] = $value;
                    }
					else {
						$userData[] = Text::_($value);
					}
                }
            }
            if (!empty(array_filter($userData))) {
                fputcsv($csv_file, $userData,';');
            }
        }
        fclose($csv_file);

		$nb_cols = count($headers);
		$nb_rows = count($user_details);
		$xls_file = $m_users->convertCsvToXls($export_filename,$nb_cols,$nb_rows,'export_users_'. $current_user->id .'_' . date('Y-m-d_H:i'),';');
        //

        // Add all the headers necessary
	    if(!empty($xls_file)) {
			$path = JPATH_SITE . '/tmp/' . $xls_file;
		    $export_filename = $xls_file;

		    header('Content-type: application/vnd.ms-excel');
	    } else {
		    header('Content-type: text/csv');
	    }

        header('Content-Disposition: attachment; filename=' . $export_filename);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
        header('Cache-control: private');
        header('Expires: 0');

        // Encode file's path and file's name if necessary
        echo json_encode(['csvFilePath' => $path, 'fileName' => $export_filename]);
        exit;
    }
}


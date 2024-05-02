<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      James Dean
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusControllerMessenger extends JControllerLegacy
{

    var $m_messenger = null;

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->m_messenger = $this->getModel('messenger');
    }

    /**
     * Get campaigns by fnums of current user
     */
    public function getfilesbyuser() {
        $user = JFactory::getUser();

        $files = $this->m_messenger->getFilesByUser();
        $data = array('data' => $files, 'current_user' => $user->id);

        echo json_encode((object)$data);
        exit;
    }

    public function getmessagesbyfnum(){
		$response = ['data' => null, 'status' => false, 'msg' => JText::_('BAD_REQUEST'), 'code' => 403];

        $jinput = JFactory::getApplication()->input;
	    $fnum = $jinput->getString('fnum');
	    $current_user = JFactory::getUser();

		if (!empty($fnum) && !empty($current_user->id)) {
			require_once (JPATH_ROOT . '/components/com_emundus/models/profile.php');
			$m_profile = new EmundusModelProfile();
			$current_user_fnums = array_keys($m_profile->getApplicantFnums($current_user->id));
			$response['msg'] = JText::_('ACCESS_DENIED');

			if (EmundusHelperAccess::asAccessAction(36, 'c', $current_user->id, $fnum) || in_array($fnum, $current_user_fnums)) {
				$offset = $jinput->getString('offset',0);

				$response['data'] = $this->m_messenger->getMessagesByFnum($fnum,$offset);
				if (!empty($response['data'])) {
					$response['msg'] = JText::_('SUCCESS');
					$response['code'] = 200;
				} else {
					$response['msg'] = JText::_('FAIL');
					$response['code'] = 500;
				}
			}
		}

        echo json_encode((object)$response);
        exit;
    }

    public function sendmessage(){
	    $response = ['data' => null, 'status' => false, 'msg' => JText::_('BAD_REQUEST'), 'code' => 403];
        $jinput = JFactory::getApplication()->input;

        $message = $jinput->getString('message', '');
        $fnum = $jinput->getString('fnum', '');

		if (!empty($fnum) && !empty($message)) {
			$response['msg'] = JText::_('ACCESS_DENIED');
			$current_user = JFactory::getUser();
			require_once (JPATH_ROOT . '/components/com_emundus/models/profile.php');
			$m_profile = new EmundusModelProfile();
			$current_user_fnums = array_keys($m_profile->getApplicantFnums($current_user->id));

			if (EmundusHelperAccess::asAccessAction(36, 'c', $current_user->id, $fnum) || in_array($fnum, $current_user_fnums)) {
				$response['data'] = $this->m_messenger->sendMessage($message,$fnum);

				if (!empty($response['data']->message_id)) {
					$response['status'] = true;
					$response['msg'] = JText::_('SUCCESS');
					$response['code'] = 200;
				} else {
					$response['msg'] = JText::_('FAIL');
					$response['code'] = 500;
				}
			}
		}

        echo json_encode((object)$response);
        exit;
    }

	public function getnotifications() {
		$response = ['data' => null, 'status' => false, 'msg' => JText::_('BAD_REQUEST'), 'code' => 403];
		$jinput = JFactory::getApplication()->input;
		$user = $jinput->getInt('user');

		if (!empty($user)) {
			$response['msg'] = JText::_('ACCESS_DENIED');
			$current_user = JFactory::getUser();

			if ($current_user->id == $user) {
				$response['data'] = $this->m_messenger->getNotifications($user);
				$response['msg'] = JText::_('SUCCESS');
				$response['code'] = 200;
				$response['status'] = true;
			}
		}

		echo json_encode((object)$response);
		exit;
	}

    public function getnotificationsbyfnum(){
	    $response = ['data' => null, 'status' => false, 'msg' => JText::_('BAD_REQUEST'), 'code' => 403];
	    $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum');

		if (!empty($fnum)) {
			$response['msg'] = JText::_('ACCESS_DENIED');

			$current_user = JFactory::getUser();
			require_once (JPATH_ROOT . '/components/com_emundus/models/profile.php');
			$m_profile = new EmundusModelProfile();
			$current_user_fnums = array_keys($m_profile->getApplicantFnums($current_user->id));

			if(EmundusHelperAccess::asAccessAction(36, 'c', $current_user->id, $fnum) || in_array($fnum, $current_user_fnums)) {
				$response['data'] = $this->m_messenger->getNotificationsByFnum($fnum);
				$response['code'] = 200;
				$response['status'] = true;
			}
		}

        echo json_encode((object)$response);
        exit;
    }

    public function markasread(){
        $jinput = JFactory::getApplication()->input;

        $fnum = $jinput->getString('fnum');

        $messages_readed = $this->m_messenger->markAsRead($fnum);

        $data = array('data' => $messages_readed);

        echo json_encode((object)$data);
        exit;
    }

    public function uploaddocument(){
		$response = ['status' => false, 'code' => 403, 'msg' => JText::_('BAD_REQUEST'), 'data' => null];

        $jinput = JFactory::getApplication()->input;
        $file = $jinput->files->get('file');

		if (!empty($file)) {
			$fnum = $jinput->get('fnum');

			if (!empty($fnum)) {
				$response['msg'] = JText::_('ACCESS_DENIED');
				$message_input = $jinput->getString('message');
				$applicant = $jinput->getBool('applicant');
				$attachment = $jinput->getInt('attachment');

				require_once(JPATH_SITE .  '/components/com_emundus/models/files.php');
				$m_files = new EmundusModelFiles();
				$fnumInfos = $m_files->getFnumInfos($fnum);
				$applicant_id = $fnumInfos['applicant_id'];
				$current_user = JFactory::getUser();

				if (($current_user->id == $applicant_id || EmundusHelperAccess::asAccessAction(36, 'c', $current_user->id, $fnum) ) && isset($file)) {
					$path = $file['name'];
					$ext = pathinfo($path, PATHINFO_EXTENSION);
					$filename = pathinfo($path, PATHINFO_FILENAME);

					$target_root = 'images/emundus/files/';
					$target_dir = $target_root . $applicant_id . '/';
					if (!file_exists($target_root)) {
						mkdir($target_root);
					}
					if (!file_exists($target_dir)) {
						mkdir($target_dir);
					}

					if ($applicant && !empty($attachment)) {
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);

						$query->select('lbl')
							->from($db->quoteName('#__emundus_setup_attachments'))
							->where($db->quoteName('id') . ' = ' . $attachment);
						$db->setQuery($query);
						$lbl = $db->loadResult();
					}

					do {
						if ($applicant && !empty($attachment)) {
							$filesrc = $fnumInfos['applicant_id'] . '-' . $fnumInfos['id'] . '-' . trim($lbl, ' _') . '-' . rand() . '.' . $ext;
						} else {
							$filesrc = $fnum . '_' . rand(1000, 90000) . '.' . $ext;
						}
						$target_file = $target_dir . $filesrc;
					} while (file_exists($target_file));

					if (move_uploaded_file($file["tmp_name"], $target_file)) {
						$message = '<p>' . $message_input . '</p><a href="' . $target_file . '" download><img src="/images/emundus/messenger/file_download.svg" class="messages__download_icon" alt="' . $filename . '">' . $filename . '</a>';
						$new_message = $this->m_messenger->sendMessage($message, $fnum);
						if ($applicant) {
							$upload_emundus = $this->m_messenger->moveToUploadedFile($fnumInfos, $attachment, $filesrc, $target_file);
						}
						$response['msg'] = $upload_emundus;
						$response['data'] = $new_message;
						$response['status'] = true;
						$response['code'] = 200;

					} else {
						$response['msg'] = JText::_('ERROR_WHILE_UPLOADING_YOUR_DOCUMENT');
						$response['status'] = false;
						$response['code'] = 500;
					}
				}
			}
		}

		echo json_encode($response);
        exit;
    }

    public function getdocumentsbycampaign(){
        $jinput = JFactory::getApplication()->input;

        $fnum = $jinput->getString('fnum');
        $applicant = $jinput->getVar('applicant');

        $messages_readed = $this->m_messenger->getDocumentsByCampaign($fnum, $applicant);

        $data = array('data' => $messages_readed);

        echo json_encode((object)$data);
        exit;
    }

    public function askattachment(){
        $jinput = JFactory::getApplication()->input;

        $fnum = $jinput->getString('fnum');
        $attachment = $jinput->getString('attachment');
        $message = $jinput->getString('message');

        $new_message = $this->m_messenger->askAttachment($fnum,$attachment,$message);

        $data = array('data' => $new_message);

        echo json_encode((object)$data);
        exit;
    }
}

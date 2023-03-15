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
		$response = ['data' => null, 'status' => false, 'msg' => JText::_('BAD_REQUEST')];

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
					$response['status'] = true;
					$response['msg'] = JText::_('SUCCESS');
				} else {
					$response['status'] = false;
					$response['msg'] = JText::_('FAIL');
				}
			}
		}

        echo json_encode((object)$response);
        exit;
    }

    public function sendmessage(){
        $jinput = JFactory::getApplication()->input;

        $message = $jinput->getString('message');
        $fnum = $jinput->getString('fnum');

        $new_message = $this->m_messenger->sendMessage($message,$fnum);

        echo json_encode((object)$new_message);
        exit;
    }

	public function getnotifications(){
		$res = array('data' => [], 'status' => false);

		$jinput = JFactory::getApplication()->input;
		$user = $jinput->getString('user');

		if (!empty($user)) {
			$notifications = $this->m_messenger->getNotifications($user);
			$res = array('data' => $notifications, 'status' => true);
		}

		echo json_encode((object)$res);
		exit;
	}

    public function getnotificationsbyfnum(){
        $jinput = JFactory::getApplication()->input;

        $fnum = $jinput->getString('fnum');

        $notifications = $this->m_messenger->getNotificationsByFnum($fnum);

        $data = array('data' => $notifications, 'status' => true);

        echo json_encode((object)$data);
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
        $jinput = JFactory::getApplication()->input;

        $file = $jinput->files->get('file');
        $fnum = $jinput->get('fnum');
        $message_input = $jinput->getString('message');
        $applicant = $jinput->getBool('applicant');
        $attachment = $jinput->getInt('attachment');

        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');

        $m_files = new EmundusModelFiles();

        $fnumInfos = $m_files->getFnumInfos($fnum);

        $applicant_id = $fnumInfos['applicant_id'];

        if(isset($file)) {
            $path = $file["name"];
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $filename = pathinfo($path, PATHINFO_FILENAME);


            $target_root = "images/emundus/files/";
            $target_dir = $target_root . $applicant_id . "/";
            if(!file_exists($target_root)){
                mkdir($target_root);
            }
            if(!file_exists($target_dir)){
                mkdir($target_dir);
            }

            if($applicant && !empty($attachment)) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->select('lbl')
                    ->from($db->quoteName('#__emundus_setup_attachments'))
                    ->where($db->quoteName('id') . ' = ' . $attachment);
                $db->setQuery($query);
                $lbl = $db->loadResult();
            }

            do{
                if($applicant && !empty($attachment)){
                    $filesrc = $fnumInfos['applicant_id'].'-'.$fnumInfos['id'].'-'.trim($lbl, ' _').'-'.rand().'.'.$ext;
                } else {
                    $filesrc = $fnum . '_' . rand(1000,90000) . '.' . $ext;
                }
                $target_file = $target_dir . $filesrc;
            } while (file_exists($target_file));

            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                $message = '<p>' . $message_input . '</p><a href="'.$target_file.'" download><img src="/images/emundus/messenger/file_download.svg" class="messages__download_icon" alt="'.$filename.'">'.$filename.'</a>';
                $new_message = $this->m_messenger->sendMessage($message,$fnum);
                if($applicant){
                    $upload_emundus = $this->m_messenger->moveToUploadedFile($fnumInfos, $attachment, $filesrc, $target_file);
                }
                echo json_encode(array('msg' => $upload_emundus,'data' => $new_message));
            } else {
                echo json_encode(array('msg' => 'ERROR WHILE UPLOADING YOUR DOCUMENT'));
            }
        }

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

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
class EmundusmessengerControllermessages extends JControllerLegacy
{

    var $model = null;

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->model = $this->getModel('messages');
    }

    /**
     * Get campaigns by fnums of current user
     */
    public function getfilesbyuser() {
        $user = JFactory::getUser();

        $m_messages = $this->model;

        $files = $m_messages->getFilesByUser();

        $data = array('data' => $files, 'current_user' => $user->id);

        echo json_encode((object)$data);
        exit;
    }

    public function getmessagesbyfnum(){
        $user = JFactory::getUser();

        $m_messages = $this->model;

        $jinput = JFactory::getApplication()->input;

        $fnum = $jinput->getString('fnum');
        $offset = $jinput->getString('offset',0);

        $messages = $m_messages->getMessagesByFnum($fnum,$offset);

        $data = array('data' => $messages);

        echo json_encode((object)$data);
        exit;
    }

    public function sendmessage(){
        $user = JFactory::getUser();

        $m_messages = $this->model;

        $jinput = JFactory::getApplication()->input;

        $message = $jinput->getString('message');
        $fnum = $jinput->getString('fnum');

        $new_message = $m_messages->sendMessage($message,$fnum);

        echo json_encode((object)$new_message);
        exit;
    }

    public function getnotifications(){
        $user = JFactory::getUser();

        $m_messages = $this->model;

        $jinput = JFactory::getApplication()->input;

        $user = $jinput->getString('user');

        $notifications = $m_messages->getNotifications($user);

        $data = array('data' => $notifications, 'status' => true);

        echo json_encode((object)$data);
        exit;
    }

    public function getnotificationsbyfnum(){
        $user = JFactory::getUser();

        $m_messages = $this->model;

        $jinput = JFactory::getApplication()->input;

        $fnum = $jinput->getString('fnum');

        $notifications = $m_messages->getNotificationsByFnum($fnum);

        $data = array('data' => $notifications, 'status' => true);

        echo json_encode((object)$data);
        exit;
    }

    public function markasread(){
        $user = JFactory::getUser();

        $m_messages = $this->model;

        $jinput = JFactory::getApplication()->input;

        $fnum = $jinput->getString('fnum');

        $messages_readed = $m_messages->markAsRead($fnum);

        $data = array('data' => $messages_readed);

        echo json_encode((object)$data);
        exit;
    }

    public function uploaddocument(){
        $m_messages = $this->model;

        $jinput = JFactory::getApplication()->input;

        $file = $jinput->files->get('file');
        $fnum = $jinput->get('fnum');
        $message_input = $jinput->getString('message');
        $applicant = $jinput->getBool('applicant');
        $attachment = $jinput->getInt('attachment');

        require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');

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
                $new_message = $m_messages->sendMessage($message,$fnum);
                if($applicant){
                    $upload_emundus = $m_messages->moveToUploadedFile($fnumInfos,$attachment,$filesrc,$target_file);
                }
                echo json_encode(array('msg' => $upload_emundus,'data' => $new_message));
            } else {
                echo json_encode(array('msg' => 'ERROR WHILE UPLOADING YOUR DOCUMENT'));
            }
        }

        exit;
    }

    public function getdocumentsbycampaign(){

        $m_messages = $this->model;

        $jinput = JFactory::getApplication()->input;
        
        $fnum = $jinput->getString('fnum');
        $applicant = $jinput->getVar('applicant');

        $messages_readed = $m_messages->getDocumentsByCampaign($fnum, $applicant);

        $data = array('data' => $messages_readed);

        echo json_encode((object)$data);
        exit;
    }

    public function askattachment(){
        $user = JFactory::getUser();

        $m_messages = $this->model;

        $jinput = JFactory::getApplication()->input;

        $fnum = $jinput->getString('fnum');
        $attachment = $jinput->getString('attachment');
        $message = $jinput->getString('message');

        $new_message = $m_messages->askAttachment($fnum,$attachment,$message);

        $data = array('data' => $new_message);

        echo json_encode((object)$data);
        exit;
    }
}

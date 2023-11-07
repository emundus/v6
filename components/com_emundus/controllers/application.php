<?php
/**
 * @version     $Id: application.php 750 2012-01-23 22:29:38Z brivalland $
 * @package     Joomla
 * @copyright   (C) 2016 eMundus LLC. All rights reserved.
 * @license     GNU General Public License
 */

// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS') );

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;

/**
 * Custom report controller
 * @package     Emundus
 */
class EmundusControllerApplication extends JControllerLegacy
{
	protected $app;

	private $_user;

	public function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'export.php');

		$this->app = Factory::getApplication();
		$this->_user = $this->app->getIdentity();

		parent::__construct($config);
	}

    public function display($cachable = false, $urlparams = false) {
        // Set a default view if none exists
		if (!$this->input->get('view')) {
            $default = 'application_form';
			$this->input->set('view', $default);
        }
        parent::display();
    }

    /**
     * Delete an applicant attachment(s)
     */
	public function delete_attachments()
	{

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            die(JText::_("ACCESS_DENIED"));
		}

		$attachments = $this->input->get('attachments', null, 'POST', 'array', 0);
		$user_id     = $this->input->get('sid', null, 'POST');
		$view        = $this->input->get('view', null, 'POST');
		$tmpl        = $this->input->get('tmpl', null, 'POST');

        $url = !empty($tmpl)?'index.php?option=com_emundus&view='.$view.'&sid='.$user_id.'&tmpl='.$tmpl.'#attachments':'index.php?option=com_emundus&view='.$view.'&sid='.$user_id.'&tmpl=component#attachments';
		ArrayHelper::toInteger($attachments, 0);

        if (count($attachments) == 0) {
			$this->app->enqueueMessage(JText::_('COM_EMUNDUS_ERROR_NO_ITEMS_SELECTED'), 'error');
            exit;
        }
		
		$m_application = $this->getModel('Application');

        foreach ($attachments as $id) {
            $upload = $m_application->getUploadByID($id);
            $attachment = $m_application->getAttachmentByID($upload['attachment_id']);

			if (EmundusHelperAccess::asAccessAction(4, 'd', $this->_user->id, $upload['fnum'])) {
                $result = $m_application->deleteAttachment($id);

                if ($result != 1) {
                    echo JText::_('ATTACHMENT_DELETE_ERROR').' : '.$attachment['value'].' : '.$upload['filename'];
                } else {
                    $file = EMUNDUS_PATH_ABS.$user_id.DS.$upload['filename'];
					unlink($file);

                    $row['applicant_id'] = $upload['user_id'];
					$row['user_id']      = $this->_user->id;
                    $row['reason'] = JText::_('COM_EMUNDUS_ATTACHMENTS_DELETED');
                    $row['comment_body'] = $attachment['value'].' : '.$upload['filename'];
                    $m_application->addComment($row);

                    echo $result;
                }
            }
            else {
                echo JText::_('ACCESS_DENIED').' : '.$attachment['value'].' : '.$upload['filename'];
            }
        }

        $this->setRedirect($url, JText::_('DONE'), 'message');
        return;
    }

    /**
     * Delete an applicant attachment (one by one)
     */
	public function delete_attachment()
	{

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			die(JText::_("ACCESS_DENIED"));
		}

		$id = $this->input->get('id', null, 'GET');

		$m_application = $this->getModel('Application');

        $upload = $m_application->getUploadByID($id);
        $attachment = $m_application->getAttachmentByID($upload['attachment_id']);

		if (EmundusHelperAccess::asAccessAction(4, 'd', $this->_user->id, $upload['fnum'])) {
            $result = $m_application->deleteAttachment($id);

            if($result != 1){
                echo JText::_('ATTACHMENT_DELETE_ERROR');
            } else {
                $row['applicant_id'] = $upload['user_id'];
				$row['user_id']      = $this->_user->id;
                $row['reason'] = JText::_('COM_EMUNDUS_ATTACHMENTS_DELETED');
                $row['comment_body'] = $attachment['value'].' : '.$upload['filename'];
                $m_application->addComment($row);

                echo ($result);
            }
        }
        else {
            echo JText::_('ACCESS_DENIED').' : '.$attachment['value'].' : '.$upload['filename'];
        }

    }

    /**
     * Upload an applicant attachment (one by one)
     */
    public function upload_attachment() {

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
	        die(JText::_("ACCESS_DENIED"));
        }

		$aid            = $this->input->get('attachment_id', null, 'POST');
		$uid            = $this->input->get('uid', null, 'POST');
		$filename       = $this->input->get('filename', null, 'POST');
		$campaign_id    = $this->input->get('campaign_id', null, 'POST');
		$can_be_viewed  = $this->input->get('can_be_viewed', null, 'POST');
		$can_be_deleted = $this->input->get('can_be_deleted', null, 'POST');

        $targetFolder = EMUNDUS_PATH_ABS.$uid;

        if (!empty($_FILES)) {
            $msg = "";
            $data = "{";
            switch ($_FILES['filename']['error']) {

            	case 0:
                	$msg .= JText::_("FILE_UPLOADED");
                    $data .= '"message":"'.$msg.'",';
                    $tempFile = $_FILES['filename']['tmp_name'];
                    $targetPath = $targetFolder;

                    // Validate the file type
                    $fileTypes = array('jpg','jpeg','gif','png', 'pdf', 'doc', 'docx', 'odt', 'zip'); // File extensions
                    $fileParts = pathinfo($_FILES['filename']['name']);

                    if (in_array($fileParts['extension'], $fileTypes)) {
						$m_application   = $this->getModel('Application');
                        $type_attachment = $m_application->getAttachmentByID($aid);

                        $filename = date('Y-m-d_H-i-s').$type_attachment['lbl'].'_'.$_FILES['filename']['name'];
                        $fileURL = EMUNDUS_PATH_REL.$uid.'/'.$filename;
                        $targetFile = rtrim($targetPath,'/') . DS . $filename;

                        move_uploaded_file($tempFile, $targetFile);

                        $filesize = $_FILES['filename']['size'];

                        $attachment["key"] = array("user_id", "attachment_id", "filename", "description", "can_be_deleted", "can_be_viewed", "campaign_id");
                        $attachment["value"] = array($uid, $aid, '"'.$filename.'"', '"'.date('Y-m-d H:i:s').'"', $can_be_deleted, $can_be_viewed, $campaign_id);

                        $id = $m_application->uploadAttachment($attachment);
                    } else {
                        $msg .= JText::_('COM_EMUNDUS_ATTACHMENTS_FILETYPE_INVALIDE');
                    }

                    $data .= '"message":"'.$msg.'",';
                    $data .= '"url":"'.htmlentities($fileURL).'",';
                    $data .= '"id":"'.$id.'",';
                    $data .= '"filesize":"'.$filesize.'",';
                    $data .= '"name":"'.$type_attachment['value'].'",';
                    $data .= '"filename":"'.$filename.'",';
                    $data .= '"path":"'.str_replace("\\", "\\\\", $targetPath).'",';
                    $data .= '"aid":"'.$aid.'",';
                    $data .= '"uid":"'.$uid.'"';
                    break;

                case 1:
                	$msg .= "The file is bigger than this PHP installation allows";
                    $data .= '"message":"'.$msg.'"';
                    break;

                case 2:
                	$msg .= "The file is bigger than this form allows";
                    $data .= '"message":"'.$msg.'"';
                    break;

                case 3:
                	$msg .= "Only part of the file was uploaded";
                    $data .= '"message":"'.$msg.'"';
                    break;

                case 4:
                	$msg .= "No file was uploaded";
                    $data .= '"message":"'.$msg.'"';
                    break;

                case 6:
                	$msg .= "Missing a temporary folder";
                    $data .= '"message":"'.$msg.'"';
                    break;

                case 7:
                	$msg .= "Failed to write file to disk";
                    $data .= '"message":"'.$msg.'"';
                    break;

                case 8:
                	$msg .= "File upload stopped by extension";
                    $data .= '"message":"'.$msg.'"';
                    break;

                default:
                	$msg .= "Unknown error ".$_FILES['filename']['error'];
                    $data .= '"message":"'.$msg.'",';
                    break;
            }
            $data .= "}";
            echo $data;
        }
    }


	public function editcomment()
	{

		$comment_id    = $this->input->get('id', null);
		$comment_title = $this->input->get('title', null, 'string');
		$comment_text  = $this->input->get('text', null, 'string');

		$m_application = $this->getModel('Application');

        $comment = $m_application->getComment($comment_id);

        $uid = $comment['user_id'];

		if ($uid == $this->_user->id && EmundusHelperAccess::asAccessAction(10, 'c', $this->_user->id, $comment['fnum'])) {
            $result = $m_application->editComment($comment_id, $comment_title, $comment_text);

            if ($result)
                $msg = JText::_('COM_EMUNDUS_COMMENTS_COMMENT_EDITED');
            else
                $msg = JTEXT::_('COM_EMUNDUS_ERROR_COMMENT_EDIT');

            $tab = array('status' => $result, 'msg' => $msg);
        }
        else{
			if (EmundusHelperAccess::asAccessAction(10, 'u', $this->_user->id, $comment['fnum'])) {
                $result = $m_application->editComment($comment_id, $comment_title, $comment_text);

                if ($result)
                    $msg = JText::_('COM_EMUNDUS_COMMENTS_COMMENT_EDITED');
                else
                    $msg = JTEXT::_('COM_EMUNDUS_ERROR_COMMENT_EDIT');

                $tab = array('status' => $result, 'msg' => $msg);
            }
            else{
                $tab = array('status' => false, 'msg' => JText::_("ACCESS_DENIED"));
            }
        }


        echo json_encode((object)$tab);
        exit;
    }


	public function deletecomment()
	{
		$comment_id = $this->input->get('comment_id', null, 'GET');

		$m_application = $this->getModel('Application');

        $comment = $m_application->getComment($comment_id);

        $uid = $comment['user_id'];

		if ($uid == $this->_user->id && EmundusHelperAccess::asAccessAction(10, 'c', $this->_user->id, $comment['fnum'])) {
            $result = $m_application->deleteComment($comment_id, $comment['fnum']);
            $tab = array('status' => $result, 'msg' => JText::_('COM_EMUNDUS_COMMENTS_DELETED'));

        }else{
			if (EmundusHelperAccess::asAccessAction(10, 'd', $this->_user->id, $comment['fnum'])) {
                $result = $m_application->deleteComment($comment_id, $comment['fnum']);
                $tab = array('status' => $result, 'msg' => JText::_('COM_EMUNDUS_COMMENTS_DELETED'));

            }else{
                $tab = array('status' => false, 'msg' => JText::_("ACCESS_DENIED"));

            }
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function deletetag(){
		$response = array('status' => 0, 'msg' => JText::_('TAG_DELETE_ERROR'));


		if (empty($this->_user->id)) {
			$response['msg'] = JText::_('ACCESS_DENIED');
		} else {

			$id_tag = $this->input->getInt('id_tag', 0);
			$fnum   = $this->input->getString('fnum', '');

			if (!empty($fnum) && $id_tag > 0) {
				$m_application = $this->getModel('Application');
				$m_files       = $this->getModel('Files');

				$tags = $m_files->getTagsByIdFnumUser($id_tag, $fnum, $this->_user->id);

				if (EmundusHelperAccess::asAccessAction(14, 'd', $this->_user->id, $fnum)) {
					$result = $m_application->deleteTag($id_tag, $fnum);

					if ($result == 1 || $result) {
						$response = array('status' => $result, 'msg' => JText::_('COM_EMUNDUS_TAGS_DELETED'));
					}
				} else if ($tags) {
					$result = $m_application->deleteTag($id_tag, $fnum, $this->_user->id);

					if ($result == 1 || $result) {
						$response = array('status' => $result, 'msg' => JText::_('COM_EMUNDUS_TAGS_DELETED'));
					}
				} else {
					$response = array('status' => 0, 'msg' => JText::_('ACCESS_DENIED'));
				}
			}
		}

        echo json_encode((object)$response);
        exit;
    }

	public function deletetraining()
	{

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			die(JText::_("ACCESS_DENIED"));
		}

		$id    = $this->input->get('id', null, 'GET');
		$sid   = $this->input->get('sid', null, 'GET');
		$table = $this->input->get('t', null, 'GET');

		$m_application = $this->getModel('Application');
        $result = $m_application->deleteData($id, $table);

        $row['applicant_id'] = $sid;
		$row['user_id']      = $this->_user->id;
        $row['reason'] = JText::_('COM_EMUNDUS_APPLICATION_DATA_DELETED');
        $row['comment_body'] = JText::_('COM_EMUNDUS_APPLICATION_LINE').' '.$id.' '.JText::_('COM_EMUNDUS_APPLICATION_FROM').' '.$table;
        $m_application->addComment($row);

        echo $result;
    }

	public function getapplicationmenu()
	{

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            die(JText::_("ACCESS_DENIED"));
		}

		$fnum   = $this->input->get('fnum', null, 'STRING');

		$m_application = $this->getModel('Application');
        $menus = $m_application->getApplicationMenu();
        $res = false;

		if (EmundusHelperAccess::asAccessAction(1, 'r', $this->_user->id, $fnum)) {
            if ($menus !== false) {
                $res = true;
                $menu_application = array();
                $i=0;

                foreach($menus as $k => $menu) {
					$access = false;
					$actions_for_access = explode(',', $menu['note']);
					
					foreach ($actions_for_access as $action_for_access) {
						$action = explode('|', $action_for_access);
						$action_id = $action[0];
						
						if (EmundusHelperAccess::asAccessAction($action[0], $action[1], $this->_user->id, $fnum)) {
							$access = true;
							break;
						}
					}

                    if ($access) {
                        if($action_id == 36){
							$messenger     = $this->getModel('Messenger');

                            $notifications = $messenger->getNotificationsByFnum($fnum);
                            if($notifications > 0) {
                                $menu['notifications'] = $messenger->getNotificationsByFnum($fnum);
                            }
                        }
                        if($action_id == 10){
							$m_files                = $this->getModel('Files');
                            $notifications_comments = sizeof($m_files->getCommentsByFnum([$fnum]));
                            $menu['notifications'] = $notifications_comments;
                        }

                        $menu_application[] = $menu;
                        if ((intval($menu['rgt']) - intval($menu['lft'])) == 1) {
                            $menu_application[$i++]['hasSons'] = false;
                        }
                        else {
                            $menu_application[$i++]['hasSons'] = true;
                        }
                    }

                }
            }
            $tab = array('status' => $res, 'menus' => $menu_application);
        }
        else {
            $tab = array('status' => false, 'msg' => JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function deleteattachement()
    {

		$fnum   = $this->input->getString('fnum', null);
		$ids    = $this->input->getString('ids', null);
        $ids = json_decode(stripslashes($ids));
        $res = new stdClass();
        if(EmundusHelperAccess::asAccessAction(4 ,'d', JFactory::getUser()->id, $fnum))
        {
			$m_application = $this->getModel('Application');
            foreach($ids as $id)
            {
                $m_application->deleteAttachment($id);
            }

            // TRACK THE LOGS
            # get fnum                  $fnum
            # get the logged user id    JFactory::getUser()->id
            # get the applicant id      $applicant_id
            # $applicant_id = $jinput->getString('student_id', null);

            require_once(JPATH_SITE.'/components/com_emundus/models/logs.php');
            $user = $this->app->getSession()->get('emundusUser');     # logged user #

            require_once(JPATH_SITE.'/components/com_emundus/models/files.php');
            $mFile = new EmundusModelFiles();
            $applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

            $res->status = true;
        }
        else
        {
            $res->status = false;
            $res->msg = JText::_("ACCESS_DENIED");
        }
        echo json_encode($res);
        exit();
    }

    public function exportpdf()
    {

		$fnum   = $this->input->post->getString('fnum', null);

	    if (!empty($fnum) && EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $fnum)) {
			$ids              = $this->input->post->getString('ids', null);
			$sid              = $this->input->post->getInt('student_id', null);
			$form_post        = $this->input->post->getVar('forms', null);
			$attachments_only = $this->input->post->getBool('attachments_only', false);
			
			$m_form    = $this->getModel('Form');
			$m_profile = $this->getModel('Profile');
			$m_files   = $this->getModel('Files');

	        $fnumInfos = $m_files->getFnumInfos($fnum);
	        $profile = $m_profile->getProfileByCampaign($fnumInfos['campaign_id']);

	        if (!$attachments_only) {
	            if(empty($form_post)){
	                $form_post = array();

	                $forms = $m_form->getFormsByProfileId($profile['profile_id']);
	                foreach ($forms as $form){
	                    if(!in_array($form->id,$form_post)){
	                        $form_post[] = $form->id;
	                    }
	                }
	            }
	        }

	        if(empty($ids)){
	            $ids = array();
				$m_application = $this->getModel('Application');

	            $profile = $m_profile->getProfileByCampaign($fnumInfos['campaign_id']);
	            $attachments_by_profile = $m_form->getDocumentsByProfile($profile['profile_id']);
	            $aids_allowed = array();
	            foreach($attachments_by_profile as $attachment){
	                $aids_allowed[] =  $attachment->attachment_id;
	            }
	            $attachments = $m_application->getAttachmentsByFnum($fnum);
	            foreach($attachments as $attachment){
	                if(in_array($attachment->attachment_id,$aids_allowed)) {
	                    $ids[] = $attachment->id;
	                }
	            }
	        }

            $exports = array();
            $tmpArray = array();

			if ($form_post) {
				$fnumInfos = $m_files->getFnumInfos($fnum);

                $exports[] = EmundusHelperExport::buildFormPDF($fnumInfos, $sid, $fnum, 1);
            }

			$m_application = $this->getModel('Application');
            $files = $m_application->getAttachments($ids);

            $isNotOnlyApplicantionForms = EmundusHelperExport::getAttachmentPDF($exports, $tmpArray, $files, $sid);


	        if (!$isNotOnlyApplicantionForms) {
		        $res = new stdClass();
		        $res->status = false;
		        $res->msg = JText::_('COM_EMUNDUS_EXPORTS_CANNOT_EXPORT_FILETYPE');
	        }
			elseif (!empty($exports)) {
                require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'fpdi.php');
                require_once (JPATH_LIBRARIES . '/emundus/vendor/autoload.php');
                $pdf = new ConcatPdf();
                $pdf->setFiles($exports);
                $pdf->concat();
                foreach($tmpArray as $fn)
                {
                    unlink($fn);
                }
                $pdf->Output(EMUNDUS_PATH_ABS.$sid.DS.$fnum.'_attachments.pdf', 'F');
                $res = new stdClass();
                $res->status = true;
                $res->link = JURI::base().EMUNDUS_PATH_REL.$sid.'/'.$fnum.'_attachments.pdf';

            }
            else
            {
                $res = new stdClass();
                $res->status = false;
                $res->msg = JText::_('COM_EMUNDUS_ATTACHMENTS_FILES_NOT_FOUND_IN_SERVER');
            }
        }
        else
        {
            $res = new stdClass();
            $res->status = false;
            $res->msg = JText::_('ACCESS_DENIED');
        }
	    echo json_encode($res);
	    exit();
    }

    public function updateaccess()
    {

		$fnum   = $this->input->getString('fnum', null);

		if (EmundusHelperAccess::asAccessAction(11, 'u', $this->_user->id, $fnum)) {
			$state         = $this->input->getInt('state', null);
			$accessid      = explode('-', $this->input->getString('access_id', null));
			$type          = $this->input->getString('type', null);
			$m_application = $this->getModel('Application');
            $res = new stdClass();
            if($type == 'groups')
            {
                $res->status = $m_application->updateGroupAccess($fnum, $accessid[0], $accessid[1], $accessid[2], $state);
            }
            else
            {
                $res->status = $m_application->updateUserAccess($fnum, $accessid[0], $accessid[1], $accessid[2], $state);
            }
            echo json_encode($res);
            exit();
        }
        else
        {
            $res = new stdClass();
            $res->status = false;
            $res->msg = JText::_('YOU_ARE_NOT_ALLOWED_TO_DO_THAT');
            echo json_encode($res);
            exit();
        }
    }

    public function deleteaccess()
    {

		$fnum   = $this->input->getString('fnum', null);

		if (EmundusHelperAccess::asAccessAction(11, 'd', $this->_user->id, $fnum)) {
			$id            = $this->input->getString('id', null);
			$type          = $this->input->getString('type', null);
			$m_application = $this->getModel('Application');
            $res = new stdClass();
            if($type == 'groups')
            {
                $res->status = $m_application->deleteGroupAccess($fnum, $id);
            }
            else
            {
                $res->status = $m_application->deleteUserAccess($fnum, $id);
            }
            echo json_encode($res);
            exit();
        }
        else
        {
            $res = new stdClass();
            $res->status = false;
            $res->msg = JText::_('YOU_ARE_NOT_ALLOWED_TO_DO_THAT');
            echo (object) json_encode(array($res));
            exit();
        }
    }
    public function attachment_validation()
    {
		$fnum          = $this->input->getString('fnum', null);
		$att_id        = $this->input->getVar('att_id', null);
		$state         = $this->input->getVar('state', null);
		$m_application = $this->getModel('Application');
        $res = new stdClass();

        if(EmundusHelperAccess::asAccessAction(4, 'c', JFactory::getUser()->id, $fnum))
        {
            $res->status = $m_application->attachment_validation($att_id, $state);
            echo json_encode($res);
            exit();
        }
        else
        {
            $res->msg = JText::_('YOU_ARE_NOT_ALLOWED_TO_DO_THAT');
            exit();
        }
    }

    /////////////////////////////////////////////////////////////
    // used by VueJS com_emundus Attachments component

    public function getuserattachments()
    {
		$m_application = $this->getModel('Application');

		$user_id = $this->input->getInt('user_id', null);

        $attachments = $m_application->getUserAttachments($user_id);

        echo json_encode($attachments);
        exit;
    }

    public function getattachmentsbyfnum()
    {
	    $response = ['msg' => JText::_('ACCESS_DENIED'), 'status' => false, 'code' => 403];
		$fnum   = $this->input->getString('fnum', '');

		if (!empty($fnum)) {
			if (EmundusHelperAccess::asAccessAction(4, 'r', $this->_user->id, $fnum)) {
				$m_application = $this->getModel('Application');

                $response['attachments'] = $m_application->getUserAttachmentsByFnum($fnum);
				$response['msg'] = JText::_('SUCCESS');
				$response['status'] = true;
				$response['code'] = 200;
			}
		}

        echo json_encode($response);
        exit;
    }

    public function updateattachment()
    {
        $update = false;
        $msg = '';

        // get post data

		$data   = $this->input->post->getArray();

        if (EmundusHelperAccess::asAccessAction(4, 'u', JFactory::getUser()->id, $data['fnum'])) {
			$m_application = $this->getModel('Application');

			if ($this->input->files->get('file')) {
				$data['file'] = $this->input->files->get('file');
            }

            if ($data['fnum'] && $data['user']) {
                $update = $m_application->updateAttachment($data);
			}
			else {
                $msg = JText::_('INVALID_PARAMETERS');
            }
        } else {
           $msg = JText::_('ACCESS_DENIED');
        }

        echo json_encode(array('status' => $update, 'msg' => $msg));
        exit;
    }

	public function getform()
	{
		$profile = $this->input->getInt('profile', null);
		$user    = $this->input->getInt('user', null);
		$fnum    = $this->input->getString('fnum', null);

		if (EmundusHelperAccess::asAccessAction(1, 'r', $this->_user->id, $fnum)) {
			$m_application = $this->getModel('Application');

            $form = $m_application->getForms($user, $fnum, $profile);
            if (!empty($form)) {
                $tab = array('status' => true, 'msg' => JText::_('FORM_RETRIEVED'), 'data' => $form);
            } else {
                $tab = array('status' => false, 'msg' => JText::_('FORM_NOT_RETRIEVED'), 'data' => null);
            }
        } else {
            $tab = array('status' => false, 'msg' => JText::_('RESTRICTED_ACCESS'));
        }

        echo json_encode($tab);
        exit;
    }


    public function getattachmentpreview()
    {
	    $response = ['status' => false, 'msg' => JText::_('ACCESS_DENIED')];

		if (EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$m_application = $this->getModel('Application');

			$user     = $this->input->getInt('user', null);
			$filename = $this->input->getString('filename', null);

			if (!empty($filename) && !empty($user)) {
				$response = $m_application->getAttachmentPreview($user, $filename);
			}
		}

        echo json_encode($response);
        exit;
    }

    public function reorderapplications()
    {
        $response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

        $emundusUser = JFactory::getSession()->get('emundusUser');
        $emundusUserFnums = array_keys($emundusUser->fnums);

		$fnum_from    = $this->input->getString('fnum_from', '');
		$fnum_to      = $this->input->getString('fnum_to', '');
		$order_column = $this->input->getString('order_column', 'ordering');

		if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id) || (in_array($fnum_from, $emundusUserFnums) && in_array($fnum_to, $emundusUserFnums))) {
			$m_application = $this->getModel('Application');
            $reordered = $m_application->invertFnumsOrderByColumn($fnum_from, $fnum_to, $order_column);

            $response['status'] = $reordered;
            $response['msg'] =  $reordered ? JText::_('SUCCESS') : JText::_('FAILED');
        }

        echo json_encode($response);
        exit;
    }

	public function createtab(){
		$response = array('tab' => 0, 'msg' => JText::_('FAILED'));

		if (!empty($this->_user->id)) {
			$tab_name = $this->input->getString('name', '');

			if (!empty($tab_name)) {
				$m_application = $this->getModel('Application');
				$tab_created   = $m_application->createTab($tab_name, $this->_user->id);

				$response['tab'] = $tab_created;
				$response['msg'] =  $tab_created ? JText::_('SUCCESS') : JText::_('FAILED');
			}
		}

		echo json_encode($response);
		exit;
	}

	public function gettabs(){
		$response = array('tabs' => array());

		if (!empty($this->_user->id)) {
			$m_application = $this->getModel('Application');
			$response['tabs'] = $m_application->getTabs($this->_user->id);
		}

		echo json_encode($response);
		exit;
	}

	public function updatetabs(){
		$response = array('msg' =>  JText::_('FAILED'));

		if (!empty($this->_user->id)) {

			$tabs   = $this->input->getRaw('tabs');
			$tabs = json_decode($tabs);

			if (!empty($tabs)) {
				$m_application = $this->getModel('Application');
				$response['updated'] = $m_application->updateTabs($tabs, $this->_user->id);
				$response['msg'] =  $response['updated'] ? JText::_('SUCCESS') : JText::_('FAILED');
			}
		}

		echo json_encode($response);
		exit;
	}

	public function deletetab(){
		$response = array('msg' =>  JText::_('FAILED'));

		if (!empty($this->_user->id)) {

			$tab    = $this->input->getInt('tab');

			if (!empty($tab)) {
				$m_application = $this->getModel('Application');
				$response['deleted'] = $m_application->deleteTab($tab, $this->_user->id);
				$response['msg'] =  $response['deleted'] ? JText::_('SUCCESS') : JText::_('FAILED');
			}
		}

		echo json_encode($response);
		exit;
	}

	public function copyfile(){
		$response = array('status' => 0, 'msg' => '');

		$fnum     = $this->input->getString('fnum');
		$campaign = $this->input->getString('campaign');

		if(!empty($fnum) && !empty($campaign)){
			$m_files = $this->getModel('Files');
			$fnumInfos = $m_files->getFnumInfos($fnum);

			if ($fnumInfos['applicant_id'] !== $this->_user->id) {
				$response['msg'] = JText::_('ACCESS_DENIED');
			} else {
				$fnum_to = $m_files->createFile($campaign,$fnumInfos['applicant_id']);

				if(!empty($fnum_to)) {
					$m_application      = $this->getModel('Application');
					$response['status'] = $m_application->copyFile($fnum, $fnum_to);
					$response['first_page'] = 'index.php?option=com_emundus&task=openfile&fnum=' . $fnum_to;
				}
				$response['msg'] =  $response['status'] ? JText::_('SUCCESS') : JText::_('FAILED');
			}
		}

		echo json_encode($response);
		exit;
	}

	public function movetotab(){
		$response = array('status' => 0, 'msg' => '');

		$fnum   = $this->input->getString('fnum');
		$tab    = $this->input->getString('tab');

		if(!empty($tab) && !empty($fnum)){
			$m_files = $this->getModel('Files');
			$fnumInfos = $m_files->getFnumInfos($fnum);

			if ($fnumInfos['applicant_id'] !== $this->_user->id) {
				$response['msg'] = JText::_('ACCESS_DENIED');
			} else {
				$m_application      = $this->getModel('Application');
				$response['status'] = $m_application->moveToTab($fnum, $tab);

				$response['msg'] =  $response['status'] ? JText::_('SUCCESS') : JText::_('FAILED');
			}
		}

		echo json_encode($response);
		exit;
	}

	public function renamefile(){
		$response = array('status' => 0, 'msg' => '');

		$fnum     = $this->input->getString('fnum');
		$new_name = $this->input->getString('new_name');

		if(!empty($fnum)){
			$m_files = $this->getModel('Files');
			$fnumInfos = $m_files->getFnumInfos($fnum);

			if ($fnumInfos['applicant_id'] !== $this->_user->id) {
				$response['msg'] = JText::_('ACCESS_DENIED');
			} else {
				$m_application      = $this->getModel('Application');
                try {
                    $response['status'] = $m_application->renameFile($fnum, $new_name);
                    $response['msg'] =  $response['status'] ? JText::_('SUCCESS') : JText::_('FAILED');
                } catch (Exception $e) {
                    $response['msg'] = $e->getMessage();
                }
			}
		}

		echo json_encode($response);
		exit;
	}

	public function getcampaignsavailableforcopy(){
		$response = array('status' => 0, 'msg' => '');

		$fnum   = $this->input->getString('fnum');

		if(!empty($fnum)){
			$m_files = $this->getModel('Files');
			$fnumInfos = $m_files->getFnumInfos($fnum);

			if ($fnumInfos['applicant_id'] !== $this->_user->id) {
				$response['msg'] = JText::_('ACCESS_DENIED');
			} else {
				$m_application      = $this->getModel('Application');
				$response['campaigns'] = $m_application->getCampaignsAvailableForCopy($fnum);

				$response['msg'] =  !empty($response['campaigns']) ? JText::_('SUCCESS') : JText::_('FAILED');
			}
		}

		echo json_encode($response);
		exit;
	}

	public function filterapplications(){
		$response = array('status' => 1, 'msg' => JText::_('SUCCESS'));

		$type   = $this->input->getString('type');
		$value  = $this->input->getString('value');

		if(!empty($type) && !empty($value) && in_array($type,['applications_order_by','applications_filter_by'])){
			JFactory::getSession()->set($type,$value);
		}
		elseif (empty($value)){
			JFactory::getSession()->clear($type);
		}
		else {
			$response = array('status' => 0, 'msg' => JText::_('FAILED'));
		}

		echo json_encode($response);
		exit;
	}

	public function applicantcustomaction() {
		$response = ['status' => false, 'msg' => JText::_('ACCESS_DENIED'), 'code' => 403];

		if (EmundusHelperAccess::isApplicant($this->_user->id)) {
			$action        = $this->input->getString('action', '');
			$fnum          = $this->input->getString('fnum', '');
			$module_id     = $this->input->getInt('module_id', 0);
			$fnum_filtered = preg_replace('/[^0-9]/', '', $fnum);

			if ($fnum_filtered === $fnum) {
				if (!empty($action) && !empty($fnum)) {
					require_once JPATH_ROOT.'/components/com_emundus/helpers/files.php';
					$h_files = new EmundusHelperFiles;
					$fnums              = $h_files->getApplicantFnums($this->_user->id);
					$current_user_fnums = array_keys($fnums);

					if (in_array($fnum, $current_user_fnums)) {
						$m_application      = $this->getModel('Application');
						$response['status'] = $m_application->applicantCustomAction($action, $fnum, $module_id);
						$response['code'] = 200;

						if ($response['status']) {
							$response['msg'] = JText::_('SUCCESS');
						} else {
							$response['msg'] = JText::_('FAILED');
						}
					} else {
						$response['msg'] = JText::_('INVALID_PARAMETERS');
						$response['code'] = 400;
					}
				}
			} else {
				// Log invalid fnum and ip address, to prevent brute force attacks
				$ip = $_SERVER['REMOTE_ADDR'];
				JLog::add('Call to custom action on Invalid fnum: ' . $fnum . ' from ip: ' . $ip, JLog::WARNING, 'com_emundus');
			}
		}

		header('Content-Type: application/json');
		header('HTTP/1.1 ' . $response['code'] . ' ' . $response['msg']);
		echo json_encode($response);
		exit;
	}
}

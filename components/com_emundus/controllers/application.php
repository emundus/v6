<?php
/**
 * @version     $Id: application.php 750 2012-01-23 22:29:38Z brivalland $
 * @package     Joomla
 * @copyright   (C) 2016 eMundus LLC. All rights reserved.
 * @license     GNU General Public License
 */
use \setasign\Fpdi\Fpdi;
// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS') );
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');

/**
 * Custom report controller
 * @package     Emundus
 */
class EmundusControllerApplication extends JControllerLegacy
{
    public function display($cachable = false, $urlparams = false) {
        // Set a default view if none exists
        if ( ! JRequest::getCmd( 'view' ) ) {
            $default = 'application_form';
            JRequest::setVar('view', $default );
        }
        parent::display();
    }

    /**
     * Delete an applicant attachment(s)
     */
    public function delete_attachments() {
        $user = JFactory::getUser();
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id))
            die(JText::_("ACCESS_DENIED"));

        $attachments = JRequest::getVar('attachments', null, 'POST', 'array', 0);
        $user_id     = JRequest::getVar('sid', null, 'POST', 'none', 0);
        $view        = JRequest::getVar('view', null, 'POST', 'none', 0);
        $tmpl        = JRequest::getVar('tmpl', null, 'POST', 'none', 0);

        $url = !empty($tmpl)?'index.php?option=com_emundus&view='.$view.'&sid='.$user_id.'&tmpl='.$tmpl.'#attachments':'index.php?option=com_emundus&view='.$view.'&sid='.$user_id.'&tmpl=component#attachments';
        JArrayHelper::toInteger($attachments, 0);
        if (count($attachments) == 0) {
            JError::raiseWarning( 500, JText::_( 'COM_EMUNDUS_ERROR_NO_ITEMS_SELECTED' ) );
            exit;
        }

        $m_application = $this->getModel('application');

        foreach ($attachments as $id) {
            $upload = $m_application->getUploadByID($id);
            $attachment = $m_application->getAttachmentByID($upload['attachment_id']);
            if (EmundusHelperAccess::asAccessAction(4, 'd', $user->id, $upload['fnum']) ) {
                $result = $m_application->deleteAttachment($id);

                if ($result != 1) {
                    echo JText::_('ATTACHMENT_DELETE_ERROR').' : '.$attachment['value'].' : '.$upload['filename'];
                } else {
                    $file = EMUNDUS_PATH_ABS.$user_id.DS.$upload['filename'];
                    @unlink($file);

                    $row['applicant_id'] = $upload['user_id'];
                    $row['user_id'] = $user->id;
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
    public function delete_attachment() {
        $user = JFactory::getUser();

        if(!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) die(JText::_("ACCESS_DENIED"));

        $id = JRequest::getVar('id', null, 'GET', 'none',0);

        $m_application = $this->getModel('application');
        $upload = $m_application->getUploadByID($id);
        $attachment = $m_application->getAttachmentByID($upload['attachment_id']);

        if( EmundusHelperAccess::asAccessAction(4, 'd', $user->id, $upload['fnum']) ) {
            $result = $m_application->deleteAttachment($id);

            if($result != 1){
                echo JText::_('ATTACHMENT_DELETE_ERROR');
            } else {
                $row['applicant_id'] = $upload['user_id'];
                $row['user_id'] = $user->id;
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

        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
	        die(JText::_("ACCESS_DENIED"));
        }

        $aid = JRequest::getVar('attachment_id', null, 'POST', 'none',0);
        $uid = JRequest::getVar('uid', null, 'POST', 'none',0);
        $filename = JRequest::getVar('filename', null, 'POST', 'none',0);
        $campaign_id = JRequest::getVar('campaign_id', null, 'POST', 'none',0);
        $can_be_viewed = JRequest::getVar('can_be_viewed', null, 'POST', 'none',0);
        $can_be_deleted = JRequest::getVar('can_be_deleted', null, 'POST', 'none',0);

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
                        $m_application = $this->getModel('application');
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


    public function editcomment() {
        $user   = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;

        $comment_id     = $jinput->get('id', null);
        $comment_title  = $jinput->get('title', null, 'string');
        $comment_text   = $jinput->get('text', null, 'string');

        $m_application = $this->getModel('application');

        $comment = $m_application->getComment($comment_id);

        $uid = $comment['user_id'];

        if($uid == $user->id && EmundusHelperAccess::asAccessAction(10, 'c', $user->id, $comment['fnum'])){
            $result = $m_application->editComment($comment_id, $comment_title, $comment_text);

            if ($result)
                $msg = JText::_('COM_EMUNDUS_COMMENTS_COMMENT_EDITED');
            else
                $msg = JTEXT::_('COM_EMUNDUS_ERROR_COMMENT_EDIT');

            $tab = array('status' => $result, 'msg' => $msg);
        }
        else{
            if (EmundusHelperAccess::asAccessAction(10, 'u', $user->id, $comment['fnum'])) {
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


    public function deletecomment(){
        $user = JFactory::getUser();
        $comment_id = JRequest::getVar('comment_id', null, 'GET', 'none',0);
        $view = JRequest::getVar('view', null, 'GET', 'none',0);
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);

        $m_application = $this->getModel('application');

        $comment = $m_application->getComment($comment_id);

        $uid = $comment['user_id'];

        if($uid == $user->id && EmundusHelperAccess::asAccessAction(10, 'c', $user->id, $comment['fnum'])){
            $result = $m_application->deleteComment($comment_id, $comment['fnum']);
            $tab = array('status' => $result, 'msg' => JText::_('COM_EMUNDUS_COMMENTS_DELETED'));

        }else{
            if(EmundusHelperAccess::asAccessAction(10, 'd', $user->id, $comment['fnum'])){
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
        $user = JFactory::getUser();
        $id_tag = JRequest::getVar('id_tag', null, 'GET', 'none',0);
        $fnum = JRequest::getVar('fnum', null, 'GET', 'none',0);

        $m_application = $this->getModel('application');
        $m_files = $this->getModel('files');

        $tags = $m_files->getTagsByIdFnumUser($id_tag, $fnum, $user->id);
        if($tags){
            $result = $m_application->deleteTag($id_tag, $fnum);
            if($result!=1 && $result!=true)
                $tab = array('status' => $result, 'msg' => JText::_('TAG_DELETE_ERROR'));
            else
                $tab = array('status' => $result, 'msg' => JText::_('COM_EMUNDUS_TAGS_DELETED'));
        }else{
            if(EmundusHelperAccess::asAccessAction(14, 'd', $user->id, $fnum))
            {
                $result = $m_application->deleteTag($id_tag, $fnum);
                if($result!=1 && $result!=true)
                    $tab = array('status' => $result, 'msg' => JText::_('TAG_DELETE_ERROR'));
                else
                    $tab = array('status' => $result, 'msg' => JText::_('COM_EMUNDUS_TAGS_DELETED'));
            } else{
                $result = 0;
                $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
            }
        }


        echo json_encode((object)$tab);
        exit;
    }

    public function deletetraining(){
        $user = JFactory::getUser();

        if(!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) die(JText::_("ACCESS_DENIED"));

        $view = JRequest::getVar('view', null, 'GET', 'none',0);
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);

        $id = JRequest::getVar('id', null, 'GET', 'none',0);
        $sid = JRequest::getVar('sid', null, 'GET', 'none',0);
        $table = JRequest::getVar('t', null, 'GET', 'none',0);

        $m_application = $this->getModel('application');
        $result = $m_application->deleteData($id, $table);

        $row['applicant_id'] = $sid;
        $row['user_id'] = $user->id;
        $row['reason'] = JText::_('COM_EMUNDUS_APPLICATION_DATA_DELETED');
        $row['comment_body'] = JText::_('COM_EMUNDUS_APPLICATION_LINE').' '.$id.' '.JText::_('COM_EMUNDUS_APPLICATION_FROM').' '.$table;
        $m_application->addComment($row);

        echo $result;
    }
    /*
     * Get Menu for application file
     */
    public function getapplicationmenu() {

        $user = JFactory::getUser();
        if(!EmundusHelperAccess::asPartnerAccessLevel($user->id))
            die(JText::_("ACCESS_DENIED"));

        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->get('fnum', null, 'STRING');

        $m_application = $this->getModel('Application');
        $menus = $m_application->getApplicationMenu();
        $res = false;

        if(EmundusHelperAccess::asAccessAction(1, 'r', $user->id, $fnum)) {
            if ($menus !== false) {
                $res = true;
                $menu_application = array();
                $i=0;

                foreach($menus as $k => $menu) {
                    $action = explode('|', $menu['note']);
                    if (EmundusHelperAccess::asAccessAction($action[0], $action[1], $user->id, $fnum)) {
                        if($action[0] == 36){
                            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'messenger.php');

                            $messenger = new EmundusModelMessenger;
                            $notifications = $messenger->getNotificationsByFnum($fnum);
                            if($notifications > 0) {
                                $menu['notifications'] = $messenger->getNotificationsByFnum($fnum);
                            }
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
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);
        $ids = $jinput->getString('ids', null);
        $ids = json_decode(stripslashes($ids));
        $res = new stdClass();
        if(EmundusHelperAccess::asAccessAction(4 ,'d', JFactory::getUser()->id, $fnum))
        {
            $m_application = $this->getModel('application');
            foreach($ids as $id)
            {
                $m_application->deleteAttachment($id);
            }

            // TRACK THE LOGS
            # get fnum                  $fnum
            # get the logged user id    JFactory::getUser()->id
            # get the applicant id      $applicant_id
            # $applicant_id = $jinput->getString('student_id', null);

            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
            $user = JFactory::getSession()->get('emundusUser');     # logged user #

            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
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
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->post->getString('fnum', null);

	    if (!empty($fnum) && EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $fnum)) {
	        $ids = $jinput->post->getString('ids', null);
	        $sid = $jinput->post->getInt('student_id', null);
	        $form_post = $jinput->post->getVar('forms', null);
	        $attachments_only = $jinput->post->getBool('attachments_only', false);

	        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
	        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
	        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'form.php');
	        $m_form = new EmundusModelForm;
	        $m_profile = new EmundusModelProfile;
	        $m_files = new EmundusModelFiles;

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
	            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
	            $m_application = new EmundusModelApplication;

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

            if($form_post)
            {
                $m_Files = $this->getModel('files');
                $fnumInfos = $m_Files->getFnumInfos($fnum);

                $exports[] = EmundusHelperExport::buildFormPDF($fnumInfos, $sid, $fnum, 1);
            }

            $m_application = $this->getModel('application');
            $files = $m_application->getAttachments($ids);

            $isNotOnlyApplicantionForms = EmundusHelperExport::getAttachmentPDF($exports, $tmpArray, $files, $sid);
            if (!$isNotOnlyApplicantionForms) {
	            $res = new stdClass();
	            $res->status = false;
	            $res->msg = JText::_('COM_EMUNDUS_EXPORTS_CANNOT_EXPORT_FILETYPE');
	            echo json_encode($res);
	            exit();
            }


            if (!empty($exports)) {
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
                echo json_encode($res);
                exit();

            }
            else
            {
                $res = new stdClass();
                $res->status = false;
                $res->msg = JText::_('COM_EMUNDUS_ATTACHMENTS_FILES_NOT_FOUND_IN_SERVER');
                echo json_encode($res);
                exit();
            }
        }
        else
        {
            $res = new stdClass();
            $res->status = false;
            $res->msg = JText::_('ACCESS_DENIED');
            echo json_encode($res);
            exit();
        }
        exit();
    }

    public function updateaccess()
    {
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);
        if(EmundusHelperAccess::asAccessAction(11, 'u', JFactory::getUser()->id, $fnum))
        {
            $state = $jinput->getInt('state', null);
            $accessid = explode('-', $jinput->getString('access_id', null));
            $type = $jinput->getString('type', null);
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
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);
        if(EmundusHelperAccess::asAccessAction(11, 'd', JFactory::getUser()->id, $fnum))
        {
            $id =  $jinput->getString('id', null);
            $type = $jinput->getString('type', null);
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
            echo (object) json_encode(array());
            exit();
        }
    }
    public function attachment_validation()
    {
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);
        $att_id = $jinput->getVar('att_id', null);
        $state = $jinput->getVar('state', null);
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

        $jinput = JFactory::getApplication()->input;

        $user_id = $jinput->getInt('user_id', null);

        $attachments = $m_application->getUserAttachments($user_id);

        echo json_encode($attachments);
        exit;
    }

    public function getattachmentsbyfnum()
    {
	    $response = ['status' => false, 'code' => 403, 'msg' => JText::_('BAD_REQUEST'), 'attachments' => null];
        $m_application = $this->getModel('Application');
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', '');

		if (!empty($fnum)) {
			$response['msg'] = JText::_('ACCESS_DENIED');

			if (EmundusHelperAccess::asAccessAction(4, 'r', JFactory::getUser()->id, $fnum)) {
				$response['attachments'] = $m_application->getUserAttachmentsByFnum($fnum, NULL);

				if ($response['attachments']) {
					$response['msg'] = JText::_('SUCCESS');
					$response['status'] = true;
					$response['code'] = 200;
				} else {
					$response['msg'] = JText::_('FAIL');
					$response['code'] = 500;
				}
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
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->post->getArray();

        if (EmundusHelperAccess::asAccessAction(4, 'u', JFactory::getUser()->id, $data['fnum'])) {
            $m_application = $this->getModel('Application');

            if ($jinput->files->get('file')) {
                $data['file'] = $jinput->files->get('file');
            }

            if ($data['fnum'] && $data['user']) {
                $update = $m_application->updateAttachment($data);

                # get logged user id
                # get application id
                # get fnum

                # GET FNUM INFO
                require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
                $mFile = new EmundusModelFiles();
                $applicant_id = ($mFile->getFnumInfos($data['fnum']))['applicant_id'];

            } else {
                $msg = JText::_('INVALID_PARAMETERS');
            }
        } else {
           $msg = JText::_('ACCESS_DENIED');
        }

        echo json_encode(array('status' => $update, 'msg' => $msg));
        exit;
    }

    public function getform() {
        $jinput = JFactory::getApplication()->input;
        $current_user = JFactory::getUser();

        $profile = $jinput->getInt('profile', null);
        $user = $jinput->getInt('user', null);
        $fnum = $jinput->getString('fnum', null);

        if(EmundusHelperAccess::asAccessAction(1, 'r', $current_user->id, $fnum)) {
            require_once(JPATH_COMPONENT . DS . 'models' . DS . 'application.php');
            $m_application = new EmundusModelApplication;

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
        $m_application = $this->getModel('Application');

        $jinput = JFactory::getApplication()->input;
        $user = $jinput->getVar('user', null);
        $filename = $jinput->getVar('filename', null);

        $preview = $m_application->getAttachmentPreview($user, $filename);

        echo json_encode($preview);
        exit;
    }

    public function getfilters()
    {
        $filters = [];
        $jinput = JFactory::getApplication()->input;
        $type = $jinput->getS('type', null);
        $id = $jinput->getVar('id', null);

        $m_application = $this->getModel('Application');
        $filters = $m_application->getFilters($type, $id);

        echo json_encode(array('status' => true, 'filters' => $filters));
        exit;
    }

    public function mountquery()
    {
        $jinput = JFactory::getApplication()->input;
        $filters = $jinput->getVar('filters', null);
        $listId = $jinput->getVar('id', null);
        $filters = json_decode($filters, true);

        $m_application = $this->getModel('Application');
        $res = $m_application->mountQuery($listId, $filters);

        echo json_encode($res);
        exit;
    }

    public function reorderapplications()
    {
        $response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

        $current_user = JFactory::getUser();
        $emundusUser = JFactory::getSession()->get('emundusUser');
        $emundusUserFnums = array_keys($emundusUser->fnums);

        $jinput = JFactory::getApplication()->input;
        $fnum_from = $jinput->getString('fnum_from', '');
        $fnum_to = $jinput->getString('fnum_to', '');
        $order_column = $jinput->getString('order_column', 'ordering');

        if (EmundusHelperAccess::asCoordinatorAccessLevel($current_user->id) || (in_array($fnum_from, $emundusUserFnums) && in_array($fnum_to, $emundusUserFnums))) {
            $m_application = $this->getModel('Application');
            $reordered = $m_application->invertFnumsOrderByColumn($fnum_from, $fnum_to, $order_column);

            $response['status'] = $reordered;
            $response['msg'] =  $reordered ? JText::_('SUCCESS') : JText::_('FAILED');
        }

        echo json_encode($response);
        exit;
    }

	public function applicantcustomaction() {
		$response = ['status' => false, 'msg' => JText::_('ACCESS_DENIED'), 'code' => 403];

		if (EmundusHelperAccess::isApplicant(JFactory::getUser()->id)) {
			$jinput = JFactory::getApplication()->input;
			$action = $jinput->getString('action', '');
			$fnum = $jinput->getString('fnum', '');

			if (!empty($action) && !empty($fnum)) {
				$emundusUser = JFactory::getSession()->get('emundusUser');
				$emundusUserFnums = array_keys($emundusUser->fnums);

				if (in_array($fnum, $emundusUserFnums)) {
					$m_application = $this->getModel('Application');
					$m_application->applicantCustomAction($action, $fnum);
				} else {
					$response['msg'] = JText::_('INVALID_PARAMETERS');
					$response['code'] = 400;
				}
			}
		}

		header('Content-Type: application/json');
		header('HTTP/1.1 ' . $response['code'] . ' ' . $response['msg']);
		echo json_encode($response);
		exit;
	}
}

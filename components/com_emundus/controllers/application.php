<?php
/**
 * @version     $Id: application.php 750 2012-01-23 22:29:38Z brivalland $
 * @package     Joomla
 * @copyright   (C) 2016 eMundus LLC. All rights reserved.
 * @license     GNU General Public License
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use \setasign\Fpdi\Fpdi;

// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS') );
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
require_once (JPATH_COMPONENT . '/models/application.php');

/**
 * Custom report controller
 * @package     Emundus
 */
class EmundusControllerApplication extends JControllerLegacy
{

    private $app;
    private $_user;

    public function __construct($config = array())
    {
        require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
        require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'export.php');

        $this->app   = Factory::getApplication();
        $this->_user = Factory::getUser();

        parent::__construct($config);
    }

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

        $m_application = new EmundusModelApplication();

        foreach ($attachments as $id) {
            $upload = $m_application->getUploadByID($id);
            $attachment = $m_application->getAttachmentByID($upload['attachment_id']);
            if (EmundusHelperAccess::asAccessAction(4, 'd', $user->id, $upload['fnum']) ) {
                $result = $m_application->deleteAttachment($id);

                if ($result != 1) {
                    echo JText::_('ATTACHMENT_DELETE_ERROR').' : '.$attachment['value'].' : '.$upload['filename'];
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

        $m_application = new EmundusModelApplication();

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
                        $m_application = new EmundusModelApplication();
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

        $m_application = new EmundusModelApplication();

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

        $m_application = new EmundusModelApplication();

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
		$response = array('status' => 0, 'msg' => JText::_('TAG_DELETE_ERROR'));

        $user = JFactory::getUser();

		if (empty($user->id)) {
			$response['msg'] = JText::_('ACCESS_DENIED');
		} else {
			$jinput = JFactory::getApplication()->input;
			$id_tag = $jinput->getInt('id_tag', 0);
			$fnum = $jinput->getString('fnum', '');

			if (!empty($fnum) && $id_tag > 0) {
				$m_application = new EmundusModelApplication();
				$m_files = $this->getModel('files');

				$tags = $m_files->getTagsByIdFnumUser($id_tag, $fnum, $user->id);

				if (EmundusHelperAccess::asAccessAction(14, 'd', $user->id, $fnum))
				{
					$result = $m_application->deleteTag($id_tag, $fnum);

					if ($result == 1 || $result) {
						$response = array('status' => $result, 'msg' => JText::_('COM_EMUNDUS_TAGS_DELETED'));
					}
				} else if ($tags) {
					$result = $m_application->deleteTag($id_tag, $fnum, $user->id);

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

    /*
     * Get Menu for application file
     */
    public function getapplicationmenu() {

        $user = JFactory::getUser();
        if(!EmundusHelperAccess::asPartnerAccessLevel($user->id))
            die(JText::_("ACCESS_DENIED"));

        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->get('fnum', null, 'STRING');

        require_once (JPATH_COMPONENT.DS.'models'.DS.'application.php');
        $m_application = new EmundusModelApplication();
        $menus = $m_application->getApplicationMenu();
        $res = false;

        if(EmundusHelperAccess::asAccessAction(1, 'r', $user->id, $fnum)) {
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

                        if (EmundusHelperAccess::asAccessAction($action[0], $action[1], $user->id, $fnum)) {
                            $access = true;
                            break;
                        }
                    }

                    if ($access) {
                        if($action_id == 36){
                            require_once (JPATH_SITE.'/components/com_emundus/models/messenger.php');

                            $messenger = new EmundusModelMessenger;
                            $notifications = $messenger->getNotificationsByFnum($fnum);
                            if($notifications > 0) {
                                $menu['notifications'] = $messenger->getNotificationsByFnum($fnum);
                            }
                        }
                        if($action_id == 10){
                            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
                            $m_files = new EmundusModelFiles;
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
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);
        $ids = $jinput->getString('ids', null);
        $ids = json_decode(stripslashes($ids));
        $res = new stdClass();
        if(EmundusHelperAccess::asAccessAction(4 ,'d', JFactory::getUser()->id, $fnum))
        {
            $m_application = new EmundusModelApplication();
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
            $user = JFactory::getSession()->get('emundusUser');     # logged user #

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
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->post->getString('fnum', null);

	    if (!empty($fnum) && EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $fnum)) {
	        $ids = $jinput->post->getString('ids', null);
	        $sid = $jinput->post->getInt('student_id', null);
	        $form_post = $jinput->post->getVar('forms', null);
	        $attachments_only = $jinput->post->getBool('attachments_only', false);

	        require_once(JPATH_SITE.'/components/com_emundus/models/profile.php');
	        require_once(JPATH_SITE.'/components/com_emundus/models/files.php');
	        require_once(JPATH_SITE.'/components/com_emundus/models/form.php');
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

            $m_application = new EmundusModelApplication();
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
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);
        if(EmundusHelperAccess::asAccessAction(11, 'u', JFactory::getUser()->id, $fnum))
        {
            $state = $jinput->getInt('state', null);
            $accessid = explode('-', $jinput->getString('access_id', null));
            $type = $jinput->getString('type', null);
            $m_application = new EmundusModelApplication();
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
            $m_application = new EmundusModelApplication();
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
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);
        $att_id = $jinput->getVar('att_id', null);
        $state = $jinput->getVar('state', null);
        $m_application = new EmundusModelApplication();
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
        $m_application = new EmundusModelApplication();
        $jinput = JFactory::getApplication()->input;

        $user_id = $jinput->getInt('user_id', null);

        $attachments = $m_application->getUserAttachments($user_id);

        echo json_encode($attachments);
        exit;
    }

    public function getattachmentsbyfnum()
    {
	    $response = ['msg' => JText::_('ACCESS_DENIED'), 'status' => false, 'code' => 403];
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', '');

		if (!empty($fnum)) {
			if (EmundusHelperAccess::asAccessAction(4, 'r', JFactory::getUser()->id, $fnum)) {
				$m_application = new EmundusModelApplication();

                // TODO: use profile only on PDF export
                /* if (!class_exists('EmundusModelFiles')) {
                    require_once(JPATH_ROOT . '/components/com_emundus/models/files.php');
                }

                $m_files = new EmundusModelFiles();
                $fnumInfos = $m_files->getFnumInfos($fnum);

                $response['attachments'] = $m_application->getUserAttachmentsByFnum($fnum, NULL, $fnumInfos['profile_id']); */
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
        $msg    = '';

        $fnum = $this->input->getString('fnum', '');

        if (!empty($fnum) && EmundusHelperAccess::asAccessAction(4, 'u', $this->_user->id, $fnum)) {
            $data = $this->input->post->getArray();

            if (empty($data['user'])) {
                $data['user'] = $this->_user->id;
            }
            if ($this->input->files->get('file')) {
                $data['file'] = $this->input->files->get('file');
            }

            $m_application = $this->getModel('Application');
            $update = $m_application->updateAttachment($data);
        }
        else {
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
	    $response = ['status' => false, 'msg' => JText::_('ACCESS_DENIED')];
		$current_user = JFactory::getUser();

		$m_application = new EmundusModelApplication();

	    $upload_id      = $this->input->getInt('upload_id', null);
	    $upload_details = $m_application->getUploadByID($upload_id);
	    $e_user         = JFactory::getSession()->get('emundusUser');

	    if (EmundusHelperAccess::asPartnerAccessLevel($current_user->id) || (in_array($upload_details['fnum'], array_keys($e_user->fnums)) && $upload_details['can_be_viewed'] == 1)) {
			$jinput = JFactory::getApplication()->input;
			$filename = $jinput->getString('filename', null);

			if (!empty($filename) && !empty($upload_details['user_id'])) {
				$response = $m_application->getAttachmentPreview($upload_details['user_id'], $filename);
			}
		}

        echo json_encode($response);
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
            $m_application = new EmundusModelApplication();
            $reordered = $m_application->invertFnumsOrderByColumn($fnum_from, $fnum_to, $order_column);

            $response['status'] = $reordered;
            $response['msg'] =  $reordered ? JText::_('SUCCESS') : JText::_('FAILED');
        }

        echo json_encode($response);
        exit;
    }

	public function createtab(){
		$response = array('tab' => 0, 'msg' => JText::_('FAILED'));
		$user = JFactory::getUser();

		if (!empty($user->id)) {
			$jinput = JFactory::getApplication()->input;
			$tab_name = $jinput->getString('name', '');

			if (!empty($tab_name)) {
				$m_application = $this->getModel('Application');
				$tab_created = $m_application->createTab($tab_name, $user->id);

				$response['tab'] = $tab_created;
				$response['msg'] =  $tab_created ? JText::_('SUCCESS') : JText::_('FAILED');
			}
		}

		echo json_encode($response);
		exit;
	}

	public function gettabs(){
		$response = array('tabs' => array());

		$user = JFactory::getUser();
		if (!empty($user->id)) {
			$m_application = $this->getModel('Application');
			$response['tabs'] = $m_application->getTabs($user->id);
		}

		echo json_encode($response);
		exit;
	}

	public function updatetabs(){
		$response = array('msg' =>  JText::_('FAILED'));
		$user = JFactory::getUser();

		if (!empty($user->id)) {
			$jinput = JFactory::getApplication()->input;
			$tabs = $jinput->getRaw('tabs');
			$tabs = json_decode($tabs);

			if (!empty($tabs)) {
				$m_application = $this->getModel('Application');
				$response['updated'] = $m_application->updateTabs($tabs, $user->id);
				$response['msg'] =  $response['updated'] ? JText::_('SUCCESS') : JText::_('FAILED');
			}
		}

		echo json_encode($response);
		exit;
	}

	public function deletetab(){
		$response = array('msg' =>  JText::_('FAILED'));
		$user = JFactory::getUser();

		if (!empty($user->id)) {
			$jinput = JFactory::getApplication()->input;
			$tab = $jinput->getInt('tab');

			if (!empty($tab)) {
				$m_application = $this->getModel('Application');
				$response['deleted'] = $m_application->deleteTab($tab, $user->id);
				$response['msg'] =  $response['deleted'] ? JText::_('SUCCESS') : JText::_('FAILED');
			}
		}

		echo json_encode($response);
		exit;
	}

	public function copyfile(){
		$response = array('status' => 0, 'msg' => '');

		$user = JFactory::getUser();

		$jinput = JFactory::getApplication()->input;
		$fnum = $jinput->getString('fnum');
		$campaign = $jinput->getString('campaign');

		if(!empty($fnum) && !empty($campaign)){
			$m_files = $this->getModel('Files');
			$fnumInfos = $m_files->getFnumInfos($fnum);

			if($fnumInfos['applicant_id'] !== $user->id){
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

		$user = JFactory::getUser();

		$jinput = JFactory::getApplication()->input;
		$fnum = $jinput->getString('fnum');
		$tab = $jinput->getString('tab');

		if(!empty($tab) && !empty($fnum)){
			$m_files = $this->getModel('Files');
			$fnumInfos = $m_files->getFnumInfos($fnum);

			if($fnumInfos['applicant_id'] !== $user->id){
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

		$user = JFactory::getUser();

		$jinput = JFactory::getApplication()->input;
		$fnum = $jinput->getString('fnum');
		$new_name = $jinput->getString('new_name');

		if(!empty($fnum)){
			$m_files = $this->getModel('Files');
			$fnumInfos = $m_files->getFnumInfos($fnum);

			if($fnumInfos['applicant_id'] !== $user->id){
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

		$user = JFactory::getUser();

		$jinput = JFactory::getApplication()->input;
		$fnum = $jinput->getString('fnum');

		if(!empty($fnum)){
			$m_files = $this->getModel('Files');
			$fnumInfos = $m_files->getFnumInfos($fnum);

			if($fnumInfos['applicant_id'] !== $user->id){
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

		$jinput = JFactory::getApplication()->input;
		$type = $jinput->getString('type');
		$value = $jinput->getString('value');

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

		$user_id = JFactory::getUser()->id;
		if (EmundusHelperAccess::isApplicant($user_id)) {
			$jinput = JFactory::getApplication()->input;
			$action = $jinput->getString('action', '');
			$fnum = $jinput->getString('fnum', '');
			$module_id = $jinput->getInt('module_id', 0);
			$fnum_filtered = preg_replace('/[^0-9]/', '', $fnum);

			if ($fnum_filtered === $fnum) {
				if (!empty($action) && !empty($fnum)) {
					require_once JPATH_ROOT.'/components/com_emundus/helpers/files.php';
					$h_files = new EmundusHelperFiles;
					$fnums = $h_files->getApplicantFnums($user_id);
					$current_user_fnums = array_keys($fnums);

					if (in_array($fnum, $current_user_fnums)) {
						$m_application = new EmundusModelApplication;
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

	public function sharefilewith()
	{
		$response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'code' => 403];

        $user = Factory::getUser();

        if (!$user->guest) {
            $fnum = $this->input->getString('fnum', '');
            $e_user = Factory::getApplication()->getSession()->get('emundusUser');

            if (!empty($fnum) && (EmundusHelperAccess::asPartnerAccessLevel($user->id) || in_array($fnum, array_keys($e_user->fnums)))) {
                $response['code'] = 500;
                $m_application      = $this->getModel('Application');
                $collaboration_url = $m_application->getCollaborationAcceptionLink();

                if (!empty($collaboration_url)) {
                    $response['msg'] = Text::_('MISSING_PARAMETERS');
                    $ccid = $this->input->getInt('ccid', 0);
                    $emails = $this->input->getString('emails', '');

                    if (!empty($emails) && !empty($ccid)) {
                        $response['msg'] = Text::_('FAILED');
                        $emails = explode(',', $emails);

                        $response['data'] = $m_application->shareFileWith($emails, $ccid, Factory::getUser()->id);

                        if ($response['data']['status']) {
                            $response['code'] = 200;
                            $response['msg'] = '';
                            $response['status'] = true;

                            require_once JPATH_ROOT . '/components/com_emundus/controllers/messages.php';
                            $c_messages = new EmundusControllerMessages();

                            $emails_not_sent = [];
                            foreach ($response['data']['emails'] as $email => $key) {
                                $post = [
                                    'COLLABORATE_URL' => $collaboration_url . $key,
                                    'COLLABORATE_BUTTON' => Text::_('COM_EMUNDUS_APPLICATIONS_COLLABORATE_BUTTON'),
                                ];

                                $sent = $c_messages->sendEmailNoFnum($email, 'collaborate_invitation', $post, $e_user->id, [], $fnum);
                                if (!$sent) {
                                    $response['data']['failed_emails'][] = $email;
                                }
                            }
                        }
                    }
                } else {
                    $response['msg'] = Text::_('COM_EMUNDUS_APPLICATIONS_COLLABORATE_LINK_NOT_CONFIGURED');
                }
            }
        }

        if ($response['code'] == 403) {
            header('HTTP/1.1 403 Forbidden');
            echo $response['msg'];
            exit;
        } else if ($response['code'] == 500) {
            header('HTTP/1.1 500 Internal Server Error');
            echo $response['msg'];
            exit;
        }
		echo json_encode($response);
		exit;
	}

	public function removeshareduser()
	{
		$response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'code' => 403];

		$fnum = $this->input->getString('fnum','');
		$e_user = Factory::getApplication()->getSession()->get('emundusUser');

		if(!empty($fnum) && (EmundusHelperAccess::asPartnerAccessLevel(Factory::getUser()->id) || in_array($fnum, array_keys($e_user->fnums)))) {
			$ccid = $this->input->getInt('ccid',0);
			$request_id = $this->input->getInt('request_id',0);

			if(!empty($request_id) && !empty($ccid)) {
				PluginHelper::importPlugin('emundus', 'custom_event_handler');
				Factory::getApplication()->triggerEvent('callEventHandler', ['onBeforeRemoveSharedUser', ['request_id' => $request_id, 'ccid' => $ccid, 'fnum' => $fnum]]);

				$m_application      = $this->getModel('Application');
				$response['status'] = $m_application->removeSharedUser($request_id, $ccid, Factory::getUser()->id);
			}
		}

		echo json_encode($response);
		exit;
	}

	public function sendnewcollaborationemail()
	{
		$response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'code' => 403];

		$fnum = $this->input->getString('fnum','');
		$e_user = Factory::getApplication()->getSession()->get('emundusUser');

		if(!empty($fnum) && (EmundusHelperAccess::asPartnerAccessLevel(Factory::getUser()->id) || in_array($fnum, array_keys($e_user->fnums)))) {
			$ccid = $this->input->getInt('ccid',0);
			$request_id = $this->input->getInt('request_id',0);

			$ttl = Factory::getSession()->get('ttl_send_email_'.$request_id);

			if($ttl && (time() - $ttl) < 900) {
				$response['msg'] = Text::_('COM_EMUNDUS_APPLICATIONS_COLLABORATE_EMAIL_TTL');
			}
			elseif(!empty($request_id) && !empty($ccid)) {
				$m_application      = $this->getModel('Application');
				$response['data'] = $m_application->regenerateKey($request_id, $ccid, Factory::getUser()->id);

				if($response['data']['status']) {
					$response['status'] = true;

					$emundus_config = ComponentHelper::getParams('com_emundus');
					$collaboration_link = $emundus_config->get('collaborate_link','index.php?option=com_fabrik&view=form&formid=378');
					$menu_item = Factory::getApplication()->getMenu()->getItems('link', $collaboration_link, true);

					require_once JPATH_ROOT . '/components/com_emundus/controllers/messages.php';
					$c_messages = new EmundusControllerMessages();

					$post = [
						'COLLABORATE_URL' => Uri::base() . $menu_item->alias . '/?key=' . $response['data']['key'],
						'COLLABORATE_BUTTON' => Text::_('COM_EMUNDUS_APPLICATIONS_COLLABORATE_BUTTON'),
					];

					$c_messages->sendEmailNoFnum($response['data']['email'],'collaborate_invitation', $post, $e_user->id, [], $fnum);

					Factory::getSession()->set('ttl_send_email_'.$request_id, time());

					$response['msg'] = Text::_('COM_EMUNDUS_APPLICATIONS_COLLABORATE_EMAIL_SENT_SUCCESFULLY');
				}
			}
		}

		echo json_encode($response);
		exit;
	}

	public function updateright()
	{
		$response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'code' => 403];

		$fnum = $this->input->getString('fnum','');
		$e_user = Factory::getApplication()->getSession()->get('emundusUser');

		if(!empty($fnum) && (EmundusHelperAccess::asPartnerAccessLevel(Factory::getUser()->id) || in_array($fnum, array_keys($e_user->fnums)))) {
			$ccid = $this->input->getInt('ccid',0);
			$request_id = $this->input->getInt('request_id',0);
			$right = $this->input->getString('right',0);
			$value = $this->input->getString('value',0);
			$value = $value == 'true' ? 1 : 0;

			if(!empty($request_id) && !empty($ccid) && !empty($right)) {
				$m_application      = $this->getModel('Application');
				$response['status'] = $m_application->updateRight($request_id, $ccid, $right, $value);
				if($response['status']) {
					$response['msg'] = Text::_('COM_EMUNDUS_APPLICATIONS_COLLABORATE_RIGHT_UPDATED_SUCCESFULLY');
				}
			}
		}

		echo json_encode($response);
		exit;
	}

	public function lockelement()
	{
		$response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'code' => 403];

		$element = $this->input->getString('element','');
		$fid = $this->input->getInt('form_id',0);
		$fnum = $this->input->getString('fnum','');
		$state = $this->input->getInt('state',0);

		$e_user = Factory::getApplication()->getSession()->get('emundusUser');
		$fnumInfos = $e_user->fnums[$fnum];

		if(!empty($fnum) && $fnumInfos->applicant_id == Factory::getUser()->id) {
			$m_application      = $this->getModel('Application');
			$response['status'] = $m_application->lockElement($element, $fid, $fnumInfos->id, $state);
		}

		echo json_encode($response);
		exit;
	}

	public function saveformsession()
	{
		$response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'code' => 403];

		$element = $this->input->getString('element','');
		$fid = $this->input->getInt('form_id',0);
		$value = $this->input->getString('value','');

		$e_user = Factory::getApplication()->getSession()->get('emundusUser');
		$fnum = $e_user->fnum;

		if(!empty($fnum) && !empty($element) && !empty($fid)) {
			$m_application      = $this->getModel('Application');
			$response['status'] = $m_application->saveFormSession($element, $fid, $value, $fnum);
		}

		echo json_encode($response);
		exit;
	}

	public function clearformsession()
	{
		$response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'code' => 403];

		$fid = $this->input->getInt('form_id',0);

		$e_user = Factory::getApplication()->getSession()->get('emundusUser');
		$fnum = $e_user->fnum;

		if(!empty($fnum) && !empty($fid)) {
			$m_application      = $this->getModel('Application');
			$response['status'] = $m_application->clearFormSession($fid, $fnum);
		}

		echo json_encode($response);
		exit;
	}
}

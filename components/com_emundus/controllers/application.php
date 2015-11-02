<?php
/**
 * @version		$Id: application.php 750 2012-01-23 22:29:38Z brivalland $
 * @package		Joomla
 * @copyright	(C) 2008 - 2013 eMundus LLC. All rights reserved.
 * @license		GNU General Public License
 */

// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( JText::_('RESTRICTED_ACCESS') );
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');

/**
 * Custom report controller
 * @package		Emundus
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
     * export ZIP
     */
/*
    public function export_zip() {
        require_once('libraries/emundus/zip.php');
        //$db	= JFactory::getDBO();
        $cid = JRequest::getVar('uid', null, 'POST', 'array', 0);
        JArrayHelper::toInteger( $cid, 0 );
        if (count( $cid ) == 0) {
            JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );
            $this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
            exit;
        }
        zip_file($cid);
        exit;
    }
*/
    /**
     * Delete an applicant attachment(s)
     */
    public function delete_attachments() {
        $user = JFactory::getUser();
        //$allowed = array("Super Users", "Administrator", "Editor");
        if(!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) die(JText::_("ACCESS_DENIED"));

        //$db	= JFactory::getDBO();
        $attachments = JRequest::getVar('attachments', null, 'POST', 'array', 0);

        $user_id = JRequest::getVar('sid', null, 'POST', 'none', 0);

        $view = JRequest::getVar('view', null, 'POST', 'none', 0);
        $tmpl = JRequest::getVar('tmpl', null, 'POST', 'none', 0);

        $url = !empty($tmpl)?'index.php?option=com_emundus&view='.$view.'&sid='.$user_id.'&tmpl='.$tmpl.'#attachments':'index.php?option=com_emundus&view='.$view.'&sid='.$user_id.'&tmpl=component#attachments';
        // die(var_dump($attachments));
        JArrayHelper::toInteger( $attachments, 0 );
        if (count( $attachments ) == 0) {
            JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );
            //$mainframe->redirect($url);
            exit;
        }

        $model = $this->getModel('application');

        foreach ($attachments as $id) {
            $upload = $model->getUploadByID($id);
            $attachment = $model->getAttachmentByID($upload['attachment_id']);

            $result = $model->deleteAttachment($id);

            if($result != 1){
                echo JText::_('ATTACHMENT_DELETE_ERROR').' : '.$attachment['value'].' : '.$upload['filename'];
            } else {
                $file = EMUNDUS_PATH_ABS.$user_id.DS.$upload['filename'];
                @unlink($file);

                $row['applicant_id'] = $upload['user_id'];
                $row['user_id'] = $user->id;
                $row['reason'] = JText::_('ATTACHMENT_DELETED');
                $row['comment_body'] = $attachment['value'].' : '.$upload['filename'];
                $model->addComment($row);

                echo $result;
            }
        }

        $this->setRedirect($url, JText::_('ATTACHMENTS_DELETED'), 'message');
        return;
    }

    /**
     * Delete an applicant attachment (one by one)
     */
    public function delete_attachment() {
        $user = JFactory::getUser();

        if(!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) die(JText::_("ACCESS_DENIED"));

        //$view = JRequest::getVar('view', null, 'GET', 'none',0);
        //$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);

        $id = JRequest::getVar('id', null, 'GET', 'none',0);

        $model = $this->getModel('application');

        $upload = $model->getUploadByID($id);
        $attachment = $model->getAttachmentByID($upload['attachment_id']);

        $result = $model->deleteAttachment($id);

        if($result != 1){
            echo JText::_('ATTACHMENT_DELETE_ERROR');
        } else {
            $row['applicant_id'] = $upload['user_id'];
            $row['user_id'] = $user->id;
            $row['reason'] = JText::_('ATTACHMENT_DELETED');
            $row['comment_body'] = $attachment['value'].' : '.$upload['filename'];
            $model->addComment($row);

            echo $result;
        }
    }

    /**
     * Upload an applicant attachment (one by one)
     */
    public function upload_attachment() {
        $user = JFactory::getUser();

        if(!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) die(JText::_("ACCESS_DENIED"));

        $view = JRequest::getVar('view', null, 'GET', 'none',0);
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $aid = JRequest::getVar('attachment_id', null, 'POST', 'none',0);
        $uid = JRequest::getVar('uid', null, 'POST', 'none',0);
        $filename = JRequest::getVar('filename', null, 'POST', 'none',0);
        $campaign_id = JRequest::getVar('campaign_id', null, 'POST', 'none',0);
        $can_be_viewed = JRequest::getVar('can_be_viewed', null, 'POST', 'none',0);
        $can_be_deleted = JRequest::getVar('can_be_deleted', null, 'POST', 'none',0);

        $targetFolder = EMUNDUS_PATH_ABS.$uid;


        //echo $stringData . $targetFolder . $_FILES['filename']['name'];

        if (!empty($_FILES)) {
            $msg = "";
            $data = "{";
            switch ($_FILES['filename']['error']) {
                case 0:		$msg .= JText::_("FILE_UPLOADED");
                    $data .= '"message":"'.$msg.'",';
                    $tempFile = $_FILES['filename']['tmp_name'];
                    $targetPath = $targetFolder;

                    // Validate the file type
                    $fileTypes = array('jpg','jpeg','gif','png', 'pdf', 'doc', 'docx', 'odt', 'zip'); // File extensions
                    $fileParts = pathinfo($_FILES['filename']['name']);

                    if (in_array($fileParts['extension'], $fileTypes)) {
                        $model = $this->getModel('application');
                        $type_attachment = $model->getAttachmentByID($aid);

                        $filename = date('Y-m-d_H-i-s').$type_attachment['lbl'].'_'.$_FILES['filename']['name'];
                        $fileURL = EMUNDUS_PATH_REL.$uid.'/'.$filename;
                        $targetFile = rtrim($targetPath,'/') . DS . $filename;

                        move_uploaded_file($tempFile, $targetFile);

                        $filesize = $_FILES['filename']['size'];

                        $attachment["key"] = array("user_id", "attachment_id", "filename", "description", "can_be_deleted", "can_be_viewed", "campaign_id");
                        $attachment["value"] = array($uid, $aid, '"'.$filename.'"', '"'.date('Y-m-d H:i:s').'"', $can_be_deleted, $can_be_viewed, $campaign_id);

                        $id = $model->uploadAttachment($attachment);
                    } else {
                        $msg .= JText::_('COM_EMUNDUS_FILETYPE_INVALIDE');
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
                    //$data .= '"html":"'.$html.'"';


                    break;
                case 1:		$msg .= "The file is bigger than this PHP installation allows";
                    $data .= '"message":"'.$msg.'"';
                    break;
                case 2:		$msg .= "The file is bigger than this form allows";
                    $data .= '"message":"'.$msg.'"';
                    break;
                case 3:		$msg .= "Only part of the file was uploaded";
                    $data .= '"message":"'.$msg.'"';
                    break;
                case 4:		$msg .= "No file was uploaded";
                    $data .= '"message":"'.$msg.'"';
                    break;
                case 6:		$msg .= "Missing a temporary folder";
                    $data .= '"message":"'.$msg.'"';
                    break;
                case 7:		$msg .= "Failed to write file to disk";
                    $data .= '"message":"'.$msg.'"';
                    break;
                case 8:		$msg .= "File upload stopped by extension";
                    $data .= '"message":"'.$msg.'"';
                    break;
                default:	$msg .= "Unknown error ".$_FILES['filename']['error'];
                    $data .= '"message":"'.$msg.'",';
                    break;
            }
            $data .= "}";
            echo $data;
            //echo json_encode($data);
        }
    }


    public function deletecomment(){
        $user = JFactory::getUser();
        $comment_id = JRequest::getVar('comment_id', null, 'GET', 'none',0);
        $view = JRequest::getVar('view', null, 'GET', 'none',0);
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);

        $model = $this->getModel('application');

        $comment = $model->getComment($comment_id);

        if( !EmundusHelperAccess::asAccessAction(10, 'd', $user->id, $comment['fnum']))
        {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        }
        else
        {
            $result = $model->deleteComment($comment_id);
            if($result!=1)
                $tab = array('status' => $result, 'msg' => JText::_('COMMENT_DELETE_ERROR'));
            else
                $tab = array('status' => $result, 'msg' => JText::_('COMMENT_DELETED'));
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function deletetag(){
        $user = JFactory::getUser();
        $id_tag = JRequest::getVar('id_tag', null, 'GET', 'none',0);
        $fnum = JRequest::getVar('fnum', null, 'GET', 'none',0);

        $model = $this->getModel('application');

        if( !EmundusHelperAccess::asAccessAction(14, 'd', $user->id, $fnum))
        {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        }
        else
        {
            $result = $model->deleteTag($id_tag, $fnum);
            if($result!=1 && $result!=true)
                $tab = array('status' => $result, 'msg' => JText::_('TAG_DELETE_ERROR'));
            else
                $tab = array('status' => $result, 'msg' => JText::_('TAG_DELETED'));
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

        $model = $this->getModel('application');
        $result = $model->deleteData($id, $table);

        $model = $this->getModel('application');

        $row['applicant_id'] = $sid;
        $row['user_id'] = $user->id;
        $row['reason'] = JText::_('DATA_DELETED');
        $row['comment_body'] = JText::_('LINE').' '.$id.' '.JText::_('FROM').' '.$table;
        $model->addComment($row);

        echo $result;

        /*
        if($result!=1){
            echo JText::_('DELETE_ERROR');
        }*/
    }
    /*
     * Get Menu for application file
     */
    public function getactionmenu()
    {
        $user = JFactory::getUser(); 
        if(!EmundusHelperAccess::asPartnerAccessLevel($user->id))
            die(JText::_("ACCESS_DENIED"));

        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->get('fnum', null, 'STRING');

        $model = $this->getModel('Application');
        $menus = $model->getActionMenu();
        $res = false;
        
        if(EmundusHelperAccess::asAccessAction(1, 'r', $user->id, $fnum))
        {
            if ($menus !== false)
            {
                $res = true;
                $menu_application = array();
                $i=0;
                foreach($menus as $k => $menu)
                {
                    $action = explode('|', $menu['note']);
                    if (EmundusHelperAccess::asAccessAction($action[0], $action[1], $user->id, $fnum)) {
                        $menu_application[] = $menu;
                        if ((intval($menu['rgt']) - intval($menu['lft'])) == 1)
                        {
                            $menu_application[$i++]['hasSons'] = false;
                        }
                        else
                        {
                            $menu_application[$i++]['hasSons'] = true;
                        }
                    }

                }
            }
            $tab = array('status' => $res, 'menus' => $menu_application);
        }
        else
        {
            $tab = array('status' => false, 'msg' => JText::_('RESTRICTED_ACCESS'));
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
            $model = $this->getModel('application');
            foreach($ids as $id)
            {
                $model->deleteAttachment($id);
            }
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
        $fnum = $jinput->getString('fnum', null);
        $ids = $jinput->getString('ids', null);
        $ids = explode(',', $ids);
        $sid = $jinput->getInt('student_id', null);
        $form_post = $jinput->getVar('forms', null);

        if(EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $fnum))
        {
            $exports = array();
            $tmpArray = array();

            if($form_post)
            {
                $modelFiles = $this->getModel('files');
                $fnumInfos = $modelFiles->getFnumInfos($fnum);

                $exports[] = EmundusHelperExport::buildFormPDF($fnumInfos, $sid, $fnum, 1);
            }

            $model = $this->getModel('application');
            $files = $model->getAttachments($ids);
            EmundusHelperExport::getAttchmentPDF($exports, $tmpArray, $files, $sid);

            if(!empty($exports))
            {
                require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'fpdi.php');
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
                $res->link = EMUNDUS_PATH_REL.DS.$sid.DS.$fnum.'_attachments.pdf';
                echo json_encode($res);
                exit();

            }
            else
            {
                $res = new stdClass();
                $res->status = false;
                $res->msg = JText::_('FILES_NOT_FOUND_IN_SERVER');
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
            $model = $this->getModel('Application');
            $res = new stdClass();
            if($type == 'groups')
            {
                $res->status = $model->updateGroupAccess($fnum, $accessid[0], $accessid[1], $accessid[2], $state);
            }
            else
            {
                $res->status = $model->updateUserAccess($fnum, $accessid[0], $accessid[1], $accessid[2], $state);
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
            $model = $this->getModel('Application');
            $res = new stdClass();
            if($type == 'groups')
            {
                $res->status = $model->deleteGroupAccess($fnum, $id);
            }
            else
            {
                $res->status = $model->deleteUserAccess($fnum, $id);
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
}

<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2015 emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * eMundus Component Controller
 *
 * @package    eMundus
 * @subpackage Components
 */
class EmundusController extends JControllerLegacy {
    var $_user = null;
    var $_db = null;

    function __construct($config = array()){
        //require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
        //require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        //require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
        //require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');

        $this->_user = JFactory::getUser();
        $this->_db = JFactory::getDBO();

        parent::__construct($config);
    }

    function display($cachable = false, $urlparams = false) {
        // Set a default view if none exists
        if ( ! JRequest::getCmd( 'view' ) ) {
            if ($this->_user->usertype == "Registered") {
                $checklist = $this->getView( 'checklist', 'html' );
                $checklist->setModel( $this->getModel( 'checklist'), true );
                $checklist->display();
            } else {
                $default = 'users';
            }
            JRequest::setVar('view', $default );
        }

        parent::display();

    }

    function clear() {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
        EmundusHelperFilters::clear();
    }

    function getCampaign() {
        $db = JFactory::getDBO();
        $query = 'SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE published=1';
        $db->setQuery( $query );
        $syear = $db->loadRow();

        return $syear[0];
    }

    function pdf() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $student_id = $jinput->get('user', null, 'string');
        $fnum = $jinput->get('fnum', null, 'string');
        $fnum = !empty($fnum)?$fnum:$user->fnum;
        $m_profile = $this->getModel('profile');
        $m_campaign = $this->getModel('campaign');
        //$profile = $model->getProfileByApplicant($student_id);

        if (!empty($fnum)) {
            $candidature = $m_profile->getFnumDetails($fnum);
            $campaign = $m_campaign->getCampaignByID($candidature['campaign_id']);
        }
//die(var_dump($campaign));
        $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_'.@$campaign['training'].'.php';

        if (!file_exists($file)) {
            $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php';
        }
        if (!file_exists(EMUNDUS_PATH_ABS.$student_id)) {
            mkdir(EMUNDUS_PATH_ABS.$student_id);
            chmod(EMUNDUS_PATH_ABS.$student_id, 0755);
        }

        require_once($file);

        if(EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            application_form_pdf(!empty($student_id)?$student_id:$user->id, $fnum);
            exit;
        } elseif(EmundusHelperAccess::isApplicant($user->id)) {
            application_form_pdf($user->id, $fnum);
            exit;
        } else die(JText::_('ACCESS_DENIED'));

        exit();
    }

    function pdf_emploi(){
        $user = JFactory::getUser();
        $student_id = JRequest::getVar('user', null, 'GET', 'none',0);
        $fnum = JRequest::getVar('fnum', null, 'GET', 'none',0);
        $rowid = explode('-', JRequest::getVar('rowid', null, 'GET', 'none',0));

        $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_emploi.php';

        if (!file_exists($file)) {
            die(JText::_('FILE_NOT_FOUND'));
        }
        if (!file_exists(EMUNDUS_PATH_ABS.$student_id)) {
            mkdir(EMUNDUS_PATH_ABS.$student_id);
            chmod(EMUNDUS_PATH_ABS.$student_id, 0755);
        }

        require_once($file);

        if(EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            application_form_pdf(!empty($student_id)?$student_id:$user->id, $rowid[0], true);
        } else die(JText::_('ACCESS_DENIED'));

        exit();
    }

    function pdf_thesis(){
        $user = JFactory::getUser();
        $student_id = JRequest::getVar('user', null, 'GET', 'none',0);
        $fnum = JRequest::getVar('fnum', null, 'GET', 'none',0);
        $rowid = explode('-', JRequest::getVar('rowid', null, 'GET', 'none',0));

        $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_thesis.php';

        if (!file_exists($file)) {
            die(JText::_('FILE_NOT_FOUND'));
        }
        if (!file_exists(EMUNDUS_PATH_ABS.$student_id)) {
            mkdir(EMUNDUS_PATH_ABS.$student_id);
            chmod(EMUNDUS_PATH_ABS.$student_id, 0755);
        }

        require_once($file);

        if(EmundusHelperAccess::asPartnerAccessLevel($user->id) || EmundusHelperAccess::isApplicant($user->id)) {
            application_form_pdf(!empty($student_id)?$student_id:$user->id, $rowid[0], true);
        } else die(JText::_('ACCESS_DENIED'));

        exit();
    }

    function export_pdf(){
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');

        $current_user = JFactory::getUser();
        if(!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id))
            die( JText::_('RESTRICTED_ACCESS') );

        $jinput = JFactory::getApplication()->input;
        $fnums_post     = $jinput->getVar('fnums', null);
        $form_post      = $jinput->getVar('forms', null);
        $doc_post       = $jinput->getVar('attachment', null);
        $eval_post      = $jinput->getVar('assessment', 0);
        $decision_post  = $jinput->getVar('decision', 0);
        
        $fnums_post = (array) json_decode(stripslashes($fnums_post));
        $model = $this->getModel('Files');
        
        if(!is_array($fnums_post) || count($fnums_post) == 0 || @$fnums_post[0] == "all") {
            $fnums = $model->getAllFnums();
        } else {
            $fnums = array();
            foreach ($fnums_post as $key => $value) {
                $fnums[]=$value->fnum;
            }
        }
        $validFnums  = array();
        foreach($fnums as $fnum) {
            if(EmundusHelperAccess::asAccessAction(8, 'c', $this->_user->id, $fnum)) {
                $validFnums[] = $fnum;
            }
        }
        $fnumsInfo = $model->getFnumsInfos($validFnums);

        $files_list = array();
        foreach ($validFnums as $fnum) {
            if (is_numeric($fnum) && !empty($fnum)) {
                $files_list[] = EmundusHelperExport::buildFormPDF($fnumsInfo[$fnum], $fnumsInfo[$fnum]['applicant_id'], $fnum, $form_post);
            
                if($doc_post) {
                    $tmpArray = array();
                    $model = $this->getModel('application');
                    $files = $model->getAttachmentsByFnum($fnum);

                    EmundusHelperExport::getAttachmentPDF($files_list, $tmpArray, $files, $fnumsInfo[$fnum]['applicant_id']);
                }
                if($eval_post) {
                    EmundusHelperExport::getEvalPDF($files_list,$fnum);
                }
            }
        }

        // all PDF in one file
        require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'fpdi.php');
        $pdf = new ConcatPdf();
        $pdf->setFiles($files_list);
        $pdf->concat();
        if(isset($tmpArray)) {
            foreach($tmpArray as $fn) {
                unlink($fn);
            }
        }
        $tmpName = '/tmp/'.time()."-applications.pdf";
        $pdf->Output(JPATH_BASE.$tmpName, 'F');
        $res = new stdClass();
        $res->status = true;
        $res->link = $tmpName;
        echo json_encode($res);
        exit();
    }



    /* 
        Delete file
    */
    function deletefile() {
        //@TODO ADD COMMENT ON DELETE
        $app    = JFactory::getApplication();

        $student_id    = JRequest::getVar('sid', null, 'GET', 'none',0);
        
        $layout        = JRequest::getVar('layout', null, 'GET', 'none',0);
        $format        = JRequest::getVar('format', null, 'GET', 'none',0);
        $itemid        = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $fnum          = JRequest::getVar('fnum', null, 'GET', 'none',0);
        
        if (empty($fnum))
            $app->redirect(JURI::Base().'index.php');
        
        $current_user  = JFactory::getUser();
        $chemin = EMUNDUS_PATH_ABS;
        $modelFiles = $this->getModel('files');

        if (EmundusHelperAccess::isApplicant($current_user->id) && in_array($fnum, array_keys($current_user->fnums))){
            $user = $current_user;
            $result = $modelFiles->deleteFile($fnum);
            
        } elseif(EmundusHelperAccess::asAccessAction(1, 'd', $current_user->id, $fnum) || 
                 EmundusHelperAccess::asAdministratorAccessLevel($current_user->id)) {
            $user = JFactory::getUser($student_id);

        } else {
            JError::raiseError(500, JText::_('ACCESS_DENIED'));
            $app->redirect('index.php');
            
            return false;
        }

        unset($current_user->fnums[$fnum]);

        if (in_array($user->fnum, array_keys($user->fnums))) {
            $app->redirect(JURI::Base().'index.php?option=com_emundus&task=openfile&fnum='.$user->fnum);
        } else {
            $fnum = array_shift($current_user->fnums);
            $app->redirect(JURI::Base().'index.php?option=com_emundus&task=openfile&fnum='.$fnum->fnum);
        }

        return true;
    }

    /* 
        Delete document from application file
    */
    function delete() {
        //@TODO ADD COMMENT ON DELETE
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $copy_application_form = $eMConfig->get('copy_application_form', 0);

        $student_id    = JRequest::getVar('sid', null, 'GET', 'none',0);
        $upload_id     = JRequest::getVar('uid', null, 'GET', 'none',0);
        $attachment_id = JRequest::getVar('aid', null, 'GET', 'none',0);
        $duplicate     = JRequest::getVar('duplicate', null, 'GET', 'none',0);
        $nb            = JRequest::getVar('nb', null, 'GET', 'none',0);
        $layout        = JRequest::getVar('layout', null, 'GET', 'none',0);
        $format        = JRequest::getVar('format', null, 'GET', 'none',0);
        $itemid        = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $fnum          = JRequest::getVar('fnum', null, 'GET', 'none',0);
        $current_user  = JFactory::getUser();
        $chemin = EMUNDUS_PATH_ABS;
        $db     = JFactory::getDBO();

        if (EmundusHelperAccess::isApplicant($current_user->id)){
            $user = $current_user;
            $fnum = $user->fnum;
            if ($duplicate == 1 && $nb <= 1 && $copy_application_form == 1) {
               $fnums = implode(',', $db->Quote(array_keys($user->fnums)));
               $where = ' AND user_id='.$user->id.' AND attachment_id='.$attachment_id. ' AND `fnum` in (select fnum from `#__emundus_campaign_candidature` where `status`=0)';
            } else {
                $fnums = $db->Quote($fnum);
                $where = ' AND user_id='.$user->id.' AND id='.$upload_id;
            }
            
        } elseif(EmundusHelperAccess::asAccessAction(4, 'd', $current_user->id, $fnum) || 
                 EmundusHelperAccess::asAdministratorAccessLevel($current_user->id)) {
            $user = JFactory::getUser($student_id);
            $fnums = $db->Quote($fnum);
        } else {
            JError::raiseError(500, JText::_('ACCESS_DENIED'));
            return false;
        }

        if (isset($layout))
            $url = 'index.php?option=com_emundus&view=checklist&layout=attachments&sid='.$user->id.'&tmpl=component&Itemid='.$itemid;
        else
            $url = 'index.php?option=com_emundus&view=checklist&Itemid='.$itemid;


        $query  = 'SELECT id, filename 
                    FROM #__emundus_uploads
                    WHERE user_id = '.mysql_real_escape_string($user->id).' 
                    AND fnum IN ('.$fnums.') '.$where;

        try {
            $db->setQuery( $query );
            $files = $db->loadAssocList();
  
            if (count($files) == 0) {
                $message = JText::_('Error : empty file');
                if ($format == 'raw') {
                    echo '{"status":false, "message":"'.$message.'"}';
                    return false;
                } else $this->setRedirect($url, $message, 'error');

            } else {
                try {
                    
                    $file_id = array();
                    $message = '';

                    foreach ($files as $file) {
                        $file_id[] = $file['id'];

                        if (unlink($chemin.$user->id.DS.$file['filename'])) {
                            if (is_file($chemin.$user->id.DS.'tn_'.$file['filename'])) {
                                unlink($chemin.$user->id.DS.'tn_'.$file['filename']);                    
                            }
                            $message .= '<br>'.JText::_('ATTACHMENT_DELETED').' : '.$file['filename'].'. ';
                            
                        } else {
                            $message .= '<br>'.JText::_('FILE_NOT_FOUND').' : '.$file['filename'].'. ';
                        }
                    }

                    $query  = 'DELETE FROM #__emundus_uploads
                                WHERE id IN ('.implode(',', $file_id).') 
                                AND user_id = '.mysql_real_escape_string($user->id). ' 
                                AND fnum IN ('.$fnums.')';
                    $db->setQuery( $query );
                    $db->execute();

                    if ($format == 'raw') 
                        echo '{"status":true, "message":"'.$message.'"}';
                    else 
                        $this->setRedirect($url, $message, 'message');
                }
                catch(Exception $e)
                {
                    $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                    JLog::add($error, JLog::ERROR, 'com_emundus');
                    if ($format == "raw") {
                        echo '{"status":false,"message":"'.$error.'"}';
                    } else
                        JError::raiseError(500, $e->getMessage());

                    return false;
                }
            }
        }
        catch(Exception $e)
        {
            $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
            JLog::add($error, JLog::ERROR, 'com_emundus');
            if ($format == "raw") {
                echo '{"aid":"0","status":false,"message":"'.$error.'" }';
            } else JError::raiseError(500, $e->getMessage());

            return false;
        }

        return true;
    }

    /*
        Open application form from fnum  for applicant
    */
    function openfile() {
        require_once (JPATH_COMPONENT.DS.'models'.DS.'profile.php');
        $app    = JFactory::getApplication();
        $Itemid = $app->input->getInt('Itemid', null, 'int');
        $fnum   = JRequest::getVar('fnum', null, 'GET', 'none', 0);
        if (empty($fnum)) {
            $app->redirect(JURI::Base().'index.php');
        }
        $redirect   = JRequest::getVar('redirect', null, 'GET');
        $redirect   = (!empty($redirect))?base64_decode($redirect):'index.php';
        $aid    = JFactory::getUser();

        $model = new EmundusModelProfile;
        $infos = $model->getFnumDetails($fnum);

        if ($aid->id != $infos['applicant_id']) return;

        $profile        = $model->getProfileByCampaign($infos['campaign_id']);
        $campaign       = $model->getCampaignById($infos['campaign_id']);
        $application    = $model->getFnumDetails($fnum);

        $aid->profile       = $profile['profile_id'];
        $aid->profile_label = $profile['label'];
        $aid->menutype      = $profile['menutype'];
        $aid->start_date    = $profile['start_date'];
        $aid->end_date      = $profile['end_date'];
        $aid->candidature_posted = $infos['submitted'];
        $aid->candidature_incomplete = $infos['status']==0?1:0;
        $aid->schoolyear    = $campaign['year'];
        $aid->code          = $campaign['training'];
        $aid->campaign_id   = $infos['campaign_id'];
        $aid->campaign_name = $campaign['label'];
        $aid->fnum          = $fnum;
        $aid->status        = $application['status'];

        //JError::raiseNotice('PERIOD', JText::sprintf('PERIOD', strftime("%d/%m/%Y %H:%M", strtotime($aid->start_date) ), strftime("%d/%m/%Y %H:%M", strtotime($aid->end_date) )));
        $this->setRedirect($redirect);
    }


    function upload() {
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $copy_application_form = $eMConfig->get('copy_application_form', 0);
        $can_submit_encrypted = $eMConfig->get('can_submit_encrypted', 1);
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');

        $db = JFactory::getDBO();

        $student_id = JRequest::getVar('sid', null, 'GET', 'none',0);
        $duplicate  = JRequest::getVar('duplicate', null, 'GET', 'none',0);
        $layout     = JRequest::getVar('layout', null, 'GET', 'none',0);
        $format     = JRequest::getVar('format', null, 'GET', 'none',0);
        $itemid     = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $fnum       = JRequest::getVar('fnum', null, 'GET', 'none',0);
        
        $fnums = array();
        $current_user = JFactory::getUser();
        $modelFiles = $this->getModel('files');

        if (EmundusHelperAccess::isApplicant($current_user->id)) {
            $user = $current_user;
            $fnum = $user->fnum;
            
            if ($copy_application_form == 1 && $duplicate == 1)
               $fnums = array_keys($user->fnums);
            else 
                $fnums[] = $fnum;

        } elseif(EmundusHelperAccess::asAccessAction(4, 'c', $current_user->id, $fnum) || 
                 EmundusHelperAccess::asAdministratorAccessLevel($current_user->id)) {
            $user = JFactory::getUser($student_id);
            $fnums[] = $fnum;
        } else {
            JError::raiseError(500, JText::_('ACCESS_DENIED'));
            return false;
        }

        $chemin         = EMUNDUS_PATH_ABS;
        $post           = JRequest::get('post');
        $attachments    = $post['attachment'];
        $descriptions   = $post['description'];
        $labels         = $post['label'];
 
        if (!empty($_FILES)) {
            $files          = array($_FILES["file"]);
        } else {
            $error = JUri::getInstance().' :: USER ID : '.$user->id;
            JLog::add($error, JLog::ERROR, 'com_emundus');
            
            if ($format == "raw")
                echo '{"aid":"0","status":false,"message":"'.$error.' -> empty $_FILES" }';
                
            return false;
        }

        
        $query = '';
        $nb = 0;

        if(!file_exists(EMUNDUS_PATH_ABS.$user->id)) {
            if (!mkdir(EMUNDUS_PATH_ABS.$user->id) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$user->id.DS.'index.html')){
                $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> Unable to create user file';
                JLog::add($error, JLog::ERROR, 'com_emundus');
               
                if ($format == "raw")
                    echo '{"aid":"0","status":false,"message":"'.$error.'" }';
                else
                    JError::raiseWarning(500, 'Unable to create user file');
                
                return false;
            }
        }
        chmod(EMUNDUS_PATH_ABS.$user->id, 0755);

        if (isset($layout))
            $url = 'index.php?option=com_emundus&view=checklist&layout=attachments&sid='.$user->id.'&tmpl=component&Itemid='.$itemid.'#a'.$attachments;
        else
            $url = 'index.php?option=com_emundus&view=checklist&Itemid='.$itemid.'#a'.$attachments;

        foreach ($fnums as $key => $fnum) {

            foreach ($files as $key => $file) {
            
                if (empty($file['name'])) {
                    $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> try to upload empty file';
                    JLog::add($error, JLog::ERROR, 'com_emundus');
                    $errorInfo = JText::_("COM_EMUNDUS_ERROR_INFO_EMPTYFILE");
                    
                    if ($format == "raw")
                        echo '{"aid":"0","status":false,"message":"'.$errorInfo.'" }';
                    else
                        JFactory::getApplication()->enqueueMessage($errorInfo."\n", 'error');
                    
                    return false;
                }
       
                try {
                    $query_ext = 'SELECT UPPER(allowed_types) as allowed_types, nbmax FROM #__emundus_setup_attachments WHERE id = '.(int)$attachments;
                    $db->setQuery( $query_ext );
                    $attachment = $db->loadAssoc();

                    try {
                        $query_cpt = 'SELECT count(id) FROM #__emundus_uploads WHERE user_id='.$user->id.' AND attachment_id='.(int)$attachments.' AND fnum like '.$db->Quote($fnum);
                        $db->setQuery( $query_cpt );
                        $cpt = $db->loadResult();

                        if ($cpt >= $attachment['nbmax']) {
                            $error = JText::_('MAX_ALLOWED').$attachment['nbmax'];
                            if ($format == "raw") {
                                echo '{"aid":"0","status":false,"message":"'.$error.'" }';
                            } else {
                                JFactory::getApplication()->enqueueMessage($error, 'error');
                                $this->setRedirect($url);
                            }

                            continue;
                        }
                    }
                    catch(Exception $e) {
                        $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                        JLog::add($error, JLog::ERROR, 'com_emundus');
                        $errorInfo = JText::_("COM_EMUNDUS_ERROR_INFO_SQL");

                        if ($format == "raw") {
                            echo '{"aid":"0","status":false,"message":"'.$errorInfo.'" }';
                        } else {
                            JFactory::getApplication()->enqueueMessage($errorInfo, 'error');
                            $this->setRedirect($url);
                        }

                        continue;
                    }
                }
                catch(Exception $e) {
                    $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                    JLog::add($error, JLog::ERROR, 'com_emundus');
                    $errorInfo = JText::_("COM_EMUNDUS_ERROR_INFO_SQL");

                    if ($format == "raw") {
                        echo '{"aid":"0","status":false,"message":"'.$errorInfo.'" }';
                    } else {
                        JFactory::getApplication()->enqueueMessage($errorInfo, 'error');
                        $this->setRedirect($url);
                    }

                    continue;
                }
                
                $file_array = explode('.', $file['name']);
                $file_ext = end($file_array);
                $pos = strpos($attachment['allowed_types'], strtoupper($file_ext));
                if ($pos === false) {
                    $error = JUri::getInstance().' :: USER ID : '.$user->id.' '.$file_ext.' -> type is not allowed, please send a doc with type : '.$attachment['allowed_types'];
                    //JLog::add($error, JLog::ERROR, 'com_emundus');
                    $errorInfo = JText::_("COM_EMUNDUS_ERROR_INFO_FILETYPE");
                    
                    if ($format == "raw") {
                        echo '{"aid":"0","status":false,"message":"'.$errorInfo.$attachment['allowed_types'].'" }';
                    } else {
                        JFactory::getApplication()->enqueueMessage($errorInfo.$attachment['allowed_types'], 'error');
                        $this->setRedirect($url);
                    }

                    return false;
                }

                //size > 0
                if (($file['size']) == 0) {
                    $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> size is not allowed, please check out your filesize : '.$file['size'];
                    JLog::add($error, JLog::ERROR, 'com_emundus');
                    $errorInfo = JText::_("COM_EMUNDUS_ERROR_INFO_FILETYPE");

                    if ($format == "raw") {
                        echo '{"aid":"0","status":false,"message":"'.$errorInfo.'" }';
                    } else {
                        JFactory::getApplication()->enqueueMessage($errorInfo, 'error');
                        $this->setRedirect($url);
                    }

                    return false;
                }

                // If encrypted pdf files are not allowed
                if ($can_submit_encrypted == 0 && strtoupper($file_ext) === "PDF") {
                    // Check if file is readable
                    if (!is_readable($file['tmp_name'])) {
                        $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> this file cannot be opened, please check if it is corrupt';
                        JLog::add($error, JLog::ERROR, 'com_emundus');
                        $errorInfo = JText::_("COM_EMUNDUS_ERROR_INFO_UNREADABLE");
                        
                        
                        if ($format == "raw") {
                            echo '{"aid":"0","status":false,"message":"'.$errorInfo.'" }';
                        } else {
                            JFactory::getApplication()->enqueueMessage($errorInfo, 'error');
                            $this->setRedirect($url);
                        }
                         
                        return false;
                    }

                    // Encrpyted pdf files are readable but require a password to be opened, this checks for this use-case
                    if (EmundusHelperExport::isEncrypted($file['tmp_name'])) {
                        $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> encrypted pdf files are not allowed, please remove protection and try again';
                        JLog::add($error, JLog::ERROR, 'com_emundus');
                        $errorInfo = JText::_("COM_EMUNDUS_ERROR_INFO_ENCRYPTED");

                        if ($format == "raw") {
                            echo '{"aid":"0","status":false,"message":"'.$errorInfo.'" }';
                        } else {
                            JFactory::getApplication()->enqueueMessage($errorInfo, 'error');
                            $this->setRedirect($url);
                        }
                        
                        return false;
                    } 
                }

                if (!empty($file['error'])) {

                    switch ($file['error']) {
                        case 1:
                            $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> file error type : '.JText::_("File ").$file['name'].JText::_(" is bigger than the authorized size!");
                            $errorInfo = JText::_("FILE").$file['name'].JText::_("COM_EMUNDUS_ERROR_INFO_MAX_ALLOWED_SIZE");
                            JFactory::getApplication()->enqueueMessage($errorInfo, 'error');
                            break;
                        case 2:
                            $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> file error type : '.JText::_("File ").$file['name'].JText::_(" is too big!\n");
                            $errorInfo = JText::_("FILE").$file['name'].JText::_("COM_EMUNDUS_ERROR_INFO_TOO_BIG");
                            JFactory::getApplication()->enqueueMessage($errorInfo, 'error');
                            break;
                        case 3:
                            $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> file error type : '.JText::_("File ").$file['name'].JText::_(" upload has been interrupted.\n");
                            $errorInfo = JText::_("FILE").$file['name'].JText::_("COM_EMUNDUS_ERROR_INFO_INTERRUPTED");
                            JFactory::getApplication()->enqueueMessage($errorInfo, 'error');
                            break;
                        case 4:
                            $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> file error type : '.JText::_("File ").$file['name'].JText::_(" is not correct.\n");
                            $errorInfo = JText::_("FILE").$file['name'].JText::_("COM_EMUNDUS_ERROR_INFO_INCORRECT");
                            JFactory::getApplication()->enqueueMessage($errorInfo, 'error');
                            break;
                        default:
                    }

                    JLog::add($error, JLog::ERROR, 'com_emundus');
                    if ($format == "raw") {
                        echo '{"aid":"0","status":false,"message":"'.$errorInfo.'" }';
                    } else {
                        JFactory::getApplication()->enqueueMessage($errorInfo, 'error');
                        $this->setRedirect($url);
                    }

                    return false;

                } elseif (isset($file['name']) && $file['error'] == UPLOAD_ERR_OK) {
                    $paths = strtolower(preg_replace(array('([\40])','([^a-zA-Z0-9-])','(-{2,})'),array('_','','_'),preg_replace('/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/','$1',htmlentities($user->name,ENT_NOQUOTES,'UTF-8'))));
                    $file_array = explode(".", $file['name']);
                    $paths .= $labels.rand().'.'.end($file_array);
                    if (copy( $file['tmp_name'], $chemin.$user->id.DS.$paths)) {
                        $can_be_deleted = @$post['can_be_deleted_'.$attachments]!=''?$post['can_be_deleted_'.$attachments]:JRequest::getVar('can_be_deleted', 1, 'POST', 'none',0);
                        $can_be_viewed = @$post['can_be_viewed_'.$attachments]!=''?$post['can_be_viewed_'.$attachments]:JRequest::getVar('can_be_viewed', 1, 'POST', 'none',0);
                        
                        $fnumInfos = $modelFiles->getFnumInfos($fnum);

                        $query .= '('.$user->id.', '.$attachments.', \''.$paths.'\', '.$db->Quote($descriptions).', '.$can_be_deleted.', '.$can_be_viewed.', '.$fnumInfos['id'].', '.$db->Quote($fnum).'),';
                        $nb++;
                    } else {
                        $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> Cannot move file : '.$file['tmp_name'].' to '.$chemin.$user->id.DS.$paths;
                        JLog::add($error, JLog::ERROR, 'com_emundus');
                        $errorInfo = JText::_("COM_EMUNDUS_ERROR_CANNOT_MOVE").$file['name'];

                        if ($format == "raw") {
                            echo '{"aid":"0","status":false,"message":"'.$errorInfo.'" }';
                        } else {
                            JFactory::getApplication()->enqueueMessage($errorInfo, 'error');
                            $this->setRedirect($url);
                        }

                    return false;
                    }

                    if ($labels=="_photo") {

                        $checkdouble_query = 'SELECT count(user_id) 
                        FROM #__emundus_uploads 
                        WHERE attachment_id=
                                (SELECT id 
                                    FROM #__emundus_setup_attachments 
                                    WHERE lbl="_photo"
                                ) 
                                AND user_id='.$user->id. ' 
                                AND fnum like '.$db->Quote($fnum);
                        
                        try {
                            $db->setQuery($checkdouble_query);
                            $cpt = $db->loadResult();
                        }
                        catch(Exception $e) {
                            $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                            JLog::add($error, JLog::ERROR, 'com_emundus');
                            $errorInfo = JText::_("COM_EMUNDUS_ERROR_INFO_SQL");

                            if ($format == "raw") {
                                echo '{"aid":"0","status":false,"message":"'.$errorInfo.'" }';
                            } else {
                                JFactory::getApplication()->enqueueMessage($errorInfo, 'error');
                                $this->setRedirect($url);
                            }

                            return false;
                        }

                        if ($cpt) {
                            $query = '';
                            return false;
                        } else {
                            $pathToThumbs = EMUNDUS_PATH_ABS.$user->id.DS.$paths;
                            $file_src = EMUNDUS_PATH_ABS.$user->id.DS.$paths;
                            //$img = imagecreatefromjpeg(EMUNDUS_PATH_ABS.$user->id.DS.$paths);
                            list($w_src, $h_src, $type) = getimagesize($file_src);  // create new dimensions, keeping aspect ratio
                            //$ratio = $w_src/$h_src;
                            //if ($w_dst/$h_dst > $ratio) {$w_dst = floor($h_dst*$ratio);} else {$h_dst = floor($w_dst/$ratio);}

                            switch ($type){
                                case 1:   //   gif -> jpg
                                    $img = imagecreatefromgif($file_src);
                                    break;
                                case 2:   //   jpeg -> jpg
                                    $img = imagecreatefromjpeg($file_src);
                                    break;
                                case 3:  //   png -> jpg
                                    $img = imagecreatefrompng($file_src);
                                    break;
                                default:
                                    $img = imagecreatefromjpeg($file_src);
                                    break;
                            }
                            //$width = imagesx( $img );
                            //$height = imagesy( $img );
                            $new_width = 200;
                            $new_height = floor( $h_src * ( $new_width / $w_src ) );
                            $tmp_img = imagecreatetruecolor( $new_width, $new_height );
                            imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $w_src, $h_src );
                            imagejpeg( $tmp_img, $chemin.$user->id.DS.'tn_'.$paths);
                            $user->avatar = $paths;
                        }
                    }
                }
            }
        }
        // delete temp uploaded file
        unlink($file['tmp_name']);

        if(!empty($query)) {
            $query = 'INSERT INTO #__emundus_uploads (user_id, attachment_id, filename, description, can_be_deleted, can_be_viewed, campaign_id, fnum) 
                        VALUES '.substr($query,0,-1);
            
            try {
                $db->setQuery( $query );
                $db->execute();
                $id = $db->insertid();

                if ($format == "raw") {
                    echo '{"id":"'.$id.'","status":true, "message":"'.JText::_('DELETE').'"}';
                } else {
                    JFactory::getApplication()->enqueueMessage($nb.($nb>1?' '.JText::_("FILES_BEEN_UPLOADED"):' '.JText::_("FILE_BEEN_UPLOADED")), 'message');
                    $this->setRedirect($url);
                }
            }
            catch(Exception $e) {
                $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                JLog::add($error, JLog::ERROR, 'com_emundus');
                $errorInfo = JText::_("COM_EMUNDUS_ERROR_INFO_SQL");
                
                if ($format == "raw") {
                    echo '{"aid":"0","status":false,"message":"'.$errorInfo.'" }';
                } else {
                    JFactory::getApplication()->enqueueMessage($errorInfo, 'error');
                    $this->setRedirect($url);
                }
            }
        }
        
        return true;
    }

    /***********************************
     ** Update profile for Applicants
     ***********************************/
    function updateprofile() {
        $user = JFactory::getUser();
        if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
            $this->setRedirect(JRoute::_('index.php'), JText::_('Only administrator can access this function.'), 'error');
            return;
        }
        $attachment_id = JRequest::getVar('aid', $default=null, $hash= 'POST', $type= 'array', $mask=0);
        $attachment_selected = JRequest::getVar('as', $default=null, $hash= 'POST', $type= 'array', $mask=0);
        $attachment_displayed = JRequest::getVar('ad', $default=null, $hash= 'POST', $type= 'array', $mask=0);
        $attachment_required = JRequest::getVar('ar', $default=null, $hash= 'POST', $type= 'array', $mask=0);
        $attachment_bank_needed = JRequest::getVar('ab', $default=null, $hash= 'POST', $type= 'array', $mask=0);
        $profile_id = JRequest::getVar('pid', $default=null, $hash= 'POST', $type= 'none', $mask=0);
        if($profile_id != JRequest::getVar('rowid', $default=null, $hash= 'GET', $type= 'none', $mask=0) || !is_numeric($profile_id) || floor($profile_id) != $profile_id || $profile_id <= 0) {
            $this->setRedirect('index.php', 'Error', 'error');
            return;
        }
        if(!empty($attachment_selected)) {
            $attachments = array();
            $a = new stdClass();
            foreach($attachment_id as $id) {
                $a->selected = @in_array($id, $attachment_selected);
                $a->displayed = @in_array($id, $attachment_displayed);
                $a->required = @in_array($id, $attachment_required);
                $a->bank_needed = @in_array($id, $attachment_bank_needed);
                $attachments[$id] = $a;
                unset($a);
            }

        }

        $db = JFactory::getDBO();
// ATTACHMENTS
        $db->setQuery('DELETE FROM #__emundus_setup_attachment_profiles WHERE profile_id = '.$profile_id);
        $db->execute() or die($db->getErrorMsg());
        if(isset($attachments)) {
            $query = 'INSERT INTO #__emundus_setup_attachment_profiles (`profile_id`, `attachment_id`, `displayed`, `mandatory`, `bank_needed`) VALUES';
            foreach($attachments as $id => $attachment) {
                if(!$attachment->selected) continue;
                $query .= '('.$profile_id.', '.$id.', ';
                $query .= $attachment->displayed?'1':'0';
                $query .= ', ';
                $query .= $attachment->required?'1':'0';
                $query .= ', ';
                $query .= $attachment->bank_needed?'1':'0';
                $query .= '),';
            }
            $db->setQuery( substr($query, 0, -1) );
            $db->execute() or die($db->getErrorMsg());
        }
// FORMS
        $Itemid = JRequest::getVar('Itemid', null, 'POST', 'none',0);
        $this->setRedirect('index.php?option=com_emundus&view=profile&rowid='.$profile_id.'&Itemid='.$Itemid, '', '');
    }


    /**
     * Get application form elements to display in CSV file
     */
    function send_elements_csv() {
        $view = JRequest::getVar('v', null, 'GET');
        // Starting a session.
        $session =& JFactory::getSession();
        $cid = $session->get( 'uid' );
        $quick_search = $session->get( 'quick_search' );

        $user =& JFactory::getUser();
        $menu=JSite::getMenu()->getActive();
        $access=!empty($menu)?$menu->access : 0;
        if (!EmundusHelperAccess::isAllowedAccessLevel($user->id, $access)) {
            die(JText::_('ACCESS_DENIED'));
        }
        require_once(JPATH_BASE.DS.'libraries'.DS.'emundus'.DS.'export_csv'.DS.'csv_'.$view.'.php');
        $elements = JRequest::getVar('ud', null, 'POST', 'array', 0);

        export_csv($cid, $elements);
    }

    function transfert_view($reqids=array()) {
        //$allowed = array("Super Users", "Administrator", "Editor");
        $view = JRequest::getVar('v', null, 'GET');

        $profile        = JRequest::getVar('profile', null, 'POST', 'none', 0);
        $finalgrade     = JRequest::getVar('finalgrade', null, 'POST', 'none', 0);
        $quick_search   = JRequest::getVar('s', null, 'POST', 'none',0);
        $gid            = JRequest::getVar('groups', null, 'POST', 'none', 0);
        $evaluator      = JRequest::getVar('user', null, 'POST', 'none', 0);
        $engaged        = JRequest::getVar('engaged', null, 'POST', 'none', 0);
        $schoolyears    = JRequest::getVar('schoolyears', null, 'POST', 'none', 0);
        $itemid         = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $miss_doc       = JRequest::getVar('missing_doc', null, 'POST', 'none',0);
        $search         = JRequest::getVar('elements', null, 'POST', 'array', 0);
        $search_values  = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
        $comments       = JRequest::getVar('comments', null, 'POST', 'none', 0);
        $complete       = JRequest::getVar('complete', null, 'POST', 'none',0);
        $validate       = JRequest::getVar('validate', null, 'POST', 'none',0);
        $cid            = JRequest::getVar('ud', null, 'POST', 'array', 0);
        /*
        foreach($cids_params as $cid_params){
            $params=explode('|',$cid_params);
            $cid[]=$params[0];
        }*/

        // Starting a session.
        $session = JFactory::getSession();
        if($cid) $session->set( 'uid', $cid );
        if($profile) $session->set( 'profile', $profile );
        if($finalgrade) $session->set( 'finalgrade', $finalgrade );
        if($quick_search) $session->set( 'quick_search', $quick_search );
        if($gid) $session->set( 'groups', $gid );
        if($evaluator) $session->set( 'evaluator', $evaluator );
        if($engaged) $session->set( 'engaged', $engaged );
        if($schoolyears) $session->set( 'schoolyears', $schoolyears );
        if($miss_doc) $session->set( 'missing_doc', $miss_doc );
        if($search) $session->set( 's_elements', $search );
        if($search_values) $session->set( 's_elements_values', $search_values );
        if($comments) $session->set( 'comments', $comments );
        if($complete) $session->set( 'complete', $complete );
        if($validate) $session->set( 'validate', $validate );

        $this->setRedirect('index.php?option=com_emundus&view=export_select_columns&v='.$view.'&Itemid='.$itemid);
    }

    function get_mime_type($filename) {
        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }

    /**
     * Check if user can or not open PDF file
     */
    function getfile() {
        $db = JFactory::getDBO();

        $url = $_GET['u'];
        $urltab = explode('/', $url);

        $cpt = count($urltab);
        $uid = $urltab[$cpt-2];
        $file = $urltab[$cpt-1];

        $current_user = JFactory::getUser();

        if ( !EmundusHelperAccess::asEvaluatorAccessLevel($current_user->id) && (EmundusHelperAccess::isApplicant($current_user->id) && $current_user->id != $uid) ) {
            JError::raiseWarning( 500, JText::_( 'ACCESS_DENIED' ) );
        } else {
            // Check if document can be viewed by applicant
            if (EmundusHelperAccess::isApplicant($current_user->id)) {
                $query = 'SELECT can_be_viewed FROM #__emundus_uploads WHERE user_id = '.$uid.' AND filename like '.$db->Quote($file);
                $db->setQuery( $query );
                $can_be_viewed = $db->loadResult();
                if ($can_be_viewed != 1) {
                    die(JText::_( 'ACCESS_DENIED' ));
                }
            }
            $file = JPATH_BASE.DS.$url;
            if (file_exists($file)) {
                $mime_type = $this->get_mime_type($file);
                //header('Content-type: application/'.$mime_type);
                header('Content-type: '.$mime_type);
                header('Content-Disposition: inline; filename='.basename($file));
                header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('Cache-Control: pre-check=0, post-check=0, max-age=0');
                header('Pragma: anytextexeptno-cache', true);
                header('Cache-control: private');
                header('Expires: 0');
                //header('Content-Transfer-Encoding: binary');
                //header('Content-Length: ' . filesize($file));
                //header('Accept-Ranges: bytes');

                ob_clean();
                flush();
                readfile($file);
                exit;
            } else {
                JError::raiseWarning( 500, JText::_( 'FILE_NOT_FOUND' ).' '.$file );
                //$this->setRedirect('index.php?option=com_emundus&view='.$view.'&Itemid='.$Itemid);
            }
        }
    }

    /*  function get_mime_type($filename, $mimePath = '../etc') {
           $fileext = substr(strrchr($filename, '.'), 1);
           if (empty($fileext)) return (false);
           $regex = "/^([\w\+\-\.\/]+)\s+(\w+\s)*($fileext\s)/i";
           $lines = file("$mimePath/mime.types");
           foreach($lines as $line) {
              if (substr($line, 0, 1) == '#') continue; // skip comments
              $line = rtrim($line) . " ";
              if (!preg_match($regex, $line, $matches)) continue; // no match to the extension
              return ($matches[1]);
           }
           return (false); // no match at all
        } */



    function sendmail($nb_email_per_batch = null) {
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $db = JFactory::getDBO();
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $keyid = JRequest::getVar('keyid', null, 'GET', 'none',0);
        //$allowed = array("Super Users", "Administrator");
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $model = $this->getModel('emailalert');

        if (EmundusHelperAccess::isAdministrator($user->id) && EmundusHelperAccess::isCoordinator($user->id) && EmundusHelperAccess::isPartner($user->id) && EmundusHelperAccess::isEvaluator($user->id)) {
            if ($nb_email_per_batch == null)
                $nb_email_per_batch = $eMConfig->get('nb_email_per_batch', '30');

            //Selection des mails à envoyer : table jos_emundus_emailtosend
            $query = '  SELECT m.user_id_from, m.user_id_to, m.subject, m.message, u.email
                        FROM #__messages m, #__users u 
                        WHERE m.user_id_to = u.id
                        AND m.state = 1
                        LIMIT 0,'.$nb_email_per_batch;
            $db->setQuery( $query );
            $db->execute();

            if($db->getNumRows() == 0) {
                $this->setRedirect('index.php?option=com_fabrik&view=table&tableid=90&Itemid='.$itemid);
            } else {
                $mail=$db->loadObjectList();

                foreach($mail as $m) {
                    $mail_subject = $m->subject;
                    //$from = JFactory::getUser($m->user_id_from);
                    $emailfrom = $app->getCfg('mailfrom');
                    $fromname = $app->getCfg('fromname');
                    $recipient = $m->email;
                    $body = $m->message;
                    if (JUtility::sendMail( $emailfrom, $fromname, $recipient, $mail_subject, $body, true)) {
                        usleep(100);
                        $query = 'UPDATE #__messages SET state = 0 WHERE user_id_to ='.$m->user_id_to;
                        $db->setQuery($query);
                        $db->execute();
                    }
                }
                $this->setRedirect('index.php?option=com_emundus&task=sendmail&keyid='.$keyid.'&Itemid='.$itemid);
            }
        }
    }

    function sendmail_applicant() {
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $sid = JRequest::getVar('mail_to', null, 'POST', 'INT',0);
        $campaign_id = JRequest::getVar('campaign_id', null, 'POST', 'INT',0);
        $model = $this->getModel('emails');
        $email = $model->sendmail();

        $model = $this->getModel('campaign');
        $email = $model->setResultLetterSent($sid, $campaign_id);


        $this->setRedirect('index.php?option=com_emundus&view=application&Itemid='.$itemid.'&sid='.$sid.'&tmpl=component');
    }

    function sendmail_expert() {
        if ( !EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id) ) {
            die(JError::raiseWarning( 500, JText::_( 'ACCESS_DENIED' ) ));
        }
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $sid = JRequest::getVar('sid', null, 'GET', 'INT',0);
        $fnum = JRequest::getVar('fnum', null, 'GET');
        //$campaign_id = JRequest::getVar('campaign_id', null, 'POST', 'INT',0);
        $model = $this->getModel('emails');
        $email = $model->sendmail('expert', $fnum);

        exit();
        /*
                $model = $this->getModel('campaign');
                $email = $model->setResultLetterSent($sid, $campaign_id);
                */

        //$this->setRedirect('index.php?option=com_emundus&view=application&Itemid='.$itemid.'&sid='.$sid.'&tmpl=component');
    }

    /*
    ** @description Validate / Unvalidate a column from table (used in administrative validation). Ajax request
    ** @return string HTML to display in page for action block indexed by user ID.
    */
    function ajax_validation() {
        //$menu=JSite::getMenu()->getActive(); die(print_r($menu));
        //$access=!empty($menu)?$menu->access : 0;
        $user = JFactory::getUser();
        if (!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
            die(JText::_('ACCESS_DENIED'));
        }
        $uid = JRequest::getVar('uid', null, 'GET', 'INT');
        $validate = JRequest::getVar('validate', null, 'GET', 'INT');
        $cible = JRequest::getVar('cible', null, 'GET', 'CMD');
        $data = explode('.', $cible);
        if(count($data)>3)  $and = ' AND `campaign_id`='.$data[3];
        else $and = '';
        if($data[0] == "jos_emundus_final_grade") $column = "student_id";
        else $column = 'user';

//      print_r($data);
        if(!empty($uid) && is_numeric($uid)) {
            $value = abs((int)$validate-1);
            $db = JFactory::getDBO();
            $query = 'UPDATE `'.$data[0].'` SET `'.$data[1].'`='.$db->Quote($value).' WHERE `'.$column.'` = '.$db->Quote((int)$uid). $and;
            $db->setQuery($query);
            $db->execute();
            if ($value > 0) {
                $img = 'tick.png';
                $btn = 'unvalidate|'.$uid;
                $alt = JText::_('VALIDATED').'::'.JText::_('VALIDATED_NOTE');
            } else {
                $img = 'publish_x.png';
                $btn = 'validate|'.$uid;
                $alt = JText::_('UNVALIDATED').'::'.JText::_('UNVALIDATED_NOTE');
            }
            echo '<span class="hasTip" title="'.$alt.'">
                    <input type="image" src="'.JURI::Base().'/media/com_emundus/images/icones/'.$img.'" onclick="validation('.$uid.', \''.$value.'\', \''.$cible.'\');" ></span> ';
        } else echo JText::_('ERROR');

    }
}
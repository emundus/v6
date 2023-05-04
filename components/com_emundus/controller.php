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
use \setasign\Fpdi\Fpdi;
use \setasign\Fpdi\PdfReader;

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
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        include_once (JPATH_COMPONENT.DS.'models'.DS.'profile.php');
        include_once (JPATH_COMPONENT.DS.'models'.DS.'campaign.php');
        include_once (JPATH_COMPONENT.DS.'models'.DS.'logs.php');
        include_once (JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');


        $this->_user = JFactory::getSession()->get('emundusUser');
        $this->_db = JFactory::getDBO();

        parent::__construct($config);
    }

    function display($cachable = false, $urlparams = false) {
        // Set a default view if none exists
        if (!JRequest::getCmd('view')) {
            if (!empty($this->_user->usertype) && $this->_user->usertype == "Registered") {
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
        $user = JFactory::getSession()->get('emundusUser');
        $jinput = JFactory::getApplication()->input;
        $student_id = $jinput->get('user', null, 'string');
        $fnum = $jinput->get('fnum', null, 'string');
        $profile = $jinput->get('profile', null, 'string');

        $fnum = !empty($fnum)?$fnum:$user->fnum;
        $m_profile = new EmundusModelProfile();
        $m_campaign = new EmundusModelCampaign();

        $options = array(
          'aemail',
          'afnum',
          'adoc-print',
          'aapp-sent',
        );

        $infos 		= $m_profile->getFnumDetails($fnum);
        $workflow_infos = $m_campaign->getCurrentCampaignWorkflow($fnum);

        if($profile == null) {
            $profile 	= !empty($infos['profile']) ? $infos['profile'] : $infos['profile_id'];
        }

        if($workflow_infos->profile !== null)  {
            $profile = $workflow_infos->profile;
        }

        $h_menu = new EmundusHelperMenu;
        $getformids = $h_menu->getUserApplicationMenu($profile);

	    $formid = [];
        foreach ($getformids as $getformid) {
            $formid[] = $getformid->form_id;
        }

        if (!empty($fnum)) {
            $candidature = $m_profile->getFnumDetails($fnum);
            $campaign = $m_campaign->getCampaignByID($candidature['campaign_id']);
        }

        $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_'.$campaign['training'].'.php';
        $file_custom = JPATH_LIBRARIES.DS.'emundus'.DS.'custom'.DS.'pdf_'.$campaign['training'].'.php';
        if (!file_exists($file) && !file_exists($file_custom)) {
            $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php';
        }
        else{
            if (file_exists($file_custom)){
                $file = $file_custom;
            }
        }

        if (!file_exists(EMUNDUS_PATH_ABS.$student_id)) {
            mkdir(EMUNDUS_PATH_ABS.$student_id);
            chmod(EMUNDUS_PATH_ABS.$student_id, 0755);
        }

        require_once($file);

        if (EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            application_form_pdf(!empty($student_id)?$student_id:$user->id, $fnum, true, 1, null, $options, null, $profile,null,null);
            exit;
        } elseif (EmundusHelperAccess::isApplicant($user->id)) {
            application_form_pdf($user->id, $fnum, true, 1, $formid, $options, null, $profile,null,null);
            exit;
        } else {
            die(JText::_('ACCESS_DENIED'));
        }
    }

    function pdf_by_form() {
        $user = JFactory::getSession()->get('emundusUser');
        $jinput = JFactory::getApplication()->input;
        $student_id = $jinput->get('user', null, 'string');
        $fnum = $jinput->get('fnum', null, 'string');
        $formid = [$jinput->get('form', null, 'string')];

        $fnum = !empty($fnum)?$fnum:$user->fnum;
        $m_profile = $this->getModel('profile');
        $m_campaign = $this->getModel('campaign');

        $options = array(
            'aemail',
            'afnum',
            'adoc-print',
            'aapp-sent',
        );

        $infos 		= $m_profile->getFnumDetails($fnum);


        if (!empty($fnum)) {
            $candidature = $m_profile->getFnumDetails($fnum);
            $campaign = $m_campaign->getCampaignByID($candidature['campaign_id']);
        }

        $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_'.$campaign['training'].'.php';
        $file_custom = JPATH_LIBRARIES.DS.'emundus'.DS.'custom'.DS.'pdf_'.$campaign['training'].'.php';
        if (!file_exists($file) && !file_exists($file_custom)) {
            $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php';
        }
        else{
            if (file_exists($file_custom)){
                $file = $file_custom;
            }
        }

        if (!file_exists(EMUNDUS_PATH_ABS.$student_id)) {
            mkdir(EMUNDUS_PATH_ABS.$student_id);
            chmod(EMUNDUS_PATH_ABS.$student_id, 0755);
        }

        require_once($file);

        // Here we call the profile by fnum function, which will get the candidate's profile in the status table
        // $profile_id = $m_profile->getProfileByFnum($fnum);

        if (EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            //application_form_pdf(!empty($student_id)?$student_id:$user->id, $fnum, true, 1, null, $options, null, $profile_id,null,null);
            application_form_pdf(!empty($student_id)?$student_id:$user->id, $fnum, true, 1, $formid, $options);
            exit;
        } else {
            die(JText::_('ACCESS_DENIED'));
        }
    }


    /**
     * Function that will print the candidat's PDF depending on their file status
     * Returns
     * @throws Exception
     */
    function pdf_by_status() {
        $user = JFactory::getSession()->get('emundusUser');
        $jinput = JFactory::getApplication()->input;
        $student_id = $jinput->get('user', null, 'string');
        $profile = $jinput->get('profile', null, 'string');

        $fnum = $jinput->get('fnum', null, 'string');
        $fnum = !empty($fnum)?$fnum:$user->fnum;
        // Don't go any further if we don't find a fnum
        if (empty($fnum)) {
            die(JText::_('ACCESS_DENIED'));
        }

        $m_profile = $this->getModel('profile');
        $m_campaign = $this->getModel('campaign');

        // We need to determine if the profile is set by the status or the campaign
        $infos = $m_profile->getProfileByStatus($fnum);
        if(empty($infos['profile'])){
            $infos = $m_profile->getFnumDetails($fnum);
        }

        if (empty($profile)) {
            $profile = !empty($infos['profile']) ? $infos['profile'] : $infos['profile_id'];
        }

        // Now we can start gettting the forms linked to the correct profile
        $h_menu = new EmundusHelperMenu;
        $getformids = $h_menu->getUserApplicationMenu($profile);

        $formid = [];
        foreach ($getformids as $getformid) {
            $formid[] = $getformid->form_id;
        }

        $campaign = $m_campaign->getCampaignByID($infos['campaign_id']);

        $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_'.@$campaign['training'].'.php';
        if (!file_exists($file)) {
            $file = JPATH_LIBRARIES . DS . 'emundus' . DS . 'pdf.php';
        }

        if (!file_exists(EMUNDUS_PATH_ABS.$student_id)) {
            mkdir(EMUNDUS_PATH_ABS.$student_id);
            chmod(EMUNDUS_PATH_ABS.$student_id, 0755);
        }

        if (file_exists($file)) {
            require_once($file);

            // Here we call the profile by fnum function, which will get the candidate's profile in the status table
            if (EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
                $student = !empty($student_id) ? $student_id : $user->id;
                application_form_pdf($student, $fnum, true, 1, null, null, null, $profile);
                exit;
            } elseif (EmundusHelperAccess::isApplicant($user->id)) {
                application_form_pdf($user->id, $fnum, true, 1, null, null, null, $profile);
                exit;
            } else {
                die(JText::_('ACCESS_DENIED'));
            }
        } else {
            die(JText::_('ACCESS_DENIED'));
        }
    }

    function pdf_emploi(){
        $user = JFactory::getSession()->get('emundusUser');
        $student_id = JRequest::getVar('user', null, 'GET', 'none',0);
        $rowid = explode('-', JRequest::getVar('rowid', null, 'GET', 'none',0));

        $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_emploi.php';

        if (!file_exists($file)) {
            die(JText::_('COM_EMUNDUS_EXPORTS_FILE_NOT_FOUND'));
        }
        if (!file_exists(EMUNDUS_PATH_ABS.$student_id)) {
            mkdir(EMUNDUS_PATH_ABS.$student_id);
            chmod(EMUNDUS_PATH_ABS.$student_id, 0755);
        }

        require_once($file);

        if (EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            application_form_pdf(!empty($student_id)?$student_id:$user->id, $rowid[0], true);
        } else {
            die(JText::_('ACCESS_DENIED'));
        }

        exit();
    }

    function pdf_thesis() {
        $user = JFactory::getSession()->get('emundusUser');
        $student_id = JRequest::getVar('user', null, 'GET', 'none',0);
        $fnum = JRequest::getVar('fnum', null, 'GET', 'none',0);
        $rowid = explode('-', JRequest::getVar('rowid', null, 'GET', 'none',0));

        $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_thesis.php';

        if (!file_exists($file)) {
            die(JText::_('COM_EMUNDUS_EXPORTS_FILE_NOT_FOUND'));
        }
        if (!file_exists(EMUNDUS_PATH_ABS.$student_id)) {
            mkdir(EMUNDUS_PATH_ABS.$student_id);
            chmod(EMUNDUS_PATH_ABS.$student_id, 0755);
        }

        require_once($file);

        if (EmundusHelperAccess::asPartnerAccessLevel($user->id) || EmundusHelperAccess::isApplicant($user->id)) {
            application_form_pdf(!empty($student_id)?$student_id:$user->id, $rowid[0], true);
        } else {
            die(JText::_('ACCESS_DENIED'));
        }

        exit();
    }

    /*
        Delete file
    */
    function deletefile() {
        //@TODO ADD COMMENT ON DELETE
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $m_profile = new EmundusModelProfile;

        $student_id = $jinput->get->get('sid', null);
        $fnum = $jinput->get->get('fnum', null);
        $redirect = $jinput->get->getBase64('redirect', null);
        // Redirect URL is currently only used in Hesam template of mod_emundus_application, it allows for the module to be located on a page other than index.php.

        if (empty($redirect)) {
            $redirect = 'index.php';
        } else {
            $redirect = base64_decode($redirect);
        }

        if (empty($fnum)) {
            $app->redirect($redirect);
        }

        $current_user  = JFactory::getSession()->get('emundusUser');
        $m_files = $this->getModel('files');

        if (in_array($fnum, array_keys($current_user->fnums))){
            $user = $current_user;
            $m_files->deleteFile($fnum);
            EmundusModelLogs::log($current_user->id, (int)substr($fnum, -7), $fnum, 1, 'd', 'COM_EMUNDUS_ACCESS_FORM_DELETE');        } elseif (EmundusHelperAccess::asAccessAction(1, 'd', $current_user->id, $fnum) || EmundusHelperAccess::asAdministratorAccessLevel($current_user->id)) {
            $user = $m_profile->getEmundusUser($student_id);
        } else {
            JError::raiseError(500, JText::_('ACCESS_DENIED'));
            $app->redirect($redirect);
            return false;
        }

        // track the LOGS (ATTACHMENT_DELETE)
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
        $user = JFactory::getSession()->get('emundusUser');     # logged user #
        EmundusModelLogs::log($current_user->id, (int)substr($fnum, -7), $fnum, 1, 'd', 'COM_EMUNDUS_ACCESS_FILE_DELETE', '');

        unset($current_user->fnums[$fnum]);

        if (in_array($user->fnum, array_keys($user->fnums))) {
            $app->redirect($redirect);
        } else {
            array_shift($current_user->fnums);
            $app->redirect($redirect);
        }

        return true;
    }

    /* complete file */
    function completefile() {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $m_profile = new EmundusModelProfile;

        $student_id = $jinput->get->get('sid', null);
        $fnum = $jinput->get->getVar('fnum', null);
        $status = $jinput->get->get('status', null);
        $redirect = $jinput->get->getBase64('redirect', null);
        // Redirect URL is currently only used in Hesam template of mod_emundus_application, it allows for the module to be located on a page other than index.php.
        if (empty($redirect) || empty($status)) {
        	$redirect = 'index.php';
        } else {
        	$redirect = base64_decode($redirect);
        }

        if (empty($fnum)) {
        	$app->redirect($redirect);
        }

        $current_user = JFactory::getSession()->get('emundusUser');
        $m_files = $this->getModel('files');
        if (EmundusHelperAccess::isApplicant($current_user->id) && in_array($fnum, array_keys($current_user->fnums))) {
        	$user = $current_user;
            $m_files->updateState($fnum, $status);
        } elseif (EmundusHelperAccess::asAccessAction(1, 'd', $current_user->id, $fnum) || EmundusHelperAccess::asAdministratorAccessLevel($current_user->id)) {
            $user = $m_profile->updateState($student_id);
        } else {
            JError::raiseError(500, JText::_('ACCESS_DENIED'));
            $app->redirect($redirect);
            return false;
        }

        if (in_array($user->fnum, array_keys($user->fnums))) {
            $app->redirect($redirect);
        } else {
            array_shift($current_user->fnums);
            $app->redirect($redirect);
        }

        return true;
    }

    /* publish file */
    function publishfile() {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $m_profile = new EmundusModelProfile;

        $student_id    = $jinput->get->get('sid', null);
        $fnum          = $jinput->get->getVar('fnum', null);
        $status          = $jinput->get->get('status', null);
        $redirect      = $jinput->get->getBase64('redirect', null);
        // Redirect URL is currently only used in Hesam template of mod_emundus_application, it allows for the module to be located on a page other than index.php.

        if (empty($redirect))
            $redirect = 'index.php';
        else
            $redirect = base64_decode($redirect);

        if (empty($fnum))
            $app->redirect($redirect);

        $current_user  = JFactory::getSession()->get('emundusUser');
        $m_files = $this->getModel('files');

        if (EmundusHelperAccess::isApplicant($current_user->id) && in_array($fnum, array_keys($current_user->fnums))){
            $user = $current_user;
            $result = $m_files->updateState($fnum, $status);

        } elseif(EmundusHelperAccess::asAccessAction(1, 'd', $current_user->id, $fnum) ||
            EmundusHelperAccess::asAdministratorAccessLevel($current_user->id)) {
            $user = $m_profile->getEmundusUser($student_id);

        } else {
            JError::raiseError(500, JText::_('ACCESS_DENIED'));
            $app->redirect($redirect);

            return false;
        }

        if (in_array($user->fnum, array_keys($user->fnums))) {
            $app->redirect($redirect);
        } else {
            array_shift($current_user->fnums);
            $app->redirect($redirect);
        }

        return true;

    }

    /*
        Delete document from application file
    */
    function delete() {
        //TODO: ADD COMMENT ON DELETE
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $copy_application_form = $eMConfig->get('copy_application_form', 0);
        $m_profile = new EmundusModelProfile;
        $jinput = JFactory::getApplication()->input;

        $student_id    = $jinput->get->get('sid');
        $upload_id     = $jinput->get->get('uid');
        $attachment_id = $jinput->get->get('aid');
        $duplicate     = $jinput->get->get('duplicate');
        $nb            = $jinput->get->get('nb');
        $layout        = $jinput->get->get('layout');
        $format        = $jinput->get->get('format');
        $itemid        = $jinput->get('Itemid');
        $fnum          = $jinput->get->get('fnum');
        $current_user  = JFactory::getSession()->get('emundusUser');
        $status_for_send = $eMConfig->get('status_for_send',0);
        $chemin = EMUNDUS_PATH_ABS;
        $db = JFactory::getDBO();

        if (EmundusHelperAccess::isApplicant($current_user->id)) {
            $user = $current_user;
            $fnum = $user->fnum;
            if ($duplicate == 1 && $nb <= 1 && $copy_application_form == 1) {
                $fnums = implode(',', $db->Quote(array_keys($user->fnums)));
                $where = ' AND user_id='.$user->id.' AND attachment_id='.$attachment_id. ' AND `fnum` in (select fnum from `#__emundus_campaign_candidature` where `status` IN ('.$status_for_send.'))';
            } else {
                $fnums = $db->Quote($fnum);
                $where = ' AND user_id='.$user->id.' AND id='.$upload_id;
            }

        } elseif (EmundusHelperAccess::asAccessAction(4, 'd', $current_user->id, $fnum) || EmundusHelperAccess::asAdministratorAccessLevel($current_user->id)) {
            $user = $m_profile->getEmundusUser($student_id);
            $fnums = $db->Quote($fnum);
        } else {
            JError::raiseError(500, JText::_('ACCESS_DENIED'));
            return false;
        }

        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('emundus', 'sync_file');
        $dispatcher->trigger('onDeleteFile', array(array('upload_id' => $upload_id)));

        if (isset($layout))
            $url = 'index.php?option=com_emundus&view=checklist&layout=attachments&sid='.$user->id.'&tmpl=component&Itemid='.$itemid;
        else
            $url = 'index.php?option=com_emundus&view=checklist&Itemid='.$itemid;


        $query  = 'SELECT id, filename
                    FROM #__emundus_uploads
                    WHERE user_id = '.$user->id.'
                    AND fnum IN ('.$fnums.') '.$where;

        try {

            $db->setQuery($query);
            $files = $db->loadAssocList();

            $fileName = reset($files)['filename'];

            // call to application model
            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
            $mApp = new EmundusModelApplication;
            $attachmentTpe = $mApp->getAttachmentByID($attachment_id)['value'];

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
                            $message .= '<br>'.JText::_('COM_EMUNDUS_ATTACHMENTS_DELETED').' : '.$file['filename'].'. ';

                        } else {
                            $message .= '<br>'.JText::_('COM_EMUNDUS_EXPORTS_FILE_NOT_FOUND').' : '.$file['filename'].'. ';
                        }
                    }

                    $query  = 'DELETE FROM #__emundus_uploads
                                WHERE id IN ('.implode(',', $file_id).')
                                AND user_id = '.$user->id. '
                                AND fnum IN ('.$fnums.')';
                    $db->setQuery( $query );
                    $db->execute();

                    if ($format == 'raw')
                        echo '{"status":true, "message":"'.$message.'"}';
                    else
                        $this->setRedirect($url, $message, 'message');
                } catch(Exception $e) {
                    $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                    JLog::add($error, JLog::ERROR, 'com_emundus');
                    if ($format == "raw")
                        echo '{"status":false,"message":"'.$error.'"}';
                    else JError::raiseError(500, $e->getMessage());

                    return false;
                }
            }
            # get the logged user id    $user->id
            # get the fnum              $fnum
            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
            $user = JFactory::getSession()->get('emundusUser');     # logged user #

            # get FNUM INFO
            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
            $mFile = new EmundusModelFiles();
            $applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

            // set logs
            $logsStd = new stdClass();

            // get attachment data
            $logsStd->element = "[" . $attachmentTpe . "]";
            $logsStd->details = $fileName;
            $logsParams = array('deleted' => [$logsStd]);
            EmundusModelLogs::log($user->id, $applicant_id, $fnum, 4, 'd', 'COM_EMUNDUS_ACCESS_ATTACHMENT_DELETE', json_encode($logsParams, JSON_UNESCAPED_UNICODE));

        } catch(Exception $e) {
            $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
            JLog::add($error, JLog::ERROR, 'com_emundus');
            if ($format == "raw")
                echo '{"aid":"0","status":false,"message":"'.$error.'" }';
            else JError::raiseError(500, $e->getMessage());

            return false;
        }

        return true;
    }

    /*
        Open application form from fnum  for applicant
    */
    function openfile() {

        require_once (JPATH_COMPONENT.DS.'models'.DS.'profile.php');
        require_once(JPATH_COMPONENT.DS.'models'.DS.'application.php');

        $app = JFactory::getApplication();
        $jinput = $app->input;
        $fnum = $jinput->get->get('fnum', null);
        $confirm = $jinput->get->get('confirm', null);

        // Redirection URL used to bring the user back to the right spot.
        $redirect = $jinput->get->getBase64('redirect', null);

        if (!empty($redirect)) {
            $redirect = base64_decode($redirect);
        }

        if (empty($fnum)) {
            $app->redirect($redirect);
        }

        $session = JFactory::getSession();
        $aid = $session->get('emundusUser');

        $m_profile = new EmundusModelProfile;
        $m_application 	= new EmundusModelApplication;
        $infos = $m_profile->getFnumDetails($fnum);

        if ($aid->id != $infos['applicant_id']) {
            if(EmundusHelperAccess::asAccessAction(1,'r',JFactory::getUser()->id,$fnum)){
                $m_application->getFileMenu();
            }
            return;
        }

	    $m_profile->initEmundusSession($fnum);

        if (empty($redirect)) {
            if (empty($confirm)) {
                $redirect = $m_application->getFirstPage();
            } else {
                $redirect = $m_application->getConfirmUrl();
            }
        }

        # get the fnum          $fnum
        # get the logged user   $aid->id
        # get FNUM INFO
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        $mFile = new EmundusModelFiles();
        $applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
        EmundusModelLogs::log(JFactory::getUser()->id, $applicant_id, $fnum, 1, 'r', 'COM_EMUNDUS_ACCESS_FILE_READ');

        $dispatcher = JEventDispatcher::getInstance();
	    $dispatcher->trigger('onBeforeApplicantEnterApplication', ['fnum' => $fnum, 'aid' => $applicant_id, 'redirect' => $redirect]);
	    $dispatcher->trigger('callEventHandler', ['onBeforeApplicantEnterApplication', ['fnum' => $fnum, 'aid' => $applicant_id, 'redirect' => $redirect]]);

        $app->redirect($redirect);
    }

    // *****************switch profile controller************
    function switchprofile() {
        include_once (JPATH_SITE.'/components/com_emundus/models/profile.php');
        include_once (JPATH_SITE.'/components/com_emundus/models/users.php');

        $jinput = JFactory::getApplication()->input;
        $profile_fnum = $jinput->get('profnum', null);
        $redirect = $jinput->get('redirect', null);

        $ids = explode('.', $profile_fnum);
        $profile = $ids[0];

        $session = JFactory::getSession();
        $aid = $session->get('emundusUser');

        $m_profile = new EmundusModelProfile;
        $applicant_profiles = $m_profile->getApplicantsProfilesArray();
        foreach ($aid->emProfiles as $emProfile) {
            if ($emProfile->id === $profile) {

                if (in_array($profile, $applicant_profiles)) {
                    $fnum = $ids[1];
                    if ($fnum !== "") {
                        $infos = $m_profile->getFnumDetails($fnum);

                        $profile        = $m_profile->getProfileByCampaign($infos['campaign_id']);
                        $campaign       = $m_profile->getCampaignById($infos['campaign_id']);
                        $application    = $m_profile->getFnumDetails($fnum);

                        if ($aid->id != $infos['applicant_id'])
                            return;

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
                        $aid->university_id = null;
                        $aid->applicant     = 1;
                        $aid->status        = $application['status'];
                    } else {
                        $aid->profile       = $profile;
                        $aid->fnum          = $ids[1];
                        $profiles = $m_profile->getProfileById($profile);
                        $aid->applicant     = 1;
                        $aid->profile_label = $profiles["label"];
                        $aid->menutype      = $profiles["menutype"];
                    }
                } else {
                    if (isset($aid->start_date))
                        unset($aid->start_date);
                    if (isset($aid->end_date))
                        unset($aid->end_date);
                    if (isset($aid->candidature_posted))
                        unset($aid->candidature_posted);
                    if (isset($aid->candidature_incomplete))
                        unset($aid->candidature_incomplete);
                    if (isset($aid->schoolyear))
                        unset($aid->schoolyear);
                    if (isset($aid->code))
                        unset($aid->code);
                    if (isset($aid->campaign_id))
                        unset($aid->campaign_id);
                    if (isset($aid->campaign_name))
                        unset($aid->campaign_name);
                    if (isset($aid->fnum))
                        unset($aid->fnum);
                    if (isset($aid->status))
                        unset($aid->status);
                    if (isset($aid->fnums))
                        unset($aid->fnums);

                    $aid->profile = $profile;

                    $profiles = $m_profile->getProfileById($profile);

                    $aid->profile_label = $profiles["label"];
                    $aid->menutype = $profiles["menutype"];
                    $aid->applicant = 0;
                }
            }
        }
        $session->set('emundusUser', $aid);

        if(!empty($redirect)){
            JFactory::getApplication()->redirect($redirect);
        }
        echo json_encode((object)(array('status' => true)));
        exit;
    }

    function upload() {
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $copy_application_form = $eMConfig->get('copy_application_form', 0);
        $can_submit_encrypted = $eMConfig->get('can_submit_encrypted', 1);
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'checklist.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'checklist.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
        $m_profile = new EmundusModelProfile;
        $h_checklist = new EmundusHelperChecklist();
        $m_checklist = new EmundusModelChecklist;
        $m_application = new EmundusModelApplication;

        $db = JFactory::getDBO();
        $jinput = JFactory::getApplication()->input;

        $student_id = $jinput->get->get('sid', null);
        $duplicate  = $jinput->get->get('duplicate', null);
        $layout     = $jinput->get->get('layout', null);
        $format     = $jinput->get->get('format', null);
        $itemid     = $jinput->get('Itemid', null);
        $fnum       = $jinput->get->get('fnum', null);

        $fnums = array();
        $current_user = JFactory::getSession()->get('emundusUser');
        $m_files = $this->getModel('files');

        if (EmundusHelperAccess::isApplicant($current_user->id)) {
            $user = $current_user;
            $fnum = $user->fnum;

            if ($copy_application_form == 1 && $duplicate == 1) {
	            $fnums = array_keys($user->fnums);
            } else {
	            $fnums[] = $fnum;
            }

        } elseif (EmundusHelperAccess::asAccessAction(4, 'c', $current_user->id, $fnum) || EmundusHelperAccess::asAdministratorAccessLevel($current_user->id)) {
            $user = $m_profile->getEmundusUser($student_id);
            $fnums[] = $fnum;
        } else {
            JError::raiseError(500, JText::_('ACCESS_DENIED'));
            return false;
        }

        $chemin = EMUNDUS_PATH_ABS;
        $post = JRequest::get('post');
        $attachments = $post['attachment'];
        $descriptions = $post['description'];

	    if (isset($post['required_desc']) && $post['required_desc'] == 1 && empty(trim($descriptions))) {
		    JLog::add(JUri::getInstance().' :: USER ID : '.$user->id.' -> empty description', JLog::ERROR, 'com_emundus');
		    $errorInfo = JText::_("COM_EMUNDUS_ERROR_DESCRIPTION_REQUIRED");

		    if ($format == "raw") {
			    echo '{"aid":"0","status":false,"message":"'.$errorInfo.'" }';
		    } else {
			    JFactory::getApplication()->enqueueMessage($errorInfo."\n", 'error');
		    }
            $this->setRedirect('index.php?option=com_emundus&view=checklist&Itemid='.$itemid);
		    return false;
	    }

        $labels = $post['label'];

        if (!empty($_FILES)) {
            $files = array($_FILES["file"]);
        } else {
            $error = JUri::getInstance().' :: USER ID : '.$user->id;
            JLog::add($error, JLog::ERROR, 'com_emundus');

            if ($format == "raw") {
	            echo '{"aid":"0","status":false,"message":"'.$error.' -> empty $_FILES" }';
            }

            JFactory::getApplication()->enqueueMessage(JText::_('COM_EMUNDUS_ATTACHMENTS_ERROR_FILE_TOO_BIG'), 'error');
            $this->setRedirect('index.php?option=com_emundus&view=checklist&Itemid='.$itemid);
            return false;
        }


        $query = '';
        $nb = 0;

        if (!file_exists(EMUNDUS_PATH_ABS.$user->id)) {
            // An error would occur when the index.html file was missing, the 'Unable to create user file' error appeared yet the folder was created.
            if (!file_exists(EMUNDUS_PATH_ABS.'index.html')) {
            	touch(EMUNDUS_PATH_ABS.'index.html');
            }

            if (!mkdir(EMUNDUS_PATH_ABS.$user->id) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$user->id.DS.'index.html')){
                $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> Unable to create user file';
                JLog::add($error, JLog::ERROR, 'com_emundus');

                if ($format == "raw") {
                	echo '{"aid":"0","status":false,"message":"'.$error.'" }';
                } else {
                	JError::raiseWarning(500, 'Unable to create user file');
                }

                return false;
            }
        }
        chmod(EMUNDUS_PATH_ABS.$user->id, 0755);

        if (isset($layout)) {
        	$url = 'index.php?option=com_emundus&view=checklist&layout=attachments&sid='.$user->id.'&tmpl=component&Itemid='.$itemid.'#a'.$attachments;
        } else {
        	$url = 'index.php?option=com_emundus&view=checklist&Itemid='.$itemid.'#a'.$attachments;
        }

        foreach ($fnums as $fnum) {

            foreach ($files as $key => $file) {

				$local_filename = $file['name'];

                $pageCount = 0;
                if (empty($file['name'])) {
                    $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> try to upload empty file';
                    JLog::add($error, JLog::ERROR, 'com_emundus');
                    $errorInfo = JText::_("COM_EMUNDUS_ERROR_INFO_EMPTYFILE");

                    if ($format == "raw") {
                    	echo '{"aid":"0","status":false,"message":"'.$errorInfo.'" }';
                    } else {
                    	JFactory::getApplication()->enqueueMessage($errorInfo."\n", 'error');
                    }

                    return false;
                }

                try {
                    $query_ext = 'SELECT UPPER(allowed_types) as allowed_types, nbmax, min_pages_pdf, max_pages_pdf FROM #__emundus_setup_attachments WHERE id = '.(int)$attachments;
                    $db->setQuery($query_ext);
                    $attachment = $db->loadAssoc();

                    try {
                        $query_cpt = 'SELECT count(id) FROM #__emundus_uploads WHERE user_id='.$user->id.' AND attachment_id='.(int)$attachments.' AND fnum like '.$db->Quote($fnum);
                        $db->setQuery( $query_cpt );
                        $cpt = $db->loadResult();

                        if ($cpt >= $attachment['nbmax']) {
                            $error = JText::_('COM_EMUNDUS_ATTACHMENTS_MAX_ALLOWED').$attachment['nbmax'];
                            if ($format == "raw") {
                                echo '{"aid":"0","status":false,"message":"'.$error.'" }';
                            } else {
                                JFactory::getApplication()->enqueueMessage($error, 'error');
                                $this->setRedirect($url);
                            }

                            continue;
                        }
                    } catch (Exception $e) {
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
                } catch (Exception $e) {
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
                    $errorInfo = JText::_("COM_EMUNDUS_ERROR_INFO_FILESIZE");

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

                // Check if pdf and if a max or min number of pages is defined
                if(($attachment['min_pages_pdf'] > 0 || $attachment['max_pages_pdf'] > 0) && strtoupper($file_ext) === "PDF"){
                    require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'fpdi.php');

                    $pdf = new Fpdi();

                    $pageCount = $pdf->setSourceFile($file['tmp_name']);

                    if ($attachment['min_pages_pdf'] > 0 && $pageCount < $attachment['min_pages_pdf']) {
                        $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> encrypted pdf files are not allowed, please remove protection and try again';
                        JLog::add($error, JLog::ERROR, 'com_emundus');
                        $errorInfo = JText::_("COM_EMUNDUS_ATTACHMENTS_ERROR_MIN_PAGES_PDF");
                        $errorInfo2 = JText::_("COM_EMUNDUS_ATTACHMENTS_PAGES");

                        if ($format == "raw") {
                            echo '{"aid":"0","status":false,"message":"'.$errorInfo.$attachment['min_pages_pdf'].$errorInfo2.'" }';
                        } else {
                            JFactory::getApplication()->enqueueMessage($errorInfo.$attachment['min_pages_pdf'].$errorInfo2, 'error');
                            $this->setRedirect($url);
                        }

                        return false;
                    }

                    if ($attachment['max_pages_pdf'] > 0 && $pageCount > $attachment['max_pages_pdf']) {
                        $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> encrypted pdf files are not allowed, please remove protection and try again';
                        JLog::add($error, JLog::ERROR, 'com_emundus');
                        $errorInfo = JText::_("COM_EMUNDUS_ATTACHMENTS_ERROR_MAX_PAGES_PDF");
                        $errorInfo2 = JText::_("COM_EMUNDUS_ATTACHMENTS_PAGES");

                        if ($format == "raw") {
                            echo '{"aid":"0","status":false,"message":"'.$errorInfo.$attachment['max_pages_pdf'].$errorInfo2.'" }';
                        } else {
                            JFactory::getApplication()->enqueueMessage($errorInfo.$attachment['max_pages_pdf'].$errorInfo2, 'error');
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
                    $fnumInfos = $m_files->getFnumInfos($fnum);
                    $paths = $h_checklist->setAttachmentName($file['name'], $labels, $fnumInfos);

                    if (copy( $file['tmp_name'], $chemin.$user->id.DS.$paths)) {
                        $can_be_deleted = @$post['can_be_deleted_'.$attachments]!=''?$post['can_be_deleted_'.$attachments]:JRequest::getVar('can_be_deleted', 1, 'POST', 'none',0);
                        $can_be_viewed = @$post['can_be_viewed_'.$attachments]!=''?$post['can_be_viewed_'.$attachments]:JRequest::getVar('can_be_viewed', 1, 'POST', 'none',0);

                        $query .= '('.$user->id.', '.$attachments.', \''.$paths.'\', '.$db->Quote($descriptions).', '.$can_be_deleted.', '.$can_be_viewed.', '.$fnumInfos['id'].', '.$db->Quote($fnum).', '.$pageCount.', '.$db->quote($local_filename).'),';
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

                    if ($labels == "_photo") {

                        $checkdouble_query = 'SELECT count(user_id)
                        FROM #__emundus_uploads
                        WHERE attachment_id=
                                (SELECT id
                                    FROM #__emundus_setup_attachments
                                    WHERE lbl like "_photo"
                                )
                                AND user_id='.$user->id. '
                                AND fnum like '.$db->Quote($fnum);

                        try {
                            $db->setQuery($checkdouble_query);
                            $cpt = $db->loadResult();
                        } catch(Exception $e) {
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

        /// resize image
        $_upload_file_type = $file['type'];

        if(strpos($_upload_file_type, 'image') !== false) {
            $file_src = EMUNDUS_PATH_ABS.$user->id.DS.$paths;
            list($w_src, $h_src, $type) = getimagesize($file_src);

            // get min_resolution, max_resolution from jos_emundus_setup_attachments (param::attachments)
            $image_resolution_query = "SELECT min_width,max_width,min_height,max_height FROM #__emundus_setup_attachments WHERE #__emundus_setup_attachments.id = " . (int)$attachments;
            $this->_db->setQuery($image_resolution_query);
            $image_resolution = $this->_db->loadObject();

            if ((!empty($image_resolution->max_width) && !empty($image_resolution->max_height)) && ($w_src * $h_src > (int)$image_resolution->max_width * (int)$image_resolution->max_height)) {

                if($w_src > $h_src) {
                    $ratio = $h_src / $w_src;

                    $new_width = max((int)$image_resolution->max_width, (int)$image_resolution->max_height);
                    $new_height = round($new_width * $ratio);

                } else if($w_src < $h_src) {
                    $ratio = $w_src / $h_src;

                    $new_height = max((int)$image_resolution->max_width, (int)$image_resolution->max_height);
                    $new_width = round($new_height * $ratio);

                } else {
                    $new_height = min((int)$image_resolution->max_width, (int)$image_resolution->max_height);
                    $new_width = min((int)$image_resolution->max_width, (int)$image_resolution->max_height);
                }

                switch ($type) {
                    case 1:   // gif
                        $original_img = imagecreatefromgif($file_src);
                        break;
                    case 2: // jpeg
                        $original_img = imagecreatefromjpeg($file_src);
                        break;
                    case 3: // png
                        $original_img = imagecreatefrompng($file_src);
                        break;
                    default:    // jpg
                        $original_img = imagecreatefromjpeg($file_src);
                        break;
                }

                /* $new_width = (int)$image_resolution->max_width;
                $new_height = (int)$image_resolution->max_height; */

                $resized_img = imagecreatetruecolor($new_width, $new_height);

                // copy resample
                imagecopyresampled($resized_img, $original_img, 0, 0, 0, 0, $new_width, $new_height, $w_src, $h_src);

                // export new image to jpeg
                imagejpeg($resized_img, $chemin . $user->id . DS . 'tn_' . $paths);

                /// remove old image only if resize was successful
                if ($resized_img !== false) {
                    unlink($file_src);
                }

                /// change name the resize image
                rename($chemin . $user->id . DS . 'tn_' . $paths, $file_src);
            } else if ((!empty($image_resolution->min_width) && !empty($image_resolution->min_height)) && ($w_src * $h_src < (int)$image_resolution->min_width * (int)$image_resolution->min_height)) {
                $errorInfo = "COM_EMUNDUS_ERROR_IMAGE_TOO_SMALL";
                echo '{"aid":"0","status":false,"message":"' . JText::_('COM_EMUNDUS_ERROR_IMAGE_TOO_SMALL') . " " . (int)$image_resolution->min_width . 'px x ' . (int)$image_resolution->min_height . 'px' . '"}';
                unlink($file_src);          /// remove uploaded file
                return false;
            }
        }

        // delete temp uploaded file
        unlink($file['tmp_name']);

        if (!empty($query)) {
            $query = 'INSERT INTO #__emundus_uploads (user_id, attachment_id, filename, description, can_be_deleted, can_be_viewed, campaign_id, fnum, pdf_pages_count, local_filename)
                        VALUES '.substr($query,0,-1);

            try {
                $db->setQuery( $query );
                $db->execute();
                $id = $db->insertid();

                // TODO: onAfterAttachmentUpload event appeared after onAfterUploadFile creation on this branch. move treatment to use onAfterAttachmentUpload
                $dispatcher = JEventDispatcher::getInstance();
                JPluginHelper::importPlugin('emundus', 'sync_file');
                $dispatcher->trigger('onAfterUploadFile', [['upload_id' => $id]]);

                $dispatcher = JEventDispatcher::getInstance();
                $dispatcher->trigger('onAfterAttachmentUpload', [$fnum, (int)$attachments, $paths]);
                $dispatcher->trigger('callEventHandler', ['onAfterAttachmentUpload', ['fnum' => $fnum, 'attachment_id' => (int)$attachments, 'file' => $paths]]);

                if ($format == "raw") {
                    echo '{"id":"'.$id.'","status":true, "message":"'.JText::_('COM_EMUNDUS_ACTIONS_DELETE').'"}';
                } else {
                    JFactory::getApplication()->enqueueMessage($nb.($nb>1?' '.JText::_("FILES_BEEN_UPLOADED"):' '.JText::_("FILE_BEEN_UPLOADED")), 'message');
                    $this->setRedirect($url);
                }
            }
            catch (Exception $e) {
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

        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
        $user = JFactory::getSession()->get('emundusUser');     # looged user #

        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        $mFile = new EmundusModelFiles();
        $applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

        // stock the attachments name //
        $logsStd = new stdClass();

        /* get attachment type */
        $attachmentTpe = $m_application->getAttachmentByID($attachments)['value'];

        $logsStd->element = '[' . $attachmentTpe . ']';
        $logsStd->details = $_FILES['file']['name'];

        // stock all logs into an array
        $logsParams = array('created' => [$logsStd]);

        EmundusModelLogs::log($user->id, $applicant_id, $fnum, 4, 'c', 'COM_EMUNDUS_ACCESS_ATTACHMENT_CREATE',json_encode($logsParams,JSON_UNESCAPED_UNICODE));
        return true;
    }

    /***********************************
     ** Update profile for Applicants
     ***********************************/
    function updateprofile() {
        $user = JFactory::getSession()->get('emundusUser');
        if (!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
            $this->setRedirect(JRoute::_('index.php'), JText::_('Only administrator can access this function.'), 'error');
            return;
        }
        $attachment_id = JRequest::getVar('aid', $default=null, $hash= 'POST', $type= 'array', $mask=0);
        $attachment_selected = JRequest::getVar('as', $default=null, $hash= 'POST', $type= 'array', $mask=0);
        $attachment_displayed = JRequest::getVar('ad', $default=null, $hash= 'POST', $type= 'array', $mask=0);
        $attachment_required = JRequest::getVar('ar', $default=null, $hash= 'POST', $type= 'array', $mask=0);
        $attachment_bank_needed = JRequest::getVar('ab', $default=null, $hash= 'POST', $type= 'array', $mask=0);
        $profile_id = JRequest::getVar('pid', $default=null, $hash= 'POST', $type= 'none', $mask=0);
        if ($profile_id != JRequest::getVar('rowid', $default=null, $hash= 'GET', $type= 'none', $mask=0) || !is_numeric($profile_id) || floor($profile_id) != $profile_id || $profile_id <= 0) {
            $this->setRedirect('index.php', 'Error', 'error');
            return;
        }
        if (!empty($attachment_selected)) {
            $attachments = array();
            $a = new stdClass();
            foreach ($attachment_id as $id) {
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
        if (isset($attachments)) {
            $query = 'INSERT INTO #__emundus_setup_attachment_profiles (`profile_id`, `attachment_id`, `displayed`, `mandatory`, `bank_needed`) VALUES';
            foreach ($attachments as $id => $attachment) {
                if (!$attachment->selected) continue;
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
        $session        =& JFactory::getSession();
        $cid            = $session->get('uid');
        $quick_search   = $session->get('quick_search');
        $user           = $session->get('emundusUser');

        $menu=JFactory::getApplication()->getMenu()->getActive();
        $access=!empty($menu)?$menu->access : 0;
        if (!EmundusHelperAccess::isAllowedAccessLevel($user->id, $access)) {
            die(JText::_('ACCESS_DENIED'));
        }

        require_once(JPATH_BASE.DS.'libraries'.DS.'emundus'.DS.'export_csv'.DS.'csv_'.$view.'.php');
        $elements = JRequest::getVar('ud', null, 'POST', 'array', 0);

        export_csv($cid, $elements);
    }

    function transfert_view($reqids=array()) {

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


        // Starting a session.
        $session = JFactory::getSession();
        if ($cid)           { $session->set( 'uid', $cid ); }
        if ($profile)       { $session->set( 'profile', $profile ); }
        if ($finalgrade)    { $session->set( 'finalgrade', $finalgrade ); }
        if ($quick_search)  { $session->set( 'quick_search', $quick_search ); }
        if ($gid)           { $session->set( 'groups', $gid ); }
        if ($evaluator)     { $session->set( 'evaluator', $evaluator ); }
        if ($engaged)       { $session->set( 'engaged', $engaged ); }
        if ($schoolyears)   { $session->set( 'schoolyears', $schoolyears ); }
        if ($miss_doc)      { $session->set( 'missing_doc', $miss_doc ); }
        if ($search)        { $session->set( 's_elements', $search ); }
        if ($search_values) { $session->set( 's_elements_values', $search_values ); }
        if ($comments)      { $session->set( 'comments', $comments ); }
        if ($complete)      { $session->set( 'complete', $complete ); }
        if ($validate)      { $session->set( 'validate', $validate ); }

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
        } else return 'application/octet-stream';
    }

    /**
     * Check if user can or not open PDF file
     */
    function getfile() {

        // Get the filename and user ID from the URL.
        $jinput = JFactory::getApplication()->input;
        $url = $jinput->get->get('u', null, 'RAW');

	    $eMConfig = JComponentHelper::getParams('com_emundus');
	    $applicant_files_path = $eMConfig->get('applicant_files_path', 'images/emundus/files/');
	    if (strpos($url, $applicant_files_path) !== 0 && strpos($url, 'tmp/') !== 0) {
		    die (JText::_('ACCESS_DENIED'));
	    }

        $urltab = explode('/', $url);

        // Split the URL into different parts.
        $cpt = count($urltab);
        $uid = (int)$urltab[$cpt-2];
        if(empty($uid)) {
            // Manage subdirectories
            $uid = (int)$urltab[$cpt-3];
        }
        $file = $urltab[$cpt-1];

        $current_user = JFactory::getSession()->get('emundusUser');

        $fnum= "";
        if($current_user->id == $uid){
            $fnum = $current_user->fnum;
        }
	    $fnums = [];
		if(!empty($current_user->fnums)) {
			$fnums = array_keys($current_user->fnums);
		}


        // This query checks if the file can actually be viewed by the user, in the case a file uploaded to his file by a coordniator is opened.
        if (!empty(JFactory::getUser($uid)->id)) {
            $db = JFactory::getDBO();

            if(EmundusHelperAccess::isApplicant($current_user->id) && !empty($fnums)){
                $query = 'SELECT can_be_viewed, fnum FROM #__emundus_uploads';
                $query.= " WHERE fnum IN (". implode(',',$db->quote($fnums)) . ')';
                $query .= " AND filename like " . $db->Quote($file);
            } else {
                $query = 'SELECT can_be_viewed, fnum FROM #__emundus_uploads';
                if (!empty($fnum)) {
                    $query .= " WHERE fnum like " . $db->quote($fnum);
                } else {
                    $query .= " WHERE user_id = " . $uid;
                }
                // TODO: adapt for all cases, KIT needs OR filename (ROAD-918)
                $query .= " AND filename like " . $db->Quote($file);
            }
            $db->setQuery($query);
            $fileInfo = $db->loadObject();

            $first_part_of_filename = explode('_', $file)[0];
            if (empty($fileInfo) && is_numeric($first_part_of_filename) && strlen($first_part_of_filename) === 28) {
                $fileInfo->fnum = $first_part_of_filename;
                $fileInfo->can_be_viewed = 1;
            }
        }

        // Check if the user is an applicant and it is his file.
        if (EmundusHelperAccess::isApplicant($current_user->id) && $current_user->id == $uid && !EmundusHelperAccess::asCoordinatorAccessLevel($current_user->id)) {
            if ($fileInfo->can_be_viewed != 1 && !empty($fileInfo)) {
                die (JText::_('ACCESS_DENIED'));
            }
        }
        // If the user has the rights to open attachments, or to create a PDF export (he needs to be able to open it, even if he can't access the documents).
        elseif (!empty($fileInfo) && (!EmundusHelperAccess::asAccessAction(4,'r', $current_user->id, $fileInfo->fnum) && !EmundusHelperAccess::asAccessAction(8,'c', $current_user->id, $fileInfo->fnum))) {
            die (JText::_('ACCESS_DENIED'));
        } elseif (empty($fileInfo) && (!EmundusHelperAccess::asAccessAction(4,'r') && !EmundusHelperAccess::asAccessAction(8,'c'))) {
            die (JText::_('ACCESS_DENIED'));
        }

        // Otherwise, open the file if it exists.
        $file = JPATH_BASE.DS.$url;
        if (is_file($file)) {
            $mime_type = $this->get_mime_type($file);

            if (EmundusHelperAccess::isDataAnonymized($current_user->id) && $mime_type === 'application/pdf') {

	            require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'fpdi.php');
	            $pdf = new ConcatPdf();
	            $pdf->setFiles([$file]);
	            $pdf->concat();
	            $pdf->Output();
	            exit;

            } else {
	            header('Content-type: '.$mime_type);
                header('Content-Disposition: inline; filename='.basename($file));
                header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('Cache-Control: pre-check=0, post-check=0, max-age=0');
                header('Pragma: anytextexeptno-cache', true);
                header('Cache-control: private');
                header('Expires: 0');

                ob_clean();
                ob_end_flush();
                readfile($file);
                exit;
            }
        } else {
            JError::raiseWarning(500, JText::_( 'COM_EMUNDUS_EXPORTS_FILE_NOT_FOUND' ).' '.$url);
        }
    }

/*
    function sendmail($nb_email_per_batch = null) {
        $app = JFactory::getApplication();
        $user = JFactory::getSession()->get('emundusUser');
        $db = JFactory::getDBO();
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $keyid = JRequest::getVar('keyid', null, 'GET', 'none',0);
        $eMConfig = JComponentHelper::getParams('com_emundus');

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

            if ($db->getNumRows() == 0) {
                $this->setRedirect('index.php?option=com_fabrik&view=table&tableid=90&Itemid='.$itemid);
            } else {
                $mail=$db->loadObjectList();

                foreach ($mail as $m) {
                    $mail_subject = $m->subject;
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
*/
    function sendmail_applicant() {
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $sid = JRequest::getVar('mail_to', null, 'POST', 'INT',0);
        $campaign_id = JRequest::getVar('campaign_id', null, 'POST', 'INT',0);
        $m_emails = $this->getModel('emails');
        $email = $m_emails->sendmail();

        $m_campaign = $this->getModel('campaign');
        $email = $m_campaign->setResultLetterSent($sid, $campaign_id);


        $this->setRedirect('index.php?option=com_emundus&view=application&Itemid='.$itemid.'&sid='.$sid.'&tmpl=component');
    }

    function sendmail_expert() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            die(JError::raiseWarning( 500, JText::_( 'ACCESS_DENIED' ) ));
        }
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $sid    = JRequest::getVar('sid', null, 'GET', 'INT',0);
        $fnum   = JRequest::getVar('fnum', null, 'GET');

        $m_emails = $this->getModel('emails');
        $email = $m_emails->sendmail('expert', $fnum);

        exit();

    }

    /*
    ** @description Validate / Unvalidate a column from table (used in administrative validation). Ajax request
    ** @return string HTML to display in page for action block indexed by user ID.
    */
    function ajax_validation() {
        $user = JFactory::getSession()->get('emundusUser');
        if (!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)){
            die(JText::_('ACCESS_DENIED'));
        }
        $uid        = JRequest::getVar('uid', null, 'GET', 'INT');
        $validate   = JRequest::getVar('validate', null, 'GET', 'INT');
        $cible      = JRequest::getVar('cible', null, 'GET', 'CMD');
        $data       = explode('.', $cible);

        if(count($data)>3)  {
            $and = ' AND `campaign_id`='.$data[3];
        } else {
            $and = '';
        }
        if($data[0] == "jos_emundus_final_grade") {
            $column = "student_id";
        } else {
            $column = 'user';
        }

        if (!empty($uid) && is_numeric($uid)) {
            $value  = abs((int)$validate-1);
            $db     = JFactory::getDBO();
            $query  = 'UPDATE `'.$data[0].'` SET `'.$data[1].'`='.$db->Quote($value).' WHERE `'.$column.'` = '.$db->Quote((int)$uid). $and;
            $db->setQuery($query);
            $db->execute();
            if ($value > 0) {
                $img = 'tick.png';
                $btn = 'unvalidate|'.$uid;
                $alt = JText::_('COM_EMUNDUS_FORMS_VALIDATED').'::'.JText::_('COM_EMUNDUS_FORMS_VALIDATED_NOTE');
            } else {
                $img = 'publish_x.png';
                $btn = 'validate|'.$uid;
                $alt = JText::_('COM_EMUNDUS_FORMS_UNVALIDATED').'::'.JText::_('COM_EMUNDUS_FORMS_UNVALIDATED_NOTE');
            }
            echo '<span class="hasTip" title="'.$alt.'">
                    <input type="image" src="media/com_emundus/images/icones/'.$img.'" onclick="validation('.$uid.', \''.$value.'\', \''.$cible.'\');" ></span> ';
        } else {
            echo JText::_('ERROR');
        }
    }

    // export_fiche_synthese
    public function export_fiche_synthese() {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
        $user = JFactory::getSession()->get('emundusUser');
        $jinput = JFactory::getApplication()->input;

        /// get valid fnum
        $fnums_post = $jinput->getRaw('checkInput');
        $fnums_array = ($fnums_post=='all')?'all':(array) json_decode(stripslashes($fnums_post), false, 512, JSON_BIGINT_AS_STRING);

        $validFnums = array();
        foreach ($fnums_array as $fnum) {
            if (EmundusHelperAccess::asAccessAction(35, 'c', $user->id, $fnum)&& $fnum != 'em-check-all-all' && $fnum != 'em-check-all')
                $validFnums[] = $fnum;
        }

        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        $m_files = new EmundusModelFiles;
        $fnumsInfo = $m_files->getFnumsInfos($validFnums);

        /// set params
        $file = $jinput->getRaw('file', null);
        $totalfile = count($validFnums);
        $model = $jinput->getRaw('model', null);
        $start = 0;
        $limit = 2;

        $forms = 0;

        /// from model --> get all model params
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'export.php');
        $h_files = new EmundusHelperFiles;
        $export_model = $h_files->getExportPdfFilterById($model);

        // from export_model --> build fiche de synthese
        $pdf_elements = array();

        $profiles = json_decode($export_model->constraints)->pdffilter->profiles;           /// type Array
        $formulaires = json_decode($export_model->constraints)->pdffilter->tables;
        $groups = json_decode($export_model->constraints)->pdffilter->groups;
        $elements = json_decode($export_model->constraints)->pdffilter->elements;

        // attachments
        $attachments = json_decode($export_model->constraints)->pdffilter->attachments;

        /// is_assessment, is_admission, is_decision
        $assessment = json_decode($export_model->constraints)->pdffilter->assessment;
        $admission = json_decode($export_model->constraints)->pdffilter->admission;
        $decision = json_decode($export_model->constraints)->pdffilter->decision;

        if(empty($profiles) and empty($formulaires) and empty($groups) and empty($elements)) { $forms = 0;} else { $forms = 1; }

        $options = json_decode($export_model->constraints)->pdffilter->headers;

        foreach($profiles as $key => $value) {
            $pdf_elements[$value] = array('fids' => $formulaires, 'gids' => $groups, 'eids' => $elements);
        }

        /// from pdf elements --> build pdf
        $files = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php';

        if (!function_exists('application_form_pdf')) {
            require_once($files);
        }

        //// pour chaque fnum --> appeler la fonction helpers/export.php/buildFormPDF
        ///
        if (file_exists(JPATH_BASE . DS . 'tmp' . DS . $files)) {
            $files_list = array(JPATH_BASE.DS.'tmp'.DS.$files);
        } else {
            $files_list = array();
        }

        for ($i = $start; $i <= $totalfile; $i++) {
            $fnum = $validFnums[$i];

            if (is_numeric($fnum) && !empty($fnum)) {
                require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
                $m_profile = new EmundusModelProfile;
                $infos = $m_profile->getFnumDetails($fnum);
                $campaign_id = $infos['campaign_id'];

                /// build pdf for forms
                if(!empty($pdf_elements) and array_keys($pdf_elements)[0] != "") {
                    $files_list[] = EmundusHelperExport::buildFormPDF($fnumsInfo[$fnum], $fnumsInfo[$fnum]['applicant_id'], $fnum, $forms, null, $options, null, $pdf_elements);
                }

                /// build pdf for attachments
                if(!empty($attachments) and $attachments[0] != "") {
                    $tmpArray = array();
                    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
                    $m_application = new EmundusModelApplication;
                    $attachment_to_export = array();
                    foreach ($attachments as $key=>$aids) {
                        $detail = explode("|", $aids);
                        if ((!empty($detail[1]) && $detail[1] == $fnumsInfo[$fnum]['training']) && ($detail[2] == $fnumsInfo[$fnum]['campaign_id'] || $detail[2] == "0")) {
                            $attachment_to_export[] = $detail[0];
                        }
                    }
                    if ($attachments || !empty($attachment_to_export)) {
                        $files = $m_application->getAttachmentsByFnum($fnum, null, $attachment_to_export);
                        if ($options[0] != "0") {
                            $files_list[] = EmundusHelperExport::buildHeaderPDF($fnumsInfo[$fnum], $fnumsInfo[$fnum]['applicant_id'], $fnum, $options);
                        }
                        $files_export = EmundusHelperExport::getAttachmentPDF($files_list, $tmpArray, $files, $fnumsInfo[$fnum]['applicant_id']);
                    }
                }

                if ($assessment == 1)
                    $files_list[] = EmundusHelperExport::getEvalPDF($fnum, $options);
                if ($decision == 1)
                    $files_list[] = EmundusHelperExport::getDecisionPDF($fnum, $options);
                if ($admission == 1)
                    $files_list[] = EmundusHelperExport::getAdmissionPDF($fnum, $options);

                if(array_keys($pdf_elements)[0] == "" and $attachments[0] == "" and ($assessment != 1) and ($decision != 1) and ($admission != 1) and ($options[0] != "0")) {
                    $files_list[] = EmundusHelperExport::buildHeaderPDF($fnumsInfo[$fnum], $fnumsInfo[$fnum]['applicant_id'], $fnum, $options);
                }
            }
        }

        if (count($files_list) > 0) {
            require_once(JPATH_LIBRARIES . DS . 'emundus' . DS . 'fpdi.php');

            $pdf = new ConcatPdf();

            $pdf->setFiles($files_list);

            $pdf->concat();

            if (isset($tmpArray)) {
                foreach ($tmpArray as $fn) {
                    unlink($fn);
                }
            }
            $pdf->Output(JPATH_BASE . DS . 'tmp' . DS . $file, 'F');

            $result = array('status' => true, 'file' => $file, 'msg' => JText::_('COM_EMUNDUS_EXPORTS_FILES_ADDED'), 'path'=>JURI::base());
        } else {
            $result = array('status' => false, 'msg' => JText::_('COM_EMUNDUS_EXPORTS_FILE_NOT_FOUND'));
        }

        echo json_encode((object) $result);
        exit();
    }

    /**
     * unregisterevent
     *
     * @return void
     */
    function unregisterevent(){
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $fnum = $jinput->get('fnum', null);

        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
        include_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'controllers'.DS.'messages.php');
        $m_files = new EmundusModelFiles();
        $m_emails = new EmundusModelEmails();
        $c_messages = new EmundusControllerMessages();

        $query = $this->_db->getQuery(true);

        if (in_array($fnum, array_keys($this->_user->fnums))){
            $user = $this->_user;

            $query->select('cc.eb_registration,sc.event,sc.training,sc.label')
                ->from($this->_db->quoteName('#__emundus_campaign_candidature','cc'))
                ->leftJoin($this->_db->quoteName('#__emundus_setup_campaigns','sc').' ON '.$this->_db->quoteName('sc.id').' = '.$this->_db->quoteName('cc.campaign_id'))
                ->where($this->_db->quoteName('cc.fnum') . ' = ' . $this->_db->quote($fnum));
            $this->_db->setQuery($query);
            $registration = $this->_db->loadObject();

            $query->clear()
                ->delete('#__eb_registrants')
                ->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($registration->eb_registration));
            $this->_db->setQuery($query);
            $this->_db->execute();

            $m_files->updateState((array)$fnum, 3);
            $m_emails->sendEmailTrigger(3, (array)$registration->training, '0,1', $this->_user);

            $query->clear()
                ->select('u.email,u.id')
                ->from($this->_db->quoteName('#__emundus_configuration_activites_repeat_eb_activities','car'))
                ->leftJoin($this->_db->quoteName('#__emundus_configuration_activites','ca').' ON '.$this->_db->quoteName('ca.id').' = '.$this->_db->quoteName('car.parent_id'))
                ->leftJoin($this->_db->quoteName('#__users','u').' ON '.$this->_db->quoteName('u.id').' = '.$this->_db->quoteName('ca.eb_referent'))
                ->where($this->_db->quoteName('car.eb_activities') . ' = ' . $this->_db->quote($registration->event));
            $this->_db->setQuery($query);
            $referent_email = $this->_db->loadObject();

            if(!empty($referent_email)) {
                $post = array(
                    'CAMPAIGN_LABEL' => $registration->label
                );
                $c_messages->sendEmailNoFnum($referent_email->email,82, $post, $referent_email->id);
            }
        } else {
            JError::raiseError(500, JText::_('ACCESS_DENIED'));
            echo 'false';
        }

        unset($this->_user->fnums[$fnum]);

        if (in_array($user->fnum, array_keys($user->fnums))) {
            echo 'true';
        } else {
            array_shift($this->_user->fnums);
            echo 'true';
        }

        exit();
    }
}

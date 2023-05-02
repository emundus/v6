<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: attachement.php 89 2008-10-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email à l'étudiant lors d'un upload de document par le consortium. 
 *				Une copie est envoyée au user qui upload le document
 */

$mainframe 		= JFactory::getApplication();
$jinput 		= $mainframe->input;
$baseurl 		= JURI::base();
$db 			= JFactory::getDBO();
$eMConfig = JComponentHelper::getParams('com_emundus');
$alert_new_attachment = $eMConfig->get('alert_new_attachment');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'checklist.php');
$m_files = new EmundusModelFiles();
$h_checklist = new EmundusHelperChecklist();

$aid = $_REQUEST['jos_emundus_uploads___attachment_id'];
$fnum = $_REQUEST['jos_emundus_uploads___fnum'];

if(is_array($aid)) {
    $aid = $aid[0];
}

$can_be_view 	= $jinput->get('jos_emundus_uploads___can_be_viewed');
$inform_applicant_by_email 	= $jinput->get('jos_emundus_uploads___inform_applicant_by_email');

$db->setQuery('SELECT id, user_id, filename FROM #__emundus_uploads WHERE id='.$jinput->get('jos_emundus_uploads___id'));
$upload = $db->loadObject();
$student = JFactory::getUser($upload->user_id);
$query = 'SELECT profile FROM #__emundus_users WHERE user_id='.$upload->user_id;
$db->setQuery( $query );
$profile=$db->loadResult();
$query = 'SELECT ap.displayed, attachment.lbl, attachment.value
			FROM #__emundus_setup_attachments AS attachment
			LEFT JOIN #__emundus_setup_attachment_profiles AS ap ON attachment.id = ap.attachment_id AND ap.profile_id='.$profile.'
			WHERE attachment.id ='.$aid.' ';
$db->setQuery( $query );
$attachment_params = $db->loadObject();

//$nom = strtolower(preg_replace(array('([\40])','([^a-zA-Z0-9-])','(-{2,})'),array('_','','_'),preg_replace('/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/','$1',htmlentities($student->name,ENT_NOQUOTES,'UTF-8'))));
$fnumInfos = $m_files->getFnumInfos($fnum);
$nom = $h_checklist->setAttachmentName($upload->filename, $attachment_params->lbl, $fnumInfos);

//$nom .= $attachment_params->lbl.rand().'.'.end(explode('.', $upload->filename));

// test if directory exist
if (!file_exists(EMUNDUS_PATH_ABS.$upload->user_id)) {
	mkdir(EMUNDUS_PATH_ABS.$upload->user_id, 0777, true);
}

if (!rename(JPATH_SITE.$upload->filename, EMUNDUS_PATH_ABS.$upload->user_id.DS.$nom)) {
    die("ERROR_MOVING_UPLOAD_FILE");
}

$db->setQuery('UPDATE #__emundus_uploads SET filename="'.$nom.'" WHERE id='.$upload->id);
$db->execute();

// PHOTOS
if ($attachment_params->lbl=="_photo") {
    $pathToThumbs = EMUNDUS_PATH_ABS.$student->id.DS.$nom;
    $file_src = EMUNDUS_PATH_ABS.$student->id.DS.$nom;
    list($w_src, $h_src, $type) = getimagesize($file_src);  // create new dimensions, keeping aspect ratio

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
    $new_width = 200;
    $new_height = floor( $h_src * ( $new_width / $w_src ) );
    $tmp_img = imagecreatetruecolor( $new_width, $new_height );
    imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $w_src, $h_src );
    imagejpeg( $tmp_img, EMUNDUS_PATH_ABS.$student->id.DS.'tn_'.$nom);
    $student->avatar = $nom;
}

# get fnum                $fnum
# get logged user id      JFactory::getUser()->id
# get applicant id        $upload->user_id

// TRACK THE LOGS
require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
$user = JFactory::getSession()->get('emundusUser'); # logged user #

require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
$mFile = new EmundusModelFiles();
$applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

// stock the attachment name
$logsStd = new stdClass();

$logsStd->element = '[' . $attachment_params->value . '] ';
$logsStd->details = str_replace("/tmp/", "", $_FILES['jos_emundus_uploads___filename']['name']);

$logsParams = array('created' => [$logsStd]);

EmundusModelLogs::log(JFactory::getUser()->id, $applicant_id, $fnum, 4, 'c', 'COM_EMUNDUS_ACCESS_ATTACHMENT_CREATE', json_encode($logsParams,JSON_UNESCAPED_UNICODE));

// Pour tous les mails
$user = JFactory::getUser();
$fnumInfos = $m_files->getFnumInfos($fnum);
$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[FNUM\]/','/\[CAMPAIGN_LABEL\]/', '/\[SITE_URL\]/','/\n/');
$replacements = array ($student->id, $student->name, $student->email, $fnum, $fnumInfos["label"],JURI::base(),'<br />');
$mode = 1;
if ($can_be_view == 1) {
	$attachment[] = EMUNDUS_PATH_ABS.$upload->user_id.DS.$nom;
	$file_url = '<br/>'.$baseurl.EMUNDUS_PATH_REL.$upload->user_id.'/'.$nom;
}
$from_id = $user->id;

if ($inform_applicant_by_email == 1) {
	// Récupération des données du mail à l'étudiant
    try {
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'controllers' . DS . 'messages.php');
        $c_messages = new EmundusControllerMessages;

        $post = array('FILE_URL' => @$file_url);
        $send = $c_messages->sendEmail($fnum, "attachment", $post);
    }
    catch (Exception $e){
        echo $e->getMessage();
        JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
    }
}
?>
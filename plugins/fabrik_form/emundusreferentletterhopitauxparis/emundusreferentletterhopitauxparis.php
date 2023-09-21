<?php
/**
 * @version 2: emundusReferentLetter 2018-04-25 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Redirection et chainage des formulaires suivant le profile de l'utilisateur
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.methods' );

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

//Export PDF
JText::script('COM_EMUNDUS_PDF_GENERATION');
JText::script('COM_EMUNDUS_CREATE_PDF');
JText::script('COM_EMUNDUS_ADD_FILES_TO_PDF');
JText::script('COM_EMUNDUS_EXPORT_FINISHED');
JText::script('COM_EMUNDUS_ERROR_NBFILES_CAPACITY');
JText::script('COM_EMUNDUS_ERROR_CAPACITY_PDF');
JText::script('COM_EMUNDUS_ATTACHMENTS_FILES_UPLOADED');
JText::script('COM_EMUNDUS_ATTACHMENTS_DESCRIPTION');
JText::script('DECISION_PDF');
JText::script('ADMISSION_PDF');
JText::script('GENERATE_PDF');
JText::script('PDF_OPTIONS');
JText::script('FILES_UPLOADED');
JText::script('TAGS');
JText::script('FILE_NOT_FOUND');
JText::script('FILE_NOT_DEFINED');
JText::script('ID_CANDIDAT');
JText::script('FNUM');
JText::script('APPLICATION_SENT_ON');
JText::script('DOCUMENT_PRINTED_ON');
JText::script('ARE_YOU_SURE_TO_DELETE_USERS');
JText::script('PDF_HEADER_INFO_CANDIDAT');
JText::script('PDF_HEADER_INFO_DOSSIER');

/**
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.juseremundus
 * @since       3.0
 */

class PlgFabrik_FormEmundusReferentLetterHopitauxParis extends plgFabrik_Form
{
    /**
     * Status field
     *
     * @var  string
     */
    protected $URLfield = '';

    function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage( );
    }

    /**
     * Get an element name
     *
     * @param   string  $pname  Params property name to look up
     * @param   bool    $short  Short (true) or full (false) element name, default false/full
     *
     * @return	string	element full name
     */
    public function getFieldName($pname, $short = false)
    {
        $params = $this->getParams();

        if ($params->get($pname) == '')
        {
            return '';
        }

        $elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));

        return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
    }

    /**
     * Get the fields value regardless of whether its in joined data or no
     *
     * @param   string  $pname    Params property name to get the value for
     * @param   mixed   $default  Default value
     *
     * @return  mixed  value
     */
    public function getParam($pname, $default = '')
    {
        $params = $this->getParams();

        if ($params->get($pname) == '')
        {
            return $default;
        }

        return $params->get($pname);
    }

    /**
     * Main script.
     *
     * @return  bool
     * @throws Exception
     */
    public function onBeforeCalculations() {

        jimport('joomla.utilities.utility');
        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.filerequest.php'], JLog::ALL, ['com_emundus']);

        include_once (JPATH_BASE.'/components/com_emundus/models/files.php');
        include_once (JPATH_BASE.'/components/com_emundus/models/emails.php');
        include_once (JPATH_BASE.'/components/com_emundus/models/profile.php');
        include_once (JPATH_BASE.'/components/com_emundus/models/campaign.php');
        include_once (JPATH_BASE.'/components/com_emundus/helpers/menu.php');

        $baseurl    = JURI::root();
        $db         = JFactory::getDBO();
        $app        = JFactory::getApplication();
        $jinput     = $app->input;

        $offset = $app->get('offset', 'UTC');
        try {
            $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
            $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
            $now = $dateTime->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            echo $e->getMessage() . '<br />';
        }

        $formModel = $this->getModel();
        $data = $formModel->formData;

        $fnum = $data['fnum'];
        $m_files = new EmundusModelFiles;
        $fnum_detail = $m_files->getFnumInfos($fnum);

        $student_id = $fnum_detail['applicant_id'];
        $campaign_id = $fnum_detail['campaign_id'];


        /**/

        $attachments = explode(',',$this->getParam('attachments','jos_emundus_references___attachment_id_1,jos_emundus_references___attachment_id_2,jos_emundus_references___attachment_id_3,jos_emundus_references___attachment_id_4'));
        $default_attachments = [4,6,21,19];
        $emails = explode(';',$this->getParam('emails','jos_emundus_references___Email_1,jos_emundus_references___Email_2,jos_emundus_references___Email_3,jos_emundus_references___Email_4'));
        $names = explode(',',$this->getParam('names','jos_emundus_references___Last_Name_1,jos_emundus_references___Last_Name_2,jos_emundus_references___Last_Name_3,jos_emundus_references___Last_Name_4'));
        $firstnames = explode(',',$this->getParam('firstnames','jos_emundus_references___First_Name_1,jos_emundus_references___First_Name_2,jos_emundus_references___First_Name_3,jos_emundus_references___First_Name_4'));

        $recipients = array();
        if(!empty($emails)){
            foreach($emails as $key => $email){
                if(strpos($email,',')){
                    $multiple_emails = explode(',',$email);
                    $sending_emails = [];
                    foreach($multiple_emails as $index => $multiple_email){
                        $sending_emails[$index] = $jinput->getString($multiple_email,'');
                    }
                    $recipients[] = array('attachment_id' => $jinput->get($attachments[$key], $default_attachments[$key]), 'email' => implode(',',$sending_emails),'name'=>$jinput->getString($names[$key], JText::_('CIVILITY_MR').'/'.JText::_('CIVILITY_MRS')),'firstname'=>$jinput->getString($firstnames[$key], ''));
                } else {
                    $recipients[] = array('attachment_id' => $jinput->get($attachments[$key], $default_attachments[$key]), 'email' => $jinput->getString($email,''),'name'=>$jinput->getString($names[$key], JText::_('CIVILITY_MR').'/'.JText::_('CIVILITY_MRS')),'firstname'=>$jinput->getString($firstnames[$key], ''));
                }
            }
        } else {
            $recipients[] = array('attachment_id' => $jinput->get('jos_emundus_references___attachment_id_1', 4), 'email' => $jinput->getString('jos_emundus_references___Email_1', ''),'name'=>$jinput->getString('jos_emundus_references___Last_Name_1', JText::_('CIVILITY_MR').'/'.JText::_('CIVILITY_MRS')),'firstname'=>$jinput->getString('jos_emundus_references___First_Name_1', ''));
            $recipients[] = array('attachment_id' => $jinput->get('jos_emundus_references___attachment_id_2', 6), 'email' => $jinput->getString('jos_emundus_references___Email_2', ''),'name'=>$jinput->getString('jos_emundus_references___Last_Name_2', JText::_('CIVILITY_MR').'/'.JText::_('CIVILITY_MRS')),'firstname'=>$jinput->getString('jos_emundus_references___First_Name_1', ''));
            $recipients[] = array('attachment_id' => $jinput->get('jos_emundus_references___attachment_id_3', 21), 'email' => $jinput->getString('jos_emundus_references___Email_3', ''),'name'=>$jinput->getString('jos_emundus_references___Last_Name_3', JText::_('CIVILITY_MR').'/'.JText::_('CIVILITY_MRS')),'firstname'=>$jinput->getString('jos_emundus_references___First_Name_1', ''));
            $recipients[] = array('attachment_id' => $jinput->get('jos_emundus_references___attachment_id_4', 19), 'email' => $jinput->getString('jos_emundus_references___Email_4', ''),'name'=>$jinput->getString('jos_emundus_references___Last_Name_4', JText::_('CIVILITY_MR').'/'.JText::_('CIVILITY_MRS')),'firstname'=>$jinput->getString('jos_emundus_references___First_Name_1', ''));
        }

        $student = JFactory::getUser($student_id);
        $current_user = JFactory::getSession()->get('emundusUser');
        if (empty($current_user->fnum) || !isset($current_user->fnum)) {
            $current_user->fnum = $fnum;
        }

        $url = $this->getParam('url');
        $sef_url = $this->getParam('sef_url', false);
        $email_tmpl = $this->getParam('email_tmpl', 'referent_letter');

        // Récupération des données du mail
        $query = 'SELECT se.id, se.subject, se.emailfrom, se.name, se.message, et.Template
				FROM #__emundus_setup_emails AS se
				LEFT JOIN #__emundus_email_templates AS et ON se.email_tmpl = et.id
				WHERE se.lbl="'.$email_tmpl.'"';
        $db->setQuery($query);
        //$db->execute();
        $obj = $db->loadObjectList();

        // Récupération de la pièce jointe : modele de lettre
        $query = 'SELECT esp.reference_letter
                FROM #__emundus_setup_profiles as esp
                WHERE esp.id = '.$current_user->profile;
        $db->setQuery($query);
        $db->execute();
        $obj_letter = $db->loadRowList();

        //////////////////////////  SET FILES REQUEST  /////////////////////////////
        //
        // Génération de l'id du prochain fichier qui devra être ajouté par le referent
        $m_emails = new EmundusModelEmails;
        $m_profile = new EmundusModelProfile;
        $m_campaign = new EmundusModelCampaign;

        // setup mail
        $email_from_sys = $app->getCfg('mailfrom');

        $from = $obj[0]->emailfrom;
        $fromname = $obj[0]->name;

        $sender = array(
            $email_from_sys,
            $fromname
        );
        $attachment = array();
        if (!empty($obj_letter[0][0])) {
            $attachment[] = JPATH_BASE.str_replace("\\", "/", $obj_letter[0][0]);
        }

        $applicant_pdf = array();

        if($this->getParam('send_attachement') == 'true'){

            // GENERATE PDF
            $base_url = "https://" . $_SERVER['SERVER_NAME'];
            $last_file = null;
            try{
                $infos 		= $m_profile->getFnumDetails($current_user->fnum);
                $profile 	= !empty($infos['profile']) ? $infos['profile'] : $infos['profile_id'];
                $h_menu = new EmundusHelperMenu;
                $getformids = $h_menu->getUserApplicationMenu($profile);

                foreach ($getformids as $getformid) {
                    $formid[] = $getformid->form_id;
                }

                $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php';

                if (!file_exists(EMUNDUS_PATH_ABS.$student_id)) {
                    mkdir(EMUNDUS_PATH_ABS.$student_id);
                    chmod(EMUNDUS_PATH_ABS.$student_id, 0755);
                }

                require_once($file);

                // Here we call the profile by campaign function, which will get the profile of the campaign's initial phase
                $profile_id = $m_profile->getProfileByCampaign($campaign_id)['profile_id'];

                application_form_pdf($student->id, $current_user->fnum, true, 1, $formid, null, null, $profile_id);

                $query_pdf = 'SELECT filename
				FROM #__emundus_uploads
				WHERE fnum="'.$current_user->fnum.'"
				ORDER BY timedate DESC';

                $db->setQuery($query_pdf);
                $files = $db->loadColumn();
                if(!empty($files)){
                    $last_file = $files[0];

                    $applicant_pdf[] = JPATH_BASE.str_replace("\\", "/", '/images/emundus/files/'.$student->id.'/'.$last_file);
                }
            } catch (Exception $e) {
                JLog::add('Cannot generate pdf to send to referent = '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            }
        }
        //

        foreach ($recipients as $recipient) {
            if (isset($recipient['email']) && !empty($recipient['email'])) {

                $attachment_id = $recipient['attachment_id']; //ID provenant de la table emundus_attachments

                $query = 'SELECT count(id) as cpt FROM #__emundus_files_request 
							WHERE student_id='.$student->id.' AND attachment_id='.$attachment_id.' AND uploaded=1 AND fnum like '.$db->Quote($current_user->fnum);

                $db->setQuery($query);
                $db->execute();
                $is_uploaded = $db->loadResult();

                if ($is_uploaded == 0) {
                    $key = md5(date('Y-m-d h:m:i').'::'.$fnum.'::'.$student_id.'::'.$attachment_id.'::'.rand());
                    // 2. MAJ de la table emundus_files_request
                    $query = 'INSERT INTO #__emundus_files_request (time_date, student_id, keyid, attachment_id, campaign_id, fnum, email) 
                          VALUES ('.$db->Quote($now).', '.$student->id.', '.$db->Quote($key).', '.$attachment_id.', '.$fnum_detail['id'].', '.$db->Quote($current_user->fnum).', '.$db->Quote($recipient['email']).')';

                    $db->setQuery($query);
                    $db->execute();
                    $request_id = $db->insertid();

                    // 3. Envoi du lien vers lequel le professeur va pouvoir uploader la lettre de référence
                    if ($sef_url === 'true') {
                        $link_upload = $baseurl.$url.'?keyid='.$key.'&sid='.$student->id.'&fnum='.$current_user->fnum;
                    } else {
                        $link_upload = $baseurl.$url.'&keyid='.$key.'&sid='.$student->id.'&fnum='.$current_user->fnum;
                    }

                    $post = [
                        'ID'             => $student->id,
                        'NAME'           => $student->name,
                        'EMAIL'          => $student->email,
                        'UPLOAD_URL'     => $link_upload,
                        'PROGRAMME_NAME' => $fnum_detail['label'],
                        'FNUM'           => $fnum,
                        'USER_NAME'      => $fnum_detail['name'],
                        'CAMPAIGN_LABEL' => $fnum_detail['label'],
                        'SITE_URL'       => JURI::base(),
                        'USER_EMAIL'     => $fnum_detail['email'],
                        'REFERENT_NAME'  => $recipient['name'],
                        'REFERENT_FIRST_NAME'  => $recipient['firstname']
                    ];
                    $tags = $m_emails->setTags($fnum_detail['applicant_id'], $post, $fnum, '', $obj[0]->subject.$obj[0]->message);
                    $subject = $obj[0]->subject;
                    $body = $obj[0]->message;

                    if ($obj[0]->Template) {
                        $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/", "/\[SITE_NAME\]/"], [$subject, $body, JFactory::getConfig()->get('sitename')], $obj[0]->Template);
                    }

                    $subject = preg_replace($tags['patterns'], $tags['replacements'], $subject);
                    $body = preg_replace($tags['patterns'], $tags['replacements'], $body);

                    $body = $m_emails->setTagsFabrik($body, [$fnum_detail['fnum']]);
                    $subject = $m_emails->setTagsFabrik($subject, [$fnum_detail['fnum']]);

                    $referent_emails = explode(',',$recipient['email']);

                    foreach($referent_emails as $referent_email){
                        $to = array($referent_email);

                        $mailer = JFactory::getMailer();
                        $mailer->setSender($sender);
                        $mailer->addReplyTo($from, $fromname);
                        $mailer->addRecipient($to);
                        $mailer->setSubject($subject);
                        $mailer->isHTML(true);
                        $mailer->Encoding = 'base64';
                        $mailer->setBody($body);
                        $mailer->addAttachment($applicant_pdf);

                        $send = $mailer->Send();
                        if ($send !== true) {

                            JFactory::getApplication()->enqueueMessage(JText::_('MESSAGE_NOT_SENT').' : '.$recipient['email'], 'error');
                            JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');

                        } else {

                            JFactory::getApplication()->enqueueMessage(JText::_('MESSAGE_SENT').' : '.$recipient['email'], 'message');
                            $response = JText::_('SENT_TO'). ' '.$recipient['email'].'<br><a href="index.php?option=com_fabrik&view=details&formid=264&rowid='.$request_id.'&listid=273" target="_blank">'.JText::_('INVITATION_LINK').'</a><br>'.$body;

                            $sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
								VALUES ('62', '-1', ".$db->quote($subject).", ".$db->quote($response).", ".$db->quote($now).")";
                            $db->setQuery($sql);
                            try {
                                $db->execute();
                            } catch (Exception $e) {
                                // catch any database errors.
                            }

                        }
                        unset($replacements);
                        unset($mailer);
                    }
                }
            }
        }
        return true;
    }


    // 1. Génération aléatoire de l'ID
    function rand_string($len, $chars = 'abcdefghijklmnopqrstuvwxyz0123456789') {
        $string = '';
        for ($i = 0; $i < $len; $i++) {
            $pos = rand(0, strlen($chars)-1);
            $string .= $chars{$pos};
        }
        return $string;
    }


    /**
     * Raise an error - depends on whether you are in admin or not as to what to do
     *
     * @param   array   &$err    Form models error array
     * @param   string   $field  Name
     * @param   string   $msg    Message
     *
     * @return  void
     * @throws Exception
     */
    protected function raiseError(&$err, $field, $msg) {
        $app = JFactory::getApplication();

        if ($app->isClient('administrator')) {
            $app->enqueueMessage($msg, 'notice');
        } else {
            $err[$field][0][] = $msg;
        }
    }
}

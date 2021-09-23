<?php
/**
 * Messages controller used for the creation and emission of messages from the platform.
 *
 * @package    Joomla
 * @subpackage Emundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Hugo Moracchini
*/

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * eMundus Component Controller
 *
 * @package    Joomla.eMundus
 * @subpackage Components
 */
class EmundusControllerMessages extends JControllerLegacy {

    /**
     * Constructor
     *
     * @since 3.8.6
     */
    function __construct($config = array()) {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'messages.php');
	    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
	    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
	    parent::__construct($config);
    }

    /**
     * Get all of the information for an email template.
     *
     * @since 3.8.6
     */
    function gettemplate() {

        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');

        $jinput = JFactory::getApplication()->input;
        $template_id = $jinput->post->getInt('select', null);

        $m_messages = new EmundusModelMessages();
        $h_files = new EmundusHelperFiles();

        $get_candidate_attachments = $h_files->tableExists('#__emundus_setup_emails_repeat_candidate_attachment');
        $get_letters_attachments = $h_files->tableExists('#__emundus_setup_emails_repeat_letter_attachment');

        $template = $m_messages->getEmail($template_id, $get_candidate_attachments, $get_letters_attachments);

        if (!$template) {
            echo json_encode((object)(['status' => false]));
            exit;
        }

		echo json_encode((object)([
            'status' => true,
            'tmpl' => $template
        ]));
		exit;

    }

    /**
     * Get email templates by category.
     *
     * @since 3.8.6
     */
    public function setcategory() {

        $jinput = JFactory::getApplication()->input;
        $category = $jinput->get->getString('category', 'all');

        $m_messages = new EmundusModelMessages();

        $templates = $m_messages->getEmailsByCategory($category);

        if (!$templates) {
            echo json_encode((object)(['status' => false]));
            exit;
        }

        echo json_encode((object)([
            'status' => true,
            'templates' => $templates
        ]));
        exit;

    }


    /**
     * Upload a file from computer to be attached to the emails sent.
     *
     * @since 3.8.6
     */
    public function uploadfiletosend() {

        $jinput = JFactory::getApplication()->input;

        // If a filetype was sent in POST: check it.
	    $filetype = $jinput->post->get('filetype', null);

        // Get the file sent via AJAX POST
        $file = $jinput->files->get('file');

        // Get the user sent via AJAX POST
        $user = $jinput->post->get('user');

        // Get the user sent via AJAX POST
        $fnum = $jinput->post->get('fnum');

        // Check if an error is present
	    if (!isset($file['error']) || is_array($file['error'])) {
		    echo json_encode(['status' => false]);
		    exit;
	    }

	    // Sanitize filename.
	    $file['name'] = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $file['name']);
	    $file['name'] = preg_replace("([\.]{2,})", '', $file['name']);
	    $file['name'] = str_replace(array( '(', ')' ), '', $file['name']);

	    // Check if file name is alphanumeric
	    if (!preg_match("`^[-0-9A-Z_\.]+$`i", $file['name'])) {
	    	echo json_encode(['status' => false]);
	    	exit;
	    }

	    // Check if file name is not too long.
	    if (mb_strlen($file['name'], "UTF-8") > 225) {
	    	echo json_encode(['status' => false]);
	    	exit;
	    }

	    // If we specifically are uploading a PDF, check the MIME type.
	    if ($filetype == 'pdf' && $file['type'] != 'application/pdf') {
		    echo json_encode(['status' => false]);
		    exit;
	    }

	    // Check file extension and remove any dengerous ones.
	    if (preg_match("/.exe$|.com$|.bat$|.zip$|.php$|.sh$/i", $file['name'])){
		    exit("You cannot upload this type of file.");
	    }
        // Check if the message attachments directory exists.
        if (!is_dir('images'.DS.'emundus'.DS.'files'.DS.$user.DS.$fnum)) {
            mkdir('images'.DS.'emundus'.DS.'files'.DS.$user.DS.$fnum, 0777, true);
        }

        // Move the uploaded file to the server directory.

	    if (!empty($user) && empty($fnum)) {
		    $target = 'images'.DS.'emundus'.DS.'files'.DS.$user.DS.$fnum.DS.$file['name'];
	    } else {
		    $target = 'images'.DS.'emundus'.DS.'files'.DS.$file['name'];
	    }

        if (file_exists($target)) {
	        unlink($target);
        }

        move_uploaded_file($file['tmp_name'], $target);

        // Send back the info to the frontend.
        echo json_encode(['status' => true, 'file_name' => $file['name'], 'file_path' => $target]);
        exit;

    }


    /**
     * Gets the names of the candidate files.
     * @since 3.8.6
     */
    public function getcandidatefilenames() {

        $m_messages = new EmundusModelMessages();

        $jinput = JFactory::getApplication()->input;
        $attachment_ids = $jinput->post->getString('attachments', null);

        if (empty($attachment_ids)) {
            echo json_encode((object)['status' => false]);
            exit;
        }

        $attachments = $m_messages->getCandidateFileNames($attachment_ids);

        if (!$attachments) {
            echo json_encode((object)['status' => false]);
            exit;
        }

        echo json_encode((object)['status' => true, 'attachments' => $attachments]);
        exit;

    }

    /**
     * Gets the names of the letter files.
     * @since 3.8.6
     */
    public function getletterfilenames() {

        $m_messages = new EmundusModelMessages();

        $jinput = JFactory::getApplication()->input;
        $attachment_ids = $jinput->post->getString('attachments', null);

        if (empty($attachment_ids)) {
            echo json_encode((object)['status' => false]);
            exit;
        }

        $attachments = $m_messages->getLetterFileNames($attachment_ids);

        if (!$attachments) {
            echo json_encode((object)['status' => false]);
            exit;
        }

        echo json_encode((object)['status' => true, 'attachments' => $attachments]);
        exit;

    }


    /**
     * Builds an HTML preview of the message to be sent alongside a recap of other information.
     *
     * @since 3.8.13
     */
    public function previewemail() {

        if (!EmundusHelperAccess::asAccessAction(9, 'c')) {
            die(JText::_("ACCESS_DENIED"));
        }

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');

        $m_messages = new EmundusModelMessages();
        $m_emails = new EmundusModelEmails();
        $m_files = new EmundusModelFiles();
        $m_campaign = new EmundusModelCampaign();

        $config = JFactory::getConfig();
        $jinput = JFactory::getApplication()->input;

        // Get default mail sender info
        $mail_from_sys = $config->get('mailfrom');
        $mail_from_sys_name = $config->get('fromname');

        $fnums = explode(',',$jinput->post->get('recipients', null, null));
        $nb_recipients = count($fnums);

        if ($nb_recipients > 1) {
            $html = '<h2>'.JText::sprintf('COM_EMUNDUS_EMAIL_ABOUT_TO_SEND', $nb_recipients).'</h2>';
        } else {
            $html = '';
        }

        // If no mail sender info is provided, we use the system global config.
        $mail_from_name = $jinput->post->getString('mail_from_name', $mail_from_sys_name);
        $mail_from = $jinput->post->getString('mail_from', $mail_from_sys);

        $mail_subject = $jinput->post->getString('mail_subject', 'No Subject');
        $template_id = $jinput->post->getInt('template', null);
        $mail_message = $jinput->post->get('message', null, 'RAW');
        $attachments = $jinput->post->get('attachments', null, null);


        // Here we filter out any CC or BCC emails that have been entered that do not match the regex.
        $cc = $jinput->post->getString('cc');
	    $bcc = $jinput->post->getString('bcc');

	    if (!empty($bcc)) {

		    if (!is_array($bcc)) {
			    $bcc = [];
		    }

		    $bcc_html = '';
		    foreach ($bcc as $key => $bcc_to_test) {
			    if (preg_match('/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-z\-0-9]+\.)+[a-z]{2,}))$/', $bcc_to_test) !== 1) {
				    unset($bcc[$key]);
			    } else {
				    $bcc_html .= '<li>'.$bcc_to_test.'</li>';
			    }
		    }
	    }

	    if (!empty($cc)) {
		    if (!is_array($cc)) {
			    $cc = [];
		    }

		    $cc_html = '';
		    foreach ($cc as $key => $cc_to_test) {
			    if (preg_match('/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-z\-0-9]+\.)+[a-z]{2,}))$/', $cc_to_test) !== 1) {
				    unset($cc[$key]);
			    } else {
				    $cc_html .= '<li>'.$cc_to_test.'</li>';
			    }
		    }
	    }

	    if (isset($cc_html) || isset($bcc_html)) {

	    	$html .= '<div class="well">';

		    if (isset($bcc_html)) {
			    $html .= '<strong>'.JText::_('COM_EMUNDUS_EMAIL_PEOPLE_BCC').'</strong> <ul>'.$bcc_html.'</ul>';
		    }

		    if (isset($cc_html)) {
			    $html .= '<strong>'.JText::_('COM_EMUNDUS_EMAIL_PEOPLE_CC').'</strong> <ul>'.$cc_html.'</ul>';
		    }

		    if ($nb_recipients > 1) {
			    $html .= '<span class="alert alert-info">'.JText::sprintf('COM_EMUNDUS_EMAIL_CC_BCC_WILL_RECEIVE', $nb_recipients).'</span>';
		    }

		    $html .= '</div>';
	    }

        // Get additional info for only the first fnum.
        $fnum = $m_files->getFnumsInfos([$fnums[0]], 'object')[$fnums[0]];

        // Loading the message template is not used for getting the message text as that can be modified on the frontend by the user before sending.
        $template = $m_messages->getEmail($template_id);
        $programme = $m_campaign->getProgrammeByTraining($fnum->training);

        $toAttach = [];
        $post = [
            'FNUM' => $fnum->fnum,
            'USER_NAME' => $fnum->name,
            'COURSE_LABEL' => $programme->label,
            'CAMPAIGN_LABEL' => $fnum->label,
            'CAMPAIGN_YEAR' => $fnum->year,
            'CAMPAIGN_START' => $fnum->start_date,
            'CAMPAIGN_END' => $fnum->end_date,
            'SITE_URL' => JURI::base(),
            'USER_EMAIL' => $fnum->email
        ];

        $tags = $m_emails->setTags($fnum->applicant_id, $post, $fnum->fnum);
        $message = $m_emails->setTagsFabrik($mail_message, [$fnum->fnum]);
        $subject = $m_emails->setTagsFabrik($mail_subject, [$fnum->fnum]);

        // Tags are replaced with their corresponding values.
        if (empty($template) || empty($template->Template)) {
        	$db = JFactory::getDbo();
        	$query = $db->getQuery(true);

        	$query->select($db->quoteName('Template'))
		        ->from($db->quoteName('#__emundus_email_templates'))
		        ->where($db->quoteName('id').' = 1');
        	$db->setQuery($query);

        	$template->Template = $db->loadResult();
        }

        $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $message], $template->Template);
	    $subject = preg_replace($tags['patterns'], $tags['replacements'], $subject);
	    $body = preg_replace($tags['patterns'], $tags['replacements'], $body);


        // Get Sender and reply to addresses.
        $mail_from = preg_replace($tags['patterns'], $tags['replacements'], $mail_from);
        $mail_from_name = preg_replace($tags['patterns'], $tags['replacements'], $mail_from_name);

        // If the email sender has the same domain as the system sender address.
        if (substr(strrchr($mail_from, "@"), 1) === substr(strrchr($mail_from_sys, "@"), 1)) {
            $mail_from_address = $mail_from;
        } else {
            $mail_from_address = $mail_from_sys;
            if (!empty($mail_from_name) && !empty($mail_from)) {
                $reply_to = $mail_from_name . ' &lt;' . $mail_from . '&gt;';
            }
        }

        $sender = $mail_from_name.' &lt;'.$mail_from_address.'&gt;';

        // Build message preview.
        $html .= '</hr><div class="email-info">
                    <strong>'.JText::_('COM_EMUNDUS_EMAILS_FROM').'</strong> '.$sender.' </br>';

        if (isset($reply_to)) {
            $html .= '<strong>'.JText::_('COM_EMUNDUS_EMAILS_REPLY_TO').'</strong> '.$reply_to.' </br>';
        }

        $html .= '<strong>'.JText::_('COM_EMUNDUS_EMAILS_TO').'</strong> '.$fnum->email.' </br>'.
                 '<strong>'.JText::_('COM_EMUNDUS_EMAILS_SUBJECT').'</strong> '.$subject.' </br>'.
                 '<strong>'.JText::_('COM_EMUNDUS_EMAILS_BODY').'</strong>
			</div>
			<div class="well">'.$body.'</div>';


        // Retrieve and build a list of the files that will be attached to the mail.

        // Files uploaded from the frontend.
        if (!empty($attachments['upload'])) {
            // In the case of an uploaded file, just add it to the email.
            foreach ($attachments['upload'] as $upload) {
                if (file_exists(JPATH_BASE.DS.$upload)) {
                    $toAttach['upload'][] = pathinfo($upload)['basename'];
                }
            }
        }

        // Files gotten from candidate files, requires attachment read rights.
        if (EmundusHelperAccess::asAccessAction(4, 'r') && !empty($attachments['candidate_file'])) {

            // Get from DB by fnum.
            foreach ($attachments['candidate_file'] as $candidate_file) {

                $filename = $m_messages->get_filename($candidate_file);

                if ($filename) {
                    $toAttach['candidate_file'][] = $filename;
                }
            }
        }

        // Files generated using the Letters system. Requires attachment creation and doc generation rights.
        if (EmundusHelperAccess::asAccessAction(4, 'c') && EmundusHelperAccess::asAccessAction(27, 'c') && !empty($attachments['setup_letters'])) {

	        // Get from DB and generate using the tags.
            foreach ($attachments['setup_letters'] as $setup_letter) {

                $letter = $m_messages->get_letter($setup_letter);

                // We only get the letters if they are for that particular programme and/or status.
                if ($letter && in_array($fnum->training, explode('","',$letter->training)) && ($letter->status == null || in_array($fnum->step, explode(',',$letter->status)))) {
                    $toAttach['letter'][] = $letter->title;
                }
            }
        }


        $files = '';
        if (!empty($toAttach)) {

            $files .= '<div class="well"><h3>'.JText::_('COM_EMUNDUS_EMAILS_ATTACHMENTS').'</h3>';

            if (!empty($toAttach['upload'])) {

                $files .= '<strong>'.JText::_('UPLOAD').'</strong>';

                $files .= '<ul>';
                foreach ($toAttach['upload'] as $attach) {
                    $files .= '<li>' . $attach . '</li>';
                }
                $files .= '</ul>';
            }


            if (!empty($toAttach['candidate_file'])) {

                $files .= '<strong>'.JText::_('CANDIDATE_FILE').'</strong>';

                $files .= '<ul>';
                foreach ($toAttach['candidate_file'] as $attach) {
                    $files .= '<li>' . $attach . '</li>';
                }
                $files .= '</ul>';
            }


            if (!empty($toAttach['letter'])) {

                $files .= '<strong>'.JText::_('SETUP_LETTERS_ATTACH').'</strong><ul>';
                foreach ($toAttach['letter'] as $attach) {
	                $files .= '<li>'.$attach.'</li>';
                }
                $files .= '</ul>';
            }
            $files .= '</div>';
        }

        $html .= $files;

        echo json_encode(['status' => true, 'html' => $html]);
        exit;
    }


    /**
     * Send the email defined in the dialog.
     *
     * @since 3.8.6
     */
    public function applicantemail() {

        if (!EmundusHelperAccess::asAccessAction(9, 'c')) {
			die(JText::_("ACCESS_DENIED"));
		}

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
	    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');

        $m_messages = new EmundusModelMessages();
        $m_emails = new EmundusModelEmails();
        $m_files = new EmundusModelFiles();
        $m_campaign = new EmundusModelCampaign();

        $user = JFactory::getUser();
        $config = JFactory::getConfig();
        $jinput = JFactory::getApplication()->input;

        // Get default mail sender info
        $mail_from_sys = $config->get('mailfrom');
        $mail_from_sys_name = $config->get('fromname');

        $fnums  = explode(',',$jinput->post->get('recipients', null, null));

        // If no mail sender info is provided, we use the system global config.
        $mail_from_name = $jinput->post->getString('mail_from_name', $mail_from_sys_name);
        $mail_from = $jinput->post->getString('mail_from', $mail_from_sys);

        $mail_subject = $jinput->post->getString('mail_subject', 'No Subject');
        $template_id = $jinput->post->getInt('template', null);
        $mail_message = $jinput->post->get('message', null, 'RAW');
        $attachments = $jinput->post->get('attachments', null, null);
        $tags_str = $jinput->post->getString('tags', null, null);


        // Here we filter out any CC or BCC emails that have been entered that do not match the regex.
        $cc = $jinput->post->getString('cc');
        if (!empty($cc)) {
            if (!is_array($cc)) {
                $cc = [];
            }
            foreach ($cc as $key => $cc_to_test) {
                if (preg_match('/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-z\-0-9]+\.)+[a-z]{2,}))$/', $cc_to_test) !== 1) {
                    unset($cc[$key]);
                }
            }
        }

        $bcc = $jinput->post->getString('bcc');
        if (!empty($bcc)) {
            if (!is_array($bcc)) {
                $bcc = [];
            }
            foreach ($bcc as $key => $bcc_to_test) {
                if (preg_match('/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-z\-0-9]+\.)+[a-z]{2,}))$/', $bcc_to_test) !== 1) {
                    unset($bcc[$key]);
                }
            }
        }

        // Get additional info for the fnums such as the user email.
        $fnums = $m_files->getFnumsInfos($fnums, 'object');

        // This will be filled with the email adresses of successfully sent emails, used to give feedback to front end.
        $sent = [];
        $failed = [];

        // Loading the message template is not used for getting the message text as that can be modified on the frontend by the user before sending.
        $template = $m_messages->getEmail($template_id);

        foreach ($fnums as $fnum) {
            if($tags_str != null){
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);

                $tags = explode(',',$tags_str);


                foreach($tags as $tag){
                    try{
                        $query->clear()
                            ->insert($db->quoteName('#__emundus_tag_assoc'));
                        $query->set($db->quoteName('fnum') . ' = ' . $db->quote($fnum->fnum))
                            ->set($db->quoteName('id_tag') . ' = ' . $db->quote($tag))
                            ->set($db->quoteName('user_id') . ' = ' . $db->quote($fnum->applicant_id));

                        $db->setQuery($query);
                        $db->execute();
                    }  catch (Exception $e) {
                        JLog::add('NOT IMPORTANT IF DUPLICATE ENTRY : Error getting template in model/messages at query :'.$query->__toString(). " with " . $e->getMessage(), JLog::ERROR, 'com_emundus');
                    }
                }
            }

            $programme = $m_campaign->getProgrammeByTraining($fnum->training);

            $toAttach = [];
            $post = [
                'FNUM' => $fnum->fnum,
                'USER_NAME' => $fnum->name,
                'COURSE_LABEL' => $programme->label,
                'CAMPAIGN_LABEL' => $fnum->label,
                'CAMPAIGN_YEAR' => $fnum->year,
                'CAMPAIGN_START' => $fnum->start_date,
                'CAMPAIGN_END' => $fnum->end_date,
                'SITE_URL' => JURI::base(),
                'USER_EMAIL' => $fnum->email
            ];

            $tags = $m_emails->setTags($fnum->applicant_id, $post, $fnum->fnum);
            $body = $m_emails->setTagsFabrik($mail_message, [$fnum->fnum]);
            $subject = $m_emails->setTagsFabrik($mail_subject, [$fnum->fnum]);

            // Tags are replaced with their corresponding values using the PHP preg_replace function.
            $subject = preg_replace($tags['patterns'], $tags['replacements'], $subject);

            if (empty($template) || empty($template->Template)) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->select($db->quoteName('Template'))
                    ->from($db->quoteName('#__emundus_email_templates'))
                    ->where($db->quoteName('id').' = 1');
                $db->setQuery($query);

                $template->Template = $db->loadResult();
            }

            $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $template->Template);
            $body = preg_replace($tags['patterns'], $tags['replacements'], $body);

	        $mail_from = preg_replace($tags['patterns'], $tags['replacements'], $mail_from);
	        $mail_from_name = preg_replace($tags['patterns'], $tags['replacements'], $mail_from_name);

	        // If the email sender has the same domain as the system sender address.
	        if (substr(strrchr($mail_from, "@"), 1) === substr(strrchr($mail_from_sys, "@"), 1)) {
		        $mail_from_address = $mail_from;
	        } else {
		        $mail_from_address = $mail_from_sys;
	        }

	        // Set sender
	        $sender = [
		        $mail_from_address,
		        $mail_from_name
	        ];

            // Configure email sender
            $mailer = JFactory::getMailer();
            $mailer->setSender($sender);
            $mailer->addReplyTo($mail_from, $mail_from_name);
            $mailer->addRecipient($fnum->email);
            $mailer->setSubject($subject);
            $mailer->isHTML(true);
            $mailer->Encoding = 'base64';
            $mailer->setBody($body);

            if (!empty($cc)) {
                $mailer->addCc($cc);
            }

            if (!empty($bcc)) {
                $mailer->addBcc($bcc);
            }

            // Files uploaded from the frontend.
            if (!empty($attachments['upload'])) {
                // In the case of an uploaded file, just add it to the email.
                foreach ($attachments['upload'] as $upload) {
                    if (file_exists(JPATH_BASE.DS.$upload)) {
                        $toAttach[] = JPATH_BASE.DS.$upload;
                    }
                }
            }

            // Files gotten from candidate files, requires attachment read rights.
            if (EmundusHelperAccess::asAccessAction(4, 'r') && !empty($attachments['candidate_file'])) {

                // Get from DB by fnum.
                foreach ($attachments['candidate_file'] as $candidate_file) {

                    $filename = $m_messages->get_upload($fnum->fnum, $candidate_file);

                      if ($filename != false) {

                        // Build the path to the file we are searching for on the disk.
                        $path = EMUNDUS_PATH_ABS.$fnum->applicant_id.DS.$filename;

                        if (file_exists($path)) {
                            $toAttach[] = $path;
                        }
                    }
                }
            }

            // Files generated using the Letters system. Requires attachment creation and doc generation rights.
            if (EmundusHelperAccess::asAccessAction(4, 'c') && EmundusHelperAccess::asAccessAction(27, 'c') && !empty($attachments['setup_letters'])) {

                // Get from DB and generate using the tags.
                foreach ($attachments['setup_letters'] as $setup_letter) {

                    $letter = $m_messages->get_letter($setup_letter);

                    // We only get the letters if they are for that particular programme.
                    if ($letter && in_array($fnum->training, explode('","',$letter->training))) {

                        // Some letters are only for files of a certain status, this is where we check for that.
                        if ($letter->status != null && !in_array($fnum->step, explode(',',$letter->status))) {
                            continue;
                        }

                        // A different file is to be generated depending on the template type.
                        switch ($letter->template_type) {

                            case '1':
                                // This is a static file, we just need to find its path add it as an attachment.
                                if (file_exists(JPATH_BASE.$letter->file)) {
                                    $toAttach[] = JPATH_BASE.$letter->file;
                                }
                            break;

                            case '2':
                                // This is a PDF to be generated from HTML.
                                require_once (JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php');

                                $path = generateLetterFromHtml($letter, $fnum->fnum, $fnum->applicant_id, $fnum->training);

                                if ($path && file_exists($path)) {
                                    $toAttach[] = $path;
                                }
                            break;

                            case '3':
                                // This is a DOC template to be completed with applicant information.
                                $path = $m_messages->generateLetterDoc($letter, $fnum->fnum);

                                if ($path && file_exists($path)) {
                                    $toAttach[] = $path;
                                }
                            break;

                            default:
                            break;
                        }
                    }
                }
            }
            $files = '';

            if (!empty($toAttach)) {

                $files = '<ul>';
                if(count($attachments['upload']) > 0) {
                    foreach ($attachments['upload'] as $attach) {
                        $filesName = basename($attach);
                        $files .= '<li>' . $filesName . '</li>';
                    }
                }
                if(count($attachments['candidate_file']) > 0) {
                    foreach ($attachments['candidate_file'] as $attach) {

                        $idTypeFile = $attach;
                        $typeAttachments = $this->getTypeAttachment($idTypeFile);
                        foreach ($typeAttachments as $typeAttachment) {
                            $nameType = $typeAttachment->value;
                        }

                        $files .= '<li>' . $nameType . '</li>';
                    }
                }
                if(count($attachments['setup_letters']) > 0) {
                    foreach ($attachments['setup_letters'] as $attach) {
                        $idTypeFile = $attach;
                        $typeAttachments = $this->getTypeLetters($idTypeFile);
                        foreach ($typeAttachments as $typeAttachment) {
                            $nameType = $typeAttachment->title;
                        }
                        $files .= '<li>' . $nameType . '</li>';
                    }
                }
            }

            $files .= '</ul>';


            $mailer->addAttachment($toAttach);

            // Send and log the email.
            $send = $mailer->Send();
            if ($send !== true) {
                $failed[] = $fnum->email;
                echo 'Error sending email: ' . $send->__toString();
                JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
            } else {
                $sent[] = $fnum->email;
                $log = [
                    'user_id_from' => $user->id,
                    'user_id_to' => $fnum->applicant_id,
                    'subject' => $subject,
                    'message' => '<i>' . JText::_('MESSAGE') . ' ' . JText::_('SENT') . ' ' . JText::_('TO') . ' ' . $fnum->email . '</i><br>' . $body . $files,
                    'type' => (empty($template->type))?'':$template->type
                ];
                $m_emails->logEmail($log);
                // Log the email in the eMundus logging system.
                EmundusModelLogs::log($user->id, $fnum->applicant_id, $fnum->fnum, 9, 'c', 'COM_EMUNDUS_LOGS_SEND_EMAIL');
            }

            // Due to mailtrap now limiting emails sent to fast, we add a long sleep.
            if ($config->get('smtphost') === 'smtp.mailtrap.io') {
            	sleep(5);
            }

        }
        echo json_encode(['status' => true, 'sent' => $sent, 'failed' => $failed]);
        exit;
    }


	/**
	 * Send an email to a user, regardless of fnum.
	 *
	 * @since 3.8.10
	 */
	public function useremail() {


		if (!EmundusHelperAccess::asAccessAction(9, 'c')) {
			die(JText::_("ACCESS_DENIED"));
		}

		require_once (JPATH_COMPONENT.DS.'models'.DS.'users.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'emails.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'campaign.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'logs.php');

		$m_messages = new EmundusModelMessages();
		$m_emails = new EmundusModelEmails();
		$m_users = new EmundusModelUsers();

		$current_user = JFactory::getUser();
		$config = JFactory::getConfig();
		$jinput = JFactory::getApplication()->input;

		// Get default mail sender info
		$mail_from_sys = $config->get('mailfrom');
		$mail_from_sys_name = $config->get('fromname');

		$uids  = explode(',',$jinput->post->get('recipients', null, null));
		$bcc = $jinput->post->getString('Bcc', false);

		// If no mail sender info is provided, we use the system global config.
		$mail_from_name = $jinput->post->getString('mail_from_name', $mail_from_sys_name);
		$mail_from = $jinput->post->getString('mail_from', $mail_from_sys);

		$mail_subject = $jinput->post->getString('mail_subject', 'No Subject');
		$template_id = $jinput->post->getInt('template', null);
		$mail_message = $jinput->post->get('message', null, 'RAW');
		$attachments = $jinput->post->get('attachments', null, null);

		// Get additional info for the fnums such as the user email.
		$users = $m_users->getUsersByIds($uids);

		// This will be filled with the email adresses of successfully sent emails, used to give feedback to front end.
		$sent = [];
		$failed = [];

		// Loading the message template is not used for getting the message text as that can be modified on the frontend by the user before sending.
		$template = $m_messages->getEmail($template_id);

		foreach ($users as $user) {

			$toAttach = [];
			$post = [
				'USER_NAME' => $user->name,
				'SITE_URL' => JURI::base(),
				'USER_EMAIL' => $user->email
			];

			$tags = $m_emails->setTags($user->id, $post);

			// Tags are replaced with their corresponding values using the PHP preg_replace function.
			$subject = preg_replace($tags['patterns'], $tags['replacements'], $mail_subject);
			$body = $mail_message;
			if ($template) {
				$body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $template->Template);
			}
			$body = preg_replace($tags['patterns'], $tags['replacements'], $body);

			$mail_from = preg_replace($tags['patterns'], $tags['replacements'], $mail_from);
			$mail_from_name = preg_replace($tags['patterns'], $tags['replacements'], $mail_from_name);

			// If the email sender has the same domain as the system sender address.
			if (substr(strrchr($mail_from, "@"), 1) === substr(strrchr($mail_from_sys, "@"), 1)) {
				$mail_from_address = $mail_from;
			} else {
				$mail_from_address = $mail_from_sys;
			}

			// Set sender
			$sender = [
				$mail_from_address,
				$mail_from_name
			];

			// Configure email sender
			$mailer = JFactory::getMailer();
			$mailer->setSender($sender);
			$mailer->addReplyTo($mail_from, $mail_from_name);
			$mailer->addRecipient($user->email);
			$mailer->setSubject($subject);
			$mailer->isHTML(true);
			$mailer->Encoding = 'base64';
			$mailer->setBody($body);

			if ($bcc === 'true') {
				$mailer->addBCC($current_user->email);
			}

			$files = '';
			// Files uploaded from the frontend.
			if (!empty($attachments)) {

				// Here we also build the HTML being logged to show which files were attached to the email.
				$files = '<ul>';
				foreach ($attachments as $upload) {
					if (file_exists(JPATH_BASE.DS.$upload)) {
						$toAttach[] = JPATH_BASE.DS.$upload;
						$files .= '<li>'.basename($upload).'</li>';
					}
				}
				$files .= '</ul>';

			}

			$mailer->addAttachment($toAttach);

			// Send and log the email.
			$send = $mailer->Send();
			if ($send !== true) {
				$failed[] = $user->email;
				echo 'Error sending email: ' . $send->__toString();
				JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
			} else {
				$sent[] = $user->email;
				$log = [
					'user_id_from' => $current_user->id,
					'user_id_to' => $user->id,
					'subject' => $subject,
					'message' => '<i>' . JText::_('MESSAGE') . ' ' . JText::_('SENT') . ' ' . JText::_('TO') . ' ' . $user->email . '</i><br>' . $body . $files,
					'type' => !empty($template)?$template->type:''
				];
				$m_emails->logEmail($log);
				// Log the email in the eMundus logging system.
				EmundusModelLogs::log($current_user->id, $user->id, '', 9, 'c', 'COM_EMUNDUS_LOGS_SEND_EMAIL');
			}

		}
		echo json_encode(['status' => true, 'sent' => $sent, 'failed' => $failed]);
		exit;
	}

	/** The generic function used for sending emails.
	 *
	 * @param       $fnum
	 * @param       $email_id
	 * @param null  $post
	 * @param array $attachments
	 * @param bool  $bcc
	 *
	 * @return bool
	 * @throws \PhpOffice\PhpWord\Exception\CopyFileException
	 * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
	 * @throws \PhpOffice\PhpWord\Exception\Exception
	 */
    function sendEmail($fnum, $email_id, $post = null, $attachments = [], $bcc = false) {

        require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
	    require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
	    require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
	    require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
	    require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'files.php');

        $m_messages = new EmundusModelMessages();
	    $m_emails   = new EmundusModelEmails();
	    $m_files    = new EmundusModelFiles();
	    $h_files    = new EmundusHelperFiles();
	    $m_campaign = new EmundusModelCampaign();

	    $user   = JFactory::getUser();
	    $config = JFactory::getConfig();

	    // Get additional info for the fnums such as the user email.
	    $fnum = $m_files->getFnumInfos($fnum);

	    $get_candidate_attachments = $h_files->tableExists('#__emundus_setup_emails_repeat_candidate_attachment');
	    $get_letters_attachments = $h_files->tableExists('#__emundus_setup_emails_repeat_letter_attachment');
	    $template = $m_messages->getEmail($email_id, $get_candidate_attachments, $get_letters_attachments);

	    $programme = $m_campaign->getProgrammeByTraining($fnum['training']);

	    // In case no post value is supplied
	    if (empty($post)) {
		    $post = [
			    'FNUM'           => $fnum['fnum'],
			    'USER_NAME'      => $fnum['name'],
			    'COURSE_LABEL'   => $programme->label,
			    'CAMPAIGN_LABEL' => $fnum['label'],
			    'SITE_URL'       => JURI::base(),
			    'USER_EMAIL'     => $fnum['email']
		    ];
	    }
	    $tags = $m_emails->setTags($fnum['applicant_id'], $post, $fnum['fnum']);

	    // Get default mail sender info
	    $mail_from_sys = $config->get('mailfrom');
	    $mail_from_sys_name = $config->get('fromname');

	    // If no mail sender info is provided, we use the system global config.
	    $mail_from_name = $user->name;
	    $mail_from = preg_replace($tags['patterns'], $tags['replacements'], $template->emailfrom);

	    // If the email sender has the same domain as the system sender address.
	    if (substr(strrchr($mail_from, "@"), 1) === substr(strrchr($mail_from_sys, "@"), 1)) {
		    $mail_from_address = $mail_from;
	    } else {
		    $mail_from_address = $mail_from_sys;
		    $mail_from_name = $mail_from_sys_name;
	    }

	    // Set sender
	    $sender = [
		    $mail_from_address,
		    $mail_from_name
	    ];

	    if (!empty($attachments) && is_array($attachments)) {
	        $toAttach = $attachments;
	    } else {
		    $toAttach[] = $attachments;
	    }

	    $message = $m_emails->setTagsFabrik($template->message, [$fnum['fnum']]);
	    $subject = $m_emails->setTagsFabrik($template->subject, [$fnum['fnum']]);

	    // Tags are replaced with their corresponding values using the PHP preg_replace function.
	    $subject = preg_replace($tags['patterns'], $tags['replacements'], $subject);
	    $body = $message;
	    if ($template) {
		    $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $template->Template);
	    }
	    $body = preg_replace($tags['patterns'], $tags['replacements'], $body);

	    // Configure email sender
	    $mailer = JFactory::getMailer();
	    if ($bcc) {
		    $mailer->addBCC($user->email);
	    }
	    $mailer->setSender($sender);
	    $mailer->addReplyTo($mail_from, $mail_from_name);
	    $mailer->addRecipient($fnum['email']);
	    $mailer->setSubject($subject);
	    $mailer->isHTML(true);
	    $mailer->Encoding = 'base64';
	    $mailer->setBody($body);


        // Get any candidate files included in the message.
        if (!empty($template->candidate_file)) {
            foreach ($template->candidate_file as $candidate_file) {

                $filename = $m_messages->get_upload($fnum['fnum'], $candidate_file);

                if ($filename) {

                    // Build the path to the file we are searching for on the disk.
                    $path = EMUNDUS_PATH_ABS.$fnum['applicant_id'].DS.$filename;

                    if (file_exists($path)) {
                        $toAttach[] = $path;
                    }
                }
            }
        }


	    // Files generated using the Letters system. Requires attachment creation and doc generation rights.
        // Get from DB and generate using the tags.
        if (!empty($template->setup_letters)) {
            foreach ($template->setup_letters as $setup_letter) {

                $letter = $m_messages->get_letter($setup_letter);

                // We only get the letters if they are for that particular programme.
                if ($letter && in_array($fnum['training'], explode('","', $letter->training))) {

                    // Some letters are only for files of a certain status, this is where we check for that.
                    if ($letter->status != null && !in_array($fnum['step'], explode(',', $letter->status))) {
	                    continue;
                    }

                    // A different file is to be generated depending on the template type.
                    switch ($letter->template_type) {

                        case '1':
                            // This is a static file, we just need to find its path add it as an attachment.
                            if (file_exists(JPATH_BASE.$letter->file)) {
                                $toAttach[] = JPATH_BASE.$letter->file;
                            }
                            break;

                        case '2':
                            // This is a PDF to be generated from HTML.
                            require_once (JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php');

                            $path = generateLetterFromHtml($letter, $fnum['fnum'], $fnum['applicant_id'], $fnum['training']);

                            if ($path && file_exists($path)) {
	                            $toAttach[] = $path;
                            }
                            break;

                        case '3':
                            // This is a DOC template to be completed with applicant information.
                            $path = $m_messages->generateLetterDoc($letter, $fnum['fnum']);

                            if ($path && file_exists($path)) {
	                            $toAttach[] = $path;
                            }
                            break;

	                    default:
                        break;

                    }
                }
            }
        }

        if (!empty($toAttach)) {
	        $mailer->addAttachment($toAttach);
        }
	    // Send and log the email.
        $send = $mailer->Send();

	    if ($send !== true) {

		    JLog::add($send, JLog::ERROR, 'com_emundus');
		    return false;

	    } else {

		    $sent[] = $fnum['email'];
		    $log = [
			    'user_id_from'  => $user->id,
			    'user_id_to'    => $fnum['applicant_id'],
			    'subject'       => $subject,
			    'message'       => '<i>'.JText::_('MESSAGE_SENT_TO').' '.$fnum['email'].'</i><br>'.$body,
			    'type'          => $template->type
		    ];
		    $m_emails->logEmail($log);

		    // Log the email in the eMundus logging system.
		    EmundusModelLogs::log($user->id, $fnum['applicant_id'], $fnum['fnum'], 9, 'c', 'COM_EMUNDUS_LOGS_SEND_EMAIL');

		    return true;
	    }
    }

	/** The generic function used for sending emails outside of emundus.
	 *
	 * @param String $email_address
	 * @param Mixed $email If a numeric ID is provided, use that, if a string is provided, get the email with that label.
	 * @param null $post
	 * @param null $user_id
	 * @param array $attachments
     * @param array $fnum If we need to replace fabrik tags
	 *
	 * @return bool
	 */
	function sendEmailNoFnum($email_address, $email, $post = null, $user_id = null, $attachments = [], $fnum = null) {

        include_once(JPATH_SITE.'/components/com_emundus/models/emails.php');
        $m_email = new EmundusModelEmails;
		$m_messages = new EmundusModelMessages();

		$user   = JFactory::getUser();
		$config = JFactory::getConfig();

		$template = $m_messages->getEmail($email);

		// Get default mail sender info
		$mail_from_sys = $config->get('mailfrom');
		$mail_from_sys_name = $config->get('fromname');

		// If no mail sender info is provided, we use the system global config.
		$mail_from_name = $user->name;
		$mail_from      = $template->emailfrom;

		// If the email sender has the same domain as the system sender address.
		if (substr(strrchr($mail_from, "@"), 1) === substr(strrchr($mail_from_sys, "@"), 1))
			$mail_from_address = $mail_from;
		else {
			$mail_from_address = $mail_from_sys;
			$mail_from_name = $mail_from_sys_name;
		}

		// Set sender
		$sender = [
			$mail_from_address,
			$mail_from_name
		];

		if (!empty($attachments) && is_array($attachments)) {
			$toAttach = $attachments;
		} else {
			$toAttach[] = $attachments;
		}

		// In case no post value is supplied
		if (empty($post)) {
			$post = [
				'SITE_URL'      => JURI::base(),
				'USER_EMAIL'    => $email_address
			];
		}

        if($user_id != null) {
            $password = !empty($post['PASSWORD']) ? $post['PASSWORD'] : "";
            $post = $m_email->setTags($user_id, $post, null, $password);
        } else {
            // Handle [] in post keys.
            $keys = [];
            foreach (array_keys($post) as $key) {
                $keys[] = '/\['.$key.'\]/';
            }
        }


		// Tags are replaced with their corresponding values using the PHP preg_replace function.
        if($user_id != null) {
            $subject = preg_replace($post['patterns'], $post['replacements'], $template->subject);
        } else {
            $subject = preg_replace($keys, $post, $template->subject);
        }

		$body = $template->message;
		if ($template != false) {
			$body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $template->Template);
		}

        if($user_id != null) {
            $body = preg_replace($post['patterns'], $post['replacements'], $body);
        } else {
            $body = preg_replace($keys, $post, $body);
        }

        if($fnum != null) {
            $body = $m_email->setTagsFabrik($body, array($fnum));
        }

		// Configure email sender
		$mailer = JFactory::getMailer();
		$mailer->setSender($sender);
		$mailer->addReplyTo($mail_from, $mail_from_name);
		$mailer->addRecipient($email_address);
		$mailer->setSubject($subject);
		$mailer->isHTML(true);
		$mailer->Encoding = 'base64';
		$mailer->setBody($body);

		if (!empty($toAttach)) {
			$mailer->addAttachment($toAttach);
		}

		// Send and log the email.
		$send = $mailer->Send();
		if ($send !== true) {
			if ($send === false) {
				JLog::add('Tried sending email with mailer disabled in site settings.', JLog::ERROR, 'com_emundus');
			} else {
				JLog::add($send->getMessage(), JLog::ERROR, 'com_emundus');
			}
			return false;
		} else {
			return true;
		}
	}


/////// chat functions
    /** send message in chat
     *
     */
    public function sendMessage() {

	    $user = JFactory::getSession()->get('emundusUser');
        $m_messages = new EmundusModelMessages();
        $jinput = JFactory::getApplication()->input;
        $message = $jinput->post->getRaw('message', null);
        $receiver = $jinput->post->get('receiver', null);
        $message = str_replace("&nbsp;", "", $message);
        $cifre_link = $jinput->post->get('cifre_link', null);

        // Get receiver info
	    $m_profile = new EmundusModelProfile();
	    $receiver_profile = $m_profile->getProfileByApplicant($receiver);
	    $user_id = JFactory::getUser($receiver)->id;
	    $email = JFactory::getUser($receiver)->email;

        // Send notification email to the receiver
	    $post = [
		    'USER_NAME' => strtoupper($receiver_profile["lastname"]). ' '.ucfirst($receiver_profile["firstname"]),
		    'SENDER' => strtoupper($user->lastname). ' '.ucfirst($user->firstname),
		    'MESSAGE' => $message
	    ];

	    if (!empty($cifre_link)) {

	    	// Find out if we should notify the receiver using the CIFRE notification system.
		    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'cifre.php');
		    $m_cifre = new EmundusModelCifre();
		    $notify = $m_cifre->checkNotify($user->id, $receiver);
		    if (!empty($notify)) {
			    $this->sendEmailNoFnum($email, 'notification_mail', $post, $user_id);
		    }


	    } else {
		    // check if the receiver is online
		    // IF he isn't connected we send them a notification email
		    $m_user = new EmundusModelUsers();
		    $online_users = $m_user->getOnlineUsers();
		    if (!in_array($receiver, $online_users)) {
			    $this->sendEmailNoFnum($email, 'notification_mail', $post, $user_id);
		    }
	    }


	    echo json_encode((object) ['status' => $m_messages->sendMessage($receiver, $message)]);
	    exit;
    }


	/** send message in chatroom
	 *
	 */
	public function sendChatroomMessage() {

		$m_messages = new EmundusModelMessages();
		$jinput = JFactory::getApplication()->input;
		$message = $jinput->post->getRaw('message', null);
		$chatroom = $jinput->post->getInt('chatroom', null);
		$message = str_replace("&nbsp;", "", $message);

		// Here we need to notify those that have a bell based on the link.
		if ($m_messages->sendChatroomMessage($chatroom, $message)) {

			require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'cifre.php');
			require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
			$m_cifre = new EmundusModelCifre();
			$m_profile = new EmundusModelProfile();

			$users = $m_messages->getChatroomUsersId($chatroom);
			$current_user = JFactory::getSession()->get('emundusUser');
			foreach ($users as $receiver) {

				if ($receiver === $current_user->id) {
					continue;
				}

				$receiver_profile = $m_profile->getProfileByApplicant($receiver);
				// Send notification email to the receiver
				$post = [
					'USER_NAME' => strtoupper($receiver_profile["lastname"]). ' '.ucfirst($receiver_profile["firstname"]),
					'SENDER' => strtoupper($current_user->lastname). ' '.ucfirst($current_user->firstname),
					'MESSAGE' => $message
				];

				// Find out if we should notify the receiver using the CIFRE notification system.
				$notify = $m_cifre->checkNotify($current_user->id, $receiver);
				if (!empty($notify)) {
					$this->sendEmailNoFnum(JFactory::getUser($receiver)->email, 'notification_mail', $post, JFactory::getUser($receiver)->id);
				}
			}

		}

		echo json_encode((object) ['status' => true]);
		exit;
	}

    /** update message list
     *
     */
    public function updatemessages() {

        $m_messages = new EmundusModelMessages();

        $jinput = JFactory::getApplication()->input;
        $lastId = $jinput->post->get('id', null);
        $other_user = $jinput->post->get('user', null);
        $chatroom = $jinput->post->getInt('chatroom', null);

        if (empty($other_user) && !empty($chatroom)) {
	        $messages = $m_messages->updateChatroomMessages($lastId, $chatroom);
        } else {
	        $messages = $m_messages->updateMessages($lastId, null, $other_user);
        }

        if (!empty($messages)) {
            foreach ($messages as $message) {
                $message->date_time = date("d/m/Y", strtotime($message->date_time));
            }
            echo json_encode((object)['status' => 'true', 'messages' => $messages]);
        } else {
            echo json_encode((object)['status' => 'false']);
        }

        exit;
    }


    public function getTypeAttachment($id) {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $query
            ->select('esa.*')
            ->from($db->quoteName('#__emundus_setup_attachments', 'esa'))
            ->where($db->quoteName('esa.id')  . ' = ' . $id);

        $db->setQuery($query);
        return $db->loadObjectList() ;
    }


    public function getTypeLetters($id) {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $query
            ->select('esl.*')
            ->from($db->quoteName('#__emundus_setup_letters', 'esl'))
            ->where($db->quoteName('esl.id')  . ' = ' . $id);

        $db->setQuery($query);
        return $db->loadObjectList() ;
    }

    /// get letter templates by fnums
    public function getlettertemplatesbyfnums() {
        // call to jinput to get form variable (fnums)
        $jinput = JFactory::getApplication()->input;

        $fnums = $jinput->post->getRaw('fnums', null);

        /// call to models/messages.php/getLetterTemplateByFnums
        $_mMessages = new EmundusModelMessages;
        $_templates = $_mMessages->getLetterTemplateByFnums($fnums);

        echo json_encode((object)['status' => true, 'templates' => $_templates]);
        exit;
    }

    // get recap info by fnum
    public function getrecapbyfnum() {
        $jinput = JFactory::getApplication()->input;

        $fnum = $jinput->post->getRaw('fnum', null);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        $_mFiles = new EmundusModelFiles;

        $_recap = $_mFiles->getFnumInfos($fnum);



        /// call to com_emundus_onbooard/settings
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus_onboard'.DS.'models'.DS.'settings.php');
        $_mSettings = new EmundusonboardModelsettings;

        echo json_encode((object)['status' => true, 'recap' => $_recap, 'color' => $_mSettings->getColorClasses()[$_recap['class']]]);
        exit;
    }

    // get message (subject, preview) + all attached documents by fnums
    public function getmessagerecapbyfnum() {
        $jinput = JFactory::getApplication()->input;

        $fnum = $jinput->post->getRaw('fnum', null);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        $_mEmails = new EmundusModelMessages;
        $_emails = $_mEmails->getMessageRecapByFnum($fnum);

        if($_emails) {
            echo json_encode((object)['status' => true, 'email_recap' => $_emails]);
        } else {
            echo json_encode((object)['status' => false, 'email_recap' => $_emails]);
        }
        exit;
    }

    /// send email to candidat with attached letters
    public function sendemailtocandidat() {
        $jinput = JFactory::getApplication()->input;

        $fnum = $jinput->post->getRaw('fnum', null);

        if (!EmundusHelperAccess::asAccessAction(9, 'c')) {
            die(JText::_("ACCESS_DENIED"));
        }

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'evaluation.php');

        $m_messages = new EmundusModelMessages();
        $m_emails = new EmundusModelEmails();
        $m_files = new EmundusModelFiles();
        $m_campaign = new EmundusModelCampaign();
        $_meval = new EmundusModelEvaluation;

        $user = JFactory::getUser();
        $config = JFactory::getConfig();

        // Get default mail sender info
        $mail_from_sys = $config->get('mailfrom');
        $mail_from_sys_name = $config->get('fromname');

        // If no mail sender info is provided, we use the system global config.
        $mail_from_name = $jinput->post->getString('mail_from_name', $mail_from_sys_name);
        $mail_from = $jinput->post->getString('mail_from', $mail_from_sys);

        /// end of default mail sender

        /// from fnum --> detect candidat email
        $fnum_info = $m_files->getFnumInfos($fnum);

        $candidat_email = $fnum_info['email'];

        /// get message recap by fnum --> reuse the function models/messages.php/getMessageRecapByFnum($fnum)
        $message = $m_messages->getMessageRecapByFnum($fnum);

        $email_recap = $message['message_recap'];                   /// length = 1
        $letter_recap = $message['attached_letter'];                /// length >= 1

        // get programme info
        $programme = $m_campaign->getProgrammeByTraining($fnum_info['training']);

        $toAttach = [];
        $post = [
            'FNUM' => $fnum_info['fnum'],
            'USER_NAME' => $fnum_info['name'],
            'COURSE_LABEL' => $programme->label,
            'CAMPAIGN_LABEL' => $fnum_info['label'],
            'CAMPAIGN_YEAR' => $fnum_info['year'],
            'CAMPAIGN_START' => $fnum_info['start_date'],
            'CAMPAIGN_END' => $fnum_info['end_date'],
            'SITE_URL' => JURI::base(),
            'USER_EMAIL' => $fnum_info['email'],
        ];

        $tags = $m_emails->setTags($fnum_info['applicant_id'], $post, $fnum_info['fnum']);
        $body = $m_emails->setTagsFabrik($email_recap[0]->message, [$fnum_info['fnum']]);
        $subject = $m_emails->setTagsFabrik($email_recap[0]->subject, [$fnum_info['fnum']]);

        // Tags are replaced with their corresponding values using the PHP preg_replace function.
        $subject = preg_replace($tags['patterns'], $tags['replacements'], $subject);
        $body = preg_replace($tags['patterns'], $tags['replacements'], $body);

        $mail_from = preg_replace($tags['patterns'], $tags['replacements'], $mail_from);
        $mail_from_name = preg_replace($tags['patterns'], $tags['replacements'], $mail_from_name);

        // If the email sender has the same domain as the system sender address.
        if (substr(strrchr($mail_from, "@"), 1) === substr(strrchr($mail_from_sys, "@"), 1)) {
            $mail_from_address = $mail_from;
        } else {
            $mail_from_address = $mail_from_sys;
        }

        // Set sender
        $sender = [
            $mail_from_address,
            $mail_from_name
        ];

        // Configure email sender
        $mailer = JFactory::getMailer();
        $mailer->setSender($sender);
        $mailer->addReplyTo($mail_from, $mail_from_name);
        $mailer->addRecipient($fnum_info['email']);
        $mailer->setSubject($subject);
        $mailer->isHTML(true);
        $mailer->Encoding = 'base64';
        $mailer->setBody($body);

        $attachments = $_meval->getLettersByFnums($fnum, true);

        $attachment_ids = array();
        foreach ($attachments as $key => $value) {
            $attachment_ids[] = $value['id'];
        }

        $attachment_ids = array_unique(array_filter($attachment_ids));

        /// get attachment letters by fnum
        $file_path = [];
        foreach($attachment_ids as $key => $value) {
            $attached_letters = $_meval->getFilesByAttachmentFnums($value, [$fnum]);
            $file_path[] = EMUNDUS_PATH_ABS . $attached_letters[0]->user_id . DS . $attached_letters[0]->filename;
        }

        $mailer->addAttachment($file_path);
        $send = $mailer->Send();

//        /// track the log of email
//        if ($send !== true) {
//            $failed[] = $fnum_info['email'];
//            echo 'Error sending email: ' . $send->__toString();
//            JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
//        } else {
//            $sent[] = $fnum_info['email'];
//            $log = [
//                'user_id_from' => $user->id,
//                'user_id_to' => $fnum_info['applicant_id'],
//                'subject' => $subject,
//                'message' => '<i>' . JText::_('MESSAGE') . ' ' . JText::_('SENT') . ' ' . JText::_('TO') . ' ' . $fnum_info['email'] . '</i><br>' . $body . $file_path,
//                'type' => $email_recap[0]->id,
//            ];
//            $m_emails->logEmail($log);
//            // Log the email in the eMundus logging system.
//            EmundusModelLogs::log($user->id, $fnum_info['applicant_id'], $fnum_info['fnum'], 9, 'c', 'COM_EMUNDUS_LOGS_SEND_EMAIL');
//        }
//        // Due to mailtrap now limiting emails sent to fast, we add a long sleep.
//        if ($config->get('smtphost') === 'smtp.mailtrap.io') {
//            sleep(5);
//        }

        echo json_encode(['status' => true]);
        exit;
    }

    /// set tags to fnum --> params :: fnum
    public function addtagsbyfnum() {
        $jinput = JFactory::getApplication()->input;

        $fnum = $jinput->post->getRaw('fnum');
        $tmpl = $jinput->post->getRaw('tmpl');

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'messages.php');
        $_mMessages = new EmundusModelMessages;

        $_tags = $_mMessages->addTagsByFnum($fnum,$tmpl);
        echo json_encode(['status'=>true]);
        exit;
    }

    // get all documents being letters
    public function getalldocumentsletters() {
        $_mMessages = $this->getModel('Messages');
        $_documents = $_mMessages->getAllDocumentsLetters();

        if($_documents) {
            echo json_encode(['status' => true, 'documents' => $_documents]);
        } else {
            echo json_encode(['status' => false, 'documents' => null]);
        }
        exit;
    }

    // get attachments by profiles
    public function getattachmentsbyprofiles() {
        $jinput = JFactory::getApplication()->input;

        $fnums = explode(',', $jinput->post->getRaw('fnums'));

        $_mMessages = $this->getModel('Messages');
        $_results = $_mMessages->getAttachmentsByProfiles($fnums);

        if($_results) {
            echo json_encode(['status' => true, 'attachments' => $_results]);
        } else {
            echo json_encode(['status' => false, 'attachments' => null]);
        }
        exit;
    }
}

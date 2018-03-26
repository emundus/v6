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
        parent::__construct($config);
    }

    /**
     * Upload a file from computer to be attached to the emails sent.
     *
     * @since 3.8.6
     */
    public function uploadfiletosend() {

        $jinput = JFactory::getApplication()->input;

        // Get the file sent via AJAX POST
        $file = $jinput->files->get('file');

        // Check if the message attachements directory exists.
        if (!is_dir('tmp'.DS.'messageattachements')) {
            mkdir('tmp'.DS.'messageattachements', 0777, true);
        }

        // Sanitize filename.
        $file['name'] = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $file['name']);
        $file['name'] = preg_replace("([\.]{2,})", '', $file['name']);

        // Move the uploaded file to the server directory.
        $target = 'tmp'.DS.'messageattachements'.DS.$file['name'];

        if (file_exists($target))
            unlink($target);

        move_uploaded_file($file['tmp_name'], $target);

        // Send back the info to the frontend.
        echo json_encode(['status' => true, 'file_name' => $file['name'], 'file_path' => $target]);
        exit;

    }

    /**
     * Send the email defined in the dialog.
     *
     * @since 3.8.6
     */
    public function applicantemail() {

        require_once (JPATH_COMPONENT.DS.'models'.DS.'messages.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');
        require_once (JPATH_COMPONENET.DS.'models'.DS.'emails.php');
        $m_messages = new EmundusModelMessages();
        $m_emails   = new EmundusModelEmails();
        $m_files    = new EmundusModelFiles();

        $user   = JFactory::getUser();
        $mailer = JFactory::getMailer();
        $config = JFactory::getConfig();
        $jinput = JFactory::getApplication()->input;

        // Get default mail sender info
        $mail_from_sys = $config->get('mailfrom');
        $mail_from_sys_name = $config->get('fromname');

        $fnums  = explode(',',$jinput->post->get('recipients', null, null));
        $bcc    = $jinput->post->getBool('Bcc', false);

        // If no mail sender info is provided, we use the system global config.
        $mail_from_name = $jinput->post->getString('mail_from_name', $mail_from_sys_name);
        $mail_form      = $jinput->post->getString('mail_from', $mail_from_sys);

        $mail_subject   = $jinput->post->getString('mail_subject', 'No Subject');
        $template_id    = $jinput->post->getInt('template', null);
        $message        = $jinput->post->get('message', null, null);
        $attachements   = $jinput->post->get('attachements', null, null);


        // Get additional info for the fnums such as the user email.
        $fnums = $m_files->getFnumsInfos($fnums, 'object');

        // Loading the message template is not used for getting the message text as that can be modified on the frontend by the user before sending.
        $template = $m_email->getEmailById($template_id);

        // If the email sender has the same domain as the system sender address.
        if (substr(strrchr($mail_from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1))
            $mail_from_address = $mail_from;
        else
            $mail_from_address = $mail_from_sys;

        // Set sender
        $sender = [
            $mail_from_address,
            $mail_from_name
        ];

        foreach ($fnums as $fnum) {

            $post = [
                'FNUM'      => $fnum->fnum,
                'USER_NAME' => $fnum->name,
            ];

            $tags = $m_emails->setTags($fnum->applicant_id, $post);
            $body = $m_emails->setTagsFabrik($body, array($user->fnum));

            // Tags are replaced with their corresponding values using the PHP preg_replace function.
            $subject = preg_replace($tags['patterns'], $tags['replacements'], $mail_subject);
            $body = preg_replace($tags['patterns'], $tags['replacements'], $message);
            $body = preg_replace(array("/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"), array($subject, $body), $template->Template);

            // Configure email sender
            $mailer->ClearAllRecipients();
            $mailer->setSender($sender);
            $mailer->addReplyTo($mail_from, $mail_from_name);
            $mailer->addRecipient($fnum->email);
            $mailer->setSubject($subject);
            $mailer->isHTML(true);
            $mailer->Encoding = 'base64';
            $mailer->setBody($body);

            foreach ($attachements as $attachement) {

                if (!empty($attachement['upload'])) {

                    // In the case of an uploaded file, just add it to the email.
                    foreach ($attachement['upload'] as $upload) {

                        // Sanitize file label.
                        $upload['label'] = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $upload['label']);
                        $upload['label'] = preg_replace("([\.]{2,})", '', $upload['label']);

                        // Sanitize filename.
                        $upload['value'] = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $upload['value']);
                        $upload['value'] = preg_replace("([\.]{2,})", '', $upload['value']);

                        $mailer->addAttachement($upload['value'], $upload['label']);
                    }

                }

                if (!empty($attachement['candidate_file'])) {

                    // Get from DB by fnum.
                    foreach ($attachement['candidate_file'] as $candidate_file) {

                        $filename = $m_messages->get_upload($fnum->fnum, $candidate_file['value']);

                        if ($filename != false) {

                            // Build the path to the file we are searching for on the disk.
                            $path = EMUNDUS_PATH_ABS.$fnum->applicant_id.DS.$filename;

                            if (file_exists($path)) {
                                $mailer->addAttachement($path, $filename);
                            }

                        }

                    }

                }


                if (!empty($attachement['setup_letters'])) {

                    // Get from DB and generate using the tags.
                    foreach ($attachement['setup_letters'] as $setup_letter) {

                        $letter = $m_messages->get_letter($setup_letter['value']);

                        if ($letter != false) {

                            

                        }

                    }

                }

            }

            // Send and log the email.
            $send = $mailer->Send();
            if ( $send !== true ) {
                echo 'Error sending email: ' . $send->__toString();
                JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
            } else {
                $message = array(
                    'user_id_from' => $from_id,
                    'user_id_to' => $recipient->id,
                    'subject' => $subject,
                    'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$recipient->email.'</i><br>'.$body
                );
                $m_emails->logEmail($message);
            }

        }

    }

}
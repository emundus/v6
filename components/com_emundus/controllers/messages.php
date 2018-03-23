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
        $m_messages = new EmundusModelMessages();
        $m_files    = new EmundusModelFiles();

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


        // Get additional info for the fnum such as the 
        $fnums = $m_files->getFnumsInfos($fnums, 'object');

        foreach ($fnums as $fnum) {

            foreach ($attachements as $attachement) {

                if (!empty($attachement['upload'])) {

                    // In the case of an uploaded file, just add it to the email.

                }

                if (!empty($attachement['candidate_file'])) {

                    // Get from DB by fnum.

                }


                if (!empty($attachement['setup_letters'])) {

                    // Get from DB and generate using the tags.

                }

            }

        }

    }

}
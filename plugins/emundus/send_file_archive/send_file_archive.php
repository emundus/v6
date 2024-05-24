<?php
/**
 * @package	eMundus
 * @version	6.6.5
 * @author	eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

class plgEmundusSend_file_archive extends JPlugin {

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		jimport('joomla.log.log');
		JLog::addLogger(array('text_file' => 'com_emundus.sendFileArchive.php'), JLog::ALL, array('com_emundus'));
	}

	/**
	 * When the file is deleted, we need to generate a zip archive and send it to the user who deleted it.
	 *
	 * @param $fnum
	 *
	 * @return bool
	 * @throws \PhpOffice\PhpWord\Exception\CopyFileException
	 * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
	 * @throws \PhpOffice\PhpWord\Exception\Exception
	 */
	function onBeforeDeleteFile($fnum) {

		$email = $this->params->get('delete_email');
		if (empty($email)) {
			return false;
		}

		return $this->sendEmailArchive($fnum, $email);
	}


	/**
	 * When a file changes to a certain status, we need to generate a zip archive and send it to the user.
	 *
	 * @param $fnum
	 *
	 * @param $state
	 *
	 * @return bool
	 * @throws \PhpOffice\PhpWord\Exception\CopyFileException
	 * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
	 * @throws \PhpOffice\PhpWord\Exception\Exception
	 */
	function onAfterStatusChange($fnum, $state) {

		$email = $this->params->get('status_email');
		if (empty($email)) {
			return false;
		}

		$event_status = $this->params->get('event_status');
		if (in_array($state, explode(',', $event_status))) {
			return $this->sendEmailArchive($fnum, $email);
		}
		return false;
	}

	/**
	 * @param $fnum
	 * @param $email
	 *
	 * @return bool
	 * @throws \PhpOffice\PhpWord\Exception\CopyFileException
	 * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
	 * @throws \PhpOffice\PhpWord\Exception\Exception
	 */
	private function sendEmailArchive($fnum, $email) {

		if (!extension_loaded('zip')) {
			JLog::add('Error: ZIP extension not loaded.', JLog::ERROR, 'com_emundus');
			return false;
		}

        require_once(JPATH_SITE.'/components/com_emundus/models/files.php');
        require_once(JPATH_SITE.'/components/com_emundus/models/emails.php');
        $m_files = new EmundusModelFiles();
        $m_emails = new EmundusModelEmails();

        $zip_attachments = $this->params->get('zip_attachments',1);
        $zip_evaluation = $this->params->get('zip_evaluation',0);
        $zip_decision = $this->params->get('zip_decision',0);

        if (!defined(EMUNDUS_PATH_ABS)) {
            define('EMUNDUS_PATH_ABS',     JPATH_ROOT.DIRECTORY_SEPARATOR.'images/emundus/files/');
        }

        $zip_name = $m_files->exportZip([$fnum], 1, $zip_attachments, $zip_evaluation, $zip_decision, 0, null, null, null, true);
        $file = JPATH_SITE.'/tmp/'.$zip_name;

        $m_emails->sendEmail($fnum, $email, null, $file, false, 2);
        return true;
	}

}

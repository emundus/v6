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

		$user = JFactory::getUser();

		if ($user->id != (int) substr($fnum, -7)) {
			return false;
		}

		if (!extension_loaded('zip')) {
			JLog::add('Error: ZIP extension not loaded.', JLog::ERROR, 'com_emundus');
			return false;
		}

		$email = $this->params->get('email');

		if (empty($email)) {
			JLog::add('Error: missing email lbl in plugin/emundus/send_file_archive.', JLog::ERROR, 'com_emundus');
			return false;
		}

		require_once(JPATH_COMPONENT.DS.'controllers'.DS.'files.php');
		require_once(JPATH_COMPONENT.DS.'controllers'.DS.'messages.php');
		$c_files = new EmundusControllerFiles();
		$c_messages = new EmundusControllerMessages();

		$zip_name = $c_files->export_zip([$fnum]);
		$file = JPATH_BASE.DS.'tmp'.DS.$zip_name;

		$c_messages->sendEmail($fnum, $email, null, $file);
		return true;

	}

}

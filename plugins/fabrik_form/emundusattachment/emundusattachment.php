<?php
/**
 * @version 2: emundusattachment
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Valide l'envoie d'un dossier de candidature et change le statut.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.juseremundus
 * @since       3.0
 */

class PlgFabrik_FormEmundusattachment extends plgFabrik_Form
{

	public function onBeforeStore()
	{
		require_once JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'events.php';
		$h_events = new EmundusHelperEvents();

		$formModel = $this->getModel();
		$h_events->updateCcidFormData($formModel);
	}

	public function onBeforeCalculations()
	{
		$mainframe 		= JFactory::getApplication();

		$db 			= JFactory::getDBO();
		$query = $db->getQuery(true);

		$eMConfig = JComponentHelper::getParams('com_emundus');
		$alert_new_attachment = $eMConfig->get('alert_new_attachment');

		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'checklist.php');
		require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
		$m_files = new EmundusModelFiles();
		$h_checklist = new EmundusHelperChecklist();

		$formModel = $this->getModel();

		$aid = $formModel->formData['attachment_id_raw'];
		$fnum = $formModel->formData['fnum'];

		if(is_array($aid)) {
			$aid = $aid[0];
		}

		$upload_id = $formModel->formData['id'];
		$can_be_view 	= $formModel->formData['can_be_viewed'];
		$inform_applicant_by_email 	= $formModel->formData['inform_applicant_by_email'];

		$query->select('id, user_id, filename')
			->from($db->quoteName('#__emundus_uploads'))
			->where($db->quoteName('id') . ' = ' . $db->quote($upload_id));
		$db->setQuery($query);
		$upload = $db->loadObject();
		$student = JFactory::getUser($upload->user_id);

		$query->clear()
			->select('profile')
			->from($db->quoteName('#__emundus_users'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($upload->user_id));
		$db->setQuery($query);
		$profile = $db->loadResult();

		$query->clear()
			->select('ap.displayed, attachment.lbl, attachment.value')
			->from($db->quoteName('#__emundus_setup_attachments', 'attachment'))
			->leftJoin($db->quoteName('#__emundus_setup_attachment_profiles','ap').' ON '.$db->quoteName('attachment.id').' = '.$db->quoteName('ap.attachment_id').' AND '.$db->quoteName('ap.profile_id').'='.$db->quote($profile))
			->where($db->quoteName('attachment.id') . ' = ' . $db->quote($aid));
		$db->setQuery($query);
		$attachment_params = $db->loadObject();

		$fnumInfos = $m_files->getFnumInfos($fnum);
		$name = $h_checklist->setAttachmentName($upload->filename, $attachment_params->lbl, $fnumInfos);

		if (!file_exists(EMUNDUS_PATH_ABS.$upload->user_id)) {
			mkdir(EMUNDUS_PATH_ABS.$upload->user_id, 0777, true);
		}

		if (!rename(JPATH_SITE.$upload->filename, EMUNDUS_PATH_ABS.$upload->user_id.DS.$name)) {
			die("ERROR_MOVING_UPLOAD_FILE");
		}

		$query->clear()
			->update($db->quoteName('#__emundus_uploads'))
			->set($db->quoteName('filename') . ' = ' . $db->quote($name))
			->where($db->quoteName('id') . ' = ' . $db->quote($upload->id));
		$db->setQuery($query);
		$db->execute();

		if ($attachment_params->lbl=="_photo")
		{
			$pathToThumbs = EMUNDUS_PATH_ABS.$student->id.DS.$name;
			$file_src = EMUNDUS_PATH_ABS.$student->id.DS.$name;
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
			imagejpeg( $tmp_img, EMUNDUS_PATH_ABS.$student->id.DS.'tn_'.$name);
			$student->avatar = $name;
		}

		$logsStd = new stdClass();
		$logsStd->element = '[' . $attachment_params->value . ']';
		$logsStd->details = str_replace("/tmp/", "", $_FILES['jos_emundus_uploads___filename']['name']);

		$logsParams = array('created' => [$logsStd]);

		EmundusModelLogs::log(JFactory::getUser()->id, $fnumInfos['applicant_id'], $fnum, 4, 'c', 'COM_EMUNDUS_ACCESS_ATTACHMENT_CREATE', json_encode($logsParams,JSON_UNESCAPED_UNICODE));

		if ($inform_applicant_by_email == 1) {
			try {
				require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'controllers' . DS . 'messages.php');
				$c_messages = new EmundusControllerMessages;

				$file_url = '';
				if ($can_be_view == 1) {
					$file_url = '<br/>'.JURI::base().EMUNDUS_PATH_REL.$upload->user_id.'/'.$name;
				}

				$post = array('FILE_URL' => @$file_url);
				$c_messages->sendEmail($fnum, 'attachment', $post);
			}
			catch (Exception $e) {
				echo $e->getMessage();
				JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			}
		}
	}
}

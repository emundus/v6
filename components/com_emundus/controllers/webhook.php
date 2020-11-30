<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @copyright  eMundus
 * @author     Benjamin Rivalland
 * @since      3.9.16
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * eMundus Component Controller
 *
 * @package    Joomla
 * @subpackage Components
 */

class EmundusControllerWebhook extends JControllerLegacy {

	private $m_files;

	public function __construct(array $config = array()) {
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
		$this->m_files = new EmundusModelFiles;

		// Attach logging system.
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.webhook.info.php'], JLog::INFO, array('com_emundus.webhook'));
		JLog::addLogger(['text_file' => 'com_emundus.webhook.error.php'], JLog::ERROR, array('com_emundus.webhook'));

		parent::__construct($config);
	}


	/**
	 * Downloads the file associated to the YouSign procedure that was pushed.
	 */
	public function yousign() {

		$app = JFactory::getApplication();
		$jinput = $app->input;
		$eventName = $jinput->post->getString('eventName');

		JLog::add('YouSign event : '.$eventName, JLog::INFO, 'com_emundus.webhook');

		// 'procedure.finished' runs when all signatures are done and blissful harmony is restored to the universe.
		if ($eventName === 'procedure.finished') {

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$eMConfig = JComponentHelper::getParams('com_emundus');

			$procedure = $jinput->post->get('procedure');

			JLog::add('YouSign procedure : '.print_r($eventName, true), JLog::INFO, 'com_emundus.webhook');

			// Now that the procedure is signed, we can remove the member ID used for loading the iFrame.
			foreach ($procedure->members as $member) {
				$this->setUserParam($member->email, 'yousignMemberId', false);
			}

			$files = [];
			foreach ($procedure->files as $file) {
				$files[] = $file->id;
				JLog::add('YouSign procedure file : '.$file->id, JLog::INFO, 'com_emundus.webhook');
			}

			// Set all of the file requests as uploaded.
			$query->update($db->quoteName('jos_emundus_files_request'))
				->set($db->quoteName('uploaded').' = 1')
				->where($db->quoteName('filename').' IN ("'.implode('","', $files).'")');
			$db->setQuery($query);
			try {
				$db->execute();
			} catch (Exception $e) {
				JLog::add('Could not load files_requests : '.$e->getMessage(), JLog::ERROR, 'com_emundus.webhook');
				return;
			}

			$query->clear()
				->select([$db->quoteName('fr.fnum'), $db->quoteName('a.lbl'), $db->quoteName('fr.attachment_id')])
				->from($db->quoteName('jos_emundus_files_request', 'fr'))
				->leftJoin($db->quoteName('jos_emundus_setup_attachments', 'a').' ON '.$db->quoteName('fr.attachment_id').' = '.$db->quoteName('id'))
				->where($db->quoteName('filename').' IN ("'.implode('","', $files).'")');
			$db->setQuery($query);
			try {
				$attachments = $db->loadObjectList();
			} catch (Exception $e) {
				JLog::add('Could not load files_requests : '.$e->getMessage(), JLog::ERROR, 'com_emundus.webhook');
				return;
			}

			$http = new JHttp();

			$host = $eMConfig->get('yousign_prod', 'https://staging-api.yousign.com');
			$api_key = $eMConfig->get('yousign_api_key', 'https://staging-api.yousign.com');
			if (empty($host) || empty($api_key)) {
				JLog::add('Missing YouSign info.', JLog::ERROR, 'com_emundus.webhook');
				return;
			}

			foreach ($files as $file) {

				// Time to download the files from the WebService.
				$response = $http->get($host.'/files/'.$file.'/download?alt=media', [
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer '.$api_key
				]);

				// File exists, time to write it to the right place.
				if ($response->code === 200) {

					// Prepare the query to save the files into the upload table.
					$query->clear()
						->insert($db->quoteName('jos_emundus_uploads'))
						->columns($db->quoteName(['user_id', 'fnum', 'campaign_id', 'attachment_id', 'filename', 'description', 'can_be_deleted', 'can_be_viewed']));

					$success = [];
					foreach ($attachments as $attachment) {

						// Save the signed file into the users folder.
						$fileName = $attachment->lbl.'_signed';
						$uid = (int)substr($attachment->fnum, -7);
						if (file_put_contents(EMUNDUS_PATH_ABS.$uid.DS.$fileName, $response->body) !== false) {

							$success[] = $attachment->fnum;
							$query->values(implode(',', [$uid, $attachment->fnum, (int)substr($attachment->fnum, 14, 7), $attachment->attachment_id, $attachment->lbl, 'YouSign signed document', '0', '0']));

						} else {
							JLog::add('Error downloading file from YouSign -> RESPONSE ('.$response->code.') '.$response->body, JLog::ERROR, 'com_emundus.webhook');
						}
					}

					// Link the files to the users' files.
					$db->setQuery($query);
					try {
						$db->execute();
						JLog::add('Saved YouSigned saved file "'.$file.'" to fnums : '.implode(', ', $success), JLog::INFO, 'com_emundus.webhook');
					} catch (Exception $e) {
						JLog::add('Error adding attachemnts to files: '.$e->getMessage(), JLog::ERROR, 'com_emundus.webhook');
					}
				}
			}
		}
	}



	/**
	 * Gets video info from addpipe webhook
	 *
	 * @return bool|string
	 * @throws Exception
	 */
	public function addpipe() {

		$db 		= JFactory::getDBO();
		$eMConfig 	= JComponentHelper::getParams('com_emundus');

		$ftp_path 	= $eMConfig->get('addpipe_path_ftp', null);
		$secret 	= JFactory::getConfig()->get('secret');
		$token 		= JFactory::getApplication()->input->get('token', '', 'ALNUM');

		if ($token != $secret) {
			JLog::add('Bad token sent.', JLog::ERROR, 'com_emundus.webhook');
			return false;
		}

		if (is_null($ftp_path)) {
			JLog::add('FTP path is null.', JLog::ERROR, 'com_emundus.webhook');
			return false;
		}

		try {
			$payload = $_POST["payload"];

			//the data is JSON encoded, so we must decode it in an associative array
			$webhookData = json_decode($payload, true);
			$webhookDataApplication = json_decode($webhookData["data"]["payload"], true);

			$vidName = $webhookData["data"]["videoName"].'.'.$webhookData["data"]["type"];

			//you can get the webhook type by accessing the event element in the array
			//$type = $webhookData["event"];

			if (empty($webhookDataApplication["userId"])) {
				$error = JUri::getInstance().' APPLICANT_ID is NULL';
				JLog::add($error, JLog::ERROR, 'com_emundus.webhook');

				return false;
			}

			//move video from ftp to applicant documents
			if (!file_exists(EMUNDUS_PATH_ABS.$webhookDataApplication["userId"])) {
				// An error would occur when the index.html file was missing, the 'Unable to create user file' error appeared yet the folder was created.
				if (!file_exists(EMUNDUS_PATH_ABS.'index.html')) {
					touch(EMUNDUS_PATH_ABS.'index.html');
				}

				if (!mkdir(EMUNDUS_PATH_ABS.$webhookDataApplication["userId"]) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$webhookDataApplication["userId"].DS.'index.html')){
					$error = JUri::getInstance().' :: USER ID : '.$webhookDataApplication["userId"].' -> Unable to create user file';
					JLog::add($error, JLog::ERROR, 'com_emundus.webhook');

					return false;
				}
			}
			chmod(EMUNDUS_PATH_ABS.$webhookDataApplication["userId"], 0755);

			if (!file_exists($ftp_path.DS.$vidName)) {
				$error = JUri::getInstance().' :: USER ID : '.$webhookDataApplication["userId"].' -> File not found: '.$ftp_path.DS.$vidName;
				JLog::add($error, JLog::ERROR, 'com_emundus.webhook');

				return false;
			}

			if (!copy($ftp_path.DS.$vidName, EMUNDUS_PATH_ABS.$webhookDataApplication["userId"].DS.$vidName)) {

                $error = JUri::getInstance().' :: USER ID : '.$webhookDataApplication["userId"].' -> Cannot move file: '.$ftp_path.DS.$vidName.' to '.EMUNDUS_PATH_ABS.$webhookDataApplication["userId"].DS.$vidName;
                JLog::add($error, JLog::ERROR, 'com_emundus.webhook');

                return false;
            }

			//add document to emundus_attachments table
			$fnumInfos = $this->m_files->getFnumInfos($webhookDataApplication["fnum"]);
			$description = $this->FileSizeConvert(filesize($ftp_path.DS.$vidName));

			$query = 'INSERT INTO jos_emundus_uploads (user_id, attachment_id, filename, description, can_be_deleted, can_be_viewed, campaign_id, fnum) 
						VALUES ('.$webhookDataApplication["userId"].', '.$webhookDataApplication["aid"].', '.$db->Quote($vidName).', '.$db->Quote($description).', 1, 1, '.$fnumInfos['id'].', '.$db->Quote($webhookDataApplication["fnum"]).')';

            try {
                $db->setQuery($query);
                $db->execute();
            } catch (Exception $e) {
                $error = JUri::getInstance().' :: USER ID : '.$webhookDataApplication["userId"].' -> '.$e->getMessage().' :: '.$query;
                JLog::add('Unable to insert uploaded document: '.$error, JLog::ERROR, 'com_emundus.webhook');
            }

		} catch (Exception $e) {
			JLog::add('Unable to handle addpipe webhook: '.$payload, JLog::ERROR, 'com_emundus.webhook');
			return false;
		}

		//log webhook
		JLog::add('Webhook START: '.$webhookDataApplication["aid"].' :: '.$webhookDataApplication["userId"].' :: '.$webhookDataApplication["fnum"].' :: '.$webhookDataApplication["jobId"].' :: '.$vidName.' :: '.$payload, JLog::WARNING, 'com_emundus.webhook');
		return true;
	}


	/**
	* Converts bytes into human readable file size.
	*
	* @param string $bytes
	* @return string human readable file size (2,87 Мб)
	* @author Mogilev Arseny
	*/
	function FileSizeConvert($bytes) {
	    $bytes = floatval($bytes);
	        $arBytes = array(
	            0 => array(
	                "UNIT" => "TB",
	                "VALUE" => pow(1024, 4)
	            ),
	            1 => array(
	                "UNIT" => "GB",
	                "VALUE" => pow(1024, 3)
	            ),
	            2 => array(
	                "UNIT" => "MB",
	                "VALUE" => pow(1024, 2)
	            ),
	            3 => array(
	                "UNIT" => "KB",
	                "VALUE" => 1024
	            ),
	            4 => array(
	                "UNIT" => "B",
	                "VALUE" => 1
	            ),
	        );

	    foreach ($arBytes as $arItem) {
	        if ($bytes >= $arItem["VALUE"]) {
	            $result = $bytes / $arItem["VALUE"];
	            $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
	            break;
	        }
	    }
	    return $result;
	}

	/**
	 * Check if video upladed by addpipe has been moved to applicant files.
	 * @return void
	 * @throws Exception
	 */
	public function is_file_uploaded() {

	   	$db 		= JFactory::getDBO();
	   	$user 		= JFactory::getSession()->get('emundusUser');

		//$secret 	= JFactory::getConfig()->get('secret');
		//$token 		= JFactory::getApplication()->input->get('token', '', 'ALNUM');
		//$fnum 		= JFactory::getApplication()->input->get('fnum', '', 'STRING'); 
		$aid = JFactory::getApplication()->input->get('aid', '', 'ALNUM');
		$applicant_id = JFactory::getApplication()->input->get('applicant_id', '', 'ALNUM');

		if ($user->id != $applicant_id) {
			JLog::add('Curent user and fnum does not match.', JLog::ERROR, 'com_emundus.webhook');
			echo json_encode((object)(array('status' => false)));
			exit();
		}

		//@TODO manage a specific filename. Will issue if we have more than one video by filetype
		$query = 'SELECT count(id) 
					FROM #__emundus_uploads 
					WHERE attachment_id='.$aid.' AND user_id='.$user->id.' AND fnum LIKE '.$db->Quote($user->fnum);
		try {
            $db->setQuery($query);
            $result = ($db->loadResult() > 0);

            echo json_encode((object)(array('status' => $result)));
            exit();
        } catch (Exception $e) {
            $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage().' :: '.$query;
            JLog::add($error, JLog::ERROR, 'com_emundus.webhook');
	
		    echo json_encode((object)(array('status' => false)));
			exit();
        }
	}


	/**
	 * @param string $user_email
	 * @param        $param
	 * @param string $value
	 *
	 * @return bool
	 * @since version
	 */
	private function setUserParam(string $user_email, $param, string $value) : bool {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('id'))
			->from($db->quoteName('jos_users'))
			->where($db->quoteName('email').' LIKE '.$db->quote($user_email));
		$db->setQuery($query);

		try {
			$user_id = $db->loadResult();
		} catch (Exception $e) {
			JLog::add('Error getting user by email when saving param : '.$e->getMessage(), JLog::ERROR, 'com_emundus.yousign');
			return false;
		}

		if (empty($user_id)) {
			JLog::add('User not found', JLog::ERROR, 'com_emundus.yousign');
			return false;
		}

		$user = JFactory::getUser($user_id);

		$table = JTable::getInstance('user', 'JTable');
		$table->load($user->id);

		// Store token in User's Parameters
		$user->setParam($param, $value);

		// Get the raw User Parameters
		$params = $user->getParameters();

		// Set the user table instance to include the new token.
		$table->params = $params->toString();

		// Save user data
		if (!$table->store()) {
			JLog::add('Error saving params : '.$table->getError(), JLog::ERROR, 'com_emundus.yousign');
			return false;
		}
		return true;
	}
}
<?php
/**
 * @version 2: emundusconfirmpost 2018-09-06 Hugo Moracchini
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
class PlgFabrik_FormEmundusyousign extends plgFabrik_Form {

	/**
	 * Status field
	 *
	 * @var  string
	 */
	protected $URLfield = '';
	protected $signer_type = '';

	public function __construct(&$subject, $config = array()) {
		parent::__construct($subject, $config);
	}

	/**
	 * Get an element name
	 *
	 * @param string $pname Params property name to look up
	 * @param bool   $short Short (true) or full (false) element name, default false/full
	 *
	 * @return    string    element full name
	 */
	public function getFieldName($pname, $short = false) {
		$params = $this->getParams();

		if ($params->get($pname) == '') {
			return '';
		}

		$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));

		return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
	}

	/**
	 * Get the fields value regardless of whether its in joined data or no
	 *
	 * @param string $pname   Params property name to get the value for
	 * @param mixed  $default Default value
	 *
	 * @return  mixed  value
	 */
	public function getParam(string $pname, $default = '') {
		$params = $this->getParams();

		if ($params->get($pname) == '') {
			return $default;
		}

		return $params->get($pname);
	}


	/**
	 * @param array $signer_value
	 *
	 * @return array
	 * @throws Exception
	 */
	private function proccessSignerValues(array $signer_value) : array {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$jinput = JFactory::getApplication()->input;

		// Run ___ table/column analysis.
		$s_queries = [];
		foreach ($signer_value as $key => $value) {
			$value = trim($value);
			if (strpos($value, '___') !== false) {
				unset($signer_value[$key]);
				$tmp_split = explode('___', $value);
				// Build an array of [table => column] assocs for the different signer names.
				if ((isset($s_queries[$tmp_split[0]]) && !in_array($tmp_split[1], $s_queries[$tmp_split[0]])) || !isset($s_queries[$tmp_split[0]])) {
					$s_queries[$tmp_split[0]][] = $tmp_split[1];
				}
			}
		}

		if (!empty($s_queries)) {
			foreach ($s_queries as $table => $columns) {
				$query->clear()
					->select($db->quoteName($columns))
					->from($db->quoteName($table));

				if ($this->signer_type === 'student') {
					$query->where($db->quoteName('fnum').' = '.$db->quote(JFactory::getSession()->get('emundusUser')->fnum));
				} else {
					$query->where($db->quoteName('user_id').' = '.JFactory::getUser()->id);
				}

				$db->setQuery($query);

				try {
					$signer_value = array_merge($signer_value, $db->loadRow());
				} catch (Exception $e) {

					// This backup solution gets the value in the INPUT, in case all else fails.
					if (count($columns) === 1 && !empty($jinput->getRaw($table.'___'.$columns[0]))) {
						$signer_value[] = $jinput->getRaw($table.'___'.$columns[0]);
					} else {
						return [];
					}
				}
			}
		}

		return $signer_value;
	}

	/**
	 * Main script.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function onAfterProcess() : void {

		// Attach logging system.
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.yousign.php'], JLog::ALL, array('com_emundus.yousign'));

		include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');

		$this->signer_type = $this->getParam('signer_type', 'student');

		$eMConfig = JComponentHelper::getParams('com_emundus');
		$application = JFactory::getApplication();
		$jinput = $application->input;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Warning: For expert invitations, which may have multiple fnums, this does not support making and signing a doc for each file.
		// Only batch signing of a single doc generated based on a single file will work.
		// However, in the case of expert invitations, multiple files_requests will be generated and updated for the signature requests.
		$fnum = $jinput->get->get('rowid');

		$key_id = $jinput->get('jos_emundus_files_request___keyid');
		if ($this->signer_type === 'other_user' && !empty($key_id)) {

			// Files_requests uses fnum field but in the case of expert invitations there can be multiple fnums.
			// This means we hace to use the expert invite procedure for getting the array of fnums by keyid and picked files.
			$files_picked = $jinput->get('jos_emundus_files_request___your_files');
			$query->clear()
				->select($db->quoteName('fnum'))
				->from($db->quoteName('#__emundus_files_request'))
				->where($db->quoteName('keyid').' LIKE '.$db->quote($key_id));
			$db->setQuery($query);
			$fnums = $db->loadColumn();

			// This means that if the re is nothing in the your_files element we will get all fnums for the keyid.
			if (!empty($files_picked)) {
				// Only get fnums that are found in BOTH arrays, this both allows filtering (only accept files which were picked by the user) and prevents the user from cheating and entering someone else's fnum.
				$fnums = array_intersect($fnums, $files_picked);
			}

			// Signular fnum is used for getting meta information such as campaign ID and such.
			$fnum = $fnums[0];


		} else {
			$fnums = [$fnum];
		}

		$signer_names = explode(',', $this->getParam('signer_name'));
		$signer_emails = explode(',', $this->getParam('signer_email'));
		$signer_tels = explode(',', $this->getParam('signer_tel'));

		if (empty($signer_names) || empty($signer_emails) || empty($signer_tels)) {
			throw new Exception('Missing signer information.');
		}

		$signers = [
			'names' => $this->proccessSignerValues($signer_names),
			'emails' => $this->proccessSignerValues($signer_emails),
			'tels' => $this->proccessSignerValues($signer_tels)
		];

		// Only allow embed if we have a single signer (because I have no clue how we would be able to get an iFrame to
		$attachment_id = $this->getParam('attachment_id');
		if (empty($attachment_id)) {
			throw new Exception('Missing attachment ID.');
		}

		$host = $eMConfig->get('yousign_prod', 'https://staging-api.yousign.com');
		$api_key = $eMConfig->get('yousign_api_key', 'https://staging-api.yousign.com');
		if (empty($host) || empty($api_key)) {
			throw new Exception('Missing YouSign info.');
		}

		$attachment_type = $this->getParam('attachment_type', 'application_form');
		if ($attachment_type === 'application_form') {

			include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
			$m_profile = new EmundusModelProfile();

			$student = JFactory::getUser((int)substr($fnum, -7));

			$query->clear()
				->select($db->quoteName('lbl'))
				->from($db->quoteName('#__emundus_setup_attachments'))
				->where($db->quoteName('id').' = '.$attachment_id);
			$db->setQuery($query);
			try {
				$attachment_label = $db->loadResult();
			} catch (Exception $e) {
				JLog::add('Error getting attachment label in plugin/yousign at query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus.yousign');
				return;
			}

			// This bit of code gets some custom pdf code based on the programme.
			if (empty($this->getParam('custom_attachment', ''))) {
				$file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_'.@$fnum['training'].'.php';
			} else {
				$file = JPATH_LIBRARIES.DS.'emundus'.DS.$this->getParam('custom_application_form', '');
			}

			if (!file_exists($file)) {
				$file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php';
			}

			// Generate the PDF which will need to be signed.
			if (!file_exists(EMUNDUS_PATH_ABS.$student->id)) {
				mkdir(EMUNDUS_PATH_ABS.$student->id);
				chmod(EMUNDUS_PATH_ABS.$student->id, 0755);
			}

			$profile_id = $m_profile->getProfileByFnum($fnum);

			require_once($file);
			application_form_pdf($student->id, $fnum, false, 1, null, null, null, $profile_id, $attachment_label);

			$fileName = $fnum.$attachment_label.'.pdf';
			$fileNamePath = EMUNDUS_PATH_ABS.$student->id.DS.$fileName;
			$base64FileContent = base64_encode(file_get_contents($fileNamePath));

		} else {

			// Handle the case of a letters doc
			include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'messages.php');
			include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
			$m_messages = new EmundusModelMessages();
			$m_files = new EmundusModelFiles();

			$letter_id = $this->getParam('letter_id');
			if (empty($letter_id)) {
				throw new Exception('Missing letter ID.');
			}

			$letter = $m_messages->get_letter($letter_id);
			$fnumInfos = $m_files->getFnumsInfos([$fnum])[$fnum];

			// We only get the letters if they are for that particular programme.
			if ($letter && in_array($fnumInfos['training'], explode('","', $letter->training))) {

				// Some letters are only for files of a certain status, this is where we check for that.
				if ($letter->status != null && !in_array($fnumInfos['step'], explode(',', $letter->status))) {
					throw new Exception('No letter configured.');
				}

				$fileName = $fnum.'-'.uniqid().'.pdf';

				// A different file is to be generated depending on the template type.
				switch ($letter->template_type) {

					case '1':
						// This is a static file, we just need to find its path add it as an attachment.
						if (file_exists(JPATH_BASE.$letter->file)) {
							$base64FileContent = base64_encode(file_get_contents(JPATH_BASE.$letter->file));
						}
						break;

					case '2':

						// This is a PDF to be generated from HTML.
						require_once (JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php');
						$path = generateLetterFromHtml($letter, $fnum, $fnumInfos['applicant_id'], $fnumInfos['training']);

						if ($path && file_exists($path)) {
							$base64FileContent = base64_encode(file_get_contents($path));
						}
						break;

					case '3':
						// This is a DOC template to be completed with applicant information.
						$path = $m_messages->generateLetterDoc($letter, $fnum);

						if ($path && file_exists($path)) {
							$base64FileContent = base64_encode(file_get_contents($path));
						}
						break;

					default:
						throw new Exception('Error getting letter type.');

				}
			}
		}

		// And now begins the YouSign API Magic.
		$http = new JHttp();

		// Step 1. Send file to API.
		$file = new stdClass();
		$file->name = $fileName;
		// API Docs specify not to send the application/pdf part of the base64.
		$file->content = str_replace('data:application/pdf;base64', '', $base64FileContent);
		$response = $http->post($host.'/files', json_encode($file),
		[
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$api_key
		]);

		// Code 201 = CREATED
		if ($response->code === 201) {
			$response->body = json_decode($response->body);
			$file_id = $response->body->id;
			JLog::add('File uploaded to YouSign -> ID: '.$response->body->id, JLog::INFO, 'com_emundus.yousign');
		} else {
			JLog::add('ERROR : ('.$response->code.') '.json_decode($response->body)->detail, JLog::ERROR, 'com_emundus.yousign');
			throw new Exception('ERROR '.$response->code.' FROM YOUSIGN.');
		}

		// Step 2: Create procedure.
		$procedure = new stdClass();
		$procedure->name = $fileName;
		$procedure->description = 'Created by eMundus.';

		// Set the webhook up to
		$webhook = new stdClass();
		$webhook->url = JUri::base().'index.php?option=com_emundus&controller=webhook&task=yousign&format=raw&token='.JFactory::getConfig()->get('secret');
		$webhook->method = 'POST';

		$procedure->config->webhook->{'procedure.finished'}[] = $webhook;

		$members = [];
		foreach ($signers['names'] as $key => $name) {
			$name = preg_split('/\s+/', $name);
			$member = new stdClass();
			$member->firstname = $name[0];
			$member->lastname = $name[1];
			$member->email = $signers['emails'][$key];
			$member->phone = $signers['tels'][$key];

			$fileObject = new stdClass();
			$fileObject->file = $file_id;
			$fileObject->page = (int)$this->getParam('signature_page', 1);
			$fileObject->position = $this->getParam('signature_position', '230,499,464,589');
			$fileObject->mention = ''; // TODO: Handle mention ?

			$member->fileObjects = [$fileObject];
			$members[] = $member;
		}

		$procedure->members = $members;

		$response = $http->post($host.'/procedures', json_encode($procedure),
		[
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$api_key
		]);

		// Code 201 = CREATED
		if ($response->code === 201) {
			$response->body = json_decode($response->body);
			$procedure_id = $response->body->id;
			JLog::add('YouSign procedure created -> ID: '.$response->body->id, JLog::INFO, 'com_emundus.yousign');

			foreach ($response->body->members as $webserviceResponseMember) {
				foreach ($signers['emails'] as $key => $email) {
					if ($email === $webserviceResponseMember->email) {
						$signers['yousign'][$key] = $webserviceResponseMember->id;
						continue 2;
					}
				}
			}

			// Save procedure to file_requests, used for keeping track of requested/signed procedures.
			// We are going to save the YouSign procedure ID as the keyid and the YouSign file ID as the filename.
			$query->clear()
				->insert($db->quoteName('#__emundus_files_request'))
				->columns($db->quoteName(['time_date', 'student_id', 'fnum', 'keyid', 'attachment_id', 'filename', 'campaign_id', 'signer_id']));

			$now = JFactory::getDate();
			foreach ($fnums as $fnum) {
				$query->values(implode(',', [
					$db->quote($now->toSql()), // time_date
					(int)substr($fnum, -7), // student_id
					$db->quote($fnum), // fnums
					$db->quote($procedure_id), // keyid
					$attachment_id, // attachement_id
					$db->quote($file_id), // filename
					(int)substr($fnum, 14, 7), // Campagin id
					JFactory::getUser()->id
				]));
			}
			$db->setQuery($query);

			try {
				$db->execute();
			} catch (Exception $e) {
				JLog::add('Error writing file request in plugin/yousign at query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus.yousign');
				return;
			}

			// Now that we have a procedure response, it's time we give the user access to the iFrame OR send him the email.
			$user_is_signer = false;
			foreach ($signers['emails'] as $key => $email) {

				if (JFactory::getUser()->email === $email) {
					$user_is_signer = true;
				}

				// Add user param containing the member ID.
				if ($this->setUserParam($email, 'yousignMemberId', $signers['yousign'][$key])) {
					JLog::add('Added YouSign member ID to user params: '.$response->body->id, JLog::INFO, 'com_emundus.yousign');
				} elseif ($this->getParam('method') === 'embed') {

					// Problem: Here we're in a case where we DID NOT add the memeber ID to the user, yet the settings are set to embed.
					// Solution: Add it to the session, continue adding the other user's params, and then redirect to the iFrame for that user.
					// This means if we have other users meant to sign, they will still get their user param added :)
					// This session is used to prevent someone sniping a YouSign member ID from some URL and using it to look at a potentially super sensitive document.
					$session = JFactory::getSession();
					$session->set('youSignTmp', $signers['yousign'][$key]);

					/* But Hugo, you ask, as you realize that this creates a case where technically we can have two signers, one is the current user and
					 the other is simply an incoreectly entered email or a link that will be manually sent by the ccordinator or something (this system is meant to be super flexible).
					 This means that we could accidentally redirect to the iFrame of the OTHER user and not the currently logged in one ? */

					// Nope, this is fine, because the iFrame page will lookup the currently logged in user's info in preference to looking at the session :).

				}

				// No need to handle email case, this part handles itself :).
				// TODO: Add email procedure handling when making YouSign procedure; this was cut out due to shortened delays.

			}

			if ($this->getParam('method') === 'embed' && $user_is_signer) {
				$application->redirect($this->getParam('embed_url', 'index.php?option=com_emundus&view=yousign'));
			}

		} else {
			JLog::add('Error from API: ('.$response->code.')  '.json_decode($response->body)->detail, JLog::ERROR, 'com_emundus.yousign');

			// In the case of a YouSign error, unassign the file.
			$query->clear()
				->delete($db->quoteName('#__emundus_users_assoc'))
				->where($db->quoteName('user_id').' = '.JFactory::getUser()->id)
				->andWhere($db->quoteName('fnum').' IN ("'.implode('","', $fnums).'")');
			$db->setQuery($query);

			try {
				$db->execute();
			} catch (Exception $e) {
				JLog::add('Error removing assoc users : '.$e->getMessage(), JLog::ERROR, 'com_emundus.yousign');
			}
			throw new Exception(JText::_('ERROR_WITH_YOUSIGN'));
		}
	}

	/**
	 * Raise an error - depends on whether you are in admin or not as to what to do
	 *
	 * @param array   &$err   Form models error array
	 * @param string   $field Name
	 * @param string   $msg   Message
	 *
	 * @return  void
	 * @throws Exception
	 */
	protected function raiseError(array &$err, string $field, string $msg) : void {
		$app = JFactory::getApplication();

		if ($app->isAdmin()) {
			$app->enqueueMessage($msg, 'notice');
		} else {
			$err[$field][0][] = $msg;
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

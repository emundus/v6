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
		$this->signer_type = $this->getParam('signer_type', 'student');
	}

	/**
	 * Get an element name
	 *
	 * @param   string  $pname  Params property name to look up
	 * @param   bool    $short  Short (true) or full (false) element name, default false/full
	 *
	 * @return	string	element full name
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
	 * @param   string $pname   Params property name to get the value for
	 * @param   mixed  $default Default value
	 *
	 * @return  mixed  value
	 */
	public function getParam($pname, $default = '') {
		$params = $this->getParams();

		if ($params->get($pname) == '') {
			return $default;
		}

		return $params->get($pname);
	}


	/**
	 * @param $signer_value
	 */
	private function proccessSignerValues($signer_value) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

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
					$query->where($db->quoteName('user_id').' = '.$db->quote(JFactory::getUser()->id));
				}

				$db->setQuery($query);

				try {
					$signer_value = array_merge($signer_value, $db->loadRow());
				} catch (Exception $e) {
					return false;
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
	public function onAfterProcess() {
		
		// Attach logging system.
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.yousign.php'], JLog::ALL, array('com_emundus.yousign'));

		include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');

		$application = JFactory::getApplication();
		$jinput = $application->input;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$fnum = $jinput->get->get('rowid');

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

		$host = $this->getParam('host');
		$api_key = $this->getParam('api_key');
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
				JLog::add('Error getting attachment label in plugin/docusign at query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
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

			$letter = $m_messages->get_letter($attachment_id);
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
		// TODO: POST file. WITHOUT base64 header
		$file = new stdClass();
		$file->name = $fileName;
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
			// TODO: Log success.
		} else {
			throw new Exception('ERROR '.$response->code.' FROM YOUSIGN.');
		}

		// Step 2: Create procedure.


		$procedure = new stdClass();
		$procedure->name = ''; // TODO: Add param.

		$members = [];
		foreach ($signers['names'] as $key => $name) {
			$name = explode(' ', $name); // TODO: Handle first/lastnames separately ?
			$member = new stdClass();
			$member->firstname = $name[0];
			$member->lastname = $name[1];
			$member->email = $signers['emails'][$key];
			$member->phone = $signers['tels'][$key];

			$fileObject = new stdClass();
			$fileObject->file = $file_id;
			$fileObject->page = 1; //TODO: Page param ?
			$fileObject->position = '230,499,464,589'; // TODO: Handle position.
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
		
		echo '<pre>'; var_dump($response); echo '</pre>'; die;

		// Code 201 = CREATED
		if ($response->code === 201) {
			$response->body = json_decode($response->body);
			$procedure_id = $response->body->id;
			// TODO: Log success.
		} else {
			throw new Exception('ERROR '.$response->code.' FROM YOUSIGN.');
		}

		// Next, create the top level envelope definition and populate it.
		$envelopeDefinition = new DocuSign\eSign\Model\EnvelopeDefinition([
			'email_subject' => $this->getParam('email_subject', 'eMundus - Please sign this document'),
			'documents' => [$document],
			'recipients' => new DocuSign\eSign\Model\Recipients(['signers' => $signers]),
			'status' => "sent"
		]);

		// Step 2. Create/send the envelope.
		$config = new DocuSign\eSign\Configuration();
		$config->setHost($host);
		$config->addDefaultHeader("X-DocuSign-Authentication", "{\"Username\":\"".$this->getParam('username')."\",\"Password\":\"".$this->getParam('password')."\",\"IntegratorKey\":\"".$this->getParam('integrator_key')."\"}");
		$apiClient = new DocuSign\eSign\ApiClient($config);

		//*** STEP 1 - Login API: get first Account ID and baseURL
		$authenticationApi = new DocuSign\eSign\Api\AuthenticationApi($apiClient);
		$options = new \DocuSign\eSign\Api\AuthenticationApi\LoginOptions();
		try {
			$loginInformation = $authenticationApi->login($options);
		} catch (DocuSign\eSign\ApiException $e) {
			JLog::add('Error logging in to API -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			try {
				JFactory::getApplication()->enqueueMessage(JText::_('PLG_FABRIK_FORM_EMUNDUSDOCUSIGN_SEND_ERROR'));
			} catch (Exception $e) {
				JLog::add('Error initiating JApplication in plugin/docusign -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			}
			return;
		}

		if (!empty($loginInformation)) {
			$loginAccount = $loginInformation->getLoginAccounts()[0];
			$host = $loginAccount->getBaseUrl();
			$host = explode("/v2",$host);
			$host = $host[0];

			// UPDATE configuration object
			$config->setHost($host);

			// instantiate a NEW docusign api client (that has the correct baseUrl/host)
			$apiClient = new DocuSign\eSign\ApiClient($config);

			$account_id = $loginAccount->getAccountId();
			if (!empty($account_id)) {
				$envelopeApi = new DocuSign\eSign\Api\EnvelopesApi($apiClient);
				try {
					$results = $envelopeApi->createEnvelope($account_id, $envelopeDefinition);
				} catch (DocuSign\eSign\ApiException $e) {
					JLog::add('Error creating DocuSign envelope -> '.$e->getResponseBody()->message, JLog::ERROR, 'com_emundus');
					try {
						JFactory::getApplication()->enqueueMessage(JText::_('PLG_FABRIK_FORM_EMUNDUSDOCUSIGN_SEND_ERROR'));
					} catch (Exception $e) {
						JLog::add('Error initiating JApplication in plugin/docusign -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
					}
					return;
				}

				// Docusign envelope has been sent, now adding it to the eMundus data model.
				$query->clear()
					->insert($db->quoteName('#__emundus_uploads'))
					->columns($db->quoteName(['user_id', 'fnum', 'campaign_id', 'attachment_id', 'filename']))
					->values('62, '.$db->quote($student->fnum).', '.$fnum['id'].', '.$attachment_id.', '.$db->quote($fileName));
				$db->setQuery($query);
				try {
					$db->execute();
				} catch (Exception $e) {
					JLog::add('Error adding upload to table in plugin/docusign at query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
				}

				$query->clear()
					->insert($db->quoteName('#__emundus_files_request'))
					->columns($db->quoteName(['time_date', 'student_id', 'fnum', 'keyid', 'attachment_id', 'filename', 'campaign_id', 'email', 'email_other']))
					->values($db->quote(date('Y-m-d H:i:s', strtotime($results->getStatusDateTime()))).', '.$student->id.', '.$db->quote($student->fnum).', '.$db->quote($results->getEnvelopeId()).', '.$attachment_id.', '.$db->quote($fileName).', '.$fnum['id'].', '.$db->quote($signer_email_1).', '.$db->quote($signer_email_2));
				$db->setQuery($query);
				try {
					$db->execute();
				} catch (Exception $e) {
					JLog::add('Error adding file_request to table in plugin/docusign at query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
				}
			}
		}
	}

	/**
	 * Raise an error - depends on whether you are in admin or not as to what to do
	 *
	 * @param   array   &$err   Form models error array
	 * @param   string   $field Name
	 * @param   string   $msg   Message
	 *
	 * @return  void
	 * @throws Exception
	 */
	protected function raiseError(&$err, $field, $msg) {
		$app = JFactory::getApplication();

		if ($app->isAdmin()) {
			$app->enqueueMessage($msg, 'notice');
		} else {
			$err[$field][0][] = $msg;
		}
	}
}

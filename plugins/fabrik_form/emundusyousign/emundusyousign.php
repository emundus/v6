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

use GuzzleHttp\Client as GuzzleClient;

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
				->select('id')
				->from('#__emundus_files_request')
				->where('keyid = '.$db->quote($key_id));
			$db->setQuery($query);
			$files_request_ids = $db->loadColumn();

			$query->clear()
				->select('DISTINCT ' . $db->quoteName('efr.fnum'))
				->from($db->quoteName('#__emundus_files_request', 'efr'))
				->leftJoin($db->quoteName('#__emundus_files_request_1614_repeat', 'efr1614') . ' ON ' . $db->quoteName('efr1614.fnum_expertise').' = '.$db->quoteName('efr.fnum'))
				->where($db->quoteName('efr.keyid').' LIKE '.$db->quote($key_id))
				->andWhere('efr1614.parent_id IN ('.implode(',', $files_request_ids).')')
				->andWhere('efr1614.status_expertise = 1');
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

        $fileNamePath = '';
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

		}
        else {

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
                        $fileNamePath = JPATH_BASE.$letter->file;
						break;
					case '2':
						// This is a PDF to be generated from HTML.
						require_once (JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php');
                        $fileNamePath = generateLetterFromHtml($letter, $fnum, $fnumInfos['applicant_id'], $fnumInfos['training']);
						break;
					case '3':
						// This is a DOC template to be completed with applicant information.
                        $fileNamePath = $m_messages->generateLetterDoc($letter, $fnum);
                        break;
					default:
						throw new Exception('Error getting letter type.');
				}
			}
		}

        // Step 1. Send file to API.
        $file = new stdClass();
        $file->name = $fileName;

		// And now begins the YouSign API Magic.
        try {
            $method = $this->getParam('method');

            $client = new GuzzleClient();
            $response = $client->request('POST', $host . '/signature_requests', ['body' => json_encode([
                'name' => $file->name,
                'delivery_mode' => $method == 'embed' ? 'none' : 'email',
            ]), 'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$api_key,
                'accept' => 'application/json'
            ]]);

            if ($response->getStatusCode() == 201) {
                $signatureRequest = json_decode($response->getBody());

                if (!empty($signatureRequest->id)) {
                    $signatureRequestId = $signatureRequest->id;
	                $default_fnum_infos = $m_files->getFnumsInfos([$fnum])[$fnum];

					foreach($fnums as $current_fnum) {
						if ($default_fnum_infos['fnum'] === $current_fnum) {
							$file_path_by_fnum = $fileNamePath;
						} else {
							$current_fnum_infos = $m_files->getFnumsInfos([$current_fnum])[$current_fnum];
							$file_path_by_fnum = str_replace('/' . $default_fnum_infos['applicant_id'] . '/', '/' . $current_fnum_infos['applicant_id'] . '/', $fileNamePath);

							$copied = copy($fileNamePath, $file_path_by_fnum);

							if (!$copied) {
								JLog::add('failed to copy file from ' . $fileNamePath . ' to ' . $file_path_by_fnum, JLog::ERROR, 'com_emundus.yousign');
							}
						}

						$query->clear()
							->update('#__emundus_files_request')
							->set('keyid = ' . $db->quote($signatureRequest->id))
							->set('filename = ' . $db->quote($file_path_by_fnum))
							->where('keyid = ' . $db->quote($key_id))
							->where('fnum = ' . $db->quote($current_fnum));
						$db->setQuery($query);
						$db->execute();
					}

                    $file_pointer = fopen($fileNamePath, 'r');
                    if ($file_pointer) {
                        $document_response = $client->request('POST', $host.'/signature_requests/' . $signatureRequestId . '/documents',
                            [
                                'multipart' => [
                                    [
                                        'name' => 'nature',
                                        'contents' => 'signable_document'
                                    ],
                                    [
                                        'name' => 'file',
                                        'filename' => $file->name,
                                        'contents' => $file_pointer,
                                        'headers' => ['Content-Type' => 'application/pdf']
                                    ]
                                ],
                                'headers' => ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$api_key]
                            ]);
                        fclose($file_pointer);

                        if ($document_response->getStatusCode() == 201) {
                            $document_body = json_decode($document_response->getBody());

                            $params = [
                                'signature_level' => 'electronic_signature',
                                'signature_authentication_mode'=> 'otp_email',
                                'info' => [],
                                'fields' => [
                                    [
                                        'document_id' => $document_body->id,
                                        'type' => 'signature',
                                        'page' => (int)$this->getParam('signature_page', 1),
                                        'x' => 249,
                                        'y' => 540
                                    ]
                                ]
                            ];

                            JLog::add('File uploaded to YouSign -> ID: '.$document_body->id, JLog::INFO, 'com_emundus.yousign');

                            foreach ($signers['names'] as $key => $name) {
                                $name = preg_split('/\s+/', $name);
                                $params['info'] = [
                                    'first_name' => $name[0],
                                    'last_name' => $name[1],
                                    'email' => $signers['emails'][$key],
                                    'locale' => 'fr'
                                ];

                                if (!empty($signers['phone'][$key])) {
                                    $params['info']['phone_number'] = $signers['phone'][$key];
                                }
                            }
                            $create_signer_response = $client->request('POST', $host .  '/signature_requests/' . $signatureRequestId . '/signers', [
                                'body' => json_encode($params),
                                'headers' => ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$api_key, 'Content-Type' => 'application/json']
                            ]);

                            if ($create_signer_response->getStatusCode() == 201) {
                                $signer_request_body = json_decode($create_signer_response->getBody());
                                $query->clear()
                                    ->update('#__emundus_files_request')
                                    ->set('signer_id = ' . $db->quote($signer_request_body->id))
                                    ->set('yousign_document_id = ' . $db->quote($document_body->id))
                                    ->where('keyid = ' . $db->quote($signatureRequest->id));
                                $db->setQuery($query);
                                $db->execute();

                                $activate_response = $client->request('POST', $host . "/signature_requests/" . $signatureRequestId . "/activate", [
                                    'headers' => ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$api_key, 'Content-Type' => 'application/json']
                                ]);

                                if ($activate_response->getStatusCode() == 201) {
                                    $activate_response_data = json_decode($activate_response->getBody(), true);

                                    if (!empty(JFactory::getUser()->id)) {
                                        $query->clear()
                                            ->update('#__users')
                                            ->set('params = ' . $db->quote(json_encode(['yousign_signer_id' => $signer_request_body->id, 'yousign_signature_request' => $signatureRequest->id, 'yousign_url' => $activate_response_data['signers'][0]['signature_link']])))
                                            ->where('id = ' . JFactory::getUser()->id);
                                        $db->setQuery($query);
                                        $db->execute();
                                    }

                                    if ($this->getParam('method') === 'embed') {
                                        if (!empty($activate_response_data['signers'])) {
                                            JFactory::getSession()->set('YousignSession', [
                                                'signature_request' => $signatureRequest->id,
                                                'signer_id' => $signer_request_body->id,
                                                'iframe_url' => $activate_response_data['signers'][0]['signature_link']
                                            ]);
                                        }

                                        $application->redirect($this->getParam('embed_url', 'index.php?option=com_emundus&view=yousign'));
                                    } else {
                                        $this->setUserParam($signers['emails'][0], 'yousignMemberId', $signer_request_body->id);
                                        $application->enqueueMessage(JText::_('CHECK_YOUSIGN_EMAILS'));
                                        $application->redirect('/');
                                    }
                                } else {
                                    JLog::add('Failed to activate signature request.', JLog::ERROR, 'com_emundus.yousign');
                                    throw new Exception('Failed to activate signature request.');
                                }
                            } else {
                                JLog::add('Failed to create signer for signature request.', JLog::ERROR, 'com_emundus.yousign');
                                throw new Exception('Failed to create signer for signature request.');
                            }
                        } else {
                            JLog::add('Failed to create document in yousign.', JLog::ERROR, 'com_emundus.yousign');
                            throw new Exception('Failed to create document in yousign.');
                        }
                    } else {
                        JLog::add('Failed to open file to send it to yousign ' . $fileNamePath, JLog::ERROR, 'com_emundus.yousign');
                        throw new Exception('Failed to open file to send it to yousign .');
                    }
                } else {
                    JLog::add('Failed to initiate signature request.', JLog::ERROR, 'com_emundus.yousign');
                    throw new Exception('Failed to initiate signature request.');
                }
            } else {
                JLog::add('Failed to initiate signature request.' . json_decode($response->getBody()), JLog::ERROR, 'com_emundus.yousign');
                throw new Exception('Failed to initiate signature request.');
            }
        } catch (Exception $e) {
            // In the case of a YouSign error, unassign the file.
            $query->clear()
                ->delete($db->quoteName('#__emundus_users_assoc'))
                ->where($db->quoteName('user_id').' = '. JFactory::getUser()->id)
                ->andWhere($db->quoteName('fnum').' IN ("'.implode('","', $fnums).'")');
            $db->setQuery($query);

            try {
                $db->execute();
            } catch (Exception $e) {
                JLog::add('Error removing assoc users : '.$e->getMessage(), JLog::ERROR, 'com_emundus.yousign');
            }

            JLog::add('Failed yousign api request.' . $e->getMessage(), JLog::ERROR, 'com_emundus.yousign');
            throw new Exception($e->getMessage());
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

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
class PlgFabrik_FormEmundusdocusign extends plgFabrik_Form {

	/**
	 * Status field
	 *
	 * @var  string
	 */
	protected $URLfield = '';

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
	 * Main script.
	 *
	 * @return void
	 */
	public function onAfterProcess() {

		// Attach logging system.
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.docusign.php'], JLog::ALL, array('com_emundus'));

		// Import docusign library.
		require_once(JPATH_LIBRARIES.DS.'docusign-php-client/autoload.php');

		include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
		include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
		include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
		include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
		$m_profile = new EmundusModelProfile();
		$m_emails = new EmundusModelEmails();
		$m_files = new EmundusModelFiles();

		$student = JFactory::getSession()->get('emundusUser');
		$fnum = $m_files->getFnumInfos($student->fnum);

		// Docusign OAuth credentials are saved in the plugin's params.
		$host = $this->getParam('host');

		$attachment_id = $this->getParam('attachment_id');
		if (empty($attachment_id)) {
			return;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('lbl'))
			->from($db->quoteName('#__emundus_setup_attachments'))
			->where($db->quoteName('id').' = '.$attachment_id);
		$db->setQuery($query);
		try {
			$attachment_label = $db->loadResult();
		} catch (Exception $e) {
			JLog::add('Error getting attachment label in plugin/docusign at query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return;
		}

		// Signer names and emails can be generated using Fabrik ID tags (ex: ${2302}) or standard tags (ex: [USER_NAME]).
		$signer_name_1 = $this->getParam('signer_name_1');
		$signer_email_1 = $this->getParam('signer_email_1');
		$signer_name_2 = $this->getParam('signer_name_2');
		$signer_email_2 = $this->getParam('signer_email_2');
		$signer_name_3 = $this->getParam('signer_name_3');
		$signer_email_3 = $this->getParam('signer_email_3');

        // These tags will be used for generating signer names and emails programmatically.
        $post = ['USER_NAME' => $student->name, 'USER_EMAIL' => $student->email];
        $tags = $m_emails->setTags($student->id, $post, $fnum, '', $signer_name_1.$signer_name_2.$signer_name_3);

        $signers = [];
		if (!empty($signer_name_1) && !empty($signer_email_1)) {

			// Parse the name and email of the signer using tags.
			$signer_name_1 = preg_replace($tags['patterns'], $tags['replacements'], $signer_name_1);
			$signer_name_1 = $m_emails->setTagsFabrik($signer_name_1, array($student->fnum));
			$signer_email_1 = preg_replace($tags['patterns'], $tags['replacements'], $signer_email_1);
			$signer_email_1 = $m_emails->setTagsFabrik($signer_email_1, array($student->fnum));

			$signer = new DocuSign\eSign\Model\Signer([
				'email' => $signer_email_1,
				'name' => $signer_name_1,
				'recipient_id' => "1",
				'routing_order' => "1"
			]);

			$signTab = new DocuSign\eSign\Model\SignHere([
				'anchor_string' => JText::_('PLG_FABRIK_FORM_EMUNDUSDOCUSIGN_SINGATURE_1_ANCHOR'),
				'anchor_y_offset' => '30',
				'anchor_x_offset' => '0',
				'anchor_units' => 'pixels'
			]);

			// Add the tabs to the signer object
			// The Tabs object wants arrays of the different field/tab types
			$signer->setTabs(new DocuSign\eSign\Model\Tabs(['sign_here_tabs' => [$signTab]]));
			$signers[] = $signer;
			unset($signer, $signTab);
		}

		if (!empty($signer_name_2) && !empty($signer_email_2)) {

			// Parse the name and email of the signer using tags.
			$signer_name_2 = preg_replace($tags['patterns'], $tags['replacements'], $signer_name_2);
			$signer_name_2 = $m_emails->setTagsFabrik($signer_name_2, array($student->fnum));
			$signer_email_2 = preg_replace($tags['patterns'], $tags['replacements'], $signer_email_2);
			$signer_email_2 = $m_emails->setTagsFabrik($signer_email_2, array($student->fnum));

			$signer = new DocuSign\eSign\Model\Signer([
				'email' => $signer_email_2,
				'name' => $signer_name_2,
				'recipient_id' => "2",
				'routing_order' => "2"
			]);

			$signTab = new DocuSign\eSign\Model\SignHere([
				'anchor_string' => JText::_('PLG_FABRIK_FORM_EMUNDUSDOCUSIGN_SINGATURE_2_ANCHOR'),
				'anchor_y_offset' => '30',
				'anchor_x_offset' => '0',
				'anchor_units' => 'pixels'
			]);

			// Add the tabs to the signer object
			// The Tabs object wants arrays of the different field/tab types
			$signer->setTabs(new DocuSign\eSign\Model\Tabs(['sign_here_tabs' => [$signTab]]));
			$signers[] = $signer;
			unset($signer, $signTab);
		}

		if (!empty($signer_name_3) && !empty($signer_email_3)) {

			// Parse the name and email of the signer using tags.
			$signer_name_3 = preg_replace($tags['patterns'], $tags['replacements'], $signer_name_3);
			$signer_name_3 = $m_emails->setTagsFabrik($signer_name_3, array($student->fnum));
			$signer_email_3 = preg_replace($tags['patterns'], $tags['replacements'], $signer_email_3);
			$signer_email_3 = $m_emails->setTagsFabrik($signer_email_3, array($student->fnum));

			$signer = new DocuSign\eSign\Model\Signer([
				'email' => $signer_email_3,
				'name' => $signer_name_3,
				'recipient_id' => "3",
				'routing_order' => "3"
			]);

			$signTab = new DocuSign\eSign\Model\SignHere([
				'anchor_string' => JText::_('PLG_FABRIK_FORM_EMUNDUSDOCUSIGN_SINGATURE_3_ANCHOR'),
				'anchor_y_offset' => '30',
				'anchor_x_offset' => '0',
				'anchor_units' => 'pixels'
			]);

			// Add the tabs to the signer object
			// The Tabs object wants arrays of the different field/tab types
			$signer->setTabs(new DocuSign\eSign\Model\Tabs(['sign_here_tabs' => [$signTab]]));
			$signers[] = $signer;
			unset($signer, $signTab);
		}

		// No point in continuing if no signers are configured.
		if (empty($signers)) {
			return;
		}

		// Step 1. The envelope definition is created.
		// Generate the PDF which will need to be signed.
		if (!file_exists(EMUNDUS_PATH_ABS.$student->id)) {
			mkdir(EMUNDUS_PATH_ABS.$student->id);
			chmod(EMUNDUS_PATH_ABS.$student->id, 0755);
		}
		$profile_id = $m_profile->getProfileByFnum($student->fnum);

        // This bit of code gets some custom pdf code based on the programme.
        if (empty($this->getParam('custom_attachment', ''))) {
            $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_'.@$fnum['training'].'.php';
        } else {
            $file = JPATH_LIBRARIES.DS.'emundus'.DS.$this->getParam('custom_attachment', '');
        }

        if (!file_exists($file)) {
            $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php';
        }

		require_once($file);
		application_form_pdf($student->id, $student->fnum, false, 1, null, null, null, $profile_id, $attachment_label);


		$fileName = $student->fnum.$attachment_label.'.pdf';
		$fileNamePath = EMUNDUS_PATH_ABS.$student->id.DS.$fileName;
		$base64FileContent = base64_encode(file_get_contents($fileNamePath));

		// create the DocuSign document object
		$document = new DocuSign\eSign\Model\Document([
			'document_base64' => $base64FileContent,
			'name' => JFactory::getConfig()->get('sitename'),
			'file_extension' => 'pdf',
			'document_id' => '1'
		]);

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

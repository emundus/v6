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
		$m_profile = new EmundusModelProfile();
		$m_campaign = new EmundusModelProfile();
		$m_emails = new EmundusModelEmails();

		$student = JFactory::getSession()->get('emundusUser');

		// These tags will be used for generating signer names and emails programmatically.
		$post = ['USER_NAME' => $student->name, 'USER_EMAIL' => $student->email];
		$tags = $m_emails->setTags($student->id, $post);

		// Docusign OAuth credentials are saved in the plugin's params.
		$token = $this->getParam('token');
		$account_id = $this->getParam('account_id');
		$host = $this->getParam('host');

		// Signer names and emails can be generated using Fabrik ID tags (ex: ${2302}) or standard tags (ex: [USER_NAME]).
		$signer_name_1 = $this->getParam('signer_name_1');
		$signer_email_1 = $this->getParam('signer_email_1');
		$signer_name_2 = $this->getParam('signer_name_2');
		$signer_email_2 = $this->getParam('signer_email_2');
		$signer_name_3 = $this->getParam('signer_name_3');
		$signer_email_3 = $this->getParam('signer_email_3');

		$signers = [];
		if (!empty($signer_name_1) && !empty($signer_email_1)) {

			// Parse the name and email of the signer using tags.
			$signer_name_1 = preg_replace($tags['patterns'], $tags['replacements'], $signer_name_1);
			$signer_name_1 = $m_emails->setTagsFabrik($signer_name_1, array($student->fnum));
			$signer_email_1 = preg_replace($tags['patterns'], $tags['replacements'], $signer_email_1);
			$signer_email_1 = $m_emails->setTagsFabrik($signer_email_1, array($student->fnum));

			$signer = new DocuSign\eSign\Model\Signer(['email' => $signer_email_1, 'name' => $signer_name_1, 'recipient_id' => "1", 'routing_order' => "1"]);

			// TODO: Work out signature positioning.
			$signTab = new DocuSign\eSign\Model\SignHere([
				'document_id' => '1', 'page_number' => '1', 'recipient_id' => '1',
				'tab_label' => 'Signature 1', 'x_position' => '195', 'y_position' => '147'
			]);

			// Add the tabs to the signer object
			// The Tabs object wants arrays of the different field/tab types
			$signer->setTabs(new DocuSign\eSign\Model\Tabs(['sign_here_tabs' => $signTab]));
			$signers[] = $signer;
			unset($signer, $signTab);
		}

		if (!empty($signer_name_2) && !empty($signer_email_2)) {

			// Parse the name and email of the signer using tags.
			$signer_name_2 = preg_replace($tags['patterns'], $tags['replacements'], $signer_name_2);
			$signer_name_2 = $m_emails->setTagsFabrik($signer_name_2, array($student->fnum));
			$signer_email_2 = preg_replace($tags['patterns'], $tags['replacements'], $signer_email_2);
			$signer_email_2 = $m_emails->setTagsFabrik($signer_email_2, array($student->fnum));

			$signer = new DocuSign\eSign\Model\Signer(['email' => $signer_email_2, 'name' => $signer_name_2, 'recipient_id' => "2", 'routing_order' => "2"]);

			// TODO: Work out signature positioning.
			$signTab = new DocuSign\eSign\Model\SignHere([
				'document_id' => '1', 'page_number' => '1', 'recipient_id' => '2',
				'tab_label' => 'Signature 2', 'x_position' => '195', 'y_position' => '147'
			]);

			// Add the tabs to the signer object
			// The Tabs object wants arrays of the different field/tab types
			$signer->setTabs(new DocuSign\eSign\Model\Tabs(['sign_here_tabs' => $signTab]));
			$signers[] = $signer;
			unset($signer, $signTab);
		}

		if (!empty($signer_name_3) && !empty($signer_email_3)) {

			// Parse the name and email of the signer using tags.
			$signer_name_3 = preg_replace($tags['patterns'], $tags['replacements'], $signer_name_3);
			$signer_name_3 = $m_emails->setTagsFabrik($signer_name_3, array($student->fnum));
			$signer_email_3 = preg_replace($tags['patterns'], $tags['replacements'], $signer_email_3);
			$signer_email_3 = $m_emails->setTagsFabrik($signer_email_3, array($student->fnum));

			$signer = new DocuSign\eSign\Model\Signer(['email' => $signer_email_3, 'name' => $signer_name_3, 'recipient_id' => "3", 'routing_order' => "3"]);

			// TODO: Work out signature positioning.
			$signTab = new DocuSign\eSign\Model\SignHere([
				'document_id' => '1', 'page_number' => '1', 'recipient_id' => '3',
				'tab_label' => 'Signature 3', 'x_position' => '195', 'y_position' => '147'
			]);

			// Add the tabs to the signer object
			// The Tabs object wants arrays of the different field/tab types
			$signer->setTabs(new DocuSign\eSign\Model\Tabs(['sign_here_tabs' => $signTab]));
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
		require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php');
		application_form_pdf($student->id, $student->fnum, true, 1, null, null, null, $profile_id);
		$fileNamePath = EMUNDUS_PATH_ABS.$student->id.DS.$student->fnum.'_application.pdf';
		$base64FileContent = base64_encode(file_get_contents($fileNamePath));

		// create the DocuSign document object
		$document = new DocuSign\eSign\Model\Document([
			'document_base64' => $base64FileContent,
			'name' => 'eMundus digital signature',
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
		$config->addDefaultHeader("Authorization", "Bearer " . $token);
		$apiClient = new DocuSign\eSign\ApiClient($config);
		$envelopeApi = new DocuSign\eSign\Api\EnvelopesApi($apiClient);

		try {
			$results = $envelopeApi->createEnvelope($account_id, $envelopeDefinition);
		} catch (DocuSign\eSign\ApiException $e) {
			JLog::add('Error creating DocuSign envelope -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		echo '<pre>'; var_dump($results); echo '</pre>'; die;
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

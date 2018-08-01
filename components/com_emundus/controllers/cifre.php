<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @copyright  eMundus
 * @author     Hugo Moracchini
 * @since      3.8.8
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

class EmundusControllerCifre extends JControllerLegacy {

	// Initialize class variables
	var $user = null;
	var $m_cifre = null;
	var $c_messages = null;
	var $m_files = null;

	public function __construct(array $config = array()) {

		require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'cifre.php');
		require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
		require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'controllers'.DS.'messages.php');
		require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');


		// Load class variables
		$this->user = JFactory::getSession()->get('emundusUser');
		$this->m_cifre = new EmundusModelCifre();
		$this->c_messages = new EmundusControllerMessages();
		$this->m_files = new EmundusModelFiles();

		parent::__construct($config);
	}


	/**
	 * Gets the type of action button to be put on the page.
	 * @param $fnum
	 *
	 * @return bool|string
	 */
	public function getActionButton($fnum) {

		// If the user is looking at his own cifre offer, no button.
		if (empty($fnum) ||$this->user->id == (int)substr($fnum, -7))
			return false;

		// The contact status is the 'level' of link they have together.
		// // -1 = user has already been contacted by the other.
		// // 1 = user has contacted but not been answered.
		// // 2 = users are in contact.
		$contact_status = $this->m_cifre->getContactStatus($this->user->id, $fnum);

		// The actions of the button are dependent on the different conditions.
		if ($contact_status == -1)
			return 'reply';
		elseif ($contact_status == 1)
			return 'retry';
		elseif ($contact_status == 2)
			return 'breakup';
		else
			return 'contact';

	}

	/**
	 * Get all offers made by the current user.
	 *
	 * @param $fnum String do not get offers linked to this fnum.
	 * @return Mixed
	 */
	public function getOwnOffers($fnum = null) {
		return $this->m_cifre->getOffersByUser($this->user->id, $fnum);
	}


	/**
	 * Contact someone for their offer.
	 */
	public function contact() {

		try {
			$application = JFactory::getApplication();
		} catch (Exception $e) {
			JLog::add('Unable to start application in c/cifre', JLog::ERROR, 'com_emundus');
			echo json_encode((object)['status' => false, 'msg' => 'Internal server error']);
			exit;
		}


		$jinput = $application->input;
		$fnum = $jinput->post->get('fnum', null);
		$linkedOffer = $jinput->post->get('linkedOffer', null);
		// If there is an entry in the contact table then that means we already have a link with this person
		if (!empty($this->m_cifre->getContactStatus($this->user->id, $fnum))) {
			echo json_encode((object)['status' => false, 'msg' => 'Vous avez déja contacté cette personne pour cette offre.']);
			exit;
		}

		// Log the act of having contacted the person.
		EmundusModelLogs::log($this->user->id, (int)substr($fnum, -7), $fnum, 32, 'c', 'COM_EMUNDUS_LOGS_CONTACT_REQUEST');

		// Add the contact request into the DB.
		if ($this->m_cifre->createContactRequest((int)substr($fnum, -7), $this->user->id, $fnum, $linkedOffer)) {

			// Get additional info for the fnums such as the user email.
			$fnum = $this->m_files->getFnumInfos($fnum);

			// This gets additional information about the offer, for example the title.
			$offerInformation = $this->m_cifre->getOffer($fnum['fnum']);

			// Link created: Send email.
			if (!empty($linkedOffer)) {
				
				$linkedOffer = $this->m_cifre->getOffer($linkedOffer);
				$url = JRoute::_(JURI::base()."/les-offres/consultez-les-offres/details/299/".$linkedOffer->search_engine_page);
				$post = [
					'USER_NAME' => $this->user->name,
					'LINKED_OFFER_FNUM' => $linkedOffer->fnum,
					'LINKED_OFFER_NAME' => $linkedOffer->titre,
					'OFFER_USER_NAME' => $fnum['name'],
					'OFFER_NAME' => $offerInformation->titre,
					'LINKED_OFFER_ID' => "<a href ='" . $url . "'>Voir offre</a>"
				];

				$email_to_send = 72;

			} else {

				$post = [
					'USER_NAME' => $this->user->name,
					'OFFER_USER_NAME' => $fnum['name'],
					'OFFER_NAME' => $offerInformation->titre
				];

				$email_to_send = 71;
			}

			echo json_encode((object)['status' => $this->c_messages->sendEmail($fnum['fnum'], $email_to_send, $post)]);
			exit;

		} else {
			echo json_encode((object)['status' => false, 'msg' => 'Internal server error']);
			exit;
		}
	}

	/**
	 * Retry contacting someone for their offer.
	 */
	public function retry() {

		try {
			$application = JFactory::getApplication();
		} catch (Exception $e) {
			JLog::add('Unable to start application in c/cifre', JLog::ERROR, 'com_emundus');
			echo json_encode((object)['status' => false, 'msg' => 'Internal server error']);
			exit;
		}

		$jinput = $application->input;
		$fnum = $jinput->post->get('fnum', null);

		$fnum = $this->m_files->getFnumInfos($fnum);

		// If we have a link type that isn't 1 then we have not contacted them.
		if ($this->m_cifre->getContactStatus($this->user->id, $fnum['fnum']) != 1) {
			echo json_encode((object)['status' => false, 'msg' => "Vous n'avez pas contacté la personne pour cette offre ou vous etes déja en lien avec cette personne."]);
			exit;
		}

		// Log the act of having contacted the person.
		EmundusModelLogs::log($this->user->id, $fnum['applicant_id'], $fnum['fnum'], 32, 'u', 'COM_EMUNDUS_LOGS_CONTACT_REQUEST_RETRY');

		// This gets additional information about the offer, for example the title.
		$offerInformation = $this->m_cifre->getOffer($fnum['fnum']);

		$post = [
			'USER_NAME' => $this->user->name,
			'OFFER_USER_NAME' => $fnum['name'],
			'OFFER_NAME' => $offerInformation->titre,
		];

		$email_to_send = 73;

		echo json_encode((object)['status' => $this->c_messages->sendEmail($fnum['fnum'], $email_to_send, $post)]);
		exit;

	}

	/**
	 * Reply to an offer that someone has contacted you with.
	 */
	public function reply() {

		try {
			$application = JFactory::getApplication();
		} catch (Exception $e) {
			JLog::add('Unable to start application in c/cifre', JLog::ERROR, 'com_emundus');
			echo json_encode((object)['status' => false, 'msg' => 'Internal server error']);
			exit;
		}

		$jinput = $application->input;
		$fnum = $jinput->post->get('fnum', null);

		// If we have a link type that isnt -1 then we are not replying.
		if ($this->m_cifre->getContactStatus($this->user->id, $fnum) != -1) {
			echo json_encode((object)['status' => false, 'msg' => "L'offre a laquelle vous répondez ne vous a jamais contacté."]);
			exit;
		}

		// Log the act of having contacted the person.
		EmundusModelLogs::log($this->user->id, (int)substr($fnum, -7), $fnum, 34, 'c', 'COM_EMUNDUS_LOGS_CONTACT_REQUEST_ACCEPTED');

		// Add the contact request into the DB.
		if ($this->m_cifre->acceptContactRequest((int)substr($fnum, -7), $this->user->id, $fnum)) {

			// This gets additional information about the offer, for example the title.
			$offerInformation = $this->m_cifre->getOffer($fnum['fnum']);

			$post = [
				'USER_NAME' => $this->user->name,
				'OFFER_USER_NAME' => $fnum['name'],
				'OFFER_NAME' => $offerInformation->titre,
			];

			$email_to_send = 74;

			echo json_encode((object)['status' => $this->c_messages->sendEmail($fnum['fnum'], $email_to_send, $post)]);
			exit;

		} else {
			echo json_encode((object)['status' => false, 'msg' => 'Internal server error']);
			exit;
		}
	}

	/**
	 * Break contact with someone for their offer.
	 */
	public function breakup() {

		try {
			$application = JFactory::getApplication();
		} catch (Exception $e) {
			JLog::add('Unable to start application in c/cifre', JLog::ERROR, 'com_emundus');
			echo json_encode((object) ['status' => false, 'msg' => 'Internal server error']);
			exit;
		}

		$jinput = $application->input;
		$fnum   = $jinput->post->get('fnum', null);

		// If we have a link type that isnt -1 then we are not replying.
		if (empty($this->m_cifre->getContactStatus($this->user->id, $fnum))) {
			echo json_encode((object) ['status' => false, 'msg' => "Vous n'etes pas en contact avec cette personne pour cette offre."]);
			exit;
		}

		// Log the act of having contacted the person.
		EmundusModelLogs::log($this->user->id, (int) substr($fnum, -7), $fnum, 34, 'd', 'COM_EMUNDUS_LOGS_CONTACT_REQUEST_DELETED');

		// Add the contact request into the DB.
		if ($this->m_cifre->deleteContactRequest((int) substr($fnum, -7), $this->user->id, $fnum)) {

			// This gets additional information about the offer, for example the title.
			$offerInformation = $this->m_cifre->getOffer($fnum['fnum']);

			$post = [
				'USER_NAME' => $this->user->name,
				'OFFER_USER_NAME' => $fnum['name'],
				'OFFER_NAME' => $offerInformation->titre,
			];

			$email_to_send = 75;

			echo json_encode((object)['status' => $this->c_messages->sendEmail($fnum['fnum'], $email_to_send, $post)]);
			exit;

		} else {
			echo json_encode((object) ['status' => false, 'msg' => 'Internal server error']);
			exit;
		}
	}
}
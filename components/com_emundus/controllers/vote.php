<?php
/**
 * @version     $Id: vote.php 750 2012-01-23 22:29:38Z brivalland $
 * @package     Joomla
 * @copyright   (C) 2016 eMundus LLC. All rights reserved.
 * @license     GNU General Public License
 */

defined( '_JEXEC' ) or die( JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS') );

use Joomla\CMS\Factory;

/**
 * Custom report controller
 * @package     Emundus
 */
class EmundusControllerVote extends JControllerLegacy
{
	protected $_app;
	private $_user;

	public function __construct($config = array(), \Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null) {
		parent::__construct($config, $factory);

		$this->_app = Factory::getApplication();
		$this->_user = $this->_app->getIdentity();
	}

	public function vote()
	{
		$result = array(
			'status' => false,
			'message' => JText::_('COM_EMUNDUS_ERROR_OCCURED'),
		);

		require_once JPATH_SITE . '/components/com_emundus/helpers/emails.php';
		$h_emails = new EmundusHelperEmails();

		$m_vote = $this->getModel('vote');
		$m_gallery = $this->getModel('gallery');

		$can_vote = true;
		$email = $this->input->getString('email', '');
		$ccid = $this->input->getInt('ccid', 0);
		$listid = $this->input->getInt('listid', 0);

		if($h_emails->correctEmail($email) && !empty($ccid)) {

			if(!empty($listid)) {
				$gallery = $m_gallery->getGalleryByList($listid);
				$votes = $m_vote->getVotesByUser(null, $email);

				if(count($votes) >= $gallery->max) {
					$can_vote = false;
				}

				if($gallery->voting_access != 1 && $this->_user->guest == 1) {
					$can_vote = false;
				}
			}

			if($can_vote) {
				$uid = 0;
				if ($this->_user->guest != 1) {
					$uid = $this->_user->id;
				}

				$result['status'] = $m_vote->vote($email, $ccid, $uid);
			}
		}

		echo json_encode($result);
		exit;
	}

	public function checkaccess()
	{
		$gallery_url = $this->input->server->getString('HTTP_REFERER','index.php');

		$result = array(
			'status' => false,
			'access' => false,
			'message' => JText::_('COM_EMUNDUS_ERROR_OCCURED'),
			'login_url' => JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($gallery_url))
		);

		$m_gallery = $this->getModel('gallery');

		$listid = $this->input->getInt('listid', 0);

		if(!empty($listid)) {
			$gallery = $m_gallery->getGalleryByList($listid);

			if(!empty($gallery)) {
				$result['message'] = '';
				$result['status'] = true;

				if(($gallery->voting_access != 1 && $this->_user->guest != 1) || $gallery->voting_access == 1) {
					$result['access'] = true;
				}
			}
		}

		echo json_encode($result);
		exit;
	}
}
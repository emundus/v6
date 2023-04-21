<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Site
 * @subpackage	mod_emundusmenu
 * @since		1.5
 */
class modEmundusCifreOffersHelper {

	// Initialize class variables
	var $user = null;
	var $m_cifre = null;

	public function __construct() {

		// Include models.
		require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'cifre.php');

		// Load class variables
		$this->user = JFactory::getSession()->get('emundusUser');
		$this->m_cifre = new EmundusModelCifre();

	}


	public function getContactRequests() {

		$contactRequests = new stdClass();

		// Get contact requests that the user has been sent.
		$contactRequests->to = $this->m_cifre->getContactToUser($this->user->id);

		// If an offer has been attached to the request, we need to get it's info.
		foreach ($contactRequests->to as $request) {
			if (!empty($request->fnum_from)) {
				$request->offer_from = $this->m_cifre->getOffer($request->fnum_from);
				$request->profile = $request->profile;
			}
		}

		// Get contact requests that the user has sent.
		$contactRequests->from = $this->m_cifre->getContactFromUser($this->user->id);
		foreach ($contactRequests->from as $request) {
			if (!empty($request->fnum_from)) {
				$request->offer_from = $this->m_cifre->getOffer($request->fnum_from);
				$request->profile = $request->profile;
			}
		}

		return $contactRequests;

	}

}

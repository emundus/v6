<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Site
 * @subpackage	mod_emundusciffresuggestions
 * @since		1.5
 */
class modEmundusCifreSuggestionsHelper {

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


	public function getSuggestions() {

		// Here we need to hit the model and get a randomized list of offers that respond to the criteria of the user.
		// Conditions are as follows
		/// status = 1
		/// profile of person who made the offer != $user profile
		/// offer needs to be searching for the profile we are
		/// if offers exists with our locations and thematics: get those
		/// if none: just get random offers that can be accepted by the candidate

		return $this->m_cifre->getSuggestions($this->user->id, $this->user->profile);

	}

}

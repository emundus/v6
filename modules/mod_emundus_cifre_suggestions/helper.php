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

	/**
	 * @param   int  $nb_suggestions
	 *
	 * @return bool|mixed
	 *
	 * @since version
	 */
	public function getSuggestions($nb_suggestions = 5) {

		// Here we need to hit the model and get a randomized list of offers that respond to the criteria of the user.
		// Conditions are as follows
		/// status = 1
		/// profile of person who made the offer != $user profile
		/// offer needs to be searching for the profile we are
		/// if offers exists with our locations and thematics: get those
		/// if none: just get random offers that can be accepted by the candidate

		$results = $this->m_cifre->getSuggestions($this->user->id, $this->user->profile, null, $nb_suggestions);
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		foreach ($results as $key => $res) {
			
			if (!empty($res->themes)) {
				$query->clear()
					->select('thematic')
					->from('data_thematics')
					->where('id IN ('.$res->themes.')');
				$db->setQuery($query);
				try {
					$themes = $db->loadColumn();
					if (sizeof($themes) > 4) {
						$themes = implode(' - ', array_slice($themes, 0, 4)).' ... ';
					} else {
						$themes = implode(' - ', $themes);
					}
					$results[$key]->themes = $themes;
				} catch (Exception $e) {
					$results[$key]->themes = '';
				}
			}
			
			if (!empty($res->department)) {
				$query->clear()
					->select('departement_nom')
					->from('data_departements')
					->where('departement_id IN ('.$res->department.')');
				$db->setQuery($query);
				try {
					$department = $db->loadColumn();
					if (sizeof($department) > 8) {
						$department = implode(' - ', array_slice($department, 0, 8)).' ... ';
					} else {
						$department = implode(' - ', $department);
					}
					$results[$key]->department = $department;
				} catch (Exception $e) {
					$results[$key]->department = '';
				}
			}

			$search = [];
			if ($res->futur_doctorant_yesno === '1') {
				$search[] = 'Futur doctorant';
			}
			if ($res->acteur_public_yesno === '1') {
				$search[] = 'Acteur public';
			}
			if ($res->equipe_de_recherche_codirection_yesno === '1' || $res->equipe_de_recherche_direction_yesno === '1') {
				$search[] = 'Équipe de recherche';
			}
			$results[$key]->search = implode(' - ', $search);
		}

		return $results;
	}
}

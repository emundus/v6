<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2018 eMundus. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      eMundus SAS - Benjamin Rivalland
 */

// No direct access
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');
/**
 * Joomla User plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  User.emundus
 * @since       5.0.0
 */
class plgUserEmundus_assign_to_files extends JPlugin {

	/**
	 * @param $user
	 * @param $isnew
	 *
	 * @return bool
	 *
	 * @throws Exception
	 * @since version
	 */
	public function onUserBeforeSave($user, $isnew) {

		// If we are modifying the user, we need to unassign him.
		if (!$isnew) {

			$app = JFactory::getApplication();

			$jinput = $app->input;
			$organisation_id = $jinput->post->get('university_id', null, null);

			$fk_field = $this->params->get('fk_field');
			$assignment_rule = $this->params->get('assignment_rule', 'organisation_id');

			// Returning true does not interrupt the rest of the flow, but will stop this plugin from running any further.
			if (empty($fk_field)) {
				return true;
			}

			$fk_field = explode('___', $fk_field);
			$fk_table = $fk_field[0];
			$fk_field = $fk_field[1];

			if (empty($fk_field) || empty($fk_table)) {
				return true;
			}

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			// If a an institution ID is provided, we need to check if it has changed from the previous value.
			$query->clear()
				->select($db->quoteName('university_id'))
				->from($db->quoteName('jos_emundus_users'))
				->where($db->quoteName('user_id').' = '.$user['id']);
			$db->setQuery($query);

			try {
				$old_organisation = $db->loadResult();
			} catch (Exception $e) {
				// Log error.
				return true;
			}

			// If we have an old institution and it is not the same as the new, this means we need to unassign all of the old files.
			if (!empty($old_organisation) && $old_organisation !== $organisation_id) {

				// If the rule is based on the field then we need
				if ($assignment_rule === 'organisation_note') {
					$query->clear()
						->select($db->quoteName('note'))
						->from($db->quoteName('jos_categories'))
						->where($db->quoteName('id').' = '.$old_organisation);
					$db->setQuery($query);

					try {
						$old_organisation = $db->loadResult();
					} catch (Exception $e) {
						// Log error
						return true;
					}
				}

				$query->clear()
					->select($db->quoteName('fnum'))
					->from($db->quoteName($fk_table))
					->where($db->quoteName($fk_field).' = '.$db->quote($old_organisation));
				$db->setQuery($query);

				try {
					$fnums_to_delete = $db->loadColumn();
				} catch (Exception $e) {
					// Log error
					return true;
				}

				if (!empty($fnums_to_delete)) {

					$query->clear()
						->delete($db->quoteName('jos_emundus_users_assoc'))
						->where($db->quoteName('fnum').' IN ("'.implode('","',$fnums_to_delete).'") AND '.$db->quoteName('user_id').' = '.$user['id']);
					$db->setQuery($query);

					try {
						$db->execute();
					} catch (Exception $e) {
						// Log error
						return true;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Utility method to act on a user after it has been saved.
	 *
	 * This method sends a registration email to new users created in the backend.
	 *
	 * @param array $user Holds the new user data.
	 *
	 * @return  bool
	 * @throws Exception
	 * @since   1.6
	 */
    public function onUserAfterSave(array $user) {

	    $app = JFactory::getApplication();

	    $jinput = $app->input;
	    $organisation = $jinput->post->get('university_id', null, null);

	    $fk_field = $this->params->get('fk_field');
	    $assignment_rule = $this->params->get('assignment_rule', 'organisation_id');

	    // Returning true does not interrupt the rest of the flow, but will stop this plugin from running any further.
	    if (empty($fk_field)) {
		    return true;
	    }

	    $fk_field = explode('___', $fk_field);
	    $fk_table = $fk_field[0];
	    $fk_field = $fk_field[1];

	    if (empty($fk_field) || empty($fk_table)) {
		    return true;
	    }

	    $db = JFactory::getDbo();
	    $query = $db->getQuery(true);

	    // If we have an organisation, then it's assignment time!
	    if (!empty($organisation)) {

		    // If the rule is based on the field then we need
		    if ($assignment_rule === 'organisation_note') {
			    $query->clear()
				    ->select($db->quoteName('note'))
				    ->from($db->quoteName('jos_categories'))
				    ->where($db->quoteName('id').' = '.$organisation);
			    $db->setQuery($query);

			    try {
				    $organisation = $db->loadResult();
			    } catch (Exception $e) {
				    // Log error
				    return true;
			    }
		    }

		    $query->clear()
			    ->select($db->quoteName('fnum'))
			    ->from($db->quoteName($fk_table))
			    ->where($db->quoteName($fk_field).' = '.$db->quote($organisation));
		    $db->setQuery($query);

		    try {
			    $fnums_to_add = $db->loadColumn();
		    } catch (Exception $e) {
			    // Log error
			    return true;
		    }

		    if (!empty($fnums_to_add)) {

			    $query->clear()
				    ->insert($db->quoteName('jos_emundus_users_assoc'))
				    ->columns($db->quoteName(['user_id', 'fnum', 'action_id', 'c', 'r', 'u', 'd']));
			    foreach ($fnums_to_add as $fnum) {
			    	$query->values($user['id'].', '.$db->quote($fnum).', 1, 0, 1, 0, 0');
			    }
			    $db->setQuery($query);

			    try {
				    $db->execute();
			    } catch (Exception $e) {
				    // Log error
				    return true;
			    }
		    }
	    }

	    return true;
    }
}

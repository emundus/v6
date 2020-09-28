<?php
/**
 * A cron task to email a recall to incomplet applications
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.email
 * @copyright   Copyright (C) 2015 emundus.fr - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

/**
 * A cron task to email records to a give set of users (incomplete application)
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusrecall
 * @since       3.0
 */

class PlgFabrik_Cronemundushesamrecap extends PlgFabrik_Cron{

    /**
	 * Check if the user can use the plugin
	 *
	 * @param   string  $location  To trigger plugin on
	 * @param   string  $event     To trigger plugin on
	 *
	 * @return  bool can use or not
     *
     * @since 6.9.3
	 */
	public function canUse($location = null, $event = null){
		return true;
	}


    /**
     * Do the plugin action
     *
     * @param   array  &$data data
     *
     * @return  int  number of records updated
     *
     * @since 6.9.3
     * @throws Exception
     */
	public function process(&$data, &$listModel) {
		jimport('joomla.mail.helper');

        // LOGGER
        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.hesamrecap.info.php'], JLog::INFO);
        JLog::addLogger(['text_file' => 'com_emundus.hesamrecap.error.php'], JLog::ERROR);

        $params = $this->getParams();
        $reminder_mail_lbl = $params->get('reminder_mail_lbl', null);

		$this->log = '';

        // Get list of applicants to notify
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(['u.id', 'u.email', 'eu.firstname', 'eu.lastname', 'eu.profile']))
            ->from($db->quoteName('#__users', 'u'))
            ->leftJoin($db->quoteName('#__emundus_users', 'eu').' ON '.$db->quoteName('eu.user_id').' = '.$db->quoteName('u.id'))
            ->where($db->quoteName('u.block').' = 0 AND UNIX_TIMESTAMP('.$db->quoteName('u.lastvisitDate'). ') != 0 AND '.$db->quoteName('eu.newsletter').' LIKE '.$db->quote('["oui"]'));
		$db->setQuery($query);

		try {
            $users = $db->loadObjectList();
        } catch (Exception $e) {
		    JLog::add('Error getting list of applicants to notify in plugin/emundusHesamRecap at query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
        }


		// Generate emails from template and store it in message table
		if (!empty($users)) {
            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'controllers'.DS.'messages.php');
            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'cifre.php');
			$c_messages = new EmundusControllerMessages();
			$m_cifre = new EmundusModelCifre();

            $weeksAgo = strtotime('-'.$params->get('number_of_weeks', '2').' weeks');

			foreach ($users as $user) {

			    // Get recommended offers for each user.
                $offers = $m_cifre->getSuggestions($user->id, $user->profile, $weeksAgo);

                if (empty($offers)) {
                    continue;
                }

                // Build an HTML list of all of these offers.
                $suggestions = '<table><tbody>';
                foreach ($offers as $offer) {
                    $suggestions .= '<tr>
                        <td>'.$offer->titre.'</td>
                        <td style="border-radius: 3px;" align="center" bgcolor="#bb0e29">
                            <a style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; border-radius: 3px; padding: 6px 18px; border: 1px solid #bb0e29; display: inline-block;" href="'.JURI::root()."les-offres/consultez-les-offres/details/299/".$offer->search_engine_page.'" target="_blank">Voir l\'offre</a>
                        </td>
                    </tr>
                    <tr><td></td><td></td></tr>';
                }
                $suggestions .= '</tbody></table>';

				$post = [
                    'FIRSTNAME' => $user->firstname,
                    'LASTNAME' => strtoupper($user->lastname),
                    'OFFERS' => $suggestions,
				];

				// Send the email.
				$c_messages->sendEmailNoFnum($user->email, $reminder_mail_lbl, $post, $user->id);
			}
		}

		$this->log .= "\n process " . count($users) . " user(s)";

		return count($users);
	}
}

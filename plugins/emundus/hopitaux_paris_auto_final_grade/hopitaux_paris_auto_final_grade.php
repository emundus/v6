<?php
/**
 * @package       eMundus
 * @version       6.6.5
 * @author        eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license       GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * A cron task to email records to a give set of users (incomplete application)
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusrecall
 * @since       3.0
 */
class PlgEmundusHopitaux_paris_auto_final_grade extends JPlugin
{

	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		jimport('joomla.log.log');
		JLog::addLogger(array('text_file' => 'com_emundus.emundushopitaux_paris_auto_final_grade.php'), JLog::ALL, array('com_emundus'));
	}


	function onAfterStatusChange($fnum, $state)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$status_to_check  = explode(',', $this->params->get('final_grade_status_step', ''));
		$elts_to_complete = explode(';', $this->params->get('final_grade_elts_to_complete', ''));
		$elt_values       = explode(';', $this->params->get('final_grade_elts_values', ''));

		$user = JFactory::getUser();

		$query->select('applicant_id,campaign_id')
			->from($db->quoteName('#__emundus_campaign_candidature'))
			->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));
		$db->setQuery($query);
		$file = $db->loadObject();

		$status = array_search($state, $status_to_check);

		if ($status === false || empty($elts_to_complete)) {
			return false;
		}

		$elts   = explode(',', $elts_to_complete[$status]);
		$values = explode(',', $elt_values[$status]);

		try {
			foreach ($elts as $key => $elt) {
				$table   = explode('___', $elt)[0];
				$element = explode('___', $elt)[1];

				$query->clear()
					->select('id')
					->from($db->quoteName($table))
					->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));
				$db->setQuery($query);
				$final_grade = $db->loadResult();

				$value_expected = $values[$key];

				if (strpos($value_expected, '___')) {
					$query->clear()
						->select(explode('___', $value_expected)[1])
						->from($db->quoteName(explode('___', $value_expected)[0]))
						->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));
					$db->setQuery($query);
					$value_expected = $db->loadResult();
				}

				if (!empty($final_grade)) {
					$query->clear()
						->select($element)
						->from($table)
						->where($db->quoteName('id') . ' = ' . $db->quote($final_grade));
					$db->setQuery($query);
					$value_exist = $db->loadResult();

					if (is_null($value_exist) || $value_exist == '' || $value_exist == 0.00) {
						$query->clear()
							->update($db->quoteName($table))
							->set($db->quoteName($element) . ' = ' . $db->quote($value_expected))
							->where($db->quoteName('id') . ' = ' . $db->quote($final_grade));
						$db->setQuery($query);
						$db->execute();
					}
				}
				else {
					$query->clear()
						->insert($db->quoteName($table))
						->set($db->quoteName('time_date') . ' = ' . $db->quote(date('Y-m-d h:i:s')))
						->set($db->quoteName('user') . ' = ' . $db->quote($user->id))
						->set($db->quoteName('student_id') . ' = ' . $db->quote($file->applicant_id))
						->set($db->quoteName('fnum') . ' = ' . $db->quote($fnum))
						->set($db->quoteName($element) . ' = ' . $db->quote($value_expected));
					$db->setQuery($query);
					$db->execute();
					$final_grade = $db->insertid();
				}
			}
		}
		catch (Exception $e) {
			JLog::add('plugins/emundus/hopitaux_paris_auto_final_grade | Error when try to complete final grade : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');

			return false;
		}

		return true;
	}
}

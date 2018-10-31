<?php
/**
 * @version 2: emundusReferentLetter 2018-04-25 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Redirection et chainage des formulaires suivant le profile de l'utilisateur
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

class PlgFabrik_FormEmundusAssignToInstitution extends plgFabrik_Form {
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

		if ($params->get($pname) == '')
			return '';

		$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));

		return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
	}

	/**
	 * Get the fields value regardless of whether its in joined data or no
	 *
	 * @param   string  $pname    Params property name to get the value for
	 * @param   array   $data     Posted form data
	 * @param   mixed   $default  Default value
	 *
	 * @return  mixed  value
	 */
	public function getParam($pname, $default = '') {
		$params = $this->getParams();

		if ($params->get($pname) == '')
			return $default;

		return $params->get($pname);
	}

	/**
	 * Main script.
	 *
	 * @return  bool
	 */
	public function onBeforeCalculations() {

		jimport('joomla.utilities.utility');
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.institutionAssign.php'], JLog::ALL, ['com_emundus']);

		$db = JFactory::getDBO();
		$jinput = JFactory::getApplication()->input;
		$fnum = $jinput->get->get('rowid');

		// TODO: Set the institution IDS to be the fabrik elements provided in the params.
		$institution_ids = [];
		$institution = $this->getParam('institution_1');
		if (!empty($institution))
			$institution_ids[] = (!empty($jinput->post->get($institution.'_raw'))) ? $jinput->post->get($institution.'_raw') : (is_array($jinput->post->get($institution))) ? $jinput->post->get($institution)[0]: $jinput->post->get($institution);
		$institution = $this->getParam('institution_2');
		if (!empty($institution))
			$institution_ids[] = (!empty($jinput->post->get($institution.'_raw'))) ? $jinput->post->get($institution.'_raw') : (is_array($jinput->post->get($institution))) ? $jinput->post->get($institution)[0]: $jinput->post->get($institution);
		$institution = $this->getParam('institution_3');
		if (!empty($institution))
			$institution_ids[] = (!empty($jinput->post->get($institution.'_raw'))) ? $jinput->post->get($institution.'_raw') : (is_array($jinput->post->get($institution))) ? $jinput->post->get($institution)[0]: $jinput->post->get($institution);
		unset($institution);

		if (empty($institution_ids))
			return false;

		// Using the institution IDs we can get the groups attached to it.
		$query = 'SELECT DISTINCT(g.id) FROM #__emundus_setup_groups AS g
                LEFT JOIN #__emundus_setup_groups_repeat_institution_id AS i ON i.parent_id = g.id
                WHERE i.institution_id IN ('.implode(',',$institution_ids).')';

		try {

			$db->setQuery($query);
			$groups = $db->loadColumn();

		} catch (Exception $e) {
			JLog::add('Error in script/assign-to-institution getting groups by institution at query: '.$query, JLog::ERROR, 'com_emundus');
		}

		if (empty($groups))
			return false;

		foreach ($groups as $group) {

			$query = 'SELECT COUNT(id) FROM #__emundus_group_assoc
                WHERE group_id = '.$group.' AND action_id = 1 AND fnum LIKE '.$db->Quote($fnum);

			try {

				$db->setQuery($query);
				$cpt = $db->loadResult();

			} catch (Exception $e) {
				JLog::add('Error in script/assign-to-institution getting groups at query: '.$query, JLog::ERROR, 'com_emundus');
			}

			if ($cpt == 0) {
				$query = 'INSERT INTO #__emundus_group_assoc (`group_id`, `action_id`, `fnum`, `c`, `r`, `u`, `d`)
                    VALUES ('.$group.', 1, '.$db->Quote($fnum).', 0, 1, 0, 0)';

				try {

					$db->setQuery($query);
					$db->execute();

				} catch (Exception $e) {
					JLog::add('Error in script/assign-to-institution setting rights to groups at query: '.$query, JLog::ERROR, 'com_emundus');
				}
			}
		}
		return true;
	}

	/**
	 * Raise an error - depends on whether you are in admin or not as to what to do
	 *
	 * @param   array   &$err   Form models error array
	 * @param   string  $field  Name
	 * @param   string  $msg    Message
	 *
	 * @return  void
	 */

	protected function raiseError(&$err, $field, $msg)
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			$app->enqueueMessage($msg, 'notice');
		}
		else
		{
			$err[$field][0][] = $msg;
		}
	}
}

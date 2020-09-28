<?php
/**
 * @version 2: emundusisapplicationsent 2018-12-04 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Locks access to a file if the file is not of a certain status.
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

class PlgFabrik_FormEmunduscompletefilestatuschange extends plgFabrik_Form {


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
	 * @throws Exception
	 */
	public function onBeforeCalculations() {

		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.changeStatusOnProfileChange.php'], JLog::ALL, ['com_emundus']);

		$status = $this->getParam('status');

		// We do a double comparison to manage the case where we would WANT the status to be 0 (empty() would return true).
		if ($status !== '0' && empty($status)) {
			JLog::add('No status provided for plugin.', JLog::ERROR, 'com_emundus');
			return false;
		}

		$jinput = JFactory::getApplication()->input;
		$fnum = $jinput->get->get('rowid');

		if (empty($fnum)) {
			JLog::add('No fnum provided.', JLog::ERROR, 'com_emundus');
			return false;
		}

		include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
		include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

		$m_application = new EmundusModelApplication;
		$m_files = new EmundusModelFiles();

		$fnumInfos = $m_files->getFnumInfos($fnum);

		$attachments = $m_application->getAttachmentsProgress($fnum);
		$forms = $m_application->getFormsProgress($fnum);

		// We need the current status of the fnum.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('status'))
			->from($db->quoteName('#__emundus_campaign_candidature'))
			->where($db->quoteName('fnum').' LIKE '.$db->quote($fnum));
		$db->setQuery($query);
		try {
			$current_status = $db->loadResult();
		} catch (Exception $e) {
			JLog::add('Could not get file status at query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}

		// If our file is complete but not sent, set to our new status.
		if ($current_status == 0 && $forms == 100 && $attachments == 100) {
			$query->clear()->update($db->quoteName('#__emundus_campaign_candidature'))
				->set($db->quoteName('status').' = '.$status)
				->where($db->quoteName('fnum').' LIKE '.$db->quote($fnum));
			$db->setQuery($query);
			try {
				$db->execute();
				return true;
			} catch (Exception $e) {
				JLog::add('Could not update file status at query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
				return false;
			}
		}

		return true;
	}
}

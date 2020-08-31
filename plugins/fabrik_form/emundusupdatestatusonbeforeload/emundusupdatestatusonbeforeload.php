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

class PlgFabrik_FormEmundusupdatestatusonbeforeload extends plgFabrik_Form {


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

		if ($params->get($pname) == '') {
            return $default;
        }

		return $params->get($pname);
	}

	/**
	 * Main script.
	 *
	 * @return  bool
	 * @throws Exception
	 */
	public function onBeforeLoad() {

		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.UpdateStatusOnBeforeLoad.php'], JLog::ALL, ['com_emundus']);

		$jinput = JFactory::getApplication()->input;
		$fnum = $jinput->get->get('rowid');

		if (empty($fnum)) {
			JLog::add('No fnum provided.', JLog::ERROR, 'com_emundus');
			return false;
		}

        $session = JFactory::getSession();
        $user = $session->get('emundusUser');
        $oldStatus = $this->getParam('oldstatus', '');
        $newStatus = $this->getParam('newstatus', '');

        require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
        $m_files = new EmundusModelFiles();

        if (empty($oldStatus)) {
            $m_files->updateState($fnum, $newStatus);
            $user->fnums[$fnum]->status = $newStatus;
            $session->set('emundusUser', $user);
            JLog::add('USER ' . $user->id . ' : Status updated from ' . $user->fnums[$fnum]->status . ' to ' .$newStatus, JLog::INFO, 'com_emundus');
        } else {
            if ($user->fnums[$fnum]->status == $oldStatus) {
                $m_files->updateState($fnum, $newStatus);
                $user->fnums[$fnum]->status = $newStatus;
                $session->set('emundusUser', $user);
                JLog::add('USER ' . $user->id . ' : Status updated from ' . $oldStatus . ' to ' .$newStatus, JLog::INFO, 'com_emundus');
            }
        }
        return true;
	}
}
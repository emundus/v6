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

class PlgFabrik_FormEmundusisevaluationconfirmed extends plgFabrik_Form {


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

		if ($params->get($pname) == '') {
			return '';
		}

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
	public function onLoad() {

		$app = JFactory::getApplication();
		$jinput = $app->input;
		$view = $jinput->get('view');
		$itemid = $jinput->get('Itemid');

		if (!$app->isAdmin() && $view == 'form') {
			require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

			jimport('joomla.log.log');
			JLog::addLogger(['text_file' => 'com_emundus.isEvaluationConfirmed.php'], JLog::ALL, ['com_emundus']);

			$user = JFactory::getSession()->get('emundusUser');
			if (empty($user)) {
				$user = JFactory::getUser();
			}

			$fnum = $jinput->get->get('jos_emundus_evaluations___fnum');
			$rowid = $jinput->get->getInt('rowid');

			if (empty($rowid)) {
				$rowid = $this->getModel()->getRowId();
			}

			if ((!empty($fnum[0]) || !empty($rowid)) && !EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {

				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select($db->quoteName('confirm'))->from($db->quoteName('jos_emundus_evaluations'));

				if (!empty($rowid)) {
					$query->where($db->quoteName('id').' = '.$rowid);
				} else {
					$query->where($db->quoteName('fnum').' LIKE '.$db->quote($fnum[0]).' AND '.$db->quoteName('user').' = '.$user->id);
				}

				try {
					$db->setQuery($query);
					$confirm = $db->loadResult();
				} catch (Exception $e) {
					JLog::add('Error getting confirmation of evaluation -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
					return false;
				}

				$profiles_list = $this->getParam('profile');
				
				if (!empty($confirm) && (empty($profiles_list) || in_array($user->profile, explode(',', $profiles_list)))) {
					$app->enqueueMessage(JText::_('COM_EMUNDUS_EVALUATION_ALREADY_CONFIRMED'), 'info');
					if (!empty($rowid)) {
						$app->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&tmpl=component&iframe=1&rowid=".$rowid);
					} else {
						$app->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&tmpl=component&iframe=1&usekey=fnum&rowid=".$fnum[0]);
					}
				} else {
					$query = "SELECT id FROM jos_emundus_evaluations WHERE user=".$user->id." AND fnum like '{jos_emundus_evaluations___fnum}'";
					$db->setQuery($query);
					$id = $db->loadResult();
					$r = $app->input->get('r', 0);

					if ($id > 0 && $r != 1) {
						$app->redirect('index.php?option=com_fabrik&c=form&view=form&formid='.$jinput->get('formid').'&student_id='.$app->input->get('student_id').'&tmpl=component&iframe=1&rowid='.$id.'&r=1');
					}
				}
			}
		}
		return true;
	}
}
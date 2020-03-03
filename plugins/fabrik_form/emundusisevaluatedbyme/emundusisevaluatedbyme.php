<?php
/**
 * @version 2: emundusisevaluatedbyme 2020-03-03 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2020 emundus.fr. All rights reserved.
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
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.juseremundus
 * @since       3.0
 */
class PlgFabrik_FormEmundusisevaluatedbyme extends plgFabrik_Form {


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

		JLog::addLogger(['text_file' => 'com_emundus.isEvaluatedByMe.error.php'], JLog::ERROR, 'com_emundus');

		$app = JFactory::getApplication();
		$jinput = $app->input;
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();

		$r = $jinput->get->get('r', 0);
		$rowid = $jinput->get->get('rowid');
		$formModel = $this->getModel();
		$formid = $formModel->getId();

		$fnum = $formModel->data['jos_emundus_evaluations___fnum'];

		if (empty($rowid) || !EmundusHelperAccess::asAccessAction(5, 'r', $user->id, $fnum)) {

			$query->select($db->quoteName('id'))
				->from($db->quoteName('jos_emundus_evaluations'))
				->where($db->quoteName('user').' = '.$user->id.' AND '.$db->quoteName('fnum').' LIKE '.$db->quote($fnum));

			try {
				$db->setQuery($query);
				$id = $db->loadResult();
			}
			catch (Exception $e) {
				JLog::add('Error getting user evaluation : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			}

			if (!empty($id) && $r != 1) {
				$app->redirect('index.php?option=com_fabrik&c=form&view=form&formid='.$formid.'&tmpl=component&iframe=1&rowid='.$id.'&r=1');
			}
		}
		return true;
	}
}
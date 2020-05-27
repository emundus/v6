<?php
/**
 * @copyright	Copyright (C) 2020 eMundus
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Site
 * @subpackage	mod_emundus_tutorial
 * @since		1.5
 */
class modEmundusTutorialHelper {

	// Initialize class variables
	var $user = null;

	public function __construct() {

		// Load class variables
		$this->user = JFactory::getUser();

		jimport('joomla.log.log');
		JLog::addLogger(array('text_file' => 'mod_emundus_tutorial.php'), JLog::ALL, array('mod_emundus_tutorial'));

	}

	/**
	 * @param         $mid
	 * @param   null  $user_param
	 *
	 * @return stdClass
	 *
	 * @since version
	 */
	public function getUserParamCondition($mid, $user_param = null) {

		$return = new stdClass();

		// If we are displaying this for the first time, we need to specify, so we can change the conditions (the presence of the param indicates to NOT display the tooltip).
		if (empty($user_param)) {
			$user_param = 'tooltip'.$mid;
			$return->load_once = true;
		} else {
			$return->load_once = false;
		}

		$table = JTable::getInstance('user', 'JTable');
		$table->load($this->user->id);
		$user_params = new JRegistry($table->params);

		$return->name = $user_param;
		$return->value = $user_params->get($user_param, false);

		return $return;
	}


	/**
	 * @param $artids
	 *
	 * @return array|bool|mixed
	 *
	 * @since version
	 */
	public function getArticles($artids) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName(['id', 'title', 'introtext']))
			->from($db->quoteName('#__content'))
			->where($db->quoteName('id').' IN ('.$artids.')');
		$db->setQuery($query);
		try {
			return $db->loadAssocList();
		} catch (Exception $e) {
			JLog::add('Error getting articles : '.$e->getMessage(), JLog::ERROR, 'mod_emundus_tutorial');
			return false;
		}
	}

	/**
	 *
	 *
	 * @since version
	 */
	static function markReadAjax() {

		$jinput = JFactory::getApplication()->input;
		$user = JFactory::getUser();

		$param = $jinput->post->get('param');
		$paramType = $jinput->post->getBool('paramType', true);
		// paramType true = add the param and set to true, otherwise its an existing param that must be set to false.

		$table = JTable::getInstance('user', 'JTable');
		$table->load($user->id);

		// Store token in User's Parameters
		$user->setParam($param, $paramType);

		// Get the raw User Parameters
		$params = $user->getParameters();

		// Set the user table instance to include the new token.
		$table->params = $params->toString();

		// Save user data
		if (!$table->store()) {
			JLog::add('Error saving params : '.$table->getError(), JLog::ERROR, 'mod_emundus_tutorial');
		}
	}
}

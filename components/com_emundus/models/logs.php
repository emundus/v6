<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU/GPL
 * @author      Hugo Moracchini
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class EmundusModelLogs extends JModelList {

	/**
	 * EmundusModelLogs constructor.
	 * @since 3.8.8
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Writes a log entry of the action to/from the user.
	 * @param int $user_from
	 * @param int $user_to
	 * @param string $fnum
	 * @param int $action
	 * @param string $crud
	 * @param string $message
	 *
	 * @since 3.8.8
	 */
	static function log($user_from, $user_to, $fnum, $action, $crud, $message = '') {

		if (empty($user_to))
			$user_to = '';

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$columns = ['user_id_from',  'user_id_to', 'fnum_to', 'action_id', 'verb', 'message'];
		$values = [$user_from, $user_to, $db->quote($fnum), $action, $db->quote($crud), $db->quote($message)];

		$query->insert($db->quoteName('#__emundus_logs'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		$db->setQuery($query);

		try {
			$db->execute();
		} catch (Exception $e) {
			JLog::add('Error logging at the following query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
		}

	}

}

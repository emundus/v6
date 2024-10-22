<?php
/**
 * @package		Joomla
 * @subpackage	eMundus
 * @copyright	Copyright (C) 2020 emundus.fr. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$session = JFactory::getSession();
$user = JFactory::getUser();

$table = JTable::getInstance('user', 'JTable');
$table->load($user->id);

$user_params = new JRegistry($table->params);
$yousignSession = JFactory::getSession()->get('YousignSession');

if (!empty($yousignSession)) {
	$db = JFactory::getDBO();
	$query = $db->getQuery(true);

	$query->clear()
		->select('jefr.signed_file, jefr.id')
		->from($db->quoteName('jos_emundus_files_request', 'jefr'))
		->leftJoin($db->quoteName('#__users', 'ju') . ' ON ju.email = jefr.email')
		->where('jefr.signed_file LIKE "%.pdf%"')
		->andWhere('ju.id = ' . $user->id);

	$db->setQuery($query);
	$signed_request = $db->loadAssoc();

	if (!empty($signed_request['id'])) {
		$query->clear()
			->update('#__users')
			->set('params = "{}"')
			->where('id = ' . $db->quote($user->id));

		$db->setQuery($query);
		$db->execute();

		// set values of current file request
		$query->clear()
			->select('signed_file, signer_id, keyid, filename, yousign_document_id')
			->from('jos_emundus_files_request')
			->where('id = ' . $signed_request['id']);

		try {
			$db->setQuery($query);
			$user_signed_file_request_infos = $db->loadAssoc();
		} catch(Exception $e) {
			var_dump($e->getMessage());
		}

		if (!empty($user_signed_file_request_infos)) {
			$query->clear()
				->update('jos_emundus_files_request')
				->set('signed_file = ' . $db->quote($user_signed_file_request_infos['signed_file']))
				->set('signer_id = ' . $db->quote($user_signed_file_request_infos['signer_id']))
				->set('keyid = ' . $db->quote($user_signed_file_request_infos['keyid']))
				->set('filename = ' . $db->quote($user_signed_file_request_infos['filename']))
				->set('yousign_document_id = ' . $db->quote($user_signed_file_request_infos['yousign_document_id']))
				->where('email LIKE ' . $db->quote($user->email));

			try {
				$db->setQuery($query);
				$db->execute();
			} catch (Exception $e) {
				var_dump('query failed' . $e->getMessage());
			}
			$yousignSession = [];

			JFactory::getSession()->set('YousignSession', null);
			JFactory::getApplication()->redirect('/');
		}
	}
}

if (!empty($yousignSession['iframe_url'])) {
    require JModuleHelper::getLayoutPath('mod_emundus_yousign_embed', 'default');
}
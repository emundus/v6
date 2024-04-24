<?php
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;

class PlgExtensionEmundus extends CMSPlugin
{
	public function onExtensionAfterSave($context, $table, $isNew)
	{
		// Check that we're modifying the correct component.
		if ($context !== 'com_config.component' || $table->element !== 'com_emundus') {
			return;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		// New component params.
		$params = new Registry($table->params);

		require_once JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php';
		$payment_activated = $params->get('application_fee');
		if ($payment_activated) {
			$removed = EmundusHelperUpdate::removeFromFile(JPATH_ROOT . '/.htaccess', ['php_value session.cookie_samesite Lax' . PHP_EOL]);
			if ($removed) {
				Factory::getApplication()->enqueueMessage(JText::_('PLG_EXTENSION_EMUNDUS_SAMESITE_REMOVED'));
			}
		}
		else {
			$inserted = EmundusHelperUpdate::insertIntoFile(JPATH_ROOT . '/.htaccess', "php_value session.cookie_samesite Lax" . PHP_EOL);
			if ($inserted) {
				Factory::getApplication()->enqueueMessage(JText::_('PLG_EXTENSION_EMUNDUS_SAMESITE_INSERTED'));
			}
		}

		// Sync exception
		$id_applicant = $params->get('id_applicants', '');

		$ids = explode(',', $id_applicant);

		$query->select('user')
			->from($db->quoteName('#__emundus_setup_exceptions'));
		$db->setQuery($query);
		$ids_db = $db->loadColumn();

		$ids_to_add = array_diff($ids, $ids_db);

		foreach ($ids_to_add as $id) {
			if(!empty($id)) {
				$columns = [
					'date_time',
					'user'
				];
				$values  = [
					$db->quote(date('Y-m-d H:i:s')),
					$id
				];

				$query->clear()
					->insert($db->quoteName('#__emundus_setup_exceptions'))
					->columns($db->quoteName($columns))
					->values(implode(',', $values));
				$db->setQuery($query);
				$db->execute();
			}
		}

		$ids_to_delete = array_diff($ids_db, $ids);
		foreach ($ids_to_delete as $id) {
			$query->clear()
				->delete($db->quoteName('#__emundus_setup_exceptions'))
				->where($db->quoteName('user') . ' = ' . $id);
			$db->setQuery($query);
			$db->execute();
		}

		if (!empty($ids_to_add) || !empty($ids_to_delete)) {
			Factory::getApplication()->enqueueMessage(JText::_('PLG_EXTENSION_EMUNDUS_EXCEPTIONS_SYNCED'));
		}
	}
}
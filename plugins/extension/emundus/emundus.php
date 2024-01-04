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

		// New component params.
		$params = new Registry($table->params);

		require_once JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php';
		$payment_activated = $params->get('application_fee');
		if ($payment_activated) {
			$removed = EmundusHelperUpdate::removeFromFile(JPATH_ROOT . '/.htaccess', ['php_value session.cookie_samesite Lax' . PHP_EOL]);
			if ($removed) {
				Factory::getApplication()->enqueueMessage(JText::_('PLG_EXTENSION_EMUNDUS_SAMESITE_REMOVED'));
			}
		} else {
			$inserted = EmundusHelperUpdate::insertIntoFile(JPATH_ROOT . '/.htaccess', "php_value session.cookie_samesite Lax" . PHP_EOL);
			if ($inserted) {
				Factory::getApplication()->enqueueMessage(JText::_('PLG_EXTENSION_EMUNDUS_SAMESITE_INSERTED'));
			}
		}
	}
}
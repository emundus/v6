<?php
defined('_JEXEC') or die;

$user = JFactory::getSession()->get('emundusUser');
$app = JFactory::getApplication();

if (!empty($user) && EmundusHelperAccess::asAccessAction(1, 'r', $user->id)) {
	if (!empty($params)) {
		$layout = $params->get('layout', '');
		$fabrik_element_id = $params->get('element_id', 0);

		if (!empty($fabrik_element_id)) {
			require_once JPATH_ROOT . '/components/com_emundus/models/filters.php';
			$m_filters = new EmundusModelFilters(['element_id' => $fabrik_element_id]);

			$filters = $m_filters->getFilters();
			$applied_filters = $m_filters->getAppliedFilters();

			require JModuleHelper::getLayoutPath('mod_emundus_filters', $layout);
		}
	}
} else {
	$app->enqueueMessage(JText::_('ACCESS_DENIED'));
	$app->redirect('/');
}
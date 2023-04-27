<?php
defined('_JEXEC') or die;

$user = JFactory::getSession()->get('emundusUser');
$app = JFactory::getApplication();

if (!empty($user) && EmundusHelperAccess::asAccessAction(1, 'r', $user->id)) {
	if (!empty($params)) {
		$layout = $params->get('layout', '');
		$filter_on_fnums = $params->get('filter_on_fnums', 0);

		if ($filter_on_fnums == 1) {
			require_once JPATH_ROOT . '/components/com_emundus/classes/filters/EmundusFiltersFiles.php';

			try {
				$m_filters = new EmundusFiltersFiles($params->toArray());
			} catch (Exception $e) {
				$app->enqueueMessage($e->getMessage());
				$app->redirect('/');
			}

		} else {
			$fabrik_element_id = $params->get('element_id', 0);
			if (!empty($fabrik_element_id)) {
				require_once JPATH_ROOT . '/components/com_emundus/classes/filters/EmundusFilters.php';

				try {
					$m_filters = new EmundusFilters(['element_id' => $fabrik_element_id]);
				} catch (Exception $e) {
					$app->enqueueMessage($e->getMessage());
					$app->redirect('/');
				}
			} else {
				$app->enqueueMessage(JText::_('MOD_EM_FILTER_FABRIK_MISSING_CONFIGURATION'));
			}
		}

		if (!empty($m_filters)) {
			$filters = $m_filters->getFilters();
			$applied_filters = $m_filters->getAppliedFilters();

			require JModuleHelper::getLayoutPath('mod_emundus_filters', $layout);
		}
	}
} else {
	$app->enqueueMessage(JText::_('ACCESS_DENIED'));
	$app->redirect('/');
}
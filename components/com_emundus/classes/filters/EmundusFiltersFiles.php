<?php
require_once(JPATH_ROOT . '/components/com_emundus/classes/filters/EmundusFilters.php');

class EmundusFiltersFiles extends EmundusFilters
{
	private $profiles = [];

	public function __construct($config = array())
	{
		JLog::addLogger(['text_file' => 'com_emundus.filters.php'], JLog::ALL, 'com_emundus.filters');

		$this->user = JFactory::getUser();

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->user->id) || !EmundusHelperAccess::asAccessAction(1, 'r', $this->user->id)) {
			throw new Exception('Access denied', 403);
		}

		$this->setProfiles();
		$this->setFilters();

		$session_filters = JFactory::getSession()->get('applied_filters');
		if (!empty($session_filters)) {
			$this->setAppliedFilters($session_filters);
		}
	}

	private function setProfiles()
	{
		$campaign_ids = EmundusHelperAccess::getAllCampaignsAssociatedToUser($this->user->id);
		if (!empty($campaign_ids)) {
			$profile_ids = [];

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			// profiles from campaigns
			$query->select('DISTINCT profile_id')
				->from('#__emundus_setup_campaigns')
				->where('id IN ('. implode(',', $db->quote($campaign_ids)) .')');

			$db->setQuery($query);
			$profiles = $db->loadColumn();
			foreach ($profiles as $profile) {
				if (!in_array($profile, $profile_ids)) {
					$profile_ids[] = $profile;
				}
			}

			// profiles from workflows
			require_once (JPATH_SITE.'/components/com_emundus/models/campaign.php');
			$m_campaign = new EmundusModelCampaign();
			$workflows = $m_campaign->getWorkflows($campaign_ids);

			foreach ($workflows as $workflow) {
				if (!in_array($workflow->profile_id, $profile_ids)) {
					$profile_ids[] = $workflow->profile_id;
				}
			}

			$this->profiles = $profile_ids;
		}
	}

	private function getProfiles()
	{
		return $this->profiles;
	}

	protected function setFilters() {
		$elements = $this->getAllAssociatedElements();
		$this->createFiltersFromFabrikElements($elements);
	}

	protected function getAllAssociatedElements()
	{
		$elements = [];
		$profiles = $this->getProfiles();

		if (!empty($profiles)) {
			$menus = [];
			foreach ($profiles as $profile) {
				$menus[] = 'menu-profile'. $profile;
			}

			// get all forms associated to the user's profiles
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('link')
				->from('#__menu')
				->where('menutype IN ('. implode(',', $db->quote($menus)) .')')
				->andWhere('link LIKE "index.php?option=com_fabrik&view=form&formid=%"')
				->andWhere('published = 1');

			$db->setQuery($query);
			$form_links = $db->loadColumn();

			if (!empty($form_links)) {
				$form_ids = [];
				foreach ($form_links as $link) {
					$form_ids[] = (int) str_replace('index.php?option=com_fabrik&view=form&formid=', '', $link);
				}
				$form_ids = array_unique($form_ids);

				$query->clear()
					->select('jfe.id, jfe.plugin, jfe.label, jfe.params, jffg.form_id')
					->from('jos_fabrik_elements as jfe')
					->join('inner', 'jos_fabrik_formgroup as jffg ON jfe.group_id = jffg.group_id')
					->where('jffg.form_id IN (' . implode(',', $form_ids) . ')')
					->andWhere('published = 1')
					->andWhere('hidden = 0');

				try {
					$db->setQuery($query);
					$elements = $db->loadAssocList();

					foreach ($elements as $key => $element) {
						$elements[$key][label] = JText::_($element[label]) . ' (' . $element[form_id] . ')';
					}
				} catch (Exception $e) {
					JLog::add('Failed to get elements associated to profiles that current user can access : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filters.error');
				}
			}
		}

		return $elements;
	}
}
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
		$this->setDefaultFilters($config);

		$session_filters = JFactory::getSession()->get('em-applied-filters', null);
		if (!empty($session_filters)) {
			$this->addSessionFilters($session_filters);
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
		$this->filters = $this->createFiltersFromFabrikElements($elements);
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
					->select('jfe.id, jfe.plugin, jfe.label, jfe.params, jffg.form_id as element_form_id, jff.label as element_form_label')
					->from('jos_fabrik_elements as jfe')
					->join('inner', 'jos_fabrik_formgroup as jffg ON jfe.group_id = jffg.group_id')
					->join('inner', 'jos_fabrik_forms as jff ON jffg.form_id = jff.id')
					->where('jffg.form_id IN (' . implode(',', $form_ids) . ')')
					->andWhere('jfe.published = 1')
					->andWhere('jfe.hidden = 0');

				try {
					$db->setQuery($query);
					$elements = $db->loadAssocList();

					foreach ($elements as $key => $element) {
						$elements[$key]['label'] = JText::_($element['label']);
						$elements[$key]['element_form_label'] = JText::_($element['element_form_label']);
					}
				} catch (Exception $e) {
					JLog::add('Failed to get elements associated to profiles that current user can access : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filters.error');
				}
			}
		}

		return $elements;
	}

	private function setDefaultFilters($config) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		if ($config['filter_status']) {
			$query->select('id, step, value')
				->from('#__emundus_setup_status');

			$db->setQuery($query);
			$statuses = $db->loadObjectList();

			$values = [];
			foreach($statuses as $status) {
				$values[] = ['value' => $status->step, 'label' => $status->value];
			}

			$this->applied_filters[] = [
				'uid' => 'status',
				'id' => 'status',
				'label' => JText::_('MOD_EMUNDUS_FILTERS_STATUS'),
				'type' => 'select',
				'values' => $values,
				'value' => ['all'],
				'default' => true
			];
		}

		if ($config['filter_campaign']) {
			$query->clear()
				->select('id as value, label')
				->from('#__emundus_setup_campaigns')
				->where('published = 1');

			$db->setQuery($query);
			$campaigns = $db->loadAssocList();

			$this->applied_filters[] = [
				'uid' => 'campaigns',
				'id' => 'campaigns',
				'label' => JText::_('MOD_EMUNDUS_FILTERS_CAMPAIGNS'),
				'type' => 'select',
				'values' => $campaigns,
				'value' => ['all'],
				'default' => true
			];
		}

		if ($config['filter_programs']) {
			$query->clear()
				->select('id as value, label')
				->from('#__emundus_setup_programmes')
				->where('published = 1');

			$db->setQuery($query);
			$programs = $db->loadAssocList();

			$this->applied_filters[] = [
				'uid' => 'programs',
				'id' => 'programs',
				'label' => JText::_('MOD_EMUNDUS_FILTERS_PROGRAMS'),
				'type' => 'select',
				'values' => $programs,
				'value' => ['all'],
				'default' => true
			];
		}

		if ($config['filter_years']) {
			$query->clear()
				->select('DISTINCT schoolyear as value, schoolyear as label')
				->from('#__emundus_setup_teaching_unity')
				->where('published = 1');

			$db->setQuery($query);
			$years = $db->loadAssocList();

			$this->applied_filters[] = [
				'uid' => 'years',
				'id' => 'years',
				'label' => JText::_('MOD_EMUNDUS_FILTERS_YEARS'),
				'type' => 'select',
				'values' => $years,
				'value' => ['all'],
				'default' => true
			];
		}

		if ($config['filter_tags']) {
			$query->clear()
				->select('id as value, label')
				->from('#__emundus_setup_action_tag');

			$db->setQuery($query);
			$tags = $db->loadAssocList();

			$this->applied_filters[] = [
				'uid' => 'tags',
				'id' => 'tags',
				'label' => JText::_('MOD_EMUNDUS_FILTERS_TAGS'),
				'type' => 'select',
				'values' => $tags,
				'value' => ['all'],
				'default' => true
			];
		}

		if ($config['filter_published']) {
			$this->applied_filters[] = [
				'uid' => 'published',
				'id' => 'published',
				'label' => JText::_('MOD_EMUNDUS_FILTERS_PUBLISHED_STATE'),
				'type' => 'select',
				'values' => [
					['value' => 1, 'label' => JText::_('MOD_EMUNDUS_FILTERS_VALUE_PUBLISHED')],
					['value' => 0, 'label' => JText::_('MOD_EMUNDUS_FILTERS_VALUE_ARCHIVED')],
					['value' => -1, 'label' => JText::_('MOD_EMUNDUS_FILTERS_VALUE_DELETED')]
				],
				'value' => [1],
				'default' => true
			];
		}

		if(!empty($config['more_filter_elements'])) {
			$config['more_filter_elements'] = json_decode($config['more_filter_elements'], true);

			foreach($config['more_filter_elements']['fabrik_element_id'] as $key => $fabrik_element_id) {
				// check in filters if element is already present
				$found = false;
				$new_default_filter = [];
				foreach($this->filters as $filter) {
					if($filter['id'] == $fabrik_element_id) {
						$new_default_filter = $filter;
						$new_default_filter['default'] = true;
						if (empty($new_default_filter['value'])) {
							$new_default_filter['value'] = $new_default_filter['type'] === 'select' ? ['all'] : '';
						}
						$new_default_filter['andorOperator'] = 'OR';
						$new_default_filter['operator'] = $filter['type'] === 'select' ? 'LIKE' : '=';

						$found = true;
						break;
					}
				}

				if (!$found) {
					$query->clear()
						->select('jfe.id, jfe.plugin, jfe.label, jfe.params, jffg.form_id as element_form_id, jff.label as element_form_label')
						->from('jos_fabrik_elements as jfe')
						->join('inner', 'jos_fabrik_formgroup as jffg ON jfe.group_id = jffg.group_id')
						->join('inner', 'jos_fabrik_forms as jff ON jffg.form_id = jff.id')
						->where('jfe.id = ' . $fabrik_element_id)
						->andWhere('jfe.published = 1');

					$db->setQuery($query);
					$element = $db->loadAssoc();

					if (!empty($element)) {
						$element['label'] = JText::_($element['label']);
						$element['element_form_label'] = JText::_($element['element_form_label']);
						$formatted_elements = $this->createFiltersFromFabrikElements([$element]);

						if (!empty($formatted_elements)) {
							$new_default_filter = $formatted_elements[0];
							$new_default_filter['default'] = true;
							if (empty($new_default_filter['value'])) {
								$new_default_filter['value'] = $new_default_filter['type'] === 'select' ? ['all'] : '';
							}
							$new_default_filter['andorOperator'] = 'OR';
							$new_default_filter['operator'] = $new_default_filter['type'] === 'select' ? 'LIKE' : '=';
						}
					}
				}

				if (!empty($new_default_filter)) {
					$this->filters[] = $new_default_filter;
					$new_default_filter['uid'] = 'default-filter-' . $new_default_filter['id'];
					$this->applied_filters[] = $new_default_filter;
				}
			}
		}
	}

	private function addSessionFilters($session_values)
	{
		foreach($session_values as $session_filter) {
			$found = false;
			foreach ($this->applied_filters as $key => $applied_filter) {
				if ($applied_filter['uid'] == $session_filter['uid']) {
					$this->applied_filters[$key]['value'] = $session_filter['value'];
					$this->applied_filters[$key]['operator'] = $session_filter['operator'];
					$this->applied_filters[$key]['andorOperator'] = $session_filter['andorOperator'];

					$found = true;
					break;
				}
			}

			if (!$found) {
				// find filter in filters
				foreach ($this->filters as $filter) {
					if ($filter['id'] == $session_filter['id']) {
						$new_filter = $filter;
						$new_filter['value'] = $session_filter['value'];
						$new_filter['operator'] = $session_filter['operator'];
						$new_filter['andorOperator'] = $session_filter['andorOperator'];
						$new_filter['uid'] = $session_filter['uid'];
						$this->applied_filters[] = $new_filter;
						break;
					}
				}
			}
		}
	}
}
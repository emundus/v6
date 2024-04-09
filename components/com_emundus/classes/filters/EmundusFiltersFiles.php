<?php
require_once(JPATH_ROOT . '/components/com_emundus/classes/filters/EmundusFilters.php');
require_once(JPATH_ROOT . '/components/com_emundus/models/users.php');
require_once(JPATH_ROOT . '/components/com_emundus/helpers/cache.php');

class EmundusFiltersFiles extends EmundusFilters
{
	private $profiles = [];
	private $user_campaigns = [];
	private $user_programs = [];
	private $config = [];
	private $m_users = null;
	private $menu_params = null;
	private $h_cache = null;

	public function __construct($config = array(), $skip = false)
	{
		JLog::addLogger(['text_file' => 'com_emundus.filters.php'], JLog::ALL, 'com_emundus.filters');
		$this->user = JFactory::getUser();

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->user->id) || !EmundusHelperAccess::asAccessAction(1, 'r', $this->user->id))
		{
			throw new Exception('Access denied', 403);
		}

		$this->h_cache        = new EmundusHelperCache();
		$this->m_users        = new EmundusModelUsers();
		$this->config         = $config;
		$this->user_campaigns = $this->m_users->getAllCampaignsAssociatedToUser($this->user->id);
		$this->user_programs  = $this->m_users->getUserGroupsProgrammeAssoc($this->user->id, 'jesp.id');

		if (!$skip)
		{
			$this->setMenuParams();
			$this->setProfiles();
			$this->setDefaultFilters($config);
			$this->setFilters();

			$session_filters = JFactory::getSession()->get('em-applied-filters', null);
			if (!empty($session_filters))
			{
				$this->addSessionFilters($session_filters);
				$this->checkFiltersAvailability();
			}

			$quick_search_filters = JFactory::getSession()->get('em-quick-search-filters', null);
			if (!empty($quick_search_filters))
			{
				$this->setQuickSearchFilters($quick_search_filters);
			}

			$this->saveFiltersAllValues();

			if ($this->config['count_filter_values'])
			{
				require_once JPATH_ROOT . '/components/com_emundus/helpers/files.php';
				$helper_files          = new EmundusHelperFiles();
				$this->applied_filters = $helper_files->setFiltersValuesAvailability($this->applied_filters);
			}
		}
	}

	private function setMenuParams()
	{
		$menu   = JFactory::getApplication()->getMenu();
		$active = $menu->getActive();
		if (!empty($active))
		{
			$this->menu_params = $active->params;
		}
		else
		{
			// get default file menu of current user profile
			$profile_id = $this->m_users->getCurrentUserProfile($this->user->id);

			if (!empty($profile_id))
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->select('menu.id')
					->from($db->quoteName('#__menu', 'menu'))
					->leftJoin($db->quoteName('#__emundus_setup_profiles', 'profile') . ' ON ' . $db->quoteName('profile.menutype') . ' = ' . $db->quoteName('menu.menutype'))
					->where('profile.id = ' . $db->quote($profile_id))
					->andWhere('menu.published = 1')
					->andWhere('menu.link LIKE "%index.php?option=com_emundus&view=files%"');

				$db->setQuery($query);
				$menu_id = $db->loadResult();

				// get menu params
				if (!empty($menu_id))
				{
					$menu              = $menu->getItem($menu_id);
					$this->menu_params = $menu->params;
				}
			}
		}

		if (empty($this->menu_params))
		{
			throw new Exception('Menu params not found', 404);
		}
	}

	private function setProfiles()
	{
		if (!empty($this->user_campaigns))
		{
			$this->profiles = $this->getProfilesFromCampaignId($this->user_campaigns);
		}
	}

	private function getProfilesFromCampaignId($campaign_ids)
	{
		$profile_ids = [];

		if (!empty($campaign_ids))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// profiles from campaigns
			$query->select('DISTINCT profile_id')
				->from('#__emundus_setup_campaigns')
				->where('id IN (' . implode(',', $db->quote($campaign_ids)) . ')');

			$db->setQuery($query);
			$profiles = $db->loadColumn();
			foreach ($profiles as $profile)
			{
				if (!in_array($profile, $profile_ids))
				{
					$profile_ids[] = $profile;
				}
			}

			// profiles from workflows
			require_once(JPATH_SITE . '/components/com_emundus/models/campaign.php');
			$m_campaign = new EmundusModelCampaign();
			$workflows  = $m_campaign->getWorkflows($campaign_ids);

			foreach ($workflows as $workflow)
			{
				if (!in_array($workflow->profile_id, $profile_ids))
				{
					$profile_ids[] = $workflow->profile_id;
				}
			}
		}

		return $profile_ids;
	}

	private function getProfiles()
	{
		return $this->profiles;
	}

	protected function setFilters()
	{
		$elements      = $this->getAllAssociatedElements();
		$this->filters = $this->createFiltersFromFabrikElements($elements);
	}

	protected function getAllAssociatedElements($element_id = null)
	{
		$elements         = [];
		$profiles         = $this->getProfiles();
		$profile_form_ids = [];
		$config_form_ids  = [];

		if (!empty($profiles))
		{
			// get all forms associated to the user's profiles
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('menu.link')
				->from($db->quoteName('#__menu', 'menu'))
				->leftJoin($db->quoteName('#__emundus_setup_profiles', 'profile') . ' ON ' . $db->quoteName('profile.menutype') . ' = ' . $db->quoteName('menu.menutype'))
				->where('profile.id IN (' . implode(',', $db->quote($profiles)) . ')')
				->andWhere('profile.published = 1')
				->andWhere('menu.link LIKE "index.php?option=com_fabrik&view=form&formid=%"')
				->andWhere('menu.published = 1');

			$db->setQuery($query);
			$form_links = $db->loadColumn();

			if (!empty($form_links))
			{
				foreach ($form_links as $link)
				{
					$profile_form_ids[] = (int) str_replace('index.php?option=com_fabrik&view=form&formid=', '', $link);
				}
			}
		}

		if (!empty($this->config) && !empty($this->config['more_fabrik_forms']))
		{
			$config_form_ids = $this->config['more_fabrik_forms'];
		}

		$form_ids = array_merge($profile_form_ids, $config_form_ids);

		return $this->getElementsFromFabrikForms($form_ids);
	}

	private function getElementsFromFabrikForms($form_ids)
	{
		$elements = [];
		$form_ids = array_unique($form_ids);

		if (!empty($form_ids))
		{
			if ($this->h_cache->isEnabled())
			{
				foreach ($form_ids as $key => $form_id)
				{
					$cache_key      = 'elements_from_form_' . $form_id;
					$cache_elements = $this->h_cache->get($cache_key);

					if (!empty($cache_elements))
					{
						$elements = array_merge($elements, $cache_elements);
						unset($form_ids[$key]);
					}
				}
			}

			if (!empty($form_ids))
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->clear()
					->select('jfe.id, jfe.plugin, jfe.label, jfe.params, jffg.form_id as element_form_id, jff.label as element_form_label')
					->from('jos_fabrik_elements as jfe')
					->join('inner', 'jos_fabrik_formgroup as jffg ON jfe.group_id = jffg.group_id')
					->join('inner', 'jos_fabrik_forms as jff ON jffg.form_id = jff.id')
					->where('jffg.form_id IN (' . implode(',', $form_ids) . ')')
					->andWhere('jfe.published = 1')
					->andWhere('jfe.hidden = 0');

				try
				{
					$db->setQuery($query);
					$query_elements = $db->loadAssocList();
					$elements       = array_merge($elements, $query_elements);

					foreach ($elements as $key => $element)
					{
						$elements[$key]['label']              = JText::_($element['label']);
						$elements[$key]['element_form_label'] = JText::_($element['element_form_label']);
					}

					if ($this->h_cache->isEnabled())
					{
						$elements_by_form = [];
						foreach ($elements as $element)
						{
							if (!isset($elements_by_form[$element['element_form_id']]))
							{
								$elements_by_form[$element['element_form_id']] = [];
							}
							$elements_by_form[$element['element_form_id']][] = $element;
						}

						foreach ($elements_by_form as $form_id => $element_by_form)
						{
							$this->h_cache->set('elements_from_form_' . $form_id, $element_by_form);
						}
					}
				}
				catch (Exception $e)
				{
					JLog::add('Failed to get elements associated to profiles that current user can access : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filters.error');
				}
			}
		}

		return $elements;
	}

	private function setDefaultFilters($config)
	{
		$found_from_cache = false;

		if ($this->h_cache->isEnabled())
		{
			$menu = JFactory::getApplication()->getMenu();

			if (!empty($menu))
			{
				$active_menu = $menu->getActive();
				if (!empty($active_menu))
				{
					$cache_default_filters = $this->h_cache->get('em_default_filters_' . $active_menu->id);

					if (!empty($cache_default_filters))
					{
						$this->applied_filters = array_merge($this->applied_filters, $cache_default_filters);
						$found_from_cache      = true;
					}
				}
			}
		}

		if (!$found_from_cache)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$filter_menu_values           = $this->menu_params->get('em_filters_values', '');
			$filter_menu_values           = explode(',', $filter_menu_values);
			$filter_menu_values_are_empty = empty($filter_menu_values);
			$filter_names                 = [];

			if (!$filter_menu_values_are_empty)
			{
				$filter_names = $this->menu_params->get('em_filters_names', '');
				$filter_names = explode(',', $filter_names);
			}

			if ($config['filter_status'])
			{
				$query->select('id, step, value, 0 as count')
					->from('#__emundus_setup_status');

				if (!$filter_menu_values_are_empty)
				{
					$position = array_search('status', $filter_names);

					if ($position !== false && isset($filter_menu_values[$position]))
					{
						$statuses = explode('|', $filter_menu_values[$position]);
						$query->where('step IN (' . implode(',', $statuses) . ')');
					}
				}

				$query->order('ordering ASC');

				$db->setQuery($query);
				$statuses = $db->loadObjectList();

				$values = [];
				foreach ($statuses as $status)
				{
					$values[] = ['value' => $status->step, 'label' => $status->value];
				}

				$this->applied_filters[] = [
					'uid'       => 'status',
					'id'        => 'status',
					'label'     => JText::_('MOD_EMUNDUS_FILTERS_STATUS'),
					'type'      => 'select',
					'values'    => $values,
					'value'     => ['all'],
					'default'   => true,
					'available' => true,
					'order'     => $config['filter_status_order']
				];
			}

			if ($config['filter_programs'])
			{
				$programs       = [];
				$programs_codes = [];

				if (!empty($this->user_programs))
				{
					$query->clear()
						->select('id as value, label, 0 as count')
						->from('#__emundus_setup_programmes')
						->where('published = 1')
						->andWhere('id IN (' . implode(',', $this->user_programs) . ')');

					if (!$filter_menu_values_are_empty)
					{
						$position = array_search('programme', $filter_names);

						if ($position !== false && !empty($filter_menu_values[$position]))
						{
							$programs_codes = explode('|', $filter_menu_values[$position]);
							if (!empty($programs_codes))
							{
								$query->where('code IN (' . implode(',', $db->quote($programs_codes)) . ')');
							}
						}
					}

					$query->order('ordering ASC');

					$db->setQuery($query);
					$programs = $db->loadAssocList();
				}

				$this->applied_filters[] = [
					'uid'       => 'programs',
					'id'        => 'programs',
					'label'     => JText::_('MOD_EMUNDUS_FILTERS_PROGRAMS'),
					'type'      => 'select',
					'values'    => $programs,
					'value'     => ['all'],
					'default'   => true,
					'available' => true,
					'order'     => $config['filter_programs_order']
				];
			}

			if ($config['filter_campaign'])
			{
				$campaigns = [];

				if (!empty($this->user_campaigns))
				{
					$query->clear()
						->select('id as value, label, 0 as count')
						->from('#__emundus_setup_campaigns')
						->where('published = 1')
						->andWhere('id IN (' . implode(',', $this->user_campaigns) . ')');

					if (!$filter_menu_values_are_empty)
					{
						$position = array_search('campaign', $filter_names);

						if ($position !== false && !empty($filter_menu_values[$position]))
						{
							$campaigns = explode('|', $filter_menu_values[$position]);
							if (!empty($campaigns))
							{
								$query->andWhere('id IN (' . implode(',', $campaigns) . ')');
							}
						}
					}

					if (!empty($programs_codes))
					{
						$query->andWhere('training IN (' . implode(',', $db->quote($programs_codes)) . ')');
					}

					$query->order('id DESC');

					$db->setQuery($query);
					$campaigns = $db->loadAssocList();
				}

				$this->applied_filters[] = [
					'uid'       => 'campaigns',
					'id'        => 'campaigns',
					'label'     => JText::_('MOD_EMUNDUS_FILTERS_CAMPAIGNS'),
					'type'      => 'select',
					'values'    => $campaigns,
					'value'     => ['all'],
					'default'   => true,
					'available' => true,
					'order'     => $config['filter_campaigns_order']
				];
			}


			if ($config['filter_years'])
			{
				$years = [];

				if (!empty($this->user_campaigns))
				{
					$query->clear()
						->select('DISTINCT year as value, year as label, 0 as count')
						->from('#__emundus_setup_campaigns')
						->where('published = 1')
						->andWhere('id IN (' . implode(',', $this->user_campaigns) . ')');

					$db->setQuery($query);
					$years = $db->loadAssocList();
				}

				$this->applied_filters[] = [
					'uid'       => 'years',
					'id'        => 'years',
					'label'     => JText::_('MOD_EMUNDUS_FILTERS_YEARS'),
					'type'      => 'select',
					'values'    => $years,
					'value'     => ['all'],
					'default'   => true,
					'available' => true,
					'order'     => $config['filter_years_order']
				];
			}

			if ($config['filter_tags'])
			{
				$query->clear()
					->select('id as value, label, 0 as count')
					->from('#__emundus_setup_action_tag');

				$db->setQuery($query);
				$tags = $db->loadAssocList();

				$this->applied_filters[] = [
					'uid'       => 'tags',
					'id'        => 'tags',
					'label'     => JText::_('MOD_EMUNDUS_FILTERS_TAGS'),
					'type'      => 'select',
					'values'    => $tags,
					'value'     => ['all'],
					'default'   => true,
					'available' => true,
					'order'     => $config['filter_tags_order']
				];
			}

			if ($config['filter_published'])
			{
				$this->applied_filters[] = [
					'uid'       => 'published',
					'id'        => 'published',
					'label'     => JText::_('MOD_EMUNDUS_FILTERS_PUBLISHED_STATE'),
					'type'      => 'select',
					'values'    => [
						['value' => 1, 'label' => JText::_('MOD_EMUNDUS_FILTERS_VALUE_PUBLISHED'), 'count' => 0],
						['value' => 0, 'label' => JText::_('MOD_EMUNDUS_FILTERS_VALUE_ARCHIVED'), 'count' => 0],
						['value' => -1, 'label' => JText::_('MOD_EMUNDUS_FILTERS_VALUE_DELETED'), 'count' => 0]
					],
					'value'     => [1],
					'default'   => true,
					'available' => true,
					'order'     => $config['filter_published_order']
				];
			}

            if ($config['filter_groups']) {
                $query->clear()
                    ->select('DISTINCT id as value, label')
                    ->from('#__emundus_setup_groups')
                    ->where('published = 1');

                $db->setQuery($query);
                $groups = $db->loadAssocList();

                $this->applied_filters[] = [
                    'uid' => 'group_assoc',
                    'id' => 'group_assoc',
                    'label' => JText::_('MOD_EMUNDUS_FILTERS_GROUP_ASSOC'),
                    'type' => 'select',
                    'values' => $groups,
                    'value' => ['all'],
                    'default' => true,
                    'available' => true,
                    'order' => $config['filter_groups_order']
                ];
            }

            if ($config['filter_users']) {
                $query->clear()
                    ->select('ju.id as value, CONCAT(ju.name, " ", ju.email ) as label')
                    ->from('#__users as ju')
                    ->leftJoin('#__emundus_users_assoc as jeua ON ju.id = jeua.user_id')
                    ->leftJoin('#__emundus_campaign_candidature AS jecc ON jeua.fnum = jecc.fnum')
                    ->leftJoin('#__emundus_setup_campaigns AS jesc ON jecc.campaign_id = jesc.id')
                    ->leftJoin('#__emundus_setup_programmes AS jesp ON jesc.training = jesp.code')
                    ->where('ju.block = 0')
                    ->andWhere('jecc.campaign_id IN ' . '(' . implode(',', $this->user_campaigns) . ') OR jesp.id IN ' . '(' . implode(',', $this->user_programs) . ')')
                    ->group('ju.id');

                try {
                    $db->setQuery($query);
                    $users = $db->loadAssocList();
                } catch (Exception $e) {
                    JLog::add('Failed to get users associated to profiles that current' . $e->getMessage(), JLog::ERROR, 'com_emundus.filters.error');
                }

                $this->applied_filters[] = [
                    'uid' => 'users_assoc',
                    'id' => 'users_assoc',
                    'label' => JText::_('MOD_EMUNDUS_FILTERS_USERS_ASSOC'),
                    'type' => 'select',
                    'values' => $users,
                    'value' => ['all'],
                    'default' => true,
                    'available' => true,
                    'order' => $config['filter_users_order']
                ];
            }

            if (!empty($config['more_filter_elements']))
			{
				$config['more_filter_elements'] = json_decode($config['more_filter_elements'], true);

				foreach ($config['more_filter_elements']['fabrik_element_id'] as $more_filter_index => $fabrik_element_id)
				{
					if (!empty($fabrik_element_id))
					{
						$found              = false;
						$new_default_filter = [];
						foreach ($this->filters as $filter)
						{
							if ($filter['id'] == $fabrik_element_id)
							{
								$new_default_filter            = $filter;
								$new_default_filter['default'] = true;
								if (empty($new_default_filter['value']))
								{
									$new_default_filter['value'] = $new_default_filter['type'] === 'select' ? ['all'] : '';
								}
								$new_default_filter['andorOperator'] = 'OR';
								$new_default_filter['operator']      = '=';

								if ($new_default_filter['type'] === 'select')
								{
									$new_default_filter['operator'] = 'IN';
									$new_default_filter['values']   = $this->getFabrikElementValuesFromElementId($filter['id']);
								}
								$found = true;
								break;
							}
						}

						if (!$found)
						{
							$query->clear()
								->select('jfe.id, jfe.plugin, jfe.label, jfe.params, jffg.form_id as element_form_id, jff.label as element_form_label')
								->from('jos_fabrik_elements as jfe')
								->join('inner', 'jos_fabrik_formgroup as jffg ON jfe.group_id = jffg.group_id')
								->join('inner', 'jos_fabrik_forms as jff ON jffg.form_id = jff.id')
								->where('jfe.id = ' . $fabrik_element_id)
								->andWhere('jfe.published = 1');

							$db->setQuery($query);
							$element = $db->loadAssoc();

							if (!empty($element))
							{
								$element['label']              = JText::_($element['label']);
								$element['element_form_label'] = JText::_($element['element_form_label']);
								$formatted_elements            = $this->createFiltersFromFabrikElements([$element]);

								if (!empty($formatted_elements))
								{
									$new_default_filter            = $formatted_elements[0];
									$new_default_filter['default'] = true;
									if (empty($new_default_filter['value']))
									{
										$new_default_filter['value'] = $new_default_filter['type'] === 'select' ? ['all'] : '';
									}
									$new_default_filter['andorOperator'] = 'OR';
									$new_default_filter['operator']      = '=';

									if ($new_default_filter['type'] === 'select')
									{
										$new_default_filter['operator'] = 'IN';
										$new_default_filter['values']   = $this->getFabrikElementValuesFromElementId($element['id']);
									}
								}
								$new_default_filter['plugin'] = $element['plugin'];
							}
						}

						if (!empty($new_default_filter))
						{
							$this->filters[]             = $new_default_filter;
							$new_default_filter['uid']   = 'default-filter-' . $new_default_filter['id'];
							$new_default_filter['order'] = $config['more_filter_elements']['order'][$more_filter_index];
							$this->applied_filters[]     = $new_default_filter;

							// add filter to adv cols
							$session                 = JFactory::getSession();
							$files_displayed_columns = $session->get('adv_cols');
							if (!empty($files_displayed_columns))
							{
								$files_displayed_columns[] = $new_default_filter['id'];
							}
							else
							{
								$files_displayed_columns = [$new_default_filter['id']];
							}
							$session->set('adv_cols', $files_displayed_columns);
						}
					}
				}
			}

			// sort applied filters array by array entry 'order'
			usort($this->applied_filters, function ($a, $b) {
				return intval($a['order']) <=> intval($b['order']);
			});

			if ($this->h_cache->isEnabled() && !empty($active_menu))
			{
				$this->h_cache->set('em_default_filters_' . $active_menu->id, $this->applied_filters);
			}
		}
	}

	private function addSessionFilters($session_values)
	{
		foreach ($session_values as $session_filter)
		{
			$found = false;
			foreach ($this->applied_filters as $key => $applied_filter)
			{
				if ($applied_filter['uid'] == $session_filter['uid'])
				{
					$this->applied_filters[$key]['value']         = $session_filter['value'];
					$this->applied_filters[$key]['operator']      = $session_filter['operator'];
					$this->applied_filters[$key]['andorOperator'] = $session_filter['andorOperator'];

					$found = true;
					break;
				}
			}

			if (!$found)
			{
				// find filter in filters
				foreach ($this->filters as $i_filter => $filter)
				{
					if ($filter['id'] == $session_filter['id'])
					{
						if ($filter['type'] == 'select' && empty($filter['values']))
						{
							$filter['values']         = $this->getFabrikElementValuesFromElementId($filter['id']);
							$this->filters[$i_filter] = $filter;
						}
						$new_filter = $filter;

						$new_filter['value']         = $session_filter['value'];
						$new_filter['operator']      = $session_filter['operator'];
						$new_filter['andorOperator'] = $session_filter['andorOperator'];
						$new_filter['uid']           = $session_filter['uid'];
						$this->applied_filters[]     = $new_filter;
						break;
					}
				}
			}
		}
	}

	private function checkFiltersAvailability()
	{
		$campaign_availables = $this->user_campaigns;

		$campaign_filter = null;
		$program_filter  = null;
		foreach ($this->applied_filters as $filter)
		{
			if ($filter['uid'] == 'campaigns')
			{
				$campaign_filter = $filter;
			}
			else
			{
				if ($filter['uid'] == 'programs')
				{
					$program_filter = $filter;
				}
			}
		}

		if (!empty($campaign_filter) && !empty($campaign_filter['value']))
		{
			if (is_array($campaign_filter['value']) && in_array('all', $campaign_filter['value']))
			{
				// stop here, all campaigns are selected
			}
			else
			{
				// if the operator is NOT IN or !=, we need to get fabrik elements associated to campaigns that are not in the filter
				switch ($campaign_filter['operator'])
				{
					case 'NOT IN':
					case '!=':
						$campaign_availables = array_diff($this->user_campaigns, $campaign_filter['value']);
						break;
					default:
						$campaign_availables = array_intersect($this->user_campaigns, $campaign_filter['value']);
						break;
				}
			}
		}

		if (!empty($program_filter) && !empty($program_filter['value']))
		{
			if (is_array($program_filter['value']) && in_array('all', $program_filter['value']))
			{
				// stop here, all campaigns are selected
			}
			else
			{
				// get campaigns associated to programs
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->select('DISTINCT esc.id')
					->from($db->quoteName('#__emundus_setup_campaigns', 'esc'))
					->join('INNER', $db->quoteName('#__emundus_setup_programmes', 'esp') . ' ON (' . $db->quoteName('esc.training') . ' = ' . $db->quoteName('esp.code') . ')')
					->where($db->quoteName('esp.id') . ' IN (' . implode(',', $program_filter['value']) . ')')
					->where('esc.published = 1')
					->andWhere('esc.id IN (' . implode(',', $this->user_campaigns) . ')');

				$db->setQuery($query);
				$campaigns_of_program = $db->loadColumn();

				if (!empty($campaigns_of_program))
				{
					// if the operator is NOT IN or !=, we need to get fabrik elements associated to campaigns that are not in the filter
					switch ($program_filter['operator'])
					{
						case 'NOT IN':
						case '!=':
							$campaign_availables = array_diff($this->user_campaigns, $campaigns_of_program);
							break;
						default:
							$campaign_availables = array_intersect($this->user_campaigns, $campaigns_of_program);
							break;
					}
				}
			}
		}

		if (!empty($campaign_availables))
		{
			$filtered_profiles = $this->getProfilesFromCampaignId($campaign_availables);

			if (!empty($filtered_profiles))
			{
				$element_ids_available = $this->getElementIdsAssociatedToProfile($filtered_profiles);

			    foreach ($this->filters as $key => $filter)
                {
                    if (!in_array($filter['id'], $element_ids_available) && !in_array($filter['group_id'], $this->config['more_fabrik_forms']))
                    {
					    $this->filters[$key]['available'] = false;
				    }
			    }
		    }
	    }
    }

	private function getElementIdsAssociatedToProfile($profile_ids)
	{
		$element_ids = [];

		if (!empty($profile_ids))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$menus      = [];
			$form_links = [];
			foreach ($profile_ids as $profile)
			{
				if ($this->h_cache->isEnabled())
				{
					$profile_form_links = $this->h_cache->get('em-filters-profile-' . $profile . '-form');

					if (!empty($profile_form_links))
					{
						$form_links = array_merge($form_links, $profile_form_links);
						continue;
					}
				}

				$menus[] = 'menu-profile' . $profile;
			}

			if (!empty($menus))
			{
				if ($this->h_cache->isEnabled())
				{
					$query->select('link, menutype')
						->from('#__menu')
						->where('menutype IN (' . implode(',', $db->quote($menus)) . ')')
						->andWhere('link LIKE "index.php?option=com_fabrik&view=form&formid=%"')
						->andWhere('published = 1');

					$db->setQuery($query);
					$form_menus = $db->loadAssocList();

					$form_links_by_profile = [];

					foreach ($form_menus as $form_menu)
					{
						$profile_id                           = (int) str_replace('menu-profile', '', $form_menu['menutype']);
						$form_links[]                         = $form_menu['link'];
						$form_links_by_profile[$profile_id][] = $form_menu['link'];
					}

					foreach ($profile_ids as $profile)
					{
						if (!empty($form_links_by_profile[$profile]))
						{
							$this->h_cache->set('em-filters-profile-' . $profile . '-form', $form_links_by_profile[$profile]);
						}
					}
				}
				else
				{
					$query->select('link')
						->from('#__menu')
						->where('menutype IN (' . implode(',', $db->quote($menus)) . ')')
						->andWhere('link LIKE "index.php?option=com_fabrik&view=form&formid=%"')
						->andWhere('published = 1');

					$db->setQuery($query);
					$form_links = $db->loadColumn();
				}
			}

			if (!empty($form_links))
			{
				$form_ids = [];
				foreach ($form_links as $link)
				{
					$form_ids[] = (int) str_replace('index.php?option=com_fabrik&view=form&formid=', '', $link);
				}
				$form_ids = array_unique($form_ids);

				if ($this->h_cache->isEnabled())
				{
					$form_ids_cached = [];
					foreach ($form_ids as $form_id)
					{
						$form_element_ids = $this->h_cache->get('em-filters-form-' . $form_id . '-element');

						if (!empty($form_element_ids))
						{
							$element_ids       = array_merge($element_ids, $form_element_ids);
							$form_ids_cached[] = $form_id;
						}
					}

					$form_ids = array_diff($form_ids, $form_ids_cached);
					if (!empty($form_ids))
					{
						$query->clear()
							->select('jfe.id as element_id, jff.id as form_id')
							->from('jos_fabrik_elements as jfe')
							->join('inner', 'jos_fabrik_formgroup as jffg ON jfe.group_id = jffg.group_id')
							->join('inner', 'jos_fabrik_forms as jff ON jffg.form_id = jff.id')
							->where('jffg.form_id IN (' . implode(',', $form_ids) . ')')
							->andWhere('jfe.published = 1')
							->andWhere('jfe.hidden = 0');

						try
						{
							$db->setQuery($query);
							$ids = $db->loadAssocList();

							$element_ids_by_form = [];
							foreach ($ids as $id)
							{
								$element_ids_by_form[$id['form_id']][] = $id['element_id'];
								$element_ids[]                         = $id['element_id'];
							}

							foreach ($form_ids as $form_id)
							{
								if (!empty($element_ids_by_form[$form_id]))
								{
									$this->h_cache->set('em-filters-form-' . $form_id . '-elements', $element_ids_by_form[$form_id]);
								}
							}
						}
						catch (Exception $e)
						{
							JLog::add('Failed to get elements associated to profiles that current user can access : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filters.error');
						}
					}
				}
				else
				{
					$query->clear()
						->select('jfe.id')
						->from('jos_fabrik_elements as jfe')
						->join('inner', 'jos_fabrik_formgroup as jffg ON jfe.group_id = jffg.group_id')
						->join('inner', 'jos_fabrik_forms as jff ON jffg.form_id = jff.id')
						->where('jffg.form_id IN (' . implode(',', $form_ids) . ')')
						->andWhere('jfe.published = 1')
						->andWhere('jfe.hidden = 0');

					try
					{
						$db->setQuery($query);
						$element_ids = $db->loadColumn();
					}
					catch (Exception $e)
					{
						JLog::add('Failed to get elements associated to profiles that current user can access : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filters.error');
					}
				}
			}
		}

		return $element_ids;
	}
}
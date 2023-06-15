<?php

class EmundusFilters
{
	protected $user = null;
	protected $default_element = 0;
	protected $filters = [];
	protected $applied_filters = [];

	public function __construct($config = array())
	{
		JLog::addLogger(['text_file' => 'com_emundus.filters.php'], JLog::ALL, 'com_emundus.filters');

		$this->user = JFactory::getUser();

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->user->id)) {
			throw new Exception('Access denied', 403);
		}

		if (!empty($config['element_id'])) {
			$this->default_element = $config['element_id'];
			$this->setFilters();
		}

		$session_filters = JFactory::getSession()->get('em-applied-filters', null);
		if (!empty($session_filters)) {
			$this->setAppliedFilters($session_filters);
		}
	}

	protected function getDefaultElement()
	{
		return $this->default_element;
	}

	protected function setFilters()
	{
		$element = $this->getDefaultElement();

		if (!empty($element)) {
			$elements = $this->getAllAssociatedElements($element);
			$this->filters = $this->createFiltersFromFabrikElements($elements);
		}
	}


	protected function createFiltersFromFabrikElements($elements)
	{
		$created_filters = [];

		if (!empty($elements)) {
			foreach($elements as $element) {
				$filter = [
					'id' => $element['id'],
					'label' => JText::_($element['label']),
					'type' => 'text',
					'values' => [],
					'group_label' => $element['element_form_label'],
					'group_id' => $element['element_form_id'],
					'available' => true,
				];

				switch ($element['plugin']) {
					case 'dropdown':
					case 'checkbox':
					case 'radiobutton':
					case 'databasejoin':
						$filter['type'] = 'select';
						$filter['values'] = $this->getFabrikElementValues($element);
						break;
					case 'yesno':
						$filter['type'] = 'select';
						$filter['values'] = [
							['value' => 1, 'label' => JText::_('JYES')],
							['value' => 0, 'label' => JText::_('JNO')]
						];
						break;
					case 'date':
					case 'jdate':
					case 'birthday':
						$filter['type'] = 'date';
						$filter['value'] = ['', ''];
						break;
					case 'time':
						$filter['type'] = 'time';
						$filter['value'] = ['', ''];
						break;
				}

				$created_filters[] = $filter;
			}
		}

		return $created_filters;
	}

	protected function getAllAssociatedElements($element_id)
	{
		$elements = [];

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('jfl.id, jfl.db_table_name, jfl.form_id')
			->from('jos_fabrik_elements as jfe')
			->join('inner', 'jos_fabrik_groups as jfg ON jfg.id = jfe.group_id')
			->join('inner', 'jos_fabrik_formgroup as jffg ON jffg.group_id = jfg.id')
			->join('inner', 'jos_fabrik_lists as jfl ON jffg.form_id = jfl.form_id')
			->where('jfe.id = ' . $element_id);

		try {
			$db->setQuery($query);
			$data = $db->loadAssoc();
		} catch (Exception $e) {
			JLog::add('Failed to get infos from fabrik element id ' . $element_id . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filters.error');
		}

		if (!empty($data['form_id'])) {
			$query->clear()
				->select('jfe.id, jfe.plugin, jfe.label, jfe.params')
				->from('jos_fabrik_elements as jfe')
				->join('inner', 'jos_fabrik_formgroup as jffg ON jfe.group_id = jffg.group_id')
				->where('jffg.form_id = ' . $data['form_id'])
				->andWhere('published = 1')
				->andWhere('hidden = 0');

			try {
				$db->setQuery($query);
				$elements = $db->loadAssocList();
			} catch (Exception $e) {
				JLog::add('Failed to get elements associated element id ' . $element_id . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filters.error');
			}
		}

		return $elements;
	}

	public function getFilters()
	{
		return $this->filters;
	}

	protected function setAppliedFilters($applied_filters)
	{
		$this->applied_filters = $applied_filters;
	}

	public function getAppliedFilters()
	{
		return $this->applied_filters;
	}

	public function applyFilters()
	{

	}

	protected function getFabrikElementValues($element)
	{
		$values = [];

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		switch ($element['plugin']) {
			case 'databasejoin':
				if (!empty($element['params'])) {
					$params = json_decode($element['params'], true);

					if (!empty($params['join_db_name']) && !empty($params['join_key_column'])) {
						$select = $params['join_key_column'] . ' AS value';

						if (!empty($params['join_val_column_concat'])) {
							$lang = substr(JFactory::getLanguage()->getTag(), 0, 2);
							$params['join_val_column_concat'] = str_replace('{thistable}', $params['join_db_name'], $params['join_val_column_concat']);
							$params['join_val_column_concat'] = str_replace('{shortlang}', $lang, $params['join_val_column_concat']);
							$params['join_val_column_concat'] = 'CONCAT(' . $params['join_val_column_concat'] . ') as label';

							if (preg_match_all('/[#_a-z]+\.[_a-z]+/', $params['join_val_column_concat'], $matches)) {
								foreach($matches[0] as $match) {
									$params['join_val_column_concat'] = str_replace($match, $db->quoteName($match), $params['join_val_column_concat']);
								}
							}
							$select .= ', ' . $params['join_val_column_concat'];
						} else {
							$select .= ', ' . $db->quoteName($params['join_val_column'], 'label');
						}

						$query->clear()
							->select($select)
							->from($params['join_db_name']);

						if(!empty($params['database_join_where_sql'])) {
							$params['database_join_where_sql'] = str_replace('{thistable}', $params['join_db_name'], $params['database_join_where_sql']);
							$params['database_join_where_sql'] = str_replace('{shortlang}', $lang, $params['database_join_where_sql']);
							$first_where_pos = stripos($params['database_join_where_sql'], 'WHERE');

							if ($first_where_pos !== false) {
								$params['database_join_where_sql'] = substr($params['database_join_where_sql'], $first_where_pos + 5);
							}

							$query->where($params['database_join_where_sql']);
						}

						try {
							$db->setQuery($query);
							$values = $db->loadAssocList();
						} catch (Exception $e) {
							var_dump($query->__toString());
							JLog::add('Failed to get filter values ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
						}
					}
				}
				break;
			case 'dropdown':
			case 'radiobutton':
			case 'checkbox':
				if (!empty($element['params'])) {
					$params = json_decode($element['params'], true);
					if (!empty($params['sub_options'])) {
						foreach($params['sub_options']['sub_values'] as $sub_opt_key => $sub_opt) {
							$label = \JText::_($params['sub_options']['sub_labels'][$sub_opt_key]);
							if (empty($label)) {
								$label = $sub_opt;
							}

							$values[] = [
								'value' => $sub_opt,
								'label' => $label
							];
						}
					}
				}
				break;
		}

		return $values;
	}
}
<?php
/**
 * Filters model used for
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class EmundusModelFilters extends JModelList
{
	private $default_element = 0;
	private $default_filters = [];
	private $applied_filters = [];

	public function __construct($config = array(), \Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);

		if (!empty($config['element_id'])) {
			$this->default_element = $config['element_id'];
			$this->setDefaultFilters();
		}

		$session_filters = JFactory::getSession()->get('applied_filters');
		if (!empty($session_filters)) {
			$this->setAppliedFilters($session_filters);
		}
	}

	private function getDefaultElement()
	{
		return $this->default_element;
	}

	private function setDefaultFilters()
	{
		$element = $this->getDefaultElement();

		if (!empty($element)) {
			$data = [];

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('jfl.id, jfl.db_table_name, jfl.form_id')
				->from('jos_fabrik_elements as jfe')
				->join('inner', 'jos_fabrik_groups as jfg ON jfg.id = jfe.group_id')
				->join('inner', 'jos_fabrik_formgroup as jffg ON jffg.group_id = jfg.id')
				->join('inner', 'jos_fabrik_lists as jfl ON jffg.form_id = jfl.form_id')
				->where('jfe.id = ' . $element);

			try {
				$db->setQuery($query);
				$data = $db->loadAssoc();
			} catch (Exception $e) {
				JLog::add('Failed to get infos from fabrik element id ' . $element . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filters.error');
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
					JLog::add('Failed to get elements associated element id ' . $element . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filters.error');
				}

				if (!empty($elements)) {
					foreach($elements as $element) {
						$default_filter = [
							'id' => $element['id'],
							'label' => JText::_($element['label']),
							'type' => 'text',
							'operators' => ['='],
							'values' => []
						];

						switch ($element['plugin']) {
							case 'dropdown':
							case 'checkbox':
							case 'radiobutton':
							case 'databasejoin':
								$default_filter['type'] = 'select';
								$default_filter['values'] = $this->getFabrikElementValues($element);
							break;
							case 'date':
							case 'jdate':
							case 'birthday':
								$default_filter['type'] = 'date';
								break;
							default:
						}

						$this->default_filters[] = $default_filter;
					}
				}
			}
		}
	}

	public function getDefaultFilters()
	{
		return $this->default_filters;
	}

	private function setAppliedFilters($applied_filters)
	{
		$this->applied_filters = $applied_filters;
	}

	public function getAppliedFilters()
	{
		return $this->default_filters;
	}

	public function applyFilters()
	{

	}

	private function getFabrikElementValues($element)
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

						try {
							$db->setQuery($query);
							$values = $db->loadAssocList();
						} catch (Exception $e) {
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
							if ($sub_opt == 0){
								$label = '';
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
?>

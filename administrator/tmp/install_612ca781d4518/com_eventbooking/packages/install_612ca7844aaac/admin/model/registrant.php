<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

class EventbookingModelRegistrant extends EventbookingModelCommonRegistrant
{
	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->state->insert('filter_event_id', 'int', 0);
	}

	/**
	 * Initial registrant data
	 *
	 * @see RADModelAdmin::initData()
	 */
	public function initData()
	{
		parent::initData();

		$this->data->event_id = $this->state->filter_event_id;
	}

	/**
	 * @param $file
	 * @param $filename
	 *
	 * @return int
	 * @throws Exception
	 */
	public function import($file, $filename = '')
	{
		$app = Factory::getApplication();
		PluginHelper::importPlugin('eventbooking');
		$config      = EventbookingHelper::getConfig();
		$registrants = EventbookingHelperData::getDataFromFile($file, $filename);

		$imported  = 0;
		$todayDate = Factory::getDate()->toSql();

		if (count($registrants))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('name, title')
				->from('#__eb_payment_plugins');
			$db->setQuery($query);
			$plugins = $db->loadObjectList('title');

			foreach ($registrants as $registrant)
			{
				if (empty($registrant['event_id']))
				{
					continue;
				}

				/* @var EventbookingTableRegistrant $row */
				$row = $this->getTable();

				if (!empty($registrant['id']))
				{
					$isNew = false;
					$row->load($registrant['id']);
				}
				else
				{
					$isNew = true;
				}

				if ($registrant['register_date'])
				{
					try
					{
						$registerDate = DateTime::createFromFormat($config->date_format, $registrant['register_date']);

						if ($registerDate === false)
						{
							$registerDate                = Factory::getDate($registrant['register_date']);
							$registrant['register_date'] = $registerDate->format('Y-m-d');
						}
						else
						{
							$registrant['register_date'] = $registerDate->format('Y-m-d');
						}
					}
					catch (Exception $e)
					{
						$registrant['register_date'] = $todayDate;
					}
				}
				else
				{
					$registrant ['register_date'] = $todayDate;
				}

				if ($registrant['payment_method'] && isset($plugins[$registrant['payment_method']]))
				{
					$registrant['payment_method'] = $plugins[$registrant['payment_method']]->name;
				}

				$row->bind($registrant);

				if ($row->number_registrants > 1)
				{
					$row->is_group_billing = 1;
				}

				$row->store();

				$registrantId = $row->id;

				$fields = self::getEventFields($row->event_id, $config);

				if (count($fields))
				{
					$query->clear()
						->delete('#__eb_field_values')
						->where('registrant_id = ' . $registrantId);
					$db->setQuery($query);
					$db->execute();

					foreach ($fields as $fieldName => $field)
					{
						$fieldValue = isset($registrant[$fieldName]) ? $registrant[$fieldName] : '';
						$fieldId    = $field->id;

						if ($field->fieldtype == 'Checkboxes' || $field->multiple)
						{
							$fieldValue = json_encode(explode(', ', $fieldValue));
						}

						$query->clear()
							->insert('#__eb_field_values')
							->columns('registrant_id, field_id, field_value')
							->values("$registrantId, $fieldId, " . $db->quote($fieldValue));
						$db->setQuery($query);
						$db->execute();
					}
				}

				if ($isNew && $row->published == 1)
				{
					$app->triggerEvent('onAfterPaymentSuccess', [$row]);
				}

				$imported++;
			}
		}

		return $imported;
	}

	/**
	 * Get all custom fields of the given event
	 *
	 * @param   int  $eventId
	 *
	 * @pram RADConfig $config
	 *
	 * @return array
	 */
	public static function getEventFields($eventId, $config)
	{
		static $fields;

		if (!isset($fields[$eventId]))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id, name, fieldtype')
				->from('#__eb_fields')
				->where('is_core = 0')
				->where('published = 1');

			if ($config->custom_field_by_category)
			{
				//Get main category of the event
				$subQuery = $db->getQuery(true);
				$subQuery->select('category_id')
					->from('#__eb_event_categories')
					->where('event_id = ' . $eventId);
				$db->setQuery($subQuery);
				$categoryIds = $db->loadColumn();

				if (empty($categoryIds))
				{
					$categoryIds = [0];
				}

				$query->where('(category_id = -1 OR id IN (SELECT field_id FROM #__eb_field_categories WHERE category_id IN (' . implode(',', $categoryIds) . ')))');
			}
			else
			{
				$query->where(' (event_id = -1 OR id IN (SELECT field_id FROM #__eb_field_events WHERE event_id=' . $eventId . '))');
			}

			$db->setQuery($query);
			$fields[$eventId] = $db->loadObjectList('name');
		}

		return $fields[$eventId];
	}
}

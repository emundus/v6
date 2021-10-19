<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

class plgEventBookingFields extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;

	/**
	 * Render settings form
	 *
	 * @param   EventbookingTableEvent  $row
	 *
	 * @return array
	 */
	public function onEditEvent($row)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		ob_start();
		$this->drawSettingForm($row);

		return ['title' => Text::_('EB_FORM_FIELDS'),
		        'form'  => ob_get_clean(),
		];
	}

	/**
	 * Store setting into database, in this case, use params field of plans table
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   Boolean                 $isNew  true if create new plan, false if edit
	 */
	public function onAfterSaveEvent($row, $data, $isNew)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		$db         = $this->db;
		$query      = $db->getQuery(true);
		$formFields = isset($data['registration_form_fields']) ? $data['registration_form_fields'] : [];

		if (!$isNew)
		{
			$query->delete('#__eb_field_events')
				->where('event_id = ' . $row->id);
			$db->setQuery($query)
				->execute();

			$query->clear();
		}

		if (!count($formFields))
		{
			return;
		}

		$query->insert('#__eb_field_events')
			->columns('event_id, field_id');

		foreach ($formFields as $fieldId)
		{
			$query->values(implode(',', [$row->id, (int) $fieldId]));
		}

		$db->setQuery($query)
			->execute();
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param   EventbookingTableEvent  $row
	 */
	private function drawSettingForm($row)
	{
		$db    = $this->db;
		$query = $db->getQuery(true)
			->select('id, event_id, name, title')
			->from('#__eb_fields')
			->where('published = 1')
			->order('event_id, ordering');
		$db->setQuery($query);
		$rowFields = $db->loadObjectList();

		foreach ($rowFields as $rowField)
		{
			if ($rowField->event_id == -1)
			{
				continue;
			}

			$query->clear()
				->select('event_id')
				->from('#__eb_field_events')
				->where('field_id = ' . $rowField->id);
			$rowField->eventIds = $db->loadColumn();
		}

		$selectedFieldIds = [];

		// Load assigned fields for this event
		if ($row->id)
		{
			$query->clear()
				->select('field_id')
				->from('#__eb_field_events')
				->where('event_id = ' . $row->id);
			$db->setQuery($query);
			$selectedFieldIds = $db->loadColumn();
		}

		$numberColumns = 4;
		$count         = 0;
		$spanClass     = 'span3';
		$numberFields  = count($rowFields);
		?>
        <div class="row-fluid">
		<?php
		foreach ($rowFields as $rowField)
		{
			$count++;
			$attributes = [];

			if ($rowField->event_id == -1)
			{
				$attributes[] = 'disabled';
				$attributes[] = 'checked';
			}
			else
			{
				if (in_array($rowField->id, $selectedFieldIds))
				{
					$attributes[] = 'checked';
				}
                elseif (!empty($rowField->eventIds) && $rowField->eventIds[0] < 0)
				{
					$negativeEventId = -1 * $row->id;

					if ($row->id == 0 || !in_array($negativeEventId, $rowField->eventIds))
					{
						$attributes[] = 'disabled';
						$attributes[] = 'checked';
					}
				}
			}
			?>
            <div class="<?php echo $spanClass; ?>">
                <label class="checkbox">
                    <input type="checkbox" class="form-check-input" value="<?php echo $rowField->id ?>"
                           name="registration_form_fields[]"<?php if (count($attributes)) echo ' ' . implode(' ', $attributes); ?>><?php echo '[' . $rowField->id . '] - ' . $rowField->title; ?>
                </label>
            </div>
			<?php
			if ($count % $numberColumns == 0 && $count < $numberFields)
			{
				?>
                </div>
                <div class="clearfix row-fluid">
				<?php
			}
		}
		?>
        </div>
		<?php
	}

	/**
	 * Method to check to see whether the plugin should run
	 *
	 * @param   EventbookingTableEvent  $row
	 *
	 * @return bool
	 */
	private function canRun($row)
	{
		if ($row->parent_id > 0)
		{
			return false;
		}

		if ($this->app->isClient('site') && !$this->params->get('show_on_frontend'))
		{
			return false;
		}

		$config = EventbookingHelper::getConfig();

		if ($config->custom_field_by_category)
		{
			return false;
		}

		return true;
	}
}

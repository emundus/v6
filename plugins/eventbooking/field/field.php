<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;

class plgEventBookingField extends CMSPlugin
{
	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;

	/**
	 * Store setting into database
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   bool                    $isNew  true if create new plan, false if edit
	 */
	public function onAfterSaveEvent($row, $data, $isNew)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		$fieldTitle    = $this->params->get('field_title', 'Ticket Type');
		$fieldTitle    = str_replace('EVENT_ID', $row->id, $fieldTitle);
		$fieldValues   = $this->params->get('field_values');
		$priceFields   = $this->params->get('price_fields');
		$fieldOrdering = $this->params->get('field_ordering');

		if (empty($priceFields))
		{
			return;
		}

		if (empty($fieldOrdering))
		{
			$fieldOrdering = 0;
		}
		else
		{
			$query->select('ordering')
				->from('#__eb_fields')
				->where('published = 1')
				->where('name = ' . $db->quote($fieldOrdering));
			$db->setQuery($query);

			$fieldOrdering = (int) $db->loadResult() + 1;
			$query->clear();
		}

		$feeValues = [];

		foreach ($priceFields as $field)
		{
			$feeValues[] = isset($data['params'][$field]) ? $data['params'][$field] : 0;
		}

		$fieldValues = explode("\r\n", $fieldValues);

		for ($i = 0, $n = count($feeValues); $i < $n; $i++)
		{
			$fieldValues[$i] = $fieldValues[$i] . ' - ' . $feeValues[$i];
		}

		$inputFilter = JFilterInput::getInstance();

		for ($i = 0, $n = count($feeValues); $i < $n; $i++)
		{
			$feeValues[$i] = $inputFilter->clean($feeValues[$i], 'FLOAT');
		}

		for ($i = 0, $n = count($feeValues); $i < $n; $i++)
		{
			$feeValues[$i] = $feeValues[$i] - $row->individual_price;
		}

		$fieldName = 'event_field_' . $row->id;
		$fieldId   = 0;

		if (!$isNew)
		{
			$query->select('id')
				->from('#__eb_fields')
				->where('name=' . $db->quote($fieldName));
			$db->setQuery($query);
			$fieldId = $db->loadResult();
		}

		$rowField = Table::getInstance('Field', 'EventbookingTable');

		if ($fieldId)
		{
			$rowField->load($fieldId);
		}

		$rowField->event_id         = 1;
		$rowField->name             = $fieldName;
		$rowField->title            = $fieldTitle;
		$rowField->fieldtype        = 'Radio';
		$rowField->fee_values       = implode("\r\n", $feeValues);
		$rowField->values           = implode("\r\n", $fieldValues);
		$rowField->required         = 0;
		$rowField->published        = 1;
		$rowField->fee_field        = 1;
		$rowField->required         = 1;
		$rowField->display_in       = 5;
		$rowField->validation_rules = 'validate[required]';
		$rowField->ordering         = $fieldOrdering;

		$rowField->store();

		if (!$fieldId)
		{
			$query->clear()
				->insert('#__eb_field_events')
				->columns('field_id, event_id')
				->values("$rowField->id, $row->id");
			$db->setQuery($query);
			$db->execute();
		}
	}
}

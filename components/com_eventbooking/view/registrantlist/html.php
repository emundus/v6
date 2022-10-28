<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingViewRegistrantlistHtml extends RADViewHtml
{
	public function display()
	{
		$state   = $this->model->getState();
		$eventId = $state->id;

		if (!$eventId || !EventbookingHelperAcl::canViewRegistrantList($eventId))
		{
			return;
		}


		$rows      = $this->model->getData();
		$config    = EventbookingHelper::getConfig();
		$event     = EventbookingHelperDatabase::getEvent($eventId);
		$rowFields = EventbookingHelperRegistration::getAllPublicEventFields($eventId);

		if ($config->get('public_registrants_list_show_ticket_types'))
		{
			list($ticketTypes, $tickets) = $this->model->getTicketsData($eventId);
		}
		else
		{
			$ticketTypes = $tickets = [];
		}

		if (count($rowFields))
		{
			$fields = [];

			foreach ($rowFields as $rowField)
			{
				if (in_array($rowField->name, ['first_name', 'last_name']))
				{
					continue;
				}

				$fieldTitles[$rowField->id] = $rowField->title;
				$fields[]                   = $rowField->id;
			}

			$this->fieldTitles = $fieldTitles;
			$this->fieldValues = $this->model->getFieldsData($fields);
			$this->fields      = $fields;

			foreach ($rows as $row)
			{
				foreach ($rowFields as $rowField)
				{
					if (property_exists($row, $rowField->name))
					{
						continue;
					}

					if (isset($this->fieldValues[$row->id][$rowField->id]))
					{
						$fieldValue = $this->fieldValues[$row->id][$rowField->id];
					}
					else
					{
						$fieldValue = '';
					}

					$row->{$rowField->name} = $fieldValue;
				}
			}

			$displayCustomField = true;
		}
		else
		{
			$displayCustomField = false;
		}

		$this->items              = $rows;
		$this->pagination         = $this->model->getPagination();
		$this->config             = $config;
		$this->displayCustomField = $displayCustomField;
		$this->bootstrapHelper    = EventbookingHelperBootstrap::getInstance();
		$this->coreFields         = EventbookingHelperRegistration::getPublishedCoreFields();
		$this->event              = $event;
		$this->ticketTypes        = $ticketTypes;
		$this->tickets            = $tickets;
		$this->state              = $state;

		// Do not display anything in the plugin if there is no registrants data
		if ($this->input->getInt('hmvc_call') && !count($this->items))
		{
			return;
		}

		parent::display();
	}
}

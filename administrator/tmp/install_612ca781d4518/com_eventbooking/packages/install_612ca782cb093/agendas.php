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
use Joomla\CMS\Table\Table;

class plgEventBookingAgendas extends CMSPlugin
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
	 * Render setting form
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

		return ['title' => Text::_('EB_AGENDAS'),
		        'form'  => $this->drawSettingForm($row),
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

		$agendas   = isset($data['agendas']) && is_array($data['agendas']) ? $data['agendas'] : [];
		$agendaIds = [];
		$ordering  = 1;

		foreach ($agendas as $agenda)
		{
			/* @var EventbookingTableSpeaker $rowAgenda */
			$rowAgenda = Table::getInstance('Agenda', 'EventbookingTable');
			$rowAgenda->bind($agenda);

			// Prevent agendas data being moved to new event on saveAsCopy
			if ($isNew)
			{
				$rowAgenda->id = 0;
			}

			$rowAgenda->event_id = $row->id;
			$rowAgenda->ordering = $ordering++;
			$rowAgenda->store();
			$agendaIds[] = $rowAgenda->id;
		}

		if (!$isNew)
		{
			$db    = $this->db;
			$query = $db->getQuery(true);
			$query->delete('#__eb_agendas')
				->where('event_id = ' . $row->id);

			if (count($agendaIds))
			{
				$query->where('id NOT IN (' . implode(',', $agendaIds) . ')');
			}

			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param   EventbookingTableEvent  $row
	 *
	 * @return string
	 */
	private function drawSettingForm($row)
	{
		$xml = file_get_contents(JPATH_ROOT . '/plugins/eventbooking/agendas/form/agenda.xml');

		if ($this->params->get('use_editor_for_description', 0))
		{
			$xml = str_replace('type="textarea"', 'type="editor"', $xml);
		}

		$form                = JForm::getInstance('agendas', $xml);
		$formData['agendas'] = [];

		// Load existing speakers for this event
		if ($row->id)
		{
			$db    = $this->db;
			$query = $db->getQuery(true)
				->select('*')
				->from('#__eb_agendas')
				->where('event_id = ' . $row->id)
				->order('ordering');
			$db->setQuery($query);

			foreach ($db->loadObjectList() as $agenda)
			{
				$formData['agendas'][] = [
					'id'          => $agenda->id,
					'time'        => $agenda->time,
					'title'       => $agenda->title,
					'description' => $agenda->description,
				];
			}
		}

		$form->bind($formData);

		return EventbookingHelperHtml::loadCommonLayout('plugins/agendas_form.php', ['form' => $form]);
	}

	/**
	 * Display event speakers
	 *
	 * @param   EventbookingTableEvent  $row
	 *
	 * @return array|void
	 */
	public function onEventDisplay($row)
	{
		$eventId = $row->parent_id ?: $row->id;
		$db      = $this->db;
		$query   = $db->getQuery(true)
			->select('*')
			->from('#__eb_agendas')
			->where('event_id = ' . $eventId)
			->order('ordering');

		$db->setQuery($query);
		$agendas = $db->loadObjectList();

		if (empty($agendas))
		{
			return;
		}

		return ['title'    => Text::_('EB_EVENT_AGENDAS'),
		        'form'     => EventbookingHelperHtml::loadCommonLayout('plugins/agendas.php', ['agendas' => $agendas]),
		        'position' => $this->params->get('output_position', 'before_register_buttons'),
		];
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
		if ($this->app->isClient('site') && !$this->params->get('show_on_frontend'))
		{
			return false;
		}

		if ($row->parent_id > 0)
		{
			return false;
		}

		return true;
	}
}

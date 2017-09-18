<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.view', JPATH_SITE);
JLoader::import('components.com_dpcalendar.helpers.schema', JPATH_ADMINISTRATOR);

class DPCalendarViewList extends DPCalendarView
{

	protected $items = array();

	protected $increment = null;

	public function display($tpl = null)
	{
		$model = JModelLegacy::getInstance('Events', 'DPCalendarModel');
		$this->setModel($model, true);

		$start = JFactory::getApplication()->getParams()->get('date_start');
		if (!JRequest::getVar('date-start') && $start)
		{
			JRequest::setVar('date-start', DPCalendarHelper::getDate($start)->format('U'));
		}

		parent::display($tpl);
	}

	protected function init()
	{
		$app = JFactory::getApplication();
		$state = $this->get('State');

		$this->params = $state->params;
		$this->increment = $this->params->get('list_increment', '1 month');
		$dateStart = DPCalendarHelper::getDate($state->get('list.start-date'));

		$dateEnd = $state->get('list.end-date');
		if (!empty($dateEnd))
		{
			$dateEnd = DPCalendarHelper::getDate($dateEnd);
		}

		$jump = $app->input->getString('jump');
		if (!empty($jump))
		{
			$dateStart = DPCalendarHelper::getDateFromString($jump, null, true);
			$dateEnd = clone $dateStart;
			$dateEnd->modify('+ ' . $this->increment);
		}
		if (empty($dateEnd))
		{
			$dateEnd = clone $dateStart;
			$dateEnd->modify('+ ' . $this->increment);
		}

		// Only set time when we are during the day.
		// It will prevent day shifts.
		if ($dateStart->format('H:i') != '00:00')
		{
			$dateStart->setTime(0, 0, 0);
			$dateEnd->setTime(0, 0, 0);
		}

		$this->state->set('list.start-date', $dateStart->format('U'));
		$this->state->set('list.end-date', $dateEnd->format('U'));

		$this->startDate = $dateStart;
		$this->endDate = $dateEnd;

		// Initialise variables
		$this->getModel()->setState('category.id', $app->getParams()
			->get('ids'));
		$this->getModel()->setState('category.recursive', true);
		$this->getModel()->setState('filter.featured', $this->params->get('list_filter_featured', '2') == '1');

		$now = DPCalendarHelper::getDate();
		$now->setTime(0, 0, 0);
		$this->getModel()->setState('list.direction', $dateEnd->format('U') < $now->format('U') ? 'desc' : 'asc');

		$items = $this->get('Items');

		if ($items === false)
		{
			return JError::raiseError(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}

		foreach ($items as $event)
		{
			$event->text = $event->description;
			JPluginHelper::importPlugin('content');
			$dispatcher = JEventDispatcher::getInstance();
			$dispatcher->trigger('onContentPrepare', array(
					'com_dpcalendar.event',
					&$event,
					&$event->params,
					0
			));
			$event->description = $event->text;
		}
		$this->items = $items;
	}
}

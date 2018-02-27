<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.helpers.schema', JPATH_ADMINISTRATOR);

class DPCalendarViewList extends \DPCalendar\View\BaseView
{

	protected $items = array();
	protected $increment = null;

	public function display($tpl = null)
	{
		$model = JModelLegacy::getInstance('Events', 'DPCalendarModel');
		$this->setModel($model, true);

		parent::display($tpl);
	}

	protected function init()
	{
		$context         = 'com_dpcalendar.listview.filter.';
		$this->params    = $this->state->params;
		$this->increment = $this->params->get('list_increment', '1 month');
		$dateStart       = DPCalendarHelper::getDate($this->state->get('list.start-date'));

		$dateEnd = $this->state->get('list.end-date');
		if (!empty($dateEnd)) {
			$dateEnd = DPCalendarHelper::getDate($dateEnd);
		}

		$this->overrideStartDate = $this->app->getUserStateFromRequest($context . 'start', 'start-date');
		if (!empty($this->overrideStartDate)) {
			$dateStart = DPCalendarHelper::getDateFromString($this->overrideStartDate, null, true);
		}
		$this->overrideEndDate = $this->app->getUserStateFromRequest($context . 'end', 'end-date');
		if (!empty($this->overrideEndDate)) {
			$dateEnd = DPCalendarHelper::getDateFromString($this->overrideEndDate, null, true);
		}

		if (empty($dateEnd)) {
			$dateEnd = clone $dateStart;
			$dateEnd->modify('+ ' . $this->increment);
		}

		// Only set time when we are during the day.
		// It will prevent day shifts.
		if ($dateStart->format('H:i') != '00:00') {
			$dateStart->setTime(0, 0, 0);
			$dateEnd->setTime(0, 0, 0);
		}

		$this->state->set('list.start-date', $dateStart);
		$this->state->set('list.end-date', $dateEnd);

		$this->startDate = $dateStart;
		$this->endDate   = $dateEnd;

		$model = $this->getModel();

		// Initialise variables
		$model->setState('category.id', $this->app->getParams()->get('ids'));
		$model->setState('category.recursive', true);
		$model->setState('filter.featured', $this->params->get('list_filter_featured', '2') == '1');
		$model->setState('filter.my', $this->params->get('show_my_only_list'));

		$now = DPCalendarHelper::getDate();
		$now->setTime(0, 0, 0);
		$model->setState('list.direction', $dateEnd->format('U') < $now->format('U') ? 'desc' : 'asc');
		$model->setState('list.limit', 100000);

		// Location filters
		if ($location = $this->app->getUserStateFromRequest($context . 'location', 'location')) {
			$model->setState('filter.location', $location);
			$model->setState('filter.radius', $this->app->getUserStateFromRequest($context . 'radius', 'radius'));
			$model->setState('filter.length-type', $this->app->getUserStateFromRequest($context . 'length-type', 'length-type'));
		}

		$this->state = $model->getState();

		$items = $this->get('Items');

		if ($items === false) {
			throw new Exception(JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}

		foreach ($items as $event) {
			$event->text = $event->description;
			JPluginHelper::importPlugin('content');
			$this->app->triggerEvent('onContentPrepare', array('com_dpcalendar.event', &$event, &$event->params, 0));
			$event->description = $event->text;
		}
		$this->items = $items;
	}
}

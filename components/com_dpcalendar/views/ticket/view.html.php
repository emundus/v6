<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.phpqrcode.phpqrcode', JPATH_ADMINISTRATOR);

class DPCalendarViewTicket extends \DPCalendar\View\BaseView
{

	public function display($tpl = null)
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models');
		$model = JModelLegacy::getInstance('Ticket', 'DPCalendarModel');
		$this->setModel($model, true);

		parent::display($tpl);
	}

	protected function init()
	{
		$this->item = $this->getModel()->getItem(array('uid' => JFactory::getApplication()->input->getCmd('uid')));

		if ($this->item->id == null) {
			$this->setError(JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		$this->event   = JModelLegacy::getInstance('Event', 'DPCalendarModel')->getItem($this->item->event_id);
		$this->booking = JModelLegacy::getInstance('Booking', 'DPCalendarModel')->getItem($this->item->booking_id);

		$this->item->text = '';
		JFactory::getApplication()->triggerEvent('onContentPrepare', array('com_dpcalendar.ticket', &$this->item, &$this->params, 0));

	}
}

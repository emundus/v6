<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPCalendarViewTicketForm extends \DPCalendar\View\LayoutView
{
	protected $layoutName = 'ticket.form.default';

	public function display($tpl = null)
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models');
		$model = JModelLegacy::getInstance('Ticket', 'DPCalendarModel');
		$this->setModel($model, true);

		return parent::display($tpl);
	}

	public function init()
	{
		$this->app->getLanguage()->load('', JPATH_ADMINISTRATOR);

		$this->ticket     = $this->get('Item');
		$this->form       = $this->get('Form');
		$this->returnPage = $this->get('ReturnPage');

		if (!$this->ticket->id) {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
	}
}

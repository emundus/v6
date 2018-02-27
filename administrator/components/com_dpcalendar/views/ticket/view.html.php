<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPCalendarViewTicket extends \DPCalendar\View\LayoutView
{
	protected $layoutName = 'ticket.form.default';

	public function init()
	{
		$this->ticket = $this->get('Item');
		$this->form   = $this->get('Form');
	}

	protected function addToolbar()
	{
		$this->input->set('hidemainmenu', true);

		$canDo = DPCalendarHelper::getActions();

		if ($canDo->get('core.edit')) {
			JToolbarHelper::apply('ticket.apply');
			JToolbarHelper::save('ticket.save');
		}
		if (empty($this->ticket->id)) {
			JToolbarHelper::cancel('ticket.cancel');
		} else {
			JToolbarHelper::cancel('ticket.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();
		parent::addToolbar();
	}
}

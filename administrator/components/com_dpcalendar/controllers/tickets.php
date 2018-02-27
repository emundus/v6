<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.controlleradmin');

class DPCalendarControllerTickets extends JControllerAdmin
{

	protected $text_prefix = 'COM_DPCALENDAR_TICKET';

	public function csvexport()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$fields                = array();
		$fields['uid']         = JText::_('JGRID_HEADING_ID');
		$fields['status']      = JText::_('JSTATUS');
		$fields['name']        = JText::_('COM_DPCALENDAR_BOOKING_FIELD_NAME_LABEL');
		$fields['event_title'] = JText::_('COM_DPCALENDAR_EVENT');
		$fields['email']       = JText::_('COM_DPCALENDAR_BOOKING_FIELD_EMAIL_LABEL');
		$fields['telephone']   = JText::_('COM_DPCALENDAR_BOOKING_FIELD_TELEPHONE_LABEL');
		$fields['country']     = JText::_('COM_DPCALENDAR_LOCATION_FIELD_COUNTRY_LABEL');
		$fields['province']    = JText::_('COM_DPCALENDAR_LOCATION_FIELD_PROVINCE_LABEL');
		$fields['city']        = JText::_('COM_DPCALENDAR_LOCATION_FIELD_CITY_LABEL');
		$fields['zip']         = JText::_('COM_DPCALENDAR_LOCATION_FIELD_ZIP_LABEL');
		$fields['street']      = JText::_('COM_DPCALENDAR_LOCATION_FIELD_STREET_LABEL');
		$fields['number']      = JText::_('COM_DPCALENDAR_LOCATION_FIELD_NUMBER_LABEL');
		$fields['price']       = JText::_('COM_DPCALENDAR_BOOKING_FIELD_PRICE_LABEL');
		$fields['seat']        = JText::_('COM_DPCALENDAR_TICKET_FIELD_SEAT_LABEL');
		$fields['user_name']   = JText::_('JGLOBAL_USERNAME');
		$fields['created']     = JText::_('JGLOBAL_CREATED');
		$fields['type']        = JText::_('COM_DPCALENDAR_TICKET_FIELD_TYPE_LABEL');

		$parser = function ($name, $ticket) {
			switch ($name) {
				case 'status':
					return \DPCalendar\Helper\Booking::getStatusLabel($ticket);
				case 'created':
					return DPCalendarHelper::getDate($ticket->$name)->format('c');
				case 'type':

					return $this->getModel('Booking')->getEvent($ticket->event_id)->price->label[$ticket->type];
				default:
					return $ticket->$name;
			}
		};
		DPCalendarHelper::exportCsv('ticket', $fields, $parser);
	}

	public function getModel($name = 'Ticket', $prefix = 'DPCalendarModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}

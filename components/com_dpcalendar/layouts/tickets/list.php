<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$tickets = $displayData['tickets'];
if (! $tickets)
{
	return;
}

$params = $displayData['params'];
if (! $params)
{
	$params = clone JComponentHelper::getParams('com_dpcalendar');
}

$hasPrice = false;
foreach ($tickets as $ticket)
{
	if ($ticket->price && $ticket->price != '0.00')
	{
		$hasPrice = true;
		break;
	}
}
$limited = $params->get('event_show_tickets') == '2';
?>

<table class="table table-striped">
	<thead>
		<tr>
			<?php if (!$limited)
			{
				echo '<th>' . JText::_('COM_DPCALENDAR_BOOKING_FIELD_ID_LABEL') . '</th>';
			}

			if (!$limited && $params->get('display_list_event', true))
			{
				echo '<th>' . JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL') . '</th>';
			}

			if (!$limited && $params->get('display_list_date', true))
			{
				echo '<th>' . JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_DATE') . '</th>';
			}

			if (!$limited)
			{
				echo '<th>' . JText::_('COM_DPCALENDAR_VIEW_EVENTS_MODAL_COLUMN_STATE') . '</th>';
			}

			echo '<th>' . JText::_('COM_DPCALENDAR_BOOKING_FIELD_NAME_LABEL') . '</th>';
			echo '<th>' . JText::_('COM_DPCALENDAR_LOCATION') . '</th>';

			if (!$limited)
			{
				echo '<th>' . JText::_('COM_DPCALENDAR_CREATED_DATE') . '</th>';
			}

			if (!$limited)
			{
				echo '<th>' . JText::_('COM_DPCALENDAR_TICKET_FIELD_SEAT_LABEL') . '</th>';
			}

			if ($hasPrice && !$limited)
			{
				echo '<th>' . JText::_('COM_DPCALENDAR_BOOKING_FIELD_PRICE_LABEL') . '</th>';
			}
			?>
		</tr>
	</thead>

	<tbody>
		<?php foreach ($tickets as $ticket)
		{
		?>
			<tr>
				<?php
				if (!$limited)
				{
				?>
				<td>
				<?php if ($ticket->params->get('access-edit'))
				{?>
					<span class="width-20">
						<a href="<?php echo DPCalendarHelperRoute::getTicketFormRoute($ticket->id);?>"><i class="hasTooltip icon-edit" title="<?php echo JText::_('JACTION_EDIT');?>"></i></a>
					</span>
				<?php
				}?>
					<a href="<?php echo DPCalendarHelperRoute::getTicketRoute($ticket, true)?>"><?php echo $this->escape(JHtmlString::abridge($ticket->uid, 15, 5));?></a>
				</td>
				<?php
				}

				if (!$limited && $params->get('display_list_event', true))
				{
				?>
				<td><a href="<?php echo DPCalendarHelperRoute::getEventRoute($ticket->event_id, $ticket->event_calid)?>"><?php echo $this->escape($ticket->event_title);?></a></td>
				<?php
				}

				if (!$limited && $params->get('display_list_date', true))
				{
					echo '<td>' . DPCalendarHelper::getDateStringFromEvent($ticket) . '</td>';
				}

				if (!$limited)
				{
					echo '<td>' . $this->escape(DPCalendarHelperBooking::getStatusLabel($ticket)) . '</td>';
				}

				echo '<td>' . $this->escape($ticket->name) . '</td>';
				echo '<td>' . $this->escape(DPCalendarHelperLocation::format(array($ticket))) . '</td>';

				if (!$limited)
				{
					echo '<td>' . DPCalendarHelper::getDate($ticket->created)->format($params->get('event_date_format', 'm.d.Y') . ' ' . $params->get('event_time_format', 'g:i a')) . '</td>';
				}

				if (!$limited)
				{
					echo '<td>' . $this->escape($ticket->seat) . '</td>';
				}

				if ($hasPrice && !$limited)
				{
					echo '<td>' . DPCalendarHelper::renderPrice($ticket->price) . '</td>';
				}
				?>
			</tr>
		<?php
		}?>
	</tbody>
</table>
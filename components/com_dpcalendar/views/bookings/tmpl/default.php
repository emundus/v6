<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
DPCalendarHelper::loadLibrary(array(
		'jquery' => true,
		'bootstrap' => true,
		'chosen' => true,
		'dpcalendar' => true
));
JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/helpers/html');
JHtml::_('behavior.tooltip');

$user = JFactory::getUser();
$params = $this->params;

$doc = JFactory::getDocument();
$doc->addStyleDeclaration('.dpcalendar #filter-search{margin:0}
@media print {
	.noprint, .event-button {
		display: none !important;
	}
	a:link:after, a:visited:after {
		display: none;
		content: "";
	}
}');

echo JLayoutHelper::render('user.timezone');

if ($this->params->get('show_page_heading'))
{?>
<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&view=bookings&Itemid=' . JRequest::getInt('Itemid'));?>" method="post" name="adminForm"
	id="adminForm" class="form-inline dp-container">
	<div class="filters row-fluid row">
	<div class="span10 col-md-10 noprint">
		<?php
		$button = JHtml::_('dpcalendaricon.printWindow', 'adminForm', false, false);
		if ($button)
		{
			echo $button;
		}
		?>
	</div>
	<div class="span2 col-md-2 noprint">
		<div class="btn-group pull-right">
			<label for="limit" class="element-invisible">
				<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
			</label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
	</div>
	</div>
	<hr/>
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_ID_LABEL')?></th>
				<th><?php echo JText::_('COM_DPCALENDAR_VIEW_EVENTS_MODAL_COLUMN_STATE')?></th>
				<th><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_NAME_LABEL')?></th>
				<th><?php echo JText::_('COM_DPCALENDAR_INVOICE_DATE')?></th>
				<th><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_PRICE_LABEL')?></th>
				<th><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_TICKETS_LABEL')?></th>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($this->bookings as $booking)
			{
			?>
				<tr>
					<td>
						<span class="width-20">
							<a href="<?php echo DPCalendarHelperRoute::getBookingFormRoute($booking->id);?>"><i class="hasTooltip icon-edit" title="<?php echo JText::_('JACTION_EDIT');?>"></i></a>
						</span>
						<a href="<?php echo DPCalendarHelperRoute::getBookingRoute($booking)?>"><?php echo $this->escape(JHtmlString::abridge($booking->uid, 15, 5));?></a>
					</td>
					<td><?php echo $this->escape(DPCalendarHelperBooking::getStatusLabel($booking));?></td>
					<td><?php echo $this->escape($booking->name);?></td>
					<td><?php echo DPCalendarHelper::getDate($booking->book_date)->format($params->get('event_date_format', 'm.d.Y') . ' ' . $params->get('event_time_format', 'g:i a'));?></td>
					<td><?php echo DPCalendarHelper::renderPrice($booking->price, $params->get('currency_symbol', '$'));?></td>
					<td><a href="<?php echo DPCalendarHelperRoute::getTicketsRoute($booking->id)?>"><?php echo $this->escape($booking->amount_tickets);?></a></td>
				</tr>
			<?php
			}?>
		</tbody>
	</table>

	<input type="hidden" name="limitstart" value="" />

	<div class="pagination">
		<p class="counter pull-right">
			<?php echo $this->pagination->getPagesCounter(); ?>
		</p>
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
</form>

<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::addIncludePath(JPATH_SITE . '/components/com_dpcalendar/helpers');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

// Clear button doesn't clear the event_id field
JFactory::getDocument()->addScriptDeclaration("jQuery(document).ready(function() {
	jQuery('.js-stools-btn-clear').click(function() {
		jQuery('#filter_event_id_id').val('');
	});
});");

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', 'com_dpcalendar');
?>
<script type="text/javascript">
	Joomla.orderTable = function() {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&view=tickets'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<table class="table table-striped" id="locationsList">
			<thead>
				<tr>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="10%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th class="title">
						<?php echo JHtml::_('searchtools.sort', 'COM_DPCALENDAR_BOOKING_FIELD_NAME_LABEL', 'a.name', $listDirn, $listOrder); ?>
					</th>
					<th class="title">
						<?php echo JHtml::_('searchtools.sort', 'COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL', 'event_title', $listDirn, $listOrder); ?>
					</th>
					<th width="20%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_USERNAME', 'booking_name', $listDirn, $listOrder); ?>
					</th>
					<th width="15%">
						<?php echo JHtml::_('searchtools.sort', 'JDATE', 'e.start_date', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap hidden-phone">
						<?php echo JText::_('COM_DPCALENDAR_LOCATION'); ?>
					</th>
					<th class="nowrap hidden-phone">
						<?php echo JText::_('COM_DPCALENDAR_ACTION'); ?>
					</th>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="10">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item)
			{
				$canEdit = $user->authorise('core.edit', 'com_dpcalendar');
				$canChange = $user->authorise('core.edit.state', 'com_dpcalendar');
				?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="">
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center hidden-phone">
						<?php echo DPCalendarHelperBooking::getStatusLabel($item);?>
					</td>
					<td class="nowrap">
						<?php if ($canEdit)
						{ ?>
							<a href="<?php echo JRoute::_('index.php?option=com_dpcalendar&task=ticket.edit&id=' . (int) $item->id)?>">
								<?php echo $this->escape($item->name); ?></a>
						<?php
						}
						else
						{
							echo $this->escape($item->name);
						} ?>
					</td>
					<td class="">
					<?php if ($canEdit)
						{ ?>
							<a href="<?php echo DPCalendarHelperRoute::getFormRoute($item->event_id, JUri::getInstance()->toString()); ?>">
								<?php echo $this->escape($item->event_title); ?>
							</a>
						<?php
						}
						else
						{
							echo $this->escape($item->event_title);
						} ?>
					</td>
					<td class="hidden-phone">
						<?php echo $this->escape($item->user_name); ?>
					</td>
					<td class="small hidden-phone">
						<?php echo DPCalendarHelper::getDateStringFromEvent($item); ?>
					</td>
					<td class="hidden-phone">
						<?php echo DPCalendarHelperLocation::format($item); ?>
					</td>
					<td class="hidden-phone">
						<?php echo JHtml::_('dpcalendaricon.pdfticket', $item->uid, false);?>
						<?php echo JHtml::_('dpcalendaricon.pdfticketsend', $item->uid, false);?>
					</td>
					<td class="center hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
				<?php
				} ?>
			</tbody>
		</table>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>

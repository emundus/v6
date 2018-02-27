<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.multiselect');

$input = JFactory::getApplication()->input;
$field = $input->getCmd('field');
$function = 'jSelectUser_' . $field;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_dpcalendar&view=bookings&layout=modal&tmpl=component&excluded=' . $input->get('excluded', '', 'BASE64')); ?>"
	method="post" name="adminForm" id="adminForm">
	<fieldset class="filter">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('JSEARCH_FILTER'); ?></label>
				<input type="text" name="filter_search" id="filter_search"
				       placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>"
				       value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip"
				       title="<?php echo JHtml::tooltipText('COM_DPCALENDAR_SEARCH_IN_NAME'); ?>" data-placement="bottom"/>
			</div>
			<div class="btn-group pull-left hidden-phone">
				<button type="submit" class="btn hasTooltip"
				        title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>" data-placement="bottom"><i
						class="icon-search"></i></button>
				<button type="button" class="btn hasTooltip"
				        title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" data-placement="bottom"
				        onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i>
				</button>
				<button type="button" class="btn"
				        onclick="if (window.parent) window.parent.<?php echo $this->escape($function); ?>('', '<?php echo JText::_('JLIB_FORM_SELECT_USER'); ?>');"><?php echo JText::_('JOPTION_NO_USER'); ?></button>
			</div>
		</div>
	</fieldset>

	<table class="table table-striped table-condensed">
		<thead>
		<tr>
			<th class="title">
				<?php echo JHtml::_('grid.sort', 'COM_DPCALENDAR_BOOKING_FIELD_NAME_LABEL', 'a.name', $listDirn, $listOrder); ?>
			</th>
			<th class="title">
				<?php echo JHtml::_('grid.sort', 'COM_DPCALENDAR_EVENT', 'a.event_title', $listDirn, $listOrder); ?>
			</th>
			<th style="width: 10%" class="nowrap hidden-phone">
				<?php echo JHtml::_('grid.sort', 'JGLOBAL_CREATED', 'a.book_date', $listDirn, $listOrder); ?>
			</th>
			<th style="width: 1%" class="nowrap center hidden-phone">
				<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
			</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="15">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
		</tfoot>
		<tbody>
		<?php
		$i = 0;

		foreach ($this->items as $item)
		{ ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<a class="pointer"
					   onclick="if (window.parent) window.parent.<?php echo $this->escape($function); ?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->name)); ?>', '<?php echo $item->event_id; ?>');">
						<?php echo $this->escape($item->name); ?></a><br />
					<span class="small">
							<?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_EMAIL_LABEL') . ': ' . $this->escape($item->email);?>
						</span>
					<div class="small">
						<?php echo $this->escape($item->telephone); ?>
					</div>
				</td>
				<td style="text-align: center">
					<?php echo $this->escape($item->event_title); ?>
				</td>
				<td class="hidden-phone">
					<?php echo DPCalendarHelper::getDate($item->book_date)->format(DPCalendarHelper::getComponentParameter('event_date_format', 'm.d.Y')
						. ' ' . DPCalendarHelper::getComponentParameter('event_time_format', 'g:i a')); ?>
				</td>
				<td class="center hidden-phone">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
		<?php
		} ?>
		</tbody>
	</table>
	<div>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="field" value="<?php echo $this->escape($field); ?>"/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

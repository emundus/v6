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

if ($this->event)
{
	JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_DPCALENDAR_VIEW_TICKETS_SHOW_FROM_EVENT', $this->escape($this->event->title)));
}
if ($this->booking)
{
	JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_DPCALENDAR_VIEW_TICKETS_SHOW_FROM_BOOKING', $this->escape($this->booking->uid)));
}

echo JLayoutHelper::render('user.timezone');

if ($this->params->get('show_page_heading'))
{?>
<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php
}
?>
<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString());?>" method="post" name="adminForm"
	id="adminForm" class="form-inline dp-container">
	<div class="filters row-fluid row">
	<div class="span10 col-md-10 noprint">
		<input type="text" name="filter[search]" id="filter_search"
			value="<?php echo htmlspecialchars($this->escape($this->state->get('filter.search')))?>" class="input-xlarge"
			onchange="this.form.submit();" placeholder="<?php echo htmlspecialchars(JText::_('JGLOBAL_FILTER_LABEL'));?>" />
		<?php
		$button = JHtml::_('dpcalendaricon.printWindow', 'adminForm', false, false);
		if ($button)
		{
			echo $button;
		}

		if (!$this->event && !$this->booking)
		{
		?>
		<input type="checkbox" name="filter[future]" id="filter_future"
			value="1" <?php echo $this->state->get('filter.future') == 1 ? 'checked="checked"' : ''?> onchange="this.form.submit();">
		<label class="checkbox" for="filter[future]">
			<?php echo JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_FUTURE')?>
		</label>
		<?php
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

	<?php
	echo JLayoutHelper::render('tickets.list', array(
				'tickets' => $this->tickets,
				'params' => $params
		));
	?>

	<input type="hidden" name="limitstart" value="" />

	<div class="pagination">
		<p class="counter pull-right">
			<?php echo $this->pagination->getPagesCounter(); ?>
		</p>
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
</form>

<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (!$this->params->get('event_show_tickets', 0) || !isset($this->event->tickets) || !$this->event->tickets)
{
	return;
}

$this->params->set('display_list_event', false);
$this->params->set('display_list_date', false);
?>

<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_VIEW_EVENT_TICKETS_LABEL');?>: </dt>
	<dd class="event-content">
		<?php
		echo JLayoutHelper::render('tickets.list', array(
				'tickets' => $this->event->tickets,
				'params' => $this->params
		));
		?>
	</dd>
</dl>

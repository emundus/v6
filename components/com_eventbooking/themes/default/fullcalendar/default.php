<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$pageHeading = $this->params->get('page_heading') ?: Text::_('EB_CALENDAR');

HTMLHelper::_('bootstrap.tooltip');
?>
<div id="eb-calendar-page" class="eb-container">
    <?php
    if ($this->params->get('show_page_heading', 1))
    {
    ?>
        <h1 class="eb-page-heading"><?php echo $this->escape($pageHeading); ?></h1>
    <?php
    }

    if (EventbookingHelper::isValidMessage($this->params->get('intro_text')))
    {
    ?>
        <div class="eb-description"><?php echo $this->params->get('intro_text');?></div>
    <?php
    }
    ?>
	<div id='eb_full_calendar'></div>
</div>

<script>
	var calendarOptions = <?php echo json_encode($this->getCalendarOptions()); ?>;
	(function ($) {
		eventRenderFunc = function (event, element) {
			if (event.thumb)
			{
				element.find('.fc-content').prepend('<img src="' + event.thumb + '" title="' + event.title + '" class="img-polaroid" border="0" align="top" />');
			}

			if (event.tooltip)
            {
                element.tooltip({
                    title: event.tooltip,
                    trigger: 'hover',
                    placement: 'top',
                    container: 'body',
                    html: true,
                    sanitize: false
                });
            }

			if (event.eventFull == '1')
            {
                element.addClass('eb-event-full');
            }
		};

		calendarOptions['eventRender'] = eventRenderFunc;

		$(document).ready(function () {
			$('#eb_full_calendar').fullCalendar(
				calendarOptions
			);
		});
	}(jQuery));
</script>

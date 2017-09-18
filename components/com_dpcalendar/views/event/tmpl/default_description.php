<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (!$this->event->description)
{
	return;
}
?>
<h2 class="dpcal-event-header"><?php echo JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_DESCRIPTION');?></h2>
<div itemprop="description">
<?php echo $this->event->description;?>
</div>

<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

// Process content plugins
$message = JHTML::_('content.prepare', DPCalendarHelper::getComponentParameter('canceltext', null));
?>

<h1 class="componentheading">
	<?php echo $this->escape(JText::_('COM_DPCALENDAR_VIEW_BOOKING_MESSAGE_SORRY')) ?>
</h1>

<?php echo $message?>

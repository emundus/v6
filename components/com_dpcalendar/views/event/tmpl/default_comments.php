<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$comments = JHtml::_('share.comment', $this->params, $this->event);
if ($comments)
{
	echo '<h2 class="dpcal-event-header">' . JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_COMMENTS') . '</h2>';
	echo '<div class="noprint">' . $comments . '</div>';
}

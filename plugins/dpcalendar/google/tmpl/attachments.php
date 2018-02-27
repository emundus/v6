<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (!$googleEvent->attachments) {
	return;
}

$buffer = '<p class="dp-event-google-attachments">';
$buffer .= '<p class="dp-event-google-attachments-title"><strong>' . JText::_('PLG_DPCALENDAR_GOOGLE_ATTACHMENTS') . '</strong></p>';
foreach ($googleEvent->attachments as $attachment) {
	$buffer .= '<p class="dp-event-google-attachment">';
	$buffer .= ' <img src="' . $attachment['iconLink'] . '"/>';
	$buffer .= ' <a href="' . $attachment['fileUrl'] . '">' . $attachment['title'] . '</a>';
	$buffer .= '</p>';
}
return $buffer . '</p>';

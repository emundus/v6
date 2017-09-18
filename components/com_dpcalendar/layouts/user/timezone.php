<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (DPCalendarHelper::getComponentParameter('enable_tz_switcher', '0') == '0')
{
	return;
}

DPCalendarHelper::loadLibrary(array(
		'chosen' => '#dpcalendar-user-timezone'
));
JFactory::getLanguage()->load('com_dpcalendar', JPATH_ADMINISTRATOR . '/components/com_dpcalendar');

$regions = array(
		'Africa' => DateTimeZone::AFRICA,
		'America' => DateTimeZone::AMERICA,
		'Antarctica' => DateTimeZone::ANTARCTICA,
		'Aisa' => DateTimeZone::ASIA,
		'Atlantic' => DateTimeZone::ATLANTIC,
		'Europe' => DateTimeZone::EUROPE,
		'Indian' => DateTimeZone::INDIAN,
		'Pacific' => DateTimeZone::PACIFIC
);

$timezones = array();
foreach ($regions as $name => $mask)
{
	$zones = DateTimeZone::listIdentifiers($mask);
	foreach ($zones as $timezone)
	{
		$timezones[$name][$timezone] = $timezone;
	}
}

$date = DPCalendarHelper::getDate();
$actualTimezone = JFactory::getSession()->get('user-timezone', $date->getTimezone()
	->getName(), 'DPCalendar');

echo '<form action="' . JRoute::_(JUri::base()) . '" method="GET">';
echo JText::_('COM_DPCALENDAR_CHOOSE_TIMEZONE') . ': ';
echo '<select name="tz" id="dpcalendar-user-timezone" onchange="this.form.submit()">';
echo '<option value="UTC" ' . ($actualTimezone == 'UTC' ? 'selected="selected"' : '') . '>' . JText::_('JLIB_FORM_VALUE_TIMEZONE_UTC') . '</option>';
foreach ($timezones as $region => $list)
{
	echo '<optgroup label="' . $region . '">' . "\n";
	foreach ($list as $timezone => $name)
	{
		$selected = '';
		if ($name == $actualTimezone)
		{
			$selected = ' selected="selected"';
		}
		$name = explode('/', $name, 2);
		echo '<option value="' . $timezone . '"' . $selected . '>' . str_replace('_', ' ', $name[1]) . '</option>' . "\n";
	}
	echo '<optgroup>' . "\n";
}
echo '</select>';
echo '<input type="hidden" name="task" value="profile.tz">';
echo '<input type="hidden" name="return" value="' . base64_encode(JUri::getInstance()->toString()) . '">';
echo '<input type="hidden" name="option" value="com_dpcalendar">';
echo '<input type="hidden" name="view" value="profile">';
echo '</form>';

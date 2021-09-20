<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// Require library + register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

EventbookingHelper::loadLanguage();
EventbookingHelper::loadComponentCssForModules();

$config = EventbookingHelper::getConfig();

Factory::getDocument()->addScriptDeclaration(
	'var siteUrl = "' . EventbookingHelper::getSiteUrl() . '";'
);

EventbookingHelper::addLangLinkForAjax();

HTMLHelper::_('jquery.framework');
HTMLHelper::_('script', 'media/com_eventbooking/assets/js/eventbookingjq.js', false, false);
HTMLHelper::_('script', 'media/com_eventbooking/assets/js/minicalendar.js', false, false);

$currentDateData = EventbookingModelCalendar::getCurrentDateData();
$year            = $currentDateData['year'];
$month           = (int) $params->get('default_month', 0);
$categoryId      = (int) $params->get('id', 0);

$Itemid = (int) $params->get('item_id');

if (!$Itemid)
{
	$Itemid = EventbookingHelperRoute::findView('calendar');
}

if (!$month)
{
	$month = $currentDateData['month'];
}

// Get calendar data for the current month and year
$model = RADModel::getTempInstance('Calendar', 'EventbookingModel');
$model->setState('month', $month)
	->setState('year', $year)
	->setState('id', $categoryId)
	->setState('mini_calendar', 1);

if ($Itemid)
{
	$model->setState('mini_calendar_item_id', $Itemid);
}

$rows = $model->getData();
$data = EventbookingHelperData::getCalendarData($rows, $year, $month, true);

$days     = [];
$startDay = (int) $config->calendar_start_date;

for ($i = 0; $i < 7; $i++)
{
	$days[$i] = EventbookingHelperData::getDayNameHtmlMini(($i + $startDay) % 7, true);
}
	
$listMonth = [
			Text::_('JANUARY'),
			Text::_('FEBRUARY'),
			Text::_('MARCH'),
			Text::_('APRIL'),
			Text::_('MAY'),
			Text::_('JUNE'),
			Text::_('JULY'),
			Text::_('AUGUST'),
			Text::_('SEPTEMBER'),
			Text::_('OCTOBER'),
			Text::_('NOVEMBER'),
			Text::_('DECEMBER'),];

if (!$Itemid)
{
	$Itemid = EventbookingHelper::getItemid();
}

require JModuleHelper::getLayoutPath('mod_eb_minicalendar', 'default');


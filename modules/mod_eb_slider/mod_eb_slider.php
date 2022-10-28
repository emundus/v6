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
use Joomla\CMS\Uri\Uri;

// Require library + register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

// Require module helper
require_once __DIR__ . '/helper.php';

$document = Factory::getDocument();
$user     = Factory::getUser();
$config   = EventbookingHelper::getConfig();
$baseUrl  = Uri::base(true);

// Load component language
EventbookingHelper::loadLanguage();

$itemId = (int) $params->get('item_id', 0) ?: EventbookingHelper::getItemid();

$rows = modEBSliderHelper::getData($params);

$sliderSettings = [
	'container'            => '.my-eb-slider',
	'loop'                 => true,
	'slideBy'              => 'page',
	'nav'                  => false,
	'autoplay'             => (bool) $params->get('autoplay', 1),
	'speed'                => (int) $params->get('speed', 300),
	'autoplayButtonOutput' => false,
	'mouseDrag'            => false,
	'lazyload'             => true,
	'controlsContainer'    => '#customize-controls',
];

$numberItemsXs = $params->get('number_items_xs', 0);
$numberItemsSm = $params->get('number_items_sm', 0);
$numberItemsMd = $params->get('number_items_md', 0);
$numberItemsLg = $params->get('number_items_lg', 0);

if ($numberItemsXs)
{
	$sliderSettings['responsive'][576]['items'] = $numberItemsXs;
}

if ($numberItemsSm)
{
	$sliderSettings['responsive'][768]['items'] = $numberItemsSm;
}

if ($numberItemsMd)
{
	$sliderSettings['responsive'][992]['items'] = $numberItemsMd;
}

if ($numberItemsLg)
{
	$sliderSettings['responsive'][1200]['items'] = $numberItemsLg;
}

if (!array_key_exists('responsive', $sliderSettings))
{
	$sliderSettings['items'] = $params->get('number_items', 3);
}

EventbookingHelper::loadComponentCssForModules();

require JModuleHelper::getLayoutPath('mod_eb_slider', 'default');

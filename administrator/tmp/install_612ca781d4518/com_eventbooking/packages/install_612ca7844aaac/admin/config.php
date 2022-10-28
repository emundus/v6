<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Factory;

$config = [
	'class_prefix'    => 'Eventbooking',
	'language_prefix' => 'EB',
	'table_prefix'    => '#__eb_',
];

if (class_exists('EventbookingControllerOverrideController'))
{
	$config['default_controller_class'] = 'EventbookingControllerOverrideController';
}
else
{
	$config['default_controller_class'] = 'EventbookingController';
}

if (Factory::getApplication()->isClient('administrator'))
{
	$config['default_view'] = 'dashboard';
}
else
{
	$config['default_view'] = 'categories';
}

return $config;
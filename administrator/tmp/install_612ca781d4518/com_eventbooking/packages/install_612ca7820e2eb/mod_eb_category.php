<?php
/**
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2021 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

// Require library + register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

EventbookingHelper::loadLanguage();

$numberCategories = (int) $params->get('number_categories', 0) ?: 1000;
$parentId         = (int) $params->get('parent_id', 0);

/* @var EventbookingModelCategories $model */
$model = RADModel::getTempInstance('Categories', 'EventbookingModel', ['table_prefix' => '#__eb_']);

$model->getParams()->set('category_ids', $params->get('category_ids', []));
$model->getParams()->set('exclude_category_ids', $params->get('exclude_category_ids', []));

$model->setState('id', $parentId)
	->setState('limit', $numberCategories);

$rows = $model->getData();

$config = EventbookingHelper::getConfig();
$itemId = (int) $params->get('item_id') ?: EventbookingHelper::getItemid();

if (count($rows))
{
	require JModuleHelper::getLayoutPath('mod_eb_category', 'default');
}

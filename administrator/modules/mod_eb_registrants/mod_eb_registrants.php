<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

JLoader::register('EventbookingModelRegistrants', JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/registrants.php');
$model = RADModel::getInstance('Registrants', 'EventbookingModel', ['ignore_request' => true, 'remember_states' => false]);

$model->setState('limitstart', 0)
	->setState('limit', $params->get('count', 5))
	->setState('filter_order', 'tbl.id')
	->setState('filter_order_Dir', 'DESC');

/* @var EventbookingModelRegistrants $model */
$rows  = $model->getData();
$count = (int) $params->get('count', 1);

require JModuleHelper::getLayoutPath('mod_eb_registrants');


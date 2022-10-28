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

// Add base AjaxURL to use in JS
$baseAjaxUrl = Uri::root(true) . '/index.php?option=com_eventbooking' . EventbookingHelper::getLangLink() . '&time=' . time();
Factory::getDocument()->addScriptDeclaration('var EBBaseAjaxUrl = "' . $baseAjaxUrl . '";');

if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
{
	$source = Factory::getApplication()->input;
}
else
{
	$source = null;
}

EventbookingHelper::prepareRequestData();

$input = new RADInput($source);

$config = require JPATH_ADMINISTRATOR . '/components/com_eventbooking/config.php';

RADController::getInstance($input->getCmd('option', null), $input, $config)
	->execute()
	->redirect();

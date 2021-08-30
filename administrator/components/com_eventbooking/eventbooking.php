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
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;

//Basic ACL support
if (!Factory::getUser()->authorise('core.manage', 'com_eventbooking'))
{
	return JError::raiseWarning(404, Text::_('JERROR_ALERTNOAUTHOR'));
}

// Require library + register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

if (EventbookingHelper::isJoomla4())
{
	HTMLHelper::_('jquery.framework');
}

if (Multilanguage::isEnabled() && !EventbookingHelper::isSynchronized())
{
	EventbookingHelper::callOverridableHelperMethod('Helper', 'setupMultilingual');
}

if (isset($_POST['language']))
{
	$_REQUEST['language'] = $_POST['language'];
}

$config = require JPATH_ADMINISTRATOR . '/components/com_eventbooking/config.php';
$input  = new RADInput();
RADController::getInstance($input->getCmd('option'), $input, $config)
	->execute()
	->redirect();

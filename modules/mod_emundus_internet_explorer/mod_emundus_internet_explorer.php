<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Access Denied');
require_once(dirname(__FILE__).DS.'helper.php');

$session = Factory::getSession();

$compatible = modEmundusInternetExplorerHelper::isCompatible();

$layout  = substr($params->get('layout', 'simple'), 2);
$message = $params->get('message', Text::_('TEXT_DEFAULT'));

$document = Factory::getDocument();
$document->addStyleSheet("modules/mod_emundus_internet_explorer/style/mod_emundus_internet_explorer.css");

require(ModuleHelper::getLayoutPath('mod_emundus_internet_explorer', $layout));
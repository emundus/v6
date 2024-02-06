<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Access Denied');
require_once(dirname(__FILE__).DS.'helper.php');

$session = Factory::getSession();

$browser = modEmundusInternetExplorerHelper::getBrowser();

$min_versions = [
	'Chrome' => 88,
	'Firefox' => 85,
	'Edge' => 88,
	'Opera' => 74,
	'Safari' => 15
];

$compatible = true;
if((!empty($min_versions[$browser['name']]) && $browser['version'] < $min_versions[$browser['name']]) || $browser['name'] == 'Internet Explorer') {
	$compatible = false;
}

$layout  = substr($params->get('layout', 'simple'), 2);
$message = $params->get('message', Text::_('TEXT_DEFAULT'));

$document = Factory::getDocument();
$document->addStyleSheet("modules/mod_emundus_internet_explorer/style/mod_emundus_internet_explorer.css");

require(ModuleHelper::getLayoutPath('mod_emundus_internet_explorer', $layout));



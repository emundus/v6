<?php
defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');

$document   = JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_information_complementaire/style/mod_emundus_information_complementaire.css" );

$helper = new modEmundusInformationComplementaireHelper;

$files = $helper->getFiles();

if($files != -1 && count($files) != 0)
	require(JModuleHelper::getLayoutPath('mod_emundus_information_complementaire','default.php'));
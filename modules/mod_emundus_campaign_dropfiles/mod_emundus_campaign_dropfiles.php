<?php
defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');

$document   = JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_campaign_dropfiles/style/mod_emundus_campaign_dropfiles.css" );

$helper = new modEmundusCampaignDropfilesHelper;

$files = $helper->getFiles();


if  (!empty($files)) {
    require(JModuleHelper::getLayoutPath('mod_emundus_campaign_dropfiles', 'default.php'));
}

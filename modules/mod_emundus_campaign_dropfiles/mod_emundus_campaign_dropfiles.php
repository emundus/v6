<?php
defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__) . DS . 'helper.php');

$document = JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_campaign_dropfiles/style/mod_emundus_campaign_dropfiles.css");

$helper = new modEmundusCampaignDropfilesHelper;

$mod_em_dropfile_column = $params->get('mod_em_dropfile_column');
$mod_em_dropfile_desc   = $params->get('mod_em_dropfile_desc');

$files = $helper->getFiles($mod_em_dropfile_column);


if (!empty($files)) {
	require(JModuleHelper::getLayoutPath('mod_emundus_campaign_dropfiles', 'default.php'));
}

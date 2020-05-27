<?php
defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'stats.php');

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$document   = JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_stat_filter/style/mod_emundus_stat_filter.css" );

$session = JFactory::getSession();
$session->set('filterStat', null);
if($session->get('filterStat') === null) {
	$array["prog"] = "-1";
	$array["year"] = "-1";
	$array["campaign"] = "-1";
	$session->set('filterStat', json_encode($array));
}

$helper = new modEmundusStatFilterHelper;

$tabProg		= $helper->getProg($session->get('filterStat'));
$tabYear		= $helper->getYear($session->get('filterStat'));
$tabCampaign	= $helper->getCampaign($session->get('filterStat'));


require(JModuleHelper::getLayoutPath('mod_emundus_stat_filter','default.php'));
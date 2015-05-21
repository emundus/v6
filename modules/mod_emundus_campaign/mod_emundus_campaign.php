<?php

defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');

JHtml::stylesheet(JURI::base() . 'media/com_emundus/css/mod_emundus_campaign.css');


$mod_em_campaign_url=$params->get('mod_em_campaign_url');
/*$mod_em_campaign_period=$params->get('mod_em_campaign_period');
$mod_em_campaign_period=$params->get('mod_em_campaign_period');*/
$mod_em_campaign_class=$params->get('mod_em_campaign_class');
$mod_em_campaign_start_date=$params->get('mod_em_campaign_start_date');
$mod_em_campaign_end_date=$params->get('mod_em_campaign_end_date');
$mod_em_campaign_list_tab=$params->get('mod_em_campaign_list_tab');
$mod_em_campaign_param_tab=$params->get('mod_em_campaign_param_tab');
$mod_em_campaign_display_groupby=$params->get('mod_em_campaign_display_groupby');
$mod_em_campaign_groupby=$params->get('mod_em_campaign_groupby');
$mod_em_campaign_order=$params->get('mod_em_campaign_orderby');
$mod_em_campaign_order_type=$params->get('mod_em_campaign_order_type');
$mod_em_campaign_class=$params->get('mod_em_campaign_order_type');
$mod_em_campaign_itemid=$params->get('mod_em_campaign_itemid');
$mod_em_campaign_itemid2=$params->get('mod_em_campaign_itemid2');
$showcampaign =$params->get('mod_em_campaign_param_showcampaign');
$showprogramme =$params->get('mod_em_campaign_param_showprogramme');

$condition ='';

$session = JFactory::getSession();

if (isset($_GET['order_date']) && !empty($_GET['order_date'])) {
    $session->set('order_date', $_GET['order_date']);
} else if(empty($order)){
    $session->set('order_date', $mod_em_campaign_order);
}
if (isset($_GET['order_time']) && !empty($_GET['order_time'])) {
    $session->set('order_time', $_GET['order_time']);
} else if(empty($order)){
    $session->set('order_time', $mod_em_campaign_order_type);
}
$order = $session->get('order_date');
$ordertime = $session->get('order_time');

if(isset($_POST['searchword']) && !empty($_POST['searchword'])) {
    $searchword=$_POST['searchword'];
    $condition = "AND CONCAT(pr.code,pr.notes,ca.label,pr.label,ca.description,ca.short_description) LIKE '%$searchword%'";
}


switch ($mod_em_campaign_groupby) {
    case 'month':
        if ($order=="start_date") {
            $condition .= ' ORDER BY start_date';
        } else {
            $condition .= ' ORDER BY end_date';
        }
        break;
    case 'program':
        if ($order=="start_date") {
            $condition .= ' ORDER BY training, start_date';
        } else {
            $condition .= ' ORDER BY training, end_date';
        }
        break;
}


switch ($ordertime) {
    case 'asc':
        $condition .=' ASC';
        break;
	case 'desc':
        $condition .=' DESC';
        break;
}

/*case 'out':
    $condition =' AND Now() >= ca.end_date and Now()<= ca.start_date';
    break;*/

$helper = new modEmundusCampaignHelper;

$currentCampaign = $helper->getCurrent($condition);
$pastCampaign = $helper->getPast($condition);
$futurCampaign = $helper->getFutur($condition);
$allCampaign = $helper->getProgram($condition);

jimport('joomla.html.pagination');
$session = JFactory::getSession();

$paginationCurrent = new JPagination($helper->getTotalCurrent(), $session->get('limitstartCurrent'), $session->get('limit'));
$paginationPast = new JPagination($helper->getTotalPast(), $session->get('limitstartPast'), $session->get('limit'));
$paginationFutur = new JPagination($helper->getTotalFutur(), $session->get('limitstartFutur'), $session->get('limit'));
$paginationTotal = new JPagination($helper->getTotal(), $session->get('limitstart'), $session->get('limit'));



require(JModuleHelper::getLayoutPath('mod_emundus_campaign'));

?>
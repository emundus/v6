<?php
defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$listUrl1 = $params->get('mod_em_list_id1');

$helper          = new modEmundusGraphsHelper;
$usersGraph      = $helper->getaccountType();
$consultationBar = $helper->consultationOffres();
$candidature     = $helper->candidatureOffres();
$connections     = $helper->getConnections();
$relations       = $helper->getRelations();

$distinctProfile = '';
$distinctOffres = '';
$distinctCandidatures = '';

foreach ($usersGraph as $users) {
    if (strpos($distinctProfile, $users['profile_id']) == false)
        $distinctProfile .= '<option value="'.$users['profile_id'].'">'.$users['profile_label'].'</option>';
}

foreach ($consultationBar as $c) {
    if (strpos($distinctOffres, $c['num_offre']) == false)
        $distinctOffres .= '<option value="'.$c['num_offre'].'">'.$c['titre'].'</option>';
}

foreach ($candidature as $cand) {
    if (strpos($distinctCandidatures, $cand['num_offre']) == false)
        $distinctCandidatures .= '<option value="'.$cand['num_offre'].'">'.$cand['titre'].'</option>';
}

require(JModuleHelper::getLayoutPath('mod_emundus_graphs','default.php'));
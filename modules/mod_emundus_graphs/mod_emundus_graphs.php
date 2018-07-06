<?php
defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'stats.php');

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$listUrl1 = $params->get('mod_em_list_id1');

$model  = new EmundusModelStats();
$helper = new modEmundusGraphsHelper;


$viewArray = [
    "jos_emundus_stats_nombre_candidature_offre" => "Nombre de candidatures",
    "jos_emundus_stats_nombre_comptes" => "Nombre de comptes",
    "jos_emundus_stats_nombre_connexions" => "Nombre de connexions",
    "jos_emundus_stats_nombre_consult_offre" => "Nombre de consultations",
    "jos_emundus_stats_nombre_relations_etablies" => "Nombre de relations établies",
    "jos_emundus_stats_nationality" => "Nationalité",
    "jos_emundus_stats_gender" => "Genre",
    "jos_emundus_stats_files" => "Dossiers"
];


// Set values to false, in quotes because sending it to Js, and Js doesn't show any value if false
$nationality = 'false';
$gender = 'false';
$files = 'false';
$cand = 'false';
$consult = 'false';
$comptes = 'false';
$con = 'false';
$rels = 'false';

// Loop to check which views are in the Db
foreach ($viewArray as $key => $value) {
    $exist = $model->viewExist($key);
    // $exist = 1 or 0
    if ($exist == 0) {
        $tableField .= '<tr><td>'.$value.'</td><td><button type="button" class="btn btn-primary" id="'.$key.'" onClick="addView(\''.$key.'\')">+</button></td></tr>';
    } else {
        switch($key) {
            case 'jos_emundus_stats_nombre_candidature_offre':
                $cand ='true';
                $candidature = $helper->candidatureOffres();
                break;

            case 'jos_emundus_stats_nombre_comptes':
                $comptes = 'true';
                $usersGraph = $helper->getaccountType();
                $distinctProfile = '';
                // create select options for different user types
                foreach ($usersGraph as $users) {
                    if (strpos($distinctProfile, $users['profile_id']) == false)
                        $distinctProfile .= '<option value="'.$users['profile_id'].'">'.$users['profile_label'].'</option>';
                }
                break;

            case 'jos_emundus_stats_nombre_connexions':
                $con = 'true';
                $connections = $helper->getConnections();
                break;

            case 'jos_emundus_stats_nombre_consult_offre':
                $consult = 'true';
                $consultationBar = $helper->consultationOffres();
                break;

            case 'jos_emundus_stats_nombre_relations_etablies':
                $rels = 'true';
                $relations = $helper->getRelations();
            break;

            case 'jos_emundus_stats_nationality':
                $nationality = 'true';
            break;

            case 'jos_emundus_stats_files':
                $files = 'true';
            break;

            case 'jos_emundus_stats_gender':
                $gender = 'true';
            break;
        }
    }
}


require(JModuleHelper::getLayoutPath('mod_emundus_graphs','default.php'));
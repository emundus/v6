<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

$db = JFactory::getDbo();
$query = $db->getQuery(true);

require_once (JPATH_SITE.DS.'components/com_emundus/models'.DS.'profile.php');
$m_profile = new EmundusModelProfile;

$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;

// Get inputs
$fnum = $jinput->get('rowid');
$formid = $jinput->get('formid');
$itemid = $jinput->get('Itemid');
$view = $jinput->get('view');

$fnumsDetails = $m_profile->getFnumDetails($fnum);

// Check the limit of files by program and by user
$query->clear()
    ->select('cc.*')
    ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
    ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'sc') . ' ON ' . $db->quoteName('sc.id') . ' = ' . $db->quoteName('cc.campaign_id'))
    ->where($db->quoteName('sc.training') . ' = ' . $db->quote($fnumsDetails['training']))
    ->andWhere($db->quoteName('sc.year') . ' = ' . $db->quote($fnumsDetails['year']))
    ->andWhere($db->quoteName('cc.applicant_id') . ' = ' . $db->quote($fnumsDetails['user_id']));
$db->setQuery($query);
$applications = $db->loadObjectList();

$status = ["1","4","5","6","7","8","9","11","12","13","14","16","17","18","19"];

foreach ($status as $statu){
    $app_key = array_search($statu, array_column($applications, 'status'));
    if($app_key !== false){
        break;
    }
}

if ($app_key !== false && $view != 'details') {
    $mainframe->redirect('index.php?option=com_fabrik&view=details&formid=' . $formid . '&Itemid=' . $itemid . '&usekey=fnum&rowid=' . $fnum . '&r=1');
}
//

<?php

$db = JFactory::getDbo();
$query = $db->getQuery(true);

$user = JFactory::getUser()->id;

$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;

$campaign = $jinput->getInt('jos_emundus_campaign_candidature___campaign_id')[0];

$query->select('training')
    ->from($db->quoteName('#__emundus_setup_campaigns'))
    ->where($db->quoteName('id') . ' = ' . $db->quote($campaign));
$db->setQuery($query);
$prog = $db->loadResult();

$query->clear()
    ->select('lp.id,lp.max_applicant_files as max')
    ->from($db->quoteName('#__emundus_limit_by_program','lp'))
    ->leftJoin($db->quoteName('#__emundus_limit_by_program_repeat_programs','lpr').' ON '.$db->quoteName('lpr.parent_id').' = '.$db->quoteName('lp.id'))
    ->where($db->quoteName('lpr.programs') . ' = ' . $db->quote($prog));
$db->setQuery($query);
$max_applicant_files = $db->loadObject();

if(!empty($max_applicant_files)){
    $query->clear()
        ->select('programs')
        ->from($db->quoteName('#__emundus_limit_by_program_repeat_programs'))
        ->where($db->quoteName('parent_id') . ' = ' . $db->quote($max_applicant_files->id));
    $db->setQuery($query);
    $progs = $db->loadColumn();

    $query->clear()
        ->select('count(cc.id)')
        ->from($db->quoteName('#__emundus_campaign_candidature','cc'))
        ->leftJoin($db->quoteName('#__emundus_setup_campaigns','sc').' ON '.$db->quoteName('sc.id').' = '.$db->quoteName('cc.campaign_id'))
        ->where($db->quoteName('sc.training') . ' IN (' . implode(',',$db->quote($progs)) . ')')
        ->andWhere($db->quoteName('applicant_id') . ' = ' . $db->quote($user));
    $db->setQuery($query);
    $applications = $db->loadResult();

    if($applications >= (int)$max_applicant_files->max){
        $message = 'Vous ne pouvez pas créer plus de 4 dossiers en M1, 2 dossiers en M2';
        JFactory::getApplication()->redirect('liste-des-campagnes',$message);
    }
}

<?php

$db = JFactory::getDbo();
$query = $db->getQuery(true);

$user = JFactory::getUser()->id;

$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;

// write log file
jimport('joomla.log.log');
JLog::addLogger(['text_file' => 'com_emundus.error.php'], JLog::ERROR, 'com_emundus');

try {
    $campaign_id = $jinput->getInt('jos_emundus_campaign_candidature___campaign_id')[0];

    $query->select('dtfc.id,dtfc.max_wishes as max,dtfc.type_formation')
        ->from($db->quoteName('data_type_formation_campaign','dtfc'))
        ->leftJoin($db->quoteName('data_type_formation_campaign_repeat_campaigns','dtfcrc').' ON '.$db->quoteName('dtfcrc.parent_id').' = '.$db->quoteName('dtfc.id'))
        ->where($db->quoteName('dtfcrc.campaigns').' = '.$db->quote($campaign_id));
    $db->setQuery($query);
    $max_applicant_files = $db->loadObject();

    $query->clear()
        ->select($db->quoteName('year'))
        ->from($db->quoteName('#__emundus_setup_campaigns'))
        ->where($db->quoteName('id').' = '.$db->quote($campaign_id));
    $db->setQuery($query);
    $campaign_year = $db->loadResult();

    if(!empty($max_applicant_files)){
        $query->clear()
            ->select($db->quoteName('campaigns'))
            ->from($db->quoteName('data_type_formation_campaign_repeat_campaigns'))
            ->where($db->quoteName('parent_id').' = '.$db->quote($max_applicant_files->id));
        $db->setQuery($query);
        $campaigns = $db->loadColumn();

        $query->clear()
            ->select('count(cc.id)')
            ->from($db->quoteName('#__emundus_campaign_candidature','cc'))
            ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'sc').' ON '.$db->quoteName('sc.id').' = '.$db->quoteName('cc.campaign_id'))
            ->where($db->quoteName('cc.campaign_id').' IN ('.implode(',',$db->quote($campaigns)).')')
            ->andWhere($db->quoteName('cc.applicant_id').' = '.$db->quote($user))
            ->andWhere($db->quoteName('sc.year').' = '.$db->quote($campaign_year))
            ->andWhere($db->quoteName('cc.status').' NOT IN (0,10,13,15)');
        $db->setQuery($query);
        $applications = $db->loadResult();

        if($applications >= (int)$max_applicant_files->max){
            $message = 'Vous ne pouvez pas créer plus de '.$max_applicant_files->max.' dossiers en '.$max_applicant_files->type_formation;
            JFactory::getApplication()->redirect('liste-des-campagnes',$message);
        }
    }
} catch (Exception $e) {
    JLog::add('Error when checking file limit for user: '.$user.' -> '.$query->__toString().' -> '.$e->getMessage().' -> ', JLog::ERROR, 'com_emundus');
    JFactory::getApplication()->redirect('','Erreur lors de la vérification de la limite de dossiers');
}
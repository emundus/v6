<?php

$db = JFactory::getDbo();
$query = $db->getQuery(true);

$user = JFactory::getUser()->id;

$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;

$fnum = JFactory::getSession()->get('emundusUser')->fnum;
if (!empty($fnum)) {
    // if fnum is defined then we are sending the file and we can work with the fnum
    require_once(JPATH_SITE.'/components/com_emundus/models/files.php');
    $m_files = new EmundusModelFiles();

    $fnumInfos = $m_files->getFnumInfos($fnum);
    $campaign_id = $jinput->getInt('jos_emundus_campaign_candidature___campaign_id')[0];

    $query
        ->select('dtfc.id,dtfc.max_wishes as max,dtfc.type_formation')
        ->from($db->quoteName('data_type_formation_campaign','dtfc'))
        ->leftJoin($db->quoteName('data_type_formation_campaign_repeat_campaigns','dtfcrc').' ON '.$db->quoteName('dtfcrc.parent_id').' = '.$db->quoteName('dtfc.id'))
        ->where($db->quoteName('dtfcrc.campaigns') . ' = ' . $db->quote($campaign_id));
    $db->setQuery($query);
    $max_applicant_files = $db->loadObject();

    if(!empty($max_applicant_file)){
        $query->clear()
            ->select('campaigns')
            ->from($db->quoteName('data_type_formation_campaign_repeat_campaigns'))
            ->where($db->quoteName('parent_id') . ' = ' . $db->quote($max_applicant_files->id));
        $db->setQuery($query);
        $campaigns = $db->loadColumn();

        $query->clear()
            ->select('count(cc.id)')
            ->from($db->quoteName('#__emundus_campaign_candidature','cc'))
            ->where($db->quoteName('cc.campaign_id') . ' IN (' . implode(',',$db->quote($campaigns)) . ')')
            ->andWhere($db->quoteName('applicant_id') . ' = ' . $db->quote($user))
            ->andWhere($db->quoteName('cc.status') . ' NOT IN (0,10,13,15)');
        $db->setQuery($query);
        $applications = $db->loadResult();

        if($applications >= (int)$max_applicant_files->max){
            $message = 'Vous ne pouvez pas envoyer plus de '.$max_applicant_files->max.' dossiers en '.$max_applicant_files->type_formation;
            JFactory::getApplication()->redirect('',$message);
        }
    }
} else {
    // else, we are creating a file so we need to use campaign_id
    $campaign_id = $jinput->getInt('jos_emundus_campaign_candidature___campaign_id')[0];

    $query
        ->select('dtfc.id,dtfc.max_wishes as max,dtfc.type_formation')
        ->from($db->quoteName('data_type_formation_campaign','dtfc'))
        ->leftJoin($db->quoteName('data_type_formation_campaign_repeat_campaigns','dtfcrc').' ON '.$db->quoteName('dtfcrc.parent_id').' = '.$db->quoteName('dtfc.id'))
        ->where($db->quoteName('dtfcrc.campaigns') . ' = ' . $db->quote($campaign_id));
    $db->setQuery($query);
    $max_applicant_files = $db->loadObject();

    if(!empty($max_applicant_files)){
        $query->clear()
            ->select('campaigns')
            ->from($db->quoteName('data_type_formation_campaign_repeat_campaigns'))
            ->where($db->quoteName('parent_id') . ' = ' . $db->quote($max_applicant_files->id));
        $db->setQuery($query);
        $campaigns = $db->loadColumn();

        $query->clear()
            ->select('count(cc.id)')
            ->from($db->quoteName('#__emundus_campaign_candidature','cc'))
            ->where($db->quoteName('cc.campaign_id') . ' IN (' . implode(',',$db->quote($campaigns)) . ')')
            ->andWhere($db->quoteName('applicant_id') . ' = ' . $db->quote($user));
        $db->setQuery($query);
        $applications = $db->loadResult();

        if($applications >= (int)$max_applicant_files->max){
            $message = 'Vous ne pouvez pas créer plus de '.$max_applicant_files->max.' dossiers en '.$max_applicant_files->type_formation;
            JFactory::getApplication()->redirect('liste-des-campagnes',$message);
        }
    }
}

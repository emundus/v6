<?php
$db = JFactory::getDbo();
$query = $db->getQuery(true);

$user = JFactory::getUser();

$jinput = JFactory::getApplication()->input;
$cid = $jinput->getInt('cid');

$query->select('ee.id,ee.custom_fields,GROUP_CONCAT(ec.category_id) as categories')
    ->from($db->quoteName('#__emundus_setup_campaigns','sc'))
    ->leftJoin($db->quoteName('#__eb_events','ee').' ON '.$db->quoteName('ee.id').' = '.$db->quoteName('sc.event'))
    ->leftJoin($db->quoteName('#__eb_event_categories','ec').' ON '.$db->quoteName('ec.event_id').' = '.$db->quoteName('ee.id'))
    ->where($db->quoteName('sc.id') . ' = ' . $db->quote($cid));
$db->setQuery($query);
$event = $db->loadObject();

$facultes = json_decode($event->custom_fields)->field_faculte;
if(empty($facultes)){
    $facultes = [4,5,6];
}
$query->clear()
    ->select('distinct er.id')
    ->from($db->quoteName('#__emundus_eb_rules','er'))
    ->leftJoin($db->quoteName('#__emundus_eb_rules_repeat_eb_activities','era').' ON '.$db->quoteName('era.parent_id').' = '.$db->quoteName('er.id'))
    ->leftJoin($db->quoteName('#__emundus_eb_rules_repeat_eb_categories','erc').' ON '.$db->quoteName('erc.parent_id').' = '.$db->quoteName('er.id'))
    ->leftJoin($db->quoteName('#__emundus_eb_rules_repeat_facultes','erf').' ON '.$db->quoteName('erf.parent_id').' = '.$db->quoteName('er.id'))
    ->where($db->quoteName('erf.facultes') . ' IN (' . implode(',',$facultes) . ')')
    ->andWhere($db->quoteName('erc.eb_categories') . ' IN (' . $event->categories . ')');
$db->setQuery($query);
$rules = $db->loadColumn();

if(!empty($rules)){
    foreach ($rules as $rule){
        $query->clear()
            ->select('er.activities_number,GROUP_CONCAT(distinct era.eb_activities) as activities,GROUP_CONCAT(distinct erc.eb_categories) as categories,GROUP_CONCAT(distinct erf.facultes) as facultes')
            ->from($db->quoteName('#__emundus_eb_rules','er'))
            ->leftJoin($db->quoteName('#__emundus_eb_rules_repeat_eb_activities','era').' ON '.$db->quoteName('era.parent_id').' = '.$db->quoteName('er.id'))
            ->leftJoin($db->quoteName('#__emundus_eb_rules_repeat_eb_categories','erc').' ON '.$db->quoteName('erc.parent_id').' = '.$db->quoteName('er.id'))
            ->leftJoin($db->quoteName('#__emundus_eb_rules_repeat_facultes','erf').' ON '.$db->quoteName('erf.parent_id').' = '.$db->quoteName('er.id'))
            ->where($db->quoteName('er.id') . ' = ' . $rule);
        $db->setQuery($query);
        $complete_rule = $db->loadObject();

        // Check if applicant has files on event of same category
        $query->clear()
            ->select('distinct cc.id, sc.event, ee.custom_fields')
            ->from($db->quoteName('#__emundus_campaign_candidature','cc'))
            ->leftJoin($db->quoteName('#__emundus_setup_campaigns','sc').' ON '.$db->quoteName('sc.id').' = '.$db->quoteName('cc.campaign_id'))
            ->leftJoin($db->quoteName('#__eb_events','ee').' ON '.$db->quoteName('ee.id').' = '.$db->quoteName('sc.event'))
            ->leftJoin($db->quoteName('#__eb_event_categories','ec').' ON '.$db->quoteName('ec.event_id').' = '.$db->quoteName('ee.id'))
            ->where($db->quoteName('ec.category_id') . ' IN (' . $event->categories . ')')
            ->andWhere($db->quoteName('cc.applicant_id') . ' = ' . $db->quote($user->id))
            ->andWhere($db->quoteName('sc.end_date') . ' > ' . $db->quote(date('y-m-d h:i:s')))
            ->andWhere($db->quoteName('cc.status') . ' = 4');
        $db->setQuery($query);
        $file_by_event_categories = $db->loadObjectList();

        foreach ($file_by_event_categories as $key => $file){
            $file_faculte = json_decode($file->custom_fields)->field_faculte;
            if(empty(array_intersect($file_faculte,explode(',',$complete_rule->facultes)))){
                unset($file_by_event_categories[$key]);
            }
        }

        if(!empty($complete_rule->activities)){
            $activities_constraint = explode(',',$complete_rule->activities);
            /*if(!in_array($event->id,$activities_constraint)){
                continue;
            }*/
            foreach ($file_by_event_categories as $key => $file){
                if(!in_array($file->event,$activities_constraint)){
                    unset($file_by_event_categories[$key]);
                }
            }
        }

        if(sizeof($file_by_event_categories) >= $complete_rule->activities_number){
            $message = 'Votre limite d\'inscriptions pour cette catégorie a été atteinte';
            JFactory::getApplication()->redirect('accueil-activites',$message);
        }
        //
    }
}
?>

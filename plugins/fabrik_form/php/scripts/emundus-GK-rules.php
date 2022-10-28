<?php
$db = JFactory::getDBO();
$query = $db->getQuery(true);
$app = JFactory::getApplication();
$student = JFactory::getSession()->get('emundusUser');

include_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');

$m_application  = new EmundusModelApplication;
$m_files        = new EmundusModelFiles;
$m_emails       = new EmundusModelEmails;
$m_campaign     = new EmundusModelCampaign;

$fnumInfos = $m_files->getFnumInfos($student->fnum);
$campaign = $fnumInfos['id'];
$status = 2;

//jos_emundus_1004_00___birth_date --> Date de naissance
//jos_emundus_1004_00___e_406_8262 --> Type de carte identité
//jos_emundus_1004_00___e_406_8263 --> Titre de séjour
//jos_emundus_1004_01___e_408_8264 --> Centre de formation
//jos_emundus_1004_01___diplome --> Niveau de diplôme
//jos_emundus_1004_01___element --> Titre du diplôme

$query->select(
    'birth_date,
        e_406_8262 as identite,
        e_406_8263 as titre_sejour'
)
    ->from($db->quoteName('jos_emundus_1004_00'))
    ->where($db->quoteName('fnum') . ' = ' . $db->quote($student->fnum));
$db->setQuery($query);
$personal_details = $db->loadAssoc();

$query->clear()
    ->select(
        'e_408_8264 as ville,
            diplome as niveau_diplome,
            element as titre_diplome'
    )
    ->from($db->quoteName('jos_emundus_1004_01'))
    ->where($db->quoteName('fnum') . ' = ' . $db->quote($student->fnum));
$db->setQuery($query);
$formations = $db->loadAssoc();

$query->clear()
    ->select('*')
    ->from($db->quoteName('#__emundus_gk_rule'))
    ->where($db->quoteName('campaign') . ' = ' . $db->quote($campaign));
$db->setQuery($query);
$rules = $db->loadObjectList();

$query->clear()
    ->select('parent_id')
    ->from($db->quoteName('data_titresdesjours_00_repeat_eligible'))
    ->where($db->quoteName('eligible') . ' = ' . $db->quote($campaign));
$db->setQuery($query);
$sejours_allowed = $db->loadColumn();

$birthday = new DateTime($personal_details['birth_date']);
$interval = $birthday->diff(new DateTime);
$age = $interval->y;

if($personal_details['identite'] == 2){
    if(!in_array($personal_details['titre_sejour'],$sejours_allowed)){
        return 3;
    }
}

foreach ($rules as $rule){
    if((!empty($rule->age_requis) && $age < (int)$rule->age_requis) || empty($rule->age_requis)){
        $query->clear()
            ->select('v.villes')
            ->from($db->quoteName('#__emundus_gk_rule_877_repeat_repeat_villes','v'))
            ->leftJoin($db->quoteName('#__emundus_gk_rule_877_repeat','r').' ON '.$db->quoteName('r.id').' = '.$db->quoteName('v.parent_id'))
            ->where($db->quoteName('r.parent_id') . ' = ' . $db->quote($rule->id));
        $db->setQuery($query);
        $ville_rules = $db->loadColumn();

        if(in_array($formations['ville'],$ville_rules)){
            $query->clear()
                ->select('d.diplomes_requis')
                ->from($db->quoteName('#__emundus_gk_rule_877_repeat_repeat_diplomes_requis','d'))
                ->leftJoin($db->quoteName('#__emundus_gk_rule_877_repeat','r').' ON '.$db->quoteName('r.id').' = '.$db->quoteName('d.parent_id'))
                ->leftJoin($db->quoteName('#__emundus_gk_rule_877_repeat_repeat_villes','v').' ON '.$db->quoteName('v.parent_id').' = '.$db->quoteName('r.id'))
                ->where($db->quoteName('r.parent_id') . ' = ' . $db->quote($rule->id))
                ->where($db->quoteName('v.villes') . ' = ' . $db->quote($formations['ville']));
            $db->setQuery($query);
            $niveaux_rules = $db->loadColumn();

            if(in_array($formations['niveau_diplome'],$niveaux_rules)){
                $query->clear()
                    ->select('t.titre_requis')
                    ->from($db->quoteName('#__emundus_gk_rule_877_repeat_repeat_titre_requis','t'))
                    ->leftJoin($db->quoteName('#__emundus_gk_rule_877_repeat','r').' ON '.$db->quoteName('r.id').' = '.$db->quoteName('t.parent_id'))
                    ->leftJoin($db->quoteName('#__emundus_gk_rule_877_repeat_repeat_villes','v').' ON '.$db->quoteName('v.parent_id').' = '.$db->quoteName('r.id'))
                    ->where($db->quoteName('r.parent_id') . ' = ' . $db->quote($rule->id))
                    ->where($db->quoteName('v.villes') . ' = ' . $db->quote($formations['ville']));
                $db->setQuery($query);
                $titres_rules = $db->loadColumn();
                if(in_array($formations['titre_diplome'],$titres_rules)){
                    return $rule->result;
                }
            }
        }
    } else {
        return 3;
    }
}

<?php

$db = JFactory::getDBO();
$user = JFactory::getSession()->get('emundusUser');

$campaign_id = $data['jos_emundus_campaign_candidature___campaign_id_raw'][0];

$eMConfig = JComponentHelper::getParams('com_emundus');
$applicant_can_renew = $eMConfig->get('applicant_can_renew', '0');
$id_profiles = $eMConfig->get('id_profiles', '0');
$id_profiles = explode(',', $id_profiles);

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
$m_campaign = new EmundusModelCampaign;
$isLimitObtained = $m_campaign->isLimitObtained($campaign_id);

if ($isLimitObtained === true) {
    JLog::add('User: '.$user->id.' Campaign limit is obtained', JLog::ERROR, 'com_emundus');

    $formModel->setFormErrorMsg("Campaign limit is obtained.");
    $formModel->getForm()->error = JText::_('ERROR');
    return false;
}

if (EmundusHelperAccess::asAccessAction(1, 'c')) {
	$applicant_can_renew = 1;
} else {
    foreach ($user->emProfiles as $profile) {
        if (in_array($profile->id, $id_profiles)) {
            $applicant_can_renew = 1;
            break;
        }
    }
}

$config = JFactory::getConfig();

$timezone = new DateTimeZone( $config->get('offset') );
$now = JFactory::getDate()->setTimezone($timezone);

switch ($applicant_can_renew) {

    // Cannot create new campaigns at all.
    case 0:
        JLog::add('User: '.$user->id.' already has a file.', JLog::ERROR, 'com_emundus');
        $formModel->setFormErrorMsg('User already has a file open and cannot have multiple.');
        $formModel->getForm()->error = JText::_('ERROR');
        return false;

    // If the applicant can only have one file per campaign.
    case 2:
        $query = 'SELECT id
					FROM #__emundus_setup_campaigns
					WHERE published = 1
					AND end_date >= ' . $db->quote($now) .
					'AND start_date <= ' . $db->quote($now) .
					'AND id NOT IN (
						SELECT campaign_id
						FROM #__emundus_campaign_candidature
						WHERE applicant_id=' . $user->id .
                        'AND published <> -1
					)';

        try {

            $db->setQuery($query);
            if (!in_array($campaign_id, $db->loadColumn())) {
                JLog::add('User: '.$user->id.' already has a file for campaign id: '.$campaign_id, JLog::ERROR, 'com_emundus');
                $formModel->setFormErrorMsg('User already has a file for this campaign.');
                $formModel->getForm()->error = JText::_('ERROR');
                return false;
            }

        } catch (Exception $e) {
            JLog::add('plugin/emundus_campaign SQL error at query :'.$query, JLog::ERROR, 'com_emundus');
        }

        break;

    // If the applicant can only have one file per school year.
    case 3:
        $query = 'SELECT id
					FROM #__emundus_setup_campaigns
					WHERE published = 1
					AND end_date >= ' . $db->quote($now) .
					'AND start_date <= ' . $db->quote($now) .
					'AND year NOT IN (
						select sc.year
						from #__emundus_campaign_candidature as cc
						LEFT JOIN #__emundus_setup_campaigns as sc ON sc.id = cc.campaign_id
						where applicant_id='. $user->id.'
					)';

        try {

            $db->setQuery($query);
            if (!in_array($campaign_id, $db->loadColumn())) {
                JLog::add('User: '.$user->id.' already has a file for year belong to campaign: '.$campaign_id, JLog::ERROR, 'com_emundus');
                $formModel->setFormErrorMsg('User already has a file for this year.');
                $formModel->getForm()->error = JText::_('ERROR');
                return false;
            }

        } catch (Exception $e) {
            JLog::add('plugin/emundus_campaign SQL error at query :'.$query, JLog::ERROR, 'com_emundus');
        }

        break;

}

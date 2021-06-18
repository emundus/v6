<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

$db = JFactory::getDbo();
$query = $db->getQuery(true);

require_once (JPATH_SITE.DS.'components/com_emundus/helpers'.DS.'files.php');
require_once (JPATH_SITE.DS.'components/com_emundus/models'.DS.'application.php');
require_once (JPATH_SITE.DS.'components/com_emundus/models'.DS.'profile.php');
require_once (JPATH_SITE.DS.'components/com_emundus/models'.DS.'files.php');
$h_files = new EmundusHelperFiles;
$m_application = new EmundusModelApplication;
$m_profile = new EmundusModelProfile;
$m_files = new EmundusModelFiles;

$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$redirect = null;

// Get inputs
$fnum = $jinput->get('___fnum');
$session = $jinput->get('___session')[0];
$user = JFactory::getSession()->get('emundusUser');
$formid = $jinput->get('formid');
//

try {
    $fnumsDetails = $m_profile->getFnumDetails($fnum);

    $query->select('cc.candidat_type,cc.profile_id,sc.dynamic_profile')
        ->from($db->quoteName('#__emundus_campaign_candidature','cc'))
        ->leftJoin($db->quoteName('data_situation_candidat','sc').' ON '.$db->quoteName('sc.id').' = '.$db->quoteName('cc.profile_id'))
        ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));
    $db->setQuery($query);
    $situation = $db->loadObject();

    if($session == $fnumsDetails['campaign_id']){
        $mainframe->enqueueMessage(JText::_('SESSION_NOT_UPDATING'));
        $mainframe->redirect('index.php');
    } else {
        // Create our new application
        $new_fnum = $h_files->createFnum($session, $user->id);

        $query->clear()
            ->insert($db->quoteName('#__emundus_campaign_candidature'));
        $query->set($db->quoteName('campaign_id') . ' = ' . $db->quote($session))
            ->set($db->quoteName('applicant_id') . ' = ' . $db->quote($user->id))
            ->set($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
            ->set($db->quoteName('candidat_type') . ' = ' . $db->quote($situation->candidat_type))
            ->set($db->quoteName('profile_id') . ' = ' . $db->quote($situation->profile_id))
            ->set($db->quoteName('fnum') . ' = ' . $db->quote($new_fnum));
        $db->setQuery($query);
        $db->execute();
        $new_file = $db->insertid();
        //

        // Copy current application to new application
        $result = $m_application->copyApplication($fnum, $new_fnum, $situation->dynamic_profile, 1);
        //

        // Delete courses choices and update referees
        if(in_array($session,[5,6,7])){
            $query->clear()
                ->delete($db->quoteName('#__emundus_1001_04'))
                ->where($db->quoteName('fnum') . ' = ' . $db->quote($new_fnum));
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->update($db->quoteName('#__emundus_files_request'))
                ->set($db->quoteName('fnum') . ' = ' . $db->quote($new_fnum))
                ->set($db->quoteName('campaign_id') . ' = ' . $db->quote($session))
                ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));
            $db->setQuery($query);
            $db->execute();
        } elseif (in_array($session,[8,9,10])){
            $query->clear()
                ->delete($db->quoteName('#__emundus_1002_00'))
                ->where($db->quoteName('fnum') . ' = ' . $db->quote($new_fnum));
            $db->setQuery($query);
            $db->execute();
        }

        $query->clear()
            ->delete($db->quoteName('#__emundus_1001_03'))
            ->where($db->quoteName('fnum') . ' = ' . $db->quote($new_fnum));
        $db->setQuery($query);
        $db->execute();

        //

        // Delete file not present in wishes
        $query->clear()
            ->update('#__emundus_campaign_candidature')
            ->set($db->quoteName('published') . ' = -1')
            ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));
        $db->setQuery($query);
        $db->execute();
        //

        // Redirect to index.php if the current fnum has been deleted
        $mainframe->enqueueMessage(JText::_('APPLICATION_COPIED'));
        $mainframe->redirect('index.php');
        //

    }
} catch(Exception $e){
    JLog::add($e->getMessage() . 'with query : ' . $query->__toString(), JLog::ERROR, 'com_emundus');
}


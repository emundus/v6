<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: emundus_copy_file.php 89 2018-03-15 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2018 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Copy file from fnum to an other
 */

jimport('joomla.log.log');
JLog::addLogger([
    // Sets file name
    'text_file' => 'com_emundus.copy.php'
],
    JLog::ALL,
    ['com_emundus']
);

$user 	= JFactory::getUser();
$app 	= JFactory::getApplication();
$db   	= JFactory::getDBO();

$jinput = $app->input;
$itemid = $jinput->get('Itemid');

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
$m_files = new EmundusModelFiles;

$fnums_from 		        = explode(',',$formModel->getElementData('jos_emundus_campaign_candidature___fnum', true));
$campaign_id 	        = $formModel->getElementData('jos_emundus_campaign_candidature___campaign_id', true);
$campaign_id 	        = is_array($campaign_id) ? $campaign_id[0] : $campaign_id;
$copied 		        = $formModel->getElementData('jos_emundus_campaign_candidature___copied', true);
$copied 		        = is_array($copied) ? $copied[0] : $copied;
$applicant_id 	        = $formModel->getElementData('jos_emundus_campaign_candidature___applicant_id', true);
$status 		        = $formModel->getElementData('jos_emundus_campaign_candidature___status', true);
$status 		        = is_array($status) ? $status[0] : $status;
$can_delete 	        = $formModel->getElementData('jos_emundus_campaign_candidature___can_be_deleted', null);
$copy_attachment 	    = $formModel->getElementData('jos_emundus_campaign_candidature___copy_attachment', 0);
$copy_tag 	            = $formModel->getElementData('jos_emundus_campaign_candidature___copy_tag', 0);
$move_hikashop_command 	= $formModel->getElementData('jos_emundus_campaign_candidature___move_hikashop_command', 0);
$delete_from_file 	    = $formModel->getElementData('jos_emundus_campaign_candidature___delete_from_file', 0);
$copyUsersAssoc 	    = $formModel->getElementData('jos_emundus_campaign_candidature___copy_users_assoc', 0);
$copyGroupsAssoc  	    = $formModel->getElementData('jos_emundus_campaign_candidature___copy_groups_assoc', 0);

foreach ($fnums_from as $fnum_from) {
    $fnum_infos = $m_files->getFnumInfos($fnum_from);
    $applicant_id = $fnum_infos['applicant_id'];

    // create new fnum
    $fnum_to = date('YmdHis') . str_pad($campaign_id, 7, '0', STR_PAD_LEFT) . str_pad($applicant_id, 7, '0', STR_PAD_LEFT);

    // 1. Get definition of fnum_from
    if ($copied == 1) {

        try {
            $query = 'SELECT * FROM #__emundus_campaign_candidature WHERE fnum like ' . $db->Quote($fnum_from);
            $db->setQuery($query);
            $application_file = $db->loadAssoc();
		    
            if (!empty($application_file)) {
		    	$application_file['fnum'] = $fnum_to;
		    	$application_file['copied'] = $copied;
                $application_file['user_id'] = $user->id;
                $application_file['campaign_id'] = $campaign_id;
                $application_file['status'] = $status;
		    	unset($application_file['id']);

                // 2. Copie definition of fnum for new file
                $query = 'INSERT INTO #__emundus_campaign_candidature (`'.implode('`,`', array_keys($application_file)).'`)
		    			VALUES ('.implode(',', $db->Quote($application_file)).')';
                $db->setQuery($query);
                $db->execute();
		    }

            // 3. Duplicate file from new fnum
            include_once(JPATH_SITE . '/components/com_emundus/models/application.php');
            require_once(JPATH_SITE . '/components/com_emundus/models/profile.php');
            require_once(JPATH_SITE . '/components/com_emundus/helpers/menu.php');

            $m_application = new EmundusModelApplication;
            $profiles = new EmundusModelProfile();

            $fnumInfos = $profiles->getFnumDetails($fnum_from);

            //$pid = (isset($fnumInfos['profile_id_form']) && !empty($fnumInfos['profile_id_form']))?$fnumInfos['profile_id_form']:$fnumInfos['profile_id'];

            $result = $m_application->copyApplication($fnum_from, $fnum_to, null, $copy_attachment, $fnumInfos['campaign_id'], $copy_tag, $move_hikashop_command, $delete_from_file,array(),$copyUsersAssoc,$copyGroupsAssoc);

            // 4. Duplicate attachments for new fnum
            /*if ($result) {
                $result = $m_application->copyDocuments($fnum_from, $fnum_to, $pid, $can_delete);
            }*/

            // 5. Duplicate evaluation for new fnum
            // TODO
        } catch (Exception $e) {
            $error = JUri::getInstance() . ' :: USER ID : ' . $user->id . ' -> ' . $query;
            JLog::add($error, JLog::ERROR, 'com_emundus');
        }

    } elseif ($copied == 2) {

        // Move the file to another campaign
        include_once(JPATH_SITE . '/components/com_emundus/models/application.php');
        $m_application = new EmundusModelApplication;

        $m_application->moveApplication($fnum_from, $fnum_to, $campaign_id, $status);

    } else {
        // new empty file
        try {

            $query = 'INSERT INTO #__emundus_campaign_candidature (`applicant_id`, `user_id`, `campaign_id`, `submitted`, `date_submitted`, `cancelled`, `fnum`, `status`, `published`, `copied`) 
					VALUES (' . $applicant_id . ', ' . $user->id . ', ' . $campaign_id . ', 0, NULL, 0, ' . $db->Quote($fnum_to) . ', ' . $status . ', 1, 0)';
            $db->setQuery($query);
            $db->execute();

        } catch (Exception $e) {
            $error = JUri::getInstance() . ' :: USER ID : ' . $user->id . ' -> ' . $query;
            JLog::add($error, JLog::ERROR, 'com_emundus');
        }
    }
}


// 5. Exit plugin before store
echo "<script>
      window.parent.$('html, body').animate({scrollTop : 0}, 300);  
      window.setTimeout(function() {
		parent.$('#em-modal-actions').modal('hide');
	}, 1800);</script>";
die('<div style="text-align: center"><img src="'.JURI::base().'images/emundus/animations/checked.gif" width="200" height="200" align="middle" /></div>');

<?php
defined( '_JEXEC' ) or die();
/**
 this mini-plugin aims to register new email trigger
    * jos_emundus_setup_emails_trigger
    * jos_emundus_setup_emails_trigger_repeat_campaign_id

    via Fabrik form (EMUNDUS_WORKFLOW_STEP)
 */

$db = JFactory::getDbo();
$query = $db->getQuery(true);

/* step 1 : clean table jos_emundus_setup_workflow_step_emails_repeat */

/* get destinations from POST */
$dests  = $_POST['jos_emundus_setup_workflow_step_emails_repeat___destination'];

/* get status from POST */
$stats  =  $_POST['jos_emundus_setup_workflow_step_emails_repeat___status'];

/* get trigger type from POST (0 => to_current_user / 1 => to_applicant) */
$trigger = $_POST['jos_emundus_setup_workflow_step_emails_repeat___trigger'];

/* get parent id (step) from POST */
$step = $_POST['jos_emundus_setup_workflow_step___id'];

/* get email template from POST */
$email = $_POST['jos_emundus_setup_workflow_step_emails_repeat___email_tmpl'];

/* get workflow id (workflow) from POST */
$workflow = $_POST['jos_emundus_setup_workflow_step___workflow'][0];

/* each time onAfterProcess is called, we need to remove all records which parent_id = $step in table "jos_emundus_setup_workflow_step_emails_repeat" */
$clean_sql_email_step = 'DELETE FROM #__emundus_setup_workflow_step_emails_repeat WHERE #__emundus_setup_workflow_step_emails_repeat.parent_id = ' . $step;
$db->setQuery($clean_sql_email_step);
$db->execute();

$clean_sql_email_trigger = 'DELETE FROM #__emundus_setup_emails_trigger WHERE #__emundus_setup_emails_trigger.phase = ' . $step;
$db->setQuery($clean_sql_email_trigger);
$db->execute();

/* and then, re-insert new records */
foreach($stats as $key => $value) {
    $raw = array('parent_id' => $step, 'destination' => current($dests[$key]), 'trigger' => current($trigger[$key]), 'email_tmpl' => current($email[$key]), 'status' => current($stats[$key]));
    $query->clear()
        ->insert($db->quoteName('#__emundus_setup_workflow_step_emails_repeat'))
        ->columns($db->quoteName(array_keys($raw)))
        ->values(implode(',', $db->quote(array_values($raw))));

    $db->setQuery($query);
    $db->execute();

    /* after that, register new email trigger (jos_emundus_setup_emails_trigger) */
    if(current($trigger[$key]) == 0) { $eTrigger = array('user' => JFactory::getUser()->id, 'date_time' => date('Y-m-d H:i:s'), 'step' => current($stats[$key]), 'email_id' => current($email[$key]), 'to_current_user' => 1, 'to_applicant' => 0, 'phase' => $step); }

    if(current($trigger[$key]) == 1) { $eTrigger = array('user' => JFactory::getUser()->id, 'date_time' => date('Y-m-d H:i:s'), 'step' => current($stats[$key]), 'email_id' => current($email[$key]), 'to_current_user' => 0, 'to_applicant' => 1, 'phase' => $step); }

    $query->clear()
        ->insert($db->quoteName('#__emundus_setup_emails_trigger'))
        ->columns($db->quoteName(array_keys($eTrigger)))
        ->values(implode(',', $db->quote(array_values($eTrigger))));

    $db->setQuery($query);
    $db->execute();

    $trigger_id = $db->insertid();
    
    /* we'll use this id to make parent_id of table jos_emundus_setup_emails_trigger_repeat_user_id */
    if(!empty(current($dests[$key]))) {
        $insert_user_trigger = 'INSERT INTO #__emundus_setup_emails_trigger_repeat_user_id (parent_id, user_id) VALUES (' . $trigger_id . ',' .  $db->quote(current($dests[$key])) . ')';
        $db->setQuery($insert_user_trigger);
        $db->execute();
    }
}

?>
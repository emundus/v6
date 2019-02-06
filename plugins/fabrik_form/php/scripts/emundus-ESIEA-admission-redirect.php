<?php
/**
 * Created by PhpStorm.
 * User: imacemundus
 * Date: 2019-01-23
 * Time: 09:43
 */

jimport( 'joomla.utilities.utility' );
jimport('joomla.log.log');
JLog::addLogger(
    array(
        // Sets file name
        'text_file' => 'com_emundus.admissionredirect.php'
    ),
    // Sets messages of all log levels to be sent to the file
    JLog::ALL,
    // The log category/categories which should be recorded in this file
    // In this case, it's just the one category from our extension, still
    // we need to put it inside an array
    array('com_emundus')
);

$app = JFactory::getApplication();

$status = [11, 12, 13, 15];

$session = JFactory::getSession();
$user = $session->get('emundusUser');

$jinput = $app->input;
$fnum = $jinput->get->get('rowid', null);

$formId = 316;
$itemId = 2841;

$eMConfig = JComponentHelper::getParams('com_emundus');
$id_applicants 			 = $eMConfig->get('id_applicants', '0');
$applicants 			 = explode(',',$id_applicants);


if (!empty($fnum) && $fnum == @$user->fnum) {
    $admissionDate = date('Y-m-d');
    $admissionDateBegin = date('Y-m-d', strtotime(@$user->fnums[$fnum]->admission_start_date));
    $admissionDateEnd = date('Y-m-d', strtotime(@$user->fnums[$fnum]->admission_end_date));

    if (@$user->fnums[$fnum]->published =="1" && @$user->fnums[$fnum]->cancelled =="0" && in_array($user->status, $status)  && $admissionDate >= $admissionDateBegin && $admissionDate <= $admissionDateEnd) {

        $user->profile = "1012";
        $user->profile_label = "Inscription à l'ESIEA";
        $user->menutype = "menu-profile1012";

        $insert = true;
        foreach ($user->emProfiles as $p) {
            if ($p->id == $user->profile) {
                $insert = false;
            }
        }

        if ($insert) {
            $user->emProfiles[] = (object)['id' => $user->profile, 'label' => $user->profile_label];
        }

        $session->set('emundusUser', $user);
        $app->redirect("index.php?option=com_fabrik&view=form&formid=".$formId."&Itemid=".$itemId."&usekey=fnum&rowid=".$fnum."&r=0");
    }
}
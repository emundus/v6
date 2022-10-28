<?php
defined( '_JEXEC' ) or die();
/**
 * @version 3: qcm.php 89 2016-12-07 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2016 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Calcul du resultat du QCM
 */
$user = JFactory::getSession()->get('emundusUser');
$db   	= JFactory::getDBO();
$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;

$query = "SELECT answer_code FROM `data_qcm` WHERE answer_result=1";
$db->setQuery( $query );
$goods = $db->loadColumn();

$query = "SELECT * FROM `jos_emundus_qcm` WHERE fnum LIKE ".$db->Quote($user->fnum);
$db->setQuery( $query );
$answers = $db->loadRow();

$result = 0;
foreach($answers as $answer) {
    foreach($goods as $good) {
        if($answer == $good) $result++;
    }
}

$offset = $mainframe->get('offset', 'UTC');
try {
    $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
    $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
    $now = $dateTime->format('Y-m-d H:i:s');
} catch(Exception $e) {
    echo $e->getMessage() . '<br />';
}

$query = "UPDATE `jos_emundus_qcm` set qcm_date_submitted=".$db->quote($now).", result=".$result.", qcm_time_elapsed=ROUND(time_to_sec((TIMEDIFF('".$now."', time_date))) / 60) WHERE fnum like ".$db->Quote($user->fnum);
$db->setQuery( $query );
$db->execute();

JFactory::getApplication()->enqueueMessage('<h1>'.JText::_('SENT').'</h1>', 'message');
$mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$jinput->get('Itemid')."&usekey=fnum&rowid=".$user->fnum."&rq=3&result=".$result);


?>

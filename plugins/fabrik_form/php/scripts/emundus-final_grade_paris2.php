<?php
defined('_JEXEC') or die();

jimport('joomla.log.log');
JLog::addLogger(array('text_file' => 'com_emundus.emundus-final-grade.php'), JLog::ALL, array('com_emundus.emundus-final-grade'));

$db = JFactory::getDBO();
$query = $db->getQuery(true);
$jinput	= JFactory::getApplication()->input->post;

$fnum = $jinput->get('jos_emundus_final_grade___fnum');
$status = $jinput->get('jos_emundus_final_grade___final_grade')[0];
$motif = $jinput->get('jos_emundus_final_grade___motif_refus')[0];

include_once (JPATH_BASE.'/components/com_emundus/models/files.php');

if (!empty($status)) {

    if($status == 7){
        $query->select($db->quoteName('status'))
            ->from($db->quoteName('data_motifs_refus'))
            ->where($db->quoteName('id').' = '.$db->quote($motif));
        $db->setQuery($query);
        $status = $db->loadResult();

        if (empty($status)) {
            $status = 7;
        }
    }

    $m_files = new EmundusModelFiles();

    try {
        $m_files->updateState($fnum, $status);
    } catch(Exception $e) {
        JLog::add('Unable to set status in plugin/emundusFinalGrade at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus.emundus-final-grade');
    }
}
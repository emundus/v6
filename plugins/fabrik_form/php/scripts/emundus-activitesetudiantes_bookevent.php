<?php
if(!empty($_SERVER['HTTP_CLIENT_IP'])){
    //ip from share internet
    $ip = $_SERVER['HTTP_CLIENT_IP'];
}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
    //ip pass from proxy
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}else{
    $ip = $_SERVER['REMOTE_ADDR'];
}

$mainframe = JFactory::getApplication();
$jinput = JFactory::getApplication()->input;

$current_user  = JFactory::getSession()->get('emundusUser');
$fnum = $current_user->fnum;

$db = JFactory::getDbo();
$query = $db->getQuery(true);

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
$m_files = new EmundusModelFiles();

jimport('joomla.log.log');
JLog::addLogger(['text_file' => 'com_emundus.hikashopAddToCart.php'], JLog::ALL, ['com_emundus']);

try {
    $fnumInfos = $m_files->getFnumInfos($fnum);

    $query->select('event')
        ->from($db->quoteName('#__emundus_setup_campaigns'))
        ->where($db->quoteName('id') . ' = ' . $fnumInfos['id']);
    $db->setQuery($query);
    $event_id = $db->loadResult();

    $columns = ['event_id', 'user_id', 'first_name', 'last_name', 'email', 'number_registrants', 'register_date', 'payment_date', 'published', 'language', 'user_ip'];
    $values = [$event_id, $fnumInfos['applicant_id'], $db->quote($fnumInfos['name']), $db->quote($fnumInfos['name']), $db->quote($fnumInfos['email']), 1, $db->quote(date('Y-m-d h:i:s')), $db->quote(date('Y-m-d h:i:s')), 1, $db->quote('fr-FR'), $db->quote($ip)];

    $query->clear()
        ->insert($db->quoteName('#__eb_registrants'))
        ->columns($columns)
        ->values(implode(',', $values));
    $db->setQuery($query);
    $db->execute();
    $registration = $db->insertid();

    $query->clear()
        ->update('#__emundus_campaign_candidature')
        ->set($db->quoteName('eb_registration') . ' = ' . $db->quote($registration))
        ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));
    $db->setQuery($query);
    $db->execute();
} catch (Exception $e){
    JLog::add('plugin/fabrik_form/php/scripts/activitesetudiantes_bbokevent error :'.$query->__toString().' : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
}
?>

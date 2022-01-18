<?php
require_once JPATH_BASE.'/plugins/fabrik_form/emunduszoommeeting/ZoomAPIWrapper.php';

$db = JFactory::getDbo();
$query = $db->getQuery(true);

$eMConfig = JComponentHelper::getParams('com_emundus');
$apiSecret = $eMConfig->get('zoom_jwt', '');

/* get user info */
$query->clear()
    ->select('jeu.firstname, jeu.lastname, ju.email')
    ->from($db->quoteName('#__users', 'ju'))
    ->leftJoin($db->quoteName('#__emundus_users', 'jeu') . ' ON ' . $db->quoteName('ju.id') . ' = ' . $db->quoteName('jeu.user_id'))
    ->where($db->quoteName('ju.id') . ' = ' . $db->quote(current($_POST['data_referentiel_zoom_token___user'])));

$db->setQuery($query);
$raw = $db->loadObject();

/* call to zoom api */
if(empty($apiSecret)) {
    return "Missing api key";
} else {
    $zoom = new ZoomAPIWrapper($apiSecret);
    
    /* create new zoom user */

    /* data prepare */
    $user = json_encode(array(
        "action" => "custCreate",
        "user_info" => ["email" =>  $raw->email, 'type' => 1, "first_name" => $raw->firstname, "last_name" => $raw->lastname],
    ));


    $response = $zoom->doRequest('POST','/users',array(),array(),$user);        /* array */

    /* update "data_referentiel_zoom_token" by adding zoom_id */
    $updateSql = "update data_referentiel_zoom_token set zoom_id = " . $db->quote($response['id']) . ' where data_referentiel_zoom_token.user = ' . current($_POST['data_referentiel_zoom_token___user']);
    $db->setQuery($updateSql);
    $db->execute();
}
    
?>
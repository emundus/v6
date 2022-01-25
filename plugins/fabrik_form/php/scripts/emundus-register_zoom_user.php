<?php
require_once JPATH_BASE.'/plugins/fabrik_form/emunduszoommeeting/ZoomAPIWrapper.php';
$db = JFactory::getDbo();
$query = $db->getQuery(true);

$eMConfig = JComponentHelper::getParams('com_emundus');
$apiSecret = $eMConfig->get('zoom_jwt', '');

# get host
$query->clear()
    ->select('jeu.firstname, jeu.lastname, ju.email')
    ->from($db->quoteName('#__users', 'ju'))
    ->leftJoin($db->quoteName('#__emundus_users', 'jeu') . ' ON ' . $db->quoteName('ju.id') . ' = ' . $db->quoteName('jeu.user_id'))
    ->where($db->quoteName('ju.id') . ' = ' . $db->quote(current($_POST['data_referentiel_zoom_token___user'])));

$db->setQuery($query);
$raw = $db->loadObject();

# call endpoint
if(empty($apiSecret)) {
    return false;
} else {
    # if host id does not exist before, generate the new one
    if(empty($_POST['data_referentiel_zoom_token___zoom_id'])) {
        $zoom = new ZoomAPIWrapper($apiSecret);

        # data prepare, using "action = custCreate" to bypass the email invitation, "create" to send invitation email, "autoCreate" is reserved to Enterprise customer with a managed domain, "ssoCreate" if you want to enable "Pre-provisioning SSO User"
        # data prepare, using "type = 1" to add Basic user, "2" to add Licensed user, "3" to add On-prem user, "99" to add None (only available with ssoCreate)
        $user = json_encode(array(
            "action" => current($_POST['data_referentiel_zoom_token___send_invitation']),
            "user_info" => ["email" => $raw->email, 'type' => current($_POST['data_referentiel_zoom_token___user_type']), "first_name" => $raw->firstname, "last_name" => $raw->lastname],
        ));

        # send request to endpoint
        $response = $zoom->doRequest('POST', '/users', array(), array(), $user);        /* array */

        # if reponseCode is 201, update the table "data_referentiel_zoom_token"
        if($zoom->responseCode() == 201) {
            $updateSql = "update data_referentiel_zoom_token set zoom_id = " . $db->quote($response['id']) . ' where data_referentiel_zoom_token.user = ' . current($_POST['data_referentiel_zoom_token___user']);
            $db->setQuery($updateSql);
            $db->execute();
        } else {
            $zoom->requestErrors();
        }
    }
}
    
?>
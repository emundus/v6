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

$zoom = new ZoomAPIWrapper($apiSecret);

# call endpoint
if(empty($apiSecret)) {
    return false;
} else {
    # if host id does not exist before, generate the new one
    if(empty($_POST['data_referentiel_zoom_token___zoom_id']) or empty($_POST['data_referentiel_zoom_token___id'])) {
        $user = json_encode(array(
            "action" => current($_POST['data_referentiel_zoom_token___send_invitation']),
                "user_info" => [
                    "email" => $raw->email, 
                        'type' => current($_POST['data_referentiel_zoom_token___user_type']), 
                            "first_name" => $raw->firstname, 
                                "last_name" => $raw->lastname],
        ));

        # send request to create endpoint
        $response = $zoom->doRequest('POST', '/users', array(), array(), $user);        /* array */

        # if reponseCode is 201, update the table "data_referentiel_zoom_token"
        if($zoom->responseCode() == 201) {
            $updateSql = "update data_referentiel_zoom_token set zoom_id = " . $db->quote($response['id']) . ' where data_referentiel_zoom_token.user = ' . current($_POST['data_referentiel_zoom_token___user']);
            $db->setQuery($updateSql);
            $db->execute();
        } else {
            $zoom->requestErrors();
        }
    } else {
        # we just allow to update the user type (1,2,3,99). If the coordinator want to upgrade the user type, use this case
        $uType = $_POST['data_referentiel_zoom_token___user_type'];

        # prepare data to send
        $updateJson = json_encode(array("type" => $uType));

        # send request to update endpoint
        $response = $zoom->doRequest('PATCH', '/users/' . $_POST['data_referentiel_zoom_token___zoom_id'], array(), array(), $updateJson);

        # get the HTTP status code
        $code = $zoom->responseCode();

        # if http status is 400 (bad request error), rollback the last user type
        if($code !== 204) {
            # again, get the last user type
            $request = $zoom->doRequest('GET', '/users/' . $_POST['data_referentiel_zoom_token___zoom_id'], array(), array(), '');

            # rollback to the database
            $rollbackSql = "update data_referentiel_zoom_token set user_type = " . $db->quote($request['type']) . ' where data_referentiel_zoom_token.user = ' . current($_POST['data_referentiel_zoom_token___user']);
            $db->setQuery($rollbackSql);
            $db->execute();
        }
    }
}
    
?>
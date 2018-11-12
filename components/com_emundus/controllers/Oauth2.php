<?php
/**
 * Created by PhpStorm.
 * User: imacemundus
 * Date: 12/11/2018
 * Time: 10:32
 */


require Joomla\Oauth2\Client;


class EmundusControllerOauth2 {
    // set Oauth2 parameters
    protected $data = Registry;

    public function __construct()
    {
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $data['redirect_uri']  = $eMConfig->get('redirecturl');
        $data['clientid']      = $eMConfig->get('clientid');
        $data['clientsecret']  = $eMConfig->get('clientsecret');
        $data['authurl']       = $eMConfig->get('authurl');
        $data['tokenurl']      = $eMConfig->get('tokenurl');
    }


    public function authenticate()
    {
        $oauth = new JOAuth2Client($this->data);
        $oauth->createUrl();
        $oauth->authenticate();
    }
}
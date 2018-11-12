<?php
/**
 * Created by PhpStorm.
 * User: imacemundus
 * Date: 12/11/2018
 * Time: 10:32
 */


use Joomla\Oauth2\Client;


class EmundusControllerOauth2 {
    // set Oauth2 parameters
    protected $data = null;

    public function __construct()
    {
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $this->data['redirect_uri']  = $eMConfig->get('redirecturl');
        $this->data['clientid']      = $eMConfig->get('clientid');
        $this->data['clientsecret']  = $eMConfig->get('clientsecret');
        $this->data['authurl']       = $eMConfig->get('authurl');
        $this->data['tokenurl']      = $eMConfig->get('tokenurl');
    }


    public function authenticate()
    {

    }
}
<?php
/**
 * Created by PhpStorm.
 * User: imacemundus
 * Date: 12/11/2018
 * Time: 10:32
 */


use \Joomla\Registry\Registry;
use Joomla\OAuth2\Client;



class EmundusControllerOauth2 {
    // set Oauth2 parameters
    protected $data = null;

    /**
     * @var    Registry  Options for the JOAuth2Client object.
     * @since  12.3
     */
    protected $options;

    /**
     * @var    JHttp  The HTTP client object to use in sending HTTP requests.
     * @since  12.3
     */
    protected $http;

    /**
     * @var    JInput  The input object to use in retrieving GET/POST data.
     * @since  12.3
     */
    protected $input;

    /**
     * @var    JApplicationWeb  The application object to send HTTP headers for redirects.
     * @since  12.3
     */
    protected $application;

    /**
     * Constructor.
     *
     * @param   Registry         $options      JOAuth2Client options object
     * @param   JHttp            $http         The HTTP client object
     * @param   JInput           $input        The input object
     * @param   JApplicationWeb  $application  The application object
     *
     * @since   12.3
     */

    public function __construct() {
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $this->data['redirect_uri']  = $eMConfig->get('redirecturl');
        $this->data['clientid']      = $eMConfig->get('clientid');
        $this->data['clientsecret']  = $eMConfig->get('clientsecret');
        $this->data['authurl']       = $eMConfig->get('authurl');
        $this->data['tokenurl']      = $eMConfig->get('tokenurl');

        $this->options = new Registry;
        $this->http = new JHttp();
        $array = array();
        $this->input = new JInput($array);
        $this->application = new JApplicationWeb;
        $this->object = new JOAuth2Client();
    }


    /**
     * Tests the auth method
     *
     * @group   JOAuth2
     * @return  void
     */
    public function testAuth()
    {
        $jinput      = JFactory::getApplication()->input;
        $this->object->setOption('authurl', $this->data['authurl'] );
        $this->object->setOption('clientid', $this->data['clientid'] );
        $this->object->setOption('scope', array('openid'));
        $this->object->setOption('redirecturi', $this->data['redirect_uri']);
        $this->object->setOption('requestparams', array('access_type' => 'offline', 'approval_prompt' => 'auto'));
        $this->object->setOption('sendheaders', true);
        $this->application->expects($this->any())
            ->method('redirect')
            ->willReturn(true);
        $this->object->authenticate();
        $this->object->setOption('tokenurl', $this->data['tokenurl']);
        $this->object->setOption('clientsecret', $this->data['clientsecret']);
        $this->input->set('code', $jinput->get->get('code'));
        $this->http->expects($this->once())->method('post')->will($this->returnCallback('encodedGrantOauthCallback'));
        $result = $this->object->authenticate();
        $this->assertEquals('accessvalue', $result['access_token']);
        $this->assertEquals('refreshvalue', $result['refresh_token']);
        $this->assertEquals(3600, $result['expires_in']);
        $this->assertLessThanOrEqual(1, time() - $result['created']);
    }


    public function authenticate()
    {
        $this->object->setOption('authurl', 'https://accounts.google.com/o/oauth2/auth');
        $this->object->setOption('clientid', '01234567891011.apps.googleusercontent.com');
        $this->object->setOption('scope', array('https://www.googleapis.com/auth/adsense', 'https://www.googleapis.com/auth/calendar'));
        $this->object->setOption('redirecturi', 'http://localhost/oauth');
        $this->object->setOption('requestparams', array('access_type' => 'offline', 'approval_prompt' => 'auto'));
        $this->object->setOption('sendheaders', true);

        $oauth = new JOAuth2Client();
        $oauth->createUrl();
        $oauth->authenticate();
    }
}
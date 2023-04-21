<?php
/**
 * @package     External_Login
 * @subpackage  CAS Plugin
 * @author      Christophe Demko <chdemko@gmail.com>
 * @author      Ioannis Barounis <contact@johnbarounis.com>
 * @author      Alexandre Gandois <alexandre.gandois@etudiant.univ-lr.fr>
 * @copyright   Copyright (C) 2008-2018 Christophe Demko, Ioannis Barounis, Alexandre Gandois. All rights reserved.
 * @license     GNU General Public License, version 2. http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.chdemko.com
 */

// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.database.table');
JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_externallogin/tables');

jimport('joomla.application.component.model');
JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_externallogin/models', 'ExternalloginModel');

if (version_compare(JVERSION, '3.8.0', '>='))
{
    JLoader::registerAlias('ExternalloginLogger', '\\Joomla\\CMS\\Log\\Logger\\ExternalloginLogger');
}

JLoader::register('ExternalloginLogger', JPATH_ADMINISTRATOR . '/components/com_externallogin/log/logger.php');
JLoader::register('ExternalloginLogEntry', JPATH_ADMINISTRATOR . '/components/com_externallogin/log/entry.php');
JLoader::register('ExternalloginHelper', JPATH_ADMINISTRATOR . '/components/com_externallogin/helpers/externallogin.php');

class plgSystemEmundus_caslogin extends JPlugin
{
    /**
     * Load the language file on instantiation. Note this is only available in Joomla 3.1 and higher.
     * If you want to support 3.0 series you must override the constructor
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * @var    ExternalloginTableServer
     * @since  2.0.0
     */
    protected $server;

    /**
     * @var    DOMXPath  The xpath object
     * @since  2.0.0
     */
    protected $xpath;

    /**
     * @var    DOMNode  The success node
     * @since  2.0.0
     */
    protected $success;

    /**
     * Constructor.
     *
     * @param   object  $subject  The object to observe
     * @param   array   $config   An array that holds the plugin configuration
     *
     * @since   2.0.0
     */
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
        JLog::addLogger(
            array('logger' => 'externallogin', 'db_table' => '#__externallogin_logs', 'plugin' => 'system-emundus_caslogin'),
            JLog::ALL,
            array(
                'system-emundus_caslogin-logout',
                'system-emundus_caslogin-login',
                'system-emundus_caslogin-verify',
                'system-emundus_caslogin-xml',
                'system-emundus_caslogin-autologin',
                'system-emundus_caslogin-groups'
            )
        );
    }

    /**
     * Get icons.
     *
     * @param   string  $context  The calling context
     *
     * @return  array   $icons
     *
     * @since   2.0.0
     */
    public function onGetIcons($context)
    {
        $icons = array();

        if ($context == 'com_externallogin') {
            JFactory::getDocument()->addStyleDeclaration(
                '.icon-caslogin {'
                . 'width: 48px;'
                . 'height: 48px;'
                . 'background-image: url(../media/plg_system_caslogin/images/administrator/icon-48-caslogin.png);'
                . 'background-position: center center;'
                . '}'
            );

            $icons[] = array(
                'image' => 'icon-caslogin',
                'link' => JRoute::_('index.php?option=com_externallogin&task=server.add&plugin=system.caslogin'),
                'alt' => JText::_('PLG_SYSTEM_CASLOGIN_ALT'),
                'text' => JText::_('PLG_SYSTEM_CASLOGIN_TEXT'),
                'target' => '_parent'
            );
        }

        return $icons;
    }

    /**
     * Get option.
     *
     * @param   string  $context  The calling context
     *
     * @return  array
     *
     * @since   2.0.0
     */
    function onGetOption($context) {
        /*
         * Plugin code goes here.
         * You can access database and application objects and parameters via $this->db,
         * $this->app and $this->params respectively
         */
        if ($context == 'com_externallogin') {
            return array('value' => 'system.emundus_caslogin', 'text' => 'eMundus CAS Login');
        }
    }

    /**
     * Prepare Form
     *
     * @param   JForm  $form  The form to be altered.
     * @param   array  $data  The associated data for the form.
     *
     * @return	boolean
     *
     * @since	2.0.0
     */
    public function onContentPrepareForm($form, $data)
    {
        if (!($form instanceof JForm)) {
            $this->_subject->setError('JERROR_NOT_A_FORM');

            return false;
        }

        // Check we are manipulating a valid form.
        if ($form->getName() != 'com_externallogin.server.system.emundus_caslogin') {
            return true;
        }

        // Add the registration fields to the form.
        JForm::addFormPath(dirname(__FILE__) . '/forms');
        $form->loadFile('emundus_cas', false);

        return true;
    }

    /**
     * After initialise event
     *
     * @return	void
     *
     * @since	2.0.0
     */
    public function onAfterInitialise()
    {
        // If the user is not connected
        if (JFactory::getUser()->guest) {
            $app = JFactory::getApplication();
            $db = JFactory::getDbo();
            $input = $app->input;

            // Get the service
            $uri = JFactory::getURI();

            $ticket = $input->get('ticket');

            if ($app->isAdmin()) {
                $sid = $input->get('server');
            } else {
                $sid = $app->getUserState('com_externallogin.server');
            }

            if(empty($sid)){
                $query = $db->getQuery(true);

                $query->select('id')
                    ->from($db->quoteName('#__externallogin_servers'))
                    ->where($db->quoteName('published') . ' = 1');
                $db->setQuery($query);
                $sid = $db->loadResult();
            }

            if (!empty($ticket) && !empty($sid)) {
                // Load the server
                $server = JTable::getInstance('Server', 'ExternalloginTable');

                if ($server->load($sid) && $server->plugin == 'system.emundus_caslogin') {
                    $params = $server->params;

                    // Log message
                    if ($params->get('log_login', 0)) {
                        JLog::add(
                            new ExternalloginLogEntry(
                                'Attempt to login using ticket "' . $ticket . '" on server ' . $sid,
                                JLog::INFO,
                                'system-emundus_caslogin-login'
                            )
                        );
                    }

                    $uri->delVar('ticket');

                    // Get the certificate information
                    $certificateFile = $params->get('certificate_file', '');
                    $certificatePath = $params->get('certificate_path', '');

                    // Verify the service
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt(
                        $curl, CURLOPT_URL,
                        $this->getUrl($params) . ($params->get('cas_v3') ? '/p3' : '' ) .
                        '/serviceValidate?ticket=' . $ticket . '&service=' . urlencode($uri)
                    );
                    curl_setopt($curl, CURLOPT_TIMEOUT, $params->get('timeout'));
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $certificateFile || $certificatePath);
                    curl_setopt($curl, CURLOPT_CAINFO, $certificateFile);
                    curl_setopt($curl, CURLOPT_CAPATH, $certificatePath);
                    if($params->get('ssl_cipher',0) == 1) {
                        curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT:!DH');
                    }
                    $result = curl_exec($curl);
                    if(!$result){
                        $error = curl_error($curl);
                        JLog::add(
                            new ExternalloginLogEntry(
                                'Error at verification of server ' . $sid . "\n" . $error,
                                JLog::ERROR,
                                'system-emundus_caslogin-verify'
                            )
                        );
                    }
                    curl_close($curl);

                    // Result is not empty
                    if (!empty($result)) {
                        $malformed_attributes = $params->get('attributes_trim',0);

                        // Log message
                        if ($params->get('log_verify', 0)) {
                            JLog::add(
                                new ExternalloginLogEntry(
                                    'Successful verification of server ' . $sid,
                                    JLog::INFO,
                                    'system-emundus_caslogin-verify'
                                )
                            );
                        }

                        // Log message
                        if ($params->get('log_xml', 0)) {
                            JLog::add(
                                new ExternalloginLogEntry(
                                    'Analyzing XML response on server ' . $sid . "\n" . $result,
                                    JLog::INFO,
                                    'system-emundus_caslogin-xml'
                                )
                            );
                        }

                        $dom = new DOMDocument;

                        if ($dom->loadXML($result)) {
                            // Log message
                            if ($params->get('log_xml', 0)) {
                                JLog::add(
                                    new ExternalloginLogEntry(
                                        'Successful analysis of XML response on server ' . $sid,
                                        JLog::INFO,
                                        'system-emundus_caslogin-xml'
                                    )
                                );
                            }

                            $xpath = new DOMXPath($dom);
                            $xpath->registerNamespace('cas', 'http://www.yale.edu/tp/cas');
                            $success = $xpath->query('/cas:serviceResponse/cas:authenticationSuccess')[1];
                            if($success->length != 1){
                                $success = $xpath->query('/cas:serviceResponse/cas:authenticationSuccess');
                            }

                            if ($success && $success->length == 1) {
                                // Store the xpath
                                $this->xpath = $xpath;

                                // Store the success node
                                $this->success = $success->item(0);

                                // Store the server
                                $this->server = $server;

                                // Get email & username
                                if($malformed_attributes == 1) {
                                    // REGEX
                                    $re = '/\s/m';
                                    $re2 = '/\----/m';
                                    $subst = '';

                                    $userName = trim(preg_replace($re2, $subst, preg_replace($re, $subst, $this->xpath->evaluate($params->get('username_xpath') ?: 'string(cas:user)', $this->success))));
                                    $userEmail = str_replace(
                                        array('<', '>', '"', "'", '%', ';', '(', ')', '&', '\\'),
                                        '',
                                        trim(preg_replace($re2, $subst, preg_replace($re, $subst, $this->xpath->evaluate($params->get('email_xpath'), $this->success))))
                                    );
                                } else {
                                    $userName = $this->xpath->evaluate($params->get('username_xpath') ?: 'string(cas:user)', $this->success);
                                    $userEmail = str_replace(
                                        array('<', '>', '"', "'", '%', ';', '(', ')', '&', '\\'),
                                        '',
                                        $this->xpath->evaluate($params->get('email_xpath'), $this->success)
                                    );
                                }

                                // Log message
                                if ($params->get('log_xml', 0)) {
                                    JLog::add(
                                        new ExternalloginLogEntry(
                                            'Successful login on server ' . $sid . ' for CAS user "' .
                                            $this->xpath->evaluate($params->get('username_xpath') ?: 'string(cas:user)', $this->success) . '"',
                                            JLog::INFO,
                                            'system-emundus_caslogin-xml'
                                        )
                                    );
                                }

                                // Check if user is enabled for cas login. Deny if not
                                $query = $db->getQuery(true);
                                $query->select("id");
                                $query->from("#__users");
                                $query->where($db->quoteName("username") . ' = ' . $db->quote($userName));
                                $db->setQuery($query);

                                try {
                                    $uID = $db->loadResult();

                                    if(empty($uID) && !empty($userEmail)){
                                        $query->clear()
                                            ->select("id");
                                        $query->from("#__users");
                                        $query->where($db->quoteName("username") . ' = ' . $db->quote($userEmail));
                                        $db->setQuery($query);
                                        $uID = $db->loadResult();

                                        if(!empty($uID)){
                                            $query->clear()
                                                ->update("#__users");
                                            $query->set($db->quoteName("username") . ' = ' . $db->quote($userName));
                                            $query->where($db->quoteName("id") . ' = ' . $db->quote($uID));
                                            $db->setQuery($query);
                                            $db->execute();

                                            $query->clear()
                                                ->insert("#__externallogin_users");
                                            $query->set($db->quoteName("server_id") . ' = ' . $db->quote($sid));
                                            $query->set($db->quoteName("user_id") . ' = ' . $db->quote($uID));
                                            $db->setQuery($query);
                                            $db->execute();
                                        }
                                    }
                                } catch (Exception $exc) {
                                    $app->enqueueMessage($exc->getMessage(), 'error');
                                }

                                // After check: true if user is activated for current server, else false
                                $access = null;

                                // Check if server is active for registered user, unregistered users should pass for reg.
                                if (!empty($uID)) {
                                    $query = $db->getQuery(true);
                                    $query->select("server_id");
                                    $query->from("#__externallogin_users");
                                    $query->where("user_id = '$uID'");
                                    $db->setQuery($query);

                                    // Load the servers assigned to the user
                                    try {
                                        $servers = $db->loadColumn();

                                        // Check if current server is activated for the user
                                        if (!empty($servers)) {
                                            foreach ($servers as $server) {
                                                if ($server == $sid) {
                                                    // Server is activated for this user - access granted
                                                    $access = true;
                                                    break;
                                                }
                                            }

                                            // Current server is not activated for this user - no access
                                            if (!$access) {
                                                $app->enqueueMessage(JText::_('PLG_SYSTEM_CASLOGIN_NO_ACTIVATED_SERVER'), 'error');
                                            }
                                        } else {
                                            $query->clear()
                                                ->insert("#__externallogin_users");
                                            $query->set($db->quoteName("server_id") . ' = ' . $db->quote($sid));
                                            $query->set($db->quoteName("user_id") . ' = ' . $db->quote($uID));
                                            $db->setQuery($query);
                                            $db->execute();

                                            $access = true;
                                        }
                                    } catch (Exception $exc) {
                                        $app->enqueueMessage($exc->getMessage(), 'error');
                                    }
                                } else {
                                    // User from CAS is a new user on this Joomla! instance
                                    $access = true;
                                }

                                // Log that access was denied
                                if (!$access) {
                                    JLog::add(
                                        new ExternalloginLogEntry(
                                            'Unsuccessful login on server ' . $sid . ', user not activated for this server',
                                            JLog::INFO,
                                            'system-emundus_caslogin-xml'
                                        )
                                    );
                                } else {
                                    // If the return url is for an Itemid, we look it up in the menu
                                    // in case it is a redirect to an external source
                                    $query = $uri->getQuery(true);

                                    if (empty($return) && !empty($query) && count($query) === 1 && array_key_exists('Itemid', $query)) {
                                        $menu      = $app->getMenu();
                                        $menuEntry = $menu->getItem($query['Itemid']);

                                        if (!empty($menuEntry)) {
                                            $return = $menuEntry->link;
                                        }
                                    }

                                    if (empty($return)) {
                                        // Original way of determining the return url
                                        $return = 'index.php' . $uri->toString(array('query'));
                                    }

                                    if ($return == 'index.php?option=com_login') {
                                        $return = 'index.php';
                                    }

                                    $request = JFactory::getApplication()->input->getInputForRequestMethod();

                                    // Prepare the connection process
                                    if ($app->isAdmin()) {
                                        $input->set('option', 'com_login');
                                        $input->set('task', 'login');
                                        $input->set(JSession::getFormToken(), 1);

                                        // We are forced to encode the url in base64 as com_login uses this encoding
                                        $request->set('return', base64_encode($return));
                                    } else {
                                        // Detect redirect menu item from the params
                                        $redirect = $params->get('redirect');

                                        if (!empty($redirect) && (!$params->get('noredirect') || $return != 'index.php')) {
                                            $return = 'index.php?Itemid=' . $redirect;
                                        }

                                        $input->set('option', 'com_users');
                                        $input->set('task', 'user.login');
                                        $request->set('Itemid', 0);
                                        $input->post->set(JSession::getFormToken(), 1);

                                        // We are forced to encode the url in base64 as com_users uses this encoding
                                        $request->set('return', base64_encode($return));
                                    }
                                }
                            } else {
                                // Log message
                                if ($params->get('log_xml', 0)) {
                                    JLog::add(
                                        new ExternalloginLogEntry(
                                            'Unsuccessful login on server ' . $sid,
                                            JLog::INFO,
                                            'system-caslogin-xml'
                                        )
                                    );
                                }
                            }
                        } else {
                            JLog::add(
                                new ExternalloginLogEntry(
                                    'Unsuccessful analysis of XML response on server ' . $sid,
                                    JLog::WARNING,
                                    'system-caslogin-xml'
                                )
                            );
                        }
                    } else {
                        // Log message
                        if ($params->get('log_verify', 0)) {
                            JLog::add(
                                new ExternalloginLogEntry(
                                    'Unsuccessful verification of server ' . $sid,
                                    JLog::WARNING,
                                    'system-caslogin-verify'
                                )
                            );
                        }
                    }
                }
            } else if (empty($sid)) {
                // Get CAS servers
                $model = JModelLegacy::getInstance('Servers', 'ExternalloginModel', array('ignore_request' => true));
                $model->setState('filter.published', 1);
                $model->setState('filter.plugin', 'system.caslogin');
                $model->setState('list.start', 0);
                $model->setState('list.limit', 0);
                $model->setState('list.ordering', 'a.ordering');
                $model->setState('list.direction', 'ASC');
                $servers = $model->getItems();

                // Try to autologin for some servers
                foreach ($servers as $server) {
                    $params = new JRegistry($server->params);
                    $sid = $server->id;

                    if ($params->get('autologin') == 1 && !$app->getUserState('system.caslogin.autologin.' . $server->id)) {
                        // Get the certificate information
                        $certificateFile = $params->get('certificate_file', '');
                        $certificatePath = $params->get('certificate_path', '');

                        // Verify the service
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($curl, CURLOPT_URL, $this->getUrl($params));
                        curl_setopt($curl, CURLOPT_TIMEOUT, $params->get('timeout'));
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $certificateFile || $certificatePath);
                        curl_setopt($curl, CURLOPT_CAINFO, $certificateFile);
                        curl_setopt($curl, CURLOPT_CAPATH, $certificatePath);
                        if($params->get('ssl_cipher',0) == 1) {
                            curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT:!DH');
                        }
                        $result = curl_exec($curl);
                        if(!$result){
                            $error = curl_error($curl);
                            JLog::add(
                                new ExternalloginLogEntry(
                                    'Error at verification of server ' . $sid . "\n" . $error,
                                    JLog::ERROR,
                                    'system-emundus_caslogin-verify'
                                )
                            );
                        }
                        curl_close($curl);

                        // Result is not empty
                        if (!empty($result)) {
                            // Log message
                            if ($params->get('log_verify', 0)) {
                                JLog::add(
                                    new ExternalloginLogEntry(
                                        'Successful verification of server ' . $sid,
                                        JLog::INFO,
                                        'system-caslogin-verify'
                                    )
                                );
                            }

                            // Log message
                            if ($params->get('log_autologin', 0)) {
                                JLog::add(
                                    new ExternalloginLogEntry(
                                        'Trying autologin on server ' . $sid,
                                        JLog::INFO,
                                        'system-caslogin-autologin'
                                    )
                                );
                            }

                            $app->setUserState('com_externallogin.server', $server->id);
                            $app->setUserState('system.caslogin.autologin.' . $server->id, 1);
                            $app->redirect($this->getUrl($params) . '/login?service=' . urlencode($uri) . '&gateway=true');
                        } else {
                            // Log message
                            if ($params->get('log_verify', 0)) {
                                JLog::add(
                                    new ExternalloginLogEntry(
                                        'Unsuccessful verification of server ' . $sid,
                                        JLog::WARNING,
                                        'system-caslogin-verify'
                                    )
                                );
                            }
                        }
                    }
                }
            } else {
                // Load the server
                $server = JTable::getInstance('Server', 'ExternalloginTable');

                if ($server->load($sid) && $server->plugin == 'system.caslogin') {
                    $params = $server->params;

                    // Log message
                    if ($params->get('log_autologin', 0)) {
                        JLog::add(
                            new ExternalloginLogEntry(
                                'Autologin failed on server ' . $sid,
                                JLog::INFO,
                                'system-caslogin-autologin'
                            )
                        );
                    }
                }
            }
        }
    }

    /**
     * Get Login URL
     *
     * @param   object  $server   The CAS server.
     * @param   string  $service  The asked service.
     *
     * @return	void|string
     *
     * @since	2.0.0
     */
    public function onGetLoginUrl($server, $service)
    {
        if ($server->plugin == 'system.emundus_caslogin') {
            // Return the login URL
            $url = $this->getUrl($server->params) . '/login?service=' . urlencode($service);

            if ($server->params->get('locale')) {
                list($locale, $country) = explode('-', JFactory::getLanguage()->getTag());
                $url .= '&locale=' . $locale;
            }

            return $url;
        }
    }

    /**
     * External Login event
     *
     * @param   JAuthenticationResponse  $response  Response to the login process
     *
     * @return	void|true
     *
     * @since	2.0.0
     */
    public function onExternalLogin(&$response)
    {
        if (isset($this->success)) {
            // Prepare response
            $server = $this->server;
            $params = $server->params;
            $sid = $server->id;
            $response->status = JAuthentication::STATUS_SUCCESS;
            $response->server = $server;
            $response->type = 'system.emundus_caslogin';
            $response->message = '';

            $malformed_attributes = $params->get('attributes_trim',0);

            // Compute attributes
            if($malformed_attributes == 1) {
                // REGEX
                $re = '/\s/m';
                $re2 = '/\----/m';
                $subst = '';

                $response->username = str_replace(
                    array('<', '>', '"', "'", '%', ';', '(', ')', '&', '\\'),
                    '',
                    trim(preg_replace($re2, $subst, preg_replace($re, $subst, $this->xpath->evaluate($params->get('username_xpath'), $this->success))))
                );
                $response->email = str_replace(
                    array('<', '>', '"', "'", '%', ';', '(', ')', '&', '\\'),
                    '',
                    trim(preg_replace($re2, $subst, preg_replace($re, $subst, $this->xpath->evaluate($params->get('email_xpath'), $this->success))))
                );
                $response->firstname = !empty($params->get('firstname_xpath')) ? trim(preg_replace($re2, $subst, preg_replace($re, $subst, $this->xpath->evaluate($params->get('firstname_xpath'), $this->success)))) : '';
                $response->lastname = !empty($params->get('lastname_xpath')) ? trim(preg_replace($re2, $subst, preg_replace($re, $subst, $this->xpath->evaluate($params->get('lastname_xpath'), $this->success)))) : '';
                if(!empty($params->get('name_xpath',''))) {
                    $response->fullname = trim(preg_replace($re2, $subst, preg_replace($re, $subst, $this->xpath->evaluate($params->get('name_xpath'), $this->success))));
                } else {
                    $response->fullname = $response->lastname . ' ' . $response->firstname;
                }
            } else {
                $response->username = str_replace(
                    array('<', '>', '"', "'", '%', ';', '(', ')', '&', '\\'),
                    '',
                    $this->xpath->evaluate($params->get('username_xpath'), $this->success)
                );
                $response->email = str_replace(
                    array('<', '>', '"', "'", '%', ';', '(', ')', '&', '\\'),
                    '',
                    $this->xpath->evaluate($params->get('email_xpath'), $this->success)
                );
                $response->firstname = !empty($params->get('firstname_xpath')) ? $this->xpath->evaluate($params->get('firstname_xpath'), $this->success) : '';
                $response->lastname = !empty($params->get('lastname_xpath')) ? $this->xpath->evaluate($params->get('lastname_xpath'), $this->success) : '';
                if(!empty($params->get('name_xpath',''))) {
                    $response->fullname = $this->xpath->evaluate($params->get('name_xpath'), $this->success);
                } else {
                    $response->fullname = $response->lastname . ' ' . $response->firstname;
                }
            }


            // Compute groups
            if ($params->get('group_xpath')) {
                $groups = $this->xpath->query($params->get('group_xpath'), $this->success);

                if ($groups && $groups->length > 0) {
                    // Log message
                    if ($params->get('log_groups', 0)) {
                        JLog::add(
                            new ExternalloginLogEntry(
                                'Successful detection of groups for user "' . $response->username . '" on server ' . $sid,
                                JLog::INFO,
                                'system-caslogin-groups'
                            )
                        );
                    }

                    $response->groups = array();

                    // Loop on each group attribute
                    for ($i = 0; $i < $groups->length; $i++) {
                        $group = (string) $groups->item($i)->nodeValue;

                        if (is_numeric($group) && $params->get('group_integer', 0)) {
                            // Log message
                            if ($params->get('log_groups', 0)) {
                                JLog::add(
                                    new ExternalloginLogEntry(
                                        'Found integer group ' . $group . ' of groups for user "' . $response->username . '" on server ' . $sid,
                                        JLog::INFO,
                                        'system-caslogin-groups'
                                    )
                                );
                            }

                            // Group is numeric
                            $dbo = JFactory::getDbo();
                            $query = $dbo->getQuery(true);
                            $query->select('id')->from('#__usergroups')->where('id = ' . (int) $group);
                            $dbo->setQuery($query);

                            if ($dbo->loadResult()) {
                                // Log message
                                if ($params->get('log_groups', 0))
                                {
                                    JLog::add(
                                        new ExternalloginLogEntry(
                                            'Added integer group ' . $group . ' of groups for user "' . $response->username . '" on server ' . $sid,
                                            JLog::INFO,
                                            'system-caslogin-groups'
                                        )
                                    );
                                }

                                $response->groups[] = $group;
                            }
                        } else {
                            // Log message
                            if ($params->get('log_groups', 0)) {
                                JLog::add(
                                    new ExternalloginLogEntry(
                                        'Found string group(s) "' . $group . '" for user "' . $response->username . '" on server ' . $sid,
                                        JLog::INFO,
                                        'system-caslogin-groups'
                                    )
                                );
                            }

                            // Group is not numeric, extract the groups
                            $newgroups = (array) ExternalloginHelper::getGroups($group, $params->get('group_separator', ''));
                            $response->groups = array_merge($response->groups, $newgroups);

                            // Log message
                            if ($params->get('log_groups', 0)) {
                                if (empty($newgroups)) {
                                    JLog::add(
                                        new ExternalloginLogEntry(
                                            'No Joomla! groups found from "' . $group . '" on server ' . $sid,
                                            JLog::INFO,
                                            'system-caslogin-groups'
                                        )
                                    );
                                } else {
                                    JLog::add(
                                        new ExternalloginLogEntry(
                                            'Added groups (' . implode(',', $newgroups) . ') for user "' .
                                            $response->username . '" on server ' . $sid,
                                            JLog::INFO,
                                            'system-caslogin-groups'
                                        )
                                    );
                                }
                            }
                        }
                    }
                } else {
                    // Log message
                    if ($params->get('log_groups', 0)) {
                        JLog::add(
                            new ExternalloginLogEntry(
                                'Unsuccessful detection of groups for user "' . $response->username . '" on server ' . $sid,
                                JLog::WARNING,
                                'system-caslogin-groups'
                            )
                        );
                    }
                }
            }

            // Compute other properties
            $response->other_properties = array();
            if ($params->get('other_properties')) {
                $other_properties = json_decode($params->get('other_properties'));

                foreach ($other_properties->name as $key => $db_column){
                    $xpath = $other_properties->code_xpath[$key];

                    if($malformed_attributes == 1) {
                        $responses = trim(preg_replace($re2, $subst, preg_replace($re, $subst, $this->xpath->query($xpath, $this->success))));
                    } else {
                        $responses = $this->xpath->query($xpath, $this->success);
                    }

                    $values = array();
                    if($responses->length > 0) {
                        for ($i = 0; $i < $responses->length; $i++) {
                            $values[] = $responses->item($i)->nodeValue;
                        }
                    }

                    $item = new stdClass;
                    $item->values = implode(',',$values);
                    $item->method = $other_properties->insert_update[$key];
                    $response->other_properties[$db_column] = $item;
                }
            }

            return true;
        }
    }

    /**
     * Get server URL
     *
     * @param   JRegistry  $params  The CAS parameters.
     *
     * @return	string  The server URL
     *
     * @since	2.0.0
     */
    protected function getUrl($params)
    {
        // Get the parameters
        $ssl = $params->get('ssl', 1);
        $url = $params->get('url');
        $dir = $params->get('dir');
        $port = (int) $params->get('port');

        // Return the server URL
        return 'http' . ($ssl == 1 ? 's' : '') . '://' . $url . ($port && $port != 443 ? (':' . $port) : '') . ($dir ? ('/' . $dir) : '');
    }

    /**
     * This method should handle any logout logic and report back to the subject
     *
     * @param   array  $user     Holds the user data.
     * @param   array  $options  Array holding options (client, ...).
     *
     * @return	boolean  True on success
     *
     * @since	2.0.0
     */
    public function onUserLogout($user, $options = array())
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__externallogin_servers AS a');
        $query->leftJoin('#__externallogin_users AS e ON e.server_id = a.id');
        $query->where('a.published = 1');
        $query->where('e.user_id = ' . (int) $user['id']);
        $db->setQuery($query);
        $server = $db->loadObject();

        if ($server) {
            // Destroy session
            $my 		= JFactory::getUser();
            $session 	= JFactory::getSession();
            $app 		= JFactory::getApplication();

            // Make sure we're a valid user first
            if ($user['id'] == 0 && !$my->get('tmp_user')) {
                return true;
            }

            // Check to see if we're deleting the current session
            if ($my->get('id') == $user['id'] && $options['clientid'] == $app->getClientId()) {
                // Hit the user last visit field
                $my->setLastVisit();

                // Destroy the php session for this user
                $session->destroy();
            }

            // Force logout all users with that userid
            $db = JFactory::getDBO();
            $db->setQuery(
                'DELETE FROM ' . $db->quoteName('#__session') .
                ' WHERE ' . $db->quoteName('userid') . ' = ' . (int) $user['id'] .
                ' AND ' . $db->quoteName('client_id') . ' = ' . (int) $options['clientid']
            );
            $db->execute();

            $params = new JRegistry($server->params);

            $local = $app->input->get('local');

            if (!isset($local) && $params->get('autologout') && $my->get('id') == $user['id']) {
                // Log message
                if ($params->get('log_logout', 0)) {
                    JLog::add(
                        new ExternalloginLogEntry(
                            'Logout of user "' . $user['username'] . '" on server ' . $server->id,
                            JLog::INFO,
                            'system-caslogin-logout'
                        )
                    );
                }

                $locale = '';
                if ($params->get('locale')) {
                    list($locale, $country) = explode('-', JFactory::getLanguage()->getTag());
                    $locale = '&locale=' . $locale;
                }

                $baseURL = $this->getUrl($params);
                $redirect = $baseURL . '/logout' . $locale;

                if ($params->get('logouturl')) {
                    $redirect = $baseURL . '/logout?service=' . urlencode($params->get('logouturl')) . $locale;
                } else if ($app->input->get('return')) {
                    $return = base64_decode($app->input->get('return', '', 'base64'));
                    $return = is_numeric($return)  ? ExternalloginHelper::url($return) : $return;

                    $redirect = $baseURL . '/logout?service=' . urlencode($return) . '&url=' . urlencode($return) . $locale;
                }

                $app->redirect($redirect);
            }
        }

        return true;
    }
}

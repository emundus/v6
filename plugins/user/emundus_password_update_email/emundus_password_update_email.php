<?php
/**
 * @version     $Id: emundus_ametys.php 10709 2016-04-07 09:58:52Z emundus.fr $
 * @package     Joomla
 * @copyright   Copyright (C) 2016 emundus.fr. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
use Sabre\VObject\Property\Boolean;

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * emundus_ametys loggin from Ametys CMS
 *
 * @package     Joomla
 * @subpackage  System
 */
class  plgUserEmundus_password_update_email extends JPlugin {

	/**
	 * Gets object of connections
	 *
	 * @param   Object user
	 * @param   Boolean isnew
	 * @return bool|string of connection tables id, description
	 * @throws Exception
	 */
	public function onUserAfterSave($user, $isnew, $result, $error) {
        $mail_to_user = $this->params->get('mail_to_user', 1);

        if ($isnew || empty($user)) {
            return;
        }

        // if saving user's data was successful
        if ($result && !$error) {
            // Send activation email
            if ($this->sendEmail($user)) {
                $app = JFactory::getApplication();
                $app->enqueueMessage(JText::_('PLG_EMUNDUS_PASSWORD_EMAIL_SENT'));
            }
        }
	}

    /**
     * Send activation email to user in order to proof it
     * @since  3.9.1
     *
     * @access private
     *
     * @param  array  $data  JUser Properties ($user->getProperties)
     * @param  string $token Activation token
     *
     * @return bool
     * @throws Exception
     */
    private function sendEmail($data) {

        $jinput = JFactory::getApplication()->input;
        $password = !empty($data['password_clear']) ? $data['password_clear'] : $jinput->post->get('jos_emundus_users___password');

        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'controllers'.DS.'messages.php');
        $c_messages = new EmundusControllerMessages();

        $baseURL = rtrim(JURI::root(), '/');

        // Compile the user activated notification mail values.
        $config = JFactory::getConfig();

        $post = [
            'USER_NAME'     => $data['name'],
            'USER_EMAIL'    => $data['email'],
            'SITE_NAME'     => $config->get('sitename'),
            'BASE_URL'      => $baseURL,
            'USER_LOGIN'    => $data['username'],
            'USER_PASSWORD' => $password
        ];

        // Send the email.
        return $c_messages->sendEmailNoFnum($data['email'], $this->params->get('email', 'password_notification'), $post);
    }
}

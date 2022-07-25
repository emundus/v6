<?php
class EmundusModelUser extends JModelList
{
    public function sendActivationEmail($data, $token, $email)
    {
        if (json_decode($data['params'])->skip_activation) {
            return false;
        }

        $jinput = JFactory::getApplication()->input;
        $civility = is_array($jinput->post->get('jos_emundus_users___civility')) ? $jinput->post->get('jos_emundus_users___civility')[0] : $jinput->post->get('jos_emundus_users___civility');
        $password = !empty($data['password_clear']) ? $data['password_clear'] : $jinput->post->get('jos_emundus_users___password');

        require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'controllers' . DS . 'messages.php');
        $c_messages = new EmundusControllerMessages();

        $userID = (int)$data['id'];
        $baseURL = rtrim(JURI::root(), '/');
        $md5Token = md5($token);

        // Compile the user activated notification mail values.
        $config = JFactory::getConfig();

        // Get a SEF friendly URL or else sites with SEF return 404.
        // WARNING: This requires making a root level menu item in the backoffice going to com_users&task=edit on the slug /activation.
        // TODO: Possibly use JRoute to make this work without needing a menu item?
        if ($config->get('sef') == 0) {
            $activation_url_rel = '/index.php?option=com_users&task=edit&emailactivation=1&u=' . $userID . '&' . $md5Token . '=1';
        } else {
            $activation_url_rel = '/activation?emailactivation=1&u=' . $userID . '&' . $md5Token . '=1';
        }
        $activation_url = $baseURL . $activation_url_rel;

        $post = [
            'CIVILITY' => $civility,
            'USER_NAME' => $data['name'],
            'USER_EMAIL' => $email,
            'SITE_NAME' => $config->get('sitename'),
            'ACTIVATION_URL' => $activation_url,
            'ACTIVATION_URL_REL' => $activation_url_rel,
            'BASE_URL' => $baseURL,
            'USER_LOGIN' => $email,
            'USER_PASSWORD' => $password
        ];

        return $c_messages->sendEmailNoFnum($email, 'registration_email', $post);
    }

    public function updateEmailUser($user_id,$email){
        $db = JFactory::getDbo();
        $session = JFactory::getSession();
        $current_user = $session->get('emundusUser');

        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('email') . ' = ' . $db->quote($email),
            $db->quoteName('username') . ' = ' . $db->quote($email)
        );
        $conditions = array(
            $db->quoteName('id') . ' = '. $db->quote($user_id)
        );
        $query->update($db->quoteName('#__users'))->set($fields)->where($conditions);
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (Exception $e) {
            JLog::add('Error updating email user: ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
        }

        $query->clear();
        $fields2 = array(
            $db->quoteName('email') . ' = ' . $db->quote($email)
        );
        $conditions2 = array(
            $db->quoteName('user_id') . ' = '. $db->quote($user_id)
        );
        $query->update($db->quoteName('#__emundus_users'))->set($fields2)->where($conditions2);
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (Exception $e) {
            JLog::add('Error updating email user: ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
        }

        $current_user->username = $email;
        $current_user->email = $email;
        $session->set('emundusUser', $current_user);
    }

}
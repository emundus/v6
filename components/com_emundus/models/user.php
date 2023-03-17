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

        $template   = JFactory::getApplication()->getTemplate(true);
        $params     = $template->params;
        if (!empty($params->get('logo')->custom->image)) {
            $logo = json_decode(str_replace("'", "\"", $params->get('logo')->custom->image), true);
            $logo = !empty($logo['path']) ? JURI::base().$logo['path'] : "";

        } else {
            $logo_module = JModuleHelper::getModuleById('90');
            preg_match('#src="(.*?)"#i', $logo_module->content, $tab);
            $pattern = "/^(?:ftp|https?|feed)?:?\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+\|=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";

            if ((bool) preg_match($pattern, $tab[1])) {
                $tab[1] = parse_url($tab[1], PHP_URL_PATH);
            }

            $logo = JURI::base().$tab[1];
        }

        $post = [
            'CIVILITY' => $civility,
            'USER_NAME' => $data['name'],
            'USER_EMAIL' => $email,
            'SITE_NAME' => $config->get('sitename'),
            'ACTIVATION_URL' => $activation_url,
            'ACTIVATION_URL_REL' => $activation_url_rel,
            'BASE_URL' => $baseURL,
            'USER_LOGIN' => $email,
            'USER_PASSWORD' => $password,
            'LOGO' => $logo
        ];

	    return $c_messages->sendEmailNoFnum($email, 'registration_email', $post, $userID);
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

    public function getUsernameByEmail($email){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $username = '';

        try {
            // Check if $email is not a username
            $query->select('username')
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('username') . ' LIKE ' . $db->quote($email));
            $db->setQuery($query);
            $username = $db->loadResult();

            if(empty($username)) {
                $query->clear()
                    ->select('username')
                    ->from($db->quoteName('#__users'))
                    ->where($db->quoteName('email') . ' LIKE ' . $db->quote($email));
                $db->setQuery($query);
                $username = $db->loadResult();
            }
        } catch (Exception $e) {
            JLog::add(basename(__FILE__) . ' | Error getting username with email : ' . $email, JLog::ERROR, 'com_emundus');
        }

        return $username;
    }

}

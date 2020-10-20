<?php
/**
 * Created by PhpStorm.
 * User: James Dean
 * Date: 2018-12-17
 * Time: 10:11
 */



        include_once(JPATH_SITE.'/components/com_emundus/helpers/access.php');

        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.setProfile.php'], JLog::ALL, ['com_emundus']);

        $session = JFactory::getSession();
        $current_user = $session->get('emundusUser');
        if (!empty($current_user->fnum)) {

            $france = 1020;
            $etranger = 1021;

            $country = $fabrikFormData['country_raw'][0];

            if($country == 78) {

                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                $query->select('*')
                    ->from($db->quoteName('#__emundus_setup_profiles', 'esp'))
                    ->where('esp.id = ' . $france);
                try {
                    $db->setQuery($query);
                    $p = $db->loadObject();
                } catch (Exception $e) {
                    JLog::add('Unable to get profile in plugin/emundusSetProfile at query: ' . preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
                    return false;
                }

                // Change user status session.
                $current_user->menutype = $p->menutype;
                $current_user->profile = $p->id;
                $session->set('emundusUser', $current_user);

                if (!EmundusHelperAccess::asCoordinatorAccessLevel($current_user->id)) {
                    // Update user profile in the database.
                    $query->clear()
                        ->update($db->quoteName('#__emundus_users'))
                        ->set($db->quoteName('profile') . ' = ' . $france)
                        ->where($db->quoteName('user_id') . ' = ' . $current_user->id);
                    try {
                        $db->setQuery($query);
                        $db->execute();
                    } catch (Exception $e) {
                        JLog::add('Unable to set profile in plugin/emundusSetProfile at query: ' . preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
                    }
                }
            }

            else {

                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                $query->select('*')
                    ->from($db->quoteName('#__emundus_setup_profiles', 'esp'))
                    ->where('esp.id = ' . $etranger);
                try {
                    $db->setQuery($query);
                    $p = $db->loadObject();
                } catch (Exception $e) {
                    JLog::add('Unable to get profile in plugin/emundusSetProfile at query: ' . preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
                    return false;
                }

                // Change user status session.
                $current_user->menutype = $p->menutype;
                $current_user->profile = $p->id;
                $session->set('emundusUser', $current_user);

                if (!EmundusHelperAccess::asCoordinatorAccessLevel($current_user->id)) {
                    // Update user profile in the database.
                    $query->clear()
                        ->update($db->quoteName('#__emundus_users'))
                        ->set($db->quoteName('profile') . ' = ' . $etranger)
                        ->where($db->quoteName('user_id') . ' = ' . $current_user->id);
                    try {
                        $db->setQuery($query);
                        $db->execute();
                    } catch (Exception $e) {
                        JLog::add('Unable to set profile in plugin/emundusSetProfile at query: ' . preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
                    }
                }
            }
        }

<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2015 eMundus. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      eMundus - James Dean
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class plgUserEmundus_su_tt_associate_user extends JPlugin
{
    public function onUserLogin($user, $options = array())
    {
        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.emundus_su_tt_plg.php'], JLog::ALL, ['emundus_su_tt_plg']);
        $app = JFactory::getApplication();


        if (!$app->isAdmin()) {
            $db = JFactory::getDbo();

            $query = $db->getQuery(true);

            $query
                ->clear()
                ->select('fnum')
                ->from($db->quoteName('#__emundus_files_request'))
                ->where($db->quoteName('email') . ' LIKE ' . $db->quote(strtolower($user['email'])))
                ->andwhere($db->quoteName('uploaded') . ' = 0 ');

            $db->setQuery($query);

            try {
                $file_requests = $db->loadColumn();
            } catch (Exception $e) {
                JLog::add('Error logging at the following query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'emundus_su_tt_plg');
                $app->enqueueMessage(JText::_('Nous avons détecté une erreur en récupérant vos dossiers. Veuillez contacter le gestionnaire de la plateforme.'), 'error');
            }

            $em_user = JFactory::getSession()->get('emundusUser');
            $insert = true;
            foreach ($em_user->emProfiles as $p) {
                if ($p->id == 6) {
                    $insert = false;
                }
            }

            if (!empty($file_requests) && ($em_user->profile != 6 || $insert)) {
                require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'users.php');
                $m_user = new EmundusModelUsers();

                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                $query
                    ->clear()
                    ->select('*')
                    ->from($db->quoteName('#__emundus_setup_profiles', 'esp'))
                    ->where('esp.id = 6');
                try {
                    $db->setQuery($query);
                    $p = $db->loadObject();
                } catch (Exception $e) {
                    JLog::add('Error logging at the following query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'emundus_su_tt_plg');
                    $app->enqueueMessage(JText::_('Nous avons détecté une erreur en récupérant vos dossiers. Veuillez contacter le gestionnaire de la plateforme.'), 'error');
                    $app->redirect("index.php");
                }

                // Change user status session.
                $em_user->menutype = $p->menutype;
                $em_user->profile = $p->id;
                $em_user->profile_label = $p->label;
                $em_user->applicant = 0;

                // Unset all candidat keys
                unset($em_user->campaign_id);
                unset($em_user->fnum);
                unset($em_user->fnums);
                unset($em_user->status);
                unset($em_user->candidature_incomplete);
                unset($em_user->start_date);
                unset($em_user->end_date);
                unset($em_user->candidature_start);
                unset($em_user->candidature_end);
                unset($em_user->admission_start_date);
                unset($em_user->admission_end_date);
                unset($em_user->candidature_posted);
                unset($em_user->schoolyear);
                unset($em_user->code);
                unset($em_user->campaign_name);

                if ($insert) {
                    $em_user->emProfiles[] = (object)['id' => $p->id, 'label' => $p->label];
                    // Update user Group in the database.
                    $query
                        ->clear()
                        ->insert($db->quoteName('#__emundus_groups'))
                        ->columns([$db->quoteName('user_id'), $db->quoteName('group_id')])
                        ->values($em_user->id . ', 2');

                    $db->setQuery($query);

                    try {
                        $db->execute();
                    } catch (Exception $e) {
                        JLog::add('Error logging at the following query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'emundus_su_tt_plg');
                        $app->enqueueMessage(JText::_('Nous avons détecté une erreur en assignant vous à vos dossiers. Veuillez contacter le gestionnaire de la plateforme.'), 'error');
                        $app->redirect("index.php");
                    }
                    $m_user->addProfileToUser($em_user->id, 6);
                } else {
                    $query
                        ->clear()
                        ->update('#__emundus_users')
                        ->set($db->quoteName('profile') . ' = 6')
                        ->where($db->quoteName('user_id') . ' = ' . $em_user->id);

                    $db->setQuery($query);
                    $db->execute();
                }
                JFactory::getSession()->set('emundusUser', $em_user);
            }

            if (!empty($file_requests)) {

                $query
                    ->clear()
                    ->select('*')
                    ->from($db->quoteName('#__emundus_auto_eval_encadrant'))
                    ->where($db->quoteName('user') . ' = ' . $db->quote($em_user->id));

                $db->setQuery($query);

                try {
                    if (empty($db->loadObject())) {
                        $app->redirect("auto-evaluation-encadrant");
                    }
                } catch (Exception $e) {
                    JLog::add('Error logging at the following query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'emundus_su_tt_plg');
                    $app->enqueueMessage(JText::_("Nous n'avons pas pû vous redirigez vers votre formulaire d'auto-évaluation. Veuillez vous diriger en cliquant sur le menu indiqué."), 'error');
                }


                // Associate Files
                require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
                $m_files = new EmundusModelFiles();

                // 2.1.1. Association de l'ID user Expert avec le candidat (#__emundus_groups_eval)
                $newobj = new stdClass();
                $newobj->id = 1;
                $newobj->c = 0;
                $newobj->r = 1;
                $newobj->u = 0;
                $newobj->d = 0;

                $actions = [$newobj];

                $m_files->shareUsers([$em_user->id], $actions, $file_requests);

                // Change uploaded state
                $query
                    ->clear()
                    ->update($db->quoteName('#__emundus_files_request'))
                    ->set([$db->quoteName('uploaded') . ' = 1'])
                    ->where($db->quoteName('fnum') . ' IN (' . implode(',', $db->quote($file_requests)) . ')');

                $db->setQuery($query);

                try {
                    $db->execute();
                } catch (Exception $e) {
                    JLog::add('Error logging at the following query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'emundus_su_tt_plg');
                    $app->enqueueMessage(JText::_("Nous avons détecté une erreur liée à l'assignation de vos dossiers. Veuillez vérifier vos informations suivantes."), 'error');
                }
            }
        }
        $app->redirect("index.php");
    }

}
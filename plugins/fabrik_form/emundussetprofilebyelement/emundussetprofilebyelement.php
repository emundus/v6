<?php
/**
 * @version 2: emundusisapplicationsent 2018-12-04 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Locks access to a file if the file is not of a certain status.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';
include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');


/**
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.juseremundus
 * @since       3.0
 */

class PlgFabrik_FormEmundussetprofilebyelement extends plgFabrik_Form
{


    /**
     * Status field
     *
     * @var  string
     */
    protected $URLfield = '';

    /**
     * Get an element name
     *
     * @param string $pname Params property name to look up
     * @param bool $short Short (true) or full (false) element name, default false/full
     *
     * @return    string    element full name
     */
    public function getFieldName($pname, $short = false)
    {
        $params = $this->getParams();

        if ($params->get($pname) == '')
            return '';

        $elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));

        return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
    }

    /**
     * Get the fields value regardless of whether its in joined data or no
     *
     * @param string $pname Params property name to get the value for
     * @param array $data Posted form data
     * @param mixed $default Default value
     *
     * @return  mixed  value
     */
    public function getParam($pname, $default = '')
    {
        $params = $this->getParams();

        if ($params->get($pname) == '')
            return $default;

        return $params->get($pname);
    }

    /**
     * Main script.
     *
     * @return  bool
     */
    public function onBeforeLoad()
    {

        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.setProfileByElement.php'], JLog::ALL, ['com_emundus']);

        $jinput = JFactory::getApplication()->input;

        $fnum = $jinput->get->get('rowid');

        $fabrik_elt = $this->getParam('element');
        $value = $this->getParam('value');
        $profile = $this->getParam('profile');


        if (($fabrik_elt !== '' && empty($value)) || empty($profile)) {
            return false;
        }

        $request = explode('___', $fabrik_elt);
        $repeated_group = strpos($request[0], '_repeat');
        $values = explode(',', $value);
        $profiles = explode(',', $profile);

        $session = JFactory::getSession();
        $user = $session->get('emundusUser');
        $db = JFactory::getDBo();
        $query = $db->getQuery(true);
        if (!empty($user->fnum) && ($user->profile == $profile || in_array($user->profile, $profiles))) {


            $session = JFactory::getSession();

            if (!empty($request[0]) && !empty($request[1])) {

                // get value from application form
                if ($repeated_group) {
                    $query = 'SELECT join_from_table FROM #__fabrik_joins WHERE table_join LIKE ' . $db->Quote($request[0]);
                    try {

                        $db->setQuery($query);
                        $table = $db->loadResult();

                    } catch (Exception $e) {
                        JLog::add('Error in script/setprofile getting parent table at query: ' . $query, JLog::ERROR, 'com_emundus');
                    }

                    $query = 'SELECT `' . $request[1] . '` FROM `' . $request[0] . '` AS rt 
							LEFT JOIN `' . $table . '` AS t ON t.id = rt.parent_id 
							WHERE `t`.`fnum` LIKE ' . $db->Quote($fnum);

                    try {

                        $db->setQuery($query);
                        $columns = $db->loadColumn();

                    } catch (Exception $e) {
                        JLog::add('Error in script/setProfile getting application values from repeated_group at query: ' . $query, JLog::ERROR, 'com_emundus');
                    }

                } else {
                    $query = 'SELECT `' . $request[1] . '` FROM `' . $request[0] . '` WHERE `fnum` LIKE ' . $db->Quote($fnum);
                }

                try {

                    $db->setQuery($query);
                    $column = !empty(json_decode(str_replace(' ', '', $db->loadResult()))) ? json_decode(str_replace(' ', '', $db->loadResult())) : str_replace(' ', '', $db->loadResult());

                } catch (Exception $e) {
                    JLog::add('Error in script/setProfile getting application value at query: ' . $query, JLog::ERROR, 'com_emundus');
                    $column = '';
                }
                // for each value compared make the good group_id association
                foreach ($values as $key => $value) {

                    if ($repeated_group) {
                        $match = in_array($value, $columns);

                    } else if (is_array($column)) {
                        $match = false;
                        foreach ($column as $col) {
                            if ($col == $value) {
                                $match = true;
                                break;
                            }
                        }
                    } else {
                        $match = ($column == $value);
                    }

                    if ($match) {
                        $user->profile = $profiles[$key];
                        $user->menutype = 'menu-profile'.$profiles[$key];
                        $user->fnums[$fnum]->profile_id = $profiles[$key];
                        $user->campaign = '';
                        $insert = true;
                        foreach ($user->emProfiles as $p) {
                            if ($p->id == $user->profile) {
                                $insert = false;
                            }
                        }
                        if ($insert) {
                            $user->emProfiles[] = (object)['id' => $user->profile, 'label' => $user->profile_label];
                        }
                        $session->set('emundusUser', $user);
                        $query = $db->getQuery(true);
                        $query
                            ->update($db->quoteName('#__emundus_users'))
                            ->set($db->quoteName('profile') . ' = ' . $profiles[$key])
                            ->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id));
                        try {
                            $db->setQuery($query);
                            return $db->execute();
                        } catch (Exception $e) {
                            return;
                            JLog::add('Unable to set profile in plugin/emundusSetProfile at query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');
                        }

                    }
                }

            }
        }
    }
    public function onAfterProcess()
    {

        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.setProfileByElement.php'], JLog::ALL, ['com_emundus']);

        $jinput = JFactory::getApplication()->input;

        $fnum = $jinput->get->get('rowid');

        $fabrik_elt = $this->getParam('element');
        $value = $this->getParam('value');
        $profile = $this->getParam('profile');


        if (($fabrik_elt !== '' && empty($value)) || empty($profile)) {
            return false;
        }

        $request = explode('___', $fabrik_elt);
        $repeated_group = strpos($request[0], '_repeat');
        $values = explode(',', $value);
        $profiles = explode(',', $profile);

        $session = JFactory::getSession();
        $user = $session->get('emundusUser');
        $db = JFactory::getDBo();
        $query = $db->getQuery(true);
        if (!empty($user->fnum) && ($user->profile == $profile || in_array($user->profile, $profiles))) {


            $session = JFactory::getSession();

            if (!empty($request[0]) && !empty($request[1])) {

                // get value from application form
                if ($repeated_group) {
                    $query = 'SELECT join_from_table FROM #__fabrik_joins WHERE table_join LIKE ' . $db->Quote($request[0]);
                    try {

                        $db->setQuery($query);
                        $table = $db->loadResult();

                    } catch (Exception $e) {
                        JLog::add('Error in script/setprofile getting parent table at query: ' . $query, JLog::ERROR, 'com_emundus');
                    }

                    $query = 'SELECT `' . $request[1] . '` FROM `' . $request[0] . '` AS rt 
							LEFT JOIN `' . $table . '` AS t ON t.id = rt.parent_id 
							WHERE `t`.`fnum` LIKE ' . $db->Quote($fnum);

                    try {

                        $db->setQuery($query);
                        $columns = $db->loadColumn();

                    } catch (Exception $e) {
                        JLog::add('Error in script/setProfile getting application values from repeated_group at query: ' . $query, JLog::ERROR, 'com_emundus');
                    }

                } else {
                    $query = 'SELECT `' . $request[1] . '` FROM `' . $request[0] . '` WHERE `fnum` LIKE ' . $db->Quote($fnum);
                }

                try {

                    $db->setQuery($query);
                    $column = !empty(json_decode(str_replace(' ', '', $db->loadResult()))) ? json_decode(str_replace(' ', '', $db->loadResult())) : str_replace(' ', '', $db->loadResult());

                } catch (Exception $e) {
                    JLog::add('Error in script/setProfile getting application value at query: ' . $query, JLog::ERROR, 'com_emundus');
                    $column = '';
                }
                // for each value compared make the good group_id association
                foreach ($values as $key => $value) {

                    if ($repeated_group) {
                        $match = in_array($value, $columns);

                    } else if (is_array($column)) {
                        $match = false;
                        foreach ($column as $col) {
                            if ($col == $value) {
                                $match = true;
                                break;
                            }
                        }
                    } else {
                        $match = ($column == $value);
                    }

                    if ($match) {
                        $user->profile = $profiles[$key];
                        $user->menutype = 'menu-profile'.$profiles[$key];
                        $user->fnums[$fnum]->profile_id = $profiles[$key];
                        $user->campaign = '';
                        $insert = true;
                        foreach ($user->emProfiles as $p) {
                            if ($p->id == $user->profile) {
                                $insert = false;
                            }
                        }
                        if ($insert) {
                            $user->emProfiles[] = (object)['id' => $user->profile, 'label' => $user->profile_label];
                        }
                        $session->set('emundusUser', $user);
                        $query = $db->getQuery(true);
                        $query
                            ->update($db->quoteName('#__emundus_users'))
                            ->set($db->quoteName('profile') . ' = ' . $profiles[$key])
                            ->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id));
                        try {
                            $db->setQuery($query);
                            return $db->execute();
                        } catch (Exception $e) {
                            return;
                            JLog::add('Unable to set profile in plugin/emundusSetProfile at query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');
                        }
                    }
                }
            }
        }
    }
}

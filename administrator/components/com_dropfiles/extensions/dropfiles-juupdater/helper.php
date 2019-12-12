<?php

/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barr?re (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') || die;

/**
 * Class JuupdaterHelper
 */
class JuupdaterHelper
{
    /**
     * Add token
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public static function juAddToken()
    {
        $app = JFactory::getApplication();
        $token = $app->input->get('token');
        if (!empty($token)) {
            self::juUpdateConfigToken('token=' . $token);
            self::juUpdateSiteToken('token=' . $token);
            self::exitStatus(true, array('token' => $token));
        } else {
            self::exitStatus(false, array('token' => ''));
        }
    }

    /**
     * Remove token
     *
     * @return void
     * @since  version
     */
    public static function juRemoveToken()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('name') . ' = ' . $db->quote('ju_user_token')
        );

        $query->delete($db->quoteName('#__joomunited_config'));
        $query->where($conditions);
        $db->setQuery($query);
        $db->execute();


        // Update site token
        $ju_base = 'https://www.joomunited.com';
        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('extra_query') . ' = ' . $db->quote(''),
        );

        $query->update($db->quoteName('#__update_sites'))->set($fields)
            ->where($db->quoteName('location') . ' LIKE ' . $db->quote('%' . $ju_base . '%'));
        $db->setQuery($query);
        $db->execute();

        // Remove in #__updaters table
        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('extra_query') . ' = ' . $db->quote(''),
        );
        $query->update($db->quoteName('#__updates'))->set($fields)
            ->where($db->quoteName('detailsurl') . ' LIKE ' . $db->quote('%' . $ju_base . '%'));
        $db->setQuery($query);
        $db->execute();

        self::exitStatus(true, array());
    }

    /**
     * Display return
     *
     * @param boolean $status Response status
     * @param array   $datas  Response datas
     *
     * @return void
     * @throws |Exception Throw when application can not start
     * @since  version
     */
    public static function exitStatus($status, $datas = array())
    {
        $response = array('response' => $status, 'datas' => $datas);
        echo json_encode($response);
        JFactory::getApplication()->close();
    }

    /**
     * Check config token
     *
     * @return integer
     * @since  version
     */
    public static function checkConfigToken()
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__joomunited_config');
        $query->where('name = "ju_user_token"');
        $db->setQuery($query);
        $res = $db->loadObjectList();

        return count($res);
    }

    /**
     * Update config token
     *
     * @param string $token Token
     *
     * @return void
     * @since  version
     */
    public static function juUpdateConfigToken($token)
    {
        $count = self::checkConfigToken();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        if (empty($count)) {
            $columns = array('name', 'value');
            $values = array($db->quote('ju_user_token'), $db->quote($token));
            $query
                ->insert($db->quoteName('#__joomunited_config'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
        } else {
            $fields = array(
                $db->quoteName('value') . ' = ' . $db->quote($token),
            );
            $query->update($db->quoteName('#__joomunited_config'))->set($fields)->where("name = 'ju_user_token'");
        }

        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Update site token
     *
     * @param string $token Token
     *
     * @return void
     * @since  version
     */
    public static function juUpdateSiteToken($token)
    {
        $ju_base = 'https://www.joomunited.com';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('extra_query') . ' = ' . $db->quote($token . '&siteurl=' . JUri::root()),
        );

        $query->update($db->quoteName('#__update_sites'))->set($fields)
            ->where($db->quoteName('location') . ' LIKE ' . $db->quote('%' . $ju_base . '%'));
        $db->setQuery($query);
        $db->execute();

        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('extra_query') . ' = ' . $db->quote($token . '&siteurl=' . JUri::root()),
        );

        $query->update($db->quoteName('#__updates'))->set($fields)
            ->where($db->quoteName('detailsurl') . ' LIKE ' . $db->quote('%' . $ju_base . '%'));
        $db->setQuery($query);
        $db->execute();
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: James Dean
 * Date: 2018-12-27
 * Time: 12:12
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class EmundusModelFormations extends JModelLegacy {

    public function checkHR($cid, $user = null, $profile = 1002) {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true);

        $query
            -> select($db->quoteName('id'))
            -> from($db->quoteName('#__emundus_user_entreprise'))
            -> where($db->quoteName('user') . ' = ' . $user . ' AND ' . $db->quoteName('profile') .' = '  . $profile . ' AND cid = ' . $cid);
        $db->setQuery($query);
        try {
            return  $db->loadResult();
        } catch(Exception $e) {
            JLog::add('Error getting stats on number of relations at m/stats in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
        }

    }

    public function deleteCompany($id) {
        $user = JFactory::getSession()->get('emundusUser')->id;

        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);

        $query
            -> delete($db->quoteName('#__emundus_entreprise'))
            -> where('id =' . $id);

        $db->setQuery($query);

        try {
            $db->execute();
        } catch(Exception $e) {
            JLog::add('Error getting stats on number of relations at m/stats in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
        }


    }


    public function deleteAssociate($id, $user = null, $profile = 1002) {
        $user = JFactory::getSession()->get('emundusUser')->id;

        $db = JFactory::getDbo();

        $query = 'SELECT `cid` 
                     FROM `jos_emundus_user_entreprise` 
                     WHERE `jos_emundus_user_entreprise`.`user` = ' . $user .'
                     AND `jos_emundus_user_entreprise`.`profile` = ' . $profile ;


        $db->setQuery($query);
        try {
            $result = $db->loadColumn();

            $query2 = 'DELETE FROM `jos_emundus_user_entreprise` 
                        WHERE cid IN (' . implode(',', $result) . ')
                        AND user = ' . $id;

            $db->setQuery($query2);
            $db->execute();

        } catch(Exception $e) {
            JLog::add('Error getting stats on number of relations at m/stats in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
        }


    }

}
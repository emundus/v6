<?php
/**
 * @package	eMundus
 * @version	6.6.5
 * @author	eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * A cron task to create a reference when status change to CA
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.tchooz_check_limits
 * @since       3.0
 */

class PlgEmundusTchooz_check_limits extends JPlugin {

    function __construct(&$subject, $config) {
        parent::__construct($subject, $config);

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.tchooz_check_limits.php'), JLog::ALL, array('com_emundus'));
    }


    function onCreateNewFile($uid, $fnum, $campaign) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            // Get limitations variables
            $max_app = (int)JFactory::getConfig()->get('plan_limit_app_forms');
            //

            $query->select('count(id)')
                ->from($db->quoteName('#__emundus_campaign_candidature'));
            $db->setQuery($query);
            $applications_count = $db->loadResult();

            if($max_app != 0){
                if($applications_count >= $max_app){
                    $query->clear()
                        ->update($db->quoteName('#__emundus_campaign_candidature'))
                        ->set($db->quoteName('published') . ' = -2')
                        ->where($db->quoteName('fnum') . ' = ' . $fnum);
                    $db->setQuery($query);
                    $db->execute();
                }
            } else {
                return false;
            }

        } catch(Exception $e) {
            JLog::add('plugins/emundus/tchooz_check_limits | Error when try to check limits : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }

        return true;
    }
}

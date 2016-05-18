    <?php
    /**
     * @package     Joomla
     * @subpackage  eMundus
     * @link        http://www.emundus.fr
     * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
     * @license     GNU General Public License version 2 or later; see LICENSE.txt
     * @author      eMundus - Benjamin Rivalland
     */

    // No direct access
    defined('_JEXEC') or die( 'Restricted access' );

    jimport('joomla.plugin.plugin');
    /**
     * Joomla User plugin
     *
     * @package     Joomla.Plugin
     * @subpackage  User.emundus
     * @since       5.0.0
     */
    class plgUserEmundus_ametys extends JPlugin
    {
        
        /**
         * This method should handle any login logic and report back to the subject
         *
         * @param   array   $user       Holds the user data
         * @param   array   $options    Array holding options (remember, autoregister, group)
         *
         * @return  boolean True on success
         * @since   1.5
         */
        public function onUserLogin($user, $options = array())
        {

            jimport('joomla.log.log');
            JLog::addLogger(
                array(
                    // Sets file name
                    'text_file' => 'com_emundus.ametys.php'
                ),
                // Sets messages of all log levels to be sent to the file
                JLog::ALL,
                // The log category/categories which should be recorded in this file
                // In this case, it's just the one category from our extension, still
                // we need to put it inside an array
                array('com_emundus')
            );

            $app            = JFactory::getApplication();
            
            if ($app->isAdmin() && $user['username'] != 'admin') {
                
                include_once(JPATH_SITE.'/components/com_emundus/models/ametys.php');

                $ametys = new EmundusModelAmetys;
                $db     = $ametys->getAmetysDBO();

                $session = substr(str_shuffle(str_repeat('-_abcdefghijklmnopqrstuvwxyz0123456789', 10)), 0, 10);

                $query = 'INSERT INTO `Ametys_CMS`.`Users_CandidateToken` (`login`, `token`, `creation_date`)
                            VALUES ("'.$user['email'].'", "'.$session.'", "'.date("Y-m-d H:i:s").'")';
                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch (Exception $e) {
                    // catch any database errors.
                    JLog::add('ERROR : CANNOT SET SESSION IN AMETYS :: '.$query, JLog::INFO, 'com_emundus');
                }

                $app->redirect(JURI::root().'?token='.$session);
            }

            return true;
        }



    }

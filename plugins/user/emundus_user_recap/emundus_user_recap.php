<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        https://www.emundus.fr
 * @copyright   Copyright (C) 2019 eMundus. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      eMundus SAS - Hugo Moracchini
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
class plgUserEmundus_user_recap extends JPlugin {

	public function __construct(&$subject, $config = array()) {
		parent::__construct($subject, $config);

		$this->loadLanguage();

		jimport('joomla.log.log');
		JLog::addLogger(array('text_file' => 'com_emundus.EmundusUserRecap.php'), JLog::ALL, array('com_emundus'));
	}

	/**
	 * Remove all sessions for the user name
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   array    $user    Holds the user data
	 *
	 * @return  boolean
	 * @throws Exception
	 * @since   3.9
	 */
    public function onUserBeforeDelete($user) {

    	if ($user['id'] > 0) {

		    $email = $this->params->get('email');

		    if (empty($email)) {
			    JLog::add('Error: missing email lbl in plugin/user/emundus_user_recap.', JLog::ERROR, 'com_emundus');
			    return false;
		    }

		    if (!extension_loaded('zip')) {
			    JLog::add('Error: ZIP extension not loaded.', JLog::ERROR, 'com_emundus');
			    return false;
		    }

    		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
		    require_once(JPATH_COMPONENT.DS.'controllers'.DS.'files.php');
		    require_once(JPATH_COMPONENT.DS.'controllers'.DS.'messages.php');
    		$m_users = new EmundusModelUsers();
		    $c_files = new EmundusControllerFiles();
		    $c_messages = new EmundusControllerMessages();

    		$fnums = [];
    		$cc = $m_users->getCampaignsCandidature($user['id']);

    		// Hey... it's Friday
    		foreach ($cc as $c) {
				$fnums[] = $c->fnum;
		    }

		    $zip_name = $c_files->export_zip($fnums);
		    $file = JPATH_BASE.DS.'tmp'.DS.$zip_name;

            if (empty($fnums)) {
                $tag_content = JText::_('PLG_USER_RECAP_NO_FILES_DELETED');
            } else {
                if (count($fnums) == 1) {
                    $tag_content = JText::sprintf('PLG_USER_RECAP_ONE_FILE_DELETED', 1);
                } else {
                    $tag_content = JText::sprintf('PLG_USER_RECAP_FILES_DELETED', sizeof($fnums));
                }
            }

            $post = [
                'FILES_DELETED' => $tag_content
            ];

		    $c_messages->sendEmailNoFnum($user['email'], $email, $post, $user['id'], $file);

	    }

        return true;
    }
}

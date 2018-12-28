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

	public function __construct(array $config = array()) {
		JLog::addLogger(array('text_file' => 'com_emundus.emundus-formations.php'), JLog::ALL, array('com_emundus'));
		parent::__construct($config);
	}

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
            JLog::add('Error checking HR at m/formation in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
        }

    }

    public function deleteCompany($id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            -> delete($db->quoteName('#__emundus_entreprise'))
            -> where('id =' . $id);

        $db->setQuery($query);

        try {
            $db->execute();
        } catch(Exception $e) {
            JLog::add('Error deleting company at m/formation in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
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
            JLog::add('Error deleting associate at m/formation in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
        }

    }


	/**
	 * @param      $user_hr Int The user who is supposedly a DRH
	 * @param null $user_intern Int The user who is supposedly an intern._
	 * @param int  $profile Int The profile which determines if a user is DRH.
	 *
	 * @return Bool
	 */
	public function checkHRUser($user_hr, $user_intern = null, $profile = 1002) {

		$db = JFactory::getDbo();

		$query = 'SELECT '.$db->quoteName('user')
			.' FROM '.$db->quoteName('#__emundus_user_entreprise')
			.' WHERE '.$db->quoteName('cid').' IN (
				  SELECT'.$db->quoteName('cid').' FROM '.$db->quoteName('#__emundus_user_entreprise')
				.'WHERE '.$db->quoteName('user').' = '.$user_hr.' AND '.$db->quoteName('profile').' = '.$profile
			.')';
		$db->setQuery($query);

		try {
			return in_array($user_intern, $db->loadColumn());
		} catch(Exception $e) {
			JLog::add('Error checking if we are DHR of user at m/formation in query: '.$query, JLog::ERROR, 'com_emundus');
			return false;
		}

	}


	/**
	 * @param      $user Int The user who we are checking.
	 * @param      $company Int The company the user may be in._
	 *
	 * @return Bool
	 */
	public function checkCompanyUser($user, $company) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('id'))
			->from($db->quoteName('#__emundus_user_entreprise'))
			->where($db->quoteName('user').' = '.$user.' AND '.$db->quoteName('cid').' = '.$company);
		$db->setQuery($query);

		try {
			return !empty($db->loadResult());
		} catch(Exception $e) {
			JLog::add('Error checking if user is in a company at m/formation in query: '.$query, JLog::ERROR, 'com_emundus');
			return false;
		}

	}

}
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

class PlgFabrik_FormEmundussetprofile extends plgFabrik_Form {


	/**
	 * Status field
	 *
	 * @var  string
	 */
	protected $URLfield = '';

	/**
	 * Get an element name
	 *
	 * @param   string  $pname  Params property name to look up
	 * @param   bool    $short  Short (true) or full (false) element name, default false/full
	 *
	 * @return	string	element full name
	 */
	public function getFieldName($pname, $short = false) {
		$params = $this->getParams();

		if ($params->get($pname) == '')
			return '';

		$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));

		return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
	}

	/**
	 * Get the fields value regardless of whether its in joined data or no
	 *
	 * @param   string  $pname    Params property name to get the value for
	 * @param   array   $data     Posted form data
	 * @param   mixed   $default  Default value
	 *
	 * @return  mixed  value
	 */
	public function getParam($pname, $default = '') {
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
	public function onBeforeLoad() {

		include_once(JPATH_SITE.'/components/com_emundus/helpers/access.php');

		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.setProfile.php'], JLog::ALL, ['com_emundus']);

		$status = $this->getParam('status');
		$profile = $this->getParam('profile');
		$redirect = $this->getParam('redirect');

		if (($status !== '0' && empty($status)) || empty($profile)) {
			return false;
		}

		$session = JFactory::getSession();
		$current_user = $session->get('emundusUser');

		if (!empty($current_user->fnum) && ($current_user->status == $status || in_array($current_user->status, explode(',' , $status))) && $current_user->profile != $profile) {

			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('*')
				->from($db->quoteName('#__emundus_setup_profiles', 'esp'))
				->where('esp.id = '.$profile);
			try {
				$db->setQuery($query);
				$p = $db->loadObject();
			} catch (Exception $e) {
				JLog::add('Unable to get profile in plugin/emundusSetProfile at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
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
					->set($db->quoteName('profile').' = '.$profile)
					->where($db->quoteName('user_id').' = '.$current_user->id);
				try {
					$db->setQuery($query);
					$db->execute();
				} catch (Exception $e) {
					JLog::add('Unable to set profile in plugin/emundusSetProfile at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
				}
			}

			if (!empty($redirect)) {
                $m_application 	= new EmundusModelApplication;

                $query
                    ->clear()
                    ->select('CONCAT(m.link,"&Itemid=", m.id) as link')
                    ->from($db->quoteName('#__emundus_setup_profiles', 'esp'))
                    ->leftJoin($db->quoteName('#__menu', 'm').' ON '.$db->quoteName('m.menutype').' = '.$db->quoteName('esp.menutype').' AND '.$db->quoteName('m.published').'=1 AND '.$db->quoteName('link').' <> "" AND '.$db->quoteName('link').' <> "#"')
                    ->where($db->quoteName('esp.menutype').'  LIKE "menu-profile'.$current_user->profile.'"')
                    ->order($db->quoteName('m.lft').' ASC');
                $db->setQuery($query);

                JFactory::getApplication()->redirect(JRoute::_(JURI::base().$db->loadResult()));
            }
		}
		return true;
	}
}

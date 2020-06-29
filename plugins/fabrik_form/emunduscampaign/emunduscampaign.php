<?php
/**
 * @version 2: emunduscampaign 2019-04-11 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Création de dossier de candidature automatique.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.juseremundus
 * @since       3.0
 */

class PlgFabrik_FormEmundusCampaign extends plgFabrik_Form {
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

		if ($params->get($pname) == '') {
			return '';
		}

		$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));

		return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
	}

	/**
	 * Get the fields value regardless of whether its in joined data or no
	 *
	 * @param   string  $pname    Params property name to get the value for
	 * @param   mixed   $default  Default value
	 *
	 * @return  mixed  value
	 */
	public function getParam($pname, $default = '') {
		$params = $this->getParams();

		if ($params->get($pname) == '') {
			return $default;
		}

		return $params->get($pname);
	}

	/**
	 * Main script.
	 *
	 * @return Bool
	 * @throws Exception
	 */
	public function onAfterProcess() {

		jimport('joomla.log.log');
		JLog::addLogger(array('text_file' => 'com_emundus.campaign.php'), JLog::ALL, array('com_emundus'));

		include_once(JPATH_BASE.'/components/com_emundus/models/profile.php');
		$m_profile = new EmundusModelProfile;
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$session = JFactory::getSession();
		$jinput = $app->input;
		$form_type = $this->getParam('form_type', 'cc');

		// This allows the plugin to be run from a different context while retaining the same functionality.
		switch ($form_type) {

			case 'user':

				$query = $db->getQuery(true);
				$query->select($db->quoteName('id'))
					->from($db->quoteName('#__users'))
					->where($db->quoteName('email').' LIKE '.$db->quote($jinput->getString('jos_emundus_users___email_raw')));
				$db->setQuery($query);
				try {
					$user = $db->loadResult();
					if (empty($user)) {
						return false;
					}
				} catch (Exception $e) {
					return false;
				}

				$user = JFactory::getUser($user);

				$campaign_id = is_array($jinput->getInt('jos_emundus_users___campaign_id_raw')) ? $jinput->getInt('jos_emundus_users___campaign_id_raw')[0] : $jinput->getInt('jos_emundus_users___campaign_id_raw');
				if (empty($campaign_id)) {
					return false;
				}

				$query->clear()
					->select($db->quoteName('id'))
					->from($db->quoteName('#__emundus_setup_campaigns'))
					->where($db->quoteName('id').' = '.$campaign_id);
				$db->setQuery($query);
				try {
					if (empty($db->loadResult())) {
						return false;
					}
				} catch (Exception $e) {
					return false;
				}

				// create new fnum
				$fnum = date('YmdHis').str_pad($campaign_id, 7, '0', STR_PAD_LEFT).str_pad($user->id, 7, '0', STR_PAD_LEFT);

				$query->clear()
					->insert($db->quoteName('#__emundus_campaign_candidature'))
					->columns($db->quoteName(['applicant_id', 'user_id', 'campaign_id', 'fnum']))
					->values($user->id.', '.$user->id.', '.$campaign_id.', '.$db->quote($fnum));
			break;

			case 'cc':
			default:
				$user = $session->get('emundusUser');
				if (empty($user)) {
					$user = JFactory::getUser();
				}
				$campaign_id = $jinput->get('jos_emundus_campaign_candidature___campaign_id_raw')[0];
				$fnum_tmp = $jinput->get('jos_emundus_campaign_candidature___fnum');
				$id = $jinput->get('jos_emundus_campaign_candidature___id');

				// create new fnum
				$fnum = date('YmdHis').str_pad($campaign_id, 7, '0', STR_PAD_LEFT).str_pad($user->id, 7, '0', STR_PAD_LEFT);
				$query = $db->getQuery(true);
				$query->update($db->quoteName('#__emundus_campaign_candidature'))
					->set($db->quoteName('fnum').' = '.$db->Quote($fnum))
					->where($db->quoteName('id').' = '.$id.' AND '.$db->quoteName('fnum').' LIKE '.$db->Quote($fnum_tmp).' AND '.$db->quoteName('campaign_id').'='.$campaign_id);
				break;

		}

		try {

			$db->setQuery($query);
			$db->execute();

			JPluginHelper::importPlugin('emundus');
			$dispatcher = JEventDispatcher::getInstance();
			$dispatcher->trigger('onCreateNewFile', [$user->id, $fnum, $campaign_id]);

		} catch (Exception $e) {
			JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query->__toString(), JLog::ERROR, 'com_emundus');
			JError::raiseError(500, $query->__toString());
		}

		$query = 'SELECT esc.*,  esp.label as plabel, esp.menutype
				FROM #__emundus_setup_campaigns AS esc
				LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = esc.profile_id
				WHERE esc.id='.$campaign_id;

		try {

			$db->setQuery($query);
			$campaign = $db->loadAssoc();

		} catch (Exception $e) {
			JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
			JError::raiseError(500, $query);
		}

		jimport( 'joomla.user.helper' );
		$user_profile = JUserHelper::getProfile($user->id)->emundus_profile;

		$schoolyear = $campaign['year'];
		$profile = $campaign['profile_id'];
		$firstname = ucfirst($user_profile['firstname']);
		$lastname = ucfirst($user_profile['lastname']);

		// Insert data in #__emundus_users
		$p = $m_profile->isProfileUserSet($user->id);
		if ($p['cpt'] == 0 ) {

			$query = 'INSERT INTO #__emundus_users (user_id, firstname, lastname, profile, schoolyear, registerDate)
			values ('.$user->id.', '.$db->quote(ucfirst($firstname)).', '.$db->quote(strtoupper($lastname)).', '.$profile.', '.$db->quote($schoolyear).', '.$db->quote($user->registerDate).')';

			try {
				$db->setQuery($query);
				$db->execute();
			} catch (Exception $e) {
				JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
				JError::raiseError(500, $query);
			}
		}

		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'))
			->from($db->quoteName('#__emundus_users_profiles'))
			->where($db->quoteName('user_id').' = '.$user->id.' AND '.$db->quoteName('profile_id').' = '.$profile);
		$db->setQuery($query);
		try {
			if (empty($db->loadResult())) {
				// Insert data in #__emundus_users_profiles
				$query = 'INSERT INTO #__emundus_users_profiles (user_id, profile_id) VALUES ('.$user->id.','.$profile.')';
				$db->setQuery($query);
				try {
					$db->execute();
				} catch (Exception $e) {
					JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
					JError::raiseError(500, $query);
				}
			}
		} catch(Exception $e) {
			JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
			JError::raiseError(500, $query);
		}

		if ($form_type == 'cc') {
			$m_profile->initEmundusSession();

			$url = $this->getParam('emunduscampaign_redirect_url', null);
			if (empty($url)) {

				include_once(JPATH_BASE.'/components/com_emundus/models/application.php');
				$m_application = new EmundusModelApplication();
				$url = 'index.php?option=com_emundus&task=openfile&fnum='.$fnum.'&redirect='.base64_encode($m_application->getFirstPage('index.php',$fnum)[$fnum]['link']);

			}
			$app->redirect($url, JText::_('FILE_OK'));
		}
		return true;
	}

	/**
	 * Raise an error - depends on whether you are in admin or not as to what to do
	 *
	 * @param array   &$err   Form models error array
	 * @param string   $field Name
	 * @param string   $msg   Message
	 *
	 * @return  void
	 * @throws Exception
	 */
	protected function raiseError(&$err, $field, $msg) {
		$app = JFactory::getApplication();

		if ($app->isAdmin()) {
			$app->enqueueMessage($msg, 'notice');
		} else {
			$err[$field][0][] = $msg;
		}
	}
}

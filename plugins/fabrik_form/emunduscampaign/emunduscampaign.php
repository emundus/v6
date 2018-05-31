<?php
/**
 * @version 2: emundusredirect 2018-04-25 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Redirection et chainage des formulaires suivant le profile de l'utilisateur
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

class PlgFabrik_FormEmundusCampaign extends plgFabrik_Form
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
	 * @param   string  $pname  Params property name to look up
	 * @param   bool    $short  Short (true) or full (false) element name, default false/full
	 *
	 * @return	string	element full name
	 */
	public function getFieldName($pname, $short = false)
	{
		$params = $this->getParams();

		if ($params->get($pname) == '')
		{
			return '';
		}

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
	public function getParam($pname, $default = '')
	{
		$params = $this->getParams();

		if ($params->get($pname) == '')
		{
			return $default;
		}

		return $params->get($pname);
	}

	/**
	 * Main script.
	 *
	 * @return  bool
	 */
	public function onAfterProcess()
	{

		jimport('joomla.log.log');
		JLog::addLogger(
			array(
				// Sets file name
				'text_file' => 'com_emundus.duplicate.php'
			),
			JLog::ALL,
			array('com_emundus')
		);

		include_once(JPATH_BASE.'/components/com_emundus/models/profile.php');
		$m_profile 	= new EmundusModelProfile;
		$app 		= JFactory::getApplication();
		$db 		= JFactory::getDBO();
		$session 	= JFactory::getSession();
		$user 		= $session->get('emundusUser');
		$jinput     = $app->input;

		if (empty($user)) {
			$user = JFactory::getUser();
		}

		$campaign_id = $jinput->get('jos_emundus_campaign_candidature___campaign_id_raw')[0];
		$fnum_tmp = $jinput->get('jos_emundus_campaign_candidature___fnum');
		$id = $jinput->get('jos_emundus_campaign_candidature___id');

		// create new fnum
		$fnum = date('YmdHis').str_pad($campaign_id, 7, '0', STR_PAD_LEFT).str_pad($user->id, 7, '0', STR_PAD_LEFT);

		try {

			$query = 'UPDATE #__emundus_campaign_candidature
				SET `fnum`='.$db->Quote($fnum). '
				WHERE id='.$id.' AND applicant_id='.$user->id. ' AND fnum like '.$db->Quote($fnum_tmp).' AND campaign_id='.$campaign_id;

			$db->setQuery($query);
			$db->execute();

		} catch (Exception $e) {
			JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
			JError::raiseError(500, $query);
		}


		try {

			$query = 'SELECT esc.*,  esp.label as plabel, esp.menutype
				FROM #__emundus_setup_campaigns AS esc
				LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = esc.profile_id
				WHERE esc.id='.$campaign_id;

			$db->setQuery($query);
			$campaign = $db->loadAssoc();

		} catch (Exception $e) {
			JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
			JError::raiseError(500, $query);
		}

		jimport( 'joomla.user.helper' );
		$user_profile = JUserHelper::getProfile($user->id)->emundus_profile;

		$schoolyear        = $campaign['year'];
		$profile           = $campaign['profile_id'];
		$firstname         = ucfirst($user_profile['firstname']);
		$lastname          = ucfirst($user_profile['lastname']);
		$registerDate      = $db->Quote($user->registerDate);
		$candidature_start = $campaign['start_date'];
		$candidature_end   = $campaign['end_date'];
		$label             = $campaign['plabel'];
		$campaign_label    = $campaign['label'];
		$menutype          = $campaign['menutype'];

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


		// Insert data in #__emundus_users_profiles
		$query = 'INSERT INTO #__emundus_users_profiles (user_id, profile_id) VALUES ('.$user->id.','.$profile.')';
		$db->setQuery($query);
		try {
			$db->execute();
		} catch (Exception $e) {
			JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
			JError::raiseError(500, $query);
		}

		$m_profile->initEmundusSession();

		$url = $this->getParam('emunduscampaign_redirect_url', 'index.php');
		$app->redirect($url,  JText::_('FILE_OK'));
	}

	/**
	 * Raise an error - depends on whether you are in admin or not as to what to do
	 *
	 * @param   array   &$err   Form models error array
	 * @param   string  $field  Name
	 * @param   string  $msg    Message
	 *
	 * @return  void
	 */

	protected function raiseError(&$err, $field, $msg)
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			$app->enqueueMessage($msg, 'notice');
		}
		else
		{
			$err[$field][0][] = $msg;
		}
	}
}

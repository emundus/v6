<?php
/**
 * @package         Joomla.Plugins
 * @subpackage      System.actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;

/**
 * Joomla! Users Actions Logging Plugin.
 *
 * @since  3.9.0
 */
class PlgSystemEmundusProxyRedirect extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  3.9.0
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  3.9.0
	 */
	protected $db;

	/**
	 * Load plugin language file automatically so that it can be used inside component
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array    $config   An optional associative array of configuration settings.
	 *
	 * @since   3.9.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->app = Factory::getApplication();
		if (version_compare(JVERSION, '4.0', '<')) {
			$this->db = Factory::getDbo();
			$user     = Factory::getUser();
		}
		else {
			$this->db = Factory::getContainer()->get('DatabaseDriver');
			$user     = $this->app->getIdentity();
		}

		if (empty($user) || $user->guest) {
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$http_headers = $_SERVER;

			$username  = $http_headers[$this->params->get('username', '')];
			$email     = $http_headers[$this->params->get('email', '')];
			$fullname  = $http_headers[$this->params->get('fullname', '')];
			$firstname = $http_headers[$this->params->get('firstname', '')];
			$lastname  = $http_headers[$this->params->get('lastname', '')];
			$groups    = $http_headers[$this->params->get('group_attribute', '')];

			$group_mapping = $this->params->get('group_mapping');
			$attributes    = $this->params->get('attributes');

			if (!empty($username) && !empty($email)) {
				require_once JPATH_ROOT . '/components/com_emundus/models/users.php';
				$m_users = new EmundusModelUsers();

				// First we check if username corresponding to existing email
				if (empty(UserHelper::getUserId($username))) {
					$query->select('username')
						->from('#__users')
						->where('email = ' . $db->quote($email));
					$db->setQuery($query);

					try {
						$existing_username = $db->loadResult();
					}
					catch (Exception $e) {
						Log::add('Failed to check if user exists from mail but with another username ' . $e->getMessage(), Log::ERROR, 'com_emundus.error');
					}

					if (!empty($existing_username)) {
						$username = $existing_username;
					}
				}

				if (!empty($fullname) && !empty($lastname) && empty($firstname)) {
					$firstname = trim(str_replace($lastname, '', $fullname));
				}

				$groups_map = $this->getGroupMapping($groups);
				//TODO: Map more attributes to define custom fields
				//$attributes_map = $this->getAttributeMapping();

				// We check again if user exists
				if (empty(UserHelper::getUserId($username))) {
					$user                 = new User();
					$user->name           = $fullname;
					$user->username       = $username;
					$user->email          = $email;
					$user->password_clear = '';
					$user->password       = '';
					$user->block          = 0;
					$user->sendEmail      = 0;
					$user->registerDate   = date('Y-m-d H:i:s');
					$user->activation     = '';
					$user->params         = '';
					$user->guest          = 0;

					$user->groups = $groups_map['j_groups'];
					$e_profiles   = $groups_map['e_profiles'];
					$e_groups     = $groups_map['e_groups'];

					if ($user->save()) {
						$other_param['firstname']    = $firstname;
						$other_param['lastname']     = $lastname;
						$other_param['profile']      = !empty($e_profiles) ? $e_profiles[0] : 9;
						$other_param['em_oprofiles'] = implode(',', $e_profiles);
						$other_param['univ_id']      = 0;
						$other_param['em_groups']    = implode(',', $e_groups);
						$other_param['em_campaigns'] = [];
						$other_param['news']         = '';
						$m_users->addEmundusUser($user->id, $other_param);

						$m_users->login($user->id);
					}
				}
				else {
					$user = User::getInstance($username);

					$user->name          = $fullname;
					$user->email         = $email;
					$user->lastvisitDate = date('Y-m-d H:i:s');
					$user->groups        = $groups_map['j_groups'];

					if ($user->save()) {
						$m_users->login($user->id);
					}
				}
			}
		}
	}

	private function getGroupMapping($groups)
	{
		$result = ['j_groups' => [], 'e_groups' => [], 'e_profiles' => []];

		$group_mapping = $this->params->get('group_mapping');

		if (!empty($group_mapping) && !empty($groups)) {
			$values        = explode(';', $groups);
			$group_mapping = json_decode($group_mapping, true);

			$groups_to_map = array_intersect_key($values, $group_mapping['value']);

			foreach ($groups_to_map as $group) {
				$index = array_search($group, $group_mapping['value']);

				if ($index !== false) {
					$j_groups = $group_mapping['joomla_group'][$index];
					if (!empty($group_mapping['emundus_group'][$index])) {
						$result['e_groups'] = array_merge($result['e_groups'], $group_mapping['emundus_group'][$index]);
					}
					if (!empty($group_mapping['emundus_profile'][$index])) {
						$result['e_profiles'] = array_merge($result['e_profiles'], $group_mapping['emundus_profile'][$index]);
					}

					foreach ($j_groups as $j_group) {
						$result['j_groups'][] = $j_group;
					}
				}
			}

			$result['j_groups']   = array_unique($result['j_groups']);
			$result['e_groups']   = array_unique($result['e_groups']);
			$result['e_profiles'] = array_unique($result['e_profiles']);
		}

		if (empty($result['j_groups'])) {
			$result['j_groups'][] = 2;
		}

		return $result;
	}
}

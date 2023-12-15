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

			$username   = $http_headers[$this->params->get('username', '')];
			$email      = $http_headers[$this->params->get('email', '')];
			$fullname   = $http_headers[$this->params->get('fullname', '')];
			$firstname  = $http_headers[$this->params->get('firstname', '')];
			$lastname   = $http_headers[$this->params->get('lastname', '')];
			$attributes = $this->params->get('attributes', []);

			if (!empty($username) && !empty($email)) {

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

				$user                    = new User();
				$user->name 			= $fullname;
				$user->username 		= $username;
				$user->email 			= $email;
				$user->password_clear 	= '';
				$user->password 		= '';
				$user->block 			= 0;
				$user->sendEmail 		= 0;
				$user->registerDate 	= date('Y-m-d H:i:s');
				$user->activation 		= '';
				$user->params 			= '';
				$user->groups 			= array(2);
				$user->guest 			= 0;
				
				if($user->save()) {
					echo '<pre>'; var_dump('here'); echo '</pre>'; die;
				}
			}
		}
	}
}

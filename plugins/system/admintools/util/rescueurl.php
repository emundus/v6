<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Joomla\CMS\Application\CliApplication;
use Joomla\CMS\Environment\Browser;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Registry\Registry;

defined('_JEXEC') || die;

/**
 * Implements the Rescue URL feature. This feature lets a Super User suspend the backend protection of Admin Tools for
 * their IP address and for a short period of time only so that they have the chance to log in to the site and unblock
 * themselves / fix the Admin Tools configuration which is blocking their access to their site.
 *
 * @since       4.3.0
 */
abstract class AtsystemUtilRescueurl
{
	/**
	 * This string is used in the 'series' column of #__user_keys to signify an Admin Tools Rescue URL token.
	 */
	public const series = 'com_admintools_rescue';

	/**
	 * Is this a CLI application?
	 *
	 * @var   bool
	 */
	protected static $isCLI = null;

	/**
	 * Is this an administrator application?
	 *
	 * @var   bool
	 */
	protected static $isAdmin = null;

	/**
	 * Caches the results of isRescueMode() for faster reference during the same page load.
	 *
	 * @var   null|bool
	 */
	private static $isRescueMode = null;

	/**
	 * Checks if the current request is trying to enable Rescue Mode. If so, we will create a new rescue token and
	 * store
	 * the relevant information in the database.
	 *
	 * This feature is only available on the backend of the site. The reasoning is that if you can access the backend
	 * of
	 * your site you can unblock yourself and fix whatever was blocking you in the first place.
	 *
	 * @param   AtsystemUtilExceptionshandler  $exceptionsHandler  The Admin Tools exceptions handler, used to find
	 *                                                             email templates
	 *
	 * @return  void
	 */
	public static function processRescueURL(AtsystemUtilExceptionshandler $exceptionsHandler)
	{
		// Is the feature enabled?
		if (!self::isFeatureEnabled())
		{
			return;
		}

		// Is this the backend of the site (we do NOT have FOF 3 yet)?
		[$isCli, $isAdmin] = self::isCliAdmin();

		if (!$isAdmin)
		{
			return;
		}

		// Do I have an email address?
		$app   = Factory::getApplication();
		$input = $app->input;
		$email = $input->get('admintools_rescue', '', 'raw');
		$email = empty($email) ? '' : trim($email);

		if (empty($email))
		{
			return;
		}

		if ($email == 'you@example.com')
		{
			echo Text::sprintf('ADMINTOOLS_RESCUEURL_ERR_INVALIDADDRESS', $email);

			$app->close(0);
		}

		// Does the email belong to a Super User?
		$userId = self::isSuperUserByEmail($email);

		if (!$userId)
		{
			return;
		}

		// Create a new random token, 96 characters long (that's about 160 bits of randomness)
		$token = UserHelper::genRandomPassword(96);

		// Check if #__user_keys has another token with series == 'com_admintools_rescue' and delete it
		self::removeOldTokens();

		// Save new #__user_keys record with invalid = 0 (unused; we'll change that to -1 when we use it)
		$browser = Browser::getInstance();
		$user    = Factory::getUser($userId);
		$ip      = AtsystemUtilFilter::getIp();

		self::saveToken($user->username, $token, 0, time(), $browser->getAgentString(), $ip);

		// Send email
		self::sendRescueURLEmail($user, $token, $exceptionsHandler);

		// Close application with a message that the email has been sent
		echo Text::_('ADMINTOOLS_RESCUEURL_SENTMSG');

		$app->close(0);
	}

	/**
	 * Are we in Rescue Mode?
	 *
	 * @return  bool
	 */
	public static function isRescueMode()
	{
		// Check the static cache first
		if (is_bool(self::$isRescueMode))
		{
			return self::$isRescueMode;
		}

		self::$isRescueMode = false;

		// Is the feature enabled?
		if (!self::isFeatureEnabled())
		{
			return false;
		}

		// Is this the backend of the site? (Rescue mode is only valid in the backend)
		[$isCli, $isAdmin] = self::isCliAdmin();

		if (!$isAdmin)
		{
			return false;
		}

		// Remove all expired tokens
		self::removeExpiredTokens();

		// If the token is present AND it's not marked as used, process it
		$app      = Factory::getApplication();
		$token    = $app->input->getCmd('admintools_rescue_token', '');
		$username = empty($token) ? null : self::getUsernameFromToken($token);

		// In case of a valid token I have to set a few things in the session
		if (!empty($username))
		{
			self::setSessionVar('rescue_timestamp', time(), 'com_admintools');
			self::setSessionVar('rescue_username', $username, 'com_admintools');
		}

		// Is the timestamp saved in the session within the time limit?
		$expiresOn = (int) self::getSessionVar('rescue_timestamp', 0, 'com_admintools')
			+ (self::getTimeout() * 60);

		if (time() > $expiresOn)
		{
			return false;
		}

		// We must be guest OR the username must match the one in the token.
		$currentUser = method_exists($app, 'getIdentity') ? $app->getIdentity() : Factory::getUser();
		$username    = self::getSessionVar('rescue_username', '', 'com_admintools');

		if (!empty($currentUser) && !$currentUser->guest && ($currentUser->username != $username))
		{
			return false;
		}

		// All checks passed, this is Rescue Mode
		self::$isRescueMode = true;

		return true;
	}

	public static function processBlockMessage($message, $email = 'you@example.com')
	{
		// Nothing to replace? Don't bother proceeding.
		if (strpos($message, '[RESCUEINFO]') === false)
		{
			return $message;
		}

		// Default replacements text for Rescue Info
		$info = Text::sprintf('ADMINTOOLS_BLOCKED_MESSAGE_RESCUEINFO', $email);

		// If the feature is disabled we will not show any rescue information
		if (!self::isFeatureEnabled())
		{
			$info = '';
		}

		// Step 1. Replace [RESCUEINFO] with the language string.
		$message = str_replace('[RESCUEINFO]', $info, $message);

		// Step 2. Replace [RESCUE_TRIGGER_URL] with the trigger URL for rescue mode
		if (strpos($message, '[RESCUE_TRIGGER_URL]') !== false)
		{
			[$isCli, $isAdmin] = self::isCliAdmin();
			$baseURL = Uri::base();
			$url     = $isAdmin ? str_replace('/administrator', '', $baseURL) : $baseURL;
			$url     = rtrim($url, '/') . '/administrator/index.php?admintools_rescue=';
			$message = str_replace('[RESCUE_TRIGGER_URL]', $url, $message);
		}

		return $message;
	}

	/**
	 * Main function to detect if we're running in a CLI environment and we're admin.
	 *
	 * Copied from FOF 3. We need it here since FOF has not been loaded yet when we need this information.
	 *
	 * @return  array  isCLI and isAdmin. It's not an associative array, so we can use list.
	 */
	protected static function isCliAdmin()
	{
		if (is_null(static::$isCLI) && is_null(static::$isAdmin))
		{
			try
			{
				if (is_null(Factory::$application))
				{
					static::$isCLI = true;
				}
				else
				{
					$app           = Factory::getApplication();
					static::$isCLI =
						$app instanceof Exception ||
						(
						version_compare(JVERSION, '3.99999.99999', 'gt')
							? ($app instanceof CliApplication)
							: ($app instanceof CliApplication)
						);
				}
			}
			catch (Exception $e)
			{
				static::$isCLI = true;
			}

			if (static::$isCLI)
			{
				static::$isAdmin = false;
			}
			else
			{
				$app = Factory::$application;

				if (!$app)
				{
					static::$isAdmin = false;
				}
				elseif (method_exists($app, 'isClient'))
				{
					static::$isAdmin = $app->isClient('administrator');
				}
				elseif (method_exists($app, 'isAdmin'))
				{
					static::$isAdmin = $app->isAdmin();
				}
			}
		}

		return [static::$isCLI, static::$isAdmin];
	}

	/**
	 * Is the Rescue Mode feature enabled in the plugin?
	 *
	 * @return  bool
	 */
	private static function isFeatureEnabled()
	{
		$params = self::getPluginParams();

		return (bool) $params->get('rescueurl', 1);
	}

	/**
	 * Get the rescue mode timeout in minutes. Must be at least one minute.
	 *
	 * @return  int
	 */
	private static function getTimeout()
	{
		$params  = self::getPluginParams();
		$timeout = (int) $params->get('rescueduration', 15);

		if ($timeout <= 0)
		{
			$timeout = 15;
		}

		return $timeout;
	}

	/**
	 * Get the plugin parameters.
	 *
	 * @return Registry
	 */
	private static function getPluginParams()
	{
		$plugin = PluginHelper::getPlugin('system', 'admintools');

		return new Registry($plugin->params);
	}

	/**
	 * Does the user exist, not blocked and have the core.admin (Super User) privilege?
	 *
	 * @param   string  $email  The email to check for
	 *
	 * @return  bool|int
	 */
	private static function isSuperUserByEmail($email)
	{
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn('#__users'))
			->where($db->qn('email') . ' = ' . $db->q($email))
			->where($db->qn('block') . ' = ' . $db->q(0));
		$userID = $db->setQuery($query)->loadResult();

		if (empty($userID))
		{
			return false;
		}

		$user = Factory::getUser($userID);

		if (!$user->authorise('core.admin'))
		{
			return false;
		}

		return $userID;
	}

	/**
	 * Check if #__user_keys has another token with series == 'com_admintools_rescue' and delete it
	 *
	 * @return  void
	 */
	private static function removeOldTokens()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete('#__user_keys')
			->where($db->qn('series') . ' = ' . $db->q(self::series));
		$db->setQuery($query)->execute();
	}

	/**
	 * Save a login token
	 *
	 * @param   string  $username    The username this cookie belongs to.
	 * @param   string  $token       The token to assign to this cookie. The token is stored hashed to prevent
	 *                               side-channel attacks.
	 * @param   int     $invalid     We use this as a status flag. 0 when the token is unused, 1 after it's been used.
	 * @param   string  $time        The timestamp this cookie was created on.
	 * @param   string  $user_agent  The user agent of the user's browser.
	 * @param   string  $ip          The IP address of the user
	 *
	 * @return  void
	 */
	private static function saveToken($username, $token, $invalid, $time, $user_agent, $ip)
	{
		// Create a combined entry for the User Agent string and IP address
		$combined = json_encode([
			'ua' => $user_agent,
			'ip' => $ip,
		]);

		$db = Factory::getDbo();
		$o  = (object) [
			'id'       => null,
			'user_id'  => $username,
			'token'    => UserHelper::hashPassword($token),
			'series'   => self::series,
			'invalid'  => $invalid,
			'time'     => $time,
			'uastring' => $combined,
		];

		if (!$db->insertObject('#__user_keys', $o, 'id'))
		{
			throw new RuntimeException('Could not save token');
		}
	}

	private static function getUsernameFromToken($token, $ua = null, $ip = null)
	{
		// Make sure we have a UA string and an IP address
		if (is_null($ua))
		{
			$browser = Browser::getInstance();
			$ua      = $browser->getAgentString();
		}

		if (is_null($ip))
		{
			$ip = AtsystemUtilFilter::getIp();
		}

		// Create a combined entry for the User Agent string and IP address
		$combined = json_encode([
			'ua' => $ua,
			'ip' => $ip,
		]);

		// Get the cutoff time for tokens
		$rescueDuration   = self::getTimeout() * 60;
		$now              = time();
		$nowMinusDuration = $now - $rescueDuration;

		// Load all non-expired Admin Tools tokens
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__user_keys'))
			->where($db->qn('series') . ' = ' . $db->q(self::series))
			->where($db->qn('time') . ' > ' . $db->q($nowMinusDuration))
			->where($db->qn('uastring') . ' = ' . $db->q($combined));

		if (version_compare(JVERSION, '3.999.999', 'le'))
		{
			$query->where($db->qn('invalid') . ' = ' . $db->q(0));
		}

		$entries = $db->setQuery($query)->loadObjectList();

		// No entry? No user.
		if (empty($entries))
		{
			return null;
		}

		// Loop all entries until we find a matching token
		foreach ($entries as $entry)
		{
			// FYI: Clean text passwords are always truncated to 72 chars. So shorten tokens will always validate
			// https://stackoverflow.com/a/28951717/485241
			if (!UserHelper::verifyPassword($token, $entry->token))
			{
				continue;
			}

			// Mark token as used
			$entry->invalid = 1;
			$db->updateObject('#__user_keys', $entry, 'id');

			return $entry->user_id;
		}

		// If we're here there was no matching token.
		return null;
	}

	/**
	 * Removes all expired Admin Tools tokens
	 *
	 * @return  void
	 */
	private static function removeExpiredTokens()
	{
		$db         = Factory::getDbo();
		$expiration = time() - 60 * self::getTimeout();

		$query = $db->getQuery(true)
			->delete('#__user_keys')
			->where($db->qn('series') . ' = ' . $db->q(self::series))
			->where($db->quoteName('time') . ' < ' . $db->quote($expiration));
		$db->setQuery($query)->execute();
	}

	/**
	 * Send an email with the Rescue URL to the user
	 *
	 * @param   User                           $user               The user requesting the Rescue URL
	 * @param   string                         $token              The Rescue URL token already saved in the database
	 * @param   AtsystemUtilExceptionshandler  $exceptionsHandler  The exceptions handler, used to fetch email templates
	 *
	 * @return  void
	 */
	private static function sendRescueURLEmail(User $user, $token, AtsystemUtilExceptionshandler $exceptionsHandler)
	{
		// Load the component's administrator translation files
		$jlang = Factory::getLanguage();
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, null, true);

		$config = Factory::getConfig();

		// Get the reason in human readable format
		$txtReason = Text::_('ADMINTOOLS_RESCUEURL');

		// Get the backend Rescue URL
		[$isCli, $isAdmin] = self::isCliAdmin();
		$baseURL = Uri::base();
		$url     = $isAdmin ? str_replace('/administrator', '', $baseURL) : $baseURL;
		$url     = rtrim($url, '/') . '/administrator/index.php?admintools_rescue_token=' . $token;

		try
		{
			$mailer = Factory::getMailer();

			$mailfrom = $config->get('mailfrom');
			$fromname = $config->get('fromname');

			// Let's get the most suitable email template
			$template = $exceptionsHandler->getEmailTemplate('rescueurl', true);

			// Got no template, the user didn't published any email template, or the template doesn't want us to
			// send a notification email. Anyway, let's stop here
			if (!$template)
			{
				return;
			}

			$subject = $template[0];
			$body    = $template[1];

			$tokens = $exceptionsHandler->getEmailVariables($txtReason, [
				'[RESCUEURL]' => $url,
				'[USER]'      => $user->username,
			]);

			$subject = str_replace(array_keys($tokens), array_values($tokens), $subject);
			$body    = str_replace(array_keys($tokens), array_values($tokens), $body);

			// This line is required because SpamAssassin is BROKEN
			$mailer->Priority = 3;

			$mailer->isHtml(true);
			$mailer->setSender([$mailfrom, $fromname]);

			// Resets the recipients, otherwise they will pile up
			$mailer->clearAllRecipients();

			if ($mailer->addRecipient($user->email) === false)
			{
				return;
			}

			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$mailer->Send();
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * @return \Joomla\CMS\Session\Session
	 * @throws Exception
	 */
	private static function getSession()
	{
		if (version_compare(JVERSION, '3.999.999', 'le'))
		{
			return Factory::getSession();
		}

		return Factory::getApplication()->getSession();
	}

	/**
	 * Get a variable from the user session
	 *
	 * @param   string  $name       The name of the variable to set
	 * @param   string  $default    (optional) The default value to return if the variable does not exit, default: null
	 * @param   string  $namespace  (optional) The variable's namespace e.g. the component name. Default: 'default'
	 *
	 * @return  mixed
	 */
	private static function getSessionVar($name, $default = null, $namespace = 'default')
	{
		$session = self::getSession();

		// Joomla 3
		if (version_compare(JVERSION, '3.9999.9999', 'le'))
		{
			return $session->get($name, $default, $namespace);
		}

		// Joomla 4
		if (empty($namespace))
		{
			return $session->get($name, $default);
		}

		$registry = $session->get('registry');

		if (is_null($registry))
		{
			$registry = new Registry();

			$session->set('registry', $registry);
		}

		return $registry->get($namespace . '.' . $name, $default);
	}

	/**
	 * Set a variable in the user session
	 *
	 * @param   string  $name       The name of the variable to set
	 * @param   string  $value      (optional) The value to set it to, default is null
	 * @param   string  $namespace  (optional) The variable's namespace e.g. the component name. Default: 'default'
	 *
	 * @return  void
	 */
	private static function setSessionVar($name, $value = null, $namespace = 'default')
	{
		$session = self::getSession();

		// Joomla 3
		if (version_compare(JVERSION, '3.9999.9999', 'le'))
		{
			$session->set($name, $value, $namespace);
		}

		// Joomla 4
		if (empty($namespace))
		{
			$session->set($name, $value);

			return;
		}

		$registry = $session->get('registry');

		if (is_null($registry))
		{
			$registry = new Registry();

			$session->set('registry', $registry);
		}

		$registry->set($namespace . '.' . $name, $value);
	}

}

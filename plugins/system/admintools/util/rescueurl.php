<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Helper\Storage;
use FOF30\Container\Container;
use FOF30\Date\Date;

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
	 * This string is used in the 'series' column of #__user_keys to signify an Admin Tools Rescue URL token.
	 */
	const series = 'com_admintools_rescue';

	/**
	 * Checks if the current request is trying to enable Rescue Mode. If so, we will create a new rescue token and store
	 * the relevant information in the database.
	 *
	 * This feature is only available on the backend of the site. The reasoning is that if you can access the backend of
	 * your site you can unblock yourself and fix whatever was blocking you in the first place.
	 *
	 * @param   AtsystemUtilExceptionshandler  $exceptionsHandler  The Admin Tools exceptions handler, used to find email templates
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
		list($isCli, $isAdmin) = self::isCliAdmin();

		if (!$isAdmin)
		{
			return;
		}

		// Do I have an email address?
		$app   = JFactory::getApplication();
		$input = $app->input;
		$email = $input->get('admintools_rescue', '', 'raw');
		$email = empty($email) ? '' : trim($email);

		if (empty($email))
		{
			return;
		}

		// Does the email belong to a Super User?
		$userId = self::isSuperUserByEmail($email);

		if (!$userId)
		{
			return;
		}

		// Create a new random token, 96 characters long (that's about 160 bits of randomness)
		$token = JUserHelper::genRandomPassword(96);

		// Check if #__user_keys has another token with series == 'com_admintools_rescue' and delete it
		self::removeOldTokens();

		// Save new #__user_keys record with invalid = 0 (unused; we'll change that to -1 when we use it)
		JLoader::import('joomla.environment.browser');
		$browser = JBrowser::getInstance();
		$user    = JFactory::getUser($userId);
		$ip      = AtsystemUtilFilter::getIp();

		self::saveToken($user->username, $token, 0, time(), $browser->getAgentString(), $ip);

		// Send email
		self::sendRescueURLEmail($user, $token, $exceptionsHandler);

		// Close application with a message that the email has been sent
		echo JText::_('ADMINTOOLS_RESCUEURL_SENTMSG');

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
		list($isCli, $isAdmin) = self::isCliAdmin();

		if (!$isAdmin)
		{
			return false;
		}

		// Remove all expired tokens
		self::removeExpiredTokens();

		// Get a reference to the session. I cannot use FOF 3 here because it is not loaded yet.
		$session = JFactory::getSession();

		// If the token is present AND it's not marked as used, process it
		$app   = JFactory::getApplication();
		$token = $app->input->getCmd('admintools_rescue_token', '');
		$username = empty($token) ? null : self::getUsernameFromToken($token);

		// In case of a valid token I have to set a few things in the session
		if (!empty($username))
		{
			$session->set('rescue_timestamp', time(), 'com_admintools');
			$session->set('rescue_username', $username, 'com_admintools');
		}

		// Is the timestamp saved in the session within the time limit?
		$expiresOn = (int) $session->get('rescue_timestamp', 0, 'com_admintools')
			+ (self::getTimeout() * 60);

		if (time() > $expiresOn)
		{
			return false;
		}

		// We must be guest OR the username must match the one in the token.
		$currentUser = JFactory::getUser();
		$username    = $session->get('rescue_username', '', 'com_admintools');

		if (!$currentUser->guest && ($currentUser->username != $username))
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
		$info = JText::sprintf('ADMINTOOLS_BLOCKED_MESSAGE_RESCUEINFO', $email);

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
			list($isCli, $isAdmin) = self::isCliAdmin();
			$baseURL = \JUri::base();
			$url     = $isAdmin ? str_replace('/administrator', '', $baseURL) : $baseURL;
			$url     = rtrim($url, '/') . '/administrator/index.php?admintools_rescue=';
			$message = str_replace('[RESCUE_TRIGGER_URL]', $url, $message);
		}

		return $message;
	}

	/**
	 * Is the Rescue Mode feature enabled in the plugin?
	 *
	 * @return  bool
	 */
	private static function isFeatureEnabled()
	{
		/**
		 * This feature is only available on Joomla! 3.6.0 and later. Previous versions of Joomla! did not have the
		 * #__user_keys database table which lets us store user authentication tokens.
		 */
		if (version_compare(JVERSION, '3.6.0', 'lt'))
		{
			return false;
		}

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
	 * @return \Joomla\Registry\Registry
	 */
	private static function getPluginParams()
	{
		$plugin = JPluginHelper::getPlugin('system', 'admintools');

		return new \Joomla\Registry\Registry($plugin->params);
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
				if (is_null(\JFactory::$application))
				{
					static::$isCLI = true;
				}
				else
				{
					$app           = \JFactory::getApplication();
					static::$isCLI =
						$app instanceof \Exception ||
						(
							version_compare(JVERSION, '3.99999.99999', 'gt')
								? ($app instanceof \Joomla\CMS\Application\CliApplication)
								: ($app instanceof JApplicationCli)
						);
				}
			}
			catch (\Exception $e)
			{
				static::$isCLI = true;
			}

			if (static::$isCLI)
			{
				static::$isAdmin = false;
			}
			else
			{
				static::$isAdmin = !\JFactory::$application ? false : \JFactory::getApplication()->isAdmin();
			}
		}

		return array(static::$isCLI, static::$isAdmin);
	}

	/**
	 * Does the user exist, not blocked and have the core.admin (Super User) privilege?
	 *
	 * @param   string $email The email to check for
	 *
	 * @return  bool|int
	 */
	private static function isSuperUserByEmail($email)
	{
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true)
		             ->select($db->qn('id'))
		             ->from($db->qn('#__users'))
		             ->where($db->qn('email') . ' = ' . $db->q($email))
		             ->where($db->qn('block') . ' = ' . $db->q(0))
		;
		$userID = $db->setQuery($query)->loadResult();

		if (empty($userID))
		{
			return false;
		}

		$user = JFactory::getUser($userID);

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
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
		            ->delete('#__user_keys')
		            ->where($db->qn('series') . ' = ' . $db->q(self::series))
		;
		$db->setQuery($query)->execute();
	}

	/**
	 * Save a login token
	 *
	 * @param   string  $username    The username this cookie belongs to.
	 * @param   string  $token       The token to assign to this cookie. The token is stored hashed to prevent side-channel attacks.
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
		    'ip' => $ip
		]);

		$db = JFactory::getDbo();
		$o  = (object) [
			'id'       => null,
			'user_id'  => $username,
			'token'    => JUserHelper::hashPassword($token),
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
			JLoader::import('joomla.environment.browser');
			$browser = JBrowser::getInstance();
			$ua = $browser->getAgentString();
		}

		if (is_null($ip))
		{
			$ip  = AtsystemUtilFilter::getIp();
		}

		// Create a combined entry for the User Agent string and IP address
		$combined = json_encode([
			'ua' => $ua,
			'ip' => $ip
		]);

		// Get the cutoff time for tokens
		$rescueDuration   = self::getTimeout() * 60;
		$now              = time();
		$nowMinusDuration = $now - $rescueDuration;

		// Load all non-expired Admin Tools tokens
		$db        = JFactory::getDbo();
		$query     = $db->getQuery(true)
		                ->select('*')
		                ->from($db->qn('#__user_keys'))
		                ->where($db->qn('series') . ' = ' . $db->q(self::series))
		                ->where($db->qn('invalid') . ' = ' . $db->q(0))
						->where($db->qn('time') . ' > ' . $db->q($nowMinusDuration))
						->where($db->qn('uastring') . ' = ' . $db->q($combined))
		;

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
			if (!JUserHelper::verifyPassword($token, $entry->token))
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
		$db         = JFactory::getDbo();
		$expiration = time() - 60 * self::getTimeout();

		$query      = $db->getQuery(true)
		                 ->delete('#__user_keys')
		                 ->where($db->qn('series') . ' = ' . $db->q(self::series))
		                 ->where($db->quoteName('time') . ' < ' . $db->quote($expiration))
		;
		$db->setQuery($query)->execute();
	}

	/**
	 * Send an email with the Rescue URL to the user
	 *
	 * @param   JUser                          $user               The user requesting the Rescue URL
	 * @param   string                         $token              The Rescue URL token already saved in the database
	 * @param   AtsystemUtilExceptionshandler  $exceptionsHandler  The exceptions handler, used to fetch email templates
	 *
	 * @return  void
	 */
	private static function sendRescueURLEmail(JUser $user, $token, AtsystemUtilExceptionshandler $exceptionsHandler)
	{
		// Load the component's administrator translation files
		$jlang = JFactory::getLanguage();
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, null, true);

		$config = JFactory::getConfig();

		// Get the reason in human readable format
		$txtReason = JText::_('ADMINTOOLS_RESCUEURL');

		// Get the backend Rescue URL
		list($isCli, $isAdmin) = self::isCliAdmin();
		$baseURL = \JUri::base();
		$url     = $isAdmin ? str_replace('/administrator', '', $baseURL) : $baseURL;
		$url     = rtrim($url, '/') . '/administrator/index.php?admintools_rescue_token=' . $token;

		try
		{
			$mailer = JFactory::getMailer();

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
			$body = $template[1];

			$tokens = $exceptionsHandler->getEmailVariables($txtReason, [
				'[RESCUEURL]' => $url,
				'[USER]'      => $user->username,
			]);

			$subject = str_replace(array_keys($tokens), array_values($tokens), $subject);
			$body = str_replace(array_keys($tokens), array_values($tokens), $body);

			// This line is required because SpamAssassin is BROKEN
			$mailer->Priority = 3;

			$mailer->isHtml(true);
			$mailer->setSender(array($mailfrom, $fromname));

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
		catch (\Exception $e)
		{
		}
	}
}

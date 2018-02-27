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
use FOF30\Utils\TimezoneWrangler;

class AtsystemUtilExceptionshandler
{
	/** @var   JRegistry  Plugin parameters */
	protected $params = null;

	/** @var   Storage  Component parameters */
	protected $cparams = null;

	/** @var   Container  The component's container */
	protected $container;

	public function __construct(JRegistry &$params, Storage &$cparams)
	{
		$this->params    = $params;
		$this->cparams   = $cparams;
		$this->container = Container::getInstance('com_admintools');
	}

	/**
	 * Logs security exceptions and processes the IP auto-ban for this IP
	 *
	 * @param string $reason                   Block reason code
	 * @param string $extraLogInformation      Extra information to be written to the text log file
	 * @param string $extraLogTableInformation Extra information to be written to the extradata field of the log table (useful for JSON format)
	 *
	 * @return bool
	 */
	public function logAndAutoban($reason, $extraLogInformation = '', $extraLogTableInformation = '')
	{
		$ret = $this->logBreaches($reason, $extraLogInformation, $extraLogTableInformation);

		$autoban = $this->cparams->getValue('tsrenable', 0);

		if ($autoban)
		{
			$this->autoBan($reason);
		}

		return $ret;
	}

	/**
	 * Blocks the request in progress and, optionally, logs the details of the
	 * blocked request for the admin to review later
	 *
	 * @param string $reason                   Block reason code
	 * @param string $message                  The message to be shown to the user
	 * @param string $extraLogInformation      Extra information to be written to the text log file
	 * @param string $extraLogTableInformation Extra information to be written to the extradata field of the log table (useful for JSON format)
	 *
	 * @throws Exception
	 */
	public function blockRequest($reason = 'other', $message = '', $extraLogInformation = '', $extraLogTableInformation = '')
	{
		// Rescue URL check
		AtsystemUtilRescueurl::processRescueURL($this);

		if (empty($message))
		{
			$customMessage = $this->cparams->getValue('custom403msg', '');

			if (!empty($customMessage))
			{
				$message = $customMessage;
			}
			else
			{
				$message = 'ADMINTOOLS_BLOCKED_MESSAGE';
			}
		}

		$r = $this->logBreaches($reason, $extraLogInformation, $extraLogTableInformation);

		if (!$r)
		{
			return;
		}

		$autoban = $this->cparams->getValue('tsrenable', 0);

		if ($autoban)
		{
			$this->autoBan($reason);
		}

		// Merge the default translation with the current translation
		$jlang = JFactory::getLanguage();
		// Front-end translation
		$jlang->load('plg_system_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('plg_system_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('plg_system_admintools', JPATH_ADMINISTRATOR, null, true);

		if ((JText::_('ADMINTOOLS_BLOCKED_MESSAGE') == 'ADMINTOOLS_BLOCKED_MESSAGE') && ($message == 'ADMINTOOLS_BLOCKED_MESSAGE'))
		{
			$message = "Access Denied";
		}
		else
		{
			$message = JText::_($message);
		}

		$message = AtsystemUtilRescueurl::processBlockMessage($message);

		// Show the 403 message
		if ($this->cparams->getValue('use403view', 0))
		{
			// Using a view
			if (!$this->container->platform->getSessionVar('block', false, 'com_admintools'))
			{

				// This is inside an if-block so that we don't end up in an infinite redirection loop
				$this->container->platform->setSessionVar('block', true, 'com_admintools');
				$this->container->platform->setSessionVar('message', $message, 'com_admintools');

				if (!$this->container->platform->isCli())
				{
					JFactory::getSession()->close();
				}

				$this->container->platform->redirect(JUri::base());
			}
		}
		else
		{
			// Using Joomla!'s error page
			JFactory::getApplication()->input->set('template', null);

			throw new Exception($message, 403);
		}
	}

	/**
	 * Logs security exceptions
	 *
	 * @param string $reason                   Block reason code
	 * @param string $extraLogInformation      Extra information to be written to the text log file
	 * @param string $extraLogTableInformation Extra information to be written to the extradata field of the log table (useful for JSON format)
	 *
	 * @return bool
	 */
	public function logBreaches($reason, $extraLogInformation = '', $extraLogTableInformation = '')
	{
		$ip = $this->getVisitorIPAddress();

		// No point continuing if I cannot get the visitor's IP address
		if ($ip === false)
		{
			return false;
		}

		// Make sure this IP is not in the "Do not block these IPs" list
		if ($this->isSafeIP())
		{
			return false;
		}

		// Make sure this IP is not in the administrator white list
		if ($this->isIPInAdminWhitelist())
		{
			return false;
		}

		// Make sure this IP doesn't resolve to a whitelisted domain
		if ($this->isWhitelistedDomain($ip))
		{
			return true;
		}

		// Is this a private network IP and IP workaround is off? If so let's raise the flag so we can notify the user
		$this->flagPrivateNetworkIPs();

		// Get the human readable blocking reason
		$txtReason = $this->getBlockingReasonHumanReadable($reason, $extraLogTableInformation);

		// Get the email tokens, also used for logging
		$tokens = $this->getEmailVariables($txtReason);

		// Log the security exception to file and the database, if necessary
		$this->logSecurityException($reason, $extraLogInformation, $extraLogTableInformation, $txtReason, $tokens);

		// Email the security exception, if necessary
		$this->emailSecurityException($reason, $tokens);

		return true;
	}

	/**
	 * Checks if an IP address should be automatically banned for raising too many security exceptions over a predefined
	 * time period.
	 *
	 * @param   string $reason The reason of the ban
	 *
	 * @return  void
	 */
	public function autoBan($reason = 'other')
	{
		// We need to be able to get our own IP, right?
		if (!function_exists('inet_pton'))
		{
			return;
		}

		// Get the IP
		$ip = AtsystemUtilFilter::getIp();

		// No point continuing if we can't get an address, right?
		if (empty($ip) || ($ip == '0.0.0.0'))
		{
			return;
		}

		// Check for repeat offenses
		$db = $this->container->db;
		$strikes = $this->cparams->getValue('tsrstrikes', 3);
		$numfreq = $this->cparams->getValue('tsrnumfreq', 1);
		$frequency = $this->cparams->getValue('tsrfrequency', 'hour');
		$mindatestamp = 0;

		switch ($frequency)
		{
			case 'second':
				break;

			case 'minute':
				$numfreq *= 60;
				break;

			case 'hour':
				$numfreq *= 3600;
				break;

			case 'day':
				$numfreq *= 86400;
				break;

			case 'ever':
				$mindatestamp = 946706400; // January 1st, 2000
				break;
		}

		JLoader::import('joomla.utilities.date');
		$jNow = new Date();

		if ($mindatestamp == 0)
		{
			$mindatestamp = $jNow->toUnix() - $numfreq;
		}

		$jMinDate = new Date($mindatestamp);
		$minDate = $jMinDate->toSql();

		$sql = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__admintools_log'))
			->where($db->qn('logdate') . ' >= ' . $db->q($minDate))
			->where($db->qn('ip') . ' = ' . $db->q($ip));
		$db->setQuery($sql);
		try
		{
			$numOffenses = $db->loadResult();
		}
		catch (Exception $e)
		{
			$numOffenses = 0;
		}

		if ($numOffenses < $strikes)
		{
			return;
		}

		// Block the IP
		$myIP = @inet_pton($ip);

		if ($myIP === false)
		{
			return;
		}

		$myIP = inet_ntop($myIP);

		$until     = $jNow->toUnix();
		$numfreq   = $this->cparams->getValue('tsrbannum', 1);
		$frequency = $this->cparams->getValue('tsrbanfrequency', 'hour');

		switch ($frequency)
		{
			case 'second':
				$until += $numfreq;
				break;

			case 'minute':
				$numfreq *= 60;
				$until += $numfreq;
				break;

			case 'hour':
				$numfreq *= 3600;
				$until += $numfreq;
				break;

			case 'day':
				$numfreq *= 86400;
				$until += $numfreq;
				break;

			case 'ever':
				$until = 2145938400; // January 1st, 2038 (mind you, UNIX epoch runs out on January 19, 2038!)
				break;
		}

		JLoader::import('joomla.utilities.date');

		$jMinDate = new Date($until);
		$minDate = $jMinDate->toSql();

		$record = (object)array(
			'ip'     => $myIP,
			'reason' => $reason,
			'until'  => $minDate
		);

		// If I'm here it means that we have to ban the user. Let's see if this is a simple autoban or
		// we have to issue a permaban as a result of several attacks
		if ($this->cparams->getValue('permaban', 0))
		{
			// Ok I have to check the number of autoban
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->qn('#__admintools_ipautobanhistory'))
				->where($db->qn('ip') . ' = ' . $db->q($myIP));

			try
			{
				$bans = $db->setQuery($query)->loadResult();
			}
			catch (Exception $e)
			{
				$bans = 0;
			}

			$limit = (int)$this->cparams->getValue('permabannum', 0);

			if ($limit && ($bans >= $limit))
			{
				$block = (object)array(
					'ip'          => $myIP,
					'description' => 'IP automatically blocked after being banned automatically ' . $bans . ' times'
				);

				try
				{
					$db->insertObject('#__admintools_ipblock', $block);
				}
				catch (Exception $e)
				{
					// This should never happen, however let's prevent a white page if anything goes wrong
				}
			}
		}

		try
		{
			$db->insertObject('#__admintools_ipautoban', $record);
		}
		catch (Exception $e)
		{
			// If the IP was already blocked and I have to block it again, I'll have to update the current record
			$db->updateObject('#__admintools_ipautoban', $record, 'ip');
		}

		// Send an optional email
		if ($this->cparams->getValue('emailafteripautoban', ''))
		{
			// Load the component's administrator translation files
			$jlang = JFactory::getLanguage();
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, null, true);

			// Get the site name
			$config = $this->container->platform->getConfig();

			$substitutions = $this->getEmailVariables(JText::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_IPAUTOBAN'), [
				'[UNTIL]'     => $minDate
			]);

			// Load the component's administrator translation files
			$jlang = JFactory::getLanguage();
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, null, true);

			// Let's get the most suitable email template
			$template = $this->getEmailTemplate('ipautoban', true);

			// Got no template, the user didn't published any email template, or the template doesn't want us to
			// send a notification email. Anyway, let's stop here.
			if (!$template)
			{
				return;
			}
			else
			{
				$subject = $template[0];
				$body    = $template[1];
			}

			foreach ($substitutions as $k => $v)
			{
				$subject = str_replace($k, $v, $subject);
				$body = str_replace($k, $v, $body);
			}

			// Send the email
			try
			{
				$mailer = JFactory::getMailer();

				$mailfrom = $config->get('mailfrom');
				$fromname = $config->get('fromname');

				$recipients = explode(',', $this->cparams->getValue('emailafteripautoban', ''));
				$recipients = array_map('trim', $recipients);

				foreach ($recipients as $recipient)
				{
					if (empty($recipient))
					{
						continue;
					}

					// This line is required because SpamAssassin is BROKEN
					$mailer->Priority = 3;

					$mailer->isHtml(true);
					$mailer->setSender(array($mailfrom, $fromname));

					// Resets the recipients, otherwise they will pile up
					$mailer->clearAllRecipients();

					if ($mailer->addRecipient($recipient) === false)
					{
						// Failed to add a recipient?
						continue;
					}

					$mailer->setSubject($subject);
					$mailer->setBody(AtsystemUtilRescueurl::processBlockMessage($body, $recipient));
					$mailer->Send();
				}
			}
			catch (\Exception $e)
			{
				// Joomla! 3.5 and later throw an exception when crap happens instead of suppressing it and returning false
			}
		}
	}

	/**
	 * Gets the email template for a specific security exception reason
	 *
	 * @param   string  $reason  The security exception reason for which to fetch the email template
	 * @param   bool    $exact   Require an exact match of the reason
	 *
	 * @return  array
	 */
	public function getEmailTemplate($reason, $exact = false)
	{
		// Let's get the subject and the body from email templates
		$jlang = JFactory::getLanguage();
		$db = $this->container->db;
		$languages = array($db->q('*'), $db->q('en-GB'), $db->q($jlang->getDefault()));
		$stack = array();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__admintools_waftemplates'))
			->where($db->qn('reason') . ' IN(' . $db->q($reason) . ', ' . $db->q('all') . ')')
			->where($db->qn('language') . ' IN(' . implode(',', $languages) . ')')
			->where($db->qn('enabled') . ' = ' . $db->q('1'));

		if ($exact)
		{
			$query->where($db->qn('reason') . ' = ' . $db->q($reason));
		}

		try
		{
			$templates = $db->setQuery($query)->loadObjectList();
		}
		catch (Exception $e)
		{
			return array();
		}

		foreach ($templates as $template)
		{
			$score = 0;

			if ($template->reason == $reason)
			{
				$score += 10;
			}

			if ($template->language == $jlang->getDefault())
			{
				$score += 10;
			}
			elseif ($template->language == '*')
			{
				$score += 5;
			}
			elseif ($template->language == 'en-GB')
			{
				$score += 1;
			}

			$stack[$score] = $template;
		}

		ksort($stack);
		$best = array_pop($stack);

		if (!$best)
		{
			return array();
		}

		if ($this->cparams->getValue('email_throttle', 1))
		{
			// Ok I found out the best template, HOWEVER, should I really send out an email? Let's do some checks vs frequency limits
			$emails       = $best->email_num ? $best->email_num : 5;
			$numfreq      = $best->email_numfreq ? $best->email_numfreq : 1;
			$frequency    = $best->email_freq ? $best->email_freq : 'hour';
			$mindatestamp = 0;

			switch ($frequency)
			{
				case 'second':
					break;

				case 'minute':
					$numfreq *= 60;
					break;

				case 'hour':
					$numfreq *= 3600;
					break;

				case 'day':
					$numfreq *= 86400;
					break;

				case 'ever':
					$mindatestamp = 946706400; // January 1st, 2000
					break;
			}

			JLoader::import('joomla.utilities.date');
			$jNow = new Date();

			if ($mindatestamp == 0)
			{
				$mindatestamp = $jNow->toUnix() - $numfreq;
			}

			$jMinDate = new Date($mindatestamp);
			$minDate = $jMinDate->toSql();

			$sql = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->qn('#__admintools_log'))
				->where($db->qn('logdate') . ' >= ' . $db->q($minDate))
				->where($db->qn('reason') . ' = ' . $db->q($reason));
			$db->setQuery($sql);
			try
			{
				$numOffenses = $db->loadResult();
			}
			catch (Exception $e)
			{
				$numOffenses = 0;
			}

			if ($numOffenses > $emails)
			{
				return array();
			}
		}

		// Because SpamAssassin blacklists our domain when it misidentifies an email as spam.
		$replaceThat = array(
			'<p style=\"text-align: right; font-size: 7pt; color: #ccc;\">Powered by <a style=\"color: #ccf; text-decoration: none;\" href=\"https://www.akeebabackup.com/products/admin-tools.html\">Akeeba AdminTools</a></p>',
			'<p style=\"text-align: right; font-size: 7pt; color: #ccc;\">Powered by <a style=\"color: #ccf; text-decoration: none;\" href=\"https://www.akeebabackup.com/products/admin-tools.html\">Akeeba AdminTools</a></p>',
			'https://www.akeebabackup.com',
			'http://www.akeebabackup.com',
			'http://akeebabackup.com',
			'https://akeebabackup.com',
			'www.akeebabackup.com',
			'akeebabackup.com',
		);

		foreach ($replaceThat as $find)
		{
			$best->subject  = str_ireplace($find, '', $best->subject);
			$best->template = str_ireplace($find, '', $best->template);
		}

		// Because SpamAssassin demands there is a body and surrounding html tag even though it's not necessary.
		if (strpos($best->template, '<body') == false)
		{
			$best->template = '<body>' . $best->template . '</body>';
		}

		if (strpos($best->template, '<html') == false)
		{
			$best->template = <<< HTML
<html>
<head>
<title>{$best->subject}</title>
</head>
$best->template
</html>
HTML;

		}

		// Inject self-unblocking information to the default emails for security exceptions and IP autoban
		if ($best->reason == 'all')
		{
			$best->template = str_replace('Reason: [REASON]</p>', 'Reason: [REASON]</p><p>[RESCUEINFO]</p>', $best->template);
		}
		elseif ($best->reason == 'ipautoban')
		{
			$best->template = str_replace('Banned until: [UNTIL]</p>', 'Banned until: [UNTIL]</p><p>[RESCUEINFO]</p>', $best->template);
		}

		// And now return the template
		return array(
			$best->subject,
			$best->template
		);
	}

	/**
	 * Flag security exceptions coming from private network IPs so we can notify the user
	 *
	 * @return  void
	 *
	 * @since   4.1.1
	 */
	private function flagPrivateNetworkIPs()
	{
		// Make sure FOF 3 can be loaded, or fail gracefuly
		if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
		{
			return;
		}

		// I'll use the Container so I can easily set the flag and then save back to the database
		$params = $this->container->params;

		// Run the check only if IP workarounds are off AND the flag is set to 0 (ie not detected)
		// There's no need to run this check if the user decided to ignore the warning (value: -1) or we already detected something (value: 1)
		if (($this->cparams->getValue('ipworkarounds', -1) == -1) || ($params->get('detected_exceptions_from_private_network', 0) != 0))
		{
			return;
		}

		$privateNetwork = array(
			'10.0.0.0-10.255.255.255',
			'172.16.0.0-172.31.255.255',
			'192.168.0.0-192.168.255.255'
		);

		if (!AtsystemUtilFilter::IPinList($privateNetwork))
		{
			return;
		}

		// This IP belongs to a private network, let's raise the flag and then notify the user
		$params->set('detected_exceptions_from_private_network', 1);

		try
		{
			$params->save();
		}
		catch (Exception $e)
		{
			// Ignore any failures, they are not show stoppers
		}
	}

	public function getComponentParam($key, $default = null)
	{
		return $this->cparams->getValue($key, $default);
	}

	/**
	 * Get the variables we can use in emails as an associative list (variable => value).
	 *
	 * @param   string  $reason           The value for the [REASON] variable
	 * @param   array   $customVariables  An array of custom variables to add to the return.
	 *
	 * @return  array
	 */
	public function getEmailVariables($reason, $customVariables = array())
	{
		// Get our IP address
		$ip = AtsystemUtilFilter::getIp();

		if ((strpos($ip, '::') === 0) && (strstr($ip, '.') !== false))
		{
			$ip = substr($ip, strrpos($ip, ':') + 1);
		}

		// Get the site name
		$config = $this->container->platform->getConfig();

		$siteName = $config->get('sitename');

		// Create a link to lookup the IP
		$ipLookupURL = $this->cparams->getValue('iplookupscheme', 'http') . '://' . $this->cparams->getValue('iplookup', 'ip-lookup.net/index.php?ip={ip}');
		$ipLookupURL = str_replace('{ip}', $ip, $ipLookupURL);

		$uri = JUri::getInstance();
		$url = $uri->toString(['scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment']);

		$user = $this->container->platform->getUser();

		if ($user->guest)
		{
			$username = 'Guest';
		}
		else
		{
			$username = $user->username . ' (' . $user->name . ' <' . $user->email . '>)';
		}

		$country   = '';
		$continent = '';

		if (class_exists('AkeebaGeoipProvider'))
		{
			$geoip     = new AkeebaGeoipProvider();
			$country   = $geoip->getCountryCode($ip);
			$continent = $geoip->getContinent($ip);
		}

		if (empty($country))
		{
			$country = '(unknown country)';
		}

		if (empty($continent))
		{
			$continent = '(unknown continent)';
		}

		$tzWrangler = new TimezoneWrangler($this->container);
		$email_timezone = $this->container->params->get('email_timezone', 'AKEEBA/DEFAULT');

		if (!empty($email_timezone) && ($email_timezone != 'AKEEBA/DEFAULT'))
		{
			try
			{
				$tzWrangler->setForcedTimezone($email_timezone);
			}
			catch (Exception $e)
			{
				// Just in case someone puts an invalid timezone in there (you can never be too paranoid).
			}
		}

		$noUser     = new JUser();

		$ret = array(
			'[SITENAME]'  => $siteName,
			'[REASON]'    => $reason,
			'[DATE]'      => $tzWrangler->getLocalTimeStamp('Y-m-d H:i:s T', $noUser),
			'[URL]'       => $url,
			'[USER]'      => $username,
			'[IP]'        => $ip,
			'[LOOKUP]'    => '<a href="' . $ipLookupURL . '">IP Lookup</a>',
			'[COUNTRY]'   => $country,
			'[CONTINENT]' => $continent,
			'[UA]'        => $_SERVER['HTTP_USER_AGENT'],
		);

		if (is_array($customVariables) && !empty($customVariables))
		{
			$ret = array_merge($ret, $customVariables);
		}

		return $ret;
	}

	/**
	 * Get the visitor IP address. Return false if we cannot get an IP address or if we get 0.0.0.0 (broken IP forwarding).
	 *
	 * @return  bool|string
	 */
	private function getVisitorIPAddress()
	{
		// Get our IP address
		$ip = AtsystemUtilFilter::getIp();

		if ((strpos($ip, '::') === 0) && (strstr($ip, '.') !== false))
		{
			$ip = substr($ip, strrpos($ip, ':') + 1);
		}

		// No point continuing if we can't get an address, right?
		if (empty($ip) || ($ip == '0.0.0.0'))
		{
			return false;
		}

		return $ip;
	}

	/**
	 * Is the IP address in the "Never block these IPs" (safe IPs) list?
	 *
	 * @return  bool
	 */
	private function isSafeIP()
	{
		$safeIPs = $this->cparams->getValue('neverblockips', '');

		if (!empty($safeIPs))
		{
			$safeIPs = explode(',', $safeIPs);

			if (!empty($safeIPs))
			{
				if (AtsystemUtilFilter::IPinList($safeIPs))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Is the IP address in the Administrator IP Whitelist?
	 *
	 * @return  bool
	 */
	private function isIPInAdminWhitelist()
	{
		if ($this->cparams->getValue('ipwl', 0) == 1)
		{
			$db  = $this->container->db;
			$sql = $db->getQuery(true)
			          ->select($db->qn('ip'))
			          ->from($db->qn('#__admintools_adminiplist'))
			;

			$db->setQuery($sql);

			try
			{
				$ipTable = $db->loadColumn();
			}
			catch (Exception $e)
			{
				$ipTable = null;
			}

			if (!empty($ipTable))
			{
				if (AtsystemUtilFilter::IPinList($ipTable))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Does the IP address resolve to one of the whitelisted domain names?
	 *
	 * @param   string  $ip
	 *
	 * @return  bool
	 */
	private function isWhitelistedDomain($ip)
	{
		static $whitelist_domains = null;

		if (is_null($whitelist_domains))
		{
			$whitelist_domains = $this->cparams->getValue('whitelist_domains', '.googlebot.com,.search.msn.com');
			$whitelist_domains = explode(',', $whitelist_domains);
		}

		if (!empty($whitelist_domains))
		{
			$remote_domain = @gethostbyaddr($ip);

			if (!empty($remote_domain))
			{
				foreach ($whitelist_domains as $domain)
				{
					$domain = trim($domain);

					if (strrpos($remote_domain, $domain) !== false)
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get the blocking reason in a human readable format
	 *
	 * @param   string  $reason
	 * @param   string  $extraLogTableInformation
	 *
	 * @return  string
	 */
	private function getBlockingReasonHumanReadable($reason, $extraLogTableInformation)
	{
		// Load the component's administrator translation files
		$jlang = JFactory::getLanguage();
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, null, true);

		// Get the reason in human readable format
		$txtReason = JText::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_' . strtoupper($reason));

		if (empty($extraLogTableInformation))
		{
			return $txtReason;
		}

		// Get extra information
		list($logReason,) = explode('|', $extraLogTableInformation);

		return $txtReason . " ($logReason)";
	}

	/**
	 * Write a security exception to the log, as long as logging is enabled and the $reason is not one of the $reasons_nolog ones
	 *
	 * @param   string  $reason
	 * @param   string  $extraLogInformation
	 * @param   string  $extraLogTableInformation
	 * @param   string  $txtReason
	 * @param   array   $tokens
	 *
	 * @return  void
	 */
	private function logSecurityException($reason, $extraLogInformation, $extraLogTableInformation, $txtReason, $tokens)
	{
		$reasons_nolog = $this->cparams->getValue('reasons_nolog', 'geoblocking');
		$reasons_nolog = explode(',', $reasons_nolog);

		if (!$this->cparams->getValue('logbreaches', 0) || in_array($reason, $reasons_nolog))
		{
			return;
		}

		// Log to file
		$this->logSecurityExceptionToFile($reason, $extraLogInformation, $txtReason, $tokens);

		// Log to the database table
		$this->logSecurityExceptionToDatabase($reason, $extraLogTableInformation, $tokens);
	}

	/**
	 * Log a security exception to our log file
	 *
	 * @param   string  $reason
	 * @param   string  $extraLogInformation
	 * @param   string  $txtReason
	 * @param   array   $tokens
	 */
	private function logSecurityExceptionToFile($reason, $extraLogInformation, $txtReason, $tokens)
	{
		// Get the log filename
		$config = $this->container->platform->getConfig();
		$logpath = $config->get('log_path');
		$fname = $logpath . DIRECTORY_SEPARATOR . 'admintools_breaches.log';

		// -- Check the file size. If it's over 1Mb, archive and start a new log.
		if (@file_exists($fname))
		{
			$fsize = filesize($fname);

			if ($fsize > 1048756)
			{
				if (@file_exists($fname . '.1'))
				{
					unlink($fname . '.1');
				}

				@copy($fname, $fname . '.1');
				@unlink($fname);
			}
		}

		// -- Log the exception
		$fp = @fopen($fname, 'at');

		if ($fp === false)
		{
			return;
		}

		fwrite($fp, str_repeat('-', 79) . "\n");
		fwrite($fp, "Blocking reason: " . $reason . "\n" . str_repeat('-', 79) . "\n");
		fwrite($fp, "Reason     : " . $txtReason . "\n");
		fwrite($fp, 'Timestamp  : ' . gmdate('Y-m-d H:i:s') . " GMT\n");
		fwrite($fp, 'Local time : ' . $tokens['[DATE]'] . " \n");
		fwrite($fp, 'URL        : ' . $tokens['[URL]'] . "\n");
		fwrite($fp, 'User       : ' . $tokens['[USER]'] . "\n");
		fwrite($fp, 'IP         : ' . $tokens['[IP]'] . "\n");
		fwrite($fp, 'Country    : ' . $tokens['[COUNTRY]'] . "\n");
		fwrite($fp, 'Continent  : ' . $tokens['[CONTINENT]'] . "\n");
		fwrite($fp, 'UA         : ' . $tokens['[UA]'] . "\n");

		if (!empty($extraLogInformation))
		{
			fwrite($fp, $extraLogInformation . "\n");
		}

		fwrite($fp, "\n\n");
		fclose($fp);
	}

	/**
	 * Log a security exception to the database table
	 *
	 * @param   string  $reason
	 * @param   string  $extraLogInformation
	 * @param   array   $tokens
	 *
	 *
	 * @since version
	 */
	private function logSecurityExceptionToDatabase($reason, $extraLogTableInformation, $tokens)
	{
		try
		{
			$date     = new Date();
			$db       = $this->container->db;
			$url      = $tokens['[URL]'];

			if (strlen($url) > 10240)
			{
				$url = substr($url, 0, 10240);
			}

			$logEntry = (object) array(
				'logdate'   => $date->toSql(),
				'ip'        => $tokens['[IP]'],
				'url'       => $url,
				'reason'    => $reason,
				'extradata' => $extraLogTableInformation,
			);

			$db->insertObject('#__admintools_log', $logEntry);
		}
		catch (Exception $e)
		{
			// Do nothing if the query fails
		}
	}

	/**
	 * Sends information about the security exception by email
	 *
	 * @param   string  $reason
	 * @param   array   $tokens
	 *
	 * @return  bool
	 */
	private function emailSecurityException($reason, $tokens)
	{
		$emailbreaches   = $this->cparams->getValue('emailbreaches', '');
		$reasons_noemail = $this->cparams->getValue('reasons_noemail', 'geoblocking');
		$reasons_noemail = explode(',', $reasons_noemail);

		if (empty($emailbreaches) || in_array($reason, $reasons_noemail))
		{
			return true;
		}

		// Get the site name
		$config = $this->container->platform->getConfig();

		// Send the email
		try
		{
			$mailer = JFactory::getMailer();

			$mailfrom = $config->get('mailfrom');
			$fromname = $config->get('fromname');

			// Let's get the most suitable email template
			$template = $this->getEmailTemplate($reason);

			// Got no template, the user didn't published any email template, or the template doesn't want us to
			// send a notification email. Anyway, let's stop here
			if (!$template)
			{
				return true;
			}
			else
			{
				$subject = $template[0];
				$body    = $template[1];
			}

			$subject = str_replace(array_keys($tokens), array_values($tokens), $subject);
			$body    = str_replace(array_keys($tokens), array_values($tokens), $body);

			$recipients = explode(',', $emailbreaches);
			$recipients = array_map('trim', $recipients);

			foreach ($recipients as $recipient)
			{
				if (empty($recipient))
				{
					continue;
				}

				// This line is required because SpamAssassin is BROKEN
				$mailer->Priority = 3;

				$mailer->isHtml(true);
				$mailer->setSender(array($mailfrom, $fromname));

				// Resets the recipients, otherwise they will pile up
				$mailer->clearAllRecipients();

				if ($mailer->addRecipient($recipient) === false)
				{
					// Failed to add a recipient?
					continue;
				}

				$mailer->setSubject($subject);
				$mailer->setBody(AtsystemUtilRescueurl::processBlockMessage($body, $recipient));
				$mailer->Send();
			}
		}
		catch (\Exception $e)
		{
		}

		return true;
	}
}

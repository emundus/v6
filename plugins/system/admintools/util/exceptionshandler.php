<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

class AtsystemUtilExceptionshandler
{
	/** @var   JRegistry  Plugin parameters */
	protected $params = null;

	/** @var   AdmintoolsModelStorage  Component parameters */
	protected $cparams = null;

	public function __construct(JRegistry &$params, AdmintoolsModelStorage &$cparams)
	{
		$this->params = $params;
		$this->cparams = $cparams;
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

		// Show the 403 message
		if ($this->cparams->getValue('use403view', 0))
		{
			// Using a view
			if (!JFactory::getSession()->get('block', false, 'com_admintools'))
			{
				// This is inside an if-block so that we don't end up in an infinite rediretion loop
				JFactory::getSession()->set('block', true, 'com_admintools');
				JFactory::getSession()->set('message', $message, 'com_admintools');
				JFactory::getSession()->close();
				JFactory::getApplication()->redirect(JURI::base());
			}
		}
		else
		{
			// Using Joomla!'s error page
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				JFactory::getApplication()->input->set('template', null);
				throw new Exception($message, 403);
			}
			else
			{
				JRequest::setVar('template', null);
				JError::raiseError(403, $message);
			}
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
		$reasons_nolog = $this->cparams->getValue('reasons_nolog', 'geoblocking');
		$reasons_noemail = $this->cparams->getValue('reasons_noemail', 'geoblocking');
		$whitelist_domains = $this->cparams->getValue('whitelist_domains', '.googlebot.com,.search.msn.com');

		$reasons_nolog = explode(',', $reasons_nolog);
		$reasons_noemail = explode(',', $reasons_noemail);
		$whitelist_domains = explode(',', $whitelist_domains);

		// === SANITY CHECK - BEGIN ===
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

		// Make sure it's not an IP in the safe list
		$safeIPs = $this->cparams->getValue('neverblockips', '');

		if (!empty($safeIPs))
		{
			$safeIPs = explode(',', $safeIPs);

			if (!empty($safeIPs))
			{
				if (AtsystemUtilFilter::IPinList($safeIPs))
				{
					return false;
				}
			}
		}

		// Make sure we don't have a list in the administrator white list
		if ($this->cparams->getValue('ipwl', 0) == 1)
		{
			$db = JFactory::getDBO();
			$sql = $db->getQuery(true)
				->select($db->qn('ip'))
				->from($db->qn('#__admintools_adminiplist'));
			$db->setQuery($sql);

			try
			{
				if (version_compare(JVERSION, '3.0', 'ge'))
				{
					$ipTable = $db->loadColumn();
				}
				else
				{
					$ipTable = $db->loadResultArray();
				}
			}
			catch (Exception $e)
			{
				$ipTable = null;
			}

			if (!empty($ipTable))
			{
				if (AtsystemUtilFilter::IPinList($ipTable))
				{
					return false;
				}
			}
		}

		// Make sure this IP doesn't resolve to a whitelisted domain
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

		// === SANITY CHECK - END ===


		// DO I have any kind of log? Let's get some extra info
		if (
			($this->cparams->getValue('logbreaches', 0) && !in_array($reason, $reasons_nolog)) ||
			($this->cparams->getValue('emailbreaches', '') && !in_array($reason, $reasons_noemail))
		)
		{
			$uri = JURI::getInstance();
			$url = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'));

			JLoader::import('joomla.utilities.date');
			$date = new JDate();

			$user = JFactory::getUser();

			if ($user->guest)
			{
				$username = 'Guest';
			}
			else
			{
				$username = $user->username . ' (' . $user->name . ' <' . $user->email . '>)';
			}

			$country = '';
			$continent = '';

			if (class_exists('AkeebaGeoipProvider'))
			{
				$geoip = new AkeebaGeoipProvider();
				$country = $geoip->getCountryCode($ip);
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
		}

		if ($this->cparams->getValue('logbreaches', 0) && !in_array($reason, $reasons_nolog))
		{
			// Logging to file
			$config = JFactory::getConfig();

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$logpath = $config->get('log_path');
			}
			else
			{
				$logpath = $config->getValue('log_path');
			}

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

			if ($fp !== false)
			{
				fwrite($fp, str_repeat('-', 79) . "\n");
				fwrite($fp, "Blocking reason: " . $reason . "\n" . str_repeat('-', 79) . "\n");
				fwrite($fp, 'Date/time : ' . gmdate('Y-m-d H:i:s') . " GMT\n");
				fwrite($fp, 'URL       : ' . $url . "\n");
				fwrite($fp, 'User      : ' . $username . "\n");
				fwrite($fp, 'IP        : ' . $ip . "\n");
				fwrite($fp, 'Country   : ' . $country . "\n");
				fwrite($fp, 'Continent : ' . $continent . "\n");
				fwrite($fp, 'UA        : ' . $_SERVER['HTTP_USER_AGENT'] . "\n");

				if (!empty($extraLogInformation))
				{
					fwrite($fp, $extraLogInformation . "\n");
				}

				fwrite($fp, "\n\n");
				fclose($fp);
			}

			// ...and write a record to the log table
			$db = JFactory::getDBO();
			$logEntry = (object)array(
				'logdate'   => $date->toSql(),
				'ip'        => $ip,
				'url'       => $url,
				'reason'    => $reason,
				'extradata' => $extraLogTableInformation,
			);

			try
			{
				$db->insertObject('#__admintools_log', $logEntry);
			}
			catch (Exception $e)
			{
				// Do nothing if the query fails
			}
		}

		$emailbreaches = $this->cparams->getValue('emailbreaches', '');

		if (!empty($emailbreaches) && !in_array($reason, $reasons_noemail))
		{
			// Load the component's administrator translation files
			$jlang = JFactory::getLanguage();
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, null, true);

			// Get the site name
			$config = JFactory::getConfig();

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$sitename = $config->get('sitename');
			}
			else
			{
				$sitename = $config->getValue('config.sitename');
			}

			// Create a link to lookup the IP
			$ip_link = $this->cparams->getValue('iplookupscheme', 'http') . '://' . $this->cparams->getValue('iplookup', 'ip-lookup.net/index.php?ip={ip}');
			$ip_link = str_replace('{ip}', $ip, $ip_link);

			// Get the reason in human readable format
			$txtReason = JText::_('ATOOLS_LBL_REASON_' . strtoupper($reason));

			// Get extra information
			if ($extraLogTableInformation)
			{
				list($logReason,) = explode('|', $extraLogTableInformation);
				$txtReason .= " ($logReason)";
			}

			// Send the email
			$mailer = JFactory::getMailer();

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$mailfrom = $config->get('mailfrom');
				$fromname = $config->get('fromname');
			}
			else
			{
				$mailfrom = $config->getValue('config.mailfrom');
				$fromname = $config->getValue('config.fromname');
			}

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
				$body = $template[1];
			}

			$tokens = array(
				'[SITENAME]'  => $sitename,
				'[REASON]'    => $txtReason,
				'[DATE]'      => gmdate('Y-m-d H:i:s') . " GMT",
				'[URL]'       => $url,
				'[USER]'      => $username,
				'[IP]'        => $ip,
				'[LOOKUP]'    => '<a href="' . $ip_link . '">IP Lookup</a>',
				'[COUNTRY]'   => $country,
				'[CONTINENT]' => $continent,
				'[UA]'        => $_SERVER['HTTP_USER_AGENT']
			);

			$subject = str_replace(array_keys($tokens), array_values($tokens), $subject);
			$body = str_replace(array_keys($tokens), array_values($tokens), $body);

			$recipients = explode(',', $emailbreaches);
			$recipients = array_map('trim', $recipients);

			foreach ($recipients as $recipient)
			{
				$mailer->isHtml(true);
				$mailer->setSender(array($mailfrom, $fromname));
				$mailer->addRecipient($recipient);
				$mailer->setSubject($subject);
				$mailer->setBody($body);
				$mailer->Send();
			}
		}

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
		$db = JFactory::getDBO();
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
		$jNow = new JDate();

		if ($mindatestamp == 0)
		{
			$mindatestamp = $jNow->toUnix() - $numfreq;
		}

		$jMinDate = new JDate($mindatestamp);
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

		$until = $jNow->toUnix();
		$numfreq = $this->cparams->getValue('tsrbannum', 1);
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

		$jMinDate = new JDate($until);
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

				$db->insertObject('#__admintools_ipblock', $block);
			}
		}

		$db->insertObject('#__admintools_ipautoban', $record);

		// Send an optional email
		if ($this->cparams->getValue('emailafteripautoban', ''))
		{
			// Get the site name
			$config = JFactory::getConfig();

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$sitename = $config->get('sitename');
			}
			else
			{
				$sitename = $config->getValue('config.sitename');
			}

			$substitutions = array(
				'[SITENAME]' => $sitename,
				'[IP]'       => $myIP,
				'[UNTIL]'    => $minDate
			);

			// Load the component's administrator translation files
			$jlang = JFactory::getLanguage();
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, null, true);

			$subject = JText::_('ATOOLS_LBL_WAF_AUTOIPBLOCKEMAIL_SUBJECT');
			$body = JText::_('ATOOLS_LBL_WAF_AUTOIPBLOCKEMAIL_BODY');

			foreach ($substitutions as $k => $v)
			{
				$subject = str_replace($k, $v, $subject);
				$body = str_replace($k, $v, $body);
			}

			// Send the email
			$mailer = JFactory::getMailer();

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$mailfrom = $config->get('mailfrom');
				$fromname = $config->get('fromname');
			}
			else
			{
				$mailfrom = $config->getValue('config.mailfrom');
				$fromname = $config->getValue('config.fromname');
			}

			$mailer->setSender(array($mailfrom, $fromname));
			$mailer->addRecipient($this->cparams->getValue('emailafteripautoban', ''));
			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$mailer->Send();
		}
	}

	/**
	 * Gets the email template for a specific security exception reason
	 *
	 * @param   string $reason The security exception reason for which to fetch the email template
	 *
	 * @return  array
	 */
	public function getEmailTemplate($reason)
	{
		// Let's get the subject and the body from email templates
		$jlang = JFactory::getLanguage();
		$db = JFactory::getDbo();
		$languages = array($db->q('*'), $db->q('en-GB'), $db->q($jlang->getDefault()));
		$stack = array();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__admintools_waftemplates'))
			->where($db->qn('reason') . ' IN(' . $db->q($reason) . ', ' . $db->q('all') . ')')
			->where($db->qn('language') . ' IN(' . implode(',', $languages) . ')')
			->where($db->qn('enabled') . ' = ' . $db->q('1'));

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
			$emails = $best->email_num ? $best->email_num : 5;
			$numfreq = $best->email_numfreq ? $best->email_numfreq : 1;
			$frequency = $best->email_freq ? $best->email_freq : 'hour';
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
			$jNow = new JDate();

			if ($mindatestamp == 0)
			{
				$mindatestamp = $jNow->toUnix() - $numfreq;
			}

			$jMinDate = new JDate($mindatestamp);
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

		return array(
			$best->subject,
			$best->template
		);
	}
} 
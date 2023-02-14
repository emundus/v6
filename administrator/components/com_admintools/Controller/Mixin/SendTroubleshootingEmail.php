<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Controller\Mixin;

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Model\ConfigureWAF;
use Exception;
use FOF40\Container\Container;
use FOF40\Factory\Exception\ModelNotFound;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

trait SendTroubleshootingEmail
{
	/**
	 * Sends a preemptive troubleshooting email to the user before taking an action which might lock them out.
	 *
	 * @param   string  $controllerName
	 *
	 * @return  void
	 */
	protected function sendTroubelshootingEmail($controllerName)
	{
		// Is sending this email blocked in the WAF configuration?
		/** @var Container $container */
		$container = $this->container;
		try
		{
			/** @var ConfigureWAF $configModel */
			$configModel = $container->factory->model('ConfigureWAF')->tmpInstance();
		}
		catch (ModelNotFound $e)
		{
			// The Core version does not have the ConfigureWAF model and must therefore not send such emails.
			return;
		}

		$wafConfig = $configModel->getConfig();
		$sendEmail = $wafConfig['troubleshooteremail'] ?? 1;

		if (!$sendEmail)
		{
			return;
		}

		// Construct the email
		$user      = $container->platform->getUser();
		$config    = $container->platform->getConfig();
		$siteName  = $config->get('sitename');
		$actionKey = 'COM_ADMINTOOLS_TROUBLESHOOTEREMAIL_ACTION_' . $controllerName;
		$action    = Text::_($actionKey);
		$subject   = Text::_('COM_ADMINTOOLS_TROUBLESHOOTEREMAIL_SUBJECT');
		$body      = Text::sprintf('COM_ADMINTOOLS_TROUBLESHOOTEREMAIL_BODY_HELLO', $user->name) . "\n\n" .
			Text::sprintf('COM_ADMINTOOLS_TROUBLESHOOTEREMAIL_BODY_DESCRIPTION', $action, $siteName) . "\n\n" .
			"-  http://akee.ba/lockedout\n" .
			"-  http://akee.ba/500htaccess\n" .
			"-  http://akee.ba/adminpassword\n" .
			"-  http://akee.ba/403edituser\n\n" .
			Text::_('COM_ADMINTOOLS_TROUBLESHOOTEREMAIL_BODY_SUPPORT') . "\n\n" .
			Text::_('COM_ADMINTOOLS_TROUBLESHOOTEREMAIL_BODY_WHOSENTTHIS') . "\n" .
			str_repeat('=', 40) . "\n\n" .
			Text::_('COM_ADMINTOOLS_TROUBLESHOOTEREMAIL_BODY_WHOSENT_1') . "\n\n" .
			Text::_('COM_ADMINTOOLS_TROUBLESHOOTEREMAIL_BODY_WHOSENT_2') . "\n";
		$body      = wordwrap($body);

		// Can't send email if I don't about this controller
		if ($action == $actionKey)
		{
			return;
		}

		// Is the Super User set up to not receive system emails?
		if (!$user->sendEmail)
		{
			return;
		}

		// Send the email
		try
		{
			$mailer    = Factory::getMailer();
			$mailfrom  = $config->get('mailfrom');
			$fromname  = $config->get('fromname');
			$recipient = trim($user->email);

			// The priority is required because SpamAssassin rejects email without a priority (WTF, right?).
			$mailer->Priority = 3;
			$mailer->isHtml(false);
			$mailer->setSender([$mailfrom, $fromname]);
			$mailer->clearAllRecipients();

			if ($mailer->addRecipient($recipient) === false)
			{
				// Failed to add a recipient?
				return;
			}

			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$mailer->Send();
		}
		catch (Exception $e)
		{
			// Joomla! 3.5 and later throw an exception when crap happens instead of suppressing it and returning false
		}
	}
}

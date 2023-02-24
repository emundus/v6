<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Helper\ServerTechnology;
use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Admin\Model\Scanner\Complexify;
use Exception;
use FOF40\Database\Installer;
use FOF40\Encrypt\Randval;
use FOF40\Model\Model;
use FOF40\JoomlaAbstraction\CacheCleaner;
use FOF40\IP\IPHelper as Ip;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use RuntimeException;

class ControlPanel extends Model
{
	/**
	 * The extension ID of the System - Admin Tools plugin
	 *
	 * @var  int
	 */
	static $pluginId = null;

	/**
	 * Get the extension ID of the System - Admin Tools plugin
	 *
	 * @return  int
	 */
	public function getPluginID(): ?int
	{
		if (empty(static::$pluginId))
		{
			$db = $this->container->db;

			$query = $db->getQuery(true)
				->select($db->qn('extension_id'))
				->from($db->qn('#__extensions'))
				->where($db->qn('enabled') . ' >= ' . $db->q('1'))
				->where($db->qn('folder') . ' = ' . $db->q('system'))
				->where($db->qn('element') . ' = ' . $db->q('admintools'))
				->where($db->qn('type') . ' = ' . $db->q('plugin'))
				->order($db->qn('ordering') . ' ASC');

			static::$pluginId = $db->setQuery($query)->loadResult();
		}

		return static::$pluginId;
	}

	/**
	 * Makes sure our system plugin is really the very first system plugin to execute
	 *
	 * @return  void
	 */
	public function reorderPlugin()
	{
		// Get our plugin's ID
		$id = $this->getPluginID();

		// The plugin is not enabled, there's no point in continuing
		if (!$id)
		{
			return;
		}

		// Get a list of ordering values per ID
		$db = $this->container->db;

		$query         = $db->getQuery(true)
			->select([
				$db->qn('extension_id'),
				$db->qn('ordering'),
			])
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('plugin'))
			->where($db->qn('folder') . ' = ' . $db->q('system'))
			->order($db->qn('ordering') . ' ASC');
		$orderingPerId = $db->setQuery($query)->loadAssocList('extension_id', 'ordering');

		$orderings   = array_values($orderingPerId);
		$orderings   = array_unique($orderings);
		$minOrdering = reset($orderings);

		$myOrdering = $orderingPerId[$id];

		reset($orderings);
		$sharedOrderings = 0;

		foreach ($orderingPerId as $fooid => $order)
		{
			if ($order > $myOrdering)
			{
				break;
			}

			if ($order == $myOrdering)
			{
				$sharedOrderings++;
			}
		}

		// Do I need to reorder the plugin?
		if (($myOrdering > $minOrdering) || ($sharedOrderings > 1))
		{
			$query = $db->getQuery(true)
				->update($db->qn('#__extensions'))
				->set($db->qn('ordering') . ' = ' . $db->q($minOrdering - 1))
				->where($db->qn('extension_id') . ' = ' . $db->q($id));
			$db->setQuery($query);
			$db->execute();

			// Reset the Joomla! plugins cache
			CacheCleaner::clearPluginsCache();
		}
	}

	/**
	 * Does the user need to enter a Download ID in the component's Options page?
	 *
	 * @return  bool
	 */
	public function needsDownloadID(): bool
	{
		// Do I need a Download ID?
		if (!ADMINTOOLS_PRO)
		{
			return false;
		}

		/** @var Updates $updateModel */
		$updateModel = $this->container->factory->model('Updates')->tmpInstance();

		// Migrate J3 to J4 settings
		$updateModel->upgradeLicenseKey();

		// Save the J4 license key in the component options, if necessary
		$updateModel->backportLicenseKey();

		$dlid = $updateModel->sanitizeLicenseKey($updateModel->getLicenseKey());

		return !$updateModel->isValidLicenseKey($dlid);
	}

	/**
	 * Checks the database for missing / outdated tables using the $dbChecks
	 * data and runs the appropriate SQL scripts if necessary.
	 *
	 * @return  $this
	 * @throws  RuntimeException    If the previous database update is stuck
	 *
	 */
	public function checkAndFixDatabase()
	{
		$params = $this->container->params;

		// First of all let's check if we are already updating
		$stuck = $params->get('updatedb', 0);

		if ($stuck)
		{
			throw new RuntimeException('Previous database update is flagged as stuck');
		}

		// Then set the flag
		$params->set('updatedb', 1);
		$params->save();

		// Install or update database
		$db          = $this->container->db;
		$dbInstaller = new Installer($db, JPATH_ADMINISTRATOR . '/components/com_admintools/sql/xml');

		$dbInstaller->updateSchema();

		// And finally remove the flag if everything went fine
		$params->set('updatedb', null);
		$params->save();

		return $this;
	}

	/**
	 * Checks all the available places if we just blocked our own IP?
	 *
	 * @param   string  $externalIp  Additional IP address to check
	 *
	 * @return  bool
	 */
	public function isMyIPBlocked($externalIp = null): bool
	{
		$isPro = (defined('ADMINTOOLS_PRO') ? ADMINTOOLS_PRO : 0) == 1;

		if (!$isPro)
		{
			return false;
		}

		// First let's get the current IP of the user
		$ipList = [];
		$ipList[] = $this->getVisitorIP();

		if ($externalIp)
		{
			$ipList[] = $externalIp;
		}

		/** @var AutoBannedAddresses $autoban */
		$autoban = $this->container->factory->model('AutoBannedAddresses')->tmpInstance();

		/** @var IPAutoBanHistories $history */
		$history = $this->container->factory->model('IPAutoBanHistories')->tmpInstance();

		/** @var BlacklistedAddresses $black */
		$black = $this->container->factory->model('BlacklistedAddresses')->tmpInstance();

		// Then for each ip let's check if it's in any "blocked" list
		foreach ($ipList as $ip)
		{
			$autoban->reset()->setState('ip', $ip);
			$history->reset()->setState('ip', $ip);
			$black->reset()->setState('ip', $ip);

			if (count($autoban->get(true)))
			{
				return true;
			}

			if (count($history->get(true)))
			{
				return true;
			}

			if (count($black->get(true)))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Update the cached live site's URL for the front-end scheduling feature
	 *
	 * @return  void
	 */
	public function updateMagicParameters()
	{
		$this->container->params->set('siteurl', str_replace('/administrator', '', Uri::base()));
		$this->container->params->save();
	}

	/**
	 * Performs some checks about Joomla configuration (log and tmp path correctly set)
	 *
	 * @return  string|bool  Warning message. Boolean FALSE if no warning is found.
	 */
	public function checkJoomlaConfiguration()
	{
		// Get the absolute path to the site's root
		$absoluteRoot = @realpath(JPATH_ROOT);
		$siteroot     = empty($absoluteRoot) ? JPATH_ROOT : $absoluteRoot;

		// First of all, do we have a VALID log folder?
		$config  = $this->container->platform->getConfig();
		$log_dir = $config->get('log_path');

		if (!$log_dir || !@is_writable($log_dir))
		{
			return Text::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_JCONFIG_INVALID_LOGDIR');
		}

		if ($siteroot == $log_dir)
		{
			return Text::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_JCONFIG_LOGDIR_SITEROOT');
		}

		// Do we have a VALID tmp folder?
		$tmp_dir = $config->get('tmp_path');

		if (!$tmp_dir || !@is_writable($tmp_dir))
		{
			return Text::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_JCONFIG_INVALID_TMPDIR');
		}

		if ($siteroot == $tmp_dir)
		{
			return Text::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_JCONFIG_TMPDIR_SITEROOT');
		}

		return false;
	}

	/**
	 * Do I need to show the Quick Setup Wizard?
	 *
	 * @return  bool
	 */
	public function needsQuickSetupWizard(): bool
	{
		$params = Storage::getInstance();

		return $params->getValue('quickstart', 0) == 0;
	}

	/**
	 * Get the most likely visitor IP address, reported by the server
	 *
	 * @return  string
	 */
	public function getVisitorIP(): string
	{
		$internalIP = Ip::getIp();

		if ((strpos($internalIP, '::') === 0) && (strstr($internalIP, '.') !== false))
		{
			$internalIP = substr($internalIP, 2);
		}

		return $internalIP;
	}

	/**
	 * Checks if we have detected private network IPs AND the IP Workaround feature is turned off
	 *
	 * @return bool
	 */
	public function needsIpWorkaroundsForPrivNetwork(): bool
	{
		$WAFparams = Storage::getInstance();
		$params    = $this->container->params;

		// If IP Workarounds is disabled AND we have detected private IPs, show the warning
		if (!$WAFparams->getValue('ipworkarounds', -1) && ($params->get('detected_exceptions_from_private_network') === 1))
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks if we have detected a prozy header AND the IP Workaround feature is turned off
	 *
	 * @return bool
	 */
	public function needsIpWorkaroundsHeaders(): bool
	{
		$WAFparams = Storage::getInstance();
		$params    = $this->container->params;

		// IP Workarounds are already loaded, no notice
		if ($WAFparams->getValue('ipworkarounds', -1))
		{
			return false;
		}

		// User suppressed the notice
		if ($params->get('detected_proxy_header') === -1)
		{
			return false;
		}

		// Ok let's check if we truly have any proxy header
		$headers = Ip::getProxyHeaders();

		foreach ($headers as $header)
		{
			// Proxy header found, warn the user
			if (isset($_SERVER[$header]))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Sets the IP workarounds or ignores the warning
	 *
	 * @param $state
	 */
	public function setIpWorkarounds($state)
	{
		if ($state)
		{
			$WAFparams = Storage::getInstance();
			$WAFparams->setValue('ipworkarounds', 1, true);
		}
		else
		{
			// If we user wants to ignore the warning, let's set every flag about IP workarounds to -1 (so they will be ignored)
			$params = $this->container->params;
			$params->set('detected_exceptions_from_private_network', -1);
			$params->set('detected_proxy_header', -1);
			$params->save();
		}
	}

	/**
	 * Is the System - Admin Tools plugin installed?
	 *
	 * @return  bool
	 *
	 * @since  4.3.0
	 */
	public function isPluginInstalled(): bool
	{
		$this->getPluginID();

		return self::$pluginId != 0;
	}

	/**
	 * Is the System - Admin Tools plugin currently loaded?
	 *
	 * @return  bool
	 *
	 * @since   4.3.0
	 */
	public function isPluginLoaded(): bool
	{
		return class_exists('plgSystemAdmintools');
	}

	/**
	 * Is the main.php file renamed?
	 *
	 * @return  bool
	 *
	 * @since   4.3.0
	 */
	public function isMainPhpDisabled(): bool
	{
		$folder = JPATH_PLUGINS . '/system/admintools/admintools';

		return @is_dir($folder) && !@file_exists($folder . '/main.php');
	}

	/**
	 * Rename the disabled main.php file back to its proper, main.php, name.
	 *
	 * @return  bool
	 *
	 * @since   4.3.0
	 */
	public function reenableMainPhp(): bool
	{
		$altName = $this->getRenamedMainPhp();

		if (!$altName)
		{
			return false;
		}

		$folder = JPATH_PLUGINS . '/system/admintools/admintools';

		$from = $folder . '/' . $altName;
		$to   = $folder . '/main.php';

		$res = @rename($from, $to);

		if (!$res)
		{
			$res = @copy($from, $to);

			if ($res)
			{
				@unlink($from);
			}
		}

		if (!$res)
		{
			$res = File::copy($from, $to);

			if ($res)
			{
				File::delete($from);
			}
		}

		return $res;
	}

	/**
	 * Get the file name under which main.php has been renamed to
	 *
	 * @return  string|null
	 *
	 * @since   4.3.0
	 */
	public function getRenamedMainPhp(): ?string
	{
		$possibleNames = [
			'main-disable.php',
			'main.php.bak',
			'main.bak.php',
			'main.bak',
			'-main.php',
		];

		$folder = JPATH_PLUGINS . '/system/admintools/admintools';

		foreach ($possibleNames as $baseName)
		{
			if (@file_exists($folder . '/' . $baseName))
			{
				return $baseName;
			}
		}

		return null;
	}

	/**
	 * Delete old log files (with a .log extension) always. If the logging feature is disabled (either the text debug
	 * log or logging in general) also delete the .php log files.
	 *
	 * @since  5.1.0
	 */
	public function deleteOldLogs()
	{
		$logpath = Factory::getConfig()->get('log_path');
		$files   = [
			$logpath . DIRECTORY_SEPARATOR . 'admintools_breaches.log',
			$logpath . DIRECTORY_SEPARATOR . 'admintools_breaches.log.1',
		];

		$WAFparams = Storage::getInstance();
		$textLogs  = $WAFparams->getValue('logfile', 0);
		$allLogs   = $WAFparams->getValue('logbreaches', 1);

		if (!$textLogs || !$allLogs)
		{
			$files = array_merge($files, [
				$logpath . DIRECTORY_SEPARATOR . 'admintools_breaches.php',
				$logpath . DIRECTORY_SEPARATOR . 'admintools_breaches.1.php',

			]);
		}

		foreach ($files as $file)
		{
			if (!@file_exists($file))
			{
				continue;
			}

			if (@unlink($file))
			{
				continue;
			}

			File::delete($file);
		}
	}

	/**
	 * Checks if the current contents of the server configuration file (ie .htaccess) match with the saved one.
	 */
	public function serverConfigEdited(): bool
	{
		// Core version? No need to continue
		if (!defined('ADMINTOOLS_PRO') || !ADMINTOOLS_PRO)
		{
			return false;
		}

		// User decided to ignore any warning about manual edits
		if (!$this->container->params->get('serverconfigwarn', 1))
		{
			return false;
		}

		$modelTech = '';

		if (ServerTechnology::isNginxSupported() == 1)
		{
			$modelTech = 'NginXConfMaker';
		}
		elseif (ServerTechnology::isWebConfigSupported() == 1)
		{
			$modelTech = 'WebConfigMaker';
		}
		elseif (ServerTechnology::isHtaccessSupported() == 1)
		{
			$modelTech = 'HtaccessMaker';
		}

		// Can't understand the Server Technology we're on, let's stop here
		if (!$modelTech)
		{
			return false;
		}

		try
		{
			/** @var ServerConfigMaker $serverModel */
			$serverModel = $this->container->factory->model($modelTech)->tmpInstance();
		}
		catch (Exception $e)
		{
			return false;
		}

		$serverFile = JPATH_ROOT . '/' . $serverModel->getConfigFileName();

		if (!file_exists($serverFile))
		{
			return false;
		}

		$actualContents = file_get_contents($serverFile);

		if (!$actualContents)
		{
			return false;
		}

		$currentContents = $serverModel->makeConfigFile();

		// Is the hash of current file different from the saved one? If so, warn the user
		return ($serverModel->getConfigHash($actualContents) != $serverModel->getConfigHash($currentContents));
	}

	/**
	 * Check the strength of the Secret Word for front-end and remote scans. If it is insecure return the reason it
	 * is insecure as a string. If the Secret Word is secure return an empty string.
	 *
	 * @return  string
	 */
	public function getFrontendSecretWordError(): string
	{
		$params = $this->container->params;

		// Is frontend backup enabled?
		$febEnabled = $params->get('frontend_enable', 0) != 0;

		if (!$febEnabled)
		{
			return '';
		}

		$secretWord = $params->get('frontend_secret_word', '');

		try
		{
			Complexify::isStrongEnough($secretWord);
		}
		catch (RuntimeException $e)
		{
			// Ah, the current Secret Word is bad. Create a new one if necessary.
			$newSecret = $this->container->platform->getSessionVar('newSecretWord', null, 'admintools.cpanel');

			if (empty($newSecret))
			{
				$random    = new Randval();
				$newSecret = $random->getRandomPassword(32);
				$this->container->platform->setSessionVar('newSecretWord', $newSecret, 'admintools.cpanel');
			}

			return $e->getMessage();
		}

		return '';
	}
}

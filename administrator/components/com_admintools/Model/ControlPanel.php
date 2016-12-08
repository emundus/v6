<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\Engine\Platform;
use Akeeba\Engine\Util\Complexify;
use FOF30\Database\Installer;
use FOF30\Model\Model;
use FOF30\Utils\CacheCleaner;
use FOF30\Utils\Ip;
use JText;

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
	public function getPluginID()
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
							->select(array(
								$db->qn('extension_id'),
								$db->qn('ordering'),
							))
							->from($db->qn('#__extensions'))
							->where($db->qn('type') . ' = ' . $db->q('plugin'))
							->where($db->qn('folder') . ' = ' . $db->q('system'))
							->order($db->qn('ordering') . ' ASC');
		$orderingPerId = $db->setQuery($query)->loadAssocList('extension_id', 'ordering');

		$orderings   = array_values($orderingPerId);
		$orderings   = array_unique($orderings);
		$minOrdering = reset($orderings);

		$myOrdering = $orderingPerId[ $id ];

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
	public function needsDownloadID()
	{
		// Do I need a Download ID?
		if (!ADMINTOOLS_PRO)
		{
			return false;
		}

		$dlid = $this->container->params->get('downloadid', '');

		if (!preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks the database for missing / outdated tables using the $dbChecks
	 * data and runs the appropriate SQL scripts if necessary.
	 *
	 * @throws  \RuntimeException    If the previous database update is stuck
	 *
	 * @return  $this
	 */
	public function checkAndFixDatabase()
	{
		$params = $this->container->params;

		// First of all let's check if we are already updating
		$stuck = $params->get('updatedb', 0);

		if ($stuck)
		{
			throw new \RuntimeException('Previous database update is flagged as stuck');
		}

		// Then set the flag
		$params->set('updatedb', 1);
		$params->save();

		// Install or update database
		$db          = \JFactory::getDbo();
		$dbInstaller = new Installer($db, JPATH_ADMINISTRATOR . '/components/com_admintools/sql/xml');

		$dbInstaller->updateSchema();

		// Let's check and fix common tables, too
		/** @var Stats $statsModel */
		$statsModel = $this->container->factory->model('Stats')->tmpInstance();
		$statsModel->checkAndFixCommonTables();

		// And finally remove the flag if everything went fine
		$params->set('updatedb', null);
		$params->save();

		return $this;
	}

	/**
	 * Checks all the available places if we just blocked our own IP?
	 *
	 * @param	string	$externalIp	 Additional IP address to check
	 *
	 * @return  bool
	 */
	public function isMyIPBlocked($externalIp = null)
	{
		// First let's get the current IP of the user
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
	 * Removed the current IP from all the "block" lists
	 *
	 * @param	string	$externalIp	Additional IP address to check
	 *
	 * @return  void
	 */
	public function unblockMyIP($externalIp = null)
	{
		// First let's get the current IP of the user
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

		/** @var SecurityExceptions $log */
		$log = $this->container->factory->model('SecurityExceptions')->tmpInstance();

		$db  = $this->container->db;

		// Let's delete all the IP. We are going to directly use the database since it would be faster
		// than loading the record and then deleting it
		foreach ($ipList as $ip)
		{
			$autoban->reset()->setState('ip', $ip);
			$history->reset()->setState('ip', $ip);
			$black->reset()->setState('ip', $ip);
			$log->reset()->setState('ip', $ip);

			if (count($autoban->get(true)))
			{
				$query = $db->getQuery(true)
							->delete($db->qn('#__admintools_ipautoban'))
							->where($db->qn('ip') . ' = ' . $db->q($ip));
				$db->setQuery($query)->execute();
			}

			if (count($history->get(true)))
			{
				$query = $db->getQuery(true)
							->delete($db->qn('#__admintools_ipautobanhistory'))
							->where($db->qn('ip') . ' = ' . $db->q($ip));
				$db->setQuery($query)->execute();
			}

			if (count($black->get(true)))
			{
				$query = $db->getQuery(true)
							->delete($db->qn('#__admintools_ipblock'))
							->where($db->qn('ip') . ' = ' . $db->q($ip));
				$db->setQuery($query)->execute();
			}

			// I have to delete the log of security exceptions, too. Otherwise at the next check the user will be
			// banned once again
			if (count($log->get(true)))
			{
				$query = $db->getQuery(true)
							->delete($db->qn('#__admintools_log'))
							->where($db->qn('ip') . ' = ' . $db->q($ip));
				$db->setQuery($query)->execute();
			}
		}
	}

	/**
	 * Update the cached live site's URL for the front-end scheduling feature
	 *
	 * @return  void
	 */
	public function updateMagicParameters()
	{
		$this->container->params->set('siteurl', str_replace('/administrator', '', \JUri::base()));
		$this->container->params->save();
	}

	/**
	 * Check the strength of the Secret Word for front-end and remote scans. If it is insecure return the reason it
	 * is insecure as a string. If the Secret Word is secure return an empty string.
	 *
	 * @return  string
	 */
	public function getFrontendSecretWordError()
	{
		// Load the Akeeba Engine autoloader
		define('AKEEBAENGINE', 1);
		require_once JPATH_ADMINISTRATOR . '/components/com_admintools/engine/Autoloader.php';

		// Load the platform
		Platform::addPlatform('filescan', JPATH_ADMINISTRATOR . '/components/com_admintools/platform/Filescan');

		// Is frontend backup enabled?
		$febEnabled = Platform::getInstance()->get_platform_configuration_option('frontend_enable', 0) != 0;

		if (!$febEnabled)
		{
			return '';
		}

		$secretWord = Platform::getInstance()->get_platform_configuration_option('frontend_secret_word', '');

		try
		{
			Complexify::isStrongEnough($secretWord);
		}
		catch (\RuntimeException $e)
		{
			// Ah, the current Secret Word is bad. Create a new one if necessary.
			$session = \JFactory::getSession();
			$newSecret = $session->get('newSecretWord', null, 'admintools.cpanel');

			if (empty($newSecret))
			{
				$random = new \Akeeba\Engine\Util\RandomValue();
				$newSecret = $random->generateString(32);
				$session->set('newSecretWord', $newSecret, 'admintools.cpanel');
			}

			return $e->getMessage();
		}

		return '';
	}

	/**
	 * Performs some checks about Joomla configuration (log and tmp path correctly set)
	 *
	 * @return  string|bool  Warning message. Boolean FALSE if no warning is found.
	 */
	public function checkJoomlaConfiguration()
	{
		// Let's get the site root using the Platform code
		if (!defined('AKEEBAENGINE'))
		{
			define('AKEEBAENGINE', 1);
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_admintools/engine/Autoloader.php';

		$siteroot      = Platform::getInstance()->get_site_root();
		$siteroot_real = @realpath($siteroot);

		if (!empty($siteroot_real))
		{
			$siteroot = $siteroot_real;
		}

		//First of all, do we have a VALID log folder?
		$config  = \JFactory::getConfig();
		$log_dir = $config->get('log_path');

		if (!$log_dir || !@is_writable($log_dir))
		{
			return JText::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_JCONFIG_INVALID_LOGDIR');
		}

		if ($siteroot == $log_dir)
		{
			return JText::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_JCONFIG_LOGDIR_SITEROOT');
		}

		// Do we have a VALID tmp folder?
		$tmp_dir = $config->get('tmp_path');

		if (!$tmp_dir || !@is_writable($tmp_dir))
		{
			return JText::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_JCONFIG_INVALID_TMPDIR');
		}

		if ($siteroot == $tmp_dir)
		{
			return JText::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_JCONFIG_TMPDIR_SITEROOT');
		}

		return false;
	}

	/**
	 * Do I need to show the Quick Setup Wizard?
	 *
	 * @return  bool
	 */
	public function needsQuickSetupWizard()
	{
		$params = Storage::getInstance();

		return $params->getValue('quickstart', 0) == 0;
	}

	/**
	 * Get the most likely visitor IP address, reported by the server
	 *
	 * @return  string
	 */
	public function getVisitorIP()
	{
		$internalIP = Ip::getIp();

		if (array_key_exists('FOF_REMOTE_ADDR', $_SERVER))
		{
			$internalIP = $_SERVER['FOF_REMOTE_ADDR'];

			return $internalIP;
		}
		elseif (function_exists('getenv') && getenv('FOF_REMOTE_ADDR'))
		{
			$internalIP = getenv('FOF_REMOTE_ADDR');

			return $internalIP;
		}

		if ((strpos($internalIP, '::') === 0) && (strstr($internalIP, '.') !== false))
		{
			$internalIP = substr($internalIP, 2);
		}

		return $internalIP;
	}
}
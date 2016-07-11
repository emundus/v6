<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

JLoader::import('joomla.application.component.model');

/**
 * The Control Panel model
 *
 */
class AdmintoolsModelCpanels extends F0FModel
{
	/**
	 * Constructor; dummy for now
	 *
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function getPluginID()
	{
		static $id = null;

		if (empty($id))
		{
			$db = $this->getDBO();

			$query = $db->getQuery(true)
				->select($db->qn('extension_id'))
				->from($db->qn('#__extensions'))
				->where($db->qn('enabled') . ' >= ' . $db->quote('1'))
				->where($db->qn('folder') . ' = ' . $db->quote('system'))
				->where($db->qn('element') . ' = ' . $db->quote('admintools'))
				->where($db->qn('type') . ' = ' . $db->quote('plugin'))
				->order($db->qn('ordering') . ' ASC');
			$db->setQuery($query);
			$id = $db->loadResult();
		}

		return $id;
	}

	/**
	 * Makes sure our system plugin is really the very first system plugin to execute
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
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select(array(
				$db->qn('extension_id'),
				$db->qn('ordering'),
			))
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('plugin'))
			->where($db->qn('folder') . ' = ' . $db->q('system'))
			->order($db->qn('ordering') . ' ASC');
		$db->setQuery($query);
		$orderingPerId = $db->loadAssocList('extension_id', 'ordering');

		$orderings = array_values($orderingPerId);
		$orderings = array_unique($orderings);
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
			F0FUtilsCacheCleaner::clearPluginsCache();
		}
	}

    /**
     * Does the user need to enter a Download ID in the component's Options page?
     *
     * @return bool
     */
	public function needsDownloadID()
	{
		if (!class_exists('AdmintoolsHelperParams'))
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_admintools/helpers/params.php';
		}

		// Do I need a Download ID?
		$ret   = false;
		$isPro = ADMINTOOLS_PRO;

		if (!$isPro)
		{
			$ret = false;
		}
		else
		{
			$params = new AdmintoolsHelperParams();
			$dlid = $params->get('downloadid', '');

			if (!preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
			{
				$ret = true;
			}
		}

		return $ret;
	}

	/**
	 * Checks the database for missing / outdated tables using the $dbChecks
	 * data and runs the appropriate SQL scripts if necessary.
	 *
	 * @return AdmintoolsModelCpanels
	 */
	public function checkAndFixDatabase()
	{
		// Install or update database
		$dbInstaller = new F0FDatabaseInstaller(array(
			'dbinstaller_directory' => JPATH_ADMINISTRATOR . '/components/com_admintools/sql/xml'
		));

		$dbInstaller->updateSchema();

        // Let's check and fix common tables, too
        F0FModel::getTmpInstance('Stats', 'AdmintoolsModel')->checkAndFixCommonTables();

		return $this;
	}

	/**
	 * Returns true if we are installed in Joomla! 3.2 or later and we have post-installation messages for our component
	 * which must be showed to the user.
	 *
	 * Returns null if the com_postinstall component is broken because the user screwed up his Joomla! site following
	 * some idiot's advice. Apparently there's no shortage of idiots giving terribly bad advice to Joomla! users.
	 *
	 * @return bool|null
	 */
	public function hasPostInstallMessages()
	{
		// Get the extension ID for our component
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('element') . ' = ' . $db->q('com_admintools'));
		$db->setQuery($query);

		try
		{
			$ids = $db->loadColumn();
		}
		catch (Exception $exc)
		{
			return false;
		}

		if (empty($ids))
		{
			return false;
		}

		$extension_id = array_shift($ids);

		$this->setState('extension_id', $extension_id);

		if (!defined('FOF_INCLUDED'))
		{
			include_once JPATH_SITE.'/libraries/fof/include.php';
		}

		if (!defined('FOF_INCLUDED'))
		{
			return false;
		}

		// Do I have messages?
		try
		{
			$pimModel = FOFModel::getTmpInstance('Messages', 'PostinstallModel');
			$pimModel->savestate(false);
			$pimModel->setState('eid', $extension_id);

			$list   = $pimModel->getList();
			$result = count($list) >= 1;
		}
		catch (\Exception $e)
		{
			$result = null;
		}

		return ($result);
	}
	
	/**
	 * Checks all the available places if we just blocked our own IP?
	 *
	 * @param	string	$externalIp	Additional IP address to check
	 *
	 * @return bool
	 */
	public function selfBlocked($externalIp = null)
	{
		// First let's get the current IP of the user
		$internalIP = F0FUtilsIp::getIp();

		if (array_key_exists('FOF_REMOTE_ADDR', $_SERVER))
		{
			$internalIP = $_SERVER['FOF_REMOTE_ADDR'];
		}
		elseif (function_exists('getenv'))
		{
			if (getenv('FOF_REMOTE_ADDR'))
			{
				$internalIP = getenv('FOF_REMOTE_ADDR');
			}
		}

		$ipList[] = $internalIP;

		if($externalIp)
		{
			$ipList[] = $externalIp;
		}

		/** @var AdmintoolsModelIpautobans $autoban */
		$autoban = F0FModel::getTmpInstance('Ipautobans', 'AdmintoolsModel');
		/** @var AdmintoolsModelIpautobanhistories $history */
		$history = F0FModel::getTmpInstance('Ipautobanhistories', 'AdmintoolsModel');
		/** @var AdmintoolsModelIpbls $black */
		$black   = F0FModel::getTmpInstance('Ipbls', 'AdmintoolsModel');

		// Then for each ip let's check if it's in any "blocked" list
		foreach ($ipList as $ip)
		{
			$autoban->reset()->set('ip', $ip);
			$history->reset()->set('ip', $ip);
			$black->reset()->set('ip', $ip);

			if($autoban->getList(true))
			{
				return true;
			}

			if($history->getList(true))
			{
				return true;
			}

			if($black->getList(true))
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
	 * @return bool
	 */
	public function unblockme($externalIp = null)
	{
		// First let's get the current IP of the user
		$internalIP = F0FUtilsIp::getIp();

		if (array_key_exists('FOF_REMOTE_ADDR', $_SERVER))
		{
			$internalIP = $_SERVER['FOF_REMOTE_ADDR'];
		}
		elseif (function_exists('getenv'))
		{
			if (getenv('FOF_REMOTE_ADDR'))
			{
				$internalIP = getenv('FOF_REMOTE_ADDR');
			}
		}

		$ipList[] = $internalIP;

		if($externalIp)
		{
			$ipList[] = $externalIp;
		}

		/** @var AdmintoolsModelIpautobans $autoban */
		$autoban = F0FModel::getTmpInstance('Ipautobans', 'AdmintoolsModel');
		/** @var AdmintoolsModelIpautobanhistories $history */
		$history = F0FModel::getTmpInstance('Ipautobanhistories', 'AdmintoolsModel');
		/** @var AdmintoolsModelIpbls $black */
		$black   = F0FModel::getTmpInstance('Ipbls', 'AdmintoolsModel');
		/** @var AdmintoolsModelLogs $log */
		$log     = F0FModel::getTmpInstance('Logs', 'AdmintoolsModel');
		$db		 = $this->getDbo();

		// Let's delete all the IP. We are going to directly use the database since it would be faster
		// than loading the record and then deleting it
		foreach ($ipList as $ip)
		{
			$autoban->reset()->set('ip', $ip);
			$history->reset()->set('ip', $ip);
			$black->reset()->set('ip', $ip);
			$log->reset()->set('ip', $ip);

			if($autoban->getList(true))
			{
				$query = $db->getQuery(true)
							->delete($db->qn('#__admintools_ipautoban'))
							->where($db->qn('ip').' = '.$db->q($ip));
				$db->setQuery($query)->execute();
			}

			if($history->getList(true))
			{
				$query = $db->getQuery(true)
							->delete($db->qn('#__admintools_ipautobanhistory'))
							->where($db->qn('ip').' = '.$db->q($ip));
				$db->setQuery($query)->execute();
			}

			if($black->getList(true))
			{
				$query = $db->getQuery(true)
							->delete($db->qn('#__admintools_ipblock'))
							->where($db->qn('ip').' = '.$db->q($ip));
				$db->setQuery($query)->execute();
			}

			// I have to delete the log of security exceptions, too. Otherwise at the next check the user will be
			// banned once again
			if($log->getList(true))
			{
				$query = $db->getQuery(true)
							->delete($db->qn('#__admintools_log'))
							->where($db->qn('ip').' = '.$db->q($ip));
				$db->setQuery($query)->execute();
			}
		}
	}

	/**
	 * Update the cached live site's URL for the front-end scheduling feature
	 */
	public function updateMagicParameters()
	{
		if (!class_exists('AdmintoolsHelperParams'))
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_admintools/helpers/params.php';
		}

		$params = new AdmintoolsHelperParams();
		$params->set('siteurl', str_replace('/administrator', '', JUri::base()));
		$params->save();
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
		\Akeeba\Engine\Platform::addPlatform('filescan', JPATH_ADMINISTRATOR . '/components/com_admintools/platform/Filescan');

		// Is frontend backup enabled?
		$febEnabled = \Akeeba\Engine\Platform::getInstance()->get_platform_configuration_option('frontend_enable', 0) != 0;

		if (!$febEnabled)
		{
			return '';
		}

		$secretWord = \Akeeba\Engine\Platform::getInstance()->get_platform_configuration_option('frontend_secret_word', '');

		try
		{
			\Akeeba\Engine\Util\Complexify::isStrongEnough($secretWord);
		}
		catch (RuntimeException $e)
		{
			// Ah, the current Secret Word is bad. Create a new one if necessary.
			$session = JFactory::getSession();
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
     */
    public function checkJoomlaConfiguration()
    {
        // Let's get the site root using the Platform code
        if(!defined('AKEEBAENGINE'))
        {
            define('AKEEBAENGINE', 1);
        }

        require_once JPATH_ADMINISTRATOR . '/components/com_admintools/engine/Autoloader.php';

        $siteroot = \Akeeba\Engine\Platform::getInstance()->get_site_root();
        $siteroot_real = @realpath($siteroot);

        if (!empty($siteroot_real))
        {
            $siteroot = $siteroot_real;
        }

        //First of all, do we have a VALID log folder?
        $config = JFactory::getConfig();
        $log_dir = $config->get('log_path');

        if(!$log_dir || !@is_writable($log_dir))
        {
            return JText::_('COM_ADMINTOOLS_CPANEL_ERR_JCONFIG_INVALID_LOGDIR');
        }

        if($siteroot == $log_dir)
        {
            return JText::_('COM_ADMINTOOLS_CPANEL_ERR_JCONFIG_LOGDIR_SITEROOT');
        }

        // Do we have a VALID tmp folder?
        $tmp_dir = $config->get('tmp_path');

        if(!$tmp_dir || !@is_writable($tmp_dir))
        {
            return JText::_('COM_ADMINTOOLS_CPANEL_ERR_JCONFIG_INVALID_TMPDIR');
        }

        if($siteroot == $tmp_dir)
        {
            return JText::_('COM_ADMINTOOLS_CPANEL_ERR_JCONFIG_TMPDIR_SITEROOT');
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
		if (interface_exists('JModel'))
		{
			/** @var AdmintoolsModelStorage $params */
			$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			/** @var AdmintoolsModelStorage $params */
			$params = JModel::getInstance('Storage', 'AdmintoolsModel');
		}

		return $params->getValue('quickstart', 0) == 0;
	}
}
<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
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
	 * Automatically migrates settings from the component's parameters storage
	 * to our version 2.1+ dedicated storage table.
	 */
	public function autoMigrate()
	{
		// First, load the component parameters
		// FIX 2.1.13: Load the component parameters WITHOUT using JComponentHelper
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('params'))
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->quote('component'))
			->where($db->qn('element') . ' = ' . $db->quote('com_admintools'));
		$db->setQuery($query);
		$rawparams = $db->loadResult();
		$cparams = new JRegistry();
		$cparams->loadString($rawparams, 'JSON');

		// Migrate parameters
		$allParams = $cparams->toArray();
		$safeList = array(
			'downloadid', 'lastversion', 'minstability',
			'scandiffs', 'scanemail', 'htmaker_folders_fix_at240',
			'acceptlicense', 'acceptsupport', 'sitename',
			'showstats', 'longconfigpage',
			'autoupdateCli', 'notificationFreq', 'notificationTime', 'notificationEmail', 'usage'
		);
		if (interface_exists('JModel'))
		{
			$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$params = JModel::getInstance('Storage', 'AdmintoolsModel');
		}
		$modified = 0;
		foreach ($allParams as $k => $v)
		{
			if (in_array($k, $safeList))
			{
				continue;
			}
			if ($v == '')
			{
				continue;
			}

			$modified++;

			$cparams->set($k, null);
			$params->setValue($k, $v);
		}

		if ($modified == 0)
		{
			return;
		}

		// Save new parameters
		$params->save();

		// Save component parameters
		$db = JFactory::getDBO();
		$data = $cparams->toString();

		$sql = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('params') . ' = ' . $db->q($data))
			->where($db->qn('element') . ' = ' . $db->q('com_admintools'))
			->where($db->qn('type') . ' = ' . $db->q('component'));

		$db->setQuery($sql);
		$db->execute();
	}

	public function needsDownloadID()
	{
		JLoader::import('joomla.application.component.helper');

		// Do I need a Download ID?
		$ret = false;
		$isPro = ADMINTOOLS_PRO;
		if (!$isPro)
		{
			$ret = true;
		}
		else
		{
			$ret = false;
			$params = JComponentHelper::getParams('com_admintools');
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
	 * Perform a fast check of Admin Tools' files
	 *
	 * @return bool False if some of the files are missing or tampered with
	 */
	public function fastCheckFiles()
	{
		$checker = new F0FUtilsFilescheck('com_admintools', ADMINTOOLS_VERSION, ADMINTOOLS_DATE);

		return $checker->fastCheck();
	}
}
<?php
/**
 * @package        AkeebaFEF
 * @copyright Copyright (c)2017-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU GPL version 3 or later
 */

defined('_JEXEC') or die();

class file_fefInstallerScript
{
	/**
	 * The minimum PHP version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumPHPVersion = '5.4.0';

	/**
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumJoomlaVersion = '3.4.0';

	/**
	 * The maximum Joomla! version this extension can be installed on
	 *
	 * @var   string
	 */
	protected $maximumJoomlaVersion = '4.0.99';

	/**
	 * The path, relative to JPATH_LIBRARIES, where FOF is expected to be found. If it's not there, the installation
	 * will abort.
	 *
	 * @var   string
	 */
	protected $fofLibrariesPath = 'fof30';

	protected $fofDefine = 'FOF30_INCLUDED';

	/**
	 * Joomla! pre-flight event. This runs before Joomla! installs or updates the component. This is our last chance to
	 * tell Joomla! if it should abort the installation.
	 *
	 * @param   string     $type   Installation type (install, update, discover_install)
	 * @param   JInstaller $parent Parent object
	 *
	 * @return  boolean  True to let the installation proceed, false to halt the installation
	 */
	public function preflight($type, $parent)
	{
		// Check the minimum PHP version
		if (!empty($this->minimumPHPVersion))
		{
			if (defined('PHP_VERSION'))
			{
				$version = PHP_VERSION;
			}
			elseif (function_exists('phpversion'))
			{
				$version = phpversion();
			}
			else
			{
				$version = '5.0.0'; // all bets are off!
			}

			if (!version_compare($version, $this->minimumPHPVersion, 'ge'))
			{
				$msg = "<p>You need PHP $this->minimumPHPVersion or later to install this package but you are currently using PHP  $version</p>";

				JLog::add($msg, JLog::WARNING, 'jerror');

				return false;
			}
		}

		// Check the minimum Joomla! version
		if (!empty($this->minimumJoomlaVersion) && !version_compare(JVERSION, $this->minimumJoomlaVersion, 'ge'))
		{
			$jVersion = JVERSION;
			$msg = "<p>You need Joomla! $this->minimumJoomlaVersion or later to install this package but you only have $jVersion installed.</p>";

			JLog::add($msg, JLog::WARNING, 'jerror');

			return false;
		}

		// Check the maximum Joomla! version
		if (!empty($this->maximumJoomlaVersion) && !version_compare(JVERSION, $this->maximumJoomlaVersion, 'le'))
		{
			$jVersion = JVERSION;
			$msg = "<p>You need Joomla! $this->maximumJoomlaVersion or earlier to install this package but you have $jVersion installed</p>";

			JLog::add($msg, JLog::WARNING, 'jerror');

			return false;
		}

		// Check if we have FOF installed
		$fofPath = JPATH_LIBRARIES . '/' . $this->fofLibrariesPath;
		$isFOFInstalled = @is_dir($fofPath);

		if (!$isFOFInstalled)
		{
			$msg = "<p>You need to have FOF installed in $fofPath before installing this package.</p>";

			JLog::add($msg, JLog::WARNING, 'jerror');

			return false;
		}

		// In case of an update, discovery etc I need to check if I am an update
		if (($type != 'install') && !$this->amIAnUpdate($parent))
		{
			$msg = "<p>You have a newer version of Akeeba Frontend Framework installed. If you want to downgrade please uninstall Akeeba Frontend Framework and install the older version.</p>";

			JLog::add($msg, JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Runs after install, update or discover_update. In other words, it executes after Joomla! has finished installing
	 * or updating your component. This is the last chance you've got to perform any additional installations, clean-up,
	 * database updates and similar housekeeping functions.
	 *
	 * @param   string                $type   install, update or discover_update
	 * @param   JInstallerAdapterFile $parent Parent object
	 */
	public function postflight($type, JInstallerAdapterFile $parent)
	{
		$this->loadFOF30();

		if (!defined($this->fofDefine))
		{
			return;
		}

		// Install or update database
		$db = JFactory::getDbo();

		/** @var JInstaller $grandpa */
		$grandpa = $parent->getParent();
		$src = $grandpa->getPath('source');
		$sqlSource = $src . '/sql';

		$dbInstaller = new FOF30\Database\Installer($db, $sqlSource);
		$dbInstaller->updateSchema();
        $dbInstaller->nukeCache();

		// Add self to FOF 3.0 dependency list
		$this->addDependency('fof30', 'file_fef');

		// Clear the FOF cache
		$fakeController = \FOF30\Container\Container::getInstance('com_FOOBAR');
		$fakeController->platform->clearCache();
	}

	/**
	 * Runs on uninstallation
	 *
	 * @param   JInstallerAdapterFile $parent The parent object
	 *
	 * @throws  RuntimeException  If the uninstallation is not allowed
	 */
	public function uninstall($parent)
	{
		// Check dependencies on FEF
		$dependencyCount = count($this->getDependencies('file_fef'));

		if ($dependencyCount)
		{
			$msg = "<p>You have $dependencyCount extension(s) depending on Akeeba Frontend Framework. The package cannot be uninstalled unless these extensions are uninstalled first.</p>";

			JLog::add($msg, JLog::WARNING, 'jerror');

			throw new RuntimeException($msg, 500);
		}

		// Remove self from FOF 3.0 dependencies
		$this->removeDependency('fof30', 'file_fef');

		JLoader::import('joomla.filesystem.folder');
		JFolder::delete(JPATH_SITE . '/media/fef');
	}


	/**
	 * Is this package an update to the currently installed FEF? If not (we're a downgrade) we will return false
	 * and prevent the installation from going on.
	 *
	 * @param   \JInstallerAdapterComponent $parent The parent object
	 *
	 * @return  bool  Am I an update to an existing version>
	 */
	protected function amIAnUpdate($parent)
	{
		/** @var JInstaller $grandpa */
		$grandpa = $parent->getParent();

		$source = $grandpa->getPath('source');

		$target = JPATH_ROOT . '/media/fef';

		if (!JFolder::exists($source . '/fef'))
		{
			// WTF? I can't find myself. I can't install anything.
			return false;
		}

		// If FEF is not really installed (someone removed the directory instead of uninstalling?) I have to install it.
		if (!JFolder::exists($target))
		{
			return true;
		}

		$fefVersion = array();

		if (JFile::exists($target . '/version.txt'))
		{
			$rawData = @file_get_contents($target . '/version.txt');
			$rawData = ($rawData === false) ? "0.0.0\n2011-01-01\n" : $rawData;
			$info = explode("\n", $rawData);
			$fefVersion['installed'] = array(
				'version' => trim($info[0]),
				'date'    => new JDate(trim($info[1]))
			);
		}
		else
		{
			$fefVersion['installed'] = array(
				'version' => '0.0',
				'date'    => new JDate('2011-01-01')
			);
		}

		$rawData = @file_get_contents($source . '/version.txt');
		$rawData = ($rawData === false) ? "0.0.0\n2011-01-01\n" : $rawData;
		$info = explode("\n", $rawData);
		$fefVersion['package'] = array(
			'version' => trim($info[0]),
			'date'    => new JDate(trim($info[1]))
		);

		$haveToInstallFEF = $fefVersion['package']['date']->toUNIX() >= $fefVersion['installed']['date']->toUNIX();

		return $haveToInstallFEF;
	}

	/**
	 * Loads FOF 3.0 if it's not already loaded
	 */
	protected function loadFOF30()
	{
		// Load FOF if not already loaded
		if (!defined($this->fofDefine))
		{
			$filePath = JPATH_LIBRARIES . '/' . $this->fofLibrariesPath . '/include.php';

			if (!defined($this->fofDefine) && file_exists($filePath))
			{
				@include_once $filePath;
			}
		}
	}

	/**
	 * Get the dependencies for a package from the #__akeeba_common table
	 *
	 * @param   string  $package  The package
	 *
	 * @return  array  The dependencies
	 */
	protected function getDependencies($package)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('value'))
			->from($db->qn('#__akeeba_common'))
			->where($db->qn('key') . ' = ' . $db->q($package));

		try
		{
			$dependencies = $db->setQuery($query)->loadResult();
			$dependencies = json_decode($dependencies, true);

			if (empty($dependencies))
			{
				$dependencies = array();
			}
		}
		catch (Exception $e)
		{
			$dependencies = array();
		}

		return $dependencies;
	}

	/**
	 * Sets the dependencies for a package into the #__akeeba_common table
	 *
	 * @param   string  $package       The package
	 * @param   array   $dependencies  The dependencies list
	 */
	protected function setDependencies($package, array $dependencies)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->delete('#__akeeba_common')
			->where($db->qn('key') . ' = ' . $db->q($package));

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			// Do nothing if the old key wasn't found
		}

		$object = (object)array(
			'key' => $package,
			'value' => json_encode($dependencies)
		);

		try
		{
			$db->insertObject('#__akeeba_common', $object, 'key');
		}
		catch (Exception $e)
		{
			// Do nothing if the old key wasn't found
		}
	}

	/**
	 * Adds a package dependency to #__akeeba_common
	 *
	 * @param   string  $package     The package
	 * @param   string  $dependency  The dependency to add
	 */
	protected function addDependency($package, $dependency)
	{
		$dependencies = $this->getDependencies($package);

		if (!in_array($dependency, $dependencies))
		{
			$dependencies[] = $dependency;

			$this->setDependencies($package, $dependencies);
		}
	}

	/**
	 * Removes a package dependency from #__akeeba_common
	 *
	 * @param   string  $package     The package
	 * @param   string  $dependency  The dependency to remove
	 */
	protected function removeDependency($package, $dependency)
	{
		$dependencies = $this->getDependencies($package);

		if (in_array($dependency, $dependencies))
		{
			$index = array_search($dependency, $dependencies);
			unset($dependencies[$index]);

			$this->setDependencies($package, $dependencies);
		}
	}

	/**
	 * Do I have a dependency for a package in #__akeeba_common
	 *
	 * @param   string  $package     The package
	 * @param   string  $dependency  The dependency to check for
	 *
	 * @return bool
	 */
	protected function hasDependency($package, $dependency)
	{
		$dependencies = $this->getDependencies($package);

		return in_array($dependency, $dependencies);
	}
}

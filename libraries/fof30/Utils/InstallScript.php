<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Utils;

use FOF30\Database\Installer;
use Exception;
use JFactory;
use JFile;
use JFolder;
use JInstaller;
use JLoader;
use JLog;

defined('_JEXEC') or die;

JLoader::import('joomla.filesystem.folder');
JLoader::import('joomla.filesystem.file');
JLoader::import('joomla.installer.installer');
JLoader::import('joomla.utilities.date');

/**
 * A helper class which you can use to create component installation scripts
 */
class InstallScript
{
	/**
	 * The component's name
	 *
	 * @var   string
	 */
	protected $componentName = 'com_foobar';

	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 *
	 * @var string
	 */
	protected $componentTitle = 'Foobar Component';

	/**
	 * The list of obsolete extra modules and plugins to uninstall on component upgrade / installation.
	 *
	 * @var array
	 */
	protected $uninstallation_queue = array(
		// modules => { (folder) => { (module) }* }*
		'modules' => array(
			'admin' => array(),
			'site'  => array()
		),
		// plugins => { (folder) => { (element) }* }*
		'plugins' => array(
			'system' => array(),
		)
	);

	/**
	 * Obsolete files and folders to remove from the free version only. This is used when you move a feature from the
	 * free version of your extension to its paid version. If you don't have such a distinction you can ignore this.
	 *
	 * @var   array
	 */
	protected $removeFilesFree = array(
		'files'   => array(
			// Use pathnames relative to your site's root, e.g.
			// 'administrator/components/com_foobar/helpers/whatever.php'
		),
		'folders' => array(
			// Use pathnames relative to your site's root, e.g.
			// 'administrator/components/com_foobar/baz'
		)
	);

	/**
	 * Obsolete files and folders to remove from both paid and free releases. This is used when you refactor code and
	 * some files inevitably become obsolete and need to be removed.
	 *
	 * @var   array
	 */
	protected $removeFilesAllVersions = array(
		'files'   => array(
			// Use pathnames relative to your site's root, e.g.
			// 'administrator/components/com_foobar/helpers/whatever.php'
		),
		'folders' => array(
			// Use pathnames relative to your site's root, e.g.
			// 'administrator/components/com_foobar/baz'
		)
	);

	/**
	 * A list of scripts to be copied to the "cli" directory of the site
	 *
	 * @var   array
	 */
	protected $cliScriptFiles = array(
		// Use just the filename, e.g.
		// 'my-cron-script.php'
	);

	/**
	 * The path inside your package where cli scripts are stored
	 *
	 * @var   string
	 */
	protected $cliSourcePath = 'cli';

	/**
	 * Is the schemaXmlPath class variable a relative path? If set to true the schemaXmlPath variable contains a path
	 * relative to the component's back-end directory. If set to false the schemaXmlPath variable contains an absolute
	 * filesystem path.
	 *
	 * @var   boolean
	 */
	protected $schemaXmlPathRelative = true;

	/**
	 * The path where the schema XML files are stored. Its contents depend on the schemaXmlPathRelative variable above
	 * true        => schemaXmlPath contains a path relative to the component's back-end directory
	 * false    => schemaXmlPath contains an absolute filesystem path
	 *
	 * @var string
	 */
	protected $schemaXmlPath = 'sql/xml';

	/**
	 * The minimum PHP version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumPHPVersion = '5.3.4';

	/**
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumJoomlaVersion = '3.2.1';

	/**
	 * The maximum Joomla! version this extension can be installed on
	 *
	 * @var   string
	 */
	protected $maximumJoomlaVersion = '3.9.99';

	/**
	 * Is this the paid version of the extension? This only determines which files / extensions will be removed.
	 *
	 * @var   boolean
	 */
	protected $isPaid = false;

	/**
	 * Post-installation message definitions for Joomla! 3.2 or later.
	 *
	 * This array contains the message definitions for the Post-installation Messages component added in Joomla! 3.2 and
	 * later versions. Each element is also a hashed array. For the keys used in these message definitions please
	 * @see InstallScript::addPostInstallationMessage
	 *
	 * @var array
	 */
	protected $postInstallationMessages = array();

	/**
	 * Joomla! pre-flight event. This runs before Joomla! installs or updates the component. This is our last chance to
	 * tell Joomla! if it should abort the installation.
	 *
	 * @param   string                       $type    Installation type (install, update, discover_install)
	 * @param   \JInstallerAdapterComponent  $parent  Parent object
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
				$msg = "<p>You need PHP $this->minimumPHPVersion or later to install this component</p>";

				JLog::add($msg, JLog::WARNING, 'jerror');

				return false;
			}
		}

		// Check the minimum Joomla! version
		if (!empty($this->minimumJoomlaVersion) && !version_compare(JVERSION, $this->minimumJoomlaVersion, 'ge'))
		{
			$msg = "<p>You need Joomla! $this->minimumJoomlaVersion or later to install this component</p>";

			JLog::add($msg, JLog::WARNING, 'jerror');

			return false;
		}

		// Check the maximum Joomla! version
		if (!empty($this->maximumJoomlaVersion) && !version_compare(JVERSION, $this->maximumJoomlaVersion, 'le'))
		{
			$msg = "<p>You need Joomla! $this->maximumJoomlaVersion or earlier to install this component</p>";

			JLog::add($msg, JLog::WARNING, 'jerror');

			return false;
		}

		// Always reset the OPcache if it's enabled. Otherwise there's a good chance the server will not know we are
		// replacing .php scripts. This is a major concern since PHP 5.5 included and enabled OPcache by default.
		if (function_exists('opcache_reset'))
		{
			opcache_reset();
		}
		// Also do that for APC cache
		elseif (function_exists('apc_clear_cache'))
		{
			@apc_clear_cache();
		}

		// Workarounds for JInstaller issues.
		if (in_array($type, array('install', 'discover_install')))
		{
			// Bugfix for "Database function returned no error"
			$this->bugfixDBFunctionReturnedNoError();
		}
		else
		{
			// Bugfix for "Can not build admin menus"
			$this->bugfixCantBuildAdminMenus();
		}

		return true;
	}

	/**
	 * Runs after install, update or discover_update. In other words, it executes after Joomla! has finished installing
	 * or updating your component. This is the last chance you've got to perform any additional installations, clean-up,
	 * database updates and similar housekeeping functions.
	 *
	 * @param   string                       $type   install, update or discover_update
	 * @param   \JInstallerAdapterComponent  $parent Parent object
	 */
	public function postflight($type, $parent)
	{
		// Add ourselves to the list of extensions depending on FOF30
		$this->addDependency('fof30', $this->componentName);

		// Install or update database
		// Uninstall database
		$dbInstaller = new Installer(JFactory::getDbo(),
			($this->schemaXmlPathRelative ? JPATH_ADMINISTRATOR . '/components/' . $this->componentName : '') . '/' .
			$this->schemaXmlPath
		);
		$dbInstaller->updateSchema();

		// Make sure menu items are installed
		$this->_createAdminMenus($parent);

		// Make sure menu items are published (surprise goal in the 92' by JInstaller wins the cup for "most screwed up
		// bug in the history of Joomla!")
		$this->_reallyPublishAdminMenuItems($parent);

		// Which files should I remove?
		if ($this->isPaid)
		{
			// This is the paid version, only remove the removeFilesAllVersions files
			$removeFiles = $this->removeFilesAllVersions;
		}
		else
		{
			// This is the free version, remove the removeFilesAllVersions and removeFilesFree files
			$removeFiles = array('files' => array(), 'folders' => array());

			if (isset($this->removeFilesAllVersions['files']))
			{
				if (isset($this->removeFilesFree['files']))
				{
					$removeFiles['files'] = array_merge($this->removeFilesAllVersions['files'], $this->removeFilesFree['files']);
				}
				else
				{
					$removeFiles['files'] = $this->removeFilesAllVersions['files'];
				}
			}
			elseif (isset($this->removeFilesFree['files']))
			{
				$removeFiles['files'] = $this->removeFilesFree['files'];
			}

			if (isset($this->removeFilesAllVersions['folders']))
			{
				if (isset($this->removeFilesFree['folders']))
				{
					$removeFiles['folders'] = array_merge($this->removeFilesAllVersions['folders'], $this->removeFilesFree['folders']);
				}
				else
				{
					$removeFiles['folders'] = $this->removeFilesAllVersions['folders'];
				}
			}
			elseif (isset($this->removeFilesFree['folders']))
			{
				$removeFiles['folders'] = $this->removeFilesFree['folders'];
			}
		}

		// Remove obsolete files and folders
		$this->removeFilesAndFolders($removeFiles);

		// Copy the CLI files (if any)
		$this->copyCliFiles($parent);

		// Show the post-installation page
		$this->renderPostInstallation($parent);

		// Uninstall obsolete subextensions
		$this->uninstallObsoleteSubextensions($parent);

		// Clear the FOF cache
		$false = false;
		$cache = \JFactory::getCache('fof', '');
		$cache->store($false, 'cache', 'fof');

		// Make sure the Joomla! menu structure is correct
		$this->_rebuildMenu();

		// Add post-installation messages on Joomla! 3.2 and later
		$this->_applyPostInstallationMessages();
	}

	/**
	 * Runs on uninstallation
	 *
	 * @param   \JInstallerAdapterComponent  $parent  The parent object
	 */
	public function uninstall($parent)
	{
		// Uninstall database
		$dbInstaller = new Installer(JFactory::getDbo(),
			($this->schemaXmlPathRelative ? JPATH_ADMINISTRATOR . '/components/' . $this->componentName : '') . '/' .
			$this->schemaXmlPath
		);

		$dbInstaller->removeSchema();

		// Uninstall post-installation messages on Joomla! 3.2 and later
		$this->uninstallPostInstallationMessages();

		// Remove ourselves from the list of extensions depending on FOF30
		$this->removeDependency('fof30', $this->componentName);

		// Show the post-uninstallation page
		$this->renderPostUninstallation($parent);
	}

	/**
	 * Copies the CLI scripts into Joomla!'s cli directory
	 *
	 * @param   \JInstallerAdapterComponent  $parent
	 */
	protected function copyCliFiles($parent)
	{
		$src = $parent->getParent()->getPath('source');

		foreach ($this->cliScriptFiles as $script)
		{
			if (is_file(JPATH_ROOT . '/cli/' . $script))
			{
				JFile::delete(JPATH_ROOT . '/cli/' . $script);
			}

			if (is_file($src . '/' . $this->cliSourcePath . '/' . $script))
			{
				JFile::copy($src . '/' . $this->cliSourcePath . '/' . $script, JPATH_ROOT . '/cli/' . $script);
			}
		}
	}

	/**
	 * Override this method to display a custom component installation message if you so wish
	 *
	 * @param  \JInstallerAdapterComponent  $parent  Parent class calling us
	 */
	protected function renderPostInstallation($parent)
	{
	}

	/**
	 * Override this method to display a custom component uninstallation message if you so wish
	 *
	 * @param  \JInstallerAdapterComponent  $parent  Parent class calling us
	 */
	protected function renderPostUninstallation($parent)
	{
	}

	/**
	 * Bugfix for "DB function returned no error"
	 */
	protected function bugfixDBFunctionReturnedNoError()
	{
		$db = JFactory::getDbo();

		// Fix broken #__assets records
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__assets')
			->where($db->qn('name') . ' = ' . $db->q($this->componentName));
		$db->setQuery($query);

		try
		{
			$ids = $db->loadColumn();
		}
		catch (Exception $exc)
		{
			return;
		}

		if (!empty($ids))
		{
			foreach ($ids as $id)
			{
				$query = $db->getQuery(true);
				$query->delete('#__assets')
					->where($db->qn('id') . ' = ' . $db->q($id));
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $exc)
				{
					// Nothing
				}
			}
		}

		// Fix broken #__extensions records
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q($this->componentName));
		$db->setQuery($query);
		$ids = $db->loadColumn();

		if (!empty($ids))
		{
			foreach ($ids as $id)
			{
				$query = $db->getQuery(true);
				$query->delete('#__extensions')
					->where($db->qn('extension_id') . ' = ' . $db->q($id));
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $exc)
				{
					// Nothing
				}
			}
		}

		// Fix broken #__menu records
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__menu')
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('menutype') . ' = ' . $db->q('main'))
			->where($db->qn('link') . ' LIKE ' . $db->q('index.php?option=' . $this->componentName));
		$db->setQuery($query);
		$ids = $db->loadColumn();

		if (!empty($ids))
		{
			foreach ($ids as $id)
			{
				$query = $db->getQuery(true);
				$query->delete('#__menu')
					->where($db->qn('id') . ' = ' . $db->q($id));
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $exc)
				{
					// Nothing
				}
			}
		}
	}

	/**
	 * Joomla! 1.6+ bugfix for "Can not build admin menus"
	 */
	protected function bugfixCantBuildAdminMenus()
	{
		$db = JFactory::getDbo();

		// If there are multiple #__extensions record, keep one of them
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q($this->componentName));
		$db->setQuery($query);

		try
		{
			$ids = $db->loadColumn();
		}
		catch (Exception $exc)
		{
			return;
		}


		if (count($ids) > 1)
		{
			asort($ids);
			$extension_id = array_shift($ids); // Keep the oldest id

			foreach ($ids as $id)
			{
				$query = $db->getQuery(true);
				$query->delete('#__extensions')
					->where($db->qn('extension_id') . ' = ' . $db->q($id));
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $exc)
				{
					// Nothing
				}
			}
		}

		// If there are multiple assets records, delete all except the oldest one
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__assets')
			->where($db->qn('name') . ' = ' . $db->q($this->componentName));
		$db->setQuery($query);
		$ids = $db->loadObjectList();

		if (count($ids) > 1)
		{
			asort($ids);
			$asset_id = array_shift($ids); // Keep the oldest id

			foreach ($ids as $id)
			{
				$query = $db->getQuery(true);
				$query->delete('#__assets')
					->where($db->qn('id') . ' = ' . $db->q($id));
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $exc)
				{
					// Nothing
				}
			}
		}

		// Remove #__menu records for good measure! –– I think this is not necessary and causes the menu item to
		// disappear on extension update.
		/**
		$query = $db->getQuery(true);
		$query->select('id')
		->from('#__menu')
		->where($db->qn('type') . ' = ' . $db->q('component'))
		->where($db->qn('menutype') . ' = ' . $db->q('main'))
		->where($db->qn('link') . ' LIKE ' . $db->q('index.php?option=' . $this->componentName));
		$db->setQuery($query);

		try
		{
		$ids1 = $db->loadColumn();
		}
		catch (Exception $exc)
		{
		$ids1 = array();
		}

		if (empty($ids1))
		{
		$ids1 = array();
		}

		$query = $db->getQuery(true);
		$query->select('id')
		->from('#__menu')
		->where($db->qn('type') . ' = ' . $db->q('component'))
		->where($db->qn('menutype') . ' = ' . $db->q('main'))
		->where($db->qn('link') . ' LIKE ' . $db->q('index.php?option=' . $this->componentName . '&%'));
		$db->setQuery($query);

		try
		{
		$ids2 = $db->loadColumn();
		}
		catch (Exception $exc)
		{
		$ids2 = array();
		}

		if (empty($ids2))
		{
		$ids2 = array();
		}

		$ids = array_merge($ids1, $ids2);

		if (!empty($ids))
		{
		foreach ($ids as $id)
		{
		$query = $db->getQuery(true);
		$query->delete('#__menu')
		->where($db->qn('id') . ' = ' . $db->q($id));
		$db->setQuery($query);

		try
		{
		$db->execute();
		}
		catch (Exception $exc)
		{
		// Nothing
		}
		}
		}
		/**/
	}

	/**
	 * Removes obsolete files and folders
	 *
	 * @param   array $removeList The files and directories to remove
	 */
	protected function removeFilesAndFolders($removeList)
	{
		// Remove files
		if (isset($removeList['files']) && !empty($removeList['files']))
		{
			foreach ($removeList['files'] as $file)
			{
				$f = JPATH_ROOT . '/' . $file;

				if (!is_file($f))
				{
					continue;
				}

				JFile::delete($f);
			}
		}

		// Remove folders
		if (isset($removeList['folders']) && !empty($removeList['folders']))
		{
			foreach ($removeList['folders'] as $folder)
			{
				$f = JPATH_ROOT . '/' . $folder;

				if (!is_dir($f))
				{
					continue;
				}

				JFolder::delete($f);
			}
		}
	}

	/**
	 * Uninstalls obsolete subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param   \JInstallerAdapterComponent $parent The parent object
	 *
	 * @return  \stdClass The subextension uninstallation status
	 */
	protected function uninstallObsoleteSubextensions($parent)
	{
		JLoader::import('joomla.installer.installer');

		$db = JFactory::getDBO();

		$status = new \stdClass();
		$status->modules = array();
		$status->plugins = array();

		// Modules uninstallation
		if (isset($this->uninstallation_queue['modules']) && count($this->uninstallation_queue['modules']))
		{
			foreach ($this->uninstallation_queue['modules'] as $folder => $modules)
			{
				if (count($modules))
				{
					foreach ($modules as $module)
					{
						// Find the module ID
						$sql = $db->getQuery(true)
							->select($db->qn('extension_id'))
							->from($db->qn('#__extensions'))
							->where($db->qn('element') . ' = ' . $db->q('mod_' . $module))
							->where($db->qn('type') . ' = ' . $db->q('module'));
						$db->setQuery($sql);
						$id = $db->loadResult();
						// Uninstall the module
						if ($id)
						{
							$installer = new JInstaller;
							$result = $installer->uninstall('module', $id, 1);
							$status->modules[] = array(
								'name'   => 'mod_' . $module,
								'client' => $folder,
								'result' => $result
							);
						}
					}
				}
			}
		}

		// Plugins uninstallation
		if (isset($this->uninstallation_queue['plugins']) && count($this->uninstallation_queue['plugins']))
		{
			foreach ($this->uninstallation_queue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin)
					{
						$sql = $db->getQuery(true)
							->select($db->qn('extension_id'))
							->from($db->qn('#__extensions'))
							->where($db->qn('type') . ' = ' . $db->q('plugin'))
							->where($db->qn('element') . ' = ' . $db->q($plugin))
							->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($sql);

						$id = $db->loadResult();
						if ($id)
						{
							$installer = new JInstaller;
							$result = $installer->uninstall('plugin', $id, 1);
							$status->plugins[] = array(
								'name'   => 'plg_' . $plugin,
								'group'  => $folder,
								'result' => $result
							);
						}
					}
				}
			}
		}

		return $status;
	}

	/**
	 * @param \JInstallerAdapterComponent $parent
	 *
	 * @return bool
	 *
	 * @throws Exception When the Joomla! menu is FUBAR
	 */
	private function _createAdminMenus($parent)
	{
		$db = $parent->getParent()->getDbo();
		/** @var \JTableMenu $table */
		$table = \JTable::getInstance('menu');
		$option = $parent->get('element');

		// If a component exists with this option in the table then we don't need to add menus
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__menu') . ' AS ' . $db->qn('m'))
			->leftJoin($db->qn('#__extensions', 'e') . ' ON ' .
				$db->qn('m.component_id') . ' = ' . $db->qn('e.extension_id'))
			->where($db->qn('m.parent_id') . ' = ' . $db->q(1))
			->where($db->qn('m.client_id') . ' = ' . $db->q(1))
			->where($db->qn('e.type') . ' = ' . $db->q('component'))
			->where($db->qn('e.element') . ' = ' . $db->q($option));

		$db->setQuery($query);

		$existingMenus = $db->loadResult();

		if ($existingMenus)
		{
			return true;
		}

		// Let's find the extension id
		$query->clear()
			->select($db->qn('e.extension_id'))
			->from($db->qn('#__extensions', 'e'))
			->where($db->qn('e.type') . ' = ' . $db->q('component'))
			->where($db->qn('e.element') . ' = ' . $db->q($option));
		$db->setQuery($query);
		$componentId = $db->loadResult();

		// Ok, now its time to handle the menus.  Start with the component root menu, then handle submenus.
		$menuElement = $parent->get('manifest')->administration->menu;

		// We need to insert the menu item as the last child of Joomla!'s menu root node. First let's make sure that
		// it exists. Normally it should be the menu item with ID = 1.
		$query = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn('#__menu'))
			->where($db->qn('id') . ' = ' . $db->q(1));
		$rootItemId = $db->setQuery($query)->loadResult();

		// If we didn't find the item with ID=1 something has screwed up the menu table, e.g. a bad upgrade script. In
		// this case we can try to find the root node by title.
		if (is_null($rootItemId))
		{
			$rootItemId = null;
			$query = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->where($db->qn('title') . ' = ' . $db->q('Menu_Item_Root'));
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		// So, someone changed the title of the menu item too?! Let's find it by alias.
		if (is_null($rootItemId))
		{
			$rootItemId = null;
			$query = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->where($db->qn('alias') . ' = ' . $db->q('root'));
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		// For crying out loud, they changed the alias too? Fine! Find it by component ID.
		if (is_null($rootItemId))
		{
			$rootItemId = null;
			$query = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->where($db->qn('component_id') . ' = ' . $db->q('0'));
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		// Um, OK. Still no go. Let's try with minimum lft value.
		if (is_null($rootItemId))
		{
			$rootItemId = null;
			$query = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->order($db->qn('lft') . ' ASC');
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		// I quit. Your site's menu structure is broken. I'll just throw an error.
		if (is_null($rootItemId))
		{
			throw new Exception("Your site is broken. There is no root menu item. As a result it is impossible to create menu items. The installation of this component has failed. Please fix your database and retry!", 500);
		}

		/** @var \SimpleXMLElement $menuElement */
		if ($menuElement)
		{
			$data = array();
			$data['menutype'] = 'main';
			$data['client_id'] = 1;
			$data['title'] = (string)trim($menuElement);
			$data['alias'] = (string)$menuElement;
			$data['link'] = 'index.php?option=' . $option;
			$data['type'] = 'component';
			$data['published'] = 0;
			$data['parent_id'] = 1;
			$data['component_id'] = $componentId;
			$data['img'] = ((string)$menuElement->attributes()->img) ? (string)$menuElement->attributes()->img : 'class:component';
			$data['home'] = 0;
			$data['path'] = '';
			$data['params'] = '';
		}
		// No menu element was specified, Let's make a generic menu item
		else
		{
			$data = array();
			$data['menutype'] = 'main';
			$data['client_id'] = 1;
			$data['title'] = $option;
			$data['alias'] = $option;
			$data['link'] = 'index.php?option=' . $option;
			$data['type'] = 'component';
			$data['published'] = 0;
			$data['parent_id'] = 1;
			$data['component_id'] = $componentId;
			$data['img'] = 'class:component';
			$data['home'] = 0;
			$data['path'] = '';
			$data['params'] = '';
		}

		try
		{
			$table->setLocation($rootItemId, 'last-child');
		}
		catch (\InvalidArgumentException $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		if (!$table->bind($data) || !$table->check() || !$table->store())
		{
			// The menu item already exists. Delete it and retry instead of throwing an error.
			$query->clear()
				->select('id')
				->from('#__menu')
				->where('menutype = ' . $db->quote('main'))
				->where('client_id = 1')
				->where('link = ' . $db->quote('index.php?option=' . $option))
				->where('type = ' . $db->quote('component'))
				->where('parent_id = 1')
				->where('home = 0');

			$db->setQuery($query);
			$menu_ids_level1 = $db->loadColumn();

			if (empty($menu_ids_level1))
			{
				// Oops! Could not get the menu ID. Go back and rollback changes.
				\JError::raiseWarning(1, $table->getError());

				return false;
			}
			else
			{
				$ids = implode(',', $menu_ids_level1);

				$query->clear()
					->select('id')
					->from('#__menu')
					->where('menutype = ' . $db->quote('main'))
					->where('client_id = 1')
					->where('type = ' . $db->quote('component'))
					->where('parent_id in (' . $ids . ')')
					->where('level = 2')
					->where('home = 0');

				$db->setQuery($query);
				$menu_ids_level2 = $db->loadColumn();

				$ids = implode(',', array_merge($menu_ids_level1, $menu_ids_level2));

				// Remove the old menu item
				$query->clear()
					->delete('#__menu')
					->where('id in (' . $ids . ')');

				$db->setQuery($query);
				$db->execute();

				// Retry creating the menu item
				$table->setLocation($rootItemId, 'last-child');

				if (!$table->bind($data) || !$table->check() || !$table->store())
				{
					// Install failed, warn user and rollback changes
					\JError::raiseWarning(1, $table->getError());

					return false;
				}
			}
		}

		/*
		 * Since we have created a menu item, we add it to the installation step stack
		 * so that if we have to rollback the changes we can undo it.
		 */
		$parent->getParent()->pushStep(array('type' => 'menu', 'id' => $componentId));

		/*
		 * Process SubMenus
		 */

		if (!$parent->get('manifest')->administration->submenu)
		{
			return true;
		}

		$parent_id = $table->id;

		/** @var \SimpleXMLElement $child */
		foreach ($parent->get('manifest')->administration->submenu->menu as $child)
		{
			$data = array();
			$data['menutype'] = 'main';
			$data['client_id'] = 1;
			$data['title'] = (string)trim($child);
			$data['alias'] = (string)$child;
			$data['type'] = 'component';
			$data['published'] = 0;
			$data['parent_id'] = $parent_id;
			$data['component_id'] = $componentId;
			$data['img'] = ((string)$child->attributes()->img) ? (string)$child->attributes()->img : 'class:component';
			$data['home'] = 0;

			// Set the sub menu link
			if ((string)$child->attributes()->link)
			{
				$data['link'] = 'index.php?' . $child->attributes()->link;
			}
			else
			{
				$request = array();

				if ((string)$child->attributes()->act)
				{
					$request[] = 'act=' . $child->attributes()->act;
				}

				if ((string)$child->attributes()->task)
				{
					$request[] = 'task=' . $child->attributes()->task;
				}

				if ((string)$child->attributes()->controller)
				{
					$request[] = 'controller=' . $child->attributes()->controller;
				}

				if ((string)$child->attributes()->view)
				{
					$request[] = 'view=' . $child->attributes()->view;
				}

				if ((string)$child->attributes()->layout)
				{
					$request[] = 'layout=' . $child->attributes()->layout;
				}

				if ((string)$child->attributes()->sub)
				{
					$request[] = 'sub=' . $child->attributes()->sub;
				}

				$qstring = (count($request)) ? '&' . implode('&', $request) : '';
				$data['link'] = 'index.php?option=' . $option . $qstring;
			}

			$table = \JTable::getInstance('menu');

			try
			{
				$table->setLocation($parent_id, 'last-child');
			}
			catch (\InvalidArgumentException $e)
			{
				return false;
			}

			if (!$table->bind($data) || !$table->check() || !$table->store())
			{
				// Install failed, rollback changes
				return false;
			}

			/*
			 * Since we have created a menu item, we add it to the installation step stack
			 * so that if we have to rollback the changes we can undo it.
			 */
			$parent->getParent()->pushStep(array('type' => 'menu', 'id' => $componentId));
		}

		return true;
	}

	/**
	 * Make sure the Component menu items are really published!
	 *
	 * @param \JInstallerAdapterComponent $parent
	 *
	 * @return bool
	 */
	private function _reallyPublishAdminMenuItems($parent)
	{
		$db = $parent->getParent()->getDbo();
		$option = $parent->get('element');

		$query = $db->getQuery(true)
			->update('#__menu AS m')
			->join('LEFT', '#__extensions AS e ON m.component_id = e.extension_id')
			->set($db->qn('published') . ' = ' . $db->q(1))
			->where('m.parent_id = 1')
			->where('m.client_id = 1')
			->where('e.type = ' . $db->quote('component'))
			->where('e.element = ' . $db->quote($option));

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			// If it fails, it fails. Who cares.
		}
	}

	/**
	 * Tells Joomla! to rebuild its menu structure to make triple-sure that the Components menu items really do exist
	 * in the correct place and can really be rendered.
	 */
	private function _rebuildMenu()
	{
		/** @var \JTableMenu $table */
		$table = \JTable::getInstance('menu');
		$db = $table->getDbo();

		// We need to rebuild the menu based on its root item. By default this is the menu item with ID=1. However, some
		// crappy upgrade scripts enjoy screwing it up. Hey, ho, the workaround way I go.
		$query = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn('#__menu'))
			->where($db->qn('id') . ' = ' . $db->q(1));
		$rootItemId = $db->setQuery($query)->loadResult();

		if (is_null($rootItemId))
		{
			// Guess what? The Problem has happened. Let's find the root node by title.
			$rootItemId = null;
			$query = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->where($db->qn('title') . ' = ' . $db->q('Menu_Item_Root'));
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		if (is_null($rootItemId))
		{
			// For crying out loud, did that idiot changed the title too?! Let's find it by alias.
			$rootItemId = null;
			$query = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->where($db->qn('alias') . ' = ' . $db->q('root'));
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		if (is_null($rootItemId))
		{
			// Dude. Dude! Duuuuuuude! The alias is screwed up, too?! Find it by component ID.
			$rootItemId = null;
			$query = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->where($db->qn('component_id') . ' = ' . $db->q('0'));
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		if (is_null($rootItemId))
		{
			// Your site is more of a "shite" than a "site". Let's try with minimum lft value.
			$rootItemId = null;
			$query = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->order($db->qn('lft') . ' ASC');
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		if (is_null($rootItemId))
		{
			// I quit. Your site is broken.
			return false;
		}

		$table->rebuild($rootItemId);
	}

	/**
	 * Adds or updates a post-installation message (PIM) definition for Joomla! 3.2 or later. You can use this in your
	 * post-installation script using this code:
	 *
	 * The $options array contains the following mandatory keys:
	 *
	 * extension_id        The numeric ID of the extension this message is for (see the #__extensions table)
	 *
	 * type                One of message, link or action. Their meaning is:
	 *                    message        Informative message. The user can dismiss it.
	 *                    link        The action button links to a URL. The URL is defined in the action parameter.
	 *                  action      A PHP action takes place when the action button is clicked. You need to specify the
	 *                              action_file (RAD path to the PHP file) and action (PHP function name) keys. See
	 *                              below for more information.
	 *
	 * title_key        The JText language key for the title of this PIM
	 *                    Example: COM_FOOBAR_POSTINSTALL_MESSAGEONE_TITLE
	 *
	 * description_key    The JText language key for the main body (description) of this PIM
	 *                    Example: COM_FOOBAR_POSTINSTALL_MESSAGEONE_DESCRIPTION
	 *
	 * action_key        The JText language key for the action button. Ignored and not required when type=message
	 *                    Example: COM_FOOBAR_POSTINSTALL_MESSAGEONE_ACTION
	 *
	 * language_extension    The extension name which holds the language keys used above. For example, com_foobar,
	 *                    mod_something, plg_system_whatever, tpl_mytemplate
	 *
	 * language_client_id   Should we load the front-end (0) or back-end (1) language keys?
	 *
	 * version_introduced   Which was the version of your extension where this message appeared for the first time?
	 *                        Example: 3.2.1
	 *
	 * enabled              Must be 1 for this message to be enabled. If you omit it, it defaults to 1.
	 *
	 * condition_file        The RAD path to a PHP file containing a PHP function which determines whether this message
	 *                        should be shown to the user. @see Template::parsePath() for RAD path format. Joomla!
	 *                        will include this file before calling the condition_method.
	 *                      Example:   admin://components/com_foobar/helpers/postinstall.php
	 *
	 * condition_method     The name of a PHP function which will be used to determine whether to show this message to
	 *                      the user. This must be a simple PHP user function (not a class method, static method etc)
	 *                        which returns true to show the message and false to hide it. This function is defined in the
	 *                        condition_file.
	 *                        Example: com_foobar_postinstall_messageone_condition
	 *
	 * When type=message no additional keys are required.
	 *
	 * When type=link the following additional keys are required:
	 *
	 * action                The URL which will open when the user clicks on the PIM's action button
	 *                        Example:    index.php?option=com_foobar&view=tools&task=installSampleData
	 *
	 * Then type=action the following additional keys are required:
	 *
	 * action_file            The RAD path to a PHP file containing a PHP function which performs the action of this PIM.
	 *
	 * @see                   Template::parsePath() for RAD path format. Joomla! will include this file
	 *                        before calling the function defined in the action key below.
	 *                        Example:   admin://components/com_foobar/helpers/postinstall.php
	 *
	 * action                The name of a PHP function which will be used to run the action of this PIM. This must be a
	 *                      simple PHP user function (not a class method, static method etc) which returns no result.
	 *                        Example: com_foobar_postinstall_messageone_action
	 *
	 * @param array $options See description
	 *
	 * @return  void
	 *
	 * @throws Exception
	 */
	protected function addPostInstallationMessage(array $options)
	{
		// Make sure there are options set
		if (!is_array($options))
		{
			throw new Exception('Post-installation message definitions must be of type array', 500);
		}

		// Initialise array keys
		$defaultOptions = array(
			'extension_id'       => '',
			'type'               => '',
			'title_key'          => '',
			'description_key'    => '',
			'action_key'         => '',
			'language_extension' => '',
			'language_client_id' => '',
			'action_file'        => '',
			'action'             => '',
			'condition_file'     => '',
			'condition_method'   => '',
			'version_introduced' => '',
			'enabled'            => '1',
		);

		$options = array_merge($defaultOptions, $options);

		// Array normalisation. Removes array keys not belonging to a definition.
		$defaultKeys = array_keys($defaultOptions);
		$allKeys = array_keys($options);
		$extraKeys = array_diff($allKeys, $defaultKeys);

		if (!empty($extraKeys))
		{
			foreach ($extraKeys as $key)
			{
				unset($options[$key]);
			}
		}

		// Normalisation of integer values
		$options['extension_id'] = (int)$options['extension_id'];
		$options['language_client_id'] = (int)$options['language_client_id'];
		$options['enabled'] = (int)$options['enabled'];

		// Normalisation of 0/1 values
		foreach (array('language_client_id', 'enabled') as $key)
		{
			$options[$key] = $options[$key] ? 1 : 0;
		}

		// Make sure there's an extension_id
		if (!(int)$options['extension_id'])
		{
			throw new Exception('Post-installation message definitions need an extension_id', 500);
		}

		// Make sure there's a valid type
		if (!in_array($options['type'], array('message', 'link', 'action')))
		{
			throw new Exception('Post-installation message definitions need to declare a type of message, link or action', 500);
		}

		// Make sure there's a title key
		if (empty($options['title_key']))
		{
			throw new Exception('Post-installation message definitions need a title key', 500);
		}

		// Make sure there's a description key
		if (empty($options['description_key']))
		{
			throw new Exception('Post-installation message definitions need a description key', 500);
		}

		// If the type is anything other than message you need an action key
		if (($options['type'] != 'message') && empty($options['action_key']))
		{
			throw new Exception('Post-installation message definitions need an action key when they are of type "' . $options['type'] . '"', 500);
		}

		// You must specify the language extension
		if (empty($options['language_extension']))
		{
			throw new Exception('Post-installation message definitions need to specify which extension contains their language keys', 500);
		}

		// The action file and method are only required for the "action" type
		if ($options['type'] == 'action')
		{
			if (empty($options['action_file']))
			{
				throw new Exception('Post-installation message definitions need an action file when they are of type "action"', 500);
			}

			$file_path = \FOFTemplateUtils::parsePath($options['action_file'], true);

			if (!@is_file($file_path))
			{
				throw new Exception('The action file ' . $options['action_file'] . ' of your post-installation message definition does not exist', 500);
			}

			if (empty($options['action']))
			{
				throw new Exception('Post-installation message definitions need an action (function name) when they are of type "action"', 500);
			}
		}

		if ($options['type'] == 'link')
		{
			if (empty($options['link']))
			{
				throw new Exception('Post-installation message definitions need an action (URL) when they are of type "link"', 500);
			}
		}

		// The condition file and method are only required when the type is not "message"
		if ($options['type'] != 'message')
		{
			if (empty($options['condition_file']))
			{
				throw new Exception('Post-installation message definitions need a condition file when they are of type "' . $options['type'] . '"', 500);
			}

			$file_path = \FOFTemplateUtils::parsePath($options['condition_file'], true);

			if (!@is_file($file_path))
			{
				throw new Exception('The condition file ' . $options['condition_file'] . ' of your post-installation message definition does not exist', 500);
			}

			if (empty($options['condition_method']))
			{
				throw new Exception('Post-installation message definitions need a condition method (function name) when they are of type "' . $options['type'] . '"', 500);
			}
		}

		// Check if the definition exists
		$tableName = '#__postinstall_messages';

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn($tableName))
			->where($db->qn('extension_id') . ' = ' . $db->q($options['extension_id']))
			->where($db->qn('type') . ' = ' . $db->q($options['type']))
			->where($db->qn('title_key') . ' = ' . $db->q($options['title_key']));
		$existingRow = $db->setQuery($query)->loadAssoc();

		// Is the existing definition the same as the one we're trying to save (ignore the enabled flag)?
		if (!empty($existingRow))
		{
			$same = true;

			foreach ($options as $k => $v)
			{
				if ($k == 'enabled')
				{
					continue;
				}

				if ($existingRow[$k] != $v)
				{
					$same = false;
					break;
				}
			}

			// Trying to add the same row as the existing one; quit
			if ($same)
			{
				return;
			}

			// Otherwise it's not the same row. Remove the old row before insert a new one.
			$query = $db->getQuery(true)
				->delete($db->qn($tableName))
				->where($db->q('extension_id') . ' = ' . $db->q($options['extension_id']))
				->where($db->q('type') . ' = ' . $db->q($options['type']))
				->where($db->q('title_key') . ' = ' . $db->q($options['title_key']));
			$db->setQuery($query)->execute();
		}

		// Insert the new row
		$options = (object)$options;
		$db->insertObject($tableName, $options);
	}

	/**
	 * Applies the post-installation messages for Joomla! 3.2 or later
	 *
	 * @return void
	 */
	protected function _applyPostInstallationMessages()
	{
		// Make sure it's Joomla! 3.2.0 or later
		if (!version_compare(JVERSION, '3.2.0', 'ge'))
		{
			return;
		}

		// Make sure there are post-installation messages
		if (empty($this->postInstallationMessages))
		{
			return;
		}

		// Get the extension ID for our component
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q($this->componentName));
		$db->setQuery($query);

		try
		{
			$ids = $db->loadColumn();
		}
		catch (Exception $exc)
		{
			return;
		}

		if (empty($ids))
		{
			return;
		}

		$extension_id = array_shift($ids);

		foreach ($this->postInstallationMessages as $message)
		{
			$message['extension_id'] = $extension_id;
			$this->addPostInstallationMessage($message);
		}
	}

	protected function uninstallPostInstallationMessages()
	{
		// Make sure it's Joomla! 3.2.0 or later
		if (!version_compare(JVERSION, '3.2.0', 'ge'))
		{
			return;
		}

		// Make sure there are post-installation messages
		if (empty($this->postInstallationMessages))
		{
			return;
		}

		// Get the extension ID for our component
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q($this->componentName));
		$db->setQuery($query);

		try
		{
			$ids = $db->loadColumn();
		}
		catch (Exception $exc)
		{
			return;
		}

		if (empty($ids))
		{
			return;
		}

		$extension_id = array_shift($ids);

		$query = $db->getQuery(true)
			->delete($db->qn('#__postinstall_messages'))
			->where($db->qn('extension_id') . ' = ' . $db->q($extension_id));

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			return;
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
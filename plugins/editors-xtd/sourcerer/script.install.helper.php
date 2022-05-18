<?php
/**
 * @package         Sourcerer
 * @version         9.2.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Filesystem\File as JFile;
use Joomla\CMS\Filesystem\Folder as JFolder;
use Joomla\CMS\Installer\Installer as JInstaller;
use Joomla\CMS\Language\Text as JText;

class PlgEditorsXtdSourcererInstallerScriptHelper
{
	public $alias                    = '';
	public $can_install              = null;
	public $client_id                = 1;
	public $current_joomla_version   = '';
	public $db                       = null;
	public $extension_type           = '';
	public $extname                  = '';
	public $install_type             = 'install';
	public $installed_joomla_version = '';
	public $installed_version        = '';
	public $module_position          = 'status';
	public $name                     = '';
	public $plugin_folder            = 'system';
	public $show_message             = true;
	public $soft_break               = false;

	public function __construct(&$params)
	{
		$this->extname = $this->extname ?: $this->alias;
		$this->db      = JFactory::getDbo();
	}

	public function addInstalledMessage()
	{
		if ( ! $this->show_message)
		{
			return;
		}

		$language_string = $this->install_type == 'update'
			? 'RLI_THE_EXTENSION_HAS_BEEN_UPDATED_SUCCESSFULLY'
			: 'RLI_THE_EXTENSION_HAS_BEEN_INSTALLED_SUCCESSFULLY';

		JFactory::getApplication()->enqueueMessage(
			JText::sprintf(
				$language_string,
				'<strong>' . JText::_($this->name) . '</strong>',
				'<strong>' . $this->getVersion() . '</strong>'
			), 'success'
		);
	}

	public function canInstall()
	{
		if ( ! is_null($this->can_install))
		{
			return $this->can_install;
		}

		$this->can_install = false;

		// The extension is not installed yet
		if ( ! $this->installed_version)
		{
			$this->can_install = true;

			return true;
		}

		// The free version is installed. So any version is ok to install
		if (strpos($this->installed_version, 'PRO') === false)
		{
			$this->can_install = true;

			return true;
		}

		// Current package is a pro version, so all good
		if (strpos($this->getVersion(), 'PRO') !== false)
		{
			$this->can_install = true;

			return true;
		}

		JFactory::getLanguage()->load($this->getPrefix() . '_' . $this->extname, __DIR__);

		JFactory::getApplication()->enqueueMessage(JText::_('RLI_ERROR_PRO_TO_FREE'), 'error');

		JFactory::getApplication()->enqueueMessage(
			html_entity_decode(
				JText::sprintf(
					'RLI_ERROR_UNINSTALL_FIRST',
					'<a href="https://regularlabs.com/' . $this->alias . '" target="_blank">',
					'</a>',
					JText::_($this->name)
				)
			), 'error'
		);

		return false;
	}

	public function delete($files = [])
	{
		foreach ($files as $file)
		{
			if (is_dir($file))
			{
				JFolder::delete($file);
			}

			if (is_file($file))
			{
				JFile::delete($file);
			}
		}
	}

	public function fixAssetsRules()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('rules'))
			->from('#__assets')
			->where($this->db->quoteName('title') . ' = ' . $this->db->quote('com_' . $this->extname))
			->setLimit(1);
		$this->db->setQuery($query);
		$rules = $this->db->loadResult();

		$rules = json_decode($rules);

		if (empty($rules))
		{
			return;
		}

		foreach ($rules as $key => $value)
		{
			if ( ! empty($value))
			{
				continue;
			}

			unset($rules->$key);
		}

		$rules = json_encode($rules);

		$query = $this->db->getQuery(true)
			->update($this->db->quoteName('#__assets'))
			->set($this->db->quoteName('rules') . ' = ' . $this->db->quote($rules))
			->where($this->db->quoteName('title') . ' = ' . $this->db->quote('com_' . $this->extname));
		$this->db->setQuery($query);
		$this->db->execute();
	}

	public function fixFileVersions($file)
	{
		if (is_array($file))
		{
			foreach ($file as $f)
			{
				self::fixFileVersions($f);
			}

			return;
		}

		if ( ! is_string($file) || ! is_file($file))
		{
			return;
		}

		$contents = file_get_contents($file);

		if (
			strpos($contents, 'FREEFREE') === false
			&& strpos($contents, 'FREEPRO') === false
			&& strpos($contents, 'PROFREE') === false
			&& strpos($contents, 'PROPRO') === false
		)
		{
			return;
		}

		$contents = str_replace(
			['FREEFREE', 'FREEPRO', 'PROFREE', 'PROPRO'],
			['FREE', 'PRO', 'FREE', 'PRO'],
			$contents
		);

		JFile::write($file, $contents);
	}

	public function foldersExist($folders = [])
	{
		foreach ($folders as $folder)
		{
			if (is_dir($folder))
			{
				return true;
			}
		}

		return false;
	}

	public function getCurrentXMLFile()
	{
		return $this->getXMLFile(__DIR__);
	}

	public function getElementName($type = null, $extname = null)
	{
		$type    = is_null($type) ? $this->extension_type : $type;
		$extname = is_null($extname) ? $this->extname : $extname;

		switch ($type)
		{
			case 'component' :
				return 'com_' . $extname;

			case 'module' :
				return 'mod_' . $extname;

			case 'plugin' :
			default:
				return $extname;
		}
	}

	public function getFullType()
	{
		return JText::_('RLI_' . strtoupper($this->getPrefix()));
	}

	public function getInstalledXMLFile()
	{
		return $this->getXMLFile($this->getMainFolder());
	}

	public function getMainFolder()
	{
		switch ($this->extension_type)
		{
			case 'plugin' :
				return JPATH_PLUGINS . '/' . $this->plugin_folder . '/' . $this->extname;

			case 'component' :
				return JPATH_ADMINISTRATOR . '/components/com_' . $this->extname;

			case 'module' :
				return JPATH_ADMINISTRATOR . '/modules/mod_' . $this->extname;

			case 'library' :
				return JPATH_SITE . '/libraries/' . $this->extname;
		}
	}

	public function getPrefix()
	{
		switch ($this->extension_type)
		{
			case 'plugin':
				return JText::_('plg_' . strtolower($this->plugin_folder));

			case 'component':
				return JText::_('com');

			case 'module':
				return JText::_('mod');

			case 'library':
				return JText::_('lib');

			default:
				return $this->extension_type;
		}
	}

	public function getVersion($file = '')
	{
		return $this->getXmlValue('version', $file);
	}

	public function getXMLFile($folder)
	{
		switch ($this->extension_type)
		{
			case 'module' :
				return $folder . '/mod_' . $this->extname . '.xml';

			default :
				return $folder . '/' . $this->extname . '.xml';
		}
	}

	public function getXmlData($file = '')
	{
		$file = $file ?: $this->getCurrentXMLFile();

		if ( ! is_file($file))
		{
			return null;
		}

		$xml = JInstaller::parseXMLInstallFile($file);

		if ( ! $xml)
		{
			return null;
		}

		return $xml;
	}

	public function getXmlJoomlaVersion($file = '')
	{
		$file = $file ?: $this->getCurrentXMLFile();

		if ( ! is_file($file))
		{
			return null;
		}

		$xml = simplexml_load_file($file);

		if ( ! $xml)
		{
			return null;
		}

		return (int) $xml->attributes()->version;
	}

	public function getXmlValue($key, $file = '')
	{
		$xml = $this->getXmlData($file);

		if ( ! $xml || ! isset($xml[$key]))
		{
			return '';
		}

		return $xml[$key];
	}

	public function isInstalled()
	{
		if ( ! is_file($this->getInstalledXMLFile()))
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('extension_id'))
			->from('#__extensions')
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote($this->extension_type))
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote($this->getElementName()))
			->setLimit(1);
		$this->db->setQuery($query);
		$result = $this->db->loadResult();

		return empty($result) ? false : true;
	}

	public function isNewer()
	{
		if ( ! $this->installed_version)
		{
			return true;
		}

		$package_version = $this->getVersion();

		return version_compare($this->installed_version, $package_version, '<=');
	}

	public function onAfterInstall($route)
	{
		if ($this->extension_type == 'component')
		{
			$this->fixAssetsRules();
		}

		return true;
	}

	public function onBeforeInstall($route)
	{
		if ( ! $this->canInstall())
		{
			return false;
		}

		return true;
	}

	public function postflight($route, $adapter)
	{
		if ( ! $this->canInstall())
		{
			return true;
		}

		$this->removeGlobalLanguageFiles();
		$this->removeUnusedLanguageFiles();

		JFactory::getLanguage()->load($this->getPrefix() . '_' . $this->extname, $this->getMainFolder());

		if ( ! in_array($route, ['install', 'update']))
		{
			return true;
		}

		$this->fixExtensionNames();
		$this->updateUpdateSites();
		$this->removeAdminCache();

		if ($this->onAfterInstall($route) === false)
		{
			return false;
		}

		$this->publishExtension($route);
		$this->addInstalledMessage();

		JFactory::getCache()->clean('com_plugins');
		JFactory::getCache()->clean('_system');

		return true;
	}

	public function preflight($route, $adapter)
	{
		if ( ! in_array($route, ['install', 'update']))
		{
			return true;
		}

		JFactory::getLanguage()->load('plg_system_regularlabsinstaller', JPATH_PLUGINS . '/system/regularlabsinstaller');

		$this->installed_version        = $this->getVersion($this->getInstalledXMLFile());
		$this->current_joomla_version   = $this->getXmlJoomlaVersion($this->getCurrentXMLFile());
		$this->installed_joomla_version = $this->getXmlJoomlaVersion($this->getInstalledXMLFile());

		if ($this->show_message && $this->isInstalled())
		{
			$this->install_type = 'update';
		}

//		if ($this->extension_type == 'component')
//		{
//			// Remove admin menu to prevent error on creating it again
//			$query = $this->db->getQuery(true)
//				->delete('#__menu')
//				->where($this->db->quoteName('path') . ' = ' . $this->db->quote('com-' . $this->extname))
//				->where($this->db->quoteName('client_id') . ' = 1');
//			$this->db->setQuery($query);
//			$this->db->execute();
//		}

		if ($this->onBeforeInstall($route) === false)
		{
			return false;
		}

		return true;
	}

	public function publishExtension($route)
	{
		if ($this->extension_type == 'module')
		{
			// Force enable administrator module extension to solve disabled J3 modules on J4 setups
			$this->enableAdministratorModuleExtension();
		}

		if ($route == 'update'
			&& $this->installed_joomla_version >= $this->current_joomla_version
		)
		{
			return;
		}

		switch ($this->extension_type)
		{
			case 'plugin' :
				$this->publishPlugin();
				break;

			case 'module' :
				$this->publishModule();
				break;
		}
	}

	public function enableAdministratorModuleExtension()
	{
		$query = $this->db->getQuery(true)
			->update('#__extensions')
			->set($this->db->quoteName('enabled') . ' = 1')
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('module'))
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote('mod_' . $this->extname))
			->where($this->db->quoteName('client_id') . ' = 1');
		$this->db->setQuery($query);
		$this->db->execute();
	}

	public function publishModule()
	{
		// Get module id
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from('#__modules')
			->where($this->db->quoteName('module') . ' = ' . $this->db->quote('mod_' . $this->extname))
			->where($this->db->quoteName('client_id') . ' = ' . (int) $this->client_id);
		$this->db->setQuery($query, 0, 1);
		$id = $this->db->loadResult();

		if ( ! $id)
		{
			return;
		}

		// check if module is already in the modules_menu table (meaning is is already saved)
		$query->clear()
			->select($this->db->quoteName('moduleid'))
			->from('#__modules_menu')
			->where($this->db->quoteName('moduleid') . ' = ' . (int) $id)
			->setLimit(1);
		$this->db->setQuery($query);
		$exists = $this->db->loadResult();

		if ($exists)
		{
			return;
		}

		// Get highest ordering number in position
		$query->clear()
			->select($this->db->quoteName('ordering'))
			->from('#__modules')
			->where($this->db->quoteName('position') . ' = ' . $this->db->quote($this->module_position))
			->where($this->db->quoteName('client_id') . ' = ' . (int) $this->client_id)
			->order('ordering DESC');
		$this->db->setQuery($query, 0, 1);
		$ordering = $this->db->loadResult();
		$ordering++;

		// publish module and set ordering number
		$query->clear()
			->update('#__modules')
			->set($this->db->quoteName('published') . ' = 1')
			->set($this->db->quoteName('ordering') . ' = ' . (int) $ordering)
			->set($this->db->quoteName('position') . ' = ' . $this->db->quote($this->module_position))
			->where($this->db->quoteName('id') . ' = ' . (int) $id);
		$this->db->setQuery($query);
		$this->db->execute();

		// add module to the modules_menu table
		$query->clear()
			->insert('#__modules_menu')
			->columns([$this->db->quoteName('moduleid'), $this->db->quoteName('menuid')])
			->values((int) $id . ', 0');
		$this->db->setQuery($query);
		$this->db->execute();
	}

	public function publishPlugin()
	{
		$query = $this->db->getQuery(true)
			->update('#__extensions')
			->set($this->db->quoteName('enabled') . ' = 1')
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote($this->extname))
			->where($this->db->quoteName('folder') . ' = ' . $this->db->quote($this->plugin_folder));
		$this->db->setQuery($query);
		$this->db->execute();
	}

	/*
	 * Fixes incorrectly formed versions because of issues in old packager
	 */

	public function uninstallComponent($extname, $show_message = true)
	{
		$this->uninstallExtension($extname, 'component', null, $show_message);
	}

	public function uninstallExtension($extname, $type = 'plugin', $folder = 'system', $show_message = true)
	{
		if (empty($extname))
		{
			return;
		}

		$folders = [];

		switch ($type)
		{
			case 'plugin':
				$folders[] = JPATH_PLUGINS . '/' . $folder . '/' . $extname;
				break;

			case 'component':
				$folders[] = JPATH_ADMINISTRATOR . '/components/com_' . $extname;
				$folders[] = JPATH_SITE . '/components/com_' . $extname;
				break;

			case 'module':
				$folders[] = JPATH_ADMINISTRATOR . '/modules/mod_' . $extname;
				$folders[] = JPATH_SITE . '/modules/mod_' . $extname;
				break;

			case 'library':
				$folders[] = JPATH_SITE . '/libraries/' . $extname;
				break;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('extension_id'))
			->from('#__extensions')
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote($this->getElementName($type, $extname)))
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote($type));

		if ($type == 'plugin')
		{
			$query->where($this->db->quoteName('folder') . ' = ' . $this->db->quote($folder));
		}

		$this->db->setQuery($query);
		$ids = $this->db->loadColumn();

		if (empty($ids))
		{
			foreach ($folders as $folder)
			{
				if ( ! is_dir($folder))
				{
					continue;
				}

				JFactory::getApplication()->enqueueMessage('2. Deleting: ' . $folder, 'notice');
				JFolder::delete($folder);
			}

			return;
		}

		$ignore_ids = JFactory::getApplication()->getUserState('rl_ignore_uninstall_ids', []);

		if (JFactory::getApplication()->input->get('option') == 'com_installer' && JFactory::getApplication()->input->get('task') == 'remove')
		{
			// Don't attempt to uninstall extensions that are already selected to get uninstalled by them selves
			$ignore_ids = array_merge($ignore_ids, JFactory::getApplication()->input->get('cid', [], 'array'));
			JFactory::getApplication()->input->set('cid', array_merge($ignore_ids, $ids));
		}

		$ids = array_diff($ids, $ignore_ids);

		if (empty($ids))
		{
			return;
		}

		$ignore_ids = array_merge($ignore_ids, $ids);
		JFactory::getApplication()->setUserState('rl_ignore_uninstall_ids', $ignore_ids);

		foreach ($ids as $id)
		{
			$tmpInstaller = new JInstaller;
			$tmpInstaller->uninstall($type, $id);
		}

		if ($show_message)
		{
			JFactory::getApplication()->enqueueMessage(
				JText::sprintf(
					'COM_INSTALLER_UNINSTALL_SUCCESS',
					JText::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($type))
				), 'success'
			);
		}
	}

	public function uninstallLibrary($extname, $show_message = true)
	{
		$this->uninstallExtension($extname, 'library', null, $show_message);
	}

	public function uninstallModule($extname, $show_message = true)
	{
		$this->uninstallExtension($extname, 'module', null, $show_message);
	}

	public function uninstallPlugin($extname, $folder = 'system', $show_message = true)
	{
		$this->uninstallExtension($extname, 'plugin', $folder, $show_message);
	}

	private function fixExtensionNames()
	{
		switch ($this->extension_type)
		{
			case 'module' :
				$this->fixModuleNames();
		}
	}

	private function fixModuleNames()
	{
		// Get module id
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from('#__modules')
			->where($this->db->quoteName('module') . ' = ' . $this->db->quote('mod_' . $this->extname))
			->where($this->db->quoteName('client_id') . ' = ' . (int) $this->client_id);
		$this->db->setQuery($query, 0, 1);
		$module_id = $this->db->loadResult();

		if (empty($module_id))
		{
			return;
		}

		$title = 'Regular Labs - ' . JText::_($this->name);

		$query->clear()
			->update('#__modules')
			->set($this->db->quoteName('title') . ' = ' . $this->db->quote($title))
			->where($this->db->quoteName('id') . ' = ' . (int) $module_id)
			->where($this->db->quoteName('title') . ' LIKE ' . $this->db->quote('NoNumber%'));
		$this->db->setQuery($query);
		$this->db->execute();

		// Fix module assets

		// Get asset id
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from('#__assets')
			->where($this->db->quoteName('name') . ' = ' . $this->db->quote('com_modules.module.' . (int) $module_id))
			->where($this->db->quoteName('title') . ' LIKE ' . $this->db->quote('NoNumber%'))
			->setLimit(1);
		$this->db->setQuery($query);
		$asset_id = $this->db->loadResult();

		if (empty($asset_id))
		{
			return;
		}

		$query->clear()
			->update('#__assets')
			->set($this->db->quoteName('title') . ' = ' . $this->db->quote($title))
			->where($this->db->quoteName('id') . ' = ' . (int) $asset_id);
		$this->db->setQuery($query);
		$this->db->execute();
	}

	private function removeAdminCache()
	{
		$this->delete([JPATH_ADMINISTRATOR . '/cache/regularlabs']);
		$this->delete([JPATH_ADMINISTRATOR . '/cache/nonumber']);
	}

	private function removeDuplicateUpdateSite()
	{
		// First check to see if there is a pro entry

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('update_site_id'))
			->from('#__update_sites')
			->where($this->db->quoteName('location') . ' LIKE ' . $this->db->quote('%download.regularlabs.com%'))
			->where($this->db->quoteName('location') . ' LIKE ' . $this->db->quote('%e=' . $this->alias . '%'))
			->where($this->db->quoteName('location') . ' NOT LIKE ' . $this->db->quote('%pro=1%'))
			->setLimit(1);
		$this->db->setQuery($query);
		$id = $this->db->loadResult();

		// Otherwise just get the first match
		if ( ! $id)
		{
			$query->clear()
				->select($this->db->quoteName('update_site_id'))
				->from('#__update_sites')
				->where($this->db->quoteName('location') . ' LIKE ' . $this->db->quote('%download.regularlabs.com%'))
				->where($this->db->quoteName('location') . ' LIKE ' . $this->db->quote('%e=' . $this->alias . '%'));
			$this->db->setQuery($query, 0, 1);
			$id = $this->db->loadResult();

			// Remove pro=1 from the found update site
			$query->clear()
				->update('#__update_sites')
				->set($this->db->quoteName('location')
					. ' = replace(' . $this->db->quoteName('location') . ', ' . $this->db->quote('&pro=1') . ', ' . $this->db->quote('') . ')')
				->where($this->db->quoteName('update_site_id') . ' = ' . (int) $id);
			$this->db->setQuery($query);
			$this->db->execute();
		}

		if ( ! $id)
		{
			return;
		}

		$query->clear()
			->select($this->db->quoteName('update_site_id'))
			->from('#__update_sites')
			->where($this->db->quoteName('location') . ' LIKE ' . $this->db->quote('%download.regularlabs.com%'))
			->where($this->db->quoteName('location') . ' LIKE ' . $this->db->quote('%e=' . $this->alias . '%'))
			->where($this->db->quoteName('update_site_id') . ' != ' . $id);
		$this->db->setQuery($query);
		$ids = $this->db->loadColumn();

		if (empty($ids))
		{
			return;
		}

		$query->clear()
			->delete('#__update_sites')
			->where($this->db->quoteName('update_site_id') . ' IN (' . implode(',', $ids) . ')');
		$this->db->setQuery($query);
		$this->db->execute();

		$query->clear()
			->delete('#__update_sites_extensions')
			->where($this->db->quoteName('update_site_id') . ' IN (' . implode(',', $ids) . ')');
		$this->db->setQuery($query);
		$this->db->execute();
	}

	private function removeGlobalLanguageFiles()
	{
		if ($this->extension_type == 'library')
		{
			return;
		}

		$language_files = JFolder::files(JPATH_ADMINISTRATOR . '/language', '\.' . $this->getPrefix() . '_' . $this->extname . '\.', true, true);

		// Remove override files
		foreach ($language_files as $i => $language_file)
		{
			if (strpos($language_file, '/overrides/') === false)
			{
				continue;
			}

			unset($language_files[$i]);
		}

		if (empty($language_files))
		{
			return;
		}

		JFile::delete($language_files);
	}

	private function removeOldUpdateSites()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('update_site_id'))
			->from('#__update_sites')
			->where($this->db->quoteName('location') . ' LIKE ' . $this->db->quote('%nonumber.nl%'))
			->where($this->db->quoteName('location') . ' LIKE ' . $this->db->quote('%e=' . $this->alias . '%'));
		$this->db->setQuery($query, 0, 1);
		$id = $this->db->loadResult();

		if ( ! $id)
		{
			return;
		}

		$query->clear()
			->delete('#__update_sites')
			->where($this->db->quoteName('update_site_id') . ' = ' . (int) $id);
		$this->db->setQuery($query);
		$this->db->execute();

		$query->clear()
			->delete('#__update_sites_extensions')
			->where($this->db->quoteName('update_site_id') . ' = ' . (int) $id);
		$this->db->setQuery($query);
		$this->db->execute();
	}

	private function removeUnusedLanguageFiles()
	{
		if ($this->extension_type == 'library')
		{
			return;
		}

		if ( ! is_file(__DIR__ . '/language'))
		{
			return;
		}

		$installed_languages = array_merge(
			is_file(JPATH_SITE . '/language') ? JFolder::folders(JPATH_SITE . '/language') : [],
			is_file(JPATH_ADMINISTRATOR . '/language') ? JFolder::folders(JPATH_ADMINISTRATOR . '/language') : []
		);

		$languages = array_diff(
			JFolder::folders(__DIR__ . '/language') ?: [],
			$installed_languages
		);

		$delete_languages = [];

		foreach ($languages as $language)
		{
			$delete_languages[] = $this->getMainFolder() . '/language/' . $language;
		}

		if (empty($delete_languages))
		{
			return;
		}

		// Remove folders
		$this->delete($delete_languages);
	}

	private function removeXXXUpdateSites()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('update_site_id'))
			->from('#__update_sites')
			->where($this->db->quoteName('location') . ' LIKE ' . $this->db->quote('%regularlabs.com/updates.xml?e=XXX%'));
		$this->db->setQuery($query);
		$ids = $this->db->loadColumn();

		if (empty($ids))
		{
			return;
		}

		$query->clear()
			->delete('#__update_sites')
			->where($this->db->quoteName('update_site_id') . ' IN (' . implode(',', $ids) . ')');
		$this->db->setQuery($query);
		$this->db->execute();

		$query->clear()
			->delete('#__update_sites_extensions')
			->where($this->db->quoteName('update_site_id') . ' IN (' . implode(',', $ids) . ')');
		$this->db->setQuery($query);
		$this->db->execute();
	}

	// Save the download key from the Regular Labs Extension Manager config to the update sites

	private function saveDownloadKey($key)
	{
		$key = trim($key);

		if ( ! $key)
		{
			return false;
		}

		if ( ! preg_match('#^[a-zA-Z0-9]{8}[A-Z0-9]{8}$#', $key, $match))
		{
			return false;
		}

		// Add the key on all regularlabs.com urls
		$query = $this->db->getQuery(true)
			->update('#__update_sites')
			->set($this->db->quoteName('extra_query') . ' = ' . $this->db->quote('k=' . $key))
			->where($this->db->quoteName('location') . ' LIKE ' . $this->db->quote('%download.regularlabs.com%'))
			->where($this->db->quoteName('location') . ' LIKE ' . $this->db->quote('%&pro=%'));
		$this->db->setQuery($query);
		$this->db->execute();

		return true;
	}

	// Save the download key from the Regular Labs Extension Manager config to the update sites

	private function updateDownloadKey()
	{
		if ($this->updateDownloadKeyFromDatabase())
		{
			return;
		}

		$this->updateDownloadKeyFromExtensionManager();
	}

	private function updateDownloadKeyFromDatabase()
	{
		$query = $this->db->getQuery(true)
			->select('extra_query')
			->from('#__update_sites')
			->where($this->db->quoteName('extra_query') . ' LIKE ' . $this->db->quote('k=%'))
			->where($this->db->quoteName('location') . ' LIKE ' . $this->db->quote('%download.regularlabs.com%'));

		$this->db->setQuery($query);

		$key = $this->db->loadResult();

		if ( ! $key)
		{
			return false;
		}

		if ( ! preg_match('#k=([a-zA-Z0-9]{8}[A-Z0-9]{8})#si', $key, $match))
		{
			return false;
		}

		return $this->saveDownloadKey($match[1]);
	}

	private function updateDownloadKeyFromExtensionManager()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('params'))
			->from('#__extensions')
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote('com_regularlabsmanager'));
		$this->db->setQuery($query);
		$params = $this->db->loadResult();

		if ( ! $params)
		{
			return false;
		}

		$params = json_decode($params);

		if ( ! isset($params->key))
		{
			return false;
		}

		return $this->saveDownloadKey($params->key);
	}

	private function updateHttptoHttpsInUpdateSites()
	{
		$query = $this->db->getQuery(true)
			->update('#__update_sites')
			->set($this->db->quoteName('location') . ' = REPLACE('
				. $this->db->quoteName('location') . ', '
				. $this->db->quote('http://') . ', '
				. $this->db->quote('https://')
				. ')')
			->where($this->db->quoteName('location') . ' LIKE ' . $this->db->quote('http://download.regularlabs.com%'));
		$this->db->setQuery($query);
		$this->db->execute();
	}

	private function updateNamesInUpdateSites()
	{
		$name = JText::_($this->name);
		if ($this->alias != 'extensionmanager')
		{
			$name = 'Regular Labs - ' . $name;
		}

		$query = $this->db->getQuery(true)
			->update('#__update_sites')
			->set($this->db->quoteName('name') . ' = ' . $this->db->quote($name))
			->where($this->db->quoteName('location') . ' LIKE ' . $this->db->quote('%download.regularlabs.com%'))
			->where($this->db->quoteName('location') . ' LIKE ' . $this->db->quote('%e=' . $this->alias . '%'));
		$this->db->setQuery($query);
		$this->db->execute();
	}

	private function updateUpdateSites()
	{
		$this->removeOldUpdateSites();
		$this->removeXXXUpdateSites();
		$this->updateNamesInUpdateSites();
		$this->updateHttptoHttpsInUpdateSites();
		$this->removeDuplicateUpdateSite();
		$this->updateDownloadKey();
	}
}

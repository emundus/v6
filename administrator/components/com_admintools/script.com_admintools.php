<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

// Load FOF if not already loaded
if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
{
	throw new RuntimeException('This component requires FOF 3.0.');
}

class Com_AdmintoolsInstallerScript extends \FOF30\Utils\InstallScript
{
	/**
	 * The component's name
	 *
	 * @var   string
	 */
	protected $componentName = 'com_admintools';

	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 *
	 * @var string
	 */
	protected $componentTitle = 'Admin Tools';

	/**
	 * The minimum PHP version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumPHPVersion = '5.3.3';

	/**
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumJoomlaVersion = '3.3.0';

	/**
	 * Obsolete files and folders to remove from both paid and free releases. This is used when you refactor code and
	 * some files inevitably become obsolete and need to be removed.
	 *
	 * @var   array
	 */
	protected $removeFilesAllVersions = array(
		'files'   => array(
			// Old cached updates from Live Update
			'cache/com_admintools.updates.php',
			'cache/com_admintools.updates.ini',

			'administrator/cache/com_admintools.updates.php',
			'administrator/cache/com_admintools.updates.ini',

		    // Obsolete files
			'administrator/components/com_admintools/restore.php',
			'administrator/components/com_admintools/dispatcher.php',
			'administrator/components/com_admintools/toolbar.php',

			'components/com_admintools/dispatcher.php',

			// PLUGIN "System - Admin Tools"

			// -- CSS/JS Combination
			'plugins/system/admintools/admintools/cssmin.php',

			// -- obsolete files
			'plugins/system/admintools/admintools/pro.php',
			'plugins/system/admintools/admintools/core.php',

			// -- removed features
			'plugins/system/admintools/feature/twofactorauth.php',
			'plugins/system/admintools/feature/xssshield.php',
			//     ...because some people have never updated to 3.6, apparently?!
			'plugins/system/admintools/feature/blockinstall.php',
		),
		'folders' => array(
			// Obsolete folders from AT 1.x, 2.x and 3.x
			'administrator/components/com_admintools/akeeba',
			'administrator/components/com_admintools/classes',
			'administrator/components/com_admintools/controllers',
			'administrator/components/com_admintools/helpers',
			'administrator/components/com_admintools/models',
			'administrator/components/com_admintools/tables',
			'administrator/components/com_admintools/views',
			'administrator/components/com_admintools/fof',

			'components/com_admintools/controllers',
			'components/com_admintools/views',

			// Bad behaviour integration
			'plugins/system/admintools/admintools/badbehaviour',

			// Public media directory (moved to administrator)
			'media/com_admintools',
		)
	);

	/**
	 * Runs on installation
	 *
	 * @param   JInstallerAdapterComponent $parent The parent object
	 *
	 * @return  void
	 */
	public function install($parent)
	{
		if (!defined('ADMINTOOLS_THIS_IS_INSTALLATION_FROM_SCRATCH'))
		{
			define('ADMINTOOLS_THIS_IS_INSTALLATION_FROM_SCRATCH', 1);
		}
	}

	/**
	 * Joomla! pre-flight event. This runs before Joomla! installs or updates the component. This is our last chance to
	 * tell Joomla! if it should abort the installation.
	 *
	 * @param   string                     $type   Installation type (install, update, discover_install)
	 * @param   JInstallerAdapterComponent $parent Parent object
	 *
	 * @return  boolean  True to let the installation proceed, false to halt the installation
	 */
	public function preflight($type, $parent)
	{
		$this->isPaid = is_dir($parent->getParent()->getPath('source') . '/backend/engine');

		$result = parent::preflight($type, $parent);

		if (!$result)
		{
			return $result;
		}
	}

	/**
	 * Runs after install, update or discover_update
	 *
	 * @param   string                      $type install, update or discover_update
	 * @param   JInstallerAdapterComponent  $parent
	 *
	 * @return  boolean  True to let the installation proceed, false to halt the installation
	 */
	function postflight($type, $parent)
	{
		// Let's install common tables
		$container = null;
		$model     = null;

		if (class_exists('FOF30\\Container\\Container'))
		{
			try
			{
				$container = \FOF30\Container\Container::getInstance('com_admintools');
			}
			catch (\Exception $e)
			{
				$container = null;
			}
		}

		if (is_object($container) && class_exists('FOF30\\Container\\Container') && ($container instanceof \FOF30\Container\Container))
		{
			/** @var \Akeeba\AdminTools\Admin\Model\Stats $model */
			try
			{
				$model = $container->factory->model('Stats')->tmpInstance();
			}
			catch (\Exception $e)
			{
				$model = null;
			}
		}

		if (is_object($model) && class_exists('Akeeba\\AdminTools\\Admin\\Model\\Stats')
		    && ($model instanceof Akeeba\AdminTools\Admin\Model\Stats)
		    && method_exists($model, 'checkAndFixCommonTables'))
		{
			try
			{
				$model->checkAndFixCommonTables();
			}
			catch (Exception $e)
			{
				// Do nothing if that failed.
			}
		}

		// Parent method
		parent::postflight($type, $parent);

		// Uninstall post-installation messages we are no longer using
		$this->uninstallObsoletePostinstallMessages();

		// Remove the update sites for this component on installation. The update sites are now handled at the package
		// level.
		$this->removeObsoleteUpdateSites($parent);

		// Remove the FOF 2.x update sites (annoying leftovers)
		$this->removeFOFUpdateSites();

		// If this is an update set the configuration wizard flag on update (so as not to bother existing users)
		if (!defined('ADMINTOOLS_THIS_IS_INSTALLATION_FROM_SCRATCH'))
		{
			if (!class_exists('Akeeba\\AdminTools\\Admin\\Helper\\Storage'))
			{
				@include_once $parent->getParent()->getPath('source') . '/backend/Helper/Storage.php';
			}

			if (class_exists('Akeeba\\AdminTools\\Admin\\Helper\\Storage'))
			{
				$params = new \Akeeba\AdminTools\Admin\Helper\Storage();
				$params->load();
				$params->setValue('quickstart', 1, true);
			}
		}
	}

	/**
	 * Renders the post-installation message
	 */
	function renderPostInstallation($parent)
	{
		try
		{
			$this->warnAboutJSNPowerAdmin();
		}
		catch (Exception $e)
		{
			// Don't sweat if the site's db croaks while I'm checking for 3PD software that causes trouble
		}

		// Load the version file
		if (!defined('ADMINTOOLS_PRO'))
		{
			@include_once JPATH_ADMINISTRATOR . '/components/com_admintools/version.php';
		}

		if (!defined('ADMINTOOLS_PRO'))
		{
			define('ADMINTOOLS_PRO', '0');
		}

		?>
		<img src="../administrator/components/com_admintools/media/images/admintools-48.png" width="48" height="48" alt="Admin Tools" align="right"/>

		<h2>Welcome to Admin Tools!</h2>

		<div style="margin: 1em; font-size: 14pt; background-color: #fffff9; color: black">
			You can download translation files <a href="http://cdn.akeebabackup.com/language/admintools/index.html">directly
				from our CDN page</a>.
		</div>

		<fieldset>
			<?php if (ADMINTOOLS_PRO): ?>
			<p>
				We strongly recommend watching our <a href="https://www.akeebabackup.com/videos/1207-admin-tools.html">video
				tutorials</a> before using this component.
			</p>

			<p>
				If this is the first time you install Admin Tools on your site please run the
				<a href="index.php?option=com_admintools&view=QuickStart">Quick Setup Wizard</a>. It will guide you through
				tailoring Admin Tools for your site. <strong>Pay attention to the messages on that page. They contain
				information to unblock yourself should you inadvertently block yourself out of your site!</strong>
			</p>
			<?php endif; ?>

			<p>
				By installing this component you are implicitly accepting
				<a href="https://www.akeebabackup.com/license.html">its license (GNU GPLv3)</a> and our
				<a href="https://www.akeebabackup.com/privacy-policy.html">Terms of Service</a>,
				including our Support Policy.
			</p>
		</fieldset>

		<?php
		// Let's install common tables
		$container = null;
		$model     = null;

		if (class_exists('FOF30\\Container\\Container'))
		{
			try
			{
				$container = \FOF30\Container\Container::getInstance('com_admintools');
			}
			catch (\Exception $e)
			{
				$container = null;
			}
		}

		if (is_object($container) && class_exists('FOF30\\Container\\Container') && ($container instanceof \FOF30\Container\Container))
		{
			/** @var \Akeeba\AdminTools\Admin\Model\Stats $model */
			try
			{
				$model = $container->factory->model('Stats')->tmpInstance();
			}
			catch (\Exception $e)
			{
				$model = null;
			}
		}

		/** @var \Akeeba\AdminTools\Admin\Model\Stats $model */
		try
		{
			if (is_object($model) && class_exists('Akeeba\\AdminTools\\Admin\\Model\\Stats')
			    && ($model instanceof Akeeba\AdminTools\Admin\Model\Stats)
			    && method_exists($model, 'collectStatistics'))
			{
				$iframe = $model->collectStatistics(true);

				if ($iframe)
				{
					echo $iframe;
				}
			}
		}
		catch (\Exception $e)
		{
		}
	}

	protected function renderPostUninstallation($parent)
	{
		?>
		<h2>Admin Tools Uninstallation Status</h2>
		<p>We are sorry that you decided to uninstall Admin Tools. Please let us know why by using the <a
				href="https://www.akeebabackup.com/contact-us.html" target="_blank">Contact Us form on our site</a>. We
			appreciate your feedback; it helps us develop better software!</p>
		<?php
	}

	/**
	 * The PowerAdmin extension makes menu items disappear. People assume it's our fault. JSN PowerAdmin authors don't
	 * own up to their software's issue. I have no choice but to warn our users about the faulty third party software.
	 */
	private function warnAboutJSNPowerAdmin()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
		            ->select('COUNT(*)')
		            ->from($db->qn('#__extensions'))
		            ->where($db->qn('type') . ' = ' . $db->q('component'))
		            ->where($db->qn('element') . ' = ' . $db->q('com_poweradmin'))
		            ->where($db->qn('enabled') . ' = ' . $db->q('1'));
		$hasPowerAdmin = $db->setQuery($query)->loadResult();

		if (!$hasPowerAdmin)
		{
			return;
		}

		$query = $db->getQuery(true)
		            ->select('manifest_cache')
		            ->from($db->qn('#__extensions'))
		            ->where($db->qn('type') . ' = ' . $db->q('component'))
		            ->where($db->qn('element') . ' = ' . $db->q('com_poweradmin'))
		            ->where($db->qn('enabled') . ' = ' . $db->q('1'));
		$paramsJson = $db->setQuery($query)->loadResult();

		$className = class_exists('JRegistry') ? 'JRegistry' : '\Joomla\Registry\Registry';

		/** @var \Joomla\Registry\Registry $jsnPAManifest */
		$jsnPAManifest = new $className();
		$jsnPAManifest->loadString($paramsJson, 'JSON');
		$version = $jsnPAManifest->get('version', '0.0.0');

		if (version_compare($version, '2.1.2', 'ge'))
		{
			return;
		}

		echo <<< HTML
<div class="well" style="margin: 2em 0;">
<h1 style="font-size: 32pt; line-height: 120%; color: red; margin-bottom: 1em">WARNING: Menu items for {$this->componentName} might not be displayed on your site.</h1>
<p style="font-size: 18pt; line-height: 150%; margin-bottom: 1.5em">
	We have detected that you are using JSN PowerAdmin on your site. This software ignores Joomla! standards and
	<b>hides</b> the Component menu items to {$this->componentName} in the administrator backend of your site. Unfortunately we
	can't provide support for third party software. Please contact the developers of JSN PowerAdmin for support
	regarding this issue.
</p>
<p style="font-size: 18pt; line-height: 120%; color: green;">
	Tip: You can disable JSN PowerAdmin to see the menu items to Akeeba Backup.
</p>
</div>

HTML;

	}

	/**
	 * Remove FOF 2.x update sites
	 */
	private function removeFOFUpdateSites()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
		            ->delete($db->qn('#__update_sites_extensions'))
		            ->where($db->qn('location') . ' = ' . $db->q('http://cdn.akeebabackup.com/updates/fof.xml'));
		try
		{
			$db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			// Do nothing on failure
		}

	}

	private function uninstallObsoletePostinstallMessages()
	{
		$db = JFactory::getDbo();

		$obsoleteTitleKeys = array(
			'COM_ADMINTOOLS_POSTSETUP_LBL_AUTOJUPDATE',
			'COM_ADMINTOOLS_POSTSETUP_LBL_ACCEPTLICENSE',
			'COM_ADMINTOOLS_POSTSETUP_LBL_ACCEPTSUPPORT',
		);

		foreach ($obsoleteTitleKeys as $obsoleteKey)
		{

			// Remove the "Upgrade profiles to ANGIE" post-installation message
			$query = $db->getQuery(true)
			            ->delete($db->qn('#__postinstall_messages'))
			            ->where($db->qn('title_key') . ' = ' . $db->q($obsoleteKey));
			try
			{
				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
				// Do nothing
			}
		}
	}

	/**
	 * Removes obsolete update sites created for the component (we are now using an update site for the package, not the
	 * component).
	 *
	 * @param   JInstallerAdapterComponent  $parent  The parent installer
	 */
	protected function removeObsoleteUpdateSites($parent)
	{
		$db = $parent->getParent()->getDBO();

		$query = $db->getQuery(true)
		            ->select($db->qn('extension_id'))
		            ->from($db->qn('#__extensions'))
		            ->where($db->qn('type') . ' = ' . $db->q('component'))
		            ->where($db->qn('name') . ' = ' . $db->q($this->componentName));

		try
		{
			$extensionId = $db->setQuery($query)->loadResult();
		}
		catch (Exception $e)
		{
			// Your database is broken.
			return;
		}

		if (!$extensionId)
		{
			return;
		}

		$query = $db->getQuery(true)
		            ->select($db->qn('update_site_id'))
		            ->from($db->qn('#__update_sites_extensions'))
		            ->where($db->qn('extension_id') . ' = ' . $db->q($extensionId));

		try
		{
			$ids = $db->setQuery($query)->loadColumn(0);
		}
		catch (Exception $e)
		{
			// Your database is broken.
			return;
		}

		if (!is_array($ids) && empty($ids))
		{
			return;
		}

		foreach ($ids as $id)
		{
			$query = $db->getQuery(true)
			            ->delete($db->qn('#__update_sites'))
			            ->where($db->qn('update_site_id') . ' = ' . $db->q($id));
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (\Exception $e)
			{
				// Do not fail in this case
			}
		}
	}

}
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
defined('_JEXEC') or die();

// Load FOF if not already loaded
if (!defined('F0F_INCLUDED'))
{
	$paths = array(
		(defined('JPATH_LIBRARIES') ? JPATH_LIBRARIES : JPATH_ROOT . '/libraries') . '/f0f/include.php',
		__DIR__ . '/fof/include.php',
	);

	foreach ($paths as $filePath)
	{
		if (!defined('F0F_INCLUDED') && file_exists($filePath))
		{
			@include_once $filePath;
		}
	}
}

// Pre-load the installer script class from our own copy of FOF
if (!class_exists('F0FUtilsInstallscript', false))
{
	@include_once __DIR__ . '/fof/utils/installscript/installscript.php';
}

// Pre-load the database schema installer class from our own copy of FOF
if (!class_exists('F0FDatabaseInstaller', false))
{
	@include_once __DIR__ . '/fof/database/installer.php';
}

// Pre-load the update utility class from our own copy of FOF
if (!class_exists('F0FUtilsUpdate', false))
{
	@include_once __DIR__ . '/fof/utils/update/update.php';
}

// Pre-load the cache cleaner utility class from our own copy of FOF
if (!class_exists('F0FUtilsCacheCleaner', false))
{
	@include_once __DIR__ . '/fof/utils/cache/cleaner.php';
}

class Com_AdmintoolsInstallerScript extends F0FUtilsInstallscript
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
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumJoomlaVersion = '3.0.0';

	/**
	 * The minimum PHP version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumPHPVersion = '5.3.4';

	/**
	 * The list of extra modules and plugins to install on component installation / update and remove on component
	 * uninstallation.
	 *
	 * @var   array
	 */
	protected $installation_queue = array
	(
		// modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules' => array(
				'admin' => array(//'atjupgrade' => array('cpanel', 1)
			),
			'site'  => array(

			)
		),
		// plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => array(
		   'system'    => array(
			   'admintools'         => 1,
			   'oneclickaction'     => 0,
			   'atoolsupdatecheck'  => 0,
			   'atoolsjupdatecheck' => 0
		   ),
		)
	);

	/**
	 * The list of obsolete extra modules and plugins to uninstall on component upgrade / installation.
	 *
	 * @var array
	 */
	protected $uninstallation_queue = array
	(
		// modules => { (folder) => { (module) }* }*
		'modules' => array
		(
			'admin' => array
			(
				'atjupgrade'
			),
			'site'  => array(

			)
		),
		// plugins => { (folder) => { (element) }* }*
		'plugins' => array
		(
			'installer'    => array(
				'admintools',
			),
			'quickicon' => array
			(
				'atoolsjupdatecheck'
			),

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
			'cache/com_admintools.updates.php',
			'cache/com_admintools.updates.ini',
			'administrator/cache/com_admintools.updates.php',
			'administrator/cache/com_admintools.updates.ini',

			'administrator/components/com_admintools/controllers/acl.php',
			'administrator/components/com_admintools/controllers/default.php',
			'administrator/components/com_admintools/controllers/ipautoban.php',
			'administrator/components/com_admintools/models/acl.php',
			'administrator/components/com_admintools/models/base.php',
			'administrator/components/com_admintools/models/ipautoban.php',
			'administrator/components/com_admintools/models/ipbl.php',
			'administrator/components/com_admintools/models/ipwl.php',
			'administrator/components/com_admintools/models/log.php',
			'administrator/components/com_admintools/tables/badwords.php',
			'administrator/components/com_admintools/tables/base.php',
			'administrator/components/com_admintools/tables/customperms.php',
			'administrator/components/com_admintools/tables/redirs.php',
			'administrator/components/com_admintools/tables/wafexceptions.php',
			'administrator/components/com_admintools/views/badwords/view.html.php',
			'administrator/components/com_admintools/views/base.view.html.php',

			'administrator/components/com_admintools/helpers/postinstall.php',

			'administrator/components/com_admintools/fof/LICENSE.txt',
			'administrator/components/com_admintools/fof/controller.php',
			'administrator/components/com_admintools/fof/dispatcher.php',
			'administrator/components/com_admintools/fof/index.html',
			'administrator/components/com_admintools/fof/inflector.php',
			'administrator/components/com_admintools/fof/input.php',
			'administrator/components/com_admintools/fof/model.php',
			'administrator/components/com_admintools/fof/query.abstract.php',
			'administrator/components/com_admintools/fof/query.element.php',
			'administrator/components/com_admintools/fof/query.mysql.php',
			'administrator/components/com_admintools/fof/query.mysqli.php',
			'administrator/components/com_admintools/fof/query.sqlazure.php',
			'administrator/components/com_admintools/fof/query.sqlsrv.php',
			'administrator/components/com_admintools/fof/table.php',
			'administrator/components/com_admintools/fof/template.utils.php',
			'administrator/components/com_admintools/fof/toolbar.php',
			'administrator/components/com_admintools/fof/view.csv.php',
			'administrator/components/com_admintools/fof/view.html.php',
			'administrator/components/com_admintools/fof/view.json.php',
			'administrator/components/com_admintools/fof/view.php',

			// Joomla! update files
			'administrator/components/com_admintools/restore.php',
			'administrator/components/com_admintools/controllers/jupdate.php',
			'administrator/components/com_admintools/models/jupdate.php',

			// CSS/JS Combination
			'plugins/system/admintools/admintools/cssmin.php',

			// Obsolete System - Admin Tools files
			'plugins/system/admintools/admintools/pro.php',
			'plugins/system/admintools/admintools/core.php',

			// Removed features in Admin Tools 3.5.0
			'administrator/components/com_admintools/models/adminuser.php',
			'administrator/components/com_admintools/controllers/adminuser.php',

			'administrator/components/com_admintools/models/dbprefix.php',
			'administrator/components/com_admintools/controllers/dbprefix.php',

			'administrator/components/com_admintools/models/twofactor.php',
			'administrator/components/com_admintools/controllers/twofactor.php',
			'plugins/system/jadmintools/feature/twofactorauth.php',

			'administrator/components/com_admintools/controllers/postsetup.php',

			'administrator/components/com_admintools/helpers/ip.php',

			// Obsolete files in .htaccess and NginX Maker
			'administrator/components/com_admintools/views/htmaker/view.raw.php',
			'administrator/components/com_admintools/views/nginxmaker/view.raw.php',

			// Removed features
			'plugins/system/admintools/feature/xssshield.php',

			// No longer needed
			'administrator/components/com_admintools/helpers/jsonlib.php',

			// Fastcheck / integrity check
			'administrator/components/com_admintools/controllers/checkfile.php',
		),
		'folders' => array(
			'administrator/components/com_admintools/views/acl',
			'administrator/components/com_admintools/views/ipautoban',
			'administrator/components/com_admintools/views/ipbl',
			'administrator/components/com_admintools/views/ipwl',
			'administrator/components/com_admintools/views/log',

			// Bad behaviour integration
			'plugins/system/admintools/admintools/badbehaviour',

			// Joomla! update files
			'administrator/components/com_admintools/classes',
			'administrator/components/com_admintools/views/jupdate',

			// Removed features in Admin Tools 3.5.0
			'administrator/components/com_admintools/views/adminuser',
			'administrator/components/com_admintools/views/dbprefix',
			'administrator/components/com_admintools/views/twofactor',
			'administrator/components/com_admintools/views/postsetup',

			// Obsolete directories
			'administrator/components/com_admintools/akeeba',
			'administrator/components/com_admintools/fof',

			// Public media directory (moved to administrator)
			'media/com_admintools',

			// Fastcheck / integrity check
			'administrator/components/com_admintools/views/checkfiles',
		)
	);

	/**
	 * A list of scripts to be copied to the "cli" directory of the site
	 *
	 * @var   array
	 */
	protected $cliScriptFiles = array(
		'admintools-filescanner.php',
		'admintools-update.php',
		'admintools-dbrepair.php',
	);

	/**
	 * Runs after install, update or discover_update
	 *
	 * @param string     $type install, update or discover_update
	 * @param JInstaller $parent
	 */
	function postflight($type, $parent)
	{
		/** @var AdmintoolsModelStats $model */
		$this->isPaid =
			is_dir($parent->getParent()->getPath('source') . '/plugins/system/admintools/admintools/pro.php');

		// Let's install common tables
		$model = F0FModel::getTmpInstance('Stats', 'AdmintoolsModel');

		if (method_exists($model, 'checkAndFixCommonTables'))
		{
			$model->checkAndFixCommonTables();
		}

		parent::postflight($type, $parent);

		$this->uninstallObsoletePostinstallMessages();

		$this->removeFOFUpdateSites();

		// Set the configuration wizad flag on update (so as not to bother existing users)
		if ($type == 'update')
		{
			if (!class_exists('AdmintoolsModelStorage'))
			{
				include_once JPATH_ADMINISTRATOR . '/components/com_admintools/models/storage.php';
			}

			if (class_exists('AdmintoolsModelStorage'))
			{
				$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
				$params->load();
				$params->setValue('quickstart', 1, true);
			}

		}
	}

	/**
	 * Renders the post-installation message
	 */
	function renderPostInstallation($status, $fofInstallationStatus, $strapperInstallationStatus, $parent)
	{
		$this->warnAboutJSNPowerAdmin();

		?>
		<div style="margin: 1em; font-size: 14pt; background-color: #fffff9; color: black">
			You can download translation files <a href="http://cdn.akeebabackup.com/language/admintools/index.html">directly
				from our CDN page</a>.
		</div>
		<img src="<?php echo rtrim(JUri::base(), '/') ?>/components/com_admintools/media/images/admintools-48.png" width="48"
			 height="48" alt="Admin Tools" align="right"/>

		<h2>Admin Tools Installation Status</h2>

		<?php
		parent::renderPostInstallation($status, $fofInstallationStatus, $strapperInstallationStatus, $parent);

        /** @var AdmintoolsModelStats $model */
        $model  = F0FModel::getTmpInstance('Stats', 'AdmintoolsModel');

        if(method_exists($model, 'collectStatistics'))
        {
            $iframe = $model->collectStatistics(true);

            if($iframe)
            {
                echo $iframe;
            }
        }
	}

	protected function renderPostUninstallation($status, $parent)
	{
		?>
		<h2>Admin Tools Uninstallation Status</h2>
		<?php
		parent::renderPostUninstallation($status, $parent);
	}

	private function uninstallObsoletePostinstallMessages()
	{
		$db = F0FPlatform::getInstance()->getDbo();

		$obsoleteTitleKeys = array(
			'COM_ADMINTOOLS_POSTSETUP_LBL_AUTOJUPDATE',
			'COM_ADMINTOOLS_POSTSETUP_LBL_ACCEPTLICENSE',
			'COM_ADMINTOOLS_POSTSETUP_LBL_ACCEPTSUPPORT',
		);

		foreach ($obsoleteTitleKeys as $obsoleteKey)
		{

			// Remove the post-installation messages
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
		$jsnPAManifest = new JRegistry();
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
	Tip: You can disable JSN PowerAdmin to see the menu items to {$this->componentName}.
</p>
</div>

HTML;

	}

	/**
	 * Remove FOF 2.x update sites
	 */
	private function removeFOFUpdateSites()
	{
		$db = F0FPlatform::getInstance()->getDbo();
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

}
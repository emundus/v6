<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Admin\Model\ConfigureWAF;
use Akeeba\AdminTools\Admin\Model\Stats;
use FOF40\Container\Container;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Adapter\ComponentAdapter;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

// Load FOF if not already loaded
if (!defined('FOF40_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof40/include.php'))
{
	throw new RuntimeException('This extension requires FOF 4.');
}

class Com_AdmintoolsInstallerScript extends \FOF40\InstallScript\Component
{
	/**
	 * The component's name
	 *
	 * @var   string
	 */
	public $componentName = 'com_admintools';

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
	protected $minimumPHPVersion = '7.2.0';

	/**
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumJoomlaVersion = '3.9.0';

	/**
	 * The maximum Joomla! version this extension can be installed on
	 *
	 * @var   string
	 */
	protected $maximumJoomlaVersion = '4.0.999';

	/**
	 * Obsolete files and folders to remove from the free version only. This is used when you move a feature from the
	 * free version of your extension to its paid version. If you don't have such a distinction you can ignore this.
	 *
	 * @var   array
	 */
	protected $removeFilesFree = [
		'files'   => [
			// Pro WAF features
			'plugins/system/admintools/feature/apache401.php',
			'plugins/system/admintools/feature/autoipfiltering.php',
			'plugins/system/admintools/feature/awayschedule.php',
			'plugins/system/admintools/feature/badwords.php',
			'plugins/system/admintools/feature/blockemaildomains.php',
			'plugins/system/admintools/feature/cachecleaner.php',
			'plugins/system/admintools/feature/cacheexpire.php',
			'plugins/system/admintools/feature/cleantemp.php',
			'plugins/system/admintools/feature/configmonitor.php',
			'plugins/system/admintools/feature/consolewarn.php',
			'plugins/system/admintools/feature/criticalfiles.php',
			'plugins/system/admintools/feature/criticalfilesglobal.php',
			'plugins/system/admintools/feature/csrfshield.php',
			'plugins/system/admintools/feature/customblock.php',
			'plugins/system/admintools/feature/customgenerator.php',
			'plugins/system/admintools/feature/deleteinactive.php',
			'plugins/system/admintools/feature/dfishield.php',
			'plugins/system/admintools/feature/disableobsoleteadmins.php',
			'plugins/system/admintools/feature/emailfailedadminlong.php',
			'plugins/system/admintools/feature/emailonlogin.php',
			'plugins/system/admintools/feature/emailphpexceptions.php',
			'plugins/system/admintools/feature/geoblock.php',
			'plugins/system/admintools/feature/ipblacklist.php',
			'plugins/system/admintools/feature/ipwhitelist.php',
			'plugins/system/admintools/feature/leakedpwd.php',
			'plugins/system/admintools/feature/muashield.php',
			'plugins/system/admintools/feature/nofesalogin.php',
			'plugins/system/admintools/feature/nonewadmins.php',
			'plugins/system/admintools/feature/phpshield.php',
			'plugins/system/admintools/feature/projecthoneypot.php',
			'plugins/system/admintools/feature/quickstart.php',
			'plugins/system/admintools/feature/removeoldlog.php',
			'plugins/system/admintools/feature/resetjoomlatfa.php',
			'plugins/system/admintools/feature/rfishield.php',
			'plugins/system/admintools/feature/saveusersignupip.php',
			'plugins/system/admintools/feature/secretword.php',
			'plugins/system/admintools/feature/selfprotect.php',
			'plugins/system/admintools/feature/sessioncleaner.php',
			'plugins/system/admintools/feature/sessionoptimiser.php',
			'plugins/system/admintools/feature/sessionshield.php',
			'plugins/system/admintools/feature/shield404.php',
			'plugins/system/admintools/feature/sqlishield.php',
			'plugins/system/admintools/feature/superuserslist.php',
			'plugins/system/admintools/feature/templateswitch.php',
			'plugins/system/admintools/feature/tempsuperuser.php',
			'plugins/system/admintools/feature/thirdpartyexception.php',
			'plugins/system/admintools/feature/tmplswitch.php',
			'plugins/system/admintools/feature/trackfailedlogins.php',
			'plugins/system/admintools/feature/uploadshield.php',
			'plugins/system/admintools/feature/wafblacklist.php',

			// Pro features
			'administrator/components/com_admintools/Controller/AutoBannedAddress.php',
			'administrator/components/com_admintools/Controller/AutoBannedAddresses.php',
			'administrator/components/com_admintools/Controller/BadWord.php',
			'administrator/components/com_admintools/Controller/BadWords.php',
			'administrator/components/com_admintools/Controller/BlacklistedAddress.php',
			'administrator/components/com_admintools/Controller/BlacklistedAddresses.php',
			'administrator/components/com_admintools/Controller/ConfigureWAF.php',
			'administrator/components/com_admintools/Controller/ExceptionsFromWAF.php',
			'administrator/components/com_admintools/Controller/GeographicBlocking.php',
			'administrator/components/com_admintools/Controller/HtaccessMaker.php',
			'administrator/components/com_admintools/Controller/ImportAndExport.php',
			'administrator/components/com_admintools/Controller/IPAutoBanHistories.php',
			'administrator/components/com_admintools/Controller/IPAutoBanHistory.php',
			'administrator/components/com_admintools/Controller/NginXConfMaker.php',
			'administrator/components/com_admintools/Controller/QuickStart.php',
			'administrator/components/com_admintools/Controller/Scan.php',
			'administrator/components/com_admintools/Controller/ScanAlert.php',
			'administrator/components/com_admintools/Controller/ScanAlerts.php',
			'administrator/components/com_admintools/Controller/Scans.php',
			'administrator/components/com_admintools/Controller/SchedulingInformation.php',
			'administrator/components/com_admintools/Controller/SecurityException.php',
			'administrator/components/com_admintools/Controller/SecurityExceptions.php',
			'administrator/components/com_admintools/Controller/ServerConfigMaker.php',
			'administrator/components/com_admintools/Controller/WAFBlacklistedRequest.php',
			'administrator/components/com_admintools/Controller/WAFBlacklistedRequests.php',
			'administrator/components/com_admintools/Controller/WAFEmailTemplate.php',
			'administrator/components/com_admintools/Controller/WAFEmailTemplates.php',
			'administrator/components/com_admintools/Controller/WebApplicationFirewall.php',
			'administrator/components/com_admintools/Controller/WebConfigMaker.php',
			'administrator/components/com_admintools/Controller/WhitelistedAddress.php',
			'administrator/components/com_admintools/Controller/WhitelistedAddresses.php',

			'administrator/components/com_admintools/Model/AutoBannedAddresses.php',
			'administrator/components/com_admintools/Model/BadWords.php',
			'administrator/components/com_admintools/Model/BlacklistedAddresses.php',
			'administrator/components/com_admintools/Model/ConfigureWAF.php',
			'administrator/components/com_admintools/Model/ExceptionsFromWAF.php',
			'administrator/components/com_admintools/Model/GeographicBlocking.php',
			'administrator/components/com_admintools/Model/HtaccessMaker.php',
			'administrator/components/com_admintools/Model/ImportAndExport.php',
			'administrator/components/com_admintools/Model/IPAutoBanHistories.php',
			'administrator/components/com_admintools/Model/NginXConfMaker.php',
			'administrator/components/com_admintools/Model/QuickStart.php',
			'administrator/components/com_admintools/Model/ScanAlerts.php',
			'administrator/components/com_admintools/Model/Scans.php',
			'administrator/components/com_admintools/Model/SchedulingInformation.php',
			'administrator/components/com_admintools/Model/SecurityExceptions.php',
			'administrator/components/com_admintools/Model/ServerConfigMaker.php',
			'administrator/components/com_admintools/Model/WAFBlacklistedRequests.php',
			'administrator/components/com_admintools/Model/WAFEmailTemplates.php',
			'administrator/components/com_admintools/Model/WebApplicationFirewall.php',
			'administrator/components/com_admintools/Model/WebConfigMaker.php',
			'administrator/components/com_admintools/Model/WhitelistedAddresses.php',

			// CLI scripts
			'cli/admintools-dbrepair.php',
			'cli/admintools-filescanner.php',
		],
		'folders' => [
			// CLI common files
			'administrator/components/com_admintools/assets/cli',

			// Pro features
			'administrator/components/com_admintools/Model/Scanner',

			'administrator/components/com_admintools/tmpl/AutoBannedAddresses',
			'administrator/components/com_admintools/tmpl/BadWords',
			'administrator/components/com_admintools/tmpl/BlacklistedAddresses',
			'administrator/components/com_admintools/tmpl/ConfigureWAF',
			'administrator/components/com_admintools/tmpl/ExceptionsFromWAF',
			'administrator/components/com_admintools/tmpl/GeographicBlocking',
			'administrator/components/com_admintools/tmpl/HtaccessMaker',
			'administrator/components/com_admintools/tmpl/ImportAndExport',
			'administrator/components/com_admintools/tmpl/IPAutoBanHistories',
			'administrator/components/com_admintools/tmpl/NginXConfMaker',
			'administrator/components/com_admintools/tmpl/QuickStart',
			'administrator/components/com_admintools/tmpl/ScanAlerts',
			'administrator/components/com_admintools/tmpl/Scans',
			'administrator/components/com_admintools/tmpl/SchedulingInformation',
			'administrator/components/com_admintools/tmpl/SecurityExceptions',
			'administrator/components/com_admintools/tmpl/WAFBlacklistedRequests',
			'administrator/components/com_admintools/tmpl/WAFEmailTemplates',
			'administrator/components/com_admintools/tmpl/WebApplicationFirewall',
			'administrator/components/com_admintools/tmpl/WebConfigMaker',
			'administrator/components/com_admintools/tmpl/WhitelistedAddresses',

			// Frontend pro features
			'components/com_admintools/Controller',
			'components/com_admintools/Model',
			'components/com_admintools/View',
		],
	];

	/**
	 * Obsolete files and folders to remove from both paid and free releases. This is used when you refactor code and
	 * some files inevitably become obsolete and need to be removed.
	 *
	 * @var   array
	 */
	protected $removeFilesAllVersions = [
		'files'   => [
			// Obsolete CLI scripts
			'cli/admintools-update.php',

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

			'components/com_admintools/Helper/Plugin.php',

			// PLUGIN "System - Admin Tools"

			// -- CSS/JS Combination
			'plugins/system/admintools/admintools/cssmin.php',

			// -- obsolete files
			'plugins/system/admintools/admintools/pro.php',
			'plugins/system/admintools/admintools/core.php',

			// -- removed features
			'plugins/system/admintools/feature/twofactorauth.php',
			'plugins/system/admintools/feature/xssshield.php',
			// ...because some people have never updated to 3.6, apparently?!
			'plugins/system/admintools/feature/blockinstall.php',

			// Moving to FEF
			'administrator/components/com_admintools/Helper/Coloriser.php',
			'administrator/components/com_admintools/View/ScanAlerts/Form.php',
			'administrator/components/com_admintools/View/Scans/Form.php',
			'administrator/components/com_admintools/View/WAFEmailTemplates/Form.php',
			'administrator/components/com_admintools/View/IPAutoBanHistories/Form.php',
			'administrator/components/com_admintools/View/AutoBannedAddresses/Form.php',
			'administrator/components/com_admintools/View/ExceptionsFromWAF/Form.php',
			'administrator/components/com_admintools/View/WAFBlacklistedRequests/Form.php',
			'administrator/components/com_admintools/View/WhitelistedAddresses/Form.php',
			'administrator/components/com_admintools/View/SecurityExceptions/Form.php',
			'administrator/components/com_admintools/View/BadWords/Form.php',
			'administrator/components/com_admintools/View/BlacklistedAddresses/Form.php',
			'administrator/components/com_admintools/View/Redirections/Form.php',

			// Replace jQplot with Chart.js
			'administrator/components/com_admintools/media/css/jquery.jqplot.min.css',
			'administrator/components/com_admintools/media/js/jquery.jqplot.min.js',
			'administrator/components/com_admintools/media/js/jqplot.highlighter.min.js',
			'administrator/components/com_admintools/media/js/jqplot.dateAxisRenderer.min.js',
			'administrator/components/com_admintools/media/js/jqplot.barRenderer.min.js',
			'administrator/components/com_admintools/media/js/jqplot.pieRenderer.min.js',
			'administrator/components/com_admintools/media/js/jqplot.hermite.min.js',

			// Obsolete eAccelerator warning
			"administrator/components/com_admintools/View/eaccelerator.php",

			// Refactored PHP File Change Scanner
			'administrator/components/com_admintools/Controller/Scanner.php',
			'administrator/components/com_admintools/Model/Scanner.php',

			// Removed Geographic IP blocking
			'administrator/components/com_admintools/Controller/GeographicBlocking.php',
			'administrator/components/com_admintools/Model/GeographicBlocking.php',
			'plugins/system/admintools/feature/geoblock.php',

			// Changelog PNG images
			'administrator/components/com_admintools/media/icons/changelog.png',

			// Moving to FEF 2
			'administrator/components/com_admintools/media/js/namespace.min.js',
			'administrator/components/com_admintools/media/js/namespace.min.map',
			'administrator/components/com_admintools/media/js/Modal.min.js',
			'administrator/components/com_admintools/media/js/Modal.min.map',
			'administrator/components/com_admintools/media/js/Tooltip.min.js',
			'administrator/components/com_admintools/media/js/Tooltip.min.map',
			'administrator/components/com_admintools/media/js/cpanelgraphs.min.js',
			'administrator/components/com_admintools/media/js/cpanelgraphs.min.map',

			// Update Charts.js
			'administrator/components/com_admintools/media/js/Chart.bundle.min.js',

			// Remove “Convert all links to HTTPS”
			'plugins/system/admintools/feature/httpsizer.php',

			// This was never meant to be a Blade template
			"administrator/components/com_admintools/tmpl/ControlPanel/phpversion_warning.blade.php",
		],
		'folders' => [
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

			// Moving to FEF
			'administrator/components/com_admintools/Form',
			'administrator/components/com_admintools/media/images',

			// Common tables (they're installed by FOF)
			'administrator/components/com_admintools/sql/common',

			// Refactored PHP File Change Scanner
			'administrator/components/com_admintools/engine',
			'administrator/components/com_admintools/platform',
			'administrator/components/com_admintools/View/Scanner',

			// Old CLI base script, replaced by FOF's base CLI script
			'administrator/components/com_admintools/assets/cli',

			// Removed Geographic IP blocking
			'administrator/components/com_admintools/View/GeographicBlocking',

			// Moving to FOF 4 and Blade views
			'administrator/components/com_admintools/View/AdminPassword/tmpl',
			'administrator/components/com_admintools/View/AutoBannedAddresses/tmpl',
			'administrator/components/com_admintools/View/BadWords/tmpl',
			'administrator/components/com_admintools/View/BlacklistedAddresses/tmpl',
			'administrator/components/com_admintools/View/ChangeDBCollation/tmpl',
			'administrator/components/com_admintools/View/CheckTempAndLogDirectories/tmpl',
			'administrator/components/com_admintools/View/CleanTempDirectory/tmpl',
			'administrator/components/com_admintools/View/ConfigureFixPermissions/tmpl',
			'administrator/components/com_admintools/View/ConfigureWAF/tmpl',
			'administrator/components/com_admintools/View/ControlPanel/tmpl',
			'administrator/components/com_admintools/View/DatabaseTools/tmpl',
			'administrator/components/com_admintools/View/EmergencyOffline/tmpl',
			'administrator/components/com_admintools/View/ExceptionsFromWAF/tmpl',
			'administrator/components/com_admintools/View/FixPermissions/tmpl',
			'administrator/components/com_admintools/View/HtaccessMaker/tmpl',
			'administrator/components/com_admintools/View/IPAutoBanHistories/tmpl',
			'administrator/components/com_admintools/View/ImportAndExport/tmpl',
			'administrator/components/com_admintools/View/MasterPassword/tmpl',
			'administrator/components/com_admintools/View/NginXConfMaker/tmpl',
			'administrator/components/com_admintools/View/QuickStart/tmpl',
			'administrator/components/com_admintools/View/Redirections/tmpl',
			'administrator/components/com_admintools/View/SEOAndLinkTools/tmpl',
			'administrator/components/com_admintools/View/ScanAlerts/tmpl',
			'administrator/components/com_admintools/View/Scans/tmpl',
			'administrator/components/com_admintools/View/SchedulingInformation/tmpl',
			'administrator/components/com_admintools/View/SecurityExceptions/tmpl',
			'administrator/components/com_admintools/View/TempSuperUsers/tmpl',
			'administrator/components/com_admintools/View/UnblockIP/tmpl',
			'administrator/components/com_admintools/View/WAFBlacklistedRequests/tmpl',
			'administrator/components/com_admintools/View/WAFEmailTemplates/tmpl',
			'administrator/components/com_admintools/View/WebApplicationFirewall/tmpl',
			'administrator/components/com_admintools/View/WebConfigMaker/tmpl',
			'administrator/components/com_admintools/View/WhitelistedAddresses/tmpl',
			'administrator/components/com_admintools/ViewTemplates',
		],
	];

	/**
	 * The list of obsolete extra modules and plugins to uninstall on component upgrade / installation.
	 *
	 * @var array
	 */
	protected $uninstallation_queue = [
		// modules => { (folder) => { (module) }* }*
		'modules' => [
			'admin' => [],
			'site'  => [],
		],
		// plugins => { (folder) => { (element) }* }*
		'plugins' => [
			'system' => [
				'atoolsjupdatecheck',
			],
		],
	];


	/**
	 * Runs on installation
	 *
	 * @param   ComponentAdapter  $parent  The parent object
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
	 * @param   string            $type                                  Installation type (install, update,
	 *                                                                   discover_install)
	 * @param   ComponentAdapter  $parent                                Parent object
	 *
	 * @return  boolean  True to let the installation proceed, false to halt the installation
	 */
	public function preflight(string $type, ComponentAdapter $parent): bool
	{
		$this->isPaid = is_dir($parent->getParent()->getPath('source') . '/backend/engine');

		return parent::preflight($type, $parent);
	}

	/**
	 * Runs after install, update or discover_update
	 *
	 * @param   string            $type  install, update or discover_update
	 * @param   ComponentAdapter  $parent
	 *
	 * @return  boolean  True to let the installation proceed, false to halt the installation
	 */
	public function postflight(string $type, ComponentAdapter $parent): void
	{
		// Let's install common tables
		$container = null;
		$model     = null;

		if (class_exists('FOF40\\Container\\Container'))
		{
			try
			{
				$container = Container::getInstance('com_admintools');
			}
			catch (Exception $e)
			{
				$container = null;
			}
		}

		if (is_object($container) && class_exists('FOF40\\Container\\Container') && ($container instanceof Container))
		{
			/** @var Stats $model */
			try
			{
				$model = $container->factory->model('Stats')->tmpInstance();
			}
			catch (Exception $e)
			{
				$model = null;
			}
		}

		// Parent method
		parent::postflight($type, $parent);

		// Add ourselves to the list of extensions depending on Akeeba FEF
		$this->addDependency('file_fef', $this->componentName);

		// Uninstall post-installation messages we are no longer using
		$this->uninstallObsoletePostinstallMessages();

		// Remove the update sites for this component on installation. The update sites are now handled at the package
		// level.
		$this->removeObsoleteUpdateSites($parent);

		// Remove the FOF 2.x update sites (annoying leftovers)
		$this->removeFOFUpdateSites();

		/**
		 * Actions to take ONLY on update
		 *
		 * - Set the quick start wizard flag on update (so as not to bother existing users)
		 * - Update the server config makers
		 */
		if (!defined('ADMINTOOLS_THIS_IS_INSTALLATION_FROM_SCRATCH'))
		{
			if (!class_exists('Akeeba\\AdminTools\\Admin\\Helper\\Storage'))
			{
				@include_once $parent->getParent()->getPath('source') . '/backend/Helper/Storage.php';
			}

			if (class_exists('Akeeba\\AdminTools\\Admin\\Helper\\Storage'))
			{
				$params = new Storage();
				$params->load();
				$params->setValue('quickstart', 1, true);

				$this->updateConfigMaker($params);
			}
		}

		/**
		 * Code to execute only on updates
		 */

		if (!defined('ADMINTOOLS_THIS_IS_INSTALLATION_FROM_SCRATCH'))
		{
			$this->_removeDefaultSuperUsersStorage();

			$this->_upgradeDisableMonitorSuperUsers($parent);

			$this->_upgradeRemoveObsoleteLoginSecurityLogEntries($parent);

			$this->_upgradeDeleteTextLogfiles();

			$this->migrateIpWorkarounds($container);
		}

		// Replace the system plugin with the actionlog plugin for logging user actions
		$this->switchActionLogPlugins();
	}

	/**
	 * Renders the post-installation message
	 */
	protected function renderPostInstallation(ComponentAdapter $parent): void
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
		<img src="../administrator/components/com_admintools/media/images/admintools-48.png" width="48" height="48"
			 alt="Admin Tools" align="right" />

		<h2>Welcome to Admin Tools!</h2>

		<fieldset>
			<?php if (ADMINTOOLS_PRO): ?>
				<p>
					We strongly recommend watching our <a
							href="https://www.akeeba.com/videos/1207-admin-tools.html">video
						tutorials</a> before using this component.
				</p>

				<p>
					If this is the first time you install Admin Tools on your site please run the
					<a href="index.php?option=com_admintools&view=QuickStart">Quick Setup Wizard</a>. It will guide you
					through
					tailoring Admin Tools for your site. <strong>Pay attention to the messages on that page. They
						contain
						information to unblock yourself should you inadvertently block yourself out of your
						site!</strong>
				</p>
			<?php endif; ?>

			<p>
				By installing this component you are implicitly accepting
				<a href="https://www.akeeba.com/license.html">its license (GNU GPLv3)</a> and our
				<a href="https://www.akeeba.com/privacy-policy.html">Terms of Service</a>,
				including our Support Policy.
			</p>
		</fieldset>

		<?php
		// Let's install common tables
		$container = null;
		$model     = null;

		if (class_exists('FOF40\\Container\\Container'))
		{
			try
			{
				$container = Container::getInstance('com_admintools');
			}
			catch (Exception $e)
			{
				$container = null;
			}
		}

		if (is_object($container) && class_exists('FOF40\\Container\\Container') && ($container instanceof Container))
		{
			/** @var Stats $model */
			try
			{
				$model = $container->factory->model('Stats')->tmpInstance();
			}
			catch (Exception $e)
			{
				$model = null;
			}
		}

		/** @var Stats $model */
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
		catch (Exception $e)
		{
		}
	}

	protected function renderPostUninstallation(ComponentAdapter $parent): void
	{
		?>
		<h2>Admin Tools Uninstallation Status</h2>
		<p>We are sorry that you decided to uninstall Admin Tools. Please let us know why by using the <a
					href="https://www.akeeba.com/contact-us.html" target="_blank">Contact Us form on our site</a>.
			We
			appreciate your feedback; it helps us develop better software!</p>
		<?php
	}

	/**
	 * Removes obsolete update sites created for the component (we are now using an update site for the package, not the
	 * component).
	 *
	 * @param   ComponentAdapter  $parent  The parent installer
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
			catch (Exception $e)
			{
				// Do not fail in this case
			}
		}
	}

	private function _removeDefaultSuperUsersStorage()
	{
		if (class_exists('FOF40\\Container\\Container'))
		{
			try
			{
				$container = Container::getInstance('com_admintools');
			}
			catch (Exception $e)
			{
				$container = null;
			}
		}

		if (is_object($container) && class_exists('FOF40\\Container\\Container') && ($container instanceof Container))
		{
			$container->params->set('default_super_user_groups', null);
		}
	}

	/**
	 * The PowerAdmin extension makes menu items disappear. People assume it's our fault. JSN PowerAdmin authors don't
	 * own up to their software's issue. I have no choice but to warn our users about the faulty third party software.
	 */
	private function warnAboutJSNPowerAdmin()
	{
		$db = Factory::getDbo();

		$query         = $db->getQuery(true)
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

		$query      = $db->getQuery(true)
			->select('manifest_cache')
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q('com_poweradmin'))
			->where($db->qn('enabled') . ' = ' . $db->q('1'));
		$paramsJson = $db->setQuery($query)->loadResult();

		$className = class_exists('JRegistry') ? 'JRegistry' : '\Joomla\Registry\Registry';

		/** @var Registry $jsnPAManifest */
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
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->qn('#__update_sites'))
			->where($db->qn('location') . ' = ' . $db->q('http://cdn.akeebabackup.com/updates/fof.xml'));
		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			// Do nothing on failure
		}

	}

	private function uninstallObsoletePostinstallMessages()
	{
		$db = Factory::getDbo();

		$obsoleteTitleKeys = [
			'COM_ADMINTOOLS_POSTSETUP_LBL_AUTOJUPDATE',
			'COM_ADMINTOOLS_POSTSETUP_LBL_ACCEPTLICENSE',
			'COM_ADMINTOOLS_POSTSETUP_LBL_ACCEPTSUPPORT',
		];

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
	 * If this is an update, disable the "Monitor Super User accounts" feature. It only happens ONCE. This will
	 * prevent people from complaining about this feature doing exactly what it's supposed to do.
	 *
	 * @param   ComponentAdapter  $parent
	 *
	 * @return  void
	 *
	 * @since   5.0.0
	 */
	private function _upgradeDisableMonitorSuperUsers($parent)
	{
		if (!class_exists('Akeeba\\AdminTools\\Admin\\Helper\\Storage'))
		{
			@include_once $parent->getParent()->getPath('source') . '/backend/Helper/Storage.php';
		}

		if (!class_exists('Akeeba\\AdminTools\\Admin\\Helper\\Storage'))
		{
			return;
		}

		$params = new Storage();
		$params->load();


		if ($params->getValue('disabled_superuserslist', 0) != 0)
		{
			return;
		}

		$params->setValue('superuserslist', 0, false);
		$params->setValue('disabled_superuserslist', 1, true);
	}

	/**
	 * If this is an update, find the security exception logs for failed logins which may have contained failed
	 * login passwords and remove them from the database.
	 *
	 * @param   ComponentAdapter  $parent
	 *
	 * @return  void
	 *
	 * @since   5.1.0
	 */
	private function _upgradeRemoveObsoleteLoginSecurityLogEntries($parent)
	{
		if (!class_exists('Akeeba\\AdminTools\\Admin\\Helper\\Storage'))
		{
			@include_once $parent->getParent()->getPath('source') . '/backend/Helper/Storage.php';
		}

		if (!class_exists('Akeeba\\AdminTools\\Admin\\Helper\\Storage'))
		{
			return;
		}

		$params = new Storage();
		$params->load();

		if ($params->getValue('showpwonloginfailure', 0) != 1)
		{
			return;
		}

		// Delete existing records
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->qn('#__admintools_log'))
			->where($db->qn('reason') . ' = ' . $db->q('loginfailure'))
			->where($db->qn('reason') . ' = ' . $db->q('loginfailure'))
			->where($db->qn('extradata') . ' LIKE ' . $db->q('%Password%'));

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			// Don't die if that fails
		}

		$params->setValue('showpwonloginfailure', 0, true);
	}

	/**
	 * Delete the old style Admin Tools text log files with a .log extension
	 *
	 * @since   5.1.0
	 */
	private function _upgradeDeleteTextLogfiles()
	{
		$logpath = Factory::getConfig()->get('log_path');
		$files   = [
			$logpath . DIRECTORY_SEPARATOR . 'admintools_breaches.log',
			$logpath . DIRECTORY_SEPARATOR . 'admintools_breaches.log.1',
		];

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

			\Joomla\CMS\Filesystem\File::delete($file);
		}
	}

	/**
	 * If IP Workarounds are set to No, migrate them to Auto
	 *
	 * @param   Container  $container
	 */
	private function migrateIpWorkarounds($container)
	{
		/**
		 * The ConfigureWAF model does not exist in the Core version. Moreover, the Core version lacks the IP
		 * Workarounds feature. It makes no sense running this when installing Core. Worse, since the model is not
		 * present it causes an installation failure.
		 */
		if (!$this->isPaid)
		{
			return;
		}

		// Value already migrated, stop here
		if ($container->params->get('ipworkarounds_migrated', 0))
		{
			return;
		}

		/** @var ConfigureWAF $wafModel */
		$wafModel = $container->factory->model('ConfigureWAF');
		$wafModel->migrateIpWorkarounds();

		// Finally save the flag so we won't do it again
		$container->params->set('ipworkarounds_migrated', 1);
		$container->params->save();
	}

	private function switchActionLogPlugins()
	{
		$db = Factory::getDbo();

		// Does the plg_system_admintoolsactionlog plugin exist? If not, there's nothing to do here.
		$query = $db->getQuery(true)
			->select('*')
			->from('#__extensions')
			->where($db->qn('type') . ' = ' . $db->q('plugin'))
			->where($db->qn('folder') . ' = ' . $db->q('system'))
			->where($db->qn('element') . ' = ' . $db->q('admintoolsactionlog'));
		try
		{
			$result = $db->setQuery($query)->loadAssoc();

			if (empty($result))
			{
				return;
			}

			$eid = $result['extension_id'];
		}
		catch (Exception $e)
		{
			return;
		}

		// If plg_system_admintoolsactionlog is enabled: enable plg_actionlog_admintools
		if (PluginHelper::isEnabled('system', 'admintoolsactionlog'))
		{
			$query = $db->getQuery(true)
				->update($db->qn('#__extensions'))
				->set($db->qn('enabled') . ' = ' . $db->q(1))
				->where($db->qn('type') . ' = ' . $db->q('plugin'))
				->where($db->qn('folder') . ' = ' . $db->q('actionlog'))
				->where($db->qn('element') . ' = ' . $db->q('admintools'));
			try
			{
				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
			}
		}

		// Deactivate plg_system_admintoolsactionlog
		$query = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('enabled') . ' = ' . $db->q(0))
			->where($db->qn('type') . ' = ' . $db->q('plugin'))
			->where($db->qn('folder') . ' = ' . $db->q('system'))
			->where($db->qn('element') . ' = ' . $db->q('admintoolsactionlog'));
		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
		}

		/**
		 * Here's a bummer. If you try to uninstall the plg_system_admintoolsactionlog plugin Joomla throws a nonsensical
		 * error message about the plugin's XML manifest missing -- after it has already uninstalled the plugin! This
		 * error causes the package installation to fail which results in the extension being installed BUT the database
		 * record of the package NOT being present which makes it impossible to uninstall.
		 *
		 * So I have to hack my way around it which is ugly but the only viable alternative :(
		 */
		try
		{
			// Safely delete the row in the extensions table
			$row = Table::getInstance('extension');
			$row->load((int) $eid);
			$row->delete($eid);

			// Delete the plugin's files
			$pluginPath = JPATH_PLUGINS . '/system/admintoolsactionlog';

			if (is_dir($pluginPath))
			{
				Folder::delete($pluginPath);
			}

			// Delete the plugin's language files
			$langFiles = [
				JPATH_ADMINISTRATOR . '/language/en-GB/en-GB.plg_system_admintoolsactionlog.ini',
				JPATH_ADMINISTRATOR . '/language/en-GB/en-GB.plg_system_admintoolsactionlog.sys.ini',
			];

			foreach ($langFiles as $file)
			{
				if (@is_file($file))
				{
					\Joomla\CMS\Filesystem\File::delete($file);
				}
			}
		}
		catch (Exception $e)
		{
			// I tried, I failed. Dear user, do NOT try to enable that old plugin. Bye!
		}
	}

	private function updateConfigMaker(Storage $storage)
	{
		$storageKeys = [
			'htconfig',
			'nginxconfig',
			'wcconfig',
		];

		$dirty = false;

		foreach ($storageKeys as $storageKey)
		{
			// Get the saved server config settings
			$savedConfig = $storage->getValue($storageKey, '');

			// No settings for this server config maker; skip over
			if (empty(trim($savedConfig)))
			{
				continue;
			}

			// Decode server config settings
			if (function_exists('base64_encode') && function_exists('base64_encode'))
			{
				$savedConfig = @base64_decode($savedConfig);
			}

			$savedConfig = @json_decode($savedConfig, true);

			//  If decoding failed, skip over this server config maker.
			if (empty($savedConfig))
			{
				continue;
			}

			// Flag the need to save changes
			$dirty = true;

			/**
			 * Update files exempted from the server front- and backend protection.
			 * - REMOVE: administrator/components/com_admintools/restore.php
			 * - ADD: administrator/components/com_akeebabackup/restore.php
			 * - ADD: administrator/components/com_joomlaupdate/restore.php
			 * - ADD: administrator/components/com_joomlaupdate/extract.php
			 */
			$remove = [
				'administrator/components/com_admintools/restore.php'
			];
			$add = [
				"administrator/components/com_akeebabackup/restore.php",
				"administrator/components/com_joomlaupdate/restore.php",
				"administrator/components/com_joomlaupdate/extract.php",
			];
			$savedConfig['exceptionfiles'] = array_merge($savedConfig['exceptionfiles'] ?: [], $add);
			$savedConfig['exceptionfiles'] = array_diff($savedConfig['exceptionfiles'] ?: [], $remove);
			$savedConfig['exceptionfiles'] = array_unique($savedConfig['exceptionfiles']);

			// Update backend file types
			$savedConfig['bepextypes'] = array_merge($savedConfig['bepextypes'] ?: [], [
				'jpe', 'jpg', 'jpeg', 'jp2', 'jpe2', 'png', 'gif', 'bmp', 'css', 'js',
				'swf', 'html', 'mpg', 'mp3', 'mpeg', 'mp4', 'avi', 'wav', 'ogg', 'ogv',
				'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar', 'pdf', 'xps',
				'txt', '7z', 'svg', 'odt', 'ods', 'odp', 'flv', 'mov', 'htm', 'ttf',
				'woff', 'woff2', 'eot', 'webp', 'xsl',
				'JPG', 'JPEG', 'PNG', 'GIF', 'CSS', 'JS', 'TTF', 'WOFF', 'WOFF2', 'EOT', 'WEBP',
			]);
			$savedConfig['bepextypes'] = array_unique($savedConfig['bepextypes']);

			// Update frontend file types
			$savedConfig['fepextypes'] = array_merge($savedConfig['fepextypes'] ?: [], [
				'jpe', 'jpg', 'jpeg', 'jp2', 'jpe2', 'png', 'gif', 'bmp', 'css', 'js',
				'swf', 'html', 'mpg', 'mp3', 'mpeg', 'mp4', 'avi', 'wav', 'ogg', 'ogv',
				'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar', 'pdf', 'xps',
				'txt', '7z', 'svg', 'odt', 'ods', 'odp', 'flv', 'mov', 'ico', 'htm',
				'ttf', 'woff', 'woff2', 'eot', 'webp', 'xsl',
				'JPG', 'JPEG', 'PNG', 'GIF', 'CSS', 'JS', 'TTF', 'WOFF', 'WOFF2', 'EOT', 'WEBP',
			]);
			$savedConfig['fepextypes'] = array_unique($savedConfig['fepextypes']);

			// Update directories where everything except .php files are allowed
			$savedConfig['exceptiondirs'] = array_merge($savedConfig['exceptiondirs'] ?: [], [
				'.well-known'
			]);
			$savedConfig['exceptiondirs'] = array_unique($savedConfig['exceptiondirs']);

			// Save the configuration back to the database
			$savedConfig       = json_encode($savedConfig);

			if (function_exists('base64_encode') && function_exists('base64_encode'))
			{
				$savedConfig = base64_encode($savedConfig);
			}

			$storage->setValue($storageKey, $savedConfig);
		}

		if ($dirty)
		{
			$storage->save();
		}
	}
}

<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\ControlPanel;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Helper\ServerTechnology;
use Akeeba\AdminTools\Admin\Model\AdminPassword;
use Akeeba\AdminTools\Admin\Model\ControlPanel;
use Akeeba\AdminTools\Admin\Model\MasterPassword;
use Akeeba\AdminTools\Admin\Model\Stats;
use Akeeba\AdminTools\Admin\View\Mixin\SystemPluginExists;
use FOF30\Date\Date;
use FOF30\View\DataView\Html as BaseView;
use JText;

class Html extends BaseView
{
	use SystemPluginExists;

	/**
	 * HTML of the processed CHANGELOG to display in the Changelog modal
	 *
	 * @var  string
	 */
	public $changeLog = '';

	/**
	 * Do I have to ask the user to provide a Download ID?
	 *
	 * @var  bool
	 */
	public $needsdlid = false;

	/**
	 * Is Joomla configuration ok? (log and tmp folders)
	 *
	 * @var  string
	 */
	public $jwarnings;

	/**
	 * Is this a pro version?
	 *
	 * @var  bool
	 */
	public $isPro;

	/**
	 * Should I display the security exceptions graphs?
	 *
	 * @var  bool
	 */
	public $showstats;

	/**
	 * Current user was blocked?
	 *
	 * @var  bool
	 */
	public $adminLocked;

	/**
	 * Do we have a valid password?
	 *
	 * @var  bool
	 */
	public $hasValidPassword;

	/**
	 * Is the Clean Temporary Directory feature available
	 *
	 * @var  bool
	 */
	public $enable_cleantmp;

	/**
	 * Is the Temporary and Log Folder Check feature available
	 *
	 * @var  bool
	 */
	public $enable_tmplogcheck;

	/**
	 * Is the Fix Permissions feature available
	 *
	 * @var  bool
	 */
	public $enable_fixperms;

	/**
	 * Is the Purge Sessions feature available
	 *
	 * @var  bool
	 */
	public $enable_purgesessions;

	/**
	 * Are the Database Tools features available
	 *
	 * @var  bool
	 */
	public $enable_dbtools;

	/**
	 * Is the Change Database Collation feature available
	 *
	 * @var  bool
	 */
	public $enable_dbchcol;

	/**
	 * Is this a MySQL server
	 *
	 * @var  bool
	 */
	public $isMySQL;

	/**
	 * The extension ID of the System - Admin Tools plugin
	 *
	 * @var  int
	 */
	public $pluginid;

	/**
	 * The error string for the front-end secret word strength issue, blank if there is no problem
	 *
	 * @var  string
	 */
	public $frontEndSecretWordIssue;

	/**
	 * Proposed new secret word for the front-end file scanner feature
	 *
	 * @var  string
	 */
	public $newSecretWord;

	/**
	 * Is this version of Admin Tools too old?
	 *
	 * @var  bool
	 */
	public $oldVersion;

	/**
	 * Is the .htaccess Maker feature supported on this server? 0 No, 1 Yes, 2 Maybe
	 *
	 * @var  int
	 */
	public $htMakerSupported;

	/**
	 * Is the NginX Conf Maker feature supported on this server? 0 No, 1 Yes, 2 Maybe
	 *
	 * @var  int
	 */
	public $nginxMakerSupported;

	/**
	 * Is the web.config Maker feature supported on this server? 0 No, 1 Yes, 2 Maybe
	 *
	 * @var  int
	 */
	public $webConfMakerSupported;

	/**
	 * Stats collection IFRAME
	 *
	 * @var  string
	 */
	public $statsIframe;

	/**
	 * The extension ID for Admin Tools
	 *
	 * @var  int
	 */
	public $extension_id;

	/**
	 * Do we need to run Quick Setup (i.e. not configured yet)?
	 *
	 * @var  bool
	 */
	public $needsQuickSetup = false;

	/**
	 * Do I have stuck updates pending?
	 *
	 * @var  bool
	 */
	public $stuckUpdates = false;

	/**
	 * The fancy formatted changelog of the component
	 *
	 * @var  string
	 */
	public $formattedChangelog = '';

	/**
	 * Did the user manually changed the server configuration file (ie .htaccess)? If so, let's warn the user that he
	 * should use the custom rule fields inside the Makers or their settings could be lost.
	 *
	 * @var bool
	 */
	public $serverConfigEdited = false;

	/**
	 * Main Control Panel task
	 *
	 * @return  void
	 */
	protected function onBeforeMain()
	{
		$this->populateSystemPluginExists();

		// Is this the Professional release?
		$this->isPro = ADMINTOOLS_PRO == 1;

		// Should we show the stats and graphs?
		$this->showstats = $this->container->params->get('showstats', 1);

		// Load the models
		/** @var ControlPanel $controlPanelModel */
		$controlPanelModel = $this->getModel();

		/** @var AdminPassword $adminPasswordModel */
		$adminPasswordModel = $this->container->factory->model('AdminPassword')->tmpInstance();

		/** @var MasterPassword $masterPasswordModel */
		$masterPasswordModel = $this->container->factory->model('MasterPassword')->tmpInstance();

		/** @var Stats $statsModel */
		$statsModel = $this->container->factory->model('Stats')->tmpInstance();

		// Is this a very old version? If it's older than 180 days let's warn the user
		$this->oldVersion = false;

		$relDate  = new Date(ADMINTOOLS_DATE, 'UTC');
		$interval = time() - $relDate->toUnix();

		if ($interval > (60 * 60 * 24 * 180))
		{
			$this->oldVersion = true;
		}

		// Get the database type
		$dbType = $this->container->db->name;

		// Pass properties to the view
		$this->isMySQL              = strpos($dbType, 'mysql') !== false;
		$this->adminLocked          = $adminPasswordModel->isLocked();
		$this->hasValidPassword     = $masterPasswordModel->hasValidPassword();
		$this->enable_cleantmp      = $masterPasswordModel->accessAllowed('CleanTempDirectory');
		$this->enable_tmplogcheck   = $masterPasswordModel->accessAllowed('CheckTempAndLogDirectories');
		$this->enable_fixperms      = $masterPasswordModel->accessAllowed('FixPermissions');
		$this->enable_purgesessions = $masterPasswordModel->accessAllowed('purgesessions');
		$this->enable_dbtools       = $masterPasswordModel->accessAllowed('DatabaseTools');
		$this->enable_dbchcol       = $masterPasswordModel->accessAllowed('ChangeDBCollation');
		$this->pluginid             = $controlPanelModel->getPluginID();

		$this->htMakerSupported      = ServerTechnology::isHtaccessSupported();
		$this->nginxMakerSupported   = ServerTechnology::isNginxSupported();
		$this->webConfMakerSupported = ServerTechnology::isWebConfigSupported();
		$this->serverConfigEdited    = $controlPanelModel->serverConfigEdited();
		$this->statsIframe           = $statsModel->collectStatistics(true);
		$this->extension_id          = $controlPanelModel->getState('extension_id', 0, 'int');
		$this->formattedChangelog    = $this->formatChangelog();
		$this->needsdlid             = $controlPanelModel->needsDownloadID();
		$this->needsQuickSetup       = $controlPanelModel->needsQuickSetupWizard();
		$this->stuckUpdates          = ($this->container->params->get('updatedb', 0) == 1);

		// Pro version secret word setup
		if (defined('ADMINTOOLS_PRO') && ADMINTOOLS_PRO)
		{
			$this->jwarnings               = $controlPanelModel->checkJoomlaConfiguration();
			$this->frontEndSecretWordIssue = $controlPanelModel->getFrontendSecretWordError();
			$this->newSecretWord           = $this->container->platform->getSessionVar('newSecretWord', null, 'admintools.cpanel');
		}

		$this->addJavascriptFile('admin://components/com_admintools/media/js/Modal.min.js');
		$this->addJavascriptFile('admin://components/com_admintools/media/js/ControlPanel.min.js');

		// Pro version, control panel graphs (only if we enabled them in config options)
		if (defined('ADMINTOOLS_PRO') && ADMINTOOLS_PRO && $this->showstats)
		{
			// Load JavaScript
			$this->addJavascriptFile('admin://components/com_admintools/media/js/Chart.bundle.min.js');
			$this->addJavascriptFile('admin://components/com_admintools/media/js/cpanelgraphs.min.js');
		}

		// Push translations
		JText::script('COM_ADMINTOOLS_LBL_DATABASETOOLS_PURGESESSIONS_WARN', true);

		// Initialize some Javascript variables used in the view
		$myIP = $controlPanelModel->getVisitorIP();

		$js = <<< JS
akeeba.jQuery(document).ready(function(){
	

	admintools.ControlPanel.myIP = '$myIP';
})
JS;
		$this->addJavascriptInline($js);
	}

	protected function formatChangelog($onlyLast = false)
	{
		$ret   = '';
		$file  = $this->container->backEndPath . '/CHANGELOG.php';
		$lines = @file($file);

		if (empty($lines))
		{
			return $ret;
		}

		array_shift($lines);

		foreach ($lines as $line)
		{
			$line = trim($line);

			if (empty($line))
			{
				continue;
			}

			$type = substr($line, 0, 1);

			switch ($type)
			{
				case '=':
					continue 2;
					break;

				case '+':
					$ret .= "\t" . '<li class="akeeba-changelog-added"><span></span>' . htmlentities(trim(substr($line, 2))) . "</li>\n";
					break;

				case '-':
					$ret .= "\t" . '<li class="akeeba-changelog-removed"><span></span>' . htmlentities(trim(substr($line, 2))) . "</li>\n";
					break;

				case '~':
					$ret .= "\t" . '<li class="akeeba-changelog-changed"><span></span>' . htmlentities(trim(substr($line, 2))) . "</li>\n";
					break;

				case '!':
					$ret .= "\t" . '<li class="akeeba-changelog-important"><span></span>' . htmlentities(trim(substr($line, 2))) . "</li>\n";
					break;

				case '#':
					$ret .= "\t" . '<li class="akeeba-changelog-fixed"><span></span>' . htmlentities(trim(substr($line, 2))) . "</li>\n";
					break;

				default:
					if (!empty($ret))
					{
						$ret .= "</ul>";
						if ($onlyLast)
						{
							return $ret;
						}
					}

					if (!$onlyLast)
					{
						$ret .= "<h3 class=\"akeeba-changelog\">$line</h3>\n";
					}
					$ret .= "<ul class=\"akeeba-changelog\">\n";

					break;
			}
		}

		return $ret;
	}
}

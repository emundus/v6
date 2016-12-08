<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\ControlPanel;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Helper\Coloriser;
use Akeeba\AdminTools\Admin\Helper\ServerTechnology;
use Akeeba\AdminTools\Admin\Model\AdminPassword;
use Akeeba\AdminTools\Admin\Model\ControlPanel;
use Akeeba\AdminTools\Admin\Model\GeographicBlocking;
use Akeeba\AdminTools\Admin\Model\MasterPassword;
use Akeeba\AdminTools\Admin\Model\Stats;
use FOF30\Utils\Ip;
use FOF30\View\DataView\Html as BaseView;
use JText;

class Html extends BaseView
{
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
	 * Is the GeoIP plugin available
	 *
	 * @var  bool
	 */
	public $hasplugin;

	/**
	 * Does the GeoIP database need to be updated
	 *
	 * @var  bool
	 */
	public $pluginNeedsUpdate;

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
	 * Main Control Panel task
	 *
	 * @return  void
	 */
	protected function onBeforeMain()
	{
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

		if (defined('ADMINTOOLS_PRO') && ADMINTOOLS_PRO)
		{
			/** @var GeographicBlocking $geoBlockModel */
			$geoBlockModel = $this->container->factory->model('GeographicBlocking')->tmpInstance();
		}

		/** @var Stats $statsModel */
		$statsModel = $this->container->factory->model('Stats')->tmpInstance();

		// Is this a very old version? If it's older than 90 days let's warn the user
		$this->oldVersion = false;
		$relDate          = new \JDate(ADMINTOOLS_DATE);
		$interval         = time() - $relDate->toUnix();

		if ($interval > (60 * 60 * 24 * 90))
		{
			$this->oldVersion = true;
		}

		// Get the database type
		$dbType = $this->container->db->name;

		// Pass properties to the view
		$this->isMySQL               = strpos($dbType, 'mysql') !== false;
		$this->adminLocked           = $adminPasswordModel->isLocked();
		$this->hasValidPassword      = $masterPasswordModel->hasValidPassword();
		$this->enable_cleantmp       = $masterPasswordModel->accessAllowed('CleanTempDirectory');
		$this->enable_tmplogcheck    = $masterPasswordModel->accessAllowed('CheckTempAndLogDirectories');
		$this->enable_fixperms       = $masterPasswordModel->accessAllowed('FixPermissions');
		$this->enable_purgesessions  = $masterPasswordModel->accessAllowed('purgesessions');
		$this->enable_dbtools        = $masterPasswordModel->accessAllowed('DatabaseTools');
		$this->enable_dbchcol        = $masterPasswordModel->accessAllowed('ChangeDBCollation');
		$this->pluginid              = $controlPanelModel->getPluginID();

		if (defined('ADMINTOOLS_PRO') && ADMINTOOLS_PRO)
		{
			$this->hasplugin             = $geoBlockModel->hasGeoIPPlugin();
			$this->pluginNeedsUpdate     = $geoBlockModel->dbNeedsUpdate();
		}

		$this->htMakerSupported      = ServerTechnology::isHtaccessSupported();
		$this->nginxMakerSupported   = ServerTechnology::isNginxSupported();
		$this->webConfMakerSupported = ServerTechnology::isWebConfigSupported();
		$this->statsIframe           = $statsModel->collectStatistics(true);
		$this->extension_id          = $controlPanelModel->getState('extension_id', 0, 'int');
		$this->needsdlid             = $controlPanelModel->needsDownloadID();
		$this->needsQuickSetup       = $controlPanelModel->needsQuickSetupWizard();
		$this->changeLog             = Coloriser::colorise(JPATH_COMPONENT_ADMINISTRATOR . '/CHANGELOG.php');
		$this->stuckUpdates          = ($this->container->params->get('updatedb', 0) == 1);

		// Pro version secret word setup
		if (defined('ADMINTOOLS_PRO') && ADMINTOOLS_PRO)
		{
			$this->frontEndSecretWordIssue = $controlPanelModel->getFrontendSecretWordError();
			$this->newSecretWord           = \JFactory::getSession()->get('newSecretWord', null, 'admintools.cpanel');
			$this->jwarnings               = $controlPanelModel->checkJoomlaConfiguration();
		}

		$this->addJavascriptFile('admin://components/com_admintools/media/js/ControlPanel.min.js');

		// Pro version, control panel graphs (only if we enabled them in config options)
		if (defined('ADMINTOOLS_PRO') && ADMINTOOLS_PRO && $this->showstats)
		{
			// Load CSS
			$this->addCssFile('admin://components/com_admintools/media/css/jquery.jqplot.min.css');

			// Load JavaScript
			$this->addJavascriptFile('admin://components/com_admintools/media/js/jquery.jqplot.min.js');
			$this->addJavascriptFile('admin://components/com_admintools/media/js/jqplot.highlighter.min.js');
			$this->addJavascriptFile('admin://components/com_admintools/media/js/jqplot.dateAxisRenderer.min.js');
			$this->addJavascriptFile('admin://components/com_admintools/media/js/jqplot.barRenderer.min.js');
			$this->addJavascriptFile('admin://components/com_admintools/media/js/jqplot.pieRenderer.min.js');
			$this->addJavascriptFile('admin://components/com_admintools/media/js/jqplot.hermite.min.js');
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
}
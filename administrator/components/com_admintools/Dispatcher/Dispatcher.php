<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Dispatcher;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Model\ConfigureWAF;
use Akeeba\AdminTools\Admin\Model\MasterPassword;
use FOF30\Container\Container;
use FOF30\Dispatcher\Mixin\ViewAliases;
use FOF30\Utils\Ip;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class Dispatcher extends \FOF30\Dispatcher\Dispatcher
{
	use ViewAliases
	{
		onBeforeDispatch as onBeforeDispatchViewAliases;
	}

	/** @var   string  The name of the default view, in case none is specified */
	public $defaultView = 'ControlPanel';

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->viewNameAliases = [
			'adminpw'            => 'AdminPassword',
			'badwords'           => 'BadWords',
			'badword'            => 'BadWords',
			'cpanel'             => 'ControlPanel',
			'cleantmp'           => 'CleanTempDirectory',
			'dbchcol'            => 'ChangeDBCollation',
			'dbtools'            => 'DatabaseTools',
			'eom'                => 'EmergencyOffline',
			'fixperms'           => 'FixPermissions',
			'fixpermsconfig'     => 'ConfigureFixPermissions',
			'htmaker'            => 'HtaccessMaker',
			'importexport'       => 'ImportAndExport',
			'importexports'      => 'ImportAndExport',
			'ipautobanhistories' => 'IPAutoBanHistories',
			'ipautobanhistory'   => 'IPAutoBanHistories',
			'ipautobans'         => 'AutoBannedAddresses',
			'ipautoban'          => 'AutoBannedAddressed',
			'ipbls'              => 'BlacklistedAddresses',
			'ipbl'               => 'BlacklistedAddressed',
			'ipwls'              => 'WhitelistedAddresses',
			'ipwl'               => 'WhitelistedAddressed',
			'logs'               => 'SecurityExceptions',
			'log'                => 'SecurityExceptions',
			'masterpw'           => 'MasterPassword',
			'nginxmaker'         => 'NginXConfMaker',
			'quickstart'         => 'QuickStart',
			'redirs'             => 'Redirections',
			'redir'              => 'Redirection',
			'scanalerts'         => 'ScanAlerts',
			'scanalert'          => 'ScanAlert',
			'scanner'            => 'Scanner',
			'scan'               => 'Scan',
			'scans'              => 'Scans',
			'schedules'          => 'SchedulingInformation',
			'schedule'           => 'SchedulingInformation',
			'seoandlink'         => 'SEOAndLinkTools',
			'tmplogcheck'        => 'CheckTempAndLogDirectories',
			'waf'                => 'WebApplicationFirewall',
			'wafblacklists'      => 'WAFBlacklistedRequests',
			'wafblacklist'       => 'WAFBlacklistedRequests',
			'waftemplates'       => 'WAFEmailTemplates',
			'waftemplate'        => 'WAFEmailTemplates',
			'wafconfig'          => 'ConfigureWAF',
			'wafexceptions'      => 'ExceptionsFromWAF',
			'wcmaker'            => 'WebConfigMaker',
		];
	}

	public function onBeforeDispatch()
	{
		$this->onBeforeDispatchViewAliases();

		// Load the FOF language
		$lang = $this->container->platform->getLanguage();
		$lang->load('lib_fof30', JPATH_ADMINISTRATOR, 'en-GB', true, true);
		$lang->load('lib_fof30', JPATH_ADMINISTRATOR, null, true, false);

		// FEF Renderer options. Used to load the common CSS file.
		$darkMode  = $this->container->params->get('dark_mode', -1);
		$customCss = 'admin://components/com_admintools/media/css/backend.min.css';

		if ($darkMode != 0)
		{
			$customCss .= ', admin://components/com_admintools/media/css/dark.min.css';
		}

		$this->container->renderer->setOptions([
			'custom_css' => $customCss,
			'fef_dark'   => $darkMode,
		]);

		// Load the version file
		@include_once($this->container->backEndPath . '/version.php');

		if (!defined('ADMINTOOLS_VERSION'))
		{
			define('ADMINTOOLS_VERSION', 'dev');
			define('ADMINTOOLS_DATE', date('Y-m-d'));
		}

		// Work around non-transparent proxy and reverse proxy IP issues when the feature is enabled and the plugin
		// has not done the same already.
		if (defined('ADMINTOOLS_PRO') && (ADMINTOOLS_PRO == 1))
		{
			/** @var ConfigureWAF $wafModel */
			$wafModel  = $this->container->factory->model('ConfigureWAF')->tmpInstance();
			$wafConfig = $wafModel->getConfig();

			if ($wafConfig['ipworkarounds'] && !isset($_SERVER['FOF_REMOTE_ADDR']))
			{
				Ip::workaroundIPIssues();
			}
		}

		// ========== Master password check ==========
		// Control Check
		$view = $this->container->inflector->singularize($this->input->getCmd('view', $this->defaultView));

		/** @var MasterPassword $model */
		$model = $this->container->factory->model('MasterPassword')->tmpInstance();
		if (!$model->accessAllowed($view))
		{
			$url = ($view == 'cpanel') ? 'index.php' : 'index.php?option=com_admintools&view=ControlPanel';
			$this->container->platform->redirect($url, 303, Text::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_NOTAUTHORIZED'), 'error');

			return;
		}

		// Inject JS code to namespace the current jQuery instance
		if ($this->container->platform->getDocument()->getType() == 'html')
		{
			HTMLHelper::_('jquery.framework');
			$this->container->template->addJS('admin://components/com_admintools/media/js/namespace.min.js', false, false, ADMINTOOLS_VERSION);
		}
	}
}

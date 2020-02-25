<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\Engine\Platform;
use FOF30\Container\Container;

defined('_JEXEC') or die();

// PHP version check
if (!version_compare(PHP_VERSION, '5.6.0', '>='))
{
	return;
}

JLoader::import('joomla.application.plugin');

class plgAconlogAdmintools extends JPlugin
{
	/** @var Container */
	private $container;

	/**
	 * Constructor
	 *
	 * @param       object $subject The object to observe
	 * @param       array  $config  An array that holds the plugin configuration
	 *
	 * @since       2.5
	 */
	public function __construct(& $subject, $config)
	{
		// Make sure Akeeba Backup is installed
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_admintools'))
		{
			return;
		}

		// Make sure Akeeba Backup is enabled
		JLoader::import('joomla.application.component.helper');

		if ( !JComponentHelper::isEnabled('com_admintools'))
		{
			return;
		}

		// Load FOF
		if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
		{
			return;
		}

		$this->container = Container::getInstance('com_admintools');

		// No point in logging guest actions
		if ($this->container->platform->getUser()->guest)
		{
			return;
		}

		// If any of the above statement returned, our plugin is not attached to the subject, so it's basically disabled
		parent::__construct($subject, $config);
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\QuickStart	$controller
	 */
	public function onComAdmintoolsControllerQuickstartAfterCommit($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_QUICKSTART_SAVE', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\AdminPassword	$controller
	 */
	public function onComAdmintoolsControllerAdminPasswordBeforeProtect($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_ADMINPASSWORD_ENABLE', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\AdminPassword	$controller
	 */
	public function onComAdmintoolsControllerAdminPasswordBeforeUnprotect($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_ADMINPASSWORD_DISABLE', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\MasterPassword	$controller
	 */
	public function onComAdmintoolsControllerMasterPasswordAfterSave($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_MASTERPASSWORD_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\EmergencyOffline	$controller
	 */
	public function onComAdmintoolsControllerEmergencyOfflineBeforeOffline($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_EMERGENCYOFFLINE_ENABLE', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\EmergencyOffline	$controller
	 */
	public function onComAdmintoolsControllerEmergencyOfflineBeforeOnline($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_EMERGENCYOFFLINE_DISABLE', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\CleanTempDirectory	$controller
	 */
	public function onComAdmintoolsControllerCleanTempDirectoryBeforeBrowse($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_CLEANTEMPDIRECTORY_RUN', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\DatabaseTools	$controller
	 */
	public function onComAdmintoolsControllerDatabaseToolsAfterBrowse($controller)
	{
		/** @var \Akeeba\AdminTools\Admin\Model\DatabaseTools $model */
		$model   = $controller->getModel();
		$percent = $model->getState('percent', 0);

		if ($percent == 100)
		{
			$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_DATABASETOOLS_REPAIR', 'com_admintools');
		}
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\DatabaseTools	$controller
	 */
	public function onComAdmintoolsControllerDatabaseToolsBeforePurgesessions($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_DATABASETOOLS_PURGESESSIONS', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\CheckTempAndLogDirectories	$controller
	 */
	public function onComAdmintoolsControllerCheckTempAndLogDirectoriesBeforeCheck($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_CHECKTEMPANDLOGDIRECTORIES_RUN', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\ConfigureWAF	$controller
	 */
	public function onComAdmintoolsControllerConfigureWAFAfterApply($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_CONFIGUREWAF_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\ConfigureWAF	$controller
	 */
	public function onComAdmintoolsControllerConfigureWAFAfterSave($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_CONFIGUREWAF_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\ImportAndExport	$controller
	 */
	public function onComAdmintoolsControllerImportAndExportBeforeDoexport($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_IMPORANDEXPORT_EXPORT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\ImportAndExport	$controller
	 */
	public function onComAdmintoolsControllerImportAndExportBeforeDoimport($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_IMPORANDEXPORT_IMPORT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\ChangeDBCollation	$controller
	 */
	public function onComAdmintoolsControllerChangeDBCollationBeforeApply($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_CHANGEDBCOLLATION_RUN', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\SEOAndLinkTools	$controller
	 */
	public function onComAdmintoolsControllerSEOAndLinkToolsAfterApply($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_SEOANDLINKTOOLS_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\SEOAndLinkTools	$controller
	 */
	public function onComAdmintoolsControllerSEOAndLinkToolsAfterSave($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_SEOANDLINKTOOLS_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\FixPermissions	$controller
	 */
	public function onComAdmintoolsControllerFixPermissionsBeforeBrowse($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_FIXPERMISSIONS_RUN', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\ConfigureFixPermissions	$controller
	 */
	public function onComAdmintoolsControllerConfigureFixPermissionsAfterSavedefaults($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_CONFIGUREFIXPERMISSIONS_DEFAULTS', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\ConfigureFixPermissions	$controller
	 */
	public function onComAdmintoolsControllerConfigureFixPermissionsBeforeSaveperms($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_CONFIGUREFIXPERMISSIONS_SAVEPERMS', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\ConfigureFixPermissions	$controller
	 */
	public function onComAdmintoolsControllerConfigureFixPermissionsBeforeSaveapplyperms($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_CONFIGUREFIXPERMISSIONS_SAVEAPPLYPERMS', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\HtaccessMaker	$controller
	 */
	public function onComAdmintoolsControllerHtaccessMakerAfterSave($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_HTACCESSMAKER_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\HtaccessMaker	$controller
	 */
	public function onComAdmintoolsControllerHtaccessMakerAfterApply($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_HTACCESSMAKER_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\NginXConfMaker	$controller
	 */
	public function onComAdmintoolsControllerNginXConfMakerAfterSave($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_NGINXCONFMAKER_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\HtaccessMaker	$controller
	 */
	public function onComAdmintoolsControllerNginXConfMakerAfterApply($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_NGINXCONFMAKER_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\WebConfigMaker	$controller
	 */
	public function onComAdmintoolsControllerWebConfigMakerAfterSave($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_WEBCONFIGMAKER_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\WebConfigMaker	$controller
	 */
	public function onComAdmintoolsControllerWebConfigMakerAfterApply($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_WEBCONFIGMAKER_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\Scans	$controller
	 */
	public function onComAdmintoolsControllerScansBeforeStartscan($controller)
	{
		$this->container->platform->logUserAction('', 'COM_ADMINTOOLS_LOGS_SCANS_RUN', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\ScanAlerts	$controller
	 */
	public function onComAdmintoolsControllerScanAlertsAfterPublish($controller)
	{
		/** @var \Akeeba\AdminTools\Admin\Model\ScanAlerts $model */
		$model = $controller->getModel();
		$ids = $this->getIDsFromRequest();

		if (!$ids)
		{
			return;
		}

		foreach ($ids as $id)
		{
			$model->find($id);

			$this->container->platform->logUserAction($model->path, 'COM_ADMINTOOLS_LOGS_SCANALERTS_MARKEDSAFE', 'com_admintools');
		}
	}

	/* Start of CRUD tasks */

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\BadWord	$controller
	 */
	public function onComAdmintoolscontrollerBadWordsAfterApplySave($controller)
	{
		/** @var \Akeeba\AdminTools\Admin\Model\BadWords $model */
		$model = $controller->getModel();

		$link = '<a href="index.php?option=com_admintools&view=BadWords&task=edit&id='.$model->id.'">'.$model->word.'</a>';

		$this->container->platform->logUserAction($link, 'COM_ADMINTOOLS_LOGS_BADWORDS_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\BadWord	$controller
	 */
	public function onComAdmintoolscontrollerBadWordsBeforeRemove($controller)
	{
		$ids = $this->getIDsFromRequest();
		$db  = $this->container->db;

		$ids = array_map(array($db, 'quote'), $ids);

		$query = $db->getQuery(true)
					->select($db->qn('word'))
					->from($db->qn('#__admintools_badwords'))
					->where($db->qn('id').' IN ('.implode(',', $ids).')');
		$words = $db->setQuery($query)->loadColumn();

		foreach ($words as $word)
		{
			$this->container->platform->logUserAction($word, 'COM_ADMINTOOLS_LOGS_BADWORDS_DELETE', 'com_admintools');
		}
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\WAFBlacklistedRequests	$controller
	 */
	public function onComAdmintoolscontrollerWAFBlacklistedRequestsAfterApplySave($controller)
	{
		/** @var \Akeeba\AdminTools\Admin\Model\WAFBlacklistedRequests $model */
		$model = $controller->getModel();

		$parts[] = $model->option ? $model->option : '(All)';
		$parts[] = $model->view ? $model->view: '(All)';
		$parts[] = $model->query ? $model->query: '(All)';

		$link = '<a href="index.php?option=com_admintools&view=WAFBlacklistedRequests&task=edit&id='.$model->id.'">'.implode(' ', $parts).'</a>';

		$this->container->platform->logUserAction($link, 'COM_ADMINTOOLS_LOGS_WAFBLACKLIST_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\WAFBlacklistedRequests	$controller
	 */
	public function onComAdmintoolscontrollerWAFBlacklistedRequestsAfterPublish($controller)
	{
		$ids = $this->getIDsFromRequest();
		$db  = $this->container->db;

		$ids = array_map(array($db, 'quote'), $ids);

		$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__admintools_wafblacklists'))
					->where($db->qn('id').' IN ('.implode(',', $ids).')');
		$rows = $db->setQuery($query)->loadObjectList();

		foreach ($rows as $row)
		{
			$parts[] = $row->option ? $row->option : '(All)';
			$parts[] = $row->view ? $row->view: '(All)';
			$parts[] = $row->query ? $row->query: '(All)';

			$this->container->platform->logUserAction(implode(' ', $parts), 'COM_ADMINTOOLS_LOGS_WAFBLACKLIST_PUBLISH', 'com_admintools');
		}
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\WAFBlacklistedRequests	$controller
	 */
	public function onComAdmintoolscontrollerWAFBlacklistedRequestsAfterUnpublish($controller)
	{
		$ids = $this->getIDsFromRequest();
		$db  = $this->container->db;

		$ids = array_map(array($db, 'quote'), $ids);

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__admintools_wafblacklists'))
			->where($db->qn('id').' IN ('.implode(',', $ids).')');
		$rows = $db->setQuery($query)->loadObjectList();

		foreach ($rows as $row)
		{
			$parts[] = $row->option ? $row->option : '(All)';
			$parts[] = $row->view ? $row->view: '(All)';
			$parts[] = $row->query ? $row->query: '(All)';

			$this->container->platform->logUserAction(implode(' ', $parts), 'COM_ADMINTOOLS_LOGS_WAFBLACKLIST_UNPUBLISH', 'com_admintools');
		}
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\WAFBlacklistedRequests	$controller
	 */
	public function onComAdmintoolscontrollerWAFBlacklistedRequestsBeforeRemove($controller)
	{
		$ids = $this->getIDsFromRequest();
		$db  = $this->container->db;

		$ids = array_map(array($db, 'quote'), $ids);

		$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__admintools_wafblacklists'))
					->where($db->qn('id').' IN ('.implode(',', $ids).')');
		$rows = $db->setQuery($query)->loadObjectList();

		foreach ($rows as $row)
		{
			$parts[] = $row->option ? $row->option : '(All)';
			$parts[] = $row->view ? $row->view: '(All)';
			$parts[] = $row->query ? $row->query: '(All)';

			$this->container->platform->logUserAction(implode(' ', $parts), 'COM_ADMINTOOLS_LOGS_WAFBLACKLIST_DELETE', 'com_admintools');
		}
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\ExceptionsFromWAF	$controller
	 */
	public function onComAdmintoolscontrollerExceptionsFromWAFAfterApplySave($controller)
	{
		/** @var \Akeeba\AdminTools\Admin\Model\ExceptionsFromWAF $model */
		$model = $controller->getModel();

		$parts[] = $model->option ? $model->option : '(All)';
		$parts[] = $model->view ? $model->view: '(All)';
		$parts[] = $model->query ? $model->query: '(All)';

		$link = '<a href="index.php?option=com_admintools&view=ExceptionsFromWAF&task=edit&id='.$model->id.'">'.implode(' ', $parts).'</a>';

		$this->container->platform->logUserAction($link, 'COM_ADMINTOOLS_LOGS_WAFEXCEPTIONS_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\ExceptionsFromWAF	$controller
	 */
	public function onComAdmintoolscontrollerExceptionsFromWAFBeforeRemove($controller)
	{
		$ids = $this->getIDsFromRequest();
		$db  = $this->container->db;

		$ids = array_map(array($db, 'quote'), $ids);

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__admintools_wafexceptions'))
			->where($db->qn('id').' IN ('.implode(',', $ids).')');
		$rows = $db->setQuery($query)->loadObjectList();

		foreach ($rows as $row)
		{
			$parts[] = $row->option ? $row->option : '(All)';
			$parts[] = $row->view ? $row->view: '(All)';
			$parts[] = $row->query ? $row->query: '(All)';

			$this->container->platform->logUserAction(implode(' ', $parts), 'COM_ADMINTOOLS_LOGS_WAFEXCEPTIONS_DELETE', 'com_admintools');
		}
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\WhitelistedAddresses	$controller
	 */
	public function onComAdmintoolscontrollerWhitelistedAddressesAfterApplySave($controller)
	{
		/** @var \Akeeba\AdminTools\Admin\Model\WhitelistedAddresses $model */
		$model = $controller->getModel();

		$link = '<a href="index.php?option=com_admintools&view=WhitelistedAddresses&task=edit&id='.$model->id.'">'.$model->ip.'</a>';

		$this->container->platform->logUserAction($link, 'COM_ADMINTOOLS_LOGS_WHITELISTEDADDRESSES_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\WhitelistedAddresses	$controller
	 */
	public function onComAdmintoolscontrollerWhitelistedAddressesBeforeRemove($controller)
	{
		$ids = $this->getIDsFromRequest();
		$db  = $this->container->db;

		$ids = array_map(array($db, 'quote'), $ids);

		$query = $db->getQuery(true)
					->select($db->qn('ip'))
					->from($db->qn('#__admintools_adminiplist'))
					->where($db->qn('id').' IN ('.implode(',', $ids).')');
		$rows = $db->setQuery($query)->loadColumn();

		foreach ($rows as $row)
		{
			$this->container->platform->logUserAction($row, 'COM_ADMINTOOLS_LOGS_WHITELISTEDADDRESSES_DELETE', 'com_admintools');
		}
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\BlacklistedAddresses	$controller
	 */
	public function onComAdmintoolscontrollerBlacklistedAddressesAfterApplySave($controller)
	{
		/** @var \Akeeba\AdminTools\Admin\Model\BlacklistedAddresses $model */
		$model = $controller->getModel();

		$link = '<a href="index.php?option=com_admintools&view=BlacklistedAddresses&task=edit&id='.$model->id.'">'.$model->ip.'</a>';

		$this->container->platform->logUserAction($link, 'COM_ADMINTOOLS_LOGS_BLACKLISTEDADDRESSES_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\BlacklistedAddresses	$controller
	 */
	public function onComAdmintoolscontrollerBlacklistedAddressesBeforeRemove($controller)
	{
		$ids = $this->getIDsFromRequest();
		$db  = $this->container->db;

		$ids = array_map(array($db, 'quote'), $ids);

		$query = $db->getQuery(true)
					->select($db->qn('ip'))
					->from($db->qn('#__admintools_ipblock'))
					->where($db->qn('id').' IN ('.implode(',', $ids).')');
		$rows = $db->setQuery($query)->loadColumn();

		foreach ($rows as $row)
		{
			$this->container->platform->logUserAction($row, 'COM_ADMINTOOLS_LOGS_BLACKLISTEDADDRESSES_DELETE', 'com_admintools');
		}
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\GeographicBlocking	$controller
	 */
	public function onComAdmintoolscontrollerGeographicBlockingAfterSave($controller)
	{
		$continents = $this->container->input->get('continent', array(), 'array', 2);
		$countries  = $this->container->input->get('country', array(), 'array', 2);

		$text = 'COM_ADMINTOOLS_LOGS_GEOBLOCK_EDIT';

		if (!$continents && !$countries)
		{
			$text = 'COM_ADMINTOOLS_LOGS_GEOBLOCK_DISABLED';
		}

		$this->container->platform->logUserAction('', $text, 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\SecurityExceptions	$controller
	 */
	public function onComAdmintoolscontrollerSecurityExceptionsBeforeRemove($controller)
	{
		$ids = $this->getIDsFromRequest();
		$db  = $this->container->db;

		$ids = array_map(array($db, 'quote'), $ids);

		$query = $db->getQuery(true)
			->select($db->qn('ip'))
			->from($db->qn('#__admintools_log'))
			->where($db->qn('id').' IN ('.implode(',', $ids).')');
		$rows = $db->setQuery($query)->loadColumn();

		foreach ($rows as $row)
		{
			$this->container->platform->logUserAction($row, 'COM_ADMINTOOLS_LOGS_SECURITYEXCEPTIONS_DELETE', 'com_admintools');
		}
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\SecurityExceptions	$controller
	 */
	public function onComAdmintoolscontrollerSecurityExceptionsAfterBan($controller)
	{
		/** @var \Akeeba\AdminTools\Admin\Model\SecurityExceptions $model */
		$model = $controller->getModel();

		$this->container->platform->logUserAction($model->ip, 'COM_ADMINTOOLS_LOGS_SECURITYEXCEPTIONS_BAN', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\SecurityExceptions	$controller
	 */
	public function onComAdmintoolscontrollerSecurityExceptionsAfterUnban($controller)
	{
		/** @var \Akeeba\AdminTools\Admin\Model\SecurityExceptions $model */
		$model = $controller->getModel();

		$this->container->platform->logUserAction($model->ip, 'COM_ADMINTOOLS_LOGS_SECURITYEXCEPTIONS_UNBAN', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\AutoBannedAddresses	$controller
	 */
	public function onComAdmintoolscontrollerAutoBannedAddressesBeforeRemove($controller)
	{
		$ids = $this->getIDsFromRequest();

		foreach ($ids as $ip)
		{
			$this->container->platform->logUserAction($ip, 'COM_ADMINTOOLS_LOGS_AUTOBANNEDADDRESSES_DELETE', 'com_admintools');
		}
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\IPAutoBanHistories	$controller
	 */
	public function onComAdmintoolscontrollerIPAutoBanHistoriesBeforeRemove($controller)
	{
		$ids = $this->getIDsFromRequest();
		$db  = $this->container->db;

		$ids = array_map(array($db, 'quote'), $ids);

		$query = $db->getQuery(true)
			->select($db->qn('ip'))
			->from($db->qn('#__admintools_ipautobanhistory'))
			->where($db->qn('id').' IN ('.implode(',', $ids).')');
		$rows = $db->setQuery($query)->loadColumn();

		foreach ($rows as $row)
		{
			$this->container->platform->logUserAction($row, 'COM_ADMINTOOLS_LOGS_IPAUTOBANHISTORIES_DELETE', 'com_admintools');
		}
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\WAFEmailTemplates	$controller
	 */
	public function onComAdmintoolscontrollerWAFEmailTemplatesAfterApplySave($controller)
	{
		/** @var \Akeeba\AdminTools\Admin\Model\WAFEmailTemplates $model */
		$model = $controller->getModel();

		$link = '<a href="index.php?option=com_admintools&view=WAFEmailTemplates&task=edit&id='.$model->admintools_waftemplate_id.'">'.$model->subject.'</a>';

		$this->container->platform->logUserAction($link, 'COM_ADMINTOOLS_LOGS_WAFEMAILTEMPLATES_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\WAFEmailTemplates	$controller
	 */
	public function onComAdmintoolscontrollerWAFEmailTemplatesBeforeRemove($controller)
	{
		$ids = $this->getIDsFromRequest();
		$db  = $this->container->db;

		$ids = array_map(array($db, 'quote'), $ids);

		$query = $db->getQuery(true)
			->select($db->qn('subject'))
			->from($db->qn('#__admintools_waftemplates'))
			->where($db->qn('admintools_waftemplate_id').' IN ('.implode(',', $ids).')');
		$words = $db->setQuery($query)->loadColumn();

		foreach ($words as $word)
		{
			$this->container->platform->logUserAction($word, 'COM_ADMINTOOLS_LOGS_WAFEMAILTEMPLATES_DELETE', 'com_admintools');
		}
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\Redirections	$controller
	 */
	public function onComAdmintoolscontrollerRedirectionAfterApplySave($controller)
	{
		/** @var \Akeeba\AdminTools\Admin\Model\Redirections $model */
		$model = $controller->getModel();

		$link = '<a href="index.php?option=com_admintools&view=Redirections&task=edit&id='.$model->id.'">'.$model->dest.'</a>';

		$this->container->platform->logUserAction($link, 'COM_ADMINTOOLS_LOGS_REDIRECTIONS_EDIT', 'com_admintools');
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\Redirections	$controller
	 */
	public function onComAdmintoolscontrollerRedirectionsAfterPublish($controller)
	{
		$ids = $this->getIDsFromRequest();
		$db  = $this->container->db;

		$ids = array_map(array($db, 'quote'), $ids);

		$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__admintools_redirects'))
					->where($db->qn('id').' IN ('.implode(',', $ids).')');
		$rows = $db->setQuery($query)->loadObjectList();

		foreach ($rows as $row)
		{
			$this->container->platform->logUserAction($row->dest, 'COM_ADMINTOOLS_LOGS_REDIRECTIONS_PUBLISH', 'com_admintools');
		}
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\Redirections	$controller
	 */
	public function onComAdmintoolscontrollerRedirectionsAfterUnpublish($controller)
	{
		$ids = $this->getIDsFromRequest();
		$db  = $this->container->db;

		$ids = array_map(array($db, 'quote'), $ids);

		$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__admintools_redirects'))
					->where($db->qn('id').' IN ('.implode(',', $ids).')');
		$rows = $db->setQuery($query)->loadObjectList();

		foreach ($rows as $row)
		{
			$this->container->platform->logUserAction($row->dest, 'COM_ADMINTOOLS_LOGS_REDIRECTIONS_UNPUBLISH', 'com_admintools');
		}
	}

	/**
	 * @param \Akeeba\AdminTools\Admin\Controller\Redirections	$controller
	 */
	public function onComAdmintoolscontrollerRedirectionsBeforeRemove($controller)
	{
		$ids = $this->getIDsFromRequest();
		$db  = $this->container->db;

		$ids = array_map(array($db, 'quote'), $ids);

		$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__admintools_redirects'))
					->where($db->qn('id').' IN ('.implode(',', $ids).')');
		$rows = $db->setQuery($query)->loadObjectList();

		foreach ($rows as $row)
		{
			$this->container->platform->logUserAction($row->dest, 'COM_ADMINTOOLS_LOGS_REDIRECTIONS_DELETE', 'com_admintools');
		}
	}

	/* End of CRUD tasks */

	/**
	 * Gets the list of IDs from the request data
	 *
	 * @return array
	 */
	private function getIDsFromRequest()
	{
		// Get the ID or list of IDs from the request or the configuration
		$cid = $this->container->input->get('cid', array(), 'array');
		$id  = $this->container->input->getInt('id', 0);

		$ids = array();

		if (is_array($cid) && !empty($cid))
		{
			$ids = $cid;
		}
		elseif (!empty($id))
		{
			$ids = array($id);
		}

		return $ids;
	}
}

<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Helper\Storage;
use FOF40\Model\Model;

class MasterPassword extends Model
{
	public $views = [
		'AdminPassword'              => 'COM_ADMINTOOLS_TITLE_ADMINPW',
		'BadWords'                   => 'COM_ADMINTOOLS_TITLE_BADWORDS',
		'CleanTempDirectory'         => 'COM_ADMINTOOLS_TITLE_CLEANTMP',
		'ChangeDBCollation'          => 'COM_ADMINTOOLS_TITLE_DBCHCOL',
		'DatabaseTools'              => 'COM_ADMINTOOLS_TITLE_DBTOOLS',
		'EmergencyOffline'           => 'COM_ADMINTOOLS_TITLE_EOM',
		'FixPermissions'             => 'COM_ADMINTOOLS_TITLE_FIXPERMS',
		'ConfigureFixPermissions'    => 'COM_ADMINTOOLS_TITLE_FIXPERMSCONFIG',
		'HtaccessMaker'              => 'COM_ADMINTOOLS_TITLE_HTMAKER',
		'NginXConfMaker'             => 'COM_ADMINTOOLS_TITLE_NGINXMAKER',
		'WebConfigMaker'             => 'COM_ADMINTOOLS_TITLE_WCMAKER',
		'IPAutoBanHistories'         => 'COM_ADMINTOOLS_TITLE_IPAUTOBANHISTORY',
		'AutoBannedAddresses'        => 'COM_ADMINTOOLS_TITLE_IPAUTOBAN',
		'BlacklistedAddresses'       => 'COM_ADMINTOOLS_TITLE_IPBL',
		'WhitelistedAddresses'       => 'COM_ADMINTOOLS_TITLE_IPWL',
		'MasterPassword'             => 'COM_ADMINTOOLS_TITLE_MASTERPW',
		'QuickStart'                 => 'COM_ADMINTOOLS_TITLE_QUICKSTART',
		'Redirections'               => 'COM_ADMINTOOLS_TITLE_REDIRS',
		'Scanner'                    => 'COM_ADMINTOOLS_TITLE_SCANNER',
		'Scan'                       => 'COM_ADMINTOOLS_TITLE_SCANS',
		'SEOAndLinkTools'            => 'COM_ADMINTOOLS_TITLE_SEOANDLINK',
		'WebApplicationFirewall'     => 'COM_ADMINTOOLS_TITLE_WAF',
		'WAFBlacklistedRequests'     => 'COM_ADMINTOOLS_TITLE_WAFBLACKLISTS',
		'ConfigureWAF'               => 'COM_ADMINTOOLS_TITLE_WAFCONFIG',
		'ExceptionsFromWAF'          => 'COM_ADMINTOOLS_TITLE_WAFEXCEPTIONS',
		'WAFEmailTemplates'          => 'COM_ADMINTOOLS_TITLE_WAFEMAILTEMPLATES',
		'CheckTempAndLogDirectories' => 'COM_ADMINTOOLS_TITLE_TMPLOGCHECK',
		'SchedulingInformation'      => 'COM_ADMINTOOLS_TITLE_SCHEDULINGINFORMATION',
		'ImportAndExport'            => 'COM_ADMINTOOLS_TITLE_EXPORT_SETTINGS',
		'ScanAlerts'                 => 'COM_ADMINTOOLS_TITLE_SCANALERTS_MASTERPW',
		'SecurityExceptions'         => 'COM_ADMINTOOLS_TITLE_LOG',
		'UnblockIP'                  => 'COM_ADMINTOOLS_TITLE_UNBLOCKIP',
		'TempSuperUsers'             => 'COM_ADMINTOOLS_TITLE_TEMPSUPERUSERS',
	];

	/**
	 * Checks if the user should be granted access to the current view,
	 * based on his Master Password setting.
	 *
	 * @param   string  $view  Optional. The string to check. Leave null to use the current view.
	 *
	 * @return  bool
	 */
	public function accessAllowed($view = null)
	{
		// Is this a view protected by the Master Password feature?
		if (empty($view))
		{
			$view = $this->input->get('view', 'ControlPanel');
		}

		$inflector = $this->container->inflector;
		$altView   = $inflector->isPlural($view) ? $inflector->singularize($view) : $inflector->pluralize($view);

		if (!isset($this->views[$view]) && !isset($this->views[$altView]))
		{
			return true;
		}

		// Do we have a Master Password?
		$params     = Storage::getInstance();
		$masterHash = $params->getValue('masterpassword', '');

		if (empty($masterHash))
		{
			return true;
		}

		// Compare the master pw with the one the user entered
		$masterHash = md5($masterHash);
		$userHash   = $this->container->platform->getSessionVar('userpwhash', '', 'admintools');

		if ($userHash === $masterHash)
		{
			// The password matches, we are allowed to access everything
			return true;
		}

		// The login is invalid. If the view is locked I'll have to kick the user out.
		$lockedviews_raw = $params->getValue('lockedviews', '');

		if (empty($lockedviews_raw))
		{
			// There are no locked views.
			return true;
		}

		$lockedViews = explode(",", $lockedviews_raw);

		if (empty($lockedViews))
		{
			// This shouldn't happen. There are no locked views.
			return true;
		}

		// Special case: view=SecurityExceptions, task=browse, format=json (graphs) is always allowed.
		$task   = $this->input->get('task', '');
		$format = $this->input->get('format', 'html');

		if (
			(($view == 'SecurityExceptions') || ($altView == 'SecurityExceptions')) &&
			($format == 'json') &&
			($task == 'browse')
		)
		{
			return true;
		}

		// Check if the view is locked.
		if (in_array($view, $lockedViews) || in_array($altView, $lockedViews))
		{
			return false;
		}

		return true;
	}

	/**
	 * Compares the user-supplied password against the master password
	 *
	 * @return  bool  True if the passwords match
	 */
	public function hasValidPassword()
	{
		$params = Storage::getInstance();

		$masterHash = $params->getValue('masterpassword', '');

		if (empty($masterHash))
		{
			return true;
		}

		$masterHash = md5($masterHash);
		$userHash   = $this->container->platform->getSessionVar('userpwhash', '', 'admintools');

		return ($masterHash == $userHash);
	}

	/**
	 * Stores the hash of the user's password in the session
	 *
	 * @param   $passwd  string  The password supplied by the user
	 *
	 * @return  void
	 */
	public function setUserPassword($passwd)
	{
		$userHash = md5($passwd);
		$this->container->platform->setSessionVar('userpwhash', $userHash, 'admintools');
	}

	/**
	 * Saves the Master Password and the proteected views list
	 *
	 * @param   string  $masterPassword  The new master password
	 * @param   array   $protectedViews  A list of the views to protect
	 *
	 * @return  void
	 */
	public function saveSettings($masterPassword, array $protectedViews)
	{
		$params = Storage::getInstance();

		// Add the new master password
		$params->setValue('masterpassword', $masterPassword);

		// Add the protected views
		if (!in_array('MasterPassword', $protectedViews))
		{
			$protectedViews[] = 'MasterPassword';
		}

		$params->setValue('lockedviews', implode(',', $protectedViews));

		$params->save();
	}

	/**
	 * Get a list of the views which can be locked down and their lockdown status
	 *
	 * @return  array
	 */
	public function &getItemList()
	{
		$lockedViews = [];

		$params = Storage::getInstance();

		$lockedViewsRaw = $params->getValue('lockedviews', '');

		if (!empty($lockedViewsRaw))
		{
			$lockedViews = explode(",", $lockedViewsRaw);
		}

		$views = [];

		foreach ($this->views as $view => $langKey)
		{
			$views[$view] = [
				in_array($view, $lockedViews),
				$langKey,
			];
		}

		return $views;
	}

	/**
	 * Returns the stored master password
	 *
	 * @return  string
	 */
	public function getMasterPassword()
	{
		$params = Storage::getInstance();

		return $params->getValue('masterpassword', '');
	}
}

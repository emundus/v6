<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Controller\Mixin;

defined('_JEXEC') or die();

use FOF30\Inflector\Inflector;
use RuntimeException;
use JText;

trait CustomACL
{
	protected function onBeforeExecute(&$task)
	{
		$this->adminToolsACLCheck($this->view, $this->task);
	}

	/**
	 * Checks if the currently logged in user has the required ACL privileges to access the current view. If not, a
	 * RuntimeException is thrown.
	 *
	 * @param   $view  string  The view to apply ACL for
	 * @param   $task  string  The task to apply ACL for
	 *
	 * @return  void
	 */
	protected function adminToolsACLCheck($view, $task)
	{
		// Akeeba Backup-specific ACL checks. All views not listed here are limited by the akeeba.configure privilege.
		$viewACLMap = [
			'AdminPassword'              => 'admintools.security',
			'AutoBannedAddresses'        => 'admintools.security',
			'BadWords'                   => 'admintools.security',
			'BlacklistedAddresses'       => 'admintools.security',
			'ChangeDBCollation'          => 'admintools.maintenance',
			'CheckTempAndLogDirectories' => 'admintools.maintenance',
			'CleanTempDirectory'         => 'admintools.maintenance',
			'ConfigureFixPermissions'    => 'admintools.maintenance',
			'ConfigureWAF'               => 'admintools.security',
			'DatabaseTools'              => 'admintools.maintenance',
			'EmergencyOffline'           => 'admintools.security',
			'ExceptionsFromWAF'          => 'admintools.security',
			'FixPermissions'             => 'admintools.maintenance',
			'GeographicBlocking'         => 'admintools.security',
			'HtaccessMaker'              => 'admintools.security',
			'ImportAndExport'            => 'admintools.security',
			'IPAutoBanHistories'         => 'admintools.security',
			'MasterPassword'             => 'admintools.security',
			'NginXConfMaker'             => 'admintools.security',
			'QuickStart'                 => 'admintools.security',
			'Redirections'               => 'admintools.maintenance',
			'ScanAlerts'                 => 'admintools.security',
			'Scanner'                    => 'admintools.security',
			'Scans'                      => 'admintools.security',
			'SchedulingInformation'      => 'admintools.security',
			'SecurityExceptions'         => 'admintools.security',
			'SEOAndLinkTools'            => 'admintools.utils',
			'WAFBlacklistedRequests'     => 'admintools.security',
			'WAFEmailtemplates'          => 'admintools.security',
			'WebApplicationFirewall'     => 'admintools.security',
			'WebConfigMaker'             => 'admintools.security',
			'WhitelistedAddresses'       => 'admintools.security',
		];

		$privilege = '';

		/** @var Inflector $inflector */
		$inflector = $this->container->inflector;

		$altView = $inflector->pluralize($view);

		if ($altView == $view)
		{
			$altView = $inflector->singularize($view);
		}

		$itemsToCheck = [
			$view,
			"$view.$task",
			$altView,
			"$altView.$task",
		];


		foreach ($itemsToCheck as $item)
		{
			if (array_key_exists($item, $viewACLMap))
			{
				$privilege = $viewACLMap[ $item ];

				break;
			}
		}

		// If an empty privilege is defined do not perform any ACL checks
		if (empty($privilege))
		{
			return;
		}

		if (!$this->container->platform->authorise($privilege, 'com_admintools'))
		{
			throw new RuntimeException(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
	}
}
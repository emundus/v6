<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') or die;

use Exception;
use FOF30\Container\Container;
use FOF30\Update\Update;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;

class Updates extends Update
{
	/**
	 * Obsolete update site locations
	 *
	 * @var  array
	 */
	protected $obsoleteUpdateSiteLocations = [
		'http://cdn.akeebabackup.com/updates/atpro.xml',
		'http://cdn.akeebabackup.com/updates/atcore.xml',
		'http://cdn.akeebabackup.com/updates/fof.xml',
	];

	/**
	 * Public constructor. Initialises the protected members as well.
	 *
	 * @param   array  $config
	 */
	public function __construct($config = [])
	{
		$container = Container::getInstance('com_admintools');

		$config['update_component'] = 'pkg_admintools';
		$config['update_sitename']  = 'Admin Tools Core';
		$config['update_site']      = 'https://cdn.akeeba.com/updates/pkgadmintoolscore.xml';
		$config['update_paramskey'] = 'downloadid';
		$config['update_container'] = $container;

		$isPro = defined('ADMINTOOLS_PRO') ? ADMINTOOLS_PRO : 0;

		if ($isPro)
		{
			$config['update_sitename'] = 'Admin Tools Professional';
			$config['update_site']     = 'https://cdn.akeeba.com/updates/pkgadmintoolspro.xml';
		}

		if (defined('ADMINTOOLS_VERSION') && !in_array(substr(ADMINTOOLS_VERSION, 0, 3), ['dev', 'rev']))
		{
			$config['update_version'] = ADMINTOOLS_VERSION;
		}

		parent::__construct($config);

		$this->container    = $container;
		$this->extension_id = $this->findExtensionId('pkg_admintools', 'package');

		if (empty($this->extension_id))
		{
			$this->createFakePackageExtension();
			$this->extension_id = $this->findExtensionId('pkg_admintools', 'package');
		}
	}

	/**
	 * Refreshes the update sites, removing obsolete update sites in the process
	 */
	public function refreshUpdateSite()
	{
		// Remove any update sites for the old com_admintools package
		$this->removeObsoleteComponentUpdateSites();

		// Refresh our update sites
		parent::refreshUpdateSite();
	}

	/**
	 * Sends an update notification email
	 *
	 * @param   string  $version  The newest available version
	 * @param   string  $email    The email address of the recipient
	 *
	 * @return  boolean  The result from JMailer::send()
	 */
	public function sendNotificationEmail($version, $email)
	{
		$email_subject = <<<ENDSUBJECT
THIS EMAIL IS SENT FROM YOUR SITE "[SITENAME]" - Update available
ENDSUBJECT;

		$email_body = <<<ENDBODY
This email IS NOT sent by the authors of Admin Tools. It is sent automatically
by your own site, [SITENAME]

================================================================================
UPDATE INFORMATION
================================================================================

Your site has determined that there is an updated version of AdminTools
available for download.

New version number: [VERSION]

This email is sent to you by your site to remind you of this fact. The authors
of the software will never contact you about available updates.

================================================================================
WHY AM I RECEIVING THIS EMAIL?
================================================================================

This email has been automatically sent by a CLI script you, or the person who built
or manages your site, has installed and explicitly activated. This script looks
for updated versions of the software and sends an email notification to all
Super Users. You will receive several similar emails from your site, up to 6
times per day, until you either update the software or disable these emails.

To disable these emails, please contact your site administrator.

If you do not understand what this means, please do not contact the authors of
the software. They are NOT sending you this email and they cannot help you.
Instead, please contact the person who built or manages your site.

================================================================================
WHO SENT ME THIS EMAIL?
================================================================================

This email is sent to you by your own site, [SITENAME]

ENDBODY;

		$jconfig  = $this->container->platform->getConfig();
		$sitename = $jconfig->get('sitename');

		$substitutions = [
			'[VERSION]'  => $version,
			'[SITENAME]' => $sitename,
		];

		$email_subject = str_replace(array_keys($substitutions), array_values($substitutions), $email_subject);
		$email_body    = str_replace(array_keys($substitutions), array_values($substitutions), $email_body);

		try
		{
			$mailer = Factory::getMailer();

			$mailfrom = $jconfig->get('mailfrom');
			$fromname = $jconfig->get('fromname');

			$mailer->setSender([$mailfrom, $fromname]);
			$mailer->addRecipient($email);
			$mailer->setSubject($email_subject);
			$mailer->setBody($email_body);

			return $mailer->Send();
		}
		catch (Exception $e)
		{
			// Joomla! 3.5 is written by incompetent bonobos
			return false;
		}
	}

	/**
	 * Removes the obsolete update sites for the component, since now we're dealing with a package.
	 *
	 * Controlled by componentName, packageName and obsoleteUpdateSiteLocations
	 *
	 * Depends on getExtensionId, getUpdateSitesFor
	 *
	 * @return  void
	 */
	private function removeObsoleteComponentUpdateSites()
	{
		// Initialize
		$deleteIDs = [];

		// Get component ID
		$componentID = $this->findExtensionId('com_admintools', 'component');

		// Get package ID
		$packageID = $this->findExtensionId('pkg_admintools', 'package');

		// Update sites for old extension ID (all)
		if ($componentID)
		{
			// Old component packages
			$moreIDs = $this->getUpdateSitesFor($componentID, null);

			if (is_array($moreIDs) && count($moreIDs))
			{
				$deleteIDs = array_merge($deleteIDs, $moreIDs);
			}

			// Obsolete update sites
			$moreIDs = $this->getUpdateSitesFor(null, $componentID, $this->obsoleteUpdateSiteLocations);

			if (is_array($moreIDs) && count($moreIDs))
			{
				$deleteIDs = array_merge($deleteIDs, $moreIDs);
			}
		}

		// Update sites for any but current extension ID, location matching any of the obsolete update sites
		if ($packageID)
		{
			// Update sites for all of the current extension ID update sites
			$moreIDs = $this->getUpdateSitesFor($packageID, null);

			if (is_array($moreIDs) && count($moreIDs))
			{
				$deleteIDs = array_merge($deleteIDs, $moreIDs);
			}

			$deleteIDs = array_unique($deleteIDs);

			// Remove the last update site
			if (count($deleteIDs))
			{
				$lastID = array_pop($moreIDs);
				$pos    = array_search($lastID, $deleteIDs);
				unset($deleteIDs[$pos]);
			}
		}

		$db        = $this->container->db;
		$deleteIDs = array_unique($deleteIDs);

		if (empty($deleteIDs) || !count($deleteIDs))
		{
			return;
		}

		$deleteIDs = array_map([$db, 'q'], $deleteIDs);

		$query = $db->getQuery(true)
			->delete($db->qn('#__update_sites'))
			->where($db->qn('update_site_id') . ' IN(' . implode(',', $deleteIDs) . ')');

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			// Do nothing.
		}

		$query = $db->getQuery(true)
			->delete($db->qn('#__update_sites_extensions'))
			->where($db->qn('update_site_id') . ' IN(' . implode(',', $deleteIDs) . ')');

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			// Do nothing.
		}
	}

	/**
	 * Gets the ID of an extension
	 *
	 * @param   string  $element  Extension element, e.g. com_foo, mod_foo, lib_foo, pkg_foo or foo (CAUTION: plugin,
	 *                            file!)
	 * @param   string  $type     Extension type: component, module, library, package, plugin or file
	 * @param   null    $folder   Plugins: plugin folder. Modules: admin/site
	 *
	 * @return  int  Extension ID or 0 on failure
	 */
	private function findExtensionId($element, $type = 'component', $folder = null)
	{
		$db    = $this->container->db;
		$query = $db->getQuery(true)
			->select($db->qn('extension_id'))
			->from($db->qn('#__extensions'))
			->where($db->qn('element') . ' = ' . $db->q($element))
			->where($db->qn('type') . ' = ' . $db->q($type));

		// Plugin? We should look for a folder
		if ($type == 'plugin')
		{
			$folder = empty($folder) ? 'system' : $folder;

			$query->where($db->qn('folder') . ' = ' . $db->q($folder));
		}

		// Module? Use the folder to determine if it's site or admin module.
		if ($type == 'module')
		{
			$folder = empty($folder) ? 'site' : $folder;

			$query->where($db->qn('client_id') . ' = ' . $db->q(($folder == 'site') ? 0 : 1));
		}

		try
		{
			$id = $db->setQuery($query, 0, 1)->loadResult();
		}
		catch (Exception $e)
		{
			$id = 0;
		}

		return empty($id) ? 0 : (int) $id;
	}

	/**
	 * Returns the update site IDs matching the criteria below. All criteria are optional but at least one must be
	 * defined for the method call to make any sense.
	 *
	 * @param   int|null  $includeEID  The update site must belong to this extension ID
	 * @param   int|null  $excludeEID  The update site must NOT belong to this extension ID
	 * @param   array     $locations   The update site must match one of these locations
	 *
	 * @return  array  The IDs of the update sites
	 */
	private function getUpdateSitesFor($includeEID = null, $excludeEID = null, $locations = [])
	{
		$db    = $this->container->db;
		$query = $db->getQuery(true)
			->select($db->qn('s.update_site_id'))
			->from($db->qn('#__update_sites', 's'));

		if (!empty($locations))
		{
			$quotedLocations = array_map([$db, 'q'], $locations);
			$query->where($db->qn('location') . 'IN(' . implode(',', $quotedLocations) . ')');
		}

		if (!empty($includeEID) || !empty($excludeEID))
		{
			$query->innerJoin($db->qn('#__update_sites_extensions', 'e') . 'ON(' . $db->qn('e.update_site_id') .
				' = ' . $db->qn('s.update_site_id') . ')'
			);
		}

		if (!empty($includeEID))
		{
			$query->where($db->qn('e.extension_id') . ' = ' . $db->q($includeEID));
		}
		elseif (!empty($excludeEID))
		{
			$query->where($db->qn('e.extension_id') . ' != ' . $db->q($excludeEID));
		}

		try
		{
			$ret = $db->setQuery($query)->loadColumn();
		}
		catch (Exception $e)
		{
			$ret = null;
		}

		return empty($ret) ? [] : $ret;
	}

	private function createFakePackageExtension()
	{
		$db = $this->container->db;

		$query = $db->getQuery(true)
			->insert($db->qn('#__extensions'))
			->columns([
				$db->qn('name'), $db->qn('type'), $db->qn('element'), $db->qn('folder'), $db->qn('client_id'),
				$db->qn('enabled'), $db->qn('access'), $db->qn('protected'), $db->qn('manifest_cache'),
				$db->qn('params'), $db->qn('custom_data'), $db->qn('system_data'), $db->qn('checked_out'),
				$db->qn('checked_out_time'), $db->qn('ordering'), $db->qn('state'),
			])
			->values([
				$db->q('Admin Tools package') . ',' .
				$db->q('package') . ',' .
				$db->q('pkg_admintools') . ',' .
				$db->q('') . ',' .
				$db->q(0) . ',' .
				$db->q(1) . ',' .
				$db->q(1) . ',' .
				$db->q(0) . ',' .
				$db->q('{"name":"Admin Tools package","type":"package","creationDate":"2016-06-01","author":"Nicholas K. Dionysopoulos","copyright":"Copyright (c)2006-2016 Akeeba Ltd \/ Nicholas K. Dionysopoulos","authorEmail":"","authorUrl":"","version":"' . $this->version . '","description":"Admin Tools installation package, for updating from version 3.x only","group":"","filename":"pkg_admintools"}') . ',' .
				$db->q('{}') . ',' .
				$db->q('') . ',' .
				$db->q('') . ',' .
				$db->q(0) . ',' .
				$db->q($db->getNullDate()) . ',' .
				$db->q(0) . ',' .
				$db->q(0),
			]);

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			// Your database if FUBAR.
			return;
		}

		$this->createFakePackageManifest();
	}

	private function createFakePackageManifest()
	{
		$path = JPATH_ADMINISTRATOR . '/manifests/packages/pkg_admintools.xml';

		if (file_exists($path))
		{
			return;
		}

		$isPro = defined('ADMINTOOLS_PRO') ? ADMINTOOLS_PRO : 0;
		$dlid  = $isPro ? '<dlid prefix="dlid=" suffix=""/>' : '';

		$content = <<< XML
<?xml version="1.0" encoding="utf-8"?>
<extension version="3.9.0" type="package" method="upgrade">
	$dlid
    <name>Admin Tools package</name>
    <author>Nicholas K. Dionysopoulos</author>
    <creationDate>2016-06-01</creationDate>
    <packagename>admintools</packagename>
    <version>{$this->version}</version>
    <url>https://www.akeeba.com</url>
    <packager>Akeeba Ltd</packager>
    <packagerurl>https://www.akeeba.com</packagerurl>
    <copyright>Copyright (c)2006-2016 Akeeba Ltd / Nicholas K. Dionysopoulos</copyright>
    <license>GNU GPL v3 or later</license>
    <description>Admin Tools installation package v.3.9.999</description>

    <files>
        <file type="component" id="com_admintools">com_admintools-pro.zip</file>
        <file type="file" id="file_admintools">file_admintools-pro.zip</file>
        <file type="plugin" group="system" id="admintools">plg_system_admintools.zip</file>
        <file type="plugin" group="system" id="atoolsjupdatecheck">plg_system_atoolsjupdatecheck.zip</file>
    </files>

    <scriptfile>script.admintools.php</scriptfile>
</extension>
XML;

		if (!@file_put_contents($content, $path))
		{
			File::write($path, $content);
		}
	}
}

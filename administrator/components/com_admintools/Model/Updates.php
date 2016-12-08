<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') or die;

use Exception;
use FOF30\Container\Container;
use FOF30\Update\Update;
use JFile;
use JLoader;

class Updates extends Update
{
	/**
	 * Obsolete update site locations
	 *
	 * @var  array
	 */
	protected $obsoleteUpdateSiteLocations = array(
		'http://cdn.akeebabackup.com/updates/atpro.xml',
		'http://cdn.akeebabackup.com/updates/atcore.xml',
		'http://cdn.akeebabackup.com/updates/fof.xml',
	);

	/**
	 * Public constructor. Initialises the protected members as well.
	 *
	 * @param array $config
	 */
	public function __construct($config = array())
	{
		$container = Container::getInstance('com_admintools');

		$config['update_component']  = 'pkg_admintools';
		$config['update_sitename']   = 'Admin Tools Core';
		$config['update_site']       = 'http://cdn.akeebabackup.com/updates/pkgadmintoolscore.xml';
		$config['update_extraquery'] = '';

		$isPro = defined('ADMINTOOLS_PRO') ? ADMINTOOLS_PRO : 0;
		$dlid = $container->params->get('downloadid', '');

		// If I have a valid Download ID I will need to use a non-blank extra_query in Joomla! 3.2+
		if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
		{
			// Even if the user entered a Download ID in the Core version. Let's switch his update channel to Professional
			$isPro = true;
		}

		if ($isPro)
		{
			$config['update_sitename']   = 'Admin Tools Professional';
			$config['update_site']       = 'http://cdn.akeebabackup.com/updates/pkgadmintoolspro.xml';
			$config['update_extraquery'] = 'dlid=' . $dlid;
		}

		parent::__construct($config);

		$this->container = $container;

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
	 * Handle automatic updates. Sends update notification emails and/or installs a new version automatically.
	 *
	 * @return  array
	 */
	public function autoupdate()
	{
		$return = array(
			'message' => ''
		);

		// First of all let's check if there are any updates
		$updateInfo = (object) $this->getUpdates(true);

		// There are no updates, there's no point in continuing
		if (!$updateInfo->hasUpdate)
		{
			return array(
				'message' => array("No available updates found")
			);
		}

		$return['message'][] = "Update detected, version: " . $updateInfo->version;

		// Ok, an update is found, what should I do?
		$params     = $this->container->params;
		$autoupdate = $params->get('autoupdateCli', 1);

		// Let's notifiy the user
		if ($autoupdate == 1 || $autoupdate == 2)
		{
			$email = $params->get('notificationEmail');

			if (!$email)
			{
				$return['message'][] = "There isn't an email for notifications, no notification will be sent.";
			}
			else
			{
				// Ok, I can send it out, but before let's check if the user set any frequency limit
				$numfreq    = $params->get('notificationFreq', 1);
				$freqtime   = $params->get('notificationTime', 'day');
				$lastSend   = $this->getLastSend();
				$shouldSend = false;

				if (!$numfreq)
				{
					$shouldSend = true;
				}
				else
				{
					$check = strtotime('-' . $numfreq . ' ' . $freqtime);

					if ($lastSend < $check)
					{
						$shouldSend = true;
					}
					else
					{
						$return['message'][] = "Frequency limit hit, I won't send any email";
					}
				}

				if ($shouldSend)
				{
					if ($this->sendNotificationEmail($updateInfo->version, $email))
					{
						$return['message'][] = "E-mail(s) correctly sent";
					}
					else
					{
						$return['message'][] = "An error occurred while sending e-mail(s). Please double check your settings";
					}

					$this->setLastSend();
				}
			}
		}

		// Let's download and install the latest version
		if ($autoupdate == 1 || $autoupdate == 3)
		{
			$return['message'][] = $this->updateComponent();
		}

		return $return;
	}

	/**
	 * Sends an update notification email
	 *
	 * @param   string $version The newest available version
	 * @param   string $email   The email address of the recipient
	 *
	 * @return  boolean  The result from JMailer::send()
	 */
	private function sendNotificationEmail($version, $email)
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

		$jconfig  = \JFactory::getConfig();
		$sitename = $jconfig->get('sitename');

		$substitutions = array(
			'[VERSION]'  => $version,
			'[SITENAME]' => $sitename
		);

		$email_subject = str_replace(array_keys($substitutions), array_values($substitutions), $email_subject);
		$email_body    = str_replace(array_keys($substitutions), array_values($substitutions), $email_body);

		try
		{
			$mailer = \JFactory::getMailer();

			$mailfrom = $jconfig->get('mailfrom');
			$fromname = $jconfig->get('fromname');

			$mailer->setSender(array($mailfrom, $fromname));
			$mailer->addRecipient($email);
			$mailer->setSubject($email_subject);
			$mailer->setBody($email_body);

			return $mailer->Send();
		}
		catch (\Exception $e)
		{
			// Joomla! 3.5 is written by incompetent bonobos
			return false;
		}
	}

	/**
	 * Automatically download and install the updated version
	 *
	 * @return  string  The message to show in the CLI output
	 */
	private function updateComponent()
	{
		\JLoader::import('joomla.updater.update');

		$db = $this->container->db;

		$updateSiteIDs = $this->getUpdateSiteIds();
		$update_site   = array_shift($updateSiteIDs);

		$query = $db->getQuery(true)
			->select($db->qn('update_id'))
			->from($db->qn('#__updates'))
			->where($db->qn('update_site_id') . ' = ' . $update_site);

		$uid = $db->setQuery($query)->loadResult();

		$update   = new \JUpdate();
		$instance = \JTable::getInstance('update');
		$instance->load($uid);
		$update->loadFromXml($instance->detailsurl);

		if (isset($update->get('downloadurl')->_data))
		{
			$url = trim($update->downloadurl->_data);
		}
		else
		{
			return "No download URL found inside XML manifest";
		}

		$extra_query = $instance->extra_query;

		if ($extra_query)
		{
			if (strpos($url, '?') === false)
			{
				$url .= '?';
			}
			else
			{
				$url .= '&amp;';
			}

			$url .= $extra_query;
		}

		$config   = \JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path');

		if (!$tmp_dest)
		{
			return "Joomla temp directory is empty, please set it before continuing";
		}
		elseif (!\JFolder::exists($tmp_dest))
		{
			return "Joomla temp directory does not exists, please set the correct path before continuing";
		}

		$p_file = \JInstallerHelper::downloadPackage($url);

		if (!$p_file)
		{
			return "An error occurred while trying to download the latest version";
		}

		// Unpack the downloaded package file
		$package = \JInstallerHelper::unpack($tmp_dest . '/' . $p_file);

		if (!$package)
		{
			return "An error occurred while unpacking the file, please double check your Joomla temp directory";
		}

		$installer = new \JInstaller;
		$installed = $installer->install($package['extractdir']);

		// Let's cleanup the downloaded archive and the temp folder
		if (\JFolder::exists($package['extractdir']))
		{
			\JFolder::delete($package['extractdir']);
		}

		if (\JFile::exists($package['packagefile']))
		{
			\JFile::delete($package['packagefile']);
		}

		if ($installed)
		{
			return "Component successfully updated";
		}
		else
		{
			return "An error occurred while trying to update the component";
		}
	}

	/**
	 * Does the user need to provide FTP credentials? It also registers any FTP credentials provided in the URL.
	 *
	 * @return  bool  True if the user needs to provide FTP credentials
	 */
	public function needsFTPCredentials()
	{
		// Determine wether FTP credentials have been passed along with the current request
		\JLoader::import('joomla.client.helper');

		$user = $this->input->get('username', null, 'raw');
		$pass = $this->input->get('password', null, 'raw');

		if (!(($user == '') && ($pass == '')))
		{
			// Add credentials to the session
			if (\JClientHelper::setCredentials('ftp', $user, $pass))
			{
				return false;
			}

			return true;
		}

		return !\JClientHelper::hasCredentials('ftp');
	}

	private function getLastSend()
	{
		return $this->container->params->get('admintools_autoupdate_lastsend', 0);
	}

	/**
	 * Set the UNIX timestamp of the last time we sent out an update notificatin email to be right now
	 *
	 * @return  void
	 */
	private function setLastSend()
	{
		$this->container->params->set('admintools_autoupdate_lastsend', time());
		$this->container->params->save();
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
		$deleteIDs      = array();

		// Get component ID
		$componentID = $this->findExtensionId('com_admintools', 'component');

		// Get package ID
		$packageID = $this->findExtensionId('pkg_admintools', 'package');

		// Update sites for old extension ID (all)
		if ($componentID)
		{
			// Old component packages
			$moreIDs   = $this->getUpdateSitesFor($componentID, null);

			if (is_array($moreIDs) && count($moreIDs))
			{
				$deleteIDs = array_merge($deleteIDs, $moreIDs);
			}

			// Obsolete update sites
			$moreIDs   = $this->getUpdateSitesFor(null, $componentID, $this->obsoleteUpdateSiteLocations);

			if (is_array($moreIDs) && count($moreIDs))
			{
				$deleteIDs = array_merge($deleteIDs, $moreIDs);
			}
		}

		// Update sites for any but current extension ID, location matching any of the obsolete update sites
		if ($packageID)
		{
			// Update sites for all of the current extension ID update sites
			$moreIDs   = $this->getUpdateSitesFor($packageID, null);

			if (is_array($moreIDs) && count($moreIDs))
			{
				$deleteIDs = array_merge($deleteIDs, $moreIDs);
			}

			$deleteIDs = array_unique($deleteIDs);

			// Remove the last update site
			if (count($deleteIDs))
			{
				$lastID = array_pop($moreIDs);
				$pos = array_search($lastID, $deleteIDs);
				unset($deleteIDs[$pos]);
			}
		}

		$db        = $this->container->db;
		$deleteIDs = array_unique($deleteIDs);

		if (empty($deleteIDs) || !count($deleteIDs))
		{
			return;
		}

		$deleteIDs = array_map(array($db, 'q'), $deleteIDs);

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
	 * @param   string  $element  Extension element, e.g. com_foo, mod_foo, lib_foo, pkg_foo or foo (CAUTION: plugin, file!)
	 * @param   string  $type     Extension type: component, module, library, package, plugin or file
	 * @param   null    $folder   Plugins: plugin folder. Modules: admin/site
	 *
	 * @return  int  Extension ID or 0 on failure
	 */
	private function findExtensionId($element, $type = 'component', $folder = null)
	{
		$db = $this->container->db;
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
	private function getUpdateSitesFor($includeEID = null, $excludeEID = null, $locations = array())
	{
		$db = $this->container->db;
		$query = $db->getQuery(true)
		            ->select($db->qn('s.update_site_id'))
		            ->from($db->qn('#__update_sites', 's'));

		if (!empty($locations))
		{
			$quotedLocations = array_map(array($db, 'q'), $locations);
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

		return empty($ret) ? array() : $ret;
	}

	private function createFakePackageExtension()
	{
		$db = $this->container->db;

		$query = $db->getQuery(true)
		            ->insert($db->qn('#__extensions'))
		            ->columns(array(
			            $db->qn('name'), $db->qn('type'), $db->qn('element'), $db->qn('folder'), $db->qn('client_id'),
			            $db->qn('enabled'), $db->qn('access'), $db->qn('protected'), $db->qn('manifest_cache'),
			            $db->qn('params'), $db->qn('custom_data'), $db->qn('system_data'), $db->qn('checked_out'),
			            $db->qn('checked_out_time'), $db->qn('ordering'), $db->qn('state')
		            ))
		            ->values(array(
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
			            $db->q(0)
		            ));

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (\Exception $e)
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


		$content = <<< XML
<?xml version="1.0" encoding="utf-8"?>
<extension version="3.3.0" type="package" method="upgrade">
    <name>Admin Tools package</name>
    <author>Nicholas K. Dionysopoulos</author>
    <creationDate>2016-06-01</creationDate>
    <packagename>admintools</packagename>
    <version>{$this->version}</version>
    <url>https://www.akeebabackup.com</url>
    <packager>Akeeba Ltd</packager>
    <packagerurl>https://www.akeebabackup.com</packagerurl>
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
			JLoader::import('joomla.filesystem.file');
			JFile::write($path, $content);
		}
	}
}
<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

/**
 * The updates provisioning Model
 */
class AdmintoolsModelUpdates extends F0FUtilsUpdate
{
    /**
     * The name of the package for which updates are registered
     *
     * @var  string
     */
    protected $packageName = 'pkg_admintools';

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
		$isPro = defined('ADMINTOOLS_PRO') ? ADMINTOOLS_PRO : 0;

        JLoader::import('joomla.application.component.helper');
        $dlid = F0FUtilsConfigHelper::getComponentConfigurationValue('com_admintools', 'downloadid', '');

        $this->extraQuery = null;

        // If I have a valid Download ID I will need to use a non-blank extra_query in Joomla! 3.2+
        if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
        {
            // Even if the user entered a Download ID in the Core version. Let's switch his update channel to Professional
            $isPro = true;

            $this->extraQuery = 'dlid=' . $dlid;
        }

        $mergeConfig = array(
            'update_component'  => $this->packageName,
            'update_component_description' => 'Admin Tools ' . ($isPro ? 'Professional' : 'Core'),
            'update_version' => defined('ADMINTOOLS_VERSION') ? ADMINTOOLS_VERSION : 'dev',
            'update_site' => 'http://cdn.akeebabackup.com/updates/pkgadmintools' . ($isPro ? 'pro' : 'core') . '.xml',
            'update_extraquery' => $this->extraQuery,
            'update_sitename' => 'Admin Tools ' . ($isPro ? 'Professional' : 'Core'),
        );

        if (!is_array($config))
        {
            $config = array();
        }

        $config = array_merge($mergeConfig, $config);

        parent::__construct($config);

        $this->extension_id = $this->findExtensionId($this->packageName, 'package');

        if (!$this->extension_id)
        {
            $this->createFakePackageExtension();
            $this->extension_id = $this->findExtensionId($this->packageName, 'package');
        }

        $this->createFakePackageManifest();
	}

    public function refreshUpdateSite()
    {
        $this->removeObsoleteComponentUpdateSites();

        parent::refreshUpdateSite();
    }

    public function autoupdate()
    {
        $return = array(
            'message' => ''
        );

        // First of all let's check if there are any updates
        $updateInfo = (object)$this->getUpdates(true);

        // There are no updates, there's no point in continuing
        if(!$updateInfo->hasUpdate)
        {
            return array(
                'message' => array("No available updates found")
            );
        }

        $return['message'][] = "Update detected, version: ".$updateInfo->version;

        // Ok, an update is found, what should I do?
        $autoupdate = F0FUtilsConfigHelper::getComponentConfigurationValue($this->component, 'autoupdateCli', 1);

        // Let's notifiy the user
        if($autoupdate == 1 || $autoupdate == 2)
        {
            $email = F0FUtilsConfigHelper::getComponentConfigurationValue($this->component, 'notificationEmail');

            if(!$email)
            {
                $return['message'][] = "There isn't an email for notifications, no notification will be sent.";
            }
            else
            {
                // Ok, I can send it out, but before let's check if the user set any frequency limit
                $numfreq    =
                    F0FUtilsConfigHelper::getComponentConfigurationValue($this->component, 'notificationFreq', 1);
                $freqtime   =
                    F0FUtilsConfigHelper::getComponentConfigurationValue($this->component, 'notificationTime', 'day');
                $lastSend   = $this->getLastSend();
                $shouldSend = false;

                if(!$numfreq)
                {
                    $shouldSend = true;
                }
                else
                {
                    $check = strtotime('-'.$numfreq.' '.$freqtime);

                    if($lastSend < $check)
                    {
                        $shouldSend = true;
                    }
                    else
                    {
                        $return['message'][] = "Frequency limit hit, I won't send any email";
                    }
                }

                if($shouldSend)
                {
                    if($this->doSendNotificationEmail($updateInfo->version, $email))
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
        if($autoupdate == 1 || $autoupdate == 3)
        {
            // DO NOT REMOVE THIS. Since we have a plural class name (AdmintoolsModelCpanels) in a singular file (cpanel.php)
            // we have to do this trick in order to load the correct file
            $t = F0FModel::getTmpInstance('Cpanel', 'AdmintoolsModel');

            if(F0FModel::getTmpInstance('Cpanels', 'AdmintoolsModel')->needsDownloadID())
            {
                $return['message'][] = "You have to enter the DownloadID in order to update your pro version";
            }
            else
            {
                $return['message'][] = $this->doUpdateComponent();
            }
        }

        return $return;
    }

    private function getLastSend()
    {
        $raw = $this->getCommonParameter('lastsend', 0);

        return (int) $raw;
    }

    private function setLastSend()
    {
        $now = time();

        $this->setCommonParameter('lastsend', $now);
    }

    /**
     * Does the user need to provide FTP credentials? It also registers any FTP credentials provided in the URL.
     *
     * @return  bool  True if the user needs to provide FTP credentials
     */
    public function needsFTPCredentials()
    {
        // Determine wether FTP credentials have been passed along with the current request
        JLoader::import('joomla.client.helper');

        $user = $this->input->get('username', null, 'raw');
        $pass = $this->input->get('password', null, 'raw');

        if (!(($user == '') && ($pass == '')))
        {
            // Add credentials to the session
            if (JClientHelper::setCredentials('ftp', $user, $pass))
            {
                return false;
            }

            return true;
        }

        return !JClientHelper::hasCredentials('ftp');
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
    protected function removeObsoleteComponentUpdateSites()
    {
        // Initialize
        $deleteIDs      = array();

        // Get component ID
        $componentID = $this->findExtensionId('com_admintools', 'component');

        // Get package ID
        $packageID = $this->findExtensionId('pkg_admintools', 'package');

        // Update sites for old extension ID (all)
        $deleteIDs = array();

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

        $db        = JFactory::getDbo();
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
    protected function findExtensionId($element, $type = 'component', $folder = null)
    {
        $db = JFactory::getDbo();
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
    protected function getUpdateSitesFor($includeEID = null, $excludeEID = null, $locations = array())
    {
        $db = JFactory::getDbo();
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

    protected function createFakePackageExtension()
    {
        $db = $this->getDbo();

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
                        $db->q('{"name":"Admin Tools package","type":"package","creationDate":"2016-06-16","author":"Nicholas K. Dionysopoulos","copyright":"Copyright (c)2006-2016 Akeeba Ltd \/ Nicholas K. Dionysopoulos","authorEmail":"","authorUrl":"","version":"' . $this->version . '","description":"Admin Tools installation package, for updating from version 3.x only","group":"","filename":"pkg_admintools"}') . ',' .
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

    protected function createFakePackageManifest()
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
    <creationDate>2016-06-16</creationDate>
    <packagename>admintools</packagename>
    <version>{$this->version}</version>
    <url>https://www.akeebabackup.com</url>
    <packager>Akeeba Ltd</packager>
    <packagerurl>https://www.akeebabackup.com</packagerurl>
    <copyright>Copyright (c)2006-2016 Akeeba Ltd / Nicholas K. Dionysopoulos</copyright>
    <license>GNU GPL v3 or later</license>
    <description>Admin Tools installation package for upgrading from version 3.x</description>

    <files>
        <file type="component" id="com_admintools">com_admintools-pro.zip</file>
        <file type="plugin" group="system" id="admintools">plg_system_admintools.zip</file>
        <file type="plugin" group="system" id="atoolsjupdatecheck">plg_system_atoolsjupdatecheck.zip</file>
    </files>

    <scriptfile>script.admintools.php</scriptfile>
</extension>
XML;

        JLoader::import('joomla.filesystem.file');
        JFile::write($path, $content);
    }
}
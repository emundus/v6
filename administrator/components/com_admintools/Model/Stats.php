<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') || die;

use AkeebaUsagestats;
use Exception;
use FOF40\Model\Model;
use Joomla\CMS\Uri\Uri;

class Stats extends Model
{
	/**
	 * Get an existing unique site ID or create a new one
	 *
	 * @return string
	 */
	public function getSiteId()
	{
		// Can I load a site ID from the database?
		$siteId = $this->getCommonVariable('stats_siteid', null);
		// Can I load the site Url from the database?
		$siteUrl = $this->getCommonVariable('stats_siteurl', null);

		// No id or the saved URL is not the same as the current one (ie site restored to a new url)?
		// Create a new, random site ID and save it to the database
		if (empty($siteId) || (md5(Uri::base()) != $siteUrl))
		{
			$siteUrl = md5(Uri::base());
			$this->setCommonVariable('stats_siteurl', $siteUrl);

			$randomData = $this->getRandomData(120);
			$siteId     = sha1($randomData);

			$this->setCommonVariable('stats_siteid', $siteId);
		}

		return $siteId;
	}

	/**
	 * Send site information to the remove collection service
	 *
	 * @param   bool  $useIframe  Should I use an IFRAME?
	 *
	 * @return bool
	 */
	public function collectStatistics($useIframe)
	{
		// Make sure there is a site ID set
		$siteId = $this->getSiteId();

		// UsageStats file is missing, no need to continue
		if (!file_exists(JPATH_ROOT . '/administrator/components/com_admintools/assets/stats/usagestats.php'))
		{
			return false;
		}

		if (!class_exists('AkeebaUsagestats', false))
		{
			require_once JPATH_ROOT . '/administrator/components/com_admintools/assets/stats/usagestats.php';
		}

		// UsageStats file is missing, no need to continue
		if (!class_exists('AkeebaUsagestats'))
		{
			return false;
		}

		$lastrun = $this->getCommonVariable('stats_lastrun', 0);

		// Data collection is turned off
		if (!$this->container->params->get('stats_enabled', 1))
		{
			return false;
		}

		// It's not time to collect the stats
		if (time() < ($lastrun + 3600 * 24))
		{
			return false;
		}

		require_once JPATH_ROOT . '/administrator/components/com_admintools/version.php';

		$db    = $this->container->db;
		$stats = new AkeebaUsagestats();

		$stats->setSiteId($siteId);

		// I can't use list since dev release don't have any dots
		$at_parts    = explode('.', ADMINTOOLS_VERSION);
		$at_major    = $at_parts[0];
		$at_minor    = $at_parts[1] ?? '';
		$at_revision = $at_parts[2] ?? '';

		[$php_major, $php_minor, $php_revision] = explode('.', phpversion());
		$php_qualifier = strpos($php_revision, '~') !== false ? substr($php_revision, strpos($php_revision, '~')) : '';

		[$cms_major, $cms_minor, $cms_revision] = explode('.', JVERSION);
		[$db_major, $db_minor, $db_revision] = explode('.', $db->getVersion());
		$db_qualifier = strpos($db_revision, '~') !== false ? substr($db_revision, strpos($db_revision, '~')) : '';

		$db_driver = get_class($db);

		if (stripos($db_driver, 'mysql') !== false)
		{
			$stats->setValue('dt', 1);
		}
		elseif (stripos($db_driver, 'sqlsrv') !== false || stripos($db_driver, 'sqlazure'))
		{
			$stats->setValue('dt', 2);
		}
		elseif (stripos($db_driver, 'postgresql') !== false)
		{
			$stats->setValue('dt', 3);
		}
		else
		{
			$stats->setValue('dt', 0);
		}

		$stats->setValue('sw', ADMINTOOLS_PRO ? 4 : 3); // software
		$stats->setValue('pro', ADMINTOOLS_PRO); // pro
		$stats->setValue('sm', $at_major); // software_major
		$stats->setValue('sn', $at_minor); // software_minor
		$stats->setValue('sr', $at_revision); // software_revision
		$stats->setValue('pm', $php_major); // php_major
		$stats->setValue('pn', $php_minor); // php_minor
		$stats->setValue('pr', $php_revision); // php_revision
		$stats->setValue('pq', $php_qualifier); // php_qualifiers
		$stats->setValue('dm', $db_major); // db_major
		$stats->setValue('dn', $db_minor); // db_minor
		$stats->setValue('dr', $db_revision); // db_revision
		$stats->setValue('dq', $db_qualifier); // db_qualifiers
		$stats->setValue('ct', 1); // cms_type
		$stats->setValue('cm', $cms_major); // cms_major
		$stats->setValue('cn', $cms_minor); // cms_minor
		$stats->setValue('cr', $cms_revision); // cms_revision

		// Store the last execution time. We must store it even if we fail since we don't want a failed stats collection
		// to cause the site to stop responding.
		$this->setCommonVariable('stats_lastrun', time());

		$return = $stats->sendInfo($useIframe);

		return $return;
	}

	/**
	 * Load a variable from the common variables table. If it doesn't exist it returns $default
	 *
	 * @param   string  $key      The key to load
	 * @param   mixed   $default  The default value if the key doesn't exist
	 *
	 * @return mixed The contents of the key or null if it's not present
	 */
	public function getCommonVariable($key, $default = null)
	{
		$db    = $this->container->db;
		$query = $db->getQuery(true)
			->select($db->qn('value'))
			->from($db->qn('#__akeeba_common'))
			->where($db->qn('key') . ' = ' . $db->q($key));

		try
		{
			$db->setQuery($query);
			$result = $db->loadResult();
		}
		catch (Exception $e)
		{
			$result = $default;
		}

		return $result;
	}

	/**
	 * Set a variable to the common variables table.
	 *
	 * @param   string  $key    The key to save
	 * @param   mixed   $value  The value to save
	 */
	public function setCommonVariable($key, $value)
	{
		$db    = $this->container->db;
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__akeeba_common'))
			->where($db->qn('key') . ' = ' . $db->q($key));

		try
		{
			$db->setQuery($query);
			$count = $db->loadResult();
		}
		catch (Exception $e)
		{
			return;
		}

		if (!$count)
		{
			$query = $db->getQuery(true)
				->insert($db->qn('#__akeeba_common'))
				->columns([$db->qn('key'), $db->qn('value')])
				->values($db->q($key) . ', ' . $db->q($value));
		}
		else
		{
			$query = $db->getQuery(true)
				->update($db->qn('#__akeeba_common'))
				->set($db->qn('value') . ' = ' . $db->q($value))
				->where($db->qn('key') . ' = ' . $db->q($key));
		}

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 *
	 * Returns a cryptographically secure random value.
	 *
	 * @param   integer  $bytes  How many bytes to return
	 *
	 * @return  string
	 */
	public function getRandomData($bytes = 32)
	{
		if (extension_loaded('openssl') && (version_compare(PHP_VERSION, '5.3.4') >= 0 || IS_WIN))
		{
			$strong    = false;
			$randBytes = openssl_random_pseudo_bytes($bytes, $strong);

			if ($strong)
			{
				return $randBytes;
			}
		}

		if (extension_loaded('mcrypt'))
		{
			return mcrypt_create_iv($bytes, MCRYPT_DEV_URANDOM);
		}

		return random_bytes($bytes);
	}
}

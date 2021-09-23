<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

class EventbookingHelperPlugin
{
	/**
	 * Check to see if the plugin should run now
	 *
	 * @param   Registry  $params
	 * @param   int       $cacheTime
	 * @param   string    $name
	 * @param   string    $type
	 *
	 * @return bool
	 */
	public static function checkAndStoreLastRuntime($params, $cacheTime, $name, $type = 'system')
	{
		$now     = time();
		$lastRun = (int) $params->get('last_run', 0);

		if (($now - $lastRun) < $cacheTime)
		{
			return false;
		}

		$db = Factory::getDbo();

		$params->set('last_run', $now);

		$query = $db->getQuery(true)
			->update('#__extensions')
			->set($db->quoteName('params') . '=' . $db->quote($params->toString()))
			->where($db->quoteName('element') . '=' . $db->quote($name))
			->where($db->quoteName('folder') . '=' . $db->quote($type));

		try
		{
			// Lock the tables to prevent multiple plugin executions causing a race condition
			$db->lockTable('#__extensions');
		}
		catch (Exception $e)
		{
			// If we can't lock the tables it's too risk continuing execution
			return false;
		}

		try
		{
			// Update the plugin parameters
			$result = $db->setQuery($query)->execute();
			self::clearCacheGroups(['com_plugins'], [0, 1]);
		}
		catch (Exception $exc)
		{
			// If we failed to execute
			$db->unlockTables();
			$result = false;
		}

		try
		{
			// Unlock the tables after writing
			$db->unlockTables();
		}
		catch (Exception $e)
		{
			// If we can't lock the tables assume we have somehow failed
			$result = false;
		}

		return $result;
	}

	/**
	 * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
	 *
	 * @param   array  $clearGroups   The cache groups to clean
	 * @param   array  $cacheClients  The cache clients (site, admin) to clean
	 *
	 * @return  void
	 *
	 * @since   2.0.4
	 */
	protected static function clearCacheGroups(array $clearGroups, array $cacheClients = [0, 1])
	{
		$conf = Factory::getConfig();

		foreach ($clearGroups as $group)
		{
			foreach ($cacheClients as $client_id)
			{
				try
				{
					$options = [
						'defaultgroup' => $group,
						'cachebase'    => ($client_id) ? JPATH_ADMINISTRATOR . '/cache' :
							$conf->get('cache_path', JPATH_SITE . '/cache'),
					];
					$cache   = JCache::getInstance('callback', $options);
					$cache->clean();
				}
				catch (Exception $e)
				{
					// Ignore it
				}
			}
		}
	}
}
<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') || die;

use FOF40\Model\Model;

class UnblockIP extends Model
{
	/**
	 * Removed the current IP from all the "block" lists
	 *
	 * @param   string|array  $ips  IP addresses to check and delete
	 *
	 * @return  bool            Did I had data to delete? If not, we will have to warn the user
	 */
	public function unblockIP($ips)
	{
		$ips = (array) $ips;

		/** @var AutoBannedAddresses $autoban */
		$autoban = $this->container->factory->model('AutoBannedAddresses')->tmpInstance();

		/** @var IPAutoBanHistories $history */
		$history = $this->container->factory->model('IPAutoBanHistories')->tmpInstance();

		/** @var BlacklistedAddresses $black */
		$black = $this->container->factory->model('BlacklistedAddresses')->tmpInstance();

		/** @var SecurityExceptions $log */
		$log = $this->container->factory->model('SecurityExceptions')->tmpInstance();

		$db    = $this->container->db;
		$found = false;

		// Let's delete all the IPs. We are going to directly use the database since it would be faster
		// than loading the record and then deleting it
		foreach ($ips as $ip)
		{
			$autoban->reset()->setState('ip', $ip);
			$history->reset()->setState('ip', $ip);
			$black->reset()->setState('ip', $ip);
			$log->reset()->setState('ip', $ip);

			if (count($autoban->get(true)))
			{
				$found = true;

				$query = $db->getQuery(true)
					->delete($db->qn('#__admintools_ipautoban'))
					->where($db->qn('ip') . ' = ' . $db->q($ip));
				$db->setQuery($query)->execute();
			}

			if (count($history->get(true)))
			{
				$found = true;

				$query = $db->getQuery(true)
					->delete($db->qn('#__admintools_ipautobanhistory'))
					->where($db->qn('ip') . ' = ' . $db->q($ip));
				$db->setQuery($query)->execute();
			}

			if (count($black->get(true)))
			{
				$found = true;

				$query = $db->getQuery(true)
					->delete($db->qn('#__admintools_ipblock'))
					->where($db->qn('ip') . ' = ' . $db->q($ip));
				$db->setQuery($query)->execute();
			}

			// I have to delete the log of security exceptions, too. Otherwise at the next check the user will be
			// banned once again
			if (count($log->get(true)))
			{
				$found = true;

				$query = $db->getQuery(true)
					->delete($db->qn('#__admintools_log'))
					->where($db->qn('ip') . ' = ' . $db->q($ip));
				$db->setQuery($query)->execute();
			}
		}

		if (!$found)
		{
			return false;
		}

		return true;
	}
}

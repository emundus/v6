<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopHistoryClass extends hikashopClass
{
	public $tables = array('history');
	public $pkeys = array('history_id');

	public function addRecord($order)
	{
		if (empty($order) || empty($order->order_id)) {
			return false;
		}

		if (isset($order->history) && $order->history === false) {
			return true;
		}

		$history = new stdClass();
		$history->history_order_id = (int)$order->order_id;
		$history->history_created = time();
		$config = hikashop_config();
		if($config->get('history_ip', 1))
			$history->history_ip = hikashop_getIP();
		$history->history_user_id = hikashop_loadUser();

		if (!empty($order->order_status)) {
			$history->history_new_status = $order->order_status;
		} elseif (!empty($order->old->order_status)) {
			$history->history_new_status = $order->old->order_status;
		} else {
			$orderClass = hikashop_get('class.order');
			$old = $orderClass->get($order->order_id);
			$history->history_new_status = $old->order_status;
		}

		if (!empty($order->history) && is_object($order->history)) {
			foreach (get_object_vars($order->history) as $k => $v) {
				if (isset($history->$k)) {
					continue;
				}
				$history->$k = $v;
			}
		}

		$ret = $this->save($history);

		return $ret;
	}

	public function deleteRecords($elements)
	{
		if (!is_array($elements)) {
			$elements = array($elements);
		}

		hikashop_toInteger($elements);

		$query = 'DELETE FROM ' . hikashop_table('history') . ' WHERE history_order_id IN (' . implode(',', $elements) . ')';

		$db = JFactory::getDBO();
		$db->setQuery($query);
		$db->execute();

		return true;
	}
}

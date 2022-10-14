<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashopUserpointstovendor extends hikashopPlugin {
	var $multiple = false;
	var $name = 'userpointstovendor';

	public function loadUserPointsPluginParams($id = 0) {
		static $pluginsCache = array();
		$key = 'payment_userpoints_'.$id;
		if(!isset($pluginsCache[$key])){
			$query = 'SELECT * FROM '.hikashop_table('payment').' WHERE payment_type = '.$this->db->Quote('userpoints');
			if($id > 0) {
				$query .= ' AND payment_id = ' . (int)$id;
			}
			$this->db->setQuery($query);
			$pluginsCache[$key] = $this->db->loadObject();
		}
		if(!empty($pluginsCache[$key])) {
			$params = 'payment_params';
			$this->userpoints_plugin_params = hikashop_unserialize($pluginsCache[$key]->$params);
			$this->userpoints_plugin_data = $pluginsCache[$key];
			return true;
		}
		$this->userpoints_plugin_params = null;
		$this->userpoints_plugin_data = null;
		return false;
	}

	public function onAfterOrderCreate(&$order, &$send_email) {
		if(hikashop_isClient('administrator'))
			return true;
		if(!isset($order->order_status))
			return true;

		if( !empty($order->order_type) && $order->order_type != 'sale' )
			return true;
		if( !empty($order->userpoints_process->updated) ) // TODO
			return true;
		$this->giveAndGiveBack($order);
		unset($this->orderObject);
		return true;
	}

	public function onAfterOrderUpdate(&$order, &$send_email) {
		if(!isset($order->order_status))
			return true;

		if( (!empty($order->order_type) && $order->order_type != 'sale') || $order->old->order_type != 'sale' )
			return true;
		if( !empty($order->userpoints_process->updated) ) // TODO
			return true;
		$this->giveAndGiveBack($order);
		unset($this->orderObject);
		return true;
	}

	public function addPoints($points, $order, $data = null, $forceMode = null) {
		if($forceMode === null)
			return false;

		if($points === 0)
			return true;
		$points_mode = $forceMode;

		if(empty($order->vendor_admin)) {
			$query = 'SELECT hu.* FROM ' . hikashop_table('hikamarket_vendor', false) . ' AS v '.
				' INNER JOIN ' . hikashop_table('user') . ' AS hu ON v.vendor_admin_id = hu.user_id '.
				' WHERE v.vendor_id = ' . (int)$order->order_vendor_id;
			$this->db->setQuery($query);
			$order->vendor_admin = $this->db->loadObject();
		}

		if($points_mode == 'aup') {
			if(!$this->userpoints_plugin->getAUP(true))
				return false;

			if($data === null)
				$data = $this->userpoints_plugin->getDataReference($order);
			$aupid = AlphaUserPointsHelper::getAnyUserReferreID($order->vendor_admin->user_cms_id);
			AlphaUserPointsHelper::newpoints('plgaup_orderValidation', $aupid, '', $data, $points);
			return true;
		}

		if($points_mode == 'esp') {
			if(!$this->userpoints_plugin->getEasysocial(true))
				return false;

			if($data === null)
				$data = $this->userpoints_plugin->getDataReference($order, $points_mode);
			$eas_points = FD::points();
			$userInfo = FD::user( $order->vendor_admin->user_cms_id );
			return $eas_points->assignCustom( $userInfo->id, $points, $data );
		}

		$ret = true;
		$userClass = hikashop_get('class.user');
		$oldUser = $userClass->get((int)$order->vendor_admin->user_id);
		if(!isset($oldUser->user_points) && !in_array('user_points', array_keys(get_object_vars($oldUser))))
			return false;
		if(empty($oldUser->user_points))
			$oldUser->user_points = 0;

		$user = new stdClass();
		$user->user_id = $oldUser->user_id;
		$user->user_points = (int)$oldUser->user_points + $points;
		if($user->user_points < 0) {
			$points = -$oldUser->user_points;
			$user->user_points = 0;
			$ret = false;
		}
		$userClass->save($user);
		return $ret;
	}

	public function loadFullOrder($order_id) {
		if(empty($this->orderObject) || $this->orderObject->order_id != $order_id) {
			$classOrder = hikashop_get('class.order');
			$this->orderObject = $classOrder->get($order_id, false);
		}
	}

	public function giveAndGiveBack(&$order) {
		$this->config = hikashop_config();
		$confirmed = null;
		if(!isset($this->params)) {
			$pluginsClass = hikashop_get('class.plugins');
			$plugin = $pluginsClass->getByName('hikashop', 'userpoints');
			$confirmed = explode(',', @$plugin->params['order_status']);
		} else if($this->params->get('order_status', '') != '') {
			$confirmed = explode(',', $this->params->get('order_status', ''));
		}
		if(empty($confirmed))
			$confirmed = explode(',', $this->config->get('invoice_order_statuses'));
		if(empty($confirmed))
			$confirmed = array('confirmed','shipped');
		$created = $this->config->get('order_created_status');

		$points = array();

		$this->loadFullOrder($order->order_id);

		$creation = empty($order->old->order_status);
		$changed = !empty($order->old->order_status) && !empty($order->order_status) && $order->old->order_status != $order->order_status;
		$old_confirmed = !empty($order->old->order_status) && in_array($order->old->order_status, $confirmed);
		$old_created = !empty($order->old->order_status) && ($order->old->order_status == $created);
		$new_confirmed = !empty($order->order_status) && in_array($order->order_status, $confirmed);
		$new_created = !empty($order->order_status) && ($order->order_status == $created);
		if(($creation || ($changed && !$old_confirmed)) && $new_confirmed) {
			if(!empty($this->orderObject->order_payment_params->userpoints->use_points)) {
				$m = $this->orderObject->order_payment_params->userpoints->use_mode;
				if(empty($points[ $m ]))
					$points[ $m ] = 0;
				$points[ $m ] -= $this->orderObject->order_payment_params->userpoints->use_points;
			}

			if($this->orderObject->order_payment_method == 'userpoints') {
				if($this->loadUserPointsPluginParams($this->orderObject->order_payment_id) && !empty($this->userpoints_plugin_params->value)) {
					$p = round($this->orderObject->order_full_price / $this->userpoints_plugin_params->value, 0);
					$m = $this->userpoints_plugin_params->points_mode;
					if(empty($points[ $m ]))
						$points[ $m ] = 0;
					$points[ $m ] -= $p;
				}
			}
		}

		if($changed && $old_confirmed && !$new_confirmed) {
			if(!empty($this->orderObject->order_payment_params->userpoints->use_points)) {
				$m = $this->orderObject->order_payment_params->userpoints->use_mode;
				if(empty($points[ $m ]))
					$points[ $m ] = 0;
				$points[ $m ] += $this->orderObject->order_payment_params->userpoints->use_points;
			}

			if($this->orderObject->order_payment_method == 'userpoints') {
				if($this->loadUserPointsPluginParams($this->orderObject->order_payment_id) && !empty($this->userpoints_plugin_params->value)) {
					$p = round($this->orderObject->order_full_price / $this->userpoints_plugin_params->value, 0);
					$m = $this->userpoints_plugin_params->points_mode;
					if(empty($points[ $m ]))
						$points[ $m ] = 0;
					$points[ $m ] += $p;
				}
			}
		}

		if(empty($points))
			return;

		$this->userpoints_plugin = hikashop_import('hikashop', 'userpoints');

		$query = 'SELECT o.* FROM '.hikashop_table('order').' AS o '.
			' WHERE o.order_type = ' . $this->db->quote('subsale') . ' AND o.order_parent_id = ' . (int)$order->order_id;
		$this->db->setQuery($query);
		$subOrders = $this->db->loadObjectList('order_id');

		$total = 0.0;
		foreach($subOrders as &$subOrder) {
			$subOrder->order_full_price = (float)hikashop_toFloat($subOrder->order_full_price);
			$total += $subOrder->order_full_price;
		}
		unset($subOrder);

		foreach($points as $mode => $p) {
			if(empty($p))
				continue;

			foreach($subOrders as $subOrder) {
				$ratio = $subOrder->order_full_price / $total;
				$vendor_p = -round($p * $ratio, 0);
				$this->addPoints($vendor_p, $subOrder, null, $mode);
			}
		}

		unset($this->userpoints_plugin);
	}
}

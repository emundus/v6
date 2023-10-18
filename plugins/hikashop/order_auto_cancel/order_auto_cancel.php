<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashopOrder_auto_cancel extends JPlugin
{
	var $message = '';
	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
	}

	function onHikashopCronTrigger(&$messages){
		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->getByName('hikashop','order_auto_cancel');

		$plugin_notify = ( isset($plugin->params['notify']) ) ? $plugin->params['notify'] : false;

		if(empty($plugin->params['period'])){
			$plugin->params['period'] = 86400;
		}
		$this->period = $plugin->params['period'];
		if(!empty($plugin->params['last_cron_update']) && $plugin->params['last_cron_update']+$this->period>time()){
			return true;
		}
		$plugin->params['last_cron_update']=time();
		$pluginsClass->save($plugin);
		$this->checkOrders((bool)$plugin_notify);
		if(!empty($this->message)){
			$messages[] = $this->message;
		}
		return true;
	}

	function checkOrders($notify=false){
		$db = JFactory::getDBO();
		$config =& hikashop_config();
		$status = $config->get('order_created_status');
		$query = 'SELECT order_id, order_status, order_created FROM '.hikashop_table('order').
			' WHERE order_type = '.$db->Quote('sale').' AND order_created < '.(time()-$this->period).' AND order_status = '.$db->Quote($status).
			' ORDER BY order_created ASC LIMIT 0, 20';
		$db->setQuery($query);
		$orders = $db->loadObjectList();

		if(!empty($orders)){
			$orderClass = hikashop_get('class.order');
			$status = $config->get('cancelled_order_status');
			$statuses = explode(',',$status);
			$status = reset($statuses);
			foreach($orders as $order){
				$update = new stdClass();
				$update->order_id = $order->order_id;
				$update->order_status = $status;
				if($notify){
					$update->history = new stdClass();
					$update->history->history_notified = 1;
				}
				$orderClass->save($update);
			}
		}

		$app = JFactory::getApplication();
		$this->message = 'Orders checked';
		$app->enqueueMessage($this->message );
		return true;
	}
}

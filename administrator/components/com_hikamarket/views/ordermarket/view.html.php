<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class ordermarketViewOrdermarket extends hikamarketView {

	const ctrl = 'order';
	const name = 'HIKAMARKET_ORDERMARKET';
	const icon = 'generic';

	public function display($tpl = null, $params = null) {
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName();
		$fct = $this->getLayout();
		if(method_exists($this, $fct))
			$this->$fct($params);
		parent::display($tpl);
	}

	public function show_order_back_show($params = null) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$currencyHelper = hikamarket::get('shop.class.currency');
		$this->assignRef('currencyHelper', $currencyHelper);

		$data = null;
		$vendor_transactions = array();
		$order_id = 0;

		if(!empty($params)) {
			$order_id = (int)$params->get('order_id');
		} else {
			$order_id = hikamarket::getCID('order_id');
		}

		$ajax = (hikaInput::get()->getCmd('tmpl', '') == 'component');

		$this->assignRef('data', $data);
		$this->assignRef('vendor_transactions', $vendor_transactions);
		$this->assignRef('order_id', $order_id);
		$this->assignRef('ajax', $ajax);

		if($order_id <= 0)
			return;

		$query = 'SELECT b.*, a.* '.
			' FROM ' . hikamarket::table('shop.order') . ' AS a '.
			' LEFT JOIN ' . hikamarket::table('vendor') . ' AS b ON a.order_vendor_id = b.vendor_id '.
			' WHERE a.order_parent_id = ' . $order_id . ' '.
			' ORDER BY b.vendor_id ASC, a.order_id ASC';
		$db->setQuery($query);
		$data = $db->loadObjectList();

		$query = 'SELECT t.* '.
			' FROM ' . hikamarket::table('order_transaction') . ' AS t '.
			' WHERE t.order_id = ' . $order_id . '';
		$db->setQuery($query);
		$transactions = $db->loadObjectList('order_transaction_id');


		foreach($transactions as $k => $t) {
			$vendor_id = isset($t->vendor_id) ? (int)$t->vendor_id : (int)@$t->order_vendor_id;
			if(empty($vendor_id))
				$vendor_id = 1;

			if(empty($vendor_transactions[$vendor_id]))
				$vendor_transactions[$vendor_id] = array();
			$vendor_transactions[ $vendor_id ][ $k ] = $t;
		}
	}
}

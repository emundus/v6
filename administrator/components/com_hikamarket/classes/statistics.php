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
class hikamarketStatisticsClass extends hikamarketClass {
	protected $tables = array();
	protected $pkeys = array();
	protected $toggle = array();

	public $timeoffset = '';

	public function __construct($config = array()) {
		parent::__construct($config);

		$this->config = hikamarket::config();
		$this->joomlaConfig = JFactory::getConfig();
		$this->shopConfig = hikamarket::config(false);

		if(!HIKASHOP_J30)
			$offset = $this->joomlaConfig->getValue('config.offset');
		else
			$offset = $this->joomlaConfig->get('offset');

		if(!is_numeric($offset)) {
			$tz = new DateTimeZone(JFactory::getUser()->getParam('timezone', $offset));
			$date = JFactory::getDate('now', $tz);
			$offset = $date->getOffsetFromGmt(true);
		}

		$this->timeoffset  = ((int)$offset)*3600;

		$this->timeoffsetStr = ''.$this->timeoffset;
		if($this->timeoffset == 0)
			$this->timeoffsetStr  = '';
		else if($this->timeoffset > 0)
			$this->timeoffsetStr = '+' . $this->timeoffset ;
	}

	public function getVendor($vendor) {
		if(is_int($vendor) || is_string($vendor))
			$vendor_id = (int)$vendor;
		else
			$vendor_id = (int)$vendor->vendor_id;

		$app = JFactory::getApplication();
		$order_type = 'subsale';
		if($vendor_id == 1) {
			$vendor_id = 0;
			$order_type = 'sale';
		}

		$valid_order_statuses = explode(',', $this->config->get('stats_valid_order_statuses', 'confirmed,shipped'));
		$created_status = false;
		foreach($valid_order_statuses as &$status) {
			if(trim($status) == 'created')
				$created_status = true;
			$status = $this->db->Quote(trim($status));
		}
		unset($status);

		$ret = array();

		$ret['products_count'] = array(
			'slot' => 0,
			'container' => 6,
			'order' => 0,
			'published' => 1,
			'type' => 'text',
			'format' => 'number',
			'label' => JText::_('HIKAM_STATS_TOTAL_PRODUCTS'),
			'text' => 'HIKAM_X_PRODUCTS',
			'query' => array(
				'get' => 'single',
				'select' => 'COUNT(*) as value',
				'tables' => hikamarket::table('shop.product') . ' AS product ',
				'filters' => array(
					'product_published' => 'product.product_published = 1',
					'product_type' => 'product.product_type = ' . $this->db->Quote('main'),
					'product_vendor_id' => ('product.product_vendor_id = ' . (int)$vendor_id)
				)
			),
			'vendor_id' => (int)$vendor_id
		);

		$ret['sales_count'] = array(
			'slot' => 0,
			'container' => 6,
			'order' => 1,
			'published' => 1,
			'type' => 'dynamic',
			'format' => 'text',
			'label' => JText::_('HIKAM_STATS_TOTAL_ORDERS'),
			'prefix' => '<table style="width:100%" class="hikamarket_stat_table"><thead><tr><th>'.JText::_('HIKAM_STAT_PERIOD').'</th><th>'.JText::_('HIKAM_STAT_NUMBER').'</th></thead><tbody>',
			'dynamic' => '<tr><td>{name}</td><td>{value}</td></tr>',
			'suffix' => '</tbody></table>',
			'vars' => array(
				array(
					'name' => JText::_('HIKAM_STAT_ALL'),
					'DATE_START' => 0,
					'DATE_END' => -1
				),
				array(
					'name' => JText::_('HIKAM_STAT_YEAR'),
					'DATE_RANGE' => 'this.year'
				),
				array(
					'name' => JText::_('HIKAM_STAT_MONTH'),
					'DATE_RANGE' => 'this.month'
				),
				array(
					'name' => JText::_('HIKAM_STAT_WEEK'),
					'DATE_RANGE' => 'this.week'
				)
			),
			'query' => array(
				'get' => 'object',
				'select' => 'COUNT(*) as value',
				'tables' => hikamarket::table('shop.order') . ' AS hk_order ',
				'filters' => array(
					'order_type' => 'hk_order.order_type = \''.$order_type.'\'',
					'order_vendor_id' => ('hk_order.order_vendor_id = ' . (int)$vendor_id),
					'order_status' => ('hk_order.order_status IN ('.implode(',', $valid_order_statuses).')'),
					'order_created' => ($created_status ?
						'hk_order.order_created >= {DATE_START} AND ({DATE_END} <= 0 OR hk_order.order_created <= {DATE_END})' :
						'hk_order.order_invoice_created >= {DATE_START} AND ({DATE_END} <= 0 OR hk_order.order_invoice_created <= {DATE_END})'),
				)
			),
			'vendor_id' => (int)$vendor_id
		);

		$ret['sales_avg'] = array(
			'slot' => 0,
			'container' => 6,
			'order' => 2,
			'published' => 1,
			'type' => 'list',
			'format' => 'price',
			'label' => JText::_('HIKAM_STATS_AVERAGE_ORDER_PRICE'),
			'vars' => array(
				'DATE_RANGE' => 'all'
			),
			'query' => array(
				'get' => 'list',
				'select' => array(
					(($vendor_id == 0) ? 'AVG(hk_order.order_full_price) as value' : 'AVG( CASE WHEN hk_order.order_vendor_price >= 0 THEN hk_order.order_vendor_price ELSE (hk_order.order_full_price + hk_order.order_vendor_price) END ) as value'),
					'hk_order.order_currency_id as currency'
				),
				'tables' => hikamarket::table('shop.order') . ' AS hk_order ',
				'filters' => array(
					'order_type' => 'hk_order.order_type = \''.$order_type.'\'',
					'order_vendor_id' => ('hk_order.order_vendor_id = ' . (int)$vendor_id),
					'order_status' => ('hk_order.order_status IN ('.implode(',', $valid_order_statuses).')'),
					'order_created' => ($created_status ?
						'hk_order.order_created >= {DATE_START} AND ({DATE_END} <= 0 OR hk_order.order_created <= {DATE_END})':
						'hk_order.order_invoice_created >= {DATE_START} AND ({DATE_END} <= 0 OR hk_order.order_invoice_created <= {DATE_END})'),
				),
				'group' => 'hk_order.order_currency_id'
			),
			'vendor_id' => (int)$vendor_id
		);

		$ret['sales_sum'] = array(
			'slot' => 0,
			'container' => 6,
			'order' => 3,
			'published' => 1,
			'type' => 'list',
			'format' => 'price',
			'label' => JText::_('HIKAM_STATS_ORDERS_THIS_MONTH'),
			'vars' => array(
				'DATE_RANGE' => 'this.month'
			),
			'query' => array(
				'get' => 'list',
				'select' => array(
					(($vendor_id == 0) ? 'SUM(hk_order.order_full_price) as value' : 'SUM( CASE WHEN hk_order.order_vendor_price >= 0 THEN hk_order.order_vendor_price ELSE (hk_order.order_full_price + hk_order.order_vendor_price) END ) as value'),
					'hk_order.order_currency_id as currency'
				),
				'tables' => hikamarket::table('shop.order') . ' AS hk_order ',
				'filters' => array(
					'order_type' => 'hk_order.order_type = \''.$order_type.'\'',
					'order_vendor_id' => ('hk_order.order_vendor_id = ' . (int)$vendor_id),
					'order_status' => ('hk_order.order_status IN ('.implode(',', $valid_order_statuses).')'),
					'order_created' => ($created_status ?
						'hk_order.order_created >= {DATE_START} AND ({DATE_END} <= 0 OR hk_order.order_created <= {DATE_END})':
						'hk_order.order_invoice_created >= {DATE_START} AND ({DATE_END} <= 0 OR hk_order.order_invoice_created <= {DATE_END})'),
				),
				'group' => 'hk_order.order_currency_id'
			),
			'vendor_id' => (int)$vendor_id
		);
		$ret['orders_total_unpaid'] = array(
			'slot' => 0,
			'container' => 6,
			'order' => 4,
			'published' => 1,
			'type' => 'dynamic',
			'dynamic' => '{value:price} ({count})',
			'format' => 'text',
			'label' => JText::_('ORDERS_UNPAID'),
			'vars' => array(
				'DATE_END' => -1,
			),
			'query' => array(
				'get' => 'list',
				'select' => array(
					'value' => 'SUM(hk_transaction.order_transaction_price) as value',
					'count' => 'COUNT(hk_transaction.order_transaction_paid) as count',
					'currency' => 'hk_transaction.order_transaction_currency_id as currency'
				),
				'tables' => hikamarket::table('order_transaction') . ' AS hk_transaction ',
				'filters' => array(
					'transaction_vendor_id' => (($vendor_id == 0) ? 'hk_transaction.vendor_id > 1' : ('hk_transaction.vendor_id = ' . (int)$vendor_id)),
					'transaction_paid' => 'hk_transaction.order_transaction_paid = 0',
					'transaction_price' => 'hk_transaction.order_transaction_price != 0.0',
					'transaction_valid' => 'hk_transaction.order_transaction_valid > 0',
					'transaction_created' => '({DATE_END} <= 0 OR hk_transaction.order_transaction_created <= {DATE_END})',
				),
				'group' => 'hk_transaction.order_transaction_currency_id'
			),
			'vendor_id' => (int)$vendor_id
		);

		if(false && (int)$this->config->get('days_payment_request', 0) > 0) {
			$now = explode('-', date('m-d-Y'), 3);
			$days = (int)$this->config->get('days_payment_request', 0) - 1;
			$ret['orders_total_unpaid']['vars']['DATE_END'] = mktime(0, 0, 0, $now[0], $now[1], $now[2]) - ($days * 24 * 3600) + $this->timeoffset;
		}

		$ret['last_orders'] = array(
			'slot' => 0,
			'container' => 6,
			'order' => 5,
			'published' => 1,
			'type' => 'dynamic',
			'label' => JText::_('HIKAM_STATS_LAST_ORDERS'),
			'vars' => array(
				'COUNTER' => 5
			),
			'prefix' => '<table style="width:100%" class="hikamarket_stat_table"><thead><tr><th>'.JText::_('ORDER_NUMBER').'</th><th>'.JText::_('ORDER_STATUS').'</th><th>'.JText::_('PRICE').'</th></thead><tbody>',
			'dynamic' => '<tr><td>{order_number:order_link}</td><td>{order_status:order_status}</td><td>{order_full_price:price}</td></tr>',
			'suffix' => '</tbody></table>',
			'format' => 'text',
			'query' => array(
				'get' => 'list',
				'select' => 'hk_order.*, hk_user.*, hk_order.order_currency_id as currency',
				'tables' => array(
					hikamarket::table('shop.order') . ' AS hk_order',
					'INNER JOIN '.hikamarket::table('shop.user').' AS hk_user ON hk_order.order_user_id = hk_user.user_id'
				),
				'filters' => array(
					'order_type' => 'hk_order.order_type = \''.$order_type.'\'',
					'order_vendor_id' => ('hk_order.order_vendor_id = ' . (int)$vendor_id),
				),
				'order' => 'hk_order.order_created DESC',
				'limit' => '{COUNTER}'
			),
			'vendor_id' => (int)$vendor_id
		);

		$ret['orders_history'] = array(
			'slot' => 1,
			'container' => 12,
			'order' => 7,
			'published' => 1,
			'type' => 'graph',
			'label' => JText::_('ORDERS'),
			'graph' => array(
				'cols' => 'currency',
				'axis' => 'date',
			),
			'vars' => array(
				'DATE_RANGE' => 'this.year',
				'DATE_GROUP' => 'day',
				'DATE_START' => 0,
				'DATE_END' => -1,
			),
			'query' => array(
				'get' => 'list',
				'select' => array(
					'value' => (($vendor_id == 0) ? 'SUM(hk_order.order_full_price) as value' : 'SUM( CASE WHEN hk_order.order_vendor_price >= 0 THEN hk_order.order_vendor_price ELSE (hk_order.order_full_price + hk_order.order_vendor_price) END ) as value'),
					'axis' => ($created_status ?
						'DATE_FORMAT(FROM_UNIXTIME(CAST(hk_order.order_created AS SIGNED)'.$this->timeoffsetStr.'),\'{DATE_FORMAT}\') as axis':
						'DATE_FORMAT(FROM_UNIXTIME(CAST(hk_order.order_invoice_created AS SIGNED)'.$this->timeoffsetStr.'),\'{DATE_FORMAT}\') as axis'),
					'currency' => 'currencies.currency_code as currency'
				),
				'tables' => array(
					hikamarket::table('shop.order') . ' AS hk_order',
					'INNER JOIN '.hikamarket::table('shop.currency').' AS currencies ON hk_order.order_currency_id = currencies.currency_id'
				),
				'filters' => array(
					'order_type' => 'hk_order.order_type = \''.$order_type.'\'',
					'order_vendor_id' => ('hk_order.order_vendor_id = ' . (int)$vendor_id),
					'order_status' => ('hk_order.order_status IN ('.implode(',', $valid_order_statuses).')'),
					'order_created' => ($created_status ?
						'hk_order.order_created >= {DATE_START} AND ({DATE_END} <= 0 OR hk_order.order_created <= {DATE_END})':
						'hk_order.order_invoice_id > 0 AND hk_order.order_invoice_created > 0 AND hk_order.order_invoice_created >= {DATE_START} AND ({DATE_END} <= 0 OR hk_order.order_invoice_created <= {DATE_END})'),
				),
				'group' => 'axis, hk_order.order_currency_id',
				'order' => 'axis ASC'
			),
			'vendor_id' => (int)$vendor_id
		);

		$ret['product_compare'] = array(
			'slot' => 1,
			'container' => 12,
			'order' => 8,
			'published' => 1,
			'type' => 'pie',
			'label' => JText::_('HIKAM_STATS_PRODUCT_COMPARE'),
			'pie' => array(
				'key' => 'order_product_name',
				'text' => 'value',
				'donut' => true,
				'3d' => false,
				'legend' => true
			),
			'vars' => array(
				'DATE_RANGE' => 'this.month',
				'LIMIT' => 15
			),
			'query' => array(
				'get' => 'list',
				'select' => array(
					'key' => 'hk_product.product_id as product_id',
					'name' => 'hk_product.order_product_name',
					'value' => 'ROUND(SUM(hk_product.order_product_price * hk_product.order_product_quantity),2) AS value',
					'counter' => 'COUNT(hk_order.order_id) AS orders_count'
				),
				'tables' => array(
					hikamarket::table('shop.order_product') . ' AS hk_product',
					'INNER JOIN '. hikamarket::table('shop.order') . ' AS hk_order ON hk_order.order_id = hk_product.order_id'
				),
				'filters' => array(
					'order_type' => 'hk_order.order_type = \''.$order_type.'\'',
					'order_vendor_id' => ('hk_order.order_vendor_id = ' . (int)$vendor_id),
					'order_status' => ('hk_order.order_status IN ('.implode(',', $valid_order_statuses).')'),
					'order_created' => ($created_status ?
						'hk_order.order_created >= {DATE_START} AND ({DATE_END} <= 0 OR hk_order.order_created <= {DATE_END})':
						'hk_order.order_invoice_created >= {DATE_START} AND ({DATE_END} <= 0 OR hk_order.order_invoice_created <= {DATE_END})'),
				),
				'group' => 'hk_product.product_id',
				'order' => 'value DESC',
				'limit' => '{LIMIT}'
			),
			'vendor_id' => (int)$vendor_id
		);

		$ret['geo_sales'] = array(
			'slot' => 1,
			'container' => 12,
			'order' => 9,
			'published' => 0,
			'type' => 'geo',
			'label' => JText::_('HIKAM_STATS_GEO_SALES'),
			'geo' => array(
				'key' => 'zone',
				'text' => 'value',
			),
			'vars' => array(
				'DATE_RANGE' => 'this.month',
			),
			'query' => array(
				'get' => 'list',
				'select' => array(
					'COUNT(hk_order.order_id) AS value',
					'hk_zone.zone_name_english AS zone'
				),
				'tables' => array(
					hikamarket::table('shop.order') . ' AS hk_order ',
					' INNER JOIN ' . hikamarket::table('shop.address') . ' AS hk_address ON hk_order.order_shipping_address_id = hk_address.address_id',
					' INNER JOIN ' . hikamarket::table('shop.zone') . ' AS hk_zone ON hk_zone.zone_namekey = hk_address.address_country',
				),
				'filters' => array(
					'order_type' => 'hk_order.order_type = \''.$order_type.'\'',
					'order_vendor_id' => ('hk_order.order_vendor_id = ' . (int)$vendor_id),
					'order_status' => ('hk_order.order_status IN ('.implode(',', $valid_order_statuses).')'),
					'order_created' => ($created_status ?
						'hk_order.order_created >= {DATE_START} AND ({DATE_END} <= 0 OR hk_order.order_created <= {DATE_END})':
						'hk_order.order_invoice_created >= {DATE_START} AND ({DATE_END} <= 0 OR hk_order.order_invoice_created <= {DATE_END})'),
				),
				'group' => 'hk_address.address_country'
			),
			'vendor_id' => (int)$vendor_id
		);

		JPluginHelper::importPlugin('hikamarket');
		$extra_list = $app->triggerEvent('onHikamarketStatisticPluginList', array(
			$vendor_id,
			array(
				'created' => $created_status,
				'valid' => $valid_order_statuses,
				'offset' => $this->timeoffsetStr
			)
		));
		if(!empty($extra_list)) {
			foreach($extra_list as $v) {
				if(!isset($v['name']))
					continue;

				if(!isset($v['vendor_id']))
					$v['vendor_id'] = (int)$vendor_id;
				$k = 'plugin.'.$v['name'];
				$ret[$k] = $v;
			}
		}

		return $ret;
	}

	public function getDashboard() {
		$ret = array();
		$ret['vendor_count'] = array(
			'slot' => 0,
			'order' => 1,
			'published' => 1,
			'class' => 'hkc-lg-4 hkc-md-6',
			'label' => JText::_('HIKA_STATS_TOTAL_VENDOR'),
			'format' => 'number',
			'type' => 'tile',
			'tile' => array(
				'icon' => array('type' => 'fa', 'value' => 'industry'),
				'view' => hikamarket::completeLink('vendor&task=listing'),
			),
			'query' => array(
				'get' => 'single',
				'select' => 'COUNT(*) as value',
				'tables' => hikamarket::table('vendor') . ' AS hk_vendor ',
				'filters' => array(
					'vendor_published' => 'hk_vendor.vendor_published = 1'
				)
			)
		);

		$ret['orders_total_unpaid'] = array(
			'slot' => 0,
			'order' => 2,
			'published' => 1,
			'class' => 'hkc-lg-4 hkc-md-6',
			'label' => JText::_('ORDERS_UNPAID'),
			'type' => 'tile',
			'format' => '{value:price} ({count})',
			'vars' => array(
				'DATE_END' => -1,
			),
			'tile' => array(
				'icon' => array('type' => 'fa', 'value' => 'money fa-money-bill-alt'),
				'view' => hikamarket::completeLink('vendor&task=listing'),
			),
			'query' => array(
				'get' => 'list',
				'select' => array(
					'value' => 'SUM(hk_transaction.order_transaction_price) as value',
					'count' => 'COUNT(hk_transaction.order_transaction_paid) as count',
					'currency' => 'hk_transaction.order_transaction_currency_id as currency'
				),
				'tables' => hikamarket::table('order_transaction') . ' AS hk_transaction ',
				'filters' => array(
					'transaction_vendor_id' => 'hk_transaction.vendor_id > 1',
					'transaction_paid' => 'hk_transaction.order_transaction_paid = 0',
					'transaction_price' => 'hk_transaction.order_transaction_price != 0.0',
					'transaction_valid' => 'hk_transaction.order_transaction_valid > 0',
					'transaction_created' => '({DATE_END} <= 0 OR hk_transaction.order_transaction_created <= {DATE_END})',
				),
				'group' => 'hk_transaction.order_transaction_currency_id'
			)
		);

		$config = hikamarket::config();
		$use_approval = (int)$config->get('product_approval', 0);

		$ret['waiting_approval'] = array(
			'slot' => 0,
			'order' => 3,
			'published' => $use_approval,
			'class' => 'hkc-lg-4 hkc-md-6',
			'label' => JText::_('WAITING_APPROVAL_LIST'),
			'type' => 'tile',
			'format' => 'number',
			'tile' => array(
				'icon' => array('type' => 'fa', 'value' => 'hourglass'),
				'view' => hikamarket::completeLink('product&task=waitingapproval'),
			),
			'query' => array(
				'get' => 'single',
				'select' => 'COUNT(*) as value',
				'tables' => hikamarket::table('shop.product').' AS hk_product ',
				'filters' => array(
					'product_type' => 'hk_product.product_type = '.$this->db->Quote('waiting_approval')
				)
			)
		);

		return $ret;
	}

	public function getAjaxData($vendor_id, $name, $value) {
		$statistics = $this->getVendor($vendor_id);
		$buttons = array();
		$app = JFactory::getApplication();

		JPluginHelper::importPlugin('hikamarket');
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashoppayment');
		$app->triggerEvent('onVendorPanelDisplay', array(&$buttons, &$statistics));

		if(empty($statistics[$name]))
			return false;

		if(empty($statistics[$name]['type']) || !in_array($statistics[$name]['type'], array('graph', 'pie', 'geo')))
			return false;

		if($statistics[$name]['type'] == 'graph')
			$dateRange = $this->getDateRangeList(true);
		else
			$dateRange = $this->getDateRangeList();
		if(!isset($dateRange[$value]))
			return false;

		$vendor_statistics = $this->config->get('vendor_statistics', null);
		if(!empty($vendor_statistics)) {
			foreach($statistics as $key => &$stat) {
				$stat['published'] = false;
			}
			unset($stat);

			$vendor_statistics = hikamarket::unserialize(base64_decode($vendor_statistics));
			foreach($vendor_statistics as $key => $stat_conf) {
				if(!isset($statistics[$key]))
					continue;

				if(isset($stat_conf['slot']))
					$statistics[$key]['slot'] = (int)$stat_conf['slot'];
				if(isset($stat_conf['container']))
					$statistics[$key]['container'] = (int)$stat_conf['container'];
				if(isset($stat_conf['order']))
					$statistics[$key]['order'] = (int)$stat_conf['order'];
				if(isset($stat_conf['published']))
					$statistics[$key]['published'] = $stat_conf['published'];
				if(!empty($stat_conf['vars'])) {
					foreach($stat_conf['vars'] as $k => $v)
						$statistics[$key]['vars'][$k] = $v;
				}
			}
		}

		if(empty($statistics[$name]) || empty($statistics[$name]['published']))
			return false;

		$stat = $statistics[$name];
		unset($statistics);

		$stat['vars']['DATE_RANGE'] = $value;

		switch($stat['type']) {
			case 'graph':
				list($headerData, $chartData) = $this->processGraphData($stat);
				break;
			case 'pie':
				list($headerData, $chartData) = $this->processPieData($stat);
				break;
			case 'geo':
				list($headerData, $chartData) = $this->processGeoData($stat);
				break;
		}

		if($stat['type'] == 'graph' && (count($headerData) == 1 || empty($chartData)))
			return '[[],[]]';

		return '['.
			'["' . implode('","', $headerData). '"]'.
			(empty($chartData) ? '' : ',[') .
			implode('],[', $chartData).
			(empty($chartData) ? '' : ']') .
		']';
	}

	public function setDateRange(&$conf, $mode, $base = 0) {
		$dates = $this->getDates($mode, $base);
		if(isset($conf['vars'])) {
			$conf['vars']['DATE_START'] = $dates['start'];
			$conf['vars']['DATE_END'] = $dates['end'];
			if(isset($dates['group']))
				$conf['vars']['DATE_GROUP'] = $dates['group'];
		} else {
			$conf['DATE_START'] = $dates['start'];
			$conf['DATE_END'] = $dates['end'];
			if(isset($dates['group']))
				$conf['DATE_GROUP'] = $dates['group'];
		}
	}

	public function getDates($mode, $base = 0) {
		$now = explode('-', hikamarket::getDate(time(), 'm-d-Y'), 3);
		$ret = array('start' => 0, 'end' => -1);

		if(strpos($mode, ':') !== false) {
			list($mode, $group) = explode(':', $mode, 2);
			if(in_array($group, array('day', 'week', 'month', 'year')))
				$ret['group'] = $group;
		}

		$manualProcess = false;
		switch($mode) {
			case '':
			case 'all':
				$ret['start'] = 0;
				$ret['end'] = -1;
				break;
			case 'this.year':
				$ret['start'] = gmmktime(0, 0, 0, 1, 1, $now[2]) - $this->timeoffset;
				$ret['end'] = gmmktime(23, 59, 59, $now[0], $now[1], $now[2]) - $this->timeoffset;
				break;
			case 'this.month':
				$ret['start'] = gmmktime(0, 0, 0, $now[0], 1, $now[2]) - $this->timeoffset;
				$ret['end'] = gmmktime(23, 59, 59, $now[0], $now[1], $now[2]) - $this->timeoffset;
				break;
			case 'this.week':
				$ret['start'] = gmmktime(0, 0, 0, $now[0], $now[1], $now[2]) - (date('N')-1)*24*3600 - $this->timeoffset;
				$ret['end'] = gmmktime(23, 59, 59, $now[0], $now[1], $now[2]) - $this->timeoffset;
				break;
			case 'this.day':
				$ret['start'] = gmmktime(0, 0, 0, $now[0], $now[1], $now[2]) - $this->timeoffset;
				$ret['end'] = gmmktime(23, 59, 59, $now[0], $now[1], $now[2]) - $this->timeoffset;
				break;
			case 'past.year':
				$ret['end'] = gmmktime(23, 59, 59, $now[0], $now[1], $now[2]) - $this->timeoffset;
				$ret['start'] = strtotime('-1 year', $ret['end'])+1;
				break;
			case 'past.month':
				$ret['end'] = gmmktime(23, 59, 59, $now[0], $now[1], $now[2]) - $this->timeoffset;
				$ret['start'] = strtotime('-1 month', $ret['end'])+1;
				break;
			case 'past.month-1':
				$ret['end'] = gmmktime(23, 59, 59, $now[0], $now[1], $now[2]) - $this->timeoffset;
				$ret['end'] = strtotime('-1 month', $ret['end'])+1;
				$ret['start'] = strtotime('-1 month', $ret['end'])+1;
				break;
			case 'past.week':
				$ret['end'] = gmmktime(23, 59, 59, $now[0], $now[1], $now[2]) - $this->timeoffset;
				$ret['start'] = strtotime('-1 week', $ret['end'])+1;
				break;
			case 'past.day':
				$ret['end'] = time() - $this->timeoffset;
				$ret['start'] = $ret['end'] - 24*3600;
				break;
			case 'previous.year':
				$ret['start'] = gmmktime(0, 0, 0, 1, 1, (int)$now[2]-1) - $this->timeoffset;
				$ret['end'] = gmmktime(23, 59, 59, 12, 31, (int)$now[2]-1) - $this->timeoffset;
				break;
			case 'previous.month':
				$ret['end'] = gmmktime(0, 0, 0, $now[0], 1, $now[2]) - 1 - $this->timeoffset;
				$ret['start'] = strtotime('-1 month', $ret['end']) + 1;
				break;
			case 'previous.week':
				$ret['end'] = gmmktime(0, 0, 0, $now[0], $now[1], $now[2]) - (date('N')-1)*24*3600 - 1 - $this->timeoffset;
				$ret['start'] = strtotime('-1 week', $ret['end']) + 1;
				break;
			case 'previous.day':
				$ret['end'] = gmmktime(0, 0, 0, $now[0], $now[1], $now[2]) - $this->timeoffset;
				$ret['start'] = $ret['end'] - 24*3600;
				break;
			default:
				$manualProcess = true;
				break;
		}
		if($manualProcess) {

		}
		return $ret;
	}

	public function getDateRangeList($group = false) {
		$periods = array('this','past','previous');
		$zones = array('year','month','week','day');

		$ret = array(
			'all' => 'HIKAM_PERIOD_ALL'
		);
		if($group === true) {
			$ret = array();
			foreach($zones as $z) {
				$ret['all:'.$z] = JText::_('HIKAM_PERIOD_ALL').' ('.JText::_('HIKAM_PERIOD_BY_'.strtoupper($z)).')';
			}
		}

		foreach($periods as $period) {
			foreach($zones as $zone) {
				$ret[$period.'.'.$zone] = 'HIKAM_PERIOD_'.strtoupper($period).'_'.strtoupper($zone);

				if($group != true)
					continue;
				$m = $zone;
				foreach($zones as $z) {
					if($z === $m) {
						$m = true;
						continue;
					}
					if($m !== true)
						continue;
					$ret[$period.'.'.$zone.':'.$z] = JText::_('HIKAM_PERIOD_'.strtoupper($period).'_'.strtoupper($zone)).' ('.JText::_('HIKAM_PERIOD_BY_'.strtoupper($z)).')';
				}
			}
		}
		return $ret;
	}

	protected function processQuery($queryData, $vars = array(), $limit = null) {
		if(empty($queryData['get']))
			$queryData['get'] = 'single';

		$select = $queryData['select'];
		if(is_array($select))
			$select = implode(', ', $select);

		$tables = $queryData['tables'];
		if(is_array($tables))
			$tables = implode(' ', $tables);

		$query = 'SELECT ' . $select . ' FROM ' . $tables;

		if(!empty($queryData['filters'])) {
			$query .= ' WHERE (' . implode(') AND (', $queryData['filters']) . ') ';
		}
		if(!empty($queryData['group']))
			$query .= ' GROUP BY ' . (is_array($queryData['group']) ? implode(',', $queryData['group']) : $queryData['group']);
		if(!empty($queryData['order']))
			$query .= ' ORDER BY ' . (is_array($queryData['order']) ? implode(',', $queryData['order']) : $queryData['order']);

		if(!empty($vars)) {
			if(isset($vars['DATE_RANGE'])) {
				$this->setDateRange($vars, $vars['DATE_RANGE']);
				$vars['_DATE_RANGE'] = $vars['DATE_RANGE'];
				unset($vars['DATE_RANGE']);
			}
			$keys = array_keys($vars);
			foreach($keys as &$key) { $key = '{'.$key.'}'; } unset($key);
			$values = array_values($vars);

			$query = str_replace($keys, $values, $query);
			if(isset($queryData['offset']))
				$queryData['offset'] = str_replace($keys, $values, $queryData['offset']);
			if(isset($queryData['limit']))
				$queryData['limit'] = str_replace($keys, $values, $queryData['limit']);

			unset($keys);
			unset($values);

			if(!isset($vars['DATE_FORMAT']) && isset($vars['DATE_GROUP'])) {
				$dateformat_key = '{DATE_FORMAT}';
				switch($vars['DATE_GROUP']) {
					case 'year':
						$dateformat_value = '%Y';
						break;
					case 'month':
						$dateformat_value = '%Y-%m';
						break;
					case 'week':
						$dateformat_value = '%Y %u';
						break;
					case 'day':
					default:
						$dateformat_value = '%Y-%m-%d';
						break;
				}
				$query = str_replace($dateformat_key, $dateformat_value, $query);
			}
		}

		$offset = 0;
		if(!empty($queryData['offset']))
			$offset = (int)$queryData['offset'];
		if(!empty($queryData['limit']) && $limit === null)
			$limit = (int)$queryData['limit'];
		if($limit === null)
			$limit = -1;
		$this->db->setQuery('SET @@session.time_zone = \'+00:00\'');
		$this->db->execute();
		$this->db->setQuery($query, $offset, $limit);
		switch($queryData['get']) {
			case 'object':
				$ret = $this->db->loadObject();
				break;
			case 'list':
				$ret = $this->db->loadObjectList();
				break;
			case 'single':
			default:
				$ret = $this->db->loadResult();
				break;
		}
		return $ret;
	}

	protected function initJS($vendor_id = null) {
		static $init = false;
		if($init) return;

		$app = JFactory::getApplication();
		$url = hikamarket::completeLink('vendor&task=reports&tmpl=ajax', false, false, true);
		if(hikamarket::isAdmin()) {
			if(empty($vendor_id) && $vendor_id === 0)
				$vendor_id = 1;
			if((int)$vendor_id > 0)
				$url = hikamarket::completeLink('vendor&task=reports&cid='.(int)$vendor_id.'&tmpl=ajax', false, false, true);
		}

		$dateRanges = array(
			'data' => array( 'all' => 0, 'year' => 1, 'month' => 2, 'week' => 3, 'day' => 4 ),
			'text' => array( 'year' => JText::_('HIKAM_PERIOD_BY_YEAR'), 'month' => JText::_('HIKAM_PERIOD_BY_MONTH'), 'week' => JText::_('HIKAM_PERIOD_BY_WEEK'), 'day' => JText::_('HIKAM_PERIOD_BY_DAY') )
		);

		$doc = JFactory::getDocument();
		$doc->addScript('https://www.google.com/jsapi');
		$doc->addScriptDeclaration('
if(!window.localPage)
	window.localPage = {};
window.localPage.chartsInit = [];
window.localPage.charts = {};
window.localPage.dateRanges = '.json_encode($dateRanges).';
window.localPage.changeChartData = function(type,id,name,value,el) {
	var chart = window.localPage.charts[id];
	if(!chart) return false;
	var dataValue = value;
	if(type == "graph") {
		if(value.substring(0,1) != ":")
			dataValue = window.localPage.getRangeValue(value);
		else
			dataValue = chart.period + value;
	}
	var url = "'.$url.'",
		postdata = "chart="+encodeURIComponent(name)+"&value="+encodeURIComponent(dataValue);
	window.Oby.xRequest(url, {mode:"POST",data:postdata}, function(x,p) {
		var d = window.Oby.evalJSON(x.responseText) || false;
		if(!d || typeof d === "object") return;

		chart.data = google.visualization.arrayToDataTable(d);
		chart.max = (d.length - 1);
		if(type == "graph") {
			chart.options.hAxis.viewWindow.min = 0;
			chart.options.hAxis.viewWindow.max = chart.max;
			chart.period = dataValue.substring(0, dataValue.indexOf(":"));
			chart.periodGroup = value.substring(value.indexOf(":") + 1);
		}
		chart.chart.draw(chart.data, chart.options);
		window.localPage.changeChartDropText(id, value, dataValue, el);
	});
	return false;
};
window.localPage.changeChartDropText = function(id,value,dataValue,el) {
	if(!el) return;
	if(value.substring(0,1) != ":") {
		var s = document.getElementById("hikamarket_chart_"+id+"_range");
		if(!s) return;
		s.innerHTML = el.innerHTML;

		var p = dataValue.indexOf(":");
		if(p >= 0)
			value = dataValue.substring(p);
	}
	if(value.substring(0,1) == ":") {
		var s = document.getElementById("hikamarket_chart_"+id+"_group");
		if(!s) return;
		s.innerHTML = window.localPage.dateRanges.text[ value.substring(1) ];
	}
};
window.localPage.getRangeValue = function(value) {
	var r = window.localPage.dateRanges, ret = value;
	if(value.indexOf(".") >= 0)
		value = value.substring(value.indexOf(".")+1);
	if(r.data[value] === undefined)
		return ret;
	var v = (r.data[value] + 1);
	for(var k in r.data) {
		if(r.data.hasOwnProperty(k) && r.data[k] === v)
			return ret + ":" + k;
	}
	return ret;
};
window.localPage.refreshGraphs = function() {
	for(var i in window.localPage.charts) {
		if(!window.localPage.charts.hasOwnProperty(i))
			continue;
		var chart = window.localPage.charts[i];
		chart.chart.draw(chart.data, chart.options);
	}
};
google.load("visualization", "49", {packages:["corechart","geochart"]});
google.setOnLoadCallback(function(){
	for(var i = 0; i < window.localPage.chartsInit.length; i++) {
		var f = window.localPage.chartsInit[i];
		f();
	}
});
jQuery(window).on("resize", function(){
	if(window.localPage.resizeTimer)
		clearTimeout(window.localPage.resizeTimer);
	window.localPage.resizeTimer = setTimeout(window.localPage.refreshGraphs, 250);
});
');
		$init = true;
	}

	public function display($data, $doText = true) {
		if(empty($data['type']))
			$data['type'] = 'text';

		$ret = '';

		if(!empty($data['query']) && (!isset($data['value']) || $data['value'] === null)) {
			if(!empty($data['vars'])) {
				$vars = reset($data['vars']);
				if(is_array($vars)) {
					$vars = $data['vars'];
					$ret = array();
					foreach($vars as $v) {
						$data['vars'] = $v;
						$data['value'] = null;
						$ret[] = $this->display($data, false);
					}
					$data['type'] = 'aggreg';
					unset($data['value']);
					$data['value'] = $ret;
				} else
					$data['value'] = $this->processQuery($data['query'], $data['vars']);
			} else
				$data['value'] = $this->processQuery($data['query']);
		}

		$ret = '';

		switch($data['type']) {
			case 'dynamic':
				$ret = $this->displayDynamic($data);
				break;
			case 'tile':
				$ret = $this->displayTile($data);
				break;
			case 'graph':
				$ret = $this->displayGraph($data);
				break;
			case 'pie':
				$ret = $this->displayPie($data);
				break;
			case 'geo':
				$ret = $this->displayGeo($data);
				break;
			case 'list':
				$ret = $this->displayList($data);
				break;
			case 'raw':
				$ret = $data['value'];
				break;
			case 'aggreg':
				$sep = '';
				if(!empty($data['separator']))
					$sep = $data['separator'];
				$ret = implode($sep, $data['value']);
				break;
			case 'plugin':
				JPluginHelper::importPlugin('hikamarket');
				$app = JFactory::getApplication();
				$ret = $app->triggerEvent('onHikamarketStatisticPluginDisplay', array($data));
				if(!empty($ret) && is_array($ret)) {
					$arr = $ret;
					$ret = reset($ret);
					if(empty($ret)) {
						while(empty($ret) && !empty($arr)) {
							$ret = array_shift($arr);
						}
					}
					unset($arr);
				}
				break;
			case 'text':
			default:
				$ret = $this->getFormatedValue($data['value'], $data['format']);
				break;
		}

		if($doText) {
			if(!empty($data['prefix']))
				$ret = $data['prefix'] . $ret;
			if(!empty($data['text'])) {
				$tmp = JText::_($data['text']);
				if($tmp != $data['text'])
					$ret = JText::sprintf($data['text'], $ret);
			}
			if(!empty($data['suffix']))
				$ret = $ret . $data['suffix'];
		}
		return $ret;
	}

	protected function displayList($data) {
		if(is_object($data['value']))
			$data['value'] = array($data['value']);

		$ret = array();
		foreach($data['value'] as $value) {
			$ret[] = $this->getFormatedValue($value, $data['format']);
		}
		if(count($ret) > 1)
			return '<ul><li>'. implode('</li><li>', $ret) . '</li></ul>';
		return reset($ret);
	}

	protected function displayDynamic($data) {
		if(empty($data['value']))
			return '';

		if(is_object($data['value']))
			$data['value'] = array($data['value']);

		$ret = array();
		foreach($data['value'] as $value) {
			$content = $data['dynamic'];
			if(preg_match_all('#{([-:. _A-Z0-9a-z]+)}#U', $content, $out)) {
				foreach($out[1] as $key) {
					if(strpos($key, ':') !== false) {
						list($col, $format) = explode(':', $key, 2);
					} else {
						$col = $key;
						$format = $data['format'];
					}
					if(isset($value->$col))
						$content = str_replace('{'.$key.'}', $this->getFormatedValue($value->$col, $format, $value), $content);
					else {
						$v = '';
						if(isset($data['vars'][$key]))
							$v = $data['vars'][$key];
						$content = str_replace('{'.$key.'}', $v, $content);
					}
				}
			}
			$ret[] = $content;
		}
		if(count($ret) > 1) {
			if(!empty($data['prefix']) && !empty($data['suffix']))
				return implode("\r\n", $ret);
			return '<ul><li>'. implode('</li><li>', $ret) . '</li></ul>';
		}
		return reset($ret);
	}

	protected function displayTile($data) {
		if(!is_array($data['value']))
			$data['value'] = array($data['value']);

		if(isset($data['vars-2'])) {
			$data['value'][] = $this->processQuery($data['query'], $data['vars-2']);
		}

		if(isset($data['format']) && $data['format'] == 'percentage' && count($data['value']) == 2) {
			$a = hikamarket::toFloat($data['value'][0]);
			$b = hikamarket::toFloat($data['value'][1]);
			$data['value'] = array(0);
			if($b > 0)
				$data['value'] = array( round($a / $b * 100) );
		}

		$d = array();
		foreach($data['value'] as $value) {
			$d[] = $this->getFormatedValue($value, @$data['format']);
		}

		$value = $this->getMergedValue($d, @$data['format']);

		if(!empty($data['tile']['mode']) && $data['tile']['mode'] == 'small') {
			$background = '';
			if(isset($value->image)) {
				$imageHelper = hikamarket::get('shop.helper.image');
				$img = $imageHelper->getThumbnail($value->image, array(150,150), array('default' => true), true);
				$background = '<img src="'.$img->url.'" alt=""/>';
			} elseif(isset($data['tile']['background']) && $data['tile']['background']['value'] == 'fa') {
				$background = '<i class="fa fa-'.$data['tile']['background']['value'].'"></i>';
			}

			if(empty($value->name) && isset($data['tile']['name'])) {
				foreach($data['tile']['name'] as $k) {
					if(isset($value->$k) && !empty($value->$k))
						$value->name = $value->$k;
				}
			}
			if(!is_object($value)) {
				$tmp = $value;
				$value = new stdClass;
				$value->name = $tmp;
				unset($tmp);
			}

			if(!empty($data['tile']['translate']))
				$value->name = hikashop_translate($value->name);

			return '
<div class="cpanel-smalltile-block">
	<div class="cpanel-tile-background">'.$background.'</div>
	<div class="cpanel-tile-header">'.$data['label'].'</div>
	<div class="cpanel-tile-name">'.$value->name.'</div>
	<div class="cpanel-tile-details"></div>
</div>
';
		}

		$icon = '';
		$compare = '';
		$footer = '';
		$extra_class = '';
		if(!empty($data['tile']['icon']['type']) && $data['tile']['icon']['type'] == 'fa') {
			hikashop_loadJsLib('font-awesome');
			$icon = '<i class="fa fa-'.$data['tile']['icon']['value'].'"></i>';
		}

		if(!empty($data['tile']['view'])) {
			$t = JText::_('HIKA_VIEW_MORE');
			$l = '#';
			if(is_string($data['tile']['view'])) {
				$l = $data['tile']['view'];
			} elseif(is_array($data['tile']['view'])) {
				if(isset($data['tile']['view']['title']))
					$t = $data['tile']['view']['title'];
				if(isset($data['tile']['view']['link']))
					$l = $data['tile']['view']['link'];
				if(!empty($data['tile']['view']['process']) && !empty($data['tile']['view']['value'])) {
					$l = $data['tile']['view']['value'];
					foreach($value as $k => $v) {
						$l = str_replace('{'.strtoupper($k).'}', $v, $l);
					}
					$l = hikamarket::completeLink($l);
				}
			}
			$footer = '<a href="'.$l.'">'.$t.'</a>';
		}

		$text = $value;
		if(is_object($value)) {
			$text = '';
			if(empty($data['tile']['name']))
				$data['tile']['name'] = array('name');
			if(!is_array($data['tile']['name']))
				$data['tile']['name'] = array($data['tile']['name']);
			foreach($data['tile']['name'] as $k) {
				if(isset($value->$k) && !empty($value->$k))
					$text = $value->$k;
			}
		}

		if(!empty($data['tile']['class']))
			$extra_class = ' '.trim($data['tile']['class']);

		if(empty($footer))
			$footer = '<div class="cpanel-tile-footer-empty"></div>';
		else
			$footer = '<div class="cpanel-tile-footer">'.$footer.'</div>';

		return '
<div class="cpanel-tile-block'.$extra_class.'">
	<div class="cpanel-tile-header">
		'.$data['label'].'
		<span class="cpanel-right">'.$compare.'</span>
	</div>
	<div class="cpanel-tile-body">
		'.$icon.'
		<h2 class="cpanel-right">'.$text.'</h2>
	</div>
	'.$footer.'
	<div style="clear:both"></div>
</div>';
	}

	protected function displayGraph($data) {
		$this->initJS(@$data['vendor_id']);
		$id = uniqid();

		list($headerData, $chartData) = $this->processGraphData($data);


		$viewport = ',hAxis:{viewWindow:{min:0,max:'.count($chartData).'}}';
		$theme =  ',chartArea:{width:"90%"}';

		$ranges = $this->getDateRangeList();

		$currentRange = isset($data['vars']['DATE_RANGE']) ? $data['vars']['DATE_RANGE'] : @$data['vars']['_DATE_RANGE'];
		$currentGroup = 'day';
		if(strpos($currentRange, ':') !== false)
			list($currentRange, $currentGroup) = explode(':', $currentRange, 2);

		$js = '
window.localPage.chartsInit[window.localPage.chartsInit.length] = function() {
	var data = google.visualization.arrayToDataTable(['.
		'["' . implode('","', $headerData). '"]'.
		(empty($chartData) ? '' : ',[') .
		implode('],[', $chartData).
		(empty($chartData) ? '' : ']') .
	']);
	var options = {legend:{position:"bottom"},focusTarget:"category",animation:{duration:500,easing:"in"}'.$theme.$viewport.'};
	var chart = new google.visualization.LineChart(document.getElementById("hikamarket_chart_'.$id.'_div"));
	chart.draw(data, options);

	window.localPage.charts["'.$id.'"] = {chart: chart, options:options, data:data, max:'.count($chartData).', period:"'.$currentRange.'",periodGroup:"'.$currentGroup.'"};
};
';
		unset($chartData);

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		$dropdownHelper = hikamarket::get('helper.dropdown');
		$groups = array('year'=>'','month'=>'','week'=>'','day'=>'');
		foreach($groups as $k => &$v) {
			$v = array(
				'name' => JText::_('HIKAM_PERIOD_BY_'.strtoupper($k)),
				'link' => '#'.$k,
				'click' => 'return window.localPage.changeChartData(\'graph\',\''.$id.'\',\''.$data['key'].'\',\':'.$k.'\', this);'
			);
		}
		unset($v);

		$dropData = array();
		foreach($ranges as $k => $v) {
			if(strpos($k, '.day') !== false)
				continue;
			$dropData[] = array('name' => JText::_($v), 'link' => '#'.$k, 'click' => 'return window.localPage.changeChartData(\'graph\',\''.$id.'\',\''.$data['key'].'\',\''.$k.'\', this);');
		}
		$drop = $dropdownHelper->display(JText::_($ranges[ $currentRange ]), $dropData, array('type' => '','right' => true,'up' => false, 'label-id' => 'hikamarket_chart_'.$id.'_range')).
				$dropdownHelper->display(JText::_('HIKAM_PERIOD_BY_'.strtoupper($currentGroup)), $groups, array('type' => '','right' => true,'up' => false, 'label-id' => 'hikamarket_chart_'.$id.'_group'));

		return '
<div class="hikamarket_chart_data_selector" style="float:right">'.$drop.'</div><div style="clear:both"></div>
<div id="hikamarket_chart_'.$id.'_div" style="width:100%; height: 250px;"><div class="hikamarket_empty_chart">'.JText::_('HIKAM_LOADING_CHART').'</div></div>';
	}

	protected function processGraphData($data) {
		if(empty($data['type']) || $data['type'] != 'graph')
			return array(false,false);

		if(!empty($data['query']) && (!isset($data['value']) || $data['value'] === null)) {
			if(!empty($data['vars'])) {
				$data['value'] = $this->processQuery($data['query'], $data['vars']);
			} else
				$data['value'] = $this->processQuery($data['query']);
		}

		$col = $data['graph']['cols'];

		$headerData = array('Date');
		$default = array();
		if(!empty($data['value'])) {
			foreach($data['value'] as $value) {
				if(!isset($headerData[ 'col_'.$value->$col ])) {
					$headerData[ 'col_'.$value->$col ] = $value->$col;
					$default[$value->$col] = '0';
				}
			}
		} else if(!empty($data['query']) && !empty($data['vars']) && isset($data['vars']['DATE_START'])) {
			$vars = $data['vars'];
			$vars['DATE_START'] = 0;
			$vars['DATE_END'] = -1;
			$vars['_DATE_RANGE'] = $vars['DATE_RANGE'];
			unset($vars['DATE_RANGE']);
			$headerValues = $this->processQuery($data['query'], $vars, 1);
			foreach($headerValues as $value) {
				if(!isset($headerData[ 'col_'.$value->$col ])) {
					$headerData[ 'col_'.$value->$col ] = $value->$col;
					$default[$value->$col] = '0';
				}
			}
		}

		$chartData = array();

		if(count($headerData) == 1)
			return array($headerData, $chartData);

		if(!empty($data['graph']['axis']) && $data['graph']['axis'] == 'date') {
			if(!empty($data['vars']['DATE_RANGE']))
				$this->setDateRange($data, $data['vars']['DATE_RANGE']);

			if(empty($data['vars']['DATE_START'])) {
				$first = reset($data['value']);
				$first = $first->axis;
				$d = explode('-', $first, 3);
				if(!isset($d[2])) $d[2] = 1;
				if(!isset($d[1])) $d[1] = 1;
				$first = mktime(1,0,0,(int)$d[1],(int)$d[2],(int)$d[0]) - $this->timeoffset;
			} else {
				$first = $data['vars']['DATE_START'] - $this->timeoffset;
			}

			if(empty($data['vars']['DATE_END']) || $data['vars']['DATE_END'] < 0) {
				$end = end($data['value']);
				$end = $end->axis;
				$d = explode('-', $end, 3);
				if(!isset($d[2])) $d[2] = 1;
				if(!isset($d[1])) $d[1] = 1;
				$end = mktime(1,0,0,(int)$d[1],(int)$d[2],(int)$d[0]) - $this->timeoffset;
			} else {
				$end = $data['vars']['DATE_END'] - $this->timeoffset;
			}

			$now = $end;
			if($now >= $first) {
				$inc = 86400;
				$format = 'Y-m-d';
				switch(@$data['vars']['DATE_GROUP']) {
					case 'year':
						$format = 'Y';
						$inc = 8640000;
						break;
					case 'month':
						$format = 'Y-m';
						$inc = 864000;
						break;
					case 'week':
						$format = 'Y W';
						$inc = 604800;
						break;
				}
				do {
					$chartData[ date($format, $first) ] = (array)$default;
					$first += $inc;
				} while($first < $now);
				if(!isset($chartData[ date($format, $now) ]))
					$chartData[ date($format, $now) ] = (array)$default;
			}
		}
		foreach($data['value'] as $value) {
			if(!isset($chartData[ $value->axis ])) {
				$chartData[ $value->axis ] = (array)($default);
			}
			$chartData[ $value->axis ][ $value->$col ] = strip_tags($value->value);
		}
		ksort($chartData);
		foreach($chartData as $k => &$d) {
			$d = '"' . strip_tags(str_replace('"','\\"', $k)) . '",' . strip_tags(implode(',', $d));
		}
		unset($d);

		return array($headerData, $chartData);
	}

	protected function displayPie($data) {
		$this->initJS(@$data['vendor_id']);
		$id = uniqid();

		list($headerData, $chartData) = $this->processPieData($data);

		$options = '';
		if(!empty($data['pie']['donut']))
			$options .= ',pieHole:0.4';

		if(!empty($data['pie']['3d']))
			$options .= ',is3D:true';

		if(isset($data['pie']['legend']) && $data['pie']['legend'] === false)
			$options .= ',legend:"none"';

		if(!empty($data['pie']['text']) && in_array($data['pie']['text'], array('label','percentage','value')))
			$options .= ',pieSliceText:"'.$data['pie']['text'].'"';

		$js = '
window.localPage.chartsInit[window.localPage.chartsInit.length] = function() {
	var data = google.visualization.arrayToDataTable(['.
		'["' . implode('","', $headerData). '"]'.
		(empty($chartData) ? '' : ',[') .
		implode('],[', $chartData).
		(empty($chartData) ? '' : ']') .
	']);
	var options = {animation:{duration:500,easing:"in"}'.$options.'};
	var chart = new google.visualization.PieChart(document.getElementById("hikamarket_chart_'.$id.'_div"));
	chart.draw(data, options);

	window.localPage.charts["'.$id.'"] = {chart: chart, options:options, data:data, max:'.count($chartData).'};
};
';

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		$dropdownHelper = hikamarket::get('helper.dropdown');
		$ranges = $this->getDateRangeList();
		$dropData = array();
		$currentRange = '';
		if(isset($data['vars']['DATE_RANGE']))
			$currentRange = JText::_($ranges[ $data['vars']['DATE_RANGE'] ]);
		else if(isset($data['vars']['_DATE_RANGE']))
			$currentRange = JText::_($ranges[ $data['vars']['_DATE_RANGE'] ]);
		foreach($ranges as $k => $v) {
			$dropData[] = array('name' => JText::_($v), 'link' => '#'.$k, 'click' => 'return window.localPage.changeChartData(\'pie\',\''.$id.'\',\''.$data['key'].'\',\''.$k.'\', this);');
		}
		$drop = $dropdownHelper->display($currentRange, $dropData, array('type' => '','right' => true,'up' => false, 'label-id' => 'hikamarket_chart_'.$id.'_range'));

		return '
<div class="hikamarket_chart_data_selector" style="float:right">'.$drop.'</div><div style="clear:both"></div>
<div id="hikamarket_chart_'.$id.'_div" style="width:100%; height: 250px;"><div class="hikamarket_empty_chart">'.JText::_('HIKAM_LOADING_CHART').'</div></div>';
	}

	protected function processPieData($data) {
		if(empty($data['type']) || $data['type'] != 'pie')
			return array(false,false);

		if(!empty($data['query']) && (!isset($data['value']) || $data['value'] === null)) {
			if(!empty($data['vars'])) {
				$data['value'] = $this->processQuery($data['query'], $data['vars']);
			} else
				$data['value'] = $this->processQuery($data['query']);
		}

		$headerData = array('Key','Value');
		if(isset($data['pie']['header']))
			$headerData = $data['pie']['header'];

		$chartData = array();
		$key = $data['pie']['key'];
		foreach($data['value'] as $value) {
			$chartData[ $value->$key ] = $value->value;
		}
		if(empty($chartData))
			return array($headerData, $chartData);

		foreach($chartData as $k => &$d) {
			if(is_array($d))
				$d = '"'.strip_tags(str_replace('"','\\"', $k)) . '",' . strip_tags(implode(',', $d));
			else
				$d = '"' . strip_tags(str_replace('"','\\"', $k)) . '",' . strip_tags($d);
		}
		unset($d);

		return array($headerData, $chartData);
	}

	protected function displayGeo($data) {
		$this->initJS(@$data['vendor_id']);
		$id = uniqid();

		list($headerData, $chartData) = $this->processGeoData($data);


		$options = array();

		$js = '
window.localPage.chartsInit[window.localPage.chartsInit.length] = function() {
	var data = google.visualization.arrayToDataTable(['.
		'["' . implode('","', $headerData). '"]'.
		(empty($chartData) ? '' : ',[') .
		implode('],[', $chartData).
		(empty($chartData) ? '' : ']') .
	']);
	var options = {'.implode(',', $options).'};
	var chart = new google.visualization.GeoChart(document.getElementById("hikamarket_chart_'.$id.'_div"));
	chart.draw(data, options);

	window.localPage.charts["'.$id.'"] = {chart:chart, options:options, data:data};
};
';

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		$dropdownHelper = hikamarket::get('helper.dropdown');
		$ranges = $this->getDateRangeList();
		$dropData = array();
		$currentRange = '';
		if(isset($data['vars']['DATE_RANGE']))
			$currentRange = JText::_($ranges[ $data['vars']['DATE_RANGE'] ]);
		else if(isset($data['vars']['_DATE_RANGE']))
			$currentRange = JText::_($ranges[ $data['vars']['_DATE_RANGE'] ]);
		foreach($ranges as $k => $v) {
			$dropData[] = array('name' => JText::_($v), 'link' => '#'.$k, 'click' => 'return window.localPage.changeChartData(\'geo\',\''.$id.'\',\''.$data['key'].'\',\''.$k.'\', this);');
		}
		$drop = $dropdownHelper->display($currentRange, $dropData, array('type' => '','right' => true,'up' => false, 'label-id' => 'hikamarket_chart_'.$id.'_range'));

		return '
<div class="hikamarket_chart_data_selector" style="float:right">'.$drop.'</div><div style="clear:both"></div>
<div id="hikamarket_chart_'.$id.'_div" style="width:100%; height: 250px;"><div class="hikamarket_empty_chart">'.JText::_('HIKAM_LOADING_CHART').'</div></div>';
	}

	protected function processGeoData($data) {
		if(empty($data['type']) || $data['type'] != 'geo')
			return array(false,false);

		if(!empty($data['query']) && (!isset($data['value']) || $data['value'] === null)) {
			if(!empty($data['vars'])) {
				$data['value'] = $this->processQuery($data['query'], $data['vars']);
			} else
				$data['value'] = $this->processQuery($data['query']);
		}

		$headerData = array('Country','Value');
		if(isset($data['geo']['header']))
			$headerData = $data['geo']['header'];

		$chartData = array();
		$key = $data['geo']['key'];
		foreach($data['value'] as $value) {
			$chartData[ $value->$key ] = $value->value;
		}
		if(empty($chartData))
			return array($headerData, $chartData);

		foreach($chartData as $k => &$d) {
			if(is_array($d))
				$d = '"'.strip_tags(str_replace('"','\\"', $k)) . '",' . strip_tags(implode(',', $d));
			else
				$d = '"' . strip_tags(str_replace('"','\\"', $k)) . '",' . strip_tags($d);
		}
		unset($d);

		return array($headerData, $chartData);
	}

	protected function getFormatedValue($value, $format, $data = array()) {
		$ret = null;
		switch($format) {
			case 'number':
				$ret = (int)$value;
				break;
			case 'date':
				$ret = hikashop_getDate($value);
				break;
			case 'price':
				if(empty($this->currencyClass))
					$this->currencyClass = hikamarket::get('shop.class.currency');
				$currency_id = 0;
				if(!empty($data) && is_array($data))
					$currency_id = (int)@$data['currency'];
				if(!empty($data) && is_object($data))
					$currency_id = (int)@$data->currency;

				if(is_object($value)) {
					if(isset($value->currency))
						$currency_id = $value->currency;
					$value = $value->value;
				}

				$ret = str_replace(' ', '&nbsp;', $this->currencyClass->format($value, $currency_id));
				break;
			case 'percentage':
				$ret = round(hikashop_toFloat($value), 2).'%';
				break;
			case 'translation':
			case 'trans':
				$ret = JText::_($value);
				break;
			case 'order_status':
				$ret = '<span class="order-label order-label-' . preg_replace('#[^a-z_0-9]#i', '_', str_replace(' ','_', $value)).'">' . hikamarket::orderStatus($value) . '</span>';
				break;
			case 'order_link':
				$order_id = 0;
				if(hikamarket::acl('order/show') && !empty($data)) {
					if(is_array($data)) $order_id = (int)@$data['order_id'];
					if(is_object($data)) $order_id = (int)@$data->order_id;
				}
				if($order_id > 0) {
					$ret = '<a href="'.hikamarket::completeLink('order&task=show&cid='.$order_id).'">'.$value.'</a>';
				} else
					$ret = $value;
				break;
			case 'string':
			case '':
				$ret = $value;
				break;

			default:
				if(preg_match_all('#{([-:. _A-Z0-9a-z]+)}#U', $format, $out)) {
					$ret = $format;
					foreach($out[1] as $key) {
						if(strpos($key, ':') !== false) {
							list($col, $f) = explode(':', $key, 2);
						} else {
							$col = $key;
							$f = 'string';
						}
						if(isset($value->$col))
							$ret = str_replace('{'.$key.'}', $this->getFormatedValue($value->$col, $f, $value), $ret);
						else {
							$v = '';
							if(isset($data['vars'][$key]))
								$v = $data['vars'][$key];
							$ret = str_replace('{'.$key.'}', $v, $ret);
						}
					}
				} else {
					$ret = $value;
				}
				break;

		}
		return $ret;
	}

	protected function getMergedValue($value, $format, $data = array()) {
		$f = reset($value);
		if(is_object($f))
			return $f;
		return implode('<br/>', $value);
	}
}

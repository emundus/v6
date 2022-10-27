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
class plgHikashopMarketmodule_vendorrelated extends JPlugin {

	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	private function init() {
		static $init = null;
		if($init !== null)
			return $init;

		$init = defined('HIKAMARKET_COMPONENT');
		if(!$init) {
			$filename = rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikamarket'.DS.'helpers'.DS.'helper.php';
			if(file_exists($filename)) {
				include_once($filename);
				$init = defined('HIKAMARKET_COMPONENT');
			}
		}
		return $init;
	}

	public function onBeforeProductListingLoad(&$filters,&$order,&$view, &$select, &$select2, &$a, &$b, &$on) {
		$ctrl = hikaInput::get()->getCmd('ctrl');
		$task = hikaInput::get()->getCmd('task');
		if($ctrl != 'product' || $task != 'show' || !$view->module)
			return;

		if(!isset($this->params)) {
			$pluginsClass = hikashop_get('class.plugins');
			$plugin = $pluginsClass->getByName('hikashop', 'marketmodule_vendorrelated');
			$ids = explode(',', @$plugin->params['ids']);
		} else if($this->params->get('ids', '') != '') {
			$ids = explode(',', $this->params->get('ids', ''));
		}

		$cid = hikashop_getCID();
		$module_id = (string)$view->params->get('from_module', '0');
		if(hikaInput::get()->getCmd('market_show_product_modules', 0))
			echo '<span class="label label-info">Product listing module : <strong>'.$module_id.'</strong></span>';
		if(empty($ids) || empty($cid) || !in_array($module_id, $ids))
			return;

		if(!$this->init())
			return;

		$productClass = hikashop_get('class.product');
		$product = $productClass->get($cid);
		$filters[] = 'b.product_vendor_id = ' . (int)$product->product_vendor_id;

		if(!isset($view->hikamarket))
			$view->hikamarket = new stdClass();
		$view->hikamarket->hide_sold_by = true;
	}
}

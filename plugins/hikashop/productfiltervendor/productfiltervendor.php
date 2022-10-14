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
class plgHikashopProductfiltervendor extends JPlugin {

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

	public function onBeforeProductListingLoad(&$filters,&$order,&$parent, &$select, &$select2, &$a, &$b, &$on) {
		global $Itemid;

		$ctrl = hikaInput::get()->getCmd('ctrl');
		$task = hikaInput::get()->getCmd('task');
		static $done = null;

		if($ctrl != 'product' || $task != 'listing' || $done === true)
			return;
		$done = true;

		if(!isset($this->params)) {
			$pluginsClass = hikashop_get('class.plugins');
			$plugin = $pluginsClass->getByName('hikashop', 'productfiltervendor');
			$ids = explode(',', @$plugin->params['ids']);
		} else if($this->params->get('ids', '') != '') {
			$ids = explode(',', $this->params->get('ids', ''));
		}

		$i = '' . $Itemid;
		if(empty($ids) || !in_array($i, $ids))
			return;

		if(!$this->init())
			return;

		$vendor = hikamarket::loadVendor(false);
		$filters[] = 'b.product_vendor_id = ' . (int)$vendor;
	}
}

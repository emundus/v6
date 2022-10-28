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
class plgHikashopProductforcevendorcategory extends JPlugin {

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

	public function onBeforeProductCreate(&$product, &$do) {
		if(!$this->init())
			return;

		if(hikashop_isClient('administrator')) {
			$vendor_id = (int)@$product->product_vendor_id;
		} else {
			$current_vendor = hikamarket::loadVendor(false);
			if($current_vendor === false)
				return;

			if($current_vendor > 1)
				$vendor_id = $current_vendor;
			else
				$vendor_id = (int)@$product->product_vendor_id;
		}

		$this->checkVendorCategory($product, $vendor_id);
	}

	public function onBeforeProductUpdate(&$product, &$do) {
		if(empty($product->categories))
			return;
		if(!$this->init())
			return;
		if(isset($product->product_vendor_id)) {
			$vendor_id = (int)$product->product_vendor_id;
		} elseif(isset($product->old->product_vendor_id)) {
			$vendor_id = (int)$product->old->product_vendor_id;
		} else {
			$productClass = hikamarket::get('shop.class.product');
			$oldProduct = $productClass->get($product->product_id);

			if(!isset($oldProduct->product_vendor_id))
				return;
			$vendor_id = (int)$oldProduct->product_vendor_id;
		}

		if($vendor_id <= 1)
			return;

		$this->checkVendorCategory($product, $vendor_id);
	}

	private function checkVendorCategory(&$product, $vendor_id) {
		if((int)$vendor_id <= 1)
			return;
		$vendorClass = hikamarket::get('class.vendor');
		$rootCategory = $vendorClass->getRootCategory($vendor_id, 1);
		if(empty($rootCategory))
			return;

		if(!in_array($rootCategory, $product->categories))
			$product->categories[] = $rootCategory;
	}
}

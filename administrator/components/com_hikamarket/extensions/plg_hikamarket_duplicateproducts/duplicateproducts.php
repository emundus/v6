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
class plgHikamarketDuplicateproducts extends JPlugin {
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	public function onAfterVendorCreate(&$vendor) {
		$pluginsClass = hikamarket::get('shop.class.plugins');
		$plugin = $pluginsClass->getByName('hikamarket', 'duplicateproducts');
		if(empty($plugin->params['category_id']))
			return;
		$category_id = (int)$plugin->params['category_id'];

		$db = JFactory::getDBO();
		$config = hikamarket::config();
		$productClass = hikamarket::get('shop.class.product');
		$importHelper = hikamarket::get('shop.helper.import');
		$vendorClass = hikamarket::get('class.vendor');

		$query = 'SELECT c.* FROM '.hikamarket::table('shop.category').' AS c '.
			' INNER JOIN '.hikamarket::table('shop.category').' AS d ON c.category_left >= d.category_left AND c.category_right <= d.category_right '.
			' where d.category_id = ' . (int)$category_id;
		$db->setQuery($query);
		$categories = $db->loadObjectList('category_id');

		if(empty($categories))
			return;

		$categories_id = array_keys($categories);
		$category_translated = array();


		$vendor_category_id = $vendorClass->getRootCategory($vendor, 1);
		if(empty($vendor_category_id)) {
			$vendor_category_id = 2;
		}

		if(version_compare(PHP_VERSION, '5.2.0', '>')) {
			$category_translated = array_fill_keys($categories_id, $vendor_category_id);
		} else {
			$tmp = array_fill(0, count($categories_id), $vendor_category_id);
			$category_translated = array_combine($categories_id, $tmp);
			unset($tmp);
		}
		unset($categories);

		$query = 'SELECT pc.product_id FROM ' . hikamarket::table('shop.product_category') . ' AS pc '.
			' INNER JOIN ' . hikamarket::table('shop.product') . ' AS p ON pc.product_id = p.product_id '.
			' WHERE p.product_vendor_id = 0 AND pc.category_id IN ('. implode(',', $categories_id).')';
		$db->setQuery($query);
		$product_ids = $db->loadColumn();

		foreach($product_ids as $product_id) {
			$importHelper->addTemplate($product_id);

			if(empty($importHelper->template))
				continue;

			$newProduct = new stdClass();
			$newProduct->product_code = $importHelper->template->product_code.'_vendor'.(int)$vendor->vendor_id;

			$importHelper->_checkData($newProduct);

			$newProduct->categories = array();
			if(!empty($importHelper->template->categories)) {
				foreach($importHelper->template->categories as $cat_id) {
					if(isset($category_translated[$cat_id]))
						$newProduct->categories[] = $category_translated[$cat_id];
					else
						$newProduct->categories[] = $cat_id;
				}
			}
			if(empty($newProduct->categories))
				$newProduct->categories[] = $vendor_category_id;

			$newProduct->product_vendor_id = (int)$vendor->vendor_id;

			$products = array($newProduct);
			if(!empty($importHelper->template->variants)) {
				foreach($importHelper->template->variants as $variant) {
					$copy = clone($variant);
					$copy->product_parent_id = $newProduct->product_code;
					$copy->product_code = $copy->product_code.'_vendor'.(int)$vendor->vendor_id;

					$copy->product_vendor_id = (int)$vendor->vendor_id;

					unset($copy->product_id);
					$products[] = $copy;
				}
			}
			$importHelper->_insertProducts($products);


			unset($newProduct);
			unset($products);
			unset($importHelper->template);
		}
	}
}

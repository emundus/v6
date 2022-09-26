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
class plgHikashopVendorgroupafterpurchase extends JPlugin {

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

	function onProductDisplay(&$product, &$html) {
		if(!$this->init())
			return;

		if(empty($product->product_vendor_params))
			$product->product_vendor_params = array();
		if(!empty($product->product_vendor_params) && is_string($product->product_vendor_params))
			$product->product_vendor_params = hikamarket::unserialize($product->product_vendor_params);

		$joomlaAclType = hikamarket::get('type.joomla_acl');
		$trStyle = '';

		if(empty($product->product_vendor_params['vendorgroupafterpurchase_isproduct']))
			$trStyle = 'display:none;';

		$html[] = '
<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_vendorgroupafterpurchase"><div>
	<div class="hikashop_product_part_title hikashop_product_edit_vendorgroupafterpurchase_title">'.JText::_('VENDOR_GROUP_AFTER_PURCHASE').'</div>
		<table class="admintable table" style="width:100%">
			<tr>
				<td class="key"><label>'.JText::_('HIKAM_IS_VENDOR_SPECIAL_PRODUCT').'</label></td>
				<td>'.JHTML::_('hikaselect.booleanlist', 'data[product][product_vendor_params][vendorgroupafterpurchase_isproduct]', 'onchange="window.localPage.vendorproduct_update(this);"', @$product->product_vendor_params['vendorgroupafterpurchase_isproduct']).'</td>
			</tr>
			<tr id="hikamarket_vendor_group_activator" style="'.$trStyle.'">
				<td class="key"><label>'.JText::_('HIKAM_IS_VENDOR_ACTIVATION_PRODUCT').'</label></td>
				<td>'.JHTML::_('hikaselect.booleanlist', 'data[product][product_vendor_params][vendorgroupafterpurchase_vendoractivation]', '', @$product->product_vendor_params['vendorgroupafterpurchase_vendoractivation']).'</td>
			</tr>
			<tr id="hikamarket_vendor_group_selector" style="'.$trStyle.'">
				<td class="key"><label>'.JText::_('HIKAM_VENDOR_GROUP').'</label></td>
				<td>'.$joomlaAclType->display('data[product][product_vendor_params][vendorgroupafterpurchase_group]', @$product->product_vendor_params['vendorgroupafterpurchase_group']).'</td>
			</tr>
		</table>
<script type="text/javascript">
if(!window.localPage)
	window.localPage = {};
window.localPage.vendorproduct_update = function(el) {
	var d = document,
		tr1 = d.getElementById("hikamarket_vendor_group_activator")
		tr2 = d.getElementById("hikamarket_vendor_group_selector");
	if(!tr1 && !tr2) return;
	s = (el.value == "1" && el.checked) ? "" : "none";
	if(tr1) tr1.style.display = s;
	if(tr2) tr2.style.display = s;
}
</script>
</div></div>';
	}

	protected function loadProductVendorParams(&$order) {
		if(empty($order->hikamarket))
			$order->hikamarket = new stdClass();

		if(!empty($order->hikamarket->products_vendor_params))
			return;

		$db = JFactory::getDBO();
		$product_ids = array();
		$order_id = 0;

		if(!empty($order->cart->products)) {
			foreach($order->cart->products as $product) {
				$product_ids[] = (int)$product->product_id;
			}
		} else if(!empty($order->product)) {
			if(is_array($order->product)) {
				foreach($order->product as $product) {
					$product_ids[] = (int)$product->product_id;
				}
			} else {
				$product_ids[] = $order->product->product_id;
			}
		} else {
			$order_id = $order->order_id;
		}

		if(!empty($product_ids)) {
			$query = 'SELECT p.product_id, p.product_parent_id, p.product_vendor_params, v.product_vendor_params as parent_product_vendor_params '.
				' FROM ' . hikashop_table('product') . ' AS p LEFT JOIN ' . hikashop_table('product') . ' AS v ON p.product_id = v.product_parent_id '.
				' WHERE p.product_id IN ('.implode(',', $product_ids).')';
		} else if(!empty($order_id)) {
			$query = 'SELECT p.product_id, p.product_parent_id, p.product_vendor_params, v.product_vendor_params as parent_product_vendor_params '.
				' FROM ' . hikashop_table('order_product') . ' AS op '.
				' INNER JOIN ' . hikashop_table('product') . ' AS p ON op.product_id = p.product_id '.
				' LEFT JOIN ' . hikashop_table('product') . ' AS v ON p.product_id = v.product_parent_id '.
				' WHERE op.order_id = ' . (int)$order_id;
		}

		if(!empty($query)) {
			$db->setQuery($query);
			$order->hikamarket->products_vendor_params = $db->loadObjectList('product_id');
		}
	}


	public function onBeforeOrderCreate(&$order, &$do) {
		if(empty($order) || empty($order->cart) || empty($order->cart->products))
			return;

		if(hikashop_isClient('administrator'))
			return;
		$app = JFactory::getApplication();

		if(!$this->init())
			return;
		$this->loadProductVendorParams($order);
		$vendor = false;

		foreach($order->hikamarket->products_vendor_params as &$product) {
			if(empty($product->product_vendor_params) && empty($product->parent_product_vendor_params))
				continue;
			if(!empty($product->product_vendor_params) && is_string($product->product_vendor_params))
				$product->product_vendor_params = hikamarket::unserialize($product->product_vendor_params);
			if(!empty($product->parent_product_vendor_params) && is_string($product->parent_product_vendor_params))
				$product->parent_product_vendor_params = hikamarket::unserialize($product->parent_product_vendor_params);

			if(!empty($product->product_vendor_params['vendorgroupafterpurchase_isproduct']) || !empty($product->parent_product_vendor_params['vendorgroupafterpurchase_isproduct'])) {
				if(!$this->init())
					continue;

				if($vendor === false)
					$vendor = hikamarket::loadVendor(true);

				if($vendor === null) {
					$do = false;
					foreach($order->cart->products as $p) {
						if($p->product_id == $product->product_id) {
							$app->enqueueMessage(JText::sprintf('HIKAM_NEED_TO_BE_VENDOR_FOR_BUYING_PRODUCT', $p->order_product_name), 'error');
							break;
						}
					}
				}
			}
		}
	}

	public function onAfterOrderCreate(&$order, &$send_email) {
		return $this->onAfterOrderUpdate($order, $send_email);
	}

	public function onAfterOrderUpdate(&$order, &$send_email) {
		if(empty($order) || empty($order->order_status))
			return;
		if(!empty($order->old) && !empty($order->old->order_status) && $order->order_status == $order->old->order_status)
			return;

		$config = hikashop_config();
		$confirmed_status = $config->get('order_confirmed_status');
		$invoice_statuses = explode(',', $config->get('invoice_order_statuses'));
		if(empty($invoice_statuses))
			$invoice_statuses = array('confirmed','shipped');

		if($order->order_status != $confirmed_status && !in_array($order->order_status, $invoice_statuses))
			return;

		if(!$this->init())
			return;
		$this->loadProductVendorParams($order);

		$user_id = 0;
		if(!empty($order->old->order_user_id))
			$user_id = (int)$order->old->order_user_id;
		if(!empty($order->order_user_id))
			$user_id = (int)$order->order_user_id;

		$vendor = false;
		$saveVendor = null;
		$vendorClass = null;

		foreach($order->hikamarket->products_vendor_params as &$product) {
			if(empty($product->product_vendor_params) && empty($product->parent_product_vendor_params))
				continue;
			if(!empty($product->product_vendor_params) && is_string($product->product_vendor_params))
				$product->product_vendor_params = hikamarket::unserialize($product->product_vendor_params);
			if(!empty($product->parent_product_vendor_params) && is_string($product->parent_product_vendor_params))
				$product->parent_product_vendor_params = hikamarket::unserialize($product->parent_product_vendor_params);

			$params = null;
			if(!empty($product->parent_product_vendor_params))
				$params = $product->parent_product_vendor_params;
			if(!empty($product->product_vendor_params))
				$params = $product->product_vendor_params;

			if(!empty($params['vendorgroupafterpurchase_isproduct'])) {
				$this->init();
				if($vendor === false) {
					$vendorClass = hikamarket::get('class.vendor');
					$vendor = $vendorClass->get($user_id, 'user');
					if(!empty($vendor->vendor_access) && is_string($vendor->vendor_access))
						$vendor->vendor_access = explode(',', $vendor->vendor_access);
					if(empty($vendor->vendor_access))
						$vendor->vendor_access = array();
				}

				if(!empty($params['vendorgroupafterpurchase_vendoractivation']) && empty($vendor->vendor_published)) {
					if(empty($saveVendor)) {
						$saveVendor = new stdClass();
						$saveVendor->vendor_id = $vendor->vendor_id;
					}
					$saveVendor->vendor_published = 1;
				}

				if(!empty($params['vendorgroupafterpurchase_group'])) {
					$groups = explode(',', $params['vendorgroupafterpurchase_group']);
					foreach($groups as $group) {
						$group = '@' . $group;
						if(!in_array($group, $vendor->vendor_access)) {
							$vendor->vendor_access[] = $group;
							if(empty($saveVendor)) {
								$saveVendor = new stdClass();
								$saveVendor->vendor_id = $vendor->vendor_id;
							}
							$saveVendor->vendor_access = implode(',', $vendor->vendor_access);
						}
					}
				}
			}
		}

		if(!empty($saveVendor)) {
			$vendorClass->save($saveVendor);
		}
	}
}

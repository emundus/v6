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
class hikamarketCheckoutClass extends hikamarketClass {

	public function afterCheckoutStep($controllerName, &$go_back, $original_go_back, &$controller) {
		if($controllerName == 'plg.market.terms')
			return $this->afterTermsCheckoutStep($go_back, $original_go_back, $controller);
	}

	private function afterTermsCheckoutStep(&$go_back, $original_go_back, &$controller) {
		$app = JFactory::getApplication();

		$is_block = hikaInput::get()->getInt('hikamarket_checkout_terms_block', 0);
		if($is_block) {
			$app->setUserState(HIKASHOP_COMPONENT.'.checkout_terms', hikaInput::get()->getInt('hikashop_checkout_terms', 0));
			$app->setUserState(HIKAMARKET_COMPONENT.'.checkout_terms', hikaInput::get()->get('hikamarket_checkout_terms', array(), 'array'));
		}

		if(!empty($controller->cart_update) || $go_back)
			return;

		$status = (bool)$app->getUserState(HIKASHOP_COMPONENT.'.checkout_terms', 0);
		if(!$status) {
			$app->enqueueMessage(JText::_('PLEASE_ACCEPT_TERMS_BEFORE_FINISHING_ORDER'), 'error');
			$go_back = true;
		}

		$cart = $controller->initCart();
		$vendors = array();
		foreach($cart->products as $product) {
			$vendor_id = (int)$product->product_vendor_id;
			$vendors[$vendor_id] = $vendor_id;
		}
		if(isset($vendors[0])) unset($vendors[0]);
		if(isset($vendors[1])) unset($vendors[1]);

		if(!empty($vendors)) {
			$db = JFactory::getDBO();
			$query = 'SELECT vendor_id, vendor_name, vendor_terms FROM '.hikamarket::table('vendor').' WHERE vendor_id IN ('.implode(',', $vendors).')';
			$db->setQuery($query);
			$terms_content = $db->loadObjectList('vendor_id');

			$terms = (array)$app->getUserState(HIKAMARKET_COMPONENT.'.checkout_terms', array());
			foreach($vendors as $vendor) {
				if(!empty($terms_content[$vendor]->vendor_terms) && empty($terms[$vendor])) {
					$app->enqueueMessage(JText::sprintf('PLEASE_ACCEPT_TERMS_FOR_VENDOR_BEFORE_FINISHING_ORDER', $terms_content[$vendor]->vendor_name), 'error');
					$go_back = true;
				}
			}
		}
	}
}

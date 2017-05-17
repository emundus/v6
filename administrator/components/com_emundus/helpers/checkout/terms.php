<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
include_once HIKASHOP_HELPER . 'checkout.php';

class hikashopCheckoutTermsHelper extends hikashopCheckoutHelperInterface {
	public function check(&$controller, &$params) {
		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();

		if(!empty($cart->cart_params->terms_checked))
			return true;

		$checkoutHelper->addMessage('terms.checkfailed', array(
			JText::_('PLEASE_ACCEPT_TERMS_BEFORE_FINISHING_ORDER'),
			'error'
		));
		return false;
	}

	public function validate(&$controller, &$params, $data = array()) {
		$checkout = JRequest::getVar('checkout', array(), '', 'array');

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();
		$cart_id = (int)$cart->cart_id;

		if(!isset($checkout['terms']))
			$checkout['terms'] = 0;

		if(isset($cart->cart_params->terms_checked) && (int)$cart->cart_params->terms_checked == (int)$checkout['terms']) {
			if((int)$cart->cart_params->terms_checked)
				return true;

			$checkoutHelper->addMessage('terms.checkfailed', array(
				JText::_('PLEASE_ACCEPT_TERMS_BEFORE_FINISHING_ORDER'),
				'error'
			));
			return false;
		}

		$cartClass = hikashop_get('class.cart');
		if(!$cartClass->updateTerms($cart_id, (int)$checkout['terms'])) {
			$checkoutHelper->addMessage('terms.updatefailed', array(
				JText::_('TERMS_AND_CONDITIONS_CHECKED_STATUS_FAILED'),
				'error'
			));
			return false;
		}

		$checkoutHelper->getCart(true);
		if((int)$checkout['terms'])
			return true;

		$checkoutHelper->addMessage('terms.checkfailed', array(
			JText::_('PLEASE_ACCEPT_TERMS_BEFORE_FINISHING_ORDER'),
			'error'
		));
		return false;
	}

	public function display(&$view, &$params) {
		$params['article_id'] = (int)$view->config->get('checkout_terms', 0);

		$params['popup_width'] = (int)$view->config->get('terms_and_conditions_width', 450);
		if($params['popup_width'] <= 0)
			$params['popup_width'] = 450;

		$params['popup_height'] = (int)$view->config->get('terms_and_conditions_height', 480);
		if($params['popup_height'] <= 0)
			$params['popup_height'] = 480;
	}
}

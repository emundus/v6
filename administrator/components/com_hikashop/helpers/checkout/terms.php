<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
include_once HIKASHOP_HELPER . 'checkout.php';

class hikashopCheckoutTermsHelper extends hikashopCheckoutHelperInterface {
	protected $params = array(
		'article_id' => array(
			'name' => 'HIKASHOP_CHECKOUT_TERMS',
			'type' => 'namebox',
			'tooltip' => 'checkout_terms',
			'namebox' => 'article',
			'default' => ''
		),
		'size' => array(
			'name' => 'TERMS_AND_CONDITIONS_POPUP_SIZE',
			'type' => 'group',
			'tooltip' => 'terms_and_conditions_xy',
			'data' => array(
				'popup_width' => array(
					'type' => 'text',
					'attributes' => 'style="width:50px"',
					'default' => 450
				),
				'size_separator' => array(
					'type' => 'html',
					'html' => ' x ',
				),
				'popup_height' => array(
					'type' => 'text',
					'attributes' => 'style="width:50px"',
					'default' => 480
				),
				'size_unit' => array(
					'type' => 'html',
					'html' => ' px',
				),
			),
		),
		'label' => array(
			'name' => 'FIELD_LABEL',
			'type' => 'textarea',
			'default' => '',
		),
		'pre_checked' =>  array(
			'name' => 'CHECKBOX_PRE_CHECKED',
			'type' => 'boolean',
			'default' => 0
		),

	);

	public function getParams() {
		$this->params['label']['attributes'] = 'rows="3" cols="30" placeholder="'.JText::_('PLEASE_ACCEPT_TERMS').'"';
		return parent::getParams();
	}

	public function check(&$controller, &$params) {
		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();

		$key = 'terms_checked_' . $params['src']['step'] . '_' .  $params['src']['pos'];
		if(!empty($cart->cart_params->$key))
			return true;

		$checkoutHelper->addMessage('terms_' . $params['src']['step'] . '_' .  $params['src']['pos'] . '.checkfailed', array(
			JText::_('PLEASE_ACCEPT_TERMS_BEFORE_FINISHING_ORDER'),
			'error'
		));
		return false;
	}

	public function validate(&$controller, &$params, $data = array()) {
		$checkout = hikaInput::get()->get('checkout', array(), 'array');

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();
		$cart_id = (int)$cart->cart_id;
		$name = 'terms_'. $params['src']['step'] . '_' .  $params['src']['pos'];

		if(!isset($checkout[$name]))
			$checkout[$name] = 0;

		$key = 'terms_checked_' . $params['src']['step'] . '_' .  $params['src']['pos'];

		if(isset($cart->cart_params->$key) && (int)$cart->cart_params->$key == (int)$checkout[$name]) {
			if((int)$cart->cart_params->$key)
				return true;

			$checkoutHelper->addMessage('terms_' . $params['src']['step'] . '_' .  $params['src']['pos'] . '.checkfailed', array(
				JText::_('PLEASE_ACCEPT_TERMS_BEFORE_FINISHING_ORDER'),
				'error'
			));
			return false;
		}

		$cartClass = hikashop_get('class.cart');
		if(!$cartClass->updateTerms($cart_id, (int)$checkout[$name], $key)) {
			$checkoutHelper->addMessage('terms_' . $params['src']['step'] . '_' .  $params['src']['pos'] . '.updatefailed', array(
				JText::_('TERMS_AND_CONDITIONS_CHECKED_STATUS_FAILED'),
				'error'
			));
			return false;
		}

		$checkoutHelper->getCart(true);
		if((int)$checkout[$name])
			return true;

		$checkoutHelper->addMessage('terms_' . $params['src']['step'] . '_' .  $params['src']['pos'] . '.checkfailed', array(
			JText::_('PLEASE_ACCEPT_TERMS_BEFORE_FINISHING_ORDER'),
			'error'
		));
		return false;
	}

	public function display(&$view, &$params) {
		if(!isset($params['article_id']))
			$params['article_id'] = (int)$view->config->get('checkout_terms', 0);
		if(!isset($params['popup_width']))
			$params['popup_width'] = (int)$view->config->get('terms_and_conditions_width', 450);
		if($params['popup_width'] <= 0)
			$params['popup_width'] = 450;
		if(!isset($params['popup_height']))
			$params['popup_height'] = (int)$view->config->get('terms_and_conditions_height', 480);
		if($params['popup_height'] <= 0)
			$params['popup_height'] = 480;
		if(empty($params['label']))
			$params['label'] = JText::_('PLEASE_ACCEPT_TERMS');
		else{
			$key = strtoupper($params['label']);
			$trans = JText::_($key);
			if($trans != $key)
				$params['label'] = $trans;
		}
	}
}

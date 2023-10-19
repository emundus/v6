<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class entryController extends hikashopController
{
	var $display = array('');
	var $modify_views = array('form', 'edit', 'newentry', 'save');
	var $add = array();
	var $modify = array();
	var $delete = array();

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config,$skip);
		if(!$skip) {
			$this->registerDefaultTask('edit');
		}
	}

	public function form() {
		return $this->edit();
	}

	public function newentry() {
		hikaInput::get()->set('layout', 'newentry');
		return $this->display();
	}

	public function save() {
		global $Itemid;
		$url = 'checkout';
		if(!empty($Itemid)) {
			$url .= '&Itemid=' . $Itemid;
		}

		$app = JFactory::getApplication();

		$fieldClass = hikashop_get('class.field');
		$null = null;
		$entriesData = $fieldClass->getInput('entry', $null);

		$hikashop_config =& hikashop_config();
		if($hikashop_config->get('checkout_legacy', 0)) {
			$app->setUserState( HIKASHOP_COMPONENT.'.entries_fields', null);
		}
		$ok = true;

		if(empty($entriesData)) {
			$app->redirect( hikashop_completeLink('entry', false, true) );
		}

		$cartClass = hikashop_get('class.cart');
		$fields =& $fieldClass->getData('frontcomp', 'entry');

		$cartClass->resetCart(0);
		$productsToAdd = array();
		$coupons = array();
		foreach($entriesData as $entryData){
			foreach(get_object_vars($entryData) as $namekey=>$value){
				foreach($fields as $field){
					if($field->field_namekey == $namekey){
						$ok = false;
						if(!empty($field->field_options) && !is_array($field->field_options))
							$field->field_options = hikashop_unserialize($field->field_options);
						if(!empty($field->field_options['product_id'])){
							if(is_numeric($value) && is_numeric($field->field_options['product_value'])){
								if( $value === $field->field_options['product_value'] ){
									$ok = true;
								}
							}elseif(is_string($value) && !empty($field->field_options['product_value']) && is_array($field->field_options['product_value']) && in_array($value,$field->field_options['product_value'])){
								$ok = true;
							}elseif($value == $field->field_options['product_value']){
								$ok = true;
							}

							if($ok){
								$id = $field->field_options['product_id'];
								if(empty($productsToAdd[$id])){
									$productsToAdd[$id]=array('id'=>$id,'qty'=>1);
								}else{
									$productsToAdd[$id]['qty'] = $productsToAdd[$id]['qty'] + 1;
								}
							}
						}

						if($field->field_type=='coupon' && !empty($field->coupon[$value])){
							$coupons[] = $field->coupon[$value];
						}
						break;
					}
				}
			}
		}

		if(!empty($productsToAdd)){
			$cartClass->addProduct(0, $productsToAdd);
		}

		if(count($coupons)>1){
			$total = 0.0;
			$currency = hikashop_getCurrency();
			$currencyClass = hikashop_get('class.currency');
			$discountClass = hikashop_get('class.discount');
			foreach($coupons as $item){
				$currencyClass->convertCoupon($item,$currency);
				$total = $total + $item->discount_flat_amount;
				$database = JFactory::getDBO();
				$database->setQuery('UPDATE '.hikashop_table('discount').' SET discount_used_times=discount_used_times+1 WHERE discount_id = '.$item->discount_id);
				$database->execute();
			}
			$newCoupon = new stdClass();
			$newCoupon->discount_type='coupon';
			$newCoupon->discount_currency_id = $currency;
			$newCoupon->discount_flat_amount = $total;
			$newCoupon->discount_quota = 1;
			jimport('joomla.user.helper');
			$newCoupon->discount_code = JUserHelper::genRandomPassword(30);
			$newCoupon->discount_published = 1;
			$discountClass->save($newCoupon);
			$coupon = $newCoupon;
		}elseif(count($coupons)==1){
			$coupon = reset($coupons);
		}

		if(!empty($coupon)){
			$cartClass->addCoupon(0, $coupon->discount_code);
		}
		if($hikashop_config->get('checkout_legacy', 0)) {
			$app->setUserState( HIKASHOP_COMPONENT.'.entries_fields', $entriesData);
		}else{
			$cart = $cartClass->get(0);
			if(!empty($cart->cart_fields) && is_string($cart->cart_fields))
				$cart->cart_fields = json_decode($cart->cart_fields);
			if(!is_object($cart->cart_fields))
				$cart->cart_fields = new stdClass();
			$cart->cart_fields->_entries = $entriesData;
			$cartClass->save($cart);
		}
		$app->redirect( hikashop_completeLink($url, false, true) );
	}
}

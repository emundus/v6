<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><span class="hikashop_product_price_full">
<?php

if(empty($this->row->prices)){
	echo JText::_('FREE_PRICE');
} else {
	$config =& hikashop_config();

	$first = true;
	echo JText::_('PRICE_BEGINNING');
	foreach($this->row->prices as $i => $price) {
		if($first) $first = false;
		else echo JText::_('PRICE_SEPARATOR');

		if(!empty($this->unit) && isset($price->unit_price)) {
			$price = $price->unit_price;
		}
		if(!isset($price->price_currency_id)) $price->price_currency_id = hikashop_getCurrency();

		$classes = array('hikashop_product_price hikashop_product_price_'.$i);
		if(!empty($this->row->discount)) {
			$classes[]='hikashop_product_price_with_discount';
		}
		echo '<span class="'.implode(' ',$classes).'">';
		if($this->params->get('price_with_tax')){
			echo $this->currencyHelper->format(@$price->price_value_with_tax, $price->price_currency_id);
		}
		if($this->params->get('price_with_tax') == 2) {
			echo JText::_('PRICE_BEFORE_TAX');
		}
		if($this->params->get('price_with_tax') == 2 || !$this->params->get('price_with_tax')) {
			echo $this->currencyHelper->format(@$price->price_value, $price->price_currency_id);
		}
		if($this->params->get('price_with_tax') == 2) {
			echo JText::_('PRICE_AFTER_TAX');
		}
		if((int)$this->params->get('show_original_price', -1) == -1) {
			$defaultParams = $config->get('default_params');
			$this->params->set('show_original_price', $defaultParams['show_original_price']);
		}
		if($this->params->get('show_original_price') && !empty($price->price_orig_value)) {
			echo JText::_('PRICE_BEFORE_ORIG');
			if($this->params->get('price_with_tax')) {
				echo $this->currencyHelper->format($price->price_orig_value_with_tax, $price->price_orig_currency_id);
			}
			if($this->params->get('price_with_tax') == 2) {
				echo JText::_('PRICE_BEFORE_TAX');
			}
			if($this->params->get('price_with_tax') == 2 || !$this->params->get('price_with_tax')) {
				echo $this->currencyHelper->format($price->price_orig_value,$price->price_orig_currency_id);
			}
			if($this->params->get('price_with_tax') == 2) {
				echo JText::_('PRICE_AFTER_TAX');
			}
			echo JText::_('PRICE_AFTER_ORIG');
		}
		echo '</span> ';

		if(!empty($this->row->discount) && isset($price->price_value_without_discount_with_tax)) {
			if($this->params->get('show_discount', 3) == 3) {
				$defaultParams = $config->get('default_params');
				$this->params->set('show_discount', $defaultParams['show_discount']);
			}
			if($this->params->get('show_discount') == 1) {
				echo '<span class="hikashop_product_discount">'.JText::_('PRICE_DISCOUNT_START');
				if(bccomp(sprintf('%F',$this->row->discount->discount_flat_amount), 0, 5) !== 0) {
					if(!$this->unit)
						$this->row->discount->discount_flat_amount = $this->row->discount->discount_flat_amount * $this->row->cart_product_quantity;
					echo $this->currencyHelper->format(-1 * $this->row->discount->discount_flat_amount, $price->price_currency_id);
				} else {
					echo (-1 * $this->row->discount->discount_percent_amount) . '%';
				}
				echo JText::_('PRICE_DISCOUNT_END').'</span>';
			} elseif($this->params->get('show_discount') == 2) {
				echo '<span class="hikashop_product_price_before_discount">'.JText::_('PRICE_DISCOUNT_START');
				if($this->params->get('price_with_tax')) {
					echo $this->currencyHelper->format($price->price_value_without_discount_with_tax, $price->price_currency_id);
				}
				if($this->params->get('price_with_tax') == 2) {
					echo JText::_('PRICE_BEFORE_TAX');
				}
				if($this->params->get('price_with_tax') == 2 || !$this->params->get('price_with_tax')) {
					echo $this->currencyHelper->format($price->price_value_without_discount,$price->price_currency_id);
				}
				if($this->params->get('price_with_tax') == 2) {
					echo JText::_('PRICE_AFTER_TAX');
				}
				if($this->params->get('show_original_price') && !empty($price->price_orig_value_without_discount_with_tax)) {
					echo JText::_('PRICE_BEFORE_ORIG');
					if($this->params->get('price_with_tax')) {
						echo $this->currencyHelper->format($price->price_orig_value_without_discount_with_tax,$price->price_orig_currency_id);
					}
					if($this->params->get('price_with_tax') == 2) {
						echo JText::_('PRICE_BEFORE_TAX');
					}
					if($this->params->get('price_with_tax') == 2 || !$this->params->get('price_with_tax')) {
						echo $this->currencyHelper->format($price->price_orig_value_without_discount,$price->price_orig_currency_id);
					}
					if($this->params->get('price_with_tax') == 2) {
						echo JText::_('PRICE_AFTER_TAX');
					}
					echo JText::_('PRICE_AFTER_ORIG');
				}
				echo JText::_('PRICE_DISCOUNT_END').'</span>';
			}
		}
	}
	echo JText::_('PRICE_END');
}
?></span>

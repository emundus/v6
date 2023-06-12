<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><span class="hikashop_product_price_full">
<?php

$config = hikashop_config();
$price_with_tax = $config->get('price_with_tax','1');
$show_original_price = $this->params->get('show_original_price');
$show_discount = $this->params->get('show_discount');

if(empty($this->row->prices)) {
	echo JText::_('FREE_PRICE');
} else {
	$first = true;
	echo JText::_('PRICE_BEGINNING');
	foreach($this->row->prices as $price) {
		if($first)
			$first=false;
		else
			echo JText::_('PRICE_SEPARATOR');

		if(isset($this->unit) && $this->unit && isset($price->unit_price)) {
			$price = $price->unit_price;
		} elseif($this->row->cart_product_total_quantity <= 0) {
			$price->price_value = 0;
			$price->price_value_with_tax = 0;
		}

		if(!isset($price->price_currency_id)) {
			$price->price_currency_id = hikashop_getCurrency();
		}

		echo '<span class="hikashop_product_price">';

		if($price_with_tax) {
			echo $this->currencyHelper->format(@$price->price_value_with_tax,$price->price_currency_id);
		}

		if($price_with_tax == 2) {
			echo JText::_('PRICE_BEFORE_TAX');
		}

		if($price_with_tax == 2 || !$price_with_tax) {
			echo $this->currencyHelper->format(@$price->price_value,$price->price_currency_id);
		}

		if($price_with_tax == 2) {
			echo JText::_('PRICE_AFTER_TAX');
		}

		if($show_original_price && !empty($price->price_orig_value)) {
			echo JText::_('PRICE_BEFORE_ORIG');
			if($price_with_tax) {
				echo $this->currencyHelper->format($price->price_orig_value_with_tax,$price->price_orig_currency_id);
			}
			if($price_with_tax == 2) {
				echo JText::_('PRICE_BEFORE_TAX');
			}
			if($price_with_tax == 2 || !$price_with_tax) {
				echo $this->currencyHelper->format($price->price_orig_value,$price->price_orig_currency_id);
			}
			if($price_with_tax == 2) {
				echo JText::_('PRICE_AFTER_TAX');
			}
			echo JText::_('PRICE_AFTER_ORIG');
		}
		echo '</span> ';

		if(!empty($this->row->discount) && $show_discount == 1) {

			echo '<span class="hikashop_product_discount">'.JText::_('PRICE_DISCOUNT_START');
			if(bccomp(sprintf('%F',$this->row->discount->discount_flat_amount), 0, 5) !== 0) {
				echo $this->currencyHelper->format( (-1 * $this->row->discount->discount_flat_amount), $price->price_currency_id);
			} else {
				echo (-1 * $this->row->discount->discount_percent_amount) . '%';
			}
			echo JText::_('PRICE_DISCOUNT_END').'</span>';

		} elseif(!empty($this->row->discount) && $show_discount == 2) {
			echo '<span class="hikashop_product_price_before_discount">'.JText::_('PRICE_DISCOUNT_START');
			if($price_with_tax) {
				echo $this->currencyHelper->format($price->price_value_without_discount_with_tax,$price->price_currency_id);
			}
			if($price_with_tax == 2) {
				echo JText::_('PRICE_BEFORE_TAX');
			}
			if($price_with_tax == 2 || !$price_with_tax) {
				echo $this->currencyHelper->format($price->price_value_without_discount,$price->price_currency_id);
			}
			if($price_with_tax == 2){
				echo JText::_('PRICE_AFTER_TAX');
			}
			if($show_original_price && !empty($price->price_orig_value_without_discount_with_tax)) {
				echo JText::_('PRICE_BEFORE_ORIG');
				if($price_with_tax){
					echo $this->currencyHelper->format($price->price_orig_value_without_discount_with_tax,$price->price_orig_currency_id);
				}
				if($price_with_tax == 2) {
					echo JText::_('PRICE_BEFORE_TAX');
				}
				if($price_with_tax == 2 || !$price_with_tax) {
					echo $this->currencyHelper->format($price->price_orig_value_without_discount,$price->price_orig_currency_id);
				}
				if($price_with_tax == 2) {
					echo JText::_('PRICE_AFTER_TAX');
				}
				echo JText::_('PRICE_AFTER_ORIG');
			}
			echo JText::_('PRICE_DISCOUNT_END').'</span>';
		}
	}
	echo JText::_('PRICE_END');
}
?>
</span>

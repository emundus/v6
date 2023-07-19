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
$config =& hikashop_config();
$class = (!empty($this->row->prices) && count($this->row->prices) > 1) ? ' hikashop_product_several_prices' : '';
if(!empty($this->row->has_options))
	$class.=' hikashop_product_has_options';

if(isset($this->element->main->product_msrp) && !(@$this->row->product_msrp > 0.0))
	$this->row->product_msrp = $this->element->main->product_msrp;
if(isset($this->row->product_msrp) && $this->row->product_msrp > 0.0 && hikaInput::get()->getCmd('layout') == 'show' && $this->params->get('from_module','') == '') {
	$show_msrp = true;
	$mainCurr = $this->currencyHelper->mainCurrency();
	$currCurrency = hikashop_getCurrency();
	if($currCurrency == $mainCurr && !empty($this->row->prices)) {
		$price = reset($this->row->prices);
		if(!empty($this->unit) && isset($price->unit_price))
			$price =& $price->unit_price;
		if($this->row->product_msrp == $price->price_value_with_tax) {
			$show_msrp = false;
		}
		unset($price);
	}
}

if(!empty($show_msrp)) {
?>
	<span class="hikashop_product_msrp_price hikashop_product_price_full">
		<span class="hikashop_product_msrp_price_title"><?php
			echo JText::_('PRODUCT_MSRP_BEFORE');
		?></span>
		<span class="hikashop_product_price"><?php
			$mainCurr = $this->currencyHelper->mainCurrency();
			$currCurrency = hikashop_getCurrency();
			$msrpCurrencied = $this->currencyHelper->convertUniquePrice($this->row->product_msrp, $mainCurr, $currCurrency);
			if($msrpCurrencied == $this->row->product_msrp)
				echo $this->currencyHelper->format($this->row->product_msrp, $mainCurr);
			else {
				echo $this->currencyHelper->format($msrpCurrencied, $currCurrency);
				if($this->params->get('show_original_price'))
					echo ' ('.$this->currencyHelper->format($this->row->product_msrp, $mainCurr).')';
			}
		?></span>
	</span>
<?php
}
?>
	<span class="hikashop_product_price_full<?php echo $class; ?>"><?php

	if(empty($this->row->prices)) {
		echo JText::_('FREE_PRICE');
	} else {
		$first = true;
		echo JText::_('PRICE_BEGINNING');
		$i = 0;

		if(!empty($show_msrp)) {
			echo '<span class="hikashop_product_our_price_title">'.JText::_('PRODUCT_MSRP_AFTER').'</span> ';
		}

		if($this->params->get('price_with_tax', 3) == 3) {
			$this->params->set('price_with_tax', (int)$config->get('price_with_tax'));
		}

		$microDataForCurrentProduct = false;

		foreach($this->row->prices as $k => $price) {
			if($first)$first=false;
			else echo JText::_('PRICE_SEPARATOR');
			if(!empty($this->unit) && isset($price->unit_price)) {
				$price =& $price->unit_price;
			}
			if(empty($price->price_currency_id))
				continue;
			$start = JText::_('PRICE_BEGINNING_'.$i);
			if($start != 'PRICE_BEGINNING_'.$i) {
				echo $start;
			}
			if(isset($price->price_min_quantity) && empty($this->cart_product_price) && $price->price_min_quantity > 1) {
				echo '<span class="hikashop_product_price_with_min_qty hikashop_product_price_for_at_least_'.$price->price_min_quantity.'">';
			}

			$classes = array('hikashop_product_price hikashop_product_price_'.$i);
			if(!empty($this->row->discount)) {
				$classes[]='hikashop_product_price_with_discount';
			}

			if(!empty($this->row->discount)) {
				if(in_array($this->params->get('show_discount'), array(1, 4))) {
					echo '<span class="hikashop_product_discount">'.JText::_('PRICE_DISCOUNT_START').'<span class="hikashop_product_discount_amount">';
					if(bccomp(sprintf('%F',$this->row->discount->discount_flat_amount), 0, 5) !== 0) {
						echo $this->currencyHelper->format( -1 * $this->row->discount->discount_flat_amount, $price->price_currency_id);
					} elseif(bccomp(sprintf('%F',$this->row->discount->discount_percent_amount), 0, 5) !== 0) {
						echo -1*$this->row->discount->discount_percent_amount.'%';
					}
					echo '</span>'.JText::_('PRICE_DISCOUNT_END').'</span>';
				}
				if(in_array($this->params->get('show_discount'), array(2, 4))) {
					echo '<span class="hikashop_product_price_before_discount">'.JText::_('PRICE_DISCOUNT_START').'<span class="hikashop_product_price_before_discount_amount">';
					if($this->params->get('price_with_tax')){
						echo $this->currencyHelper->format($price->price_value_without_discount_with_tax, $price->price_currency_id);
					}
					if($this->params->get('price_with_tax') == 2) {
						echo JText::_('PRICE_BEFORE_TAX');
					}
					if($this->params->get('price_with_tax') == 2 || !$this->params->get('price_with_tax')) {
						echo $this->currencyHelper->format($price->price_value_without_discount, $price->price_currency_id);
					}
					if($this->params->get('price_with_tax') == 2) {
						echo JText::_('PRICE_AFTER_TAX');
					}
					if($this->params->get('show_original_price') && !empty($price->price_orig_value_without_discount_with_tax)) {
						echo JText::_('PRICE_BEFORE_ORIG');
						if($this->params->get('price_with_tax')) {
							echo $this->currencyHelper->format($price->price_orig_value_without_discount_with_tax, $price->price_orig_currency_id);
						}
						if($this->params->get('price_with_tax') == 2) {
							echo JText::_('PRICE_BEFORE_TAX');
						}
						if($this->params->get('price_with_tax') == 2 || !$this->params->get('price_with_tax') && !empty($price->price_orig_value_without_discount)) {
							echo $this->currencyHelper->format($price->price_orig_value_without_discount, $price->price_orig_currency_id);
						}
						if($this->params->get('price_with_tax') == 2) {
							echo JText::_('PRICE_AFTER_TAX');
						}
						echo JText::_('PRICE_AFTER_ORIG');
					}
					echo '</span>'.JText::_('PRICE_DISCOUNT_END').'</span>';
				} elseif($this->params->get('show_discount') == 3) {

				}
			}

			$attributes = '';
			if(!empty($this->element->product_id) && !$microDataForCurrentProduct) {
				$round = $this->currencyHelper->getRounding($price->price_currency_id, true);
				$prefix = 'data-';
				$microDataForCurrentProduct = true;
				if(empty($this->displayed_price_microdata)) {
					$this->displayed_price_microdata = true;
					$prefix = '';
				}
				if($this->params->get('price_with_tax')) {
					$price_attributes = str_replace(',','.',$this->currencyHelper->round($price->price_value_with_tax, $round, 0, true));
				} else {
					$price_attributes = str_replace(',','.',$this->currencyHelper->round($price->price_value, $round, 0, true));
				}
				$this->itemprop_price = new stdClass();
				$this->itemprop_price = $price_attributes .'';
			}


			echo '<span class="'.implode(' ',$classes).'">';

			if($this->params->get('price_with_tax')) {
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
			if($this->params->get('show_original_price') && !empty($price->price_orig_value)) {
				echo JText::_('PRICE_BEFORE_ORIG');
				if($this->params->get('price_with_tax')) {
					echo $this->currencyHelper->format($price->price_orig_value_with_tax, $price->price_orig_currency_id);
				}
				if($this->params->get('price_with_tax') == 2) {
					echo JText::_('PRICE_BEFORE_TAX');
				}
				if($this->params->get('price_with_tax') == 2 || !$this->params->get('price_with_tax')){
					echo $this->currencyHelper->format($price->price_orig_value, $price->price_orig_currency_id);
				}
				if($this->params->get('price_with_tax') == 2) {
					echo JText::_('PRICE_AFTER_TAX');
				}
				echo JText::_('PRICE_AFTER_ORIG');
			}
			echo '</span> ';
			if(isset($price->price_min_quantity) && empty($this->cart_product_price) && $this->params->get('per_unit', 1)) {
				if($price->price_min_quantity > 1) {
					echo '<span class="hikashop_product_price_per_unit_x">'.JText::sprintf('PER_UNIT_AT_LEAST_X_BOUGHT',$price->price_min_quantity).'</span>';
				} else {
					echo '<span class="hikashop_product_price_per_unit">'.JText::_('PER_UNIT').'</span>';
				}
			}
			if($this->params->get('show_price_weight')){
				if(!empty($this->element->product_id) && isset($this->row->product_weight) && bccomp(sprintf('%F',$this->row->product_weight), 0, 3)) {

					echo JText::_('PRICE_SEPARATOR').'<span class="hikashop_product_price_per_weight_unit">';
					if($this->params->get('price_with_tax')){
						$weight_price = $price->price_value_with_tax / $this->row->product_weight;
						echo $this->currencyHelper->format($weight_price, $price->price_currency_id).' / '.JText::_($this->row->product_weight_unit);
					}
					if($this->params->get('price_with_tax') == 2) {
						echo JText::_('PRICE_BEFORE_TAX');
					}
					if($this->params->get('price_with_tax') == 2 || !$this->params->get('price_with_tax')) {
						$weight_price = $price->price_value / $this->row->product_weight;
						echo $this->currencyHelper->format($weight_price, $price->price_currency_id).' / '.JText::_($this->row->product_weight_unit);
					}
					if($this->params->get('price_with_tax') == 2) {
						echo JText::_('PRICE_AFTER_TAX');
					}
					echo '</span>';
				}
			}
			if(isset($price->price_min_quantity) && empty($this->cart_product_price) && $price->price_min_quantity > 1) {
				echo '</span>';
			}
			$end = JText::_('PRICE_ENDING_'.$i);
			if($end != 'PRICE_ENDING_'.$i) {
				echo $end;
			}
			$i++;
		}
		echo JText::_('PRICE_END');
	}
	?></span>

<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(empty($this->ajax)) { ?>
<div id="hikashop_checkout_payment_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" data-checkout-step="<?php echo $this->step; ?>" data-checkout-pos="<?php echo $this->module_position; ?>" class="hikashop_checkout_payment">
<?php } ?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner"></div>

<?php

$this->checkoutHelper->displayMessages('payment');
$cart = $this->checkoutHelper->getCart();

if(!empty($cart->usable_methods->payment)) {
	if(!empty($this->options['show_title'])) {
?>
<legend><?php echo JText::_('HIKASHOP_PAYMENT_METHOD');?></legend>
<?php
	}

	if($this->options['payment_selector'] == 2) {
?>
	<fieldset class="hika_payment_field hikashop_checkout_payment_block">
		<select id="hikashop_payment_selector_<?php echo $this->step.'_'.$this->module_position; ?>"
				name="checkout[payment][id]"
				class="hikashop_field_dropdown"
				onchange="window.checkout.paymentSelected(this.options[this.selectedIndex]);">
<?php
		foreach($cart->usable_methods->payment as $payment) {
			$selected = (!empty($cart->payment) && $payment->payment_id == $cart->payment->payment_id);
			$input_data = array(
				'step' => $this->step,
				'pos' => $this->module_position,
				'block' => 'payment',
				'type' => $payment->payment_type,
				'id' => (int)$payment->payment_id,
			);
			$payment_name = Jtext::sprintf('CHECKCOUT_DROPDOWN_PAYMENT_NAME', $payment->payment_name, $this->checkoutHelper->getDisplayPrice($payment, 'payment', $this->options));

			?><option value="<?php echo $payment->payment_id; ?>"<?php echo ($selected ? ' selected="selected"' : ''); ?>
				data-hk-checkout="<?php echo $this->escape(json_encode($input_data)); ?>">
				<?php echo $payment_name;?>
			</option>
<?php
		}
?>
		</select>
	</fieldset>
<?php
	}
	else {
?>
<table style="width:100%" class="hikashop_payment_methods_table table table-bordered table-striped table-hover">
<?php
	}
	foreach($cart->usable_methods->payment as $payment) {
		$input_id = 'payment_radio_'.$this->step.'_'.$this->module_position.'__'.$payment->payment_type.'_'.$payment->payment_id;
		$container_id = 'hikashop_checkout_payment_'.$this->step.'_'.$this->module_position.'__'.$payment->payment_id;
		$selected = (!empty($cart->payment) && $payment->payment_id == $cart->payment->payment_id);

		if(!empty($this->options['read_only']) && !$selected)
			continue;
		$input_data = array(
			'step' => $this->step,
			'pos' => $this->module_position,
			'block' => 'payment',
			'type' => $payment->payment_type,
			'id' => (int)$payment->payment_id,
		);
?>
<tr><td>
<?php
		if($this->options['payment_selector'] != 2) {
			if(empty($this->options['read_only'])) {
?>
	<input class="hikashop_checkout_payment_radio" type="radio" name="checkout[payment][id]" id="<?php echo $input_id; ?>" data-hk-checkout="<?php echo $this->escape(json_encode($input_data)); ?>" onchange="window.checkout.paymentSelected(this);" value="<?php echo $payment->payment_id;?>"<?php echo ($selected ? ' checked="checked"' : ''); ?>/>
<?php
			}
?>
	<label for="<?php echo $input_id; ?>" style="cursor:pointer;">
		<span class="hikashop_checkout_payment_name"><?php echo $payment->payment_name;?></span>
	</label>
	<span class="hikashop_checkout_payment_cost"><?php
		echo $this->checkoutHelper->getDisplayPrice($payment, 'payment', $this->options);
	?></span>
<?php
			if(!empty($payment->payment_images)) {
?>
	<span class="hikashop_checkout_payment_images">
<?php
				$images = explode(',', $payment->payment_images);
				foreach($images as $image) {
					$img = $this->checkoutHelper->getPluginImage($image, 'payment');
					if(empty($img))
						continue;
?>
		<img src="<?php echo $img->url; ?>" alt=""/>
<?php
				}
?>
	</span>
<?php
			}
?>
<?php
			if(!empty($payment->payment_description)) {
?>
	<div class="hikashop_checkout_payment_description"><?php
		echo $this->getDescription($payment);
	?></div>
<?php
			}
		}
?>
<?php
		if(empty($this->options['read_only']) && !empty($payment->ask_cc)) {
?>
	<div id="<?php echo $container_id; ?>__card" class="hikashop_checkout_payment_card" style="<?php echo $selected ? '' : ' display:none;'; ?>"><?php
			$cc_data = $this->checkoutHelper->getCreditCard($payment);
			if(empty($cc_data))
				hikashop_loadJsLib('creditcard');
?>
		<dl class="hika_options large">
<?php
			if(!empty($payment->ask_owner)) {
?>
			<dt><?php echo JText::_('CREDIT_CARD_OWNER'); ?></dt>
			<dd>
<?php
				if(empty($cc_data)) {
?>
				<input type="text" autocomplete="off" name="checkout[payment][card][<?php echo $payment->payment_id;?>][owner]" value="" />
<?php
				} else {
?>
				<span class="hikashop_checkout_payment_card_details"><?php echo $this->escape((string)@$cc_data->owner); ?></span>
<?php
				}
?>
			</dd>
<?php
			}
?>
<?php
			if(!empty($payment->ask_cctype)) {
?>
			<dt><?php echo JText::_('CARD_TYPE'); ?></dt>
			<dd><?php
				if(empty($cc_data)) {
					$values = array();
					foreach($payment->ask_cctype as $k => $v) {
						$values[] = JHTML::_('select.option', $k, $v);
					}
					echo JHTML::_('select.genericlist', $values, 'checkout[payment][card]['.$payment->payment_id.'][type]', '', 'value', 'text', '');
					} else {
?>
				<span class="hikashop_checkout_payment_card_details"><?php
						if(isset($payment->ask_cctype[@$cc_data->type]))
							echo $this->escape($payment->ask_cctype[@$cc_data->type]);
						else
							echo $this->escape((string)@$cc_data->type);
				?></span>
<?php
					}
			?></dd>
<?php
			}
?>
			<dt><label for="hk_co_p_c_n_<?php echo $payment->payment_id; ?>"><?php echo JText::_('CREDIT_CARD_NUMBER'); ?></label></dt>
			<dd>
<?php
			if(empty($cc_data)) {
?>
			<input type="text" autocomplete="off" name="checkout[payment][card][<?php echo $payment->payment_id; ?>][num]" value="" onchange="if(!hikashopCheckCreditCard(this.value)){ this.value = ''; }" id="hk_co_p_c_<?php echo $payment->payment_id; ?>"/>
<?php
			} else {
?>
			<span class="hikashop_checkout_payment_card_details"><?php echo $this->escape((string)@$cc_data->num); ?></span>
<?php
			}
?>
			</dd>
			<dt><label for="hk_co_p_c_e_<?php echo $payment->payment_id; ?>"><?php echo JText::_('EXPIRATION_DATE'); ?></label></dt>
			<dd>
<?php
			if(empty($cc_data)) {
?>
			<input type="text" autocomplete="off" name="checkout[payment][card][<?php echo $payment->payment_id; ?>][mm]" class="card_expiration_date_input" maxlength="2" size="2" value="" placeholder="<?php $mm = JText::_('CC_MM'); if($mm=='CC_MM') $mm = JText::_('MM'); echo $mm;?>" id="hk_co_p_c_e_<?php echo $payment->payment_id; ?>"/>
			/
			<input type="text" autocomplete="off" name="checkout[payment][card][<?php echo $payment->payment_id; ?>][yy]" class="card_expiration_date_input" maxlength="2" size="2" value="" placeholder="<?php echo JText::_('YY');?>" />
<?php
			} else {
?>
			<span class="hikashop_checkout_payment_card_details"><?php echo $this->escape((string)@$cc_data->mm) . '/' . $this->escape((string)@$cc_data->yy); ?></span>
<?php
			}
?>
			</dd>
<?php
			if(!empty($payment->ask_ccv)) {
				hikashop_loadJsLib('tooltip');
?>
			<dt><label for="hk_co_p_c_v_<?php echo $payment->payment_id; ?>" data-toggle="hk-tooltip" data-title="<?php echo htmlspecialchars('<strong>'.JText::_('CVC_TOOLTIP_TITLE').'</strong><br/>'.JText::_('CVC_TOOLTIP_TEXT'), ENT_COMPAT, 'UTF-8'); ?>"><?php echo JText::_('CARD_VALIDATION_CODE'); ?></label></dt>
			<dd>
<?php
				if(empty($cc_data)) {
?>
				<input type="text" autocomplete="off" name="checkout[payment][card][<?php echo $payment->payment_id;?>][ccv]" maxlength="4" size="4" value="" id="hk_co_p_c_v_<?php echo $payment->payment_id; ?>"/>
<?php
				} else {
?>
				<span class="hikashop_checkout_payment_card_details"><?php echo $this->escape((string)@$cc_data->ccv); ?></span>
<?php
				}
?>
			</dd>
<?php
			}
?>
		</dl>
<?php
			if(empty($cc_data)) {
?>
		<div class="hikashop_checkout_payment_submit">
			<button class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn_checkout_payment_submit" onclick="return window.checkout.submitPayment(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>);"><?php echo JText::_('HIKA_SUBMIT'); ?></button>
		</div>
<?php
			} else {
?>
		<div class="hikashop_checkout_payment_submit">
			<button class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn_checkout_payment_reset" onclick="return window.checkout.resetPayment(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo $payment->payment_id; ?>);"><?php echo JText::_('RESET'); ?></button>
		</div>
<?php
			}
?>
	</div>
<?php
		}
?>
<?php
		if(empty($this->options['read_only']) && !empty($payment->custom_html)) {
?>
	<div id="<?php echo $container_id; ?>__custom" class="hikashop_checkout_payment_custom" style="<?php echo $selected ? '' : ' display:none;'; ?>">
<?php
			echo $this->checkoutHelper->getCustomHtml($payment->custom_html, 'checkout[payment][custom]['.$payment->payment_id.']');

			if(empty($payment->custom_html_no_btn)) {
?>
		<div class="hikashop_checkout_payment_submit">
			<button class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn_checkout_payment_submit" id="hikabtn_checkout_payment_submit_p<?php echo $payment->payment_id; ?>" onclick="return window.checkout.submitCustomPayment('<?php echo $payment->payment_type; ?>',<?php echo (int)$payment->payment_id; ?>,<?php echo $this->step; ?>,<?php echo $this->module_position; ?>);"><?php echo JText::_('HIKA_SUBMIT'); ?></button>
		</div>
<?php
			}
?>
	</div>
<?php
		}
?>
</td></tr>
<?php
		}
?>
</table>
<?php
}

if(empty($this->ajax)) { ?>
</div>
<script type="text/javascript">
if(!window.checkout) window.checkout = {};
window.checkout.selectedPayment = <?php echo (int)@$cart->payment->payment_id; ?>;

window.Oby.registerAjax(['checkout.payment.updated','cart.updated', 'checkout.cart.updated'], function(params){
	if(params && (params.cart_empty || (params.resp && params.resp.empty))) return;
	if(window.checkout.isSource(params, <?php echo (int)$this->step; ?>, <?php echo (int)$this->module_position; ?>))
		return;
	window.checkout.refreshPayment(<?php echo (int)$this->step; ?>, <?php echo (int)$this->module_position; ?>);
});
window.Oby.registerAjax('checkoutFormSubmit', function(params){
	var needSubmit = window.Oby.fireAjax('custompayment.needsubmit', {'step': <?php echo (int)$this->step; ?>, 'payment_id': window.checkout.selectedPayment});
	if(needSubmit === false || needSubmit.length == 0)
		return;
	window.checkout.submitCustomPayment(needSubmit[0],window.checkout.selectedPayment,<?php echo $this->step; ?>,<?php echo $this->module_position; ?>);
	return true;
});
window.checkout.refreshPayment = function(step, id) { return window.checkout.refreshBlock('payment', step, id); };
window.checkout.submitPayment = function(step, id) { return window.checkout.submitBlock('payment', step, id); };
window.checkout.submitCustomPayment = function(name, id, step, pos) {
	var ret = window.Oby.fireAjax('custompayment.submit', {method: name, payment_id: id, step: step, pos: pos});
	if(ret === false || ret.length == 0) return window.checkout.submitBlock('payment', step, pos);
	return false;
};
window.checkout.paymentSelected = function(el) {
	var data = window.Oby.evalJSON(el.getAttribute('data-hk-checkout')),
		prefix = 'hikashop_checkout_payment_' + data.step + '_' + data.pos + '__',
		d = document;
	window.checkout.setLoading(null, true);

	var url = "<?php echo hikashop_completeLink('checkout&task=submitblock&blocktask=payment'.$this->cartIdParam.'&Itemid='.$this->itemid, 'ajax', false, true); ?>",
		formData = 'cid=' + encodeURIComponent(data.step) + '&blockpos=' + encodeURIComponent(data.pos) + '&selectionOnly=1&' + encodeURI('checkout[payment][id]') + '=' + encodeURIComponent(data.id) + '&' + encodeURI(window.checkout.token)+'=1';
	window.Oby.xRequest(url, {mode:"POST", data: formData}, function(x,p) {
		window.checkout.setLoading(null, false);
		var r = window.Oby.evalJSON(x.responseText);
		if(r && r.ret > 0) {
			window.checkout.selectedPayment = data.id;
		}
		if(r && r.events)
			window.checkout.processEvents(r.events, {step:<?php echo (int)$this->step; ?>, pos:<?php echo (int)$this->module_position; ?>});
	});

	if(window.checkout.selectedPayment > 0) {
		var b = prefix + window.checkout.selectedPayment;
		window.hikashop.setArrayDisplay([b + '__card', b + '__custom'], false);
	}

	var b = prefix + data.id;
	window.hikashop.setArrayDisplay([b + '__card', b + '__custom'], true);
};
window.checkout.resetPayment = function(step, pos, payment_id) {
	var formData = encodeURI('checkout[payment][id]') + '=' + encodeURIComponent(payment_id) + '&' + encodeURI('checkout[payment][card]['+payment_id+']') + '=reset';
	return window.checkout.submitBlock('payment', step, pos, formData);
};

var ccHikaErrors = {
	3: "<?php echo JText::_('CREDIT_CARD_INVALID', true); ?>",
	5: "<?php echo JText::_('CREDIT_CARD_EXPIRED', true); ?>"
};
</script>
<?php }

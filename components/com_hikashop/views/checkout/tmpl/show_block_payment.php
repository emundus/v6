<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(empty($this->ajax)) { ?>
<div id="hikashop_checkout_payment_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" class="hikashop_checkout_payment">
<?php } ?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner"></div>
<?php

$this->checkoutHelper->displayMessages('payment');
$cart = $this->checkoutHelper->getCart();

if(!empty($cart->usable_methods->payment)) {
?>
<table style="width:100%" class="hikashop_payment_methods_table table table-bordered table-striped table-hover">
<?php
	foreach($cart->usable_methods->payment as $payment) {
		$input_id = 'payment_radio_'.$this->step.'_'.$this->module_position.'__'.$payment->payment_type.'_'.$payment->payment_id;
		$container_id = 'hikashop_checkout_payment_'.$this->step.'_'.$this->module_position.'__'.$payment->payment_id;
		$selected = (!empty($cart->payment) && $payment->payment_id == $cart->payment->payment_id);

		$input_data = array(
			'step' => $this->step,
			'pos' => $this->module_position,
			'block' => 'payment',
			'type' => $payment->payment_type,
			'id' => (int)$payment->payment_id,
		);
?>
<tr><td>
	<input class="hikashop_checkout_payment_radio" type="radio" name="checkout[payment][id]" id="<?php echo $input_id; ?>" data-hk-checkout="<?php echo $this->escape(json_encode($input_data)); ?>" onchange="window.checkout.paymentSelected(this);" value="<?php echo $payment->payment_id;?>"<?php echo ($selected ? ' checked="checked"' : ''); ?>/>
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
		echo $payment->payment_description;
	?></div>
<?php
		}
?>
<?php
		if(!empty($payment->ask_cc)) {
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
				<span class="hikashop_checkout_payment_card_details"><?php echo $this->escape(@$cc_data->owner); ?></span>
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
						echo $this->escape(@$cc_data->type);
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
				<input type="text" autocomplete="off" name="checkout[payment][card][<?php echo $payment->payment_id; ?>][num]" value="" onchange="if(!hikashopCheckCreditCard(this.value)){ this.value = '';}" id="hk_co_p_c_<?php echo $payment->payment_id; ?>"/>
<?php
				} else {
?>
				<span class="hikashop_checkout_payment_card_details"><?php echo $this->escape(@$cc_data->num); ?></span>
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
				<span class="hikashop_checkout_payment_card_details"><?php echo $this->escape(@$cc_data->mm) . '/' . $this->escape(@$cc_data->yy); ?></span>
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
				<span class="hikashop_checkout_payment_card_details"><?php echo $this->escape(@$cc_data->ccv); ?></span>
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
		if(!empty($payment->custom_html)) {
?>
	<div id="<?php echo $container_id; ?>__custom" class="hikashop_checkout_payment_custom" style="<?php echo $selected ? '' : ' display:none;'; ?>">
<?php
		echo $this->checkoutHelper->getCustomHtml($payment->custom_html, 'checkout[payment][custom]['.$payment->payment_id.']');
?>
		<div class="hikashop_checkout_payment_submit">
			<button class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn_checkout_payment_submit" onclick="return window.checkout.submitPayment(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>);"><?php echo JText::_('HIKA_SUBMIT'); ?></button>
		</div>
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
window.checkout.selectedPayment = <?php echo (int)$cart->payment->payment_id; ?>;

window.Oby.registerAjax(['checkout.payment.updated','cart.updated'], function(params){
	if(params && (params.cart_empty || (params.resp && params.resp.empty))) return;
	window.checkout.refreshPayment(<?php echo (int)$this->step; ?>, <?php echo (int)$this->module_position; ?>);
});
window.checkout.refreshPayment = function(step, id) { return window.checkout.refreshBlock('payment', step, id); };
window.checkout.submitPayment = function(step, id) { return window.checkout.submitBlock('payment', step, id); };
window.checkout.paymentSelected = function(el) {
	var data = window.Oby.evalJSON(el.getAttribute('data-hk-checkout')),
		prefix = 'hikashop_checkout_payment_' + data.step + '_' + data.pos + '__',
		el = null, d = document;

	var url = "<?php echo hikashop_completeLink('checkout&task=submitblock&blocktask=payment&cid=HIKACID&blockpos=HIKAPOS&tmpl=ajax', false, false, true); ?>".replace("HIKACID", data.step).replace("HIKAPOS", data.pos),
		formData = 'selectionOnly=1&' + encodeURI('checkout[payment][id]') + '=' + encodeURIComponent(data.id) + '&' + encodeURI(window.checkout.token)+'=1';
	window.Oby.xRequest(url, {mode:"POST", data: formData}, function(x,p) {
		var r = window.Oby.evalJSON(x.responseText);
		if(r && r.ret > 0) {
			window.checkout.selectedPayment = data.id;
		}
		if(r && r.events)
			window.checkout.processEvents(r.events);
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
</script>
<?php }

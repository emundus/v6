<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.6.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2022 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(empty($this->ajax)) { ?>
<div id="hikashop_checkout_coupon_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" data-checkout-step="<?php echo $this->step; ?>" data-checkout-pos="<?php echo $this->module_position; ?>" class="hikashop_checkout_coupon em-mt-32">
<?php } ?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner"></div>

<?php
	$this->checkoutHelper->displayMessages('coupon');

	$cart = $this->checkoutHelper->getCart();
	if(empty($cart->coupon)) {
		if($cart->full_total->prices[0]->price_value_with_tax > 0.001) {
?>
    <h5><?php echo JText::_('HIKASHOP_COUPON_TITLE') ?></h5>
	<label for="hikashop_checkout_coupon_input_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>"></label>
	<div class="em-flex-row">
		<input class="hikashop_checkout_coupon_field" id="hikashop_checkout_coupon_input_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" type="text" name="checkout[coupon]" value=""/>
        <button style="height: 41px" type="submit" onclick="return window.checkout.submitCoupon(<?php echo $this->step.','.$this->module_position; ?>);" class="<?php echo $this->config->get('css_button','hikabtn'); ?> em-transparent-button hikabtn_checkout_coupon_add"><?php
            echo JText::_('ADD');
            ?></button>
		</div>
<?php
		}
	} else {
		echo JText::sprintf('HIKASHOP_COUPON_LABEL', @$cart->coupon->discount_code);
		if(empty($cart->cart_params->coupon_autoloaded)) {
			global $Itemid;
			$url_itemid = '';
			if(!empty($Itemid))
				$url_itemid = '&Itemid=' . $Itemid;
?>
	<a href="#removeCoupon" onclick="return window.checkout.removeCoupon(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>);" title="<?php echo JText::_('REMOVE_COUPON'); ?>">
		<i class="fas fa-trash"></i>
	</a>
<?php
		}
	}

	if(empty($this->ajax)) { ?>
</div>
<script type="text/javascript">
if(!window.checkout) window.checkout = {};
window.Oby.registerAjax(['checkout.coupon.updated','cart.updated', 'checkout.cart.updated'], function(params){
	if(params && (params.cart_empty || (params.resp && params.resp.empty))) return;
	window.checkout.refreshCoupon(<?php echo (int)$this->step; ?>, <?php echo (int)$this->module_position; ?>);
});
window.checkout.refreshCoupon = function(step, id) { return window.checkout.refreshBlock('coupon', step, id); };
window.checkout.submitCoupon = function(step, id) {
	var el = document.getElementById('hikashop_checkout_coupon_input_' + step + '_' + id);
	if(!el)
		return false;
	if(el.value == '') {
		window.Oby.addClass(el, 'hikashop_red_border');
		return false;
	}
	return window.checkout.submitBlock('coupon', step, id);
};
window.checkout.removeCoupon = function(step, id) {
	window.checkout.submitBlock('coupon', step, id, {'checkout[removecoupon]':1});
	return false;
};
</script>
<?php }

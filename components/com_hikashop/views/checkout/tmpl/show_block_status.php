<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(empty($this->ajax)) { ?>
<div id="hikashop_checkout_status_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" data-checkout-step="<?php echo $this->step; ?>" data-checkout-pos="<?php echo $this->module_position; ?>" class="hikashop_checkout_status">
<?php } ?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner small_spinner"></div>
<?php
	$cart = $this->checkoutHelper->getCart();

	$array = array();
	if(!empty($cart->shipping)) {
		$names = array();
		foreach($cart->shipping as $shipping) {
			$names[] = $shipping->shipping_name;
		}
		$array[] = JText::sprintf('HIKASHOP_SHIPPING_METHOD_CHOSEN', '<span class="label label-info">'.implode('</span> <span class="label label-info">', $names).'</span>');
	}

	if(!empty($cart->payment))
		$array[] = JText::sprintf('HIKASHOP_PAYMENT_METHOD_CHOSEN', '<span class="label label-info">'.$cart->payment->payment_name.'</span>');

	echo implode('<br/>', $array);

	if(empty($this->ajax)) { ?>
</div>
<script type="text/javascript">
if(!window.checkout) window.checkout = {};
window.Oby.registerAjax(['checkout.shipping.updated','checkout.payment.updated'], function(params){
	window.checkout.refreshStatus(<?php echo (int)$this->step; ?>, <?php echo (int)$this->module_position; ?>);
});
window.checkout.refreshStatus = function(step, id) { return window.checkout.refreshBlock('status', step, id); };
</script>
<?php }

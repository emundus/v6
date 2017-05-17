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
<div id="hikashop_checkout_shipping_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" class="hikashop_checkout_shipping">
<?php } ?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner"></div>
<?php

$this->checkoutHelper->displayMessages('shipping');
$cart = $this->checkoutHelper->getCart();

if(!empty($cart->usable_methods->shipping)) {
	$several_groups = (count($cart->shipping_groups) > 1);

	if($several_groups && !empty($this->options['show_shipping_products']))
		hikashop_loadJsLib('tooltip');
	if($several_groups && empty($this->imageHelper))
		$this->imageHelper = hikashop_get('helper.image');

	foreach($cart->shipping_groups as $shipping_group_key => $group) {
?>
<div class="hikashop_shipping_group">
<?php
		if(!empty($group->name) || $several_groups) {
?>
	<p class="hikashop_shipping_group_name"><?php
		if(!empty($group->name))
			echo $group->name;
		elseif($several_groups)
			echo JText::_('SHIPPING_INFORMATION');
	?></p>
<?php
		}

		if($several_groups && !empty($this->options['show_shipping_products'])) {
?>
	<ul class="hikashop_shipping_products">
<?php
			foreach($group->products as $product) {
				if($product->cart_product_quantity <= 0)
					continue;

				$thumbnail = '';
				if(!empty($product->images))
					$thumbnail = $product->images[0]->file_path;
				$image = $this->imageHelper->getThumbnail($thumbnail, array(50,50), array('default' => true), true);
				$name = $this->escape(strip_tags($product->product_name));
?>
		<li class="hikashop_shipping_product"><img src="<?php echo $image->url; ?>" alt="<?php echo $name; ?>" data-toggle="hk-tooltip" data-title="<?php echo $name; ?>"/></li>
<?php
			}
?>
	</ul>
<?php
		}
?>
	<table style="width:100%" class="hikashop_shipping_methods_table table table-bordered table-striped table-hover">
<?php
		foreach($cart->usable_methods->shipping as $shipping) {
			if($several_groups && $shipping->shipping_warehouse_id != $shipping_group_key)
				continue;

			$selected = false; // (!empty($cart->shipping) && $shipping->shipping_id == $cart->shipping->shipping_id);
			if(!empty($cart->shipping)) {
				$shipping_id = is_numeric($shipping->shipping_id) ? (int)$shipping->shipping_id : $shipping->shipping_id;
				foreach($cart->shipping as $s) {
					$s_id = is_numeric($s->shipping_id) ? (int)$s->shipping_id : $s->shipping_id;
					if($s_id === $shipping_id && (!$several_groups || $s->shipping_warehouse_id === $shipping->shipping_warehouse_id)) {
						$selected = true;
						break;
					}
				}
			}

			$input_id = 'shipping_radio_'.$this->step.'_'.$this->module_position.'__'.$shipping_group_key.'__'.$shipping->shipping_type.'_'.$shipping->shipping_id;
			$container_id = 'hikashop_checkout_shipping_'.$this->step.'_'.$this->module_position.'__'.$shipping_group_key.'__'.$shipping->shipping_id;
			$input_data = array(
				'step' => $this->step,
				'pos' => $this->module_position,
				'block' => 'shipping',
				'type' => $shipping->shipping_type,
				'warehouse' => $shipping_group_key,
				'id' => $shipping->shipping_id,
			);
?>
	<tr><td>
		<input class="hikashop_checkout_shipping_radio" type="radio" name="checkout[shipping][<?php echo $shipping_group_key; ?>][id]" id="<?php echo $input_id; ?>" data-hk-checkout="<?php echo $this->escape(json_encode($input_data)); ?>" onchange="window.checkout.shippingSelected(this);" value="<?php echo $shipping->shipping_id;?>"<?php echo ($selected ? ' checked="checked"' : ''); ?>/>
		<label for="<?php echo $input_id; ?>" style="cursor:pointer;">
			<span class="hikashop_checkout_shipping_name"><?php echo $shipping->shipping_name;?></span>
		</label>
		<span class="hikashop_checkout_shipping_cost"><?php
			echo $this->checkoutHelper->getDisplayPrice($shipping, 'shipping', $this->options);
		?></span>
<?php
			if(!empty($shipping->shipping_images)) {
?>
		<span class="hikashop_checkout_shipping_images">
<?php
				$images = explode(',', $shipping->shipping_images);
				foreach($images as $image) {
					$img = $this->checkoutHelper->getPluginImage($image, 'shipping');
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
			if(!empty($shipping->shipping_description)) {
?>
		<div class="hikashop_checkout_shipping_description"><?php
			echo $shipping->shipping_description;
		?></div>
<?php
			}
?>
	</td></tr>
<?php
		}

		if(empty($group->shippings)) {
			if(!empty($group->no_weight) && empty($group->errors)) {
?>
	<tr><td class="hikashop_checkout_shipping_message checkout_no_shipping_required" colspan="3">
		<?php echo JText::_('NO_SHIPPING_REQUIRED'); ?>
		<input type="radio" style="display:none;" name="checkout[shipping][id][<?php echo $shipping_group_key; ?>]" value="0" checked="checked" />
	</td></tr>
<?php
			} else {
?>
	<tr><td class="hikashop_checkout_shipping_message checkout_no_shipping_available" colspan="3">
		<?php echo JText::_('NO_SHIPPING_AVAILABLE_FOR_WAREHOUSE'); ?>
		<input type="radio" style="display:none;" name="checkout[shipping][id][<?php echo $shipping_group_key; ?>]" value="0" checked="checked" />
	</td></tr>
<?php
			}
		}
?>
	</table>
	</div>
<?php
	}
}

if(empty($this->ajax)) { ?>
</div>
<script type="text/javascript">
if(!window.checkout) window.checkout = {};
window.Oby.registerAjax(['checkout.shipping.updated','cart.updated','checkout.user.updated'], function(params){
	if(params && (params.cart_empty || (params.resp && params.resp.empty))) return;
	window.checkout.refreshShipping(<?php echo (int)$this->step; ?>, <?php echo (int)$this->module_position; ?>);
});
window.checkout.refreshShipping = function(step, id) { return window.checkout.refreshBlock('shipping', step, id); };
window.checkout.submitShipping = function(step, id) { return window.checkout.submitBlock('shipping', step, id); };
window.checkout.shippingSelected = function(el) {
	var data = window.Oby.evalJSON(el.getAttribute('data-hk-checkout'));

	var url = "<?php echo hikashop_completeLink('checkout&task=submitblock&blocktask=shipping&cid=HIKACID&blockpos=HIKAPOS&tmpl=ajax', false, false, true); ?>".replace("HIKACID", data.step).replace("HIKAPOS", data.pos),
		formData = 'selectionOnly=1&' + encodeURI('checkout[shipping]['+data.warehouse+'][id]') + '=' + encodeURIComponent(data.id) + '&' + encodeURI(window.checkout.token)+'=1';
	window.Oby.xRequest(url, {mode:"POST", data: formData}, function(x,p) {
		var r = window.Oby.evalJSON(x.responseText);
		if(r && r.events)
			window.checkout.processEvents(r.events);
	});
};
</script>
<?php }

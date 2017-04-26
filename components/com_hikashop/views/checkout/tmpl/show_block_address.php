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
<div id="hikashop_checkout_address_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" class="hikashop_checkout_address">
<?php } ?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner"></div>
<?php

if(!empty($this->options['display'])) {

	if(empty($this->addressClass))
		$this->addressClass = hikashop_get('class.address');
	$cart = $this->checkoutHelper->getCart();
	if(empty($this->options['read_only']))
		$this->cart_addresses = $this->checkoutHelper->getAddresses();

	if(empty($this->options['edit_address']) && !empty($this->options['show_billing']) && !empty($this->options['show_shipping'])) {
?>
	<div class="hk-container-fluid">
		<div class="hkc-sm-6 hkc-xs-12">
<?php
	}

	if(!empty($this->options['edit_address'])) {
		if(empty($this->edit_address))
			$this->edit_address = new stdClass();
?>
		<fieldset class="hika_address_field hikashop_checkout_checkout_address_block">
<?php
		if($this->options['edit_address'] === true) {
?>
			<legend><?php echo JText::_('HIKASHOP_NEW_ADDRESS'); ?></legend>
<?php
		} else {
?>
			<input type="hidden" name="data[address_<?php echo $this->step . '_' . $this->module_position; ?>][address_id]" value="<?php echo (int)$this->options['edit_address']; ?>"/>
			<legend><?php echo JText::_('HIKASHOP_EDIT_ADDRESS'); ?></legend>
<?php
		}

		$this->checkoutHelper->displayMessages('address');
?>
<table class="admintable table">
<?php
		foreach($this->cart_addresses['fields'] as $field) {
			if(empty($field->field_frontcomp))
				continue;

			$fieldname = $field->field_namekey;
?>
	<tr class="hikashop_checkout_address_<?php echo $fieldname;?>" id="hikashop_checkout_address_<?php echo $this->step . '_' . $this->module_position .'_'.$fieldname; ?>">
		<td class="key"><?php echo $this->fieldClass->getFieldName($field, true, 'hkcontrol-label'); ?></td>
		<td><?php
			$onWhat = 'onchange';
			if($field->field_type == 'radio')
				$onWhat = 'onclick';

			$field->table_name = 'order';
			echo $this->fieldClass->display(
					$field,
					@$this->edit_address->$fieldname,
					'data[address_'.$this->step . '_' . $this->module_position.']['.$fieldname.']',
					false,
					' ' . $onWhat . '="window.hikashop.toggleField(this.value,\''.$fieldname.'\',\'address_'.$this->step . '_'.$this->module_position.'\',0,\'hikashop_checkout_\');"',
					false,
					$this->cart_addresses['fields'],
					$this->edit_address,
					false
			);
		?></td>
	</tr>
<?php
		}
?>
</table>
<?php
		if(!empty($this->options['new_address_type'])) {
?>
		<input type="hidden" name="data[address_type_<?php echo $this->step . '_' . $this->module_position; ?>]" value="<?php echo $this->options['new_address_type']; ?>" />
<?php
		}
?>
	</fieldset>
	<div style="float:right">
		<button onclick="return window.checkout.submitAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>);" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikashop_checkout_address_ok_button"><img src="<?php echo HIKASHOP_IMAGES; ?>save2.png" alt="" style="vertical-align:middle;"/> <?php echo JText::_('HIKA_OK'); ;?></button>
	</div>
	<button onclick="return window.checkout.refreshAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>);" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikashop_checkout_address_cancel_button"><img src="<?php echo HIKASHOP_IMAGES; ?>cancel.png" alt="" style="vertical-align:middle;"/> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
	<div style="clear:both"></div>
<?php
	}

	if(empty($this->options['edit_address']) && !empty($this->options['show_billing'])) {
?>

		<fieldset class="hika_address_field hikashop_checkout_billing_address_block">
			<legend><?php echo JText::_('HIKASHOP_BILLING_ADDRESS'); ?></legend>
<?php
		if(!empty($this->options['read_only'])) {
			echo $this->addressClass->displayAddress($this->cart_addresses['fields'], $this->cart_addresses['data'][ $cart->cart_billing_address_id ], 'address');
		} elseif($this->options['address_selector'] == 2) {
			$values = array();
			foreach($this->cart_addresses['data'] as $k => $address) {
				$addr = $this->addressClass->miniFormat($address);
				$values[] = JHTML::_('select.option', $k, $addr);
			}
			$values[] = JHTML::_('select.option', 0, JText::_('HIKASHOP_NEW_ADDRESS_ITEM'));
			echo JHTML::_('select.genericlist', $values, 'checkout[address][billing]', 'class="hikashop_field_dropdown" onchange="window.checkout.submitAddress('.$this->step.','.$this->module_position.');"', 'value', 'text', $cart->cart_billing_address_id, 'hikashop_address_billing_selector_'.$this->step.'_'.$this->module_position);

			$update_url = 'address&task=edit&cid='.(int)$cart->cart_billing_address_id;
			$delete_url = 'address&task=delete&cid='.(int)$cart->cart_billing_address_id;
?>
			<div class="">
				<div class="hika_edit">
					<a href="<?php echo hikashop_completeLink($update_url);?>" onclick="return window.checkout.editAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo (int)$cart->cart_billing_address_id; ?>);"><img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt=""/><span><?php echo JText::_('HIKA_EDIT'); ?></span></a>
					<a href="<?php echo hikashop_completeLink($delete_url);?>" onclick="return window.checkout.deleteAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo (int)$cart->cart_billing_address_id; ?>);"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt=""/><span><?php echo JText::_('HIKA_DELETE'); ?></span></a>
				</div>
<?php
				echo $this->addressClass->displayAddress($this->cart_addresses['fields'], $this->cart_addresses['data'][ $cart->cart_billing_address_id ], 'address');
?>
			</div>
<?php
		} else { // address_selector : 0 or 1
			foreach($this->cart_addresses['data'] as $k => $address) {
				$update_url = 'address&task=edit&cid='.(int)$address->address_id;
				$delete_url = 'address&task=delete&cid='.(int)$address->address_id;

				$checked = '';
				if($cart->cart_billing_address_id == (int)$address->address_id)
					$checked = ' checked="checked"';
?>
			<div class="">
				<input type="radio" name="checkout[address][billing]" value="<?php echo (int)$address->address_id; ?>" onchange="window.checkout.submitAddress(<?php echo (int)$this->step; ?>,<?php echo (int)$this->module_position; ?>);" <?php echo $checked; ?>/>
				<div class="hika_edit">
					<a href="<?php echo hikashop_completeLink($update_url);?>" onclick="return window.checkout.editAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo (int)$address->address_id; ?>);"><img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt=""/><span><?php echo JText::_('HIKA_EDIT'); ?></span></a>
					<a href="<?php echo hikashop_completeLink($delete_url);?>" onclick="return window.checkout.deleteAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo (int)$address->address_id; ?>);"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt=""/><span><?php echo JText::_('HIKA_DELETE'); ?></span></a>
				</div>
<?php
				echo $this->addressClass->displayAddress($this->cart_addresses['fields'], $address, 'address');
?>
			</div>
<?php
			}
?>
			<button onclick="return window.checkout.newAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>, 'billing');" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikashop_checkout_address_new_button"><?php echo JText::_('HIKA_NEW'); ?></button>
<?php
		}
?>
		</fieldset>
<?php
	}

	if(empty($this->options['edit_address']) && !empty($this->options['show_billing']) && !empty($this->options['show_shipping'])) {
?>
		</div>
		<div class="hkc-sm-6 hkc-xs-12">
<?php
	}

	if(empty($this->options['edit_address']) && !empty($this->options['show_shipping'])) {
		$shippingAddress_override = $this->checkoutHelper->getShippingAddressOverride();
		if($shippingAddress_override !== '') {
?>

		<fieldset class="hika_address_field hikashop_checkout_shipping_address_block">
			<legend><?php echo JText::_('HIKASHOP_SHIPPING_ADDRESS'); ?></legend>
<?php
			if(!empty($shippingAddress_override)) {
?>
			<span class="hikashop_checkout_shipping_address_info"><?php
				echo $shippingAddress_override;
			?></span>
<?php
			} elseif(!empty($this->options['read_only'])) {
				echo $this->addressClass->displayAddress($addresses['fields'], $cart->shipping_address, 'address');
			} elseif($this->options['address_selector'] == 2) {
				$shipping_address_id = (int)$cart->cart_shipping_address_ids;

				$values = array();
				foreach($this->cart_addresses['data'] as $k => $address) {
					$addr = $this->addressClass->miniFormat($address);
					$values[] = JHTML::_('select.option', $k, $addr);
				}
				$values[] = JHTML::_('select.option', 0, JText::_('HIKASHOP_NEW_ADDRESS_ITEM'));
				echo JHTML::_('select.genericlist', $values, 'checkout[address][shipping]', 'class="hikashop_field_dropdown" onchange="window.checkout.submitAddress('.$this->step.','.$this->module_position.');"', 'value', 'text', $shipping_address_id, 'hikashop_address_shipping_selector_'.$this->step.'_'.$this->module_position);

				$update_url = 'address&task=edit&cid='.(int)$shipping_address_id;
				$delete_url = 'address&task=delete&cid='.(int)$shipping_address_id;
?>
			<div class="">
				<div class="hika_edit">
					<a href="<?php echo hikashop_completeLink($update_url);?>" onclick="return window.checkout.editAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo (int)$shipping_address_id; ?>);"><img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt=""/><span><?php echo JText::_('HIKA_EDIT'); ?></span></a>
					<a href="<?php echo hikashop_completeLink($delete_url);?>" onclick="return window.checkout.deleteAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo (int)$shipping_address_id; ?>);"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt=""/><span><?php echo JText::_('HIKA_DELETE'); ?></span></a>
				</div>
<?php
				echo $this->addressClass->displayAddress($this->cart_addresses['fields'], $this->cart_addresses['data'][ $shipping_address_id ], 'address');
?>
			</div>
<?php
			} else {
				$shipping_address_id = (int)$cart->cart_shipping_address_ids;
				foreach($this->cart_addresses['data'] as $k => $address) {
					$update_url = 'address&task=edit&cid='.(int)$address->address_id;
					$delete_url = 'address&task=delete&cid='.(int)$address->address_id;

					$checked = '';
					if($shipping_address_id == (int)$address->address_id)
						$checked = ' checked="checked"';
?>
			<div class="">
				<input type="radio" name="checkout[address][shipping]" value="<?php echo (int)$address->address_id; ?>" onchange="window.checkout.submitAddress(<?php echo (int)$this->step; ?>,<?php echo (int)$this->module_position; ?>);" <?php echo $checked; ?>/>
				<div class="hika_edit">
					<a href="<?php echo hikashop_completeLink($update_url);?>" onclick="return window.checkout.editAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo (int)$address->address_id; ?>);"><img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt=""/><span><?php echo JText::_('HIKA_EDIT'); ?></span></a>
					<a href="<?php echo hikashop_completeLink($delete_url);?>" onclick="return window.checkout.deleteAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo (int)$address->address_id; ?>);"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt=""/><span><?php echo JText::_('HIKA_DELETE'); ?></span></a>
				</div>
<?php
					echo $this->addressClass->displayAddress($this->cart_addresses['fields'], $address, 'address');
?>
			</div>
<?php
				}
?>
			<button onclick="return window.checkout.newAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>, 'shipping');" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikashop_checkout_address_new_button"><?php echo JText::_('HIKA_NEW'); ?></button>
<?php
			}
?>
		</fieldset>
<?php
		}
	}

	if(empty($this->options['edit_address']) && !empty($this->options['show_billing']) && !empty($this->options['show_shipping'])) {
?>
		</div>
	</div>
<?php
	}

	if(!empty($this->options['js'])) {
?>
<script type="text/javascript">
<?php echo $this->options['js']; ?>
</script>
<?php
	}

} // Options:Display

if(empty($this->ajax)) {
?>
</div>
<script type="text/javascript">
if(!window.checkout) window.checkout = {};
window.Oby.registerAjax(['checkout.user.updated','checkout.address.updated'], function(params){
	window.checkout.refreshAddress(<?php echo (int)$this->step; ?>, <?php echo (int)$this->module_position; ?>);
});
window.checkout.refreshAddress = function(step, id) { return window.checkout.refreshBlock('address', step, id); };
window.checkout.submitAddress = function(step, id) { return window.checkout.submitBlock('address', step, id); };
window.checkout.editAddress = function(step, id, addr) {
	window.checkout.submitBlock('address', step, id, {'checkout[address][edit]':addr});
	return false;
};
window.checkout.deleteAddress = function(step, id, addr) {
	window.checkout.submitBlock('address', step, id, {'checkout[address][delete]':addr});
	return false;
};
window.checkout.newAddress = function(step, id, type) {
	window.checkout.submitBlock('address', step, id, {'checkout[address][new]':type});
	return false;
};
</script>
<?php
}

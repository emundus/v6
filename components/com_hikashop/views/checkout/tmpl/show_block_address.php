<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.0.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2018 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$labelcolumnclass = 'hkc-sm-4';
$inputcolumnclass = 'hkc-sm-8';

if(empty($this->ajax)) {
?>
<div id="hikashop_checkout_address_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" data-checkout-step="<?php echo $this->step; ?>" data-checkout-pos="<?php echo $this->module_position; ?>" class="hikashop_checkout_address">
<?php
}
?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner"></div>
<?php

if(!empty($this->options['display'])) {

	if(empty($this->addressClass))
		$this->addressClass = hikashop_get('class.address');
	$cart = $this->checkoutHelper->getCart();
	$this->cart_addresses = $this->checkoutHelper->getAddresses();

	if(empty($this->options['edit_address']) && !empty($this->options['show_billing']) && !empty($this->options['show_shipping'])) {
?>
	<div class="hk-container-fluid">
		<div class="hkc-sm-6">
<?php
	}

	if(!empty($this->options['edit_address'])) {
		if(empty($this->edit_address))
			$this->edit_address = new stdClass();
?>
			<fieldset class="hika_address_field hikashop_checkout_checkout_address_block">
<?php
		if($this->options['edit_address'] === true) {
			$label = 'HIKASHOP_NEW_ADDRESS';
			if(!empty($this->options['new_address_type']) && in_array($this->options['new_address_type'], array('billing','shipping')))
				$label = 'HIKASHOP_NEW_'.strtoupper($this->options['new_address_type']).'_ADDRESS';
?>
				<legend><?php echo JText::_($label); ?></legend>
<?php
		} else {
?>
				<input type="hidden" name="data[address_<?php echo $this->step . '_' . $this->module_position; ?>][address_id]" value="<?php echo (int)$this->options['edit_address']; ?>"/>
				<legend><?php echo JText::_('HIKASHOP_EDIT_ADDRESS'); ?></legend>
<?php
		}

		$this->checkoutHelper->displayMessages('address');
?>
<fieldset class="hkform-horizontal">
<?php
		foreach($this->cart_addresses['fields'] as $field) {
			if(empty($field->field_frontcomp))
				continue;

			$fieldname = $field->field_namekey;
?>

	<div class="hkform-group control-group hikashop_checkout_address_<?php echo $fieldname;?>" id="hikashop_checkout_address_<?php echo $this->step . '_' . $this->module_position .'_'.$fieldname; ?>">
<?php
		$classname = $labelcolumnclass.' hkcontrol-label';
		echo $this->fieldClass->getFieldName($field, true, $classname);
?>
		<div class="<?php echo $inputcolumnclass;?>">
<?php
			$onWhat = ($field->field_type == 'radio') ? 'onclick' : 'onchange';
			$field->table_name = 'order';
			echo $this->fieldClass->display(
				$field,
				@$this->edit_address->$fieldname,
				'data[address_'.$this->step . '_' . $this->module_position.']['.$fieldname.']',
				false,
				' class="hkform-control" ' . $onWhat . '="window.hikashop.toggleField(this.value,\''.$fieldname.'\',\'address_'.$this->step . '_'.$this->module_position.'\',0,\'hikashop_checkout_\');"',
				false,
				$this->cart_addresses['fields'],
				$this->edit_address,
				false
			);
?>
		</div>
	</div>
	<input type="hidden" name="data[address_selecttype_<?php echo $this->step . '_' . $this->module_position; ?>]" value="1" />
<?php
		}
		if(!empty($this->options['same_address']) && !empty($this->options['new_address_type'])) {
?>
	<div class="hkform-group control-group hikashop_checkout_address_same" id="hikashop_checkout_address_<?php echo $this->step . '_' . $this->module_position .'_same'; ?>">
		<div class="<?php echo $labelcolumnclass; ?>"></div>
		<div class="<?php echo $inputcolumnclass;?>">
			<label><input type="checkbox" checked="checked" name="data[address_bothtypes_<?php echo $this->step . '_' . $this->module_position; ?>]" value="1"> <?php
				$other = ($this->options['new_address_type'] == 'billing') ? 'shipping' : 'billing';
				echo JText::_('HIKASHOP_ALSO_'.strtoupper($other).'_ADDRESS');
			?></label>
		</div>
	</div>
<?php
		}
?>
</fieldset>
<?php
		if(!empty($this->options['new_address_type'])) {
?>
				<input type="hidden" name="data[address_type_<?php echo $this->step . '_' . $this->module_position; ?>]" value="<?php echo $this->options['new_address_type']; ?>" />
<?php
		}
?>
			</fieldset>
			<div class="hkform-group control-group hikashop_address_required_info_line">
				<div class="controls"><?php echo JText::_('HIKA_REGISTER_REQUIRED'); ?></div>
			</div>
			<div style="float:right">
				<button onclick="return window.checkout.submitAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>);" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikashop_checkout_address_ok_button"><i class="fa fa-save"></i> <?php echo JText::_('HIKA_OK'); ;?></button>
			</div>
			<button onclick="return window.checkout.refreshAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>);" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikashop_checkout_address_cancel_button"><i class="fa fa-times"></i> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
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
				if(empty($address))
					continue;
				if(!empty($address->address_type) && !in_array($address->address_type, array('both', 'billing')))
					continue;
				$addr = $this->addressClass->miniFormat($address);
				$values[] = JHTML::_('select.option', $k, $addr);
			}
			$values[] = JHTML::_('select.option', 0, JText::_('HIKASHOP_NEW_ADDRESS_ITEM'));
			echo JHTML::_('select.genericlist', $values, 'checkout[address][billing]', 'class="hikashop_field_dropdown" onchange="window.checkout.submitAddress('.$this->step.','.$this->module_position.');"', 'value', 'text', $cart->cart_billing_address_id, 'hikashop_address_billing_selector_'.$this->step.'_'.$this->module_position);

			$update_url = 'address&task=edit&cid='.(int)$cart->cart_billing_address_id;
			$delete_url = 'address&task=delete&cid='.(int)$cart->cart_billing_address_id;
?>
				<div class="hika_address_element">
					<div class="hika_edit">
						<a href="<?php echo hikashop_completeLink($update_url);?>" onclick="return window.checkout.editAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo (int)$cart->cart_billing_address_id; ?>);" title="<?php echo JText::_('HIKA_EDIT'); ?>"><i class="fas fa-pen"></i><span><?php echo JText::_('HIKA_EDIT'); ?></span></a>
						<a href="<?php echo hikashop_completeLink($delete_url);?>" onclick="return window.checkout.deleteAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo (int)$cart->cart_billing_address_id; ?>);" title="<?php echo JText::_('HIKA_DELETE'); ?>" ><i class="fa fa-trash"></i><span><?php echo JText::_('HIKA_DELETE'); ?></span></a>
					</div>
					<div class="hika_address_display">
<?php
			echo $this->addressClass->displayAddress($this->cart_addresses['fields'], $this->cart_addresses['data'][ $cart->cart_billing_address_id ], 'address');
?>
					</div>
				</div>
<?php
		} else { // address_selector : 0 or 1
			foreach($this->cart_addresses['data'] as $k => $address) {
				if(!empty($address->address_type) && !in_array($address->address_type, array('both', 'billing')))
					continue;

				$update_url = 'address&task=edit&cid='.(int)$address->address_id;
				$delete_url = 'address&task=delete&cid='.(int)$address->address_id;

				$checked = '';
				if($cart->cart_billing_address_id == (int)$address->address_id)
					$checked = ' checked="checked"';
?>
				<div class="hika_address_element">
					<div class="hika_edit">
						<input type="radio" name="checkout[address][billing]" value="<?php echo (int)$address->address_id; ?>" onchange="window.checkout.submitAddress(<?php echo (int)$this->step; ?>,<?php echo (int)$this->module_position; ?>);" <?php echo $checked; ?>/>
						<a href="<?php echo hikashop_completeLink($update_url);?>" onclick="return window.checkout.editAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo (int)$address->address_id; ?>);" title="<?php echo JText::_('HIKA_EDIT'); ?>"><i class="fas fa-pen"></i> <span><?php echo JText::_('HIKA_EDIT'); ?></span></a>
						<a href="<?php echo hikashop_completeLink($delete_url);?>" onclick="return window.checkout.deleteAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo (int)$address->address_id; ?>);" title="<?php echo JText::_('HIKA_DELETE'); ?>"><i class="fa fa-trash"></i> <span><?php echo JText::_('HIKA_DELETE'); ?></span></a>
					</div>
					<div class="hika_address_display">
<?php
			echo $this->addressClass->displayAddress($this->cart_addresses['fields'], $address, 'address');
?>
					</div>
				</div>
<?php
			}
?>
				<button onclick="return window.checkout.newAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>, 'billing');" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn-success hikashop_checkout_address_new_button"><i class="fa fa-plus"></i> <?php echo JText::_('HIKA_NEW'); ?></button>
<?php
		}
?>
			</fieldset>
<?php
	}

	if(empty($this->options['edit_address']) && !empty($this->options['show_billing']) && !empty($this->options['show_shipping'])) {
?>
		</div>
		<div class="hkc-sm-6">
<?php
	}

	if(empty($this->options['edit_address']) && !empty($this->options['show_shipping'])) {
		$shippingAddress_override = $this->checkoutHelper->getShippingAddressOverride();
		if($shippingAddress_override !== '') {
?>

			<fieldset class="hika_address_field hikashop_checkout_shipping_address_block">
				<legend><?php echo JText::_('HIKASHOP_SHIPPING_ADDRESS'); ?></legend>
<?php
			$shipping_address_id = (int)$cart->cart_shipping_address_ids;
			if(!empty($shippingAddress_override)) {
?>
				<span class="hikashop_checkout_shipping_address_info"><?php
					echo $shippingAddress_override;
				?></span>
<?php
			} elseif(!empty($this->options['read_only'])) {
				echo $this->addressClass->displayAddress($this->cart_addresses['fields'], $this->cart_addresses['data'][ $shipping_address_id ], 'address');
			} elseif($this->options['address_selector'] == 2) {
				$values = array();
				foreach($this->cart_addresses['data'] as $k => $address) {
					if(empty($address))
						continue;
					if(!empty($address->address_type) && !in_array($address->address_type, array('both', 'shipping')))
						continue;
					$addr = $this->addressClass->miniFormat($address);
					$values[] = JHTML::_('select.option', $k, $addr);
				}
				$values[] = JHTML::_('select.option', 0, JText::_('HIKASHOP_NEW_ADDRESS_ITEM'));
				echo JHTML::_('select.genericlist', $values, 'checkout[address][shipping]', 'class="hikashop_field_dropdown" onchange="window.checkout.submitAddress('.$this->step.','.$this->module_position.');"', 'value', 'text', $shipping_address_id, 'hikashop_address_shipping_selector_'.$this->step.'_'.$this->module_position);

				$update_url = 'address&task=edit&cid='.(int)$shipping_address_id;
				$delete_url = 'address&task=delete&cid='.(int)$shipping_address_id;
?>
				<div class="hika_address_element">
					<div class="hika_edit">
						<a href="<?php echo hikashop_completeLink($update_url);?>" onclick="return window.checkout.editAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo (int)$shipping_address_id; ?>);" title="<?php echo JText::_('HIKA_EDIT'); ?>"><i class="fas fa-pen"></i> <span><?php echo JText::_('HIKA_EDIT'); ?></span></a>
						<a href="<?php echo hikashop_completeLink($delete_url);?>" onclick="return window.checkout.deleteAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo (int)$shipping_address_id; ?>);" title="<?php echo JText::_('HIKA_DELETE'); ?>"><i class="fa fa-trash"></i> <span><?php echo JText::_('HIKA_DELETE'); ?></span></a>
					</div>
					<div class="hika_address_display">
<?php
				echo $this->addressClass->displayAddress($this->cart_addresses['fields'], $this->cart_addresses['data'][ $shipping_address_id ], 'address');
?>
					</div>
				</div>
<?php
			} else {
				foreach($this->cart_addresses['data'] as $k => $address) {
					if(!empty($address->address_type) && !in_array($address->address_type, array('both', 'shipping')))
						continue;
					$update_url = 'address&task=edit&cid='.(int)$address->address_id;
					$delete_url = 'address&task=delete&cid='.(int)$address->address_id;

					$checked = '';
					if($shipping_address_id == (int)$address->address_id)
						$checked = ' checked="checked"';
?>
				<div class="hika_address_element">
					<div class="hika_edit">
						<input type="radio" name="checkout[address][shipping]" value="<?php echo (int)$address->address_id; ?>" onchange="window.checkout.submitAddress(<?php echo (int)$this->step; ?>,<?php echo (int)$this->module_position; ?>);" <?php echo $checked; ?>/>
						<a href="<?php echo hikashop_completeLink($update_url);?>" onclick="return window.checkout.editAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo (int)$address->address_id; ?>);" title="<?php echo JText::_('HIKA_EDIT'); ?>"><i class="fas fa-pen"></i> <span><?php echo JText::_('HIKA_EDIT'); ?></span></a>
						<a href="<?php echo hikashop_completeLink($delete_url);?>" onclick="return window.checkout.deleteAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>,<?php echo (int)$address->address_id; ?>);" title="<?php echo JText::_('HIKA_DELETE'); ?>"><i class="fa fa-trash"></i> <span><?php echo JText::_('HIKA_DELETE'); ?></span></a>
					</div>
					<div class="hika_address_display">
<?php
					echo $this->addressClass->displayAddress($this->cart_addresses['fields'], $address, 'address');
?>
					</div>
				</div>
<?php
				}
?>
				<button onclick="return window.checkout.newAddress(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>, 'shipping');" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn-success hikashop_checkout_address_new_button"><i class="fa fa-plus"></i> <?php echo JText::_('HIKA_NEW'); ?></button>
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

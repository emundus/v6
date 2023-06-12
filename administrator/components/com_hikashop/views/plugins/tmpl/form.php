<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<div>
	<form action="<?php echo hikashop_completeLink('plugins'); ?>" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
<?php
if(!empty($this->plugin->pluginView)) {
	$this->setLayout($this->plugin->pluginView);
	echo $this->loadTemplate();
} else if(!empty($this->plugin->noForm)) {
	echo $this->content;
} else {
	if(empty($this->plugin_type)) $this->plugin_type= '';

	$type = $this->plugin_type;
	$upType = strtoupper($type);
	$plugin_published = $type . '_published';
	$plugin_images = $type . '_images';
	$plugin_name = $type . '_name';
	$plugin_name_input = $plugin_name . '_input';
?>
<div id="page-plugins" class="hk-row-fluid hikashop_backend_tile_edition">
	<div class="hkc-md-6">
		<div class="hikashop_tile_block"><div>
			<div class="hikashop_tile_title"><?php
				echo JText::_('MAIN_INFORMATION');
			?></div>
<?php
	$this->$plugin_name_input = 'data['.$type.']['.$plugin_name.']';
	if($this->translation) {
		$this->setLayout('translation');
	} else {
		$this->setLayout('normal');
	}
	echo $this->loadTemplate();
?>
	</div>
	</div>
<?php
	if(!empty($this->content)) {
?>
		<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php
			echo JText::_('PLUGIN_SPECIFIC_CONFIGURATION');
		?></div>
			<table class="admintable table"><?php
				echo $this->content;
			?></table>
		</div></div>
<?php
	}

	if(!empty($this->extra_blocks)) {
		echo implode("\r\n", $this->extra_blocks);
	}
?>
	</div>
	<div class="hkc-md-6">
		<div class="hikashop_tile_block"><div>
			<div class="hikashop_tile_title"><?php
				echo JText::_('PLUGIN_GENERIC_CONFIGURATION');
			?></div>
			<table class="admintable table">
<?php
	if($this->multiple_plugin) {
?>
				<tr>
					<td class="key"><label><?php
						echo JText::_('HIKA_PUBLISHED');
					?></label></td>
					<td><?php
						echo JHTML::_('hikaselect.booleanlist', 'data['. $type.']['.$type.'_published]', '', @$this->element->$plugin_published);
					?></td>
				</tr>
<?php
	}

	if($this->plugin_type == 'payment' || $this->plugin_type == 'shipping') {
?>
				<tr>
					<td class="key"><label for="data_<?php echo $type; ?>_<?php echo $type; ?>_images_text"><?php
						echo JText::_( 'HIKA_IMAGES' );
					?></label></td>
					<td><?php
					if(empty($this->nameboxType))
						$this->nameboxType = hikashop_get('type.namebox');

					echo $this->nameboxType->display(
						'data['.$type.']['. $type .'_images]',
						@$this->element->$plugin_images,
						hikashopNameboxType::NAMEBOX_MULTIPLE,
						'plugin_images',
						array(
							'delete' => true,
							'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
							'type' => $type,
							'id' => 'images',
						)
					);

					?></td>
				</tr>
<?php
	}

	if($this->plugin_type == 'payment') {
?>
				<tr>
					<td class="key"><label for="payment_price"><?php
						echo JText::_('PRICE');
					?></label></td>
					<td>
						<input type="text" id="payment_price" name="data[payment][payment_price]" value="<?php echo @$this->element->payment_price; ?>" /><?php echo $this->currencies->display('data[payment][payment_params][payment_currency]',@$this->element->payment_params->payment_currency); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="payment_percentage"><?php
							echo JText::_('DISCOUNT_PERCENT_AMOUNT');
						?></label>
					</td>
					<td>
						<input type="text" id="payment_percentage" name="data[payment][payment_params][payment_percentage]" value="<?php echo (float)@$this->element->payment_params->payment_percentage; ?>" />%
					</td>
				</tr>
<!--jms2win_begin -->
				<tr>
					<td class="key">
						<label for="datapaymentpayment_paramspayment_tax_id"><?php
							echo JText::_( 'PRODUCT_TAXATION_CATEGORY' );
						?></label>
					</td>
					<td><?php
						$categoryType = hikashop_get('type.categorysub');
						$categoryType->type='tax';
						$categoryType->field='category_id';

						echo $categoryType->display('data[payment][payment_params][payment_tax_id]', @$this->element->payment_params->payment_tax_id, 'tax');
					?></td>
				</tr>
				<tr style="display:none;">
					<td class="key">
						<label for="data[payment][payment_params][payment_algorithm]"><?php
							echo JText::_( 'Payment algorithm' );
						?></label>
					</td>
					<td><?php
						$values = array(
							JHTML::_('select.option', '0', JText::_('Default')),
							JHTML::_('select.option', 'realcost', JText::_('Real cost')),
						);

						echo JHTML::_('select.genericlist', $values, "data[payment][payment_params][payment_algorithm]" , 'onchange="hika_payment_algorithm(this);"', 'value', 'text', @$this->element->payment_params->payment_algorithm );
					?>
<script type="text/javascript">
function hika_payment_algorithm(el) {
	var t = document.getElementById('hika_payment_algorithm_text');
	if(!t) return;
	t.style.display = (el.value == 3 || el.value == 4) ? '' : 'none';
}
</script>
					</td>
				</tr>
<!-- jms2win_end -->
<?php
	}
	if($this->plugin_type == 'shipping' && $this->multiple_interface) {
?>
				<tr>
					<td class="key">
						<label for="shipping_price"><?php
							echo JText::_('PRICE');
						?></label>
					</td>
					<td>
						<input type="text" id="shipping_price" name="data[shipping][shipping_price]" value="<?php echo @$this->element->shipping_price; ?>" /><?php echo $this->data['currency']->display('data[shipping][shipping_currency_id]',@$this->element->shipping_currency_id); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="shipping_percentage"><?php
							echo JText::_('DISCOUNT_PERCENT_AMOUNT');
						?></label>
					</td>
					<td>
						<input type="text" id="shipping_percentage" name="data[shipping][shipping_params][shipping_percentage]" value="<?php echo (float)@$this->element->shipping_params->shipping_percentage; ?>" />%
					</td>
				</tr>
				<tr>
					<td class="key"><label for="shipping_formula"><?php
						echo JText::_('HIKA_FORMULA');
					?></label></td>
					<td>
						<input type="text" id="shipping_formula" name="data[shipping][shipping_params][shipping_formula]" value="<?php echo @$this->element->shipping_params->shipping_formula; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="datashippingshipping_paramsshipping_tax"><?php
							echo JText::_('AUTOMATIC_TAXES');
						?></label>
					</td>
					<td>
						<?php
						if(empty($this->element->shipping_id))
							$this->element->shipping_params->shipping_tax = 1;

						$values = array(
							JHTML::_('select.option', 0, JText::_('HIKASHOP_NO')),
							JHTML::_('select.option', 1, JText::_('PROPORTION')),
							JHTML::_('select.option', 2, JText::_('HIGHEST_RATE')),
							JHTML::_('select.option', 3, JText::_('LOWEST_RATE')),
						);

						echo JHTML::_('select.genericlist', $values, "data[shipping][shipping_params][shipping_tax]" , 'onchange="hikashopToggleTax(this.value);"', 'value', 'text', @$this->element->shipping_params->shipping_tax); ?>
					</td>
				</tr>
				<tr data-tax-display="1">
					<td class="key">
						<label for="datashippingshipping_tax_id"><?php
							echo JText::_( 'TAXATION_CATEGORY' );
						?></label>
					</td>
					<td><?php
						echo $this->categoryType->display('data[shipping][shipping_tax_id]',@$this->element->shipping_tax_id,true);
					?></td>
				</tr>
				<tr>
					<td class="key">
						<label><?php
							echo JText::_('USE_PRICE_PER_PRODUCT');
						?></label>
					</td>
					<td><?php
						if(!isset($this->element->shipping_params->shipping_per_product))
							$this->element->shipping_params->shipping_per_product = false;
						echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][shipping_per_product]" , ' onchange="hikashop_switch_tr(this,\'hikashop_shipping_per_product_\',2)"', @$this->element->shipping_params->shipping_per_product);
					?></td>
				</tr>
				<tr id="hikashop_shipping_per_product_1"<?php if($this->element->shipping_params->shipping_per_product == false) { echo ' style="display:none;"';}?>>
					<td class="key">
						<label for="shipping_price_per_product"><?php
							echo JText::_( 'PRICE_PER_PRODUCT' );
						?></label>
					</td>
					<td>
						<input type="text" id="shipping_price_per_product" name="data[shipping][shipping_params][shipping_price_per_product]" value="<?php echo @$this->element->shipping_params->shipping_price_per_product; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label><?php echo JText::_( 'INCLUDE_VIRTUAL_PRODUCTS_PRICE' ); ?></label>
					</td>
					<td><?php
						if(!isset($this->element->shipping_params->shipping_virtual_included)){
							$config = hikashop_config();
							$this->element->shipping_params->shipping_virtual_included = $config->get('force_shipping',1);
						}
						echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][shipping_virtual_included]" , '',$this->element->shipping_params->shipping_virtual_included);
					?></td>
				</tr>
				<tr>
					<td class="key">
						<label for="datashippingshipping_paramsshipping_override_address"><?php
							echo JText::_( 'OVERRIDE_SHIPPING_ADDRESS' );
						?></label>
					</td>
					<td><?php
						$values = array(
							JHTML::_('select.option', '0', JText::_('HIKASHOP_NO')),
							JHTML::_('select.option', '1', JText::_('STORE_ADDRESS')),
							JHTML::_('select.option', '2', JText::_('HIKA_HIDE')),
							JHTML::_('select.option', '3', JText::_('TEXT_VERSION')),
							JHTML::_('select.option', '4', JText::_('HTML_VERSION'))
						);

						echo JHTML::_('select.genericlist', $values, "data[shipping][shipping_params][shipping_override_address]" , 'class="custom-select"  onchange="hika_shipping_override(this);"', 'value', 'text', @$this->element->shipping_params->shipping_override_address );
					?>
						<script type="text/javascript">
						function hika_shipping_override(el) {
							var t = document.getElementById('hikashop_shipping_override_text');
							if(!t) return;
							if(el.value == 3 || el.value == 4) {
								t.style.display = '';
							} else {
								t.style.display = 'none';
							}
						}
						</script>
					</td>
				</tr>
				<tr id="hikashop_shipping_override_text" style="<?php
						$override = (int)@$this->element->shipping_params->shipping_override_address;
						if( $override != 3 && $override != 4 ) { echo 'display:none;'; }
					?>">
					<td class="key">
						<label for="shipping_override_address_text_textarea"><?php
							echo JText::_( 'OVERRIDE_SHIPPING_ADDRESS_TEXT' );
						?></label>
					</td>
					<td>
						<textarea id="shipping_override_address_text_textarea" name="data[shipping][shipping_params][shipping_override_address_text]"><?php
							echo @$this->element->shipping_params->shipping_override_address_text;
						?></textarea>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data_shipping_shipping_params_override_tax_zone_text"><?php
							echo JText::_('OVERRIDE_TAX_ZONE');
						?></label>
					</td>
					<td>
						<?php
						echo $this->nameboxType->display(
							'data[shipping][shipping_params][override_tax_zone]',
							@$this->element->shipping_params->override_tax_zone->zone_id,
							hikashopNameboxType::NAMEBOX_SINGLE,
							'zone',
							array(
								'type' => 'id',
								'delete' => true,
								'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
								'zone_types' => array('country' => 'COUNTRY', 'tax' => 'TAXES'),
							)
						);
						?>
					</td>
				</tr>
<?php
	}

	if(!empty($this->extra_config)) {
		echo implode("\r\n", $this->extra_config);
	}
?>
			</table>
		</div></div>
<?php


	if($this->plugin_type == 'payment' || $this->plugin_type == 'shipping') {
?>
		<div class="hikashop_tile_block"><div>
			<div class="hikashop_tile_title"><?php
				echo JText::_('HIKA_RESTRICTIONS');
			?></div>
				<table class="admintable table">
					<tr>
						<td class="key"><label for="<?php echo 'data_'.$type.'_'.$type.'_zone_namekey_text'; ?>"><?php echo JText::_('ZONE'); ?></label></td>
						<td>
						<?php
						$plugin_zone_namekey = $type.'_zone_namekey';
						$key = 'ship';
						$val = 'SHIPPING';
						if($this->plugin_type == 'payment') {
							$key = 'payment';
							$val = 'PAYMENT_ZONES';
						}
						echo $this->nameboxType->display(
							'data['.$type.']['.$type.'_zone_namekey]',
							@$this->element->$plugin_zone_namekey,
							hikashopNameboxType::NAMEBOX_SINGLE,
							'zone',
							array(
								'delete' => true,
								'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
								'zone_types' => array('country' => 'COUNTRY', $key => $val),
							)
						);
						?>
						</td>
					</tr>
<?php
		if($this->plugin_type == 'payment') {
?>
					<tr>
						<td class="key"><label for="data_payment_payment_shipping_methods_text"><?php echo JText::_('HIKASHOP_SHIPPING_METHOD'); ?></label></td>
						<td><?php
			if(@$this->element->payment_shipping_methods == 'all') $this->element->payment_shipping_methods = '';
			echo  $this->nameboxType->display(
				'data[payment][payment_shipping_methods]',
				explode("\n",trim((string)@$this->element->payment_shipping_methods,"\n")),
				hikashopNameboxType::NAMEBOX_MULTIPLE,
				'shipping_methods',
				array(
					'delete' => true,
					'main_only' => false,
					'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				)
			);

?>
						</td>
					</tr>
<?php
		}
?>
					<tr>
<?php $name = $this->plugin_type.'_currency'; ?>
						<td class="key"><label for="<?php echo 'data_'.$this->plugin_type.'_'.$name.'_text'; ?>"><?php
							echo JText::_('CURRENCY');
						?></label></td>
						<td><?php

			echo  $this->nameboxType->display(
				'data['.$this->plugin_type.']['.$name.']',
				$this->element->$name,
				hikashopNameboxType::NAMEBOX_MULTIPLE,
				'currency',
				array(
					'delete' => true,
					'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				)
			);
?>
						</td>
					</tr>
<?php

		if($this->plugin_type == 'shipping' && $this->multiple_interface) {
?>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_warehouse_filter]"><?php
							echo JText::_('WAREHOUSE');
						?></label>
					</td>
					<td>
						<?php echo $this->warehouseType->display('data[shipping][shipping_params][shipping_warehouse_filter]', @$this->element->shipping_params->shipping_warehouse_filter, true) ;?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="shipping_min_price"><?php
							echo JText::_('SHIPPING_MIN_PRICE');
						?></label>
					</td>
					<td>
						<input type="text" id="shipping_min_price" name="data[shipping][shipping_params][shipping_min_price]" value="<?php echo @$this->element->shipping_params->shipping_min_price; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="shipping_max_price"><?php
							echo JText::_( 'SHIPPING_MAX_PRICE' );
						?></label>
					</td>
					<td>
						<input type="text" id="shipping_max_price" name="data[shipping][shipping_params][shipping_max_price]" value="<?php echo @$this->element->shipping_params->shipping_max_price; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label><?php
							echo JText::_('WITH_TAX');
						?></label>
					</td>
					<td>
						<?php
						if(!isset($this->element->shipping_params->shipping_price_use_tax)) $this->element->shipping_params->shipping_price_use_tax=1;
						echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][shipping_price_use_tax]" , '', $this->element->shipping_params->shipping_price_use_tax); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="shipping_min_quantity"><?php
							echo JText::_('SHIPPING_MIN_QUANTITY');
						?></label>
					</td>
					<td>
						<input type="text" id="shipping_min_quantity" name="data[shipping][shipping_params][shipping_min_quantity]" value="<?php echo @$this->element->shipping_params->shipping_min_quantity; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="shipping_max_quantity"><?php
							echo JText::_('SHIPPING_MAX_QUANTITY');
						?></label>
					</td>
					<td>
						<input type="text" id="shipping_max_quantity" name="data[shipping][shipping_params][shipping_max_quantity]" value="<?php echo @$this->element->shipping_params->shipping_max_quantity; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="shipping_min_weight"><?php
							echo JText::_('SHIPPING_MIN_WEIGHT');
						?></label>
					</td>
					<td>
						<input type="text" id="shipping_min_weight" name="data[shipping][shipping_params][shipping_min_weight]" value="<?php echo @$this->element->shipping_params->shipping_min_weight; ?>"/>
						<?php
							echo $this->data['weight']->display('data[shipping][shipping_params][shipping_weight_unit]',@$this->element->shipping_params->shipping_weight_unit);
						?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="shipping_max_weight"><?php
							echo JText::_('SHIPPING_MAX_WEIGHT');
						?></label>
					</td>
					<td>
						<input type="text" id="shipping_max_weight" name="data[shipping][shipping_params][shipping_max_weight]" value="<?php echo @$this->element->shipping_params->shipping_max_weight; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="shipping_min_volume"><?php
							echo JText::_('SHIPPING_MIN_VOLUME');
						?></label>
					</td>
					<td>
						<input type="text" id="shipping_min_volume" name="data[shipping][shipping_params][shipping_min_volume]" value="<?php echo @$this->element->shipping_params->shipping_min_volume; ?>"/>
						<?php
							echo $this->data['volume']->display('data[shipping][shipping_params][shipping_size_unit]',@$this->element->shipping_params->shipping_size_unit);
						?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="shipping_max_volume"><?php
							echo JText::_('SHIPPING_MAX_VOLUME');
						?></label>
					</td>
					<td>
						<input type="text" id="shipping_max_volume" name="data[shipping][shipping_params][shipping_max_volume]" value="<?php echo @$this->element->shipping_params->shipping_max_volume; ?>"/>
					</td>
				</tr>
<?php
		}

		if($this->plugin_type == 'payment' && $this->multiple_interface) {
?>
				<tr>
					<td class="key">
						<label for="payment_min_price"><?php
							echo JText::_('SHIPPING_MIN_PRICE');
						?></label>
					</td>
					<td>
						<input type="text" id="payment_min_price" name="data[payment][payment_params][payment_min_price]" value="<?php echo @$this->element->payment_params->payment_min_price; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="payment_max_price"><?php
							echo JText::_( 'SHIPPING_MAX_PRICE' );
						?></label>
					</td>
					<td>
						<input type="text" id="payment_max_price" name="data[payment][payment_params][payment_max_price]" value="<?php echo @$this->element->payment_params->payment_max_price; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label><?php
							echo JText::_('WITH_TAX');
						?></label>
					</td>
					<td>
						<?php
						if(!isset($this->element->payment_params->payment_price_use_tax)) $this->element->payment_params->payment_price_use_tax=1;
						echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][payment_price_use_tax]" , '', $this->element->payment_params->payment_price_use_tax); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="payment_min_quantity"><?php
							echo JText::_('SHIPPING_MIN_QUANTITY');
						?></label>
					</td>
					<td>
						<input type="text" id="payment_min_quantity" name="data[payment][payment_params][payment_min_quantity]" value="<?php echo @$this->element->payment_params->payment_min_quantity; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="payment_max_quantity"><?php
							echo JText::_('SHIPPING_MAX_QUANTITY');
						?></label>
					</td>
					<td>
						<input type="text" id="payment_max_quantity" name="data[payment][payment_params][payment_max_quantity]" value="<?php echo @$this->element->payment_params->payment_max_quantity; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="payment_min_weight"><?php
							echo JText::_('SHIPPING_MIN_WEIGHT');
						?></label>
					</td>
					<td>
						<input type="text" id="payment_min_weight" name="data[payment][payment_params][payment_min_weight]" value="<?php echo @$this->element->payment_params->payment_min_weight; ?>"/>
						<?php
							echo $this->data['weight']->display('data[payment][payment_params][payment_weight_unit]',@$this->element->payment_params->payment_weight_unit);
						?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="payment_max_weight"><?php
							echo JText::_('SHIPPING_MAX_WEIGHT');
						?></label>
					</td>
					<td>
						<input type="text" id="payment_max_weight" name="data[payment][payment_params][payment_max_weight]" value="<?php echo @$this->element->payment_params->payment_max_weight; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="payment_min_volume"><?php
							echo JText::_('SHIPPING_MIN_VOLUME');
						?></label>
					</td>
					<td>
						<input type="text" id="payment_min_volume" name="data[payment][payment_params][payment_min_volume]" value="<?php echo @$this->element->payment_params->payment_min_volume; ?>"/>
						<?php
							echo $this->data['volume']->display('data[payment][payment_params][payment_size_unit]',@$this->element->payment_params->payment_size_unit);
						?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="payment_max_volume"><?php
							echo JText::_('SHIPPING_MAX_VOLUME');
						?></label>
					</td>
					<td>
						<input type="text" id="payment_max_volume" name="data[payment][payment_params][payment_max_volume]" value="<?php echo @$this->element->payment_params->payment_max_volume; ?>"/>
					</td>
				</tr>
<?php
		}


		if($this->plugin_type == 'shipping') {
?>
				<tr>
					<td class="key">
						<label for="shipping_zip_regex"><?php
							echo JText::_('SHIPPING_ZIP_REGEX');
						?></label>
					</td>
					<td>
						<input type="text" id="shipping_zip_regex" name="data[shipping][shipping_params][shipping_zip_regex]" value="<?php echo @$this->element->shipping_params->shipping_zip_regex; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="shipping_zip_prefix"><?php
							echo JText::_('SHIPPING_PREFIX');
						?></label>
					</td>
					<td>
						<input type="text" id="shipping_zip_prefix" name="data[shipping][shipping_params][shipping_zip_prefix]" value="<?php echo @$this->element->shipping_params->shipping_zip_prefix; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="shipping_min_zip"><?php
							echo JText::_('SHIPPING_MIN_ZIP');
						?></label>
					</td>
					<td>
						<input type="text" id="shipping_min_zip" name="data[shipping][shipping_params][shipping_min_zip]" value="<?php echo @$this->element->shipping_params->shipping_min_zip; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="shipping_max_zip"><?php
							echo JText::_('SHIPPING_MAX_ZIP');
						?></label>
					</td>
					<td>
						<input type="text" id="shipping_max_zip" name="data[shipping][shipping_params][shipping_max_zip]" value="<?php echo @$this->element->shipping_params->shipping_max_zip; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="shipping_zip_suffix"><?php
							echo JText::_('SHIPPING_SUFFIX');
						?></label>
					</td>
					<td>
						<input type="text" id="shipping_zip_suffix" name="data[shipping][shipping_params][shipping_zip_suffix]" value="<?php echo @$this->element->shipping_params->shipping_zip_suffix; ?>"/>
					</td>
				</tr>
<?php
		}
		if($this->plugin_type == 'payment') {
?>
				<tr>
					<td class="key">
						<label for="payment_zip_regex"><?php
							echo JText::_('SHIPPING_ZIP_REGEX');
						?></label>
					</td>
					<td>
						<input type="text" id="payment_zip_regex" name="data[payment][payment_params][payment_zip_regex]" value="<?php echo @$this->element->payment_params->payment_zip_regex; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="payment_zip_prefix"><?php
							echo JText::_('SHIPPING_PREFIX');
						?></label>
					</td>
					<td>
						<input type="text" id="payment_zip_prefix" name="data[payment][payment_params][payment_zip_prefix]" value="<?php echo @$this->element->payment_params->payment_zip_prefix; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="payment_min_zip"><?php
							echo JText::_('SHIPPING_MIN_ZIP');
						?></label>
					</td>
					<td>
						<input type="text" id="payment_min_zip" name="data[payment][payment_params][payment_min_zip]" value="<?php echo @$this->element->payment_params->payment_min_zip; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="payment_max_zip"><?php
							echo JText::_('SHIPPING_MAX_ZIP');
						?></label>
					</td>
					<td>
						<input type="text" id="payment_max_zip" name="data[payment][payment_params][payment_max_zip]" value="<?php echo @$this->element->payment_params->payment_max_zip; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="payment_zip_suffix"><?php
							echo JText::_('SHIPPING_SUFFIX');
						?></label>
					</td>
					<td>
						<input type="text" id="payment_zip_suffix" name="data[payment][payment_params][payment_zip_suffix]" value="<?php echo @$this->element->payment_params->payment_zip_suffix; ?>"/>
					</td>
				</tr>
<?php
		}
?>
				</table>
			</div>
		</div>
<?php
	}
?>
		<div class="hikashop_tile_block"><div style="min-height:auto;">
			<div class="hikashop_tile_title"><?php
				echo JText::_('ACCESS_LEVEL');
			?></div>
<?php
	if(hikashop_level(2)) {
		$acltype = hikashop_get('type.acl');
		$access = $type.'_access';
		echo $acltype->display($access, @$this->element->$access, $type);
	} else {
		echo '<small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>';
	}
?>
		</div></div>
	</div>
</div>
		<input type="hidden" name="data[<?php echo $type;?>][<?php echo $type;?>_id]" value="<?php echo $this->id;?>"/>
		<input type="hidden" name="data[<?php echo $type;?>][<?php echo $type;?>_type]" value="<?php echo $this->name;?>"/>
		<input type="hidden" name="task" value="save"/>
<?php
}
?>
		<input type="hidden" name="name" value="<?php echo $this->name;?>"/>
		<input type="hidden" name="subtask" value="<?php echo hikaInput::get()->getVar('subtask', '');?>"/>
		<input type="hidden" name="ctrl" value="plugins" />
		<input type="hidden" name="plugin_type" value="<?php echo $this->plugin_type;?>" />
		<input type="hidden" name="<?php echo $this->plugin_type; ?>_plugin_type" value="<?php echo $this->name; ?>"/>
		<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
<script type="text/javascript">
function selectNone(name) {
	var el = document.getElementById(name);
	if(!el) return false;
	for (var i = 0; i < el.options.length; i++) {
		el.options[i].selected = false;
	}
}
function hikashop_switch_tr(el, name, num) {
	var d = document, s = (el.value == '1');
	if(!el.checked) { s = !s; }
	if(num === undefined) {
		var e = d.getElementById(name);
		if(!e) return;
		e.style.display = (s?'':'none');
		return;
	}
	var e = null;
	for(var i = num; i >= 0; i--) {
		var e = d.getElementById(name + i);
		if(e) {
			e.style.display = (s?'':'none');
		}
	}
}

function hikashopToggleTax(value) {
	var elements = document.querySelectorAll("[data-tax-display]");
	for(var i = elements.length - 1; i >= 0; i--) {
		elements[i].style.display = (elements[i].getAttribute("data-tax-display") == value) ? "none" : "";
	}
}
window.hikashop.ready( function(){ hikashopToggleTax('<?php echo (int) @$this->element->shipping_params->shipping_tax; ?>'); });
</script>

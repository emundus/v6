<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
echo $this->leftmenu(
	'main',
	array(
		'#main_global' => JText::_('MAIN'),
		'#main_tax' => JText::_('TAXES'),
		'#main_product' => JText::_('PRODUCT'),
		'#main_cart' => JText::_('HIKASHOP_CHECKOUT_CART'),
		'#main_order' => JText::_('HIKASHOP_ORDER'),
		'#main_files' => JText::_('FILES'),
		'#main_images' => JText::_('HIKA_IMAGES'),
		'#main_emails' => JText::_('EMAILS')
	)
);
?>
<div id="page-main" class="rightconfig-container <?php if(HIKASHOP_BACK_RESPONSIVE) echo 'rightconfig-container-j30';?>">
<!-- MAIN - GLOBAL -->
<div id="main_global" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('MAIN'); ?></div>
<table class="hk_config_table table" style="width:100%">
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('version');?>><?php echo JText::_('VERSION');?></td>
		<td>
			HikaShop <?php echo $this->config->get('level').' '.$this->config->get('version'); ?> [2306262337]
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('store_offline');?>><?php echo JText::_('PUT_STORE_OFFLINE'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[store_offline]', 'onchange="if(this.checked && this.value==1) alert(\''.JText::_('STORE_OFFLINE_WARNING',true).'\');"', $this->config->get('store_offline',0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('store_address');?>><?php echo JText::_('STORE_ADDRESS'); ?></td>
		<td>
			<textarea class="inputbox" name="config_store_address" cols="30" rows="5"><?php echo $this->config->get('store_address'); ?></textarea>
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('image_address_path');?>><?php echo JText::_('LOGO'); ?></td>
		<td>
			<input type="text" class="inputbox" name="config[image_address_path]" value="<?php echo $this->escape($this->config->get('image_address_path'));?>" />
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('img_style_css');?>><?php echo JText::_('HIKA_IMAGE_ADDRESS_CSS'); ?></td>
		<td>
			<input type="text" class="inputbox" name="config[img_style_css]" value="<?php echo $this->escape($this->config->get('img_style_css'));?>"/>
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('main_currency');?>><?php echo JText::_('MAIN_CURRENCY'); ?></td>
		<td>
			<?php echo $this->currency->display('config[main_currency]',$this->config->get('main_currency')); ?>
			<a target="_blank" href="<?php echo hikashop_completeLink('currency');?>" class="btn btn-primary" <?php echo $this->docTip('access_currency_manager');?>>
				<i class="fa fa-chevron-right" aria-hidden="true"></i>
			</a>
		</td>
	</tr>
</table>
	</div></div>
</div>

<?php if($this->config->get('default_type') != 'individual') { ?>
<!-- MAIN - ADDRESS -->
<div id="main_address" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('ADDRESS'); ?></div>
<table class="hk_config_table table" style="width:100%">
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('default_type');?>><?php echo JText::_('DEFAULT_ADDRESS_TYPE'); ?></td>
		<td><?php
			echo $this->tax->display('config[default_type]', $this->config->get('default_type'));
		?></td>
	</tr>
</table>
	</div></div>
</div>
<?php } ?>

<!-- MAIN - TAX -->
<div id="main_tax" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('TAXES'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('main_tax_zone');?>><?php echo JText::_('MAIN_TAX_ZONE'); ?></td>
		<td><?php
			echo $this->nameboxType->display(
				'config[main_tax_zone]',
				@$this->zone->zone_id,
				hikashopNameboxType::NAMEBOX_SINGLE,
				'zone',
				array(
					'default_text' => JText::_('HIKA_NONE'),
					'type' => 'id'
				)
			);
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('tax_zone_type');?>><?php echo JText::_('ZONE_TAX_ADDRESS_TYPE'); ?></td>
		<td><?php
			echo $this->tax_zone->display('config[tax_zone_type]',$this->config->get('tax_zone_type'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('detailed_tax_display');?>><?php echo JText::_('DETAILED_TAX_DISPLAY');?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[detailed_tax_display]', '', @$this->config->get('detailed_tax_display'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('price_with_tax');?>><?php echo JText::_('SHOW_TAXED_PRICES'); ?></td>
		<td><?php
			echo $this->pricetaxType->display('config[price_with_tax]' , $this->config->get('price_with_tax',@$this->default_params['price_with_tax']));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('vat_check');?>><?php echo JText::_('VAT_CHECK'); ?></td>
		<td><?php
			echo $this->vat->display('config[vat_check]',$this->config->get('vat_check'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('round_calculations');?>><?php echo JText::_('ROUND_PRICES_DURING_CALCULATIONS'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist','config[round_calculations]' , '', $this->config->get('round_calculations',0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('floating_tax_prices');?>><?php echo JText::_('FLOATING_TAX_PRICES'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist','config[floating_tax_prices]' , '', $this->config->get('floating_tax_prices',0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('discount_before_tax');?>><?php echo JText::_('APPLY_DISCOUNTS'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[discount_before_tax]",'',$this->config->get('discount_before_tax'),JTEXT::_('BEFORE_TAXES'),JTEXT::_('AFTER_TAXES'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('coupon_before_tax');?>><?php echo JText::_('APPLY_COUPONS'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[coupon_before_tax]",'',$this->config->get('coupon_before_tax'),JTEXT::_('BEFORE_TAXES'),JTEXT::_('AFTER_TAXES'));
		?></td>
	</tr>

</table>
	</div></div>
</div>

<!-- MAIN - PRODUCT -->
<div id="main_product" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('PRODUCT'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('characteristics_values_sorting');?>><?php echo JText::_('CHARACTERISTICS_VALUES_ORDER'); ?></td>
		<td><?php
			echo $this->characteristicorderType->display('config[characteristics_values_sorting]',$this->config->get('characteristics_values_sorting'));
		?></td>
	</tr>
<?php if(!$this->config->get('append_characteristic_values_to_product_name', 1)) { ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('append_characteristic_values_to_product_name');?>><?php echo JText::_('APPEND_CHARACTERISTICS_VALUE_TO_PRODUCT_NAME'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[append_characteristic_values_to_product_name]', '', $this->config->get('append_characteristic_values_to_product_name', 1));
		?></td>
	</tr>
<?php } ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('update_stock_after_confirm');?>><?php echo JText::_('UPDATE_AFTER_ORDER_CONFIRM'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[update_stock_after_confirm]', '', $this->config->get('update_stock_after_confirm'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('display_add_to_cart_for_free_products');?>><?php echo JText::_('DISPLAY_ADD_TO_CART_BUTTON_FOR_FREE_PRODUCT'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[display_add_to_cart_for_free_products]','',$this->config->get('display_add_to_cart_for_free_products'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('show_out_of_stock');?>><?php echo JText::_('DISPLAY_OUT_OF_STOCK_PRODUCTS');?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[show_out_of_stock]', '', $this->config->get('show_out_of_stock', 1));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('product_association_in_both_ways');?>><?php echo JText::_('PRODUCT_ASSOCIATION_IN_BOTH_WAYS'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[product_association_in_both_ways]', '', $this->config->get('product_association_in_both_ways', 1));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('group_options');?>><?php echo JText::_('GROUP_OPTIONS_WITH_PRODUCT'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[group_options]', 'onchange="displayGroupoptionsChange(this.value)"', $this->config->get('group_options', 0));
		?></td>
	</tr>
	<tr id="hikashop_groupoptions_change_row"<?php if(!$this->config->get('group_options', 0)) { echo ' style="display:none;"'; } ?>>
		<td class="hk_tbl_key"<?php echo $this->docTip('shipping_group_product_options');?>><?php echo JText::_('SHIPPING_GROUP_PRODUCT_OPTIONS'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[shipping_group_product_options]', '', $this->config->get('shipping_group_product_options', 0));
		?></td>
	</tr>
</table>
	</div></div>
</div>

<!-- MAIN - CART -->
<div id="main_cart" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('HIKASHOP_CHECKOUT_CART'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('clean_cart');?>><?php echo JText::_('CLEAN_CART_WHEN_ORDER_IS'); ?></td>
		<td><?php
			$values = array(
				JHTML::_('select.option', 'order_created', JText::_('CREATED')),
				JHTML::_('select.option', 'order_confirmed', JText::_('CONFIRMED'))
			);
			echo JHTML::_('select.genericlist', $values, 'config[clean_cart]', 'class="custom-select" size="1"', 'value', 'text', $this->config->get('clean_cart','order_created'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('cart_item_limit');?>><?php echo JText::_('LIMIT_NUMBER_OF_ITEMS_IN_CART'); ?></td>
		<td><?php
			if(hikashop_level(1)) {
				$item_limit = (int)$this->config->get('cart_item_limit', 0);
				if(empty($item_limit)) {
					$item_limit = JText::_('UNLIMITED');
				}
				?><input name="config[cart_item_limit]" type="text" value="<?php echo $item_limit; ?>" onfocus="if(this.value == '<?php echo JText::_('UNLIMITED', true); ?>') this.value = '';"/><?php
			} else {
				echo hikashop_getUpgradeLink('essential');
			}
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('synchronized_add_to_cart');?>><?php echo JText::_('SYNCHRONIZED_ADD_TO_CART'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[synchronized_add_to_cart]', '', $this->config->get('synchronized_add_to_cart', 0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('redirect_url_when_cart_is_empty');?>><?php echo JText::_('WHEN_CART_IS_EMPTY'); ?></td>
		<td>
			<input type="text" class="inputbox" name="config[redirect_url_when_cart_is_empty]" value="<?php echo $this->escape($this->config->get('redirect_url_when_cart_is_empty'));?>"/>
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('print_cart');?>><?php echo JText::_('ALLOW_USERS_TO_PRINT_CART'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[print_cart]', '', $this->config->get('print_cart', 0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('cart_ip');?>><?php echo JText::_('LOG_IP_ADDRESS'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[cart_ip]', '', $this->config->get('cart_ip', 1));
		?></td>
	</tr>

</table>
	</div></div>
</div>

<!-- MAIN - ORDER -->
<div id="main_order" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('HIKASHOP_ORDER'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('order_number_format');?>><?php echo JText::_('ORDER_NUMBER_FORMAT'); ?></td>
		<td><?php
			if(hikashop_level(1)) {
				?><input class="inputbox" type="text" name="config[order_number_format]" value="<?php echo $this->escape($this->config->get('order_number_format','{automatic_code}')); ?>"><?php
			} else {
				echo hikashop_getUpgradeLink('essential');
			}
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('invoice_number_format');?>><?php echo JText::_('INVOICE_NUMBER_FORMAT'); ?></td>
		<td><?php
			if(hikashop_level(1)) {
				?><input class="inputbox" type="text" name="config[invoice_number_format]" value="<?php echo $this->escape($this->config->get('invoice_number_format','{automatic_code}')); ?>"><?php
			} else {
				echo hikashop_getUpgradeLink('essential');
			}
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('invoice_reset_frequency');?>><?php echo JText::_('INVOICE_RESET_FREQUENCY'); ?></td>
		<td><?php
			if(hikashop_level(1)) {
				$values = array(
					JHTML::_('select.option', '', JText::_('HIKA_NONE')),
					JHTML::_('select.option', 'year', JText::_('EVERY_YEARS')),
					JHTML::_('select.option', 'month', JText::_('EVERY_MONTHS')),
					JHTML::_('select.option', '*'.'/'.'*', JText::_('EVERY_DAYS')),
				);
				$value = $this->config->get('invoice_reset_frequency', '');
				if(strpos($value, '/') !== false && $value != '*'.'/'.'*') {
					$values[] = JHTML::_('select.option', $value, $value);
				}
				echo JHTML::_('select.genericlist', $values, 'config[invoice_reset_frequency]', 'class="custom-select" size="1"', 'value', 'text', $value);
			} else {
				echo hikashop_getUpgradeLink('essential');
			}
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('allow_payment_button');?>><?php echo JText::_('ALLOW_CUSTOMERS_TO_PAY_ORDERS_AFTERWARD'); ?></td>
		<td><?php
			if(hikashop_level(1)) {
				echo JHTML::_('hikaselect.booleanlist', 'config[allow_payment_button]','onchange="displayPaymentChange(this.value)"',$this->config->get('allow_payment_button'));
			} else {
				echo hikashop_getUpgradeLink('essential');
			}
		?></td>
	</tr>
	<tr id="hikashop_payment_change_row"<?php if(!$this->config->get('allow_payment_button')) { echo ' style="display:none;"'; } ?>>
		<td class="hk_tbl_key"<?php echo $this->docTip('allow_payment_change');?>><?php echo JText::_('ALLOW_CUSTOMERS_TO_CHANGE_THEIR_PAYMENT_METHOD_AFTER_CHECKOUT'); ?></td>
		<td><?php
			if(hikashop_level(1)) {
				echo JHTML::_('hikaselect.booleanlist', 'config[allow_payment_change]','',$this->config->get('allow_payment_change',1));
			} else {
				echo hikashop_getUpgradeLink('essential');
			}
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('allow_reorder');?>><?php echo JText::_('ALLOW_CUSTOMERS_TO_REORDER'); ?></td>
		<td><?php
			if(hikashop_level(1)) {
				echo JHTML::_('hikaselect.booleanlist', 'config[allow_reorder]','',$this->config->get('allow_reorder',0));
			} else {
				echo hikashop_getUpgradeLink('essential');
			}
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('order_ip');?>><?php echo JText::_('LOG_IP_ADDRESS'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[order_ip]', '', $this->config->get('order_ip', 1));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('history_ip');?>><?php echo JText::_('LOG_HISTORY_IP_ADDRESS'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[history_ip]', '', $this->config->get('history_ip', 1));
		?></td>
	</tr>
</table>
	</div></div>
</div>

<!-- MAIN - FILES -->
<div id="main_files" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('FILES'); ?></div>
<table class="hk_config_table table" style="width:100%">
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('enable_customer_downloadlist');?>><?php echo JText::_('ENABLE_CUSTOMER_DOWNLOADLIST'); ?></td>
		<td><?php
			if(hikashop_level(1)) {
				echo JHTML::_('hikaselect.booleanlist', 'config[enable_customer_downloadlist]', '', $this->config->get('enable_customer_downloadlist'));
			} else {
				echo hikashop_getUpgradeLink('essential');
			}
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('allowedfiles');?>><?php echo JText::_('ALLOWED_FILES'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[allowedfiles]" size="50" value="<?php echo strtolower(str_replace(' ','',$this->config->get('allowedfiles'))); ?>" />
		</td>
	</tr>

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('uploadsecurefolder');?>><?php echo JText::_('UPLOAD_SECURE_FOLDER'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[uploadsecurefolder]" size="50" value="<?php echo $this->config->get('uploadsecurefolder'); ?>" />
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('uploadfolder');?>><?php echo JText::_('UPLOAD_FOLDER'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[uploadfolder]" size="50" value="<?php echo $this->config->get('uploadfolder'); ?>" />
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('payment_log_file');?>><?php echo JText::_('PAYMENT_LOG_FILE'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[payment_log_file]" size="50" value="<?php echo $this->config->get('payment_log_file'); ?>" />
<?php
	echo $this->popup->display(
		'<button type="button" class="btn" onclick="return false">'.JText::_('REPORT_SEE').'</button>',
		'PAYMENT_LOG_FILE',
		hikashop_completeLink('config&task=seepaymentreport',true),
		'hikashop_log_file',
		760, 480, '', '', 'link'
	);
?>
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('download_time_limit');?>><?php echo JText::_('DOWNLOAD_TIME_LIMIT'); ?></td>
		<td><?php
			echo $this->delayTypeDownloads->display('config[download_time_limit]',$this->config->get('download_time_limit',0),3);
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('download_number_limit');?>><?php echo JText::_('DOWNLOAD_NUMBER_LIMIT'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[download_number_limit]" value="<?php echo $this->config->get('download_number_limit'); ?>" />
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('display_downloads_on_product_page');?>><?php echo JText::_('PURCHASED_FILE_DOWNLOAD_ON_PRODUCT_PAGE'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[display_downloads_on_product_page]', '', $this->config->get('display_downloads_on_product_page', 0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('csv_separator');?>><?php echo JText::_('CSV_SEPARATOR'); ?></td>
		<td><?php
			echo $this->csvType->display('config[csv_separator]',$this->config->get('csv_separator',';'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('csv_decimal_separator');?>><?php echo JText::_('CSV_DECIMAL_SEPARATOR'); ?></td>
		<td><?php
			echo $this->csvDecimalType->display('config[csv_decimal_separator]',$this->config->get('csv_decimal_separator','.'));
		?></td>
	</tr>

</table>
	</div></div>
</div>

<!-- MAIN - IMAGES -->
<div id="main_images" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('HIKA_IMAGES'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('allowedimages');?>><?php echo JText::_('ALLOWED_IMAGES'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[allowedimages]" size="50" value="<?php echo strtolower(str_replace(' ','',$this->config->get('allowedimages'))); ?>" />
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('default_image');?>><?php echo JText::_('DEFAULT_IMAGE'); ?></td>
		<td>
<?php
	$options = array(
		'upload' => true,
		'tooltip' => true,
		'gallery' => true,
		'text' => JText::_('HIKA_DEFAULT_IMAGE_EMPTY_UPLOAD'),
		'uploader' => array('config', 'default_image'),
	);
	$params = new stdClass();
	$params->file_path = $this->config->get('default_image', '');
	$params->field_name = 'config[default_image]';
	$img = $this->imageHelper->getThumbnail($params->file_path, array(100, 100), array('default' => true));
	if($img->success) {
		$params->thumbnail_url = $img->url;
		$params->origin_url = $img->origin_url;
	}
	$js = '';
	$content = hikashop_getLayout('upload', 'image_entry', $params, $js);
	echo $this->uploaderType->displayImageSingle('hikashop_config_default_image', $content, $options);
?>
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('thumbnail');?>><?php echo JText::_('THUMBNAIL'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[thumbnail]', '', $this->config->get('thumbnail'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('thumbnail_xy');?>><?php echo JText::_('THUMBNAIL_XY'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[thumbnail_x]" value="<?php echo $this->config->get('thumbnail_x'); ?>" />
			px <i class="fas fa-times fa-2x"></i>
			<input class="inputbox" type="text" name="config[thumbnail_y]" value="<?php echo $this->config->get('thumbnail_y'); ?>" />
			px
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('product_image_xy');?>><?php echo JText::_('PRODUCT_PAGE_IMAGE_XY'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[product_image_x]" value="<?php echo $this->config->get('product_image_x'); ?>" />
			px <i class="fas fa-times fa-2x"></i>
			<input class="inputbox" type="text" name="config[product_image_y]" value="<?php echo $this->config->get('product_image_y'); ?>" />
			px
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('image_force_size');?>><?php echo JText::_('IMAGE_FORCE_SIZE'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[image_force_size]" , '',$this->config->get('image_force_size', true));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('image_scale_mode');?>><?php echo JText::_('IMAGE_SCALE_MODE'); ?></td>
		<td><?php
			$arr = array(
				JHTML::_('select.option', 'inside', JText::_('IMAGE_KEEP_RATIO')),
				JHTML::_('select.option', 'outside', JText::_('IMAGE_CROP')),
			);
			echo JHTML::_('hikaselect.genericlist', $arr, 'config[image_scale_mode]', 'class="custom-select"', 'value', 'text',$this->config->get('image_scale_mode', 'inside'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('image_xy');?>><?php echo JText::_('IMAGE_XY'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[image_x]" value="<?php echo $this->config->get('image_x'); ?>" />
			px <i class="fas fa-times fa-2x"></i>
			<input class="inputbox" type="text" name="config[image_y]" value="<?php echo $this->config->get('image_y'); ?>" />
			px
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('images_stripes_background');?>><?php echo JText::_('IMAGES_STRIPES_COLOR'); ?></td>
		<td>
<?php
	$type = hikashop_get('type.color');
	echo $type->displayAll('images_stripes_background','config[images_stripes_background]', $this->config->get('images_stripes_background', ''));
?>
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('keep_category_product_images');?>><?php echo JText::_('KEEP_IMAGES_AFTER_DELETE'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[keep_category_product_images]" , '',$this->config->get('keep_category_product_images', 0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('watermark');?>><?php echo JText::_('WATERMARK_ON_IMAGES'); ?></td>
		<td><?php
	if(hikashop_level(2)) {
		$options = array(
			'upload' => true,
			'tooltip' => true,
			'gallery' => true,
			'text' => JText::_('HIKA_DEFAULT_IMAGE_EMPTY_UPLOAD'),
			'uploader' => array('config', 'watermark'),
		);
		$params = new stdClass();
		$params->file_path = $this->config->get('watermark', '');
		$params->delete = true;
		$params->uploader_id = 'hikashop_config_watermark_image';
		$params->field_name = 'config[watermark]';
		$js = '';
		$content = hikashop_getLayout('upload', 'image_entry', $params, $js);
		if(!empty($params->empty))
			$options['empty'] = true;
		echo $this->uploaderType->displayImageSingle('hikashop_config_watermark_image', $content, $options);
	}else{
		echo hikashop_getUpgradeLink('business');
	}
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('opacity');?>><?php echo JText::_('WATERMARK_OPACITY'); ?></td>
		<td><?php
			if(hikashop_level(2)) {
				?><input class="inputbox" type="text" name="config[opacity]" value="<?php echo $this->config->get('opacity',0); ?>" size="3" />%<?php
			} else {
				echo hikashop_getUpgradeLink('business');
			}
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('add_webp_images');?>><?php echo JText::_('GENERATE_WEBP_IMAGES'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[add_webp_images]" , '',$this->config->get('add_webp_images', 1));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('variant_images_behavior');?>><?php echo JText::_('VARIANT_IMAGES_BEHAVIOR'); ?></td>
		<td><?php
			$arr = array(
				JHTML::_('select.option', 'replace_main_product_images', JText::_('REPLACE_MAIN_PRODUCT_IMAGES')),
				JHTML::_('select.option', 'display_along_main_product_images', JText::_('DISPLAY_ALONG_MAIN_PRODUCT_IMAGES')),
				JHTML::_('select.option', 'display_along_before_product_images', JText::_('DISPLAY_BEFORE_MAIN_PRODUCT_IMAGES')),
			);
			echo JHTML::_('hikaselect.genericlist', $arr, 'config[variant_images_behavior]', 'class="custom-select"', 'value', 'text',$this->config->get('variant_images_behavior', 'replace_main_product_images'));
		?></td>

	</tr>
</table>
	</div></div>
</div>

<!-- MAIN - EMAILS -->
<div id="main_emails" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('EMAILS'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('from_name');?>><?php echo JText::_('FROM_NAME'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[from_name]" size="40" value="<?php echo $this->escape($this->config->get('from_name')); ?>">
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('from_email');?>><?php echo JText::_('FROM_ADDRESS'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[from_email]" size="40" value="<?php echo $this->escape($this->config->get('from_email')); ?>">
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('reply_name');?>><?php echo JText::_('REPLYTO_NAME'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[reply_name]" size="40" value="<?php echo $this->escape($this->config->get('reply_name')); ?>">
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('reply_email');?>><?php echo JText::_('REPLYTO_ADDRESS'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[reply_email]" size="40" value="<?php echo $this->escape($this->config->get('reply_email')); ?>">
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('bounce_email');?>><?php echo JText::_('BOUNCE_ADDRESS'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[bounce_email]" size="40" value="<?php echo $this->escape($this->config->get('bounce_email')); ?>">
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('payment_notification_email');?>><?php echo JText::_('PAYMENTS_NOTIFICATIONS_EMAIL_ADDRESS'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[payment_notification_email]" size="40" value="<?php echo $this->escape($this->config->get('payment_notification_email')); ?>">
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('order_creation_notification_email');?>><?php echo JText::_('ORDER_CREATION_NOTIFICATION_EMAIL_ADDRESS'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[order_creation_notification_email]" size="40" value="<?php echo $this->escape($this->config->get('order_creation_notification_email')); ?>">
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('contact_request_email');?>><?php echo JText::_('CONTACT_REQUEST_EMAIL_ADDRESS'); ?></td>
		<td>
			<input class="inputbox" type="text" placeholder="<?php echo $this->escape($this->config->get('from_email')); ?>" name="config[contact_request_email]" size="40" value="<?php echo $this->escape($this->config->get('contact_request_email')); ?>">
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('add_names');?>><?php echo JText::_('ADD_NAMES'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[add_names]', '', $this->config->get('add_names', true));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('encoding_format');?>><?php echo JText::_('ENCODING_FORMAT'); ?></td>
		<td><?php
			echo $this->encodingType->display('config[encoding_format]', $this->config->get('encoding_format', 'base64'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('charset');?>><?php echo JText::_('CHARSET'); ?></td>
		<td><?php
			echo $this->charsetType->display('config[charset]', $this->config->get('charset', 'UTF-8'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('word_wrapping');?>><?php echo JText::_('WORD_WRAPPING'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[word_wrapping]" size="10" value="<?php echo $this->config->get('word_wrapping',0) ?>">
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('email_favicon');?>><?php echo JText::_('EMAIL_FAVICON'); ?></td>
		<td>
			<input class="inputbox" type="text" placeholder="<?php echo HIKASHOP_LIVE.'media/com_hikashop/images/icons/icon-32-show_cart.png'; ?>" name="config[email_favicon]" size="40" value="<?php echo $this->escape($this->config->get('email_favicon')); ?>">
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('embed_images');?>><?php echo JText::_('EMBED_IMAGES'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[embed_images]', '', $this->config->get('embed_images', 0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('embed_files');?>><?php echo JText::_('EMBED_ATTACHMENTS'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[embed_files]', '', $this->config->get('embed_files', 1));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('multiple_part');?>><?php echo JText::_('MULTIPLE_PART'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[multiple_part]', '', $this->config->get('multiple_part', 0));
		?></td>
	</tr>
<?php if(file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php')){ ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('mail_folder');?>><?php echo JText::_('MAIL_FOLDER'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[mail_folder]" size="60" value="<?php echo $this->escape($this->config->get('mail_folder')); ?>">
		</td>
	</tr>
<?php } ?>

</table>
	</div></div>
</div>

</div>

<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
echo $this->leftmenu(
	'market',
	array(
		'#market_general' => JText::_('HIKAM_OPTIONS_GENERAL'),
		'#market_images' => JText::_('HIKAM_OPTIONS_IMAGES'),
		'#market_display' => JText::_('HIKAM_OPTIONS_SHOW'),
		'#market_email' => JText::_('HIKAM_OPTIONS_EMAIL'),
		'#market_registration' => JText::_('HIKAM_OPTIONS_REGISTRATION'),
		'#market_categories' => JText::_('HIKAM_OPTIONS_CATEGORIES'),
		'#market_limitations' => JText::_('HIKAM_OPTIONS_TITLE_VENDOR_LIMITATIONS'),
		'#market_tax' => JText::_('HIKAM_OPTIONS_TAX'),
	)
);
?>
<div id="page-market" class="rightconfig-container <?php if(HIKASHOP_BACK_RESPONSIVE) echo 'rightconfig-container-j30';?>">

<div id="market_general" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('HIKAM_OPTIONS_GENERAL'); ?></div>
<table class="hk_config_table table" style="width:100%">

<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('updatable_order_statuses');?>><?php echo JText::_('HIKAM_UPDATABLE_ORDER_STATUSES'); ?></td>
	<td><?php
		$order_statuses = explode(',', $this->config->get('updatable_order_statuses', 'created'));
		if(!empty($order_statuses)) {
			foreach($order_statuses as &$order_status) {
				$order_status = trim($order_status);
			}
			unset($order_status);
		}
		echo $this->nameboxType->display(
			'config[updatable_order_statuses]',
			$order_statuses,
			hikamarketNameboxType::NAMEBOX_MULTIPLE,
			'order_status',
			array(
				'delete' => true,
				'sort' => false,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				'force_data' => true
			)
		);
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('valid_order_statuses');?>><?php echo JText::_('HIKAM_VALID_ORDER_STATUSES'); ?></td>
	<td><?php
		$order_statuses = explode(',', $this->config->get('valid_order_statuses', 'confirmed,shipped'));
		if(!empty($order_statuses)) {
			foreach($order_statuses as &$order_status) {
				$order_status = trim($order_status);
			}
			unset($order_status);
		}
		echo $this->nameboxType->display(
			'config[valid_order_statuses]',
			$order_statuses,
			hikamarketNameboxType::NAMEBOX_MULTIPLE,
			'order_status',
			array(
				'delete' => true,
				'sort' => false,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				'force_data' => true
			)
		);
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('use_same_order_number');?>><?php echo JText::_('MARKET_USE_SAME_ORDER_NUMBER'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[use_same_order_number]",'',$this->config->get('use_same_order_number',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('prefix_product_code');?>><?php echo JText::_('PREFIX_VENDOR_PRODUCT_CODE'); ?></td>
	<td>
		<input type="text" name="config[prefix_product_code]" value="<?php echo $this->escape( @$this->config->get('prefix_product_code', '') ); ?>" />
	</td>
</tr>
<tr class="option_title">
	<td colspan="2"><?php echo JText::_('HIKAM_OPTIONS_TITLE_VENDOR_SELECTION'); ?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('allow_zone_vendor');?>><?php echo JText::_('ALLOW_ZONE_VENDORS'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[allow_zone_vendor]",'',$this->config->get('allow_zone_vendor', 0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('vendor_select_custom_field');?>><?php echo JText::_('ALLOW_VENDOR_SELECTOR'); ?></td>
	<td><?php
		$options = array(
			JHTML::_('select.option', '', JText::_('HIKA_NONE'))
		);
		if(!empty($this->vendorselect_customfields)) {
			foreach($this->vendorselect_customfields as $field) {
				if(in_array($field->field_table, array('order', 'item')))
					$options[] = JHTML::_('select.option', $field->field_namekey, $field->field_table . ' - ' . $field->field_realname);
			}
		}
		echo JHTML::_('select.genericlist', $options, 'config[vendor_select_custom_field]', '', 'value', 'text', $this->config->get('vendor_select_custom_field', ''));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('preferred_vendor_select_custom_field');?>><?php echo JText::_('PREFERRED_VENDOR_FOR_VENDOR_SELECTOR'); ?></td>
	<td><?php
		$options = array(
			JHTML::_('select.option', '', JText::_('HIKA_NONE'))
		);
		if(!empty($this->vendorselect_customfields)) {
			foreach($this->vendorselect_customfields as $field) {
				if($field->field_table == 'user')
					$options[] = JHTML::_('select.option', $field->field_namekey, $field->field_table . ' - ' . $field->field_realname);
			}
		}
		echo JHTML::_('select.genericlist', $options, 'config[preferred_vendor_select_custom_field]', '', 'value', 'text', $this->config->get('preferred_vendor_select_custom_field', ''));
	?></td>
</tr>
<tr class="option_title">
	<td colspan="2"><?php echo JText::_('HIKAM_OPTIONS_TITLE_VENDOR_PAYMENT_SHIPPING'); ?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('vendors_in_cart');?>><?php echo JText::_('LIMIT_VENDORS_IN_CART'); ?></td>
	<td><?php
		$options = array(
			JHTML::_('select.option', 0, JText::_('HIKAM_NO_LIMIT_VENDOR')),
			JHTML::_('select.option', 1, JText::_('HIKAM_LIMIT_ONE_VENDOR')),
			JHTML::_('select.option', 2, JText::_('HIKAM_LIMIT_ONE_EXTRA_VENDOR'))
		);
		echo JHTML::_('hikaselect.radiolist', $options, 'config[vendors_in_cart]', '', 'value', 'text', $this->config->get('vendors_in_cart', 0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('only_vendor_payments');?>><?php echo JText::_('SHOW_ONLY_VENDOR_PAYMENTS'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', 'config[only_vendor_payments]', '', $this->config->get('only_vendor_payments',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('split_order_payment_fees');?>><?php echo JText::_('SPLIT_PAYMENT_FEES_ON_VENDORS'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', 'config[split_order_payment_fees]', '', $this->config->get('split_order_payment_fees',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('split_order_shipping_fees');?>><?php echo JText::_('SPLIT_SHIPPING_FEES_ON_VENDORS'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', 'config[split_order_shipping_fees]', '', $this->config->get('split_order_shipping_fees',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('shipping_per_vendor');?>><?php echo JText::_('SHIPPING_PER_VENDOR'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', 'config[shipping_per_vendor]', '', $this->config->get('shipping_per_vendor', 1));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('plugin_vendor_config');?>><?php echo JText::_('PLUGIN_VENDOR_CONFIG'); ?></td>
	<td><?php
		$options = array(
			JHTML::_('hikaselect.option', 0, JText::_('HIKASHOP_NO')),
			JHTML::_('hikaselect.option', 1, JText::_('HIKAM_OWN_PLUGIN')),
		);
		echo JHTML::_('hikaselect.radiolist', $options, 'config[plugin_vendor_config]', '', 'value', 'text', $this->config->get('plugin_vendor_config', 0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('user_multiple_vendor');?>><?php echo JText::_('MARKET_ALLOW_MULTIPLE_VENDOR_FOR_USERS'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', 'config[user_multiple_vendor]', '', $this->config->get('user_multiple_vendor',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('filter_orderstatus_paid_order');?>><?php echo JText::_('FILTER_ORDER_STATUS_WHEN_VENDOR_PAID'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', 'config[filter_orderstatus_paid_order]', '', $this->config->get('filter_orderstatus_paid_order', 1));
	?></td>
</tr>
<?php
?>
</table>
	</div></div>
</div>

<div id="market_images" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('HIKAM_OPTIONS_IMAGES'); ?></div>
<table class="hk_config_table table" style="width:100%">

<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('default_vendor_image');?>><?php echo JText::_('HIKAM_DEFAULT_VENDOR_IMAGE'); ?></td>
	<td><?php
		$options = array(
			'upload' => true,
			'gallery' => true,
			'text' => JText::_('HIKAM_VENDOR_IMAGE_EMPTY_UPLOAD'),
			'uploader' => array('config', 'default_vendor_image'),
		);
		$params = new stdClass();
		$params->file_path = $this->config->get('default_vendor_image', '');
		$params->field_name = 'config[default_vendor_image]';
		$js = '';
		$content = hikamarket::getLayout('uploadmarket', 'image_entry', $params, $js);

		echo $this->uploaderType->displayImageSingle('hikamarket_config_default_vendor_image', $content, $options);
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('vendor_image_x');?>><?php echo JText::_('HIKAM_VENDOR_IMAGE_WIDTH'); ?></td>
	<td>
		<div class="hk-input-group">
			<input type="text" class="hk-form-control" name="config[vendor_image_x]" value="<?php echo $this->escape( @$this->config->get('vendor_image_x', '') ); ?>" />
			<div class="hk-input-group-append">
				<span class="hk-input-group-text">px</span>
			</div>
		</div>
	</td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('vendor_image_y');?>><?php echo JText::_('HIKAM_VENDOR_IMAGE_HEIGHT'); ?></td>
	<td>
		<div class="hk-input-group">
			<input type="text" class="hk-form-control" name="config[vendor_image_y]" value="<?php echo $this->escape( @$this->config->get('vendor_image_y', '') ); ?>" />
			<div class="hk-input-group-append">
				<span class="hk-input-group-text">px</span>
			</div>
		</div>
	</td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('image_forcesize');?>><?php echo JText::_('HIKAM_IMAGE_FORCESIZE');?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[image_forcesize]",'',$this->config->get('image_forcesize',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('image_grayscale');?>><?php echo JText::_('HIKAM_IMAGE_GRAYSCALE');?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[image_grayscale]",'',$this->config->get('image_grayscale',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('image_scale');?>><?php echo JText::_('HIKAM_IMAGE_SCALE');?></td>
	<td><?php
		$scale_arr = array(
			JHTML::_('select.option', 1, JText::_('HIKAM_IMAGE_SCALE_INSIDE')),
			JHTML::_('select.option', 0, JText::_('HIKAM_IMAGE_SCALE_OUTSIDE')),
		);
		echo JHTML::_('hikaselect.radiolist', $scale_arr, "config[image_scale]" , '', 'value', 'text', $this->config->get('image_scale', 0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('image_radius');?>><?php echo JText::_('HIKAM_IMAGE_RADIUS');?></td>
	<td>
		<div class="hk-input-group">
			<input class="hk-form-control" name="config[image_radius]" type="text" value="<?php echo (int)$this->config->get('image_radius', 0); ?>" />
			<div class="hk-input-group-append">
				<span class="hk-input-group-text">px</span>
			</div>
		</div>
	</td>
</tr>

</table>
	</div></div>
</div>

<div id="market_display" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('HIKAM_OPTIONS_SHOW'); ?></div>
<table class="hk_config_table table" style="width:100%">

<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('display_vendor_vote');?>><?php echo JText::_('HIKAM_DISPLAY_VENDOR_VOTE'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[display_vendor_vote]",'',$this->config->get('display_vendor_vote',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('display_vendor_contact');?>><?php echo JText::_('HIKAM_DISPLAY_VENDOR_CONTACT_BTN'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[display_vendor_contact]",'',$this->config->get('display_vendor_contact',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('show_sold_by');?>><?php echo JText::_('HIKAM_FRONT_SHOW_SOLD_BY'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[show_sold_by]",'',$this->config->get('show_sold_by',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('show_sold_by_me');?>><?php echo JText::_('HIKAM_FRONT_SHOW_SOLD_BY_ME'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[show_sold_by_me]",'',$this->config->get('show_sold_by_me',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('listing_show_main_vendor');?>><?php echo JText::_('HIKAM_FRONT_SHOW_MAIN_VENDOR_IN_LISTING'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[listing_show_main_vendor]",'',$this->config->get('listing_show_main_vendor',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('vendor_default_menu');?>><?php echo JText::_('HIKAM_FRONT_VENDOR_DEFAULT_MENU'); ?></td>
	<td><?php
		echo $this->menusType->display('config[vendor_default_menu]', $this->config->get('vendor_default_menu',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('override_vendor_category_link');?>><?php echo JText::_('HIKAM_FRONT_VENDOR_CATEGORY_TO_VENDOR_PAGE'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[override_vendor_category_link]",'',$this->config->get('override_vendor_category_link',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('vendor_show_modules');?>><?php echo JText::_('VENDOR_SHOW_MODULES'); ?></td>
	<td><?php
		echo $this->nameboxType->display(
			'config[vendor_show_modules]',
			explode(',', $this->config->get('vendor_show_modules')),
			hikamarketNameboxType::NAMEBOX_MULTIPLE,
			'modules',
			array(
				'delete' => true,
				'sort' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				'force_data' => true
			)
		);
	?></td>
</tr>

</table>
	</div></div>
</div>

<div id="market_email" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('HIKAM_OPTIONS_EMAIL'); ?></div>
<table class="hk_config_table table" style="width:100%">

<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('contact_mail_to_vendor');?>><?php echo JText::_('HIKAM_CONTACT_MAIL_TO_VENDORS'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[contact_mail_to_vendor]",'',$this->config->get('contact_mail_to_vendor',1));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('always_send_product_email');?>><?php echo JText::_('HIKAM_ALWAYS_SEND_PRODUCT_EMAIL'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', 'config[always_send_product_email]','',$this->config->get('always_send_product_email',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('mail_display_vendor');?>><?php echo JText::_('HIKAM_DISPLAY_VENDOR_NAME_IN_EMAILS'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', 'config[mail_display_vendor]','',$this->config->get('mail_display_vendor',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('admin_notify_subsale');?>><?php echo JText::_('HIKAM_NOTIFY_ADMIN_FOR_VENDOR_MODIFICATION'); ?></td>
	<td><?php
		$order_statuses = explode(',', $this->config->get('admin_notify_subsale', 'cancelled,refunded'));
		if(!empty($order_statuses)) {
			foreach($order_statuses as &$order_status) {
				$order_status = trim($order_status);
			}
			unset($order_status);
		}
		echo $this->nameboxType->display(
			'config[admin_notify_subsale]',
			$order_statuses,
			hikamarketNameboxType::NAMEBOX_MULTIPLE,
			'order_status',
			array(
				'delete' => true,
				'sort' => false,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				'force_data' => true
			)
		);
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('send_mail_subsale_update_main');?>><?php echo JText::_('HIKAM_SEND_MAIL_SUBSALE_UPDATE_MAIN'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', 'config[send_mail_subsale_update_main]','',$this->config->get('send_mail_subsale_update_main',0));
	?></td>
</tr>

</table>
	</div></div>
</div>

<div id="market_registration" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('HIKAM_OPTIONS_REGISTRATION'); ?></div>
<table class="hk_config_table table" style="width:100%">

<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('allow_registration');?>><?php echo JText::_('HIKAM_ALLOW_VENDOR_REGISTRATION'); ?></td>
	<td><?php
		$options = array(
			JHTML::_('hikaselect.option', 0, JText::_('HIKASHOP_NO')),
			JHTML::_('hikaselect.option', 1, JText::_('HIKAM_REGISTER_MANUAL_VALIDATION')),
			JHTML::_('hikaselect.option', 2, JText::_('HIKAM_REGISTER_AUTO_VALIDATION')),
			JHTML::_('hikaselect.option', 3, JText::_('HIKAM_REGISTER_AUTO_CREATION'))
		);
		echo JHTML::_('select.genericlist', $options, 'config[allow_registration]', 'onchange="window.localPage.allowRegistrationChanged(this);"', 'value', 'text', $this->config->get('allow_registration',0));
	?>
<?php
JFactory::getDocument()->addScriptDeclaration('
if(!window.localPage) window.localPage = {};
window.localPage.allowRegistrationChanged = function(el) {
	if(!el) return;
	var els = ["hikamarket_config_auto_registration_group"];
	window.hikashop.setArrayDisplay(els, (el.value == 3));
};
window.hikashop.ready(function(){ window.localPage.allowRegistrationChanged(document.getElementById("configallow_registration")) });
');
?>
	</td>
</tr>
<tr id="hikamarket_config_auto_registration_group">
	<td class="hk_tbl_key"<?php echo $this->docTip('auto_registration_group');?>><?php echo JText::_('HIKAM_ALLOW_VENDOR_REGISTRATION'); ?></td>
	<td><?php
		echo $this->joomlaaclType->display('config[auto_registration_group]', $this->config->get('auto_registration_group', 'all'), true, true);
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('register_ask_currency');?>><?php echo JText::_('HIKAM_REGISTRATION_ASK_CURRENCY'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[register_ask_currency]",'',$this->config->get('register_ask_currency',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('register_ask_description');?>><?php echo JText::_('HIKAM_REGISTRATION_ASK_DESCRIPTION'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[register_ask_description]",'',$this->config->get('register_ask_description',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('register_ask_terms');?>><?php echo JText::_('HIKAM_REGISTRATION_ASK_TERMS'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[register_ask_terms]",'',$this->config->get('register_ask_terms',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('register_terms_required');?>><?php echo JText::_('HIKAM_REGISTRATION_TERMS_REQUIRED'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[register_terms_required]",'',$this->config->get('register_terms_required',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('register_ask_paypal');?>><?php echo JText::_('HIKAM_REGISTRATION_ASK_PAYPAL'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[register_ask_paypal]",'',$this->config->get('register_ask_paypal',1));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('register_paypal_required');?>><?php echo JText::_('HIKAM_REGISTRATION_PAYPAL_REQUIRED'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[register_paypal_required]",'',$this->config->get('register_paypal_required',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('registration_ask_password');?>><?php echo JText::_('HIKAM_REGISTRATION_ASK_PASSWORD'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[registration_ask_password]",'',$this->config->get('registration_ask_password',1));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('registration_email_is_username');?>><?php echo JText::_('HIKAM_REGISTRATION_EMAIL_IS_USERNAME'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[registration_email_is_username]",'',$this->config->get('registration_email_is_username',0));
	?></td>
</tr>
<!--
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('register_ask_image');?>><?php echo JText::_('HIKAM_REGISTRATION_ASK_IMAGE'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[register_ask_image]",'',$this->config->get('register_ask_image',0));
	?></td>
</tr>
-->
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('link_admin_groups');?>><?php echo JText::_('HIKAM_LINK_VENDOR_GROUP_WITH_ADMIN'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[link_admin_groups]",'',$this->config->get('link_admin_groups',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('user_group_registration');?>><?php echo JText::_('HIKAM_USERGROUP_ON_REGISTRATION'); ?></td>
	<td><?php
		echo $this->joomlaaclType->displayList('config[user_group_registration]', $this->config->get('user_group_registration', ''), 'HIKA_INHERIT');
	?></td>
</tr>

</table>
	</div></div>
</div>

<div id="market_categories" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('HIKAM_OPTIONS_CATEGORIES'); ?></div>
<table class="hk_config_table table" style="width:100%">

<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('vendor_create_category');?>><?php echo JText::_('HIKAM_VENDOR_CREATE_CATEGORY'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[vendor_create_category]",'',$this->config->get('vendor_create_category',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('vendor_parent_category');?>><?php echo JText::_('HIKAM_VENDOR_PARENT_CATEGORY'); ?></td>
	<td><?php
		echo $this->categoryType->displaySingle('config[vendor_parent_category]', $this->config->get('vendor_parent_category',''));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('vendor_chroot_category');?>><?php echo JText::_('HIKAM_VENDOR_CHROOT_CATEGORY'); ?></td>
	<td><?php
		$options = array(
			JHTML::_('hikaselect.option', 0, JText::_('HIKASHOP_NO')),
			JHTML::_('hikaselect.option', 1, JText::_('HIKAM_VENDOR_HOME')),
			JHTML::_('hikaselect.option', 2, JText::_('HIKASHOP_YES'))
		);
		echo JHTML::_('hikaselect.radiolist', $options, 'config[vendor_chroot_category]', 'onchange="window.hikamarket.switchBlock(this, 2, \'config__vendor_root_category\')"', 'value', 'text', $this->config->get('vendor_chroot_category',0));
	?></td>
</tr>
<tr id="config__vendor_root_category" <?php if($this->config->get('vendor_chroot_category',0) != 2) echo ' style="display:none;"'; ?>>
	<td class="hk_tbl_key"<?php echo $this->docTip('vendor_root_category');?>><?php echo JText::_('HIKAM_VENDORS_ROOT_CATEGORY'); ?></td>
	<td><?php
		echo $this->categoryType->displaySingle('config[vendor_root_category]', $this->config->get('vendor_root_category', 0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('vendor_extra_categories');?>><?php echo JText::_('HIKAM_VENDOR_EXTRA_CATEGORIES'); ?></td>
	<td><?php
		$vendor_categories = trim($this->config->get('vendor_extra_categories', ''));
		$vendor_categories = !empty($vendor_categories) ? explode(',', $vendor_categories) : array();
		echo $this->nameboxType->display(
			'config[vendor_extra_categories]',
			$vendor_categories,
			hikamarketNameboxType::NAMEBOX_MULTIPLE,
			'category',
			array(
				'delete' => true,
				'root' => 0,
				'sort' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				'force_data' => true
			)
		);
	?></td>
</tr>

</table>
	</div></div>
</div>

<div id="market_limitations" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('HIKAM_OPTIONS_TITLE_VENDOR_LIMITATIONS'); ?></div>
<table class="hk_config_table table" style="width:100%">

<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('vendor_product_limitation');?>><?php echo JText::_('VENDOR_PRODUCT_LIMITATION'); ?></td>
	<td>
		<input type="text" name="config[vendor_product_limitation]" value="<?php echo (int)$this->config->get('vendor_product_limitation', 0); ?>" />
	</td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('related_all_vendors');?>><?php echo JText::_('HIKAM_OPTION_RELATED_ALL_VENDORS'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[related_all_vendors]",'',$this->config->get('related_all_vendors',1));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('options_all_vendors');?>><?php echo JText::_('HIKAM_OPTION_OPTIONS_ALL_VENDORS'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[options_all_vendors]",'',$this->config->get('options_all_vendors',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('check_vendor_completion');?>><?php echo JText::_('HIKAM_OPTION_CHECK_VENDOR_COMPLETION'); ?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[check_vendor_completion]",'',$this->config->get('check_vendor_completion', 0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('days_payment_request');?>><?php echo JText::_('DAYS_FOR_PAYMENT_REQUEST'); ?></td>
	<td>
		<input type="text" name="config[days_payment_request]" value="<?php echo (int)$this->config->get('days_payment_request', 0); ?>" />
	</td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('min_value_payment_request');?>><?php echo JText::_('MIN_VALUE_FOR_PAYMENT_REQUEST'); ?></td>
	<td>
		<input type="text" name="config[min_value_payment_request]" value="<?php echo hikamarket::toFloat($this->config->get('min_value_payment_request', 0.0)); ?>" />
		<?php echo $this->main_currency->currency_symbol.' '.$this->main_currency->currency_code; ?>
	</td>
</tr>

</table>
	</div></div>
</div>

<div id="market_tax" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('HIKAM_OPTIONS_TAX'); ?></div>
<table class="hk_config_table table" style="width:100%">

<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('market_mode');?>><?php echo JText::_('HIKAM_MARKET_MODE'); ?></td>
	<td><?php
		$options = array(
			JHTML::_('select.option', 'fee', JText::_('MARKETMODE_FEE')),
			JHTML::_('select.option', 'commission', JText::_('MARKETMODE_COMMISSION')),
		);
		echo JHTML::_('hikaselect.radiolist', $options, 'config[market_mode]', '', 'value', 'text', $this->config->get('market_mode', 'fee'));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('calculate_vendor_price_with_tax');?>><?php echo JText::_('HIKAM_VENDOR_PRICE_WITH_TAX');?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[calculate_vendor_price_with_tax]",'',$this->config->get('calculate_vendor_price_with_tax',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('fee_on_shipping');?>><?php echo JText::_('HIKAM_APPLY_FEES_ON_SHIPPING');?></td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "config[fee_on_shipping]",'',$this->config->get('fee_on_shipping',0));
	?></td>
</tr>
<tr>
	<td class="hk_tbl_key"<?php echo $this->docTip('vendor_pay_content');?>><?php echo JText::_('HIKAM_MARKET_PAYVENDORCONTENT_MODE'); ?></td>
	<td><?php
		$options = array(
			JHTML::_('select.option', 'orders', JText::_('MARKETMODE_PAY_ORDERS')),
			JHTML::_('select.option', 'products', JText::_('MARKETMODE_PAY_PRODUCTS')),
		);
		echo JHTML::_('hikaselect.radiolist', $options, 'config[vendor_pay_content]', '', 'value', 'text', $this->config->get('vendor_pay_content', 'orders'));
	?></td>
</tr>
<!--
vendor_limit_orders_display (integer)
vendor_limit_products_display (integer)
-->
</table>
<?php
	$params = new hikaParameter('');
	$params->set('configPanelIntegration', true);
	$js = '';
	echo hikamarket::getLayout('vendormarket', 'fees', $params, $js);
?>
	</div></div>
</div>

</div>

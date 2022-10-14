<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(empty($this->history))
	return;

?>
<div class="hikamarket_order">
	<dl class="hikam_options">
		<dt class="hikamarket_order_history_type"><label><?php echo JText::_('HIKA_TYPE'); ?></label></dt>
		<dd class="hikamarket_order_history_type"><span><?php
			if(!empty($this->history->history_type))
				echo $this->escape($this->history->history_type);
			else
				echo '<em>' . JText::_('HIKA_NONE') . '</em>';
		?></span></dd>

		<dt class="hikamarket_order_history_date"><label><?php echo JText::_('DATE'); ?></label></dt>
		<dd class="hikamarket_order_history_date"><span><?php
			echo hikamarket::getDate($this->history->history_created, '%Y-%m-%d %H:%M');
		?></span></dd>

		<dt class="hikamarket_order_history_user"><label><?php echo JText::_('HIKA_USER'); ?></label></dt>
		<dd class="hikamarket_order_history_user"><span><?php
			echo $this->escape($this->history->user->name) . ' (' . $this->escape($this->history->user->user_email) . ')';
		?></span></dd>

		<dt class="hikamarket_order_history_status"><label><?php echo JText::_('ORDER_STATUS'); ?></label></dt>
		<dd class="hikamarket_order_history_status"><span><?php
			echo hikamarket::orderStatus($this->history->history_new_status);
		?></span></dd>

<?php if(!empty($this->history->history_reason)) { ?>
		<dt class="hikamarket_order_history_reason"><label><?php echo JText::_('REASON'); ?></label></dt>
		<dd class="hikamarket_order_history_reason"><span><?php
			echo $this->escape($this->history->history_reason);
		?></span></dd>
<?php } ?>

<?php if(!empty($this->history->history_ip)) { ?>
		<dt class="hikamarket_order_history_ip"><label><?php echo JText::_('IP'); ?></label></dt>
		<dd class="hikamarket_order_history_ip"><span><?php
			echo $this->escape($this->history->history_ip);
		?></span></dd>
<?php } ?>

<?php if(!empty($this->history->history_data) && is_string($this->history->history_data)) { ?>
		<dt class="hikamarket_order_history_data"><label><?php echo JText::_('DATA'); ?></label></dt>
		<dd class="hikamarket_order_history_data"><span><?php
			echo $this->escape($this->history->history_data);
		?></span></dd>
<?php } ?>

	</dl>
<?php if(!empty($this->history->history_data) && !is_string($this->history->history_data)) { ?>
	<div class="hikamarket_order_history_data"><?php

	if(isset($this->history->history_data['customer'])) {
		$data = $this->history->history_data['customer'];
		$userClass = hikamarket::get('shop.class.user');
		$old_customer = $new_customer = null;
		if(!empty($data['old']))
			$old_customer = $userClass->get( (int)$data['old'] );
		if(!empty($data['new']))
			$new_customer = $userClass->get( (int)$data['new'] );
?>
	<h3><?php echo JText::_('HIKAM_HISTORY_CUSTOMER'); ?></h3>
	<h4><?php echo JText::_('HIKAM_HISTORY_OLD'); ?></h4>
	<dl class="hikam_options">
		<dt class="history_data_customer_name"><?php echo JText::_('HIKA_NAME'); ?></dt>
		<dd class="history_data_customer_name"><?php
			if(empty($old_customer))
				echo JText::_('HIKAM_USER_DOES_NOT_EXIST_ANYMORE');
			elseif(!empty($old_customer->name))
				echo $old_customer->name;
			else
				echo '<em>' . JText::_('HIKA_NONE') . '</em>';
		?></dd>
<?php if(!empty($old_customer)) { ?>
		<dt class="history_data_customer_email"><label><?php echo JText::_('HIKA_EMAIL');?></label></dt>
		<dd class="history_data_customer_email"><?php echo $this->escape(@$new_customer->user_email); ?></dd>
<?php } ?>
	</dl>
	<h4><?php echo JText::_('HIKAM_HISTORY_NEW'); ?></h4>
	<dl class="hikam_options">
		<dt class="history_data_customer_name"><?php echo JText::_('HIKA_NAME'); ?></dt>
		<dd class="history_data_customer_name"><?php
			if(empty($new_customer))
				echo JText::_('HIKAM_USER_DOES_NOT_EXIST_ANYMORE');
			elseif(!empty($new_customer->name))
				echo $new_customer->name;
			else
				echo '<em>' . JText::_('HIKA_NONE') . '</em>';
		?></dd>
<?php if(!empty($new_customer)) { ?>
		<dt class="history_data_customer_email"><label><?php echo JText::_('HIKA_EMAIL');?></label></dt>
		<dd class="history_data_customer_email"><?php echo $this->escape(@$new_customer->user_email); ?></dd>
<?php } ?>
	</dl>
<?php
	}

	if(isset($this->history->history_data['coupon'])) {
		$data = $this->history->history_data['coupon'];
?>
	<h3><?php echo JText::_('HIKAM_HISTORY_COUPON'); ?></h3>
	<h4><?php echo JText::_('HIKAM_HISTORY_OLD'); ?></h4>
	<dl class="hikam_options">
<?php if(isset($data['old']['code'])) { ?>
		<dt class="history_data_coupon_code"><label><?php echo JText::_('COUPON_CODE');?></label></dt>
		<dd class="history_data_coupon_code"><?php echo $this->escape($data['old']['code']); ?></dd>
<?php } ?>
<?php if(isset($data['old']['value'])) { ?>
		<dt class="history_data_coupon_value"><label><?php echo JText::_('COUPON_VALUE');?></label></dt>
		<dd class="history_data_coupon_value"><?php
			echo (float)hikamarket::toFloat($data['old']['value']);
		?></dd>
<?php } ?>
<?php if(isset($data['old']['tax'])) { ?>
		<dt class="history_data_coupon_tax"><label><?php echo JText::_('COUPON_TAX');?></label></dt>
		<dd class="history_data_coupon_tax"><?php
			echo (float)hikamarket::toFloat($data['old']['tax']);
		?></dd>
<?php } ?>
<?php if(isset($data['old']['tax_namekey'])) { ?>
		<dt class="history_data_coupon_taxnamekey"><label><?php echo JText::_('COUPON_TAX');?></label></dt>
		<dd class="history_data_coupon_taxnamekey"><?php echo $this->escape($data['old']['tax_namekey']); ?></dd>
<?php } ?>
	</dl>
	<h4><?php echo JText::_('HIKAM_HISTORY_NEW'); ?></h4>
	<dl class="hikam_options">
<?php if(isset($data['new']['code'])) { ?>
		<dt class="history_data_coupon_code"><label><?php echo JText::_('COUPON_CODE');?></label></dt>
		<dd class="history_data_coupon_code"><?php echo $this->escape($data['new']['code']); ?></dd>
<?php } ?>
<?php if(isset($data['new']['value'])) { ?>
		<dt class="history_data_coupon_value"><label><?php echo JText::_('COUPON_VALUE');?></label></dt>
		<dd class="history_data_coupon_value"><?php
			echo (float)hikamarket::toFloat($data['new']['value']);
		?></dd>
<?php } ?>
<?php if(isset($data['new']['tax'])) { ?>
		<dt class="history_data_coupon_tax"><label><?php echo JText::_('COUPON_TAX');?></label></dt>
		<dd class="history_data_coupon_tax"><?php
			echo (float)hikamarket::toFloat($data['new']['tax']);
		?></dd>
<?php } ?>
<?php if(isset($data['new']['tax_namekey'])) { ?>
		<dt class="history_data_coupon_taxnamekey"><label><?php echo JText::_('COUPON_TAX');?></label></dt>
		<dd class="history_data_coupon_taxnamekey"><?php echo $this->escape($data['new']['tax_namekey']); ?></dd>
<?php } ?>
	</dl>
<?php
	}

	if(isset($this->history->history_data['shipping'])) {
		$data = $this->history->history_data['shipping'];
	}

	if(isset($this->history->history_data['payment'])) {
		$data = $this->history->history_data['payment'];
	}

	if(isset($this->history->history_data['fields'])) {
		$data = $this->history->history_data['fields'];
	}

	if(isset($this->history->history_data['billing_address'])) {
		$data = $this->history->history_data['billing_address'];
	}

	if(isset($this->history->history_data['shipping_address'])) {
		$data = $this->history->history_data['shipping_address'];
	}

	if(isset($this->history->history_data['product'])) {
		$data = $this->history->history_data['product'];
	}

	if(isset($this->history->history_data['product_delete'])) {
		$data = $this->history->history_data['product_delete'];
?>
	<h3><?php echo JText::_('HIKAM_HISTORY_PRODUCT_DELETE'); ?></h3>
	<dl class="hikam_options">
		<dt class="history_data_productdelete_id"><label><?php echo JText::_('ID');?></label></dt>
		<dd class="history_data_productdelete_id"><?php echo (int)$data['product']['id']; ?></dd>

		<dt class="history_data_productdelete_name"><label><?php echo JText::_('PRODUCT_NAME');?></label></dt>
		<dd class="history_data_productdelete_name"><?php echo $this->escape($data['product']['name']); ?></dd>

		<dt class="history_data_productdelete_code"><label><?php echo JText::_('PRODUCT_CODE');?></label></dt>
		<dd class="history_data_productdelete_code"><?php echo $this->escape($data['product']['code']); ?></dd>

		<dt class="history_data_productdelete_qty"><label><?php echo JText::_('PRODUCT_QUANTITY');?></label></dt>
		<dd class="history_data_productdelete_qty"><?php echo (int)$data['product']['qty']; ?></dd>

		<dt class="history_data_productdelete_price"><label><?php echo JText::_('PRICE');?></label></dt>
		<dd class="history_data_productdelete_price"><?php
			echo (float)hikamarket::toFloat($data['product']['price']);
		?></dd>
	</dl>
<?php
	}
	?></div>
<?php } ?>
</div>

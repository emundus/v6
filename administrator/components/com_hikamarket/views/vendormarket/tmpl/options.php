<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><dl class="hika_options">
<?php if((int)$this->config->get('vendor_chroot_category', 0) == 2) { ?>
	<dt class="hikamarket_vendor_opt_root"><label for="data[vendor][vendor_params][vendor_root_category]"><?php echo JText::_('HIKAM_VENDOR_ROOT_CATEGORY'); ?></label></dt>
	<dd class="hikamarket_vendor_opt_root"><?php
		echo $this->nameboxType->display(
			'data[vendor][vendor_params][vendor_root_category]',
			@$this->vendor->vendor_params->vendor_root_category,
			hikamarketNameboxType::NAMEBOX_SINGLE,
			'category',
			array(
				'delete' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>'
			)
		);
	?></dd>
<?php } ?>
<?php if(empty($this->vendor->vendor_id) || $this->vendor->vendor_id > 1) { ?>
	<dt class="hikamarket_vendor_opt_invoice"><label for="data_vendor_vendor_params_invoice_number_format"><?php echo JText::_('INVOICE_NUMBER_FORMAT'); ?></label></dt>
	<dd class="hikamarket_vendor_opt_invoice"><?php
		if(hikashop_level(1)) {
			$format = @$this->vendor->vendor_params->invoice_number_format;
			?><input class="inputbox" type="text" name="data[vendor][vendor_params][invoice_number_format]" value="<?php echo $this->escape($format); ?>" id="data_vendor_vendor_params_invoice_number_format"/><?php
		} else {
			echo '<small style="color:red">'.JText::_('ONLY_HIKASHOP_COMMERCIAL').'</small>';
		}
	?></dd>
<?php } ?>

	<dt class="hikamarket_vendor_opt_paypal"><label for="data_vendor_vendor_params_paypal_email"><?php echo JText::_('PAYPAL_EMAIL'); ?></label></dt>
	<dd class="hikamarket_vendor_opt_paypal">
		<input type="text" name="data[vendor][vendor_params][paypal_email]" value="<?php echo @$this->vendor->vendor_params->paypal_email; ?>" id="data_vendor_vendor_params_paypal_email"/>
	</dd>

	<dt class="hikamarket_vendor_opt_productlimit"><label for="data_vendor_vendor_params_product_limitation"><?php echo JText::_('VENDOR_PRODUCT_LIMITATION'); ?></label></dt>
	<dd class="hikamarket_vendor_opt_productlimit">
		<input type="text" name="data[vendor][vendor_params][product_limitation]" value="<?php echo $this->escape( @$this->vendor->vendor_params->product_limitation ); ?>" id="data_vendor_vendor_params_product_limitation"/>
	</dd>

	<dt class="hikamarket_vendor_opt_notif"><label for="data_vendor_vendor_params_notif_order_statuses_text"><?php echo JText::_('HIKAM_NOTIFICATION_STATUSES_FILTER'); ?></label></dt>
	<dd class="hikamarket_vendor_opt_notif"><?php
		if(!empty($this->vendor->vendor_params->notif_order_statuses) && is_string($this->vendor->vendor_params->notif_order_statuses)) {
			$this->vendor->vendor_params->notif_order_statuses = explode(',', $this->vendor->vendor_params->notif_order_statuses);
			foreach($this->vendor->vendor_params->notif_order_statuses as &$status) {
				$status = trim($status);
			}
			unset($status);
		}
		echo $this->nameboxType->display(
			'data[vendor][vendor_params][notif_order_statuses]',
			@$this->vendor->vendor_params->notif_order_statuses,
			hikamarketNameboxType::NAMEBOX_MULTIPLE,
			'order_status',
			array(
				'delete' => true,
				'sort' => false,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				'force_data' => true
			)
		);
	?></dd>

	<dt class="hikamarket_vendor_opt_extracat"><label for="data_vendor_vendor_params_extra_categories_text"><?php echo JText::_('HIKAM_VENDOR_EXTRA_CATEGORIES'); ?></label></dt>
	<dd class="hikamarket_vendor_opt_extracat"><?php
		$extra_categories = array();
		if(!empty($this->vendor->vendor_params->extra_categories))
			$extra_categories = explode(',', $this->vendor->vendor_params->extra_categories);
		echo $this->nameboxType->display(
			'data[vendor][vendor_params][extra_categories]',
			$extra_categories,
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
	?></dd>
</dl>

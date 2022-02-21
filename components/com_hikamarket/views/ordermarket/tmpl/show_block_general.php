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
if(empty($this->order))
	return;

if(empty($this->ajax)) { ?>
<div id="hikamarket_order_block_general">
<?php } ?>
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>

	<dl class="hikam_options large">
<?php if(empty($this->order->order_invoice_created) || $this->order->order_invoice_created != $this->order->order_created) { ?>
		<dt class="hikamarket_order_created"><label><?php echo JText::_('DATE_ORDER_CREATED');?></label></dt>
		<dd class="hikamarket_order_created"><?php echo hikamarket::getDate($this->order->order_created, '%Y-%m-%d %H:%M'); ?></dd>
<?php } ?>

		<dt class="hikamarket_order_invoicenumber"><label><?php echo JText::_('INVOICE_NUMBER'); ?></label></dt>
		<dd class="hikamarket_order_invoicenumber"><span><?php echo @$this->order->order_invoice_number; ?></span></dd>

<?php if(!empty($this->order->order_invoice_created)) { ?>
		<dt class="hikamarket_order_created"><label><?php echo JText::_('DATE_ORDER_PAID');?></label></dt>
		<dd class="hikamarket_order_created"><?php echo hikamarket::getDate($this->order->order_invoice_created, '%Y-%m-%d %H:%M');?></dd>
<?php } ?>

<?php if((int)$this->order->order_vendor_id > 1 && (int)$this->vendor->vendor_id <= 1 && !empty($this->order->hikamarket->vendor)) { ?>
		<dt class="hikamarket_order_vendor"><label><?php echo JText::_('HIKA_VENDOR');?></label></dt>
		<dd class="hikamarket_order_vendor"><?php echo $this->escape($this->order->hikamarket->vendor->vendor_name); ?></dd>
<?php } ?>
<?php if((int)$this->vendor->vendor_id <= 1 && $this->order->order_parent_id > 0 && isset($this->order->hikamarket->parent)) { ?>
		<dt class="hikamarket_order_parent"><label><?php echo JText::_('HIKAM_PARENT_ORDER');?></label></dt>
		<dd class="hikamarket_order_parent"><a href="<?php echo hikamarket::completeLink('order&task=show&cid='.(int)$this->order->order_parent_id); ?>"><?php
			echo $this->escape($this->order->hikamarket->parent->order_number);
		?></a></dd>
<?php } ?>
	</dl>
<?php

if(!empty($this->ajax))
	return;
?>
</div>
<script type="text/javascript">
window.Oby.registerAjax(['orderMgr.general','orderMgr.order_status'],function(params){ window.orderMgr.refreshBlock('general'); });
</script>

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
if(!hikamarket::acl('order/show/customer'))
	return;

if(empty($this->ajax)) { ?>
<div id="hikamarket_order_block_customer">
<?php } ?>
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>

	<dl class="hikam_options large">
		<dt class="hikamarket_order_customer_name"><label><?php echo JText::_('HIKA_NAME');?></label></dt>
		<dd class="hikamarket_order_customer_name"><span id="hikamarket_order_customer_name"><?php
			$customer_name = @$this->order->customer->name;
			if(empty($customer_name))
				$customer_name = '<em>' . JText::_('HIKA_NONE') . '</em>';

			if($this->order->order_user_id > 0 && hikamarket::acl('user/show')) {
				?><a onclick="return window.orderMgr.showCustomer(this);" data-popup-href="<?php echo hikamarket::completeLink('user&task=show&cid='.$this->order->order_user_id, true); ?>" href="<?php echo hikamarket::completeLink('user&task=show&cid='.$this->order->order_user_id); ?>"><?php
					echo $customer_name;
				?></a><?php
			} else
				echo $customer_name;
		?></span></dd>

		<dt class="hikamarket_order_customer_email"><label><?php echo JText::_('HIKA_EMAIL');?></label></dt>
		<dd class="hikamarket_order_customer_email"><span id="hikamarket_order_customer_email"><?php echo @$this->order->customer->user_email; ?></span></dd>
	</dl>
<?php

if(!empty($this->ajax))
	return;
?>
</div>
<?php
if(hikamarket::acl('user/show')) {
	echo $this->popup->display(
		'',
		'HIKAM_CUSTOMER_DETAILS',
		hikamarket::completeLink('user&task=show&cid=0', true),
		'hikamarket_showcustomer_popup',
		750, 460, 'style="display:none;"', '', 'link'
	);
}
?>
<script type="text/javascript">
window.Oby.registerAjax('orderMgr.customer',function(params){
	if(params && params.src && params.src == 'customer') return;
	window.orderMgr.refreshBlock('customer');
});
window.orderMgr.showCustomer = function(el) { return this.showEl(el, 'hikamarket_showcustomer_popup'); };
</script>

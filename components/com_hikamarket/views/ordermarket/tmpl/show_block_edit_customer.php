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
if(!$this->editable_order || !hikamarket::acl('order/edit/customer'))
	return;

if(empty($this->ajax)) {
	echo $this->dropdownHelper->display(
		JText::_('HIKAM_EDIT'),
		array(
			'customer' => array(
				'name' => '<i class="fas fa-user"></i> ' . JText::_('HIKAM_CHANGE_CUSTOMER'),
				'link' => '#customer',
				'click' => 'return window.orderMgr.showSelectCustomer(this, true);'
			)
		),
		array('type' => '', 'mini' => true, 'class' => 'hikabtn-primary', 'right' => false, 'up' => false)
	);
?>
	<div class="hikamarket_order_edit_block" id="hikamarket_order_edit_customer" style="display:none;">
		<div class="hikamarket_ajax_loading_elem"></div>
		<div class="hikamarket_ajax_loading_spinner"></div>
<?php
	hikamarket::loadJslib('otree');
?>
	</div>
<script type="text/javascript">
window.orderMgr.showSelectCustomer = function(el, show) {
	var d = document,
		block = d.getElementById('hikamarket_order_edit_customer'),
		box = window.oNameboxes['hikamarket_order_edit_customer_namebox'];
	if(!block)
		return false;
	block.style.display = ((show === undefined && block.style.display == 'none') || show == true) ? '' : 'none';

	if(box) {
		box.clear();
	} else {
		this.refreshBlock('customer', true);
	}
	return false;
};
window.orderMgr.selectCustomer = function(el) {
	var d = document, w = window, o = w.Oby,
		block = document.getElementById('hikamarket_order_block_customer');
	if(block) o.addClass(el, "hikamarket_ajax_loading");
	this.submitBlock("customer", {data:false, update:false}, function(x,p){
		if(el) {
			o.removeClass(block, "hikamarket_ajax_loading");
			if(x.responseText.length > 1)
				return window.Oby.updateElem(block, x.responseText);
		}
		window.Oby.fireAjax('orderMgr.customer', null);
	});
	return this.showSelectCustomer(el, false);
};
</script>
<?php
	return;
}

echo $this->nameboxType->display(
	'order[customer][user_id]',
	'',
	hikamarketNameboxType::NAMEBOX_SINGLE,
	'user',
	array(
		'customer' => true,
		'delete' => true,
		'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
		'id' => 'hikamarket_order_edit_customer_namebox'
	)
);
?>
	<div>
		<label for="hikamarket_order_edit_customer_addrlink"><input type="checkbox" value="1" name="order[customer][addrlink]" id="hikamarket_order_edit_customer_addrlink" /><span><?php echo JText::_('SET_USER_ADDRESS'); ?></span></label>
	</div>
	<div style="clear:both;margin-top:4px;"></div>
	<div style="float:right">
		<button onclick="return window.orderMgr.selectCustomer(this);" class="hikabtn hikabtn-success"><i class="fas fa-check"></i> <?php echo JText::_('HIKAM_SELECT_CUSTOMER'); ;?></button>
	</div>
	<button onclick="return window.orderMgr.showSelectCustomer(this, false);" class="hikabtn hikabtn-danger"><i class="far fa-times-circle"></i> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
	<div style="clear:both"></div>

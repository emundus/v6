<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikam_product_listing">
<form action="<?php echo hikamarket::completeLink('order&task=listing'.$this->url_itemid); ?>" method="post" id="adminForm" name="adminForm">

<div class="hk-row-fluid">
	<div class="hkc-md-12">
<?php
	echo $this->loadHkLayout('search', array(
		'id' => 'hikamarket_order_listing_search'
	));
?>
		<div class="hikam_sort_zone"><?php
			if(!empty($this->ordering_values))
				echo JHTML::_('select.genericlist', $this->ordering_values, 'filter_fullorder', 'onchange="this.form.submit();"', 'value', 'text', $this->full_ordering);
		?></div>
	</div>
	<div class="hkc-md-12">
		<div class="expand-filters">
<?php

echo $this->orderStatusType->display('filter_status', $this->pageInfo->filter->filter_status, ' onchange="document.adminForm.submit();"', true);

if(!empty($this->pageInfo->filter->filter_user)) {
	$userClass = hikamarket::get('shop.class.user');
	$user_filter = $userClass->get($this->pageInfo->filter->filter_user);
?>
	<input type="hidden" name="filter_user" value="<?php echo (int)$this->pageInfo->filter->filter_user; ?>" id="hikamarket_order_listing_filter_user" />
	<button class="hikabtn" onclick="var el = document.getElementById('hikamarket_order_listing_filter_user'); if(el) el.value = ''; document.adminForm.submit(); return false;"><?php echo $user_filter->user_email; ?> <i class="far fa-trash-alt"></i></button>
<?php
}

foreach($this->extrafilters as $name => $filterObj) {
	echo $filterObj->displayFilter($name, $this->pageInfo->filter);
}

?>
		</div>
		<div style="clear:both"></div>
	</div>
</div>
<?php
	if(!empty($this->order_stats)) {
?><table class="order_statistics hikam_table hikam_bordered" style="width:100%">
	<tr>
<?php
		$width = floor(100 / (count($this->order_stats)+1));
		$total_orders = 0;
		foreach($this->order_stats as $status => $obj) {
			if(empty($status))
				continue;
			$total = (int)$obj->total;
			$total_orders += $total;

			$class = ($this->pageInfo->filter->filter_status == $status) ? 'order_statistics_active' : '';

?>		<td style="width:<?php echo $width;?>%" class="<?php echo $class; ?>">
			<a href="<?php echo hikamarket::completeLink('order&task=listing&filter_status='.$status); ?>">
				<span class="value"><?php echo $total; ?></span>
				<span class="order-label order-label-<?php echo preg_replace('#[^a-z_0-9]#i', '_', str_replace(' ','_',$status)); ?>"><?php
					echo hikamarket::orderStatus($status);
				?></span>
			</a>
		</td>
<?php
		}
?>
		<td style="width:<?php echo $width;?>%">
			<a href="<?php echo hikamarket::completeLink('order&task=listing&filter_status='); ?>">
				<span class="value"><?php echo $total_orders; ?></span>
				<span class="order-label order-label-all"><?php echo JText::_('HIKAM_STAT_ALL'); ?></span>
			</a>
		</td>
	</tr>
</table>
<?php
	}
?>

<div id="hikam_order_main_listing">
<?php
$manage = hikamarket::acl('order/show');
$edit_order_status = hikamarket::acl('order/edit/general') && (int)$this->config->get('edit_order_status_listing', false);
$extra_classes = '';

foreach($this->orders as $order) {
	$url = ($manage) ? hikamarket::completeLink('order&task=show&cid='.$order->order_id.$this->url_itemid) : null;
?>
	<div class="hk-card hk-card-default hk-card-vendor-order<?php echo $extra_classes; ?>" data-hkm-order="<?php echo (int)$order->order_id; ?>">
		<div class="hk-card-header">
<?php if(!empty($url)) { ?>
			<a class="hk-row-fluid" href="<?php echo $url; ?>">
<?php } else { ?>
			<div class="hk-row-fluid">
<?php } ?>
				<div class="hkc-sm-6 hkm_order_date">
					<i class="fa fa-clock"></i>
					<?php echo hikashop_getDate((int)$order->order_created, '%Y-%m-%d %H:%M'); ?>
				</div>
				<div class="hkc-sm-6 hkm_order_price">
					<i class="fa fa-credit-card"></i>
					<?php echo $this->currencyHelper->format($order->order_full_price, $order->order_currency_id); ?>
				</div>
<?php if(!empty($url)) { ?>
			</a>
<?php } else { ?>
			</div>
<?php } ?>
		</div>
		<div class="hk-card-body">
			<div class="hk-row-fluid">
				<div class="hkc-sm-4 hkm_order_number">
<?php
	if(!empty($url)) {
					?><a href="<?php echo $url; ?>"><?php
	}
					?><i class="far fa-file-alt" style="margin-right:4px;"></i><span class="hika_order_number_value"><?php echo $order->order_number; ?></span><?php
	if(!empty($url)) {
					?></a><?php
	}
?>
<?php if(!empty($order->order_invoice_number)) { ?>
					<span class="hkm_order_number_invoice_separator"> - </span>
					<span class="hkm_invoice_number_value"><?php echo $order->order_invoice_number; ?></span>
<?php } ?>
<?php if(hikamarket::acl('order/show/customer')) { ?>
					<div class="hkm_order_customer">
						<i class="fas fa-user"></i> <?php echo $this->escape($order->user_email); ?>
					</div>
<?php } ?>
				</div>
				<div class="hkc-sm-2 hkm_order_status">
					<span id="hikamarket_order_status_<?php echo $order->order_id; ?>" class="order-label order-label-<?php echo preg_replace('#[^a-z_0-9]#i', '_', str_replace(' ','_',$order->order_status)); ?>"><?php
						echo hikamarket::orderStatus($order->order_status);
					?></span>
<?php
	if($edit_order_status) {
		echo $this->popup->display(
			'<i class="fa fa-edit"></i>',
			'HIKAM_EDIT_ORDER_STATUS',
			hikamarket::completeLink('order&task=status&cid='.(int)$order->order_id.$this->url_itemid,true),
			'',
			640, 300, 'onclick="if(window.orderMgr.editOrderStatus) return window.orderMgr.editOrderStatus(this); window.hikashop.openBox(this); return false;"', '', 'link'
		);
	}
?>
				</div>
				<div class="hkc-sm-3 hkm_order_billing">
<?php
	if(hikamarket::acl('order/show/billingaddress') && !empty($order->order_billing_address_id)) {
		$full_address = $this->addressClass->maxiFormat($this->addresses[(int)$order->order_billing_address_id], $this->address_fields, true);
		$country = $this->addressClass->miniFormat($this->addresses[(int)$order->order_billing_address_id], $this->address_fields, '{address_city}, {address_state_code_3} {address_country_code_3}');
		echo hikamarket::tooltip($full_address, JText::_('HIKASHOP_BILLING_ADDRESS'), '', $country, '', 0);

		if(!empty($order->order_payment_method))
			echo '<br/>';
	}

	if(!empty($order->order_payment_method)) {
		$payment_price = $this->currencyHelper->format($order->order_payment_price, $order->order_currency_id);
		if(!empty($this->payments[$order->order_payment_id]))
			$payment_name = $this->payments[$order->order_payment_id]->payment_name;
		else
			$payment_name = $order->order_payment_method;

		echo '<span class="hk-label hk-label-blue">' .
			hikamarket::tooltip($payment_price, '', '', $payment_name, '', 0) .
			'</span>';
	}
?>
				</div>
				<div class="hkc-sm-3 hkm_order_shipping">
<?php
	if(hikamarket::acl('order/show/shippingaddress') && !empty($order->order_shipping_address_id) && !empty($order->order_shipping_id)) {
		$full_address = $this->addressClass->maxiFormat($this->addresses[(int)$order->order_shipping_address_id], $this->address_fields, true);
		$country = $this->addressClass->miniFormat($this->addresses[(int)$order->order_shipping_address_id], $this->address_fields, '{address_city}, {address_state_code_3} {address_country_code_3}');
		echo hikamarket::tooltip($full_address, JText::_('HIKASHOP_SHIPPING_ADDRESS'), '', $country, '', 0);

		if(!empty($order->shipping_name))
			echo '<br/>';
	}

	if(!empty($order->shipping_name)) {
		if($this->shopConfig->get('price_with_tax'))
			$shipping_price = $this->currencyHelper->format($order->order_shipping_price, $order->order_currency_id);
		else
			$shipping_price = $this->currencyHelper->format($order->order_shipping_price - @$order->order_shipping_tax, $order->order_currency_id);

		echo '<span class="hk-label hk-label-blue">';
		if(is_string($order->shipping_name)) {
			echo hikamarket::tooltip($shipping_price, '', '', $order->shipping_name, '', 0);
		} else
			echo hikamarket::tooltip('- '.implode('<br/>- ',$order->shipping_name), JText::_('SHIPPING_PRICE').': '.$shipping_price, '', '<em>'.JText::_('HIKAM_SEVERAL_SHIPPING').' &raquo;</em>', '', 0);
		echo '</span>';
	}
?>
				</div>
<?php
	if(!empty($this->fields)) {
?>
				<div class="hkc-sm-12 hkm_order_fields">
<?php
		$field_cpt = 0;
		foreach($this->fields as $field) {
			$namekey = $field->field_namekey;
			if($field->field_type == 'customtext' || empty($order->$namekey))
				continue;
			if(empty($field_cpt))
				echo '<dl>';
			$field_cpt++;
			echo '<dt>'.$this->fieldClass->trans($field->field_realname) . '</dt>'.
				'<dd>'.$this->fieldClass->show($field, $order->$namekey).'</dd>';
		}
		if($field_cpt > 0)
			echo '</dl>';
?>
			</div>
<?php
	}
?>
			</div>
		</div>
	</div>
<?php
}
?>
	<div class="hikamarket_orders_footer">
		<div class="hikamarket_pagination">
			<?php $this->pagination->form = '_bottom'; echo $this->pagination->getListFooter(); ?>
			<?php echo $this->pagination->getResultsCounter(); ?>
		</div>
	</div>
</div>

	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="listing" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
<?php
echo $this->popup->display('','HIKAM_EDIT_ORDER_STATUS','','hikamarket_order_status_popup', 640, 300, '', '', 'link');

$js = '
if(!window.orderMgr) window.orderMgr = {};
window.orderMgr.editOrderStatus = function(el) {
	window.hikamarket.submitFct = function(data) {
		var orderstatus = document.getElementById("hikamarket_order_status_" + data.id);
		if(data.id && orderstatus) {
			orderstatus.innerHTML = data.name;
			orderstatus.className = "order-label order-label-" + data.order_status.replace(/[^a-z_0-9]/i, "_");
		}
		window.hikamarket.closeBox();
	};
	var href = el.getAttribute("href");
	if(href == "" || href == null || href == "#")
		return false;
	window.hikamarket.openBox("hikamarket_order_status_popup", href);
	return false;
};
';
JFactory::getDocument()->addScriptDeclaration($js);

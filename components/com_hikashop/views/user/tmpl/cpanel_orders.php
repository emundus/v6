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
$url_itemid = (isset($this->url_itemid)) ? $this->url_itemid : '';
$cancel_orders = false;
$print_invoice = false;

?>
<div class="hika_cpanel_main_top">
	<h3 class="hika_cpanel_main_data_title"><?php echo $this->cpanel_data->cpanel_title; ?></h3>
</div>
<?php

if(empty($this->cpanel_data->cpanel_orders)) {
?>
	<div class="hk-well hika_no_orders">
		<p><?php echo JText::_('HIKA_CPANEL_NO_ORDERS'); ?></p>
	</div>
<?php
}
$cancel_url = '&cancel_url='.base64_encode(hikashop_currentURL());
foreach($this->cpanel_data->cpanel_orders as $order_id => $order) {
	$order_link = hikashop_completeLink('order&task=show&cid='.$order_id.$url_itemid.$cancel_url);
?>
<div class="hk-card hk-card-default hk-card-order">
	<div class="hk-card-header">
		<a class="hk-row-fluid" href="<?php echo $order_link; ?>">
			<div class="hkc-sm-6 hika_cpanel_date">
				<i class="fa fa-clock"></i>
				<?php echo hikashop_getDate((int)$order->order_created, '%d %B %Y %H:%M'); ?>
			</div>
			<div class="hkc-sm-6 hika_cpanel_price">
				<i class="fa fa-credit-card"></i>
				<?php echo $this->currencyClass->format($order->order_full_price, $order->order_currency_id); ?>
			</div>
		</a>
	</div>
	<div class="hk-card-body">
		<div class="hk-row-fluid">
			<div class="hkc-sm-4" class="hika_cpanel_order_number">
<?php if(!empty($order->extraData->topLeft)) { echo implode("\r\n", $order->extraData->topLeft); } ?>
				<span class="hika_cpanel_title"><?php echo  JText::_('ORDER_NUMBER'); ?> : </span>
				<span class="hika_cpanel_value"><?php echo $order->order_number; ?></span>
<?php if(!empty($order->order_invoice_number)) { ?>
				<br/>
				<span class="hika_cpanel_title"><?php echo JText::_('INVOICE_NUMBER'); ?> : </span>
				<span class="hika_cpanel_value"><?php echo $order->order_invoice_number; ?></span>
<?php } ?>
<?php if(!empty($order->extraData->bottomLeft)) { echo implode("\r\n", $order->extraData->bottomLeft); } ?>
			</div>
			<div class="hkc-sm-4 hika_cpanel_order_status">
<?php if(!empty($order->extraData->topMiddle)) { echo implode("\r\n", $order->extraData->topMiddle); } ?>
				<span class="order-label order-label-<?php echo $order->order_status; ?>"><?php echo hikashop_orderStatus($order->order_status); ?></span>
<?php if(!empty($order->extraData->bottomMiddle)) { echo implode("\r\n", $order->extraData->bottomMiddle); } ?>
			</div>
			<div class="hkc-sm-4 hika_cpanel_order_action">
<?php if(!empty($order->extraData->topRight)) { echo implode("\r\n", $order->extraData->topRight); } ?>
<?php
		$dropData = array(
			array(
				'name' => '<i class="fas fa-search"></i> '. JText::_('HIKA_DETAILS'),
				'link' => $order_link
			)
		);

		if(!empty($order->show_print_button)) {
			$print_invoice = true;
			$dropData[] = array(
				'name' => '<i class="fas fa-print"></i> '. JText::_('PRINT_INVOICE'),
				'link' => '#print_invoice',
				'click' => 'return window.localPage.printInvoice('.(int)$order->order_id.');',
			);
		}
		if(!empty($order->show_contact_button)) {
			$url = hikashop_completeLink('order&task=contact&order_id='.$order->order_id.$url_itemid);
			$dropData[] = array(
				'name' => '<i class="far fa-envelope"></i> '. JText::_('CONTACT_US_ABOUT_YOUR_ORDER'),
				'link' => $url
			);
		}
		if(!empty($order->show_cancel_button)) {
			$cancel_orders = true;
			$dropData[] = array(
				'name' => '<i class="fas fa-ban"></i> '. JText::_('CANCEL_ORDER'),
				'link' => '#cancel_order',
				'click' => 'return window.localPage.cancelOrder('.(int)$order->order_id.',\''.$order->order_number.'\');',
			);
		}
		if(!empty($order->show_payment_button) && bccomp(sprintf('%F',$order->order_full_price), 0, 5) > 0) {
			$url_param = ($this->payment_change) ? '&select_payment=1' : '';
			$url = hikashop_completeLink('order&task=pay&order_id='.$order->order_id.$url_param.$url_itemid);
			if($this->config->get('force_ssl',0) && strpos('https://',$url) === false)
				$url = str_replace('http://','https://', $url);
			$dropData[] = array(
				'name' => '<i class="fas fa-money-bill-alt"></i> '. JText::_('PAY_NOW'),
				'link' => $url
			);
		}
		if($this->config->get('allow_reorder', 0)) {
			$url = hikashop_completeLink('order&task=reorder&order_id='.$order->order_id.$url_itemid);
			if($this->config->get('force_ssl',0) && strpos('https://',$url) === false)
				$url = str_replace('http://','https://', $url);
			$dropData[] = array(
				'name' => '<i class="fas fa-redo-alt"></i> '. JText::_('REORDER'),
				'link' => $url
			);
		}

		if(!empty($order->actions)) {
			$dropData = array_merge($dropData, $order->actions);
		}

		if(!empty($dropData)) {
			echo $this->dropdownHelper->display(
				JText::_('HIKASHOP_ACTIONS'),
				$dropData,
				array('type' => 'btn', 'right' => true, 'up' => false)
			);
		}
?>
<?php if(!empty($order->extraData->bottomRight)) { echo implode("\r\n", $order->extraData->bottomRight); } ?>
			</div>
		</div>
	</div>
	<div class="hk-row-fluid">
		<div class="hkc-md-8 hk-list-group hika_cpanel_products">
<?php if(!empty($order->extraData->beforeProductsListing)) { echo implode("\r\n", $order->extraData->beforeProductsListing); } ?>
<?php
	$show_more = false;
	$max_products = (int)$this->config->get('max_products_cpanel', 4);
	if($max_products <= 0) $max_products = 4;
	if(count($order->products) > $max_products) {
		$order->products = array_slice($order->products, 0, $max_products);
		$show_more = true;
	}
	$group = $this->config->get('group_options',0);
	foreach($order->products as $product) {
		if($group && $product->order_product_option_parent_id)
			continue;
		$link = '#';
		if(!empty($product->product_id) && !empty($this->products[$product->product_id]) && !empty($this->products[$product->product_id]->product_published))
			$link = hikashop_contentLink('product&task=show&cid='.$product->product_id.'&name='.@$this->products[$product->product_id]->alias . $url_itemid, $this->products[$product->product_id]);

?>
			<div class="hk-list-group-item hika_cpanel_product">
<?php
		if(!empty($this->cpanel_data->cpanel_order_image)) {
			$img = $this->imageHelper->getThumbnail(@$product->images[0]->file_path, array(50, 50), array('default' => true, 'forcesize' => true,  'scale' => 'outside'));
			if(!empty($img) && $img->success) {
?>
				<a class="hika_cpanel_product_image_link" href="<?php echo $link; ?>"><img class="hika_cpanel_product_image" src="<?php echo $img->url; ?>" alt="" /></a>
<?php
			}
		}
?>
		<a href="<?php echo $link; ?>">
			<span class="hika_cpanel_product_name"><?php echo $product->order_product_name; ?></span>
<?php
		if($this->config->get('show_code')) {
?>
			<span class="hikashop_cpanel_product_code"><?php echo $product->order_product_code; ?></span>
<?php
		}
		if($group) {
			foreach($order->products as $j => $optionElement) {
				if($optionElement->order_product_option_parent_id != $product->order_product_id)
					continue;
				$product->order_product_price += $optionElement->order_product_price;
				$product->order_product_tax += $optionElement->order_product_tax;
				$product->order_product_total_price += $optionElement->order_product_total_price;
				$product->order_product_total_price_no_vat += $optionElement->order_product_total_price_no_vat;
			}
		}
?>
		</a>
				<p class="hika_cpanel_product_price">
					<span class="hika_cpanel_product_price_quantity">
						<?php echo  $product->order_product_quantity; ?>
					</span>
					<span class="hika_cpanel_product_price_times"> x
					</span>
					<span class="hika_cpanel_product_price_amount">
						<?php echo  $this->currencyClass->format( $product->order_product_price + $product->order_product_tax, $order->order_currency_id ); ?>
					</span>
				</p>
<?php
		if(!empty($product->extraData))
			echo '<p class="hikashop_order_product_extra">' . (is_string($product->extraData) ? $product->extraData : implode('<br/>', $product->extraData)) . '</p>';
?>
				<div style="clear:both;"></div>
			</div>
<?php
	}
	if($show_more) {
?>
			<a href="<?php echo $order_link; ?>" class="hk-list-group-item hika_cpanel_product hika_cpanel_product_more"><span><?php
				echo JText::_('SHOW_MORE_PRODUCTS');
			?> <i class="fa fa-arrow-right"></i></span></a>
<?php
	}
?>
<?php if(!empty($order->extraData->afterProductsListing)) { echo implode("\r\n", $order->extraData->afterProductsListing); } ?>
		</div>
		<div class="hkc-md-4 hika_cpanel_methods">
<?php if(!empty($order->extraData->beforeInfo)) { echo implode("\r\n", $order->extraData->beforeInfo); } ?>
			<dl class="hika_cpanel_order_methods">
<?php if(!empty($order->payment)) { ?>
				<dt><?php echo JText::_('HIKASHOP_PAYMENT_METHOD'); ?></dt>
				<dd><?php echo $order->payment->payment_name; ?></dd>
<?php } ?>
<?php if(!empty($order->shippings)) { ?>
				<dt><?php echo JText::_('HIKASHOP_SHIPPING_METHOD'); ?></dt>
				<dd><?php
		$shippingClass = hikashop_get('class.shipping');
		$shippings_data = $shippingClass->getAllShippingNames($order);
		if(!empty($shippings_data)) {
			if(count($shippings_data) > 1) {
				echo '<ul><li>'.implode('</li><li>', $shippings_data).'</li></ul>';
			} else {
				echo reset($shippings_data);
			}
		}
?></dd>
<?php } ?>
			</dl>
<?php if(!empty($order->extraData->afterInfo)) { echo implode("\r\n", $order->extraData->afterInfo); } ?>
		</div>
	</div>
</div>
<?php
}

if(!empty($this->cpanel_data->cpanel_orders) && ($print_invoice || $cancel_orders)) {
	echo $this->popupHelper->display(
		'',
		'INVOICE',
		hikashop_completeLink('order&task=invoice'.$url_itemid.'',true),
		'hikashop_print_popup',
		760, 480, '', '', 'link'
	);
?>
<script>
if(!window.localPage) window.localPage = {};
window.localPage.cancelOrder = function(id, number) {
	var d = document, form = d.getElementById('hikashop_cancel_order_form');
	if(!form || !form.elements['order_id']) {
		console.log('Error: Form not found, cannot cancel the order');
		return false;
	}
	if(!confirm('<?php echo JText::_('HIKA_CONFIRM_CANCEL_ORDER', true); ?>'.replace(/ORDER_NUMBER/, number)))
		return false;
	form.elements['order_id'].value = id;
	form.submit();
	return false;
};
window.localPage.printInvoice = function(id) {
	hikashop.openBox('hikashop_print_popup','<?php
		$u = hikashop_completeLink('order&task=invoice'.$url_itemid,true);
		echo $u;
		echo (strpos($u, '?') === false) ? '?' : '&';
	?>order_id='+id);
	return false;
};
</script>
<form action="<?php echo hikashop_completeLink('order&task=cancel_order&email=1'); ?>" name="hikashop_cancel_order_form" id="hikashop_cancel_order_form" method="POST">
	<input type="hidden" name="Itemid" value="<?php global $Itemid; echo $Itemid; ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="cancel_order" />
	<input type="hidden" name="email" value="1" />
	<input type="hidden" name="order_id" value="" />
	<input type="hidden" name="ctrl" value="order" />
	<input type="hidden" name="redirect_url" value="<?php echo hikashop_currentURL(); ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
<?php
}

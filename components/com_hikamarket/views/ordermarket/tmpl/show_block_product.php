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
$product = $this->product;
$pid = isset($this->pid) ? $this->pid : (int)$product->order_product_id;
$showVendor = (hikamarket::level(1) && $this->order->order_type == 'sale' && $this->vendor->vendor_id <= 1);

$td_class = !empty($product->order_product_option_parent_id) ? ' hikamarket_order_item_option' : '';

?>
<td class="hikamarket_order_item_name_value<?php echo $td_class; ?>" data-order-product-id="<?php echo $pid; ?>">
<?php
if( $this->editable_order && hikamarket::acl('order/edit/products') ) {
	$product_hash = md5((int)$product->order_product_id . '#' . (int)$product->order_id . '#' . (int)$this->order->order_modified);

	$dropData = array(
		array(
			'name' => '<i class="fas fa-pencil-alt"></i> ' . JText::_('HIKAM_EDIT_PRODUCT'),
			'link' => '#edit-product',
			'click' => 'return window.orderMgr.refreshProduct(this, ' . (int)$product->order_product_id . ', true);'
		),
		'-',
		array(
			'name' => '<i class="far fa-trash-alt"></i> ' . JText::_('HIKAM_DELETE_PRODUCT'),
			'link' => '#delete-product',
			'click' => 'return window.orderMgr.deleteProduct(this, ' . (int)$product->order_product_id . ', \'' . $product_hash . '\');'
		)
	);
	echo '<div style="float:right">' .
		$this->dropdownHelper->display(JText::_('HIKAM_EDIT'), $dropData, array('type' => '', 'mini' => true, 'class' => 'hikabtn-primary', 'right' => false, 'up' => false)) .
		'</div>';
}

if(!empty($product->product_id)) {
?>
	<a onclick="return window.orderMgr.showProduct(this);" data-popup-href="<?php echo hikamarket::completeLink('shop.product&task=show&cid='.$product->product_id, true); ?>" href="<?php echo hikamarket::completeLink('shop.product&task=show&cid='.$product->product_id); ?>"><?php
		if(!empty($product->images)) {
			$img = reset($product->images);
			$thumb = $this->imageHelper->getThumbnail(@$img->file_path, array(50,50), array('default' => 1, 'forcesize' => 1));
			if(!empty($thumb->path) && empty($thumb->external))
				echo '<img src="'. $this->imageHelper->uploadFolder_url . str_replace('\\', '/', $thumb->path).'" alt="" class="hikam_imglist"/>';
			else if(!empty($thumb->path) && !empty($thumb->url))
				echo '<img src="'. $thumb->url.'" alt="" width="50" height="50" class="hikam_imglist"/>';
		}
		echo $product->order_product_name;
	?></a>
<?php
} else {
	echo $product->order_product_name;
}
?>
	<br/>
<?php
echo $product->order_product_code;

if(hikashop_level(2) && !empty($this->fields['item'])) {
?>
	<p class="hikamarket_order_product_custom_item_fields">
<?php
	foreach($this->fields['item'] as $field) {
		$namekey = $field->field_namekey;
		if(empty($product->$namekey) && !strlen($product->$namekey))
			continue;
		echo '<p class="hikamarket_order_item_'.$namekey.'">' .
			$this->fieldsClass->trans($field->field_realname) . ': ' . $this->fieldsClass->show($field,$product->$namekey) .
			'</p>';
	}
?>
	</p>
<?php
}

if(!empty($product->files) && hikamarket::acl('order/show/files')) {
?>
	<div class="hikamarket_order_product_files" style="clear:left;">
		<a href="#files" data-toggle-display="hikamarket_order_product_files_<?php echo (int)$product->order_product_id; ?>" onclick="return window.orderMgr.toggleDisplay(this);"><?php
			echo JText::_('HIKAM_SHOW_FILES');
		?></a>
		<ul id="hikamarket_order_product_files_<?php echo (int)$product->order_product_id;?>" style="display:none;">
<?php
	foreach($product->files as $file) {
		echo '<li class="hikamarket_order_product_file">';

		if(empty($file->file_name))
			$file->file_name = $file->file_path;

		echo $file->file_name;

		if(!empty($this->order_status_for_download) && !in_array($this->order->order_status, explode(',',$this->order_status_for_download)))
			echo $fileHtml .= ' / <b>'.JText::_('BECAUSE_STATUS_NO_DOWNLOAD').'</b>';

		if(!empty($this->download_time_limit)) {
			$time_limit = ($this->download_time_limit + (!empty($this->order->order_invoice_created) ? $this->order->order_invoice_created : $this->order->order_created));
			if($time_limit < time()) {
				echo ' / <b>' . JText::_('TOO_LATE_NO_DOWNLOAD') . '</b>';
			} else {
				echo ' / ' . JText::sprintf('UNTIL_THE_DATE', hikashop_getDate($time_limit));
			}
		}

		if(!empty($file->file_limit) && (int)$file->file_limit != 0) {
			$download_number_limit = $file->file_limit;
			if($download_number_limit < 0)
				$download_number_limit = 0;
		} else {
			$download_number_limit = $this->download_number_limit;
		}

		if(!empty($download_number_limit)) {
			if($download_number_limit <= $file->download_number) {
				echo ' / <b>'.JText::_('MAX_REACHED_NO_DOWNLOAD').'</b>';
			} else {
				echo ' / '.JText::sprintf('X_DOWNLOADS_LEFT', $download_number_limit - $file->download_number);
			}
		} else {
			echo ' / ' . JText::sprintf('X_DOWNLOADS_MADE', $file->download_number);
		}
		echo '</li>';
	}
?>
		</ul>
	</div>
<?php
}

if(hikashop_level(1) && !empty($product->bundle)) {
?>
	<div style="clear:both"></div>
<?php
	foreach($product->bundle as $bundle) {
		$desc = '<strong>'.$bundle->order_product_name . '</strong><br/>'.
			JText::_('CART_PRODUCT_QUANTITY').' '.(int)$bundle->order_product_options['related_quantity'];

		$img = new stdClass();
		if(!empty($bundle->images))
			$img = reset($bundle->images);
		$thumb = $this->imageHelper->getThumbnail(@$img->file_path, array(35,35), array('default' => 0, 'forcesize' => 1));
		if(!empty($thumb->success)) {
			echo '<img src="'. $thumb->url.'" alt="" class="hikam_bundlelist" alt="'.$this->escape($bundle->order_product_name).'" data-toggle="hk-tooltip" data-title="' . htmlspecialchars($desc, ENT_COMPAT, 'UTF-8') . '"/> ';
		} else {
			echo hikamarket::tooltip($desc, '', '', $bundle->order_product_name). ' ';
		}
	}
}

if(!empty($product->extraData)) {
	if(!is_array($product->extraData))
		$product->extraData = array($product->extraData);
	echo implode("\r\n", $product->extraData);
}

?>
</td>
<td class="hikamarket_order_item_price_value"><?php
	echo $this->currencyHelper->format($product->order_product_price, $this->order->order_currency_id);
	if(bccomp($product->order_product_tax, 0, 5))
		echo '<br/>'.JText::sprintf('PLUS_X_OF_VAT', $this->currencyHelper->format($product->order_product_tax, $this->order->order_currency_id));
?></td>
<?php if($showVendor) { ?>
<td class="hikamarket_order_item_vendor_value"><?php
	$vendor_display = false;
	if(!empty($product->vendor_data) && is_array($product->vendor_data)) {
		foreach($product->vendor_data as $vendor_data) {
			if((int)$vendor_data->vendor_id <= 1)
				continue;
			$vendor_display = true;
			echo '<p>'.$vendor_data->vendor_name.'<br/>'.
				$this->currencyHelper->format($vendor_data->order_product_vendor_price, $this->order->order_currency_id).'</p>';
		}
	}

	if(!$vendor_display)
		echo '-';
?></td>
<?php } ?>
<td class="hikamarket_order_item_quantity_value"><?php
	echo (int)$product->order_product_quantity;
?></td>
<td class="hikamarket_order_item_total_price_value"><?php
	echo $this->currencyHelper->format($product->order_product_total_price, $this->order->order_currency_id);
?></td>

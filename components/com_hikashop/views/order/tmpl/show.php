<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_order_main">
<?php
$weight = bccomp(sprintf('%F',$this->order->order_weight), 0, 3);
$unit_weight = false;
if($weight && $this->config->get('show_invoice_unit_weight',0))
	$unit_weight = true;
$total_weight = false;
if($weight && $this->config->get('show_invoice_total_weight',0))
	$total_weight = true;
$order_weight = false;
if($weight && $this->config->get('show_invoice_weight',0))
	$order_weight = true;
$colspan = 4;
$css_button = $this->config->get('css_button','hikabtn');
if($this->invoice_type == 'order') {
	echo $this->toolbarHelper->process($this->toolbar, $this->title);
?>
<form action="<?php echo hikashop_completeLink('order'.$this->url_itemid); ?>" method="post" name="adminForm" id="adminForm">
<?php
}
?>
	<table class="hikashop_order_main_table">
		<tr>
			<td>
				<div id="hikashop_order_right_part" class="hikashop_order_right_part">
<!-- DATE -->
<?php
		if($this->invoice_type == 'order' || empty($this->element->order_invoice_created)) {
			echo JText::_('DATE').': '.hikashop_getDate($this->element->order_created, '%d %B %Y');
		} else {
			echo JText::_('DATE').': '.hikashop_getDate($this->element->order_invoice_created, '%d %B %Y');
		}
?>
<br/>
<!-- EO DATE -->
<!-- INVOICE NUMBER -->
<?php
		if($this->invoice_type == 'order' || empty($this->element->order_invoice_number)) {
			echo JText::_('HIKASHOP_ORDER').': '.@$this->element->order_number;
		} else {
			echo JText::_(strtoupper($this->invoice_type)).': '.@$this->element->order_invoice_number;
		}
?>
<!-- EO INVOICE NUMBER -->
				</div>
				<div id="hikashop_order_left_part" class="hikashop_order_left_part">
<!-- STORE LOGO -->
<?php
		if (!empty($this->image_address_path) && $this->invoice_type != 'order') {
?>					<div class="hika_invoice_img">
						<img src="<?php echo $this->image_address_path; ?>" style="<?php echo $this->img_style_css; ?>">
					</div>
<?php
		}
?>
<!-- EO STORE LOGO -->
<!-- STORE ADDRESS -->
<?php
					echo $this->store_address;
?>
<!-- EO STORE ADDRESS -->
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<table>
					<tr>
<?php
	$params = null;
	$js = '';
?>
<!-- BILLING ADDRESS -->
<?php
	if(!empty($this->element->billing_address)) {
?>
						<td>
							<fieldset class="adminform" id="htmlfieldset_billing">
							<legend style="background-color: #FFFFFF;"><?php echo JText::_('HIKASHOP_BILLING_ADDRESS'); ?></legend>
<?php
		$addressClass = hikashop_get('class.address');
		echo $addressClass->displayAddress($this->element->fields,$this->element->billing_address,'address');
?>
							</fieldset>
						</td>
<?php
	}
?>
<!-- EO BILLING ADDRESS -->
<!-- SHIPPING ADDRESS -->
<?php
	if(!empty($this->element->order_shipping_id) && !empty($this->element->shipping_address)) {
?>
						<td>
							<fieldset class="adminform" id="htmlfieldset_shipping">
								<legend style="background-color: #FFFFFF;"><?php echo JText::_('HIKASHOP_SHIPPING_ADDRESS'); ?></legend>
<?php
		$override = false;
		if(!empty($this->element->override_shipping_address)) {
			$override = $this->element->override_shipping_address;
		}
		if($override !== false ) {
			echo $override;
		} else {
			$addressClass = hikashop_get('class.address');
			echo $addressClass->displayAddress($this->element->fields, $this->element->shipping_address, 'address');
		}
?>
							</fieldset>
						</td>
<?php
	}
?>
<!-- EO SHIPPING ADDRESS -->
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<fieldset class="adminform" id="htmlfieldset_products">
					<legend style="background-color: #FFFFFF;"><?php echo JText::_('PRODUCT_LIST'); ?></legend>
					<table cellpadding="1" width="100%">
						<thead>
							<tr>
<!-- PRODUCT IMAGE & NAME HEADER -->
								<th class="hikashop_order_item_name_title title" colspan="2"><?php
									echo JText::_('PRODUCT');
								?></th>
<!-- EO PRODUCT IMAGE & NAME HEADER -->
<!-- CUSTOM PRODUCT FIELDS HEADER -->
<?php
	$null = null;
	$type = 'display:back_invoice=1';
	if(hikashop_level(1)){
		$productFields = $this->fieldsClass->getFields($type,$null,'product');
		if(!empty($productFields)) {
			$usefulFields = array();
			foreach($productFields as $field){
				$fieldname = $field->field_namekey;
				foreach($this->products as $product){
					if(!empty($product->$fieldname)){
						$usefulFields[] = $field;
						break;
					}
				}
			}
			$productFields = $usefulFields;

			if(!empty($productFields)) {
				foreach($productFields as $field){
					$colspan++;
					?>
					<th class="title" ><?php echo $this->fieldsClass->trans($field->field_realname);?></th>
					<?php
				}
			}
		}
	}
?>
<!-- EO CUSTOM PRODUCT FIELDS HEADER -->
<!-- FILES HEADER -->
<?php
	$files = false;
	foreach($this->order->products as $product ){
		if(!empty($product->files)) {
			$files = true;
			break;
		}
	}
?>
<?php
	if($this->invoice_type == 'order' && $files) {
		$colspan++;
?>
								<th class="hikashop_order_item_files_title title"><?php
									echo JText::_('HIKA_FILES');
								?></th>
<?php
	}
?>
<!-- EO FILES HEADER -->
<!-- ACTIONS HEADER -->
<?php
	if($this->invoice_type == 'order' && !empty($this->action_column)) {
		$colspan++;
		echo '<th class="hikashop_order_item_actions_title title titletoggle">' . JText::_('HIKASHOP_ACTIONS') . '</th>';
	}
?>
<!-- EO ACTIONS HEADER -->
<!-- WEIGHT HEADER -->
<?php
	if($this->invoice_type != 'order' && $unit_weight) {
		$colspan++;
		echo '<th class="hikashop_order_item_unit_weight_title title titletoggle">' . JText::_('PRODUCT_WEIGHT') . '</th>';
	}
?>
<!-- EO WEIGHT HEADER -->
<!-- PRICE HEADER -->
								<th class="hikashop_order_item_price_title title"><?php
									echo JText::_('UNIT_PRICE');
								?></th>
<!-- EO PRICE HEADER -->
<!-- QUANTITY HEADER -->
								<th class="hikashop_order_item_quantity_title title titletoggle"><?php
									echo JText::_('PRODUCT_QUANTITY');
								?></th>
<!-- EO QUANTITY HEADER -->
<!-- TOTAL WEIGHT HEADER -->
<?php
	if($this->invoice_type != 'order' && $total_weight) {
		$colspan++;
		echo '<th class="hikashop_order_item_total_weight_title title titletoggle">' . JText::_('TOTAL_WEIGHT') . '</th>';
	}
?>
<!-- EO TOTAL WEIGHT HEADER -->
<!-- TOTAL PRICE HEADER -->
								<th class="hikashop_order_item_total_title title titletoggle"><?php
									echo JText::_('PRICE');
								?></th>
<!-- EO TOTAL PRICE HEADER -->
							</tr>
						</thead>
						<tbody>
<?php
	$k=0;
	$group = $this->config->get('group_options',0);
	$imageHelper = hikashop_get('helper.image');
	$width = (int)$this->config->get('cart_thumbnail_x', 50);
	$height = (int)$this->config->get('cart_thumbnail_y', 50);
	$image_options = array(
		'default' => true,
		'forcesize' => $this->config->get('image_force_size', true),
		'scale' => $this->config->get('image_scale_mode','inside')
	);
	foreach($this->order->products as $product) {
		$productData = null;
		if(!empty($product->product_id) && !empty($this->products[ (int)$product->product_id ]))
			$productData = $this->products[ (int)$product->product_id ];
		if($group && $product->order_product_option_parent_id)
			continue;
?>
							<tr class="row<?php echo $k;?>">
<!-- IMAGE -->
								<td data-title="<?php echo JText::_('HIKA_IMAGE'); ?>" class="hikashop_order_item_image_value">
<?php
		$image_path = (!empty($product->images) ? @$product->images[0]->file_path : '');
		$img = $imageHelper->getThumbnail($image_path, array('width' => $width, 'height' => $height), $image_options);
		if($img->success) {
			echo '<img class="hikashop_order_item_image" title="'.$this->escape((string)@$product->images[0]->file_description).'" alt="'.$this->escape((string)@$product->images[0]->file_name).'" src="'.$img->url.'"/>';
		}
?>
								</td>
<!-- EO IMAGE -->
<!-- NAME -->
								<td data-title="<?php echo JText::_('PRODUCT'); ?>" class="hikashop_order_item_name_value">
<?php if($this->invoice_type == 'order' && !empty($product->product_id)) { ?>
									<a class="hikashop_order_product_link" href="<?php echo hikashop_contentLink('product&task=show&cid='.$product->product_id.$this->url_itemid, $productData); ?>">
<?php } ?>
										<div class="hikashop_order_product_name">
											<?php echo '<div>'.$product->order_product_name.'</div>'; ?>
											<?php if($this->config->get('show_code')) { ?>
												<div class="hikashop_product_code_order"><?php echo $product->order_product_code; ?></div>
											<?php } ?>
										</div>
<?php
		if($group) {
			$display_item_price = false;
			foreach($this->order->products as $j => $optionElement) {
				if($optionElement->order_product_option_parent_id != $product->order_product_id)
					continue;
				if($optionElement->order_product_price > 0) {
					$display_item_price = true;
				}

			}
			if($display_item_price) {
				if($this->config->get('price_with_tax')) {
					echo '<div>'.$this->currencyHelper->format($product->order_product_price + $product->order_product_tax, $this->order->order_currency_id).'</div>';
				} else {
					echo '<div>'.$this->currencyHelper->format($product->order_product_price, $this->order->order_currency_id).'</div>';
				}
			}
		}
?>
<?php if($this->invoice_type == 'order' && !empty($product->product_id)) { ?>
									</a>
									<div  class="hikashop_order_product_extra">
<?php }
		if(hikashop_level(2)){
			$item_type = 'display:back_invoice=1';
			if($this->invoice_type == 'order'){
				$item_type = 'display:front_order=1';
			}
			$itemFields = $this->fieldsClass->getFields($item_type,$product,'item');
			if(!empty($itemFields)) {
				foreach($itemFields as $field) {
					$namekey = $field->field_namekey;
					if(!empty($product->$namekey) && strlen($product->$namekey)) {
						echo '<p class="hikashop_order_item_'.$namekey.'">' .
							$this->fieldsClass->getFieldName($field) . ': <span>' .
							$this->fieldsClass->show($field,$product->$namekey).'</span>' .
							'</p>';
					}
				}
			}
		}
		if($group) {
			foreach($this->order->products as $j => $optionElement) {
				if($optionElement->order_product_option_parent_id != $product->order_product_id)
					continue;

				$product->order_product_weight += $optionElement->order_product_weight;
				$product->order_product_price += $optionElement->order_product_price;
				$product->order_product_tax += $optionElement->order_product_tax;
				$product->order_product_total_price += $optionElement->order_product_total_price;
				$product->order_product_total_price_no_vat += $optionElement->order_product_total_price_no_vat;
?>
											<p class="hikashop_order_option_name">
<?php
				echo $optionElement->order_product_name;
				if($optionElement->order_product_price > 0) {
					if($this->config->get('price_with_tax')) {
						echo ' ( + '.$this->currencyHelper->format($optionElement->order_product_price + $optionElement->order_product_tax, $this->order->order_currency_id).' )';
					} else {
						echo ' ( + '.$this->currencyHelper->format($optionElement->order_product_price, $this->order->order_currency_id).' )';
					}
				}
?>
											</p>
<?php
			}
		}
		if(!empty($product->extraData))
			echo '<p class="hikashop_order_product_extra">' . (is_string($product->extraData) ? $product->extraData : implode('<br/>', $product->extraData)) . '</p>';
		?>							</div>
								</td>
<!-- EO NAME -->
<!-- CUSTOM PRODUCT FIELDS -->
<?php	if(hikashop_level(1)){
			if(!empty($productFields)) {
				foreach($productFields as $field){
					$namekey = $field->field_namekey;
					$productData = @$this->products[$product->product_id];
					?>
					<td>
					<?php
					if(!empty($productData->$namekey))
						echo  '<p class="hikashop_order_product_'.$namekey.'">'.$this->fieldsClass->show($field,$productData->$namekey).'</p>';
					?>
					</td>
				<?php
				}
			}
		}
?>
<!-- EO CUSTOM PRODUCT FIELDS -->
<!-- FILES -->
<?php
		if($this->invoice_type == 'order' && $files) { ?>
								<td data-title="<?php echo JText::_('HIKA_FILES'); ?>" class="hikashop_order_item_files_value">
<?php
			if(!empty($product->files) && ($this->order_status_download_ok || bccomp(sprintf('%F',$product->order_product_price), 0, 5) == 0)) {
				$html = array();
				foreach($product->files as $file) {
					$fileHtml = '';

					$download_time_limit = $this->download_time_limit;
					if(!empty($file->file_time_limit))
						$download_time_limit = $file->file_time_limit;
					if(!empty($download_time_limit) && ($download_time_limit + (!empty($this->order->order_invoice_created) ? $this->order->order_invoice_created : $this->order->order_created)) < time()) {
						$fileHtml = JText::_('TOO_LATE_NO_DOWNLOAD');
					}
					if(!empty($file->file_limit) && (int)$file->file_limit != 0) {
						$download_number_limit = $file->file_limit;
						if($download_number_limit < 0)
							$download_number_limit = 0;
					} else {
						$download_number_limit = $this->download_number_limit;
					}

					if(!empty($download_number_limit) && $download_number_limit<=$file->download_number) {
						$fileHtml = JText::_('MAX_REACHED_NO_DOWNLOAD');
					}

					if(empty($fileHtml)) {
						if(empty($file->file_name)) {
							$file->file_name = JText::_('DOWNLOAD_NOW');
						}
						$file_pos = '';
						if(!empty($file->file_pos)) {
							$file_pos = '&file_pos='.$file->file_pos;
						}
						$token = hikaInput::get()->getVar('order_token');
						if(!empty($token))
							$file_pos .= '&order_token='.urlencode($token);
						$fileHtml = 
						'<a class="'.$css_button.'" href="'.hikashop_completeLink('order&task=download&file_id='.$file->file_id.'&order_id='.$this->order->order_id.$file_pos.$this->url_itemid).'">'.
							$file->file_name.
							'<i class="fas fa-download"></i>'.
						'</a>';

						$order_created = (empty($this->order->order_invoice_created) ? $this->order->order_created : $this->order->order_invoice_created);
						if(!empty($download_time_limit))
						$fileHtml .= '<div>/ ' . JText::sprintf('UNTIL_THE_DATE', hikashop_getDate($order_created + $download_time_limit)).'</div>';
						if(!empty($download_number_limit))
						$fileHtml .= '<div>/ '. JText::sprintf('X_DOWNLOADS_LEFT', $download_number_limit - $file->download_number).'</div>';
					} else {
						if(empty($file->file_name)) {
							$file->file_name = JText::_('EMPTY_FILENAME');
						}
						$fileHtml = $file->file_name . ' ' . $fileHtml;
					}
					$html[] = $fileHtml;
				}
				echo implode('<br/>', $html);
		}
?>
								</td>
<?php		if(!empty($product->files) && ($this->order_status_download_ok || bccomp(sprintf('%F',$product->order_product_price), 0, 5) == 0)) { ?>
								<td data-title="<?php echo JText::_('HIKA_FILES'); ?>" class="hikashop_order_item_files_value_resp">
									<span>
										<?php echo implode('<br/>', $html); ?>
									</span>
								</td>
<?php		}
		}
?>
<!-- EO FILES -->
<!-- ACTIONS -->
<?php
		if($this->invoice_type == 'order' && !empty($this->action_column)) {
			echo '<td data-title="' . JText::_('HIKASHOP_ACTIONS') . '" class="hikashop_order_item_actions_value">';
			if(!empty($product->actions)) {
				if(count($product->actions) == 1) {
					$d = reset($product->actions);
					$link = '#';
					$extra = '';
					if(!empty($d['link']))
						$link = $d['link'];
					if(!empty($d['extra']))
						$extra .= ' '.trim($d['extra']);
					if(!empty($d['click']))
						$extra .= ' onclick="'.trim($d['click']).'"';

?>
<a href="<?php echo $link; ?>" class="<?php echo $css_button; ?> hikabtn_order_action" <?php echo $extra; ?>><?php echo $d['name']; ?></a>
<?php
				} else {
					echo $this->dropdownHelper->display(
						JText::_('HIKA_MORE'),
						$product->actions,
						array('type' => 'btn', 'right' => true, 'up' => false)
					);
				}
			}
			echo '</td>';
		}
?>
<!-- EO ACTIONS -->
<!-- WEIGHT -->
<?php
		if($this->invoice_type != 'order' && $unit_weight) {
			echo '<td data-title="'.JText::_('PRODUCT_WEIGHT').'" class="hikashop_order_item_weight_value">' . rtrim(rtrim($product->order_product_weight,'0'),',.').' '.JText::_($product->order_product_weight_unit) . '</td>';
		}
?>
<!-- EO WEIGHT -->
<!-- PRICE -->
								<td data-title="<?php echo JText::_('UNIT_PRICE'); ?>" class="hikashop_order_item_price_value"><?php
									if($this->config->get('price_with_tax')) {
										echo '<span>'.$this->currencyHelper->format($product->order_product_price + $product->order_product_tax, $this->order->order_currency_id).'</span>';
									} else {
										echo '<span>'.$this->currencyHelper->format($product->order_product_price, $this->order->order_currency_id).'</span>';
									}
								?></td>
<!-- EO PRICE -->
<!-- QUANTITY -->
								<td data-title="<?php echo JText::_('PRODUCT_QUANTITY'); ?>" class="hikashop_order_item_quantity_value"><?php
									echo '<span>'.$product->order_product_quantity.'</span>';
								?></td>
<!-- EO QUANTITY -->
<!-- TOTAL WEIGHT -->
<?php
		if($this->invoice_type != 'order' && $total_weight) {
			echo '<td data-title="'.JText::_('TOTAL_WEIGHT').'" class="hikashop_order_item_total_weight_value">' . rtrim(rtrim($product->order_product_weight*$product->order_product_quantity,'0'),',.').' '.JText::_($product->order_product_weight_unit) . '</td>';
		}
?>
<!-- EO TOTAL WEIGHT -->
<!-- TOTAL PRICE -->
								<td data-title="<?php echo JText::_('PRICE'); ?>" class="hikashop_order_item_total_value"><?php
									if($this->config->get('price_with_tax')) {
										echo '<span>'.$this->currencyHelper->format($product->order_product_total_price,$this->order->order_currency_id).'</span>';
									} else {
										echo '<span>'.$this->currencyHelper->format($product->order_product_total_price_no_vat,$this->order->order_currency_id).'</span>';
									}
								?></td>
<!-- EO TOTAL PRICE -->
							</tr>
<?php
		$k = 1 - $k;
	}
	$taxes = $this->currencyHelper->round($this->order->order_subtotal - $this->order->order_subtotal_no_vat + $this->order->order_shipping_tax + $this->order->order_payment_tax - $this->order->order_discount_tax, $this->currencyHelper->getRounding($this->order->order_currency_id, true));
?>
<!-- SUBTOTAL -->
							<tr>
								<td class="hikashop_order_subtotal_title key" style="border-top:2px solid #B8B8B8;" colspan="<?php echo $colspan; ?>">
									<label><?php
										echo JText::_( 'SUBTOTAL' );
									?></label>
								</td>
								<td class="hikashop_order_subtotal_value" style="border-top:2px solid #B8B8B8;"><?php
									if($this->config->get('price_with_tax')) {
										echo $this->currencyHelper->format($this->order->order_subtotal,$this->order->order_currency_id);
									} else {
										echo $this->currencyHelper->format($this->order->order_subtotal_no_vat,$this->order->order_currency_id);
									}
								?></td>
							</tr>
<!-- EO SUBTOTAL -->
<!-- COUPON -->
<?php
	if(!empty($this->order->order_discount_code)) {
?>
							<tr>
								<td class="hikashop_order_coupon_title key" colspan="<?php echo $colspan; ?>">
									<label><?php
										echo JText::_( 'HIKASHOP_COUPON' );
									?></label>
								</td>
								<td class="hikashop_order_coupon_value"><?php
									if($this->config->get('price_with_tax')) {
										echo $this->currencyHelper->format($this->order->order_discount_price*-1.0,$this->order->order_currency_id);
									} else {
										echo $this->currencyHelper->format(($this->order->order_discount_price-@$this->order->order_discount_tax)*-1.0,$this->order->order_currency_id);
									}
								?></td>
							</tr>
<?php
	}
?>
<!-- EO COUPON -->
<!-- SHIPPING FEE -->
<?php
	if(!empty($this->order->order_shipping_method)) {
?>
							<tr>
								<td class="hikashop_order_shipping_title key" colspan="<?php echo $colspan; ?>">
									<label><?php
										echo JText::_( 'SHIPPING' );
									?></label>
								</td>
								<td class="hikashop_order_shipping_value"><?php
									if($this->config->get('price_with_tax')) {
										echo $this->currencyHelper->format($this->order->order_shipping_price,$this->order->order_currency_id);
									} else {
										echo $this->currencyHelper->format($this->order->order_shipping_price-@$this->order->order_shipping_tax,$this->order->order_currency_id);
									}
								?></td>
							</tr>
<?php
	}
?>
<!-- EO SHIPPING FEE -->
<!-- ADDITIONAL -->
<?php
	if(!empty($this->order->additional)) {
		$exclude_additionnal = explode(',', $this->config->get('order_additional_hide', ''));
		foreach($this->order->additional as $additional) {
			if(in_array($additional->order_product_name, $exclude_additionnal))
				continue;
?>
							<tr>
								<td class="hikashop_order_additionall_title key" colspan="<?php echo $colspan; ?>">
									<label><?php
										echo JText::_($additional->order_product_name);
									?></label>
								</td>
								<td class="hikashop_order_additional_value"><?php
									if(!empty($additional->order_product_price)) {
										$additional->order_product_price = (float)$additional->order_product_price;
									}
									if(!empty($additional->order_product_price) || empty($additional->order_product_options)) {
										if($this->config->get('price_with_tax')) {
											echo $this->currencyHelper->format($additional->order_product_price + @$additional->order_product_tax, $this->order->order_currency_id);
										}else{
											echo $this->currencyHelper->format($additional->order_product_price, $this->order->order_currency_id);
										}
									} else {
										echo $additional->order_product_options;
									}
								?></td>
							</tr>
<?php
		}
	}
?>
<!-- EO ADDITIONAL -->
<!-- PAYMENT FEE -->
<?php
	if(!empty($this->order->order_payment_method) && $this->order->order_payment_price != 0) {
?>
							<tr>
								<td class="hikashop_order_payment_title key" colspan="<?php echo $colspan; ?>">
									<label><?php
										echo JText::_( 'HIKASHOP_PAYMENT' );
									?></label>
								</td>
								<td class="hikashop_order_payment_value" ><?php
									if($this->config->get('price_with_tax')) {
										echo $this->currencyHelper->format($this->order->order_payment_price, $this->order->order_currency_id);
									} else {
										echo $this->currencyHelper->format($this->order->order_payment_price - @$this->order->order_payment_tax, $this->order->order_currency_id);
									}
								?></td>
							</tr>
<?php
	}
?>
<!-- EO PAYMENT FEE -->
<!-- TAXES -->
<?php
	if($taxes != 0){
		if($this->config->get('detailed_tax_display') && !empty($this->order->order_tax_info)) {
			foreach($this->order->order_tax_info as $tax) {
?>
							<tr>
								<td class="hikashop_order_tax_title key" colspan="<?php echo $colspan; ?>">
									<label><?php
										echo hikashop_translate($tax->tax_namekey);
									?></label>
								</td>
								<td class="hikashop_order_tax_value"><?php
									echo $this->currencyHelper->format($tax->tax_amount, $this->order->order_currency_id);
								?></td>
							</tr>
<?php
				}
		} else {
?>
							<tr>
								<td class="hikashop_order_tax_title key" colspan="<?php echo $colspan; ?>">
									<label><?php
										echo JText::_( 'VAT' );
									?></label>
								</td>
								<td class="hikashop_order_tax_value"><?php
									echo $this->currencyHelper->format($taxes,$this->order->order_currency_id);
								?></td>
							</tr>
<?php
		}
	}
?>
<!-- EO TAXES -->
<!-- TOTAL -->
							<tr>
								<td class="hikashop_order_total_title key" colspan="<?php echo $colspan; ?>">
									<label><?php
										echo JText::_( 'HIKASHOP_TOTAL' );
									?></label>
								</td>
								<td class="hikashop_order_total_value" ><?php
									echo $this->currencyHelper->format($this->order->order_full_price, $this->order->order_currency_id);
								?></td>
							</tr>
<!-- EO TOTAL -->
						</tbody>
					</table>
				</fieldset>
			</td>
		</tr>
<!-- SHIPPING AND PAYMENT METHODS -->
		<tr>
			<td>
<?php
	if(!empty($this->shipping)) {
		echo '<p class="hikashop_order_shipping_method">'.JText::_('HIKASHOP_SHIPPING_METHOD') . ' : ';
		if(is_string($this->order->order_shipping_method)) {
			if(strpos($this->order->order_shipping_id, '-') !== false)
				echo $this->shippingClass->getShippingName($this->order->order_shipping_method, $this->order->order_shipping_id);
			else
				echo $this->shipping->getName($this->order->order_shipping_method, $this->order->order_shipping_id);
		} else
			echo implode(', ', $this->order->order_shipping_method);
		echo '</p>';
	}
?>
<?php
	if(!empty($this->payment)) {
		echo '<p class="hikashop_order_payment_method">'.JText::_('HIKASHOP_PAYMENT_METHOD') . ' : ' . $this->payment->getName($this->order->order_payment_method, $this->order->order_payment_id).'</p>';
	}
	if($order_weight) {
		echo '<p class="hikashpo_order_total_weight">'.JText::_('HIKASHOP_TOTAL_ORDER_WEIGHT') . ' : ' . rtrim(rtrim($this->order->order_weight,'0'),',.').' '.JText::_($this->order->order_weight_unit).'</p>';
	}
?>
			</td>
		</tr>
<!-- EO SHIPPING AND PAYMENT METHODS -->
<!-- PLUGINS HTML -->
		<tr>
			<td>
<?php
	JPluginHelper::importPlugin('hikashop');
	JPluginHelper::importPlugin('hikashopshipping');
	JPluginHelper::importPlugin('hikashoppayment');
	$app = JFactory::getApplication();
	$app->triggerEvent('onAfterOrderProductsListingDisplay', array(&$this->order, 'order_front_show'));
?>
			</td>
		</tr>
<!-- EO PLUGINS HTML -->
<!-- CUSTOM ORDER FIELDS -->
<?php if(hikashop_level(2) && !empty($this->fields['order'])) { ?>
		<tr>
			<td>
				<fieldset class="hikashop_order_custom_fields_fieldset">
					<legend><?php echo JText::_('ADDITIONAL_INFORMATION'); ?></legend>
					<table class="hikashop_order_custom_fields_table adminlist" cellpadding="1" width="100%">
<?php
		foreach($this->fields['order'] as $fieldName => $oneExtraField) {
			if(empty($this->order->$fieldName))
				continue;
?>
						<tr class="hikashop_order_custom_field_<?php echo $fieldName;?>_line">
							<td class="key"><?php
								echo $this->fieldsClass->getFieldName($oneExtraField);
							?></td>
							<td><?php
								echo $this->fieldsClass->show($oneExtraField,$this->order->$fieldName);
							?></td>
						</tr>
<?php
		}
?>
					</table>
				</fieldset>
			</td>
		</tr>
<?php } ?>
<!-- EO CUSTOM ORDER FIELDS -->
<!-- ENTRIES -->
<?php if(hikashop_level(2) && !empty($this->order->entries)) { ?>
		<tr>
			<td>
				<fieldset class="htmlfieldset_entries">
					<legend><?php echo JText::_('HIKASHOP_ENTRIES'); ?></legend>
					<table class="hikashop_entries_table adminlist" cellpadding="1" width="100%">
						<thead>
							<tr>
								<th class="title titlenum"><?php
									echo JText::_( 'HIKA_NUM' );
								?></th>
<?php
		if(!empty($this->fields['entry'])) {
			foreach($this->fields['entry'] as $field) {
				echo '<th class="title">' . $this->fieldsClass->trans($field->field_realname) . '</th>';
			}
		}
?>
							</tr>
						</thead>
						<tbody>
<?php
		$k = 0;
		$i = 1;
		foreach($this->order->entries as $entry) {
?>
							<tr class="row<?php echo $k;?>">
								<td><?php
									echo $i;
								?></td>
<?php
			if(!empty($this->fields['entry'])) {
				foreach($this->fields['entry'] as $field) {
					$namekey = $field->field_namekey;
					if(!empty($entry->$namekey))
						echo '<td>'.$this->fieldsClass->show($field, $entry->$namekey).'</td>';
				}
			}
?>
							</tr>
<?php
			$k = 1 - $k;
			$i++;
		}
?>
						</tbody>
					</table>
				</fieldset>
			</td>
		</tr>
<?php } ?>
<!-- EO ENTRIES -->
	</table>
	<input type="hidden" name="cid" value="<?php echo (int)$this->element->order_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="cancel_redirect" value="<?php echo hikaInput::get()->getString('cancel_redirect'); ?>" />
	<input type="hidden" name="cancel_url" value="<?php echo hikaInput::get()->getString('cancel_url'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
<?php
if($this->invoice_type == 'order') {
?>
</form>
<?php } ?>
</div>
<div style="page-break-after:always"></div>

<div id="order-detail-content" class="prochec_able_block">
<table id="cart_summary" class="ttr_prochec_table_background col-md-12 std table">
	<colgroup>
		<col width="25%">
		<col width="12.5%">
		<col width="12.5%">
		<col width="12.5%">
		<col width="12.5%">
		<col width="12.5%">
		<col width="12.5%">			
	</colgroup>
<thead>
<tr class="ttr_prochec_Heading row-0 table_head_background">
	<th><?php echo vmText::_ ('COM_VIRTUEMART_CART_NAME') ?></th>
	<th><?php echo vmText::_ ('COM_VIRTUEMART_CART_SKU') ?></th>
	<th><?php echo vmText::_ ('COM_VIRTUEMART_CART_PRICE') ?></th>
	<th><?php echo vmText::_ ('COM_VIRTUEMART_CART_QUANTITY') ?></th>	
	<?php if (VmConfig::get ('show_tax')) {
		$tax = vmText::_ ('COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT');
		if(!empty($this->cart->cartData['VatTax'])){
			if(count($this->cart->cartData['VatTax']) < 2) {
				reset($this->cart->cartData['VatTax']);
				$taxd = current($this->cart->cartData['VatTax']);
				$tax = shopFunctionsF::getTaxNameWithValue($taxd['calc_name'],$taxd['calc_value']);
			}
		}
		?>
	<th><?php echo $tax ?></th>
	<?php } ?>
	<th><?php echo vmText::_ ('COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT')?></th>
	<th><?php echo vmText::_ ('COM_VIRTUEMART_CART_TOTAL') ?></th>
</tr>
</thead>

<tbody>
<?php
$i = 1;
foreach ($this->cart->products as $pkey => $prow) {
	$prow->prices = array_merge($prow->prices,$this->cart->cartPrices[$pkey]);
?>

<tr class="ttr_prochec_row_1 row-1" style="vertical-align: top;">
	<input type="hidden" name="cartpos[]" value="<?php echo $pkey ?>">
	<td class="ttr_prochec_image_border prochec_img" data-title="Name" >
		<?php if ($prow->virtuemart_media_id) { ?>
		<span class="cart-images">
						 <?php
			if (!empty($prow->images[0])) 
			{
				echo $prow->images[0]->displayMediaThumb ('', FALSE);
			}
			?>
		</span>
		<?php } ?>
		<span class="ttr_product_link">	
		<?php echo JHtml::link ($prow->url, $prow->product_name);	?>
		</span>
		<?php /*echo $this->customfieldsModel->CustomsFieldCartDisplay ($prow);*/ ?>

	</td>
	<td class="prochec_des" style="text-align: left;" data-title="SKU">
	<p class="ttr_order_description">	
		<?php  echo $prow->product_sku ?>	
	</p>
	</td>
	<td class="ttr_prochec_price prochec_unit_price" data-title="Price">
		<?php
		if (VmConfig::get ('checkout_show_origprice', 1) && $prow->prices['discountedPriceWithoutTax'] != $prow->prices['priceWithoutTax']) {
			echo '<span class="line-through">' . $this->currencyDisplay->createPriceDiv ('basePriceVariant', '', $prow->prices, TRUE, FALSE) . '</span><br>';
		}

		if ($prow->prices['discountedPriceWithoutTax']) {
			echo $this->currencyDisplay->createPriceDiv ('discountedPriceWithoutTax', '', $prow->prices, FALSE, FALSE, 1.0, false, true);
		} else {
			echo $this->currencyDisplay->createPriceDiv ('basePriceVariant', '', $prow->prices, FALSE, FALSE, 1.0, false, true);
		}
		?>
	</td>
	<td class="ttr_prochec_price prochec_quant" data-title="Quantity"><?php
				if ($prow->step_order_level)
					$step=$prow->step_order_level;
				else
					$step=1;
				if($step==0)
					$step=1;
				?>
		   <input type="text"
				   onblur="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED',true)?>');"
				   onclick="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED',true)?>');"
				   onchange="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED',true)?>');"
				   onsubmit="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED',true)?>');"
				   title="<?php echo  vmText::_('COM_VIRTUEMART_CART_UPDATE') ?>" class="ttr_prochec_price" size="3" maxlength="4" name="quantity[<?php echo $pkey; ?>]" value="<?php echo $prow->quantity ?>" />
				   <span>
				   <button type="submit" class="btn btn-info btn-sm" name="updatecart.<?php echo $pkey ?>" title="<?php echo  vmText::_ ('COM_VIRTUEMART_CART_UPDATE') ?>" >
				   <i class="glyphicon glyphicon-refresh"></i>
				   </button>
				   <button type="submit" class="btn btn-danger btn-sm" name="delete.<?php echo $pkey ?>" title="<?php echo vmText::_ ('COM_VIRTUEMART_CART_DELETE') ?>" >
				   <i class="glyphicon glyphicon-remove-circle"></i>
				   </button>
				   </span>
			</td>

	<?php if (VmConfig::get ('show_tax')) { ?>
	<td class="ttr_prochec_price prochec_tax" data-title="Tax"><?php echo $this->currencyDisplay->createPriceDiv ('taxAmount', '', $prow->prices, FALSE, FALSE, $prow->quantity, false, true) ?></td>
	<?php } ?>
	<td class="ttr_prochec_price prochec_discount" data-title="Discount"><?php echo $this->currencyDisplay->createPriceDiv ('discountAmount', '', $prow->prices, FALSE, FALSE, $prow->quantity, false, true) ?></td>
	<td class="ttr_prochec_price prochec_total" data-title="Total">
		<?php //vmdebug('hm',$prow->prices,$this->cart->cartPrices[$pkey]);
		if (VmConfig::get ('checkout_show_origprice', 1) && !empty($prow->prices['basePriceWithTax']) && $prow->prices['basePriceWithTax'] != $prow->prices['salesPrice']) {
			echo $this->currencyDisplay->createPriceDiv ('basePriceWithTax', '', $prow->prices, TRUE, FALSE, $prow->quantity);
		}
		elseif (VmConfig::get ('checkout_show_origprice', 1) && empty($prow->prices['basePriceWithTax']) && !empty($prow->prices['basePriceVariant']) && $prow->prices['basePriceVariant'] != $prow->prices['salesPrice']) {
			echo $this->currencyDisplay->createPriceDiv ('basePriceVariant', '', $prow->prices, TRUE, FALSE, $prow->quantity);
		}
		echo $this->currencyDisplay->createPriceDiv ('salesPrice', '', $prow->prices, FALSE, FALSE, $prow->quantity) ?></td>
</tr>

	<?php
	$i = ($i==1) ? 2 : 1;
} ?>
<!--Begin of SubTotal, Tax, Shipment, Coupon Discount and Total listing -->
<?php if( empty($this->cart->products)) {  ?>
<tr class="responsive-row row-2">
	<td colspan="4">&nbsp;</td>
	<td colspan="4" class="single-line">
	<hr/>
	</td>
</tr>
<?php  }  ?>

<?php
if (VmConfig::get ('coupons_enable')) {
	?>
	<?php if (VmConfig::get ('show_tax')) {
		$colspan = 3;
	} else {
		$colspan = 2;
	} ?>
<tr class="row-2 responsive-row">
	<td class="ttr_cart_content shipment-payment" colspan="4">
		<?php if (!empty($this->layoutName) && $this->layoutName == 'default') {
		echo $this->loadTemplate ('coupon');
		} ?>

		<?php if (!empty($this->cart->cartData['couponCode'])) { ?>
		<?php
		echo $this->cart->cartData['couponCode'];
		echo $this->cart->cartData['couponDescr'] ? (' (' . $this->cart->cartData['couponDescr'] . ')') : '';
		?>
	</td>

		<?php if (VmConfig::get ('show_tax')) { ?>
	<td class="ttr_prochec_price shipment-payment"><?php echo $this->currencyDisplay->createPriceDiv ('couponTax', '', $this->cart->cartPrices['couponTax'], FALSE); ?> </td>
		<?php } ?>
	<td class="ttr_prochec_price shipment-payment"> </td>
	<td class="ttr_prochec_price shipment-payment"><?php echo $this->currencyDisplay->createPriceDiv ('salesPriceCoupon', '', $this->cart->cartPrices['salesPriceCoupon'], FALSE); ?> </td>
	<?php } else { ?>

	&nbsp;</td>
	<td class="ttr_prochec_price shipment-payment" colspan="<?php echo $colspan ?>">&nbsp;</td>
	<?php }	?>
</tr>
<?php } ?>

<?php
if ($this->totalInPaymentCurrency) {
?>
<tr class="row-2 responsive-price-detail">
	<td class="ttr_cart_content shipment-payment" colspan="4"><?php echo vmText::_ ('COM_VIRTUEMART_CART_TOTAL_PAYMENT') ?>:</td>

	<?php if (VmConfig::get ('show_tax')) { ?>
	<td class="ttr_prochec_price shipment-payment"></td>
	<?php } ?>
	<td class="ttr_prochec_price shipment-payment"></td>
	<td class="ttr_prochec_price shipment-payment"><strong><?php echo $this->totalInPaymentCurrency;   ?></strong></td>
</tr>

<?php
	}

if ( 	VmConfig::get('oncheckout_opc',true) or
	!VmConfig::get('oncheckout_show_steps',false) or
	(!VmConfig::get('oncheckout_opc',true) and VmConfig::get('oncheckout_show_steps',false) and
		!empty($this->cart->virtuemart_shipmentmethod_id) )
) { ?>
<tr class="row-2 responsive-price-detail" style="vertical-align:top;">
	<?php if (!$this->cart->automaticSelectedShipment) { 
	if (VmConfig::get('oncheckout_opc', 0)) 
		{
			$checkout_class = "single_page_checkout";
		}
		else
		{
			$checkout_class = "";	
		}
	?>
		<td class="ttr_cart_content shipment-payment <?php echo $checkout_class ?>" colspan="4" style="vertical-align:top;">
			<?php
				echo '<h3 class="right-aligned ttr_cart_content">'.vmText::_ ('COM_VIRTUEMART_CART_SELECTED_SHIPMENT').'</h3>';
				echo $this->cart->cartData['shipmentName'].'<br>';

		if (!empty($this->layoutName) and $this->layoutName == 'default') {
			if (VmConfig::get('oncheckout_opc', 0)) {
				$previouslayout = $this->setLayout('select');
				echo $this->loadTemplate('shipment');
				
				$this->setLayout($previouslayout);
			} else {
				echo JHtml::_('link', JRoute::_('index.php?option=com_virtuemart&view=cart&task=edit_shipment', $this->useXHTML, $this->useSSL), $this->select_shipment_text, 'class="forgotpassword"');
			}
		} else {
			echo vmText::_ ('COM_VIRTUEMART_CART_SHIPPING');
		}
		echo '</td>';
	} else {
	?>
	<td class="ttr_cart_content shipment-payment" colspan="4" style="vertical-align:top;">
		<?php echo '<h4 class="right-aligned  ttr_cart_content">'.vmText::_ ('COM_VIRTUEMART_CART_SELECTED_SHIPMENT').'</h4>'; ?>
		<?php echo $this->cart->cartData['shipmentName'];
		echo '<span class="floatright">' . $this->currencyDisplay->createPriceDiv ('shipmentValue', '', $this->cart->cartPrices['shipmentValue'], FALSE) . '</span>';
		?>
	</td>
	<?php } ?>

	<?php if (VmConfig::get ('show_tax')) { ?>
	<td class="ttr_prochec_price shipment-payment" data-title="Tax"><?php

	echo "<span class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('shipmentTax', '', $this->cart->cartPrices['shipmentTax'], FALSE) . "</span>"; ?> </td>
	<?php } ?>
	<td class="ttr_prochec_price shipment-payment" data-title="Discount"><?php if($this->cart->cartPrices['salesPriceShipment'] < 0) echo $this->currencyDisplay->createPriceDiv ('salesPriceShipment', '', $this->cart->cartPrices['salesPriceShipment'], FALSE); ?></td>
	<td class="ttr_prochec_price shipment-payment" data-title="Total Tax"><?php echo $this->currencyDisplay->createPriceDiv ('salesPriceShipment', '', $this->cart->cartPrices['salesPriceShipment'], FALSE); ?> </td>
</tr>
<?php } ?>

<?php if ($this->cart->pricesUnformatted['salesPrice']>0.0 and
	( 	VmConfig::get('oncheckout_opc',true) or
		!VmConfig::get('oncheckout_show_steps',false) or
		( (!VmConfig::get('oncheckout_opc',true) and VmConfig::get('oncheckout_show_steps',false) ) and !empty($this->cart->virtuemart_paymentmethod_id))
	)
) { ?>
<tr class="row-2 responsive-price-detail" style="vertical-align:top;">
	<?php if (!$this->cart->automaticSelectedPayment) { 
		if (VmConfig::get('oncheckout_opc', 0)) 
		{
			$checkout_class = "single_page_checkout";
		}
		else
		{
			$checkout_class = "";	
		}
	?>
		<td class="ttr_cart_content shipment-payment <?php echo $checkout_class; ?>" colspan="4" style="vertical-align:top;">
			<?php
				echo '<h3 class="right-aligned ttr_cart_content">'.vmText::_ ('COM_VIRTUEMART_CART_SELECTED_PAYMENT').'</h3>';
				echo $this->cart->cartData['paymentName'].'<br>';

		if (!empty($this->layoutName) && $this->layoutName == 'default') {
			if (VmConfig::get('oncheckout_opc', 0)) {
				$previouslayout = $this->setLayout('select');
				echo $this->loadTemplate('payment');
				$this->setLayout($previouslayout);
			} else {
				echo JHtml::_('link', JRoute::_('index.php?option=com_virtuemart&view=cart&task=editpayment', $this->useXHTML, $this->useSSL), $this->select_payment_text, 'class="forgotpassword"');
			}
		} else {
		echo vmText::_ ('COM_VIRTUEMART_CART_PAYMENT');
	} ?> </td>

	<?php } else { ?>
		<td class="ttr_cart_content shipment-payment" colspan="4" style="vertical-align:top;" >
			<?php echo '<h4 class="right-aligned  ttr_cart_content">'.vmText::_ ('COM_VIRTUEMART_CART_SELECTED_PAYMENT').'</h4>'; ?>
			<?php echo $this->cart->cartData['paymentName']; ?> </td>
	<?php } ?>
	<?php if (VmConfig::get ('show_tax')) { ?>
	<td class="ttr_prochec_price  shipment-payment" data-title="Tax"><?php echo $this->currencyDisplay->createPriceDiv ('paymentTax', '', $this->cart->cartPrices['paymentTax'], FALSE) ; ?> </td>
	<?php } ?>
	<td class="ttr_prochec_price shipment-payment" data-title="Discount"><?php if($this->cart->cartPrices['salesPricePayment'] < 0) echo $this->currencyDisplay->createPriceDiv ('salesPricePayment', '', $this->cart->cartPrices['salesPricePayment'], FALSE); ?></td>
	<td class="ttr_prochec_price  shipment-payment" data-title="Total Tax"><?php  echo $this->currencyDisplay->createPriceDiv ('salesPricePayment', '', $this->cart->cartPrices['salesPricePayment'], FALSE); ?> </td>
</tr>
<?php  } ?>

<?php if (VmConfig::get ('show_tax')) {
	$colspan = 3;
} else {
	$colspan = 2;
} ?>

<tr class="row-2 responsive-price-detail">
	<td class="ttr_cart_content shipment-payment" colspan="4"><?php echo vmText::_ ('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_PRICES_TOTAL'); ?></td>

	<?php if (VmConfig::get ('show_tax')) { ?>
	<td class="ttr_prochec_price shipment-payment" data-title="Tax"><?php echo $this->currencyDisplay->createPriceDiv ('taxAmount', '', $this->cart->cartPrices, FALSE, false, true) ?></td>
	<?php } ?>
	<td class="ttr_prochec_price shipment-payment" data-title="Discount"><?php echo $this->currencyDisplay->createPriceDiv ('discountAmount', '', $this->cart->cartPrices, FALSE) ?></td>
	<td class="ttr_prochec_price shipment-payment" data-title="Total Tax"><?php echo $this->currencyDisplay->createPriceDiv ('salesPrice', '', $this->cart->cartPrices, FALSE) ?></td>
</tr>

<?php
foreach ($this->cart->cartData['DBTaxRulesBill'] as $rule) {
	?>
<tr class="row-2<?php /*echo $i*/ ?>">
	<td class="ttr_cart_content shipment-payment" colspan="4"><?php echo $rule['calc_name'] ?> </td>

	<?php if (VmConfig::get ('show_tax')) { ?>
	<td class="ttr_prochec_price shipment-payment"></td>
	<?php } ?>
	<td class="ttr_prochec_price shipment-payment"><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?></td>
	<td class="ttr_prochec_price shipment-payment"><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?> </td>
</tr>
	<?php
	if ($i) {
		$i = 1;
	} else {
		$i = 0;
	}
} ?>
<?php

foreach ($this->cart->cartData['taxRulesBill'] as $rule) {
	if($rule['calc_value_mathop']=='avalara') continue;
	?>
<tr class="row-2<?php /*echo $i*/ ?>">
	<td class="ttr_cart_content shipment-payment" colspan="4"><?php echo $rule['calc_name'] ?> </td>
	<?php if (VmConfig::get ('show_tax')) { ?>
	<td class="ttr_prochec_price"><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?> </td>
	<?php } ?>
	<td class="ttr_prochec_price shipment-payment"><?php ?> </td>
	<td class="ttr_prochec_price shipment-payment"><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?> </td>
</tr>
	<?php
	if ($i) {
		$i = 1;
	} else {
		$i = 0;
	}
}

foreach ($this->cart->cartData['DATaxRulesBill'] as $rule) {
	?>
<tr class="row-2" colspan="4"><?php echo   $rule['calc_name'] ?> </td>

	<?php if (VmConfig::get ('show_tax')) { ?>
	<td class="ttr_cart_content"></td>

	<?php } ?>
	<td class="ttr_prochec_price shipment-payment" ><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?>  </td>
	<td class="ttr_prochec_price shipment-payment" ><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?> </td>
</tr>
	<?php
	if ($i) {
		$i = 1;
	} else {
		$i = 0;
	}
}
?>
<?php
//Show VAT tax seperated
if(!empty($this->cart->cartData)){
	if(!empty($this->cart->cartData['VatTax'])){
		$c = count($this->cart->cartData['VatTax']);
		if (!VmConfig::get ('show_tax') or $c>1) {
			if($c>0){
				?><tr class="row-2">
				<td class="ttr_cart_content shipment-payment" colspan="4"><?php echo vmText::_ ('COM_VIRTUEMART_TOTAL_INCL_TAX') ?></td>
				<?php if (VmConfig::get ('show_tax')) { ?>
				<td ></td>
				<?php } ?>
				<td></td>
				<td></td>
				</tr><?php
			}
			foreach( $this->cart->cartData['VatTax'] as $vatTax ) {
				if(!empty($vatTax['result'])) {
					echo '<tr class="row-2">';
					echo '<td colspan="4" class="ttr_cart_content">'.shopFunctionsF::getTaxNameWithValue($vatTax['calc_name'],$vatTax['calc_value']). '</td>';
					echo '<td  class="ttr_prochec_price">					
					'.$this->currencyDisplay->createPriceDiv( 'taxAmount', '', $vatTax['result'], FALSE, false, 1.0,false,true ).'					
					</td>';
					echo '<td></td><td></td>';
					echo '</tr>';
				}
			}
		}
	}
}
?>

<tr class="row-2 responsive-row last">
	<td class="ttr_prochec_total" colspan="4"><?php echo vmText::_ ('COM_VIRTUEMART_CART_TOTAL') ?></td>

	<?php if (VmConfig::get ('show_tax')) { ?>
	<td class="ttr_prochec_total"  data-title="Total Tax"> <?php echo $this->currencyDisplay->createPriceDiv ('billTaxAmount', '', $this->cart->cartPrices['billTaxAmount'], FALSE) ?> </td>
	<?php } ?>
	<td class="ttr_prochec_total"  data-title="Total Discount"> <?php echo $this->currencyDisplay->createPriceDiv ('billDiscountAmount', '', $this->cart->cartPrices['billDiscountAmount'], FALSE) ?> </td>
	<td class="ttr_prochec_total" style="font-weight:bold;"  data-title="Total"><?php echo $this->currencyDisplay->createPriceDiv ('billTotal', '', $this->cart->cartPrices['billTotal'], FALSE); ?></td>
</tr>

</tbody>
</table>
<div style="clear: both;"></div>
</div>
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
$class_search = "hika_j3_search";
$class_filters = ' no_extrafilter';

if (HIKASHOP_J40) {
	$class_search = "hika_j4_search";
}
if ((!empty($this->extrafilters)) && (count($this->extrafilters))) {
	foreach($this->extrafilters as $name => $filterObj) {
		if ($name == 'filter_partner') {
			$filter_partner = $filterObj->displayFilter($name, $this->pageInfo->filter);
			unset($this->extrafilters[$name]);
		}
	}
} 
if ((!empty($this->extrafilters)) && (count($this->extrafilters))) {
	$class_filters =' hikafilter_extra extra_'.count($this->extrafilters);
}
?>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('order'); ?>" method="post"  name="adminForm" id="adminForm">
<div class="hk-row-fluid">
	<div class="hkc-md-3 <?php echo $class_search; ?>"><?php
		echo $this->loadHkLayout('search', array());
	?></div>
	<div id="hikashop_listing_filters_id" class="hkc-md-12 hikashop_listing_filters hikashop_listing_filters_order <?php echo $this->openfeatures_class.$class_filters; ?>">
		<div class="hikashop_listing_filters_column colum1 hkc-md-3">
<?php
	if(!is_numeric($this->pageInfo->filter->filter_start) && !empty($this->pageInfo->filter->filter_start)) $this->pageInfo->filter->filter_start = strtotime($this->pageInfo->filter->filter_start);
	echo '<span class="hikafilter_span">'.JText::_('FROM').'</span> ';
	echo JHTML::_('calendar', hikashop_getDate((@$this->pageInfo->filter->filter_start?@$this->pageInfo->filter->filter_start:''),'%Y-%m-%d'), 'filter_start','period_start',hikashop_getDateFormat('%d %B %Y'),array('size'=>'10','onchange'=>'this.form.task=\'\';document.adminForm.submit();', 'onChange'=>'this.form.task=\'\';document.adminForm.submit();'));
	if (isset($filter_partner))
		echo $filter_partner;
?>		
		</div>
		<div class="hikashop_listing_filters_column colum2 hkc-md-3">
<?php
	if(!is_numeric($this->pageInfo->filter->filter_end) && !empty($this->pageInfo->filter->filter_end)) $this->pageInfo->filter->filter_end = strtotime($this->pageInfo->filter->filter_end);
	echo ' <span class="hikafilter_span">'.JText::_('TO').'</span> ';
	echo JHTML::_('calendar', hikashop_getDate((@$this->pageInfo->filter->filter_end?@$this->pageInfo->filter->filter_end:''),'%Y-%m-%d'), 'filter_end','period_end',hikashop_getDateFormat('%d %B %Y'),array('size'=>'10','onchange'=>'this.form.task=\'\';document.adminForm.submit();', 'onChange'=>'this.form.task=\'\';document.adminForm.submit();'));
	echo $this->payment->display("filter_payment",$this->pageInfo->filter->filter_payment,false);
	$this->category->multiple = true;
?>
		</div>
<?php
	if ((!empty($this->extrafilters)) && (count($this->extrafilters))) {
?>		<div class="hikashop_listing_filters_column colum_extra hkc-md-3">
<?php		foreach($this->extrafilters as $name => $filterObj) {
				echo $filterObj->displayFilter($name, $this->pageInfo->filter);
			}
?>		
		</div>
<?php	} ?>

		<div class="hikashop_listing_filters_column colum3 hkc-md-2">
<?php
	echo $this->category->display("filter_status",$this->pageInfo->filter->filter_status,false);
	$this->category->multiple = false;
?>
		</div>
	</div>
</div>
<?php
	$classes = 'adminlist table';
	if(empty($this->colors)) {
		$classes .= ' table-striped table-hover';
	}
	echo $this->loadHkLayout('columns', array()); 
?>
	<table id="hikashop_order_listing" class="<?php echo $classes; ?>" cellpadding="1">
		<thead>
			<tr>
				<th class="hikashop_order_num_title title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="hikashop_order_select_title title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="hikashop_order_number_title title">
					<?php echo JHTML::_('grid.sort', JText::_('ORDER_NUMBER'), 'b.order_number', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
					<br/>
					<?php echo JHTML::_('grid.sort', JText::_('INVOICE_NUMBER'), 'b.order_invoice_number', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_customer_title title">
					<?php echo JHTML::_('grid.sort', JText::_('CUSTOMER'), 'c.name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_total_number_of_products_title title default" data-alias="number_of_products">
					<?php echo JText::_('NUMBER_OF_PRODUCTS'); ?>
				</th>
				<th class="hikashop_order_products_title title default" data-alias="products">
					<?php echo JText::_('PRODUCTS'); ?>
				</th>
				<th class="hikashop_order_billing_address_title title default" data-alias="billing_address">
					<?php echo JText::_('HIKASHOP_BILLING_ADDRESS'); ?>
				</th>
				<th class="hikashop_order_shipping_address_title title default" data-alias="shipping_address">
					<?php echo JText::_('HIKASHOP_SHIPPING_ADDRESS'); ?>
				</th>
				<th class="hikashop_order_shipping_title title default" data-alias="shipping">
					<?php echo JHTML::_('grid.sort', JText::_('HIKASHOP_SHIPPING_METHOD'), 'b.order_shipping_method', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_payment_title title">
					<?php echo JHTML::_('grid.sort', JText::_('PAYMENT_METHOD'), 'b.order_payment_method', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_date_title title">
					<?php echo JHTML::_('grid.sort', JText::_('DATE'), 'b.order_created', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_modified_title title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_LAST_MODIFIED'), 'b.order_modified', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_status_title title">
					<?php echo JHTML::_('grid.sort',   JText::_('ORDER_STATUS'), 'b.order_status', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_coupon_code_title title default" data-alias="coupon_code">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKASHOP_COUPON'), 'b.order_discount_code', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_coupon_price_title title default" data-alias="coupon_price">
					<?php echo JHTML::_('grid.sort',   JText::_('COUPON_VALUE'), 'b.order_discount_price', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_total_title title">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKASHOP_TOTAL'), 'b.order_full_price', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<?php
				$count_fields=0;
				if(!empty($this->rates)){
					foreach($this->rates as $rate){
						$count_fields++;
						echo '<th class="hikashop_order_'.$rate->tax_namekey.'_title title default" data-alias="'.$rate->tax_namekey.'">'.hikashop_translate($rate->tax_namekey).'</th>';
					}
				}

				if(hikashop_level(2) && !empty($this->fields)){
					foreach($this->fields as $field){
						$count_fields++;
						echo '<th class="hikashop_order_'.$field->field_namekey.'_title title">'.JHTML::_('grid.sort', $this->fieldsClass->trans($field->field_realname), 'b.'.$field->field_namekey, $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ).'</th>';
					}
				}
				$count_extrafields = 0;
				if(!empty($this->extrafields)) {
					foreach($this->extrafields as $namekey => $extrafield) {
						echo '<th class="hikashop_order_'.$namekey.'_title title">'.$extrafield->name.'</th>'."\r\n";
					}
					$count_extrafields = count($this->extrafields);
				}?>
				<th class="hikashop_order_id_title title">
					<?php echo JHTML::_('grid.sort', JText::_( 'ID' ), 'b.order_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo 10 + $count_fields + $count_extrafields; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$target = '';
				if($this->popup)
					$target = '" target="_top';
				$k = 0;
				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];

					$products = '<ul>';
					if(!empty($row->products)) {
						foreach($row->products as $p) {
							if(empty($p->order_product_quantity) || !empty($p->order_product_option_parent_id))
								continue;
							$products .= '<li>'.$p->order_product_name.' (x'.$p->order_product_quantity.')</li>';
						}
					}
					$products .= '</ul>';

					$attributes = '';
					if(!empty($this->orderStatuses[$row->order_status]->orderstatus_color))
						$attributes .= ' style="background-color:'.$this->orderStatuses[$row->order_status]->orderstatus_color.';"';
			?>
				<tr class="row<?php echo $k; ?>"<?php echo $attributes; ?>>
					<td class="hikashop_order_num_value">
					<?php echo $this->pagination->getRowOffset($i);
					?>
					</td>
					<td class="hikashop_order_select_value">
						<?php echo JHTML::_('grid.id', $i, $row->order_id ); ?>
					</td>
					<td class="hikashop_order_number_value">
						<?php if($this->manage){ ?>
							<a title="<?php echo JText::_('ORDER_NUMBER'); ?>" href="<?php echo hikashop_completeLink('order&task=edit&cid[]='.$row->order_id.'&cancel_redirect='.urlencode(base64_encode(hikashop_completeLink('order')))).$target; ?>">
						<?php } ?>
								<?php echo $row->order_number; ?>
						<?php if($this->manage){ ?>
							</a>
						<?php } ?>
						<?php if(!empty($row->order_invoice_number)) {
								if($this->manage){ ?>
							<a title="<?php echo JText::_('INVOICE_NUMBER'); ?>" href="<?php echo hikashop_completeLink('order&task=edit&cid[]='.$row->order_id.'&cancel_redirect='.urlencode(base64_encode(hikashop_completeLink('order')))).$target; ?>">
							<?php } ?>
								<?php echo '<br/>'.$row->order_invoice_number; ?>
						<?php if($this->manage){ ?>
							</a>
						<?php }
							} ?>
					</td>
					<td class="hikashop_order_customer_value">
						<?php
						if(empty($row->address_firstname) && empty($row->address_lastname)){
							echo $row->name;
						}else{
							echo $row->address_firstname.' '.$row->address_middle_name.' '.$row->address_lastname;
						}
						if(!empty($row->username)){
							echo ' ( '.$row->username.' )';
						}
						echo '<br/>';
						if(!empty($row->user_id)){
							$url = hikashop_completeLink('user&task=edit&cid[]='.$row->user_id);
							$config =& hikashop_config();
							if(hikashop_isAllowed($config->get('acl_user_manage','all'))) echo $row->user_email.' <a href="'.$url.$target.'"><i class="fa fa-chevron-right"></i></a>';
						}elseif(!empty($row->user_email)){
							echo $row->user_email;
						}
						?>
					</td>
					<td class="hikashop_order_total_number_of_products_value">
						<?php
							echo hikashop_hktooltip($products, '', (int)$row->total_number_of_products);
						?>
					</td>
					<td class="hikashop_order_products_value">
						<?php 
							echo $products;
						?>
					</td>
					<td class="hikashop_order_billing_address_value">
<?php
					if(!empty($row->billing_address)){
						if(empty($row->override_billing_address)) {
							$addressClass = hikashop_get('class.address');
							echo $addressClass->displayAddress($row->fields, $row->billing_address, 'order');
						} else {
							echo $row->override_billing_address;
						}
					}
?>
					</td>
					<td class="hikashop_order_shipping_address_value">
<?php
					if(!empty($row->order_shipping_id) && !empty($row->shipping_address)){
						if(empty($row->override_shipping_address)) {
							$addressClass = hikashop_get('class.address');
							echo $addressClass->displayAddress($row->fields, $row->shipping_address, 'order');
						} else {
							echo $row->override_shipping_address;
						}
					}
?>
					</td>
					<td class="hikashop_order_shipping_value">
						<?php
						if(!empty($row->order_shipping_method)){
							$shippings_data = $this->shippingClass->getAllShippingNames($row);
							if(!empty($shippings_data)) {
								if(count($shippings_data)>1)
									echo '<ul><li>'.implode('</li><li>', $shippings_data).'</li></ul>';
								else
									echo implode('', $shippings_data);
							}
						} ?>
					</td>
					<td class="hikashop_order_payment_value">
						<?php if(!empty($row->order_payment_method)){
							if(!empty($this->payments[$row->order_payment_id])){
								echo $this->payments[$row->order_payment_id]->payment_name;
							}elseif(!empty($this->payments[$row->order_payment_method])){
								echo $this->payments[$row->order_payment_method]->payment_name;
							}else{
								echo $row->order_payment_method;
							}
						} ?>
					</td>
					<td class="hikashop_order_date_value">
						<?php echo hikashop_getDate($row->order_created,'%d %B %Y %H:%M');?>
					</td>
					<td class="hikashop_order_modified_value">
						<?php echo hikashop_getDate($row->order_modified,'%d %B %Y %H:%M');?>
					</td>
					<td class="hikashop_order_status_value">
						<?php
						if($this->manage && !$this->popup){
							$doc = JFactory::getDocument();
							$doc->addScriptDeclaration(' var '."default_filter_status_".$row->order_id.'=\''.$row->order_status.'\'; ');
							echo $this->category->display("filter_status_".$row->order_id,$row->order_status,'onchange="if(this.value==default_filter_status_'.$row->order_id.'){return;} hikashop.openBox(\'status_change_link\',\''.hikashop_completeLink('order&task=changestatus&order_id='.$row->order_id,true).'&status=\'+this.value);this.value=default_filter_status_'.$row->order_id.';if(typeof(jQuery)!=\'undefined\'){jQuery(this).trigger(\'liszt:updated\');}"');
						} else {
							echo $row->order_status;
						}
						?>
					</td>
					<td class="hikashop_order_coupon_code_value">
						<?php echo $row->order_discount_code;?>
					</td>
					<td class="hikashop_order_coupon_price_value">
						<?php echo $this->currencyHelper->format($row->order_discount_price,$row->order_currency_id);?>
					</td>
					<td class="hikashop_order_total_value">
						<?php echo $this->currencyHelper->format($row->order_full_price,$row->order_currency_id);?>
					</td>
<?php
					if(!empty($this->rates)){
						foreach($this->rates as $rate){
							echo '<td>';
							if(!empty($row->order_tax_info)) {
								if(is_string($row->order_tax_info))
									$row->order_tax_info = hikashop_unserialize($row->order_tax_info);
								foreach($row->order_tax_info as $tax) {
									if($tax->tax_namekey == $rate->tax_namekey)
										echo $this->currencyHelper->format($tax->tax_amount,$row->order_currency_id);
								}
							}
							echo '</td>';
						}
					}
					if(hikashop_level(2) && !empty($this->fields)){
						foreach($this->fields as $field){
							$namekey = $field->field_namekey;
							echo '<td class="hikashop_order_'.$namekey.'_value">';
							if(!empty($row->$namekey)) echo $this->fieldsClass->show($field,$row->$namekey);
							echo '</td>';
						}
					}
					if(!empty($this->extrafields)) {
						foreach($this->extrafields as $namekey => $extrafield) {
							$value = '';
							if(!empty($extrafield->value)) {
								$n = $extrafield->value;
								$value = $row->$n;
							} else if(!empty($extrafield->obj)) {
								$n = $extrafield->obj;
								$value = $n->showfield($this, $namekey, $row);
							}
							echo '<td class="hikashop_order_'.$namekey.'_value">'.$value.'</td>';
						}
					}
?>
					<td class="hikashop_order_id_value">
						<?php echo $row->order_id; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
	<?php if($this->manage && !$this->popup){
		echo $this->popupHelper->display(
			JText::_('ORDER_STATUS'),
			'ORDER_STATUS',
			'/',
			'status_change_link',
			760, 480, 'style="display:none;"', '', 'link'
		);
	}
	?>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_product" value="<?php echo $this->pageInfo->filter->filter_product; ?>"/>
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

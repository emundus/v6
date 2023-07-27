<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=report" method="post" name="adminForm" id="adminForm">
<div id="page-report" class="hk-row-fluid">
	<div class="hkc-md-6">
				<fieldset class="adminform">
					<legend><?php echo JText::_('CURRENT_REPORT'); ?></legend>
					<table class="paramlist admintable table">
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_NAME' ); ?>
							</td>
							<td>
								<input type="text" name="data[widget][widget_name]" id="name" class="inputbox" size="40" value="<?php echo $this->escape(@$this->element->widget_name); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "data[widget][widget_published]" , '',@$this->element->widget_published, 'HIKASHOP_YES', 'HIKASHOP_NO', 'data_widget_widget_published'); ?>
							</td>
						</tr>
					</table>
				</fieldset>
				<fieldset class="adminform">
					<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
					<?php
					if(hikashop_level(2)){
						$acltype = hikashop_get('type.acl');
						echo $acltype->display('widget_access',@$this->element->widget_access,'widget');
					}else{
						echo hikashop_getUpgradeLink('business');
					} ?>
				</fieldset>
	</div>
	<div class="hkc-md-6">
				<fieldset class="adminform">
					<legend><?php echo JText::_('DISPLAY'); ?></legend>
					<table class="paramlist admintable table">
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_TYPE' );?>
							</td>
							<td >
								<?php echo $this->widget_dataType->display('data[widget][widget_params][display]',@$this->element->widget_params->display, '', 'widget_display',@$this->element->widget_id, $this->row_id, $this->element->widget_params->display); ?>
							</td>
						</tr>
						<tr id="widget_date">
							<td class="key" >
								<?php echo JText::_( 'DATE_TYPE' );// only for orders ?>
							</td>
							<td>
								<?php echo $this->dateType->display('data[widget][widget_params][date_type]',@$this->element->widget_params->date_type); ?>
							</td>
						</tr>
						<tr id="widget_group">
							<td class="key" >
								<?php echo JText::_( 'DATE_GROUP' );//only for graph and gauge ?>
							</td>
							<td>
								<?php echo $this->dateGroup->display('data[widget][widget_params][date_group]',@$this->element->widget_params->date_group); ?>
							</td>
						</tr>
						<tr id="widget_period">
							<td class="key"><?php echo JText::_('PERIOD'); ?></td>
							<td>
								<span>
									<input <?php if(empty($this->element->widget_params->periodType) || $this->element->widget_params->periodType == 'proposedPeriod') echo 'checked="checked"'; ?> onClick="updatePeriodSelection()" type="radio" value="proposedPeriod" name="data[widget][widget_params][periodType]" id="display_proposed_period"/>
									<?php echo $this->periodType->display('data[widget][widget_params][proposedPeriod]',@$this->element->widget_params->proposedPeriod); ?>
								</span>
								<br/>
<?php
$checked = '';
$start = hikashop_getDate(@$this->element->widget_params->start, '%Y-%m-%d %H:%M');
$end = hikashop_getDate(@$this->element->widget_params->end, '%Y-%m-%d %H:%M');
if(!empty($this->element->widget_params->periodType) && $this->element->widget_params->periodType == 'specificPeriod') {
	$checked = 'checked="checked"';
}else{
	$start = hikashop_getDate(@$this->element->widget_params->start, '%Y-%m-%d').' 00:00';
	$end = hikashop_getDate(@$this->element->widget_params->end, '%Y-%m-%d').' 00:00';
}
?>
								<span>
									<input <?php echo $checked; ?> onClick="updatePeriodSelection()" type="radio" value="specificPeriod" name="data[widget][widget_params][periodType]" id="display_specific_period"/>
<?php
echo JText::_('START_DATE').' ';
echo JHTML::_('calendar', $start, 'data[widget][widget_params][start]','period_start',hikashop_getDateFormat('%d %B %Y %H:%M'),array('size'=>'20'));
echo ' '.JText::_('END_DATE').' ';
echo JHTML::_('calendar', $end, 'data[widget][widget_params][end]','period_end',hikashop_getDateFormat('%d %B %Y %H:%M'),array('size'=>'20'));
?>
									<br/>
<?php echo JText::_('PERIOD').' '; echo $this->delay->display('data[widget][widget_params][period]',(int)@$this->element->widget_params->period,3); ?>
							</td>
						</tr>
					</table>
				</fieldset>
	</div>
</div>
			<div id="widget_type">
				<fieldset class="adminform">
					<legend><?php echo JText::_('HIKA_TYPE'); ?></legend>
					<table class="paramlist admintable">
						<tr>
							<td>
<?php
		$arr = array(
			JHTML::_('select.option', 'orders', JText::_( 'ORDERS' ) ),
			JHTML::_('select.option', 'sales',  JText::_('SALES') ),
			JHTML::_('select.option', 'taxes',  JText::_('TAXES') ),
			JHTML::_('select.option', 'customers', JText::_( 'CUSTOMERS' ) ),
			JHTML::_('select.option', 'partners',  JText::_('PARTNERS') ),
			JHTML::_('select.option', 'products',  JText::_('PRODUCTS') ),
			JHTML::_('select.option', 'categories',  JText::_('HIKA_CATEGORIES') ),
			JHTML::_('select.option', 'discounts',  JText::_('DISCOUNT') ),
		);
		$attribs = 'onClick="updateDisplayType()"';
		if(!HIKASHOP_J40)
			$attribs .= ' class="custom-select"';
		echo JHTML::_('hikaselect.radiolist', $arr, "data[widget][widget_params][content]" , $attribs, 'value', 'text', $this->element->widget_params->content);
?>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
<div id="page-report2" class="hk-row-fluid">
	<div class="hkc-md-6">
				<div id="filters">
					<fieldset class="adminform">
						<legend><?php echo JText::_( 'FILTERS' ); ?></legend>
						<table class="paramlist admintable table" width="100%">
							<tr id="widget_status">
								<td class="key" >
									<?php echo JText::_( 'ORDER_STATUS' );  ?>
								</td>
								<td>
									<?php echo $this->status->display('data[widget][widget_params][filters][a.order_status][]',@$this->element->widget_params->filters['a.order_status'],' multiple="multiple" size="5"',false); ?>
								</td>
							</tr>
							<tr id="widget_currencies">
								<td class="key">
												<?php echo JText::_( 'CURRENCIES' ); ?>
									</td>
									<td>
											<?php 	$currency=hikashop_get('type.currency');
											 $currencyList=$currency->display("data[widget][widget_params][filters][a.order_currency_id][]", @$this->element->widget_params->filters['a.order_currency_id'], 'multiple="multiple" size="4"');
												echo $currencyList;
											?>
									</td>
								</tr>
								<tr>
									<td class="key">
											<?php echo JText::_( 'HIKASHOP_SHIPPING_METHOD' ); ?>
									</td>
									<td>
										<?php echo $this->shippingMethods->display('data[widget][widget_params][shipping][]',@$this->element->widget_params->shipping_type,@$this->element->widget_params->shipping_id,true,'multiple="multiple" size="5"'); ?>
									</td>
								</tr>
								<tr>
									<td class="key">
											<?php echo JText::_( 'HIKASHOP_PAYMENT_METHOD' ); ?>
									</td>
									<td>
										<?php echo $this->paymentMethods->display('data[widget][widget_params][payment][]',@$this->element->widget_params->payment_type,@$this->element->widget_params->payment_id, true, 'multiple="multiple" size="5"'); ?>
									</td>
								</tr>
								<tr>
									<td class="key">
											<?php echo JText::_( 'HIKA_CATEGORIES' ); ?>
									</td>
									<td>
										<?php
											if(@$this->element->widget_params->categories_list == 'all') $this->element->widget_params->categories_list = '';
											echo  $this->nameboxType->display(
												'category',
												explode(',',trim(@$this->element->widget_params->categories_list,',')),
												hikashopNameboxType::NAMEBOX_MULTIPLE,
												'category',
												array(
													'delete' => true,
													'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
												)
											);
										?>
								</td>
						</tr>
						<tr>
								<td class="key">
										<?php echo JText::_( 'INCLUDING_SUB_CATEGORIES' ); ?>
							</td>
							<td>
									<?php echo JHTML::_('hikaselect.booleanlist', "data[widget][widget_params][category_childs]" , '',@$this->element->widget_params->category_childs	); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'PRODUCTS' ); ?>
								</td>
								<td>
										<?php
											if(@$this->element->widget_params->products_list == 'all') $this->element->widget_params->products_list = '';
											echo  $this->nameboxType->display(
												'widget',
												hikashop_unserialize(@$this->element->widget_params->products_list),
												hikashopNameboxType::NAMEBOX_MULTIPLE,
												'product',
												array(
													'delete' => true,
													'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
													'variants' => 2,
													'url_params' => array('VARIANTS' => 2)
												)
											);
										?>
								 </td>
							 </tr>
							 <tr>
									<td class="key">
											<?php echo JText::_( 'COUPONS' ); ?>
									</td>
									<td>
										<?php
											if(@$this->element->widget_params->coupons_list == 'all') $this->element->widget_params->coupons_list = '';
											echo  $this->nameboxType->display(
												'coupon',
												hikashop_unserialize(@$this->element->widget_params->coupons_list),
												hikashopNameboxType::NAMEBOX_MULTIPLE,
												'discount',
												array(
													'delete' => true,
													'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
													'type' => 'coupon',
													'url_params' => array(
														'TYPE' => 'coupon',
													),
												)
											);
										?>
								</td>
						</tr>
					</table>
				</fieldset>
			</div>
	</div>
	<div class="hkc-md-6">
			<div id="customers_options">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'SPECIFIC_OPTIONS' ); ?></legend>
					<table class="paramlist admintable table">
						<tr id="widget_status">
							<td class="key" >
								<?php echo JText::_( 'DISPLAYED_INFORMATION' );  ?>
							</td>
							<td>
								<select name="data[widget][widget_params][customers]" size=2 class="custom-select">
									<option <?php if(!isset($this->element->widget_params->customers) || $this->element->widget_params->customers=='last_customers') echo "selected=\"selected\""; ?> value="last_customers"><?php echo JText::_( 'LAST_CUSTOMER' );  ?></option>
									<option <?php if(isset($this->element->widget_params->customers) && $this->element->widget_params->customers == 'best_customers') echo "selected=\"selected\""; ?> value="best_customers"><?php echo JText::_( 'BEST_CUSTOMER' );  ?></option>
								</select>
							</td>
						</tr>
						<tr id="widget_status">
							<td class="key" >
								<?php echo JText::_( 'ORDERING' ); ?>
							</td>
							<td>
								<select name="data[widget][widget_params][customers_order]" size=2 class="custom-select">
									<option <?php if(!isset($this->element->widget_params->customers_order) || $this->element->widget_params->customers_order == 'sales') echo "selected=\"selected\""; ?> value="sales"><?php echo JText::_( 'SALES' );  ?></option>
									<option <?php if(isset($this->element->widget_params->customers_order) && $this->element->widget_params->customers_order=='orders') echo "selected=\"selected\""; ?> value="orders"><?php echo JText::_( 'ORDERS' );  ?></option>
								</select>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			<div id="partners_options">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'SPECIFIC_OPTIONS' ); ?></legend>
					<table class="paramlist admintable table">
						<tr id="widget_status">
							<td class="key" >
								<?php echo JText::_( 'DISPLAYED_INFORMATION' );  ?>
							</td>
							<td>
								<select name="data[widget][widget_params][partners]" size=2 class="custom-select">
									<option <?php if(!isset($this->element->widget_params->partners) || $this->element->widget_params->partners=='last_customers') echo "selected=\"selected\""; ?> value="last_customers"><?php echo JText::_( 'LAST_PARTNER' );  ?></option>
									<option <?php if(isset($this->element->widget_params->partners) && $this->element->widget_params->partners == 'best_customers') echo "selected=\"selected\""; ?> value="best_customers"><?php echo JText::_( 'BEST_PARTNER' );  ?></option>
								</select>
							</td>
						</tr>
						<tr id="widget_status">
							<td class="key" >
								<?php echo JText::_( 'ORDERING' );  ?>
							</td>
							<td>
								<select name="data[widget][widget_params][partners_order]" size=2 class="custom-select">
									<option <?php if(!isset($this->element->widget_params->partners_order) || $this->element->widget_params->partners_order == 'sales') echo "selected=\"selected\""; ?> value="sales"><?php echo JText::_( 'SALES' );  ?></option>
									<option <?php if(isset($this->element->widget_params->partners_order) && $this->element->widget_params->partners_order=='orders') echo "selected=\"selected\""; ?> value="orders"><?php echo JText::_( 'ORDERS' );  ?></option>
								</select>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			<div id='widget_compare'>
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'COMPARE' ); ?></legend>
					<table width:"100%" class="paramlist admintable table">
						<tr>
							<td class="key" >
								<?php echo JText::_( 'COMPARE' );  ?>
							</td>
							<td>
								<span>
									<input <?php if(empty($this->element->widget_params->compare_with) || $this->element->widget_params->compare_with == 'values') echo 'checked="checked"'; ?> onClick="updateCompare()" type="radio" value="values" name="data[widget][widget_params][compare_with]" id="compare_with_values"/>
									<label  for="compare_with_values"><?php echo JText::_( 'VALUES' ); ?>:</label>
									<span><input <?php if(!empty($this->element->widget_params->compares['a.order_status'])) echo 'checked="checked"'; ?> type="checkbox" value="a.order_status" name="data[widget][widget_params][compares][a.order_status]" id="compares_order_status"/><label for="compares_order_status">Order status</label></span>
									<span><input <?php if(!empty($this->element->widget_params->compares['a.order_currency_id'])) echo 'checked="checked"'; ?> type="checkbox" value="d.currency_name" name="data[widget][widget_params][compares][a.order_currency_id]" id="compares_order_currency_id"/><label for="compares_order_currency_id">Currencies</label></span>
									<span><input <?php if(!empty($this->element->widget_params->compares['a.order_payment_method'])) echo 'checked="checked"'; ?> type="checkbox" value="a.order_payment_method" name="data[widget][widget_params][compares][a.order_payment_method]" id="compares_order_payment_method"/><label for="compares_order_payment_method">Payment Methods</label></span>
									<span><input <?php if(!empty($this->element->widget_params->compares['a.order_shipping_method'])) echo 'checked="checked"'; ?> type="checkbox" value="a.order_shipping_method" name="data[widget][widget_params][compares][a.order_shipping_method]" id="compares_order_shipping_method"/><label for="compares_order_shipping_method">Shipping Methods</label></span>
									<span><input <?php if(!empty($this->element->widget_params->compares['a.order_discount_code'])) echo 'checked="checked"'; ?> type="checkbox" value="a.order_discount_code" name="data[widget][widget_params][compares][a.order_discount_code]" id="compares_order_discount_code"/><label for="compares_order_discount_code">Coupons</label></span>
									<span><input <?php if(!empty($this->element->widget_params->compares['prod.order_product_name'])) echo 'checked="checked"'; ?> type="checkbox" value="prod.order_product_name" name="data[widget][widget_params][compares][prod.order_product_name]" id="compares_products"/><label for="compares_products">Products</label></span>
									<span><input <?php if(!empty($this->element->widget_params->compares['c.category_id'])) echo 'checked="checked"'; ?> type="checkbox" value="c.category_name" name="data[widget][widget_params][compares][c.category_id]" id="compares_categories"/><label for="compares_categories">Categories</label></span>
											<?php  ?>
								</span>
								<br/>
								<span>
									<input <?php if(empty($this->element->widget_params->compare_with) || $this->element->widget_params->compare_with == 'periods') echo 'checked="checked"'; ?> onClick="updateCompare()" type="radio" value="periods" name="data[widget][widget_params][compare_with]" id="compare_with_period"/>
									<label  for="compare_with_period"><?php echo JText::_( 'PERIOD' ); ?>:</label>
									<select name="data[widget][widget_params][period_compare]" id="compare_period" class="custom-select">
												<option <?php if(!isset($this->element->widget_params->period_compare) || $this->element->widget_params->period_compare=='none') echo "selected=\"selected\""; ?> value="none">None</option>
										<option <?php if(isset($this->element->widget_params->period_compare) && $this->element->widget_params->period_compare == 'last_period') echo "selected=\"selected\""; ?> value="last_period">Last Similar Period</option>
										<option <?php if(isset($this->element->widget_params->period_compare) && $this->element->widget_params->period_compare == 'last_year') echo "selected=\"selected\""; ?> value="last_year">Same period last year</option>
									</select>
								</span>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			<div id="widget_specific_options">
			 	<fieldset class="adminform">
					<legend><?php echo JText::_( 'OPTIONS' ); ?></legend>
					<table class="paramlist admintable table">
						<tr id="widget_limit">
							<td class="key">
								<?php echo JText::_( 'LIMIT' );?>
							</td>
							<td>
								<input type="texts" name="data[widget][widget_params][limit]" value="<?php echo $this->escape(@$this->element->widget_params->limit); ?>" onchange="if(this.value <0 || this.value > 50){ alert('Setting a negative value or a too high value for the limit might might broke the dashboard.');}" />
							</td>
						</tr>
						<tr id="widget_region">
							<td class="key">
								<?php echo JText::_( 'ZONE' );//only for map ?>
							</td>
							<td>
								<?php echo $this->region->display('data[widget][widget_params][region]',@$this->element->widget_params->region); ?>
							</td>
						</tr>
						<?php if(hikashop_level(2)){ ?>
						<tr>
							<td class="key">
								<?php echo JText::_('ENCODING_FORMAT'); ?>
							</td>
							<td>
								<?php echo $this->encoding->display("data[widget][widget_params][format]",@$this->element->widget_params->format); ?>
							</td>
						</tr>
						<?php }else{ ?>
						<tr>
							<td class="key">
								<?php echo JText::_('ENCODING_FORMAT'); ?>
							</td>
							<td>
								<?php echo hikashop_getUpgradeLink('business'); ?>
							</td>
						</tr>
						<?php } ?>
						<tr id="map_options">
							<td class="key">
								<?php echo JText::_( 'ORDERING' ); ?>
							</td>
							<td>
								<div class="controls">
									<fieldset class="radio btn-group">
										<input <?php if(empty($this->element->widget_params->map_source) || $this->element->widget_params->map_source == 'shipping') echo 'checked="checked"'; ?> type="radio" value="shipping" name="data[widget][widget_params][map_source]" id="map_source_shipping"/><label for="map_source_shipping"><?php echo JText::_( 'HIKASHOP_SHIPPING_ADDRESS' );  ?></label>
										<input <?php if(!empty($this->element->widget_params->map_source) && $this->element->widget_params->map_source == 'billing') echo 'checked="checked"'; ?> type="radio" value="billing" name="data[widget][widget_params][map_source]" id="map_source_billing"/><label for="map_source_billing"><?php echo JText::_( 'HIKASHOP_BILLING_ADDRESS' );  ?></label>
									</fieldset>
								</div>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			<div id="products_options">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'SPECIFIC_OPTIONS' ); ?></legend>
					<table class="paramlist admintable">
						<tr id='product_datas'>
							<td class="key">
								<?php echo JText::_( 'DISPLAYED_INFORMATION' );?>
							</td>
							<td>
								<div class="controls">
									<fieldset class="radio btn-group">
										<input <?php if(empty($this->element->widget_params->product_data) || $this->element->widget_params->product_data == 'sales') echo 'checked="checked"'; ?> type="radio" value="sales" name="data[widget][widget_params][product_data]" id="data_sales"/><label for="data_sales"><?php echo JText::_( 'SALES' );  ?></label>
										<input <?php if(!empty($this->element->widget_params->product_data) && $this->element->widget_params->product_data == 'orders') echo 'checked="checked"'; ?> type="radio" value="orders" name="data[widget][widget_params][product_data]" id="data_orders"/><label for="data_orders"><?php echo JText::_( 'ORDERS' );  ?></label>
										<span id="data_hits"><input <?php if(!empty($this->element->widget_params->product_data) && $this->element->widget_params->product_data == 'clicks') echo 'checked="checked"'; ?> type="radio" value="clicks" name="data[widget][widget_params][product_data]" id="data_clicks"/><label for="data_clicks"><?php echo JText::_( 'CLICKS' );  ?></label></span>
									</fieldset>
								</div>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'ORDERING' );?>
							</td>
							<td>
							<div class="controls">
										<fieldset class="radio btn-group">
								<input <?php if(empty($this->element->widget_params->product_order_by) || $this->element->widget_params->product_order_by == 'best') echo 'checked="checked"'; ?> type="radio" value="best" name="data[widget][widget_params][product_order_by]" id="order_best"/><label for="order_best"><?php echo JText::_( 'BEST' );  ?></label>
												<input <?php if(!empty($this->element->widget_params->product_order_by) && $this->element->widget_params->product_order_by == 'worst') echo 'checked="checked"'; ?> type="radio" value="worst" name="data[widget][widget_params][product_order_by]" id="order_worst"/><label for="order_worst"><?php echo JText::_( 'WORST' );  ?></label>
								</fieldset>
							</div>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			<div id="orders_options">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'SPECIFIC_OPTIONS' ); ?></legend>
					<table class="paramlist admintable table">
						<tr id="orders_order_by">
							<td class="key">
								<?php echo JText::_( 'ORDERING' ); ?>
							</td>
							<td>
							<div class="controls">
								<fieldset class="radio btn-group">
										<input <?php if(empty($this->element->widget_params->orders_order_by) || $this->element->widget_params->orders_order_by == 'last') echo 'checked="checked"'; ?> type="radio" value="last" name="data[widget][widget_params][orders_order_by]" id="orders_order_last"/><label for="orders_order_last"><?php echo JText::_( 'HIKA_LAST' );  ?></label>
										<input <?php if(!empty($this->element->widget_params->orders_order_by) && $this->element->widget_params->orders_order_by == 'best') echo 'checked="checked"'; ?> type="radio" value="best" name="data[widget][widget_params][orders_order_by]" id="orders_order_best"/><label for="orders_order_best"><?php echo JText::_( 'BEST' );  ?></label>
								</fieldset>
							</div>
							</td>
						</tr>
						<tr id="orders_total_calculation">
							<td class="key">
								<?php echo JText::_( 'INCLUDE_SHIPPING' ); ?>
							</td>
							<td>
							<div class="controls">
								<fieldset class="radio btn-group">
									<input <?php if(empty($this->element->widget_params->orders_total_calculation) || $this->element->widget_params->orders_total_calculation == 'include_fees') echo 'checked="checked"'; ?> type="radio" value="include_fees" name="data[widget][widget_params][orders_total_calculation]" id="include_fees"/><label for="include_fees"><?php echo JText::_( 'HIKASHOP_YES' );  ?></label>
									<input <?php if(!empty($this->element->widget_params->orders_total_calculation) && $this->element->widget_params->orders_total_calculation == 'exclude_fees') echo 'checked="checked"'; ?> type="radio" value="exclude_fees" name="data[widget][widget_params][orders_total_calculation]" id="exclude_fees"/><label for="exclude_fees"><?php echo JText::_( 'HIKASHOP_NO' );  ?></label>
								</fieldset>
							</div>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
	</div>
</div>
	<div style="clear:both" class="clr"></div>
	<?php if($this->dashboard){ 'ok';?>
	<input type="hidden" name="dashboard" value="<?php echo "TRUE"; ?>"/>
	<?php } ?>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->widget_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="delete_row" id="delete_row" value="-1" />
	<input type="hidden" name="ctrl" value="report" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<div id="chart" ></div>
<br style="clear:both" />

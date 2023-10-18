<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('cart'); ?>" method="post" name="adminForm" id="adminForm">

<div class="hikashop_backend_tile_edition">
	<div class="hk-container-fluid">
		<div class="hkc-xl-6 hkc-lg-6 hikashop_tile_block hikashop_cart_edit_general"><div>
			<div class="hikashop_tile_title"><?php echo JText::_('MAIN_INFORMATION'); ?></div>
			<dl class="hika_options large">
				<dt><?php echo JText::_('ID'); ?></dt>
				<dd><span><?php
					echo $this->cart->cart_id;
				?></span></dd>

				<dt><?php echo JText::_('HIKA_TYPE'); ?></dt>
				<dd class="input_large"><?php
					$cart_types = array(
						JHTML::_('select.option', 'cart', JText::_('HIKASHOP_CHECKOUT_CART')),
						JHTML::_('select.option', 'wishlist', JText::_('WISHLIST'))
					);
					echo JHTML::_('select.genericlist', $cart_types, 'data[cart][cart_type]', 'class="custom-select"', 'value', 'text', $this->cart->cart_type);
				?></dd>

				<dt><?php echo JText::_('DATE'); ?></dt>
				<dd><span><?php
					echo hikashop_getDate($this->cart->cart_modified, '%Y-%m-%d %H:%M');
				?></span></dd>

				<dt><?php echo JText::_('CUSTOMER'); ?></dt>
				<dd><?php
					echo $this->nameboxType->display(
						'data[cart][user_id]',
						(int)$this->cart->user_id,
						hikashopNameboxType::NAMEBOX_SINGLE,
						'user',
						array(
							'default_text' => JText::_('HIKA_NONE'),
							'displayFormat' => '{name} - {user_email}',
						)
					);
				?></dd>

				<dt><?php echo JText::_('HIKA_NAME'); ?></dt>
				<dd class="input_large">
					<input type="text" name="data[cart][cart_name]" value="<?php echo $this->escape($this->cart->cart_name); ?>" />
				</dd>

<?php if($this->cart->cart_type == 'cart'){
		if(empty($this->cart->cart_coupon)) {
			$this->cart->cart_coupon = '';
		} elseif(is_array($this->cart->cart_coupon)) {
			$this->cart->cart_coupon = implode("\r\n", $this->cart->cart_coupon);
		}
?>
				<dt><?php echo JText::_('HIKASHOP_COUPON'); ?></dt>
				<dd class="input_large">
					<input type="text" name="data[cart][cart_coupon]" value="<?php echo $this->escape($this->cart->cart_coupon); ?>" />
				</dd>
<?php } ?>
			</dl>
		</div></div>

		<div class="hkc-xl-6 hkc-lg-6 hikashop_tile_block hikashop_cart_edit_general"><div>
			<div class="hikashop_tile_title"><?php echo JText::_('HIKA_DETAILS'); ?></div>
			<dl class="hika_options large">
<?php if(!empty($this->cart->cart_type) && $this->cart->cart_type == 'wishlist') { ?>
				<dt><?php echo JText::_('SHARE'); ?></dt>
				<dd><?php echo $this->cartShareType->display('data[cart][cart_share]', $this->cart->cart_share); ?></dd>
<?php } ?>
<?php if(!empty($this->cart->package['weight'])) { ?>
				<dt><?php echo JText::_('PRODUCT_WEIGHT'); ?></dt>
				<dd><?php echo round($this->cart->package['weight']['value'], 3) . ' ' . $this->cart->package['weight']['unit']; ?></dd>
<?php } ?>
<?php if(!empty($this->cart->package['volume'])) { ?>
				<dt><?php echo JText::_('PRODUCT_VOLUME'); ?></dt>
				<dd><?php echo round($this->cart->package['volume']['value'], 3) . ' ' . $this->cart->package['volume']['unit']; ?></dd>
<?php } ?>
<?php
?>
			</dl>
		</div></div>

		<div class="hkc-xl-12 hkc-lg-12 hikashop_tile_block hikashop_cart_edit_products"><div>
			<div class="hikashop_tile_title"><?php echo JText::_('PRODUCT_LIST'); ?></div>

<table class="adminlist table table-striped table-bordered table-hover">
	<thead>
		<tr>
			<th class="hikashop_cart_item_name_title title">
				<?php echo JText::_('PRODUCT'); ?>
<?php
	$dropData = array(
		array(
			'name' => JText::_('HIKA_ADD_PRODUCT'),
			'link' => '#add-product',
			'click' => 'return window.cartMgr.showAddProduct(this);'
		)
	);
	echo '<div style="float:right">' .
		$this->dropdownHelper->display(JText::_('HIKA_EDIT'), $dropData, array('type' => '', 'right' => true, 'up' => false)) .
		'</div>';
?>
			</th>
<?php
	$colspan = 2;
	if(hikashop_level(2) && !empty($this->fields['product'])) {
		$colspan += count($this->fields['product']);
		foreach($this->fields['product'] as $field) {
?>
			<th class="hikashop_cart_product_<?php echo $field->field_namekey; ?>"><?php echo $this->fieldClass->getFieldName($field); ?></th>
<?php
		}
	}
?>
			<th class="hikashop_cart_item_quantity_title title titletoggle"><?php echo JText::_('HIKASHOP_CHECKOUT_STATUS'); ?></th>
			<th class="hikashop_cart_item_files_title titletoggle"><?php echo JText::_('PRODUCT_QUANTITY'); ?></th>
			<th class="hikashop_cart_item_price_title titletoggle"><?php echo JText::_('PRICE'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="<?php echo $colspan; ?>" style="text-align: right;"><?php echo JText::_('HIKASHOP_TOTAL'); ?></td>
			<td><?php
				echo (int)@$this->cart->quantity->total;
			?></td>
			<td><?php
	if(isset($this->cart->total) && isset($this->cart->total->prices[0])) {
		$price = $this->cart->total->prices[0];
		echo $this->currencyClass->format(
			isset($price->price_value_with_tax) ? $price->price_value_with_tax : $price->price_value,
			$this->cart->cart_currency_id
		);
	}
			?></td>
		</tr>
	</tfoot>
	<tbody>
		<tr id="hikashop_cart_product_0" class="row0" style="display:none;">
			<td colspan="4">
<?php
	echo $this->nameboxType->display(
		'hikashop_cart_new_product',
		0,
		hikashopNameboxType::NAMEBOX_SINGLE,
		'product',
		array(
			'variants' => true,
			'delete' => true,
		)
	);
?>
				<div style="clear:both;margin-top:4px;"></div>
				<div style="float:right">
					<button onclick="return window.cartMgr.addProduct(this);" class="btn btn-success"><img src="<?php echo HIKASHOP_IMAGES; ?>save2.png" alt="" style="vertical-align:middle;"/> <?php echo JText::_('HIKA_OK'); ;?></button>
				</div>
				<button onclick="return window.cartMgr.cancelAddProduct(this);" class="btn btn-danger"><img src="<?php echo HIKASHOP_IMAGES; ?>cancel.png" alt="" style="vertical-align:middle;"/> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
				<div style="clear:both"></div>
			</td>
		</tr>
<?php
	$k = 1;
	foreach($this->cart->cart_products as $k => $cart_product) {
		if(empty($cart_product) || empty($cart_product->cart_product_quantity) || empty($this->cart) || empty($this->cart->products) || !isset($this->cart->products[ $k ]))
			continue;

		$product = $this->cart->products[ $k ];
?>
		<tr id="hikashop_cart_product_<?php echo (int)$product->cart_product_id; ?>" class="row<?php echo $k; ?>">
<?php
		$this->product = $product;
		$this->cart_product = $cart_product;
		echo $this->loadTemplate('block_product');
?>
		</tr>
<?php
		$k = 1 - $k;
	}
?>
	</tbody>
</table>

		</div></div>

	<div style="clear:both" class="clr"></div>
	<input type="hidden" name="cid" value="<?php echo (int)$this->cart->cart_id; ?>" />
	<input type="hidden" name="cart_id" value="<?php echo (int)$this->cart->cart_id; ?>" />
	<input type="hidden" name="cart_type" value="<?php echo $this->cart->cart_type; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="ctrl" value="cart" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<script type="text/javascript">
if(!window.cartMgr)
	window.cartMgr = {};
window.cartMgr.cpt = 0;
window.cartMgr.showAddProduct = function(el) {
	var d = document, o = window.Oby,
		c = null, e = d.getElementById('hikashop_cart_product_0');
	if(!e) return false;
	if(e.style.display != 'none') return false;

	e.style.display = '';

	e = window.oNameboxes['hikashop_cart_new_product'];
	if(!e) return false;
	e.clear();

	return false;
};
window.cartMgr.cancelAddProduct = function(el) {
	var d = document, o = window.Oby,
		c = null, e = d.getElementById('hikashop_cart_product_0');
	if(!e) return false;
	if(e.style.display == 'none') return false;

	e.style.display = 'none';

	e = window.oNameboxes['hikashop_cart_new_product'];
	if(!e) return false;
	e.clear();

	return false;
};
window.cartMgr.addProduct = function(el) {
	var d = document, o = window.Oby,
		c = null, e = d.getElementById('hikashop_cart_product_0');
	if(!e) return false;

	e = window.oNameboxes['hikashop_cart_new_product'];
	if(!e)
		return this.cancelAddProduct(el);

	var product = e.get();

	var url = '<?php echo hikashop_completeLink('cart&task=addproduct&cid='.(int)$this->cart->cart_id, 'ajax', true); ?>',
		params = {mode:'POST', data: o.encodeFormData({'<?php echo hikashop_getFormToken(); ?>':1,'product_id':product.value})};
	o.xRequest(url, params, function(x,p) {
		try {
			var o = JSON.parse(x.responseText);

			if (o && typeof o === "object") {
				for(var i = o.length - 1; i >= 0; i--) {
					alert(o[i].msg);
				}
			}
		}
		catch (e) {
			var trLine = document.createElement('tr');
			trLine.id = 'hikashop_cart_product_n'+(window.cartMgr.cpt++);

			e = d.getElementById('hikashop_cart_product_0');
			e.parentNode.appendChild(trLine);

			var tr = document.createElement('tr'), cell = null;
			tr.innerHTML = x.responseText;
			for(var i = tr.cells.length - 1; i >= 0; i--) {
				cell = tr.cells[0];
				tr.removeChild(cell);
				trLine.appendChild(cell);
				cell = null;
			}
			tr = null;
		}
	});

	return this.cancelAddProduct(el);
};
</script>

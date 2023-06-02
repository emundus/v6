<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$quantity_counter = $this->getQuantityCounter();
$id = 'hikashop_product_quantity_field_'.$quantity_counter;
if($this->params->get('show_quantity_legacy', false)) {
	$i = (int)$this->params->get('i', 0);
	if($i > 0) $id = 'hikashop_product_quantity_field_'.$i;
}
$this->last_quantity_field_id = $id;
$name = 'quantity';
if(!empty($this->row->quantityFieldName)){
	$name = $this->row->quantityFieldName;
}
if(!isset($this->config))
	$this->config = hikashop_config();

if(isset($this->row) && isset($this->row->product_min_per_order)) {
	$min_quantity = ($this->row->product_min_per_order || empty($this->element->main)) ? $this->row->product_min_per_order : @$this->element->main->product_min_per_order;
	$max_quantity = ($this->row->product_max_per_order || empty($this->element->main)) ? $this->row->product_max_per_order : @$this->element->main->product_max_per_order;
	if($this->row->product_quantity > 0) {
		if($max_quantity > 0)
			$max_quantity = min($max_quantity, $this->row->product_quantity);
		else
			$max_quantity = $this->row->product_quantity;
	}
	$min_quantity = max((int)$min_quantity, 1);
	$max_quantity = max((int)$max_quantity, 0);
} else {
	$min_quantity = max((int)$this->params->get('min_quantity', 0), 1);
	$max_quantity = max((int)$this->params->get('max_quantity', 0), 0);
}
$html = $this->params->get('html');

if(!isset($this->global_on_listing)){
	$this->global_on_listing = $this->config->get('show_quantity_field') == 2;
}
if(!empty($this->global_on_listing))
	$min_quantity = 0;

$current_quantity = hikaInput::get()->getInt('quantity', $min_quantity);

if(!isset($this->quantityLayout)) {
	$quantityLayout = $this->config->get('product_quantity_display', 'show_default_div');
	if(isset($this->row))
		$quantityLayout = $this->getProductQuantityLayout($this->row);
} else
	$quantityLayout = $this->quantityLayout;

$extra_classes = '';
if($this->config->get('synchronized_add_to_cart', 0) && isset($this->row)) {
	$cartClass = hikashop_get('class.cart');
	$cartProductData = $cartClass->getCartProductData($this->row->product_id);
	$this->row->synched_cart_quantity = (int)@$cartProductData->cart_product_quantity;
	if($quantityLayout == 'show_default')
		$quantityLayout = 'show_default_div';
	if($this->row->synched_cart_quantity)
		$current_quantity = $this->row->synched_cart_quantity;
	if($min_quantity == 1)
		$min_quantity = 0;
	$cartClass->syncInit();
	if(in_array($quantityLayout, array('show_select','show_select_price')))
		$extra_classes = 'no-chzn';
?>
	<input id="<?php echo $id; ?>_synch" class="synchronized_add_to_cart" data-id="<?php echo $id; ?>" data-product-id="<?php echo $this->row->product_id; ?>"  data-cart-product-id="<?php echo @$cartProductData->cart_product_id; ?>" type="hidden" name="synched_cart_quantity" value="<?php echo $this->row->synched_cart_quantity; ?>"/>
<?php
}
if(HIKASHOP_J40) {
	if(in_array($quantityLayout, array('show_select','show_select_price')))
		$extra_classes.=' '.HK_FORM_SELECT_CLASS;
	else
		$extra_classes.=' '.HK_FORM_CONTROL_CLASS;
}


hikashop_loadJslib('notify');
hikashop_loadJslib('translations');

switch($quantityLayout) {
	case 'show_none':
?>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div"><?php
			echo $html;
		?></div>
<?php
		break;

	case 'show_regrouped':
?>
		<div id="<?php echo $id; ?>_area" class="input-append hikashop_product_quantity_div hikashop_product_quantity_input_div_regrouped">
			<input id="<?php echo $id; ?>" type="text" onfocus="this.select()" value="<?php echo $current_quantity; ?>" class="hikashop_product_quantity_field <?php echo $extra_classes; ?>" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
			<div class="add-on hikashop_product_quantity_div hikashop_product_quantity_change_div_regrouped">
				<div class="hikashop_product_quantity_change_div_plus_regrouped">
					<a class="hikashop_product_quantity_field_change_plus hikashop_product_quantity_field_change" href="#" data-hk-qty-mod="1" onclick="return window.hikashop.updateQuantity(this, '<?php echo $id; ?>');">+</a>
				</div>
				<div class="hikashop_product_quantity_change_div_plus_regrouped">
					<a class="hikashop_product_quantity_field_change_minus hikashop_product_quantity_field_change" href="#" data-hk-qty-mod="-1" onclick="return window.hikashop.updateQuantity(this, '<?php echo $id; ?>');">&ndash;</a>
				</div>
			</div>
		</div>
		<div id="<?php echo $id; ?>_buttons" class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_regrouped"><?php
			echo $html;
		?></div>
<?php
		break;

	case 'show_select':
		$increment = ($min_quantity ? $min_quantity : 1);
		if(empty($max_quantity)) {
			$max_quantity = (int)$increment * $this->config->get('quantity_select_max_default_value', 15);
		} else {
			$increment = min($increment, $max_quantity, $max_quantity-$min_quantity);
		}
		if($min_quantity == $max_quantity)
			$r = array($min_quantity);
		else
			$r = range($min_quantity, $max_quantity, $increment);
?>
		<div id="<?php echo $id; ?>_area" class="hikashop_product_quantity_div hikashop_product_quantity_input_div_select"><?php
			if(!in_array($max_quantity, $r))
				$r[] = $max_quantity;
			$values = array_combine($r, $r);
			ksort($values);
			echo JHTML::_('select.genericlist', $values, '', ' class="'.$extra_classes.'" onchange="document.getElementById(\''.$id.'\').value = this.value; document.getElementById(\''.$id.'\').onchange();"', 'value', 'text', $current_quantity, $id.'_select');
			?>
			<input id="<?php echo $id; ?>" type="hidden" value="<?php echo $current_quantity; ?>" class="hikashop_product_quantity_field" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
		</div>
		<div id="<?php echo $id; ?>_buttons" class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_select"><?php
			echo $html;
		?></div>
<?php
		break;

	case 'show_select_price':
		$increment = ($min_quantity ? $min_quantity : 1);
		if(!$max_quantity){
			$max_quantity = (int)$increment * $this->config->get('quantity_select_max_default_value', 15);
		}
?>
		<div id="<?php echo $id; ?>_area" class="hikashop_product_quantity_div hikashop_product_quantity_input_div_select"><?php
				$values = array();
				if(!isset($this->row->all_prices) && isset($this->row->prices))
					$this->row->all_prices =& $this->row->prices;
				if(!empty($this->row->all_prices)){
					foreach($this->row->all_prices as $price) {
						$price_min_qty = max((int)$price->price_min_quantity, $min_quantity);
						$values[$price_min_qty] = $price_min_qty;
					}
					$min_quantity = min($values);
					$max_quantity = max($values);
					if($current_quantity < $min_quantity)
						$current_quantity = $min_quantity;
				}
				if(empty($values)) {
					$increment = min($increment, $max_quantity);
					$r = range($min_quantity, $max_quantity, $increment);
					if(!in_array($max_quantity, $r))
						$r[] = $max_quantity;
					$values = array_combine($r, $r);
				}
				ksort($values);
				echo JHTML::_('select.genericlist', $values, '', ' class="'.$extra_classes.'" onchange="document.getElementById(\''.$id.'\').value = this.value; document.getElementById(\''.$id.'\').onchange();"', 'value', 'text', $current_quantity);
			?>
			<input id="<?php echo $id; ?>" type="hidden" value="<?php echo $current_quantity; ?>" class="hikashop_product_quantity_field" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
		</div>
		<div id="<?php echo $id; ?>_buttons" class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_select"><?php
			echo $html;
		?></div>
<?php
		break;

	case 'show_simple':
?>
		<span id="<?php echo $id; ?>_area"><input id="<?php echo $id; ?>" type="hidden" value="<?php echo $current_quantity; ?>" class="hikashop_product_quantity_field" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" /></span>
		<div id="<?php echo $id; ?>_buttons" class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_simple"><?php
			echo $html;
		?></div>
<?php
		break;

	case 'show_leftright':

	$extra_class = '';
	if (HIKASHOP_J40) {
		$extra_class = 'hika_j4';
	}
?>
		<div id="<?php echo $id; ?>_area" class="input-prepend input-append hikashop_product_quantity_div hikashop_product_quantity_change_div_leftright <?php echo $extra_class; ?>">
			<span class="add-on">
				<a class="hikashop_product_quantity_field_change_minus hikashop_product_quantity_field_change" href="#" data-hk-qty-mod="-1" onclick="return window.hikashop.updateQuantity(this,'<?php echo $id; ?>');">&ndash;</a>
			</span>
			<input id="<?php echo $id; ?>" type="text" value="<?php echo $current_quantity; ?>" onfocus="this.select()" class="hikashop_product_quantity_field <?php echo $extra_classes; ?>" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
			<span class="add-on">
				<a class="hikashop_product_quantity_field_change_plus hikashop_product_quantity_field_change" href="#" data-hk-qty-mod="1" onclick="return window.hikashop.updateQuantity(this,'<?php echo $id; ?>');">+</a>
			</span>
		</div>
		<div id="<?php echo $id; ?>_buttons" class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_leftright"><?php
			echo $html;
		?></div>
<?php
		break;

	case 'show_simplified':
?>
		<div id="<?php echo $id; ?>_area" class="hikashop_product_quantity_div hikashop_product_quantity_input_div_simplified">
			<input id="<?php echo $id; ?>" type="text" value="<?php echo $current_quantity; ?>" onfocus="this.select()" class="hikashop_product_quantity_field <?php echo $extra_classes; ?>" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
		</div>
		<div id="<?php echo $id; ?>_buttons" class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_simplified"><?php
			echo $html;
		?></div>
<?php
		break;

	case 'show_html5':
		$html5_data = ((int)$max_quantity > 0) ? 'max="'.(int)$max_quantity.'"' : '';
?>
		<div id="<?php echo $id; ?>_area" class="hikashop_product_quantity_div hikashop_product_quantity_input_div_simplified">
			<input id="<?php echo $id; ?>" type="number" min="<?php echo $min_quantity; ?>" value="<?php echo $current_quantity; ?>" class="hikashop_product_quantity_field <?php echo $extra_classes; ?>" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
		</div>
		<div id="<?php echo $id; ?>_buttons" class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_simplified"><?php
			echo $html;
		?></div>
<?php
		break;

	case 'show_default':
?>
		<table>
			<tr>
				<td rowspan="2">
					<input id="<?php echo $id; ?>" type="text" value="<?php echo $current_quantity; ?>" onfocus="this.select()" class="hikashop_product_quantity_field <?php echo $extra_classes; ?>" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
				</td>
				<td>
					<a class="hikashop_product_quantity_field_change_plus hikashop_product_quantity_field_change" href="#" data-hk-qty-mod="1" onclick="return window.hikashop.updateQuantity(this,'<?php echo $id; ?>');">+</a>
				</td>
				<td rowspan="2"><?php
					echo $html;
				?></td>
			</tr>
			<tr>
				<td>
					<a class="hikashop_product_quantity_field_change_minus hikashop_product_quantity_field_change" href="#" data-hk-qty-mod="-1" onclick="return window.hikashop.updateQuantity(this,'<?php echo $id; ?>');">&ndash;</a>
				</td>
			</tr>
		</table>
<?php
		break;

	default:
		if(!empty($this->quantityLayout) && substr($this->quantityLayout, 0, 14) == 'show_quantity_') {
			$quantityDisplayType = hikashop_get('type.quantitydisplay');
			if($quantityDisplayType->check($this->quantityLayout)) {
				$doc = JFactory::getDocument();
				$viewType = $doc->getType();
				$controller = new hikashopBridgeController(array('name'=>'product'));
				$view = $controller->getView('', $viewType, '');
				$view->setLayout($this->quantityLayout);
				echo $view->loadTemplate();
				break;
			}
		}
	case 'show_default_div':
?>
		<div id="<?php echo $id; ?>_area" class="hikashop_product_quantity_input_div_default_main">
			<div class="hikashop_product_quantity_div hikashop_product_quantity_input_div_default">
				<input id="<?php echo $id; ?>" type="text" value="<?php echo $current_quantity; ?>" onfocus="this.select()" class="hikashop_product_quantity_field <?php echo $extra_classes; ?>" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
			</div>
			<div class="hikashop_product_quantity_div hikashop_product_quantity_change_div_default">
				<div class="hikashop_product_quantity_change_div_plus_default">
					<a class="hikashop_product_quantity_field_change_plus hikashop_product_quantity_field_change" href="#" data-hk-qty-mod="1" onclick="return window.hikashop.updateQuantity(this,'<?php echo $id; ?>');">+</a>
				</div>
				<div class="hikashop_product_quantity_change_div_minus_default">
					<a class="hikashop_product_quantity_field_change_minus hikashop_product_quantity_field_change" href="#" data-hk-qty-mod="-1" onclick="return window.hikashop.updateQuantity(this,'<?php echo $id; ?>');">&ndash;</a>
				</div>
			</div>
		</div>
		<div id="<?php echo $id; ?>_buttons" class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_default"><?php
			echo $html;
		?></div>
<?php
		break;
}

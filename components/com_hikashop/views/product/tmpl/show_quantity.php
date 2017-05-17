<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
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
$name = 'quantity';
if(!empty($this->row->quantityFieldName)){
	$name = $this->row->quantityFieldName;
}

if(isset($this->row) && isset($this->row->product_min_per_order)) {
	$min_quantity = max((int)$this->row->product_min_per_order, 1);
	$max_quantity = max((int)$this->row->product_max_per_order, 0);
} else {
	$min_quantity = max((int)$this->params->get('min_quantity', 0), 1);
	$max_quantity = max((int)$this->params->get('max_quantity', 0), 0);
}
$html = $this->params->get('html');

if(!isset($this->global_on_listing)){
	if(!isset($this->config))
		$this->config = hikashop_config();
	$this->global_on_listing = $this->config->get('show_quantity_field') == 2;
}
if(!empty($this->global_on_listing))
	$min_quantity = 0;

$current_quantity = JRequest::getInt('quantity', $min_quantity);

if(!isset($this->quantityLayout)) {
	$quantityLayout = $this->config->get('product_quantity_display', 'show_default');
	if(isset($this->row))
		$quantityLayout = $this->getProductQuantityLayout($this->row);
} else
	$quantityLayout = $this->quantityLayout;

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
		<div class="input-append hikashop_product_quantity_div hikashop_product_quantity_input_div_regrouped">
			<input id="<?php echo $id; ?>" type="text" onfocus="this.select()" value="<?php echo $current_quantity; ?>" class="hikashop_product_quantity_field" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
			<div class="add-on hikashop_product_quantity_div hikashop_product_quantity_change_div_regrouped">
				<div class="hikashop_product_quantity_change_div_plus_regrouped">
					<a class="hikashop_product_quantity_field_change_plus hikashop_product_quantity_field_change" href="#" data-hk-qty-mod="1" onclick="return window.hikashop.updateQuantity(this, '<?php echo $id; ?>');">+</a>
				</div>
				<div class="hikashop_product_quantity_change_div_plus_regrouped">
					<a class="hikashop_product_quantity_field_change_minus hikashop_product_quantity_field_change" href="#" data-hk-qty-mod="-1" onclick="return window.hikashop.updateQuantity(this, '<?php echo $id; ?>');">&ndash;</a>
				</div>
			</div>
		</div>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_regrouped"><?php
			echo $html;
		?></div>
<?php
		break;

	case 'show_select':
		$increment = ($min_quantity ? $min_quantity : 1);
		if(empty($max_quantity)){
			$max_quantity = (int)$increment * 15;
		}
?>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_input_div_select"><?php
			$r = range($min_quantity, $max_quantity, $increment);
			if(!in_array($max_quantity, $r))
				$r[] = $max_quantity;
			$values = array_combine($r, $r);
			ksort($values);
			echo JHTML::_('select.genericlist', $values, '', 'onchange="document.getElementById(\''.$id.'\').value = this.value;"', 'value', 'text', $current_quantity, $id.'_select');
			?>
			<input id="<?php echo $id; ?>" type="hidden" value="<?php echo $current_quantity; ?>" class="hikashop_product_quantity_field" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
		</div>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_select"><?php
			echo $html;
		?></div>
<?php
		break;

	case 'show_select_price':
		$increment = ($min_quantity ? $min_quantity : 1);
		if(!$max_quantity){
			$max_quantity = (int)$increment * 15;
		}
?>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_input_div_select"><?php
				$values = array();
				if(!empty($this->row->all_prices)){
					foreach($this->row->all_prices as $price) {
						$price_min_qty = max((int)$price->price_min_quantity, $min_quantity);
						$values[$price_min_qty] = $price_min_qty;
					}
				}
				if(empty($values)) {
					$r = range($min_quantity, $max_quantity, $increment);
					if(!in_array($max_quantity, $r))
						$r[] = $max_quantity;
					$values = array_combine($r, $r);
				}
				ksort($values);
				echo JHTML::_('select.genericlist', $values, '', 'onchange="document.getElementById(\''.$id.'\').value = this.value;"', 'value', 'text', $current_quantity);
			?>
			<input id="<?php echo $id; ?>" type="hidden" value="<?php echo $current_quantity; ?>" class="hikashop_product_quantity_field" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
		</div>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_select"><?php
			echo $html;
		?></div>
<?php
		break;

	case 'show_simple':
?>
		<input id="<?php echo $id; ?>" type="hidden" value="<?php echo $current_quantity; ?>" class="hikashop_product_quantity_field" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
		<div class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_simple"><?php
			echo $html;
		?></div>
<?php
		break;

	case 'show_leftright':
?>
		<div class="input-prepend input-append hikashop_product_quantity_div hikashop_product_quantity_change_div_leftright">
			<span class="add-on">
				<a class="hikashop_product_quantity_field_change_minus hikashop_product_quantity_field_change" href="#" data-hk-qty-mod="-1" onclick="return window.hikashop.updateQuantity(this,'<?php echo $id; ?>');">&ndash;</a>
			</span>
			<input id="<?php echo $id; ?>" type="text" value="<?php echo $current_quantity; ?>" onfocus="this.select()" class="hikashop_product_quantity_field" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
			<span class="add-on">
				<a class="hikashop_product_quantity_field_change_plus hikashop_product_quantity_field_change" href="#" data-hk-qty-mod="1" onclick="return window.hikashop.updateQuantity(this,'<?php echo $id; ?>');">+</a>
			</span>
		</div>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_leftright"><?php
			echo $html;
		?></div>
<?php
		break;

	case 'show_simplified':
?>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_input_div_simplified">
			<input id="<?php echo $id; ?>" type="text" value="<?php echo $current_quantity; ?>" onfocus="this.select()" class="hikashop_product_quantity_field" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
		</div>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_simplified"><?php
			echo $html;
		?></div>
<?php
		break;

	case 'show_html5':
		$html5_data = ((int)$max_quantity > 0) ? 'max="'.(int)$max_quantity.'"' : '';
?>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_input_div_simplified">
			<input id="<?php echo $id; ?>" type="number" min="<?php echo $min_quantity; ?>" value="<?php echo $current_quantity; ?>" class="hikashop_product_quantity_field" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
		</div>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_simplified"><?php
			echo $html;
		?></div>
<?php
		break;

	case 'show_default':
?>
		<table>
			<tr>
				<td rowspan="2">
					<input id="<?php echo $id; ?>" type="text" value="<?php echo $current_quantity; ?>" onfocus="this.select()" class="hikashop_product_quantity_field" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
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
		<div class="hikashop_product_quantity_div hikashop_product_quantity_input_div_default">
			<input id="<?php echo $id; ?>" type="text" value="<?php echo $current_quantity; ?>" onfocus="this.select()" class="hikashop_product_quantity_field" name="<?php echo $name; ?>" data-hk-qty-min="<?php echo $min_quantity; ?>" data-hk-qty-max="<?php echo $max_quantity; ?>" onchange="window.hikashop.checkQuantity(this);" />
		</div>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_change_div_default">
			<div class="hikashop_product_quantity_change_div_plus_default">
				<a class="hikashop_product_quantity_field_change_plus hikashop_product_quantity_field_change" href="#" data-hk-qty-mod="1" onclick="return window.hikashop.updateQuantity(this,'<?php echo $id; ?>');">+</a>
			</div>
			<div class="hikashop_product_quantity_change_div_minus_default">
				<a class="hikashop_product_quantity_field_change_minus hikashop_product_quantity_field_change" href="#" data-hk-qty-mod="-1" onclick="return window.hikashop.updateQuantity(this,'<?php echo $id; ?>');">&ndash;</a>
			</div>
		</div>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_default"><?php
			echo $html;
		?></div>
<?php
		break;
}

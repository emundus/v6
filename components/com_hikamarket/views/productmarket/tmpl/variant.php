<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikamarket_variant_toolbar">
	<div style="float:left;">
		<button onclick="if(window.productMgr.closeVariant) { return window.productMgr.closeVariant(); } else return false;" class="hikabtn hikabtn-danger"><i class="far fa-times-circle"></i> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
	</div>
	<div style="float:right;">
<?php
	if($this->config->get('product_cart_link', 0) && $this->product->product_id > 0) {
	}
?>
		<button onclick="if(window.productMgr.saveVariant) { return window.productMgr.saveVariant(<?php echo $this->product->product_id; ?>); } else return false;" class="hikabtn hikabtn-success"><i class="far fa-times-circle"></i> <?php echo JText::_('HIKA_SAVE'); ;?></button>
	</div>
	<div style="clear:both"></div>
</div>
<div id="hikamarket_product_variant_edition_<?php echo $this->product->product_id; ?>">
	<table class="hikam_blocks">
		<tr>
			<td class="hikam_block_l hikam_block_d30">
<?php
	if(hikamarket::acl('product/variant/images')) {
		$this->setLayout('form');
		$this->upload_ajax = true;
		echo $this->loadTemplate('image');
	}
?>
			</td>
			<td class="hikam_block_r">
				<dl class="hikam_options">
<?php if(hikamarket::acl('product/variant/name')) { ?>
					<dt class="hikamarket_product_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
					<dd class="hikamarket_product_name"><input type="text" name="data[variant][product_name]" value="<?php echo @$this->product->product_name; ?>"/></dd>

<?php } else { ?>
					<dt class="hikamarket_product_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
					<dd class="hikamarket_product_name"><?php echo @$this->product->product_name; ?></dd>
<?php }

	if(hikamarket::acl('product/variant/code')) { ?>
					<dt class="hikamarket_product_code"><label><?php echo JText::_('PRODUCT_CODE'); ?></label></dt>
					<dd class="hikamarket_product_code"><input type="text" name="data[variant][product_code]" value="<?php echo @$this->product->product_code; ?>"/></dd>
<?php }

	$edit_variant = hikamarket::acl('product/variant/characteristics');
	foreach($this->product->characteristics as $characteristic){ ?>
					<dt class="hikamarket_product_characteristic"><label><?php echo $characteristic->characteristic_value; ?></label></dt>
					<dd class="hikamarket_product_characteristic"><?php
						if($edit_variant)
							echo $this->characteristicType->display('data[variant][characteristic]['.$characteristic->characteristic_id.']', (int)@$characteristic->default_id, @$characteristic->values);
						else
							echo $characteristic->values[$characteristic->default_id];
					?></dd>
<?php
	}

	if(hikamarket::acl('product/variant/quantity')) { ?>
					<dt class="hikamarket_product_quantity"><label><?php echo JText::_('PRODUCT_QUANTITY'); ?></label></dt>
					<dd class="hikamarket_product_quantity">
						<?php echo $this->quantityType->display('data[variant][product_quantity]', @$this->product->product_quantity);?>
					</dd>
<?php }

	if(hikamarket::acl('product/variant/published')) { ?>
					<dt class="hikamarket_product_published"><label><?php echo JText::_('HIKA_PUBLISHED'); ?></label></dt>
					<dd class="hikamarket_product_published"><?php
						echo $this->radioType->booleanlist('data[variant][product_published]', '', @$this->product->product_published);
					?></dd>
<?php } ?>
				</dl>
			</td>
		</tr>
<?php
	if(hikamarket::acl('product/variant/description')) {
		if(!$this->config->get('front_small_editor')) { ?>
		<tr class="hikamarket_product_description">
			<td colspan="2">
				<label class="hikamarket_product_description_label"><?php echo JText::_('HIKA_DESCRIPTION'); ?></label>
				<?php echo $this->editor->display();?>
				<div style="clear:both"></div>
<script type="text/javascript">
window.productMgr.saveVariantEditor = function() { <?php echo $this->editor->jsCode(); ?> };
</script>
			</td>
		</tr>
<?php	} else { ?>
		<tr>
			<td colspan="2">
				<dl class="hikam_options">
					<dt class="hikamarket_product_description"><label><?php echo JText::_('HIKA_DESCRIPTION'); ?></label></dt>
					<dd class="hikamarket_product_description"><?php echo $this->editor->display();?><div style="clear:both"></div></dd>
				</dl>
<script type="text/javascript">
window.productMgr.saveVariantEditor = function() { <?php echo $this->editor->jsCode(); ?> };
</script>
			</td>
		</tr>
<?php	}
	}
?>
		<tr>
			<td colspan="2">
				<dl class="hikam_options">
<?php
	if(hikamarket::acl('product/variant/price')) { ?>
					<dt class="hikamarket_product_price"><label><?php echo JText::_('PRICES'); ?></label></dt>
					<dd class="hikamarket_product_price"><?php
						$this->setLayout('form');
						$this->price_form_key = 'variantprice';
						echo $this->loadTemplate('price');
					?></dd>
<?php }

	if(hikamarket::acl('product/variant/priceoverride')) {?>
					<dt class="hikamarket_product_price_override"><label><?php echo JText::_('MAIN_PRICE_OVERRIDE'); ?></label></dt>
					<dd class="hikamarket_product_price_override">
						<input type="text" name="data[variant][product_price_percentage]" value="<?php echo $this->escape(@$this->product->product_price_percentage); ?>" />%
					</dd>
<?php }

	if(hikamarket::acl('product/variant/qtyperorder')) {?>
					<dt class="hikamarket_product_qtyperorder"><label><?php echo JText::_('QUANTITY_PER_ORDER'); ?></label></dt>
					<dd class="hikamarket_product_qtyperorder">
						<input type="text" name="data[variant][product_min_per_order]" value="<?php echo (int)@$this->product->product_min_per_order; ?>" /><?php
						echo ' ' . JText::_('HIKA_RANGE_TO'). ' ';
						echo $this->quantityType->display('data[variant][product_max_per_order]', @$this->product->product_max_per_order);
					?></dd>
<?php }

	if(hikamarket::acl('product/variant/saledates')) {?>
					<dt class="hikamarket_product_salestart"><label><?php echo JText::_('PRODUCT_SALE_DATES'); ?></label></dt>
					<dd class="hikamarket_product_salestart"><?php
						echo JHTML::_('calendar', hikamarket::getDate((@$this->product->product_sale_start?@$this->product->product_sale_start:''),'%Y-%m-%d %H:%M'), 'data[variant][product_sale_start]','product_variant_sale_start','%Y-%m-%d %H:%M',array('size' => '20'));
						echo ' <span class="calendar-separator">' . JText::_('HIKA_RANGE_TO') . '</span> ';
						echo JHTML::_('calendar', hikamarket::getDate((@$this->product->product_sale_end?@$this->product->product_sale_end:''),'%Y-%m-%d %H:%M'), 'data[variant][product_sale_end]','product_variant_sale_end','%Y-%m-%d %H:%M',array('size' => '20'));
					?></dd>
<?php }

	if(hikamarket::acl('product/variant/weight')) { ?>
					<dt class="hikamarket_product_weight"><label><?php echo JText::_('PRODUCT_WEIGHT'); ?></label></dt>
					<dd class="hikamarket_product_weight"><input type="text" name="data[variant][product_weight]" value="<?php echo @$this->product->product_weight; ?>"/><?php echo $this->weight->display('data[variant][product_weight_unit]', @$this->product->product_weight_unit); ?></dd>
<?php }

	if(hikamarket::acl('product/variant/volume')) { ?>
					<dt class="hikamarket_product_volume"><label><?php echo JText::_('PRODUCT_VOLUME'); ?></label></dt>
					<dd class="hikamarket_product_volume">
							<label><?php echo JText::_('PRODUCT_LENGTH'); ?></label>
							<input size="10" type="text" name="data[variant][product_length]" value="<?php echo @$this->product->product_length; ?>"/><br/>
							<label><?php echo JText::_('PRODUCT_WIDTH'); ?></label>
							<input size="10" type="text" name="data[variant][product_width]" value="<?php echo @$this->product->product_width;?>"/><?php echo $this->volume->display('data[variant][product_dimension_unit]', @$this->product->product_dimension_unit);?><br/>
							<label><?php echo JText::_('PRODUCT_HEIGHT'); ?></label>
							<input size="10" type="text" name="data[variant][product_height]" value="<?php echo @$this->product->product_height; ?>"/>
					</dd>
<?php }

	if(hikamarket::acl('product/variant/customfields')) {
		if(!empty($this->fields)) {
?>
				</dl>
				<div style="clear:both"></div>
<?php
			foreach($this->fields as $fieldName => $oneExtraField) {
?>
				<dl id="<?php echo $this->fieldsClass->prefix . 'product_' . $fieldName; ?>" class="hikam_options">
					<dt class="hikamarket_product_<?php echo $fieldName; ?>"><?php echo $this->fieldsClass->getFieldName($oneExtraField); ?></dt>
					<dd class="hikamarket_product_<?php echo $fieldName; ?>"><?php
						$onWhat = 'onchange';
						if($oneExtraField->field_type == 'radio')
							$onWhat = 'onclick';
						echo $this->fieldsClass->display($oneExtraField, $this->product->$fieldName, 'data[variant]['.$fieldName.']', false, ' '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\'product\',0,\''.$this->fieldsClass->prefix.'\');"');
					?></dd>
				</dl>
<?php
			}
?>
				<dl class="hikam_options">
<?php
		}
	}

	if(hikamarket::acl('product/variant/acl') && hikashop_level(2)) { ?>
					<dt class="hikamarket_product_acl"><label><?php echo JText::_('ACCESS_LEVEL'); ?></label></dt>
					<dd class="hikamarket_product_acl"><?php
						$product_access = 'all';
						if(isset($this->product->product_access))
							$product_access = $this->product->product_access;
						echo $this->joomlaAcl->display('data[variant][product_access]', $product_access, true, true);
					?></dd>
<?php }

	if(hikamarket::acl('product/variant/plugin')) {
		$html = array();
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikamarket');
		JFactory::getApplication()->triggerEvent('onMarketProductBlocksDisplay', array(&$this->product, &$html));

		foreach($html as $h) {
			echo $h;
		}
	}
?>
				</dl>
				<div style="clear:both"></div>
<?php
	if(hikamarket::acl('product/variant/files')) {
		$this->setLayout('form');
		$this->upload_ajax = true;
		echo $this->loadTemplate('file');
	}
?>
			</td>
		</tr>
	</table>
</div>
<input type="hidden" name="data[variant][product_id]" value="<?php echo $this->product->product_id; ?>" />
<script type="text/javascript">
if(JoomlaCalendar && JoomlaCalendar.init){
	setTimeout(function(){
		var section = document.getElementById('hikamarket_product_variant_edition_<?php echo $this->product->product_id; ?>');
		if(!section) return;
		elements = section.querySelectorAll(".field-calendar");
		for(i = 0; i < elements.length; i++){
			JoomlaCalendar.init(elements[i]);
		}
	}, 500);
}
</script>
<?php
$doc = JFactory::getDocument();
foreach($doc->_custom as $custom) {
	$custom = preg_replace('#<script .*(type="text/javascript")? src=".*"></script>#iU', '', $custom);
	$custom = preg_replace('#<script .*type=[\'"]text/javascript[\'"]>#iU', '<script type="text/javascript">', $custom);
	if(strpos($custom,'<script type="text/javascript">') !== false) {
		$custom = str_replace(
			array('<script type="text/javascript">', '</script>'),
			array('<script type="text/javascript">setTimeout(function(){', '},20);</script>'),
			$custom);
	}
	echo $custom;
}
foreach($doc->_script as $script) {
	echo '<script type="text/javascript">'."\r\n".$script."\r\n".'</script>';
}

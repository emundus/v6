<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="ajax_loading_elem"></div>
<div class="ajax_loading_spinner"></div>
<div class="hikashop_variant_toolbar">
	<div style="float:left;">
		<button onclick="if(window.productMgr.closeVariant) { return window.productMgr.closeVariant(); } else return false;" class="btn btn-danger"><img src="<?php echo HIKASHOP_IMAGES; ?>cancel.png" alt="" style="vertical-align:middle;"/> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
	</div>
	<div style="float:right;">
<?php
	if($this->config->get('product_cart_link', 0) && $this->product->product_id > 0) {
	}
?>
		<button onclick="if(window.productMgr.saveVariant) { return window.productMgr.saveVariant(<?php echo $this->product->product_id; ?>); } else return false;" class="btn btn-success"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png" alt="" style="vertical-align:middle;"/> <?php echo JText::_('HIKA_SAVE'); ;?></button>
	</div>
	<div style="clear:both"></div>
</div>
<div id="hikashop_product_variant_edition_<?php echo $this->product->product_id; ?>" class="hk-container-fluid">

	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_general"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_general_title"><?php
			echo JText::_('MAIN_OPTIONS');
		?></div>
		<dl class="hika_options">
<?php if(hikashop_acl('product/variant/name')) { ?>
			<dt class="hikashop_product_name"><label for="data_variant__product_name"><?php echo JText::_('HIKA_NAME'); ?></label></dt>
			<dd class="hikashop_product_name"><input id="data_variant__product_name" type="text" name="data[variant][product_name]" value="<?php echo $this->escape(@$this->product->product_name); ?>"/></dd>
<?php }

	if(hikashop_acl('product/variant/code')) { ?>
			<dt class="hikashop_product_code"><label for="data_variant__product_code"><?php echo JText::_('PRODUCT_CODE'); ?></label></dt>
			<dd class="hikashop_product_code"><input id="data_variant__product_code" type="text" name="data[variant][product_code]" value="<?php echo $this->escape(@$this->product->product_code); ?>"/></dd>
<?php }

	$edit_variant = hikashop_acl('product/variant/characteristics');
	if(!empty($this->product->characteristics)) {
		foreach($this->product->characteristics as $characteristic) { ?>
			<dt class="hikashop_product_characteristic"><label><?php echo hikashop_translate($characteristic->characteristic_value); ?></label></dt>
			<dd class="hikashop_product_characteristic"><?php
				if($edit_variant)
					echo $this->characteristicType->display('data[variant][characteristic]['.$characteristic->characteristic_id.']', (int)@$characteristic->default_id, @$characteristic->values);
				else
					echo $characteristic->values[$characteristic->default_id];
			?></dd>
<?php
		}
	}

	if(hikashop_acl('product/variant/quantity')) { ?>
			<dt class="hikashop_product_quantity"><label for="data_variant__product_quantity"><?php echo JText::_('PRODUCT_QUANTITY'); ?></label></dt>
			<dd class="hikashop_product_quantity"><?php
				echo $this->quantityType->displayInput('data[variant][product_quantity]', @$this->product->product_quantity);
			?></dd>
<?php
	}

	if(hikashop_acl('product/variant/published')) { ?>
			<dt class="hikashop_product_published"><label><?php echo JText::_('HIKA_PUBLISHED'); ?></label></dt>
			<dd class="hikashop_product_published"><?php echo JHTML::_('hikaselect.booleanlist', "data[variant][product_published]" , '', @$this->product->product_published); ?></dd>
<?php
	}

	if(hikashop_acl('product/edit/translations') && !empty($this->product->translations) && !empty($this->product->product_id)) {
?>
			<dt class="hikashop_product_translations"><label><?php echo JText::_('HIKA_TRANSLATIONS'); ?></label></dt>
			<dd class="hikashop_product_translations"><?php
		foreach($this->product->translations as $language_id => $translation){
			$lngName = $this->translationHelper->getFlag($language_id);
			echo '<div class="hikashop_multilang_button hikashop_language_'.$language_id.'"">' .
				$this->popup->display(
					$lngName, $lngName,
					hikashop_completeLink('product&task=edit_translation&product_id=' . @$this->product->product_id.'&language_id='.$language_id, true),
					'hikashop_product_translation_'.$language_id,
					(int)$this->config->get('multi_language_edit_x', 760), (int)$this->config->get('multi_language_edit_y', 480), '', '', 'link'
				).
				'</div>';
		}
			?></dd>
<?php
	}
?>
		</dl>
	</div></div>

	<?php if(hikashop_acl('product/variant/images') || hikashop_acl('product/edit/files')) { ?>
	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_images"><div>
		<div class="hikashop_product_part_title hikashop_product_upload_title"><?php
			echo JText::_('IMAGES_AND_FILES');
		?></div>
<?php
	if(hikashop_acl('product/edit/images')) {
		$this->setLayout('form');
		if(empty($this->params))
			$this->params = new stdClass();
		if(empty($this->params->product_type))
			$this->params->product_type = 'variant';
		$this->upload_ajax = true;
		echo $this->loadTemplate('image');
	}

	if(hikashop_acl('product/edit/files')) {
		$this->setLayout('form');
		if(empty($this->params))
			$this->params = new stdClass();
		if(empty($this->params->product_type))
			$this->params->product_type = 'variant';
		$this->upload_ajax = true;
		echo $this->loadTemplate('file');
	}
?>
	</div></div>
<?php } ?>

	<div class="hkc-lg-clear"></div>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_price"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_price_title"><?php
			echo JText::_('PRICES');
		?></div>
		<dl class="hika_options">
<?php

	if(hikashop_acl('product/edit/tax')) {
	?>
			<dt class="hikashop_product_tax"><label for="data_variant__product_tax_id"><?php echo JText::_('PRODUCT_TAXATION_CATEGORY'); ?></label></dt>
			<dd class="hikashop_product_tax"><?php
				echo $this->categoryType->display('data[variant][product_tax_id]', @$this->product->product_tax_id_orig, 'tax');
			?></dd>
	<?php
	}
	$curr = '';
	$mainCurr = $this->currencyClass->getCurrencies($this->main_currency_id, $curr);
?>
			<dt class="hikashop_product_msrp"><label for="data_variant__product_msrp"><?php echo JText::_('PRODUCT_MSRP'); ?></label></dt>
			<dd class="hikashop_product_msrp">
				<input type="text" id="data_variant__product_msrp" name="data[variant][product_msrp]" value="<?php echo $this->escape(@$this->product->product_msrp); ?>"/> <?php echo $mainCurr[$this->main_currency_id]->currency_symbol.' '.$mainCurr[$this->main_currency_id]->currency_code;?>
			</dd>
<?php if(hikashop_acl('product/variant/price')) { ?>

			<dd class="hikashop_product_price"><?php
				$this->price_form_key = 'variantprice';
				echo $this->loadTemplate('price');
			?></dd>
<?php
	}

	if(hikashop_acl('product/variant/price_override')) { ?>
			<dt class="hikashop_product_price_override"><label for="data_variant__product_price_percentage"><?php echo JText::_('MAIN_PRICE_OVERRIDE'); ?></label></dt>
			<dd class="hikashop_product_price_override">
				<input type="text" id="data_variant__product_price_percentage" name="data[variant][product_price_percentage]" value="<?php echo $this->escape(@$this->product->product_price_percentage); ?>" />%
			</dd>
<?php } ?>
		</dl>
	</div></div>

	<div class="hkc-xl-clear"></div>

	<?php if(hikashop_acl('product/variant/description')) { ?>
	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_description"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_description_title"><?php
			echo JText::_('HIKA_DESCRIPTION');
		?></div>
		<?php echo $this->editor->display();?>
		<div style="clear:both"></div>
<script type="text/javascript">
window.productMgr.saveVariantEditor = function() { <?php echo $this->editor->jsCode(); ?> };
window.productMgr.closeVariantEditor = function() { <?php echo $this->editor->jsUnloadCode(); ?> };
</script>
	</div></div>
<?php } ?>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_restrictions"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_restrictions_title"><?php
			echo JText::_('RESTRICTIONS_AND_DIMENSIONS');
		?></div>
		<dl class="hika_options">
<?php
	if(hikashop_acl('product/edit/qtyperorder')) { ?>
			<dt class="hikashop_product_qtyperorder">
				<label for="data_variant__product_min_per_order"><?php echo JText::_('QUANTITY_PER_ORDER'); ?></label>
			</dt>
			<dd class="hikashop_product_qtyperorder">
				<input type="text" id="data_variant__product_min_per_order" name="data[variant][product_min_per_order]" value="<?php echo (int)@$this->product->product_min_per_order; ?>" /><?php
					echo ' <label for="data_variant__product_max_per_order" style="font-weight:bold">' . JText::_('HIKA_QTY_RANGE_TO') . '</label> ';
					echo $this->quantityType->displayInput('data[variant][product_max_per_order]', @$this->product->product_max_per_order);
			?></dd>
<?php
	}

	if(hikashop_acl('product/edit/salestart')) { ?>
			<dt class="hikashop_product_salestart"><label for="product_sale_start_img"><?php echo JText::_('PRODUCT_SALE_DATES'); ?></label></dt>
			<dd class="hikashop_product_salestart"><?php
				echo JHTML::_('calendar', hikashop_getDate((@$this->product->product_sale_start?@$this->product->product_sale_start:''),'%Y-%m-%d %H:%M'), 'data[variant][product_sale_start]','product_variant_sale_start',hikashop_getDateFormat('%d %B %Y %H:%M'),array('size' => '20'));
				echo ' <label for="product_sale_end_img" class="calendar-separator" style="font-weight:bold">' . JText::_('HIKA_RANGE_TO') . '</label> ';
				echo JHTML::_('calendar', hikashop_getDate((@$this->product->product_sale_end?@$this->product->product_sale_end:''),'%Y-%m-%d %H:%M'), 'data[variant][product_sale_end]','product_variant_sale_end',hikashop_getDateFormat('%d %B %Y %H:%M'),array('size' => '20'));
			?></dd>
<?php
	}

	if(hikashop_acl('product/edit/acl') && hikashop_level(2)) { ?>
			<dt class="hikashop_product_acl"><label for="data[variant][product_access]"><?php echo JText::_('ACCESS_LEVEL'); ?></label></dt>
			<dd class="hikashop_product_acl"><?php
				$product_access = 'all';
				if(isset($this->product->product_access))
					$product_access = $this->product->product_access;
				echo $this->joomlaAcl->display('data[variant][product_access]', $product_access, true, true);
			?></dd>
<?php }

	if(hikashop_acl('product/edit/weight')) { ?>
			<dt class="hikashop_product_weight"><label for="data_variant__product_weight_unit"><?php echo JText::_('PRODUCT_WEIGHT'); ?></label></dt>
			<dd class="hikashop_product_weight">
				<input type="text" id="data_variant__product_weight_unit" name="data[variant][product_weight]" value="<?php echo $this->escape(@$this->product->product_weight); ?>"/>
				<?php echo $this->weight->display('data[variant][product_weight_unit]', @$this->product->product_weight_unit, '', 'style="width:70px;"'); ?>
			</dd>
<?php
	}

	if(hikashop_acl('product/edit/volume')) { ?>
			<dt class="hikashop_product_volume"><label for="data_variant__product_weight"><?php echo JText::_('PRODUCT_VOLUME'); ?></label></dt>
			<dd class="hikashop_product_volume">
				<div class="input-prepend">
					<span class="add-on"><?php
						echo str_replace('#MYTEXT#', '<i class="icon-14-length"></i>', hikashop_tooltip(JText::_('PRODUCT_LENGTH'), '', '', '#MYTEXT#', '', 0));
					?></span>
					<input size="10" style="width:50px" type="text" id="data_variant__product_weight" name="data[variant][product_length]" value="<?php echo $this->escape(@$this->product->product_length); ?>"/>
				</div>
				<div class="input-prepend">
					<span class="add-on"><?php
						echo str_replace('#MYTEXT#', '<i class="icon-14-width"></i>', hikashop_tooltip(JText::_('PRODUCT_WIDTH'), '', '', '#MYTEXT#', '', 0));
					?></span>
					<input size="10" style="width:50px" type="text" name="data[variant][product_width]" value="<?php echo $this->escape(@$this->product->product_width); ?>"/>
				</div>
				<div class="input-prepend">
					<span class="add-on"><?php
						echo str_replace('#MYTEXT#', '<i class="icon-14-height"></i>', hikashop_tooltip(JText::_('PRODUCT_HEIGHT'), '', '', '#MYTEXT#', '', 0));
					?></span>
					<input size="10" style="width:50px" type="text" name="data[variant][product_height]" value="<?php echo $this->escape(@$this->product->product_height); ?>"/>
				</div>
				<?php echo $this->volume->display('data[variant][product_dimension_unit]', @$this->product->product_dimension_unit, 'dimension', '', 'class="no-chzn" style="width:70px;"'); ?>
			</dd>
<?php
	}
?>
		</dl>
	</div></div>

	<div class="hkc-lg-clear"></div>

<?php

	JPluginHelper::importPlugin('hikashop');
	$app = JFactory::getApplication();
	$html = array();
	$app->triggerEvent('onProductFormDisplay', array( &$this->product, &$html ));

	if(!empty($this->fields) && hikashop_acl('product/edit/customfields') || !empty($html)) {
?>
	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_fields"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_fields_title"><?php
			echo JText::_('FIELDS');
		?></div>
<?php
		if(!empty($this->fields) && hikashop_acl('product/edit/customfields')) {
			$after = array();
			foreach($this->fields as $fieldName => $oneExtraField) {
				$onWhat = 'onchange';
				if($oneExtraField->field_type == 'radio')
					$onWhat = 'onclick';
				$txt = $this->fieldsClass->display($oneExtraField, @$this->product->$fieldName, 'data[variant]['.$fieldName.']', false, ' '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\'product\',0,\''.$this->fieldsClass->prefix.'\');"');
				if($oneExtraField->field_type == 'hidden') {
					$after[] = $txt;
					continue;
				}
?>
		<dl id="<?php echo $this->fieldsClass->prefix; ?>product_<?php echo $fieldName; ?>" class="hika_options">
			<dt class="hikashop_product_<?php echo $fieldName; ?>"><label><?php echo $this->fieldsClass->getFieldName($oneExtraField); ?></label></dt>
			<dd class="hikashop_product_<?php echo $fieldName; ?>"><?php
				echo $txt;
			?></dd>
		</dl>
<?php		}
			if(count($after)) {
				echo implode("\r\n", $after);
			}
		}

		if(!empty($html)) {
			foreach($html as $k => $h) {
				if(is_string($h) && strtolower(substr(trim($h), 0, 4)) == '<tr>')
					continue;
				if(is_string($h)) {
					echo $h;
				} else {
					$fieldname = strtolower($h['name']);
					if(empty($h['label']))
						$h['label'] = $h['name'];
					$fieldname = preg_replace('([^-a-z0-9])', '_', $fieldname);
?>
		<dl id="hikashop_product_<?php echo $fieldname; ?>" class="hika_options">
			<dt class="hikashop_product_<?php echo $fieldname; ?>"><label><?php echo JText::_($h['label']); ?></label></dt>
			<dd class="hikashop_product_<?php echo $fieldname; ?>"><?php echo $h['content']; ?></dd>
		</dl>
<?php
				}

				unset($html[$k]);
			}
		}

		if(!empty($html)) {
?>
		<table class="admintable table" width="100%">
<?php
			foreach($html as $h) {
				echo $h;
			}
?>
		</table>
<?php
		}
?>
	</div></div>
<?php
	}
?>

	<div class="hkc-xl-clear"></div>

<?php
	$html = array();
	JPluginHelper::importPlugin('hikashop');
	$app = JFactory::getApplication();
	$app->triggerEvent('onProductBlocksDisplay', array(&$this->product, &$html));
	if(!empty($html)) {
		echo '<div style="clear:both"></div>';
		foreach($html as $h) {
			echo $h;
		}
	}

	$areas_order = $this->config->get('variant_areas_order');
	if(empty($areas_order))
		$areas_order = $this->config->get('product_areas_order');
	$areas_fields = $this->config->get('variant_areas_fields');
	if(empty($areas_fields))
		$areas_fields = $this->config->get('product_areas_fields');
?>

<div style="clear:both"></div>

</div>
<input type="hidden" name="data[variant][product_id]" value="<?php echo $this->product->product_id; ?>" />
<input type="hidden" name="variant_areas_order"  id="variant_areas_order" value="<?php echo $this->escape($areas_order); ?>"/>
<input type="hidden" name="variant_areas_fields"  id="variant_areas_fields" value="<?php echo $this->escape($areas_fields); ?>"/>
<div style="clear:both"></div>
<script type="text/javascript">
if(JoomlaCalendar && JoomlaCalendar.init){
	setTimeout(function(){
		var section = document.getElementById('hikashop_product_variant_edition');
		elements = section.querySelectorAll(".field-calendar");
		for(i = 0; i < elements.length; i++){
			JoomlaCalendar.init(elements[i]);
		}
	}, 500);
}
if(Joomla && Joomla.JoomlaTinyMCE && Joomla.JoomlaTinyMCE.setupEditors) {
	var section = document.getElementById('hikashop_product_variant_edition');
	Joomla.JoomlaTinyMCE.setupEditors(section);
}
<?php 
if($this->config->get('form_custom', 1)) {
?>
window.hikashop.ready( function() {
	var options = {
		mainArea: '#hikashop_product_variant_edition .hk-container-fluid',
		type: 'variant',
		skipEmpty: true,
		customize: <?php echo (int)$this->customize; ?>,
	};
	window.variantDragOptionsKey = window.formCustom.initDragAndDrop(options);
});
<?php } ?>
</script>
<?php
$doc = JFactory::getDocument();
foreach($doc->_custom as $custom) {
	$custom = preg_replace('#<script .*(type="text/javascript")? src=".*"></script>#iU', '', $custom);
	$custom = preg_replace('#<script .*type=[\'"]text/javascript[\'"]>#iU', '<script type="text/javascript">', $custom);
	if( strpos($custom,'<script type="text/javascript">') !== false ){
		$custom = str_replace(
			array('<script type="text/javascript">', '</script>'),
			array('<script type="text/javascript">setTimeout(function(){', '},20);</script>'),
			$custom);
	}
	echo $custom;
}
foreach($doc->_script as $script) {
	if(is_array($script)) {
		foreach($script as $s) {
			echo '<script type="text/javascript">'."\r\n".$s."\r\n".'</script>';
		}
	} else {
		echo '<script type="text/javascript">'."\r\n".$script."\r\n".'</script>';
	}
}


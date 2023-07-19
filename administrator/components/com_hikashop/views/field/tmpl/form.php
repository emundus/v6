<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('field'); ?>" method="post" name="adminForm" id="adminForm">

<?php
if (HIKASHOP_J40) {
	$style = 'width:125px;';
	$select_style = 'width:90px';
}
else {
	$style = 'width:auto;';
	$select_style = 'width:auto;';
}
?>

<div class="hikashop_backend_tile_edition">
	<div class="hk-container-fluid">

<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block hikashop_field_edit_general"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('MAIN_INFORMATION'); ?></div>
	<dl class="hika_options large">

		<dt><label for="field_name"><?php
			echo JText::_('FIELD_LABEL');
		?></label></dt>
		<dd class="input_large">
			<input type="text" name="data[field][field_realname]" id="field_name" class="inputbox" value="<?php echo $this->escape(@$this->field->field_realname); ?>" />
		</dd>

		<dt><label><?php
			echo JText::_('FIELD_TABLE');
		?></label></dt>
		<dd><?php
	if (hikashop_level(1) && empty($this->field->field_id)) {
		echo $this->tabletype->display('data[field][field_table]', $this->field->field_table, true, 'onchange="setVisible(this.value);"');
	} else {
		echo $this->field->field_table .
			'<input type="hidden" name="data[field][field_table]" value="'.$this->escape($this->field->field_table).'" />';
	}
		?></dd>

		<dt><label><?php
			echo JText::_('FIELD_COLUMN');
		?></label></dt>
		<dd>
<?php
	if(empty($this->field->field_id)) {
?>
			<input type="text" name="data[field][field_namekey]" id="namekey" class="inputbox" size="40" value="" />
<?php
	} else {
		echo $this->field->field_namekey;
	}
?>
		</dd>

		<dt><label><?php
			echo JText::_('FIELD_TYPE');
		?></label></dt>
		<dd><?php
	if(!empty($this->field->field_type) && $this->field->field_type == 'customtext') {
		$this->fieldtype->addJS();
		echo $this->field->field_type .
			'<input type="hidden" id="fieldtype" name="data[field][field_type]" value="'.$this->escape($this->field->field_type).'" />';
	} else {
		echo $this->fieldtype->display('data[field][field_type]', @$this->field->field_type, @$this->field->field_table);
	}
		?></dd>

		<dt><label><?php
			echo JText::_('HIKA_PUBLISHED');
		?></label></dt>
		<dd><?php
			echo JHTML::_('hikaselect.booleanlist', 'data[field][field_published]', '', @$this->field->field_published);
		?></dd>
<?php
	if(!empty($this->translations) && !empty($this->field->field_id)) {
		?>
					<dt class="hikashop_field_translations"><label><?php echo JText::_('HIKA_TRANSLATIONS'); ?></label></dt>
					<dd class="hikashop_field_translations"><?php
				foreach($this->translations as $language_id => $translation){
					$lngName = $this->translationHelper->getFlag($language_id);
					echo '<div class="hikashop_multilang_button hikashop_language_'.$language_id.'"">' .
						$this->popup->display(
							$lngName, strip_tags($lngName),
							hikashop_completeLink('field&task=edit_translation&field_id=' . @$this->field->field_id.'&language_id='.$language_id, true),
							'hikashop_field_translation_'.$language_id,
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

<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block hikashop_field_edit_attributes"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('RESTRICTIONS'); ?></div>
	<dl class="hika_options large">

<?php
	$displayBlock = in_array($this->field->field_table, array('product', 'item', 'category', 'contact', 'order')) ? '' : ' style="display:none"';
?>
		<dt<?php echo $displayBlock; ?>><label><?php
			echo JText::_('HIKA_CATEGORIES');
		?></label></dt>
		<dd<?php echo $displayBlock; ?>><?php
	if(@$this->field->field_categories == 'all') $this->field->field_categories = '';
	echo  $this->nameboxType->display(
		'category',
		explode(',',trim((string)@$this->field->field_categories,',')),
		hikashopNameboxType::NAMEBOX_MULTIPLE,
		'category',
		array(
			'delete' => true,
			'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
		)
	);
		?></dd>

		<dt<?php echo $displayBlock; ?>><label><?php
			echo JText::_('INCLUDING_SUB_CATEGORIES');
		?></label></dt>
		<dd<?php echo $displayBlock; ?>><?php
			echo JHTML::_('hikaselect.booleanlist', 'data[field][field_with_sub_categories]', '', @$this->field->field_with_sub_categories);
		?></dd>

<?php
	$displayBlock = in_array($this->field->field_table, array('product', 'item', 'contact', 'order')) ? '' : ' style="display:none"';
?>
		<dt<?php echo $displayBlock; ?>><label><?php
			echo JText::_('PRODUCTS');
		?></label></dt>
		<dd<?php echo $displayBlock; ?>><?php
	echo  $this->nameboxType->display(
		'data[field][field_products]',
		explode(',',trim((string)@$this->field->field_products,',')),
		hikashopNameboxType::NAMEBOX_MULTIPLE,
		'product',
		array(
			'delete' => true,
			'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
		)
	);
		?></dd>
<?php
	$displayBlock = in_array($this->field->field_table, array('address')) ? '' : ' style="display:none"';
?>
		<dt<?php echo $displayBlock; ?>><label><?php
			echo JText::_('HIKASHOP_ADDRESS_TYPE');
		?></label></dt>
		<dd<?php echo $displayBlock; ?>><?php
	$values = array(
		JHTML::_('select.option', '', JText::_('WIZARD_BOTH')),
		JHTML::_('select.option', 'billing', JText::_('HIKASHOP_BILLING_ADDRESS')),
		JHTML::_('select.option', 'shipping', JText::_('HIKASHOP_SHIPPING_ADDRESS')),
	);
	echo JHTML::_('select.genericlist',   $values, 'data[field][field_address_type]', 'class="custom-select" size="1"', 'value', 'text', @$this->field->field_address_type );
		?></dd>

<?php
	$displayBlock = in_array($this->field->field_table, array('order')) ? '' : ' style="display:none"';
?>
		<dt<?php echo $displayBlock; ?>><label><?php
			echo JText::_('SHIPPING_METHODS');
		?></label></dt>
		<dd<?php echo $displayBlock; ?>><?php
	if(@$this->field->field_shipping_id == 'all') $this->field->field_shipping_id = '';
	echo  $this->nameboxType->display(
		'data[field][field_shipping_id]',
		explode(',',trim((string)@$this->field->field_shipping_id,',')),
		hikashopNameboxType::NAMEBOX_MULTIPLE,
		'shipping_methods',
		array(
			'delete' => true,
			'main_only' => true,
			'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
		)
	);
		?></dd>
		<dt<?php echo $displayBlock; ?>><label><?php
			echo JText::_('PAYMENT_METHODS');
		?></label></dt>
		<dd<?php echo $displayBlock; ?>><?php
	if(@$this->field->field_payment_id == 'all') $this->field->field_payment_id = '';
	echo  $this->nameboxType->display(
		'data[field][field_payment_id]',
		explode(',',trim((string)@$this->field->field_payment_id,',')),
		hikashopNameboxType::NAMEBOX_MULTIPLE,
		'payment_methods',
		array(
			'delete' => true,
			'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
		)
	);
		?></dd>

		<dt data-hk-display="limit_to"><label><?php
			echo JText::_('DISPLAY_LIMITED_TO');
		?></label></dt>
		<dd data-hk-display="limit_to">
<?php
	if(hikashop_level(2)) {
		if(empty($this->field->field_table)) {
			echo JText::_('SAVE_THE_FIELD_FIRST_BEFORE');
		} else {
			echo $this->limitParent->display('field_options[limit_to_parent]', @$this->field->field_options['limit_to_parent'], $this->field->field_table, @$this->field->field_options['parent_value'], $this->field);
		}
	}else{
		echo hikashop_getUpgradeLink('business');
	}
?>
			<span id="parent_value"></span>
		</dd>

		<dt><label><?php
			echo JText::_('ACCESS_LEVEL');
		?></label></dt>
		<dd><?php
	if(hikashop_level(2)) {
		$acltype = hikashop_get('type.acl');
		echo $acltype->display('field_access', @$this->field->field_access, 'field');
	} else {
		echo hikashop_getUpgradeLink('business');
	}
		?></dd>

<?php if(hikashop_level(2) && $this->field->field_table == 'entry') { ?>
		<dt data-hk-display="product_link"><label><?php
			echo JText::_('CORRESPOND_TO_PRODUCT');
		?></label></dt>
		<dd data-hk-display="product_link"><?php

	echo $this->nameboxType->display(
		'field_options[product_id]',
		@$this->field->field_options['product_id'],
		hikashopNameboxType::NAMEBOX_SINGLE,
		'product',
		array(
			'delete' => true,
			'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
		)
	);
	$this->fieldsClass->suffix = '_corresponding';
	echo '<br />' . JText::_('FOR_THE_VALUE') . ' ' . $this->fieldsClass->display($this->field, @$this->field->field_options['product_value'], 'field_options[product_value]', false, '', true);

		?></dd>
<?php } ?>
	</dl>
</div></div>
<div class="hkc-lg-clear"></div>
<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block hikashop_field_edit_attributes" data-hk-displays="required,regex,attribute,placeholder,inline,target_blank,default,add"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('MAIN_ATTRIBUTES'); ?></div>
	<dl class="hika_options large">

		<dt data-hk-display="required"><label><?php
			echo JText::_('REQUIRED');
		?></label></dt>
		<dd data-hk-display="required"><?php
			echo JHTML::_('hikaselect.booleanlist', "data[field][field_required]" , '',@$this->field->field_required);
		?></dd>

		<dt data-hk-display="required"><label for="field_errormessage"><?php
			echo JText::_('FIELD_ERROR');
		?></label></dt>
		<dd data-hk-display="required" class="input_large">
			<input type="text" id="field_errormessage" size="80" name="field_options[errormessage]" value="<?php echo $this->escape(@$this->field->field_options['errormessage']); ?>"/>
		</dd>

		<dt data-hk-display="regex"><label for="field_regex"><?php
			echo JText::_('FIELD_REGEX');
		?></label></dt>
		<dd data-hk-display="regex" class="input_large">
			<input type="text" id="field_regex" size="80" name="field_options[regex]" value="<?php echo $this->escape(@$this->field->field_options['regex']); ?>"/>
		</dd>

		<dt data-hk-display="attribute"><label for="field_attribute"><?php
			echo JText::_('FIELD_ATTRIBUTE');
		?></label></dt>
		<dd data-hk-display="attribute" class="input_large">
			<input type="text" id="field_attribute" size="80" name="field_options[attribute]" value="<?php echo $this->escape(@$this->field->field_options['attribute']); ?>"/>
		</dd>

		<dt data-hk-display="placeholder"><label for="field_placeholder"><?php
			echo JText::_('FIELD_PLACEHOLDER');
		?></label></dt>
		<dd data-hk-display="placeholder" class="input_large">
			<input type="text" id="field_placeholder" size="80" name="field_options[placeholder]" value="<?php echo $this->escape(@$this->field->field_options['placeholder']); ?>"/>
		</dd>

		<dt data-hk-display="inline"><label><?php
			echo JText::_('HIKA_INLINE');
		?></label></dt>
		<dd data-hk-display="inline"><?php
			echo JHTML::_('hikaselect.booleanlist', 'field_options[inline]', '', @$this->field->field_options['inline']);
		?></dd>

		<dt data-hk-display="target_blank"><label for="field_target_blank"><?php
			echo JText::_('FIELD_TARGET_BLANK');
		?></label></dt>
		<dd data-hk-display="target_blank"><?php
			if(!isset($this->field->field_options['target_blank']))
				$this->field->field_options['target_blank'] = 1;
			echo JHTML::_('hikaselect.booleanlist', 'field_options[target_blank]' , '', (int)$this->field->field_options['target_blank']);
		?></dd>

		<dt data-hk-display="default"><label><?php
			echo JText::_('FIELD_DEFAULT');
		?></label></dt>
		<dd data-hk-display="default"><?php
			$this->fieldsClass->suffix = '';
			echo $this->fieldsClass->display($this->field, @$this->field->field_default, 'data[field][field_default]', false, '', true, $this->allFields);
		?></dd>


		<dt data-hk-display="add"><label><?php
			echo JText::_('FIELD_ALLOW_ADD');
		?></label></dt>
		<dd data-hk-display="add"><?php
			echo JHTML::_('hikaselect.booleanlist', 'field_options[allow_add]' , '', (int)@$this->field->field_options['allow_add']);
		?></dd>
	</dl>
</div></div>
<?php
	$filters = array('cols','filtering','maxlength','rows','zone','pleaseselect','size','format','customtext','allow','readonly','allowed_extensions','upload_dir','max_filesize', 'max_dimensions', 'multiple', 'thumbnail', 'delete_files');
	if(!empty($this->field->field_table) && in_array($this->field->field_table, array('product', 'category')))
		$filters[] = 'translatable';
	if(!empty($this->fieldtype->externalOptions)) {
		foreach($this->fieldtype->externalOptions as $key => $extraOption) {
			if((is_array($extraOption) && !empty($extraOption['own_block'])) || (is_object($extraOption) && !empty($extraOption->own_block)))
				continue;
			if(is_numeric($key)) {
				if(is_array($extraOption) && isset($extraOption['name']))
					$key = $extraOption['name'];
				else
					$key = @$extraOption->name;
			}
			if(empty($key) || is_numeric($key))
				continue;
			$filters[] = $key;
		}
	}
?>
<div class="hkc-xl-clear"></div>
<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block hikashop_field_edit_display" data-hk-displays="<?php echo implode(',', $filters); ?>"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('EXTRA_ATTRIBUTES'); ?></div>
	<dl class="hika_options large">

		<dt data-hk-display="cols"><label for="field_cols"><?php
			echo JText::_('FIELD_COLUMNS');
		?></label></dt>
		<dd data-hk-display="cols" class="input_large">
			<input type="text" name="field_options[cols]" id="field_cols" class="inputbox" value="<?php echo $this->escape(@$this->field->field_options['cols']); ?>"/>
		</dd>

		<dt data-hk-display="filtering"><label><?php
			echo JText::_('INPUT_FILTERING');
		?></label></dt>
		<dd data-hk-display="filtering"><?php
			if(!isset($this->field->field_options['filtering']))
				$this->field->field_options['filtering'] = 1;
			echo JHTML::_('hikaselect.booleanlist', 'field_options[filtering]', '', (int)$this->field->field_options['filtering']);
		?></dd>

		<dt data-hk-display="maxlength"><label for="field_maxlength"><?php
			echo JText::_('MAXLENGTH');
		?></label></dt>
		<dd data-hk-display="maxlength" class="input_large">
			<input type="text" size="10" name="field_options[maxlength]" id="field_maxlength" class="inputbox" value="<?php echo (int)@$this->field->field_options['maxlength']; ?>"/>
		</dd>

		<dt data-hk-display="rows"><label for="field_rows"><?php
			echo JText::_('FIELD_ROWS');
		?></label></dt>
		<dd data-hk-display="rows" class="input_large">
			<input type="text" size="10" name="field_options[rows]" id="field_rows" class="inputbox" value="<?php echo $this->escape(@$this->field->field_options['rows']); ?>"/>
		</dd>

		<dt data-hk-display="zone"><label><?php
			echo JText::_('FIELD_ZONE');
		?></label></dt>
		<dd data-hk-display="zone"><?php
			echo $this->zoneType->display('field_options[zone_type]', @$this->field->field_options['zone_type'], true);
		?></dd>

		<dt data-hk-display="pleaseselect"><label><?php
			echo JText::_('ADD_SELECT_VALUE');
		?></label></dt>
		<dd data-hk-display="pleaseselect"><?php
			echo JHTML::_('hikaselect.booleanlist', 'field_options[pleaseselect]', '', @$this->field->field_options['pleaseselect']);
		?></dd>

		<dt data-hk-display="size"><label for="field_size"><?php
			echo JText::_('FIELD_SIZE');
		?></label></dt>
		<dd data-hk-display="size" class="input_large">
			<input type="text" id="field_size" name="field_options[size]" value="<?php echo $this->escape(@$this->field->field_options['size']); ?>"/>
		</dd>


		<dt data-hk-display="display_format"><label for="display_format"><?php
			echo JText::_('FORMAT');
		?></label></dt>
		<dd data-hk-display="display_format" class="input_large">
			<input type="text" id="display_format" placeholder="{value}" name="field_options[display_format]" value="<?php echo $this->escape(@$this->field->field_options['display_format']); ?>"/>
		</dd>

		<dt data-hk-display="format"><label for="field_format"><?php
			echo JText::_('FORMAT');
		?></label></dt>
		<dd data-hk-display="format" class="input_large">
<?php
	if(!isset($this->field->field_options['format']))
		$this->field->field_options['format'] = '%Y-%m-%d';
?>
			<input type="text" id="field_format" name="field_options[format]" value="<?php echo $this->escape($this->field->field_options['format']); ?>"/>
		</dd>

		<dt data-hk-display="customtext"><label for="field_customtext"><?php
			echo JText::_('CUSTOM_TEXT');
		?></label></dt>
		<dd data-hk-display="customtext" class="input_large">
			<textarea cols="50" rows="6" id="field_customtext" name="fieldcustomtext"><?php
				echo @$this->field->field_options['customtext'];
			?></textarea>
		</dd>

		<dt data-hk-display="allow"><label><?php
			echo JText::_('ALLOW');
		?></label></dt>
		<dd data-hk-display="allow"><?php
			echo $this->allowType->display('field_options[allow]', @$this->field->field_options['allow']);
		?></dd>

		<dt data-hk-display="allowed_extensions"><label><?php
			echo JText::_('ALLOWED_FILES');
		?></label></dt>
		<dd data-hk-display="allowed_extensions">
			<input type="text" name="field_options[allowed_extensions]" value="<?php echo $this->escape(@$this->field->field_options['allowed_extensions']); ?>"/>
		</dd>
		<dt data-hk-display="upload_dir"><label><?php
			echo JText::_('UPLOAD_FOLDER');
		?></label></dt>
		<dd data-hk-display="upload_dir">
			<input type="text" name="field_options[upload_dir]" value="<?php echo $this->escape(@$this->field->field_options['upload_dir']); ?>" placeholder="<?php echo $this->config->get('uploadsecurefolder'); ?>"/>
		</dd>

		<dt data-hk-display="max_filesize"><label><?php
			echo JText::_('MAX_FILESIZE');
		?></label></dt>
		<dd data-hk-display="max_filesize">
			<?php
			$unit = 'm';
			$size = '';
			if(!empty($this->field->field_options['max_filesize'])) {
				$units   = array('b', 'k', 'm', 'g');
				$factor = floor((strlen($this->field->field_options['max_filesize']) - 1) / 3);
				$size = rtrim(rtrim(sprintf("%.2f", $this->field->field_options['max_filesize'] / pow(1024, $factor)), '0'), '.');
				$unit = $units[$factor];
			}
			?>
			<input type="text" id="__size__" name="__size__" value="<?php echo $size; ?>" onchange="window.recalculateSize();" style="width:80px;"/>
			<?php
				$values = array(
					JHTML::_('select.option', 'b', 'B'),
					JHTML::_('select.option', 'k', 'KB'),
					JHTML::_('select.option', 'm', 'MB'),
					JHTML::_('select.option', 'g', 'GB'),
				);
				echo JHTML::_('select.genericlist',   $values, '__unit__', 'class="custom-select" size="1"  onchange="window.recalculateSize();" style="width: 100px"', 'value', 'text', $unit );
			?>
			<input type="hidden" id="__main__" name="field_options[max_filesize]" value="<?php echo (int)@$this->field->field_options['max_filesize']; ?>"/>
			<script>
window.recalculateSize = function() {
	var size = parseFloat(document.getElementById('__size__').value);
	if(isNaN(size))
		return;
	var unit = document.getElementById('__unit__').value;
	var main = document.getElementById('__main__');
	var powers = {'b': 0, 'k': 1, 'm': 2, 'g': 3};
    main.value = size * Math.pow(1024, powers[unit]);
}
			</script>
		</dd>

		<dt data-hk-display="thumbnail"><label><?php
			echo JText::_('THUMBNAILS_DIMENSIONS');
		?></label></dt>
		<dd data-hk-display="thumbnail">
			<input type="text" name="field_options[thumbnail_x]" value="<?php echo (int)@$this->field->field_options['thumbnail_x']; ?>" placeholder="100"/> px
			<i class="fas fa-times fa-2x"></i>
			<input type="text" name="field_options[thumbnail_y]" value="<?php echo (int)@$this->field->field_options['thumbnail_y']; ?>" placeholder="100"/> px
		</dd>
		<dt data-hk-display="max_dimensions"><label><?php
			echo JText::_('MAX_DIMENSIONS');
		?></label></dt>
		<dd data-hk-display="max_dimensions">
			<input type="text" name="field_options[max_width]" value="<?php echo (int)@$this->field->field_options['max_width']; ?>" placeholder="<?php echo JText::_('NO_MAXIMUM_WIDTH'); ?>"/> px
			<i class="fas fa-times fa-2x"></i>
			<input type="text" name="field_options[max_height]" value="<?php echo (int)@$this->field->field_options['max_height']; ?>" placeholder="<?php echo JText::_('NO_MAXIMUM_HEIGHT'); ?>"/> px
		</dd>

		<dt data-hk-display="delete_files"><label><?php
			echo JText::_('DELETE_FILES');
		?></label></dt>
		<dd data-hk-display="delete_files">
			<?php echo JHTML::_('hikaselect.booleanlist', 'field_options[delete_files]', '', @$this->field->field_options['delete_files']); ?>
		</dd>

		<dt data-hk-display="multiple"><label><?php
			echo JText::_('MULTIPLE_FILES');
		?></label></dt>
		<dd data-hk-display="multiple">
			<?php echo JHTML::_('hikaselect.booleanlist', 'field_options[multiple]', '', @$this->field->field_options['multiple']); ?>
		</dd>

		<dt data-hk-display="readonly"><label><?php
			echo JText::_('READONLY');
		?></label></dt>
		<dd data-hk-display="readonly"><?php
			echo JHTML::_('hikaselect.booleanlist', 'field_options[readonly]', '', @$this->field->field_options['readonly']);
		?></dd>

<?php if(!empty($this->field->field_table) && in_array($this->field->field_table, array('product', 'category'))) { ?>
		<dt data-hk-display="translatable"><label><?php
			echo JText::_('HIKA_TRANSLATABLE');
		?></label></dt>
		<dd data-hk-display="translatable"><?php
			echo JHTML::_('hikaselect.booleanlist', 'field_options[translatable]', '', @$this->field->field_options['translatable']);
		?></dd>
<?php } ?>

<?php
	if(!empty($this->fieldtype->externalOptions)) {
		foreach($this->fieldtype->externalOptions as $key => $extraOption) {
			if((is_array($extraOption) && !empty($extraOption['own_block'])) || (is_object($extraOption) && !empty($extraOption->own_block)))
				continue;

			if(is_numeric($key)) {
				if(is_array($extraOption) && isset($extraOption['name']))
					$key = $extraOption['name'];
				else
					$key = @$extraOption->name;
			}
			if(empty($key) || is_numeric($key))
				continue;

?>
		<dt data-hk-display="<?php echo $key; ?>"><label><?php
			if(is_array($extraOption) && isset($extraOption['text']))
				echo $extraOption['text'];
			else
				echo @$extraOption->text;
		?></label></dt>
		<dd data-hk-display="<?php echo $key; ?>"><?php
			if((is_array($extraOption) && isset($extraOption['content'])) || isset($extraOption->content)) {
				if(is_array($extraOption))
					echo $extraOption['content'];
				else
					echo $extraOption->content;
			}
			if((is_array($extraOption) && isset($extraOption['obj'])) || isset($extraOption->obj)) {
				$o = is_array($extraOption) ? $extraOption['obj'] : $extraOption->obj;
				if(is_string($o))
					$o = new $o();

				echo $o->show( @$this->field->field_options[$key] );
			}
		?></dd>
<?php
		}
	}
?>

	</dl>
</div></div>


<div data-hk-display="multivalues" class="hkc-xl-4 hkc-lg-6 hikashop_tile_block hikashop_field_edit_advanced"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('FIELD_DATA'); ?></div>
<?php
$type = 'values';
if(!empty($this->field->field_options['mysql_query'])) {
	$type = 'query';
}
?>
	<input type="hidden" id="data_type" name="data_type" value="<?php echo $type; ?>" />
	<ul class="hika_tabs" rel="tabs:hikashop_field_data_tab_">
		<li <?php echo ($type == 'values' ? 'class="active"' : ''); ?>><a href="#values" rel="tab:values" onclick="document.getElementById('data_type').value='values'; return window.hikashop.switchTab(this);"><?php echo JText::_('FIELD_VALUES'); ?></a></li>
		<li <?php echo ($type == 'query' ? 'class="active"' : ''); ?>><a href="#query" rel="tab:query" onclick="document.getElementById('data_type').value='query'; return window.hikashop.switchTab(this);"><?php echo JText::_('MYSQL_QUERY'); ?></a></li>
	</ul>
	<div id="hikashop_field_data_tab_values" <?php echo ($type != 'values' ? 'style="display:none;"' : ''); ?>>
		<table id="hikashop_field_values_table" style="width:100%;" class="table table-striped table-hover">
			<thead>
				<tr>
					<th></th>
					<th><?php echo JText::_('FIELD_VALUE')?></th>
					<th><?php echo JText::_('FIELD_TITLE'); ?></th>
					<th><?php echo JText::_('FIELD_DISABLED'); ?></th>
					<th></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td></td>
					<td colspan="3">
						<a class="btn btn-success" onclick="addLine();return false;" href='#' title="<?php echo $this->escape(JText::_('FIELD_ADDVALUE')); ?>"><?php echo JText::_('FIELD_ADDVALUE'); ?></a>
					</td>
					<td></td>
				</tr>
			</tfoot>
			<tbody id="tablevalues">
<?php
	$k = 0;
	if($type != 'values' && isset($this->field->field_value_old)) {
		$this->field->field_value = $this->field->field_value_old;
	}
	if(!empty($this->field->field_value) && is_array($this->field->field_value) && $this->field->field_type != 'zone') {
		$i = 0;
		foreach($this->field->field_value as $title => $value){
			$no_selected = 'selected="selected"';
			$yes_selected = '';
			if((int)$value->disabled) {
				$no_selected = '';
				$yes_selected = 'selected="selected"';
			}
?>
				<tr class="row<?php echo $k; ?>">
					<td class="column_move"><img src="<?php echo HIKASHOP_IMAGES; ?>move.png" alt=""/></td>
					<td><input type="text" name="field_values[title][]" value="<?php echo $this->escape($title); ?>" style="<?php echo $style;?>"/></td>
					<td><input type="text" name="field_values[value][]" value="<?php echo $this->escape($value->value); ?>" style="<?php echo $style;?>"/></td>
					<td>
						<select name="field_values[disabled][]" class="custom-select no-chzn inputbox" style="<?php echo $select_style;?>">
							<option <?php echo $no_selected; ?> value="0"><?php echo JText::_('HIKASHOP_NO'); ?></option>
							<option <?php echo $yes_selected; ?> value="1"><?php echo JText::_('HIKASHOP_YES'); ?></option>
						</select>
					</td>
					<td><a href="#" onclick="window.hikashop.deleteRow(this); return false;"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="<?php echo JText::_('DELETE'); ?>)"/></a></td>
				</tr>
<?php
			$i++;
			$k = 1 - $k;
		}

	}
?>
				<tr class="row<?php echo $k; ?>">
					<td class="column_move"><img src="<?php echo HIKASHOP_IMAGES; ?>move.png"/></td>
					<td><input type="text" name="field_values[title][]" value="" style="<?php echo $style;?>"/></td>
					<td><input type="text" name="field_values[value][]" value="" style="<?php echo $style;?>"/></td>
					<td>
						<select name="field_values[disabled][]" class="custom-select no-chzn inputbox" style="<?php echo $select_style;?>">
							<option selected="selected" value="0"><?php echo JText::_('HIKASHOP_NO'); ?></option>
							<option value="1"><?php echo JText::_('HIKASHOP_YES'); ?></option>
						</select>
					</td>
					<td><a href="#" onclick="window.hikashop.deleteRow(this); return false;"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="<?php echo JText::_('DELETE'); ?>)"/></a></td>
				</tr>

				<tr id="hikashop_field_values_table_template"  class="row<?php echo (1 - $k); ?>" style="display:none;">
					<td class="column_move"><img src="<?php echo HIKASHOP_IMAGES; ?>move.png"/></td>
					<td><input type="text" name="{TITLE}" value="" style="<?php echo $style;?>"/></td>
					<td><input type="text" name="{VALUE}" value="" style="<?php echo $style;?>"/></td>
					<td>
						<select name="{DISABLED}" class="custom-select no-chzn" style="<?php echo $select_style;?>">
							<option selected="selected" value="0"><?php echo JText::_('HIKASHOP_NO'); ?></option>
							<option value="1"><?php echo JText::_('HIKASHOP_YES'); ?></option>
						</select>
					</td>
					<td><a href="#" onclick="window.hikashop.deleteRow(this); return false;"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="<?php echo JText::_('DELETE'); ?>)"/></a></td>
				</tr>
			</tbody>
		</table>
		<script type="text/javascript">
		hkjQuery("#hikashop_field_values_table tbody").sortable({
			axis: "y", cursor: "move", opacity: 0.8,
			helper: function(e, ui) {
				ui.children().each(function() {
					hkjQuery(this).width(hkjQuery(this).width());
				});
				return ui;
			},
			stop: function(event, ui) {
				window.hikashop.cleanTableRows('hikashop_field_values_table');
			}
		});
		window.hikashop.ready(function(){ window.hikashop.noChzn(); });
		</script>
	</div>
	<div id="hikashop_field_data_tab_query" <?php echo ($type != 'query' ? 'style="display:none;"' : ''); ?>>
		<textarea style="width:97%;" rows="12" name="field_options[mysql_query]" id="hikashop_field_myqsl_query_textarea"><?php echo $this->escape(@$this->field->field_options['mysql_query']); ?></textarea>
		<?php echo hikashop_display(JText::_('MYSQL_QUERY_DATA_TAGS'),'info'); ?>
	</div>
</div></div>

<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block hikashop_field_edit_display"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('DISPLAY'); ?></div>
	<dl class="hika_options large">

		<dt><label<?php echo $this->docFieldTip('frontcomp');?>><?php
			echo JText::_('DISPLAY_FRONTCOMP');
		?></label></dt>
		<dd><?php
			echo JHTML::_('hikaselect.booleanlist', 'data[field][field_frontcomp]', '', @$this->field->field_frontcomp);
		?></dd>

		<dt><label<?php echo $this->docFieldTip('back_form');?>><?php
			echo JText::_('DISPLAY_BACKEND_FORM');
		?></label></dt>
		<dd><?php
			echo JHTML::_('hikaselect.booleanlist', 'data[field][field_backend]', '', @$this->field->field_backend);
		?></dd>

<?php if(!in_array($this->field->field_table, array('address'))) { ?>
		<dt><label<?php echo $this->docFieldTip('back_list');?>><?php
			echo JText::_('DISPLAY_BACKEND_LISTING');
		?></label></dt>
		<dd><?php
			echo JHTML::_('hikaselect.booleanlist', 'data[field][field_backend_listing]', '', @$this->field->field_backend_listing);
		?></dd>
<?php } else { ?>
		<input type="hidden" name="data[field][field_backend_listing]" value="<?php echo (int)@$this->field->field_backend_listing; ?>" />
<?php } ?>

<?php
	$displayOptionGroups = array();

	if(!empty($this->displayOptions)) {
		foreach($this->displayOptions as $displayOption) {
			$displayOptionName = '';
			$displayOptionTitle = '';
			$group = null;

			if(is_string($displayOption)) {
				$displayOptionName = $displayOption;
			} else if(!empty($displayOption->name)) {
				$displayOptionName = $displayOption->name;
				$displayOptionTitle = @$displayOption->title;
				$group = @$displayOption->group;
			} else if(!empty($displayOption['name'])) {
				$displayOptionName = $displayOption['name'];
				$displayOptionTitle = @$displayOption['title'];
				$group = @$displayOption['group'];
			}

			if(empty($displayOptionName))
				continue;

			if(!empty($group)) {
				$displayOptionGroups[$group] = $group;
				continue;
			}

			if(empty($displayOptionTitle))
				$displayOptionTitle = JText::_($displayOptionName);

			if(!isset($this->field->field_display->$displayOptionName)){
				if(!is_object($this->field->field_display))
					$this->field->field_display = new stdClass();
				$this->field->field_display->$displayOptionName = 0;
			}
?>
		<dt><label><?php
			echo $displayOptionTitle;
		?></label></dt>
		<dd><?php
			echo JHTML::_('hikaselect.booleanlist', 'field_display['.$displayOptionName.']' , '', $this->field->field_display->$displayOptionName);
		?></dd>
<?php
		}
	}
?>

	</dl>
</div></div>

<?php
	if(!empty($displayOptionGroups)) {
		foreach($displayOptionGroups as $optionsGroup) {
			$groupName = JText::_('DISPLAY') . ' : ' . $optionsGroup;

			$key = 'FIELD_DISPLAY_' . strtoupper($optionsGroup);
			if(JText::_($key) != $key)
				$groupName = $key;
?>
<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block hikashop_field_edit_display"><div>
	<div class="hikashop_tile_title"><?php echo $groupName; ?></div>
	<dl class="hika_options large">
<?php
			foreach($this->displayOptions as $displayOption) {
				$displayOptionName = '';
				$displayOptionTitle = '';
				$group = null;

				if(is_string($displayOption)) {
					$displayOptionName = $displayOption;
				} else if(!empty($displayOption->name)) {
					$displayOptionName = $displayOption->name;
					$displayOptionTitle = @$displayOption->title;
					$group = @$displayOption->group;
				} else if(!empty($displayOption['name'])) {
					$displayOptionName = $displayOption['name'];
					$displayOptionTitle = @$displayOption['title'];
					$group = @$displayOption['group'];
				}

				if(empty($displayOptionName) || empty($group) || $group != $optionsGroup)
					continue;

				if(empty($displayOptionTitle))
					$displayOptionTitle = JText::_($displayOptionName);
?>
		<dt><label><?php
			echo $displayOptionTitle;
		?></label></dt>
		<dd><?php
			echo JHTML::_('hikaselect.booleanlist', 'field_display['.$displayOptionName.']' , '', @$this->field->field_display->$displayOptionName);
		?></dd>
<?php
			}
?>
	</dl>
</div></div>
<?php
		}
	}
?>

<?php if(!empty($this->field->field_id)) { ?>
<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block hikashop_field_preview"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('PREVIEW'); ?></div>
	<dl class="hika_options">
		<dt>
			<label><?php $this->fieldsClass->suffix = '_preview'; echo $this->fieldsClass->getFieldName($this->field); ?></label>
		</dt>
		<dd><?php
			echo $this->fieldsClass->display($this->field, $this->field->field_default, 'data['.$this->field->field_table.']['.$this->field->field_namekey.']', false, '', true, $this->allFields);
		?></dd>
	</dl>
<?php
	if(hikashop_level(2) && !empty($this->field->field_id) && in_array($this->field->field_type, array('radio', 'singledropdown', 'zone'))) {
		$this->fieldsClass->suffix = '';
		$this->fieldsClass->chart($this->field->field_table, $this->field);
	}
?>
</div></div>
<?php } ?>

<?php
	if(!empty($this->fieldtype->externalOptions)) {
		foreach($this->fieldtype->externalOptions as $key => $extraOption) {
			if((is_array($extraOption) && empty($extraOption['own_block'])) || (is_object($extraOption) && empty($extraOption->own_block)))
				continue;

			if(is_numeric($key)) {
				if(is_array($extraOption) && isset($extraOption['name']))
					$key = $extraOption['name'];
				else
					$key = @$extraOption->name;
			}
			if(empty($key) || is_numeric($key))
				continue;

?>
<div data-hk-display="<?php echo $key; ?>" class="hkc-xl-4 hkc-lg-6 hikashop_tile_block hikashop_field_edit_opt_<?php echo $key; ?>"><div>
	<div class="hikashop_tile_title"><?php
		if(is_array($extraOption) && isset($extraOption['text']))
			echo $extraOption['text'];
		else
			echo @$extraOption->text;
	?></div>
<?php
			if((is_array($extraOption) && isset($extraOption['content'])) || isset($extraOption->content)) {
				if(is_array($extraOption))
					echo $extraOption['content'];
				else
					echo $extraOption->content;
			}
			if((is_array($extraOption) && isset($extraOption['obj'])) || isset($extraOption->obj)) {
				$o = is_array($extraOption) ? $extraOption['obj'] : $extraOption->obj;
				if(is_string($o))
					$o = new $o();

				echo $o->show( @$this->field->field_options[$key] );
			}
?>
</div></div>
<?php
		}
	}
?>
	<input type="hidden" name="cid[]" value="<?php echo @$this->field->field_id; ?>" />
	<input type="hidden" name="option" value="com_hikashop" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="field" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<div class="clr" style="<?php if(hikashop_level(2) && !empty($this->field->field_id) && in_array($this->field->field_type,array('radio','singledropdown','zone'))){ echo 'height:400px;';} ?>width:100%"></div>
<script type="text/javascript">
window.hikashop.ready(function(){window.hikashop.dlTitle('adminForm');});
</script>

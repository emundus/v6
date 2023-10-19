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
if(!isset($this->element['layout_type']))
	$this->element['layout_type'] = 'inherit';
if (HIKASHOP_J40) {
?>
<style>
	legend,
	fieldset#fieldset-basic small.form-text.text-muted,
	section#attrib-products small.form-text.text-muted {
		display: none;
	}
	fieldset#fieldset-hk_options,
	section#attrib-hk_options {
		border: none;
		padding: 0px;
		margin: 0px;
	}
	section#attrib-basic,
	section#attrib-products {
		padding: 0px;
	}
	div#hikashop_main_content {
		border: 1px solid #b2bfcd;
		border-width: 1px 0px 0px 0px;
	}
	small.form-text.text-muted {
		display: none;
	}
	div#hikashop_main_content select.custom-select {
		width: 260px;
	}
</style>
<?php
}

?>
<div id="hikashop_main_content" class="hikashop_main_content hk-container-fluid item-module-interface hika_j<?php echo (int)HIKASHOP_JVERSION; ?>">
	<!-- module edition -->
	<div id="hikashop_module_backend_page_edition">
		<!-- Top part (Layout selection) -->
		<?php
		if($this->type == 'category'){
			$class = 'hkc-xl-4';
		}else{
			$class = 'hkc-xl-3';
		}
		$this->layoutType->load();
		?>
		<div class="hk-row-fluid hikashop_edit_display_type">
		<?php
		foreach($this->layoutType->values as $value){
			$dataDisplay = '';
			if($value->value == 'table')
				$dataDisplay = 'data-display-type="product"';
			if($value->value == 'inherit'){
				if($this->type == 'category' && $this->default_params['layout_type'] == 'table')
					$value->text = $value->text.' (div)';
				else
					$value->text = $value->text.' ('.$this->default_params['layout_type'].')';
			}
			$src = strtolower($value->value);
		?>
			<div class="<?php echo $class; ?> hikashop_module_block_content_type hikashop_module_edit_display_type_<?php echo $value->value; ?>" <?php echo $dataDisplay; ?> onclick="window.optionMgr.tabChange(this);" data-type="product_layout_choice" data-layout="<?php echo 'product_'.$value->value; ?>">
				<img class="hikashop_menu_block_img_unselected" src="<?php echo HIKASHOP_IMAGES; ?>icons/icon-24-<?php echo $src; ?>.png">
				<img class="hikashop_menu_block_img_selected" src="<?php echo HIKASHOP_IMAGES; ?>icons/icon-24-<?php echo $src; ?>-selected.png">
				<?php echo $value->text; ?>
			</div>
		<?php
		}
		?>
			<input type="hidden" id="data_module__product_layout_type" name="<?php echo $this->name; ?>[layout_type]" value="<?php echo $this->element['layout_type']; ?>">
			<input type="hidden" id="data_module__product_layout_type_default" name="" value="<?php echo $this->default_params['layout_type']; ?>">
		</div>

		<!-- Middle part (Display options) -->
		<div class="hikashop_menu_block hikashop_module_edit_display">
<!--			<div class="hkc-xl-4 hikashop_module_block hikashop_module_edit_display_preview">
			<?php
				$this->setLayout('options_display_preview');
				echo $this->loadTemplate();
			?>
			</div>
-->
			<div class="hikashop_module_edit_display_settings">
			<?php
			foreach($this->layoutType->values as $value){
				if($value->value == 'inherit') continue;
				$this->setLayout('options_display_'.$value->value);
				echo $this->loadTemplate();
			}
			?>
			</div>
		</div>

		<!-- Bottom part (Generic options) -->
		<div class="hk-row-fluid hikashop_module_block hikashop_module_edit_general">
		<?php
		$this->setLayout('options_main');
		echo $this->loadTemplate();

		$this->setLayout('options_category');
		echo $this->loadTemplate();

		$this->setLayout('options_product');
		echo $this->loadTemplate();

		$this->setLayout('options_display_restriction');
			echo $this->loadTemplate();

		?>
		</div>

		<!-- Extra part (Carousel options & ...) -->
		<div class="hk-row-fluid hikashop_module_block hikashop_module_edit_extra" data-display-tab="div">
		<?php
			$this->setLayout('options_product_extra');
			echo $this->loadTemplate();
		?>
		</div>
<?php
if(!empty($this->extra_blocks['layouts'])) {
	foreach($this->extra_blocks['layouts'] as $key => $r) {
		if(is_string($r))
			echo $r;
		if(is_array($r)) {
			if(!isset($r['name']) && isset($r[0]))
				$r['name'] = $r[0];
			if(!isset($r['value']) && isset($r[1]))
				$r['value'] = $r[1];
?>
		<div class="hk-row-fluid hikashop_module_block hikashop_module_edit_<?php echo $key; ?>">
<div class="hkc-xl-4 hikashop_module_subblock hikashop_module_edit_product">
	<div class="hikashop_module_subblock_content">
		<div class="hikashop_module_subblock_title hikashop_module_edit_<?php echo $key; ?>_title"><?php echo JText::_(@$r['name']); ?></div>
<?php
			if(is_array($r['value'])) {
?>
		<dl class="hika_options">
<?php
				foreach($r['value'] as $k => $v) {
?>
			<dt class="hikashop_option_name"><?php echo JText::_($k); ?></dt>
			<dd class="hikashop_option_value"><?php echo $v; ?></dd>
<?php
				}
?>
		</dl>
<?php
			} else {
				echo $r['value'];
			}
?>
	</div>
</div>
		</div>
<?php
		}
	}
}
?>
	</div>
</div>
<?php
$js = "
window.hikashop.ready(function(){
	window.hikashop.dlTitle('hikashop_main_content');
";
$js .= "
	hkjQuery('div[data-type=\'product_layout\']').hide();
	window.optionMgr.tabChange('div[data-layout=\'product_".$this->element['layout_type']."\']');
";
$js .= "
	hkjQuery('div.accordion-group #hikashop_main_content').prev().hide();
";
$js .= "
	var ctype = hkjQuery('[name=\'".$this->name."[content_type]\']').val(), ltype = hkjQuery('[name=\'".$this->name."[layout_type]\']').val();
	if(ctype == 'product')
		hkjQuery('div[data-display-type=\'category\']').hide();
	else
		hkjQuery('div[data-display-type=\'product\'], dl[data-display-type=\'product\']').hide();
	if(ltype != 'div') {
		var defaultLayout = document.getElementById('data_module__product_layout_type_default').value;
		if(ltype != 'inherit' || defaultLayout != 'div')
			hkjQuery('div[data-display-tab=\'div\']').hide();
	}
	hkjQuery('#content_select_jform_params_hikashopmodule').change(function(){
		if(hkjQuery(this).val() == 'product'){
			hkjQuery('div[data-layout=\'product_inherit\']').html('".JText::_('HIKA_INHERIT')." ('+defaultParams['layout_type']+')');
			hkjQuery('div[data-display-type=\'product\'], dl[data-display-type=\'product\']').show();
			hkjQuery('div[data-display-type=\'category\']').hide();
			hkjQuery('.hikashop_edit_display_type').children('div').removeClass('hkc-xl-4').addClass('hkc-xl-3');
		}else{
			if(defaultParams['layout_type'] == 'table')
				hkjQuery('div[data-layout=\'product_inherit\']').html('".JText::_('HIKA_INHERIT')." (div)');
			hkjQuery('div[data-display-type=\'product\'], dl[data-display-type=\'product\']').hide();
			hkjQuery('div[data-display-type=\'category\']').show();
			hkjQuery('.hikashop_edit_display_type').children('div').removeClass('hkc-xl-3').addClass('hkc-xl-4');
		}
		window.optionMgr.tabChange('div[data-layout=\'product_inherit\']');
	});
";
$js .="
	window.optionMgr.hideDisplayOptions();
	hkjQuery('#hikashop_main_content').find('input').change(function(){
		window.optionMgr.hideDisplayOptions(''+hkjQuery(this).attr('name')+'',''+hkjQuery(this).val()+'');
	});
	hkjQuery('#hikashop_main_content').find('select').change(function(){
		window.optionMgr.hideDisplayOptions(''+hkjQuery(this).attr('name')+'',''+hkjQuery(this).val()+'');
	});
";
$js .="
	hkjQuery('#hikashop_module_backend_page_edition .hikashop_module_edit_display .hikashop_option_value').find('input').change(function(){
		var name = hkjQuery(this).attr('name');
		var val = hkjQuery(this).val();
		hkjQuery('[name=\''+name+'\']').val(val);
	});
	hkjQuery('#hikashop_module_backend_page_edition .hikashop_module_edit_display .hikashop_option_value').find('select').change(function(){
		var name = hkjQuery(this).attr('name');
		var val = hkjQuery(this).val();
		hkjQuery('[name=\''+name+'\']').val(val);
	});
";
$js .="
	window.optionMgr.showCarouselOptions('carousel','".@$this->element['enable_carousel']."','".$this->name."');
	hkjQuery('.hikashop_module_edit_extra .hikashop_option_value').find('input').change(function(){
		window.optionMgr.showCarouselOptions(''+hkjQuery(this).parent().parent().attr('data-control')+'',''+hkjQuery(this).val()+'','".$this->name."');
	});
	hkjQuery('.hikashop_module_edit_extra .hikashop_option_value').find('select').change(function(){
		window.optionMgr.showCarouselOptions(''+hkjQuery(this).parent().attr('data-control')+'',''+hkjQuery(this).val()+'','".$this->name."');
	});
";
$js .="
	hkjQuery('.listing_item_quantity_fields input').change(function(){
		var name = hkjQuery(this).attr('name').replace('[columns]','').replace('[rows]','');
		var listType = hkjQuery(this).closest('.listing_item_quantity_fields').attr('data-list-type');
		var cCol = 1;
		if(listType != 'table')
			cCol = hkjQuery('input[name=\''+name+'[columns]\']').val();
		var cRow = hkjQuery('input[name=\''+name+'[rows]\']').val();
		hkjQuery('input[name=\''+name+'[limit]\']').val(parseInt(cRow) * parseInt(cCol));
		window.optionMgr.fillSelector(cCol,cRow, name);
	});
	hkjQuery('.listing_item_quantity_fields .hikashop_product_listing_input_button').click(function(){
		var ref = hkjQuery(this).attr('data-ref'),
		inc = hkjQuery(this).attr('data-inc');
		if(inc == 'plus'){
			hkjQuery('input[name=\''+ref+'\']').val(parseInt(hkjQuery('input[name=\''+ref+'\']').val()) + 1).trigger('change');
		}else if(parseInt(hkjQuery('input[name=\''+ref+'\']').val()) > 1){
			hkjQuery('input[name=\''+ref+'\']').val(parseInt(hkjQuery('input[name=\''+ref+'\']').val()) - 1).trigger('change');
		}
	});
	hkjQuery('.listing_item_quantity_selector div').mouseover(function(){
		var classes = hkjQuery(this).attr('class').split(' ');
		window.optionMgr.fillSelector(parseInt(classes[0].replace('col',''))+1,parseInt(classes[1].replace('row',''))+1, hkjQuery(this).parent().attr('data-name'));
	});
	hkjQuery('.listing_item_quantity_selector div').click(function(){
		var name = hkjQuery(this).parent().attr('data-name');
		var classes = hkjQuery(this).attr('class').split(' ');
		hkjQuery('input[name=\''+name+'[columns]\']').val(parseInt(classes[0].replace('col',''))+1);
		hkjQuery('input[name=\''+name+'[rows]\']').val(parseInt(classes[1].replace('row',''))+1);
		hkjQuery('input[name=\''+name+'[limit]\']').val((parseInt(classes[0].replace('col',''))+1) * (parseInt(classes[1].replace('row',''))+1));
	});
	hkjQuery('.listing_item_quantity_selector').mouseleave(function(){
		var name = hkjQuery(this).attr('data-name');
		var cCol = hkjQuery('input[name=\''+name+'[columns]\']').val();
		var limit = hkjQuery('input[name=\''+name+'[limit]\']').val();
		var cRow = 0;
		if(limit != 0)
			cRow = limit / cCol;
		Math.ceil(cRow);
		window.optionMgr.fillSelector(cCol,cRow,name);
	});
});
";
$js .= "var defaultParams = [];";
foreach($this->default_params as $k => $v){
	$js .= "defaultParams['".$k."'] = '".str_replace(array("\\", "'"), array("\\\\", "\'"), $v)."';";
}
$js .= "
window.optionMgr = {
	cpt:{
	},
	fillSelector : function(cCol,cRow,name) {
		hkjQuery('.listing_item_quantity_selector[data-name=\''+name+'\'] div').each(function(){
			var classes = hkjQuery(this).attr('class').split(' ');
			var col = parseInt(classes[0].replace('col',''));
			var row = parseInt(classes[1].replace('row',''));
			hkjQuery(this).removeClass('selected');
			if(col < cCol && row < cRow)
				hkjQuery(this).addClass('selected');
		});
	},
	tabChange : function(el) {
		var val = hkjQuery(el).attr('data-layout'), ctype = hkjQuery('[name=\'".$this->name."[content_type]\']').val();
		if(ctype == 'manufacturer')
			ctype = 'category';
		if(val === undefined || (ctype == 'category' && val == 'product_table'))
			val = 'product_inherit';
		var info = val.split('_');
		if(info[1] == 'inherit'){
			if(ctype == 'category' && '".$this->default_params['layout_type']."' == 'table')
				val = info[0]+'_div';
			else
				val = info[0]+'_".$this->default_params['layout_type']."';
		}
		hkjQuery('div[data-type=\''+info[0]+'_layout\']').css('display','none');
		hkjQuery('div[data-layout=\''+val+'\']').css('display','');
		hkjQuery('#data_module__'+info[0]+'_layout_type').val(info[1]);
		hkjQuery('div[data-type=\''+info[0]+'_layout_choice\']').removeClass('selected');
		hkjQuery(el).addClass('selected');
		if(info[1] == 'div') {
			hkjQuery('div[data-display-tab=\'div\']').show();
		} else {
			var defaultLayout = document.getElementById('data_module__product_layout_type_default').value;
			if(info[1] == 'inherit' && defaultLayout == 'div')
				hkjQuery('div[data-display-tab=\'div\']').show();
			else
				hkjQuery('div[data-display-tab=\'div\']').hide();
		}
	},
	hideDisplayOptions : function(optionName,newValue) {
		var dynamicHide = {
			'child_display_type': {
				'hideValues': ['nochild','inherit'],
				'hideOptions': ['child_limit']
			},
			'div_item_layout_type': {
				'hideValues': ['title','inherit'],
				'hideOptions': ['image_width','image_height']
			},
			'show_price': {
				'hideValues': ['0','-1'],
				'hideOptions': ['price_display_type','price_with_tax','show_original_price','show_discount']
			}
		};
		if(optionName === undefined || newValue === undefined){
			hkjQuery.each(dynamicHide, function(mainOption,values){
				var currentValue = hkjQuery('[name=\'".$this->name."['+mainOption+']\'][checked=\'checked\']').val();
				if(currentValue === undefined)
					currentValue = hkjQuery('[name=\'".$this->name."['+mainOption+']\']').val();
				if((currentValue == 'inherit' || currentValue == '-1'))
					currentValue = defaultParams[mainOption];
				if(hkjQuery.inArray(currentValue,dynamicHide[mainOption]['hideValues']) != '-1'){
					hkjQuery.each(values['hideOptions'],function(index, optionList){
						option = optionList.split(',');
						for(var i = option.length - 1; i >= 0; i--){
							hkjQuery('[name=\'".$this->name."['+option+']\']').parent().parent('.hika_options').hide();
							hkjQuery('[name=\'".$this->name."['+option+']\']').parent().parent().parent('.hika_options').hide();
						}
					});
				}
			});
		}else{
			optionName = optionName.replace('".$this->name."[','').replace(']','');
			if(dynamicHide[optionName] === undefined)
				return;
			if((newValue == 'inherit' || newValue == '-1'))
				newValue = defaultParams[optionName];
			if(hkjQuery.inArray(newValue,dynamicHide[optionName]['hideValues']) != '-1'){
				hkjQuery.each(dynamicHide[optionName]['hideOptions'],function(j, option){
					hkjQuery('[name=\'".$this->name."['+option+']\']').parent().parent('.hika_options').hide();
					hkjQuery('[name=\'".$this->name."['+option+']\']').parent().parent().parent('.hika_options').hide();
				});
			}else{
				hkjQuery.each(dynamicHide[optionName]['hideOptions'],function(j, option){
					hkjQuery('[name=\'".$this->name."['+option+']\']').parent().parent('.hika_options').show();
					hkjQuery('[name=\'".$this->name."['+option+']\']').parent().parent().parent('.hika_options').show();
				});
			}
		}
	},
	showCarouselOptions : function(dataControl,value,name){
		var mainValue = value;
		var dataParts = [dataControl];
		if(dataControl == 'carousel'){
			if(value == 1){
				hkjQuery('.hikashop_module_edit_product_extra_part2').show();
				dataParts = ['effect','paginationthumbnail','pagination','autoslide','carousel'];
			}else{
				hkjQuery('.hikashop_module_edit_product_extra_part2').hide();
				dataParts = ['carousel','effect','autoslide','pagination','paginationthumbnail'];
			}
		}else if(dataControl == 'pagination'){
			dataParts = ['paginationthumbnail','pagination'];
		}
		for(var i = dataParts.length - 1; i >= 0; i--){
			if(dataParts[i] != dataControl && (dataControl != 'carousel' || mainValue != 0)){
				if(dataParts[i] == 'carousel')
					value = hkjQuery('[name=\''+name+'[enable_carousel]\'][checked=\'checked\']').val();
				if(dataParts[i] == 'effect')
					value = hkjQuery('[name=\''+name+'[carousel_effect]\']').val();
				if(dataParts[i] == 'pagination')
					value = hkjQuery('[name=\''+name+'[pagination_type]\']').val();
				if(dataParts[i] == 'autoslide')
					value = hkjQuery('[name=\''+name+'[auto_slide]\'][checked=\'checked\']').val();
			}else{
				value = mainValue;
			}
			if(value == '' || value == '0' || value == 'no' || value == 'fade' || value == 'no_pagination' || (dataParts[i] == 'paginationthumbnail' && value != 'thumbnails')){
				hkjQuery('dl[data-part=\''+dataParts[i]+'\']').hide();
			}else{
				hkjQuery('dl[data-part=\''+dataParts[i]+'\']').show();
			}
		}
	}
};
";

if(HIKASHOP_J40) {
	$js .= "
window.hikashop.ready(function(){
	var mainDiv = document.getElementById('hikashop_main_content');
	mainDiv.parentNode.classList.remove('column-count-md-2');
	mainDiv.parentNode.classList.remove('column-count-lg-3');
});
";
}
$doc = JFactory::getDocument();
$doc->addScriptDeclaration($js);

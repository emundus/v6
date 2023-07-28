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
$showDescription = !in_array(@$this->element['div_item_layout_type'], array('fade','slider_horizontal','slider_vertical')) ? 'style="display:none"' : '';

if(hikashop_level(2)){
	$productEffect="";
	$productEffectDuration="";
	$paneHeight="";
	if(!isset($this->element['div_item_layout_type'])){
		$this->element['div_item_layout_type']='inherit';
	}
	if($this->element['div_item_layout_type']=='fade'){
		$productEffect='style="display:none"';
	}else if($this->element['div_item_layout_type']=='img_pane'){
		$productEffect='style="display:none"';
		$productEffectDuration='style="display:none"';
	}else if($this->element['div_item_layout_type']!='slider_horizontal' && $this->element['div_item_layout_type']!='slider_vertical'){
		$productEffect='style="display:none"';
		$productEffectDuration='style="display:none"';
	}
}
?>
<div class="hk-row-fluid hikashop_menu_edit_display_settings_div" data-type="<?php echo $this->type; ?>_layout" data-layout="<?php echo $this->type; ?>_div">
	<div class="hkc-xl-4 hkc-md-6 hikashop_menu_subblock hikashop_menu_edit_display_settings_subdiv">
		<div class="hikashop_menu_subblock_content">
			<div class="hikashop_menu_subblock_title hikashop_menu_edit_display_settings_div_title"><?php echo JText::_('HIKA_ITEMS'); ?></div>
			<dl class="hika_options">
				<dt class="hikashop_option_name">
					<label class="field_rows"><?php echo JText::_( 'FIELD_ROWS' ); ?></label>
				</dt>
				<dd class="hikashop_option_value">
					<p class="field_columns"><?php echo JText::_( 'FIELD_COLUMNS' ); ?></p>
					<div class="listing_item_quantity_selector" data-name="<?php echo $this->name; ?>">
<?php
		if(!isset($this->element['limit']))
			$this->element['limit'] = '20';
		if(!isset($this->element['columns']))
			$this->element['columns'] = '3';
		$colsNb = @$this->element['columns'];
		$rowsNb = 0;
		if(@$this->element['columns'] != 0)
			$rowsNb = round((int)$this->element['limit'] / (int)$this->element['columns']);
		for($j = 0; $j < 12; $j++){
			for($i = 0; $i < 6; $i++){
				$class = ($i < $colsNb && $j < $rowsNb) ? ' selected' : '';
				echo '<div class="col'.$i.' row'.$j.' listing_div'.$class.'"></div>';
			}
			echo '<br/>';
		}
?>
					</div>
					<div class="listing_item_quantity_fields" data-list-type="div">
						<div class="input-append">
							<input type="text" class="hikashop_product_listing_input" name="<?php echo $this->name; ?>[columns]" value="<?php echo $colsNb; ?>">
							<div class="add-on hikashop_product_listing_input_buttons">
								<div class="hikashop_product_listing_input_button hikashop_product_listing_input_plus" data-ref="<?php echo $this->name; ?>[columns]" data-inc="plus">+</div>
								<div class="hikashop_product_listing_input_button hikashop_product_listing_input_minus" data-ref="<?php echo $this->name; ?>[columns]" data-inc="minus">&ndash;</div>
							</div>
						</div>
						x
						<div class="input-append">
							<input type="text" class="hikashop_product_listing_input" name="<?php echo $this->name; ?>[rows]" value="<?php echo $rowsNb; ?>">
							<div class="add-on hikashop_product_listing_input_buttons">
								<div class="hikashop_product_listing_input_button hikashop_product_listing_input_plus" data-ref="<?php echo $this->name; ?>[rows]" data-inc="plus">+</div>
								<div class="hikashop_product_listing_input_button hikashop_product_listing_input_minus" data-ref="<?php echo $this->name; ?>[rows]" data-inc="minus">&ndash;</div>
							</div>
						</div>
					</div>
				</dd>
			</dl>
			<dl style="display: none;" class="hika_options">
				<dt class="hikashop_option_name">
					<label for="data_menu__<?php echo $this->type; ?>_columns"><?php echo JText::_( 'NUMBER_OF_COLUMNS' ); ?></label>
				</dt>
				<dd class="hikashop_option_value">
					<?php if(!isset($this->element['columns'])) $this->element['columns'] = '3'; ?>
					<input type="text" id="data_menu__<?php echo $this->type; ?>_columns" name="<?php echo $this->name; ?>[columns]" value="<?php echo $this->element['columns']; ?>">
				</dd>
			</dl>
			<dl style="display: none;" class="hika_options">
				<dt class="hikashop_option_name">
					<label for="data_menu__<?php echo $this->type; ?>_limit"><?php echo JText::_( 'NUMBER_OF_ITEMS' ); ?></label>
				</dt>
				<dd class="hikashop_option_value">
					<?php if(!isset($this->element['limit'])) $this->element['limit'] = '20'; ?>
					<input type="text" id="data_menu__<?php echo $this->type; ?>_limit" name="<?php echo $this->name; ?>[limit]" value="<?php echo $this->element['limit']; ?>">
				</dd>
			</dl>
			<?php if((($this->menu == 'product') || ($this->menu == 'category')) && ($this->type == 'product')){ ?>
			<dl class="hika_options"><?php
				if(!isset($this->element['enable_switcher'])) $this->element['enable_switcher'] = 0; ?>
				<dt class="hikashop_option_name">
					<label><?php echo JText::_('HIKA_SWITCHER_MODE'); ?></label>
				</dt>
				<dd class="hikashop_option_value">
					<?php
					if(!isset($this->element['enable_switcher'])) $this->element['enable_switcher'] = '-1';
					echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[enable_switcher]', @$this->element['enable_switcher']);
					?>
				</dd>
			</dl>
			</dl>
			<?php } ?>
		</div>
	</div>
	<div class="hkc-xl-4 hkc-md-6 hikashop_menu_subblock hikashop_menu_edit_display_settings_subdiv">
		<div class="hikashop_menu_subblock_content">
			<div class="hikashop_menu_subblock_title hikashop_menu_edit_display_settings_div_title"><?php echo JText::_('HIKA_ITEM_LAYOUT'); ?></div>
			<dl class="hika_options">
				<dt class="hikashop_option_name">
					<label><?php echo JText::_('HIKA_LAYOUT_TYPE'); ?></label>
				</dt>
				<dd class="hikashop_option_value"><?php
					if(!isset($this->element['div_item_layout_type'])) $this->element['div_item_layout_type'] = 'inherit';
					echo $this->itemType->display($this->name.'[div_item_layout_type]',@$this->element['div_item_layout_type'],$this->js, '');
				?></dd>
			</dl>
			<dl class="hika_options">
				<dt class="hikashop_option_name">
					<label><?php echo JText::_('IMAGE_X'); ?></label>
				</dt>
				<dd class="hikashop_option_value">
					<input size=12 name="<?php echo $this->name; ?>[image_width]" type="text" value="<?php echo @$this->element['image_width'];?>" /> px
				</dd>
			</dl>
			<dl class="hika_options">
				<dt class="hikashop_option_name">
					<label><?php echo JText::_('IMAGE_Y'); ?></label>
				</dt>
				<dd class="hikashop_option_value">
					<input size=12 name="<?php echo $this->name; ?>[image_height]" type="text" value="<?php echo @$this->element['image_height'];?>" /> px
				</dd>
			</dl>
<?php if(hikashop_level(2)){ ?>
			<dl class="hika_options layouteffect_hide layoutfade_hide" id="product_effect" <?php echo $productEffect; ?>>
				<dt class="hikashop_option_name">
					<label><?php echo JText::_('HIKA_TRANSITION_EFFECT'); ?></label>
				</dt>
				<dd class="hikashop_option_value"><?php
					echo $this->transition_effectType->display($this->name.'[product_transition_effect]',@$this->element['product_transition_effect']);
				?></dd>
			</dl>
			<dl class="hika_options layouteffect_hide" id="product_effect_duration" <?php echo $productEffectDuration; ?>>
				<dt class="hikashop_option_name">
					<label><?php echo JText::_('HIKA_EFFECT_DURATION'); ?></label>
				</dt>
				<dd class="hikashop_option_value">
					<input size=12 name="<?php echo $this->name; ?>[product_effect_duration]" type="text" value="<?php echo @$this->element['product_effect_duration'];?>" /> ms
				</dd>
			</dl>
<?php } ?>
			<dl class="hika_options">
				<dt class="hikashop_option_name">
					<label><?php echo JText::_('PANE_HEIGHT'); ?></label>
				</dt>
				<dd class="hikashop_option_value">
					<input size=12 name="<?php echo $this->name; ?>[pane_height]" type="text" value="<?php echo @$this->element['pane_height'];?>" /> px
				</dd>
			</dl>
			<dl class="hika_options">
				<dt class="hikashop_option_name">
					<label><?php echo JText::_('TEXT_CENTERED'); ?></label>
				</dt>
				<dd class="hikashop_option_value"><?php
					if(!isset($this->element['text_center'])) $this->element['text_center'] = -1;
					echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[text_center]', @$this->element['text_center']);
				?></dd>
			</dl>
			<dl class="hika_options showdescription_hide" <?php echo $showDescription; ?>>
				<dt class="hikashop_option_name">
					<label><?php echo JText::_('ELEMENT_DESCRIPTION'); ?></label>
				</dt>
				<dd class="hikashop_option_value"><?php
					if(!isset($this->element['show_description_listing'])) $this->element['show_description_listing'] = 0;
					echo JHTML::_('hikaselect.booleanlist', $this->name.'[show_description_listing]', '', @$this->element['show_description_listing']);
				?></dd>
			</dl>
			<dl class="hika_options">
				<dt class="hikashop_option_name">
					<label><?php echo JText::_('HIKA_HEIGHT_CONSISTENCY'); ?></label>
				</dt>
				<dd class="hikashop_option_value"><?php
					if(!isset($this->element['consistencyheight'])) $this->element['consistencyheight'] = 1;
					echo JHTML::_('hikaselect.booleanlist', $this->name.'[consistencyheight]', '', @$this->element['consistencyheight']);
				?></dd>
			</dl>
			<?php if($this->menu == 'product' || ($this->menu == 'category' && $this->type == 'product')){ ?>
			<dl class="hika_options">
				<dt class="hikashop_option_name">
					<label><?php echo JText::_('HIKA_INFINITE_SCROLL'); ?></label>
				</dt>
				<dd class="hikashop_option_value"><?php
					if(!isset($this->element['infinite_scroll'])) $this->element['infinite_scroll'] = 0;
					echo JHTML::_('hikaselect.booleanlist', $this->name.'[infinite_scroll]', '', @$this->element['infinite_scroll']);
				?></dd>
			</dl>
			<?php } ?>
		</div>
	</div>
	<div class="hkc-xl-4 hkc-md-6 hikashop_menu_subblock hikashop_menu_edit_display_settings_subdiv">
		<div class="hikashop_menu_subblock_content">
			<div class="hikashop_menu_subblock_title hikashop_menu_edit_display_settings_div_title"><?php echo JText::_('HIKA_ITEM_BOX_SETTINGS'); ?></div>
			<dl class="hika_options">
				<dt class="hikashop_option_name">
					<label><?php echo JText::_('BOX_COLOR'); ?></label>
				</dt>
				<dd class="hikashop_option_value">
					<?php echo $this->colorType->displayAll('',$this->name.'[background_color]',@$this->element['background_color']); ?>
				</dd>
			</dl>
			<dl class="hika_options">
				<dt class="hikashop_option_name">
					<label><?php echo JText::_('BOX_MARGIN'); ?></label>
				</dt>
				<dd class="hikashop_option_value">
					<input name="<?php echo $this->name; ?>[margin]" type="text" value="<?php echo @$this->element['margin'];?>" /> px
				</dd>
			</dl>
			<dl class="hika_options">
				<dt class="hikashop_option_name">
					<label><?php echo JText::_('BOX_BORDER'); ?></label>
				</dt>
				<dd class="hikashop_option_value">
					<?php
					if(!isset($this->element['border_visible'])) $this->element['border_visible'] = '-1';
					echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[border_visible]', @$this->element['border_visible'], JHTML::_('select.option', 2, JText::_('THUMBNAIL')));
					?>
				</dd>
			</dl>
			<dl class="hika_options">
				<dt class="hikashop_option_name">
					<label><?php echo JText::_('BOX_ROUND_CORNER'); ?></label>
				</dt>
				<dd class="hikashop_option_value">
					<?php
					if(!isset($this->element['rounded_corners'])) $this->element['rounded_corners'] = '-1';
					echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[rounded_corners]', @$this->element['rounded_corners']);
					?>
				</dd>
			</dl>
			<dl class="hika_options">
				<dt class="hikashop_option_name">
					<label for="data_menu__zoom_on_hover">
						<?php echo JText::_('HIKA_ZOOM_HOVER');?>
					</label>
				</dt>
				<dd class="hikashop_option_value"><?php
					if(!isset($this->element['zoom_on_hover'])) $this->element['zoom_on_hover'] = '-1';
					echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[zoom_on_hover]', $this->element['zoom_on_hover']);
				?></dd>
			</dl>
		</div>
	</div>
</div>
<?php
$js = "
window.hikashop.ready(function(){
	hkjQuery('select[name=\'".$this->name."[div_item_layout_type]\']').change(function(){
		if(hkjQuery(this).val()==\"slider_vertical\" || hkjQuery(this).val()==\"slider_horizontal\"){
			hkjQuery('.layouteffect_hide').show();
			hkjQuery('.showdescription_hide').show();
		}else if(hkjQuery(this).val()==\"fade\"){
			hkjQuery('.layouteffect_hide').show();
			hkjQuery('.showdescription_hide').show();
			hkjQuery('.layoutfade_hide').hide();
		}else{
			hkjQuery('.layouteffect_hide').hide();
			hkjQuery('.showdescription_hide').hide();
		}
	});
});
";
$doc = JFactory::getDocument();
$doc->addScriptDeclaration($js);

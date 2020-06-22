<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hk-row-fluid hikashop_menu_edit_display_settings_list" data-type="<?php echo $this->type; ?>_layout" data-layout="<?php echo $this->type; ?>_list">
	<div class="hkc-xl-4 hkc-md-6 hikashop_menu_subblock hikashop_menu_edit_display_settings_subdiv">
		<div class="hikashop_menu_subblock_content">
			<div class="hikashop_menu_subblock_title hikashop_menu_edit_display_settings_div_title"><?php echo JText::_('HIKA_ITEMS'); ?></div>
			<dl class="hika_options">
				<dt class="hikashop_option_name greytint">
					<label class="field_rows" for="data_menu__<?php echo $this->type; ?>_limit"><?php echo JText::_( 'FIELD_ROWS' ); ?></label>
				</dt>
				<dd class="hikashop_option_value">
					<p class="field_columns"><?php echo JText::_( 'FIELD_COLUMNS' ); ?></p>
					<div class="listing_item_quantity_selector" data-name="<?php echo $this->name; ?>">
<?php
					$colsNb = @$this->element['columns'];
					$rowsNb = 0;
					if(@$this->element['columns'] != 0)
						$rowsNb = round($this->element['limit'] / $this->element['columns']);
					for($j = 0; $j < 20; $j++){
						for($i = 0; $i < 6; $i++){
							$class = ' listing_list';
							if($i < $colsNb && $j < $rowsNb)
								$class .= ' selected';
							echo '<div class="col'.$i.' row'.$j.$class.'"></div>';
						}
						echo '<br/>';
					}
?>
					</div>
					<div class="listing_item_quantity_fields" data-list-type="list">
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
		</div>
	</div>
	<div class="hkc-xl-4 hkc-md-6 hikashop_menu_subblock hikashop_menu_edit_display_settings_subdiv">
		<div class="hikashop_menu_subblock_content">
			<div class="hikashop_menu_subblock_title hikashop_menu_edit_display_settings_div_title"><?php echo JText::_('HIKA_UL_SETTINGS'); ?></div>
			<dl class="hika_options">
				<dt class="hikashop_option_name">
					<?php echo JText::_('UL_CLASS_NAME');?>
				</dt>
				<dd class="hikashop_option_value">
					<input name="<?php echo $this->name; ?>[ul_class_name]" type="text" value="<?php echo @$this->element['ul_class_name'];?>" />
				</dd>
<?php if($this->type == 'category') { ?>
				<dt class="hikashop_option_name">
					<?php echo hikashop_hktooltip(JText::_('UL_DISPLAY_SIMPLELIST'), '', JText::_('UL_DISPLAY_SIMPLELIST'), '', 0);?>
				</dt>
				<dd class="hikashop_option_value"><?php
					echo JHTML::_('hikaselect.booleanlist', $this->name.'[ul_display_simplelist]' , '', @$this->element['ul_display_simplelist']);
				?></dd>
<?php } ?>
			</dl>
		</div>
	</div>
</div>

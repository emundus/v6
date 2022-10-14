<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><script type="text/javascript">
window.productMgr = { cpt:{} };
window.hikashop.ready(function(){
	window.hikamarket.dlTitle('hikamarket_products_form');
	window.hikamarket.collapseTitles();
});
</script>
<form action="<?php echo hikamarket::completeLink('product');?>" method="post" name="hikamarket_form" id="hikamarket_products_form" enctype="multipart/form-data">
<?php if($this->aclEdit('variants')) { ?>
<div id="hikamarket_product_edition_header" style="<?php if(empty($this->product->characteristics) || empty($this->product->product_id)) echo 'display:none;'; ?>">
<?php
	if(!empty($this->product)) {
		$image = $this->imageHelper->getThumbnail(@$this->product->images[0]->file_path, array(50,50), array('default' => true));
		if($image->success)
			$image_url = $image->url;
		else
			$image_url = $image->path;
		unset($image);
?>
	<h3><img src="<?php echo $image_url; ?>" alt="" style="vertical-align:middle;margin-right:5px;"/><?php echo $this->product->product_name; ?></h3>
	<ul class="hikam_tabs" rel="tabs:hikamarket_product_edition_tab_">
		<li class="active"><a href="#product" rel="tab:1" onclick="return window.hikamarket.switchTab(this);"><?php echo JText::_('PRODUCT'); ?></a></li>
		<li><a href="#variants" rel="tab:2" onclick="return window.hikamarket.switchTab(this);"><?php echo JText::_('VARIANTS'); ?><span id="hikamarket_product_variant_label"></span></a></li>
	</ul>
	<div style="clear:both"></div>
<?php
	}
?>
</div>
<div id="hikamarket_product_edition_tab_1">
<?php } ?>

<div class="hk-row-fluid">
<?php if($this->aclEdit('images')) { ?>
	<div class="hkc-md-4"><?php
		echo $this->loadTemplate('image');
	?></div>
	<div class="hkc-md-8">
<?php } else { ?>
	<div class="hkc-md-12">
<?php } ?>
		<dl class="hikam_options">
<?php if($this->aclEdit('name')) { ?>
			<dt class="hikamarket_product_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
			<dd class="hikamarket_product_name"><input type="text" name="data[product][product_name]" value="<?php echo $this->escape(@$this->product->product_name); ?>"/></dd>
<?php } else { ?>
			<dt class="hikamarket_product_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
			<dd class="hikamarket_product_name"><?php
				if(!empty($this->product->product_name))
					echo $this->product->product_name;
				else
					echo '<em>'.JText::_('PRODUCT_NO_NAME').'</em>';
			?></dd>
<?php }

	if($this->aclEdit('code')) { ?>
			<dt class="hikamarket_product_code"><label><?php echo JText::_('PRODUCT_CODE'); ?></label></dt>
			<dd class="hikamarket_product_code"><input type="text" name="data[product][product_code]" value="<?php echo $this->escape(@$this->product->product_code); ?>"/></dd>
<?php }

	if($this->aclEdit('quantity')) { ?>
			<dt class="hikamarket_product_quantity"><label><?php echo JText::_('PRODUCT_QUANTITY'); ?></label></dt>
			<dd class="hikamarket_product_quantity"><?php
				echo $this->quantityType->display('data[product][product_quantity]', @$this->product->product_quantity);
			?></dd>
<?php }

	if(@$this->product->product_type != 'variant' && $this->aclEdit('category')) { ?>
			<dt class="hikamarket_product_category"><label><?php echo JText::_('PRODUCT_CATEGORIES'); ?></label></dt>
			<dd class="hikamarket_product_category"><?php
		$categories = null;
		if(!empty($this->product->categories))
			$categories = array_keys($this->product->categories);
		echo $this->nameboxType->display(
			'data[product][categories]',
			$categories,
			hikamarketNameboxType::NAMEBOX_MULTIPLE,
			'category',
			array(
				'delete' => true,
				'sort' => true,
				'root' => $this->vendorCategories,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
			?></dd>
<?php }

	if(@$this->product->product_type != 'variant' && $this->aclEdit('manufacturer')) {?>
			<dt class="hikamarket_product_manufacturer"><label><?php echo JText::_('MANUFACTURER'); ?></label></dt>
			<dd class="hikamarket_product_manufacturer"><?php
		echo $this->nameboxType->display(
			'data[product][product_manufacturer_id]',
			(int)@$this->product->product_manufacturer_id,
			hikamarketNameboxType::NAMEBOX_SINGLE,
			'brand',
			array(
				'delete' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
			?></dd>
<?php }

	if($this->aclEdit('published')) { ?>
			<dt class="hikamarket_product_published"><label><?php echo JText::_('HIKA_PUBLISHED'); ?></label></dt>
			<dd class="hikamarket_product_published"><?php
				echo $this->radioType->booleanlist('data[product][product_published]', '', @$this->product->product_published);
			?></dd>
<?php }

	if($this->aclEdit('translations')) {
		if(!empty($this->product->translations) && !empty($this->product->product_id)) { ?>
			<dt class="hikamarket_product_translations"><label><?php echo JText::_('HIKA_TRANSLATIONS'); ?></label></dt>
			<dd class="hikamarket_product_translations"><?php
				foreach($this->product->translations as $language_id => $translation){
					$lngName = $this->translationHelper->getFlag($language_id);
					echo '<div class="hikamarket_multilang_button">' .
						$this->popup->display(
							$lngName, strip_tags($lngName),
							hikamarket::completeLink('product&task=edit_translation&product_id=' . @$this->product->product_id.'&language_id='.$language_id, true),
							'hikamarket_product_translation_'.$language_id,
							760, 480, '', '', 'link'
						).
						'</div>';
				}
			?></dd>
<?php
		}
	}

	if(hikamarket::level(1) && $this->vendor->vendor_id == 1 && hikamarket::acl('product/subvendor') && hikamarket::acl('product/edit/vendor')) {
?>
			<dt class="hikamarket_product_vendor"><label><?php echo JText::_('HIKA_VENDOR'); ?></label></dt>
			<dd class="hikamarket_product_vendor"><?php
		echo $this->nameboxType->display(
			'data[product][product_vendor_id]',
			(int)@$this->product->product_vendor_id,
			hikamarketNameboxType::NAMEBOX_SINGLE,
			'vendor',
			array(
				'delete' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>'
			)
		);
			?></dd>
<?php }
?>
		</dl>
	</div>
	<div class="hkc-md-12">
<?php
	if($this->aclEdit('description')) {
?>
	<div class="hikamarket_section_container hikamarket_section_product_description">
		<h3 data-section-toggle="product_description" class="hikamarket_section_toggle"><?php echo JText::_('HIKA_DESCRIPTION'); ?></h3>
		<div id="hikamarket_section_product_description">
			<?php echo $this->editor->display(); ?>
			<div style="clear:both"></div>
		</div>
	</div>
<?php
	}

	if($this->aclEdit('price') || (@$this->product->product_type != 'variant' && ($this->aclEdit('tax') || $this->aclEdit('msrp')))) {
?>
	<div class="hikamarket_section_container hikamarket_section_product_prices">
		<h3 data-section-toggle="product_prices" class="hikamarket_section_toggle"><?php echo JText::_('PRICES_AND_TAXES'); ?></h3>
		<div id="hikamarket_section_product_prices">
<?php
		if($this->aclEdit('price')) {
			echo $this->loadTemplate('price');
		}

		if(@$this->product->product_type != 'variant' && ($this->aclEdit('tax') || $this->aclEdit('msrp'))) {
?>
			<dl class="hikam_options">
<?php
			if(@$this->product->product_type != 'variant' && $this->aclEdit('tax')) { ?>
				<dt class="hikamarket_product_tax"><label><?php echo JText::_('TAXATION_CATEGORY'); ?></label></dt>
				<dd class="hikamarket_product_tax"><?php
					echo $this->categoryType->display('data[product][product_tax_id]', @$this->product->product_tax_id, 'tax');
				?></dd>
<?php
			}

			if(@$this->product->product_type != 'variant' && $this->aclEdit('msrp')) {
				$curr = '';
				$mainCurr = $this->currencyClass->getCurrencies($this->main_currency_id, $curr);
?>
				<dt class="hikamarket_product_msrp"><label><?php echo JText::_('PRODUCT_MSRP'); ?></label></dt>
				<dd class="hikamarket_product_msrp">
					<input type="text" name="data[product][product_msrp]" value="<?php echo $this->escape(@$this->product->product_msrp); ?>"/> <?php echo $mainCurr[$this->main_currency_id]->currency_symbol.' '.$mainCurr[$this->main_currency_id]->currency_code;?>
				</dd>
<?php
			}
?>
			</dl>
<?php
		}
?>
		</div>
	</div>
<?php
	}

	if(!$this->is_variant_product && ($this->aclEdit('characteristics') || $this->aclEdit('related') || (hikashop_level(1) && ($this->aclEdit('options') || $this->aclEdit('bundles'))))) {
?>
	<div class="hikamarket_section_container hikamarket_section_product_specifications">
		<h3 data-section-toggle="product_specifications" class="hikamarket_section_toggle"><?php echo JText::_('SPECIFICATIONS'); ?></h3>
		<div id="hikamarket_section_product_specifications">
			<dl class="hikam_options">
<?php
		if($this->aclEdit('characteristics')) { ?>
				<dt class="hikamarket_product_characteristics"><label><?php echo JText::_('CHARACTERISTICS'); ?></label></dt>
				<dd class="hikamarket_product_characteristics"><?php
					echo $this->loadTemplate('characteristic');
				?></dd>
<?php
		}

		if($this->aclEdit('related')) { ?>
				<dt class="hikamarket_product_related"><label><?php echo JText::_('RELATED_PRODUCTS'); ?></label></dt>
				<dd class="hikamarket_product_related"><?php
			echo $this->nameboxType->display(
				'data[product][related]',
				@$this->product->related,
				hikamarketNameboxType::NAMEBOX_MULTIPLE,
				'product',
				array(
					'delete' => true,
					'sort' => true,
					'root' => $this->rootCategory,
					'allvendors' => (int)$this->config->get('related_all_vendors', 1),
					'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				)
			);
				?></dd>
<?php
		}

		if(hikashop_level(1) && $this->aclEdit('options')) { ?>
				<dt class="hikamarket_product_options"><label><?php echo JText::_('OPTIONS'); ?></label></dt>
				<dd class="hikamarket_product_options"><?php
			echo $this->nameboxType->display(
				'data[product][options]',
				@$this->product->options,
				hikamarketNameboxType::NAMEBOX_MULTIPLE,
				'product',
				array(
					'delete' => true,
					'sort' => true,
					'root' => $this->rootCategory,
					'allvendors' => (int)$this->config->get('options_all_vendors', 0),
					'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				)
			);
				?></dd>
<?php
		}

		if(hikashop_level(1) && $this->aclEdit('bundles')) { ?>
				<dt class="hikamarket_product_bundles"><label><?php echo JText::_('BUNDLED_PRODUCTS'); ?></label></dt>
				<dd class="hikamarket_product_bundles"><?php
					echo $this->loadTemplate('bundle');
				?></dd>
<?php
		}
?>
			</dl>
		</div>
	</div>
<?php
	}

	if(!$this->is_variant_product && ($this->aclEdit('pagetitle') || $this->aclEdit('url') || $this->aclEdit('metadescription') || $this->aclEdit('keywords') || $this->aclEdit('alias') || $this->aclEdit('canonical') || $this->aclEdit('tags'))) {
?>
	<div class="hikamarket_section_container hikamarket_section_product_seo">
		<h3 data-section-toggle="product_seo" class="hikamarket_section_toggle"><?php echo JText::_('SEO'); ?></h3>
		<div id="hikamarket_section_product_seo">
			<dl class="hikam_options">
<?php
		if($this->aclEdit('pagetitle')) { ?>
				<dt class="hikamarket_product_pagetitle"><label><?php echo JText::_('PAGE_TITLE'); ?></label></dt>
				<dd class="hikamarket_product_pagetitle"><input type="text" class="fullrow" size="45" name="data[product][product_page_title]" value="<?php echo $this->escape(@$this->product->product_page_title); ?>" /></dd>
<?php
		}

		if($this->aclEdit('url')) { ?>
				<dt class="hikamarket_product_url"><label><?php echo JText::_('URL'); ?></label></dt>
				<dd class="hikamarket_product_url"><input type="text" class="fullrow" size="45" name="data[product][product_url]" value="<?php echo $this->escape(@$this->product->product_url); ?>" /></dd>
<?php
		}

		if($this->aclEdit('metadescription')) { ?>
				<dt class="hikamarket_product_metadescription"><label><?php echo JText::_('PRODUCT_META_DESCRIPTION'); ?></label></dt>
				<dd class="hikamarket_product_metadescription"><textarea id="product_meta_description" class="fullrow" cols="35" rows="2" name="data[product][product_meta_description]"><?php echo $this->escape(@$this->product->product_meta_description); ?></textarea></dd>
<?php
		}

		if($this->aclEdit('keywords')) { ?>
				<dt class="hikamarket_product_keywords"><label><?php echo JText::_('PRODUCT_KEYWORDS'); ?></label></dt>
				<dd class="hikamarket_product_keywords"><textarea id="product_keywords" class="fullrow" cols="35" rows="2" name="data[product][product_keywords]"><?php echo $this->escape(@$this->product->product_keywords); ?></textarea></dd>
<?php
		}

		if($this->aclEdit('alias')) { ?>
				<dt class="hikamarket_product_alias"><label><?php echo JText::_('HIKA_ALIAS'); ?></label></dt>
				<dd class="hikamarket_product_alias"><input type="text" class="fullrow" size="45" name="data[product][product_alias]" value="<?php echo $this->escape(@$this->product->product_alias); ?>" /></dd>
<?php
		}

		if($this->aclEdit('canonical')) { ?>
				<dt class="hikamarket_product_canonical"><label><?php echo JText::_('PRODUCT_CANONICAL'); ?></label></dt>
				<dd class="hikamarket_product_canonical"><input type="text" class="fullrow" size="45" name="data[product][product_canonical]" value="<?php echo $this->escape(@$this->product->product_canonical); ?>"/></dd>
<?php
		}

		if($this->aclEdit('tags')) {
			$tagsHelper = hikamarket::get('shop.helper.tags');
			if(!empty($tagsHelper) && $tagsHelper->isCompatible()) { ?>
				<dt class="hikamarket_product_tags"><label><?php echo JText::_('JTAG'); ?></label></dt>
				<dd class="hikamarket_product_tags"><?php
					$tags = $tagsHelper->loadTags('product', $this->product);
					echo $tagsHelper->renderInput($tags, array('name' => 'data[tags]', 'class' => 'inputbox'));
				?></dd>
<?php
			}
		}
?>
		</dl>
		</div>
	</div>
<?php
	}

	if($this->aclEdit('qtyperorder') || $this->aclEdit('saledates') || $this->aclEdit('warehouse') || $this->aclEdit('weight') || $this->aclEdit('volume') || ($this->aclEdit('acl') && hikashop_level(2))) {
?>
	<div class="hikamarket_section_container hikamarket_section_product_restrictions">
		<h3 data-section-toggle="product_restrictions" class="hikamarket_section_toggle"><?php echo JText::_('RESTRICTIONS_AND_DIMENSIONS'); ?></h3>
		<div id="hikamarket_section_product_restrictions">
			<dl class="hikam_options">
<?php
	if($this->aclEdit('qtyperorder')) {?>
				<dt class="hikamarket_product_qtyperorder"><label><?php echo JText::_('QUANTITY_PER_ORDER'); ?></label></dt>
				<dd class="hikamarket_product_qtyperorder">
					<input type="text" name="data[product][product_min_per_order]" value="<?php echo (int)@$this->product->product_min_per_order; ?>" /><?php
					echo ' ' . JText::_('HIKA_QTY_RANGE_TO'). ' ';
					echo $this->quantityType->display('data[product][product_max_per_order]', @$this->product->product_max_per_order);
				?></dd>
<?php }

	if($this->aclEdit('saledates')) {?>
				<dt class="hikamarket_product_salestart"><label><?php echo JText::_('PRODUCT_SALE_DATES'); ?></label></dt>
				<dd class="hikamarket_product_salestart"><?php
					echo JHTML::_('calendar', hikamarket::getDate((@$this->product->product_sale_start?@$this->product->product_sale_start:''),'%Y-%m-%d %H:%M'), 'data[product][product_sale_start]','product_sale_start','%Y-%m-%d %H:%M',array('size' => '20'));
					echo ' <span class="calendar-separator">' . JText::_('HIKA_RANGE_TO') . '</span> ';
					echo JHTML::_('calendar', hikamarket::getDate((@$this->product->product_sale_end?@$this->product->product_sale_end:''),'%Y-%m-%d %H:%M'), 'data[product][product_sale_end]','product_sale_end','%Y-%m-%d %H:%M',array('size' => '20'));
				?></dd>
<?php }

	if($this->aclEdit('warehouse')) { ?>
				<dt class="hikamarket_product_warehouse"><label><?php echo JText::_('WAREHOUSE'); ?></label></dt>
				<dd class="hikamarket_product_warehouse"><?php
		echo $this->nameboxType->display(
			'data[product][product_warehouse_id]',
			(int)@$this->product->product_warehouse_id,
			hikamarketNameboxType::NAMEBOX_SINGLE,
			'warehouse',
			array(
				'delete' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
				?></dd>
<?php }

	if($this->aclEdit('weight')) { ?>
				<dt class="hikamarket_product_weight"><label><?php echo JText::_('PRODUCT_WEIGHT'); ?></label></dt>
				<dd class="hikamarket_product_weight"><input type="text" name="data[product][product_weight]" value="<?php echo $this->escape(@$this->product->product_weight); ?>"/><?php echo $this->weight->display('data[product][product_weight_unit]', @$this->product->product_weight_unit); ?></dd>
<?php }

	if($this->aclEdit('volume')) { ?>
				<dt class="hikamarket_product_volume"><label><?php echo JText::_('PRODUCT_VOLUME'); ?></label></dt>
				<dd class="hikamarket_product_volume">
					<div class="hkinput-group">
						<span class="hkinput-group-addon"><?php
							echo hikamarket::tooltip(JText::_('PRODUCT_LENGTH'), '', '', '<i class="hk-icon-14 iconM-14-length"></i>', '', 0)
						?></span><input size="10" class="hk-control" style="width:50px" type="text" name="data[product][product_length]" value="<?php echo $this->escape(@$this->product->product_length); ?>"/>
					</div>
					<div class="hkinput-group">
						<span class="hkinput-group-addon"><?php
							echo hikamarket::tooltip(JText::_('PRODUCT_WIDTH'), '', '', '<i class="hk-icon-14 iconM-14-width"></i>', '', 0);
						?></span><input size="10" class="hk-control" style="width:50px" type="text" name="data[product][product_width]" value="<?php echo $this->escape(@$this->product->product_width); ?>"/>
					</div>
					<div class="hkinput-group">
						<span class="hkinput-group-addon"><?php
							echo hikamarket::tooltip(JText::_('PRODUCT_HEIGHT'), '', '', '<i class="hk-icon-14 iconM-14-height"></i>', '', 0);
						?></span><input size="10" class="hk-control" style="width:50px" type="text" name="data[product][product_height]" value="<?php echo $this->escape(@$this->product->product_height); ?>"/>
					</div>
					<?php echo $this->volume->display('data[product][product_dimension_unit]', @$this->product->product_dimension_unit);?>
				</dd>
<?php }

	if(hikashop_level(2) && $this->aclEdit('acl')) { ?>
			<dt class="hikamarket_product_acl"><label><?php echo JText::_('ACCESS_LEVEL'); ?></label></dt>
			<dd class="hikamarket_product_acl"><?php
				$product_access = 'all';
				if(isset($this->product->product_access))
					$product_access = $this->product->product_access;
				echo $this->joomlaAcl->display('data[product][product_access]', $product_access, true, true);
			?></dd>
<?php }
?>
			</dl>
		</div>
	</div>
<?php } ?>
<?php
	if($this->aclEdit('files')) {
?>
	<div class="hikamarket_section_container hikamarket_section_product_files">
		<h3 data-section-toggle="product_files" class="hikamarket_section_toggle"><?php echo JText::_('FILES'); ?></h3>
		<div id="hikamarket_section_product_files">
<?php
		echo $this->loadTemplate('file');
?>
		</div>
	</div>
<?php
	}
?>
<?php
	if($this->aclEdit('customfields')) {
		if(!empty($this->fields)) {
?>
	<div class="hikamarket_section_container hikamarket_section_product_fields">
		<h3 data-section-toggle="product_fields" class="hikamarket_section_toggle"><?php echo JText::_('FIELDS'); ?></h3>
		<div id="hikamarket_section_product_fields">
<?php
			foreach($this->fields as $fieldName => $oneExtraField) {
?>
		<dl id="hikashop_product_<?php echo $fieldName; ?>" class="hikam_options">
			<dt class="hikamarket_product_<?php echo $fieldName; ?>"><?php echo $this->fieldsClass->getFieldName($oneExtraField); ?></dt>
			<dd class="hikamarket_product_<?php echo $fieldName; ?>"><?php
				$onWhat = 'onchange';
				if($oneExtraField->field_type == 'radio')
					$onWhat = 'onclick';
				echo $this->fieldsClass->display($oneExtraField, @$this->product->$fieldName, 'data[product]['.$fieldName.']', false, ' '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\'product\',0);"');
			?></dd>
		</dl>
<?php
			}
?>
		</div>
	</div>
<?php
		}
	}

	if($this->aclEdit('plugin')) {
		$html = array();
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikamarket');
		JFactory::getApplication()->triggerEvent('onMarketProductBlocksDisplay', array(&$this->product, &$html));

		foreach($html as $h) {
			echo $h;
		}
	}
?>
	</div>
</div>
<?php if($this->aclEdit('variants')) { ?>
</div>
<div id="hikamarket_product_edition_tab_2" style="display:none;">
	<div id="hikamarket_product_variant_list"><?php
		echo $this->loadTemplate('variants');
	?></div>
	<div id="hikamarket_product_variant_edition">
	</div>
</div>
<?php } ?>
<?php if(!empty($this->product->product_type) && $this->product->product_type == 'variant' && !empty($this->product->product_parent_id)) { ?>
	<input type="hidden" name="data[product][product_type]" value="<?php echo $this->product->product_type; ?>"/>
	<input type="hidden" name="data[product][product_parent_id]" value="<?php echo (int)$this->product->product_parent_id; ?>"/>
<?php } ?>
	<input type="hidden" name="cancel_action" value="<?php echo @$this->cancel_action; ?>"/>
	<input type="hidden" name="cancel_url" value="<?php echo @$this->cancel_url; ?>"/>
	<input type="hidden" name="cid[]" value="<?php echo @$this->product->product_id; ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="ctrl" value="product"/>
	<?php echo JHTML::_('form.token'); ?>
</form>

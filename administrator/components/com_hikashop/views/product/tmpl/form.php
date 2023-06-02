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
<script type="text/javascript">
	window.productMgr = { cpt:{} };
	window.hikashop.ready(function(){window.hikashop.dlTitle('adminForm');});
</script>
<form action="<?php echo hikashop_completeLink('product');?>" method="post" onsubmit="window.productMgr.prepare();" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<!-- Product edition header -->
	<div id="hikashop_product_edition_header" style="<?php if(empty($this->product->characteristics)) echo 'display:none;'; ?>">

	<span id="hikashop_variants_missing_error" style="<?php if(!empty($this->product->variants)) echo 'display:none;'; ?>">
		<?php echo hikashop_display(JText::_('GO_IN_VARIANTS_TAB_AND_GENERATE_VARIANTS'), 'info'); ?>
	</span>
<?php
	if(!empty($this->product)) {
		$image = $this->imageHelper->getThumbnail(@$this->product->images[0]->file_path, array(50,50), array('default' => true));
		if($image->success)
			$image_url = $image->url;
		else
			$image_url = $image->path;
		unset($image);
?>
		<h3><img src="<?php echo $image_url; ?>" alt="" style="vertical-align:middle;margin-right:5px;"/><?php echo @$this->product->product_name; ?></h3>
		<ul class="hika_tabs" rel="tabs:hikashop_product_edition_tab_">
			<li class="active"><a href="#product" rel="tab:1" onclick="return window.hikashop.switchTab(this);"><?php echo JText::_('PRODUCT'); ?></a></li>
			<li><a href="#variants" rel="tab:2" onclick="return window.hikashop.switchTab(this);"><?php echo JText::_('VARIANTS'); ?><span id="hikashop_product_variant_label"></span></a></li>
		</ul>
		<div style="clear:both"></div>
<?php
	}
	$customize_css = "hikashop_customize_area";
	if($this->customize)
		$customize_css .= " hikashop_customize_pointer";
?>
	</div>
<div id="hikashop_product_backend_page_edition" class="<?php echo $customize_css; ?>">

	<!-- Product edition : main tab -->
	<div id="hikashop_product_edition_tab_1"><div class="hk-container-fluid">

	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_general" data-id="general"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_general_title"><?php
			echo JText::_('MAIN_OPTIONS');
		?></div>

		<dl class="hika_options">
<?php if(hikashop_acl('product/edit/name')) { ?>
			<dt class="hikashop_product_name"><label for="data_product__product_name"><?php echo JText::_('HIKA_NAME'); ?></label></dt>
			<dd class="hikashop_product_name"><input type="text" id="data_product__product_name" name="data[product][product_name]" value="<?php echo $this->escape(@$this->product->product_name); ?>"/></dd>
<?php } else { ?>
			<dt class="hikashop_product_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
			<dd class="hikashop_product_name"><?php echo @$this->product->product_name; ?></dd>
<?php }

	if(hikashop_acl('product/edit/code')) { ?>
			<dt class="hikashop_product_code"><label for="data_product__product_code"><?php echo hikashop_tooltip(JText::_('PRODUCT_CODE_SKU'), '', '', JText::_('HIKA_PRODUCT_CODE'), '', 0); ?></label></dt>
			<dd class="hikashop_product_code"><input type="text" id="data_product__product_code" name="data[product][product_code]" value="<?php echo $this->escape(@$this->product->product_code); ?>"/></dd>
<?php }

	if(hikashop_acl('product/edit/quantity')) { ?>
			<dt class="hikashop_product_quantity"><label for="data_product__product_quantity"><?php echo JText::_('PRODUCT_QUANTITY'); ?></label></dt>
			<dd class="hikashop_product_quantity"><?php
				echo $this->quantityType->displayInput('data[product][product_quantity]', @$this->product->product_quantity);
			?></dd>
<?php }

	if(@$this->product->product_type != 'variant') { ?>
			<dt class="hikashop_product_category"><label for="data_product_categories_text"><?php echo JText::_('HIKA_CATEGORIES'); ?></label></dt>
			<dd class="hikashop_product_category"><?php
		$categories = null;
		if(!empty($this->product->categories))
			$categories = array_keys($this->product->categories);
		echo $this->nameboxType->display(
			'data[product][categories]',
			$categories,
			hikashopNameboxType::NAMEBOX_MULTIPLE,
			'category',
			array(
				'delete' => true,
				'brand' => false,
				'sort' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				'tooltip' => true,
			)
		);
			?></dd>
<?php }

	if(@$this->product->product_type != 'variant' && hikashop_acl('product/edit/manufacturer')) { ?>
			<dt class="hikashop_product_manufacturer"><label for="data_product_product_manufacturer_id_text"><?php echo JText::_('MANUFACTURER'); ?></label></dt>
			<dd class="hikashop_product_manufacturer"><?php
		echo $this->nameboxType->display(
			'data[product][product_manufacturer_id]',
			(int)@$this->product->product_manufacturer_id,
			hikashopNameboxType::NAMEBOX_SINGLE,
			'brand',
			array(
				'delete' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
			?></dd>
<?php }

	$tagsHelper = hikashop_get('helper.tags');
	if(!empty($tagsHelper) && $tagsHelper->isCompatible()) {
?>
			<dt class="hikashop_product_tags"><label for="data_tags_"><?php echo JText::_('JTAG'); ?></label></dt>
			<dd class="hikashop_product_tags"><?php
				$tags = $tagsHelper->loadTags('product', $this->product);
				echo $tagsHelper->renderInput($tags, array('name' => 'data[tags]', 'class' => 'inputbox'));
			?></dd>
<?php
	}

	if(hikashop_acl('product/edit/published')) { ?>
			<dt class="hikashop_product_published"><label><?php echo JText::_('HIKA_PUBLISHED'); ?></label></dt>
			<dd class="hikashop_product_published"><?php echo JHTML::_('hikaselect.booleanlist', "data[product][product_published]" , '', @$this->product->product_published); ?></dd>
<?php }

	if(hikashop_acl('product/edit/translations') && !empty($this->product->translations) && !empty($this->product->product_id)) {
?>
			<dt class="hikashop_product_translations"><label><?php echo JText::_('HIKA_TRANSLATIONS'); ?></label></dt>
			<dd class="hikashop_product_translations"><?php
		foreach($this->product->translations as $language_id => $translation){
			$lngName = $this->translationHelper->getFlag($language_id);
			echo '<div class="hikashop_multilang_button hikashop_language_'.$language_id.'"">' .
				$this->popup->display(
					$lngName, strip_tags($lngName),
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


	<?php
	if(hikashop_acl('product/edit/images') || hikashop_acl('product/edit/files')) {
?>
	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_images" data-id="images"><div>
		<div class="hikashop_product_part_title hikashop_product_upload_title"><?php
			echo JText::_('IMAGES_AND_FILES');
		?></div>
<?php
		if(hikashop_acl('product/edit/images'))
			echo $this->loadTemplate('image');

		if(hikashop_acl('product/edit/files'))
			echo $this->loadTemplate('file');
?>
	</div></div>
<?php
	}
?>
	<div class="hkc-lg-clear"></div>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_price" data-id="prices"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_price_title">
			<?php echo JText::_('PRICES_AND_TAXES'); ?>
		</div>
		<dl class="hika_options">
<?php
		if(hikashop_acl('product/edit/tax')) {
?>
			<dt class="hikashop_product_tax"><label for="dataproductproduct_tax_id"><?php echo JText::_('PRODUCT_TAXATION_CATEGORY'); ?></label></dt>
			<dd class="hikashop_product_tax"><?php
				echo $this->categoryType->display('data[product][product_tax_id]', @$this->product->product_tax_id, 'tax');
			?></dd>
<?php
		}

		$curr = '';
		$mainCurr = $this->currencyClass->getCurrencies($this->main_currency_id, $curr);
?>
			<dt class="hikashop_product_msrp"><label for="data_product__product_msrp"><?php echo JText::_('PRODUCT_MSRP'); ?></label></dt>
			<dd class="hikashop_product_msrp">
				<input type="text" id="data_product__product_msrp" name="data[product][product_msrp]" value="<?php echo $this->escape(@$this->product->product_msrp); ?>"/> <?php echo $mainCurr[$this->main_currency_id]->currency_symbol.' '.$mainCurr[$this->main_currency_id]->currency_code;?>
			</dd>
		</dl>
<?php
	if(hikashop_acl('product/edit/price')) {
?>
		<div class="hikashop_product_price"><?php
			echo $this->loadTemplate('price');
		?></div>
<?php
	}
?>
	</div></div>
	<div class="hkc-xl-clear"></div>

<?php 
	if(isset($_COOKIE['hikashop_descWidth_cookie'])) {
		switch ($_COOKIE['hikashop_descWidth_cookie']) {	
			case 'desc_width_small':
			case 'desc_width_mid':
			case 'desc_width_max':
				$cookie_value = $_COOKIE['hikashop_descWidth_cookie'];
			break;
			default;
				$cookie_value = 'desc_width_small';
			break;
		}	
	} else {
		$cookie_value = 'desc_width_small';
	}
	$config = hikashop_config();
	$delay = (int)$config->get('switcher_cookie_retaining_period', 31557600);
	setcookie('hikashop_descWidth_cookie', $cookie_value, time() + $delay, "/");

	if(hikashop_acl('product/edit/description')) { ?>
	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_description <?php echo $cookie_value; ?>" data-id="description"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_description_title"><?php
			echo JText::_('HIKA_DESCRIPTION');
?>			<span onclick="descWidth('<?php echo $delay; ?>'); return false;" class="hikashop_desc_width hikabtn hikabtn-primary" style="display:inline-block;"
			 data-toggle="hk-tooltip" data-title="<?php echo JText::_('HIKA_DESC_WIDTH'); ?>">
				<i class="fas fa-chevron-left fa-2x"></i>
				<i class="fas fa-chevron-right fa-2x"></i>
			</span>
		</div>
		<?php echo $this->editor->display(); ?>
<script type="text/javascript">
window.productMgr.saveProductEditor = function() { <?php echo $this->editor->jsCode(); ?> };

if(!window.localPage) window.localPage = {};
function descWidth(delay) {
	var desc = document.querySelector('.hikashop_product_edit_description');

	if (desc.classList.contains("desc_width_small")) {
		desc.classList.remove("desc_width_small");
		desc.classList.add("desc_width_mid");

		window.hikashop.setCookie('hikashop_descWidth_cookie','desc_width_mid',delay);
		return;
	}
	if (desc.classList.contains("desc_width_mid")) {
		desc.classList.remove("desc_width_mid");
		desc.classList.add("desc_width_max");

		window.hikashop.setCookie('hikashop_descWidth_cookie','desc_width_max',delay);
		return;
	}
	if (desc.classList.contains("desc_width_max")) {
		desc.classList.remove("desc_width_max");
		desc.classList.add("desc_width_small");

		window.hikashop.setCookie('hikashop_descWidth_cookie','desc_width_small',delay);
		return;
	}
}
</script>
		<div style="clear:both"></div>
	</div></div>
<?php } ?>

<?php
	if(!isset($this->product->product_type) || $this->product->product_type != 'variant') {
?>
	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_meta" data-id="meta"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_meta_title"><?php
			echo JText::_('SEO');
		?></div>
		<dl class="hika_options">
<?php
		if(hikashop_acl('product/edit/pagetitle')) { ?>
			<dt class="hikashop_product_pagetitle"><label for="data_product__product_page_title"><?php echo JText::_('PAGE_TITLE'); ?></label></dt>
			<dd class="hikashop_product_pagetitle"><input id="data_product__product_page_title" type="text" style="width:100%" size="45" name="data[product][product_page_title]" value="<?php echo $this->escape(@$this->product->product_page_title); ?>" /></dd>
<?php
		}

		if(hikashop_acl('product/edit/url')) { ?>
			<dt class="hikashop_product_url"><label for="data_product__product_url"><?php echo JText::_('BRAND_URL'); ?></label></dt>
			<dd class="hikashop_product_url"><input id="data_product__product_url" type="text" style="width:100%" size="45" name="data[product][product_url]" value="<?php echo $this->escape(@$this->product->product_url); ?>" /></dd>
<?php
		}

		if(hikashop_acl('product/edit/metadescription')) { ?>
			<dt class="hikashop_product_metadescription"><label for="product_meta_description"><?php echo JText::_('PRODUCT_META_DESCRIPTION'); ?></label></dt>
			<dd class="hikashop_product_metadescription"><textarea id="product_meta_description" style="width:100%" cols="35" rows="2" name="data[product][product_meta_description]"><?php echo $this->escape(@$this->product->product_meta_description); ?></textarea></dd>
<?php
		}

		if(hikashop_acl('product/edit/keywords')) { ?>
			<dt class="hikashop_product_keywords"><label for="product_keywords"><?php echo JText::_('PRODUCT_KEYWORDS'); ?></label></dt>
			<dd class="hikashop_product_keywords"><textarea id="product_keywords" style="width:100%" cols="35" rows="2" name="data[product][product_keywords]"><?php echo $this->escape(@$this->product->product_keywords); ?></textarea></dd>
<?php
		}

		if(hikashop_acl('product/edit/alias')) { ?>
			<dt class="hikashop_product_alias"><label for="data_product__product_alias"><?php echo JText::_('HIKA_ALIAS'); ?></label></dt>
			<dd class="hikashop_product_alias"><input id="data_product__product_alias" type="text" style="width:100%" size="45" name="data[product][product_alias]" value="<?php echo $this->escape(@$this->product->product_alias); ?>" /></dd>
<?php
		}

		if(hikashop_acl('product/edit/canonical')) { ?>
			<dt class="hikashop_product_canonical"><label for="data_product__product_canonical"><?php echo JText::_('PRODUCT_CANONICAL'); ?></label></dt>
			<dd class="hikashop_product_canonical"><input id="data_product__product_canonical" type="text" style="width:100%" size="45" name="data[product][product_canonical]" value="<?php echo $this->escape(@$this->product->product_canonical); ?>"/></dd>
<?php
		}
		if(hikashop_acl('product/edit/condition')) { ?>
			<dt class="hikashop_product_condition"><label for="data_product__product_condition"><?php echo JText::_('HIKA_CONDITION'); ?></label></dt>
			<dd class="hikashop_product_condition">
<?php
			$options = array(
				JHTML::_('hikaselect.option', 'NewCondition', JText::_('HIKA_NEW')),
				JHTML::_('hikaselect.option', 'UsedCondition', JText::_('HIKA_USED')),
				JHTML::_('hikaselect.option', 'RefurbishedCondition', JText::_('HIKA_REFURBISHED')),
				JHTML::_('hikaselect.option', '', JText::_('HIKA_NONE'))
			);
			echo JHTML::_('select.genericlist', $options, 'data[product][product_condition]', 'class="custom-select"', 'value', 'text', @$this->product->product_condition);
?>
			</dd>
<?php
		}
?>
		</dl>
	</div></div>
<?php
	}
?>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_restrictions" data-id="restrictions"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_restrictions_title"><?php
			echo JText::_('RESTRICTIONS_AND_DIMENSIONS');
		?></div>
		<dl class="hika_options">
<?php
	if(hikashop_acl('product/edit/qtyperorder')) { ?>
			<dt class="hikashop_product_qtyperorder">
				<label for="data_product__product_min_per_order"><?php echo JText::_('QUANTITY_PER_ORDER'); ?></label>
<?php
		if(HIKASHOP_BACK_RESPONSIVE)
			echo '<div class="hikashop_product_qtyperorder_dt">'.JText::_('HIKA_QTY_RANGE_TO').'</div>';
?>
			</dt>
			<dd class="hikashop_product_qtyperorder">
				<input type="text" id="data_product__product_min_per_order" name="data[product][product_min_per_order]" value="<?php echo (int)@$this->product->product_min_per_order; ?>" /><?php
					echo ' <label for="data_product__product_max_per_order" style="font-weight:bold">' . JText::_('HIKA_QTY_RANGE_TO') . '</label> ';
					echo $this->quantityType->displayInput('data[product][product_max_per_order]', @$this->product->product_max_per_order);
			?></dd>
<?php
	}

	if(hikashop_acl('product/edit/salestart')) { ?>
			<dt class="hikashop_product_salestart">
				<label for="product_sale_start_img"><?php echo JText::_('PRODUCT_SALE_DATES'); ?></label>
<?php
		if(HIKASHOP_BACK_RESPONSIVE)
			echo '<div class="hikashop_product_salestart_dt">To</div>';
?>
			</dt>
			<dd class="hikashop_product_salestart"><?php
				if(!HIKASHOP_J30)
					echo '<div class="calendarj25" style="display: inline; margin-left: 2px">';

				echo JHTML::_('calendar', hikashop_getDate((@$this->product->product_sale_start?@$this->product->product_sale_start:''),'%Y-%m-%d %H:%M'), 'data[product][product_sale_start]','product_sale_start', hikashop_getDateFormat('%d %B %Y %H:%M'), array('size' => '20', 'showTime' => true));
				if(!HIKASHOP_J30)
					echo '</div>';

				echo ' <label for="product_sale_end_img" class="calendar-separator" style="font-weight:bold">' . JText::_('HIKA_RANGE_TO') . '</label> ';

				if(!HIKASHOP_J30)
					echo '<div class="calendarj25" style="display: inline; margin-left: 2px">';
				echo JHTML::_('calendar', hikashop_getDate((@$this->product->product_sale_end?@$this->product->product_sale_end:''),'%Y-%m-%d %H:%M'), 'data[product][product_sale_end]','product_sale_end', hikashop_getDateFormat('%d %B %Y %H:%M'), array('size' => '20', 'showTime' => true));
				if(!HIKASHOP_J30)
					echo '</div';
			?></dd>
<?php
	}

	if(hikashop_acl('product/edit/acl') && hikashop_level(2)) { ?>
			<dt class="hikashop_product_acl"><label><?php echo JText::_('ACCESS_LEVEL'); ?></label></dt>
			<dd class="hikashop_product_acl"><?php
				$product_access = 'all';
				if(isset($this->product->product_access))
					$product_access = $this->product->product_access;
				echo $this->joomlaAcl->display('data[product][product_access]', $product_access, true, true);
			?></dd>
<?php }

	if(hikashop_acl('product/edit/warehouse')) { ?>
			<dt class="hikashop_product_warehouse"><label for="data_product_product_warehouse_id_text"><?php echo JText::_('WAREHOUSE'); ?></label></dt>
			<dd class="hikashop_product_warehouse"><?php
				echo $this->nameboxType->display(
					'data[product][product_warehouse_id]',
					(int)@$this->product->product_warehouse_id,
					hikashopNameboxType::NAMEBOX_SINGLE,
					'warehouse',
					array(
						'delete' => true,
						'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
					)
				);
			?></dd>
<?php
	}

	if(hikashop_acl('product/edit/weight')) { ?>
			<dt class="hikashop_product_weight"><label for="data_product__product_weight"><?php echo JText::_('PRODUCT_WEIGHT'); ?></label></dt>
			<dd class="hikashop_product_weight">
				<input type="text" id="data_product__product_weight" name="data[product][product_weight]" id="data_product__product_weight" value="<?php echo $this->escape(@$this->product->product_weight); ?>"/>
				<?php echo $this->weight->display('data[product][product_weight_unit]', @$this->product->product_weight_unit, '', 'style="width:93px;"'); ?>
			</dd>
<?php
	}

	if(hikashop_acl('product/edit/volume')) { ?>
			<dt class="hikashop_product_volume"><label for="data_product__product_length"><?php echo JText::_('PRODUCT_VOLUME'); ?></label></dt>
			<dd class="hikashop_product_volume">
				<div class="input-prepend"><?php
					echo str_replace('#MYTEXT#', '<span class="add-on"><i class="hk-icon-14 icon-14-length"></i></span><input size="10" style="width:50px" type="text" id="data_product__product_length" name="data[product][product_length]" value="' . $this->escape(@$this->product->product_length) . '"/>', hikashop_tooltip(JText::_('PRODUCT_LENGTH'), '', '', '#MYTEXT#', '', 0));
				?></div>
				<div class="input-prepend"><?php
					echo str_replace('#MYTEXT#', '<span class="add-on"><i class="hk-icon-14 icon-14-width"></i></span><input size="10" style="width:50px" type="text" id="data_product__product_width" name="data[product][product_width]" value="' . $this->escape(@$this->product->product_width) . '"/>', hikashop_tooltip(JText::_('PRODUCT_WIDTH'), '', '', '#MYTEXT#', '', 0));
				?></div>
				<div class="input-prepend"><?php
					echo str_replace('#MYTEXT#', '<span class="add-on"><i class="hk-icon-14 icon-14-height"></i></span><input size="10" style="width:50px" type="text" id="data_product__product_height" name="data[product][product_height]" value="' . $this->escape(@$this->product->product_height) . '"/>', hikashop_tooltip(JText::_('PRODUCT_HEIGHT'), '', '', '#MYTEXT#', '', 0));
				?></div>
				<?php echo $this->volume->display('data[product][product_dimension_unit]', @$this->product->product_dimension_unit, 'dimension', '', 'class="no-chzn" style="width:93px;"'); ?>
			</dd>
<?php
	}
?>
		</dl>
	</div></div>
	<div class="hkc-xl-clear"></div>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_specifications" data-id="specifications"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_specifications_title"><?php
			echo JText::_('SPECIFICATIONS');
		?></div>
		<dl class="hika_options">
<?php
	if(hikashop_acl('product/edit/characteristics')) { ?>
			<dt class="hikashop_product_characteristics"><label><?php echo JText::_('CHARACTERISTICS'); ?></label></dt>
			<dd id="hikashop_product_characteristics" class="hikashop_product_characteristics"><?php
				echo $this->loadTemplate('characteristic');
			?></dd>
<?php
	}

	if(hikashop_acl('product/edit/related')) { ?>
			<dt class="hikashop_product_related"><label for="data_product_related_text"><?php echo JText::_('RELATED_PRODUCTS'); ?></label></dt>
			<dd class="hikashop_product_related"><?php
				echo $this->nameboxType->display(
					'data[product][related]',
					@$this->product->related,
					hikashopNameboxType::NAMEBOX_MULTIPLE,
					'product',
					array(
						'delete' => true,
						'sort' => true,
						'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
					)
				);
			?></dd>
<?php
	}

	if(hikashop_acl('product/edit/options')) { ?>
			<dt class="hikashop_product_options"><label for="data_product_options_text"><?php echo JText::_('OPTIONS'); ?></label></dt>
			<dd class="hikashop_product_options"><?php
				if(hikashop_level(1)) {
					echo $this->nameboxType->display(
						'data[product][options]',
						@$this->product->options,
						hikashopNameboxType::NAMEBOX_MULTIPLE,
						'product',
						array(
							'delete' => true,
							'sort' => true,
							'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
						)
					);
				} else
					echo hikashop_getUpgradeLink('essential');
			?></dd>
<?php
	}

	if(hikashop_acl('product/edit/bundle')) { ?>
			<dt class="hikashop_product_bundle"><label for="data_product_bundle_text"><?php echo JText::_('BUNDLED_PRODUCTS'); ?></label></dt>
			<dd class="hikashop_product_bundle"><?php
				if(hikashop_level(1)) {
					echo $this->loadTemplate('bundle');
				} else
					echo hikashop_getUpgradeLink('essential');
			?></dd>
<?php
	}
?>
		</dl>
	</div></div>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_display" data-id="display"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_display_title"><?php
			echo JText::_('DISPLAY');
		?></div>
		<dl class="hika_options">
<?php
	if(hikashop_level(1) && $this->config->get('product_contact', 0) == 1 && hikashop_acl('product/edit/contactbtn')) {
?>
			<dt class="hikashop_product_contact_btn"><label><?php echo hikashop_tooltip(JText::_('DISPLAY_CONTACT_BUTTON'), '', '', JText::_('CONTACT_BUTTON'), '', 0); ?></label></dt>
			<dd class="hikashop_product_contact_btn"><?php echo JHTML::_('hikaselect.booleanlist', "data[product][product_contact]" , '',@$this->product->product_contact ); ?></dd>
<?php
	}

	if(hikashop_level(1) && $this->config->get('product_waitlist', 0) == 1 && hikashop_acl('product/edit/waitlistbtn')) { ?>
			<dt class="hikashop_product_waitlist_btn"><label><?php echo JText::_('DISPLAY_WAITLIST_BUTTON'); ?></label></dt>
			<dd class="hikashop_product_waitlist_btn"><?php echo JHTML::_('hikaselect.booleanlist', "data[product][product_waitlist]" , '',@$this->product->product_waitlist ); ?></dd>
<?php
	}

	if(hikashop_acl('product/edit/productlayout')) { ?>
			<dt class="hikashop_product_productlayout"><label><?php echo JText::_('PAGE_LAYOUT'); ?></label></dt>
			<dd class="hikashop_product_productlayout"><?php echo $this->productDisplayType->display('data[product][product_layout]' , @$this->product->product_layout); ?></dd>
<?php
	}

	if(hikashop_acl('product/edit/quantitylayout')) { ?>
			<dt class="hikashop_product_quantitylayout"><label><?php echo hikashop_tooltip(JText::_('QUANTITY_LAYOUT_ON_PRODUCT_PAGE'), '', '', JText::_('QUANTITY_LAYOUT'), '', 0); ?></label></dt>
			<dd class="hikashop_product_quantitylayout"><?php echo $this->quantityDisplayType->display('data[product][product_quantity_layout]' , @$this->product->product_quantity_layout); ?></dd>
<?php
	}

	if(hikashop_level(1) && $this->config->get('product_selection_method', 'generic') == 'per_product' && hikashop_acl('product/edit/option_selection_method')) { ?>
			<dt class="hikashop_product_option_selection_method"><label><?php echo JText::_('PRODUCT_SELECTION_METHOD'); ?></label></dt>
			<dd class="hikashop_product_option_selection_method"><?php
			if(hikashop_level(1)) {
				$options = array(
					JHTML::_('hikaselect.option', 'generic', JText::_('FIELD_SINGLEDROPDOWN')),
					JHTML::_('hikaselect.option', 'check', JText::_('FIELD_CHECKBOX')),
					JHTML::_('hikaselect.option', 'radio', JText::_('FIELD_RADIO')),
					JHTML::_('hikaselect.option', 'per_product', JText::_('ON_A_PER_PRODUCT_BASIS'))
				);
				echo JHTML::_('select.genericlist', $options, 'data[product][product_option_method]', 'class="custom-select"', 'value', 'text', @$this->product->product_option_method);
			} else {
				echo hikashop_getUpgradeLink('essential');
			} ?></dd>
<?php
	}
?>
		</dl>
	</div></div>

<?php
	JPluginHelper::importPlugin('hikashop');
	$app = JFactory::getApplication();
	$html = array();
	$app->triggerEvent('onProductFormDisplay', array( &$this->product, &$html ));

	if((!empty($this->fields) && hikashop_level(1) && hikashop_acl('product/edit/customfields')) || !empty($html)) {
?>
	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_fields" data-id="fields"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_fields_title"><?php
			echo JText::_('FIELDS');
		?></div>
<?php
		if(hikashop_level(1) && !empty($this->fields) && hikashop_acl('product/edit/customfields')) {
			$after = array();
			foreach($this->fields as $fieldName => $oneExtraField) {
				$onWhat = 'onchange';
				if($oneExtraField->field_type == 'radio')
					$onWhat = 'onclick';
				$txt = $this->fieldsClass->display($oneExtraField, @$this->product->$fieldName, 'data[product]['.$fieldName.']', false, ' '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\'product\',0);"');

				if($oneExtraField->field_type == 'hidden') {
					$after[] = $txt;
					continue;
				}
?>
		<dl id="hikashop_product_<?php echo $fieldName; ?>" class="hika_options">
			<dt class="hikashop_product_<?php echo $fieldName; ?>"><label><?php echo $this->fieldsClass->getFieldName($oneExtraField); ?></label></dt>
			<dd class="hikashop_product_<?php echo $fieldName; ?>"><?php
				echo $txt;
			?></dd>
		</dl>
<?php
			}
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

	$html = array();
	$app->triggerEvent('onProductDisplay', array( &$this->product, &$html ) );
	$app->triggerEvent('onProductBlocksDisplay', array(&$this->product, &$html));
	if(!empty($html)){
		echo '<div class="hkc-xl-clear"></div>';
		$i = 0;
		foreach($html as $h){
			$i++;
			echo $h;
			if($i == 3) {
				$i = 0;
				echo '<div class="hkc-xl-clear"></div>';
			}
		}
	}
?>
	</div>

<?php if($this->customize) { ?>
	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_new_block"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_new_block_title"><?php
			echo JText::_('FORM_CUSTOMIZATION');
		?></div>

<?php
	if($this->config->get('form_custom', 1)) {
?>
		<dl class="hika_options">
			<dt class="hikashop_product_new_view_name"><label for="data_product__product_new_view_name"><?php echo JText::_('NEW_BLOCK_NAME'); ?></label></dt>
			<dd class="hikashop_product_new_view_name"><input id="product_new_view_name" type="text" style="width:100%" size="45" name="" value=""/></dd>
		</dl>
		<div class="new_block_button">
			<a href="#" class="btn btn-primary" onclick="window.formCustom.addNewBlock('product_new_view_name', window.productDragOptionsKey); return false;"><?php
					echo JText::_('ADD_NEW_BLOCK');
				?></a>
		</div>

		<div class="new_activate_button">
			<a href="#" class="btn btn-primary" onclick="window.productMgr.toggleCustom(0); return false;"><?php
						echo JText::_('DEACTIVATE_FORM_CUSTOMIZATION');
					?></a>
		</div>
		<div class="reset_block_button">
			<a href="#" class="btn btn-danger" onclick="window.formCustom.reset(window.productDragOptionsKey); return false;"><i class="fa fa-trash"></i> <?php
					echo JText::_('RESET_TO_DEFAULT_VIEW');
				?></a>
			<p><?php echo JText::_('BLOCK_HIDDEN_ACCESS_LEVEL'); ?></p>
		</div>
<?php
	} else {
?>
		<div class="new_activate_button">
			<a href="#" class="btn btn-primary" onclick="window.productMgr.toggleCustom(1); return false;"><?php
				echo JText::_('ACTIVATE_FORM_CUSTOMIZATION');
			?></a>
		</div>
<?php
	}
?>

	</div></div>
<?php
}
?>
	<input type="hidden" name="config_form_custom"  id="config_form_custom" value="<?php echo $this->config->get('form_custom', 1); ?>"/>
	</div>

	<div id="hikashop_product_edition_tab_2" style="display:none;">
		<div id="hikashop_product_variant_list"><?php
			echo $this->loadTemplate('variants');
		?></div>
		<div id="hikashop_product_variant_edition">
		</div>
	</div>
</div>
<?php if(!empty($this->product->product_type) && $this->product->product_type == 'variant' && !empty($this->product->product_parent_id)) { ?>
	<input type="hidden" name="data[product][product_type]" value="<?php echo $this->product->product_type; ?>"/>
	<input type="hidden" name="data[product][product_parent_id]" value="<?php echo (int)$this->product->product_parent_id; ?>"/>
<?php } ?>
	<input type="hidden" name="cancel_action" value="<?php echo @$this->cancel_action; ?>"/>
	<input type="hidden" name="cancel_url" value="<?php echo @$this->cancel_url; ?>"/>
	<input type="hidden" name="product_id" value="<?php echo @$this->product->product_id; ?>"/>
	<input type="hidden" name="cid[]" value="<?php echo @$this->product->product_id; ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="ctrl" value="product"/>
	<input type="hidden" name="product_reset_custom"  id="product_reset_custom" value="0"/>
	<input type="hidden" name="product_areas_order"  id="product_areas_order" value="<?php echo $this->escape($this->config->get('product_areas_order')); ?>"/>
	<input type="hidden" name="product_areas_fields"  id="product_areas_fields" value="<?php echo $this->escape($this->config->get('product_areas_fields')); ?>"/>
	<?php echo JHTML::_('form.token'); ?>
</form>
<script type="text/javascript">
window.productMgr.prepare = function() {
	var w = window, o = w.Oby;
	if(w.productMgr.saveProductEditor) {
		try { w.productMgr.saveProductEditor(); } catch(err){}
	}
	if(window.productMgr.saveVariantEditor) {
		try { window.productMgr.saveVariantEditor(); } catch(err){}
	}
	o.fireAjax("syncWysiwygEditors", null);
};
window.productMgr.toggleCustom = function(newStatus) {
	var input = document.getElementById('config_form_custom');
	input.value = newStatus;
	input.form.task.value = 'apply';
	input.form.submit();
};
</script>
<?php 
if($this->config->get('form_custom', 1)) {
	hikashop_loadJslib('formCustom');
?>
<script type="text/javascript">
window.hikashop.ready( function() {
	window.productDragOptionsKey = window.formCustom.initDragAndDrop({
		customize: <?php echo (int)$this->customize; ?>,
		hide: <?php echo (int)hikashop_level(2); ?>,
	});
});
</script>
<?php
}

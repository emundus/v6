<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$arr = array(
	JHTML::_('select.option', '-1', JText::_('HIKA_INHERIT')),
	JHTML::_('select.option', '1', JText::_('HIKASHOP_YES')),
	JHTML::_('select.option', '0', JText::_('HIKASHOP_NO')),
);
?>
<div id="<?php echo $this->id; ?>" class="hikashop_backend_tile_edition hk-container-fluid">

<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block"><div>
<div class="hikashop_tile_title"><?php
	echo JText::_('HIKA_DETAILS');
?></div>
<dl class="hika_options large">
	<input type="hidden" value="vendor" name="<?php echo $this->name; ?>[content_type]" />

	<dt><?php
		echo JText::_('TYPE_OF_LAYOUT');
	?></dt>
	<dd><?php
		if(!isset($this->element['layout_type']))
			$this->element['layout_type'] = 'inherit';
		echo $this->layoutType->display($this->name.'[layout_type]', @$this->element['layout_type'], $this->js, $this->id);
	?></dd>

	<dt id="<?php echo $this->id; ?>_columns"><?php
		echo JText::_('NUMBER_OF_COLUMNS');
	?></dt>
	<dd id="<?php echo $this->id; ?>_columns_0">
		<input name="<?php echo $this->name; ?>[columns]" type="text" value="<?php echo @$this->element['columns'];?>" />
	</dd>

	<dt><?php
		echo JText::_('NUMBER_OF_ITEMS');
	?></dt>
	<dd>
		<input name="<?php echo $this->name; ?>[limit]" type="text" value="<?php echo @$this->element['limit'];?>" />
	</dd>

	<dt><?php
		echo JText::_('RANDOM_ITEMS');
	?></dt>
	<dd><?php
		if(!isset($this->element['random']))
			$this->element['random'] = '-1';
		echo JHTML::_('hikaselect.radiolist', $arr, $this->name.'[random]' , '', 'value', 'text', @$this->element['random']);
	?></dd>
</dl>
</div></div>

<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block"><div>
<div class="hikashop_tile_title"><?php
	echo JText::_('HIKAM_PARAMS_FOR_IMAGES');
?></div>
<dl class="hikam_options large">
	<dt><?php echo JText::_('IMAGE_X');?></dt>
	<dd>
		<input size="12" name="<?php echo $this->name; ?>[image_width]" type="text" value="<?php echo @$this->element['image_width'];?>" /> px
	</dd>

	<dt><?php echo JText::_('IMAGE_Y');?></dt>
	<dd>
		<input size="12" name="<?php echo $this->name; ?>[image_height]" type="text" value="<?php echo @$this->element['image_height'];?>" /> px
	</dd>

	<dt><?php echo JText::_('HIKAM_IMAGE_FORCESIZE');?></dt>
	<dd><?php
		if(!isset($this->element['image_forcesize']))
			$this->element['image_forcesize'] = '-1';
		echo JHTML::_('hikaselect.radiolist', $arr, $this->name.'[image_forcesize]' , '', 'value', 'text', @$this->element['image_forcesize']);
	?></dd>

	<dt><?php echo JText::_('HIKAM_IMAGE_GRAYSCALE');?></dt>
	<dd><?php
		if(!isset($this->element['image_grayscale']))
			$this->element['image_grayscale'] = '-1';
		echo JHTML::_('hikaselect.radiolist', $arr, $this->name.'[image_grayscale]' , '', 'value', 'text', @$this->element['image_grayscale']);
	?></dd>

	<dt><?php echo JText::_('HIKAM_IMAGE_SCALE');?></dt>
	<dd><?php
		if(!isset($this->element['image_scale']))
			$this->element['image_scale'] = '-1';
			$scale_arr = array(
				JHTML::_('select.option', '-1', JText::_('HIKA_INHERIT')),
				JHTML::_('select.option', '1', JText::_('HIKAM_IMAGE_SCALE_INSIDE')),
				JHTML::_('select.option', '0', JText::_('HIKAM_IMAGE_SCALE_OUTSIDE')),
			);
		echo JHTML::_('hikaselect.radiolist', $scale_arr, $this->name.'[image_scale]' , '', 'value', 'text', @$this->element['image_scale']);
	?></dd>

	<dt><?php echo JText::_('HIKAM_IMAGE_RADIUS');?></dt>
	<dd>
		<input size="12" name="<?php echo $this->name; ?>[image_radius]" type="text" value="<?php echo @$this->element['image_radius'];?>" /> px
	</dd>
</dl>
</div></div>

<div class="hkc-lg-clear"></div>

<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block" data-block="content" data-block-value="vendor"><div>
<div class="hikashop_tile_title"><?php echo JText::_('PARAMS_FOR_VENDORS'); ?></div>
<dl class="hikam_options large">
	<dt><?php echo JText::_('ORDERING_FIELD');?></dt>
	<dd><?php
		if(!isset($this->element['vendor_order']))
			$this->element['vendor_order'] = 'inherit';
		echo $this->orderType->display($this->name.'[vendor_order]', @$this->element['vendor_order'], '#_hikamarket_vendor');
	?></dd>
	<dt><?php
		echo JText::_('ORDERING_DIRECTION');
	?></dt>
	<dd><?php
		if(!isset($this->element['vendor_order_dir']))
			$this->element['vendor_order_dir'] = 'inherit';
		echo $this->orderdirType->display($this->name.'[vendor_order_dir]', @$this->element['vendor_order_dir']);
	?></dd>

	<dt><?php echo JText::_('LINK_TO_VENDOR_PAGE');?></dt>
	<dd><?php
		if(!isset($this->element['link_to_vendor_page']))
			$this->element['link_to_vendor_page'] = '-1';
		echo JHTML::_('hikaselect.radiolist', $arr, $this->name.'[link_to_vendor_page]' , '', 'value', 'text', @$this->element['link_to_vendor_page']);
	?></dd>

	<dt><?php echo JText::_('DISPLAY_VOTE');?></dt>
	<dd><?php
		if(!isset($this->element['show_vote']))
			$this->element['show_vote'] = '-1';
		echo JHTML::_('hikaselect.radiolist',  $arr, $this->name.'[show_vote]', '', 'value', 'text', @$this->element['show_vote']);
	?></dd>

<?php if(hikashop_level(2)) { ?>
	<dt><?php echo JText::_('DISPLAY_CUSTOM_FIELDS');?></dt>
	<dd><?php
		if(!isset($this->element['display_custom_fields']))
			$this->element['display_custom_fields'] = '-1';
		echo JHTML::_('hikaselect.radiolist', $arr, $this->name.'[display_custom_fields]' , '', 'value', 'text', @$this->element['display_custom_fields']);
	?></dd>
<?php } ?>
</dl>
</div></div>

<div class="hkc-xl-clear"></div>

<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block" data-block="layout" data-block-value="div"><div>
<div class="hikashop_tile_title"><?php echo JText::_('PARAMS_FOR_DIV'); ?></div>
<dl class="hikam_options large">

	<dt><?php echo JText::_('TYPE_OF_ITEM_LAYOUT');?></dt>
	<dd><?php
		if(!isset($this->element['div_item_layout_type']))
			$this->element['div_item_layout_type'] = 'inherit';
		echo $this->itemlayoutType->display($this->name.'[div_item_layout_type]', @$this->element['div_item_layout_type'], $this->js, '');
	?></dd>

	<dt><?php echo JText::_('PANE_HEIGHT');?></dt>
	<dd>
		<input size="12" name="<?php echo $this->name; ?>[pane_height]" type="text" value="<?php echo @$this->element['pane_height'];?>" /> px
	</dd>

	<dt><?php echo JText::_('ITEM_BOX_COLOR');?></dt>
	<dd><?php
		echo $this->colorType->displayAll('', $this->name.'[background_color]', @$this->element['background_color']);
	?></dd>

	<dt><?php echo JText::_('ITEM_BOX_MARGIN');?></dt>
	<dd>
		<input name="<?php echo $this->name; ?>[margin]" type="text" value="<?php echo @$this->element['margin'];?>" /> px
	</dd>

	<dt><?php echo JText::_('ITEM_BOX_BORDER');?></dt>
	<dd><?php
		if(!isset($this->element['border_visible']))
			$this->element['border_visible'] = '-1';
		$arr2 = $arr;
		$arr2[] = JHTML::_('select.option', 2, JText::_('THUMBNAIL'));
		echo JHTML::_('hikaselect.radiolist', $arr2, $this->name.'[border_visible]' , '', 'value', 'text', @$this->element['border_visible']);
	?></dd>

	<dt><?php echo JText::_('ITEM_BOX_ROUND_CORNER');?></dt>
	<dd><?php
		if(!isset($this->element['rounded_corners']))
			$this->element['rounded_corners'] = '-1';
		echo JHTML::_('hikaselect.radiolist', $arr, $this->name.'[rounded_corners]' , '', 'value', 'text', @$this->element['rounded_corners']);
	?></dd>

	<dt><?php echo JText::_('TEXT_CENTERED');?></dt>
	<dd><?php
		if(!isset($this->element['text_center']))
			$this->element['text_center'] = '-1';
		echo JHTML::_('hikaselect.radiolist', $arr, $this->name.'[text_center]' , '', 'value', 'text', @$this->element['text_center']);
	?></dd>

</dl>
</div></div>

<div class="hkc-lg-clear"></div>

<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block" data-block="layout" data-block-value="list"><div>
<div class="hikashop_tile_title"><?php echo JText::_('PARAMS_FOR_LIST'); ?></div>
<dl class="hikam_options large">
	<dt><?php echo JText::_('UL_CLASS_NAME');?></dt>
	<dd>
		<input name="<?php echo $this->name; ?>[ul_class_name]" type="text" value="<?php echo @$this->element['ul_class_name'];?>" />
	</dd>
</dl>
</div></div>

<div style="clear:both"></div>
</div>

<script type="text/javascript">
if(!window.localPage)
	window.localPage = {};
window.localPage.switchPanel = function(id, name, type) {
	var d = document, el = null;
	if(type == 'layout') {
		el = d.getElementById(id + '_columns');
		if(el) {
			var v = (name == 'table') ? 'none' : '';
			el.style.display = v;
			el = d.getElementById(id + '_columns_0');
			if(el)
				el.style.display = v;
		}
	}
	var container = d.getElementById(id);
	if(!container)
		return;
	for(var j = container.childNodes.length - 1; j >= 0; j--) {
		e = container.childNodes[j];
		if(e.nodeType && e.getAttribute && e.getAttribute('data-block') == type)
			e.style.display = (e.getAttribute('data-block-value') == name) ? '' : 'none';
	}
};
<?php echo $this->js; ?>
</script>

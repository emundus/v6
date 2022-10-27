<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<?php
	$arr = array(
		JHTML::_('select.option', '-1', JText::_('HIKA_INHERIT')),
		JHTML::_('select.option', '1', JText::_('HIKASHOP_YES')),
		JHTML::_('select.option', '0', JText::_('HIKASHOP_NO')),
	);
?>
<form action="<?php echo hikamarket::completeLink('modules'); ?>" method="POST" name="adminForm" id="adminForm">
<div id="page-modules" class="hk-row-fluid">
	<div class="hkc-6">
				<fieldset class="adminform">
					<legend><?php echo JText::_('HIKA_DETAILS'); ?></legend>

					<table class="admintable table" cellspacing="1">
						<tr>
							<td class="key"><?php
								echo JText::_('HIKA_TITLE');
							?></td>
							<td>
								<input class="text_area" type="text" name="module<?php echo $this->control; ?>[title]" id="title" size="35" value="<?php echo $this->escape(@$this->element->title); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key"><?php
								echo JText::_('SHOW_TITLE');
							?></td>
							<td><?php
								echo JHTML::_('hikaselect.booleanlist', 'module' . $this->control . '[showtitle]', 'class="inputbox"', @$this->element->showtitle);
							?></td>
						</tr>
						<tr>
							<td class="key"><?php
								echo JText::_('HIKA_PUBLISHED');
							?></td>
							<td><?php
								echo JHTML::_('hikaselect.booleanlist', 'module' . $this->control . '[published]', 'class="inputbox"', @$this->element->published);
							?></td>
						</tr>
						<tr>
							<td class="key"><?php
								echo JText::_('TYPE_OF_CONTENT');
							?></td>
							<td><?php
								if(empty($this->element->content_type) || $this->element->content_type != 'vendor')
									$this->element->content_type = 'vendor';
								echo $this->contentType->display($this->control.'[content_type]',@$this->element->content_type, $this->js, true, true);
							?></td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('TYPE_OF_LAYOUT'); ?></td>
							<td><?php
								if(!isset($this->element->hikamarket_params['layout_type']))
									$this->element->hikamarket_params['layout_type'] = 'inherit';
								echo $this->layoutType->display($this->control.'[layout_type]', @$this->element->hikamarket_params['layout_type'], $this->js, true);
							?></td>
						</tr>
						<tr id="number_of_columns">
							<td class="key"><?php echo JText::_('NUMBER_OF_COLUMNS');?></td>
							<td>
								<input name="<?php echo $this->control; ?>[columns]" type="text" value="<?php echo @$this->element->hikamarket_params['columns'];?>" />
							</td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('NUMBER_OF_ITEMS'); ?></td>
							<td>
								<input name="<?php echo $this->control; ?>[limit]" type="text" value="<?php echo @$this->element->hikamarket_params['limit'];?>" />
							</td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('RANDOM_ITEMS');?></td>
							<td><?php
								if(!isset($this->element->hikamarket_params['random']))
									$this->element->hikamarket_params['random'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[random]' , '', 'value', 'text', @$this->element->hikamarket_params['random']);
							?></td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('ORDERING_DIRECTION'); ?></td>
							<td><?php
								if(!isset($this->element->hikamarket_params['order_dir']))
									$this->element->hikamarket_params['order_dir'] = 'inherit';
								echo $this->orderdirType->display($this->control.'[order_dir]',@$this->element->hikamarket_params['order_dir']);
							?></td>
						</tr>
<?php
?>
					</table>
				</fieldset>
				<fieldset data-block="options" data-block-value="image" class="adminform">
					<legend><?php echo JText::_('HIKAM_PARAMS_FOR_IMAGES'); ?></legend>
					<table class="admintable table" cellspacing="1" width="100%">
						<tr>
							<td class="key"><?php echo JText::_('IMAGE_X');?></td>
							<td>
								<input size="12" name="<?php echo $this->control;?>[image_width]" type="text" value="<?php echo @$this->element->hikamarket_params['image_width'];?>" /> px
							</td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('IMAGE_Y');?></td>
							<td>
								<input size="12" name="<?php echo $this->control;?>[image_height]" type="text" value="<?php echo @$this->element->hikamarket_params['image_height'];?>" /> px
							</td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('HIKAM_IMAGE_FORCESIZE');?></td>
							<td><?php
								if(!isset($this->element->hikamarket_params['image_forcesize']))
									$this->element->hikamarket_params['image_forcesize'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[image_forcesize]' , '', 'value', 'text', @$this->element->hikamarket_params['image_forcesize']);
							?></td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('HIKAM_IMAGE_GRAYSCALE');?></td>
							<td><?php
								if(!isset($this->element->hikamarket_params['image_grayscale']))
									$this->element->hikamarket_params['image_grayscale'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[image_grayscale]' , '', 'value', 'text', @$this->element->hikamarket_params['image_grayscale']);
							?></td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('HIKAM_IMAGE_SCALE');?></td>
							<td><?php
								if(!isset($this->element->hikamarket_params['image_scale']))
									$this->element->hikamarket_params['image_scale'] = '-1';
									$scale_arr = array(
										JHTML::_('select.option', '-1', JText::_('HIKA_INHERIT')),
										JHTML::_('select.option', '1', JText::_('HIKAM_IMAGE_SCALE_INSIDE')),
										JHTML::_('select.option', '0', JText::_('HIKAM_IMAGE_SCALE_OUTSIDE')),
									);
								echo JHTML::_('hikaselect.radiolist', $scale_arr, $this->control.'[image_scale]' , '', 'value', 'text', @$this->element->hikamarket_params['image_scale']);
							?></td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('HIKAM_IMAGE_RADIUS');?></td>
							<td>
								<input size="12" name="<?php echo $this->control;?>[image_radius]" type="text" value="<?php echo @$this->element->hikamarket_params['image_radius'];?>" /> px
							</td>
						</tr>
					</table>
				</fieldset>
	</div>
	<div class="hkc-6">
				<fieldset data-block="content" data-block-value="vendor" class="adminform">
					<legend><?php echo JText::_('PARAMS_FOR_VENDORS'); ?></legend>
					<table class="admintable table" cellspacing="1" width="100%">
						<tr>
							<td class="key"><?php echo JText::_('ORDERING_FIELD');?></td>
							<td><?php
								if(!isset($this->element->hikamarket_params['vendor_order']))
									$this->element->hikamarket_params['vendor_order'] = 'inherit';
								echo $this->orderType->display($this->control.'[vendor_order]', @$this->element->hikamarket_params['vendor_order'], '#_hikamarket_vendor');
							?></td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('LINK_TO_VENDOR_PAGE');?></td>
							<td><?php
								if(!isset($this->element->hikamarket_params['link_to_vendor_page']))
									$this->element->hikamarket_params['link_to_vendor_page'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[link_to_vendor_page]' , '', 'value', 'text', @$this->element->hikamarket_params['link_to_vendor_page']);
							?></td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('DISPLAY_VOTE'); ?></td>
							<td><?php
								if(!isset($this->element->hikamarket_params['show_vote']))
									$this->element->hikamarket_params['show_vote'] = '-1';
								echo JHTML::_('hikaselect.radiolist',  $arr, $this->control.'[show_vote]', '', 'value', 'text', @$this->element->hikamarket_params['show_vote']);
							?></td>
						</tr>
<?php if(hikashop_level(2)) { ?>
							<tr>
								<td class="key"><?php echo JText::_('DISPLAY_CUSTOM_FIELDS');?></td>
								<td><?php
									if(!isset($this->element->hikamarket_params['display_custom_fields']))
										$this->element->hikamarket_params['display_custom_fields'] = '-1';
									echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[display_custom_fields]' , '', 'value', 'text', @$this->element->hikamarket_params['display_custom_fields']);
								?></td>
							</tr>
<?php } ?>
					</table>
				</fieldset>
				<fieldset data-block="layout" data-block-value="div" class="adminform">
					<legend><?php echo JText::_('PARAMS_FOR_DIV'); ?></legend>
					<table class="admintable table" cellspacing="1" width="100%">
						<tr>
							<td class="key"><?php echo JText::_('TYPE_OF_ITEM_LAYOUT');?></td>
							<td><?php
								if(!isset($this->element->hikamarket_params['div_item_layout_type']))
									$this->element->hikamarket_params['div_item_layout_type'] = 'inherit';
								echo $this->itemlayoutType->display($this->control.'[div_item_layout_type]', @$this->element->hikamarket_params['div_item_layout_type'], $this->js, 'adminForm');
							?></td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('PANE_HEIGHT');?></td>
							<td>
								<input size="12" name="<?php echo $this->control;?>[pane_height]" type="text" value="<?php echo @$this->element->hikamarket_params['pane_height'];?>" /> px
							</td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('ITEM_BOX_COLOR');?></td>
							<td><?php
								echo $this->colorType->displayAll('',$this->control.'[background_color]',@$this->element->hikamarket_params['background_color']);
							?></td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('ITEM_BOX_MARGIN');?></td>
							<td>
								<input name="<?php echo $this->control;?>[margin]" type="text" value="<?php echo @$this->element->hikamarket_params['margin'];?>" /> px
							</td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('ITEM_BOX_BORDER');?></td>
							<td><?php
								if(!isset($this->element->hikamarket_params['border_visible']))
									$this->element->hikamarket_params['border_visible'] = '-1';
								$arr2 = $arr;
								$arr2[] = JHTML::_('select.option', 2, JText::_('THUMBNAIL'));
								echo JHTML::_('hikaselect.radiolist', $arr2, $this->control.'[border_visible]' , '', 'value', 'text', @$this->element->hikamarket_params['border_visible']);
							?></td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('ITEM_BOX_ROUND_CORNER');?></td>
							<td><?php
								if(!isset($this->element->hikamarket_params['rounded_corners']))
									$this->element->hikamarket_params['rounded_corners'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[rounded_corners]' , '', 'value', 'text', @$this->element->hikamarket_params['rounded_corners']);
							?></td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('TEXT_CENTERED');?></td>
							<td><?php
								if(!isset($this->element->hikamarket_params['text_center']))
									$this->element->hikamarket_params['text_center'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[text_center]' , '', 'value', 'text', @$this->element->hikamarket_params['text_center']);
							?></td>
						</tr>
					</table>
				</fieldset>
				<fieldset data-block="layout" data-block-value="list" class="adminform">
					<legend><?php echo JText::_('PARAMS_FOR_LIST'); ?></legend>
					<table class="admintable table" cellspacing="1" width="100%">
						<tr>
							<td class="key"><?php echo JText::_('UL_CLASS_NAME');?></td>
							<td>
								<input name="<?php echo $this->control;?>[ul_class_name]" type="text" value="<?php echo @$this->element->hikamarket_params['ul_class_name'];?>" />
							</td>
						</tr>
					</table>
				</fieldset>
	</div>
</div>
	<div class="clr"></div>

	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="cid" value="<?php echo (int)@$this->element->id; ?>" />
	<input type="hidden" name="module[id]" value="<?php echo (int)@$this->element->id; ?>" />
	<input type="hidden" name="module[module]" value="<?php echo $this->element->module; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl');?>" />
	<input type="hidden" name="return" value="<?php echo hikaInput::get()->getString('return');?>" />
	<input type="hidden" name="client" value="0" />
	<?php echo JHTML::_('form.token');?>
</form>

<script type="text/javascript">
if(!window.localPage)
	window.localPage = {};
window.localPage.switchPanel = function(id, name, type) {
	var d = document, el = null;
	if(type == 'layout') {
		el = d.getElementById('number_of_columns');
		if(el)
			el.style.display = (name == 'table') ? 'none' : '';
	}
	var container = d.getElementById('adminForm');
	var elements = container.getElementsByTagName("fieldset");
	for(var j = elements.length - 1; j >= 0; j--) {
		e = elements[j];
		if(e.nodeType && e.getAttribute && e.getAttribute('data-block') == type)
			e.style.display = (e.getAttribute('data-block-value') == name) ? '' : 'none';
	}
};
<?php echo $this->js; ?>
</script>

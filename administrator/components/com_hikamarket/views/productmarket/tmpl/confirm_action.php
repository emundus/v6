<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo hikamarket::completeLink('product&task='.$this->action); ?>" method="post" name="adminForm" id="adminForm">
<?php
switch($this->action) {
	case 'approve':
		hikamarket::display(JText::_('HIKAM_CONFIRM_APPROVE_PRODUCTS'), 'success', false, false);
		break;
	case 'decline':
		hikamarket::display(JText::_('HIKAM_CONFIRM_DECLINE_PRODUCTS'), 'error', false, false);
		break;
	case 'remove':
		hikamarket::display(JText::_('HIKAM_CONFIRM_DELETE_PRODUCTS'), 'error', false, false);
		break;
}
?>
<div class="hikashop_backend_tile_edition hk-row-fluid">
<?php
$image_options = array(
  'default' => true,
  'forcesize' => $this->config->get('image_force_size', true),
  'scale' => $this->config->get('image_scale_mode', 'inside')
);

foreach($this->products as $product) {
?>
	<div class="hkc-xl-4 hkc-md-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('PRODUCT'); ?> (<?php echo (int)$product->product_id; ?>)</div>
		<div class="product_stat_img" style="float:left; margin: 5px;"><?php
	$img = $this->imageHelper->getThumbnail(@$product->file_path, array('width' => 50, 'height' => 50), $image_options);
	if($img->success) {
		$attributes = '';
		if($img->external)
			$attributes = ' width="'.$img->req_width.'" height="'.$img->req_height.'"';
		echo '<img class="hikashop_product_image" title="'.$this->escape(@$product->file_description).'" alt="'.$this->escape(@$product->file_name).'" src="'.$img->url.'"'.$attributes.'/>';
	}
		?></div>

		<h4><?php echo $product->product_name; ?></h4>
		<label><input type="checkbox" name="cid[]" value="<?php echo (int)$product->product_id; ?>" checked="checked"/> <?php echo $product->product_code; ?></label>
		<div style="clear:both"></div>

		<dl class="hika_options">
			<dt><?php echo JText::_('HIKA_VENDOR'); ?></dt>
			<dd><?php
				if(empty($product->product_vendor_id))
					echo '<em>'.JText::_('NO_VENDOR').'</em>';
				else if(!empty($product->vendor_name))
					echo $this->escape($product->vendor_name);
				else
					echo '<em>'.JText::_('HIKA_VENDOR').' '.(int)$product->product_vendor_id.'</em>';
			?></dd>
<?php
		if(!empty($this->extra_columns)) {
			foreach($this->extra_columns as $colName => $column) {
?>
			<dt><?php echo (is_array($column) && isset($column['key'])) ? $column['name'] : $column; ?></dt>
			<dd class="hikamarket_product_extra_<?php echo $colName;?>_value"><?php
				if(is_array($column) && isset($column['key']))
					echo $product->{ $column['key'] };
				else
					echo $product->$colName;
			?></dd>
<?php
			}
		}
?>
		</dl>
	</div></div>
<?php
}
?>
</div>
<?php
	if(in_array($this->action, array('approve', 'decline'))) {
?>
<div class="hikashop_backend_tile_edition hk-row-fluid">
	<div class="hkc-md-12 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('HIKAM_EMAIL_NOTIFICATION'); ?></div>
		<dl class="hika_options">
			<dt><?php echo JText::_('HIKAM_SEND_NOTIFICATION'); ?></dt>
			<dd>
				<label><input type="checkbox" name="data[notify][send]" value="1" checked="checked"/> <?php echo JText::_('NOTIFY_VENDOR'); ?></label>
			</dd>

			<dt><?php echo JText::_('MESSAGE'); ?></dt>
			<dd>
				<textarea name="data[notify][msg]" cols="60" rows="6" style="width:100%"></textarea>
			</dd>
		</dl>
	</div></div>
</div>
<?php
	}
?>
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="<?php echo $this->action; ?>" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="boxchecked" value="<?php echo count($this->products); ?>" />
	<input type="hidden" name="confirmation" value="<?php echo $this->confirmation; ?>" />
	<input type="hidden" name="redirect" value="<?php echo hikaInput::get()->getCmd('redirect'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

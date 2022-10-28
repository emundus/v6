<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikamarket_product_listing">
<form action="<?php echo hikamarket::completeLink('product&task=waitingapproval'); ?>" method="post" name="adminForm" id="adminForm">

<div class="hk-row-fluid">
	<div class="hkc-md-12 hikam_search_zone">
<?php
	echo $this->loadHkLayout('search', array(
		'id' => 'hikamarket_products_listing_search',
	));
?>
		<div class="hikam_sort_zone"><?php
			echo JHTML::_('select.genericlist', $this->ordering_values, 'filter_fullorder', 'onchange="this.form.submit();"', 'value', 'text', $this->full_ordering);
		?></div>
	</div>
	<div class="hkc-md-12">
		<div id="hikam_product_filters" class="expand-filters">
<?php
	if(!empty($this->vendorType))
		echo $this->vendorType->display('filter_vendors', @$this->pageInfo->filter->vendors);
?>
		</div>
	</div>
</div>
<?php
	$show_product_image = $this->config->get('front_show_product_image', 1);
	$acl_product_code = hikamarket::acl('product/edit/code');
?>
<div id="hikam_product_main_listing">
<?php
foreach($this->products as $product) {
	$url = ($this->manage) ? hikamarket::completeLink('product&task=edit&cid='.(int)$product->product_id) : hikamarket::completeLink('shop.product&task=show&cid='.(int)$product->product_id);

	if(empty($product->product_name) && !empty($product->parent_product_name))
		$product_name = '<em>'.$this->escape($product->parent_product_name, true).'</em>';
	else if(empty($product->product_name))
		$product_name = '<em>'.JText::sprintf('HIKAM_PRODUCT_NO_NAME', $product->product_code).'</em>';
	else
		$product_name = $this->escape($product->product_name, true);

	$stock_color = 'green';
	$extra_classes = '';
	if($product->product_type == 'waiting_approval')
		$extra_classes .= ' hkm_product_approval';
	if($product->product_quantity < 0) {
		$stock_color = 'blue';
	} else if($product->product_quantity == 0) {
		$stock_color = 'red';
		$extra_classes .= ' hkm_product_no_stock';
	} else if($product->product_quantity < $this->config->get('stock_warning_level', 10)) {
		$stock_color = 'orange';
		$extra_classes .= ' hkm_product_low_stock';
	}
?>
	<div class="hk-card hk-card-default hk-card-product<?php echo $extra_classes; ?>" data-hkm-product="<?php echo (int)$product->product_id; ?>">
		<div class="hk-card-header">
			<a class="hk-row-fluid" href="<?php echo $url; ?>">
				<div class="hkc-sm-6 hkm_product_name"><?php
					echo $product_name;
				?></div>
				<div class="hkc-sm-6 hkm_product_price">
					<i class="fa fa-credit-card"></i> <?php
						if(empty($product->prices))
							echo JText::_('FREE_PRICE');
						else
							echo $this->currencyHelper->displayPrices($product->prices);
					?>
				</div>
			</a>
		</div>
		<div class="hk-card-body">
			<div class="hk-row-fluid">
				<div class="hkc-sm-2 hkm_product_image">
					<a href="<?php echo $url; ?>"><?php
					$thumb = $this->imageHelper->getThumbnail(@$product->file_path, array(50,50), array('default' => 1, 'forcesize' => 1));
					if(!empty($thumb->path) && empty($thumb->external))
						echo '<img src="'. $this->imageHelper->uploadFolder_url . str_replace('\\', '/', $thumb->path).'" class="" alt=""/>';
					else if(!empty($thumb->path) && !empty($thumb->url))
						echo '<img src="'. $thumb->url.'" class="" alt="" width="50" height="50"/>';
					?></a>
				</div>
				<div class="hkc-sm-5 hkm_product_details">
<?php if($acl_product_code) { ?>
					<div class="hkm_product_code">
						<i class="fas fa-tag"></i> <span><?php echo $this->escape($product->product_code); ?></span>
					</div>
<?php } ?>
					<div class="hkm_product_stock">
						<i class="fas fa-cubes"></i> <span class="hk-label hk-label-<?php echo $stock_color; ?>"><?php
							echo ($product->product_quantity == 0) ? JText::_('HIKA_OUT_OF_STOCK') : (($product->product_quantity >= 0) ? $product->product_quantity : JText::_('UNLIMITED'));
						?></span>
					</div>
<?php if(!empty($product->vendor_name)) { ?>
					<div class="hkm_product_vendor">
						<i class="fas fa-user-tie"></i> <?php echo $product->vendor_name; ?>
					</div>
<?php } ?>
<?php if($product->product_type == 'waiting_approval') { ?>
					<div class="hkm_product_approval">
						<i class="far fa-thumbs-up"></i> <span class="hk-label hk-label-orange"><?php echo JText::_('HIKAM_PRODUCT_NOT_APPROVED') ?></span>
					</div>
<?php } else { ?>
					<div class="hkm_product_stats">
						<span class="hkm_product_hit" data-toggle="hk-tooltip" data-title="<?php echo JText::sprintf('HIKAM_X_VIEWS', (int)$product->product_hit); ?>"><i class="far fa-eye"></i> <span><?php echo $this->niceNumber((int)$product->product_hit); ?></span></span>
						/
						<span class="hkm_product_sales" data-toggle="hk-tooltip" data-title="<?php echo JText::sprintf('HIKAM_X_SALES', (int)$product->product_sales); ?>"><i class="fas fa-shopping-cart"></i> <span><?php echo $this->niceNumber((int)$product->product_sales); ?></span></span>
					</div>
<?php } ?>

<?php
	if(!empty($this->fields)) {
		$fields = array();
		foreach($this->fields as $fieldName => $oneExtraField) {
			$r = $this->fieldsClass->show($oneExtraField, $product->$fieldName);
			if(empty($r))
				continue;
			$fields[] = '<dt class="hkm_product_field_'.$fieldName.'">'.$this->fieldsClass->trans($oneExtraField->field_realname).'</dt><dd class="hkm_product_field_'.$fieldName.'">'.$r.'</dd>';
		}
		if(!empty($fields)) {
?>
					<dl class="hikam_options hkm_product_fields"><?php
						echo implode("\r\n", $fields);
						unset($fields);
					?></dl>
<?php
		}
	}
?>

				</div>
				<div class="hkc-sm-3 hkm_product_publish">
<?php
	if($product->product_published) {
?>
						<span class="hk-label hk-label-green"><i class="fas fa-check"></i> <?php echo JText::_('HIKA_PUBLISHED'); ?></span>
<?php
	} else {
?>
						<span class="hk-label hk-label-red"><i class="fas fa-times"></i> <?php echo JText::_('HIKA_UNPUBLISHED'); ?></span>
<?php
	}
?>
				</div>
				<div class="hkc-sm-2 hkm_product_actions"><?php
	$data = array(
		'details' => array(
			'name' => '<i class="fas fa-search"></i> ' . JText::_('HIKA_DETAILS', true),
			'link' => $url
		)
	);
	if($this->product_action_delete)
		$data['delete'] = array(
			'name' => '<i class="fas fa-trash"></i> ' . JText::_('HIKA_DELETE', true),
			'link' => '#delete',
			'click' => 'return window.localPage.deleteProduct('.(int)$product->product_id.', \''.urlencode(strip_tags($product->product_name)).'\');'
		);
	if(!empty($data)) {
		echo $this->dropdownHelper->display(
			JText::_('HIKA_ACTIONS'),
			$data,
			array('type' => '', 'class' => 'hikabtn-primary', 'right' => true, 'up' => false)
		);
	}
				?></div>
			</div>
		</div>
	</div>
<?php
}
?>
	<div class="hikamarket_pagination">
		<?php echo $this->pagination->getListFooter(); ?>
		<?php echo $this->pagination->getResultsCounter(); ?>
	</div>
</div>

	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="waitingapproval" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
<?php if($this->product_action_delete) { ?>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.deleteProduct = function(id, name) {
	var confirmMsg = "<?php echo JText::_('CONFIRM_DELETE_PRODUCT_X'); ?>";
	if(!confirm(confirmMsg.replace('{PRODUCT}', decodeURI(name))))
		return false;
	var f = document.forms['hikamarket_delete_product_form'];
	if(!f) return false;
	f.product_id.value = id;
	f.submit();
	return false;
};
</script>
<form action="<?php echo hikamarket::completeLink('product&task=delete'); ?>" method="post" name="hikamarket_delete_product_form" id="hikamarket_delete_product_form">
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="delete" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="product_id" value="0" />
	<?php echo JHTML::_('form.token'); ?>
</form>
<?php } ?>

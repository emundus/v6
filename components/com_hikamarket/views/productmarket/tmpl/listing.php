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
<form action="<?php echo hikamarket::completeLink('product&task=listing'); ?>" method="post" name="adminForm" id="adminForm">

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
	if(!empty( $this->extrafilters)) {
		foreach($this->extrafilters as $name => $filterObj) {
			if(is_string($filterObj)) {
				echo $filterObj;
			} elseif( is_object($filterObj) && method_exists($filterObj, 'displayFilter')) {
				echo $filterObj->displayFilter($name, $this->pageInfo->filter);
			} elseif( isset($filterObj->filter_html_search)) {
				echo $filterObj->filter_html_search;
			}
		}
	}

	if(!empty($this->vendorType))
		echo $this->vendorType->display('filter_vendors', @$this->pageInfo->filter->vendors);
	if($this->config->get('show_category_explorer', 1))
		echo $this->childdisplayType->display('filter_type', $this->pageInfo->selectedType, false, false);
?>
		</div>
	</div>
</div>
<?php
if(!empty($this->breadcrumb)) {
?>
<div class="hikam_breadcrumb_explorer">
	<div class="hikam_breadcrumb" onclick="window.Oby.toggleClass(this.parentNode, 'explorer_open');">
<?php
	foreach($this->breadcrumb as $i => $breadcrumb) {
		if($i > 0)
			echo '<span class="breadcrumb_sep">/</span>';

		echo '<span class="breadcrumb_el">';
		if($breadcrumb->category_id != $this->cid) {
			echo '<a href="'.hikamarket::completeLink('product&task=listing&cid='.$breadcrumb->category_id).'">'.JText::_($breadcrumb->category_name).'</a>';
		} else {
			echo JText::_($breadcrumb->category_name);
		}
		echo '</span>';
	}
?>
		<span class="breadcrumb_expand_icon"><i class="fas fa-folder-open"></i></span>
	</div>
	<div class="hikam_category_explorer"><?php
		echo $this->shopCategoryType->displayTree('hikam_categories', $this->rootCategory, null, true, true, $this->cid, hikamarket::completeLink('category&task=getTree', false, true));
	?></div>
<script type="text/javascript">
window.hikashop.ready(function(){
var otreeCategories = window.oTrees['hikam_categories'];
otreeCategories.sel(otreeCategories.find(<?php echo $this->cid; ?>));
otreeCategories.callbackSelection = function(tree,id) {
	var d = document, node = tree.get(id);
	if(node.value && node.name) {
		var u = "<?php echo hikamarket::completeLink('product&task=listing&cid=HIKACID', false, false, true);?>";
		window.location = u.replace('HIKACID', node.value);
	}
};
});
</script>
</div>
<?php
}
	$acl_product_code = hikamarket::acl('product/edit/code');
	$acl_product_quantity = hikamarket::acl('product/edit/quantity');

	$publish_content = '<i class="fas fa-check"></i> ' . JText::_('HIKA_PUBLISHED');
	$unpublish_content = '<i class="fas fa-times"></i> ' . JText::_('HIKA_UNPUBLISHED');
?>
<div id="hikam_product_main_listing">
<?php
if(!empty($this->products)) {
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
<?php
	if($this->product_action_sort) {
		if($this->product_action_publish) {
?>
						<a class="hikabtn hikabtn-mini hikabtn-<?php echo ($product->product_published) ? 'success' : 'danger'; ?> hkm_publish_button" data-toggle-state="<?php echo $product->product_published ? 1 : 0; ?>" data-toggle-id="<?php echo $product->product_id; ?>" onclick="return window.localPage.toggleProduct(this);"><?php
							echo ($product->product_published) ? $publish_content : $unpublish_content;
						?></a>
<?php
		} else {
?>
						<span class="hkm_publish_state hk-label hk-label-<?php echo ($product->product_published) ? 'green' : 'red'; ?>"><?php echo ($product->product_published) ? $publish_content : $unpublish_content; ?></span>
<?php
		}
	}
?>
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
<?php
		if($this->product_action_sort) {
?>
				<div class="hkc-sm-3 hkm_product_order">
					<div class="hk-input-group">
						<div class="hk-input-group-prepend">
							<a class="hikabtn" href="#up" data-ordering="-1" data-ordering-id="<?php echo (int)$product->product_id; ?>" onclick="return window.localPage.orderingProduct(this);"><i class="fas fa-arrow-up"></i></a>
						</div>
						<input type="text" class="hk-form-control hkm_order_value" size="3" name="order[<?php echo $product->product_id; ?>]" value="<?php echo (int)@$product->ordering; ?>" />
						<div class="hk-input-group-append">
							<a class="hikabtn" href="#down" data-ordering="1" data-ordering-id="<?php echo (int)$product->product_id; ?>" onclick="return window.localPage.orderingProduct(this);"><i class="fas fa-arrow-down"></i></a>
						</div>
					</div>
				</div>
<?php
		} else {
?>
				<div class="hkc-sm-3 hkm_product_publish">
<?php
			if($this->product_action_publish) {
?>
					<a class="hikabtn hikabtn-<?php echo ($product->product_published) ? 'success' : 'danger'; ?> hkm_publish_button" data-toggle-state="<?php echo $product->product_published ? 1 : 0; ?>" data-toggle-id="<?php echo $product->product_id; ?>" onclick="return window.localPage.toggleProduct(this);"><?php
						echo ($product->product_published) ? $publish_content : $unpublish_content;
					?></a>
<?php
			} else {
?>
					<span class="hkm_publish_state hk-label hk-label-<?php echo ($product->product_published) ? 'green' : 'red'; ?>"><?php echo ($product->product_published) ? $publish_content : $unpublish_content; ?></span>
<?php
			}
?>
				</div>
<?php
	}
?>
				<div class="hkc-sm-2 hkm_product_actions"><?php
		$data = array(
			'details' => array(
				'name' => '<i class="fas fa-search"></i> ' . JText::_('HIKA_DETAILS', true),
				'link' => $url
			)
		);
		if($this->product_action_copy)
			$data['copy'] = array(
				'name' => '<i class="fas fa-copy"></i> ' . JText::_('HIKA_COPY', true),
				'link' => '#copy',
				'click' => 'return window.localPage.copyProduct('.(int)$product->product_id.', \''.urlencode(strip_tags($product->product_name)).'\');'
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
<?php
} else {
?>
	<div class="hk-well hikam_no_products">
		</p><?php echo JText::_('HIKAM_EMPTY_PRODUCT_LISTING'); ?></p>
	</div>
<?php
}
?>
</div>
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="listing" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="cid" value="<?php echo $this->cid; ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
<?php if($this->product_action_publish) { ?>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.toggleProduct = function(el) {
	var w=window, d=document, o=w.Oby,
		state = el.getAttribute('data-toggle-state'),
		id = el.getAttribute('data-toggle-id');
	if(!id) return false;
	var url="<?php echo hikamarket::completeLink('toggle','ajax',true);?>",
		v = (state == 0) ? 1 : 0,
		data=o.encodeFormData({"task":"product_published-"+id,"value":v,"table":"product","<?php echo hikamarket::getFormToken(); ?>":1});
	el.disabled = true;
	if(state == 1) el.innerHTML = "<i class=\"fas fa-spinner fa-pulse\"></i> <?php echo JText::_('HIKA_UNPUBLISHING', true); ?>";
	else el.innerHTML = "<i class=\"fas fa-spinner fa-pulse\"></i> <?php echo JText::_('HIKA_PUBLISHING', true); ?>";
	el.classList.remove("hikabtn-success", "hikabtn-danger");
	o.xRequest(url,{mode:"POST",data:data},function(x,p){
		if(x.responseText && x.responseText == '1')
			state = v;
		el.disabled = false;
		el.setAttribute('data-toggle-state', v);
		if(state == 1) el.innerHTML = "<i class=\"fas fa-check\"></i> <?php echo JText::_('HIKA_PUBLISHED', true); ?>";
		else el.innerHTML = "<i class=\"fas fa-times\"></i> <?php echo JText::_('HIKA_UNPUBLISHED', true); ?>";
		el.classList.add( state ? "hikabtn-success" : "hikabtn-danger" );
	});
};
</script>
<?php } ?>
<?php if($this->product_action_sort) { ?>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.orderingProduct = function(el) {
	var id = el.getAttribute('data-ordering-id'),
		direction = el.getAttribute('data-ordering') == '-1';
	if(!id) return false;
	var block = document.querySelector('[data-hkm-product="'+id+'"]');
	if(!block) return false;
	var input = block.querySelector('input[name="order['+id+']"]');
	if(!input) return false;
<?php if($this->pageInfo->filter->order->value == 'product_category.ordering') { ?>
	var switchBlock = (direction) ? block.previousElementSibling : block.nextElementSibling;
	if(!switchBlock) return false;
	var switchId = switchBlock.getAttribute('data-hkm-product'),
		switchInput = switchBlock.querySelector('input[name="order['+switchId+']"]');
	if(direction)
		block.parentNode.insertBefore(block, switchBlock);
	else
		switchBlock.parentNode.insertBefore(switchBlock, block);
	var i = input.value;
	input.value = switchInput.value;
	switchInput.value = i;
<?php } else { ?>
	var value = parseInt(input.value);
	if(isNaN(value)) value = 1;
	value += (direction ? -1 : 1);
	if(value < 1) value = 1;
	input.value = value;
<?php } ?>
	return false;
};
</script>
<?php } ?>
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
<?php if($this->product_action_copy) { ?>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.copyProduct = function(id, name) {
	var confirmMsg = "<?php echo JText::_('CONFIRM_COPY_PRODUCT_X'); ?>";
	if(!confirm(confirmMsg.replace('{PRODUCT}', decodeURI(name))))
		return false;
	var f = document.forms['hikamarket_copy_product_form'];
	if(!f) return false;
	f.product_id.value = id;
	f.submit();
	return false;
};
</script>
<form action="<?php echo hikamarket::completeLink('product&task=copy'); ?>" method="post" name="hikamarket_copy_product_form" id="hikamarket_copy_product_form">
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="copy" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="product_id" value="0" />
	<?php echo JHTML::_('form.token'); ?>
</form>
<?php } ?>
</div>

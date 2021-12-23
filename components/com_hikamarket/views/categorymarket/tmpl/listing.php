<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="">
<form action="<?php echo hikamarket::completeLink('category&task=listing&cid='.$this->cid); ?>" method="post" name="adminForm" id="adminForm">

<div class="hk-row-fluid">
	<div class="hkc-md-12 hikam_search_zone">
<?php
	echo $this->loadHkLayout('search', array(
		'id' => 'hikamarket_category_listing_search',
	));
?>
		<div class="hikam_sort_zone"><?php
			echo JHTML::_('select.genericlist', $this->ordering_values, 'filter_fullorder', 'onchange="this.form.submit();"', 'value', 'text', $this->full_ordering);
		?></div>
	</div>
</div>
<div class="hk-row-fluid">
	<div class="hkc-md-12">
		<div class="expand-filters" style="width:auto;">
<?php
	echo $this->childdisplayType->display('filter_type', $this->pageInfo->selectedType, false);
?>
		</div>
		<div style="clear:both"></div>
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
			echo '<a href="'.hikamarket::completeLink('category&task=listing&cid='.$breadcrumb->category_id).'">'.JText::_($breadcrumb->category_name).'</a>';
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
		var u = "<?php echo hikamarket::completeLink('category&task=listing&cid=HIKACID', false, false, true); ?>";
		window.location = u.replace('HIKACID', node.value);
	}
};
});
</script>
</div>
<?php
}

	$publish_content = '<i class="fas fa-check"></i> ' . JText::_('HIKA_PUBLISHED');
	$unpublish_content = '<i class="fas fa-times"></i> ' . JText::_('HIKA_UNPUBLISHED');

?>
<div id="hikam_product_main_listing">
<?php
if(!empty($this->categories)) {
	foreach($this->categories as $category) {
		$url = ($this->manage) ? hikamarket::completeLink('category&task=edit&cid='.(int)$category->category_id) : null;
		$browse_url = hikamarket::completeLink('category&task=listing&cid='.(int)$category->category_id);
		$extra_classes = '';
?>
	<div class="hk-card hk-card-default hk-card-category<?php echo $extra_classes; ?>" data-hkm-category="<?php echo (int)$category->category_id; ?>">
		<div class="hk-card-header">
			<div class="hk-row-fluid">
				<a class="hkc-sm-6 hkm_category_name" href="<?php echo $url; ?>"><?php
					echo $category->category_name;
				?></a>
				<a class="hkc-sm-6 hkm_category_children" href="<?php echo $browse_url; ?>"><?php
					if(empty($category->children))
						echo '<i class="far fa-folder"></i>' . JText::_('HIKAM_NO_CHILD_CATEGORY');
					else if($category->children == 1)
						echo '<i class="fas fa-folder-open"></i>' . JText::sprintf('HIKAM_X_CHILD_CATEGORY', $category->children);
					else
						echo '<i class="fas fa-folder-open"></i>' . JText::sprintf('HIKAM_X_CHILD_CATEGORIES', $category->children);
				?></a>
			</div>
		</div>
		<div class="hk-card-body">
			<div class="hk-row-fluid">
				<div class="hkc-sm-2 hkm_category_image">
					<a href="<?php echo $url; ?>"><?php
					$thumb = $this->imageHelper->getThumbnail(@$category->file_path, array(50,50), array('default' => 1, 'forcesize' => 1));
					if(!empty($thumb->path) && empty($thumb->external))
						echo '<img src="'. $this->imageHelper->uploadFolder_url . str_replace('\\', '/', $thumb->path).'" class="" alt=""/>';
					else if(!empty($thumb->path) && !empty($thumb->url))
						echo '<img src="'. $thumb->url.'" class="" alt="" width="50" height="50"/>';
					?></a>
				</div>
				<div class="hkc-sm-5 hkm_category_details">
					<div class="hkm_category_stats">
						<i class="fas fa-cubes"></i> <?php
							if(empty($category->products))
								echo JText::_('HIKAM_NO_PRODUCTS');
							else if($category->products == 1)
								echo JText::sprintf('HIKAM_X_PRODUCT', $category->products);
							else
								echo JText::sprintf('HIKAM_X_PRODUCTS', $category->products);
						?>
					</div>
<?php
		if($this->category_action_sort) {
			if($this->category_action_publish) {
?>
					<div class="hkm_category_publish">
						<a class="hikabtn hikabtn-mini hikabtn-<?php echo ($category->category_published) ? 'success' : 'danger'; ?> hkm_publish_button" data-toggle-state="<?php echo $category->category_published ? 1 : 0; ?>" data-toggle-id="<?php echo $category->category_id; ?>" onclick="return window.localPage.toggleCategory(this);"><?php
							echo ($category->category_published) ? $publish_content : $unpublish_content;
						?></a>
					</div>
<?php
			} else {
?>
					<div class="hkm_category_publish">
						<span class="hkm_publish_state hk-label hk-label-<?php echo ($category->category_published) ? 'green' : 'red'; ?>"><?php echo ($category->category_published) ? $publish_content : $unpublish_content; ?></span>
					</div>
<?php
			}
		}

		if(!empty($this->fields)) {
			$fields = array();
			foreach($this->fields as $fieldName => $oneExtraField) {
				$r = $this->fieldsClass->show($oneExtraField, $category->$fieldName);
				if(empty($r))
					continue;
				$fields[] = '<dt class="hkm_category_field_'.$fieldName.'">'.$this->fieldsClass->trans($oneExtraField->field_realname).'</dt><dd class="hkm_category_field_'.$fieldName.'">'.$r.'</dd>';
			}
			if(!empty($fields)) {
?>
					<dl class="hikam_options hkm_category_fields"><?php
						echo implode("\r\n", $fields);
						unset($fields);
					?></dl>
<?php
			}
		}
?>
				</div>
<?php
		if($this->category_action_sort) {
?>
				<div class="hkc-sm-3 hkm_category_order">
					<div class="hk-input-group">
						<div class="hk-input-group-prepend">
							<a class="hikabtn" href="#up" data-ordering="-1" data-ordering-id="<?php echo (int)$category->category_id; ?>" onclick="return window.localPage.orderingCategory(this);"><i class="fas fa-arrow-up"></i></a>
						</div>
						<input type="text" class="hk-form-control hkm_order_value" size="3" name="order[<?php echo $category->category_id; ?>]" value="<?php echo (int)@$category->category_ordering; ?>" />
						<div class="hk-input-group-append">
							<a class="hikabtn" href="#down" data-ordering="1" data-ordering-id="<?php echo (int)$category->category_id; ?>" onclick="return window.localPage.orderingCategory(this);"><i class="fas fa-arrow-down"></i></a>
						</div>
					</div>
				</div>
<?php
		} else {
?>
				<div class="hkc-sm-3 hkm_category_publish">
<?php
			if($this->category_action_publish) {
?>
					<a class="hikabtn hikabtn-<?php echo ($category->category_published) ? 'success' : 'danger'; ?> hkm_publish_button" data-toggle-state="<?php echo $category->category_published ? 1 : 0; ?>" data-toggle-id="<?php echo $category->category_id; ?>" onclick="return window.localPage.toggleCategory(this);"><?php
						echo ($category->category_published) ? $publish_content : $unpublish_content;
					?></a>
<?php
			} else {
?>
					<span class="hkm_publish_state hk-label hk-label-<?php echo ($category->category_published) ? 'green' : 'red'; ?>"><?php echo ($category->category_published) ? $publish_content : $unpublish_content; ?></span>
<?php
			}
?>
				</div>
<?php
		}
?>
				<div class="hkc-sm-2 hkm_category_actions"><?php
		$data = array(
			'browse' => array(
				'name' => '<i class="fas fa-folder-open"></i> ' . JText::_('HIKA_BROWSE_CATEGORY', true),
				'link' => $browse_url
			)
		);
		if($this->manage) {
			$data['details'] = array(
				'name' => '<i class="fas fa-search"></i> ' . JText::_('HIKA_DETAILS', true),
				'link' => $url
			);
		}
		if($this->category_action_delete && hikamarket::isVendorCategory($category->category_id)) {
			$data[] = '-';
			$data['delete'] = array(
				'name' => '<i class="fas fa-trash"></i> ' . JText::_('HIKA_DELETE', true),
				'link' => '#delete',
				'click' => 'return window.localPage.deleteCategory('.(int)$category->category_id.', \''.urlencode(strip_tags($category->category_name)).'\');'
			);
		}
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
	<div class="hk-well hikam_no_categories">
		<p><?php echo JText::_('HIKAM_EMPTY_CATEGORY_LISTING');	?></p>
	</div>
<?php
}
?>
</div>
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="cid" value="<?php echo $this->cid; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="listing" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php if($this->category_action_publish) { ?>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.toggleCategory = function(el) {
	var w=window, d=document, o=w.Oby,
		state = el.getAttribute('data-toggle-state'),
		id = el.getAttribute('data-toggle-id');
	if(!id) return false;
	var url="<?php echo hikamarket::completeLink('toggle','ajax',true);?>",
		v = (state == 0) ? 1 : 0,
		data=o.encodeFormData({"task":"category_published-"+id,"value":v,"table":"category","<?php echo hikamarket::getFormToken(); ?>":1});
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
<?php if($this->category_action_sort) { ?>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.orderingCategory = function(el) {
	var id = el.getAttribute('data-ordering-id'),
		direction = el.getAttribute('data-ordering') == '-1';
	if(!id) return false;
	var block = document.querySelector('[data-hkm-category="'+id+'"]');
	if(!block) return false;
	var input = block.querySelector('input[name="order['+id+']"]');
	if(!input) return false;
<?php if($this->pageInfo->filter->order->value == 'category.category_ordering') { ?>
	var switchBlock = (direction) ? block.previousElementSibling : block.nextElementSibling;
	if(!switchBlock) return false;
	var switchId = switchBlock.getAttribute('data-hkm-category'),
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
<?php if($this->category_action_delete) { ?>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.deleteCategory = function(id, name) {
	var confirmMsg = "<?php echo JText::_('CONFIRM_DELETE_CATEGORY_X'); ?>";
	if(!confirm(confirmMsg.replace('{CATEGORY}', decodeURI(name))))
		return false;
	var f = document.forms['hikamarket_delete_category_form'];
	if(!f) return false;
	f.category_id.value = id;
	f.submit();
	return false;
};
</script>
<form action="<?php echo hikamarket::completeLink('category&task=delete'); ?>" method="post" name="hikamarket_delete_category_form" id="hikamarket_delete_category_form">
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="delete" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="category_id" value="0" />
	<?php echo JHTML::_('form.token'); ?>
</form>
<?php } ?>
</div>

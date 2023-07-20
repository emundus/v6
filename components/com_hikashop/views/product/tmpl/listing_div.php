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
if( (empty($this->rows) && $this->module && !hikaInput::get()->getVar('hikashop_front_end_main', 0)) || empty($this->pageInfo->elements->total) )
	return;

if(!empty($this->tmpl_ajax) && empty($this->rows)) {
	hikashop_cleanBuffers();
	exit;
}
$zoomHover = $this->params->get('zoom_on_hover', 0);
$classZoom = '';
if ($zoomHover)
	$classZoom = 'hikashop_zoom_hover';
$this->type = '';

$mainDivName = $this->params->get('main_div_name', '');
$enableCarousel = (int)$this->params->get('enable_carousel', 0) && $this->module;
$infinite_scroll = !$enableCarousel && ((int)$this->params->get('infinite_scroll', 0) == 1) && !hikashop_isAmpPage();
$switchMode = !$enableCarousel && (int)$this->params->get('enable_switcher', 0) && (!$infinite_scroll || empty($this->pageInfo->limit->start)) && (!$this->module || hikaInput::get()->getVar('hikashop_front_end_main', 0));
$pagination = false;
if(!$enableCarousel) {
	$pagination = $this->config->get('pagination','bottom');
}
$this->align = (((int)$this->params->get('text_center') == 0) ? 'left' : 'center');

switch((int)$this->params->get('border_visible', 1)) {
	case 1:
		$this->borderClass = 'hikashop_subcontainer_border';
		break;
	case 2:
		$this->borderClass = 'thumbnail';
		break;
	default:
		$this->borderClass = '';
		break;
}

$height = (int)$this->params->get('image_height', 0);
$width = (int)$this->params->get('image_width', 0);

if(empty($width) && empty($height)) {
	$width = $this->image->main_thumbnail_x;
	$height = $this->image->main_thumbnail_y;
}

if(!empty($this->rows)) {
	$row = reset($this->rows);
	$this->image->checkSize($width, $height, $row);
	$this->newSizes = new stdClass();
	$this->newSizes->height = $height;
	$this->newSizes->width = $width;
	$this->image->main_thumbnail_y = $height;
	$this->image->main_thumbnail_x = $width;
}

$columns = max((int)$this->params->get('columns'), 1);

if(in_array($pagination, array('top', 'both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total && !$infinite_scroll) {
	$this->pagination->form = '_top';
?>
<form action="<?php echo str_replace(array('&tmpl=raw', '&tmpl=component'), '', hikashop_currentURL()); ?>" method="post" name="adminForm_<?php echo $mainDivName . $this->category_selected; ?>_top">
	<div class="hikashop_products_pagination hikashop_products_pagination_top">
		<?php echo str_replace(array('&tmpl=raw', '&tmpl=component'),'', $this->pagination->getListFooter($this->params->get('limit'))); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
	</div>
	<input type="hidden" name="filter_order_<?php echo $mainDivName . $this->category_selected; ?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir_<?php echo $mainDivName . $this->category_selected; ?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
<?php
}
$attributes = ($columns > 1 && $this->params->get('consistencyheight', 1)) ? ' data-consistencyheight=".hikashop_subcontainer"' : '';

if(!empty($this->rows)) {
	if ($switchMode) {
		if(isset($_COOKIE['hikashop_switcher_cookie']) && ($_COOKIE['hikashop_switcher_cookie'] == 'display_grid' || $_COOKIE['hikashop_switcher_cookie'] == 'display_list')) {
			$cookie_value = $_COOKIE['hikashop_switcher_cookie'];
		} else {
			$cookie_value = 'display_grid';
		}
		$config = hikashop_config();
		$delay = (int)$config->get('switcher_cookie_retaining_period', 31557600);
		setcookie('hikashop_switcher_cookie', $cookie_value, time() + $delay, "/");
	}

	if(empty($this->tmpl_ajax)) {
?>
<div id="hikashop_products_switcher_<?php echo $mainDivName; ?>" class="hikashop_products <?php echo @$cookie_value; ?>"<?php echo $attributes; ?> itemscope="" itemtype="https://schema.org/itemListElement">
<?php
	}
	if ($switchMode) {

?>	<div class="hikashop_products_listing_switcher">
<?php 
		$css_button = $this->config->get('css_button', 'hikabtn');

		$onclick_params = "'display_grid', 'display_list','".$delay."', 'hikashop_products_switcher_".$mainDivName."'";
		$onclick = 'onclick="window.localPage.switcherDisplay('.$onclick_params.'); return false;" data-toggle="hk-tooltip" data-original-title="'.JText::_( 'HIKASHOP_SWITCHER_GRID' ).'"';
		$attributes = 'class="'.$css_button.' hikashop_switcher_grid" '. $onclick;
		$fallback_url = "";
		$content = '<span class="btnIcon hk-icon">'.
			'<i class="fas fa-th"></i>'.
		'</span>';

		echo $this->loadHkLayout('button', array( 'attributes' => $attributes, 'content' => $content, 'fallback_url' => $fallback_url));

		$onclick_params = "'display_list', 'display_grid','".$delay."', 'hikashop_products_switcher_".$mainDivName."'";
		$onclick = 'onclick="window.localPage.switcherDisplay('.$onclick_params.'); return false;" data-toggle="hk-tooltip" data-original-title="'.JText::_( 'HIKASHOP_SWITCHER_LIST' ).'"';
		$attributes = 'class="'.$css_button.' hikashop_switcher_list" '. $onclick;
		$content = '<span class="btnIcon hk-icon">'.
			'<i class="fas fa-th-list"></i>'.
		'</span>';

		echo $this->loadHkLayout('button', array( 'attributes' => $attributes, 'content' => $content, 'fallback_url' => $fallback_url));	
?>
	</div>
	<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.switcherDisplay = function (oldClass, newClass, delay, target) {
  var element = document.getElementById(target);
	if (element.classList.contains(oldClass))
		return;
	else {
		window.Oby.removeClass(element, newClass);
		window.Oby.addClass(element, oldClass);
		window.hikashop.setCookie('hikashop_switcher_cookie',oldClass,delay);
	}
	if(window.Oby && window.Oby.fireAjax) window.Oby.fireAjax('hkAfterProductListingSwitch', {element:element});
};
	</script>
<?php
	}

	if($this->config->get('show_quantity_field') >= 2 && empty($this->tmpl_ajax)) {
?>
	<form action="<?php echo hikashop_completeLink('product&task=updatecart'); ?>" method="post" name="hikashop_product_form_<?php echo $mainDivName; ?>" enctype="multipart/form-data">
<?php
	}

	if($enableCarousel) {
		$this->setLayout('carousel');
		echo $this->loadTemplate();
	} else {
		$width = (int)(100 / $columns) - 1;
		$current_column = 1;
		$current_row = 1;

		switch($columns) {
			case 12:
			case 6:
			case 4:
			case 3:
			case 2:
			case 1:
				$row_fluid = 12;
				$span = $row_fluid / $columns;
				break;
			case 10:
			case 8:
			case 7:
				$row_fluid = $columns;
				$span = 1;
				break;
			case 5:
				$row_fluid = 10;
				$span = 2;
				break;
			case 9: // special case
				$row_fluid = 10;
				$span = 1;
				break;
		}

		if($row_fluid == 12)
			echo '<div class="hk-row-fluid">';
		else
			echo '<div class="hk-row-fluid hk-row-'.$row_fluid.'">';

		$itemLayoutType = $this->params->get('div_item_layout_type');
		if(empty($itemLayoutType))
			$itemLayoutType = 'img_title';

		foreach($this->rows as $row) {
?>
		<div class="hkc-md-<?php echo (int)$span; ?> hikashop_product hikashop_product_column_<?php echo $current_column; ?> hikashop_product_row_<?php echo $current_row; ?>"
			itemprop="itemList" itemscope="" itemtype="http://schema.org/ItemList">
			<div class="hikashop_container <?php echo $classZoom; ?>">
				<div class="hikashop_subcontainer <?php echo $this->borderClass; ?>">
<?php
			$this->quantityLayout = $this->getProductQuantityLayout($row);
			$this->row =& $row;
			$this->setLayout('listing_' . $itemLayoutType);
			echo $this->loadTemplate();
			unset($this->row);
?>
				</div>
			</div>
		</div>
<?php
			if($current_column >= $columns) {
				$current_row++;
				$current_column = 0;
			}
			$current_column++;
		}

		echo '</div>';
	}
?> <div style="clear:both"></div>
<?php

	if($infinite_scroll && $this->pageInfo->elements->page > 1) {

		global $Itemid;

		$filters_params = '';
		if(!empty($this->filters)){
			$reseted = hikaInput::get()->getVar('reseted');
			$app = JFactory::getApplication();
			foreach($this->filters as $uniqueFitler){
				$name = 'filter_'.$uniqueFitler->filter_namekey;
				$value = hikaInput::get()->getVar($name, null);
				if(is_null($value) || (is_string($value) && !strlen($value))) {
					$cid = hikaInput::get()->getInt("cid",'itemid_'.hikaInput::get()->getInt("Itemid",0));
					$value = $app->getUserState('com_hikashop.'.$cid.'_filter_'.$uniqueFitler->filter_namekey, '');
				}
				if(is_array($value))
					$value = implode('::', $value);
				if($reseted)
					$value = '';
				$filters_params .= '&'.$name . '=' . $value;

				$name .= '_values';
				$value = hikaInput::get()->getVar($name, null);
				if(is_null($value) || (is_string($value) && !strlen($value))) {
					$cid = hikaInput::get()->getInt("cid",'itemid_'.hikaInput::get()->getInt("Itemid",0));
					$value = $app->getUserState('com_hikashop.'.$cid.'_filter_'.$uniqueFitler->filter_namekey.'_values', '');
				}
				if($reseted)
					continue;
				if(is_array($value))
					$value = implode('::', $value);
				if(empty($value))
					continue;

				$filters_params .= '&'.$name . '=' . $value;
			}
		}
		$cid = '';
		if($this->categoryFromURL)
			$cid = '&cid='.(int)(is_array($this->pageInfo->filter->cid) ? reset($this->pageInfo->filter->cid) : $this->pageInfo->filter->cid);
		if(!empty($this->tmpl_ajax)) {
?>
<script type="text/javascript">
window.localPage.infiniteScrollUrl = '<?php echo HIKASHOP_LIVE; ?>index.php?option=com_hikashop&ctrl=product&task=listing<?php echo $cid; ?>&limitstart=HIKAPAGE<?php echo $filters_params; ?>&Itemid=<?php echo (int)$Itemid; ?>&tmpl=<?php echo (HIKASHOP_J30 ? 'raw' : 'component'); ?>';
</script>
<?php
		}
	}
	if($infinite_scroll && empty($this->tmpl_ajax) && $this->pageInfo->elements->page > 1) {
?>
		<div class="hikashop_infinite_scroll" id="<?php echo $mainDivName; ?>_infinite_scroll" data-url="<?php echo HIKASHOP_LIVE; ?>index.php?option=com_hikashop&ctrl=product&task=listing<?php echo $cid; ?>&limitstart=HIKAPAGE<?php echo $filters_params; ?>&Itemid=<?php echo (int)$Itemid; ?>&tmpl=<?php echo (HIKASHOP_J30 ? 'raw' : 'component'); ?>">
			<a href="#" onclick="return window.localPage.infiniteScroll('<?php echo $mainDivName; ?>');">
				<span><?php echo JText::_('HIKA_LOAD_MORE'); ?></span>
			</a>
		</div>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.infiniteScrollEvents = {};
window.localPage.infiniteScrollPage = 1;
window.localPage.infiniteScrollUrl = '<?php echo HIKASHOP_LIVE; ?>index.php?option=com_hikashop&ctrl=product&task=listing<?php echo $cid; ?>&limitstart=HIKAPAGE<?php echo $filters_params; ?>&Itemid=<?php echo (int)$Itemid; ?>&tmpl=<?php echo (HIKASHOP_J30 ? 'raw' : 'component'); ?>';
window.localPage.infiniteScroll = function(container_name) {
	if(window.localPage.infiniteScrollPage <= 0)
		return false;

	var w = window, d = document, o = w.Oby,
		container = d.getElementById(container_name + '_infinite_scroll');

	if(!container)
		return false;
	if(container.loading)
		return false;

	var dataUrl = container.getAttribute('data-url');
	if(dataUrl)
		window.localPage.infiniteScrollUrl = dataUrl;

	container.loading = true;
	o.addClass(container, 'loading');
	var url = window.localPage.infiniteScrollUrl.replace(/HIKAPAGE/g, <?php echo (int)$this->pageInfo->limit->value; ?> * window.localPage.infiniteScrollPage);
	o.xRequest(url, null, function(xhr) {
		if(xhr.responseText.length == 0) {
			window.localPage.infiniteScrollPage = -1;
			container.style.display = 'none';
			container.loading = false;
			o.removeClass(container, 'loading');
			return;
		}
		var div = d.createElement('div');
		window.hikashop.updateElem(div, xhr.responseText);
		var newNode = container.parentNode.insertBefore(div, container);
<?php if($this->params->get('consistencyheight', 1)) { ?>
		if(newNode.getElementsByClassName)
			elems = newNode.getElementsByClassName('hikashop_subcontainer');
		else
			elems = newNode.querySelectorAll('.hikashop_subcontainer');
		if(elems && elems.length) {
			window.hikashop.setConsistencyHeight(elems, 'min');
			setTimeout(function(){ window.hikashop.setConsistencyHeight(elems, 'min'); }, 1000);
		}
<?php } ?>
		o.removeClass(container, 'loading');
		container.loading = false;
		window.localPage.infiniteScrollPage++;

		setTimeout(function(){
<?php if($this->params->get('show_vote')) { ?>
			if(window.hikaVotes)
				initVote(newNode);
			hkjQuery('[data-toggle="hk-tooltip"]').hktooltip({"html": true,"container": "body"});
<?php } ?>
			window.localPage.checkInfiniteScroll('<?php echo $mainDivName; ?>');
		}, 500);
	});
	return false;
};
window.localPage.checkInfiniteScroll = function(container_name) {
	if(window.localPage.infiniteScrollPage < 0)
		return;
	var d = document,
		el = d.getElementById(container_name + '_infinite_scroll');
	if(!el)
		return;
	var top = el.getBoundingClientRect().top;
	if(top > window.innerHeight)
		return;
	window.localPage.infiniteScroll(container_name);
};
window.Oby.addEvent(window, 'scroll', function() {
	window.localPage.checkInfiniteScroll('<?php echo $mainDivName; ?>');
});
window.Oby.addEvent(window, 'resize', function() {
	window.localPage.checkInfiniteScroll('<?php echo $mainDivName; ?>');
});
window.hikashop.ready(function() { window.localPage.checkInfiniteScroll('<?php echo $mainDivName; ?>'); });
</script>
<?php
	}

	if($this->config->get('show_quantity_field') >= 2) {
		$this->ajax = 'if(hikashopCheckChangeForm(\'item\',\'hikashop_product_form_'.$mainDivName.'\')){ return hikashopModifyQuantity(\'\',field,1,\'hikashop_product_form_'.$mainDivName.'\'); } return false;';
		$this->row = new stdClass();
		$this->row->prices = array($this->row);
		$this->row->product_quantity = -1;
		$this->row->product_min_per_order = 0;
		$this->row->product_max_per_order = -1;
		$this->row->product_sale_start = 0;
		$this->row->product_sale_end = 0;
		$this->row->formName = 'hikashop_product_form_'.$this->params->get('main_div_name', '');
		$this->row->prices = array('filler');
		$this->params->set('show_quantity_field', 2);

		$this->setLayout('quantity');
		echo $this->loadTemplate();
		if(!empty($this->ajax) && $this->config->get('redirect_url_after_add_cart', 'stay_if_cart') == 'ask_user') {
?>
		<input type="hidden" name="popup" value="1"/>
<?php
		}
?>
		<input type="hidden" name="hikashop_cart_type_0" id="hikashop_cart_type_0" value="cart"/>
		<input type="hidden" name="add" value="1"/>
		<input type="hidden" name="ctrl" value="product"/>
		<input type="hidden" name="task" value="updatecart"/>
		<input type="hidden" name="return_url" value="<?php echo urlencode(base64_encode(urldecode($this->redirect_url))); ?>"/>
	</form>
<?php
	}
}
if(empty($this->tmpl_ajax)) {
?>
</div>
<?php
}

if(in_array($pagination, array('bottom', 'both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total && !$infinite_scroll) {
	$this->pagination->form = '_bottom';
?>
<form action="<?php echo str_replace(array('&tmpl=raw', '&tmpl=component'), '', hikashop_currentURL()); ?>" method="post" name="adminForm_<?php echo $mainDivName . $this->category_selected; ?>_bottom">
	<div class="hikashop_products_pagination hikashop_products_pagination_bottom">
		<?php echo str_replace(array('&tmpl=raw', '&tmpl=component'),'', $this->pagination->getListFooter($this->params->get('limit'))); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
	</div>
	<input type="hidden" name="filter_order_<?php echo $mainDivName . $this->category_selected; ?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir_<?php echo $mainDivName . $this->category_selected; ?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
<?php
}

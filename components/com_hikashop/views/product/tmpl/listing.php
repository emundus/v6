<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(!$this->module && isset($this->element->category_canonical) && !empty($this->element->category_canonical)) {
	$canonicalUrl = hikashop_cleanURL($this->element->category_canonical);
	if(!empty($this->pageInfo->limit->start)) {
		if(strpos($canonicalUrl, '?')) {
			$canonicalUrl .= '&';
		} else {
			$canonicalUrl .= '?';
		}
		$canonicalUrl .= 'limitstart='.(int)$this->pageInfo->limit->start;
	}
	$doc = JFactory::getDocument();
	$doc->addHeadLink($canonicalUrl, 'canonical');
}

if(!empty($this->tmpl_ajax)) {
	$this->setLayout('listing');
	$layout_type = $this->params->get('layout_type');
	echo $this->loadTemplate($layout_type);
	return;
}

if(hikashop_level(2) && hikaInput::get()->getVar('hikashop_front_end_main', 0) && hikaInput::get()->getVar('task') == 'listing' && $this->params->get('show_compare')) { ?>
<script type="text/javascript">
<!--
var compare_list = {length: 0};
function setToCompareList(product_id,name,elem) {
	var compareBtn = document.getElementById('hikashop_compare_button');
	if(compare_list[product_id]) {
		var old = compare_list[product_id];
		compare_list[product_id] = null;
		compare_list.length--;
		if( elem == null ) elem = old.elem;
		var nn = elem.nodeName.toLowerCase();
		if( nn == 'a' ) {
			elem.innerHTML = "<?php echo JText::_('ADD_TO_COMPARE_LIST', true);?>";
		} else if( nn == 'input' ) {
			if(elem.type.toLowerCase()=='submit')
				elem.value = "<?php echo JText::_('ADD_TO_COMPARE_LIST', true);?>";
			else
				elem.checked = false;
		}
	} else {
		if(compare_list.length < <?php echo (int)$this->config->get('compare_limit', 5); ?> ) {
			compare_list[product_id] = {name: name, elem: elem};
			compare_list.length++;
			var nn = elem.nodeName.toLowerCase();
			if( nn == 'a' ) {
				elem.innerHTML = "<?php echo JText::_('REMOVE_FROM_COMPARE_LIST', true);?>";
			} else if( nn == 'input' ) {
				if(elem.type.toLowerCase()=='submit')
					elem.value = "<?php echo JText::_('REMOVE_FROM_COMPARE_LIST', true);?>";
				else
					elem.checked = true;
			}
		} else {
			alert("<?php echo JText::_('COMPARE_LIMIT_REACHED', true);?>");
			elem.checked = false;
		}
	}
	compareBtn.style.display = (compare_list.length == 0) ? 'none' : '';
	return false;
}
function compareProducts() {
	var url = '';
	for(var k in compare_list) {
		if(!compare_list.hasOwnProperty(k))
			continue;
		if( url != '' ) url += '&';
		url += 'cid[]=' + k;
	}
	window.location = "<?php
		$u = hikashop_completeLink('product&task=compare'.$this->itemid, false, true);
		if( strpos($u,'?')  === false ) {
			echo $u.'?';
		} else {
			echo $u.'&';
		}
	?>" + url;
	return false;
}
window.hikashop.ready(function(){
try{
	document.querySelectorAll('input.hikashop_compare_checkbox').forEach(function(el){
		el.checked = false;
	});
}catch(e){}
});
window.Oby.registerAjax('compare.updated', function(evt){
	var d = document, w = window, o = w.Oby,
		btn = d.getElementById('hikashop_compare_button');
	if(!btn) return;
	btn.style.display = (evt.size == 0) ? 'none': '';
	if(evt.added && (evt.size == 0 || evt.size < <?php echo (int)$this->config->get('compare_limit', 5); ?>))
		return true;
	if(!evt.added && evt.size >= <?php echo (int)$this->config->get('compare_limit', 5); ?>)
		return true;
	var elems = d.querySelectorAll('[data-addToCompare]'), v = null;
	if(!elems) return true;
	elems.forEach(function(e){
		if(!evt.added) {
			e.removeAttribute('disabled');
			o.removeClass(e,'disabled');
			return;
		}
		v = e.getAttribute('data-addToCompare');
		if(evt.list.hasOwnProperty(v))
			return;
		e.setAttribute('disabled', 'disabled');
		o.addClass(e,'disabled');
	});
	return true;
});
//-->
</script>
<?php }

ob_start();
$title_key = 'show_page_heading';
$titleType = 'h1';
if($this->module) {
	$title_key = 'showtitle';
	$titleType = 'h2';
}

$title = (string)$this->params->get($title_key, '');
if((!$this->module || hikaInput::get()->getVar('hikashop_front_end_main', 0)) && $title_key == 'show_page_heading' && $title === '') {
	$params = JComponentHelper::getParams('com_menus');
	$title = $params->get('show_page_heading');
}

if(!empty($title) && hikaInput::get()->getVar('hikashop_front_end_main', 0) && (!$this->module || $this->pageInfo->elements->total)) {
	$name = $this->params->get('page_title');
	if($this->module) {
		$name = $this->params->get('title');
	} elseif($this->params->get('page_heading')) {
		$name = $this->params->get('page_heading');
	}
?>
	<<?php echo $titleType; ?>>
	<?php echo $name; ?>
	</<?php echo $titleType; ?>>
<?php
}


$val = hikaInput::get()->getVar('hikashop_front_end_main',0);
hikaInput::get()->set('hikashop_front_end_main',0);

if(($this->params->get('show_image') && !empty($this->element->file_path)) || ($this->params->get('show_description', !$this->module) && !empty($this->element->category_description))) {
?>
		<div class="hikashop_category_description">
<?php
	if($this->params->get('show_image') && !empty($this->element->file_path)){
		jimport('joomla.filesystem.file');
		if(JFile::exists($this->image->getPath($this->element->file_path,false))){
?>
			<img src="<?php echo $this->image->getPath($this->element->file_path); ?>" class="hikashop_category_image" title="<?php echo $this->escape((string)@$this->element->file_description); ?>" alt="<?php echo $this->escape((string)@$this->element->file_name); ?>"/>
<?php
		}
	}
	if($this->params->get('show_description',!$this->module)&&!empty($this->element->category_description)){
?>
			<div class="hikashop_category_description_content"><?php
				echo JHTML::_('content.prepare',$this->element->category_description);
			?></div>
<?php
	}
?>
		</div>
<?php
}

if(!empty($this->fields)) {
	ob_start();
	$this->fieldsClass->prefix = '';
	foreach($this->fields as $fieldName => $oneExtraField) {
		if(!empty($this->element->$fieldName)) {
?>
			<tr class="hikashop_category_custom_<?php echo $oneExtraField->field_namekey;?>_line">
				<td class="key">
					<span id="hikashop_category_custom_name_<?php echo $oneExtraField->field_id;?>" class="hikashop_category_custom_name">
						<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
					</span>
				</td>
				<td>
					<span id="hikashop_category_custom_value_<?php echo $oneExtraField->field_id;?>" class="hikashop_category_custom_value">
						<?php echo $this->fieldsClass->show($oneExtraField,$this->element->$fieldName); ?>
					</span>
				</td>
			</tr>
<?php
		}
	}
	$custom_fields_html = ob_get_clean();
	if(!empty($custom_fields_html)) {
?>
		<div id="hikashop_category_custom_info_main" class="hikashop_category_custom_info_main">
			<h4><?php echo JText::_('CATEGORY_ADDITIONAL_INFORMATION');?></h4>
			<table class="hikashop_category_custom_info_main">
				<?php echo $custom_fields_html; ?>
			</table>
		</div>
<?php
	}
}
hikaInput::get()->set('hikashop_front_end_main',$val);

$mainInfo = ob_get_clean();
ob_start();

$display_filters = (int)$this->params->get('display_filters', -1);
if($display_filters == -1) {
	$config =& hikashop_config();
	$display_filters = (int)$config->get('show_filters');
}
if(hikashop_level(2) && hikaInput::get()->getVar('hikashop_front_end_main', 0) && (hikaInput::get()->getVar('task','listing')=='listing' || !empty($this->force_display_filter)) && $display_filters == 1) {
	$this->setLayout('filter');
	$htmlFilter = $this->loadTemplate();
}
$task = hikaInput::get()->getCmd('task', '');
$ctrl = hikaInput::get()->getCmd('ctrl', '');

if(!empty($htmlFilter) && $ctrl != 'category') {
	echo $htmlFilter;
	$htmlFilter = '';
}

$filter_type = (int)$this->params->get('filter_type');
$layout_type = $this->params->get('layout_type');
if(empty($layout_type))
	$layout_type = 'div';


$classes = 'hikashop_category_information hikashop_products_listing_main hikashop_product_listing_'.@$this->element->category_id;

if (HIKASHOP_J40) 
	$classes .= ' hika_j4';
else
	$classes .= ' hika_j3';

$attributes = '';
if(!$this->module || hikaInput::get()->getVar('hikashop_front_end_main',0)) {
	$classes .= ' filter_refresh_div';
	$url = hikashop_currentURL();
	$tmpl = 'component';
	if(HIKASHOP_J30) {
		$tmpl = 'raw';
	}
	if(!strpos($url, '&tmpl='.$tmpl.'&filter=1')) {
		if(strpos($url, '?'))
			$url .= '&tmpl='.$tmpl.'&filter=1';
		else
			$url .= '?tmpl='.$tmpl.'&filter=1';
	}
	$attributes = 'data-refresh-class="hikashop_checkout_loading" data-refresh-url="' . $url . '" data-use-url="1"';
}

if($filter_type !== 3) {
	$this->setLayout('listing');
	$html = $this->loadTemplate($layout_type);
	if(!$this->module)
		echo $mainInfo;
	if(!empty($html)){
		if($this->module) echo $mainInfo;
		if(!empty($htmlFilter) && $ctrl == 'category')
			echo $htmlFilter;
?>
	<div class="hikashop_products_listing">
<?php
		if(hikaInput::get()->getVar('hikashop_front_end_main',0) && hikaInput::get()->getVar('task') == 'listing' && $this->params->get('show_compare')) {
			$css_button = $this->config->get('css_button', 'hikabtn');
			$css_button_compare = $this->config->get('css_button_compare', 'hikabtn-compare');
?>
			<div id="hikashop_compare_zone" class="hikashop_compare_zone">
				<a class="<?php echo $css_button . ' ' . $css_button_compare; ?>" id="hikashop_compare_button" style="display:none;" href="#" data-compare-href="<?php echo hikashop_completeLink('product&task=compare'.$this->itemid, false, true); ?>" onclick="if(window.hikashop.compareProducts) { return window.hikashop.compareProducts(this); }"><span><?php
					echo JText::_('COMPARE_PRODUCTS');
				?></span></a>
			</div>
<?php
		}
		echo $html;
?>
	</div>
<?php
	if ((!empty($this->filters)) && (is_array($this->filters)) && (count($this->filters)) && (!empty($this->filter_set))) {
		$classes .=  ' filter_refresh_div_applied';
	}
?>

<?php
	} elseif(( !$this->module || hikaInput::get()->getVar('hikashop_front_end_main',0) ) && ($ctrl == 'product'  || $ctrl == 'category') && $task == 'listing' && !empty($this->filters) && is_array($this->filters) && count($this->filters) && !empty($this->filter_set)) {
		if(!empty($htmlFilter))
			echo $htmlFilter;
		echo '<div class="hk-well hika_no_products"><i class="fa fa-search"></i> ' . JText::_('HIKASHOP_NO_RESULT') . '</div>';
	}
} else if(!empty($this->rows) && !empty($this->categories)) {

	if(!$this->module)
		echo $mainInfo;

	$allrows = $this->rows;

	$pagination = '';
	if((!$this->module || hikaInput::get()->getVar('hikashop_front_end_main',0)) && $this->pageInfo->elements->total) {
		$pagination = $this->config->get('pagination','bottom');
		$this->config->set('pagination', '');
	}

	if((!empty($allrows) || !$this->module || hikaInput::get()->getVar('hikashop_front_end_main',0)) && in_array($pagination, array('top','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total) {
		$this->pagination->form = '_top';
?>
	<form action="<?php echo str_replace(array('&tmpl=raw', '&tmpl=component'), '', hikashop_currentURL()); ?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_top">
		<div class="hikashop_products_pagination hikashop_products_pagination_top">
		<?php echo str_replace(array('&tmpl=raw', '&tmpl=component'), '', $this->pagination->getListFooter($this->params->get('limit'))); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
		</div>
		<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
<?php
	}

	$main_div_name = $this->params->get('main_div_name');
	foreach($this->categories as $category) {
		if(empty($category['products']))
			continue;

		$this->rows = array();
		foreach($allrows as $p) {
			if(in_array($p->product_id, $category['products']))
				$this->rows[] = $p;
		}

		$this->params->set('main_div_name', $main_div_name.'_'.$category['category']->category_id);

		$this->setLayout('listing');
		$html = $this->loadTemplate($layout_type);
		if(!empty($html)) {
			if(!empty($htmlFilter) && $ctrl == 'category')
				echo $htmlFilter;
?>
	<h2><?php echo $category['category']->category_name; ?></h2>
	<div id="<?php echo $main_div_name.'_'.$category['category']->category_id; ?>" class="hikashop_products_listing">
<?php
		if(hikaInput::get()->getVar('hikashop_front_end_main',0) && hikaInput::get()->getVar('task') == 'listing' && $this->params->get('show_compare')) {
			$css_button = $this->config->get('css_button', 'hikabtn');
			$css_button_compare = $this->config->get('css_button_compare', 'hikabtn-compare');
?>
			<div id="hikashop_compare_zone" class="hikashop_compare_zone">
				<a class="<?php echo $css_button . ' ' . $css_button_compare; ?>" id="hikashop_compare_button" style="display:none;" href="#" data-compare-href="<?php echo hikashop_completeLink('product&task=compare'.$this->itemid, false, true); ?>" onclick="if(window.hikashop.compareProducts) { return window.hikashop.compareProducts(this); }"><span><?php
					echo JText::_('COMPARE_PRODUCTS');
				?></span></a>
			</div>
<?php
		}
		echo $html;
?>
	</div>
<?php
		}
	}
	$this->params->set('main_div_name', $main_div_name);
	$this->config->set('pagination', $pagination);
	if((!empty($allrows) || !$this->module || hikaInput::get()->getVar('hikashop_front_end_main',0)) && in_array($pagination,array('bottom','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total) {
		$this->pagination->form = '_bottom';
?>
	<form action="<?php echo str_replace(array('&tmpl=raw', '&tmpl=component'), '', hikashop_currentURL()); ?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_bottom">
		<div class="hikashop_products_pagination hikashop_products_pagination_bottom">
		<?php echo str_replace(array('&tmpl=raw', '&tmpl=component'), '', $this->pagination->getListFooter($this->params->get('limit'))); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
		</div>
		<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
<?php }
}

$html = ob_get_clean();
if(!empty($html) || hikaInput::get()->getVar('hikashop_front_end_main',0)) {
?>
	<div id="<?php echo $this->params->get('main_div_name');?>" class="<?php echo $classes; ?>" <?php echo $attributes; ?>>
<?php
	if(hikaInput::get()->getVar('hikashop_front_end_main',0)) {
?>
		<div class="hikashop_checkout_loading_elem"></div>
		<div class="hikashop_checkout_loading_spinner"></div>
<?php
	}
	echo $html;
?>
	</div>
<?php
}

if(!$this->module){
?>
<div class="hikashop_submodules" style="clear:both">
<?php
	if(!empty($this->modules)){
		jimport('joomla.application.module.helper');
		foreach($this->modules as $module) {
			echo JModuleHelper::renderModule($module);
		}
	}
?>
</div>
<?php
}

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
if(!HIKASHOP_J40 && $this->config->get('carousel_legacy', true)) {
	$this->setLayout('carousel_legacy');
	echo $this->loadTemplate();
	return;
}
hikashop_loadJslib('owl-carousel');
$options = array();

$rows = array();
if($this->params->get('only_if_products','-1')=='-1'){
	$config = hikashop_config();
	$defaultParams = $config->get('default_params');
	$this->params->set('only_if_products',@$defaultParams['only_if_products']);
}
$only_if_products = $this->params->get('only_if_products',0);
foreach($this->rows as $row) {
	if($only_if_products && $row->number_of_products < 1)
			continue;
	$rows[] = $row;
}
$this->rows = $rows;


$nb_products = count($this->rows);
if($nb_products < $this->params->get('columns')) {
	$options['loop'] = false;
	$columns = $this->params->get('columns');
	$products = $this->params->get('item_by_slide');
} else {
	$columns = min($nb_products, $this->params->get('columns'));
	$products = min($nb_products, $this->params->get('item_by_slide'));
}
if(empty($products))
	$products=$columns;


$borderClass="";
if($this->params->get('border_visible', 1) == 1) {
	$borderClass = "hikashop_subcontainer_border";
}
if($this->params->get('border_visible', 1) == 2) {
	$borderClass = "thumbnail";
}


$mainDivName = $this->params->get('main_div_name');
$options['margin'] = (int)$this->params->get('margin');
$options['nav'] = (bool) $this->params->get('display_button');
if($options['nav']) {
	$options['navText'] = "[
			'".JText::_('HIKA_PREVIOUS_CAROUSEL')."',
			'".JText::_('HIKA_NEXT_CAROUSEL')."'
		]";
}
$lang = JFactory::getLanguage();
$options['rtl'] = (bool) $lang->isRTL();
$options['autoplay'] = (bool) $this->params->get('auto_slide');
if($options['autoplay']) {
	$options['autoplayHoverPause'] = true;
	$autoSlideDuration = $this->params->get('auto_slide_duration');
	if(empty($autoSlideDuration))
		$autoSlideDuration = 3000;
	$options['autoplayTimeout'] = $autoSlideDuration;
}
$carouselEffect = $this->params->get('carousel_effect');
if($carouselEffect == 'fade') {
	$options['animateIn'] = "'fadeIn'";
	$options['animateOut'] = "'fadeOut'";
	$products = 1;
}


$options['items'] = $products;

$duration = $this->params->get('carousel_effect_duration');
if(empty($duration))
	$duration = 5000;
$options['smartSpeed'] = $duration/$products;

$pagination_type = $this->params->get('pagination_type');
$options['dots'] = $pagination_type != 'no_pagination';
if($products > 1) {
	$options['slideBy'] = $this->params->get('one_by_one') ? 1 : $products;
	$slideByFor2 = $this->params->get('one_by_one') ? 1 : 2;
	if($options['dots']) {
		$options['responsive'] = '{0:{items:1, slideBy:1, dotsEach: 1}, 768:{items:2, slideBy:'.$slideByFor2.', dotsEach: '.$slideByFor2.'}, 992:{items:'.$products.', dotsEach: '.$products.'}}';
	} else {
		$options['responsive'] = '{0:{items:1, slideBy:1}, 768:{items:2, slideBy:'.$slideByFor2.'}, 992:{items:'.$products.'}}';
	}
}
$top = false;
$bottom = false;
$mainDivClasses = 'hikashop_carousel';
$paginationDivClasses = 'owl-theme';
if($options['dots']) {
	$options['dots'] = true;
	$options['dotsContainer'] = "'#hikashop_carousel_".$mainDivName."_dots'";

	switch($this->params->get('pagination_position')){
		case 'right':
			$mainDivClasses .= ' carousel_main_vertical_pagination carousel_main_vertical_pagination_'.$pagination_type;
			$paginationDivClasses .= ' carousel_vertical_pagination carousel_vertical_pagination_'.$pagination_type;
		case 'inside':
		case 'bottom':
			$bottom = true;
			break;
		case 'left':
			$mainDivClasses .= ' carousel_main_vertical_pagination carousel_main_vertical_pagination_'.$pagination_type;
			$paginationDivClasses .= ' carousel_vertical_pagination carousel_vertical_pagination_'.$pagination_type;
		case 'top':
			$top = true;
			break;
	}
	ob_start();
?>
<div class="<?php echo $paginationDivClasses; ?>">
	<div class="owl-controls">
		<div id="hikashop_carousel_<?php echo $mainDivName; ?>_dots" class="owl-dots">
		</div>
	</div>
</div>
<?php
	$pagination = ob_get_clean();
}
ob_start();
?>
<div class="<?php echo $mainDivClasses; ?>">
	<div id="hikashop_carousel_<?php echo $mainDivName; ?>" class="owl-carousel owl-theme">
<?php
$i = 0;
foreach($this->rows as $row) {
	$i++;
	$this->row =& $row;
	$data ='';
	switch($pagination_type){
		case 'numbers':
			$data = 'data-dot="'.$i.'"';
			break;
		case 'names':
			$data = 'data-dot="'.$i.' '.$this->escape($row->category_name).'"';
			break;
		case 'thumbnails':
			$thumbnailHeight = $this->params->get('pagination_image_height');
			$thumbnailWidth = $this->params->get('pagination_image_width');
			if(empty($thumbnailWidth) && empty($thumbnailHeight)){
				$thumbnailHeight = $this->image->main_thumbnail_y/4;
				$thumbnailWidth = $this->image->main_thumbnail_x/4;
			}
			$img = $this->image->getThumbnail(
				@$this->row->file_path,
				array('width' => $thumbnailWidth, 'height' => $thumbnailHeight),
				array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'))
			);
			if($img->success)
				$data = 'data-dot="<img title=\''.$this->escape((string)@$this->row->file_description).'\' alt=\''.$this->escape((string)@$this->row->file_name).'\' src=\''.$img->url.'\'/>"';
			break;
	}
	if(!empty($data))
		$options['dotsData'] = true;
?>
		<div class="hikashop_carousel_item <?php echo $borderClass; ?> hikashop_subcontainer"  <?php echo $data; ?>>
<?php
	$this->setLayout('listing_'.$this->params->get('div_item_layout_type'));
	echo $this->loadTemplate();
?>
		</div>
<?php
}
?>
	</div>
</div>
<?php
$carousel = ob_get_clean();
?>
<div id="hikashop_carousel_parent_div_<?php echo $mainDivName; ?>" class="hikashop_carousel_parent_div <?php echo $pagination_type; ?>">
<?php
if($top)
	echo $pagination;
echo $carousel;
if($bottom)
	echo $pagination;
?>
</div>
<script type="text/javascript">
window.hikashop.ready(function(){
	hkjQuery('#hikashop_carousel_<?php echo $mainDivName; ?>').owlCarousel({
		loop:true,
<?php
foreach($options as $key => $val){
	if(is_bool($val))
		$val = $val ? 'true' : 'false';
	echo "\t\t".$key.':'.$val.','."\r\n";
}
?>
	});
	window.hikashop.checkConsistency();
});
</script>

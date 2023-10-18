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
$variant_name = '';
$variant_main = '_main';
$display_mode = '';
if(!empty($this->variant_name)) {
	$variant_name = $this->variant_name;
	if(substr($variant_name, 0, 1) != '_')
		$variant_name = '_' . $variant_name;
	$variant_main = $variant_name;
	$display_mode = 'display:none;';
}
?>
<div id="hikashop_product_image<?php echo $variant_main;?>" class="hikashop_global_image_div" style="<?php echo $display_mode;?>">
<!-- MAIN IMAGE -->
	<div id="hikashop_main_image_div<?php echo $variant_name;?>" class="hikashop_main_image_div">
<?php

if(!empty ($this->element->images)) {
	$image = reset($this->element->images);
}
$height = (int)$this->config->get('product_image_y');
$width = (int)$this->config->get('product_image_x');
if(empty($height)) $height = (int)$this->config->get('thumbnail_y');
if(empty($width)) $width = (int)$this->config->get('thumbnail_x');
$divWidth = $width;
$divHeight = $height;
$this->image->checkSize($divWidth, $divHeight, $image);
$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));

$imgMode = (int)$this->config->get('image_slide');
$imgMode_class = '';
if (isset($imgMode)) {
	switch ($imgMode) {
		case 0:
			$imgMode_class = ' hikashop_img_mode_classic';
		break;
		case 1:
			$imgMode_class = ' hikashop_img_mode_slider';
		break;
		case 2:
			$imgMode_class = ' hikashop_img_mode_both';
		break;
		default:
			$imgMode_class = ' hikashop_img_mode_classic';
	}
}
if(!$this->config->get('thumbnail')) {
	if(!empty ($this->element->images)) {
		echo '<img itemprop="image" src="' . $this->image->uploadFolder_url . $image->file_path . '" alt="' . $image->file_name . '" id="hikashop_main_image" style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle" />';
	}
} else {
	$style = '';
	if( !empty($this->element->images) && count($this->element->images) > 1 && !empty($height)) {
		$style = ' style="height:' . ($height + 20) . 'px;"';
	}
	$variant_name = isset($this->variant_name) ? $this->variant_name : '';

	$prev_btn = '';
	$next_btn = '';	
	if( !empty($this->element->images) && count($this->element->images) > 1) {
		$nb_img = count($this->element->images);
		$selected_slide_prev = 'hikashop_slide_prev_active';
		$selected_slide_next = 'hikashop_slide_next_active';	

		foreach($this->element->images as $key => $img) {
			$prev_id = (int)$key - 1;
			$next_id = (int)$key + 1;

			if($key == 0) {
				$prev_id = (int)$nb_img - 1;
			}
			if($key == (int)$nb_img - 1) {
				$next_id = 0;
			}
			if ($key > 0) {
				$selected_slide_prev = '';
				$selected_slide_next = '';
			}
			$prev_btn .= '<a id="hikashop_main_image'.$variant_name.'_prev_'.$key.'" class="hikashop_slide_prev '.$selected_slide_prev.'" onclick="onMouseOverTrigger('.$prev_id.'); return false;">'
				. '<i class="fas fa-chevron-left"></i>'
			. '</a>';
			$next_btn .= '<a id="hikashop_main_image'.$variant_name.'_next_'.$key.'" class="hikashop_slide_next '.$selected_slide_next.'" onclick="onMouseOverTrigger('. $next_id .'); return false;">'
				. '<i class="fas fa-chevron-right"></i>'
			. '</a>';
		}
	}
?>
		<div class="hikashop_product_main_image_thumb<?php echo $imgMode_class; ?>" id="hikashop_image_main_thumb_div<?php echo $variant_name;?>" <?php echo $style;?> >
<?php
	echo $prev_btn;
?>			<div style="<?php if(!empty($divHeight) && !$this->config->get('image_force_size',true)){ echo 'height:'.($divHeight+20).'px;'; } ?>text-align:center;clear:both;" class="hikashop_product_main_image">
				<div style="position:relative;text-align:center;clear:both;margin: auto;" class="hikashop_product_main_image_subdiv">
<?php
	if($this->image->override) {
		echo $this->image->display(@$image->file_path, true, @$image->file_name, 'id="hikashop_main_image'.$variant_name.'" itemprop="image" style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle"','id="hikashop_main_image_link"', $width, $height);
	} else {
		if(empty($this->popup))
			$this->popup = hikashop_get('helper.popup');
		$img = $this->image->getThumbnail(@$image->file_path, array('width' => $width, 'height' => $height), $image_options);
		if(@$img->success) {
			$attributes = 'style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle"';
			if($img->external && $img->req_width && $img->req_height)
				$attributes .= ' width="'.$img->req_width.'" height="'.$img->req_height.'"';
			$html = '<img id="hikashop_main_image'.$variant_name.'" '.$attributes.' title="'.$this->escape((string)@$image->file_description).'" alt="'.$this->escape((string)@$image->file_name).'" src="'.$img->url.'"/>';
			if($this->config->get('add_webp_images', 1) && function_exists('imagewebp') && !empty($img->webpurl)) {
				$html = '
				<picture>
					<source id="hikashop_main_image'.$variant_name.'_webp" srcset="'.$img->webpurl.'" type="image/webp">
					<source id="hikashop_main_image'.$variant_name.'_src" srcset="'.$img->url.'" type="image/'.$img->ext.'">
					'.$html.'
				</picture>
				';
			}

			if(!empty($this->element->badges))
				$html .= $this->classbadge->placeBadges($this->image, $this->element->badges, array('vertical' => 0, 'horizontal' => 0, 'thumbnail' => $img, 'echo' => false));

			$attr = 'title="'.$this->escape((string)@$image->file_description).'"';
			if (!empty ($this->element->images) && count($this->element->images) > 1)
				$attr .= ' onclick="return window.localPage.openImage(\'hikashop_main_image'.$variant_name.'\', \''.$variant_name.'\', event);"';
			echo $this->popup->image($html, $img->origin_url, null, $attr);
		}
	}
?>	
				</div>
			</div>
<?php
	echo $next_btn;
?>		</div>
<?php	
	if(empty($this->variant_name) && !empty($img->origin_url)) {
		if(strpos($img->origin_url, 'http://') === false && strpos($img->origin_url, 'https://') === false) {
			$url = HIKASHOP_LIVE;
			$pieces = parse_url(HIKASHOP_LIVE);
			if(!empty($pieces['path']))
				$url = substr(HIKASHOP_LIVE,0,strrpos(HIKASHOP_LIVE,$pieces['path']));
			$img->origin_url = $url.$img->origin_url;
		}
?>
		<meta itemprop="image" content="<?php echo $img->origin_url; ?>"/>
<?php
	}
}
?>
	</div>
<!-- EO MAIN IMAGE -->
<!-- THUMBNAILS -->
	<div id="hikashop_small_image_div<?php echo $variant_name;?>" class="hikashop_small_image_div">
<?php
	if( !empty($this->element->images) && count($this->element->images) > 1) {
		$firstThunb = true;
		$i = 0;
		foreach($this->element->images as $key => $image) {
			if($this->image->override) {
				echo $this->image->display($image->file_path, 'hikashop_main_image'.$variant_name, $image->file_name, 'class="hikashop_child_image"','', $width,  $height);
				continue;
			}

			if(empty($this->popup))
				$this->popup = hikashop_get('helper.popup');
			$img = $this->image->getThumbnail(@$image->file_path, array('width' => $width, 'height' => $height), $image_options);
			if(empty($img->success))
				continue;

			$id = null;
			$classname = 'hikashop_child_image';
			$thumbnail_class = 'hikashop_thumbnail_'.$key;
			if($firstThunb) {
				$id = 'hikashop_first_thumbnail'.$variant_name;
				$firstThunb = false;
				$classname .= ' hikashop_child_image_active';
				$thumbnail_class .= ' hikashop_active_thumbnail';
			}
			$thumbnail_class = 'class="'.$thumbnail_class.'"';

			$main_image_size = $img->width.', '.$img->height;
			if($img->external && $img->req_width && $img->req_height)
				$main_image_size = $img->req_width.', '.$img->req_height;

			$attr = $thumbnail_class.' title="'.$this->escape((string)@$image->file_description).'" onmouseover="return window.localPage.changeImage('
				. 'this, \'hikashop_main_image'.$variant_name.'\', \''.$img->url.'\', '.$main_image_size.', \''.str_replace(array("'", '"'),
				array("\'", '&quot;'),@$image->file_description).'\', \''.str_replace(array("'", '"'),array("\'", '&quot;'),@$image->file_name).'\', '.$key.');"';
			$html = '<img class="'.$classname.'" title="'.$this->escape((string)@$image->file_description).'" alt="'.$this->escape((string)@$image->file_name).'" src="'.$img->url.'"/>';
			if($this->config->get('add_webp_images', 1) && function_exists('imagewebp') && !empty($img->webpurl)) {
				$html = '
				<picture>
					<source srcset="'.$img->webpurl.'" type="image/webp">
					<source srcset="'.$img->url.'" type="image/'.$img->ext.'">
					'.$html.'
				</picture>
				';
			}
			if(empty($variant_name)) {
				echo $this->popup->image($html, $img->origin_url, $id, $attr, array('gallery' => 'hikashop_main_image'));
			} else {
				echo $this->popup->image($html, $img->origin_url, $id, $attr, array('gallery' => 'hikashop_main_image_VARIANT_NAME'));
			}
		}
	}
?>
	</div>
<!-- EO THUMBNAILS -->
</div>
<?php
if(empty($variant_name)) {
?>
<script type="text/javascript">
if(!window.localPage)
	window.localPage = {};
if(!window.localPage.images)
	window.localPage.images = {};
window.localPage.changeImage = function(el, id, url, width, height, title, alt, ref) {
	var d = document, target = d.getElementById(id), w = window, o = window.Oby;
	if(!target) return false;
	target.src = url;
	target.width = width;
	target.height = height;
	target.title = title;
	target.alt = alt;

	var target_src = d.getElementById(id+'_src');
	if(target_src) {
		target_src.srcset = url;
	}
	var target_webp = d.getElementById(id+'_webp');
	if(el.firstChild.tagName == 'picture') {
		if(target_webp) {
			target_webp.srcset = url.substr(0, url.lastIndexOf(".")) + '.webp';
		}
	} else if(target_webp) {
		target_webp.remove();
	}

	var thumb_img = null, thumbs_div = d.getElementById('hikashop_small_image_div');
	if(thumbs_div) {
		thumbs_img = thumbs_div.getElementsByTagName('img');
		if(thumbs_img) {
			for(var i = thumbs_img.length - 1; i >= 0; i--) {
				o.removeClass(thumbs_img[i], 'hikashop_child_image_active');
			}
		}
	}
	thumb_img = el.getElementsByTagName('img');
	if(thumb_img) {
		for(var i = thumb_img.length - 1; i >= 0; i--) {
			o.addClass(thumb_img[i], 'hikashop_child_image_active');
		}
	}

	window.localPage.images[id] = el;

	var active_thumb = document.querySelector('.hikashop_active_thumbnail');

	var curr_prev = document.querySelector('.hikashop_slide_prev_active');
	var curr_next = document.querySelector('.hikashop_slide_next_active');
	var next_prev = document.querySelector('#'+id+'_prev_'+ref);
	var next_next = document.querySelector('#'+id+'_next_'+ref);

	curr_prev.classList.remove('hikashop_slide_prev_active');
	curr_next.classList.remove('hikashop_slide_next_active');
	next_prev.classList.add('hikashop_slide_prev_active');
	next_next.classList.add('hikashop_slide_next_active');

	active_thumb.classList.remove("hikashop_active_thumbnail");
	el.classList.add("hikashop_active_thumbnail");

		return false;
};
window.localPage.openImage = function(id, variant_name, e) {
	if(!variant_name) variant_name = '';
	if(!window.localPage.images[id])
		window.localPage.images[id] = document.getElementById('hikashop_first_thumbnail' + variant_name);

	e = e || window.event;
	e.stopPropagation();
	e.cancelBubble = true;
	window.Oby.cancelEvent(e);
	window.localPage.images[id].click();
	return false;
};
function onMouseOverTrigger(a) {
	var element = document.querySelector('.hikashop_thumbnail_'+a);
	element.onmouseover();
}


document.addEventListener('touchstart', handleTouchStart, false);
document.addEventListener('touchmove', handleTouchMove, false);

var xDown = null;
var yDown = null;

function getTouches(evt) {
	return evt.touches || evt.originalEvent.touches;
}
function handleTouchStart(evt) {
	const firstTouch = getTouches(evt)[0];
	xDown = firstTouch.clientX;
	yDown = firstTouch.clientY;
}
function handleTouchMove(evt) {
	if ( ! xDown || ! yDown ) {
		return;
	}
	var xUp = evt.touches[0].clientX;
	var yUp = evt.touches[0].clientY;
	var xDiff = xDown - xUp;
	var yDiff = yDown - yUp;
	if ( Math.abs( xDiff ) > Math.abs( yDiff ) ) {
		if ( xDiff > 0 ) {

			var next = document.querySelector('.hikashop_slide_next_active');
			if (next) {
				next.onclick();
			}
		} else {

			var prev = document.querySelector('.hikashop_slide_prev_active');
			if (prev) {
				prev.onclick();
			}
		}
	}

	xDown = null;
	yDown = null;
}
</script>
<?php
}

<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_category_image">
	<a href="<?php echo $this->row->link;?>" title="<?php echo $this->escape($this->row->category_name); ?>">
		<?php
		$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));
		$img = $this->image->getThumbnail(@$this->row->file_path, array('width' => $this->image->main_thumbnail_x, 'height' => $this->image->main_thumbnail_y), $image_options);
		if($img->success) {
			$html = '<img class="hikashop_product_listing_image" title="'.$this->escape(@$this->row->file_description).'" alt="'.$this->escape(@$this->row->file_name).'" src="'.$img->url.'"/>';
			if($this->config->get('add_webp_images', 1) && function_exists('imagewebp') && !empty($img->webpurl)) {
				$html = '
				<picture>
					<source srcset="'.$img->webpurl.'" type="image/webp">
					<source srcset="'.$img->url.'" type="image/'.$img->ext.'">
					'.$html.'
				</picture>
				';
			}
			echo $html;
		}
		?>
	</a>
</div>
<?php
if($this->rows[0]->category_id == $this->row->category_id){
	$mainDivName = $this->params->get('main_div_name');
?>
<style>
	#<?php echo $mainDivName; ?> .hikashop_category_image{
		height:<?php echo $this->image->main_thumbnail_y;?>px;
		text-align:center;
		clear:both;
	}
</style>
<?php } ?>

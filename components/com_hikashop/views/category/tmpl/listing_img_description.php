<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
	$mainDivName = $this->params->get('main_div_name', '');
?>
<div class="hk-row-fluid" id="<?php echo $mainDivName.'_'.$this->row->category_id; ?>">
	<div class="hkc-sm-4 hhikashop_category_left_part">
		<!-- CATEGORY IMG -->
		<div class="hikashop_product_image">
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
		<!--EO CATEGORY IMG -->
	</div>
	<div class="hkc-sm-8 hikashop_category_right_part">
		<h2>
			<!-- CATEGORY NAME -->
			<span class="hikashop_category_name">
				<a href="<?php echo $this->row->link;?>">
					<?php
					echo $this->row->category_name;
					if($this->params->get('number_of_products',0))
						echo ' ('.$this->row->number_of_products.')';
					?>
				</a>
			</span>
			<!-- EO CATEGORY NAME -->
		</h2>
		<!-- CATEGORY DESC -->
		<span class="hikashop_category_desc">
			<?php echo preg_replace('#<hr *id="system-readmore" */>.*#is','',$this->row->category_description); ?>
		</span>
		<!-- EO CATEGORY DESC -->
	</div>
</div>
<?php
if($this->rows[0]->category_id == $this->row->category_id){
	$mainDivName = $this->params->get('main_div_name');
?>
<style>
	#<?php echo $mainDivName; ?> .hikashop_category_left_part{
		text-align:center
	}
	#<?php echo $mainDivName; ?> .hikashop_product_image{
		height:<?php echo $this->image->main_thumbnail_y;?>px;
		width:<?php echo $this->image->main_thumbnail_x;?>px;
		text-align:center;
		margin:auto
	}
	#<?php echo $mainDivName; ?> .hikashop_category_td{
		width: <?php echo (int)$this->image->main_thumbnail_x+30;?>px
	}
	#<?php echo $mainDivName; ?> .hikashop_category_desc{
		text-align:<?php echo $this->align; ?>
	}
</style>
<?php } ?>

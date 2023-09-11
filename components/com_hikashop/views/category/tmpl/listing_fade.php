<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_fade_effect"  <?php echo ($this->params->get('link_to_product_page',1))?'onclick = "window.location.href=\''.$this->row->link.'\'"':''; ?> >
 	<div class="hikashop_fade_effect_picture" >
		<!-- CATEGORY IMG -->
			<div class="hikashop_product_image">
				<a href="<?php echo $this->row->link;?>" title="<?php echo $this->escape($this->row->category_name); ?>">
					<?php
					$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));
					$img = $this->image->getThumbnail(@$this->row->file_path, array('width' => $this->image->main_thumbnail_x, 'height' => $this->image->main_thumbnail_y), $image_options);
					if($img->success) {
						$html = '<img class="hikashop_product_listing_image" title="'.$this->escape((string)@$this->row->file_description).'" alt="'.$this->escape((string)@$this->row->file_name).'" src="'.$img->url.'"/>';
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
		<!-- EO CATEGORY IMG -->
		<div class="hikashop_img_pane_panel">
			<!-- CATEGORY NAME -->
			<span class="hikashop_category_name">
				<a href="<?php echo $this->row->link;?>">
					<?php
					echo $this->row->category_name;

					if($this->params->get('number_of_products',0)){
						echo ' ('.$this->row->number_of_products.')';
					}
					?>
				</a>
			</span>
			<!-- EO CATEGORY NAME -->
		</div>
	</div>
	<!-- CATEGORY NAME -->
	<span class="hikashop_category_name">
		<a href="<?php echo $this->row->link;?>">
			<?php echo $this->row->category_name; ?>
		</a>
	</span>
	<!-- EO CATEGORY NAME -->

	<!-- CATEGORY DESCRIPTION -->
	<?php if($this->params->get('show_description_listing',0)){ ?>
		<div class="hikashop_category_desc">
			<?php echo preg_replace('#<hr *id="system-readmore" */>.*#is','',$this->row->category_description); ?>
		</div>
	<?php } ?>
	<!-- EO CATEGORY DESCRIPTION -->
</div>
<?php
if($this->rows[0]->category_id == $this->row->category_id){
	$mainDivName = $this->params->get('main_div_name');
	$duration=$this->params->get('product_effect_duration',1000);
?>
<style>
	#<?php echo $mainDivName; ?> .hikashop_fade_effect{
		margin: auto; text-align:center;
		<?php
		if($this->params->get('link_to_product_page',1))
			echo 'cursor:pointer;';
		?>
		height:<?php echo $this->height; ?>px;
		width:<?php echo $this->width; ?>px;
		overflow:hidden;
		position:relative;
	}
	#<?php echo $mainDivName; ?> .hikashop_fade_effect_picture{
		<?php
		if($this->params->get('background_color',0))
			echo 'background-color:'.$this->params->get('background_color',0).';';
		?>
		position:absolute;
		height:<?php echo $this->height; ?>px;
		width:<?php echo $this->width; ?>px;
	}
	#<?php echo $mainDivName; ?> .hikashop_product_image{
		height:<?php echo $this->image->main_thumbnail_y;?>px;
		width:<?php echo $this->image->main_thumbnail_x;?>px;
		text-align:center;
		margin:auto
	}
	#<?php echo $mainDivName; ?> .hikashop_img_pane_panel{
		width:<?php echo $this->width; ?>px;
		<?php
		if($this->params->get('pane_height','') != '')
			 echo 'height:'.$this->params->get('pane_height').'px';
		?>
	}
	#<?php echo $mainDivName; ?> .hikashop_category_desc{
		height:<?php echo $this->height; ?>px;
		text-align:<?php echo $this->align; ?>;
		overflow:hidden;
	}
	#<?php echo $mainDivName; ?> .hikashop_fade_effect_picture{
		opacity: 1;
		-webkit-transition: opacity <?php echo (int)$duration / 1000; ?>s ease-in;
		-moz-transition: opacity <?php echo (int)$duration / 1000; ?>s ease-in;
		-o-transition: opacity <?php echo (int)$duration / 1000; ?>s ease-in;
		transition: opacity <?php echo (int)$duration / 1000; ?>s ease-in;
	}
	#<?php echo $mainDivName; ?> .hikashop_fade_effect_picture:hover{
		opacity: 0;
	}
</style>
<?php
}
?>

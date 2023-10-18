<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset id="hikashop_banner_listing" class="hikashop_banner_listing adminform">
	<legend><?php echo JText::_('BANNER_SAMPLES'); ?></legend>
	<table class="hikashop_affiliate_banners_table table table-striped table-hover" cellspacing="1" width="100%">
	<?php
		$k = 1;
		foreach($this->banners as $banner){
			if(!empty($banner->banner_url)){
				if(strpos($banner->banner_url,'?')){
					$banner->banner_url.='&';
				}else{
					$banner->banner_url.='?';
				}
				$banner->banner_url.=$this->partner_info;
			}
		?>
		<tr class="hikashop_banner_row sectiontableentry<?php echo $k; ?>">
			<td>
				<ul>
					<?php
					if(!empty($banner->banner_comment)){
						echo '<li>'.JText::_('COMMENT').': '.str_replace('{id}',$this->partner_id,$banner->banner_comment).'</li>';
					}
					if(!empty($banner->banner_url)){
						echo '<li>'.JText::_('LINK').': <a target="_blank" href="'.$this->escape($banner->banner_url).'">'.$banner->banner_url.'</a></li>';
					}
					if(!empty($banner->banner_image_url)){
						echo '<li>'.JText::_('HIKA_IMAGE').': <a target="_blank" href="'.$this->escape($banner->banner_image_url).'">'.$banner->banner_image_url.'</a></li>';
					}
					if(!empty($banner->banner_url)){
					?>
					<li>
						<?php echo JText::_('HTML_CODE').':';?>
						<br/>
						<textarea cols="71" onclick="this.select();"><?php
							$content = '';
							if(!empty($banner->banner_title)){
								$content = $banner->banner_title;
							}
							if(!empty($banner->banner_image_url)){
								if(!empty($content)){
									$content = 'title="'.$this->escape($content).'" ';
								}
								$content = '<img '.$content.'src="'.$banner->banner_image_url.'"/>';
							}
							$html = '<a target="_blank" href="'.$this->escape($banner->banner_url).'">'.$content.'</a>';
							echo $html;
						?></textarea>
					</li>
					<li>
						<?php echo JText::_('PREVIEW').': '.$html;?>
					</li>
					<?php } ?>
				</ul>
			</td>
		</tr>
		<?php
		if($k==1){
			$k=2;
		}else{
			$k=1;
		}
	}?>
	</table>
</fieldset>
<div class="clear_both"></div>

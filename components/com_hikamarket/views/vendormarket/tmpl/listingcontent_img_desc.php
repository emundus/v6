<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$link = hikamarket::completeLink('vendor&task=show&cid='.$this->row->vendor_id.'&name='.$this->row->alias . $this->menu_id);
if($this->shopConfig->get('thumbnail',1)) {
	$image = null;
	if(!empty($this->row->vendor_image))
		$image = $this->imageHelper->getThumbnail($this->row->vendor_image, $this->image_size, $this->image_options);
	if(empty($image) || !$image->success)
		$image = $this->default_vendor_image;
}
?>
<table>
	<tr>
<?php if($this->shopConfig->get('thumbnail',1)) { ?>
		<td>
			<div style="height:<?php echo $image->height; ?>px;width:<?php echo $image->width; ?>px;text-align:center;clear:both;" class="hikamarket_vendor_image">
<?php
	if($this->params->get('link_to_vendor_page')) {
?>
				<a href="<?php echo $link;?>" title="<?php echo $this->escape($this->row->vendor_name); ?>">
<?php
	}
?>
					<img src="<?php echo $image->url; ?>" alt="<?php echo $this->escape($this->row->vendor_name); ?>"/>
<?php
	if($this->params->get('link_to_vendor_page')) {
?>
				</a>
<?php
	}
?>
			</div>
		</td>
<?php } ?>
		<td valign="top">
			<div>
				<h2>
					<span class="hikamarket_vendor_name">
<?php
	if($this->params->get('link_to_vendor_page')) {
?>
						<a href="<?php echo $link;?>" title="<?php echo $this->escape($this->row->vendor_name); ?>">
<?php
	}

	echo $this->row->vendor_name;
	if($this->params->get('number_of_products', 0)) {
		echo ' ('.$this->row->number_of_products.')';
	}

	if($this->params->get('link_to_vendor_page')) {
?>
						</a>
<?php
	}
?>
					</span>
				</h2>

				<span class="hikamarket_vendor_desc" style="text-align:<?php echo @$this->align; ?>;"><?php
					echo preg_replace('#<hr *id="system-readmore" */>.*#is','',$this->row->vendor_description);
				?></span>
			</div>
		</td>
	</tr>
</table>

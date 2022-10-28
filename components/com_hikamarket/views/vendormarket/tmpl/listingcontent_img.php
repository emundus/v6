<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$link = hikamarket::completeLink('vendor&task=show&cid='.$this->row->vendor_id.'&name='.$this->row->alias . $this->menu_id);
$image = null;
if(!empty($this->row->vendor_image))
	$image = $this->imageHelper->getThumbnail($this->row->vendor_image, $this->image_size, $this->image_options);
if(empty($image) || !$image->success)
	$image = $this->default_vendor_image;
?>
<div style="height:<?php echo $image->height; ?>px;text-align:center;clear:both;" class="hikamarket_vendor_image">
<?php if($this->params->get('link_to_vendor_page')) { ?>
	<a href="<?php echo $link;?>" title="<?php echo $this->escape($this->row->vendor_name); ?>">
<?php } ?>
		<img src="<?php echo $image->url; ?>" alt="<?php echo $this->escape($this->row->vendor_name); ?>"/>
<?php if($this->params->get('link_to_vendor_page')) { ?>
	</a>
<?php } ?>
</div>

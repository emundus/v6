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
$type = (!empty($this->params->product_type) && $this->params->product_type == 'variant') ? 'variant' : 'product';
?><a href="#delete" class="deleteImg" onclick="return window.productMgr.delImage(this, '<?php echo $type; ?>');"><i class="fas fa-trash-alt"></i></a>
<div class="hikamarket_image">
<?php
	$size_x = (int)$this->config->get('product_edition_image_x', 100);
	if($size_x <= 20) $size_x = 100;
	$size_y = (int)$this->config->get('product_edition_image_y', 100);
	if($size_y <= 20) $size_y = 100;
	if(empty($this->params->file_id))
		$this->params->file_id = 0;
	$image = $this->imageHelper->getThumbnail(@$this->params->file_path, array($size_x, $size_y), array('default' => true));
	if(!empty($image) && $image->success) {
		$extra_data = '';
		if(!empty($image->external))
			$extra_data .= 'width="100" height="100;"';

		$content = '<img src="'.$image->url.'" alt="'.$image->filename.'" '.$extra_data.'/>';
	} else {
		$content = '<img src="" alt="'.@$this->params->file_name.'" />';
	}
	echo $this->popup->display(
		$content,
		'MARKET_IMAGE',
		hikamarket::completeLink('product&task=image&cid='.@$this->params->file_id.'&pid='.@$this->params->product_id,true),
		'',
		750, 460, 'onclick="return window.productMgr.editImage(this, '.$this->params->file_id.', \''.$type.'\');"', '', 'link'
	);
?>
</div><input type="hidden" name="data[<?php echo $type; ?>][product_images][]" value="<?php echo @$this->params->file_id;?>"/>

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
$type = (!empty($this->params->product_type) && $this->params->product_type == 'variant') ? 'variant' : 'product';
?><a href="#delete" class="deleteImg" onclick="return window.productMgr.delImage(this, '<?php echo $type; ?>');" title="<?php echo JText::_('HIKA_DELETE'); ?>">
	<span class="fa-stack">
		<i class="fas fa-circle fa-stack-1x" style="color:white"></i>
		<i class="fa fa-times-circle fa-stack-1x"></i>
	</span>
</a>
<div class="hikashop_image">
<?php
	if(empty($this->params->file_id))
		$this->params->file_id = 0;
	$image = $this->imageHelper->getThumbnail(@$this->params->file_path, array(100, 100), array('default' => true, 'forcesize' => true));
	if(!empty($image) && $image->success) {
		$attributes = '';
		if($image->external)
			$attributes = ' width="'.$image->req_width.'" height="'.$image->req_height.'"';
		$content = '<img src="'.$image->url.'" alt="'.$image->filename.'"'.$attributes.' />';
	} else {
		$content = '<img src="" alt="'.@$this->params->file_name.'" />';
	}
	echo $this->popup->display(
		$content,
		'HIKASHOP_IMAGE',
		hikashop_completeLink('product&task=selectimage&cid='.@$this->params->file_id.'&pid='.@$this->params->product_id,true),
		'',
		750, 460, 'onclick="return window.productMgr.editImage(this, '.$this->params->file_id.', \''.$type.'\');"', '', 'link'
	);
?>
</div><input type="hidden" name="data[<?php echo $type; ?>][product_images][]" value="<?php echo @$this->params->file_id;?>"/>

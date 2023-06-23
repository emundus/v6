<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php $image = $this->imageHelper->getThumbnail(@$this->params->file_path, array(100, 100), array('default' => true)); ?>
<div>
	<a href="#delete" class="deleteImg" onclick="return window.hkUploaderList['hikashop_category_image'].delImage(this);"><img src="<?php echo HIKASHOP_IMAGES; ?>cancel.png" border="0"></a>
	<div class="hikashop_image">

<?php
	echo $this->popup->display(
		'<img src="' . $image->url. '" alt="' . $image->filename. '"/>',
		'HIKASHOP_IMAGE',
		hikashop_completeLink('category&task=selectimage&cid='.@$this->params->file_id.'&pid='.@$this->params->category_id,true),
		'',
		750, 460, 'onclick="return window.categoryMgr.editImage(this, '.$this->params->file_id.', \'category\');"', '', 'link'
	);
?>
	</div><input type="hidden" name="data[category][category_image]" value="<?php echo @$this->params->file_id;?>"/>
</div>

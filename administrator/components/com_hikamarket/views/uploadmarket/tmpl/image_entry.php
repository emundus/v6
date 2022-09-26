<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div>
<?php if(!empty($this->params->delete) && !empty($this->params->uploader_id)) { ?>
	<a href="#delete" class="deleteImg" onclick="return window.hkUploaderList['<?php echo $this->params->uploader_id; ?>'].delImage(this);"><img src="<?php echo HIKAMARKET_IMAGES; ?>icon-16/delete.png" border="0"></a>
<?php } ?>
	<div class="hikamarket_image"><?php
	$img = $this->imageHelper->getThumbnail(@$this->params->file_path, array(100, 100), null);
	if($img->success) {
		$content = '<img src="'.$img->url.'" alt="'.$img->filename.'" />';
		echo $this->popup->image($content, $img->origin_url);
	}

	if(!empty($this->params->field_name))
		echo '<input type="hidden" name="'.$this->params->field_name.'" value="'.$this->escape(@$this->params->file_path).'"/>';
	?></div>
</div>

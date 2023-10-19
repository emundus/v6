<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div>
<?php
if(!empty($this->params->delete) && !empty($this->params->uploader_id)) {
	$p = '';
	if(!empty($this->params->field_name))
		$p = ',\'' . $this->params->field_name . '\'';
?>
	<a href="#delete" class="deleteImg" onclick="return window.hkUploaderList['<?php echo $this->params->uploader_id; ?>'].delImage(this<?php echo $p;?>);" title="<?php echo JText::_('HIKA_DELETE'); ?>">
		<span class="fa-stack">
				<i class="fas fa-circle fa-stack-1x" style="color:white"></i>
				<i class="fa fa-times-circle fa-stack-1x"></i>
		</span>
	</a>
<?php
}
?>
	<div class="hikashop_image"><?php
		if(empty($this->params->thumbnail_url)) {
			$img = $this->imageHelper->getThumbnail(@$this->params->file_path, array(100, 100), array('default' => true));
			if($img->success) {
				$content = '<img src="'.$img->url.'" alt="'.$img->filename.'" />';
				echo $this->popup->image($content, $img->origin_url);
			}
		} else {
			$content = '<img src="' . $this->params->thumbnail_url . '" alt="'.@$this->params->file_path . '" />';
			echo $this->popup->image($content, $this->params->origin_url);
		}

		if(!empty($this->params->field_name))
			echo '<input type="hidden" name="'.$this->params->field_name.'" value="'.$this->escape((string)@$this->params->file_path).'"/>';
		if(!empty($this->params->extra_fields)) {
			foreach($this->params->extra_fields as $key => $value) {
				echo '<input type="hidden" name="'.$this->escape($key).'" value="'.$this->escape($value).'"/>';
			}
		}
	?></div>
</div>

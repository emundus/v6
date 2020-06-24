<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_upload_file">
<?php
	if(!empty($this->params->delete) && !empty($this->params->uploader_id)) {
		$p = '';
		if(!empty($this->params->field_name))
			$p = ',\'' . $this->params->field_name . '\'';
?>
	<a href="#delete" class="deleteFile" onclick="return window.hkUploaderList['<?php echo $this->params->uploader_id; ?>'].delBlock(this<?php echo $p;?>);" title="<?php echo JText::_('HIKA_DELETE'); ?>"><i class="fa fa-times"></i></a>
<?php
	}

if(!empty($this->params->file_name)) {
	$content = $this->params->file_name;
	if(!empty($this->params->origin_url)) {
		$content = '<a href="'.$this->params->origin_url.'">'.$content.'</a>';
	}
?>
	<span class="file_name" style="white-space:nowrap"><?php echo $content; ?></span><br/>
	<span class="file_size"><?php
		$u = array('B','KB','MB','GB','TB','PB');
		echo sprintf('%01.2f', @round($this->params->file_size/pow(1024,($i=floor(log($this->params->file_size,1024)))),2)).'&nbsp;'.$u[$i];
	?></span>
<?php
}

if(!empty($this->params->field_name))
	echo '<input type="hidden" name="'.$this->params->field_name.'" value="'.$this->escape(@$this->params->file_path).'"/>';
if(!empty($this->params->extra_fields)) {
	foreach($this->params->extra_fields as $key => $value) {
		echo '<input type="hidden" name="'.$this->escape($key).'" value="'.$this->escape($value).'"/>';
	}
}
?>
</div>

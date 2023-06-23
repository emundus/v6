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
?><div class="hikashop_product_file">
	<a href="#delete" class="deleteFile" onclick="return window.productMgr.delFile(this, '<?php echo $type; ?>');">
		<span class="fa-stack">
			<i class="fas fa-circle fa-stack-1x" style="color:white"></i>
			<i class="fa fa-times-circle fa-stack-1x"></i>
		</span>
	</a>
<?php
	if(!empty($this->params->file_free_download)) {
		echo '';
	} else {
		echo '';
	}
	if(empty($this->params->file_name))
		$this->params->file_name = JText::_('HIKA_NONE');
?>
	<span class="file_name" style="white-space:nowrap"><?php
		if(empty($this->params->file_id))
			$this->params->file_id = 0;
		echo $this->popup->display(
			$this->params->file_name,
			'HIKASHOP_FILE',
			hikashop_completeLink('product&task=selectfile&cid='.$this->params->file_id.'&pid='.$this->params->product_id,true),
			'',
			750, 460, 'onclick="return window.productMgr.editFile(this, '.$this->params->file_id.', '.$this->params->product_id.', \''.$type.'\');"', '', 'link'
		);
	?></span>
	<div style="clear:both"></div>
	<span class="file_path_text"><?php echo JText::_('FILENAME');?>:&nbsp;</span><span class="file_path"><?php
		echo hikashop_limitString($this->params->file_path, 24, '...', true);
	?></span><br/>
	<span class="file_limit_text"><?php echo JText::_('DOWNLOADS');?>: </span><span class="file_limit"><?php
		echo (int)@$this->params->download_number . ' / ';

		if(!isset($this->params->file_limit) || (int)$this->params->file_limit == 0)
			echo '<em>'.$this->config->get('download_number_limit').'</em>';
		else if((int)$this->params->file_limit > 0)
			echo $this->params->file_limit;
		else
			echo JText::_('UNLIMITED');
	?></span><br/>
	<span class="file_free_text"><?php echo JText::_('FREE_DOWNLOAD');?>: </span><span class="file_free"><?php
		echo ( !empty($this->params->file_free_download) ? JText::_('JYES') : JText::_('JNO') );
	?></span>
	<input type="hidden" name="data[<?php echo $type; ?>][product_files][]" value="<?php echo $this->params->file_id; ?>"/>
</div>

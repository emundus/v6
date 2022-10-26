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
$type = (!empty($this->params->product_type) && $this->params->product_type == 'variant') ? 'variant' : 'product';
?><div class="hikamarket_product_file">
	<a href="#delete" class="deleteFile" onclick="return window.productMgr.delFile(this, '<?php echo $type; ?>');"><i class="fas fa-trash-alt"></i></a>
<?php
	if(!empty($this->params->file_free_download)) {
		echo '';
	} else {
		echo '';
	}
?>
	<span class="file_name" style="white-space:nowrap"><?php
		if(empty($this->params->file_id))
			$this->params->file_id = 0;
		if(empty($this->params->file_name))
			$this->params->file_name = '<em>' . JText::_('HIKA_NONE') . '</em>';

		echo $this->popup->display(
			$this->params->file_name,
			'MARKET_FILE',
			hikamarket::completeLink('product&task=file&cid='.$this->params->file_id.'&pid='.$this->params->product_id,true),
			'',
			750, 460, 'onclick="return window.productMgr.editFile(this, '.$this->params->file_id.', '.$this->params->product_id.', \''.$type.'\');"', '', 'link'
		);
	?></span><br/>
	<span class="file_path_text"><?php echo JText::_('FILENAME');?>:&nbsp;</span><span class="file_path"><?php
		if($this->vendor->vendor_id > 1) {
			$start = 'vendor' . $this->vendor->vendor_id;
			if(substr($this->params->file_path, 0, strlen($start)) == $start)
				$this->params->file_path = substr($this->params->file_path, strlen($start)+1);
		}
		echo hikamarket::limitString($this->params->file_path, 24, '...', true);
	?></span><br/>
	<span class="file_limit_text"><?php echo JText::_('DOWNLOADS');?>: </span><span class="file_limit"><?php
		if($this->vendor->vendor_id <= 1)
			echo (int)@$this->params->download_number . ' / ';

		if(!isset($this->params->file_limit) || (int)$this->params->file_limit == 0)
			echo '<em>'.$this->shopConfig->get('download_number_limit').'</em>';
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

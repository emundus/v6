<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="hikabtn" type="button" onclick="hikamarket.submitform('addfile','hikamarket_form');">
			<img style="vertical-align:middle" src="<?php echo HIKASHOP_IMAGES; ?>save.png"/> <?php echo JText::_('OK'); ?>
		</button>
	</div>
</fieldset>
<form action="<?php echo hikamarket::completeLink('product&task=file'); ?>" method="post" name="hikamarket_form" id="hikamarket_form" enctype="multipart/form-data">

<dl class="hikam_options">
	<dt>
		<label for="data_file_file_name"><?php echo JText::_('HIKA_NAME'); ?></label>
	</dt>
	<dd>
		<input type="text" name="data[file][file_name]" id="data_file_file_name" value="<?php echo $this->escape(@$this->element->file_name); ?>"/>
	</dd>
<?php
	if(empty($this->element->file_path)) {
		if(hikamarket::acl('product/edit/files/upload')) {
?>
	<dt>
		<label for="datafilemode"><?php echo JText::_('HIKA_FILE_MODE'); ?></label>
	</dt>
	<dd><?php
		$values = array(
			JHTML::_('select.option', 'upload', JText::_('HIKA_FILE_MODE_UPLOAD')),
			JHTML::_('select.option', 'path', JText::_('HIKA_FILE_MODE_PATH'))
		);
		echo JHTML::_('hikaselect.genericlist', $values, "data[filemode]", 'class="inputbox" size="1" onchange="hikamarket_filemode_switch(this);"', 'value', 'text', 'upload');
	?></dd>
<script type="text/javascript">
function hikamarket_filemode_switch(el) {
	var d = document, m = null,
		blocks = d.querySelectorAll('[data-section="filemode"]');
	blocks.forEach(function(b){
		m = b.getAttribute('data-filemode');
		b.style.display = (m == el.value) ? '' : 'none';
	});
}
window.hikashop.ready(function(){
	var el = document.getElementById('datafilemode');
	if(el) hikamarket_filemode_switch(el);
});
</script>

	<dt data-section="filemode" data-filemode="path">
		<label for="data_file_file_path"><?php echo JText::_('HIKA_PATH'); ?></label>
	</dt>
	<dd data-section="filemode" data-filemode="path">
		<input type="text" name="data[file][file_path]" id="data_file_file_path" size="60" style="width:100%" value=""/>
	</dd>

	<dt data-section="filemode" data-filemode="upload">
		<label for=""><?php echo JText::_('HIKA_FILE'); ?></label>
	</dt>
	<dd data-section="filemode" data-filemode="upload">
		<input type="file" name="files[]" size="30" /><br/>
		<?php echo JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')); ?>
	</dd>
<?php
		}
	} else {
?>
	<dt>
		<label for="data_file_file_path"><?php echo JText::_('FILENAME'); ?></label>
	</dt>
	<dd>
<?php
		if(hikamarket::acl('product/edit/files/upload')) {
?>
		<input type="text" name="data[file][file_path]" id="data_file_file_path" size="60" style="width:100%" value="<?php echo $this->escape($this->element->file_path); ?>"/>
<?php
		} else {
			echo '<span class="hikam_raw_filename">'.$this->escape($this->element->file_path).'</span>';
		}
?>
	</dd>
<?php
	}
?>
<?php if(hikamarket::acl('product/edit/files/limit')) { ?>
	<dt>
		<label for="data_file_file_limit"><?php echo JText::_('DOWNLOAD_NUMBER_LIMIT'); ?></label>
	</dt>
	<dd>
<?php
	$file_limit = (isset($this->element->file_limit) ? ($this->element->file_limit < 0 ? JText::_('UNLIMITED') : (int)$this->element->file_limit) : '');
?>
		<input type="text" name="data[file][file_limit]" id="data_file_file_limit" value="<?php echo $file_limit; ?>"/>
		<p>
			0: <?php echo JText::_('DEFAULT_PARAMS_FOR_PRODUCTS');?> (<?php echo $this->shopConfig->get('download_number_limit');?>)<br/>
			-1: <?php echo JText::_('UNLIMITED');?><br/>
		</p>
	</dd>
<?php } ?>
<?php if(hikamarket::acl('product/edit/files/free')) { ?>
	<dt>
		<label for="data_file_file_free_download"><?php echo JText::_('FREE_DOWNLOAD'); ?></label>
	</dt>
	<dd><?php
		if(empty($this->element))
			$this->element = new stdClass();
		if(!isset($this->element->file_free_download))
			$this->element->file_free_download = $this->config->get('upload_file_free_download', 0);
		echo $this->radioType->booleanlist('data[file][file_free_download]', '', $this->element->file_free_download);
	?></dd>
<?php } ?>
<?php if(hikamarket::acl('product/edit/files/description')) { ?>
	<dt>
		<label for="data_file_file_description"><?php echo JText::_('HIKA_DESCRIPTION'); ?></label>
	</dt>
	<dd>
		<textarea name="data[file][file_description]" id="data_file_file_description"><?php echo $this->escape(@$this->element->file_description); ?></textarea>
	</dd>
<?php } ?>
	</table>
	<div class="clr"></div>
	<input type="hidden" name="data[file][file_type]" value="file" />
	<input type="hidden" name="data[file][file_ref_id]" value="<?php echo $this->product_id; ?>" />
	<input type="hidden" name="cid" value="<?php echo @$this->cid; ?>" />
	<input type="hidden" name="pid" value="<?php echo (int)$this->product_id; ?>" />
	<input type="hidden" name="id" value="<?php echo hikaInput::get()->getInt('id');?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="task" value="file" />
	<input type="hidden" name="ctrl" value="product" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

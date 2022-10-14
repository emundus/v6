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
		<button class="hikabtn" type="button" onclick="hikamarket.submitform('addimage','hikamarket_form');">
			<img style="vertical-align:middle" src="<?php echo HIKASHOP_IMAGES; ?>save.png" alt=""/> <?php echo JText::_('OK'); ?>
		</button>
	</div>
</fieldset>
<form action="<?php echo hikamarket::completeLink('product&task=image'); ?>" method="post" name="hikamarket_form" id="hikamarket_form" enctype="multipart/form-data">
<dl class="hikam_options">
	<dt>
		<label for="data_file_file_name"><?php echo JText::_('HIKA_NAME'); ?></label>
	</dt>
	<dd>
		<input type="text" name="data[file][file_name]" id="data_file_file_name" value="<?php echo $this->escape(@$this->element->file_name); ?>"/>
	</dd>
<?php if(hikamarket::acl('product/edit/images/title')) { ?>
	<dt>
		<label for="data_file__file_description"><?php echo JText::_('HIKA_TITLE'); ?></label>
	</dt>
	<dd>
		<input type="text" name="data[file][file_description]" id="data_file__file_description" value="<?php echo $this->escape(@$this->element->file_description); ?>"/>
	</dd>
<?php } ?>
<?php
	if(empty($this->element->file_path)) {
		if(!empty($this->image_link) && hikamarket::acl('product/edit/images/link')) {
?>
	<dt>
		<label for="data_files"><?php echo JText::_('HIKA_PATH'); ?></label>
	</dt>
	<dd>
		<input type="text" name="data[filepath]" size="60" style="width:100%" value=""/>
	</dd>
<?php
		} else if(empty($this->image_link) && hikamarket::acl('product/edit/images/upload')) {
?>
	<dt>
		<label for="data_files"><?php echo JText::_('HIKA_IMAGE'); ?></label>
	</dt>
	<dd>
		<input id="data_files" type="file" name="files[]" size="30" /><br/>
		<?php echo JText::sprintf('MAX_UPLOAD', (hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')); ?>
	</dd>
<?php
		}
	} else {
?>
	<dt>
		<label for="files"><?php echo JText::_( 'HIKA_IMAGE' ); ?></label>
	</dt>
	<dd>
<?php $image = $this->imageHelper->getThumbnail($this->element->file_path, array(100, 100), array('default' => true)); ?>
		<img src="<?php echo $image->url; ?>" alt="<?php echo $image->filename; ?>"/>
	</dd>
<?php
	}
?>
</dl>
	<div class="clr"></div>
	<input type="hidden" name="data[file][file_type]" value="product" />
	<input type="hidden" name="data[file][file_ref_id]" value="<?php echo $this->product_id; ?>" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->cid; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="task" value="addimage" />
	<input type="hidden" name="ctrl" value="product" />
	<?php echo JHTML::_('form.token'); ?>
</form>

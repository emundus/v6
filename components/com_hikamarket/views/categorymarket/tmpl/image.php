<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="hikabtn" type="button" onclick="hikamarket.submitform('addimage','hikamarket_form');"><i class="far fa-save"></i> <?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<form action="<?php echo hikamarket::completeLink('category&task=image'); ?>" method="post" name="hikamarket_form" id="hikamarket_form" enctype="multipart/form-data">
	<table width="100%">
		<tr>
			<td class="key">
				<label for="file_name"><?php echo JText::_('HIKA_NAME'); ?></label>
			</td>
			<td>
				<input type="text" name="data[file][file_name]" value="<?php echo $this->escape(@$this->element->file_name); ?>"/>
			</td>
		</tr>
		<tr>
<?php
	if(empty($this->element->file_path)){
		if(hikamarket::acl('category/edit/images/upload')) {
?>
			<td class="key">
				<label for="files"><?php echo JText::_('HIKA_IMAGE'); ?></label>
			</td>
			<td>
				<input type="file" name="files[]" size="30" />
				<?php echo JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')); ?>
			</td>
<?php
		}
	}else{
?>
			<td class="key">
				<label for="files"><?php echo JText::_('HIKA_IMAGE'); ?></label>
			</td>
			<td><?php
				$image = $this->imageHelper->getThumbnail($this->element->file_path,array(100, 100),array('default' => true));
			?><img src="<?php echo $image->url; ?>" alt="<?php echo $image->filename; ?>"/></td>
<?php
	}
?>
		</tr>
	</table>
	<div class="clr"></div>
	<input type="hidden" name="data[file][file_type]" value="category" />
	<input type="hidden" name="data[file][file_ref_id]" value="<?php echo $this->category_id; ?>" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->cid; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="task" value="addimage" />
	<input type="hidden" name="ctrl" value="product" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

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
		<button class="hikabtn" type="button" onclick="hikamarket.submitform('addimage','hikamarket_form');"<i class="fas fa-check"></i> <?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<form action="<?php echo hikamarket::completeLink('upload&task=image'); ?>" method="post" name="hikamarket_form" id="hikamarket_form" enctype="multipart/form-data">
	<table width="100%">
		<tr>
<?php
	if(empty($this->element->file_path)) {
?>
			<td class="key">
				<label for="files"><?php echo JText::_('HIKA_IMAGE'); ?></label>
			</td>
			<td>
				<input type="file" name="files[]" size="30" />
				<?php echo JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')); ?>
			</td>
<?php
	} else {
?>
			<td class="key">
				<label for="files"><?php echo JText::_( 'HIKA_IMAGE' ); ?></label>
			</td>
			<td><?php
				$image = $this->imageHelper->getThumbnail($this->element->file_path, array(100, 100), array('default' => true));
			?><img src="<?php echo $image->url ;?>" alt="<?php echo $image->filename ;?>" /></td>
<?php
	}
?>
		</tr>
	</table>
	<div class="clr"></div>
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="ctrl" value="upload" />
	<input type="hidden" name="task" value="addimage" />
	<input type="hidden" name="uploader" value="<?php echo $this->uploader; ?>" />
	<input type="hidden" name="field" value="<?php echo $this->field; ?>" />
<?php
	if(!empty($this->uploadConfig['extra'])) {
		foreach($this->uploadConfig['extra'] as $uploadField => $uploadFieldValue) {
?>
	<input type="hidden" name="<?php echo $uploadField; ?>" value="<?php echo $uploadFieldValue; ?>" />
<?php
		}
	}
?>
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

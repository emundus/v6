<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>
<table class="admintable table" cellspacing="1">
	<tr>
		<td class="key" >
			<?php echo JText::_('UPLOAD_FILE'); ?>
		</td>
		<td>
			<input type="file" size="50" name="importfile" />
			<?php echo JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')); ?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('CHARSET_FILE'); ?>
		</td>
		<td>
			<?php $charsetType = hikashop_get('type.charset'); array_unshift($charsetType->values,JHTML::_('select.option','', JText::_('UNKNOWN'))); echo $charsetType->display('charsetconvert',hikaInput::get()->getString('charsetconvert','')); ?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('UPDATE_PRODUCTS'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', 'file_update_products','',hikaInput::get()->getInt('file_update_products','1'));?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('CREATE_CATEGORIES'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', 'file_create_categories','',hikaInput::get()->getInt('file_create_categories','1'));?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('FORCE_PUBLISH'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', 'file_force_publish','',hikaInput::get()->getInt('file_force_publish','1'));?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('UPDATE_PRODUCT_QUANTITY'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', 'file_update_product_quantity','',hikaInput::get()->getInt('file_update_product_quantity','0'));?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('STORE_IMAGES_LOCALLY'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', 'file_store_images_locally','',hikaInput::get()->getInt('file_store_images_locally','1'));?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('STORE_FILES_LOCALLY'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', 'file_store_files_locally','',hikaInput::get()->getInt('file_store_files_locally','1'));?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('KEEP_OTHER_VARIANTS'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', 'keep_other_variants','',hikaInput::get()->getInt('keep_other_variants','1'));?>
		</td>
	</tr>
</table>

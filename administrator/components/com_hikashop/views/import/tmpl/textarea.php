<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><textarea style="width:100%" rows="20" name="textareaentries">
<?php $text = hikaInput::get()->getString("textareaentries");
if(empty($text)){ ?>
product_name,price_value,price_currency_id
Bread,1.20,EUR
Coffee,2,USD
<?php }else echo $text?>
</textarea>
<table class="admintable table" cellspacing="1">
	<tr>
		<td class="key" >
			<?php echo JText::_('UPDATE_PRODUCTS'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', 'textarea_update_products','',hikaInput::get()->getInt('textarea_update_products','1'));?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('CREATE_CATEGORIES'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', 'textarea_create_categories','',hikaInput::get()->getInt('textarea_create_categories','1'));?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('FORCE_PUBLISH'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', 'textarea_force_publish','',hikaInput::get()->getInt('textarea_force_publish','1'));?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('UPDATE_PRODUCT_QUANTITY'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', 'textarea_update_product_quantity','',hikaInput::get()->getInt('textarea_update_product_quantity','0'));?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('STORE_IMAGES_LOCALLY'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', 'textarea_store_images_locally','',hikaInput::get()->getInt('textarea_store_images_locally','1'));?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('STORE_FILES_LOCALLY'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', 'textarea_store_files_locally','',hikaInput::get()->getInt('textarea_store_files_locally','1'));?>
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

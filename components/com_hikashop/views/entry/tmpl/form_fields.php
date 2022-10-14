<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.4.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>		<fieldset class="input">
			<legend><?php echo JText::_('HIKASHOP_ENTRY');?></legend>
			<?php if($this->id>1){?>
				<div class="hikashop_delete_entry_button"><a href="#" onClick="hikashopRemoveEntryHTML(<?php echo $this->id;?>);return false;"><?php echo JText::_('REMOVE_ENTRY');?></a></div>
			<?php } ?>
			<table cellpadding="0" cellspacing="0" border="0" class="hikashop_contentpane">
		<?php foreach($this->extraFields['entry'] as $fieldName => $oneExtraField) { ?>
			<tr id="hikashop_entry_<?php echo $fieldName.'_'.$this->id;?>" class="hikashop_entry_<?php echo $fieldName;?>">
				<td class="key">
					<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
				</td>
				<td>
					<?php
					 $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';
					 echo $this->fieldsClass->display($oneExtraField,$this->entry->$fieldName,'data[entry][entry_'.$this->id.']['.$fieldName.']',false,' '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\'entry\','.$this->id.');"'); ?>
				</td>
			</tr>
		<?php }?>
			</table>
		</fieldset>

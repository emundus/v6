<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>		<fieldset class="input">
			<legend><?php echo JText::_('HIKASHOP_ENTRY');?></legend>
<?php
	if($this->id>1){
?>
				<div class="hikashop_delete_entry_button"><a href="#" onClick="hikashopRemoveEntryHTML(<?php echo $this->id;?>);return false;"><?php echo JText::_('REMOVE_ENTRY');?></a></div>
<?php
	}
?>
			<table cellpadding="0" cellspacing="0" border="0" class="hikashop_contentpane">
<?php 
	$after = array();
	foreach($this->extraFields['entry'] as $fieldName => $oneExtraField) {
		$onWhat='onchange';
		if($oneExtraField->field_type=='radio')
			$onWhat='onclick';
		$html = $this->fieldsClass->display($oneExtraField,$this->entry->$fieldName,'data[entry][entry_'.$this->id.']['.$fieldName.']',false,' '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\'entry\','.$this->id.');"');
		if($oneExtraField->field_type=='hidden') {
			$after[] = $html;
			continue;
		}
?>
			<tr id="hikashop_entry_<?php echo $fieldName.'_'.$this->id;?>" class="hikashop_entry_<?php echo $fieldName;?>">
				<td class="key">
					<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
				</td>
				<td>
					<?php echo $html; ?>
				</td>
			</tr>
<?php
	}
?>
			</table>
<?php
	if(count($after)) {
		echo implode("\r\n", $after);
	}
?>
		</fieldset>

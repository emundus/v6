<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_product_custom_item_info" class="hikashop_product_custom_item_info">
	<table class="hikashop_product_custom_item_info_table">
<?php
$after = array();
foreach ($this->itemFields as $fieldName => $oneExtraField) {
	if(empty($this->element->$fieldName))
		$this->element->$fieldName = $oneExtraField->field_default;
	$itemData = hikaInput::get()->getString('item_data_' . $fieldName, $this->element->$fieldName);
	$onWhat='onchange';
	if($oneExtraField->field_type=='radio')
		$onWhat='onclick';
	$oneExtraField->product_id = $this->element->product_id;
	$html = $this->fieldsClass->display(
		$oneExtraField,
		$itemData,
		'data[item]['.$oneExtraField->field_namekey.']',
		false,
		' class="'.HK_FORM_CONTROL_CLASS.'" '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\'item\',0);"',
		false,
		$this->itemFields
	);
	if($oneExtraField->field_type == 'hidden') {
		$after[] = $html;
		continue;
	}
?>
		<tr id="hikashop_item_<?php echo $oneExtraField->field_namekey; ?>" class="hikashop_item_<?php echo $oneExtraField->field_namekey;?>_line">
			<td class="key">
				<span id="hikashop_product_custom_item_name_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_item_name"><?php
					echo $this->fieldsClass->getFieldName($oneExtraField);
				?></span>
			</td>
			<td>
				<span id="hikashop_product_custom_item_value_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_item_value"><?php
					echo $html;
				?></span>
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
</div>

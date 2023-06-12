<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$type = $this->type;
$after = array();
foreach($this->extraFields[$type] as $fieldName => $oneExtraField) {
	$oneExtraField->registration_page = @$this->registration_page;
	$onWhat='onchange';
	if($oneExtraField->field_type=='radio')
		$onWhat='onclick';
	$html = $this->fieldsClass->display(
		$oneExtraField,
		@$this->$type->$fieldName,
		'data['.$type.']['.$fieldName.']',
		false,
		' class="form-control" '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\''.$type.'\',0);"',
		false,
		$this->extraFields[$type],
		$this->$type
	);
	if($oneExtraField->field_type=='hidden') {
		$after[] = $html;
		continue;
	}
?>
	<div class="control-group hikashop_registration_<?php echo $fieldName;?>_line" id="hikashop_<?php echo $type.'_'.$oneExtraField->field_namekey; ?>">
		<div class="control-label">
			<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
		</div>
		<div class="controls">
			<?php echo $html; ?>
		</div>
	</div>
<?php
}

if(count($after)) {
	echo implode("\r\n", $after);
}

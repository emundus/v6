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

if($this->tmpl == 'component') {

?>
<h1 id="hikashop_address_form_header_iframe"><?php echo JText::_('ADDRESS_INFORMATION');?></h1>
<div id="hikashop_address_form_span_iframe">
<?php

} else {
?>
<div id="hikashop_address_edition">
<?php echo $this->toolbarHelper->process($this->toolbar, JText::_('ADDRESS')); ?>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.saveAddr = function(el) {
	if(!hikashopCheckChangeForm('address','hikashop_address_form'))
		return false;
	var frm = document.getElementById('hikashop_address_form');
	frm.submit();
	return false;
};
</script>
<?php
}
?>
<form action="<?php echo hikashop_completeLink('address&task=save'); ?>" method="post" name="hikashop_address_form" id="hikashop_address_form" enctype="multipart/form-data">
<table class="table">
<?php
$after = array();
foreach($this->extraFields['address'] as $fieldName => $oneExtraField) {
	$onWhat='onchange';
	if($oneExtraField->field_type=='radio')
		$onWhat='onclick';
	$html = $this->fieldsClass->display(
		$oneExtraField,
		@$this->address->$fieldName,
		'data[address]['.$fieldName.']',
		false,
		' '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\'address\',0);"',
		false,
		$this->extraFields['address'],
		@$this->address,
		false
	);
	if($oneExtraField->field_type=='hidden') {
		$after[] = $html;
		continue;
	}
?>
	<tr class="hikashop_address_<?php echo $fieldName;?>_line" id="hikashop_address_<?php echo $oneExtraField->field_namekey; ?>">
		<td class="key"><?php
			echo $this->fieldsClass->getFieldName($oneExtraField, true, 'hkcontrol-label');
		?></td>
		<td><?php

			echo $html;
		?></td>
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
	<input type="hidden" name="Itemid" value="<?php global $Itemid; echo $Itemid; ?>"/>
	<input type="hidden" name="ctrl" value="address"/>
	<input type="hidden" name="task" value="save"/>
	<input type="hidden" name="type" value="<?php echo hikaInput::get()->getCmd('type', ''); ?>"/>
	<input type="hidden" name="action" value="<?php echo hikaInput::get()->getCmd('task', ''); ?>"/>
	<input type="hidden" name="makenew" value="<?php echo hikaInput::get()->getInt('makenew', 0); ?>"/>
	<input type="hidden" name="redirect" value="<?php echo hikaInput::get()->getWord('redirect', ''); ?>"/>
	<input type="hidden" name="step" value="<?php echo hikaInput::get()->getInt('step', -1); ?>"/>
	<input type="hidden" name="data[address][address_user_id]" value="<?php echo !empty($address->address_user_id) ? (int)$address->address_user_id : (int)$this->user_id;?>"/>
<?php
	if($this->tmpl == 'component') {
?>
	<input type="hidden" name="tmpl" value="component"/>
<?php
	}

	if(!hikaInput::get()->getInt('makenew')) {
?>
	<input type="hidden" name="data[address][address_id]" value="<?php echo (int)@$this->address->address_id;?>"/>
	<input type="hidden" name="address_id" value="<?php echo (int)@$this->address->address_id;?>"/>
<?php
	}

	echo JHTML::_('form.token');

	if($this->tmpl == 'component') {
		echo $this->cart->displayButton(JText::_('OK'),'ok',$this->params,hikashop_completeLink('address&task=save'),'if(hikashopCheckChangeForm(\'address\',\'hikashop_address_form\')) document.forms[\'hikashop_address_form\'].submit(); return false;');
	}
?>
</form>
</div>
<div class="clear_both"></div>

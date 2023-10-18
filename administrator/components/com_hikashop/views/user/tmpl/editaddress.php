<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><h1><?php echo JText::_('ADDRESS_INFORMATION');?></h1>
<div id="hikashop_address_form_span_iframe">
	<form action="<?php echo hikashop_completeLink('user&task=saveaddress'); ?>" method="post" name="hikashop_address_form" enctype="multipart/form-data">
		<table class="table">
<?php
	foreach($this->extraFields['address'] as $fieldName => $oneExtraField) {
?>
			<tr class="hikashop_address_<?php echo $fieldName;?>_line" id="hikashop_address_<?php echo $oneExtraField->field_namekey; ?>">
				<td class="key"><?php
					echo $this->fieldsClass->getFieldName($oneExtraField);
				?></td>
				<td><?php
					$onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';
					echo $this->fieldsClass->display(
						$oneExtraField,
						$this->address->$fieldName,
						'data[address]['.$fieldName.']',
						false,
						' '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\'address\',0);"',
						false,
						$this->extraFields['address'],
						$this->address
					);
				?></td>
			</tr>
<?php }	?>
		</table>
		<input type="hidden" name="ctrl" value="user"/>
		<input type="hidden" name="tmpl" value="component"/>
		<input type="hidden" name="task" value="saveaddress"/>
		<input type="hidden" name="data[address][address_user_id]" value="<?php echo $this->user_id;?>"/>
		<input type="hidden" name="data[address][address_id]" value="<?php echo (int)@$this->address->address_id;?>"/>
		<input type="hidden" name="data[address][address_type]" value="<?php echo @$this->address->address_type;?>"/>
<?php
	if(empty($this->address->address_id) && !empty($this->address->address_type)) {
		$label = 'HIKASHOP_ALSO_SHIPPING_ADDRESS';
		if($this->address->address_type == 'shipping') {
			$label = 'HIKASHOP_ALSO_BILLING_ADDRESS';
		}
		$config = hikashop_config();
		$checked = '';
		if($config->get('same_address_default_checked', 0))
			$checked = ' checked="checked"';
?>
		<div class="hikashop_new_address_same">
			<input class="hikashop_same_address_checkbox" id="hikashop_address_same_address_input" type="checkbox" name="same_address"<?php echo $checked; ?> value="1"/>
			<label for="hikashop_address_same_address_input"><?php echo JText::_($label); ?></label>
		</div>
		<br/>
<?php
	}
	echo JHTML::_( 'form.token' );
?>
		<a href="#" onclick="if(hikashopCheckChangeForm('address','hikashop_address_form')) document.forms['hikashop_address_form'].submit(); return false;" class="btn btn-success">
			<i class="fa fa-save"></i> <?php echo JText::_('OK'); ?>
		</a>
	</form>
</div>

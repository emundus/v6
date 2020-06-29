<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
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
	echo JHTML::_( 'form.token' );
?>
		<a href="#" onclick="if(hikashopCheckChangeForm('address','hikashop_address_form')) document.forms['hikashop_address_form'].submit(); return false;" class="btn btn-success">
			<i class="fa fa-save"></i> <?php echo JText::_('OK'); ?>
		</a>
	</form>
</div>

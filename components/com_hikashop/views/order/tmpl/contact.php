<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_order_contact_<?php echo hikaInput::get()->getInt('cid');?>_page" class="hikashop_order_contact_page">
	<form action="<?php echo hikashop_completeLink('order'); ?>" id="hikashop_order_contact_form" name="hikashop_order_contact_form" method="post"  onsubmit="return checkFields();">
		<fieldset>
			<div class="" style="float:left">
<!-- TITLE -->
				<h1><?php
if(!empty($this->order->order_id)) {
	$doc = JFactory::getDocument();
	$doc->setMetaData( 'robots', 'noindex' );

	echo JText::_('HIKASHOP_ORDER').': '.@$this->order->order_number;
} else {
	echo @$this->title;
}
				?></h1>
<!-- EO TITLE -->
			</div>
			<div class="toolbar" id="toolbar" style="float: right;">
<!-- OK BUTTON -->
				<button class="hikabtn hikabtn-success" type="submit"><i class="fa fa-check"></i> <?php echo JText::_('OK'); ?></button>
<!-- EO OK BUTTON -->
<!-- CANCEL BUTTON -->
<?php if(hikaInput::get()->getCmd('tmpl', '') != 'component') { ?>
				<button class="hikabtn hikabtn-danger" type="button" onclick="history.back(); return false;"><i class="fa fa-times"></i> <?php echo JText::_('HIKA_CANCEL'); ?></button>
<?php } ?>
<!-- EO CANCEL BUTTON -->
			</div>
			<div style="clear:both"></div>
		</fieldset>
<?php
	$formData = hikaInput::get()->getVar('formData','');
	if(empty($formData))
		$formData = new stdClass();
	if(isset($this->element->name) && !isset($formData->name)){
		$formData->name = $this->element->name;
	}
	if(isset($this->element->email) && !isset($formData->email)){
		$formData->email = $this->element->email;
	}
?>
		<dl>
<!-- CUSTOM CONTACT FIELDS -->
<?php
	if(!empty($this->contactFields)){
?>
		</dl>
<?php
		foreach ($this->contactFields as $fieldName => $oneExtraField) {
			$itemData = @$formData->$fieldName;
?>
		<dl id="hikashop_contact_<?php echo $oneExtraField->field_namekey; ?>">
			<dt id="hikashop_contact_item_name_<?php echo $oneExtraField->field_id;?>" class="hikashop_contact_item_name">
				<label for="data[contact][<?php echo $oneExtraField->field_namekey; ?>]">
					<?php echo $this->fieldsClass->getFieldName($oneExtraField, true);?>
				</label>
			</dt>
			<dd id="hikashop_contact_item_value_<?php echo $oneExtraField->field_id;?>" class="hikasho_contact_item_value"><?php
					$onWhat='onchange';
					if($oneExtraField->field_type=='radio')
						$onWhat='onclick';
					$oneExtraField->order_id = $this->element->order_id;
					echo $this->fieldsClass->display(
						$oneExtraField,$itemData,
						'data[contact]['.$oneExtraField->field_namekey.']',
						false,
						' class="'.HK_FORM_CONTROL_CLASS.'" '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\'contact\',0);"',
						false,
						null,
						null,
						false
					);
				?>
			</dd>
		</dl>
<?php
		}
?>
		<dl>
<?php
	}
?>
<!-- EO CUSTOM CONTACT FIELDS -->
<!-- EXTRA DATA FIELDS -->
<?php
	if(!empty($this->extra_data['fields'])) {
		foreach($this->extra_data['fields'] as $key => $value) {
?>			<dt id="hikashop_contact_<?php echo $key; ?>_email" class="hikashop_contact_item_name">
				<label><?php echo JText::_($value['label']); ?></label>
			</dt>
			<dd id="hikashop_contact_<?php echo $key; ?>_email" class="hikashop_contact_item_value">
				<?php echo $value['content']; ?>
			</dd>
<?php
		}
	}
?>
<!-- EO EXTRA DATA FIELDS -->
<!-- ADDITIONAL INFORMATION -->
			<dt id="hikashop_contact_name_altbody" class="hikashop_contact_item_name">
				<label for="data[contact][altbody]"><?php echo JText::_( 'YOUR_MESSAGE' ); ?> <span class="hikashop_field_required_label">*</span></label>
			</dt>
			<dd id="hikashop_contact_value_altbody" class="hikashop_contact_item_value">
				<textarea id="hikashop_contact_altbody" cols="60" rows="10" name="data[contact][altbody]" style="width:100%;" placeholder="<?php echo JText::_( 'WRITE_HERE_YOUR_MESSAGE' ); ?>"><?php
					if(isset($formData->altbody)) echo $formData->altbody;
				?></textarea>
			</dd>
<!-- EO ADDITIONAL INFORMATION -->
<!-- CONFIRM CONSENT -->
<?php
	if(!empty($this->privacy)) {
		$text = JText::_( 'PLG_CONTENT_CONFIRMCONSENT_CONSENTBOX_LABEL' ) . ' <span class="hikashop_field_required_label">*</span>';
		if(!empty($this->privacy['id'])) {
			$popupHelper = hikashop_get('helper.popup');
			$text = $popupHelper->display(
				$text,
				'PLG_CONTENT_CONFIRMCONSENT_CONSENTBOX_LABEL',
				JRoute::_('index.php?option=com_hikashop&ctrl=checkout&task=privacyconsent&type=contact&tmpl=component'),
				'contact_privacyconsent',
				800, 500, '', '', 'link'
			);
		}
?>
			<dt id="hikashop_contact_name_consent" class="hikashop_contact_item_name">
				<label><?php echo $text; ?></label>
			</dt>
			<dd id="hikashop_contact_value_consent" class="hikashop_contact_item_value">
				<label class="checkbox">
					<input type="checkbox" id="hikashop_contact_consent" name="data[contact][consent]" value="1"/> <?php echo $this->privacy['text']; ?>
				</label>
				<input type="hidden" name="data[contact][consentcheck]" value="1"/>
			</dd>
<?php
	}
?>
<!-- EO CONFIRM CONSENT -->
<!-- GET A COPY -->
<?php
	if($this->config->get('contact_form_copy_checkbox', 0)) {
?>
			<dt id="hikashop_contact_name_copy" class="hikashop_contact_item_name">
				<label><?php echo JText::_('GET_A_COPY'); ?></label>
			</dt>
			<dd id="hikashop_contact_value_copy" class="hikashop_contact_item_value">
				<label class="checkbox">
					<input type="checkbox" id="hikashop_contact_copy" name="data[contact][copy]" value="1"/> <?php echo JText::_('CHECK_THIS_CHECKBOX_TO_GET_A_COPY'); ?>
				</label>
				<input type="hidden" name="data[contact][copycheck]" value="1"/>
			</dd>
<?php
	}
?>
<!-- EO GET A COPY -->
		</dl>
		<input type="hidden" name="data[contact][order_id]" value="<?php echo hikashop_getCID('order_id');?>" />
		<input type="hidden" name="cid" value="<?php echo hikaInput::get()->getInt('cid');?>" />
		<input type="hidden" name="order_token" value="<?php echo hikaInput::get()->getVar('order_token');?>" />
		<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
		<input type="hidden" name="task" value="send_email" />
		<input type="hidden" name="ctrl" value="order" />
<?php
	$redirect_url = hikaInput::get()->getString('redirect_url', '');
	if(empty($redirect_url) && !empty($_SERVER['HTTP_REFERER'])) {
		$redirect_url = $_SERVER['HTTP_REFERER'];
	} 
?>
		<input type="hidden" name="redirect_url" value="<?php echo $this->escape($redirect_url); ?>" />
<?php
	if(!empty($this->extra_data['hidden'])) {
		foreach($this->extra_data['hidden'] as $key => $value) {
			echo "\t\t" . '<input type="hidden" name="'.$this->escape($key).'" value="'.$this->escape($value).'" />' . "\r\n";
		}
	}
	if(hikaInput::get()->getVar('tmpl', '') == 'component') {
?>		<input type="hidden" name="tmpl" value="component" />
<?php
	}
	echo JHTML::_( 'form.token' );
?>
	</form>
</div>

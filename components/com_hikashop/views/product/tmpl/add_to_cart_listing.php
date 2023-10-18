<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(!$this->config->get('add_to_cart_legacy', true)) {
	$this->setLayout('add_to_cart_ajax');
	echo $this->loadTemplate();
	return;
}

if($this->config->get('show_quantity_field') < 2) {
?>
	<form action="<?php echo hikashop_completeLink('product&task=updatecart'); ?>" method="post" name="hikashop_product_form_<?php echo $this->row->product_id.'_'.$this->params->get('main_div_name'); ?>" enctype="multipart/form-data">
<?php
}

if(empty($this->row->has_options) && ($this->row->product_quantity == -1 || $this->row->product_quantity > 0) && !$this->config->get('catalogue') && ($this->config->get('display_add_to_cart_for_free_products') || !empty($this->row->prices)) ) {
	$itemFields = array();
	if(hikashop_level(2)) {
		if(isset($this->row->itemFields))
			$itemFields = $this->row->itemFields;
		else
			$itemFields = $this->fieldsClass->getFields('display:front_product_listing=1', $this->row, 'item', 'checkout&task=state');
	}

	$display_custom_item_fields = (int)$this->params->get('display_custom_item_fields', 0);
	if($display_custom_item_fields == -1){
		$default_params = $this->config->get('default_params');
		$display_custom_item_fields = (int)@$default_params['display_custom_item_fields'];
	}

	if(!$display_custom_item_fields){
		if(!empty($this->row->has_required_item_field))
			$this->row->has_options = true;
		$itemFields = array();
	}

	if(!empty($itemFields)) {
		$null = array();
		$this->fieldsClass->addJS($null,$null,$null);
		$this->fieldsClass->jsToggle($itemFields, $this->row, 0);

		$extraFields = array('item' => &$itemFields);
		$requiredFields = array();
		$validMessages = array();
		$values = array('item' => $this->row);
		$this->fieldsClass->checkFieldsForJS($extraFields, $requiredFields, $validMessages, $values);
		$this->fieldsClass->addJS($requiredFields, $validMessages, array('item'));

?>
	<!-- CUSTOM ITEM FIELDS -->
	<div id="hikashop_product_custom_item_info_for_product_<?php echo $this->row->product_id; ?>" class="hikashop_product_custom_item_info hikashop_product_listing_custom_item">
		<table class="hikashop_product_custom_item_info_table hikashop_product_listing_custom_item_table" width="100%">
<?php
		foreach($itemFields as $fieldName => $oneExtraField) {
			$itemData = hikaInput::get()->getString('item_data_'.$fieldName, @$this->row->$fieldName);
?>
			<tr id="hikashop_item_<?php echo $oneExtraField->field_namekey; ?>" class="hikashop_item_<?php echo $oneExtraField->field_namekey;?>_line">
				<td class="key">
					<span id="hikashop_product_custom_item_name_<?php echo $oneExtraField->field_id;?>_for_product_<?php echo $this->row->product_id; ?>" class="hikashop_product_custom_item_name"><?php
						echo $this->fieldsClass->getFieldName($oneExtraField);
					?></span>
				</td>
				<td>
					<span id="hikashop_product_custom_item_value_<?php echo $oneExtraField->field_id;?>_for_product_<?php echo $this->row->product_id; ?>" class="hikashop_product_custom_item_value"><?php
			$onWhat='onchange';
			if($oneExtraField->field_type=='radio')
				$onWhat = 'onclick';
			$oneExtraField->product_id = $this->row->product_id;
			$this->fieldsClass->prefix = 'product_'.$this->row->product_id.'_';
			echo $this->fieldsClass->display(
				$oneExtraField,
				$itemData,
				'data[item]['.$oneExtraField->field_namekey.']',
				false,
				' '.$onWhat.'="if (\'function\' == typeof window.hikashopToggleFields) { window.hikashop.toggleField(this.value,\''.$fieldName.'\',\'item\',0); }"'
			);
					?></span>
				</td>
			</tr>
<?php
		}
		$this->fieldsClass->prefix = '';
?>
		</table>
	</div>
	<!-- EO CUSTOM ITEM FIELDS -->
<?php
	}
}

if($this->config->get('show_quantity_field') < 2) {
	$module_id = $this->params->get('from_module', 0);
	$this->formName = ',\'hikashop_product_form_'.$this->row->product_id.'_'.$this->params->get('main_div_name').'\'';
	$this->ajax = '';
	if(!$this->config->get('ajax_add_to_cart', 0) || !empty($itemFields)) {
		$this->ajax = 'if(hikashopCheckChangeForm(\'item\',\'hikashop_product_form_'.$this->row->product_id.'_'.$this->params->get('main_div_name').'\')){ return hikashopModifyQuantity(\''.$this->row->product_id.'\',field,1,\'hikashop_product_form_'.$this->row->product_id.'_'.$this->params->get('main_div_name').'\',\'cart\','.$module_id.'); } return false;';
	}
	$this->setLayout('quantity');
	echo $this->loadTemplate();

	if(!empty($this->ajax) && $this->config->get('redirect_url_after_add_cart','stay_if_cart') == 'ask_user') {
?>
		<input type="hidden" name="popup" value="1"/>
<?php
	}
?>
		<input type="hidden" name="hikashop_cart_type_<?php echo $this->row->product_id.'_'.$module_id; ?>" id="hikashop_cart_type_<?php echo $this->row->product_id.'_'.$module_id; ?>" value="cart"/>
		<input type="hidden" name="product_id" value="<?php echo $this->row->product_id; ?>" />
		<input type="hidden" name="module_id" value="<?php echo $module_id; ?>" />
		<input type="hidden" name="add" value="1"/>
		<input type="hidden" name="ctrl" value="product"/>
		<input type="hidden" name="task" value="updatecart"/>
		<input type="hidden" name="return_url" value="<?php echo urlencode(base64_encode(urldecode($this->redirect_url)));?>"/>
	</form>
<?php
} elseif(empty($this->row->has_options) && !$this->config->get('catalogue') && ($this->config->get('display_add_to_cart_for_free_products') || !empty($this->row->prices)) ) {
	if($this->row->product_quantity == -1 || $this->row->product_quantity > 0) {
		if(empty($this->quantitylayout) || $this->quantitylayout != 'show_select') {
?>
		<input id="hikashop_listing_quantity_<?php echo $this->row->product_id;?>" type="text" style="width:40px;" name="data[<?php echo $this->row->product_id;?>]" class="hikashop_listing_quantity_field" value="0" />
<?php
		} else {
			if((int)$this->row->product_min_per_order <= 0)
				$this->row->product_min_per_order = 1;
			$min_quantity = (int)$this->row->product_min_per_order;
			if((int)$this->row->product_max_per_order == 0)
				$max_quantity = $min_quantity * $this->config->get('quantity_select_max_default_value', 15);
?>
		<select id="hikashop_listing_quantity_select_<?php echo $this->row->product_id;?>" class="tochosen" onchange="var qty_field = document.getElementById('hikashop_listing_quantity_<?php echo $this->row->product_id;?>'); qty_field.value = this.value;">
<?php
			echo '<option value="0" selected="selected">0</option>';
			for($j = $min_quantity; $j <= $max_quantity; $j += $min_quantity) {
				echo '<option value="'.$j.'">'.$j.'</option>';
			}
?>
		</select>
		<input id="hikashop_listing_quantity_<?php echo $this->row->product_id;?>" type="hidden" name="data[<?php echo $this->row->product_id;?>]" value="0" />
<?php
		}
	} else {
		echo JText::_('NO_STOCK');
	}
}

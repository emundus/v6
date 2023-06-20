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
class contact_requestPreviewMaker {
	public $displaySubmitButton = true;

	public function prepareMail($data = null) {
		if(empty($data))
			return $this->getDefaultData();

		$element = new stdClass();
		foreach($data['contact'] as $column => $value) {
			hikashop_secureField($column);
			$element->$column = strip_tags($value);
		}
		$config =& hikashop_config();
		$app = JFactory::getApplication();
		JPluginHelper::importPlugin('hikashop');
		$send = (int)$config->get('product_contact', 0);
		$app->triggerEvent('onBeforeSendContactRequest', array(&$element, &$send));

		$subject = JText::_('CONTACT_REQUEST');
		if(!empty($element->product_id)) {
			$productClass = hikashop_get('class.product');
			$product = $productClass->get((int)$element->product_id);

			if(!empty($product) && $product->product_type == 'variant') {
				$db = JFactory::getDBO();
				$query = 'SELECT * FROM '.hikashop_table('variant').' AS v '.
					' LEFT JOIN '.hikashop_table('characteristic') .' AS c ON v.variant_characteristic_id = c.characteristic_id '.
					' WHERE v.variant_product_id = '.(int)$element->product_id.' ORDER BY v.ordering';
				$db->setQuery($query);
				$product->characteristics = $db->loadObjectList();
				$parentProduct = $productClass->get((int)$product->product_parent_id);
				$productClass->checkVariant($product, $parentProduct);
			}

			if(!empty($product) && !empty($product->product_name)){
				$subject = JText::sprintf('CONTACT_REQUEST_FOR_PRODUCT',strip_tags($product->product_name));
			}
		}

		$mailClass = hikashop_get('class.mail');
		$infos = new stdClass();
		$infos->element =& $element;
		$infos->product =& $product;
		$mail = $mailClass->get('contact_request', $infos);
		$mail->subject = $subject;
		$mail->from_email = $config->get('from_email');
		$mail->from_name = $config->get('from_name');
		$mail->reply_email = $element->email;
		if(empty($mail->dst_email))
			$mail->dst_email = array($config->get('from_email'));
		return $mail;
	}

	public function getDefaultData() {
	}

	public function getSelector($data) {
		$nameboxType = hikashop_get('type.namebox');
		$html = $nameboxType->display(
			'data[contact][product_id]',
			(int)@$data['contact']['product_id'],
			hikashopNameboxType::NAMEBOX_SINGLE,
			'product',
			array(
				'delete' => false,
				'variants' => 2,
				'url_params' => array('VARIANTS' => 2),
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				'returnOnEmpty' => false,
			)
		);
		$product = null;
		if(!empty($data['contact']['product_id'])){
			$class = hikashop_get('class.product');
			$product = $class->get($data['contact']['product_id']);
		}
		$fieldClass = hikashop_get('class.field');
		$contactFields = $fieldClass->getFields('frontcomp', $product, 'contact');
?>
<dl class="hika_options">
	<dt>
		<?php echo JText::_('PRODUCT'); ?>
	</dt>
	<dd>
		<?php echo $html; ?>
	</dd>
	<dt id="hikashop_contact_name_name" class="hikashop_contact_item_name">
		<label for="data[contact][name]"><?php echo JText::_( 'HIKA_USER_NAME' ); ?></label>
	</dt>
	<dd id="hikashop_contact_value_name" class="hikashop_contact_item_value">
		<input id="hikashop_contact_name" type="text" name="data[contact][name]" size="40" value="<?php echo @$data['contact']['name']; ?>" />
	</dd>
	<dt id="hikashop_contact_name_email" class="hikashop_contact_item_name">
		<label for="data[contact][email]"><?php echo JText::_( 'HIKA_EMAIL' ); ?></label>
	</dt>
	<dd id="hikashop_contact_value_email" class="hikashop_contact_item_value">
		<input id="hikashop_contact_email" type="text" name="data[contact][email]" size="40" value="<?php echo @$data['contact']['email']; ?>" />
	</dd>
<?php
	if(!empty($contactFields)){
?>
</dl>
<?php
		foreach ($contactFields as $fieldName => $oneExtraField) {
			$itemData = @$data['contact'][$fieldName];
?>
<dl id="hikashop_contact_<?php echo $oneExtraField->field_namekey; ?>" class="hika_options">
	<dt id="hikashop_contact_item_name_<?php echo $oneExtraField->field_id;?>" class="hikashop_contact_item_name">
		<label for="data[contact][<?php echo $oneExtraField->field_namekey; ?>]">
			<?php echo $fieldClass->getFieldName($oneExtraField, true);?>
		</label>
	</dt>
	<dd id="hikashop_contact_item_value_<?php echo $oneExtraField->field_id;?>" class="hikasho_contact_item_value"><?php
			$onWhat='onchange';
			if($oneExtraField->field_type=='radio')
				$onWhat='onclick';
			$oneExtraField->product_id = (int)@$data['contact']['product_id'];
			echo $fieldClass->display(
				$oneExtraField,$itemData,
				'data[contact]['.$oneExtraField->field_namekey.']',
				false,
				' '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\'contact\',0);"',
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
<dl class="hika_options">
<?php
	}
?>
	<dt id="hikashop_contact_name_altbody" class="hikashop_contact_item_name">
		<label for="data[contact][altbody]"><?php echo JText::_( 'ADDITIONAL_INFORMATION' ); ?></label>
	</dt>
	<dd id="hikashop_contact_value_altbody" class="hikashop_contact_item_value">
		<textarea id="hikashop_contact_altbody" cols="60" rows="10" name="data[contact][altbody]" style="width:100%;"><?php echo @$data['contact']['altbody']; ?></textarea>
	</dd>
</dl>
<?php
	}
}

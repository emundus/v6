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
class waitlist_notificationPreviewMaker {
	public $displaySubmitButton = true;

	public function prepareMail($data = null) {
		if(empty($data))
			return $this->getDefaultData();

		$element = new stdClass();
		foreach($data['waitlist'] as $column => $value) {
			hikashop_secureField($column);
			$element->$column = strip_tags($value);
		}
		$config =& hikashop_config();

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
		}
		$product->product_item_id = 0;

		$mailClass = hikashop_get('class.mail');
		$infos = new stdClass();
		$mail = $mailClass->get('waitlist_notification', $product);
		$mail->subject = JText::sprintf($mail->subject, HIKASHOP_LIVE);
		$mail->from_email = $config->get('from_email');
		$mail->from_name = $config->get('from_name');
		$mail->dst_email = $element->email;
		return $mail;
	}

	public function getDefaultData() {
	}

	public function getSelector($data) {
		$nameboxType = hikashop_get('type.namebox');
		$html = $nameboxType->display(
			'data[waitlist][product_id]',
			(int)@$data['waitlist']['product_id'],
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

?>
<dl class="hika_options">
	<dt>
		<?php echo JText::_('PRODUCT'); ?>
	</dt>
	<dd>
		<?php echo $html; ?>
	</dd>
	<dt id="hikashop_waitlist_name_email" class="hikashop_waitlist_item_name">
		<label for="data[waitlist][email]"><?php echo JText::_( 'HIKA_EMAIL' ); ?></label>
	</dt>
	<dd id="hikashop_waitlist_value_email" class="hikashop_waitlist_item_value">
		<input id="hikashop_waitlist_email" type="text" name="data[waitlist][email]" size="40" value="<?php echo @$data['waitlist']['email']; ?>" />
	</dd>
</dl>
<?php
	}
}

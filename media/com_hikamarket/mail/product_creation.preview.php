<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class market_product_creationPreviewMaker {
	public $displaySubmitButton = false;
	public $type = 'product';

	public function prepareMail($data = null) {
		if(empty($data))
			return $this->getDefaultData();

		$producClass = hikashop_get('class.product');
		$vendorClass = hikamarket::get('class.vendor');

		$params = new stdClass();
		$params->product = $producClass->get((int)$data);
		$params->vendor = $vendorClass->get( (int)$params->product->product_vendor_id );

		$mailClass = hikamarket::get('class.mail');
		$mail = $mailClass->load('product_creation', $params);

		$mail->hikamarket = true;
		if(empty($mail->subject))
			$mail->subject = 'MARKET_PRODUCT_CREATION_SUBJECT';

		return $mail;
	}

	public function getDefaultData() {
	}

	public function getSelector($data) {
		$nameboxType = hikashop_get('type.namebox');
		$html = $nameboxType->display(
			'data',
			(int)$data,
			hikashopNameboxType::NAMEBOX_SINGLE,
			'product',
			array(
				'delete' => false,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				'returnOnEmpty' => false,
			)
		);
		if(!$html) {
			hikashop_display(JText::_('PLEASE_FIRST_CREATE_A_PRODUCT'), 'info');
			return;
		}
		if(empty($data)) {
			echo hikashop_display(Jtext::_('PLEASE_SELECT_A_PRODUCT_FOR_THE_PREVIEW'));
		}
?>
<dl class="hika_options">
	<dt><?php echo JText::_('HIKA_PRODUCT_NAME'); ?></dt>
	<dd><?php echo $html; ?></dd>
</dl>
<script type="text/javascript">
window.Oby.ready(function() {
	var w = window;
	if(!w.oNameboxes['data'])
		return;
	w.oNameboxes['data'].register('set', function(e) {
		hikashop.submitform('preview','adminForm');
	});
});
</script>
<?php
	}
}

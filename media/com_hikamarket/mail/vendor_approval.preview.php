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
class market_vendor_registrationPreviewMaker {
	public $displaySubmitButton = false;
	public $type = 'registration';

	public function prepareMail($data = null) {
		if(empty($data))
			return $this->getDefaultData();

		if(empty($data['vendor']))
			$data['vendor'] = 1;

		$userClass = hikashop_get('class.user');
		$vendorClass = hikamarket::get('class.vendor');

		$params = new stdClass();
		$params->user = $userClass->get((int)$data['user']);
		$params->vendor = $vendorClass->get((int)$data['vendor']);
		$params->vendor_name = $params->vendor->vendor_name;
		$params->name = $params->user->name;

		$mailClass = hikamarket::get('class.mail');
		$mail = $mailClass->load('vendor_registration', $params);

		$mail->hikamarket = true;
		if(empty($mail->subject))
			$mail->subject = 'MARKET_VENDOR_REGISTRATION_SUBJECT';

		return $mail;
	}

	public function getDefaultData() {
	}

	public function getSelector($data) {
		$nameboxType = hikashop_get('type.namebox');
		$marketNameboxType = hikamarket::get('type.namebox');

		$html_user = $nameboxType->display(
			'data[user]',
			(int)$data['user'],
			hikashopNameboxType::NAMEBOX_SINGLE,
			'user',
			array(
				'delete' => false,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				'returnOnEmpty' => false,
			)
		);
		if(!$html_user) {
			hikashop_display(JText::_('PLEASE_FIRST_CREATE_A_PRODUCT'), 'info');
			return;
		}

		$html_vendor = $marketNameboxType->display(
			'data[vendor]',
			(int)$data['vendor'],
			hikashopNameboxType::NAMEBOX_SINGLE,
			'vendor',
			array(
				'delete' => false,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				'returnOnEmpty' => false,
			)
		);

		if(empty($html_user)) {
			echo hikashop_display(Jtext::_('PLEASE_SELECT_A_USER_FOR_THE_PREVIEW'));
		}
		if(empty($html_vendor)) {
			echo hikashop_display(Jtext::_('PLEASE_SELECT_A_VENDOR_FOR_THE_PREVIEW'));
		}
?>
<dl class="hika_options">
	<dt><?php echo JText::_('HIKA_USER'); ?></dt>
	<dd><?php echo $html_user; ?></dd>
</dl>
<dl class="hika_options">
	<dt><?php echo JText::_('HIKAMARKET_VENDOR'); ?></dt>
	<dd><?php echo $html_vendor; ?></dd>
</dl>
<script type="text/javascript">
window.Oby.ready(function() {
	var w = window;
	if(!w.oNameboxes['data_user'])
		return;
	w.oNameboxes['data_user'].register('set', function(e) {
		hikashop.submitform('preview','adminForm');
	});
	w.oNameboxes['data_vendor'].register('set', function(e) {
		hikashop.submitform('preview','adminForm');
	});
});
</script>
<?php
	}
}

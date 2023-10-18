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
class user_account_admin_notificationPreviewMaker {
	public $displaySubmitButton = false;
	public $type = 'user';

	public function prepareMail($data = null) {
		if(empty($data))
			return $this->getDefaultData();

		$class = hikashop_get('class.user');
		$user = $class->get($data);
		$user->activation_url = $user->partner_url = hikashop_currentURL();
		$user->active = true;
		$user->password = '*************';
		$user->user_data = $user;
		$config =& hikashop_config();
		$subject = JText::_('HIKA_USER_ACCOUNT_ADMIN_NOTIFICATION_SUBJECT');

		$mailClass = hikashop_get('class.mail');
		$mail = $mailClass->get('user_account_admin_notification', $user);
		$mail->subject = $subject;
		$mail->from_email = $config->get('from_email');
		$mail->from_name = $config->get('from_name');
		$mail->dst_email = $user->user_email;
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
			'user',
			array(
				'delete' => false,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
		if(empty($data)) {
			echo hikashop_display(Jtext::_('PLEASE_SELECT_A_USER_FOR_THE_PREVIEW'));
		}
?>
<dl class="hika_options">
	<dt>
		<?php echo JText::_('HIKA_USER'); ?>
	</dt>
	<dd>
		<?php echo $html; ?>
	</dd>
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

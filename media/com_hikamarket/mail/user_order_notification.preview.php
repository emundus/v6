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
class user_order_notificationPreviewMaker {
	public $displaySubmitButton = true;
	public $type = 'order';

	public function prepareMail($data = null) {
		if(empty($data))
			return $this->getDefaultData();

		$orderClass = hikashop_get('class.order');
		$order = $orderClass->loadNotification((int)$data, 'market.user_order_notification');
		return $order->mail;
	}

	public function getDefaultData() {
	}

	public function getSelector($data) {
		$nameboxType = hikashop_get('type.namebox');
		$html = $nameboxType->display(
			'data',
			(int)$data,
			hikashopNameboxType::NAMEBOX_SINGLE,
			'order',
			array(
				'delete' => false,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				'returnOnEmpty' => false,
			)
		);
		if(!$html){
			hikashop_display(JText::_('PLEASE_FIRST_CREATE_AN_ORDER'), 'info');
			return;
		}
		if(empty($data)) {
			echo hikashop_display(Jtext::_('PLEASE_SELECT_AN_ORDER_FOR_THE_PREVIEW'));
		}
?>
<dl class="hika_options">
	<dt><?php
		echo JText::_('HIKASHOP_ORDER');
	?></dt>
	<dd><?php
		echo $html;
	?></dd>
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
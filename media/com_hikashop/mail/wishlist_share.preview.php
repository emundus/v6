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
class wishlist_sharePreviewMaker {
	public $displaySubmitButton = true;

	public function prepareMail($data = null) {
		if(empty($data))
			return $this->getDefaultData();

		$wishlistClass = hikashop_get('class.cart');
		$mail = $wishlistClass->loadNotification($data['cart']['cart_id'], 'wishlist_share');
		return $mail;
	}

	public function getDefaultData() {
	}

	public function getSelector($data) {
		$nameboxType = hikashop_get('type.namebox');
		$html = $nameboxType->display(
			'data[cart][cart_id]',
			(int)@$data['cart']['cart_id'],
			hikashopNameboxType::NAMEBOX_SINGLE,
			'cart',
			array(
				'delete' => false,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				'returnOnEmpty' => false,
				'type' => 'wishlist',
				'url_params' => array(
					'TYPE' => 'wishlist',
				),
			)
		);

		if(!$html){
			hikashop_display(JText::_('PLEASE_FIRST_CREATE_A_WISHLIST'), 'info');
			return;
		}
		if(empty($data)) {
			echo hikashop_display(Jtext::_('PLEASE_SELECT_A_WISHLIST_FOR_THE_PREVIEW'));
		}
?>
<dl class="hika_options">
	<dt>
		<?php echo JText::_('WISHLIST'); ?>
	</dt>
	<dd>
		<?php echo $html; ?>
	</dd>
</dl>
<?php
	}
}

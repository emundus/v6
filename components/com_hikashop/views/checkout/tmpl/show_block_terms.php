<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$cart = $this->checkoutHelper->getCart();
if(empty($this->ajax)) {
?>
<div id="hikashop_checkout_fields_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" class="hikashop_checkout_fields">
<?php } ?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner small_spinner"></div>
<?php
	$this->checkoutHelper->displayMessages('terms');
	$terms_checked = (!empty($cart->cart_params->terms_checked)) ? 'checked="checked"' : '';
?>
<div id="hikashop_checkout_terms_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" class="hikashop_checkout_terms hikashop_checkout_terms_<?php echo $this->module_position; ?>">
	<input onclick="window.checkout.submitBlock('terms',<?php echo $this->step; ?>,<?php echo $this->module_position; ?>);" class="hikashop_checkout_terms_checkbox" id="hikashop_checkout_terms_checkbox_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" type="checkbox" name="checkout[terms]" value="1" <?php echo $terms_checked; ?> />
<?php
	$text = JText::_('PLEASE_ACCEPT_TERMS');

	if(!empty($this->options['article_id'])) {
		$popupHelper = hikashop_get('helper.popup');
		$text = $popupHelper->display(
			$text,
			'HIKASHOP_CHECKOUT_TERMS',
			JRoute::_('index.php?option=com_hikashop&ctrl=checkout&task=termsandconditions&tmpl=component'),
			'shop_terms_and_cond',
			(int)$this->options['popup_width'], (int)$this->options['popup_height'], '', '', 'link'
		);
	}
?>
	<label for="hikashop_checkout_terms_checkbox_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>"><?php echo $text; ?></label>
</div>
<?php
	if(empty($this->ajax)) { ?>
</div>
<?php }

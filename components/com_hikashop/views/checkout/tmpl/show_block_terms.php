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
$cart = $this->checkoutHelper->getCart();
if(empty($this->ajax)) {
?>
<div id="hikashop_checkout_terms_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" data-checkout-step="<?php echo $this->step; ?>" data-checkout-pos="<?php echo $this->module_position; ?>" class="hikashop_checkout_terms hikashop_checkout_terms_<?php echo $this->module_position; ?>">
<?php } ?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner small_spinner"></div>
<?php
	$this->checkoutHelper->displayMessages('terms_' . $this->step . '_' .  $this->module_position);
	$key = 'terms_checked_' . $this->step . '_' .  $this->module_position;
	if(!isset($cart->cart_params->$key))
		$terms_checked = (!empty($this->options['pre_checked'])) ? 'checked="checked"' : '';
	else
		$terms_checked = (!empty($cart->cart_params->$key)) ? 'checked="checked"' : '';
?>
	<input onclick="window.checkout.submitBlock('terms',<?php echo $this->step; ?>,<?php echo $this->module_position; ?>);" class="hikashop_checkout_terms_checkbox" id="hikashop_checkout_terms_checkbox_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" type="checkbox" name="checkout[terms_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>]" value="1" <?php echo $terms_checked; ?> />
<?php
	$text = $this->options['label'];

	if(!empty($this->options['article_id'])) {
		$popupHelper = hikashop_get('helper.popup');
		$text = $popupHelper->display(
			$text,
			'HIKASHOP_CHECKOUT_TERMS',
			JRoute::_('index.php?option=com_hikashop&ctrl=checkout&task=termsandconditions&step='.$this->step.'&pos='.$this->module_position.'&tmpl=component'),
			'shop_terms_and_cond',
			(int)$this->options['popup_width'], (int)$this->options['popup_height'], '', '', 'link'
		);
	}
?>
	<label for="hikashop_checkout_terms_checkbox_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>"><?php echo $text; ?></label>
<?php
	if(empty($this->ajax)) { ?>
</div>
<?php }

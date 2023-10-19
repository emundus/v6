<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(!empty($this->legacy)) { ?>
<div id="hikashop_userpoints_status">
<?php } else {
	if(!$this->ajax) { ?>
<div id="hikashop_checkout_plg-shop-userpoints_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" class="hikashop_checkout_userpoints">
<?php } ?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner small_spinner"></div>
<?php } ?>
<?php if($this->display) { ?>
	<fieldset>
		<legend><?php echo JText::_('POINTS'); ?></legend>
		<span class="hikashop_userpoints_status_value"><?php
			if($points > 0) {
				echo JText::sprintf('USERPOINTS_HAVE_X_POINTS', $points);
			} else {
				echo JText::_('USERPOINTS_NO_POINTS');
			}
		?></span>
<?php
	if(!empty($consume)) {
?>
			<br/><span class="hikashop_userpoints_status_user"><?php echo JText::sprintf('USERPOINTS_USER_FOR_DISCOUNT', $consume['points'], $discount); ?></span>
<?php if(!empty($this->plugin_options['ask_no_coupon'])) { ?>
			<br/><span class="hikashop_userpoints_status_question"><span><?php echo JText::_('USERPOINTS_USE_DISCOUNT_QUESTION');?></span> <?php
				if(!empty($this->legacy)) {
					echo JHTML::_('hikaselect.booleanlist', 'userpoints_use_coupon', 'onchange="this.form.submit();"', $use_coupon);
				} else {
					echo JHTML::_('hikaselect.booleanlist', 'userpoints_use_coupon', "onchange=\"if(window.checkout) return window.checkout.submitUserpoints(".$this->step.",".$this->module_position.");\"", $use_coupon);
				}
			?></span>
<?php }
	}
	if($earn_points !== false && !empty($earn_points)) {
?>		<br/><span class="hikashop_userpoints_earn"><?php echo JText::sprintf('USERPOINTS_EARN_POINTS', $earn_points); ?></span>
<?php
	}
?>
	</fieldset>
<?php } ?>
<?php if(!empty($this->legacy)) { ?>
</div>
<?php } else if(!$this->ajax) { ?>
</div>
<script type="text/javascript">
if(!window.checkout) window.checkout = {};
window.Oby.registerAjax(['checkout.user.updated','checkout.cart.updated'], function(params){
	window.checkout.refreshUserpoints(<?php echo (int)$this->step; ?>, <?php echo (int)$this->module_position; ?>);
});
window.checkout.refreshUserpoints = function(step, id) { return window.checkout.refreshBlock('plg.shop.userpoints', step, id); };
window.checkout.submitUserpoints = function(step, id) {
	return window.checkout.submitBlock('plg.shop.userpoints', step, id);
};
</script>
<?php } ?>

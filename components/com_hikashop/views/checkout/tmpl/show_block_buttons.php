<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_checkout_buttons">
	<div class="buttons_left">
<!-- CONTINUE SHOPPING BUTTON -->
<?php
	$continue_shopping = $this->config->get('continue_shopping','');
	if(!empty($continue_shopping)) {
		$continue_shopping = hikashop_translate($continue_shopping);
		if(strpos($continue_shopping, 'Itemid') === false) {
			if(strpos($continue_shopping, 'index.php?') !== false) {
				global $Itemid;
				$url_itemid = (!empty($Itemid)) ? '&Itemid='.$Itemid : '';
				$continue_shopping .= $url_itemid;
			}
		}
		if(!preg_match('#^https?://#',$continue_shopping))
			$continue_shopping = JURI::base().ltrim($continue_shopping,'/');
?>
		<a href="<?php echo $continue_shopping; ?>" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn_checkout_continue" id="hikashop_checkout_shopping_button"><?php echo JText::_('CONTINUE_SHOPPING'); ?></a>
<?php
	}
?>
<!-- EO CONTINUE SHOPPING BUTTON -->
	</div>
	<div class="buttons_right">
<!-- NEXT BUTTON -->
		<button id="hikabtn_checkout_next" type="submit" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn-success hikabtn_checkout_next" onclick="return window.checkout.submitStep(this);"><?php
			$steps = count($this->checkoutHelper->checkout_workflow['steps']);
			$txt = JText::_('HIKA_NEXT');
			if(($this->step + 1) == $steps) {
				$k = 'CHECKOUT_BUTTON_FINISH';
				$txt = JText::_($k);
				if($txt == $k)
					$txt = JText::_('HIKA_NEXT');
			}
			echo $txt;
		?></button>
<!-- EO NEXT BUTTON -->
	</div>
	<div style="clear:both;"></div>
</div>

<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.0.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2018 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_checkout_buttons">
	<div class="buttons_left">
<?php
	$continue_shopping = $this->config->get('continue_shopping','');
	if(!empty($continue_shopping)) {
		if(strpos($continue_shopping, 'Itemid') === false) {
			if(strpos($continue_shopping, 'index.php?') !== false) {
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
	</div>
	<div class="buttons_right">
		<button id="hikabtn_checkout_next" type="submit" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn-success hikabtn_checkout_next" onclick="this.form.submit(); this.disabled=true; window.Oby.addClass(this, 'next_button_disabled'); return false;"><?php
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
	</div>
	<div style="clear:both;"></div>
</div>

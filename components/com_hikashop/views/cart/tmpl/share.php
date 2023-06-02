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
$labelcolumnclass = 'hkc-sm-4';
$inputcolumnclass = 'hkc-sm-8';
?>
<form action="<?php echo hikashop_completeLink('cart&task=sendshare'); ?>" id="hikashop_wishlist_share_form" name="hikashop_wishlist_share_form" method="post">
	<fieldset class="hkform-horizontal">
<!-- TITLE -->
		<legend><?php echo JText::_('SHARE_YOUR_WISHLIST'); ?></legend>
<!-- EO TITLE -->
<!-- LINK -->
<?php
if($this->cart->cart_share != 'nobody'){
?>
		<div class="hkform-group control-group hikashop_wishlist_link_line" id="hikashop_wishlist_link_line">
			<label id="linkmsg" for="cart_link" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKASHOP_WISHLIST_LINK'); ?></label>
			<div class="<?php echo $inputcolumnclass;?>">
				<span id="cart_link" style="word-break: break-word;"><?php echo $this->escape($this->cart_share_url); ?></span>
			</div>
		</div>
<?php
}
?>
<!-- EO LINK -->
<!-- EMAILS -->
		<div class="hkform-group control-group hikashop_wishlist_emails_line" id="hikashop_wishlist_emails_line">
			<label id="emailsmsg" for="hikashop_wishlist_share_emails" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('EMAILS_FOR_SHARING'); ?></label>
			<div class="<?php echo $inputcolumnclass;?>">
				<textarea class="hikashop_wishlist_share_emails" id="hikashop_wishlist_share_emails" rows="4" name="emails" placeholder="<?php echo JText::_('ENTER_HERE_THE_EMAILS_THAT_WILL_RECEIVE_THE_WISHLIST'); ?>"><?php echo $this->escape($this->emails); ?></textarea>
			</div>
		</div>
<!-- EO EMAILS -->
<!-- COPY -->
		<div class="hkform-group control-group hikashop_wishlist_copy_line" id="hikashop_wishlist_copy_line">
			<label id="copymsg" for="hikashop_wishlist_share_copy" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><input id="hikashop_wishlist_share_copy" value="1" name="copy" type="checkbox" <?php if(!empty($this->copy)) echo 'checked="checked"'; ?> /><?php echo JText::_('SEND_ME_A_COPY'); ?></label>
		</div>
<!-- EO COPY -->
	</fieldset>
	<div class="buttons_right">
<!-- SEND BUTTON -->
		<button type="submit" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn-primary hikabtn_share_ok" onclick="this.form.submit(); this.disabled=true; window.Oby.addClass(this, 'next_button_disabled'); return false;"><?php
			echo JText::_('MASS_SEND_EMAIL');
		?></button>
<!-- EO SEND BUTTON -->
	</div>
	<input type="hidden" name="cid" value="<?php echo $this->cart->cart_id;?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="sendshare" />
	<input type="hidden" name="ctrl" value="cart" />
	<input type="hidden" name="tmpl" value="component" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

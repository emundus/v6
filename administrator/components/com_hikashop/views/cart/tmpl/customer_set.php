<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo hikashop_completeLink('cart&task=customer_save') ;?>" method="post" name="hikashop_form" id="hikashop_form">
<div class="hika_confirm">
	<?php echo JText::_('HIKA_CONFIRM_USER')?><br/>
	<table class="admintable table hika_options">
		<tbody>
			<tr>
				<td class="key"><label><?php echo JText::_('HIKA_NAME'); ?></label></td>
				<td id="hikashop_order_customer_name"><?php echo $this->rows->name; ?></td>
			</tr>
			<tr>
				<td class="key"><label><?php echo JText::_('HIKA_EMAIL'); ?></label></td>
				<td id="hikashop_order_customer_email"><?php echo $this->rows->user_email; ?></td>
			</tr>
			<tr>
				<td class="key"><label><?php echo JText::_('ID'); ?></label></td>
				<td id="hikashop_order_customer_id"><?php echo $this->rows->user_id; ?></td>
			</tr>
		</tbody>
	</table>
	<input type="hidden" name="user_id" value="<?php echo $this->rows->user_id; ?>"/>
	<input type="hidden" name="cid" value="<?php echo $this->cart->cart_id; ?>"/>
	<input type="hidden" name="cart_id" value="<?php echo $this->cart->cart_id; ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="customer_save" />
	<input type="hidden" name="finalstep" value="1" />
	<input type="hidden" name="single" value="1" />
	<input type="hidden" name="ctrl" value="cart" />
	<input type="hidden" name="tmpl" value="component" />
	<?php echo JHTML::_('form.token'); ?>
	<div class="hika_confirm_btn">
		<?php
		if($this->rows->user_cms_id == '0'){
			echo JText::_('HIKA_CANT_SELECT_USER_NO_JOOMLA_ACCOUNT');
		}else{
		?>
		<button onclick="hikashop.submitform('customer_save', 'hikashop_form');" class="btn"><img src="<?php echo HIKASHOP_IMAGES ?>ok.png" style="vertical-align:middle" alt=""/> <span><?php echo Jtext::_('OK'); ?></span></button>
		<?php } ?>
	</div>
</div>
</form>

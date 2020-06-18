<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_nets_end" id="hikashop_nets_end">
	<span id="hikashop_nets_end_message" class="hikashop_nets_end_message"><?php
		echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X', $this->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');
	?></span>
	<span id="hikashop_nets_end_spinner" class="hikashop_nets_end_spinner hikashop_checkout_end_spinner"></span>
	<br/>
	<form id="hikashop_nets_form" name="hikashop_nets_form" action="<?php echo $this->redirect_url;?>" method="POST">
		<div id="hikashop_nets_end_image" class="hikashop_nets_end_image">
			<input id="hikashop_nets_button" type="submit" class="btn btn-primary" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
<?php
	hikaInput::get()->set('noform',1);
?>
	</form>
	<script type="text/javascript">
	<!--
		document.getElementById('hikashop_nets_form').submit();
	//-->
	</script>
</div>

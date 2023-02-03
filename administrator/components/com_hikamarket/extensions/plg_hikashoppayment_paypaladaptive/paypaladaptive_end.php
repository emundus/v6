<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_paypal_end" id="hikashop_paypal_end">
	<span id="hikashop_paypal_end_message" class="hikashop_paypal_end_message">
<?php
	if(!empty($this->display_mode) && $this->display_mode == 'popup') {
		echo JText::sprintf('PLEASE_WAIT_BEFORE_SUBMISSION_TO_X', $this->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOTHING');
	} else {
		echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X', $this->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');
	}
?>
	</span>
	<span id="hikashop_paypal_end_spinner" class="hikashop_paypal_end_spinner hikashop_checkout_end_spinner"></span>
	<br/>
	<form id="hikashop_paypal_form" name="hikashop_paypal_form" action="<?php echo $this->return_url;?>" target="<?php echo @$this->target; ?>" method="<?php if(!empty($this->display_mode) && $this->display_mode == 'popup') { echo 'GET'; } else { echo 'POST'; } ?>">
<?php
	if(empty($this->payment_params->classical)) {
?>
		<input id="paykey" type="hidden" name="paykey" value="<?php echo $this->paykey; ?>">
<?php if(!empty($this->display_mode) && $this->display_mode == 'popup') { ?>
		<input id="type" type="hidden" name="expType" value="light"> <!-- or "mini" -->
<?php }
	} else {
 		foreach($this->vars as $name => $value ) {
			echo '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars((string)$value).'" />';
		}
	}
?>
		<div id="hikashop_paypal_end_image" class="hikashop_paypal_end_image">
			<input id="hikashop_paypal_button" type="submit" class="hikabtn hikabtn-primary" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
<?php
	hikaInput::get()->set('noform', 1);
?>
	</form>
<?php if(!empty($this->display_mode) && $this->display_mode == 'popup') { ?>
	<script src="https://www.paypalobjects.com/js/external/dg.js"></script>
	<script>
		<!--
		var dgFlow = new PAYPAL.apps.DGFlow({ trigger: "hikashop_paypal_button" });
		function validatePaypalBox(url) {
			window.location = url;
		}
		//-->
	</script>
<?php
}
if(!$this->payment_params->debug) {
?>
	<script type="text/javascript">
		<!--
		document.getElementById('hikashop_paypal_form').submit();
		//-->
	</script>
<?php
} else {
	echo '<p><strong>[Debug mode] Please do a manual validation</strong></p>';
}
?>
</div>

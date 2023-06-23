<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><script src="https://www.paypal.com/sdk/js?<?php echo http_build_query($this->params); ?>"></script>
<div class="hikashop_paypalcheckout_end" id="hikashop_paypalcheckout_end">
<div id="paypal-select-message"><?php echo JText::_('PLEASE_SELECT_A_PAYMENT_METHOD'); ?></div>
<div id="paypal-button-container"></div>
<script>
paypal.Buttons(
	{
		createOrder: function(data, actions) {
			return actions.order.create(<?php echo json_encode($this->orderData, JSON_PRETTY_PRINT); ?>);
		},
		onApprove: function (data, actions) {
			return actions.order.capture().then(function (details) {
				window.location.href = "<?php echo $this->notify_url; ?>&paypal_id="+details.id;
			});
		},
		onError: function (err) {
			var errormsg = "<?php echo JText::sprintf('PAYMENT_REQUEST_REFUSED_BY_PAYPAL_CANCEL_URL', $this->cancel_url); ?>";

			var error = err.message.split("\n\n");
			if(error.length == 2) {
				var data = JSON.parse('{"data": '+error[1]+'}');
				if(data.data.details) {
					for(var i = 0; i < data.data.details.length; i++) {
						var details = data.data.details[i];
						var msg = '';
						if(details.issue)
							msg+='['+details.issue+'] ';
						if(details.description)
							msg+=details.description;
						if(msg.length)
							errormsg+='<br/>'+msg;
					}
				}
			}
			Joomla.renderMessages({"error":[errormsg]});
			var errDiv = document.getElementById('system-message-container');
			if(errDiv)
				errDiv.scrollIntoView();
		},
	}
).render('#paypal-button-container');
</script>
<style>
#paypal-button-container {
    text-align: center;
	max-width: 55px;
	margin: auto;
}
div#paypal-select-message {
    text-align: center;
    margin: 5px;
    font-weight: bold;
}
</style>
</div>

<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><tr>
	<td class="key">
		<label for="data[payment][payment_params][email]"><?php
			echo JText::_('HIKA_EMAIL');
		?></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][email]" value="<?php echo $this->escape(@$this->element->payment_params->email); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][classical]"><?php
			echo JText::_('PAYPAL_CLASSICAL');
		?></label>
	</td>
	<td><?php
		if(!isset($this->element->payment_params->classical))
			$this->element->payment_params->classical = false;
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][classical]" , ' onchange="pp_adative_classical(this);"', $this->element->payment_params->classical);
	?>
<script type="text/javascript">
function pp_adative_classical(el) {
	var value = (el.value == "1"), elements = document.getElementsByTagName('tr');
	for (var i = 0; i < elements.length; i++) {
		if(elements[i].className == "pp_adative_opt")
			elements[i].style.display = (value ? 'none' : '');
	}
}
window.hikashop.ready(function(){
	var el = {value:<?php echo (int)$this->element->payment_params->classical; ?>};
	pp_adative_classical(el);
});
</script>
	</td>
</tr>
<tr class="pp_adative_opt">
	<td class="key">
		<label for="data[payment][payment_params][username]"><?php
			echo JText::_('HIKA_USERNAME');
		?></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][username]" value="<?php echo $this->escape(@$this->element->payment_params->username); ?>" />
	</td>
</tr>
<tr class="pp_adative_opt">
	<td class="key">
		<label for="data[payment][payment_params][password]"><?php
			echo JText::_('HIKA_PASSWORD');
		?></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][password]" value="<?php echo $this->escape(@$this->element->payment_params->password); ?>" />
	</td>
</tr>
<tr class="pp_adative_opt">
	<td class="key">
		<label for="data[payment][payment_params][signature]"><?php
			echo JText::_('SIGNATURE');
		?></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][signature]" value="<?php echo $this->escape(@$this->element->payment_params->signature); ?>" />
	</td>
</tr>
<tr class="pp_adative_opt">
	<td class="key">
		<label for="data[payment][payment_params][applicationid]"><?php
			echo 'Application Id';
		?></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][applicationid]" value="<?php echo $this->escape(@$this->element->payment_params->applicationid); ?>" />
	</td>
</tr>
<tr class="pp_adative_opt">
	<td class="key">
		<label for="data[payment][payment_params][reverse_all_on_error]"><?php
			echo 'Reverse all on error';
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][reverse_all_on_error]" , '', @$this->element->payment_params->reverse_all_on_error);
	?></td>
</tr>
<tr class="pp_adative_opt">
	<td class="key">
		<label for="data[payment][payment_params][payment_mode]"><?php
			echo 'Payment mode';
		?></label>
	</td>
	<td><?php
		$arr = array(
			JHTML::_('select.option', 'chained', 'Chained'),
			JHTML::_('select.option', 'parallel', 'Parallel'),
		);
		echo JHTML::_('hikaselect.genericlist',  $arr, "data[payment][payment_params][payment_mode]", '', 'value', 'text', @$this->element->payment_params->payment_mode);
	?></td>
</tr>
<tr class="pp_adative_opt">
	<td class="key">
		<label for="data[payment][payment_params][display_mode]"><?php
			echo 'Display mode';
		?></label>
	</td>
	<td><?php
		$arr = array(
			JHTML::_('select.option', 'redirect', 'Redirect'),
			JHTML::_('select.option', 'popup', 'Popup'),
		);
		echo JHTML::_('hikaselect.genericlist',  $arr, "data[payment][payment_params][display_mode]", '', 'value', 'text', @$this->element->payment_params->display_mode);
	?></td>
</tr>
<tr class="pp_adative_opt">
	<td class="key">
		<label for="data[payment][payment_params][fee_mode]"><?php
			echo 'Fee mode';
		?></label>
	</td>
	<td><?php
		$arr = array(
			JHTML::_('select.option', 'each', 'Each Receiver'),
			JHTML::_('select.option', 'sender', 'Sender'),
			JHTML::_('select.option', 'primary', 'Primary Receiver'),
			JHTML::_('select.option', 'secondary', 'Secondary Receiver(s)'),
		);
		echo JHTML::_('hikaselect.genericlist',  $arr, "data[payment][payment_params][fee_mode]", '', 'value', 'text', @$this->element->payment_params->fee_mode);
	?></td>
</tr>
<tr class="pp_adative_opt">
	<td class="key">
		<label for="data[payment][payment_params][store_secondary]"><?php
			echo 'Put store as a secondary receiver';
		?></label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][store_secondary]" , '', @$this->element->payment_params->store_secondary); ?>
		<p>
			<em><strong>Important</strong>: This option is not recommended.<br/>
			It won't work correctly if you have several vendors in a single order.</em>
		</p>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][require_paypal_email_for_all]"><?php
			echo 'Require paypal email for all';
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][require_paypal_email_for_all]" , '', @$this->element->payment_params->require_paypal_email_for_all);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][debug]"><?php
			echo JText::_('DEBUG');
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][debug]" , '', @$this->element->payment_params->debug);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][sandbox]"><?php
			echo JText::_('SANDBOX');
		?></label>
	</td>
	<td><?php
		if(!isset($this->element->payment_params->sandbox) && isset($this->element->payment_params->debug))
			$this->element->payment_params->sandbox = $this->element->payment_params->debug;
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][sandbox]" , '', @$this->element->payment_params->sandbox);
	?></td>
</tr>
<tr class="pp_adative_opt">
	<td class="key">
		<label for="data[payment][payment_params][notify_wrong_emails]"><?php
			echo 'Notify for wrong emails';
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][notify_wrong_emails]" , '', @$this->element->payment_params->notify_wrong_emails);
	?></td>
</tr>
<tr class="pp_adative_opt">
	<td class="key">
		<label for="data[payment][payment_params][use_fsock]"><?php
			echo 'Use Raw sockets instead of cURL';
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][use_fsock]" , '', @$this->element->payment_params->use_fsock);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][validation]"><?php
			echo JText::_('ENABLE_VALIDATION').' (Classical only)'
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', 'data[payment][payment_params][validation]', '', @$this->element->payment_params->validation);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][cancel_url]"><?php
			echo JText::_('CANCEL_URL');
		?></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][cancel_url]" value="<?php echo $this->escape(@$this->element->payment_params->cancel_url); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][return_url]"><?php
			echo JText::_('RETURN_URL');
		?></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][return_url]" value="<?php echo $this->escape(@$this->element->payment_params->return_url); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][ips]"><?php
			echo JText::_('IPS');
		?></label>
	</td>
	<td>
		<textarea id="paypal_ips" name="data[payment][payment_params][ips]" ><?php echo (!empty($this->element->payment_params->ips) && is_array($this->element->payment_params->ips)?trim(implode(',',$this->element->payment_params->ips)):''); ?></textarea>
		<br/>
		<a href="#" onclick="return paypal_refreshIps();"><?php echo JText::_('REFRESH_IPS');?></a>
<script type="text/javascript">
function paypal_refreshIps() {
	var w = window, d = document, o = w.Oby;
	o.xRequest('<?php echo hikashop_completeLink('plugins&plugin_type=payment&task=edit&name='.$this->data['name'].'&subtask=ips',true,true);?>', null, function(xhr) {
		d.getElementById('paypal_ips').value = xhr.responseText;
	});
	return false;
}
</script>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][invalid_status]"><?php
			echo JText::_('INVALID_STATUS');
		?></label>
	</td>
	<td><?php
		echo $this->data['order_statuses']->display("data[payment][payment_params][invalid_status]", @$this->element->payment_params->invalid_status);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][pending_status]"><?php
			echo JText::_('PENDING_STATUS');
		?></label>
	</td>
	<td><?php
		echo $this->data['order_statuses']->display("data[payment][payment_params][pending_status]", @$this->element->payment_params->pending_status);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][verified_status]"><?php
			echo JText::_('VERIFIED_STATUS');
		?></label>
	</td>
	<td><?php
		echo $this->data['order_statuses']->display("data[payment][payment_params][verified_status]", @$this->element->payment_params->verified_status);
	?></td>
</tr>

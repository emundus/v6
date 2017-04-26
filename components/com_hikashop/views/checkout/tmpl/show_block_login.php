<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(!empty($this->options['current_login']) && empty($this->ajax))
	return;

if(!empty($this->options['current_login'])) {
?>
<script type="text/javascript">
var hct = document.getElementById('hikashop_checkout_token');
if(hct) hct.name = '<?php echo hikashop_getFormToken(); ?>';
if(window.checkout) window.checkout.token = '<?php echo hikashop_getFormToken(); ?>';
</script>
<?php
	return;
}

if(empty($this->ajax)) {
?>
<div id="hikashop_checkout_login_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" class="hikashop_checkout_login">
<?php
}
?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner"></div>
<?php
	$this->checkoutHelper->displayMessages('login');

	if(!empty($this->options['registration_invalid_fields'])){
?>
<script type="text/javascript">
<?php
		foreach($this->options['registration_invalid_fields'] as $id){
			if(is_numeric($id))
				continue;
?>
var invalid_field = document.getElementById('<?php echo $id; ?>');
if(invalid_field)
	window.Oby.addClass(invalid_field,'invalid');
<?php
		}
?>
</script>
<?php
	}

	$title = '';
	if($this->options['display_method'] == 0) {
		$title = !empty($this->options['registration_guest']) ? 'LOGIN_OR_GUEST' : 'LOGIN_OR_REGISTER_ACCOUNT';
	} else {
		if(!empty($this->options['show_login']) && ($this->options['registration_registration'] || $this->options['registration_simplified'] || $this->options['registration_password']) && $this->options['registration_guest'])
			$title = 'LOGIN_OR_REGISTER_ACCOUNT_OR_GUEST';
		else if(!empty($this->options['show_login']) && ($this->options['registration_registration'] || $this->options['registration_simplified'] || $this->options['registration_password']))
			$title = 'LOGIN_OR_REGISTER_ACCOUNT';
		else if(!empty($this->options['show_login']) && $this->options['registration_guest'])
			$title = 'LOGIN_OR_GUEST';
		else if(empty($this->options['show_login']) && ($this->options['registration_registration'] || $this->options['registration_simplified'] || $this->options['registration_password']) && $this->options['registration_guest'])
			$title = 'REGISTER_ACCOUNT_OR_GUEST';
	}

	if(!empty($title))
		echo '<h1>'.JText::_($title).'</h1>';

	if($this->options['show_login']) {
		$classLogin = '';
		$classRegistration = 'hikashop_hidden_checkout';
		$defaultSelection = $this->options['default_registration_view'];
	}

	if($this->options['display_method'] == 0) {
		if(empty($this->options['current_login']) && (!empty($this->options['registration']) || !empty($this->options['registration_not_allowed'])) && !empty($this->options['show_login'])) {
?>
		<div class="hk-container-fluid">
		<div class="hkc-lg-4">
<?php
		}

		if(empty($this->options['current_login']) && !empty($this->options['show_login'])) {
?>
			<div id="hikashop_checkout_login_form">
				<h2><?php echo JText::_('HIKA_LOGIN'); ?></h2>
<?php
			$this->setLayout('sub_block_login_form');
			echo $this->loadTemplate();
?>
			</div>
<?php
		}

		if(empty($this->options['current_login']) && (!empty($this->options['registration']) || !empty($this->options['registration_not_allowed'])) && !empty($this->options['show_login'])) {
?>
		</div>
		<div class="hkc-lg-8">
<?php
		}
		if(empty($this->options['current_login']) && !empty($this->options['registration'])) {
?>
			<div id="hikashop_checkout_registration">
				<h2><?php
					$txt = !empty($this->options['registration_guest']) ? 'GUEST' : 'HIKA_REGISTRATION';
					echo JText::_($txt);
				?></h2>
<?php
			if(!empty($this->options['registration']) || !empty($this->options['registration_guest'])) {
				$this->setLayout('sub_block_login_registration');
				echo $this->loadTemplate();
			}
?>
			</div>
<?php
		} else {
			echo JText::_('REGISTRATION_NOT_ALLOWED');
		}

		if(empty($this->options['current_login']) && (!empty($this->options['registration']) || !empty($this->options['registration_not_allowed'])) && !empty($this->options['show_login'])) {
?>
		</div>
		</div>
<?php
		}
	} else {
?>
	<!-- THIS IS THE SWITCHER DISPLAY, RADIO BUTTON ON THE LEFT, FORMS ON THE RIGHT-->
		<div class="hk-container-fluid">
<?php
		if(($this->options['show_login'] && $this->options['registration_count'] > 0) || $this->options['registration_count'] > 1) {

?>
			<div class="hkc-lg-4">
				<h2><?php echo JText::_('IDENTIFICATION'); ?></h2>
<?php
			$values = array();
			$v = null;
			if($this->options['show_login']) {
				$v = JHTML::_('select.option', 'login', JText::_('HIKA_LOGIN').'<br/>');
				$v->class = 'hikabtn-checkout-login';
				$values[] = $v;
			}
			if($this->options['registration_registration']) {
				$v = JHTML::_('select.option', 0, JText::_('HIKA_REGISTRATION').'<br/>');
				$v->class = 'hikabtn-checkout-registration';
				$values[] = $v;
			}
			if($this->options['registration_simplified']) {
				$v = JHTML::_('select.option', 1, JText::_('HIKA_REGISTRATION').'<br/>');
				$v->class = 'hikabtn-checkout-simplified';
				$values[] = $v;
			}
			if($this->options['registration_password']) {
				$v = JHTML::_('select.option', 3, JText::_('HIKA_REGISTRATION').'<br/>');
				$v->class = 'hikabtn-checkout-simplified-pwd';
				$values[] = $v;
			}
			if($this->options['registration_guest']) {
				$v = JHTML::_('select.option', 2, JText::_('GUEST').'<br/>');
				$v->class = 'hikabtn-checkout-guest';
				$values[] = $v;
			}
?>
<script type="text/javascript">
window.hikashop.ready(function(){
	var currentRegistrationSelection = window.document.getElementById('data_register_registration_method<?php echo $this->options['default_registration_view']; ?>');
	if(!currentRegistrationSelection) currentRegistrationSelection = window.document.getElementById('data[register][registration_method]<?php echo $this->options['default_registration_view']; ?>');
	displayRegistration(currentRegistrationSelection);
});
function displayRegistration(el) {
	if(!el)
		return;
	var d = document, value = el.value, checked = el.checked,
		name = d.getElementById("hikashop_registration_name_line"),
		username = d.getElementById("hikashop_registration_username_line"),
		pwd = d.getElementById("hikashop_registration_password_line"),
		pwd2 = d.getElementById("hikashop_registration_password2_line"),
		registration_div = d.getElementById("hikashop_checkout_registration"),
		login_div = d.getElementById("hikashop_checkout_login_form");

	if(!checked)
		return;

	if(value == "login") {
<?php if(!empty($this->options['registration_not_allowed'])){
		echo '
		els = registration_div.getElementsByTagName("fieldset");
		if(els)
			els = Array.prototype.slice.call(els);
		els.forEach(function(el) {
			el.className = "form-horizontal";
		});';
}?>	
		if(login_div)
			login_div.className = '';
		if(registration_div)
			registration_div.className = 'hikashop_hidden_checkout';
		return;
	}

	if(value == 0 || value == 1 || value == 3) {
		if(login_div)
			login_div.className="hikashop_hidden_checkout";
		if(registration_div)
			registration_div.className="";

		d.getElementById("hika_registration_type").innerHTML = "<?php echo JText::_('HIKA_REGISTRATION',true); ?>";
		d.getElementById("hikashop_register_form_button").firstChild.data = "<?php echo JText::_('HIKA_REGISTER',true); ?>";

<?php if(!empty($this->options['registration_not_allowed'])){
		echo '
		els = registration_div.getElementsByTagName("fieldset");
		if(els)
			els = Array.prototype.slice.call(els);
		els.forEach(function(el) {
			el.className = "hikashop_hidden_checkout";
		});
		return;';
}?>	

		if(value == 0) {
			if(name) name.style.display = "";
			if(username) username.style.display = "";
			if(pwd) pwd.style.display = "";
			if(pwd2) pwd2.style.display = "";
		} else if(value == 1) {
			if(name) name.style.display = "none";
			if(username) username.style.display = "none";
			if(pwd) pwd.style.display = "none";
			if(pwd2) pwd2.style.display = "none";
		} else if(value == 3) {
			if(pwd) pwd.style.display = "";
			if(pwd2) pwd2.style.display = "";
		}
	}

	if(value == 2) {
<?php if(!empty($this->options['registration_not_allowed'])){
		echo '
		els = registration_div.getElementsByTagName("fieldset");
		if(els)
			els = Array.prototype.slice.call(els);
		els.forEach(function(el) {
			el.className = "form-horizontal";
		});';
}?>	
		if(login_div)
			login_div.className = 'hikashop_hidden_checkout';
		if(registration_div)
			registration_div.className = '';

		d.getElementById("hika_registration_type").innerHTML = "<?php echo JText::_('GUEST',true); ?>";
		d.getElementById("hikashop_register_form_button").firstChild.data = "<?php echo JText::_('HIKA_NEXT',true); ?>";

		if(name) name.style.display = "none";
		if(username) username.style.display = "none";
		if(pwd) pwd.style.display = "none";
		if(pwd2) pwd2.style.display = "none";
	}
}
</script>
<?php
			echo JHTML::_('hikaselect.radiolist', $values, 'data[register][registration_method]', ' onchange="displayRegistration(this)"', 'value', 'text', $this->options['default_registration_view'], false, false, true);
?>
		</div>
<?php 	}
?>
		<div class="hkc-lg-8">
<?php
		if(empty($this->options['current_login']) && !empty($this->options['registration'])) {
?>
			<div id="hikashop_checkout_registration">
				<h2 id="hika_registration_type"><?php
					$txt = (!empty($this->options['registration_guest']) && $this->options['registration_count'] == 1) ? 'GUEST' : 'HIKA_REGISTRATION';
					echo JText::_($txt);
				?></h2>
<?php
			if(!empty($this->options['registration']) || !empty($this->options['registration_guest'])) {
				$this->setLayout('sub_block_login_registration');
				echo $this->loadTemplate();
			}
?>
			</div>
<?php
		} else {
			echo JText::_('REGISTRATION_NOT_ALLOWED');
		}
		if(empty($this->options['current_login']) && !empty($this->options['show_login'])) {
?>
			<div id="hikashop_checkout_login_form">
				<h2><?php echo JText::_('HIKA_LOGIN'); ?></h2>
<?php
			$this->setLayout('sub_block_login_form');
			echo $this->loadTemplate();
?>
			</div>
<?php
		}
?>
	</div>
</div>
<?php
	}
?>
	<input type="hidden" id="login_view_action_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" name="login_view_action" value="" />
<?php

if(empty($this->ajax)) {

?>
</div>

<script type="text/javascript">
if(!window.checkout) window.checkout = {};
window.checkout.refreshLogin = function(step, id) { return window.checkout.refreshBlock('login', step, id); };
window.checkout.submitLogin = function(step, id, action) {
	if(action === undefined)
		action = '';
	var el = document.getElementById('login_view_action_' + step + '_' + id);
	if(el)
		el.value = action;
	return window.checkout.submitBlock('login', step, id);
};
</script>
<?php

}

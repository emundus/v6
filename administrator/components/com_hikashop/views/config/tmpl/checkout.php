<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
echo $this->leftmenu(
	'checkout',
	array(
		'#checkout_workflow' => JText::_('CHECKOUT_FLOW'),
		'#checkout_checkout' => JText::_('CHECKOUT'),
		'#checkout_shipping' => JText::_('ADDRESS_SHIPPING'),
		'#checkout_login' => JText::_('LOGIN_REGISTRATION')
	)
);
?>
<div id="page-checkout" class="rightconfig-container <?php if(HIKASHOP_BACK_RESPONSIVE) echo 'rightconfig-container-j30';?>">

<!-- CHECKOUT - WORKFLOW -->
<div id="checkout_workflow" class="hikashop_backend_tile_edition"<?php echo $this->docTip('checkout_workflow');?>>
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('CHECKOUT_FLOW'); ?></div>
<?php
	$checkout_workflow = $this->config->get('checkout_workflow', '');
	if(empty($checkout_workflow))
		$checkout_workflow = $this->config->get('checkout');
	if((int)$this->config->get('checkout_legacy', 0) == 1) {
		echo $this->checkout_workflow->displayLegacy('config[checkout]', $this->config->get('checkout'));
	} else
	echo $this->checkout_workflow->display('config[checkout_workflow]', $checkout_workflow);
?>
	</div></div>
</div>

<!-- CHECKOUT - CHECKOUT -->
<div id="checkout_checkout" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('CHECKOUT'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('checkout_itemid');?>><?php echo JText::_('FORCE_MENU_ON_CHECKOUT'); ?></td>
		<td><?php
			echo $this->menusType->display('config[checkout_itemid]', $this->config->get('checkout_itemid', '0'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('force_ssl');?>><?php echo JText::_('CHECKOUT_FORCE_SSL'); ?></td>
		<td><?php
			$values = array(
				JHTML::_('select.option', 1, JText::_('HIKASHOP_YES')),
				JHTML::_('select.option', 0, JText::_('HIKASHOP_NO'))
			);
			if($this->config->get('force_ssl', 0) == 'url')
				$values [] = JHTML::_('select.option', 'url', JText::_('SHARED_SSL'));
			echo JHTML::_('hikaselect.radiolist', $values, 'config[force_ssl]', 'onchange="displaySslField()"', 'value', 'text', $this->config->get('force_ssl', 0));

			$hidden = ($this->config->get('force_ssl', 0) == 'url') ? '' : 'display:none';
			?><input class="inputbox" id="force_ssl_url" name="config[force_ssl_url]" type="text" size="20" value="<?php echo $this->config->get('force_ssl_url'); ?>" style="<?php echo $hidden; ?>" />
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('continue_shopping');?>><?php echo JText::_('CONTINUE_SHOPPING_BUTTON_URL');?></td>
		<td>
			<input name="config[continue_shopping]" type="text" value="<?php echo $this->config->get('continue_shopping');?>" />
		</td>
	</tr>
<?php
	if((int)$this->config->get('checkout_legacy', 0) == 1) {
?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('checkout_cart_delete');?>><?php echo JText::_('CHECKOUT_SHOW_CART_DELETE'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[checkout_cart_delete]', '', $this->config->get('checkout_cart_delete'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('checkout_terms');?>><?php echo JText::_('HIKASHOP_CHECKOUT_TERMS'); ?></td>
		<td>
			<input class="inputbox" id="checkout_terms" name="config[checkout_terms]" type="text" size="20" value="<?php echo $this->config->get('checkout_terms'); ?>" onchange="showTermsPopupSize(this.value);" >
<?php

		$link = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;object=content&amp;function=jSelectArticle_checkout';
		$js = '
function jSelectArticle_checkout(id, title, catid, object) {
	document.getElementById("checkout_terms").value = id;
	hikashop.closeBox();
}
';
		$this->doc->addScriptDeclaration($js);

		echo $this->popup->display(
			'<button type="button" class="btn" onclick="return false">'.JText::_('Select').'</button>',
			'TERMS_AND_CONDITIONS_SELECT_ARTICLE',
			$link,
			'checkout_terms_link',
			760, 480, '', '', 'link'
		);
?>
		</td>
	</tr>
<?php
		$js = '
function showTermsPopupSize(value) {
	if(value != "") {
		jQuery("#checkout_terms_size").css("display", "table-row");
	} else {
		jQuery("#checkout_terms_size").css("display", "none");
	}
}
jQuery(document).ready(function(){
	var checkoutTerms = jQuery("#checkout_terms").val();
	showTermsPopupSize(checkoutTerms);
});
';
		$this->doc->addScriptDeclaration($js);
?>
	<tr id="checkout_terms_size">
		<td class="hk_tbl_key"<?php echo $this->docTip('terms_and_conditions_xy');?>><?php echo JText::_('TERMS_AND_CONDITIONS_POPUP_SIZE'); ?></td>
		<td>
			<input type="text" style="width:50px;" class="inputbox" name="config[terms_and_conditions_width]" value="<?php echo $this->escape($this->config->get('terms_and_conditions_width','450'));?>"/>
			x
			<input type="text" style="width:50px;" class="inputbox" name="config[terms_and_conditions_height]" value="<?php echo $this->escape($this->config->get('terms_and_conditions_height','450'));?>"/>
			px
		</td>
	</tr>
<?php
	}
?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('display_checkout_bar');?>><?php echo JText::_('DISPLAY_CHECKOUT_BAR'); ?></td>
		<td><?php
			echo $this->checkout->display('config[display_checkout_bar]', $this->config->get('display_checkout_bar'));
		?></td>
	</tr>
<?php
	if((int)$this->config->get('checkout_legacy', 0) == 1) {
?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('show_cart_image');?>><?php echo JText::_('SHOW_IMAGE'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[show_cart_image]','',$this->config->get('show_cart_image'));
		?></td>
	</tr>
<?php
	}
?>
</table>
	</div></div>
</div>

<!-- CHECKOUT - SHIPPING/PAYMENT -->
<div id="checkout_shipping" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('ADDRESS_SHIPPING'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('force_shipping');?>><?php echo JText::_('FORCE_SHIPPING_REGARDLESS_OF_WEIGHT'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[force_shipping]', '', $this->config->get('force_shipping', 0));
		?></td>
	</tr>
<?php
	if((int)$this->config->get('checkout_legacy', 0) == 1) {
?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('checkout_address_selector');?>><?php echo JText::_('HIKASHOP_CHECKOUT_ADDRESS_SELECTOR'); ?></td>
		<td><?php
			$values = array(
				JHTML::_('select.option', 1, JText::_('HIKASHOP_CHECKOUT_ADDRESS_SELECTOR_LIST')),
				JHTML::_('select.option', 2, JText::_('HIKASHOP_CHECKOUT_ADDRESS_SELECTOR_DROPDOWN'))
			);
			$selector = $this->config->get('checkout_address_selector',0);
			if($this->config->get('checkout_legacy', 0))
				$values[] = JHTML::_('select.option', 0, JText::_('HIKASHOP_CHECKOUT_ADDRESS_SELECTOR_POPUP'));
			elseif( $selector == 0 )
				$selector = 1;
			echo JHTML::_('hikaselect.radiolist',  $values, 'config[checkout_address_selector]', 'class="custom-select"', 'value', 'text', $selector );
		?></td>
	</tr>
<?php
	}
?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('mini_address_format');?>><?php echo JText::_('MINI_ADDRESS_FORMAT'); ?></td>
		<td>
			<input type="text" style="width:100%;" name="config[mini_address_format]" value="<?php
				$value = $this->config->get('mini_address_format', '');
				if(empty($value))
					$value = '{address_lastname} {address_firstname} - {address_street}, {address_state} ({address_country})';
				echo $this->escape($value);
			?>"/>
		</td>
	</tr>
<?php if(!empty($this->address_format)){ ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('address_format');?>><?php echo JText::_('ADDRESS_FORMAT'); ?></td>
		<td id="address_format">
			<textarea cols="65" rows="8" id="address_format_textarea" name="config_address_format"><?php echo $this->address_format; ?></textarea>
<?php
		$js = '
			window.hikashop.ready(function(){
				var el = document.getElementById(\'address_format_textarea\');
				window.hikashop.address = el.value;
				el.form.addEventListener(\'submit\', function(e) {
					if(el.value == window.hikashop.address)
						el.value = \'\';
				});
			});
			';
		if(!empty($this->address_format_reset)){
			$js .= '
			function resetAddressFormat() {
				if (!confirm(\''.JText::_('PROCESS_CONFIRMATION').'\'))
					return false;
				window.Oby.xRequest(\''. hikashop_completeLink('config&task=resetAddressFormat'.'&'.hikashop_getFormToken().'=1', true, false, true).'\', {mode: \'GET\'}, function(x,p) {
					var r = window.Oby.evalJSON(x.responseText);
					document.getElementById(\'address_format_reset_button\').style.display = \'none\';
					if(r.result){
						document.getElementById(\'address_format\').innerHTML += \'<br/>\' + r.message;
						document.getElementById(\'address_format_textarea\').value = r.defaultFormat;
						return false;
					}
					document.getElementById(\'address_format\').innerHTML += \'<br/>\' + r.error;
					return false;
				});
			}
			';
?>
			<button type="button" id="address_format_reset_button" class="btn" onclick="return resetAddressFormat();"><?php echo JText::_('RESET_ADDRESS_FORMAT_TO_DEFAULT'); ?></button>
<?php
		}
		$this->doc->addScriptDeclaration($js);
?>
		</td>
	</tr>
<?php } ?>
</table>
	</div></div>
</div>

<!-- CHECKOUT - LOGIN/REGISTRATION -->
<div id="checkout_login" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('LOGIN_REGISTRATION'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('display_login');?>><?php echo JText::_('HIKA_LOGIN'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[display_login]','onclick="changeDefaultRegistrationViewType();"',$this->config->get('display_login',1));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('display_method');?>><?php echo JText::_('REGISTRATION_DISPLAY_METHOD'); ?></td>
		<td><?php
			if(hikashop_level(1)) {
				echo $this->display_method->display('config[display_method]', $this->config->get('display_method', 0));
			} else {
				echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
			}
		?></td>
	</tr>
	<tr id="default_registration_view_tr">
		<td class="hk_tbl_key"<?php echo $this->docTip('default_registration_view');?>><?php echo JText::_('DEFAULT_REGISTRATION_VIEW'); ?></td>
		<td><?php
			if(hikashop_level(1)) {
				echo $this->default_registration_view->display('config[default_registration_view]',$this->config->get('default_registration_view','login'));
			} else {
				echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
			}
		?></td>
	</tr>
	<tr id="default_registration_select">
		<td class="hk_tbl_key"<?php echo $this->docTip('simplified_registration');?>><?php echo JText::_('HIKA_REGISTRATION'); ?></td>
		<td><?php
	if(hikashop_level(1)) {
		$display = (int)$this->config->get('display_method', 0);
		$type = ($display == 1) ? 'checkbox' : "radio";
		$registration = $this->config->get('simplified_registration', 0);
		$registration = explode(',',$registration);
?>
		<label>
			<input <?php if(in_array('0',$registration)) echo 'checked="checked"'; ?> onchange="registrationAvailable(this.value, this.checked)" style="margin-right: 5px;" type="<?php echo $type;?>" value="0" name="config[simplified_registration][]" id="config_simplified_registration_normal"/>
			<?php echo JText::_('HIKA_REGISTRATION'); ?>
		</label>
		<label>
			<input <?php if(in_array('1',$registration)) echo 'checked="checked"'; ?> onchange="registrationAvailable(this.value, this.checked)" style="margin-right: 5px;" type="<?php echo $type;?>" value="1" name="config[simplified_registration][]" id="config_simplified_registration_simple"/>
			<?php echo JText::_('SIMPLIFIED_REGISTRATION'); ?>
		</label>
		<label>
			<input <?php if(in_array('3',$registration)) echo 'checked="checked"'; ?> onchange="registrationAvailable(this.value, this.checked)" style="margin-right: 5px;" type="<?php echo $type;?>" value="3" name="config[simplified_registration][]" id="config_simplified_registration_simple_pwd"/>
			<?php echo JText::_('SIMPLIFIED_REGISTRATION_WITH_PASSWORD'); ?>
		</label>
		<label>
			<input <?php if(in_array('2',$registration)) echo 'checked="checked"'; ?> onchange="registrationAvailable(this.value, this.checked)" style="margin-right: 5px;" type="<?php echo $type;?>" value="2" name="config[simplified_registration][]" id="config_simplified_registration_guest"/>
			<?php echo JText::_('GUEST'); ?>
		</label>
<?php
	} else {
		echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
	}
	$joomla_params = JComponentHelper::getParams('com_users');
	if((int)$joomla_params->get('allowUserRegistration') == 0){
		echo '<p style="color:red">'.JText::_('JOOMLA_REGISTRATION_DEACTIVATED').'</p>';
	}
	?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('show_email_confirmation_field');?>><?php echo JText::_('DISPLAY_EMAIL_CONFIRMATION_FIELD'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[show_email_confirmation_field]','',$this->config->get('show_email_confirmation_field',0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('address_on_registration');?>><?php echo JText::_('ASK_ADDRESS_ON_REGISTRATION'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[address_on_registration]','',$this->config->get('address_on_registration',1));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('user_group_registration');?>><?php echo JText::_('HIKA_USERGROUP_ON_REGISTRATION'); ?></td>
		<td><?php
			echo $this->joomlaAclType->displayList('config[user_group_registration]', $this->config->get('user_group_registration', ''), 'HIKA_INHERIT');
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('show_register_after_guest');?>><?php echo JText::_('ALLOW_REGISTRATION_AFTER_GUEST_CHECKOUT'); ?></td>
		<td><?php
			if(hikashop_level(1)) {
				if((int)$joomla_params->get('allowUserRegistration') == 0)
					echo JText::_('IMPOSSIBLE_WITH_JOOMLA_REGISTRATION_DEACTIVATED');
				elseif(in_array('2',$registration))
					echo JHTML::_('hikaselect.booleanlist', 'config[register_after_guest]','',$this->config->get('register_after_guest', 1));
				else
					echo JText::_('ONLY_WITH_GUEST_MODE');
			} else {
				echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
			}
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('user_ip');?>><?php echo JText::_('LOG_IP_ADDRESS'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[user_ip]', '', $this->config->get('user_ip', 1));
		?></td>
	</tr>
</table>
	</div></div>
</div>

<?php if(!empty($registration)){ ?>
<script type="text/javascript">
<?php
	foreach($registration as $key){
		if(!empty($key) && $key!=2)	echo 'registrationAvailable('.$key.', true);';
	} ?>
</script>
<?php } ?>

</div>

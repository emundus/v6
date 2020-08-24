<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if ($this->payment_params->api == 'dpm' && @$this->payment_params->iframe){
		$url=urlencode(base64_encode(json_encode($this->vars)));
	?>
	<iframe name="frame" scrolling="auto" height="1000" width="660" Frameborder="no" src="<?php echo $this->vars["x_relay_url"].'&iframe='.$url;?>"></iframe>
<?php return;
} ?>
<div class="hikashop_authorize_end" id="hikashop_authorize_end">
	<?php if ($this->payment_params->api == 'sim') {?>
		<span id="hikashop_authorize_end_message" class="hikashop_authorize_end_message">
			<?php echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X',$this->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');?>
		</span>
		<span id="hikashop_authorize_end_spinner" class="hikashop_authorize_end_spinner">
			<img src="<?php echo HIKASHOP_IMAGES.'spinner.gif';?>" />
		</span>
		<br/>
		<?php } ?>
		<form id="hikashop_authorize_form" name="hikashop_authorize_form" action="<?php echo $this->payment_params->url;?>" method="post">
			<?php
			foreach($this->vars as $name => $value) {
				if(is_array($value)) {
					foreach ($value as $v) {
						echo '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($v) . '" />';
					}
				} else {
					echo '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '" />';
				}
			}
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration("window.hikashop.ready( function() {document.getElementById('hikashop_authorize_form').submit();});");
			hikaInput::get()->set('noform',1);
		?>
		<div id="hikashop_authorize_end_image" class="hikashop_authorize_end_image">
			<input id="hikashop_authorize_button" type="submit" class="btn btn-primary" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
	</form>
</div>

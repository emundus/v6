<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo hikamarket::completeLink('order', true); ?>" method="post" name="hikamarket_mail_form" id="hikamarket_mail_form">

<div id="hikamarket_email_preview_settings">
<dl class="hikam_options large">

	<dt><label for="data[mail][dst_email]"><?php echo JText::_('TO_ADDRESS'); ?></label></dt>
	<dd><?php
		$values = array(
			0 => JText::_('CUSTOMER'),
			1 => JText::_('HIKA_VENDOR'),
		);
		echo JHTML::_('select.genericlist', $values, 'data[mail][dst_email]', '', 'value', 'text', 0);
	?></dd>

	<dt><label for="data[mail][subject]"><?php echo JText::_('EMAIL_SUBJECT'); ?></label></dt>
	<dd>
		<input type="text" name="data[mail][subject]" size="80" style="width:90%" value="<?php echo $this->escape($this->element->mail->subject); ?>" />
	</dd>

<?php
	foreach($this->element->mail_params_config as $k => $v) {
?>
	<dt><label for="data[mail][params][<?php echo $k; ?>]"><?php echo JText::_($v[1]); ?></label></dt>
	<dd><?php
		switch($v[0]) {
			case 'textarea':
				$placeholder = !empty($v[3]) ? JText::_($v[3], true) : JText::_('HIKA_INHERIT', true);
				echo '<textarea name="data[mail][params]['.$k.']" style="width:90%;height:6em;" placeholder="'.$placeholder.'" onblur="window.localPage.refreshMail()">'.@$this->element->mail_params[$k].'</textarea>';
				break;
			case 'boolean':
				echo $this->radioType->booleanlist('data[mail][params]['.$k.']', 'onchange="window.localPage.refreshMail()"', @$this->element->mail_params[$k]);
				break;
		}
	?></dd>
<?php
	}
?>
</dl>
</div>
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="order_id" value="<?php echo (int)$this->element->order_id; ?>" />
	<input type="hidden" name="task" value="mail" />
	<input type="hidden" name="ctrl" value="order" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

<script>
if(!window.localPage) window.localPage = {};
window.localPage.refreshMail = function() {
	var w = window, d = document, o = w.Oby, el = d.getElementById('hikamarket_mail_preview'),
		data = o.getFormData('hikamarket_email_preview_settings', true);
	o.addClass(el, "hikamarket_ajax_loading");
	o.xRequest("<?php echo hikamarket::completeLink('order&task=previewmail&order_id='.(int)$this->element->order_id); ?>", {mode:'POST',data:data,update:'hikamarket_mail_preview'}, function(xhr){
		o.removeClass(el, "hikamarket_ajax_loading");
	});
};
</script>

<h4><?php echo JText::_('PREVIEW_EMAIL'); ?></h4>
<div id="hikamarket_mail_preview" class="hikamarket_mail_preview">
<?php
	$this->setLayout('previewmail');
	echo $this->loadTemplate();
?>
</div>

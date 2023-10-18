<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if( !$this->singleSelection ) { ?>
<fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="hikashop_setId(this);"><img style="vertical-align:middle" src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('OK'); ?></button>
	</div>
<script type="text/javascript">
function hikashop_setId(el) {
	if(document.hikashop_form.boxchecked.value==0){
		alert('<?php echo JText::_('PLEASE_SELECT_SOMETHING', true); ?>');
	}else{
		el.form.ctrl.value = '<?php echo $this->ctrl ?>';
		hikashop.submitform("<?php echo $this->task; ?>",el.form);
	}
}
</script>
</fieldset>
<?php } ?>
<form action="<?php echo hikashop_completeLink('product'); ?>" method="post" name="adminForm" id="adminForm">
	<dl>
		<dt>
<?php
echo JText::_('PLEASE_SELECT_A_PRODUCT_FIRST');
?>
		</dt>
		<dd>
<?php
echo $this->nameboxType->display(
	'cid',
	'',
	($this->singleSelection ? hikashopNameboxType::NAMEBOX_SINGLE : hikashopNameboxType::NAMEBOX_MULTIPLE),
	'product',
	array(
		'delete' => true,
		'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
		'variants' => 2,
		'url_params' => array('VARIANTS' => 2)
	)
);
?>
		</dd>
	</dl>
<?php if( $this->singleSelection ) { ?>
	<input type="hidden" name="pid" value="0" />
<?php } ?>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="<?php echo $this->task; ?>" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="selection" value="products" />
	<input type="hidden" name="after" value="<?php echo hikaInput::get()->getVar('after', ''); ?>" />
	<input type="hidden" name="afterParams" value="<?php echo hikaInput::get()->getVar('afterParams', ''); ?>" />
	<input type="hidden" name="confirm" value="<?php echo $this->confirm ? '1' : '0'; ?>" />
	<input type="hidden" name="single" value="<?php echo $this->singleSelection ? '1' : '0'; ?>" />
	<input type="hidden" name="ctrl" value="<?php echo $this->ctrl ?>" />
	<input type="hidden" name="boxchecked" value="0" />
<?php
	if(!empty($this->afterParams)) {
		foreach($this->afterParams as $p) {
			if(empty($p[0]) || !isset($p[1]))
				continue;
			echo '<input type="hidden" name="'.$this->escape($p[0]).'" value="'.$this->escape($p[1]).'"/>' . "\r\n";
		}
		echo '<input type="hidden" name="after" value="'.hikaInput::get()->getString('after', '').'"/>'."\r\n";
		echo '<input type="hidden" name="afterParams" value="'.hikaInput::get()->getString('afterParams', '').'"/>'."\r\n";
	}
?>
	<?php echo JHTML::_('form.token'); ?>
</form>
<?php if($this->singleSelection) { ?>
<script>
window.Oby.ready(function() {
	setTimeout(() => {
		var w = window, ona = 'cid';
		if(!w.oNameboxes[ona])
			return;

		w.oNameboxes[ona].register('set', function(e) {
			var form = document.getElementById("adminForm");
			hikashop.submitform("<?php echo $this->task; ?>", form);
			document.adminForm.submit();
		});

		els = document.querySelectorAll('input[type=text]');
		if(!els.length)
			return true;
		for(var idx = 0 ; idx < els.length ; idx++) {
			els[idx].addEventListener('keydown', function(e) {
				if(e.key === undefined && e.keyCode === undefined && e.which === undefined)
					return;
				if((e.key !== undefined && e.key != "Enter") || (e.keyCode !== undefined && e.keyCode != 13) || (e.which !== undefined && e.which != 13))
					return;
				e.preventDefault();
			});
		}
	}, 500);
});
</script>
<?php } ?>
<script type="text/javascript">
document.adminForm = document.getElementById("hikashop_form");
</script>

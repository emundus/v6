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

?>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('email'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<?php if(hikaInput::get()->getString('tmpl') == 'component') { ?>
	<div style="min-height: 50px;">
		<h1 style="float:left;">
			<?php echo Jtext::_('REPORT_EDIT'); ?>
		</h1>
		<div class="toolbar" id="toolbar" style="float: right;">
			<button class="btn btn-success" type="button" onclick="javascript:submitbutton('apply'); return false;"><i class="fa fa-save"></i> <?php echo JText::_('HIKA_SAVE',true); ?></button>
		</div>
	</div>
<?php } ?>
<?php
	echo $this->loadTemplate('param');
?>
	<div class="hikashop_backend_tile_edition">
		<div class="hkc-xl-12 hkc-lg-12 hikashop_tile_block hikashop_mail_edit_html"><div>
			<div class="hikashop_tile_title">
<?php			echo JText::_('HTML_VERSION');

				if(!empty($this->mail->html_override)) {
					echo ' '.$this->popupHelper->display(
						'<span style="text-transform: none;" class="btn btn-primary"><i class="fa fa-file" aria-hidden="true"></i> '.strtoupper(JText::_('SEE_MODIFICATIONS')) . '</span>',
						'HIKASHOP_MODIFICATIONS',
						'\''.'index.php?option=com_hikashop&amp;tmpl=component&amp;ctrl=email&amp;task=diff&amp;type=html&amp;mail_name='.$this->mail_name.'\'',
						'hikashop_html_diff',
						$this->config->get('email_diff_popup_width',1080),$this->config->get('email_diff_popup_height',640), 'title="'.JText::_('HIKASHOP_MODIFICATIONS').'"', '', 'link',true
					);
				}
				if(@$this->mail_name == 'order_status_notification') {

					echo ' '.$this->popupHelper->display(
						'<span style="text-transform: none;" class="btn btn-primary"><i class="fa fa-magic" aria-hidden="true"></i> '.JText::_('PER_STATUS_OVERRIDE') . '</span>',
						'PER_STATUS_OVERRIDE',
						'\''.'index.php?option=com_hikashop&amp;tmpl=component&amp;ctrl=email&amp;task=orderstatus&amp;type=html&amp;email_name='.$this->mail_name.'\'',
						'hikashop_edit_html_status',
						$this->config->get('email_status_popup_width',1080),$this->config->get('email_status_popup_height',640), 'title="'.JText::_('PER_STATUS_OVERRIDE').'"', '', 'link',true
					);
				}
				if (in_array(@$this->mail_name, $this->tag_documentation['main_page_array'] ) ) { ?>
				<a class="hikashop_filter_collapsable_title btn btn-primary" onclick="return window.emailMgr.toggleTags(this);" href="#">
					<div class="hikashop_tag_button">
						<i class="fas fa-info"></i>
						<span id="hikashop_filter_collapsable_title_text"> <?php echo JText::_('HIKA_SEE_TITLE'); ?></span>
					</div>
				</a>
<?php } ?>
			</div>
			<div id="hikashop_html_version_main_div" style="padding-top:8px;">
<!--
			<link rel="stylesheet" href="//unpkg.com/grapesjs/dist/css/grapes.min.css">
			<script src="//unpkg.com/grapesjs"></script>
			<link rel="stylesheet" href="//unpkg.com/grapesjs-preset-newsletter/dist/grapesjs-preset-newsletter.css">
			<script src="//unpkg.com/grapesjs-preset-newsletter"></script>
			<div id="gjs">
				<?php //echo @$this->mail->body; ?>
			</div>
			<script>
const editor = grapesjs.init({
  container: '#gjs',
  plugins: ['gjs-preset-newsletter'],
  fromElement: true,
  height: '300px',
  width: 'auto',
  storageManager: false,
});
editor.BlockManager.add('fcfield', {
	label: 'Mon block',
	category: 'Data',
  	category: 'HikaShop',
content: '<div style="display: inline-block" data-gjs-resizable="true" data-gjs-dragMode="absolute">{VAR:BILLING_ADDRESS}</div>',
select: true,
activate: true,
});
			</script>
		-->
<?php
				echo $this->editor->displayCode(
					'data[mail][body]',
					@$this->mail->body,
					array('autoFocus' => false)
				);
?>
			</div>
<?php

			if (in_array(@$this->mail_name, $this->tag_documentation['main_page_array'] ) ) { ?>
			<div id="hikashop_mail_ref_array" class="hikashop_mail_ref_array" style="display: none;">
				<br/>
				<h4><?php echo JText::_('HIKA_TAG_TITLE'); ?></h4>
				<p style="margin: 0px 0px 10px 10px;border: 1px solid #d7d7d7;border-width: 0px 0px 1px 0px;"><?php echo JText::_('HIKA_EMAIL_TAG_INFO'); ?></p>
				<div id ="hikashop_mail_edit_var" class="hikashop_mail_edit_var">
					<p style="margin-left:10px;height:46px;"><?php echo JText::_('HIKA_EMAIL_VAR_INFO'); ?></p>
					<div class="hikashop_mail_edit_var_array">
				<?php	foreach ($this->tag_documentation['var_array'] as $key => $value) { ?>
						<div class="hikashop_mail_edit_var_header"><?php echo $key; ?></div>
<?php
							foreach ($value as $translation => $ref)	{
								$custom_fields = 0;
								switch($key) {
									case 'order_field':
										$custom_fields = 1;
										break;
									case 'customer_field':
										$custom_fields = 1;
										break;
								}

								if (($key == 'billing_address') || ($key == 'shipping_address') || ($custom_fields)) {
									echo '<p>'.$ref.'</p>';
								}
								else { ?>
							<a onclick="window.emailMgr.copyToClipboard('{VAR:<?php echo $key; ?>.<?php echo $ref; ?>}'); return false;" href="#" >
								<p data-toggle="hk-tooltip" data-title="<?php echo JText::_($translation); ?>" data-original-title="" title="">
									{VAR:<span class="hikashop_mail_tag_key_table"><?php echo $key; ?></span>.<span class="hikashop_mail_tag_key_column"><?php echo $ref; ?></span>}
								</p>
							</a>
<?php
								}
							}
						} ?>
					</div>
				</div>
				<div class="hikashop_mail_edit_linevar">
					<p style="margin-left:10px;height:46px;"><?php echo JText::_('HIKA_EMAIL_LINEVAR_INFO'); ?>
					<a style="font-size:0.9em;" rel="nofollow" onclick="return window.hikashop.openBox(this);" id="hikashop_linevar_screen" href="https://www.hikashop.com/images/stories/linevar-position.png" data-hk-popup="vex" data-vex="{x:850, y:260}">
						<?php echo JText::_('HIKA_EMAIL_LINEVAR_POS'); ?>
					</a>
					</p>
					<div class="hikashop_mail_edit_linevar_array">
				<?php	foreach ($this->tag_documentation['linevar_array'] as $key => $value) { ?>
						<div class="hikashop_mail_edit_var_header"><?php echo $key; ?></div>
<?php
							foreach ($value as $translation => $ref) {
								$custom_fields = 0;
								switch($key) {
									case 'item_field':
										$custom_fields = 1;
										break;
									case 'product_field':
										$custom_fields = 1;
										break;
								}
								if ($custom_fields) {
									echo '<p>'.$ref.'</p>';
								}
								else { ?>
							<a onclick="window.emailMgr.copyToClipboard('{LINEVAR:<?php echo $key; ?>.<?php echo $ref; ?>}'); return false;" href="#" >
								<p data-toggle="hk-tooltip" data-title="<?php echo JText::_($translation); ?>" data-original-title="" title="">
									{LINEVAR:<span class="hikashop_mail_tag_key_table"><?php echo $key; ?></span>.<span class="hikashop_mail_tag_key_column"><?php echo $ref; ?></span>}
								</p>
							</a>
<?php
								}
							}
						} ?>
					</div>
				</div>
			</div>
			<?php } ?>
			<div style="clear:both"></div>
		</div></div>
		<div class="hkc-xl-12 hkc-lg-12 hikashop_tile_block hikashop_mail_edit_text"><div>
			<div class="hikashop_tile_title">
<?php
				echo JText::_('TEXT_VERSION');

				if(!empty($this->mail->text_override)) {
					echo ' '.$this->popupHelper->display(
						'<span style="text-transform: none;" class="btn btn-primary"><i class="fa fa-file" aria-hidden="true"></i> '.strtoupper(JText::_('SEE_MODIFICATIONS')) . '</span>',
						'HIKASHOP_MODIFICATIONS',
						'\''.'index.php?option=com_hikashop&amp;tmpl=component&amp;ctrl=email&amp;task=diff&amp;type=text&amp;mail_name='.$this->mail_name.'\'',
						'hikashop_text_diff',
						$this->config->get('email_diff_popup_width',1080),$this->config->get('email_diff_popup_height',640), 'title="'.JText::_('HIKASHOP_MODIFICATIONS').'"', '', 'link',true
					);
				}

				if(@$this->mail_name == 'order_status_notification') {
					$popupHelper = hikashop_get('helper.popup');
					echo ' ' . $popupHelper->display(
						'<span style="text-transform: none;" class="btn btn-primary"><i class="fa fa-magic" aria-hidden="true"></i> '.JText::_('PER_STATUS_OVERRIDE') . '</span>',
						'PER_STATUS_OVERRIDE',
						'\''.'index.php?option=com_hikashop&amp;tmpl=component&amp;ctrl=email&amp;task=orderstatus&amp;type=text&amp;email_name='.$this->mail_name.'\'',
						'hikashop_edit_text_status',
						$this->config->get('email_status_popup_width',1080),$this->config->get('email_status_popup_height',640), 'title="'.JText::_('PER_STATUS_OVERRIDE').'"', '', 'link',true
					);
				}
?>
			</div>
				<textarea style="width:100%" rows="20" name="data[mail][altbody]" id="altbody" ><?php echo @$this->mail->altbody; ?></textarea>
			</div>
		</div>
		<div class="hkc-xl-12 hkc-lg-12 hikashop_tile_block hikashop_mail_edit_preload" id="preloadfieldset"><div>
			<div class="hikashop_tile_title">
<?php
				echo JText::_('PRELOAD_VERSION');

				if(!empty($this->mail->preload_override)) {
					echo ' '.$this->popupHelper->display(
						'<span style="text-transform: none;" class="btn btn-primary"><i class="fa fa-file" aria-hidden="true"></i> '.strtoupper(JText::_('SEE_MODIFICATIONS')) . '</span>',
						'HIKASHOP_MODIFICATIONS',
						'\''.'index.php?option=com_hikashop&amp;tmpl=component&amp;ctrl=email&amp;task=diff&amp;type=preload&amp;mail_name='.$this->mail_name.'\'',
						'hikashop_preload_diff',
						$this->config->get('email_diff_popup_width',1080),$this->config->get('email_diff_popup_height',640), 'title="'.JText::_('HIKASHOP_MODIFICATIONS').'"', '', 'link',true
					);
				}

				if(@$this->mail_name == 'order_status_notification') {
					$popupHelper = hikashop_get('helper.popup');
					echo ' ' . $popupHelper->display(
						'<span style="text-transform: none;" class="btn btn-primary"><i class="fa fa-magic" aria-hidden="true"></i> '.JText::_('PER_STATUS_OVERRIDE') . '</span>',
						'PER_STATUS_OVERRIDE',
						'\''.'index.php?option=com_hikashop&amp;tmpl=component&amp;ctrl=email&amp;task=orderstatus&amp;type=preload&amp;email_name='.$this->mail_name.'\'',
						'hikashop_edit_preload_status',
						$this->config->get('email_status_popup_width',1080),$this->config->get('email_status_popup_height',640), 'title="'.JText::_('PER_STATUS_OVERRIDE').'"', '', 'link',true
					);
				}
?>
			</div>
<?php
				echo $this->editor->displayCode(
					'data[mail][preload]',
					@$this->mail->preload,
					array('autoFocus' => false)
				);
?>
			</div>
		</div>
	</div>
	<div class="clr"></div>

	<input type="hidden" name="mail_name" value="<?php echo @$this->mail_name; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="email" />
	<?php echo JHTML::_('form.token'); ?>
</form>
<script type="text/javascript">
if(!window.emailMgr) window.emailMgr = {};
window.emailMgr.toggleTags = function(btn) {
	var d = document, btnText = d.getElementById('hikashop_filter_collapsable_title_text'),
	tagsDiv = d.getElementById('hikashop_mail_ref_array'), editorDiv = d.getElementById('hikashop_html_version_main_div');
	if(tagsDiv.style.display == 'none') {
		tagsDiv.style.display = '';
		editorDiv.style.width = '63%';
		editorDiv.style.float = 'left';
		btnText.innerHTML = ' <?php echo JText::_('HIKA_CLOSE_TITLE'); ?>';
	} else {
		tagsDiv.style.display = 'none';
		editorDiv.style.width = '100%';
		editorDiv.style.float = 'none';
		btnText.innerHTML = ' <?php echo JText::_('HIKA_SEE_TITLE'); ?>';
	}
	return false;
};
window.emailMgr.copyToClipboard = function(text) {
	var textArea = document.createElement("textarea");
	textArea.value = text;
	document.body.appendChild(textArea);
	textArea.select();
	try {
		var successful = document.execCommand('copy');
		if(successful) {
		}
		var msg = successful ? 'successful' : 'unsuccessful';
		console.log('Copying text command was ' + msg);
	}
	catch (err) {
		console.log('Oops, unable to copy');
	}
}
</script>

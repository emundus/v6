<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_backend_tile_edition">
	<div class="hk-container-fluid">
		<div class="hkc-xl-6 hkc-lg-6 hikashop_tile_block hikashop_mail_edit_general"><div>
			<div class="hikashop_tile_title"><?php echo JText::_('MAIN_INFORMATION'); ?></div>
			<dl class="hika_options large">
				<dt><?php echo JText::_('EMAIL_SUBJECT'); ?></dt>
				<dd class="input_large">
					<input type="text" name="data[mail][subject]" id="subject" class="inputbox" value="<?php echo $this->escape(@$this->mail->subject); ?>" />
				</dd>

				<dt><?php echo JText::_('SEND_HTML'); ?></dt>
				<dd class=""><?php
					echo JHTML::_('hikaselect.booleanlist', 'data[mail][html]' , 'onchange="updateEditor(this.value)"', $this->mail->html);
				?></dd>

				<dt><?php echo JText::_('HIKA_PUBLISHED'); ?></dt>
				<dd class=""><?php
					echo JHTML::_('hikaselect.booleanlist', 'data[mail][published]' , '', $this->mail->published);
				?></dd>
<?php if(!empty($this->email_history_plugin)) { ?>
				<dt><?php echo JText::_('EMAIL_HISTORY'); ?></dt>
				<dd class=""><?php
					echo JHTML::_('hikaselect.booleanlist', 'data[mail][email_log_published]' , '', $this->mail->email_log_published);
				?></dd>
<?php } ?>

				<dt><?php echo JText::_('EMAIL_TEMPLATE');  ?></dt>
				<dd class=""><?php
					echo $this->emailtemplateType->display('data[mail][template]', $this->mail->template, $this->mail->mail_name);
				?></dd>

				<dt><?php echo JText::_('ATTACHED_FILES'); ?></dt>
				<dd class=""><?php
	$options = array(
		'classes' => array(
			'mainDiv' => 'hikashop_main_file_div',
			'contentClass' => 'hikashop_product_files',
			'btn_upload' => 'fa fa-upload'
		),
		'upload' => true,
		'tooltip' => true,
		'text' => JText::_('HIKA_MAIL_FILES_EMPTY_UPLOAD'),
		'uploader' => array('email', 'mail_file'),
		'vars' => array(
			'mail_name' => $this->mail->mail_name,
			'file_type' => 'file'
		)
	);
	$content = array();
	if(!empty($this->mail->attach)) {
		$js = null;
		foreach($this->mail->attach as $k => $v) {
			$v->uploader_id = 'hikashop_mail_files';
			$v->field_name = 'data[mail][attachments][]';
			$content[] = hikashop_getLayout('upload', 'file_entry', $v, $js);
		}
	}
	echo $this->uploaderType->displayFileMultiple('hikashop_mail_files', $content, $options);
				?><input type="hidden" name="data[mail][attachments][]" value=""/></dd>

			</dl>
		</div></div>

		<div class="hkc-xl-6 hkc-lg-6 hikashop_tile_block hikashop_mail_edit_general"><div>
			<div class="hikashop_tile_title"><?php echo JText::_('SENDER_INFORMATIONS'); ?></div>
			<dl class="hika_options large">
				<dt><?php echo JText::_('FROM_NAME'); ?></dt>
				<dd class="input_large">
					<input type="text" name="data[mail][from_name]" class="inputbox" value="<?php echo $this->escape(@$this->mail->from_name); ?>" placeholder="<?php echo $this->escape($this->mail->default_values->from_name); ?>"/>
				</dd>

				<dt><?php echo JText::_('FROM_ADDRESS'); ?></dt>
				<dd class="input_large">
					<input type="text" name="data[mail][from_email]" class="inputbox" value="<?php echo $this->escape(@$this->mail->from_email); ?>" placeholder="<?php echo $this->escape($this->mail->default_values->from_email); ?>"/>
				</dd>

				<dt><?php echo JText::_('REPLYTO_NAME'); ?></dt>
				<dd class="input_large">
					<input type="text" name="data[mail][reply_name]" class="inputbox" value="<?php echo $this->escape(@$this->mail->reply_name); ?>" placeholder="<?php echo $this->escape($this->mail->default_values->reply_name); ?>"/>
				</dd>

				<dt><?php echo JText::_('REPLYTO_ADDRESS'); ?></dt>
				<dd class="input_large">
					<input type="text" name="data[mail][reply_email]" class="inputbox" value="<?php echo $this->escape(@$this->mail->reply_email); ?>" placeholder="<?php echo $this->escape($this->mail->default_values->reply_email); ?>"/>
				</dd>

				<dt><?php echo JText::_('BCC'); ?></dt>
				<dd class="input_large">
					<input type="text" name="data[mail][bcc_email]" class="inputbox" value="<?php echo $this->escape(@$this->mail->bcc_email); ?>"/>
				</dd>

				<dt><?php echo JText::_('CC'); ?></dt>
				<dd class="input_large">
					<input type="text" name="data[mail][cc_email]" class="inputbox" value="<?php echo $this->escape(@$this->mail->cc_email); ?>"/>
				</dd>
			</dl>
		</div></div>
	</div>
</div>

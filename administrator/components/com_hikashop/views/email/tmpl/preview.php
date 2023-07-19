<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(empty($this->mailClass)) {
	echo hikashop_display(JText::_('NO_PREVIEW_AVAILABLE'));
	return;
}
?>
<form class="hikashop_email_preview" action="<?php echo hikashop_completeLink('email&task=preview'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<?php
if(method_exists($this->previewMaker, 'getSelector'))
	echo $this->previewMaker->getSelector($this->formData);
if(!empty($this->previewMaker->displaySubmitButton)) { ?>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="javascript:submitbutton('preview'); return false;"><?php echo JText::_('HIKA_SUBMIT'); ?></button>
	</div>
<?php
}
?>
	<input type="hidden" name="mail_name" value="<?php echo $this->mail_name; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="ctrl" value="email" />
	<?php echo JHTML::_('form.token'); ?>
</form>
<?php
if(!empty($this->mailClass->mailer->Body)) {
?>
<div class="hikashop_email_preview">
<?php
	echo $this->mailClass->mailer->Body;
?>
</div>
<?php
}

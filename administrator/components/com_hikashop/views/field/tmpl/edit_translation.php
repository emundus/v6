<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><h4 style="float:left"><?php echo JText::_('HIKA_TRANSLATIONS'); ?> : <?php echo $this->translationHelper->getFlag(key($this->field->translations)); ?></h4>
<div class="toolbar" id="toolbar" style="float: right;">
	<button class="btn btn-success" type="button" onclick="submitbutton('save_translation');"><i class="fa fa-save"></i> <?php echo JText::_('OK'); ?></button>
</div>
<div style="clear:both"></div>
<form action="<?php echo hikashop_completeLink('field'); ?>" name="adminForm" id="adminForm" method="post" enctype="multipart/form-data">
<?php
if(!empty($this->field->translations)) {
	foreach($this->field->translations as $language_id => $translation) {
?>
	<dl class="hika_options">
		<dt class="hikashop_field_realname"><label><?php echo JText::_('FIELD_LABEL'); ?></label></dt>
		<dd class="hikashop_field_realname"><input type="text" name="translation[field_realname][<?php echo $language_id; ?>]" value="<?php echo @$translation->field_realname->value; ?>"/></dd>
<?php
		if(!empty($this->field->field_options['errormessage'])) {
?>
		<dt class="hikashop_field_errormessage"><label><?php echo JText::_('FIELD_ERROR'); ?></label></dt>
		<dd class="hikashop_field_errormessage">
			<input type="hidden" name="originals[field_errormessage]" value="<?php echo $this->field->field_options['errormessage']; ?>"/>
			<input type="text" name="translations[field_errormessage]" value="<?php echo $this->translationHelper->loadOne($this->field->field_options['errormessage'], $language_id); ?>"/>
		</dd>
<?php
		}
		if($this->custom_text && !empty($this->field->field_options['customtext'])) {
?>
		<dt class="hikashop_field_customtext"><label><?php echo JText::_('CUSTOM_TEXT'); ?></label></dt>
		<dd class="hikashop_field_customtext">
			<input type="hidden" name="originals[field_customtext]" value="<?php echo $this->field->field_options['customtext']; ?>"/>
			<input type="text" name="translations[field_customtext]" value="<?php echo $this->translationHelper->loadOne($this->field->field_options['customtext'], $language_id); ?>"/>
		</dd>
<?php
		}
?><?php
if($this->values && !empty($this->field->field_value) && count($this->field->field_value)) {
?>
		<table class="table table-striped table-hover">
			<thead>
				<tr>
					<td>
						<?php echo JText::_('HIKA_ORIGINAL_TEXT'); ?>
					</td>
					<td>
						<?php echo JText::_('HIKA_TRANSLATION'); ?>
					</td>
				</tr>
			</thead>
			<tbody>
<?php
	$i = 1;
	foreach($this->field->field_value as $value => $params) {
?>
				<tr>
					<td>
						<?php echo $params->value; ?>
					</td>
					<td>
						<input type="hidden" name="originals[value_<?php echo $i; ?>]" value="<?php echo $params->value; ?>"/>
						<input type="text" name="translations[value_<?php echo $i; ?>]" value="<?php echo $this->translationHelper->loadOne($params->value, $language_id); ?>"/>
					</td>
				</tr>
<?php
		$i++;
	}
?>
			</tbody>
		</table>
<?php
}
?>
	</dl>
<?php
	}
}
?>
	<input type="hidden" name="language_id" value="<?php echo key($this->field->translations); ?>" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->field->field_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="field" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

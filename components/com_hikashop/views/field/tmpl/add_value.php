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
if(empty($this->field)) {
    hikashop_display('Field not found');
    return;
}
if(empty($this->field->field_options['allow_add'])) {
    hikashop_display('"Add new value" setting not activated in the field');
    return;
}
?>
<form action="<?php echo hikashop_completeLink('field&task=save_value'); ?>" method="post" name="adminForm" id="form_value">
    <div style="float:left;">
    <h2><?php echo JText::_('FIELD_ADDVALUE'); ?></h2>
    </div>
    <div style="float:right;">
    <button class="btn btn-success" onclick="document.getElementById('form_value').submit();" title="<?php echo $this->escape(JText::_('OK')); ?>"><i class="fas fa-save"></i> <?php echo JText::_('OK'); ?></button>
    </div>
    <div style="clear:both;"></div>
    <dl class="hika_options">
        <dt><?php echo JText::_('FIELD_VALUE')?></dt>
        <dd><input type="text" name="value_title" value="" class="<?php echo HK_FORM_CONTROL_CLASS; ?>"/></dd>
        <dt><?php echo JText::_('FIELD_TITLE')?></dt>
        <dd><input type="text" name="value_value" value="" class="<?php echo HK_FORM_CONTROL_CLASS; ?>"/></dd>
        <dt><?php echo JText::_('FIELD_DISABLED')?></dt>
        <dd>
            <select name="values_disabled" class="<?php echo HK_FORM_SELECT_CLASS; ?> no-chzn">
                <option selected value="0"><?php echo JText::_('HIKASHOP_NO'); ?></option>
                <option value="1"><?php echo JText::_('HIKASHOP_YES'); ?></option>
            </select>
        </dd>
    </dl>
    <input type="hidden" name="option" value="com_hikashop" />
	<input type="hidden" name="task" value="save_value" />
	<input type="hidden" name="ctrl" value="field" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="field_id" value="<?php echo $this->field->field_id; ?>" />
    <?php echo JHTML::_( 'form.token' ); ?>
</form>

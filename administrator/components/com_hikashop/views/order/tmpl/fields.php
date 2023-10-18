<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="submitbutton('savefields');"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('order',true); ?>" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
	<table width="100%" class="admintable table">
		<?php
		if(!empty($this->fields['order'])){
			foreach($this->fields['order'] as $fieldName => $oneExtraField) {
			?>
				<tr>
					<td class="key">
						<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
					</td>
					<td>
						<?php echo $this->fieldsClass->display($oneExtraField,$this->element->$fieldName,'data[order]['.$fieldName.']'); ?>
					</td>
				</tr>
			<?php
			}
		}
		$this->setLayout('notification'); echo $this->loadTemplate();?>
	</table>
	<input type="hidden" name="data[order][history][history_type]" value="modification" />
	<input type="hidden" name="data[order][order_id]" value="<?php echo @$this->element->order_id;?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="order" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><h1><?php echo JText::_('NEW_GUEST_USER'); ?>
</h1>
<form action="<?php echo hikashop_completeLink('order&task=save&subtask=guest&tmpl=component'); ?>" name="hikashop_order_guest_form" id="hikashop_order_guest_form" method="post" enctype="multipart/form-data">
	<dl class="hika_options">
		<dt class="hikashop_order_guest"><label><?php echo JText::_('HIKA_EMAIL'); ?></label></dt>
		<dd class="hikashop_order_guest">
			<input type="text" name="email" value="" placeholder="<?php echo $this->escape(JText::_('ENTER_AN_EMAIL_ADDRESS_NOT_ALREADY_USED_ON_THE_WEBSITE')); ?>" />
		</dd>
	</dl>
<div style="clear:both;"></div>
	<a class="btn btn-success" href="#save" onclick="return window.hikashop.submitform('save','hikashop_order_guest_form');"><i class="fa fa-save"></i> <?php echo JText::_('HIKA_SAVE'); ?></a>
	<input type="hidden" name="data[order][history][history_type]" value="modification" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->order_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="subtask" value="guest" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="ctrl" value="order" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

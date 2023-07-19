<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>" method="post"  name="adminForm" id="adminForm">
	<div>
		<label><?php echo JText::_('ORDER_STATUS'); ?></label>
		<?php echo $this->order_statusType->display('order_status', $this->order_status, 'class="custom-select" onchange="document.getElementById(\'hika_code_editor\').innerHTML = \'\'; this.form.submit();"', false); ?>
		<div class="toolbar" id="toolbar" style="float: right;">
			<button class="btn btn-success" type="button" onclick="javascript:submitbutton('saveorderstatus'); return false;"><i class="fa fa-save"></i> <?php echo JText::_('HIKA_SAVE'); ?></button>
		</div>
	</div>
	<div id="hika_code_editor">
		<?php echo $this->editor->displayCode('emailcontent',$this->content); ?>
	</div>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="orderstatus" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="ctrl" value="email" />
	<input type="hidden" name="email_name" value="<?php echo $this->email_name; ?>" />
	<input type="hidden" name="type" value="<?php echo $this->type; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

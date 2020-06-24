<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div>
	<div classs="title" style="float:left;">
		<h1><?php echo JText::_( 'ORDER_NEW_STATUS' ); ?></h1>
	</div>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn btn-success" type="button" onclick="submitbutton('savechangestatus');"><i class="fa fa-save"></i> <?php echo JText::_('OK'); ?></button>
	</div>
</div>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('order',true); ?>" method="post"  name="adminForm" id="adminForm">
	<table width="100%" class="admintable table">
		<tr>
			<td class="key">
				<label for="data[order][order_status]">
					<?php echo JText::_( 'ORDER_NEW_STATUS' ); ?>
				</label>
			</td>
			<td>
				<?php echo @$this->element->mail_status; ?>
			</td>
		</tr>
		<?php $this->setLayout('notification'); echo $this->loadTemplate();?>
	</table>
	<input type="hidden" name="data[order][history][history_type]" value="modification" />
	<input type="hidden" name="edit" value="<?php echo hikaInput::get()->getInt('edit',0);?>" />
	<input type="hidden" name="data[order][order_id]" value="<?php echo @$this->element->order_id;?>" />
	<input type="hidden" id="data[order][order_status]" name="data[order][order_status]" value="<?php echo trim(@$this->element->order_status); ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="order" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

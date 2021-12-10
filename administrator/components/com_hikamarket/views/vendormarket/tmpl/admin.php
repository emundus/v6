<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="submitbutton('save_admin');"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikamarket::completeLink('vendor', true); ?>" method="post" name="adminForm" id="adminForm">
	<table class="admintable" style="width:100%;">
		<tr>
			<td class="key">
				<label for="data[vendor][vendor_admin_id]">
					<?php echo JText::_( 'HIKA_USER' ); ?>
				</label>
			</td>
			<td>
				<?php
				$type = hikamarket::get('shop.type.user');
				echo $type->display('data[vendor][vendor_admin_id]', hikaInput::get()->getVar('vendor_admin_id', 0));
				?>
			</td>
		</tr>
	</table>
	<input type="hidden" name="data[vendor][history][history_type]" value="modification" />
	<input type="hidden" name="data[vendor][vendor_id]" value="<?php echo @$this->element->vendor_id;?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="order" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

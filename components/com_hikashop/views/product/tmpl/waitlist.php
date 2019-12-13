<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_product_waitlist_<?php echo hikaInput::get()->getInt('cid');?>_page" class="hikashop_product_waitlist_page">
	<div class="hikashop_product_waitlist_title"><?php
		$url = '<a href="'. $this->product_url.'">'. $this->product->product_name.'</a>';
		echo Jtext::sprintf('WAITLIST_FOR_PRODUCT', $url);
	?></div>
	<fieldset>
		<div class="toolbar" id="toolbar" style="float: right;">
			<button class="hikabtn hikabtn-success" type="button" onclick="submitform('add_waitlist');"><i class="fa fa-check"></i> <?php echo JText::_('OK'); ?></button>
			<button class="hikabtn hikabtn-danger" type="button" onclick="history.back();"><i class="fa fa-times"></i> <?php echo JText::_('HIKA_CANCEL'); ?></button>
		</div>
	</fieldset>
	<div class="iframedoc" id="iframedoc"></div>
	<form action="<?php echo hikashop_completeLink('product'); ?>" method="post"  name="adminForm" id="adminForm">
		<table>
			<tr>
				<td class="key">
					<label for="data[register][name]">
						<?php echo JText::_( 'HIKA_USER_NAME' ); ?>
					</label>
				</td>
				<td>
					<input type="text" name="data[register][name]" size="40" value="<?php echo $this->escape(@$this->element->name);?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="data[register][email]">
						<?php echo JText::_( 'HIKA_EMAIL' ); ?>
					</label>
				</td>
				<td>
					<input type="text" name="data[register][email]" size="40" value="<?php echo $this->escape(@$this->element->email);?>" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="data[register][product_id]" value="<?php echo hikaInput::get()->getInt('cid');?>" />
		<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="cid" value="<?php echo hikaInput::get()->getInt('cid');?>" />
		<input type="hidden" name="ctrl" value="product" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>

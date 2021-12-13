<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo hikamarket::completeLink('order');?>" method="post" name="hikamarket_form" id="hikamarket_order_create_form">

	<dl class="hikam_options">
		<dt class="hikamarket_order_customer"><label><?php echo JText::_('CUSTOMER'); ?></label></dt>
		<dd class="hikamarket_order_customer"><?php
			echo $this->nameboxType->display(
				'data[order][order_user_id]',
				'',
				hikamarketNameboxType::NAMEBOX_SINGLE,
				'user',
				array(
					'customer' => true,
					'delete' => true,
					'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
					'id' => 'hikamarket_order_create_customer_namebox'
				)
			);
		?></dd>

		<dt class="hikamarket_order_currency"><label><?php echo JText::_('CURRENCY'); ?></label></dt>
		<dd class="hikamarket_order_currency"><?php
			echo $this->currencyType->display('data[order][order_currency_id]', $this->main_currency);
		?></dd>
	</dl>

	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="create" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>

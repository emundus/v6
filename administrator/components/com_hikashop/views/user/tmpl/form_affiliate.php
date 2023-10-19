<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="clear_both"></div>
<div class="hkc-lg-6 hikashop_tile_block hikashop_user_addresses_general"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('AFFILIATE'); ?></div>
	<dl class="hika_options large">

		<dt><label><?php
			echo JText::_('AFFILIATE_ACCOUNT_ACTIVE');
		?></label></dt>
		<dd><?php
			echo JHTML::_('hikaselect.booleanlist', 'data[user][user_partner_activated]', '', @$this->user->user_partner_activated);
		?></dd>
		<dt><label for="user_partner_email"><?php
			echo JText::_('PAYMENT_EMAIL_ADDRESS');
		?></label></dt>
		<dd class="input_large">
			<input type="text" size="30" name="data[user][user_partner_email]" id="user_partner_email" class="inputbox" value="<?php echo $this->escape(@$this->user->user_partner_email); ?>" />
		</dd>

		<dt><label><?php
			echo JText::_('PARTNER_CURRENCY');
		?></label></dt>
		<dd><?php
			if(!$this->config->get('allow_currency_selection', 0) || empty($this->user->user_currency_id)) {
				$this->user->user_currency_id =  $this->config->get('partner_currency',1);
			}
			if($this->config->get('allow_currency_selection', 0)) {
				echo $this->currencyType->display('data[user][user_currency_id]', $this->user->user_currency_id);
			} else {
				$currency = $this->currencyClass->get($this->user->user_currency_id);
				echo $currency->currency_code;
			}
		?></dd>
		<dt><label><?php
			echo JText::_('CUSTOM_FEES');
		?></label></dt>
		<dd><?php
			echo JHTML::_('hikaselect.booleanlist', 'data[user][user_params][user_custom_fee]', 'onchange="updateCustomFeesPanel(this.value);return false;"', @$this->user->user_params->user_custom_fee);
		?></dd>

		<dt><label><?php
			echo JText::_('PARTNER');
		?></label></dt>
		<dd><?php
			echo $this->nameboxType->display(
				'data[user][user_partner_id]',
				@$this->user->user_partner_id,
				hikashopNameboxType::NAMEBOX_SINGLE,
				'user',
				array(
					'delete' => true,
					'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				)
			);
		?></dd>

	</dl>

	<div id="custom_fees_panel" <?php if(empty($this->user->user_params->user_custom_fee)) echo 'style="display:none"';?>>
	<dl class="hika_options large">
		<dt><label><?php
			echo JText::_('PARTNER_FEES_CURRENCY');
		?></label></dt>
		<dd><?php
			echo $this->currencyType->display("data[user][user_params][partner_fee_currency]",@$this->user->user_params->partner_fee_currency);
		?></dd>
		<dt><label for="user_partner_email"><?php
			echo JText::_('PARTNER_LEAD_FEE');
		?></label></dt>
		<dd class="">
			<input type="text" size="5" name="data[user][user_params][user_partner_lead_fee]" class="inputbox" value="<?php echo $this->escape(@$this->user->user_params->user_partner_lead_fee); ?>" />
		</dd>
		<dt><label for="user_partner_email"><?php
			echo JText::_('PARTNER_ORDER_PERCENT_FEE');
		?></label></dt>
		<dd class="">
			<input type="text" size="5" name="data[user][user_params][user_partner_percent_fee]" class="inputbox" value="<?php echo $this->escape(@$this->user->user_params->user_partner_percent_fee); ?>" />%
		</dd>
		<dt><label for="user_partner_email"><?php
			echo JText::_('PARTNER_ORDER_FLAT_FEE');
		?></label></dt>
		<dd class="">
			<input type="text" size="5" name="data[user][user_params][user_partner_flat_fee]" class="inputbox" value="<?php echo $this->escape(@$this->user->user_params->user_partner_flat_fee); ?>" />
		</dd>
		<dt><label for="user_partner_email"><?php
			echo JText::_('PARTNER_CLICK_FEE');
		?></label></dt>
		<dd class="">
			<input type="text" size="5" name="data[user][user_params][user_partner_click_fee]" class="inputbox" value="<?php echo $this->escape(@$this->user->user_params->user_partner_click_fee); ?>" />
		</dd>
	</dl>
	</div>

</div></div>

<?php if(!empty($this->user->user_partner_activated)) { ?>
<div class="hkc-lg-6 hikashop_tile_block hikashop_user_addresses_general"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('STATS'); ?></div>
<?php
	$affiliate_payment_delay = $this->config->get('affiliate_payment_delay');
	if(!empty($affiliate_payment_delay))
		$delayType = hikashop_get('type.delay');
?>
	<table class="admintable table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th></th>
				<th><?php echo JText::_('HIKASHOP_ACTIONS'); ?></th>
<?php if(!empty($affiliate_payment_delay)) { ?>
				<th><?php
					echo hikashop_tooltip(JText::sprintf('AMOUNT_DELAY', $delayType->displayDelay($this->config->get('affiliate_payment_delay'))), JText::_('PAYABLE'), '', JText::_('PAYABLE'))
				?></th>
<?php } ?>
				<th><?php echo JText::_('HIKASHOP_TOTAL'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php echo JText::_('CLICKS_UNPAID_AMOUNT'); ?></td>
				<td><?php
					echo $this->popup->display(
						'<span class="hikabtn">'.JText::_('HIKA_DETAILS').'</span>',
						'CLICKS_UNPAID_AMOUNT',
						hikashop_completeLink('user&task=clicks&user_id='.$this->user->user_id.'',true),
						'clicks_link',
						760, 480, '', '', 'link'
					);
				?></td>
<?php if(!empty($affiliate_payment_delay)) { ?>
				<td class="hk_center"><?php
					echo $this->escape(@$this->user->accumulated['currentclicks']);
				?></td>
<?php } ?>
				<td class="hk_center"><?php
					echo $this->escape(@$this->user->accumulated['clicks']);
				?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('LEADS_UNPAID_AMOUNT'); ?></td>
				<td><?php
					echo $this->popup->display(
						'<span class="hikabtn">'.JText::_('HIKA_DETAILS').'</span>',
						'LEADS_UNPAID_AMOUNT',
						hikashop_completeLink('user&task=leads&user_id='.$this->user->user_id.'',true),
						'leads_link',
						760, 480, '', '', 'link'
					);
				?></td>
<?php if(!empty($affiliate_payment_delay)) { ?>
				<td class="hk_center"><?php
					echo $this->escape(@$this->user->accumulated['currentleads']);
				?></td>
<?php } ?>
				<td class="hk_center"><?php
					echo $this->escape(@$this->user->accumulated['leads']);
				?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('SALES_UNPAID_AMOUNT'); ?></td>
				<td><?php
					echo $this->popup->display(
						'<span class="hikabtn">'.JText::_('HIKA_DETAILS').'</span>',
						'SALES_UNPAID_AMOUNT',
						hikashop_completeLink('user&task=sales&user_id='.$this->user->user_id.'',true),
						'sales_link',
						760, 480, '', '', 'link'
					);
				?></td>
<?php if(!empty($affiliate_payment_delay)) { ?>
				<td class="hk_center"><?php
					echo $this->escape(@$this->user->accumulated['currentsales']);
				?></td>
<?php } ?>
				<td class="hk_center"><?php
					echo $this->escape(@$this->user->accumulated['sales']);
				?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('TOTAL_UNPAID_AMOUNT'); ?></td>
				<td><?php
	$total = @$this->user->accumulated['total'];
	if(!empty($affiliate_payment_delay)) {
		$total = @$this->user->accumulated['currenttotal'];
	}
	if($total > 0) {
		echo $this->popup->display(
			'<span class="hikabtn">'.JText::_('PAY_NOW').'</span>',
			'PAY_NOW',
			hikashop_completeLink('user&task=pay&user_id='.$this->user->user_id.'',true),
			'pay_link',
			760, 480, '', '', 'link'
		);
	}
				?></td>
<?php if(!empty($affiliate_payment_delay)) { ?>
				<td class="hk_center"><?php
					echo $this->escape(@$this->user->accumulated['currenttotal']);
				?></td>
<?php } ?>
				<td class="hk_center"><?php
					echo $this->escape(@$this->user->accumulated['total']);
				?></td>
			</tr>
	</tbody>
</table>

</div></div>
<?php } ?>

<div class="clear_both"></div>

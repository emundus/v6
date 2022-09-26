<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><h1><?php echo JText::_('HIKAM_MANGOPAY_TITLE'); ?></h1>
<form action="<?php echo hikamarket::completeLink('mangopay');?>" method="post" name="hikamarket_form" id="hikamarket_mangopay_payout_form">
	<dl class="mangopay_payout dl-horizontal">
		<dt><?php echo JText::_('MANGOPAY_CURRENT_BALANCE'); ?></dt>
		<dd><?php echo $this->currencyClass->format($this->mango_wallet->Balance->Amount / 100, $this->currency_id); ?></dd>
		<dt><?php echo JText::_('MANGOPAY_AUTHORIZED_PAYOUT'); ?></dt>
		<dd><?php echo $this->currencyClass->format($this->maximum_authorized, $this->currency_id); ?></dd>
	</dl>
	<dl class="mangopay_payout dl-horizontal">
		<dt><?php echo JText::_('MANGOPAY_PAYOUT_VALUE'); ?></dt>
		<dd>
			<input type="text" value="<?php echo number_format($this->maximum_authorized, 2, '.', ''); ?>" placeholder="<?php echo number_format($this->maximum_authorized, 2, '.', ''); ?>" name="payout[value]"/>
			<?php echo $this->mango_wallet->Balance->Currency;?>
		</dd>
		<dt><?php echo JText::_('MANGOPAY_PAYOUT_BANK'); ?></dt>
		<dd><?php
			if(empty($this->mango_bank_accounts)) {
				echo '<em>' . JText::_('MANGOPAY_NO_BANK_ACCOUNT') . '</em>';
			} else {
				$bank_accounts = array();
				foreach($this->mango_bank_accounts as $bank_account) {
					$bank_accounts[] = JHTML::_('select.option', $bank_account->Id, $bank_account->OwnerName . ' ' . $bank_account->OwnerAddress . ' (' . $bank_account->Type . ')');
				}
				echo JHTML::_('select.genericlist', $bank_accounts, 'payout[bank]', ' style="width:100%"', 'value', 'text', '');
			}
		?></dd>
	</dl>
	<div>
		<input class="btn btn-primary" value="<?php echo JText::_('MANGOPAY_PAYOUT'); ?>" type="submit" onclick="return window.hikamarket.submitform('dopayout','hikamarket_mangopay_payout_form');"/>
		<div style="float:right">
			<a class="btn btn-info" href="<?php echo hikamarket::completeLink('mangopay'); ?>"><?php echo JText::_('HIKA_CANCEL'); ?></a>
		</div>
	</div>
	<div style="clear:both"></div>
	<input type="hidden" name="payout[wallet]" value="<?php echo $this->walletId; ?>"/>

	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>"/>
	<input type="hidden" name="task" value="payout"/>
	<input type="hidden" name="ctrl" value="mangopay"/>
	<?php echo JHTML::_('form.token'); ?>
</form>

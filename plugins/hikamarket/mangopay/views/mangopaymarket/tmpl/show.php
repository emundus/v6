<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><h1><?php echo JText::_('HIKAM_MANGOPAY_TITLE'); ?></h1>
<form action="<?php echo hikamarket::completeLink('mangopay');?>" method="post" name="hikamarket_form" id="hikamarket_mangopay_form">
	<ul class="hikam_tabs" rel="tabs:hikamarket_mangopay_tab_">
		<li class="active"><a href="#wallet" rel="tab:1" onclick="return window.hikamarket.switchTab(this);"><?php echo JText::_('HIKAM_MANGO_WALLET'); ?></a></li>
		<li><a href="#bank" rel="tab:2" onclick="return window.hikamarket.switchTab(this);"><?php echo JText::_('HIKAM_MANGO_BANK'); ?></a></li>
		<li><a href="#profile" rel="tab:3" onclick="return window.hikamarket.switchTab(this);"><?php echo JText::_('HIKAM_MANGO_PROFILE'); ?></a></li>
		<li><a href="#document" rel="tab:4" onclick="return window.hikamarket.switchTab(this);"><?php echo JText::_('HIKAM_MANGO_DOCUMENTS'); ?></a></li>
	</ul>
	<div style="clear:both"></div>

<div id="hikamarket_mangopay_tab_1">
<?php
	if(count($this->mango_wallets) == 1) {
		$wallet = reset($this->mango_wallets);
?>
	<dl class="mangopay_wallet_details dl-horizontal">
		<dt><?php echo JText::_('CURRENCY'); ?></dt>
		<dd><?php echo $wallet->Currency; ?></dd>
		<dt><?php echo JText::_('MANGOPAY_BALANCE'); ?></dt>
		<dd><?php
			$currency_id = $this->convertCurrency($wallet->Balance->Currency);
			$amount = (float)hikamarket::toFloat($wallet->Balance->Amount) / 100;
			echo $this->currencyClass->format($amount, $currency_id);
		?></dd>
		<dt><?php echo JText::_('MANGOPAY_PAYOUT'); ?></dt>
		<dd><?php
			if($amount > 0)
				echo '<a href="'.hikamarket::completeLink('mangopay&task=payout&wallet='.(int)$wallet->Id).'">'.JText::_('MANGOPAY_DO_PAYOUT').'</a>';
			else
				echo '<em>'.JText::_('MANGOPAY_NO_PAYOUT').'</em>';
		?></dd>
	</dl>
<?php
	} else {
?>
	<table class="hikam_listing <?php echo (HIKASHOP_RESPONSIVE)?'table table-striped table-hover':'hikam_table'; ?>" style="width:100%">
		<thead>
			<tr>
				<th><?php echo JText::_('CURRENCY'); ?></th>
				<th><?php echo JText::_('MANGOPAY_BALANCE'); ?></th>
				<th><?php echo JText::_('MANGOPAY_PAYOUT'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
		foreach($this->mango_wallets as $wallet) {
?>
			<tr>
				<td><?php echo $wallet->Currency; ?></td>
				<td><?php
					$currency_id = $this->convertCurrency($wallet->Balance->Currency);
					$amount = (float)hikamarket::toFloat($wallet->Balance->Amount) / 100;
					echo $this->currencyClass->format($amount, $currency_id);
				?></td>
				<td><?php
					if($amount > 0)
						echo '<a class="btn btn-success" href="'.hikamarket::completeLink('mangopay&task=payout&wallet='.(int)$wallet->Id).'">'.JText::_('MANGOPAY_DO_PAYOUT').'</a>';
					else
						echo '<em>'.JText::_('MANGOPAY_NO_PAYOUT').'</em>';
				?></td>
			</tr>
<?php
		}
?>
		</tbody>
	</table>
<?php
	}
?>
</div>

<div id="hikamarket_mangopay_tab_2" style="display:none;">
<?php
	if(!empty($this->mango_bank_accounts)) {
?>
	<table class="hikam_listing <?php echo (HIKASHOP_RESPONSIVE)?'table table-striped table-bordered':'hikam_table'; ?>" style="width:100%">
		<thead>
			<tr>
				<th><?php echo JText::_('MANGOPAY_BANK_TYPE'); ?></th>
				<th><?php echo JText::_('MANGOPAY_BANK_OWNER_NAME'); ?></th>
				<th><?php echo JText::_('MANGOPAY_BANK_OWNER_ADDRESS'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
			foreach($this->mango_bank_accounts as $bank) {
?>
			<tr>
				<td><?php echo $this->escape($bank->Type); ?></td>
				<td><?php echo $this->escape($bank->OwnerName); ?></td>
				<td><?php echo $this->escape($bank->OwnerAddress); ?></td>
			</tr>
			<tr><td colspan="3">
				<dl class="mangopay_bank_details dl-horizontal"><?php
					foreach($bank->Details as $k => $v) {
						echo '<dt>'.JText::_('MANGOPAY_BANK_'.$k).'</dt>'.
							'<dd>'.$this->escape($v).'</dd>';
					}
				?></dl>
			</td></tr>
<?php
		}
?>
		</tbody>
	</table>
<?php
	} else {
		echo '<p><em>'.JText::_('MANGOPAY_NO_BANKACOUNT').'</em></p>';
	}
?>
	<a class="btn btn-info" href="<?php echo hikamarket::completeLink('mangopay&task=bank'); ?>"><?php echo JText::_('MANGOPAY_ADD_BANKACCOUNT'); ?></a>
</div>

<div id="hikamarket_mangopay_tab_3" style="display:none;">
	<dl class="mangopay_profile_main dl-horizontal">
		<dt><?php
			echo JText::_('HIKA_NAME');
		?></dt>
		<dd><input type="text" name="mango[name]" value="<?php echo $this->escape($this->mango_vendor->Name); ?>"/></dd>
		<dt><?php
			echo JText::_('MANGOPAY_EMAIL');
		?></dt>
		<dd><input type="text" name="mango[email]" value="<?php echo $this->escape($this->mango_vendor->Email); ?>"/></dd>
		<dt><?php
			echo JText::_('MANGOPAY_LEGALPERSONTYPE');
		?></dt>
		<dd><?php
			$values = array(
				'BUSINESS' => JHTML::_('select.option', 'BUSINESS', JText::_('MANGOPAY_PERSONTYPE_BUSINESS')),
				'ORGANIZATION' => JHTML::_('select.option', 'ORGANIZATION', JText::_('MANGOPAY_PERSONTYPE_ORGANIZATION')),
				'SOLETRADER' => JHTML::_('select.option', 'SOLETRADER', JText::_('MANGOPAY_PERSONTYPE_SOLETRADER')),
			);
			echo JHTML::_('select.genericlist', $values, 'mango[legalpersontype]', '', 'value', 'text', $this->mango_vendor->LegalPersonType);
		?></dd>
		<dt><?php
			echo JText::_('MANGOPAY_HEADQUARTERSADDRESS');
		?></dt>
		<dd><input type="text" name="mango[headquartersaddress]" value="<?php echo $this->escape($this->mango_vendor->HeadquartersAddress); ?>"/></dd>
	</dl>

	<h3><?php echo JText::_('MANGOPAY_LEGALREPRESENTATIVE'); ?></h3>
	<dl class="mangopay_profile_legal dl-horizontal">
		<dt><?php
			echo JText::_('MANGOPAY_LEGALREPRESENTATIVEFIRSTNAME');
		?></dt>
		<dd><input type="text" name="mango[legalrepresentativefirstname]" value="<?php echo $this->escape($this->mango_vendor->LegalRepresentativeFirstName); ?>"/></dd>
		<dt><?php
			echo JText::_('MANGOPAY_LEGALREPRESENTATIVELASTNAME');
		?></dt>
		<dd><input type="text" name="mango[legalrepresentativelastname]" value="<?php echo $this->escape($this->mango_vendor->LegalRepresentativeLastName); ?>"/></dd>
		<dt><?php
			echo JText::_('MANGOPAY_LEGALREPRESENTATIVEADDRESS');
		?></dt>
		<dd><input type="text" name="mango[legalrepresentativeaddress]" value="<?php echo $this->escape($this->mango_vendor->LegalRepresentativeAddress); ?>"/></dd>
		<dt><?php
			echo JText::_('MANGOPAY_LEGALREPRESENTATIVEEMAIL');
		?></dt>
		<dd><input type="text" name="mango[legalrepresentativeemail]" value="<?php echo $this->escape($this->mango_vendor->LegalRepresentativeEmail); ?>"/></dd>
		<dt><?php
			echo JText::_('MANGOPAY_LEGALREPRESENTATIVEBIRTHDAY');
		?></dt>
		<dd><?php
			$birthday = (int)$this->mango_vendor->LegalRepresentativeBirthday;
			if(empty($birthday))
				$birthday = time();
			echo JHTML::_('calendar', hikamarket::getDate($birthday,'%Y-%m-%d'), 'mango[legalrepresentativebirthday]','mango_legalrepresentativebirthday','%Y-%m-%d',array('size' => '20'));
		?></dd>
		<dt><?php
			echo JText::_('MANGOPAY_LEGALREPRESENTATIVENATIONALITY');
		?></dt>
		<dd><?php
			$countries = $this->getCountryList();
			echo JHTML::_('select.genericlist', $countries, 'mango[legalrepresentativenationality]', '', 'value', 'text', $this->mango_vendor->LegalRepresentativeNationality);
		?></dd>
		<dt><?php
			echo JText::_('MANGOPAY_LEGALREPRESENTATIVECOUNTRYOFRESIDENCE');
		?></dt>
		<dd><?php
			echo JHTML::_('select.genericlist', $countries, 'mango[legalrepresentativecountryofresidence]', '', 'value', 'text', $this->mango_vendor->LegalRepresentativeCountryOfResidence);
		?></dd>
	</dl>

	<input class="btn btn-primary" value="<?php echo JText::_('HIKA_SAVE'); ?>" type="submit" onclick="return window.hikamarket.submitform('save','hikamarket_mangopay_form');"/>
</div>

<div id="hikamarket_mangopay_tab_4" style="display:none;">
	<dl class="mangopay_profile_documents dl-horizontal">
		<dt><?php
			echo JText::_('MANGOPAY_STATUTE');
		?></dt>
		<dd><?php
			if($this->mango_vendor->Statute == null) {
				echo '<em>' . JText::_('HIKA_NONE') . '</em>';
			} else {
				echo $this->escape($this->mango_vendor->Statute);
			}
		?></dd>
		<dt><?php
			echo JText::_('MANGOPAY_PROOF_REGISTRATION');
		?></dt>
		<dd><?php
			if($this->mango_vendor->ProofOfRegistration == null) {
				echo '<em>' . JText::_('HIKA_NONE') . '</em>';
			} else {
				echo $this->escape($this->mango_vendor->ProofOfRegistration);
			}
		?></dd>
<?php
		if($this->mango_vendor->LegalPersonType == 'BUSINESS') {
?>
		<dt><?php
			echo JText::_('MANGOPAY_SHAREHOLDER_DECLARATION');
		?></dt>
		<dd><?php
			if($this->mango_vendor->ShareholderDeclaration == null) {
				echo '<em>' . JText::_('HIKA_NONE') . '</em>';
			} else {
				echo $this->escape($this->mango_vendor->ShareholderDeclaration);
			}
		?></dd>
<?php
		}
?>
	</dl>
	<a class="btn btn-info" href="<?php echo hikamarket::completeLink('mangopay&task=document'); ?>"><?php echo JText::_('MANGOPAY_ADD_DOCUMENT'); ?></a>
</div>

	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>"/>
	<input type="hidden" name="task" value="show"/>
	<input type="hidden" name="ctrl" value="mangopay"/>
	<?php echo JHTML::_('form.token'); ?>
</form>
